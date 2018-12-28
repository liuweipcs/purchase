<?php
namespace app\synchcloud\models;
use Yii;
use app\config\Vhelper;
use app\services\CommonServices;
use app\services\SupplierServices;
use yii\behaviors\TimestampBehavior;
class ExcPurchaseOrder extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{mid_purchase_order_exc}}';
    }

    public static function getDb()
    {
        return Yii::$app->mid_cloud;
    }
    
    /**
     * @desc 检查数据是否存在
     */
    public function dataExist($fields='*',$purNumber='') {
    	$obj = self::find()
	    	->select($fields)
	    	->where('purchase_no="'.$purNumber.'"');
    	
    	/* echo $obj->createCommand()->getSql();
    	 exit; */
	    	$data = $obj->asArray()->one();
    	//print_r($data);exit;
    
    	return $data;
    }
    
    public function batchInsertData($key=[],$values=[]){
    	$ret = self::getDb()->createCommand()->batchInsert(self::tableName(),$key, $values)->execute();
    	return $ret;
    }

}