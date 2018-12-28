<?php
namespace app\synchcloud\models;
use Yii;
use app\config\Vhelper;
use app\services\CommonServices;
use app\services\SupplierServices;
use yii\behaviors\TimestampBehavior;
class ExcPurchaseOrderDetail extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{mid_purchase_order_detail_exc}}';
    }

    public static function getDb()
    {
        return Yii::$app->mid_cloud;
    }

    public function batchInsertData($key=[],$values=[]){
    	$ret = self::getDb()->createCommand()->batchInsert(self::tableName(),$key, $values)->execute();
    	return $ret;
    }
    
}