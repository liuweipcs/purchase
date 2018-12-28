<?php
namespace app\synchcloud\models;
use Yii;
use yii\helpers\ArrayHelper;

class PurchaseOrderRefundQuantity extends \yii\db\ActiveRecord
{
	const CLOUD_SYNC_STATUS_INIT = 0; //待同步
	const CLOUD_SYNC_STATUS_SUCCESS = 1; //同步成功
	const CLOUD_SYNC_STATUS_FAIL = 2; //同步失败
	
	const CLOUD_SYNCH_ERROR_1 = 1 ; //采购员工编码为空
	const CLOUD_SYNCH_ERROR_2 = 2 ; //采购单不存在
	const CLOUD_SYNCH_ERROR_3 = 3 ; //供应商不存在
	const CLOUD_SYNCH_ERROR_4 = 4 ; //仓库编码不存在

    public $purchase_id;

    public static function tableName()
    {
        return '{{%purchase_order_refund_quantity}}';
    }

    // 关联采购单
    public function getPurchaseOrder()
    {
        return $this->hasOne(PurchaseOrder::className(), ['pur_number' => 'pur_number']);
    }

    // 关联退款单
    public function getPurchaseReceipt()
    {
        return $this->hasOne(\app\models\PurchaseOrderReceipt::className(), ['pur_number' => 'pur_number', 'requisition_number' => 'requisition_number']);
    }

    // 通过采购员名称获取员工编码
    public function getUserNumberById($id)
    {
        if($id) {
            $user_number = \app\models\User::find()->select('user_number')->where(['id' => $id])->scalar();
            return $user_number ? $user_number : '';
        } else {
            return '';
        }
    }

    public function getUserNumberByName($username)
    {
        if($username) {
            $user_number = \app\models\User::find()->select('user_number')->where(['username' => $username])->scalar();
            return $user_number ? $user_number : '';
        } else {
            return '';
        }
    }

    /**
     * 获取同步到MID的采购退料数据
     */
    public function getData($limit = 10)
    {
        $rows = self::find()
            ->where(['is_push_to_k3cloud' => 0])
            ->limit($limit)
            ->all();

        $validList = [];
        $mistakeList = [];

        foreach($rows as $row) {
            $order = $row->purchaseOrder;
            $receipt = $row->purchaseReceipt;

            $n = $this->getUserNumberByName($row['creator']);
            if($n == '') {
                $mistakeList[] = $row->id;
                continue;
            }

            $row = ArrayHelper::toArray($row);
            $row['creator'] = $n;

            $row['is_drawback'] = $order->is_drawback;

            if(!empty($order)) {
                $row['supplier_code'] = $order->supplier_code;
            } else {
                $row['supplier_code'] = '';
            }

            if(!empty($order)) {
                $row['warehouse_code'] = $order->warehouse_code;
            } else {
                $row['warehouse_code'] = '';
            }
            if($row['supplier_code'] == '' || $row['warehouse_code'] == '') {
                $mistakeList[] = $row->id;
                continue;
            }

            if(!empty($receipt)) {
                $row['note'] = $receipt->review_notice;
            } else {
                $row['note'] = '';
            }
            $validList[] = $row;
        }
        return [
            'valid' => $validList,
            'mistake' => $mistakeList
        ];
    }

    
    public function getSyncReturnCloudData($fields='*',$limit=100,$conditions='')
    {
    	$where = 'a.is_push_to_k3cloud = '.self::CLOUD_SYNC_STATUS_INIT;
    	if (!empty($conditions)){
    		$where .= $conditions;
    	}
    
    	$obj = self::find()
	    	->select($fields)
	    	->from(self::tableName().' as a')
	    	->where($where)
	    	->limit($limit);
    
    	/* echo $obj->createCommand()->getSql();
    	 exit; */
    	$data = $obj->all();
    	return $data;
    }
    
    /**
     * 更新cloud系统同步状态
     */
    public function updateReturnSynchCloud($ids=array(),$status,$date,$error = 0){
    	return self::getDb()->createCommand()->update(
    			self::tableName(),array('is_push_to_k3cloud' => $status,'cloud_synch_error'=>$error,'cloud_synch_time'=>$date),
    			['in','id',$ids])->execute();
    }


}
