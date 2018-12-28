<?php
namespace app\synchcloud\models;
use Yii;
use app\config\Vhelper;
class ExcSupplierBank extends \yii\db\ActiveRecord
{   
    public static function tableName()
    {
        return '{{%bank_account_exc}}';
    }

    public static function getDb()
    {
        return Yii::$app->cloud_basic;
    }
    
    /**
     * @desc 检查数据是否存在
     */
    public function dataExist($fields='*',$supplierCode='',$bankAccount='') {
    	$obj = self::find()
    			->select($fields)
    			->where('bank_account="'.$bankAccount.'"')
    			->andWhere('supplier_code="'.$supplierCode.'"');
    	$data = $obj->asArray()->one();
    	//print_r($data);exit;
    	
    	return $data;
    }
    
    public function batchInsertData($key=[],$values=[]){
    	$ret = self::getDb()->createCommand()->batchInsert(self::tableName(),$key, $values)->execute();
    	return $ret;
    }

    /**
     * 更新数据
     */
    public function updateSupplierBankExc($data=[]){
    	if(!$data) return false;
    	return self::getDb()->createCommand()->update(self::tableName(),$data,"supplier_code = '".$data['supplier_code']."' and bank_account='".$data['bank_account']."'")->execute();
    }
    
    /**
     * 停用银行信息
     */
    public function stopSupplierBankExc($data=[]){
    	if(!$data) return false;
    	return self::getDb()->createCommand()->update(self::tableName(),$data,"supplier_code = '".$data['supplier_code']."'")->execute();
    }

}


