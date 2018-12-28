<?php
namespace app\synchcloud\models;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
class PurchaseOrderPayRestore extends \yii\db\ActiveRecord
{

    public $purchase_id;
    
    const CLOUD_SYNC_STATUS_INIT = 0; //待同步
    const CLOUD_SYNC_STATUS_SUCCESS = 1; //同步成功
    const CLOUD_SYNC_STATUS_FAIL = 2; //同步失败
    
    const CLOUD_SYNCH_ERROR_1 = 1 ; //付款金额为0
    const CLOUD_SYNCH_ERROR_2 = 2 ; //付款申请人为空
    const CLOUD_SYNCH_ERROR_3 = 3 ; //付款人为空
    const CLOUD_SYNCH_ERROR_4 = 4 ; //付款时间为空
    const CLOUD_SYNCH_ERROR_5 = 5 ; //采购合同不存在
    const CLOUD_SYNCH_ERROR_6 = 6 ; //采购单不存在
    const CLOUD_SYNCH_ERROR_7 = 7 ; //付款账号不存在

    public static function tableName()
    {
        return '{{%purchase_order_pay_restore}}';
    }

    // 关联采购单
    public function getPurchaseOrder()
    {
        return $this->hasOne(PurchaseOrder::className(), ['pur_number' => 'pur_number']);
    }

    public function getBankAccount()
    {
        return $this->hasOne(\app\models\BankCardManagement::className(), ['id' => 'pay_account']);
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
     * @desc 获取待同步cloud的采购付款流程
     */
    public function getSyncPaymentRestoreCloudData($fields='*',$limit=100,$conditions='')
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
    public function updatePoSynchCloud($ids=array(),$status,$date,$error = 0){
    	return self::getDb()->createCommand()->update(
    			self::tableName(),array('is_push_to_k3cloud' => $status,'cloud_synch_error'=>$error,'cloud_synch_time'=>$date),
    			['in','id',$ids])->execute();
    }
    
    /**
     * @desc 根据财务付款申请临时表里面的pur_number查询合同信息
     */
    public function getCompactRestoreBindData($fields='*',$compactNo='')
    {
    	if( !$compactNo ) return null;
    	$obj = self::find()
	    	->select($fields)
	    	->from('pur_purchase_compact_items_restore')
	    	->where('compact_number="'.$compactNo.'"')
	    	->andWhere('bind=1');
    
    	/* echo $obj->createCommand()->getSql();
    	 exit; */
    	$data = $obj->asArray()->all();
    	return $data;
    }
    
    /**
     * @desc 查询合同绑定信息
     */
    public function getCompactBindData($fields='*',$compactNo='')
    {
    	if( !$compactNo ) return null;
    	$obj = self::find()
	    	->select($fields)
	    	->from('pur_purchase_compact_items')
	    	->where('compact_number="'.$compactNo.'"')
	    	->andWhere('bind=1');
     
    	/* echo $obj->createCommand()->getSql();
    	 exit; */ 
    	$data = $obj->asArray()->all();
    	return $data;
    }
    
    /**
     * @desc 查询合同信息
     */
    public function getCompactData($fields='*',$compactNo='')
    {
    	if( !$compactNo ) return null;
    	$obj = self::find()
	    	->select($fields)
	    	->from('pur_purchase_compact')
	    	->where('compact_number="'.$compactNo.'"');
    
    	/* echo $obj->createCommand()->getSql();
    	 exit; */
    	$data = $obj->asArray()->one();
    	return $data;
    }
    


}
