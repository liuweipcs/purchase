<?php

namespace app\models;

use app\models\base\BaseModel;
use Yii;
use app\config\Vhelper;

class PurchaseCompactItems extends BaseModel
{
    public static function tableName()
    {
        return '{{%purchase_compact_items}}';
    }

    public function rules()
    {
        return [
            [[
                'compact_number',
                'pur_number',
                'sku_num',
                'sku_total_money',
            ], 'required']
        ];
    }

    public function attributeLabels()
    {
        return [
            'compact_number' => '合同编号',
            'pur_number' => '订单号',
            'sku_num' => 'sku数量',
            'sku_total_money' => 'sku总金额',
        ];
    }
    /**
     * 关联采购单--一对一
     */
    public function getPurchaseOrder()
    {
        return $this->hasOne(PurchaseOrder::className(), ['pur_number' => 'pur_number']);
    }

    /**
     * 关联请款表-一对多
     */
    public function getPurchaseOrderPay()
    {
        return $this->hasMany(PurchaseOrderPay::className(),['pur_number'=>'compact_number']);
    }

    /**
     * 作废逻辑-判断是否是合同单，是否付了款
     */
    public static function getCancelJudgeCompact($pur_number, $data = [])
    {
        $pay_ratios[] = $pay_categorys[] = 0; //付款比例，付款类型
        $order_total_price=0;
        $compactItemsInfo = PurchaseCompactItems::find()->where(['pur_number'=>$pur_number,'bind'=>1])->asArray()->one();
        if (!empty($compactItemsInfo)) {
            $compact_number = $compactItemsInfo['compact_number'];
            $compactItemsNumberInfo = PurchaseCompactItems::find()->select('pur_number')->where(['compact_number'=>$compact_number,'bind'=>1])->asArray()->all();
            $pur_numbers = array_column($compactItemsNumberInfo, 'pur_number');

            //订单总额
            foreach ($pur_numbers as $v) {
                $itemsPriceInfo = PurchaseOrderItems::getItemsPrice($v);
                foreach ($itemsPriceInfo as $item){
                    $order_total_price += bcmul($item['ctq'],$item['price'],3);
                }
            }

            $orderPayInfo[] = PurchaseOrderPay::find()
                ->select('*,sum(pay_price) as pay_total_price')
                ->where(['pur_number'=>$compact_number])
                ->andWhere(['in', 'pay_status', [5, 6]])
                // ->andWhere(['in', 'pay_category', [12, 13, 21]])
                // ->andWhere(['<>', 'pay_category', '20'])
                ->asArray()->one();

            $data['pur_number'] = $pur_number;
            $data['compact_number'] = $compact_number;
            $data['orderPayInfo'] = $orderPayInfo;
            $data['order_total_price'] = $order_total_price?:0;
            $data['pay_total_price'] = !empty($orderPayInfo[0]['pay_total_price'])?$orderPayInfo[0]['pay_total_price']:0;
            if (!empty($orderPayInfo)) {
                $pay_ratios = array_column($orderPayInfo, 'pay_ratio');
                $pay_categorys = array_column($orderPayInfo, 'pay_category');
            }
            
            $data['pay_ratios'] = $pay_ratios;
            $data['pay_categorys'] = $pay_categorys;

            // pay_category
            // '12' => '合同付订金', //第一个比例
            // '13' => '合同付中期款项', //中间比例
            // '20' => '合同付尾款',//最后比例
            // '21' => '合同付款手动输入金额', //手动请款
        }
        //判断是合同还是网采
        if (!empty($compactItemsInfo) && !empty($orderPayInfo) && ($data['pay_total_price']<$data['order_total_price']) ) {
            # 合同：审核通过的，合同单，部分付款的，
            return $data;
        } else {
            # 网采：网采，合同未付款
            return false;
        }
    }
    /**
     * 作废逻辑-海外仓新版-判断是否是合同单，是否付了款
     */
    public static function getNewCancelJudgeCompact()
    {}





}
