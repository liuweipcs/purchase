<?php
namespace app\synchcloud\models;
use Yii;
use app\config\Vhelper;
use app\services\CommonServices;
use app\services\SupplierServices;
use yii\behaviors\TimestampBehavior;
class MidPurchaseOrder extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{mid_purchase_order}}';
    }

    public static function getDb()
    {
        return Yii::$app->mid_cloud;
    }
    
    /**
     * @desc 检查数据是否存在
     */
    public function dataExist($fields='*',$purNumber='') {
    	$obj = self::find()
	    	->select($fields)
	    	->where('purchase_no="'.$purNumber.'"');
    	
    	/* echo $obj->createCommand()->getSql();
    	 exit; */
	    	$data = $obj->asArray()->one();
    	//print_r($data);exit;
    
    	return $data;
    }

    /**
     * 批量写入MID采购订单主表
     */
    public function saveRows($rows)
    {
        $success = [];
        foreach($rows as $k => $v) {
            $model = self::find()->where(['purchase_no' => $v['pur_number']])->one();

            if(!empty($model)) {
                $success[] = $v['id'];
                continue;
            } else {
                $model = new self();
            }

            $model->hrms_code = $v['buyer'];               // 员工编号
            $model->purchase_no = $v['pur_number'];        // 采购订单号
            $model->warehouse_code = $v['warehouse_code']; // erp仓库code
            $model->supplier_code = $v['supplier_code'];   // pms供应商code
            $model->purchase_date = !empty($v['created_at']) ? $v['created_at'] : date('Y-m-d H:i:s', time()); // 采购时间，采用PMS的采购单创建时间
            $model->move_status = 0; // 初始状态

            if($v['is_drawback'] == 1) {   // 组织id  101.香港易佰(不退税) 102.深圳易佰(退税)
                $model->org_id = '101';
            } elseif($v['is_drawback'] == 2) {
                $model->org_id = '102';
            }

            if($v->purchaseOrderPayType) {
                $freight = !empty($v->purchaseOrderPayType->freight) ? $v->purchaseOrderPayType->freight : 0;
            } else {
                $freight = 0;
            }
            $model->freight_price = $freight; // 运费
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
     * @desc 获取待更新是 合同类型，组织的采购单
     */
    public function getSyncCompactOrgData($fields='*',$limit=100,$conditions='')
    {
    	$where = ' 1=1 ';
    	if (!empty($conditions)){
    		$where .= $conditions;
    	}
    
    	$obj = self::find()
	    	->select($fields)
	    	->from(self::tableName().' as a')
	    	->where($where)
	    	->andWhere('a.is_compact is null')
	    	->andWhere('a.purchase_date >= "2017-12-01 00:00:00" and a.purchase_date <"2018-02-01 00:00:00"')
	    	->andWhere('purchase_no not like "T-%"')
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
    	$model->is_compact = $rows['is_compact'];  
    	$model->org_id = $rows['org_id']; 
    	$model->is_update = $rows['is_update'];
    	$res = $model->save(false);
    
    	return $res;
    }

}