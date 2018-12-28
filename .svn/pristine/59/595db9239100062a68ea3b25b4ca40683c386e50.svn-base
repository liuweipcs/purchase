<?php
namespace app\synchcloud\models;
use app\config\Vhelper;
use Yii;
use yii\helpers\ArrayHelper;

class PurchasePaymentRefund extends \yii\db\ActiveRecord
{  
	const CLOUD_SYNC_STATUS_INIT = 0; //待同步
	const CLOUD_SYNC_STATUS_SUCCESS = 1; //同步成功
	const CLOUD_SYNC_STATUS_FAIL = 2; //同步失败
	
	const CLOUD_SYNCH_ERROR_1 = 1 ; //采购员为空
	const CLOUD_SYNCH_ERROR_2 = 2 ; //删除汇总系统原有采购退款记录失败
	
    public static function tableName()
    {
        return '{{%purchase_payment_refund}}';
    }
    
    /**
     * @desc 获取待同步cloud的退款单 【修复】
     */
    public function getSyncReceiptCloudData($fields='*',$limit=100,$conditions='')
    {
    	$where = 'a.is_push_to_k3cloud = '.self::CLOUD_SYNC_STATUS_INIT;
    	if (!empty($conditions)){
    		$where .= $conditions;
    	}
    
    	$obj = self::find()
	    	->select($fields)
	    	->from(self::tableName().' as a')
	    	->leftJoin('pur_purchase_order as b','a.purchase_no = b.pur_number')
	    	->where($where)
	    	->limit($limit);
    
    	/* echo $obj->createCommand()->getSql();
    	 exit; */
    	$data = $obj->asArray()->all();
    	return $data;
    }
    
    /**
     * 更新cloud系统同步状态
     */
    public function updateReceiptSynchCloud($ids=array(),$status,$date,$error = 0){
    	return self::getDb()->createCommand()->update(
    			self::tableName(),array('is_push_to_k3cloud' => $status,'cloud_synch_error'=>$error,'cloud_synch_time'=>$date),
    			['in','id',$ids])->execute();
    }

}
