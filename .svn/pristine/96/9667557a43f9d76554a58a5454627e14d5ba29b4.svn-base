<?php
namespace app\synchcloud\models;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
class PurchaseOrderPay extends \yii\db\ActiveRecord
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
    const CLOUD_SYNCH_ERROR_8 = 8 ; //与通途采购单存在合并付款，拦截不处理
    const CLOUD_SYNCH_ERROR_9 = 9 ; //供应商不存在
    const CLOUD_SYNCH_ERROR_10 = 10 ; //采购单创建时间为空
    const CLOUD_SYNCH_ERROR_11 = 11 ; //采购单明细不存在
    const CLOUD_SYNCH_ERROR_12 = 12 ; //采购单非合同单

    public static function tableName()
    {
        return '{{%purchase_order_pay}}';
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

    public function getPurchaseCompact()
    {
        return $this->hasOne(\app\models\PurchaseCompact::className(), ['compact_number' => 'pur_number']);
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
     * 获取同步到MID的采购订单付款数据
     */
    public function getData($limit = 10)
    {
        $payments = self::find()
            ->where(['is_push_to_k3cloud' => 0])
            ->limit($limit)
            ->all();

        $validList = [];
        $mistakeList = [];

        foreach($payments as $pay) {
            $orderPay = $pay->purchaseOrderPay;
            if($orderPay) {
                $n1 = $this->getUserNumberById($orderPay['applicant']);
            } else {
                $n1 = '';
            }
            if($n1 == '') {
                $mistakeList[] = $pay->id;
                continue;
            }
            $pur_number = $pay['pur_number'];
            if(preg_match('/HT/', $pur_number)) {
                $cmp = $pay->purchaseCompact;
                if(empty($cmp)) {
                    continue;
                }
                $is_drawback = $cmp->is_drawback ? $cmp->is_drawback : 1;
            } else {
                $order = $pay->purchaseOrder;
                if(empty($order)) {
                    continue;
                }
                $is_drawback = $order->is_drawback ? $order->is_drawback : 1;
            }
            $bank = $pay->bankAccount;
            if(!empty($bank)) {
                $k3_bank_account = $bank->k3_bank_account ? $bank->k3_bank_account : '1002.999.999';
            } else {
                $k3_bank_account = '1002.999.999';
            }
            $row = \yii\helpers\ArrayHelper::toArray($pay);
            $row['is_drawback'] = $is_drawback;
            $row['purchase_id'] = $n1;
            $row['our_account_abbreviation'] = $k3_bank_account;
            $n2 = $this->getUserNumberById($row['create_id']);
            if($n2 == '') {
                $mistakeList[] = $pay->id;
                continue;
            }
            $row['create_id'] = $n2;
            $validList[] = $row;
        }
        return [
            'valid' => $validList,
            'mistake' => $mistakeList
        ];
    }
    
    /**
     * @desc 获取待同步cloud的采购付款流程 
     */
    public function getSyncPaymentCloudData($fields='*',$limit=100,$conditions='')
    {
    	$where = 'a.is_push_to_k3cloud = '.self::CLOUD_SYNC_STATUS_INIT;
    	if (!empty($conditions)){
    		$where .= $conditions;
    	}
    
    	$obj = self::find()
	    	->select($fields)
	    	->from(self::tableName().' as a')
	    	->leftJoin('pur_purchase_order_pay_restore as i', 'a.pur_number = i.pur_number')
	    	->leftJoin('pur_purchase_compact_items_restore as c', 'a.pur_number = c.pur_number')
	    	->leftJoin('pur_purchase_compact_items_restore as d', 'a.pur_number = d.compact_number')
	    	->where($where)
	    	->andWhere('i.id is null and c.id is null and d.id is null')  //存在虚拟合同 或 存在真实合同的
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
     * @desc 获取待同步汇总系统的 单据
     */
    public function getSyncPoHtExcData($fields='*',$limit=100,$conditions='')
    {
    	$where = 'a.is_push_to_exc = '.self::CLOUD_SYNC_STATUS_INIT;
    	if (!empty($conditions)){
    		$where .= $conditions;
    	}
    
    	$obj = self::find()
    		->select($fields)
	    	->from(self::tableName().' as a')
	    	->where($where)
	    	->andWhere('a.pay_status in(5,6)') //已付款的，部分付款的
	    	//->andWhere('a.pay_type in(3)') //银行卡转账
	    	->andWhere('a.payer_time>="2018-12-01"')
	    	->groupBy('a.pur_number')
	    	->limit($limit);
    
    	/* echo $obj->createCommand()->getSql();
    	 exit; */
    	$data = $obj->asArray()->all();
    	return $data;
    }

    /**
     * 更新推送结汇系统同步状态
     */
    public function updatePoSynchExc($purNumbers = array(),$status,$date,$error = 0){
    	return self::getDb()->createCommand()->update(
    			self::tableName(),array('is_push_to_exc' => $status,'exc_synch_error'=>$error,'exc_synch_time'=>$date),
    			['in','pur_number',$purNumbers])->execute();
    }
    

}
