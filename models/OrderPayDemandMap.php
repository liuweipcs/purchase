<?php

namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use Yii;
use yii\db\Exception;

/**
 * This is the model class for table "{{%product}}".
 *
 * @property string $id
 * @property string $sku
 * @property string $product_category_id
 * @property integer $product_status
 * @property string $uploadimgs
 * @property string $product_cn_link
 * @property string $product_en_link
 * @property string $create_id
 * @property string $create_time
 * @property string $product_cost
 * @property string $product_is_multi
 */
class OrderPayDemandMap extends BaseModel
{

    public $payer_time;
    public $pay_status;
    
	/**
     * 关联请款单表
     */
    public function getPurchaseOrderPay()
    {
    	return $this->hasOne(PurchaseOrderPay::className(), ['requisition_number' => 'requisition_number']);
    }

    /**
     * 关联需求表-一对一
     */
    public function getPlatformSummary(){
        return $this->hasOne(PlatformSummary::className(),['demand_number'=>'demand_number']);
    }
	/**
	 * 获取已付款金额
	 * @return [type] [description]
	 */
	public static function getPayPrice($demand_number)
	{
		$res = [];
		$res['pay_price'] = 0; //请款金额

		$model = self::find()->where(['demand_number'=>$demand_number])->all();
		if (empty($model)) {
			# 如果未付款
			$res['pay_status'] = 1; //未申请付款
			$res['price_type'] = -1; //请款类型：-1未请款
			$res['pay_ratio'] = 0; //请款比例
		} else {
			foreach ($model as $key => $map) {
			    if(!isset($map->purchaseOrderPay))continue;
				$pay_status = $map->purchaseOrderPay->pay_status;
				$res['pay_status'] = $pay_status; //请款状态
				$res['price_type'] = $map->price_type; //1:比例请款,2:手动请款
				$res['pay_ratio'] = $map->pay_ratio; //请款比例
				$pay_arr = [5,6]; //已付款和部分付款
				if (in_array($pay_status, $pay_arr)) {
					# 已付款
					$res['pay_price'] += ($map->pay_amount+$map->freight-$map->discount);
				}
			}
		}
		return $res;
	}
	/**
     * 获取当前请款的sku
     */
	public  static function getCurrentPaySkus($where)
    {
        $skus = []; //已请款的sku

        if (!empty($where['requisition_number'])) {
            $demandMap = self::find()->joinWith('platformSummary')->where(['requisition_number'=>$where['requisition_number']])->asArray()->all();
            if (!empty($demandMap)) foreach ($demandMap as $v) $skus[] = $v['platformSummary']['sku'];
        }
        return $skus;
    }
}
