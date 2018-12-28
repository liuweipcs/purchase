<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use app\config\Vhelper;
use yii\bootstrap\Modal;

$this->title = '采购订单统计';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="purchase-order-statistics-index">
    <?= $this->render('_search', ['model' => $searchModel,'view'=>'index']); ?>
    <p class="clearfix"></p>
    <div>
        <?= Html::button('导出Excel',['class' => 'btn btn-success','id'=>'export-csv']) ?>
    </div>
    <div >
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            //'filterModel' => $searchModel,
            "options" => ["class" => "grid-view","style"=>"overflow:auto", "id" => "grid"],
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
                    'label'=>'用户类型',
                    'value'=>function($model){
                        $cn_name = '';
                        if($model->purchase_type == 1){
                            $cn_name = '国内仓';
                        }elseif ($model->purchase_type == 2){
                            $cn_name = '海外仓';
                        }elseif ($model->purchase_type == 3){
                            $cn_name = 'FBA';
                        }
                        return $cn_name;
                    }
                ],
                [
                    'label'=>'sku',
                    'attribute' => 'sku',
                    'format' => 'raw',
                    'value'=>function($model){
                        return Html::a($model->sku, ['/purchase-order-statistics/history', 'sku' => $model->sku ,'purchase_type'=>$model->purchase_type], [
                            'title' => Yii::t('app', '编辑'),
                            'class' => 'edit',
                            'data-toggle' => 'modal',
                            'data-target' => '#create-modal',
                        ]);
                    },
                ],
                [
                    'label'=>'供应商名称',
                    'attribute' => 'supplier_name',
                    'format' => 'raw',
                    'value'=>function($model){
                        return isset($model->supplier_name)?$model->supplier_name:'';
                    },
                ],
                [
                    'label'=>'产品名称',
                    'value'=>function($model){
                        return isset($model->name)?$model->name:'';
                    },
                ],
                [
                    'label'=>'最新报价',
                    'value'=>function($model){
                        return isset($model->price)?$model->price:0;
                    },
                ],
                [
                    'label'=>'采购数量',
                    'attribute' => 'ctq',
                    'format' => 'raw',
                    'value'=>function($model){
                        //return isset($model->ctq)?$model->ctq:0;
                        return \app\controllers\PurchaseOrderStatisticsController::countCtq($model->sku,$model->purchase_type);
                    },
                ],
                [
                    'label'=>'开票点',
                    'attribute' => 'taxes',
                    'format' => 'raw',
                    'value'=>function($model){
                        return \app\models\PurchaseOrderTaxes::getABDTaxes($model->sku,$model->pur_number).'%';
                    },
                ],
                [
                    'label'=>'采购金额',
                    'format' => 'raw',
                    'value'=>function($model){
                        /*$type = Vhelper::getNumber($model->pur_number);
                        if($model->is_drawback == 2 && $type != 3){//税金税金税金
                            $rate = \app\models\PurchaseOrderTaxes::getABDTaxes($model->sku,$model->pur_number);
                            $tax = bcadd(bcdiv($rate,100,2),1,2);
                            $pay  = round($tax*$model->price*$model->ctq,2);//数量*单价*(1+税点)
                        }else{
                            $pay = round($model->price*$model->ctq,2);
                        }
                        return $pay;*/
                        return \app\controllers\PurchaseOrderStatisticsController::countPrice($model->sku,$model->purchase_type);
                    },
                ],
                [
                    'label'=>'采购次数',
                    'format' => 'raw',
                    'value'=>function($model){
                        return \app\models\PurchaseOrderItems::getQuotes($model->sku,$model->purchase_type);
                    },
                ],
                [
                    'label'=>'结算方式',
                    'value'=>function($model){
                        return $model->account_type ? \app\services\SupplierServices::getSettlementMethod($model->account_type) : '';
                    },
                ],
                [
                    'label'=>'产品分类',
                    'value'=>function($model){
                        return !empty($model->product_category_id)?\app\services\BaseServices::getCategory($model->product_category_id):'';
                    },
                ],
            ],
            'containerOptions' => ["style"=>"overflow:auto"], // only set when $responsive = false
            'toolbar' =>  [

                //'{export}',
            ],


            'pjax' => false,
            'bordered' => true,
            'striped' => false,
            'condensed' => true,
            'responsive' => true,
            'hover' => false,
            'floatHeader' => false,
            'showPageSummary' => false,

            'exportConfig' => [
                GridView::EXCEL => [],
            ],
            'panel' => [
                //'heading'=>'<h3 class="panel-title"><i class="glyphicon glyphicon-globe"></i> Countries</h3>',
                'type'=>'success',
                //'before'=>false,
                //'after'=>false,
                //'after'=>Html::a('<i class="glyphicon glyphicon-repeat"></i> 刷新', ['index'], ['class' => 'btn btn-info']),
                //'footer'=>true
            ],
        ]);?>
    </div>
</div>
<?php
Modal::begin([
    'id' => 'create-modal',
    'header' => '<h4 class="modal-title">系统信息</h4>',
    'footer' => '<a href="#" class="btn btn-primary closes" data-dismiss="modal">关闭</a>',
    'size'=>'modal-lg',
    'options'=>[
        'data-backdrop'=>'static',//点击空白处不关闭弹窗

    ],
]);
Modal::end();
$js = <<<JS
     $(function(){
            $("#export-csv").click(function(){
                var purchase_type = $("#purchaseordersearch-purchase_type :selected").val();
                var start_time = $("input[name='PurchaseOrderSearch[start_time]']").val();
                var end_time = $("input[name='PurchaseOrderSearch[end_time]']").val();
                window.location.href='/purchase-order-statistics/export-csv?purchase_type='+purchase_type+'&start_time='+start_time+'&end_time='+end_time;
            });
        });

    // 查看历史数据
    $(document).on('click', '.edit', function () {
        $('.modal-body').html('');
        $.get($(this).attr('href'), {},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });

JS;
$this->registerJs($js);
?>
