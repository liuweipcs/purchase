<?php

namespace app\models;

use app\config\Vhelper;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception;
use yii\web\HttpException;
use app\models\PurchaseOrderPayType;
/**
 * This is the model class for table "{{%purchase_order_refund_quantity}}".
 *
 * @property integer $id
 * @property string $sku
 * @property string $name
 * @property integer $purchase_qty
 * @property integer $refund_qty
 * @property string $pur_number
 * @property string $requisition_number
 * @property integer $refund_status
 * @property string $creator
 * @property string $created_at
 */
class PurchaseOrderRefundQuantity extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_order_refund_quantity}}';
    }
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                   // \yii\db\ActiveRecord::EVENT_BEFORE_UPDATE => ['audit_time'],
                ],
                // if you're using datetime instead of UNIX timestamp:
                'value' => date('Y-m-d H:i:s',time()),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sku','name','purchase_qty','refund_qty','pur_number','creator'], 'required'],
            [['requisition_number'], 'string'],
            [['refund_status'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                => Yii::t('app', 'ID'),
            'sku'        => Yii::t('app', '退款的SKU'),
            'name'        => Yii::t('app', '产品名称'),
            'purchase_qty'        => Yii::t('app', '采购数量'),
            'refund_qty'        => Yii::t('app', '退货数量'),
            'pur_number'    => Yii::t('app', '采购单号'),
            'requisition_number'     => Yii::t('app', '退款申请的编号'),
            'refund_status'          => Yii::t('app', '退货状态'),
            'creator'   => Yii::t('app', '创建人'),
            'created_at'    => Yii::t('app', '创建时间')
        ];
    }


    //退款数量校验
    public static function verifyRefundQty($pur_number,$sku,$refund_qty=0){
        //待退货或者已退货的数量
        //$cancel_qty = self::find()->select('sum(refund_qty) as total')->where(['pur_number' => $pur_number,'sku'=>$sku])->scalar();
        $cancel_qty = self::find()
            ->alias('refund')
            ->select('sum(refund.refund_qty) as total')
            ->leftJoin(PurchaseOrderReceipt::tableName(), 'pur_purchase_order_receipt.requisition_number=refund.requisition_number')
            ->select('sum(refund.refund_qty) as total')
            ->where(['refund.pur_number' => $pur_number,'refund.sku'=>$sku])
            ->andWhere(['!=','pur_purchase_order_receipt.pay_status', 10])
            ->scalar();

        //采购订单数量
        $ctq = PurchaseOrderItems::find()->select('ctq')->andWhere(['pur_number' => $pur_number,'sku'=>$sku])->scalar();
        //订单取消信息
        $order_model   = new PurchaseOrder();
        $orderInfo     = $order_model->getOrderDetail($pur_number, $sku);
        if($refund_qty){
            if(isset($orderInfo['purchaseOrderItems'][0])){
                if(isset($orderInfo['purchaseOrderItems'][0]['weidaohuo_num']) && $orderInfo['purchaseOrderItems'][0]['weidaohuo_num']>0){
                    $weidaohuo_num = 0;
                    foreach($orderInfo['purchaseOrderItems'] as $key=>$val){
                        if($val['sku'] == $sku){
                            $weidaohuo_num = $val['ctq'] - $val['ruku_num'];
                        }
                    }
                    $left_num = $weidaohuo_num-$cancel_qty;
                    if($refund_qty > $left_num){
                        return [
                            'error'   => 1,
                            'message' => "SKU:{$sku}已经超出了订单数量，不能再申请退款了"
                        ];
                    }
                }
            }
        }
    }


    public static function getCancelCtq($pur_number=null,$sku=null)
    {
        $refund_ctq = PurchaseOrderRefundQuantity::find()
            ->select(['refund_qty'])
            ->where(['pur_number'=>$pur_number,'sku'=>$sku,'refund_status'=>1])
            ->asArray()
            ->one();
        $cancel_ctq = 0;
        if(isset($refund_ctq['refund_qty']) && $refund_ctq['refund_qty']>0){
            $cancel_ctq = $refund_ctq['refund_qty'];
        }
        return $cancel_ctq;
    }

    /**
     *  比较 取消数量  和  采购数量-入库数量
     */
    public static function cancelCtqBccomp($pur_number)
    {
        //采购数量
        $ctq_total = PurchaseOrderItems::getCtqTotal($pur_number);
        //订单详情
        $order_model   = new PurchaseOrder();
        $orderInfo     = $order_model->getOrderDetail($pur_number);

        $ruku_nums = 0;
        $cancel_ctq = 0;
        //WarehouseResults::getResults($v['pur_number'], $v['sku'])
        if (!empty($orderInfo['purchaseOrderItems'])) {
            foreach($orderInfo['purchaseOrderItems'] as $k => $v) {
                //入库数量
                $ruku_num = !empty(WarehouseResults::getResults($pur_number, $v['sku']))? (WarehouseResults::getResults($pur_number, $v['sku'])->arrival_quantity) : 0;
                $ruku_nums += $ruku_num;
                $cancel_ctq += (self::getCancelCtq($pur_number,$v['sku']));
            }
        }

        //如果：取消数量 = 采购数量-入库数量，状态变为，部分到货不等待剩余
        $ctq = $ctq_total-$ruku_num;
        return bccomp($cancel_ctq,$ctq);
    }
}
