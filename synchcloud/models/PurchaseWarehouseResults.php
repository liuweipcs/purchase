<?php

namespace app\synchcloud\models;

use app\config\Vhelper;
use Yii;
use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;

class PurchaseWarehouseResults extends \yii\db\ActiveRecord
{

    public static function tableName()
    {
        return '{{%warehouse_results}}';
    }
    
    /**
     * @desc 根据采购单获取采购入库结果表 
     */
    public function getInStockByPo($fields='*',$po='')
    {
    	if( !$po ) return null;
    	$obj = self::find()
	    	->select($fields)
	    	->from(self::tableName())
	    	->where(['=', 'pur_number', $po]);
    
    	/* echo $obj->createCommand()->getSql();
    	 exit; */
    	$data = $obj->asArray()->all();
    	return $data;
    }


}
