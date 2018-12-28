<?php
namespace app\synchcloud\models;
use app\config\Vhelper;
use Yii;

class PurchaseOrderPayType extends \yii\db\ActiveRecord
{
	const CLOUD_SYNC_STATUS_INIT = 0; //待同步
	const CLOUD_SYNC_STATUS_SUCCESS = 1; //同步成功
	const CLOUD_SYNC_STATUS_FAIL = 2; //同步失败
	
	const CLOUD_SYNCH_ERROR_1 = 1 ; //采购员工编码为空
	const CLOUD_SYNCH_ERROR_2 = 2 ; //业务日期为空
	
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
        return '{{%purchase_order_pay_type}}';
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

    /**
     * 获取同步到MID的采购订单优惠额数据
     * 采购单已经同步到MID了，优惠额还未同步的，优惠额大于零的
     */
    public function getData($limit = 10)
    {
        $sql = "SELECT 
            b.id,
            b.discount, 
            a.pur_number, 
            a.supplier_code, 
            a.buyer, 
            a.created_at, 
            a.is_drawback, 
            b.note
            FROM pur_purchase_order AS a 
            LEFT JOIN pur_purchase_order_pay_type AS b 
            ON a.pur_number = b.pur_number
            WHERE a.is_push_to_k3cloud = 1 
            AND b.discount_push_to_k3cloud = 0 
            AND b.discount > 0 
            LIMIT {$limit}
            ";
        $query = Yii::$app->db->createCommand($sql);
        $data = $query->queryAll();
        foreach($data as &$v) {
            $v['buyer'] = $this->getUserNumber($v['buyer']);
        }
        return $data;
    }

    /**
     * 获取同步到MID的采购运费数据
     * 采购单已经同步到MID了，运费还未同步的，运费大于零的
     */
    public function getData2($limit = 10)
    {
        $sql = "SELECT 
            b.id,
            b.freight, 
            a.pur_number, 
            a.supplier_code, 
            a.buyer, 
            a.created_at, 
            a.is_drawback, 
            a.is_push_to_k3cloud
            FROM pur_purchase_order AS a 
            LEFT JOIN pur_purchase_order_pay_type AS b 
            ON a.pur_number = b.pur_number
            WHERE a.is_push_to_k3cloud = 1 
            AND b.freight_push_to_k3cloud = 0 
            AND b.freight > 0 
            LIMIT {$limit};
            ";
        $query = Yii::$app->db->createCommand($sql);
        $data = $query->queryAll();
        foreach($data as &$v) {
            $v['buyer'] = $this->getUserNumber($v['buyer']);
        }
        return $data;
    }

    /**
     * 获取同步到MID的采购运费差值（修改了运费以后就同步）
     */
    public function getData3($limit = 10)
    {
        $sql = "SELECT 
            b.id,
            b.freight, 
            a.pur_number, 
            a.supplier_code, 
            a.buyer, 
            a.created_at, 
            a.is_drawback 
            FROM pur_purchase_order AS a 
            LEFT JOIN pur_purchase_order_pay_type AS b 
            ON a.pur_number = b.pur_number
            WHERE b.freight_push_to_k3cloud = 1 
            AND b.is_update_freight = 1 
            AND freight IS NOT NULL 
            LIMIT {$limit};
            ";
        $query = Yii::$app->db->createCommand($sql);
        $data = $query->queryAll();
        foreach($data as &$v) {
            $v['buyer'] = $this->getUserNumber($v['buyer']);
        }
        return $data;
    }

    /**
     * 获取同步到MID的采购优惠差值（修改了优惠以后就同步）
     */
    public function getData4($limit = 10)
    {
        $sql = "SELECT 
            b.id,
            b.discount, 
            a.pur_number, 
            a.supplier_code, 
            a.buyer, 
            a.created_at, 
            a.is_drawback 
            FROM pur_purchase_order AS a 
            LEFT JOIN pur_purchase_order_pay_type AS b 
            ON a.pur_number = b.pur_number
            WHERE b.discount_push_to_k3cloud = 1 
            AND b.is_update_discount = 1 
            AND discount IS NOT NULL 
            LIMIT {$limit};
            ";
        $query = Yii::$app->db->createCommand($sql);
        $data = $query->queryAll();
        foreach($data as &$v) {
            $v['buyer'] = $this->getUserNumber($v['buyer']);
        }
        return $data;
    }
    
