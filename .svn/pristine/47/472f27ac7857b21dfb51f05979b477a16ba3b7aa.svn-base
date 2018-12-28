<?php

namespace app\synchcloud\models;

use app\services\CommonServices;
use app\services\SupplierServices;
use Yii;
use yii\behaviors\TimestampBehavior;
use app\config\Vhelper;

use app\api\v1\models\SupplierContactInformation;

class SupplierPaymentAccount extends \yii\db\ActiveRecord
{  
	
	const EXC_SYNC_STATUS_INIT = 0; //待同步 
	const EXC_SYNC_STATUS_SUCCESS = 1; //同步成功
	const EXC_SYNC_STATUS_FAIL = 2; //同步失败
	
	const EXC_SYNCH_ERROR_1 = 1 ;//城市code错误
	const EXC_SYNCH_ERROR_2 = 2 ;//银行code错误
	const EXC_SYNCH_ERROR_3 = 3 ; //结汇方式支付的银行信息无账户名
	const EXC_SYNCH_ERROR_4 = 4 ; //结汇方式支付的银行信息无证件号码
	
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%supplier_payment_account}}';
    }


    /**
     * 关联支付方式
     * @return \yii\db\ActiveQuery
     */
    public function getPay()
    {
        return $this->hasMany(SupplierPaymentAccount::className(), ['supplier_id' => 'id']);
    }

    /**
     * 关联联系方式
     * @return \yii\db\ActiveQuery
     */
    public function getContact()
    {

        return $this->hasMany(SupplierContactInformation::className(), ['supplier_id' => 'id']);
    }

    public function getData($limit = 10)
    {
        $query = new \yii\db\Query();
        $fields = [
            'a.*',
            'b.province',
            'b.city',
            'b.area',
            'b.supplier_address',
            'b.supplier_name',
            'b.contract_notice',
            'c.supplier_settlement_name'
        ];

        $rows = $query->select(implode(',', $fields))
            ->from('pur_supplier_payment_account as a')
            ->leftJoin('pur_supplier as b', 'b.id = a.supplier_id')
            ->leftJoin('pur_supplier_settlement as c', 'b.supplier_settlement = c.supplier_settlement_code')
            ->where(['a.is_push_to_k3cloud' => 0])
            ->limit($limit)
            ->all();
        if(empty($rows)) {
            return [];
        }



        Vhelper::dump($rows);

        $batch_data = [];

        foreach($rows as $k=>$v) {
            $arr = [
                'provider_id' => $v['supplier_code'],
                'provider_name' => $v['supplier_name'],
                'short_name' => $v['supplier_name'],
                'provider_settlement_type' => !empty($v['supplier_settlement']) ? $v['supplier_settlement'] : 1,
                'provider_type' => 1
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
    public function getSyncSupplierBankData($fields='*',$limit=100,$conditions='')
    {
    	$where = 'exc_synch_status = '.self::EXC_SYNC_STATUS_INIT;
    	if (!empty($conditions)){
    		$where .= $conditions;
    	}
    	
    	$obj = self::find()
	    	->select($fields)
	    	->from(self::tableName())
	    	->where($where)
	    	->andWhere('supplier_code != "" and supplier_code is not null')
	    	->andWhere(['in','payment_platform',[6]]) //付款平台为结汇的
	    	->limit($limit);
    
    	/* echo $obj->createCommand()->getSql();
    	 exit; */
    	$data = $obj->asArray()->all();
    	return $data;
    }
    
    /**
     * 更新结汇系统同步状态 
     */
    public function updateSupplierBankSynchExc($ids=array(),$status,$date,$error = 0){
    	return self::getDb()->createCommand()->update(
    			self::tableName(),array('exc_synch_status' => $status,'exc_synch_error'=>$error,'exc_synch_time'=>$date,'modify_time'=>$date),
    			['in','pay_id',$ids])->execute();
    }
    
    /**
     * 获取更新后待同步结汇系统的供应商银行信息
     */
    public function getUpdateBankExc($fields='*',$limit = 30,$condition = ''){
    	$where = 'exc_synch_status = '.self::EXC_SYNC_STATUS_SUCCESS.' AND exc_synch_time < modify_time';
    
    	if (!empty($condition)){
    		$where .= $condition;
    	}
    
    	$obj = self::find()
	    	->select($fields)
	    	->from(self::tableName())
	    	->where($where)
	    	->andWhere('supplier_code != "" and supplier_code is not null')
	    	->andWhere(['in','payment_platform',[6]]) //付款平台为结汇的
	    	->limit($limit);
    	 
    	/* echo $obj->createCommand()->getSql();
    	 exit; */
    	$data = $obj->asArray()->all();
    	 
    	return $data;
    }
    
    /**
     * @desc 根据供应商code查询供应商支付信息中付款平台为 "结汇方式"的支付信息
     * @return unknown
     */
    public function getPaymentAccountExc($fields='*',$supplierCode='')
    {
    	if(empty($supplierCode)) return null;
    	$obj = self::find()
	    	->select($fields)
	    	->from(self::tableName())
	    	->where('supplier_code="'.$supplierCode.'"')
	    	->andWhere(['in','payment_platform',[6]])
    		->andWhere(['in','status',[1]]);
    
    	/* echo $obj->createCommand()->getSql();
    	 exit; */
    	$data = $obj->asArray()->one();
    	return $data;
    }
    
    /**
     * @desc 根据供应商code查询供应商支付信息
     * @return unknown
     */
    public function getPaymentAccount($fields='*',$supplierCode='')
    {
    	if(empty($supplierCode)) return null;
    	$obj = self::find()
	    	->select($fields)
	    	->from(self::tableName())
	    	->where('supplier_code="'.$supplierCode.'"')
	    	->andWhere(['in','status',[1]]);
    
    	/* echo $obj->createCommand()->getSql();
    	 exit; */
    	$data = $obj->asArray()->one();
    	return $data;
    }
    
}


