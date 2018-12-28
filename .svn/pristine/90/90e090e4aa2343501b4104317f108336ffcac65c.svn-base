<?php
namespace app\synchcloud\models;
use app\services\CommonServices;
use app\services\SupplierServices;
use Yii;
use yii\behaviors\TimestampBehavior;
use app\config\Vhelper;

use app\api\v1\models\SupplierContactInformation;
use app\api\v1\models\SupplierPaymentAccount;


class Supplier extends \yii\db\ActiveRecord
{  
	
	const EXC_SYNC_STATUS_INIT = 0; //待同步
	const EXC_SYNC_STATUS_SUCCESS = 1; //同步成功
	const EXC_SYNC_STATUS_FAIL = 2; //同步失败
	
	const CLOUD_SYNC_STATUS_INIT = 0; //待同步
	const CLOUD_SYNC_STATUS_SUCCESS = 1; //同步成功
	const CLOUD_SYNC_STATUS_FAIL = 2; //同步失败
	
	const EXC_SYNCH_ERROR_1 = 1 ; //供应商结汇方式支付的银行信息无账户名
	const EXC_SYNCH_ERROR_2 = 2 ; //供应商结汇方式支付的银行信息无证件号码
	const EXC_SYNCH_ERROR_3 = 3 ; //结算方式不存在
	const EXC_SYNCH_ERROR_4 = 4 ; //供应商不存在合同采购单
	const EXC_SYNCH_ERROR_5 = 5 ; //供应商结算方式不存在
	
	const CLOUD_SYNCH_ERROR_1 = 1 ; //供应商名称为空
	
    public static function tableName()
    {
        return '{{%supplier}}';
    }

    public function getApplicativeSupplier($limit = 10)
    {
        $sql = "SELECT
            a.id,          
            a.supplier_code,
            a.supplier_name,
            a.supplier_settlement,
            a.credit_code,
            a.create_time,
            a.update_time,
            b.account_name,
            b.account,
            b.payment_platform_bank
            FROM pur_supplier AS a
            LEFT JOIN pur_supplier_payment_account AS b 
            ON a.supplier_code = b.supplier_code
            WHERE a.is_push_to_k3cloud = 0
            LIMIT {$limit}";

        $rows = Yii::$app->db->createCommand($sql)->queryAll();
        if(empty($rows)) {
            return [];
        }
        $batch_data = [];
        $time = date('Y-m-d H:i:s', time());
        foreach($rows as $k=>$v) {
            $arr = [
                'id' => $v['id'],
                'supplier_code' => $v['supplier_code'],
                'supplier_name' => $v['supplier_name'],
                'short_name' => (mb_strlen($v['supplier_name']) >= 50) ? '' : $v['supplier_name'],
                'supplier_settlement_type' => !empty($v['supplier_settlement']) ? $v['supplier_settlement'] : 1,
                'supplier_type' => 1,
                'credit_code' => $v['credit_code'], // 企业社会信用码
                'supplier_bankname' => urldecode($v['payment_platform_bank']), // 支行类型
                'supplier_holder' => urldecode($v['account_name']), // 开户名称
                'supplier_account' => $v['account'] ? $v['account'] : '', // 开户帐号
                'add_time' => !empty($v['create_time']) ? date('Y-m-d H:i:s', $v['create_time']) : $time,
                'update_time' => !empty($v['update_time']) ? date('Y-m-d H:i:s', $v['update_time']) : $time,
            ];
            $batch_data[$k] = $arr;
        }
        return $batch_data;
    }
    
    /**
     * @desc 获取待同步结汇系统的供应商银行信息
     * @param string $fields
     * @param number $limit
     * @param string $conditions
     * @return unknown
     */
    public function getSyncSupplierExcData($fields='*',$limit=100,$conditions='')
    {
    	$where = 'a.exc_synch_status = '.self::EXC_SYNC_STATUS_INIT;
    	if (!empty($conditions)){
    		$where .= $conditions;
    	}
    	$obj = self::find()
	    	->select($fields)
	    	->from(self::tableName().' as a')
	    	->where($where)
	    	->andWhere('a.supplier_code != "" and a.supplier_code is not null')
	    	->limit($limit);
    
    	/* echo $obj->createCommand()->getSql();
    	exit; */
    	$data = $obj->asArray()->all();
    	return $data;
    }
    
    /**
     * @desc 获取待同步cloud的供应商信息
     * @param string $fields
     * @param number $limit
     * @param string $conditions
     * @return unknown
     */
    public function getSyncSupplierCloudData($fields='*',$limit=100,$conditions='')
    {
    	$where = 'a.is_push_to_k3cloud = '.self::CLOUD_SYNC_STATUS_INIT;
    	if (!empty($conditions)){
    		$where .= $conditions;
    	}
    
    	$obj = self::find()
	    	->select($fields)
	    	->from(self::tableName().' as a')
	    	->where($where)
	    	->andWhere('a.supplier_code != "" and a.supplier_code is not null')
	    	->andWhere('a.status in(1)')
	    	->limit($limit);
    
    	/* echo $obj->createCommand()->getSql();
    	 exit; */
    	$data = $obj->asArray()->all();
    	return $data;
    }
    
    /**
     * 更新结汇系统同步状态
     */
    public function updateSupplierSynchExc($supplierCodes=array(),$status,$date,$error = 0){
    	return self::getDb()->createCommand()->update(
    			self::tableName(),array('exc_synch_status' => $status,'exc_synch_error'=>$error,'exc_synch_time'=>$date,'modify_time'=>$date),
    			['in','supplier_code',$supplierCodes])->execute();
    }
    
    /**
     * 更新cloud系统同步状态
     */
    public function updateSupplierSynchCloud($ids=array(),$status,$date,$error = 0){
    	return self::getDb()->createCommand()->update(
    			self::tableName(),array('is_push_to_k3cloud' => $status,'cloud_synch_error'=>$error,'cloud_synch_time'=>$date),
    			['in','id',$ids])->execute();
    }
    
    /**
     * 获取更新后待同步结汇系统的供应商银行信息
     */
    public function getUpdateSupplierExc($fields='*',$limit = 30,$condition = ''){
    	$where = 'a.exc_synch_status = '.self::EXC_SYNC_STATUS_SUCCESS.' AND (a.exc_synch_time < a.modify_time)';
    	if (!empty($condition)){
    		$where .= $condition;
    	}
    	
    	$obj = self::find()
	    	->select($fields)
	    	->from(self::tableName().' as a')
	    	->where($where)
	    	->andWhere('a.supplier_code != "" and a.supplier_code is not null')
	    	->limit($limit);
    	
    	/* echo $obj->createCommand()->getSql();
    	 exit; */
    	$data = $obj->asArray()->all();
    
    	return $data;
    }
    
    /**
     * 获取更新后待同步cloud供应商信息
     */
    public function getUpdateSupplierCloud($fields='*',$limit = 30,$condition = ''){
    	$where = 'a.is_push_to_k3cloud = '.self::CLOUD_SYNC_STATUS_SUCCESS. ' AND (a.cloud_synch_time < a.modify_time)';
    	if (!empty($condition)){
    		$where .= $condition;
    	}
    	
    	$obj = self::find()
	    	->select($fields)
	    	->from(self::tableName().' as a')
	    	->where($where)
	    	->andWhere('a.supplier_code != "" and a.supplier_code is not null')
	    	->andWhere('a.status in(1)')
	    	->limit($limit);
    	
    	/* echo $obj->createCommand()->getSql();
    	 exit; */
    	$data = $obj->asArray()->all();
    
    	return $data;
    }

}


