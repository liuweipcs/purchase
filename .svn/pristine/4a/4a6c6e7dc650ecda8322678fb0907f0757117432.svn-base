<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use app\services\PurchaseOrderServices;
use app\services\SupplierServices;
use app\services\BaseServices;
use app\config\Vhelper;
use mdm\admin\components\Helper;
use app\models\SupplierQuotes;
/* @var $this yii\web\View */
/* @var $searchModel app\models\TodayListSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '采购需求汇总';
$this->params['breadcrumbs'][] = $this->title;
?>
<?=
GridView::widget([
    'dataProvider' => $dataProvider,
    //'filterModel' => $searchModel,
    'options'=>[
        'id'=>'grid_overseas_purchase',
    ],
    'filterSelector' => "select[name='".$dataProvider->getPagination()->pageSizeParam."'],input[name='".$dataProvider->getPagination()->pageParam."']",
    'pager'=>[
        'options'=>['class' => 'pagination','style'=> "display:block;"],
        'class'=>\liyunfang\pager\LinkPager::className(),
        'pageSizeList' => [20, 50, 100, 200],
        'firstPageLabel'=>"首页",
        'prevPageLabel'=>'上一页',
        'nextPageLabel'=>'下一页',
        'lastPageLabel'=>'末页',
    ],
    'columns' => [
        [
            'class' => 'kartik\grid\CheckboxColumn',
            'name'=>"id" ,
            'checkboxOptions' => function ($model, $key, $index, $column) {
                return ['value' => $model->id];
            }
        ],
        'id',
        /*[
            'label'=>'产品图片',
            'attribute'=>'uploadimgs',
            'format'=>'raw',
            'value' => function ($model) {
                return Vhelper::toSkuImg($model->sku,$model->uploadimgs);
            }
        ],*/

        [
            'label'=>'产品信息',
            'attribute' => 'product_name',
            "format" => "raw",
            'value'=>
                function($model){
//                        $title = !empty($model->desc->title) ? $model->desc->title :'';
//                        $data = '产品名：'. $title .'<br/>';
                    $data = '产品名：'.$model->product_name.'<br/>';
                    $data.= $model->product_category?'产品分类：'.BaseServices::getCategory($model->product_category).'<br/>':'产品分类：'.''.'<br/>';
//                        $data.= !empty($model->productCategory['category_cn_name']) ?'产品分类：'.$model->productCategory['category_cn_name'].'<br/>':'产品分类：'. '' .'<br/>';
                    $data.= '<span style="color:red">sku:'.$model->sku.'</span><br/>';
                    $data.= '<span style="color:#00a65a">需求单号:'.$model->demand_number.'</span><br/>';
                    $suppliercode =!empty($model->supplierQuotes['quotes_id'])?SupplierQuotes::getFileds($model->supplierQuotes['quotes_id'],'suppliercode')->suppliercode:'';
                    if(!in_array('产品开发组',array_keys(Yii::$app->authManager->getRolesByUser(Yii::$app->user->id)))) {
                    $data.= !empty($suppliercode)?'<span style="color:#00a65a">供应商:'.BaseServices::getSupplierName($suppliercode).'</span></br>':'<span style="color:#00a65a">供应商:</span></br>';
                    
                    $data.= $model->is_purchase==1?'是否生成采购计划：<span style="color:red">未生成</span><br/>':'是否生成采购计划：<span style="color:#00a65a">已生成</span><br/>';
                    }

                    $data .= '是否生成采购单：';
                    if ($model->create_time > '2018-08-29 10:00:00') {
                        $data .= ( (6<$model->demand_status) && ($model->demand_status<14) ) ? '<span style="color:#00a65a">已生成</span>': '<span style="color:red">未生成</span>';
                    } else {
                        $purNum = (new \yii\db\Query())
                            ->select('o.pur_number')
                            ->from('pur_purchase_demand as d')
                            ->leftJoin('pur_purchase_order as o','d.pur_number = o.pur_number')
                            ->where(['d.demand_number'=>$model->demand_number])
                            ->andwhere(['in','o.purchas_status',['3','5','6','7','8','9','99']])
                            ->all();

                        $data.= empty($purNum) ? '<span style="color:red">未生成</span>':'<span style="color:#00a65a">已生成</span>';
                    }
                    return $data;
                },

        ],
        [
            'attribute' => 'platform_number',
            "format" => "raw",
            'value'=>
                function($model){
                    return  $model->platform_number;
                },

        ],
        [
            'label'=>'数量/金额',
            'attribute' => 'purchase_quantity',
            "format" => "raw",
            'value'=>
                function($model){
                    $data ='采购数：'.'<span style="color:red">'.$model->purchase_quantity.'</span><br/>';
                    $data .='中转数：'.'<span style="color:red">'.$model->transit_number.'</span><br/>';
                    $price = \app\models\ProductProvider::find()
                        ->select('q.supplierprice')
                        ->alias('t')
                        ->where(['t.sku'=>$model->sku])
                        ->andWhere(['t.is_supplier'=>1])
                        ->leftJoin(SupplierQuotes::tableName().' q','t.quotes_id=q.id')
                        ->scalar();
                if(!in_array('产品开发组',array_keys(Yii::$app->authManager->getRolesByUser(Yii::$app->user->id)))) {
                    if($price!==false){
                        $data.='采购单价：'.'<span style="color:red">'.$price.'</span><br/>';
                        $data.='采购金额：'.'<span style="color:red">'.$model->purchase_quantity*$price.'</span>';
                    }
                    }
                    return $data;
                },

        ],
        [
            'attribute' => 'purchase_warehouse',
            "format" => "raw",
            'value'=>
                function($model){
                    return !empty($model->purchase_warehouse) ? BaseServices::getWarehouseCode($model->purchase_warehouse) : $model->purchase_warehouse;
                },

        ],
        [
            'attribute' => 'is_transit',
            "format" => "raw",
            'value'=>
                function($model){
                    return  $model->is_transit==1?'<span style="color:red">否</span>':'<span style="color:#00a65a">是</span>';   //主要通过此种方式实现
                },

        ],
        [
            'attribute' => 'transit_warehouse',
            "format" => "raw",
            'value'=>
                function($model){
                    return  $model->transit_warehouse?BaseServices::getWarehouseCode($model->transit_warehouse):'';
                },

        ],
        [
            'attribute'=>'transport_style',
            'format'=>'raw',
            'value'=>function($model){
                return $model->transport_style ? PurchaseOrderServices::getTransport($model->transport_style) : '';
            },
        ],
        [
            'label'=>'需求信息',
            'attribute' => 'create_id',
            "format" => "raw",
            'value'=>
                function($model){
                    $data = '需求人:'.$model->create_id.'<br/>';
                    $data .='需求时间:'.$model->create_time;
                    return  $data;
                },

        ],

        [
            'attribute' => 'level_audit_status',
            "format" => "raw",
            'value'=>
                function($model){
                    if($model->level_audit_status==1){
                        $str ='';
                        $str .= '<span style="color:#00a65a">'.Yii::$app->params['demand'][$model->level_audit_status].'</span>';
                        return $str;

                    } elseif($model->level_audit_status==2){

                        $str = '<span style="color:red">'.Yii::$app->params['demand'][$model->level_audit_status].'</span><br/>';
                        $str .= '原因：'.$model->audit_note;
                        return $str;

                    } elseif($model->level_audit_status==4){

                        $str = '<span style="color:red">'.Yii::$app->params['demand'][$model->level_audit_status].'</span><br/>';
                        $str .= '原因：'.$model->purchase_note;
                        return $str;

                    }elseif($model->level_audit_status==6){
                        $str = '<span style="color:red">'.Yii::$app->params['demand'][$model->level_audit_status].'</span><br/>';
                        $str .= '原因：'.$model->audit_note;
                        return $str;
                    } elseif($model->level_audit_status == 8){// 需求作废
                        $str = '<span style="color:red">'.Yii::$app->params['demand'][$model->level_audit_status].'</span><br/>';
                        $logs = (new \yii\db\Query())->select('message')
                            ->from(\app\models\DemandLog::tableName())
                            ->where(['demand_number' => $model->demand_number])
                            ->andWhere(['or',["like",'message','作废订单[备注：'],["like",'message','审核作废订单【通过】[']])
                            ->column();
                        if($logs){// 备注存在时显示备注信息
                            $logs = str_replace(['作废订单[备注：','审核作废订单【通过】[审核备注:'],['作废备注[','审核作废备注['],implode(',',$logs));
                            $str .= '原因：'.$logs;
                        }
                        return $str;
                    } else{
                        return  Yii::$app->params['demand'][$model->level_audit_status];
                    }

                },

        ],
        [
            'label'=>'同意(驳回)信息',
            'attribute' => 'agree_user',
            "format" => "raw",
            'value'=>
                function($model){
                    if($model->level_audit_status==4)
                    {
                        $data = '采购驳回人:'.$model->buyer.'<br/>';
                        $data .= '采购驳回时间:'.$model->purchase_time.'<br/>';
                    } else{

                        $data = '同意(驳回)人:'.$model->agree_user.'<br/>';
                        $data .= '同意(驳回)时间:'.$model->agree_time.'<br/>';
                    }

                    if ( !empty($model->update_time)) {
                        $data .= '更新时间:'.$model->update_time;
                    }
                    return  $data;
                },

        ],
        [
            'attribute' => 'sales_note',
            "format" => "raw",
            'value'=>
                function($model){
                    return  $model->sales_note;
                },

        ],
        [
            'label'=>'推送到海外仓',
            'attribute' => 'created_ats',
            "format" => "raw",
            'value'=>
                function($model, $key, $index, $column){
                    return PurchaseOrderServices::getPush($model->is_push);
                },
        ],
        [
            'class' => 'kartik\grid\ActionColumn',
            'dropdown' => false,
            'width'=>'180px',
            'template' => Helper::filterActionColumn('{update}{purchase-disagree}{agree}{disagree}{delete}{update-status}{show-log}'),
            'buttons'=>[
                'update' => function ($url, $model, $key)
                {
                    $arr= ['1','4','5','3'];
                    if(!in_array($model->level_audit_status,$arr)) {
                        return Html::a('<i class="fa fa-fw fa-check"></i>修改', ['update', 'id' => $key], [
                            'title' => Yii::t('app', '修改'),
                            'class' => 'btn btn-xs red'
                        ]);
                    }
                },
                'agree' => function ($url, $model, $key)
                {
                    $arr= ['1','2','4','5','3','6','7'];
                    if(!in_array($model->level_audit_status,$arr)) {
                        return Html::a('<i class="fa fa-fw fa-check"></i>同意', ['agree', 'id' => $key], [
                            'title' => Yii::t('app', '同意'),
                            'class' => 'btn btn-xs red'
                        ]);
                    }
                },
                'disagree' => function ($url, $model, $key)
                {
                    $arr= ['1','2','4','5','3','6','7'];
                    if(!in_array($model->level_audit_status,$arr)) {
                        return Html::a('<i class="fa fa-fw fa-close"></i>驳回', ['disagree', 'id' => $key], [
                            'title'       => Yii::t('app', '驳回'),
                            'class'       => 'btn btn-xs disagree',
                            'data-toggle' => 'modal',
                            'data-target' => '#create-modal',
                        ]);
                    }
                },
                'purchase-disagree' => function ($url, $model, $key)
                {
                    $arr= ['1'];
                    if(in_array($model->level_audit_status,$arr)) {
                        return Html::a('<i class="fa fa-fw fa-close"></i>采购驳回', ['purchase-disagree', 'id' => $key], [
                            'title'       => Yii::t('app', '采购驳回'),
                            'class'       => 'btn btn-xs pdisagree',
                            'data-toggle' => 'modal',
                            'data-target' => '#create-modal',
                        ]);
                    }
                },
                'delete' => function ($url, $model, $key)
                {
                    $arr= ['3'];
                    if(in_array($model->level_audit_status,$arr)) {
                        $page=Yii::$app->request->get('page') ? Yii::$app->request->get('page') : 1;
                        return Html::a('<i class="fa fa-fw fa-close"></i>删除', ['delete', 'id' => $key], [
                            'title'       => Yii::t('app', '删除'),
                            'class'       => 'btn btn-xs pdisagree',

                        ]);
                    }
                },
                'update-status' => function ($url, $model, $key)
                {
                    $arr= ['6'];
                    if(in_array($model->level_audit_status,$arr)) {
                        return Html::a('<i class="glyphicon glyphicon-info-sign"></i>拦截审核', ['update-status', 'id' => $key], [
                            'title' => Yii::t('app', '拦截审核'),
                            'class' => 'btn btn-xs pdisagree',
                            'data' => [
                                'confirm' => '确定跳过拦截规则推送吗?',
                            ]

                        ]);
                    }
                },
                'show-log' => function ($url, $model, $key)
                {
                    return Html::a('<i class="glyphicon glyphicon-info-sign"></i>日志', ['/overseas-purchase-order2/demand-log', 'demand_number' => $model->demand_number], [
                        'title'       => Yii::t('app', '查看日志'),
                        'class'       => 'btn btn-xs pdisagree',
                        'data-toggle' => 'modal',
                        'data-target' => '#create-modal',
                    ]);
                },
            ]

        ],
    ],
    'containerOptions' => ["style"=>"overflow:auto"], // only set when $responsive = false
   // 'toolbar' =>  (!in_array(Yii::$app->user->id,$authData))?['{export}']:[''],


    'pjax' => false,
    'bordered' => true,
    'striped' => false,
    'condensed' => true,
    'responsive' => true,
    'hover' => true,
    'floatHeader' => true,
    'showPageSummary' => false,

    'exportConfig' => [
        GridView::EXCEL => [],
    ],
    'panel' => [
        //'heading'=>'<h3 class="panel-title"><i class="glyphicon glyphicon-globe"></i> Countries</h3>',
        'type'=>'success',
        //'after'=>Html::a('<i class="glyphicon glyphicon-repeat"></i> 刷新', ['index'], ['class' => 'btn btn-info']),
    ],
]); ?>