<?php

namespace app\controllers;

use app\config\Vhelper;
use app\models\ArrivalRecord;
use app\models\PurchaseOrder;
use app\models\WarehouseResults;
use m35\thecsv\theCsv;
use Yii;
use app\models\PurchaseEstimatedTime;
use app\models\PurchaseOrderAccount;
use app\models\PurchaseOrderItems;
use app\models\PurchaseOrderSearch;
use app\models\PurchaseSuggestNote;
use yii\filters\VerbFilter;

class PurchaseOrderFollowGoodsController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }
    public function actionIndex()
    {
//        VerbFilter::className(1);
        $searchModel = new PurchaseOrderSearch();
        $dataProvider = $searchModel->search9(\Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    /**
     * 未处理原因
     * @return string
     */
    public function actionUpdateFollowGoodsNote($data=null)
    {
        if (empty($data)) {
            $data = \Yii::$app->request->get();
        }
        return PurchaseEstimatedTime::updateNote($data);
    }

    public function actionGetPlatformDetail(){
        if(Yii::$app->request->isAjax && Yii::$app->request->isGet){
            $sku    = Yii::$app->request->getQueryParam('sku');
            $pur_number = Yii::$app->request->getQueryParam('pur_number');
            $arrive = ArrivalRecord::find()->where(['sku'=>$sku,'purchase_order_no'=>$pur_number])->asArray()->all();
            $instock = WarehouseResults::find()->where(['sku'=>$sku,'pur_number'=>$pur_number])->asArray()->all();
            return $this->renderAjax('detail', ['arrive' => $arrive,'stock'=>$instock]);
        }
    }

    public function actionExport(){
        set_time_limit(0);
        $searchParams = Yii::$app->session->get('purchaseOrderFollowSearch');
        $model = new PurchaseOrderSearch();
        $query = $model->search9($searchParams,true);
        $datas = $query->all();
        $table = [
            'PO生成日期',
            'SKU',
            '中文名称',
            'PO号',
            '结算方式',
            '采购员',
            '供应商名称',
            '采购数量',
            '待入库数量',
            '已入库数量',
            '预警状态',
            '预计到货时间',
            '审核通过时间',
            '申请付款时间',
            '付款时间',
            '物流信息',
            '上架时间',
            '备注',
        ];

        $table_head = [];
        foreach($datas as $k=>$v)
        {
            $create_time = PurchaseOrder::getAuditTime($v->pur_number);
            $table_head[$k][] = date('Y-m-d', strtotime($create_time));
            $table_head[$k][] = $v->sku;
            $table_head[$k][] = $v->name;
            $table_head[$k][] = $v->pur_number;
            $table_head[$k][] = \app\services\SupplierServices::getSettlementMethod($v->purNumber->account_type);
            $table_head[$k][] = PurchaseOrder::getBuyer($v->pur_number);
            $table_head[$k][] = PurchaseOrder::getSupplierName($v->pur_number);
            $table_head[$k][] = $v->ctq;
            $table_head[$k][] = !empty($v->cty) ? $v->ctq-$v->cty : $v->ctq;
            $table_head[$k][] = empty($v->cty) ? 0 : $v->cty;
            $html = '';
            if(!empty($v->warnStatus)){
                foreach ($v->warnStatus as $s){
                    $html.= \app\services\PurchaseOrderServices::getEarlyWarningStatus($s->warn_status)."\r\n";
                }
            }
            $table_head[$k][] = $html;
            $table_head[$k][] = !empty($v->purNumber) ? $v->purNumber->date_eta : '';
            $table_head[$k][] = !empty($v->purNumber) ? $v->purNumber->audit_time : '';
            $applyTime = \app\models\PurchaseOrderPay::find()
                ->select('application_time,payer_time')
                ->where(['pur_number'=>$v->pur_number])
                ->andWhere(['<>','pay_status',0])
                ->orderBy('id ASC')
                ->asArray()->one();
            $table_head[$k][] = $applyTime ? $applyTime['application_time'] : '';
            $table_head[$k][] = $applyTime ? $applyTime['payer_time'] : '';
            $data='';
            if(!empty($v->purNumber->fbaOrderShip)){
                foreach($v->purNumber->fbaOrderShip as $value) {
                    if(!empty($value['cargo_company_id'])) {
                        $data .= $value['express_no']."\r\n";   //主要通过此种方式实现
                    }
                }
            }
            $shtml = '物流编码:'.$data;
            $shtml .= '签收时间:'.\app\models\ArrivalRecord::getDeliveryTime($v->pur_number,$v->sku,"\r\n");
            $table_head[$k][] = $shtml;
            $data = \app\models\WarehouseResults::find()->select('instock_date')->where(['pur_number'=>$v->pur_number,'sku'=>$v->sku])->one();
            $table_head[$k][] =empty($data)?'':$data->instock_date;
            $table_head[$k][] =!empty($v->purchaseEstimatedTime)?$v->purchaseEstimatedTime->note:'';
        }
        theCsv::export([
            'header' =>$table,
            'data' => $table_head,
        ]);
    }

    public function actionOverseaIndex(){
        $searchModel = new PurchaseOrderSearch();
        $dataProvider = $searchModel->search14(\Yii::$app->request->queryParams);
        return $this->render('oversea-index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionOverseaExport(){
        set_time_limit(0);
        $searchParams = Yii::$app->session->get('purchaseOrderOverFollowSearch');
        $model = new PurchaseOrderSearch();
        $query = $model->search14($searchParams,true);
        $datas = $query->all();
        $table = [
            'PO生成日期',
            'SKU',
            '中文名称',
            'PO号',
            '中转仓库',
            '采购仓库',
            '结算方式',
            '付款状态',
            '采购员',
            '供应商名称',
            '采购数量',
            '待入库数量',
            '已入库数量',
            '预警状态',
            '预计到货时间',
            '审核通过时间',
            '申请付款时间',
            '付款时间',
            '物流信息',
            '上架时间',
            '权均交期',
            '权均交期剩余时间',
            '备注',
        ];

        $table_head = [];
        foreach($datas as $k=>$v)
        {
            $create_time = PurchaseOrder::getAuditTime($v->pur_number);
            $table_head[$k][] = date('Y-m-d', strtotime($create_time));
            $table_head[$k][] = $v->sku;
            $table_head[$k][] = $v->name;
            $table_head[$k][] = $v->pur_number;
            $table_head[$k][] = $v->purNumber ? \app\services\PurchaseOrderServices::getWarehouseCode($v->purNumber->transit_warehouse) : '';
            $table_head[$k][] = $v->purNumber ? \app\services\PurchaseOrderServices::getWarehouseCode($v->purNumber->warehouse_code) : '';
            $table_head[$k][] = \app\services\SupplierServices::getSettlementMethod($v->purNumber->account_type);
            $state = \app\models\PurchaseOrderPay::getOrderPayStatus($v->pur_number);
            if(!$state) {
                $payhtml =  \app\services\PurchaseOrderServices::getPayStatus(1)."\r\n";
            } else {
                $payhtml = '';
                foreach($state as $pv) {
                    $payhtml .= \app\services\PurchaseOrderServices::getPayStatus($pv['pay_status'])."\r\n";
                }
            }
            $ratio = \app\models\PurchaseOrderPayType::find()->select('settlement_ratio')->where(['pur_number'=>$v->pur_number])->scalar();
            $payhtml.= '结算比例:'.$ratio;
            $table_head[$k][] = $payhtml;
            $table_head[$k][] = PurchaseOrder::getBuyer($v->pur_number);
            $table_head[$k][] = PurchaseOrder::getSupplierName($v->pur_number);
            $table_head[$k][] = $v->ctq;
            $table_head[$k][] = !empty($v->cty) ? $v->ctq-$v->cty : $v->ctq;
            $table_head[$k][] = empty($v->cty) ? 0 : $v->cty;
            $html = '';
            if(!empty($v->warnStatus)){
                foreach ($v->warnStatus as $s){
                    $html.= \app\services\PurchaseOrderServices::getEarlyWarningStatus($s->warn_status)."\r\n";
                }
            }
            $table_head[$k][] = $html;
            $table_head[$k][] = !empty($v->purNumber) ? $v->purNumber->date_eta : '';
            $table_head[$k][] = !empty($v->purNumber) ? $v->purNumber->audit_time : '';
            $applyTime = \app\models\PurchaseOrderPay::find()
                ->select('application_time,payer_time')
                ->where(['pur_number'=>$v->pur_number])
                ->andWhere(['<>','pay_status',0])
                ->orderBy('id ASC')
                ->asArray()->one();
            $table_head[$k][] = $applyTime ? $applyTime['application_time'] : '';
            $table_head[$k][] = $applyTime ? $applyTime['payer_time'] : '';
            $data='';
            if(!empty($v->purNumber->fbaOrderShip)){
                foreach($v->purNumber->fbaOrderShip as $value) {
                    if(!empty($value['cargo_company_id'])) {
                        $data .= $value['express_no']."\r\n";   //主要通过此种方式实现
                    }
                }
            }
            $shtml = '物流编码:'.$data;
            $shtml .= '签收时间:'.\app\models\ArrivalRecord::getDeliveryTime($v->pur_number,$v->sku,"\r\n");
            $table_head[$k][] = $shtml;
            $data = \app\models\WarehouseResults::find()->select('instock_date')->where(['pur_number'=>$v->pur_number,'sku'=>$v->sku])->one();
            $table_head[$k][] =empty($data)?'':$data->instock_date;
            $arrivalAvg = \app\models\HwcAvgDeliveryTime::find()->select('delivery_total,purchase_time')->where(['sku'=>$v->sku])->asArray()->one();
            $table_head[$k][] =$arrivalAvg&&$arrivalAvg['purchase_time']!=0 ? printf('%.2f',$arrivalAvg['delivery_total']/($arrivalAvg['purchase_time']*60*60)).'H' : 0;
            $audit = $v->purNumber->audit_time;
            $avgtime = $arrivalAvg&&$arrivalAvg['purchase_time']!=0 ? $arrivalAvg['delivery_total']/($arrivalAvg['purchase_time']) : 0;
            $table_head[$k][] =sprintf('%.2f',($avgtime-(time()-strtotime($audit)))/(60*60)).'H';
            $table_head[$k][] =!empty($v->purchaseEstimatedTime)?$v->purchaseEstimatedTime->note:'';
        }
        theCsv::export([
            'header' =>$table,
            'data' => $table_head,
        ]);
    }
}
