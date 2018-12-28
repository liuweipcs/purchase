<?php

namespace app\api\v1\models;

use app\config\Vhelper;
use app\models\PurchaseOrderPayType;
use Yii;

/**
 * This is the model class for table "pur_ali_order_baseinfo".
 *
 * @property integer $id
 * @property integer $buyer_user_id
 * @property string $remark
 * @property string $step_pay_all
 * @property string $buyer_memo
 * @property string $pay_time
 * @property string $buyer_feedback
 * @property string $modify_time
 * @property string $buyer_login_id
 * @property integer $seller_user_id
 * @property string $status
 * @property string $all_delivered_time
 * @property integer $seller_order
 * @property string $business_type
 * @property string $discount
 * @property string $shipping_fee
 * @property string $buyer_remark_icon
 * @property string $confirmed_time
 * @property string $close_reason
 * @property integer $trade_type
 * @property string $create_time
 * @property string $sum_product_payment
 * @property string $seller_credit_level
 * @property string $currency
 * @property string $refund_status
 * @property string $refund
 * @property string $total_amount
 * @property string $receiving_time
 * @property integer $over_sea_order
 * @property string $refund_payment
 * @property string $complete_time
 * @property string $refund_status_for_as
 * @property string $pur_number
 * @property string $order_number
 */
