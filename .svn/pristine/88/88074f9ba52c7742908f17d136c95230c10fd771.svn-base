<?php
namespace app\synchcloud\models;
use Yii;
class MidPurchaseInDetail extends \yii\db\ActiveRecord
{ 
    public static function tableName()
    {
        return '{{mid_purchase_in_detail}}';
    }

    public static function getDb()
    {
        return Yii::$app->mid_cloud;
    }

    // 获取员工编号为null的数据
    public function getData($limit = 10)
    {
        $models = self::find()->where(['hrms_code' => ''])->limit($limit)->all();
        return $models;
    }

    /**
     * 批量写入MID采购订单主表
     */
    public function saveRows($rows=[])
    {
    	if(!$rows) return false;
    	$model = self::findOne($rows['id']);
    	if(empty($model)) {
    		return false;
    	}
    	$model->ticketed_point = $rows['ticketed_point'];   //开票点
    	$model->base_price = $rows['base_price'];           //原价(不含税)
    	$model->price = $rows['price'];           //采购价
    	$res = $model->save(false);
    	
        return $res;
    }
}