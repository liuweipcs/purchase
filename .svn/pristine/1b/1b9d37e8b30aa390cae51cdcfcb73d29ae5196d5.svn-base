<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use app\config\Vhelper;
use app\services\BaseServices;
use app\services\SupplierGoodsServices;
use mdm\admin\components\Helper;
use yii\helpers\Url;
use yii\bootstrap\Modal;
/* @var $this yii\web\View */
/* @var $searchModel app\models\ProductSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '异常节点管控');
$this->params['breadcrumbs'][] = $this->title;
?>

    <div class="product-index">
    <div class="panel-body">
        <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>
        <?php
        if(Helper::checkRoute('lack-goods/excep-export'))
        {
            echo Html::a('导出', ['lack-goods/excep-export'], ['class' => 'btn btn-info']);
        }
        ?>
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
                [
                    'label'=>'SKU',
                    'value'=>function($model){
                        return $model->sku;
                    }
                ],
                [
                    'label' => '仓库',
                    'value'=> function($model){
                        return !empty($model->warehouse)? $model->warehouse->warehouse_name : $model->warehouse_code;
                    }
                ],
                [
                    'label' => '在途库存',
                    'value'=> function($model){
                        return !empty($model->stock) ? $model->szStock->on_way_stock :0;
                    }
                ],
                [
                    'label'=>'可用库存',
                    'value'=>function($model){
                        return !empty($model->stock) ? $model->szStock->available_stock :0;
                    }
                ],
                [
                    'label' => '缺货数量',
                    'value'=> function($model){
                        //return $model->left_stock;

                        return Html::a($model->left_stock, 'javascript:;', ['class' => 'platform','id'=>$model->sku,'is_show'=>0,'warehouse'=>$model->warehouse_code]);
                    },
                    'format' => 'raw'
                ],
                [
                    'label'=>'采购员',
                    'value'=>function($model){
                        return !empty($model->buyer) ? $model->buyer->buyer :'';
                    }
                ],
                [
                    'label' => '缺货开始时间',
                    'attribute'=>'earlest_outofstock_date',
                    'value'=> function($model){
                        return $model->earlest_outofstock_date;
                    }
                ],
                [
                    'label'=>'缺货时间',
                    'value'=>function($model){
                        return round(((time()-strtotime($model->earlest_outofstock_date))/(60*60)),2).'H';
                    }
                ]
            ],

            //'containerOptions' => ["style"=>"overflow:auto"], // only set when $responsive = false
            'toolbar' =>  [

                //'{export}',
            ],

            'pjax' => false,
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
<?php
Modal::begin([
    'id' => 'created-modal',
    //'header' => '<h4 class="modal-title">系统信息</h4>',
    //'footer' => '<a href="#" class="btn btn-primary"  data-dismiss="modal"  >Close</a>',
    'closeButton' =>false,
    'size'=>'modal-lg',
    'options'=>[
        'z-index' =>'-1',

    ],
]);
Modal::end();
?>
<?php
$js = <<<JS
    $(document).on('click', '.platform', function () {
        var sku = $(this).attr('id');
        var is_show = $(this).attr('is_show');
        var warehouse_code = $(this).attr('warehouse');
        $.get('/lack-goods/platform', {sku:sku,warehouse:warehouse_code},
            function (data) {
                if(is_show == 1){
                    $("#platform_"+sku).empty();
                    $("a[id='"+sku+"'][warehouse='"+warehouse_code+"']").attr('is_show',0);
                }else{
                    $("a[id='"+sku+"'][warehouse='"+warehouse_code+"']").after("<span id='platform_"+sku+"'>"+data+'</span>');
                    $("a[id='"+sku+"'][warehouse='"+warehouse_code+"']").attr('is_show',1);
                }
            }
        );
    });   
JS;
$this->registerJs($js);
?>