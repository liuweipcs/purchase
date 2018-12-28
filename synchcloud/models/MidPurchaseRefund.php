<?php
namespace app\synchcloud\models;
use Yii;
use app\config\Vhelper;
class MidPurchaseRefund extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{mid_purchase_payment_refund}}';
    }

    public static function getDb()
    {
        return Yii::$app->mid_cloud;
    }

    /**
     * 批量写入MID采购订单收款表
     */
    public function saveRows($rows)
    {
        $success = [];
        foreach($rows as $k => $v) {
            $model = self::find()->where(['purchase_no' => $v['pur_number']])->one();
            if(!empty($model)) {
                $success[] = $v['id'];
                continue;
            } else {
                $model = new self();
            }
            $model->bill_no         = $v['transaction_number'];        // 单据编号
            $model->provider_id     = $v['supplier_code'];             // 供应商收款单位(来往单位)
            $model->dates           = !empty($v['pay_time']) ? $v['pay_time'] : date('Y-m-d H:i:s', time()); // 业务日期
            $model->pay_id          = $v['our_account_abbreviation'];  // 我方银行帐号-付款帐号
            $model->purchase_id     = $v['purchase_id'];               // 员工编码（付款流水表没有记录，关联采购单信息）
            $model->payment_user_id = $v['create_id'];                 // 付款人（create_id）
            $model->refund_money    = !empty($v['price']) ? $v['price'] : '0.000'; // 应退金额
            $model->purchase_no     = $v['pur_number'];                // 采购订单号
            $model->note            = $v['remarks'];                   // 备注
            $model->move_status     = 0;

            if($v['is_drawback'] == 1) {   // 组织id  101.香港易佰(不退税) 102.深圳易佰(退税)
                $model->org_id = '101';
            } elseif($v['is_drawback'] == 2) {
                $model->org_id = '102';
            }

            $res = $model->save();
            if($res) {
                $success[] = $v['id'];
            }
        }
        return $success;
    }
    
    /**
     * @desc 检查数据是否存在
     */
    public function dataExist($fields='*',$purNumber='') {
    	$obj = self::find()
	    	->select($fields)
	    	->where('purchase_no="'.$purNumber.'"');
    	
	    	$data = $obj->asArray()->one();
    	//print_r($data);exit;
    
    	return $data;
    }
    
    /**
     * @desc 根据采购单号删除记录
     */
    public function delByPo( $po='' ){
    	if( !$po ) return false;
    	$rlt = self::deleteAll(['purchase_no'=>$po]);
    	return $rlt;
    }
    
    public function batchInsertData($key=[],$values=[]){
    	$ret = self::getDb()->createCommand()->batchInsert(self::tableName(),$key, $values)->execute();
    	return $ret;
    }

}