<?php
namespace app\synchcloud\models;
use Yii;
use app\config\Vhelper;
class MidPurchasePayment extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{mid_purchase_payment}}';
    }

    public static function getDb()
    {
        return Yii::$app->mid_cloud;
    }

    /**
     * 批量写入MID采购订单付款表
     */
    public function saveRows($rows)
    {
        $success = [];
        try {
            foreach($rows as $k => $v) {
                $model = self::find()->where(['purchase_no' => $v['pur_number'], 'bill_no' => $v['transaction_number']])->one();
                if(!empty($model)) {
                    $success[] = $v['id'];
                } else {
                    $model = new self();
                }
                $model->bill_no =  $v['transaction_number']; // 单据编号
                $model->provider_id = $v['supplier_code']; // 供应商收款单位(来往单位)
                $model->dates =  !empty($v['pay_time']) ? $v['pay_time'] : time('Y-m-d H:i:s', time()); // 业务日期
                $model->pay_id = $v['our_account_abbreviation']; // 我方银行帐号-付款帐号
                $model->purchase_id = $v['purchase_id']; // 员工编码（付款流水表没有记录，关联采购单信息）
                $model->payment_user_id = $v['create_id']; // 付款人（create_id）
                $model->pay_amount = $v['price']; // 应付金额
                $model->purchase_no = $v['pur_number']; // 采购订单号
                $model->note = $v['remarks']; // 备注
                $model->move_status = 0;
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
        } catch(\Exception $e) {
            Vhelper::dump($e->getMessage());
        }
        return $success;
    }
    
    /**
     * @desc 检查数据是否存在
     */
    public function dataExist($fields='*',$purNumber='',$billNo = '') {
    	$obj = self::find()
	    	->select($fields)
	    	->where('purchase_no="'.$purNumber.'"')
    		->andWhere('bill_no="'.$billNo.'"');
    	$data = $obj->asArray()->one();
    	//print_r($data);exit;
    
    	return $data;
    }
    
    public function batchInsertData($key=[],$values=[]){
    	$ret = self::getDb()->createCommand()->batchInsert(self::tableName(),$key, $values)->execute();
    	return $ret;
    }
    
    public function updateRows($purchaseNos = array(),$orgId='',$isCompact=''){
    	if( !$purchaseNos || !$orgId ) return false;
    	return self::getDb()->createCommand()->update(
    			self::tableName(),array('is_compact' => $isCompact,'org_id'=>$orgId,'is_update' => 1),
    			['in','purchase_no',$purchaseNos])->execute();
    }
    
}