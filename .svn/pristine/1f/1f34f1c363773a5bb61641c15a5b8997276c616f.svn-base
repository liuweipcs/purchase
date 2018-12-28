<?php
namespace app\synchcloud\models;
class PurchaseCompactItemsRestore extends \yii\db\ActiveRecord
{
	const CLOUD_SYNC_STATUS_INIT = 0; //待同步
	const CLOUD_SYNC_STATUS_SUCCESS = 1; //同步成功
	const CLOUD_SYNC_STATUS_FAIL = 2; //同步失败
	
	const CLOUD_SYNCH_ERROR_1 = 1 ; //采购单或合同单号为空
	
    public static function tableName()
    {
        return '{{%purchase_compact_items_restore}}';
    }

    /**
     * 获取同步到MID的采购合同与采购单的绑定关系
     * 采购单已经同步到MID了，优惠额还未同步的，优惠额大于零的
     */
    public function getData($limit = 10)
    {
        $data = self::find()
            ->select(['id', 'compact_number', 'pur_number'])
            ->where(['bind' => 1, 'is_push_to_k3cloud' => 0])
            ->limit($limit)
            ->asArray()
            ->all();
        return $data;
    }
    
    /**
     * @desc 获取待同步cloud的合同和采购单绑定关系
     */
    public function getSyncCompactBindCloudData($fields='*',$limit=100,$conditions='')
    {
    	$where = 'a.is_push_to_k3cloud = '.self::CLOUD_SYNC_STATUS_INIT;
    	if (!empty($conditions)){
    		$where .= $conditions;
    	}
    
    	$obj = self::find()
	    	->select($fields)
	    	->from(self::tableName().' as a')
	    	->where($where)
	    	->andWhere('a.bind=1')
	    	->limit($limit);
    
    	/* echo $obj->createCommand()->getSql();
    	 exit; */
    	$data = $obj->all();
    	return $data;
    }
    
    /**
     * 更新cloud系统同步状态
     */
    public function updateCompactBindSynchCloud($ids=array(),$status,$date,$error = 0){
    	return self::getDb()->createCommand()->update(
    			self::tableName(),array('is_push_to_k3cloud' => $status,'cloud_synch_error'=>$error,'cloud_synch_time'=>$date),
    			['in','id',$ids])->execute();
    }
    
    /**
     * @desc 根据采购单查询是否有捆绑合同 
     */
    public function getCompactBindByPo($fields='*',$po='')
    {
    	if( !$po ) return null;
    	$obj = self::find()
	    	->select($fields)
	    	->from(self::tableName())
	    	->where('pur_number="'.$po.'"')
	    	->andWhere('bind=1');
    
    	/* echo $obj->createCommand()->getSql(); 
    	 exit; */
    	$data = $obj->asArray()->one();
    	return $data;
    }
    
    /**
     * @desc 根据采购合同查询采购单
     */
    public function getPoInfoByCompact($fields='*',$compactNo='')
    {
    	if( !$po ) return null;
    	$obj = self::find()
	    	->select($fields)
	    	->from(self::tableName())
	    	->where('compact_number="'.$compactNo.'"')
	    	->andWhere('bind=1');
    
    	/* echo $obj->createCommand()->getSql();
    	 exit; */
    	$data = $obj->asArray()->all();
    	return $data;
    }
    
    
    /**
     * @desc  根据采购单批量查询是否有捆绑合同
     */
    public function getCompactsBindByPo($fields='*',$pos=[])
    {
    	if( !$pos || !is_array($pos) ) return null;
    	$obj = self::find()
	    	->select($fields)
	    	->from(self::tableName())
	    	->where(['in','pur_number',$pos])
	    	->andWhere('bind=1');
    
    	/* echo $obj->createCommand()->getSql();
    	 exit; */
    	$data = $obj->asArray()->all();
    	return $data;
    }

}

