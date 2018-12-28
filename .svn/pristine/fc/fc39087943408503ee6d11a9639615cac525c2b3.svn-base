<?php
namespace app\synchcloud\models;
use Yii;
use app\config\Vhelper;
class MidPurchasePayRelation extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{purchase_pay_relation}}';
    }

    public static function getDb()
    {
        return Yii::$app->mid_cloud;
    }

    /**
     * 批量写入MID采购订单主表
     */
    public function saveRows($rows)
    {
        $success = [];
        foreach($rows as $k => $v) {
            $model = new self();
            $model->purchase_contract_no = $v['compact_number'];
            $model->purchase_order_no = $v['pur_number'];
            $res = $model->save();
            if($res) {
                $success[] = $v['id'];
            }
        }
        return $success;
    }
    
    /**
     * @desc 检查数据是否存在
     */
    public function dataExist($fields='*',$purNumber='',$compactNo = '') {
    	$obj = self::find()
	    	->select($fields)
	    	->where('purchase_order_no="'.$purNumber.'"')
	    	->andWhere('purchase_contract_no="'.$compactNo.'"');
	    	$data = $obj->asArray()->one();
    	//print_r($data);exit;
    
    	return $data;
    }
    
    public function batchInsertData($key=[],$values=[]){
    	$ret = self::getDb()->createCommand()->batchInsert(self::tableName(),$key, $values)->execute();
    	return $ret;
    }

}