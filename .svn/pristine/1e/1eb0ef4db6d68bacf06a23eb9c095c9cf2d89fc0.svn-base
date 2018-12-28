<?php
namespace app\synchcloud\models;
use Yii;
use app\config\Vhelper;
class ExcBankCode extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%bank_code}}';
    }

    public static function getDb()
    {
        return Yii::$app->exchange;
    }
    
    /**
     * @desc 查询银行code
     */
    public function getBankCodeData($fields='*',$bankCodes = []) {
    	$obj = self::find()
    			->select($fields);
    	
    	if($bankCodes){
    		$obj->where(['in','bank_code',$bankCodes]);
    	}else{
    		return null;
    	}
    	$data = $obj->asArray()->all();
    	
    	return $data;
    }

}


