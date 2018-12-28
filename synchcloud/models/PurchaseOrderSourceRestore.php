<?php
namespace app\synchcloud\models;
use app\config\Vhelper;
use Yii;
use yii\helpers\ArrayHelper;

class PurchaseOrderSourceRestore extends \yii\db\ActiveRecord
{  
	const CLOUD_SYNC_STATUS_INIT = 0; //待同步
	const CLOUD_SYNC_STATUS_SUCCESS = 1; //同步成功
	const CLOUD_SYNC_STATUS_FAIL = 2; //同步失败
	
    public static function tableName()
    {
        return '{{%purchase_order_source_restore}}';
    }
    
    /**
     * @desc 获取采购单合同、组织修复
     */
    public function getPoSourceData($fields='*',$purNumbers = [])
    {
    	if( !$purNumbers ) return null;
    
    	$obj = self::find()
	    	->select($fields)
	    	->from(self::tableName().' as a')
	    	->where( ['in','a.pur_number',$purNumbers] )
    		->andWhere('a.source>0 and a.company>0');
    
    	/* echo $obj->createCommand()->getSql();
    	 exit; */
    	$data = $obj->asArray()->all();
    	return $data;
    }
    
    /**
     * @desc 获取待同步、更新汇总系统-采购优惠 【修复】
     */
    public function getSyncDiscountRestoreCloudData($fields='*',$limit=100,$conditions='')
    {
    	$where = 'a.discount_push_to_k3cloud = '.self::CLOUD_SYNC_STATUS_INIT;
    	if (!empty($conditions)){
    		$where .= $conditions;
    	}
    
    	$obj = self::find()
	    	->select($fields)
	    	->from(self::tableName().' as a')
	    	->leftJoin('pur_purchase_order as b','a.pur_number = b.pur_number')
	    	->where($where)
	    	->limit($limit);
    
    	/* echo $obj->createCommand()->getSql();
    	 exit; */
    	$data = $obj->asArray()->all();
    	return $data;
    }
    
    /**
     * 更新cloud系统同步状态-优惠
     */
    public function updateDiscountSynchCloud($ids=array(),$status){
    	return self::getDb()->createCommand()->update(
    			self::tableName(),array('discount_push_to_k3cloud' => $status),
    			['in','id',$ids])->execute();
    }
    
    /**
     * @desc 获取待同步、更新汇总系统-采购运费 【修复】
     */
    public function getSyncFreightRestoreCloudData($fields='*',$limit=100,$conditions='')
    {
    	$where = 'a.freight_push_to_k3cloud = '.self::CLOUD_SYNC_STATUS_INIT;
    	if (!empty($conditions)){
    		$where .= $conditions;
    	}
    
    	$obj = self::find()
	    	->select($fields)
	    	->from(self::tableName().' as a')
	    	->leftJoin('pur_purchase_order as b','a.pur_number = b.pur_number')
	    	->where($where)
	    	->limit($limit);
    
    	/* echo $obj->createCommand()->getSql();
    	 exit; */
    	$data = $obj->asArray()->all();
    	return $data;
    }
    
    /**
     * 更新cloud系统同步状态-运费
     */
    public function updateFreightSynchCloud($ids=array(),$status){
    	return self::getDb()->createCommand()->update(
    			self::tableName(),array('freight_push_to_k3cloud' => $status),
    			['in','id',$ids])->execute();
    }
    
    
    /**
     * @desc 获取待同步、更新汇总系统-采购付款 【修复】
     */
    public function getSyncPaymentRestoreCloudData($fields='*',$limit=100,$conditions='')
    {
    	$where = 'a.pay_push_to_k3cloud = '.self::CLOUD_SYNC_STATUS_INIT;
    	if (!empty($conditions)){
    		$where .= $conditions;
    	}
    
    	$obj = self::find()
	    	->select($fields)
	    	->from(self::tableName().' as a')
	    	->where($where)
	    	->limit($limit);
    
    	/* echo $obj->createCommand()->getSql();
    	 exit; */
    	$data = $obj->asArray()->all();
    	return $data;
    }
    
    /**
     * 更新cloud系统同步状态-付款
     */
    public function updatePaymentSynchCloud($ids=array(),$status){
    	return self::getDb()->createCommand()->update(
    			self::tableName(),array('pay_push_to_k3cloud' => $status),
    			['in','id',$ids])->execute();
    }

}
