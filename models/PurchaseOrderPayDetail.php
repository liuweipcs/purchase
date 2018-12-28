<?php

namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use Yii;

class PurchaseOrderPayDetail extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_order_pay_detail}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pur_number', 'requisition_number', 'freight', 'discount', 'sku_list', 'order_number'], 'safe'],
        ];
    }
    /**
     * @return 关联请款表
     */
    public function getPurchaseOrderPay()
    {
        return $this->hasOne(PurchaseOrderPay::className(), ['requisition_number' => 'requisition_number','pur_number'=>'pur_number']);
    }

    /**
     * 获取取消的sku详情
     * $is_all = 是展示所有
     */
    public static function getSkuList($where,$is_all=false)
    {
        $list = [];

        //判断这个采购单是否是合同
        $compactModel = PurchaseCompactItems::find()->select('compact_number')->where(['pur_number'=>$where['pur_number']])->andWhere(['bind' => 1])->one();
        if (!empty($compactModel)) $where['pur_number'] = $compactModel->compact_number;

        //sku请款详情
        if (!empty($is_all)){
            $sku_list = PurchaseOrderPayDetail::find()->alias('popd')->joinWith('purchaseOrderPay')->select('sku_list')->where(['popd.pur_number'=>$where['pur_number']])->andWhere(['in', 'pay_status', [2,4,5,6,10,13]])->all();
        } else {
            $sku_list = PurchaseOrderPayDetail::find()->select('sku_list')->where($where)->scalar();
        }
        if (!empty($sku_list)) {
            if (is_array($sku_list)) {
                foreach ($sku_list as $k=>$v) {
                    if ( empty(trim($v['sku_list'])) ) continue;
                    foreach (json_decode($v['sku_list']) as $jk=>$jv) {
                        if (!empty($list[$jv->sku])) {
                            $list[$jv->sku] += $jv->num;
                        }else {
                            $list[$jv->sku] = $jv->num;
                        }
                    }
                }
            } else {
                $sku_list = json_decode($sku_list);
                $list = \yii\helpers\ArrayHelper::map($sku_list,'sku','num');
            }
        }

        return $list;
    }

}