<?php
namespace app\synchcloud\models;
use Yii;
use app\config\Vhelper;
class ExcCityCode extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%city_code}}';
    }

    public static function getDb()
    {
        return Yii::$app->exchange;
    }
    
    /**
     * @desc 查询城市code
     */
    public function getCityCodeData($fields='*',$cityCodes = []) {
    	$obj = self::find()
    			->select($fields);
    	
    	if($cityCodes){
    		$obj->where(['in','city_code',$cityCodes]);
    	}else{
    		return null;
    	}
    	$data = $obj->asArray()->all();
    	
    	return $data;
    }

}


