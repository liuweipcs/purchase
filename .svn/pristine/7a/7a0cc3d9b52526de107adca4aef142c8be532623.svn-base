<?php
namespace app\synchcloud\models;
use app\config\Vhelper;
use Yii;
use yii\helpers\ArrayHelper;

class PurchaseOrderTaxes extends \yii\db\ActiveRecord
{  
	const CLOUD_SYNC_STATUS_INIT = 0; //待同步
	const CLOUD_SYNC_STATUS_SUCCESS = 1; //同步成功
	const CLOUD_SYNC_STATUS_FAIL = 2; //同步失败
	
    public static function tableName()
    {
        return '{{%purchase_order_taxes}}';
    }
    
    /**
     * @desc 查询采购单开票点 -- 海外仓
     * @return unknown
     */
    public function getAbPurTicketPointList($fields='*',$purNumbers=[])
    {
    	if( !$purNumbers ) return null;
    	$obj = self::find()
	    	->select($fields)
	    	->from(self::tableName())
	    	->where('pur_number like "ABD%"')
	    	->andWhere(['in','pur_number',$purNumbers]);
    
    	/* echo $obj->createCommand()->getSql();
    	 exit; */
    	$data = $obj->asArray()->all();
    	return $data;
    }

}
