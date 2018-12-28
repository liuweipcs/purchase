<?php

namespace app\api\v1\models;

use app\config\Vhelper;
use app\models\PurchaseCompact;
use app\models\PurchaseLog;
use app\models\PurchaseNote;
use app\models\PurchaseOrderPayUfxfuiou;
use app\models\PurchaseOrderPayWater;
use app\models\UfxfuiouPayDetail;
use app\models\User;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\db\Exception;

/**
 * This is the model class for table "{{%purchase_order_pay}}".
 *
 * @property integer $id
 * @property integer $pay_status
 * @property string $pur_number
 * @property string $requisition_number
 * @property string $supplier_code
 * @property integer $settlement_method
 * @property string $pay_name
 * @property string $pay_price
 * @property string $create_notice
 * @property integer $applicant
 * @property integer $auditor
 * @property integer $approver
 * @property string $application_time
 * @property string $review_time
 * @property string $processing_time
 * @property string $review_notice
 *
 * @property PurchaseOrderPayLog[] $purchaseOrderPayLogs
 */
class PurchaseOrderPay extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_order_pay}}';
    }

    //保存富友支付回调数据并变更请款单状态;
    public static function savePayResult($resultDatas){
        //通知类型
        $notifyType = isset($resultDatas['notifyType']) ? $resultDatas['notifyType'] :'04';
        //通知主体节点
        $notify   = 'notify'.$notifyType;
        //rspCode 5005 划款成功   5007 划款失败，资金已退回  0000（复核拒绝会出现其他情况还不确定） 011105 转账到代收付异常
        $ufxiouPaydata = UfxfuiouPayDetail::find()
            ->where(['pur_tran_num'=>isset($resultDatas[$notify]['eicSsn']) ? $resultDatas[$notify]['eicSsn'] : null])
            ->one();
        if(empty($ufxiouPaydata)){
            exit('没有找到该交易流水号');
        }
        $ufxiouPaydata->ufxfuiou_tran_num = isset($resultDatas[$notify]['fuiouTransNo']) ?$resultDatas[$notify]['fuiouTransNo'] :'';//富友流水号
        $ufxiouPaydata->tranfer_result_code = isset($resultDatas['rspCode']) ?$resultDatas['rspCode'] :'';//回调状态
        $ufxiouPaydata->tranfer_result_reason = isset($resultDatas['rspDesc']) ?$resultDatas['rspDesc'] :'';//回调原因
        $ufxiouPaydata->tranfer_result_money = isset($resultDatas[$notify]['amt']) ?$resultDatas[$notify]['amt'] :'';//转账金额
        $ufxiouPaydata->pay_status = isset($resultDatas['rspCode']) ?$resultDatas['rspCode'] :'';//回调状态

        $orderPayRequestNumbers = PurchaseOrderPayUfxfuiou::find()
            ->select('requisition_number')
            ->where(['pur_tran_num'=>isset($resultDatas[$notify]['eicSsn']) ? $resultDatas[$notify]['eicSsn'] : null])
            ->andWhere(['status'=>1])
            ->column();
        $pur_tran_num = isset($resultDatas[$notify]['eicSsn']) ? $resultDatas[$notify]['eicSsn'] : '';
        if(!$orderPayRequestNumbers){
            exit('没有该流水号的请款信息');
        }
        $tran = Yii::$app->db->beginTransaction();
        try{
            if(isset($resultDatas['rspCode'])&&$resultDatas['rspCode']=='5005'){
                //回调状态成功更新付款申请状态
                $saveStatus = self::updateSuccess($orderPayRequestNumbers,$pur_tran_num);
                if($saveStatus){
                    $ufxiouPaydata->status = 5;
                    if($ufxiouPaydata->save()==false){
                        throw new Exception('付款详情更新失败01');
                    }
                }else{
                    throw new Exception('付款成功状态更新失败02');
                }
            }elseif (isset($resultDatas['rspCode'])&&($resultDatas['rspCode']=='5007'|| $resultDatas['rspCode']=='0000'||$resultDatas['rspCode']=='011105')){
                //回调状态转账失败或者复核拒绝
                $saveStatus = self::updateFail($orderPayRequestNumbers,$pur_tran_num);
                if($saveStatus){
                    $ufxiouPaydata->status=4;
                    if($ufxiouPaydata->save()==false){
                        throw new Exception('付款详情更新失败02');
                    }
                }
            }else{
                $rspCode = isset($resultDatas['rspCode']) ? $resultDatas['rspCode'] :'';
                throw new Exception('返回编码未知'.$rspCode);
            }
            $tran->commit();
        }catch (Exception $e){
            $tran->rollBack();
            Vhelper::ToMail('接口回调异常','付款接口回调出错');
        }
    }

    //富友付款成功执行操作
    public static function updateSuccess($orderPayRequestNumbers,$pur_tran_num){
        foreach ($orderPayRequestNumbers as $key=>$orderPayRequestNumber){
            $payData = PurchaseOrderPay::find()->where(['requisition_number'=>$orderPayRequestNumber,'pay_status'=>13])->one();
            if(empty($payData)){
                continue;
                // 请款信息为空或者请款单状态不是富友付款待复核则跳过;
            }
            //1合同2网采
            if($payData->source==1){
                 $savePay = self::saveCompactSuccess($payData,$pur_tran_num);
                 if($savePay){
                     self::savePurchaseNote($payData);
                 }
            }elseif($payData->source==2){
                 $savePay = self::saveInternetSuccess($payData,$pur_tran_num);
                if($savePay){
                    self::savePurchaseNote($payData);
                }
            }else{

            }
        }
        return true;
    }

    //富友付款失败执行操作
    public static function updateFail($orderPayRequestNumbers,$pur_tran_num){
        //更新请款单与富有申请绑定关系
        PurchaseOrderPayUfxfuiou::updateAll(['status'=>0,'update_time'=>date('Y-m-d H:i:s',time()),'update_user_name'=>'复核回调'],['pur_tran_num'=>$pur_tran_num]);
        //复核失败或者转账失败请款单回退到待财务付款
        foreach ($orderPayRequestNumbers as $orderPayRequestNumber){
            $payData = PurchaseOrderPay::find()->where(['requisition_number'=>$orderPayRequestNumber,'pay_status'=>[5,13]])->one();
            if(empty($payData)){
                continue;
               //'请款信息为空或者请款单状态不是富友付款待复核';
            }
            $payData->pay_status=4;
            if($payData->save(false)==false){
                return false;
            }
        }
        return true;
    }
    //执行合同请款成功
    public static function saveCompactSuccess($payData,$pur_tran_num,$status=5,$notice='富友支付成功'){
        $pos = PurchaseCompact::getPurNumbers($payData->pur_number);
        if(empty($pos)) {
            return false;
        }
        $compact = PurchaseCompact::find()->select('real_money')->where(['compact_number' => $payData->pur_number])->one();
        if($status == 5) {
            // 付款流程
            $real_money = $compact->real_money;
            $has_pay = \app\models\PurchaseOrderPay::getOrderPaidMoney($payData->pur_number);
            $has_pay = $has_pay+$payData->pay_price;
            $pay_status = $status;
            if(bccomp($real_money, $has_pay, 3) !== 0) {
                $pay_status = 6; // 部分付款
            }

            $payData->pay_status     = 5;
            $payData->payment_notice = $notice;
            $payData->real_pay_price = $payData->pay_price;
            $userName = User::find()->select('username')->where(['id'=>$payData->payer])->scalar();
            $userName = $userName ? $userName : '';
            $log = [
                'pur_number' => $payData->pur_number,
                'note'       => $userName.' 在 '.$payData->payer_time.' 确认了付款(富友)',
                'user'       =>  $userName
            ];
            self::savePurchaseLog($log);
            PurchaseOrder::updateAll(['pay_status' => $pay_status], ['pur_number' => $pos]);
            if($payData->save(false)==false){
                return false;
            }
            //记录流水
            $saveWater = self::savePayWater($payData,$pur_tran_num);
            if(!$saveWater){
                return $saveWater;
            }

            //海外仓NEW流程
            \app\models\PlatformSummary::overseasPayUpdateDemandPaystatus($payData->requisition_number, $notice);
            return true;
        }
    }
    //执行网采请款成功
    public static function saveInternetSuccess($payData,$pur_tran_num,$pay_status=5){
        $model = PurchaseOrderPay::findOne($payData->id);
        $model->pay_status = $pay_status;
        $model->payer_time = date('Y-m-d H:i:s', time());
        $model->payment_notice = '富友支付成功';
        $userName = User::find()->select('username')->where(['id'=>$payData->payer])->scalar();
        $userName = $userName ? $userName : '';
        $s = [
            'pur_number' => $model->pur_number,
            'note'          => '确认付款(富友)',
            'user'       =>  $userName
        ];
        self::savePurchaseLog($s);
        PurchaseOrder::updateAll(['pay_status' => $pay_status], ['pur_number' => $payData->pur_number]);
        $saveWater = self::savePayWater($payData,$pur_tran_num);
        if(!$saveWater){
            return $saveWater;
        }
        if($model->save(false)==false){
            return false;
        }
        //海外仓NEW流程
        \app\models\PlatformSummary::overseasPayUpdateDemandPaystatus($payData->requisition_number, '富友支付成功');
        return true;
    }

    //新增采购日志
    public static function savePurchaseLog($data){
        $model              = new PurchaseLog();
        $model->pur_number  = $data['pur_number'];
        $model->note        = $data['note'];
        $model->request_url = Yii::$app->request->absoluteUrl;
        $model->create_user = $data['user'];
        $model->ip          = Yii::$app->request->userIP;
        $model->create_time = date('Y-m-d H:i:s');
        $model->save();
    }

    public static function savePurchaseNote($payData){
        $model = new PurchaseNote();
        $model->detachBehaviors();
        $model->pur_number = $payData->pur_number;
        $model->note = '富友支付成功';
        $model->create_time = date('Y-m-d H:i:s',time());
        $model->create_id = $payData->payer;
        $model->purchase_type = self::getOrderType($payData->pur_number);
        $model->save(false);
    }

    //保存富有支付流水
    public static function savePayWater($payData,$pur_tran_num){
            $ufxPayDetail = UfxfuiouPayDetail::find()->where(['pur_tran_num'=>$pur_tran_num])->one();
            if(empty($ufxPayDetail)){
                return false;
            }
            $waterModel = new PurchaseOrderPayWater();
            $waterModel->pur_number = $payData->pur_number;
            $waterModel->supplier_code = $payData->supplier_code;
            $waterModel->billing_object_type = 1;
            $waterModel->transaction_number = 'PY' . date('YmdHis', time()) . mt_rand(10, 99);;
            $waterModel->is_bill = 2;
            $waterModel->price = $payData->pay_price;
            $waterModel->write_off_price = $payData->pay_price;
            $waterModel->original_price = $payData->pay_price;
            $waterModel->original_currency = 'RMB';
            $waterModel->write_off_sign = 2;
            $waterModel->monthly_checkout = 1;
            $waterModel->internal_offset_sign = 2;
            $waterModel->remarks = '富友支付成功';
            $waterModel->create_id = $payData->payer;
            $waterModel->create_time = date('Y-m-d H:i:s',time());
            $waterModel->beneficiary_payment_method = $payData->pay_type;
            $waterModel->beneficiary_branch = $ufxPayDetail->branch_bank;
            $waterModel->beneficiary_account = $ufxPayDetail->payee_card_number;
            $waterModel->beneficiary_account_name = $ufxPayDetail->payee_user_name;
            $waterModel->our_branch = '富友账号';
            $waterModel->our_account_abbreviation = '富友账号';
            $waterModel->our_account_holder = $ufxPayDetail->ufxfuiou_account;
            $waterModel->pay_time = date('Y-m-d H:i:s',time());
            $waterModel->is_push_to_k3cloud = 0;
            return $waterModel->save(false);
    }

    public static function getOrderType($pur_number){
        if(strpos($pur_number,'ABD')){
            return 2;
        }
        if(strpos($pur_number,'PO')){
            return 1;
        }
        if(strpos($pur_number,'FBA')){
            return 3;
        }
        return 1;
    }
}
