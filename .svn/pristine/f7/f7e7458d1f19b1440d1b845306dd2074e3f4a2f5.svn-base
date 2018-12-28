<?php

namespace app\api\v1\models;

use app\config\Vhelper;
use app\models\PurchaseOrderBreakage;
use Yii;
use yii\behaviors\TimestampBehavior;
use app\models\PurchaseEstimatedTime;
use app\models\PurchaseOrderCancelSub;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%purchase_order}}".
 *
 * @property integer $id
 * @property string $pur_number
 * @property string $warehouse_code
 * @property string $supplier_code
 * @property string $pur_type
 * @property string $shipping_method
 * @property string $operation_type
 * @property string $created_at
 * @property string $creator
 * @property string $account_type
 * @property string $pay_type
 * @property string $currency_code
 * @property string $pay_ship_amount
 * @property string $date_eta
 * @property string $tracking_number
 * @property integer $buyer
 * @property integer $merchandiser
 * @property string $reference
 * @property integer $create_type
 * @property integer $audit_return
 * @property integer $purchas_status
 * @property integer $carrier
 *
 * @property PurchaseOrderItems[] $purchaseOrderItems
 */
class PurchaseOrder extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_order}}';
    }
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                    //\yii\db\ActiveRecord::EVENT_BEFORE_UPDATE => ['audit_time'],
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
            [['pur_number', 'warehouse_code', 'supplier_code', 'pur_type', 'creator'], 'required'],
            [['shipping_method', 'operation_type',], 'string'],
            [['created_at', 'date_eta','supplier_name'], 'safe'],
            [['pay_ship_amount'], 'number'],
            [['buyer', 'merchandiser', 'create_type', 'audit_return', 'purchas_status','carrier'], 'integer'],
            [['pur_number', 'creator'], 'string', 'max' => 20],
            [['warehouse_code', 'supplier_code', 'tracking_number', 'reference'], 'string', 'max' => 30],
            [['currency_code'], 'string', 'max' => 10],
            [['pur_number'], 'unique'],
        ];
    }



    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPurchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItems::className(), ['pur_number' => 'pur_number']);
    }

    /**
     * 关联供应商
     * @return \yii\db\ActiveQuery
     */
    public  function  getSupplier()
    {
        return $this->hasOne(Supplier::className(), ['supplier_code' => 'supplier_code']);
    }
    /**
     * 关联快递商
     * @return \yii\db\ActiveQuery
     */
    public  function  getLogistics()
    {
        return $this->hasOne(LogisticsCarrier::className(),['id'=>'carrier']);
    }
    /**
     * 关联快递商记录
     * @return \yii\db\ActiveQuery
     */
    public  function  getOrderShip()
    {
        return $this->hasOne(PurchaseOrderShip::className(),['pur_number'=>'pur_number'])->orderBy('id asc');
    }
    /**
     * 关联付款时间
     * @return \yii\db\ActiveQuery
     */
    public  function  getOrderPay()
    {
        return $this->hasOne(PurchaseOrderPay::className(),['pur_number'=>'pur_number'])->orderBy('id asc');
    }
    /**
     * 关联中间表
     * @return \yii\db\ActiveQuery
     */
    public  function  getOrderDemd()
    {
        return $this->hasOne(PurchaseDemand::className(),['pur_number'=>'pur_number'])->orderBy('id asc');
    }

    /**
     * 通过仓库到货数量修改采购单状态
     * @param $purchase_quantity
     * @param $arrival_quantity
     * @param $pur_number
     * @param int $receiving_exception_status
     * @param int $qc_abnormal_status
     * @return int
     */
    public static function UpdateStatus($purchase_quantity,$arrival_quantity,$pur_number,$receiving_exception_status,$qc_abnormal_status)
    {


        if($arrival_quantity < $purchase_quantity)
        {
            $model = self::find()->where(['pur_number' => $pur_number])->one();
            $model->receiving_exception_status                   = !empty($model->$receiving_exception_status)?$model->$receiving_exception_status:$receiving_exception_status;
            $model->qc_abnormal_status                           = !empty($model->$qc_abnormal_status)?$model->$qc_abnormal_status:$qc_abnormal_status;
            $model->save(false);

        } elseif ($arrival_quantity >= $purchase_quantity){
            $model = self::find()->where(['pur_number' => $pur_number])->one();
            $model->receiving_exception_status                   = !empty($model->$receiving_exception_status)?$model->$receiving_exception_status:$receiving_exception_status;
            $model->qc_abnormal_status                           = !empty($model->$qc_abnormal_status)?$model->$qc_abnormal_status:$qc_abnormal_status;
            $model->save(false);
        }

    }
    /**
     * 通过仓库入库更新采购状态
     * @param $purchase_quantity 采购数量
     * @param $arrival_quantity 国内：入库数量， 海外，FBA：到货数量
     * @param $pur_number
     * @return int
     */
    public static function UpdateStatusO($purchase_quantity,$arrival_quantity,$pur_number)
    {


        if($arrival_quantity < $purchase_quantity)
        {
            $model = self::find()->where(['pur_number' => $pur_number])->one();
            if($model)
            {
                if($model->refund_status==4 || $model->refund_status==2)
                {
                    //部分到货不等待剩余
                    $model->purchas_status=9;
                    $model->save(false);

                } else{
                    //部分到货等待剩余
                    $model->purchas_status=8;
                    $model->save(false);
                }

            }

        } elseif ($arrival_quantity >= $purchase_quantity){
            $model = self::find()->where(['pur_number' => $pur_number])->one();
            //全到货
            if ($model)
            {
                $model->purchas_status=6;
                $model->save(false);
            }

        }

    }

    /**
     * 通过采购单获取字段
     */
    public static function getFiled($pur_number,$filed='*')
    {
            return self::find()->select($filed)->where(['pur_number'=>$pur_number])->asArray()->one();
    }

    /**
     * 修改全到货状态
     */
    public static function updateArrivalStatus($pur_number)
    {
        $order_model = PurchaseOrder::findOne(['pur_number'=>$pur_number]);
        if (empty($order_model)) return false;

        $time_model = PurchaseEstimatedTime::find()
                ->where(['pur_number'=>$pur_number])
                ->andWhere(['not', ['estimated_time' => null]])
                ->count();
        $items_model = PurchaseOrderItems::find()->where(['pur_number'=>$pur_number])->count();       

        $time_status = bccomp($time_model, $items_model);

        if ($time_model == 0) {
            //未到货
            $order_model->arrival_status = 1;
        } elseif ($time_status == -1) {
            //部分到货
            $order_model->arrival_status = 2;
        } else {
            //全到货
            $order_model->arrival_status = 3;
        }
        $status = $order_model->save(false);
        return $status;

    }

    public static function updateOrderStatus($pur_number,$update=true){
        $type = Vhelper::getNumber($pur_number);

        $orderItems = PurchaseOrderItems::find()->select(['sku'=>'sku','ctq'=>'ifnull(ctq,0)'])->where(['pur_number'=>$pur_number])->asArray()->all();
        if (empty($orderItems)) return false;

        $orderItems = ArrayHelper::map($orderItems,'sku','ctq');
        $instockItems = WarehouseResults::find()->select(['sku'=>'sku','instock_qty_count'=>'ifnull(instock_qty_count,0)'])->where(['pur_number'=>$pur_number])->asArray()->all();
        $instockItems = ArrayHelper::map($instockItems,'sku','instock_qty_count');

        $statusArray = [];
        $is_cancel_ctq = false; //是否有取消数量

        foreach ($orderItems as $k=>$v){
            $cancel_ctq = PurchaseOrderCancelSub::getCancelCtq($pur_number,$k);//FBA-取消数量
            $breakage_num = PurchaseOrderBreakage::getNumber($k,$pur_number); //报损数量
            $instockItemsCtq = !isset($instockItems[$k])?0:$instockItems[$k]; //获取入库数量
            
            //海外仓-需求状态修改
            if ($type == 2) {
                $resultsData = WarehouseResults::find()->where(['pur_number'=>$pur_number, 'sku'=>$k])->asArray()->one();
                WarehouseResults::saveDemandInstock($resultsData);
            }
            

            //针对采购单的
            if ($cancel_ctq>0 || $breakage_num>0 ) $is_cancel_ctq=true;

            if( ($instockItemsCtq==0) && ($cancel_ctq==0) && ($breakage_num==0) ) {
                $statusArray[]=0;//没有入库数量且取消数量为0：则未到货
                continue;
            }

            if($v>($instockItemsCtq+$cancel_ctq+$breakage_num) && $instockItemsCtq!=0){
                $statusArray[]=1; //入库数量不够则未完全到货
                continue;
            }
            if($v<=($instockItemsCtq+$cancel_ctq+$breakage_num) ){
                $statusArray[]=2;//全部到货
                continue;
            }
            $statusArray[]=0;
        }
        $statusArray = array_unique($statusArray);
        if(!$update){
            var_dump($orderItems);
            var_dump($instockItems);
            var_dump($statusArray);exit();
        }
        if(count($statusArray)==1&&in_array(2,$statusArray)){
            //所有产品入库数量大于等于采购数量更新采购单状态为全部到货，只限等待到货，已审核，部分到货等待剩余，部分到货不等待剩余这几个状态的采购单
            PurchaseOrder::updateAll(['purchas_status'=>$is_cancel_ctq?9:6],['purchas_status'=>[6,7,3,8],'pur_number'=>$pur_number]);
        }elseif (count($statusArray)==1&&in_array(0,$statusArray)){
            PurchaseOrder::updateAll(['purchas_status'=>7],['purchas_status'=>[3,6,7,8],'pur_number'=>$pur_number]);
        }else{
            //有一个产品为未完全到货则更新产品状态未部分到货等待剩余，只限等待到货，已审核，部分到货等待剩余这几个状态的采购单
            PurchaseOrder::updateAll(['purchas_status'=>8],['pur_number'=>$pur_number,'purchas_status'=>[6,7,3,8]]);
        }

        

    }

}
