<?php
namespace app\synchcloud\models;
use app\config\Vhelper;
use Yii;
use yii\helpers\ArrayHelper;

class PurchaseOrder extends \yii\db\ActiveRecord
{  
	const SOURCE_COMPACT = 1; //合同采购
	const SOURCE_ONLINE = 2; //网络采购
	
	const CLOUD_SYNC_STATUS_INIT = 0; //待同步
	const CLOUD_SYNC_STATUS_SUCCESS = 1; //同步成功
	const CLOUD_SYNC_STATUS_FAIL = 2; //同步失败
	
	const CLOUD_SYNCH_ERROR_1 = 1 ; //员工编码为空
	const CLOUD_SYNCH_ERROR_2 = 2 ; //仓库编码为空
	const CLOUD_SYNCH_ERROR_3 = 3 ; //供应商编码为空
	const CLOUD_SYNCH_ERROR_4 = 4 ; //采购日期为空
	const CLOUD_SYNCH_ERROR_5 = 5 ; //采购单明细为空
	const CLOUD_SYNCH_ERROR_6 = 6 ; //采购单sku入库结果异常
	
    public static $avliable_order_status = [
        '3' => '已审批',
        '5' => '部分到货',
        '6' => '全到货',
        '7' => '等待到货',
        '8' => '部分到货等待剩余',
        '9' => '部分到货不等待剩余',
        '99' => '未全部到货',
    ];

    public static function tableName()
    {
        return '{{%purchase_order}}';
    }

    // 关联订单商品表
    public function getPurchaseOrderItems()
    {
        return $this->hasMany(\app\models\PurchaseOrderItems::className(), ['pur_number' => 'pur_number']);
    }

    // 关联运费表
    public function getPurchaseOrderPayType()
    {
        return $this->hasOne(\app\models\PurchaseOrderPayType::className(), ['pur_number' => 'pur_number']);
    }

    // 关联供应商
    public function getSupplier()
    {
        return $this->hasOne(Supplier::className(), ['supplier_code' => 'supplier_code']);
    }

    // 关联快递商
    public function getLogistics()
    {
        return $this->hasOne(LogisticsCarrier::className(),['id' => 'carrier']);
    }
    
 
    // 通过采购员名称获取员工编码
    public function getUserNumber($username)
    {
        if($username) {
            $user_number = \app\models\User::find()->select('user_number')->where(['username' => $username])->scalar();
            return $user_number ? $user_number : '';
        } else {
            return '';
        }
    }
    
    // 批量查询采购员员工编码
    public function getStaffCodeByUsers($username = [])
    {
    	if( $username && is_array($username) ) {
    		$data = \app\models\User::find()->select('user_number,username')->where(['in','username',$username])->asArray()->all();
    		return $data;
    	} else {
    		return null;
    	}
    }
    
    // 通过人员ID名称获取员工编码
    public function getUserNumberByIds($ids=[])
    {
    	if(!is_array($ids)) return null;
    	if($ids) {
    		$data = \app\models\User::find()->select('user_number,id')->where(['in','id',$ids])->asArray()->all();
    		return $data;
    	} else {
    		return null;
    	}
    }

    /**
     * 获取同步到MID的采购订单数据（非合同单）
     * 只同步已经付过款的订单
     */
    public function getData($limit = 10)
    {
        $status = array_keys(self::$avliable_order_status);
        $fields = [
            'a.id',
            'a.buyer',
            'a.pur_number',
            'a.warehouse_code',
            'a.supplier_code',
            'a.created_at',
            'a.is_drawback',
            'b.pay_status',
            'b.pay_price'
        ];
        $orders = self::find()
            ->select($fields)
            ->from('pur_purchase_order as a')
            ->leftJoin('pur_purchase_order_pay as b', 'a.pur_number = b.pur_number')
            ->where(['a.is_push_to_k3cloud' => 0])
            ->andWhere(['>', 'a.source', 1])
            ->andWhere(['in', 'a.purchas_status', $status])
            ->andWhere(['in', 'b.pay_status', [5, 6]])
            ->groupBy('a.pur_number')
            ->limit($limit)
            ->all();

        if(empty($orders)) {
            return [];
        }
        // 去重
        $pos = ArrayHelper::getColumn($orders, 'pur_number');
        $has = MidPurchaseOrder::find()->select('purchase_no')->where(['in', 'purchase_no', $pos])->column();

        $validList = []; // 可用的数据
        $mistakeList = []; // 有错误的数据
        $syncedList = []; // 已经同步过的数据

        foreach($orders as $order) {
            if($has && in_array($order['pur_number'], $has)) {
                $syncedList[] = $order['id'];
                continue;
            }
            $number = $this->getUserNumber($order['buyer']);
            if($order['pur_number'] == '' || $order['warehouse_code'] == '' || $order['supplier_code'] == '' || $number == '') {
                $mistakeList[] = $order['id'];
                continue;
            }
            $order['buyer'] = $number;
            $validList[] = $order;
            $pos[] = $order['pur_number'];
        }
        return [
            'valid' => $validList,
            'mistake' => $mistakeList,
            'synced' => $syncedList
        ];
    }

