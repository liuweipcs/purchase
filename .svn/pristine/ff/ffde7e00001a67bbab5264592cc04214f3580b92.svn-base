<?php
namespace app\synchcloud\models;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
class PurchaseOrderPayWater extends \yii\db\ActiveRecord
{

    public $purchase_id;
    
    const CLOUD_SYNC_STATUS_INIT = 0; //待同步
    const CLOUD_SYNC_STATUS_SUCCESS = 1; //同步成功
    const CLOUD_SYNC_STATUS_FAIL = 2; //同步失败
    
    const CLOUD_SYNCH_ERROR_1 = 1 ; //采购退款单为空
    const CLOUD_SYNCH_ERROR_2 = 2 ; //采购审批付款人为空
    const CLOUD_SYNCH_ERROR_3 = 3 ; //付款人为空
    const CLOUD_SYNCH_ERROR_4 = 4 ; //业务日期为空
    const CLOUD_SYNCH_ERROR_5 = 5 ; //采购合同不存在
    const CLOUD_SYNCH_ERROR_6 = 6 ; //采购单不存在

    public static function tableName()
    {
        return '{{%purchase_order_pay_water}}';
    }

    // 关联采购单
    public function getPurchaseOrder()
    {
        return $this->hasOne(PurchaseOrder::className(), ['pur_number' => 'pur_number']);
    }

    public function getPurchaseOrderPay()
    {
        return $this->hasOne(\app\models\PurchaseOrderPay::className(), ['pur_number' => 'pur_number']);
    }

    public function getBankAccount()
    {
        return $this->hasOne(\app\models\BankCardManagement::className(), ['account_abbreviation' => 'our_account_abbreviation']);
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


}
