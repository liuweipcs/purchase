<?php

namespace app\api\v1\models;

use app\config\Vhelper;
use Yii;
use yii\behaviors\TimestampBehavior;
use app\models\PurchaseEstimatedTime;
use app\models\PurchaseOrderCancelSub;

/**
 * This is the model class for table "{{%warehouse_results}}".
 *
 * @property integer $id
 * @property string $pur_number
 * @property string $sku
 * @property string $express_no
 * @property integer $purchase_quantity
 * @property integer $arrival_quantity
 * @property integer $nogoods
 * @property integer $have_sent_quantity
 * @property integer $receipt_number
 */
class WarehouseResults extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%warehouse_results}}';
    }
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['create_time'],
                    \yii\db\ActiveRecord::EVENT_BEFORE_UPDATE => ['update_time'],
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
            [['purchase_quantity', 'arrival_quantity', 'nogoods', 'have_sent_quantity','instock_qty_count'], 'integer'],
            [['pur_number', 'sku','receipt_number'], 'string', 'max' => 30],
            [['express_no'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                 => Yii::t('app', 'ID'),
            'pur_number'         => Yii::t('app', '采购单号'),
            'sku'                => Yii::t('app', '产品sku'),
            'express_no'         => Yii::t('app', '快递单号'),
            'purchase_quantity'  => Yii::t('app', '采购数量'),
            'arrival_quantity'   => Yii::t('app', '到货数量'),
            'nogoods'            => Yii::t('app', '不良品数量'),
            'have_sent_quantity' => Yii::t('app', '赠送数量'),
            'instock_qty_count'  => Yii::t('app', '上架数量'),
            'receipt_number'     => Yii::t('app', '入库单号'),
        ];
    }

    /**
     * 检测有没有存在这个订单
     * @param $datas
     * @return mixed
     */
    public static function findWarehouse($datas)
    {
        //Vhelper::dump($datas);
        $data=[];
        foreach($datas as $k=>$v)
        {

            $model = self::find()->where(['sku'=>$v['sku'],'pur_number'=>$v['pur_number']])->one();
            InformMessage::saveMessage($v);
            if ($model)
            {
                $status = self::saveWarehouse($model,$v);
                if($status)
                {
                    $data['success_list'][$k]['pur_number'] = $model->attributes['pur_number'];
                    $data['success_list'][$k]['sku']        = $model->attributes['sku'];
                    $data['success_list'][$k]['express_no'] = $model->attributes['express_no'];
                    $data['success_list'][$k]['receipt_id'] = isset($v['receipt_id']) ? $v['receipt_id'] : '';
                } else{

                    $data['failure_list'][$k]['pur_number'] = $v['pur_number'];
                    $data['failure_list'][$k]['sku']        = $v['sku'];
                    $data['failure_list'][$k]['express_no'] = $v['express_no'];
                    $data['failure_list'][$k]['receipt_id'] = isset($v['receipt_id']) ? $v['receipt_id'] : '';
                }

            } else {
                $model =new self;
                $status = self::saveWarehouse($model,$v);
                if($status)
                {
                    $data['success_list'][$k]['pur_number'] = $model->attributes['pur_number'];
                    $data['success_list'][$k]['sku']        = $model->attributes['sku'];
                    $data['success_list'][$k]['express_no'] = $model->attributes['express_no'];
                    $data['success_list'][$k]['receipt_id'] =  isset($v['receipt_id']) ? $v['receipt_id'] : '';
                } else{
                    $data['failure_list'][$k]['pur_number'] = $v['pur_number'];
                    $data['failure_list'][$k]['sku']        = $v['sku'];
                    $data['failure_list'][$k]['express_no'] = $v['express_no'];
                    $data['failure_list'][$k]['receipt_id'] =  isset($v['receipt_id']) ? $v['receipt_id'] : '';
                }
            }
        }
        return $data;
    }

    /**
     * 保存返回的信息
     * @param $model
     * @param $v
     * @return mixed
     */
    public  static function saveWarehouse($model,$v)
    {
        $transaction=\Yii::$app->db->beginTransaction();
        try {

            $type = Vhelper::getNumber($v['pur_number']);
            //根据采购单判断类型,如果是类型等于1或则是等于FBA的话
            if($type==1 || $type==3)
            {
                $arrival_quantitys = $v['arrival_quantity']< 0 ?0:$v['arrival_quantity'];
                $instock_qty_count = $v['instock_qty_count']< 0 ?0:$v['instock_qty_count'];
                $model->sku                 = $v['sku'];
                $model->express_no          = $v['express_no'];
                $model->receipt_number      = isset($v['receipt_number'])?$v['receipt_number']:'';
                $model->purchase_quantity   = $v['purchase_quantity'];
                $model->have_sent_quantity  = $v['purchase_quantity'] - $v['arrival_quantity'];
                $model->arrival_quantity    = $arrival_quantitys;
                $model->nogoods             = $v['nogoods'];
                $model->pur_number          = $v['pur_number'];
                $model->instock_qty_count   = $instock_qty_count;
                $model->instock_user        = isset($v['instock_user'])?$v['instock_user']:'未知';
                $model->instock_date        = isset($v['instock_date'])?$v['instock_date']:'';
                //更新采购单里里面的详细表
                PurchaseOrderItems::updateAll(['rqy'=>$arrival_quantitys,'cty'=>$instock_qty_count],['pur_number'=>$v['pur_number'],'sku'=>$v['sku']]);
                //更新采购单里面的状态
                //PurchaseOrder::UpdateStatusO($v['purchase_quantity'],$v['arrival_quantity'],$v['pur_number']);
                //保存收货异常,如果到货数量小于采购数量说明出现了收获异常
                //$v['arrival_quantity'] < $v['purchase_quantity'] ? PurchaseReceive::FindOnes($v):'';
                //保存qc异常,如国这个不良品大于零说明有qc异常出现了
                //$v['nogoods'] > 0 ? PurchaseQc::FindOnes($v):'';
                $status =$model->save(false);

                //更新采购单里面的状态
                PurchaseOrder::updateOrderStatus($v['pur_number']);


            } elseif($type==2) {
                /****
                 *
                 * 1海外仓的到货记录,中间件是存在入库记录表。
                 * 2国内仓的到货记录是通过另一个接口过来,当前接口只接收入库的数据。
                 * 3也就是说此接口承担着保存海外仓到货记录的功能,又要对海外仓单个sku入库数量进行汇总的功能。

                 ****/

                $delivery_time = isset($v['instock_date']) ? $v['instock_date'] : ''; //到货数量
                $qc_id = isset($v['receipt_id']) ? $v['receipt_id'] : 0;

                $arrival_model = ArrivalRecord::find()
                    ->where(['purchase_order_no'=>$v['pur_number']])
                    ->andWhere(['sku'=>$v['sku']])
                    ->andWhere(['qc_id'=>$qc_id])
                    ->one();

                if(empty($arrival_model)){
                    //无数据就新增
                    $arrival_model                    = new ArrivalRecord();
                }

                $arrival_model->purchase_order_no = $v['pur_number'];
                $arrival_model->sku               = $v['sku'];
                $arrival_model->delivery_qty      = $v['arrival_quantity'];
                $arrival_model->delivery_time     = isset($v['delivery_date']) ? $v['delivery_date'] : date('Y-m-d H:i:s',time());
                $arrival_model->delivery_user     = isset($v['delivery_user']) ? $v['delivery_user'] : '未知';
                $arrival_model->bad_products_qty  = isset($v['nogoods']) ? $v['nogoods'] : 0;
                $arrival_model->check_time        = isset($v['instock_date']) ? $v['instock_date'] : '';
                $arrival_model->check_user        = isset($v['check_user']) ? $v['check_user'] : '';
                $arrival_model->cdate             = time();
                $arrival_model->express_no        = $v['express_no'];
                $arrival_model->qc_id             = $qc_id;
                $arrival_model->name              = PurchaseOrderItems::find()->select('name')->where(['pur_number' => $v['pur_number'], 'sku' => $v['sku']])->scalar();
                $status =$arrival_model->save(false);

                $purchase_quantity = PurchaseOrderItems::find()->where(['pur_number'=>$v['pur_number']])->sum('ctq');

                $arrivalTotal = ArrivalRecord::find()->where(['purchase_order_no'=>$v['pur_number'],'sku'=>$v['sku']])->sum('ifnull(delivery_qty,0)');
                $badGoodsTotal = ArrivalRecord::find()->where(['purchase_order_no'=>$v['pur_number'],'sku'=>$v['sku']])->sum('ifnull(bad_products_qty,0)');
                //保存入库记录
                $model->sku                 = $v['sku'];
                $model->express_no          = $v['express_no'];
                $model->receipt_number      = isset($v['receipt_number'])?$v['receipt_number']:'';
                $model->purchase_quantity   = $v['purchase_quantity'];
                $model->have_sent_quantity  = ($arrivalTotal - $v['purchase_quantity'])>0 ? ($arrivalTotal - $v['purchase_quantity']) :0;
                $model->arrival_quantity    = $arrivalTotal;
                $model->nogoods             = $badGoodsTotal;
                $model->pur_number          = $v['pur_number'];
                $model->instock_qty_count   = isset($v['instock_qty_count']) ? $v['instock_qty_count']:0;
                $model->instock_user        = isset($v['instock_user'])?$v['instock_user']:'未知';
                $instockDate='';
                if(!$model->isNewRecord){
                    if(isset($v['instock_date'])&&!empty($v['instock_date'])){
                        if(empty($model->intock_date)||strtotime($model->instock_date)<=strtotime($v['instock_date'])){
                            $instockDate = $v['instock_date'];
                        }else{
                            $instockDate = $model->instock_date;
                        }
                    }
                }else{
                    $instockDate = isset($v['instock_date']) ? $v['instock_date'] : '';
                }
                $existInstockTime = $model->isNewRecord ? '' :$model->instock_date;
                $model->instock_date        = $instockDate;
                $model->instock_platform    = isset($v['instock_platform'])?$v['instock_platform']:'';
                $status =$model->save(false);
                //按采购统计采购数量与收到的数量
                $items = PurchaseOrderItems::find()->where(['pur_number'=>$v['pur_number'],'sku'=>$v['sku']])->one();
                $rqy  = WarehouseResults::find()->where(['pur_number'=>$v['pur_number'],'sku'=>$v['sku']])->sum('arrival_quantity');
                if($items)
                {
                    $items->rqy=$arrivalTotal<=0 ? 0 :$arrivalTotal;
                    $items->cty=isset($v['instock_qty_count']) ? $v['instock_qty_count']:0;
                    $items->save(false);
                }

                //海外仓新版-修改需求对应的入库数量和状态
                $demandStatus = self::saveDemandInstock($v);

                //同步时间
                PurchaseEstimatedTime::saveArrivalDate($v['sku'],$v['pur_number'], isset($v['instock_date'])?$v['instock_date']:'', true);
                //修改采购到货状态
                PurchaseOrder::updateArrivalStatus($v['pur_number']);

                //更新采购单里面的状态入库一次调整一次采购单状态
                if(!empty($existInstockTime)&&(empty($v['instock_date'])||strtotime($v['instock_date'])<strtotime($existInstockTime))){

                }else{
                    PurchaseOrder::updateOrderStatus($v['pur_number']);
                }

                $status = ($status==true && $demandStatus==true)?true:false;
            } else{

            }

            $transaction->commit(); 
            return $status;
        } catch (Exception $e) {

            $transaction->rollBack();
            return false;
        }
    }
    /**
     * 海外仓新版-修改需求对应的入库数量和状态
     */
    public static function saveDemandInstock($data)
    {
        $status = true;
        $instock_platform = $data['instock_platform'];
        if (isset($instock_platform) && !empty($instock_platform)){
            $instock_date        = isset($data['instock_date'])?$data['instock_date']:'';
            $instock_platform = json_decode($instock_platform);
            foreach ($instock_platform as $k => $v) {
                $summaryModel = PlatformSummary::find()->where(['demand_number'=>$k])->andWhere(['>','agree_time','2018-08-29 10:00:00'])->one();

                if (empty($summaryModel)) continue; //如果不是当前的sku 或 入库数量-采购数量小于零  或 状态为等待到货之前的状态，则跳过
                if ( $summaryModel->demand_status<7 || $summaryModel->demand_status>11 ) {
                    $status = false;
                    continue;
                }

                $demand_cancel_ctq = PurchaseOrderCancelSub::getCancelCtq($data['pur_number'],$data['sku'],$k);//海外仓-取消数量
                $cty = $v; //入库数量
                $demand_status = $summaryModel->demand_status;

                if ($cty<=0) {//入库数量==0  需求状态为等待到货
                    $demand_status = 7;
                } elseif ($summaryModel->purchase_quantity - $cty - $demand_cancel_ctq>0 ) {//需求数量-取消数量-入库>0  部分到货等待剩余
                    $demand_status = 10;
                } elseif ( $summaryModel->purchase_quantity - $cty - $demand_cancel_ctq<=0) {//需求数量-取消数量-入库数量 <=0 全到货
                    $demand_status = ($demand_cancel_ctq==0)?9:11;
                }
                $summaryModel->rqy = $v; //到货数量
                $summaryModel->cty = $v; //入库数量
                $summaryModel->instock_date = $instock_date; //入库时间
                $summaryModel->demand_status = $demand_status; //需求状态
                $status = $summaryModel->save();
            }
        } 
        return $status;
    }
}