    /**
     * 获取同步到MID的采购订单数据（合同单）
     * 只同步已经付过款的订单
     */
    public function getData2($limit = 10)
    {
        $status = array_keys(self::$avliable_order_status);
        $fields = [
            'a.id',
            'a.buyer',
            'a.pur_number',
            'a.warehouse_code',
            'a.supplier_code',
            'a.created_at',
            'a.is_drawback',
            'b.pay_price',
            'c.compact_number'
        ];
        $orders = self::find()
            ->select($fields)
            ->from('pur_purchase_order as a')
            ->leftJoin('pur_purchase_compact_items as c', 'a.pur_number = c.pur_number')
            ->leftJoin('pur_purchase_order_pay as b', 'c.compact_number = b.pur_number')
            ->where(['a.is_push_to_k3cloud' => 0, 'a.source' => 1])
            ->andWhere(['in', 'a.purchas_status', $status])
            ->andWhere(['in', 'b.pay_status', [5, 6]])
            ->groupBy('a.pur_number')
            ->limit($limit)
            ->all();

        if(empty($orders)) {
            return [];
        }
        // 去重
        $pos = ArrayHelper::getColumn($orders, 'pur_number');
        $has = MidPurchaseOrder::find()->select('purchase_no')->where(['in', 'purchase_no', $pos])->column();

        $validList = []; // 可用的数据
        $mistakeList = []; // 有错误的数据
        $syncedList = []; // 已经同步过的数据

        foreach($orders as $order) {
            if($has && in_array($order['pur_number'], $has)) {
                $syncedList[] = $order['id'];
                continue;
            }
            $number = $this->getUserNumber($order['buyer']);
            if($order['pur_number'] == '' || $order['warehouse_code'] == '' || $order['supplier_code'] == '' || $number == '') {
                $mistakeList[] = $order['id'];
                continue;
            }
            $order['buyer'] = $number;
            $validList[] = $order;
        }
        return [
            'valid' => $validList,
            'mistake' => $mistakeList,
            'synced' => $syncedList
        ];
    }

    /**
     * 根据订单模型列表依次获取订单的sku信息
     */
    public function GetPurcahseOrderItems($orders)
    {
        if(empty($orders)) return null;
        $orderItems = [];
        foreach($orders as $mod) {
            $items = $mod->purchaseOrderItems;
            if(!empty($items))
                $orderItems = array_merge($orderItems, ArrayHelper::toArray($items));
        }
        return $orderItems;
    }
    
    /**
     * @desc 查询供应商 是否存在合同采购单 
     * @return unknown
     */
    public function checkPoTypeOfCompact($fields='*',$supplierCode='')
    {
    	$obj = self::find()
	    	->select($fields)
	    	->from(self::tableName())
	    	->where('supplier_code="'.$supplierCode.'"')
	    	->andWhere('source='.self::SOURCE_COMPACT);
    
    	/* echo $obj->createCommand()->getSql();
    	 exit; */
    	$data = $obj->asArray()->one();
    	return $data;
    }
    
