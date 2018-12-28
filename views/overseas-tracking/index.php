<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use app\services\PurchaseOrderServices;
use app\models\PurchaseOrderItems;
use app\services\BaseServices;


/* @var $this yii\web\View */
/* @var $searchModel app\models\OverseasWarehouseGoodsTaxRebateSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '开发跟踪');
$this->params['breadcrumbs'][] = $this->title;
?>
<style type="text/css">
    /*.overseas-warehouse-goods-tax-rebate-index{width: 10px; height: 1px;!important;}*/
</style>

<div class="overseas-warehouse-goods-tax-rebate-index">
    <p class="clearfix"></p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        'pager'=>[
            //'options'=>['class'=>'hidden']//关闭分页
            'firstPageLabel'=>"首页",
            'prevPageLabel'=>'上一页',
            'nextPageLabel'=>'下一页',
            'lastPageLabel'=>'末页',
        ],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => '产品开发日期',
                "format" => "raw",
                'contentOptions' => ['style'=>'color:#8A2BE2;'],
                'value'=> function($model){
                    return "开发日期:{$model->kf_create_time}<br />开发人：{$model->kf_user}";
                },
            ],
            [
                'attribute' => '审核日期',
                "format" => "raw",
                'contentOptions' => ['style'=>'color:#8A2BE2;'],
                'value'=> function($model){
                    if(empty($model->kf_audit_status)) {
                        return false;
                    } else {
                        $status = ($model->kf_audit_status == 1) ? '同意' : '驳回';
                        return "审核状态:{$status}<br />审核日期:{$model->kf_audit_time}<br />审核人：{$model->kf_audit_user}";
                    }

                },
            ],
            [
                'attribute' => '质检日期',
                "format" => "raw",
                'contentOptions' => ['style'=>'color:#8A2BE2;'],
                'value'=> function($model){
                    if(empty($model->kf_zhijian_status)) {
                        return false;
                    } else {
                        $status = ($model->kf_zhijian_status == 1) ? '同意' : '驳回';
                        return "质检状态:{$status}<br />质检日期:{$model->kf_zhijian_time}<br />质检人：{$model->kf_zhijian_user}";
                    }
                },
            ],
            [
                'attribute' => '销售建单日期',
                "format" => "raw",
                'contentOptions' => ['style'=>'color:#4876FF;'],
                'value'=> function($model){
                    if(empty($model->cg_xiaoshou_time)) {
                        return false;
                    } else {
                        return "创建日期:{$model->cg_xiaoshou_time}<br />创建人：{$model->cg_xiaoshou_user}";
                    }
                },
            ],
            [
                'attribute' => '销售审核通过日期',
                "format" => "raw",
                'contentOptions' => ['style'=>'color:#4876FF;'],
                'value'=> function($model){
                    if(empty($model->cg_xiaoshou_audit_time)) {
                        return false;
                    } else {
                        if (empty($model->cg_xiaoshou_audit_status)) {
                            $status = '';
                        } else {
                            $status = Yii::$app->params['demand'][$model->cg_xiaoshou_audit_status];
                        }
                        return "审核状态:{$status}<br />确认日期:{$model->cg_xiaoshou_audit_time}<br />操作人：{$model->cg_xiaoshou_audit_user}";
                    }

                },
            ],
            [
                'attribute' => '采购建议生成日期',
                "format" => "raw",
                'contentOptions' => ['style'=>'color:#4876FF;'],
                'value'=> function($model){
                    if(empty($model->cg_suggest_time)) {
                        return false;
                    } else {
                        return "确认日期:{$model->cg_suggest_time}<br />确认人：{$model->cg_suggest_user}";
                    }
                },
            ],
            [
                'attribute' => '采购审核通过日期',
                "format" => "raw",
                'contentOptions' => ['style'=>'color:#4876FF;'],
                'value'=> function($model){
                    if(empty($model->cg_audit_time)) {
                        return false;
                    } else {
                        if (empty($model->cg_audit_status)) {
                            $status = '';
                        } else {
                            $status = PurchaseOrderServices::getPurchaseStatus($model->cg_audit_status);
                        }
                        return "审核状态:{$status}<br />审核日期:{$model->cg_audit_time}<br />审核人：{$model->cg_audit_user}";
                    }

                },
            ],
            [
                'attribute' => '申请付款日期',
                "format" => "raw",
                'contentOptions' => ['style'=>'color:#4876FF;'],
                'value'=> function($model){
                    if(empty($model->cg_shenqing_pay_time)) {
                        return false;
                    } else {
                        return "申请日期:{$model->cg_shenqing_pay_time}<br />申请人：{$model->cg_shenqing_pay_user}";
                    }

                },
            ],
            [
                'attribute' => '财务付款日期',
                "format" => "raw",
                'contentOptions' => ['style'=>'color:#4876FF;'],
                'value'=> function($model){
                    if(empty($model->cg_caiwu_pay_time)) {
                        return false;
                    } else {
                        if (empty($model->cg_audit_status)) {
                            $status = '';
                        } else {
                            $status = PurchaseOrderServices::getPayStatus($model->cg_caiwu_pay_status);
                        }
                        return "审核状态:{$status}<br />审核日期:{$model->cg_caiwu_pay_time}<br />审核人：{$model->cg_caiwu_pay_user}";
                    }
                },
            ],
