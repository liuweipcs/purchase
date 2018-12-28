<?php
namespace app\synchcloud\models;
use app\services\CommonServices;
use app\services\SupplierServices;
use Yii;
use yii\behaviors\TimestampBehavior;
use app\config\Vhelper;

class SupplierBuyer extends \yii\db\ActiveRecord
{
	const CLOUD_SYNC_STATUS_INIT = 0;
	const CLOUD_SYNC_STATUS_SUCCESS = 1;
	const CLOUD_SYNC_STATUS_FAIL = 2;
	
	const SYNCHRO_ERROR_1 = 1 ;//ERP同步K3失败
	const SYNCHRO_ERROR_2 = 2 ;//ERP更新K3失败
	
    public static function tableName()
    {
        return '{{pur_supplier_buyer}}';
    }

    public function getSyncBuyerSupplierData($fields='*',$limit=100,$conditions='')
    {
    	$where = 'k3_cloud_status = '.self::CLOUD_SYNC_STATUS_INIT;
    	if (!empty($condition)){
    		$where .= $condition;
    	}
    	
    	$obj = self::find()
	    	->select($fields)
	    	->from(self::tableName())
	    	->where($where)
	    	->andWhere('supplier_code != "" and supplier_code is not null')
	    	->limit($limit);
    	 
    	/* echo $obj->createCommand()->getSql();
    	exit; */
    	$data = $obj->asArray()->all();
    	return $data;
    }
    
    /**
     * 更新K3cloud同步状态
     */
    public function updateBuyerSupplierMapSynced($suppliers,$k3_cloud_status,$date,$error = 0){
    	if(!$suppliers) return false;
    	$ret = self::getDb()->createCommand()->update(self::tableName(), array('k3_cloud_status' => $k3_cloud_status,'sync_cloud_error'=>$error,'k3_cloud_time'=>$date,'modify_time'=>$date),array('in','supplier_code',$suppliers))->execute();
    	return $ret;
    }
    
    /**
     * 获取更新后需要同步到K3cloud的采购员和供应商关系数据
     */
    public function getUpdateBuyerSupplierMap($fields='*',$limit = 30,$condition = ''){
    	$where = 'k3_cloud_status = '.self::CLOUD_SYNC_STATUS_SUCCESS.' AND k3_cloud_time < modify_time';
    
    	if (!empty($condition)){
    		$where .= $condition;
    	}
    
    	$obj = self::find()
	    	->select($fields)
	    	->from(self::tableName())
	    	->where($where)
	    	->andWhere('supplier_code != "" and supplier_code is not null')
	    	->limit($limit);
    	
    	/* echo $obj->createCommand()->getSql();
    	 exit; */
    	$data = $obj->asArray()->all();
    	
    	return $data;
    }

}