    /**
     * @desc 获取待同步cloud的采购订单-非合同
     */
    public function getSyncPoCloudData($fields='*',$limit=100,$conditions='')
    {
    	$status = array_keys(self::$avliable_order_status);
    	
    	$where = 'a.is_push_to_k3cloud = '.self::CLOUD_SYNC_STATUS_INIT;
    	if (!empty($conditions)){
    		$where .= $conditions;
    	}
    
    	$obj = self::find()
	    	->select($fields)
	    	->from(self::tableName().' as a')
	    	->leftJoin('pur_purchase_order_pay as b', 'a.pur_number = b.pur_number')
	    	->leftJoin('pur_purchase_compact_items_restore as c', 'c.pur_number = a.pur_number')
	    	->leftJoin('pur_purchase_compact_items as i', 'i.pur_number = a.pur_number')
	    	->where($where)
	    	//->andWhere('a.source>1')
	    	->andWhere('a.purchas_status in('.implode(',', $status).')')
	    	->andWhere('b.pay_status in(5,6)') //已付款的
	    	->andWhere('c.id is null')  //不存在虚拟合同的
	    	->andWhere('i.id is null')  //不存在真实合同的
	    	->groupBy('a.pur_number')
	    	->limit($limit);
    
    	/* echo $obj->createCommand()->getSql();
    	 exit; */
    	$data = $obj->all();
    	return $data;
    }
    
    /**
     * @desc 获取待同步cloud的采购订单-合同
     */
    public function getSyncPoHtCloudData($fields='*',$limit=100,$conditions='')
    {
    	$status = array_keys(self::$avliable_order_status);
    	
    	$where = 'a.is_push_to_k3cloud = '.self::CLOUD_SYNC_STATUS_INIT;
    	if (!empty($conditions)){
    		$where .= $conditions;
    	}
    
    	$obj = self::find()
	    	->select($fields)
	    	->from(self::tableName().' as a')
	    	->leftJoin('pur_purchase_compact_items as i', 'a.pur_number = i.pur_number')
	    	->leftJoin('pur_purchase_compact_items_restore as c', 'a.pur_number = c.pur_number')
	    	->leftJoin('pur_purchase_order_pay as b', 'i.compact_number = b.pur_number')
	    	->leftJoin('pur_purchase_order_pay_restore as e', 'c.compact_number = e.pur_number')
	    	->where($where)
	    	//->andWhere('a.source = 1')
	    	->andWhere('a.purchas_status in('.implode(',', $status).')')
	    	->andWhere('b.pay_status in(5,6) or e.pay_status in(5,6)') //已付款的
	    	->andWhere('c.id is not null or i.id is not null')  //存在虚拟合同 或 存在真实合同的
	    	->groupBy('a.pur_number')
	    	->limit($limit);
    
    	/* echo $obj->createCommand()->getSql();
    	 exit; */
    	$data = $obj->all();
    	return $data;
    }
    
    /**
     * 更新cloud系统同步状态 
     */
    public function updatePoSynchCloud($ids=array(),$status,$date,$error = 0){
    	return self::getDb()->createCommand()->update(
    			self::tableName(),array('is_push_to_k3cloud' => $status,'cloud_synch_error'=>$error,'cloud_synch_time'=>$date),
    			['in','id',$ids])->execute();
    }
    
    /**
     * @desc 查询采购付款申请单信息
     * @return unknown
     */
    public function getPayOrderInfo($fields='*',$purNumber='')
    {
    	$obj = self::find()
	    	->select($fields)
	    	->from('pur_purchase_order_pay')
	    	->where('pur_number="'.$purNumber.'"');
    
    	/* echo $obj->createCommand()->getSql();
    	 exit; */
    	$data = $obj->asArray()->one();
    	return $data;
    }
    
    /**
     * @desc 查询采购付款申请单信息 根据供应商
     * @return unknown
     */
    public function getPayOrderInfoBySupplier($fields='*',$supplierCode='')
    {
    	$obj = self::find()
		    	->select($fields)
		    	->from('pur_purchase_order_pay')
		    	->where('supplier_code="'.$supplierCode.'"')
    			->andWhere('pay_type in(3,5)');
    
    	/* echo $obj->createCommand()->getSql();
    	 exit; */
    	$data = $obj->asArray()->one();
    	return $data;
    }
    
    /**
     * @desc 根据采购单查询
     * @param string $fields
     * @param unknown $purNumbers
     */
    public function getPurchaseByPo( $fields='*',$purNumbers = [] ){
    	if(!$purNumbers) return null;
    	$obj = self::find()
		    	->select($fields)
		    	->from('pur_purchase_order')
		    	->andWhere(['in','pur_number',$purNumbers]);
    	
    	/* echo $obj->createCommand()->getSql();
    	 exit; */
    	$data = $obj->asArray()->all();
    	return $data;
    }
    
}
