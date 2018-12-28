<?php
namespace app\synchcloud\models;
class PurchaseOrderItems extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%purchase_order_items}}';
    }

    /**
     * 根据订单模型列表依次获取订单的sku信息  
     */
    public function GetPurcahseOrderItems($orders = [])
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
     * @desc 获取待同步cloud的采购订单明细
     */
    public function getSyncPoDetailCloudData($fields='*',$purchaseNumber='')
    {
    	if( !$purchaseNumber ) return null;
    
    	$obj = self::find()
	    	->select($fields)
	    	->from(self::tableName())
	    	->where('pur_number = "'.$purchaseNumber.'"');
    
    	/* echo $obj->createCommand()->getSql();
    	 exit; */
    	$data = $obj->asArray()->all();
    	return $data;
    }
    
    /**
     * @desc 获取采购单明细 根据采购单号
     */
    public function getPoDetailData($fields='*',$purNumbers = [])
    {
    	if( !$purNumbers ) return null;
    
    	$obj = self::find()
	    	->select($fields)
	    	->from(self::tableName().' as a')
	    	->leftJoin(PurchaseOrder::tableName().' as b','a.pur_number = b.pur_number')
	    	->where( ['in','a.pur_number',$purNumbers] );
    
    	/* echo $obj->createCommand()->getSql();
    	 exit; */
    	$data = $obj->asArray()->all();
    	return $data;
    }

}
