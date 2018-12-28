<?php
namespace app\synchcloud\models;
use Yii;
use app\config\Vhelper;
class MidSupplier extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%supplier}}';
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

    public static function saveRows($rows)
    {
        $success = [];
        foreach($rows as $k => $v) {

            $model = self::find()->where(['supplier_code' => $v['supplier_code']])->one();
            if(!empty($model)) {
                $success[] = $v['id'];
                continue;
            } else {
                $model = new self();
            }

            $model->supplier_code = $v['supplier_code'];
            $model->supplier_name = $v['supplier_name'];
            $model->short_name = $v['short_name'];
            $model->supplier_settlement_type = $v['supplier_settlement_type'];
            $model->supplier_type = $v['supplier_type'];
            $model->credit_code = $v['credit_code'];
            $model->supplier_bankname = $v['supplier_bankname'];
            $model->supplier_holder = $v['supplier_holder'];
            $model->supplier_account = $v['supplier_account'];
            $model->add_time = $v['add_time'];
            $model->update_time = $v['update_time'];
            $res = $model->save();
            if($res) {
                $success[] = $v['id'];
            }
        }
        return $success;
    }
    
    public function batchInsertData($key=[],$values=[]){
    	$ret = self::getDb()->createCommand()->batchInsert(self::tableName(),$key, $values)->execute();
    	return $ret;
    }
    
    /**
     * 更新数据
     */
    public function updateSupplierCloud($data=[]){
    	if(!$data) return false;
    	return self::getDb()->createCommand()->update(self::tableName(),$data,"supplier_code = '".$data['supplier_code']."'")->execute();
    }

}


