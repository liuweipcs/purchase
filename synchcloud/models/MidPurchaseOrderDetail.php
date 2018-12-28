<?php
namespace app\synchcloud\models;
use Yii;
use app\config\Vhelper;
use app\services\CommonServices;
use app\services\SupplierServices;
use yii\behaviors\TimestampBehavior;
class MidPurchaseOrderDetail extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{mid_purchase_order_detail}}';
    }

    public static function getDb()
    {
        return Yii::$app->mid_cloud;
    }

    // 批量写入MID采购订单明细表
    public function saveRows($rows)
    {
        $success = [];
        foreach($rows as $k => $v) {
            $model = self::find()->where(['purchase_no' => $v['pur_number'], 'sku' => $v['sku']])->one();

            if(!empty($model)) {
                $success[] = $v['id'];
                continue;
            } else {
                $model = new self();
            }

            $model->purchase_no = $v['pur_number'];
            $model->sku = trim($v['sku']);
            $model->price = $v['price'];
            $model->qty = $v['ctq'];
            $model->is_donation = $v['price'] > 0 ? 0 : 1;
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
     * @desc 获取待更新开票点的采购单据
     */
    public function getSyncPoTaxData($fields='*',$limit=100,$conditions='')
    {
    	$where = ' 1=1 ';
    	if (!empty($conditions)){
    		$where .= $conditions;
    	}
    
    	$obj = self::find()
	    	->select($fields)
	    	->from(self::tableName().' as a')
	    	->leftJoin(MidPurchaseOrder::tableName().' as b', 'a.purchase_no = b.purchase_no')
	    	->where($where)
	    	->andWhere('a.ticketed_point is null')
	    	->andWhere('b.purchase_date >= "2018-01-01 00:00:00" and b.purchase_date <"2018-02-01 00:00:00"')
	    	->limit($limit);
    
    	/* echo $obj->createCommand()->getSql();
    	 exit; */
    	$data = $obj->asArray()->all();
    	return $data;
    }
    
    /**
     * 批量写入MID采购订单主表
     */
    public function updateRows($rows=[])
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