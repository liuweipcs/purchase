<?php

namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use Yii;
use app\services\PlatformSummaryServices;

/**
 * This is the model class for table "{{%purchase_order_cancel}}".
 *
 * @property string $id
 * @property string $pur_number
 * @property string $buyer
 * @property string $buyer_note
 * @property string $create_time
 * @property string $audit
 * @property string $audit_time
 * @property string $audit_note
 * @property string $buyer_id
 * @property integer $audit_status
 * @property integer $purchase_type
 *
 * @property PurchaseOrder $purNumber
 */
class PurchaseOrderCancel extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_order_cancel}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_time', 'audit_time'], 'safe'],
            [['buyer_id', 'audit_status', 'purchase_type'], 'integer'],
            [['pur_number'], 'string', 'max' => 100],
            [['buyer', 'audit'], 'string', 'max' => 50],
            [['buyer_note', 'audit_note'], 'string', 'max' => 255],
            //[['pur_number'], 'exist', 'skipOnError' => true, 'targetClass' => PurchaseOrder::className(), 'targetAttribute' => ['pur_number' => 'pur_number']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pur_number' => 'PO号',
            'buyer' => '采购员',
            'buyer_note' => '采购备注',
            'create_time' => '创建时间',
            'audit' => '审核员',
            'audit_time' => '审核时间',
            'audit_note' => '审核备注',
            'buyer_id' => '采购员ID',
            'audit_status' => '审核状态',
            'purchase_type' => 'Purchase Type',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPurNumber()
    {
        return $this->hasOne(PurchaseOrder::className(), ['pur_number' => 'pur_number']);
    }

    /**
     * 关联取消未到货子表
     */
    public function getPurchaseOrderCancelSub()
    {
        return $this->hasMany(PurchaseOrderCancelSub::className(), ['cancel_id' => 'id']);
    }
    /**
     * 关联收款表
     */
    public function getPurchaseOrderReceipt()
    {
        return $this->hasMany(PurchaseOrderReceipt::className(), ['requisition_number' => 'requisition_number']);
    }
    /**
     * 保存取消未到货数量
     */
    public static function saveCancel($data,$purchase_type,$cancel_type=1, $is_audit = null)
    {
        if (!empty($is_audit)) {
            //审核通过
            $cancel_model = self::find()->where(['id'=>$data['cancel_id']])->one();
            $cancel_model->audit = Yii::$app->user->identity->username;
            $cancel_model->audit_time = date('Y-m-d H:i:s', time());
            $cancel_model->audit_note = $data['audit_note'];
            $cancel_model->audit_status = $data['audit_status'];
            return $cancel_model->save(false);
        } else {
            $cancel_model = new  self();
            $cancel_model->pur_number = $data['pur_number'];
            $cancel_model->buyer = Yii::$app->user->identity->username;
            $cancel_model->buyer_note = $data['buyer_note'];
            $cancel_model->create_time = date('Y-m-d H:i:s', time());
            $cancel_model->buyer_id = Yii::$app->user->identity->id;
            $cancel_model->purchase_type = $purchase_type;
            $cancel_model->audit_status = 1;
            $cancel_model->cancel_type = $cancel_type;
            $cancel_model->freight = $data['freight'];
            $cancel_model->discount = $data['discount'];
            $cancel_model->discount = $data['discount'];
            $status = $cancel_model->save(false);
            return $cancel_model->id;// 返回模型的ID
        }
    }
    /**
     * 保存申请单号，用来获取订单状态
     */
    public static function saveRequisitionNumber($cancel_id,$requisition_number)
    {
        $model = self::find()->where(['id'=>$cancel_id])->one();
        $model->requisition_number = $requisition_number;

        //表修改日志-更新
        $change_content = TablesChangeLog::updateCompare($model->attributes, $model->oldAttributes);
        $change_data = [
            'table_name' => 'pur_purchase_order_cancel', //变动的表名称
            'change_type' => '2', //变动类型(1insert，2update，3delete)
            'change_content' => $change_content, //变更内容
        ];
        TablesChangeLog::addLog($change_data);

        $status = $model->save(false);
    }

    /**
     * 获取备注
     */
    public static function getNote($id)
    {
        $notes = self::find()->where(['id'=>$id])->one();
        $info = '';
        $info .= "采购员备注：{$notes->buyer_note}<br />";
        $info .= "审核备注：{$notes->audit_note}";
        return $info;
    }
    /**
     * 获取采购员备注
     */
    public static function getBuyerNote($id)
    {
        $notes = self::find()->where(['id'=>$id])->one();
        return $notes->buyer_note;
    }
    /**
     * 获取审核结果
     */
    public static function getAuditStatus($id)
    {
        $notes = self::find()->where(['id'=>$id])->one();
        return $notes->audit_status;
    }
    /**
     * 获取审核备注
     */
    public static function getAuditNote($id)
    {
        $notes = self::find()->where(['id'=>$id])->one();
        return $notes->audit_note;
    }
    /**
     * 获取取消类型
     */
    public static function getCancelType($id)
    {
        return self::find()->select('cancel_type')->where(['id'=>$id])->scalar();
    }
    /**
     * 获取运费
     */
    public static function getFreight($id)
    {
        return self::find()->select('freight')->where(['id'=>$id])->scalar();
    }
    /**
     * 获取优惠额
     */
    public static function getDiscount($id)
    {
        return self::find()->select('discount')->where(['id'=>$id])->scalar();
    }
    /**
     * 比较取消的金额和付款的金额
     */
    public static function comparePrice($data)
    {
        $cancel_total_price = $data['cancel_total_price']; //取消的总金额
        $audit_total_price = PurchaseOrderCancelSub::getCancelPriceOrder($data['pur_number']); //审核通过的订单的金额
        $cancel_price = $cancel_total_price + $audit_total_price; //总取消金额：当前取消的+审核通过的

        $totalPay = PurchaseOrderPay::getOrderPaidMoney($data['pur_number']); //财务付款总金额（6部分付款 5已付款）除去运费和优惠额


        if ($totalPay > 0) {
            //优惠和运费
            $price_info = PurchaseOrderPayType::getDiscountPrice($data['pur_number']);

            $freight = !empty($price_info['freight']) ?$price_info['freight'] : 0;
            $discount = !empty($price_info['discount']) ?$price_info['discount'] : 0;
            $total_pay = $totalPay + $freight - $discount;

            //如果有收款
            $bool = bccomp($cancel_price,$total_pay);
            if ($bool == 1) {
                // 总取消金额>付款金额
                return "<br />总取消金额大于财务付款金额<br />此次取消总金额：{$cancel_total_price}<br />审核通过总金额：{$audit_total_price}<br />财务付款总金额：{$total_pay}";
            }
        }
        return true;
    }
    /**
     * 财务收款时，修改推送状态
     */
    public static function updateIsPush($requisition_number)
    {
       return self::updateAll(['is_push'=>0],['requisition_number'=>$requisition_number]);
    }
    /**
     * 保存作废信息
     */
    public static function saveCancelInfo($purchase_type)
    {
        $post_info    = Yii::$app->request->post();
        $data = Vhelper::changeData($post_info['cancel_ctq']);
        $tran = Yii::$app->db->beginTransaction();
        try{
            //比较金额
            $cancel_id = PurchaseOrderCancel::saveCancel($post_info,$purchase_type,$post_info['is_all_cancel']);

            foreach ($data as $k=>$v) {
                if (!empty($v['demand_number'])) {
                    $summary_model = PlatformSummary::findOne(['demand_number'=>$v['demand_number']]);
                    $summary_model->audit_level = PlatformSummaryServices::getOverseasChecklevel(4, $v['price']*$v['ctq']);
                    $summary_model->save();
                }

                $data[$k]['cancel_id'] = $cancel_id;
            }
            PurchaseOrderCancelSub::saveCancelSub($data,$post_info['is_all_cancel']);

            //修改需求的订单状态
            $demand_numbers = array_column($data, 'demand_number'); //获取需求单号
            if (!empty($demand_numbers)) {
                PlatformSummary::updateAll(['demand_status'=>12], ['demand_number'=>$demand_numbers]);
            }
            $tran->commit();

            Yii::$app->getSession()->setFlash('success','恭喜你，修改成功，请等待审核');
        }catch(HttpException $e){
            $tran->rollBack();
            Yii::$app->getSession()->setFlash('error','恭喜你，修改失败，请重新修改');
        }
    }
    /**
     * 获取合同单取消的总金额
     * @param  [type] $pur_number [description]
     * @param  [type] $is_new     [是否显示在前端的取消金额]
     * @return [type]             [description]
     */
    public static function getCompactCancelPrice($compact_number, $is_new=true)
    {
        $cancel_price = 0;
        //根据采购单号获取合同下的所有采购单号
        // $compactItemsInfo = PurchaseCompactItems::find()->select('compact_number')->where(['pur_number'=>$pur_number, 'bind'=>1])->asArray()->one();
        // $compact_number = $compactItemsInfo['compact_number'];
        $compactItemsInfo = PurchaseCompactItems::find()->select('pur_number')->where(['compact_number'=>$compact_number])->asArray()->all();
        $pur_numbers = array_column($compactItemsInfo, 'pur_number');
        if ($is_new) {
            foreach ($pur_numbers as $v) $cancel_price += PurchaseOrderCancelSub::getCancelPriceOrder($v);
        } elseif( !empty($pur_numbers[0]) ) {
            $compactItemsInfo = PurchaseCompactItems::getCancelJudgeCompact($pur_numbers[0]);
            //付了订金，当前请款为尾款
            if (!empty($compactItemsInfo)) {
                foreach ($pur_numbers as $v) $cancel_price += PurchaseOrderCancelSub::getCancelPriceOrder($v);
            }
        }
        return $cancel_price;
    }
    /**
     * 请款中需要减去的金额
     * 如果需求部分作废：尾款-取消数量*单价
     * 如果需求全部作废：尾款-订金
     */
    public static function getNewCompactCancelPrice($compact_number)
    {
        $return_res = 0;
        $demand_numbers = $demand_pay_price = $itemsPrice = $cancelCtq = [];
        // '5'  => '已付款',
        // '6'  => '已部分付款',
        $andWhere = ['in', 'pay_status', [5, 6]];

        $compactItemsInfo = PurchaseCompactItems::find()->select('pur_number')->where(['compact_number'=>$compact_number])->asArray()->all();
        $pur_numbers = array_column($compactItemsInfo, 'pur_number');
        $orderCancelInfo = PurchaseOrderCancel::find()
            ->alias('poc')
            ->joinWith('purchaseOrderCancelSub')
            ->where(['in', 'poc.pur_number', $pur_numbers])
            ->andWhere(['audit_status'=>2])
            ->asArray()->all();

        foreach ($pur_numbers as $key => $value) {
            $itemsPrice = array_merge($itemsPrice,PurchaseOrderItems::getItemsPrice($value));
        }

        if (!empty($orderCancelInfo)) {
            $orderCancelSubInfo = array_column($orderCancelInfo, 'purchaseOrderCancelSub');
            //获取有取消未到货的需求号集合
            foreach ($orderCancelSubInfo as $sk => $sv) {
                foreach ($sv as $k => $v) {
                    if (!empty($v['demand_number'])) {
                        $demand_numbers[] = $v['demand_number'];
                        if ( !empty($cancelCtq[$v['demand_number']]) ) {
                            $cancelCtq[$v['demand_number']] += $v['cancel_ctq'];
                        } else {
                            $cancelCtq[$v['demand_number']] = $v['cancel_ctq'];
                        }
                    }
                }
            }

            $orderPayDemandMap = OrderPayDemandMap::find()->joinWith('purchaseOrderPay')->where(['in', 'demand_number', $demand_numbers])->andWhere($andWhere)->asArray()->all();
            if (!empty($orderPayDemandMap)) {
                //算出每个需求付的金额
                foreach ($orderPayDemandMap as $mk => $mv) {
                    if (isset($demand_pay_price[$mv['demand_number']])) {
                        $demand_pay_price[$mv['demand_number']] += $mv['pay_amount'];
                    } else {
                        $demand_pay_price[$mv['demand_number']] = $mv['pay_amount'];
                    }
                }

                $summaryInfo = PlatformSummary::find()->select('sku, demand_number,purchase_quantity,demand_status')->where(['in', 'demand_number', $demand_numbers])->asArray()->all();
                foreach ($summaryInfo as $sk => $sv) {
                    if (!empty($demand_pay_price[$sv['demand_number']])) {
                        if ($sv['demand_status'] == 14) {
                            // 如果需求全部作废：尾款-订金
                            $return_res += $demand_pay_price[$sv['demand_number']];
                        } else {
                            // 如果需求部分作废：尾款-取消数量*单价
                            $return_res += $cancelCtq[$sv['demand_number']]*$itemsPrice[$sv['sku']]['price'];
                        }
                    }
                }
            }
        }
        return $return_res;
    }
}
