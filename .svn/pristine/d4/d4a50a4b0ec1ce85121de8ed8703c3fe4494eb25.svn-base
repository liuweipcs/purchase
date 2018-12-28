<?php
namespace app\synchcloud\models;
use Yii;
use app\config\Vhelper;
class MidPurchaseReturns extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{mid_purchase_returns}}';
    }

    public static function getDb()
    {
        return Yii::$app->mid_cloud;
    }

    /**
     * 批量写入MID采购订单退料表
     */
    public function saveRows($rows)
    {
        $success = [];
        foreach($rows as $k => $v) {
            $model = new self();
            $model->bill_no        = $v['pur_number'].'-'.$v['id'];
            $model->sku            = trim($v['sku']);
            $model->provider_id    = $v['supplier_code'];
            $model->dates          = !empty($v['created_at']) ? $v['created_at'] : date('Y-m-d H:i:s', time());
            $model->warehouse_code = $v['warehouse_code'];
            $model->purchase_id    = $v['creator'];
            $model->price          = $v['price'];
            $model->purchase_no    = $v['pur_number'];
            $model->retreating_qty = $v['refund_qty'];
            $model->note           = $v['note'];
            $model->move_status    = 0;

            if($v['is_drawback'] == 1) {   // 组织id  101.香港易佰(不退税) 102.深圳易佰(退税)
                $model->org_id = '101';
            } elseif($v['is_drawback'] == 2) {
                $model->org_id = '102';
            }

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
    public function dataExist($fields='*',$purNumber='',$billNo = '') {
    	$obj = self::find()
	    	->select($fields)
	    	->where('purchase_no="'.$purNumber.'"')
	    	->andWhere('bill_no="'.$billNo.'"');
	    	$data = $obj->asArray()->one();
    	//print_r($data);exit;
    
    	return $data;
    }
    
    public function batchInsertData($key=[],$values=[]){
    	$ret = self::getDb()->createCommand()->batchInsert(self::tableName(),$key, $values)->execute();
    	return $ret;
    }

}