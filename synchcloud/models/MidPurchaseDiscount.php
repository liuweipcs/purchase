<?php
namespace app\synchcloud\models;
use Yii;
use app\config\Vhelper;
class MidPurchaseDiscount extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{mid_purchase_discount}}';
    }

    public static function getDb()
    {
        return Yii::$app->mid_cloud;
    }

    /**
     * 批量写入MID采购折扣单
     */
    public function saveRows($rows)
    {
        $success = [];
        foreach($rows as $k => $v) {
            $model = new self();
            $model->bill_no     = $v['pur_number'].'-'.$v['id']; // 单据编号
            $model->purchase_no = $v['pur_number'];      // 采购订单号
            $model->provider_id = $v['supplier_code'];   // 供应商收款单位(来往单位)
            $model->purchase_id = $v['buyer'];           // 员工编码
            $model->discount    = !empty($v['discount']) ? $v['discount'] : 0; // 折扣金额
            $model->note        = $v['note'];            // 备注
            $model->dates       = !empty($v['created_at']) ? $v['create_at'] : time('Y-m-d H:i:s', time()); // 业务日期（中间表不允许有为空的日期）
            $model->move_status = 0;                     // 0.初始状态 1.已同步待归档 2.异常
            if($v['is_drawback'] == 1) {   // 组织id  101.香港易佰(不退税) 102.深圳易佰(退税)
                $model->org_id = '101';
            } elseif($v['is_drawback'] == 2) {
                $model->org_id = '102';
            }
            $res = $model->save(false);
            if($res) {
                $success[] = $v['id'];
            }
        }
        return $success;
    }

    /**
     * 批量写入MID采购折扣单（差值）
     */
    public function saveRows2($rows)
    {
        $success = [];
        foreach($rows as $k => $v) {
            $model = new self();
            $shipment = self::find()
                ->select('sum(discount)')
                ->where(['purchase_no' => $v['pur_number']])
                ->scalar();
            if($shipment) {
                $discount = bcsub($v['discount'], $shipment, 3);
            } else {
                $discount = $v['discount'];
            }
            if($discount == 0) {
                $success[] = $v['id'];
            } else {
                $t = time();
                $fix = substr($t, -3, 3);
                $model->bill_no     = $v['pur_number'].'-'.$v['id'].'-'.$fix; // 单据编号
                $model->purchase_id = $v['buyer'];
                $model->provider_id = $v['supplier_code'];   // 供应商收款单位(来往单位)
                $model->purchase_no = $v['pur_number'];      // 采购订单号
                $model->dates       = !empty($v['created_at']) ? $v['created_at'] : date('Y-m-d H:i:s', time());      // 业务日期
                $model->discount    = $discount; // 折扣金额
                $model->purchase_no = $v['pur_number'];      // 采购订单号
                $model->move_status = 0;                     // 0.初始状态 1.已同步待归档 2.异常
                $model->note        = "优惠额修改为{$v['discount']}";
                if($v['is_drawback'] == 1) {  // 组织id  101.香港易佰(不退税) 102.深圳易佰(退税)
                    $model->org_id = '101';
                } elseif($v['is_drawback'] == 2) {
                    $model->org_id = '102';
                }
                $res = $model->save(false);
                if($res) {
                    $success[] = $v['id'];
                }
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
	    	->where('purchase_no="'.$purNumber.'"');
	    
	    if( $billNo ){
	    	$obj->andWhere('bill_no="'.$billNo.'"');
	    }
    	$data = $obj->asArray()->one();
    	//print_r($data);exit;
    
    	return $data;
    }
    
    public function batchInsertData($key=[],$values=[]){
    	$ret = self::getDb()->createCommand()->batchInsert(self::tableName(),$key, $values)->execute();
    	return $ret;
    }
    
    /**
     * @desc 查询采购订单已经同步折扣 
     */
    public function getSynchedDiscountByPo($purNumber='') {
    	$obj = self::find()
	    	->select('sum(discount) as sum_discount')
	    	->where('purchase_no="'.$purNumber.'"');
    	
    	$data = $obj->asArray()->one();
    	//print_r($data);exit;
    
    	return $data;
    }
    
    /**
     * @desc 根据采购单号删除记录
     */
    public function delByPo( $po='' ){
    	if( !$po ) return false;
    	$rlt = self::deleteAll(['purchase_no'=>$po]);
    	return $rlt;
    }

}