//             'cg_caiwu_audit_time',
//             'cg_caiwu_audit_user',
            [
                'attribute' => '采购到货日期',
                "format" => "raw",
                'contentOptions' => ['style'=>'color:##32CD32;'],
                'value'=> function($model){
                    if(empty($model->wms_daohuo_time)) {
                        return false;
                    } else {
                        if (empty($model->cg_audit_status)) {
                            $status = '';
                        } else {
                            $status = PurchaseOrderServices::getDaoHuo($model->wms_daohuo_status); //??
                        }
                        return "到货状态:{$status}<br />到货日期:{$model->wms_daohuo_time}<br />点货人：{$model->wms_daohuo_user}";
                    }

                },
            ],
            [
                'attribute' => '仓库拆包质检日期',
                "format" => "raw",
                'contentOptions' => ['style'=>'color:##32CD32;'],
                'value'=> function($model){
                    if(empty($model->wms_zhijian_time)) {
                        return false;
                    } else {
                        return "质检日期:{$model->wms_zhijian_time}<br />质检人：{$model->wms_zhijian_user}";
                    }
                },
            ],
            [
                'attribute' => '仓库入库日期',
                "format" => "raw",
                'contentOptions' => ['style'=>'color:##32CD32;'],
                'value'=> function($model){
                    if(empty($model->wms_ruku_time)) {
                        return false;
                    } else {
                        return "入库日期:{$model->wms_ruku_time}<br />操作人：{$model->wms_ruku_user}";
                    }
                },
            ],
            [
                'attribute' => '仓库发货日期',
                "format" => "raw",
                'contentOptions' => ['style'=>'color:##32CD32;'],
                'value'=> function($model){
                    if(empty($model->wms_fahuo_time)) {
                        return false;
                    } else {
                        return "发货日期:{$model->wms_fahuo_time}<br />发货人：{$model->wms_fahuo_user}";
                    }
                },
            ],
            [
                'attribute' => '创建备货单日期',
                "format" => "raw",
                'contentOptions' => ['style'=>'color:##32CD32;'],
                'value'=> function($model){
                    if(empty($model->wms_beihuo_time)) {
                        return false;
                    } else {
                        return "创建日期:{$model->wms_beihuo_time}<br />创建人：{$model->wms_beihuo_user}";
                    }
                },
            ],
            [
                'attribute' => '物流组审核日期',
                "format" => "raw",
                'contentOptions' => ['style'=>'color:##32CD32;'],
                'value'=> function($model){
                    if(empty($model->wms_audit_time)) {
                        return false;
                    } else {
                        return "审核日期:{$model->wms_audit_time}<br />审核人：{$model->wms_audit_user}";
                    }
                },
            ],
            [
                'attribute' => '仓库拣货日期',
                "format" => "raw",
                'contentOptions' => ['style'=>'color:##32CD32;'],
                'value'=> function($model){
                    if(empty($model->wms_jianhuo_time)) {
                        return false;
                    } else {
                        return "拣货日期:{$model->wms_jianhuo_time}<br />拣货人：{$model->wms_jianhuo_user}";
                    }
                },
            ],
            [
                'attribute' => '物流商验货日期',
                "format" => "raw",
                'contentOptions' => ['style'=>'color:#32CD32;'],
                'value'=> function($model){
                    if(empty($model->wl_yanhuo_time)) {
                        return false;
                    } else {
                        return "验货日期:{$model->wl_yanhuo_time}";
                    }
                },
            ],
            [
                'attribute' => '海外仓上架时间',
                "format" => "raw",
                //'visible' => !empty($model->wl_shangjia_time) ? true : false,
                'contentOptions' => ['style'=>'color:#32CD32;'],
                'value'=> function($model){
                    if(empty($model->wl_shangjia_time)) {
                        return false;
                    } else {
                        return "上架日期:{$model->wl_shangjia_time}";
                    }
                },
            ],

        ],

        'containerOptions' => ["style"=>"overflow:auto"], // only set when $responsive = false
        'toolbar' =>  [
        ],

        'pjax' => true,
        'bordered' => true,
        'striped' => false,
        'condensed' => true,
        'responsive' => true,
        'hover' => true,
        'floatHeader' => false,
        'showPageSummary' => false,

        'exportConfig' => [
            GridView::EXCEL => [],
        ],
        'panel' => [
            //'heading'=>'<h3 class="panel-title"><i class="glyphicon glyphicon-globe"></i> Countries</h3>',
            'type'=>'success',
            // 'after'=>Html::a('<i class="glyphicon glyphicon-repeat"></i> 刷新', ['index'], ['class' => 'btn btn-info']),
            //'footer'=>true
        ],

    ]); ?>
</div>