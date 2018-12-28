<?php
namespace app\synchcloud\models;
use Yii;
use app\config\Vhelper;
class MidSupplierBuyer extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%supplier_buyer}}';
    }

    public static function getDb()
    {
        return Yii::$app->cloud_basic;
    }
    
    /**
     * @desc 检查数据是否存在
     */
    public function dataExist($fields='*',$supplierCode='') {
    	$obj = self::find()
    			->select($fields)
    			->where('supplier_code="'.$supplierCode.'"');
    	$data = $obj->asArray()->one();
    	//print_r($data);exit;
    	
    	return $data;
    }
    
    public function batchInsertData($key=[],$values=[]){
    	$ret = self::getDb()->createCommand()->batchInsert(MidSupplierBuyer::tableName(),$key, $values)->execute();
    	return $ret;
    }

    /**
     * 更新数据
     */
    public function updateBuyerSupplierMap($data=[]){
    	if(!$data) return false;
    	return self::getDb()->createCommand()->update(self::tableName(),$data,"supplier_code = '".$data['supplier_code']."'")->execute();
    }

}