    /**
     * @desc 获取待同步cloud的优惠
     */
    public function getSyncDiscountCloudData($fields='*',$limit=100,$conditions='')
    {
    	$where = 'a.is_push_to_k3cloud = '.self::CLOUD_SYNC_STATUS_SUCCESS;
    	if (!empty($conditions)){
    		$where .= $conditions;
    	}
    
    	$obj = self::find()
	    	->select($fields)
	    	->from('pur_purchase_order as a')
	    	->leftJoin(self::tableName().' as b','a.pur_number = b.pur_number')
	    	->where($where)
	    	->andWhere('b.discount_push_to_k3cloud = 0')
	    	->andWhere('b.discount > 0')
	    	->limit($limit);
    
    	/* echo $obj->createCommand()->getSql();
    	 exit; */
    	$data = $obj->asArray()->all();
    	return $data;
    }
    
    /**
     * 更新cloud系统同步状态
     */
    public function updateDiscountSynchCloud($ids=array(),$status,$date,$error = 0){
    	return self::getDb()->createCommand()->update(
    			self::tableName(),array('discount_push_to_k3cloud' => $status,'cloud_discount_synch_error'=>$error,'cloud_discount_synch_time'=>$date),
    			['in','id',$ids])->execute();
    }
    
    /**
     * @desc 获取待同步cloud的已更新的优惠
     */
    public function getSyncDiscountDiffCloudData($fields='*',$limit=100,$conditions='')
    {
    	$where = 'a.is_push_to_k3cloud = '.self::CLOUD_SYNC_STATUS_SUCCESS;
    	if (!empty($conditions)){
    		$where .= $conditions;
    	}
    
    	$obj = self::find()
	    	->select($fields)
	    	->from('pur_purchase_order as a')
	    	->leftJoin(self::tableName().' as b','a.pur_number = b.pur_number')
	    	->where($where)
	    	->andWhere('b.discount_push_to_k3cloud = 1')
	    	->andWhere('b.discount IS NOT NULL')
	    	->andWhere('b.is_update_discount = 1')
	    	->limit($limit);
    
    	/* echo $obj->createCommand()->getSql();
    	 exit; */
    	$data = $obj->asArray()->all();
    	return $data;
    }
    
    /**
     * 更新cloud系统同步状态
     */
    public function updateDiscountDiffSynchCloud($ids=array(),$status,$date,$error = 0){
    	return self::getDb()->createCommand()->update(
    			self::tableName(),array('is_update_discount' => $status,'cloud_discount_synch_error'=>$error,'cloud_discount_synch_time'=>$date),
    			['in','id',$ids])->execute();
    }
    
    
    /**
     * @desc 获取待同步cloud的运费
     */
    public function getSyncFreightCloudData($fields='*',$limit=100,$conditions='')
    {
    	$where = 'a.is_push_to_k3cloud = '.self::CLOUD_SYNC_STATUS_SUCCESS;
    	if (!empty($conditions)){
    		$where .= $conditions;
    	}
    
    	$obj = self::find()
	    	->select($fields)
	    	->from('pur_purchase_order as a')
	    	->leftJoin(self::tableName().' as b','a.pur_number = b.pur_number')
	    	->where($where)
	    	->andWhere('b.freight_push_to_k3cloud = 0')
	    	->andWhere('b.freight > 0')
	    	->limit($limit);
    
    	/* echo $obj->createCommand()->getSql();
    	 exit; */
    	$data = $obj->asArray()->all();
    	return $data;
    }
    
    /**
     * 更新cloud系统同步状态
     */
    public function updateFreightSynchCloud($ids=array(),$status,$date,$error = 0){
    	return self::getDb()->createCommand()->update(
    			self::tableName(),array('freight_push_to_k3cloud' => $status,'cloud_freight_synch_error'=>$error,'cloud_freight_synch_time'=>$date),
    			['in','id',$ids])->execute();
    }
    
    /**
     * @desc 获取待同步cloud的已更新的运费
     */
    public function getSyncFerightDiffCloudData($fields='*',$limit=100,$conditions='')
    {
    	$where = 'a.is_push_to_k3cloud = '.self::CLOUD_SYNC_STATUS_SUCCESS;
    	if (!empty($conditions)){
    		$where .= $conditions;
    	}
    
    	$obj = self::find()
	    	->select($fields)
	    	->from('pur_purchase_order as a')
	    	->leftJoin(self::tableName().' as b','a.pur_number = b.pur_number')
	    	->where($where)
	    	->andWhere('b.freight_push_to_k3cloud = 1')
	    	->andWhere('b.freight IS NOT NULL')
	    	->andWhere('b.is_update_freight = 1')
	    	->limit($limit);
    
    	/* echo $obj->createCommand()->getSql();
    	 exit; */
    	$data = $obj->asArray()->all();
    	return $data;
    }
    
    /**
     * 更新cloud系统同步状态
     */
    public function updateFreightDiffSynchCloud($ids=array(),$status,$date,$error = 0){
    	return self::getDb()->createCommand()->update(
    			self::tableName(),array('is_update_freight' => $status,'cloud_freight_synch_error'=>$error,'cloud_freight_synch_time'=>$date),
    			['in','id',$ids])->execute();
    }

}