class AliOrderBaseinfo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_ali_order_baseinfo';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['buyer_user_id', 'seller_user_id', 'seller_order','step_pay_all','discount', 'over_sea_order'], 'integer'],
            [['remark', 'buyer_memo', 'buyer_feedback'], 'string'],
            [['pay_time', 'modify_time', 'all_delivered_time', 'confirmed_time', 'create_time', 'receiving_time', 'complete_time'], 'safe'],
            [['shipping_fee', 'sum_product_payment', 'refund', 'total_amount', 'refund_payment'], 'number'],
            [['buyer_login_id', 'status', 'business_type', 'pur_number'], 'string', 'max' => 50],
            [['buyer_remark_icon', 'close_reason', 'seller_credit_level', 'currency', 'refund_status', 'refund_status_for_as'], 'string', 'max' => 255],
            [['order_number','trade_type'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'buyer_user_id' => 'Buyer User ID',
            'remark' => 'Remark',
            'step_pay_all' => 'Step Pay All',
            'buyer_memo' => 'Buyer Memo',
            'pay_time' => 'Pay Time',
            'buyer_feedback' => 'Buyer Feedback',
            'modify_time' => 'Modify Time',
            'buyer_login_id' => 'Buyer Login ID',
            'seller_user_id' => 'Seller User ID',
            'status' => 'Status',
            'all_delivered_time' => 'All Delivered Time',
            'seller_order' => 'Seller Order',
            'business_type' => 'Business Type',
            'discount' => 'Discount',
            'shipping_fee' => 'Shipping Fee',
            'buyer_remark_icon' => 'Buyer Remark Icon',
            'confirmed_time' => 'Confirmed Time',
            'close_reason' => 'Close Reason',
            'trade_type' => 'Trade Type',
            'create_time' => 'Create Time',
            'sum_product_payment' => 'Sum Product Payment',
            'seller_credit_level' => 'Seller Credit Level',
            'currency' => 'Currency',
            'refund_status' => 'Refund Status',
            'refund' => 'Refund',
            'total_amount' => 'Total Amount',
            'receiving_time' => 'Receiving Time',
            'over_sea_order' => 'Over Sea Order',
            'refund_payment' => 'Refund Payment',
            'complete_time' => 'Complete Time',
            'refund_status_for_as' => 'Refund Status For As',
            'pur_number' => 'Pur Number',
            'order_number' => 'Order Number',
        ];
    }

    public static function saveData($pur_number,$order_number,$data){
        $model = self::find()->where(['pur_number'=>$pur_number,'order_number'=>$order_number])->one();
        if(empty($model)){
            $model=new self();
        }
        $model->buyer_user_id       = isset($data['buyerUserId'])       ? $data['buyerUserId'] : '';
        $model->remark              = isset($data['remark'])            ? $data['remark']   : '';
        $model->step_pay_all        = isset($data['stepPayAll'])        ? $data['stepPayAll'] ? 1:0 : 2;
        $model->buyer_memo          = isset($data['buyerMemo'])         ? $data['buyerMemo'] : '';
        $model->pay_time            = isset($data['payTime'])           ? Vhelper::getAliDateTime($data['payTime']) : '';
        $model->buyer_feedback      = isset($data['buyerFeedback'])     ? $data['buyerFeedback'] : '';
        $model->modify_time         = isset($data['modifyTime'])        ? Vhelper::getAliDateTime($data['modifyTime']) : '';
        $model->buyer_login_id      = isset($data['buyerLoginId'])      ? $data['buyerLoginId'] : '';
        $model->seller_user_id      = isset($data['sellerUserId'])      ? $data['sellerUserId'] : '';
        $model->status              = isset($data['status'])            ? $data['status'] : '';
        $model->all_delivered_time  = isset($data['allDeliveredTime'])  ? Vhelper::getAliDateTime($data['allDeliveredTime']) : '';
        $model->seller_order        = isset($data['sellerOrder'])       ? $data['sellerOrder'] ? 1 :0 : 2;
        $model->business_type       = isset($data['businessType'])      ? $data['businessType'] : '';
        $model->discount            = isset($data['discount'])          ? $data['discount'] : '';
        $model->shipping_fee        = isset($data['shippingFee'])       ? $data['shippingFee'] : '';
        $model->confirmed_time      = isset($data['confirmedTime'])     ? Vhelper::getAliDateTime($data['confirmedTime']) : '';
        $model->close_reason        = isset($data['closeReason'])       ? $data['closeReason'] : '';
        $model->trade_type          = isset($data['tradeType'])         ? $data['tradeType'] : '';
        $model->create_time         = isset($data['createTime'])        ? Vhelper::getAliDateTime($data['createTime']) : '';
        $model->sum_product_payment = isset($data['sumProductPayment']) ? $data['sumProductPayment'] : '';
        $model->seller_credit_level = isset($data['sellerCreditLevel']) ? $data['sellerCreditLevel'] : '';
        $model->currency            = isset($data['currency'])          ? $data['currency'] : '';
        $model->refund_status       = isset($data['refundStatus'])      ? $data['refundStatus'] : '';
        $model->refund              = isset($data['refund'])            ? $data['refund'] : '';
        $model->total_amount        = isset($data['totalAmount'])       ? $data['totalAmount'] : '';
        $model->receiving_time      = isset($data['receivingTime'])     ? Vhelper::getAliDateTime($data['receivingTime']) : '';
        $model->over_sea_order      = isset($data['overSeaOrder'])      ? $data['overSeaOrder'] ? 1: 0 : 2;
        $model->refund_payment      = isset($data['refundPayment'])     ? $data['refundPayment'] : '';
        $model->complete_time       = isset($data['completeTime'])      ? Vhelper::getAliDateTime($data['completeTime']) : '';
        $model->refund_status_for_as= isset($data['refundStatusForAs']) ? $data['refundStatusForAs'] : '';
        $model->pur_number          = $pur_number;
        $model->order_number        = $order_number;
        //保存卖家联系方式
        if(isset($data['sellerContact'])){
            AliOrderContact::saveData($pur_number,$order_number,$data['sellerContact'],1);
        }
        //保存买家联系方式
        if(isset($data['buyerContact'])){
            AliOrderContact::saveData($pur_number,$order_number,$data['buyerContact'],2);
        }
        if(isset($data['status'])&&in_array($data['status'],['success','cancel','terminated'])){
            //订单状态是成功,取消,终止则更新数据下次不再拉取,
            PurchaseOrderPayType::updateAll(['is_success'=>1,'check_date'=>date('Y-m-d H:i:s')],['pur_number'=>$pur_number,'platform_order_number'=>$order_number]);
            //记录标记成功日志
            $logDatas = [
                'pur_number'=>$pur_number,
                'order_number'=>$order_number,
                'message'=>'订单状态是成功,取消,终止则更新数据下次不再拉取',
                'error_code'=>'order_cancel_success',
            ];
            AliOrderLog::saveSuccessLog($logDatas);
        }else{
            //只更新查时间，今天不再拉取,
            PurchaseOrderPayType::updateAll(['check_date'=>date('Y-m-d H:i:s')],['pur_number'=>$pur_number,'platform_order_number'=>$order_number]);
        }
        return $model->save();
    }
}
