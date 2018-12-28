<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use app\config\Vhelper;
use app\services\BaseServices;
use app\services\SupplierGoodsServices;
use app\models\ProductProvider;
use mdm\admin\components\Helper;
use yii\helpers\Url;
use yii\bootstrap\Modal;
/* @var $this yii\web\View */
/* @var $searchModel app\models\ProductSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '产品列表');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="product-index">

    <h1><?php //echo Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?php
        if(Helper::checkRoute('create'))
        {
            echo Html::a(Yii::t('app', '添加产品'), ['create'], ['class' => 'btn btn-success']);
        }
        ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pager'=>[
            //'options'=>['class'=>'hidden']//关闭分页
            'firstPageLabel'=>"首页",
            'prevPageLabel'=>'上一页',
            'nextPageLabel'=>'下一页',
            'lastPageLabel'=>'末页',
        ],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            /*[
                'attribute'=>'uploadimgs',
                'format'=>'raw',
                'value' => function ($model) {
                    return !empty($model->uploadimgs)?Vhelper::toSkuImg($model->sku,$model->uploadimgs):'';
                }
            ],*/

            [
                'attribute'=>'desc.title',
                'format'=>'raw',
                'value'=> function ($model) {
                    return $model->desc['title'];
                },
            ],
                [
                    'attribute'=>'sku',
                    'format'=>'raw',
                    'value'=> function ($model) {
                        $href = Yii::$app->params['SKU_ERP_Product_Detail'].$model->sku;
                        $html = "<a href=$href target='_blank'>$model->sku</a>";
                        $html .='<br>'.Html::a('<span class="glyphicon glyphicon-stats" style="font-size:10px;color:cornflowerblue;" title="销量库存"></span>', ['product/get-stock-sales','sku'=>$model->sku],
                                [
                                    'class' => 'btn btn-xs stock-sales-purchase',
                                    'data-toggle' => 'modal',
                                    'data-target' => '#created-modal3',
                                ]);
                        $html .=Html::a('<span class="glyphicon glyphicon-eye-open" style="font-size:10px;color:cornflowerblue;" title="历史采购记录"></span>', ['purchase-suggest/histor-purchase-info','sku'=>$model->sku],[
                            'data-toggle' => 'modal',
                            'data-target' => '#created-modal3',
                            'class'=>'btn btn-xs stock-sales-purchase',
                        ]);
                        return $html;
                    },
                ],
            [
                'label'=>'捆绑类型',
                'attribute'=>'product_types',
                'format'=>'raw',
                'value'=> function ($model) {
                    return $model->product_type ==1 ?'普通':'捆绑';
                },
            ],
            [
                'attribute'=>'product_category_id',
                'format'=>'raw',
                'value'=> function ($model) {
                   return $model->cat['category_cn_name'];
                },
                'filter'=>BaseServices::getCategory(),
            ],

            [
                'attribute'=>'product_cn_link',
                'format'=>'raw',
                'value'=> function ($model) {
                    $str=$model->product_cn_link;
                    $url = !empty($str)?Vhelper::toSubStr($str,100,60):'暂无链接';
                    $str=preg_match('/http:\/\/[\w.]+[\w\/]*[\w.]*\??[\w=&\+\%]*/is',$str)?$str:'http://www.1688.com';
                    return "<a href='$str' title='$str' target='_blank'><i class='fa fa-fw fa-internet-explorer'></i></a>";
                }
            ],

            [
                'attribute'=>'product_en_link',
                'format'=>'raw',
                'value'=> function ($model) {
                    $str=$model->product_en_link;
                    $url = !empty($str)?Vhelper::toSubStr($str,100,60):'暂无链接';
                    $str=preg_match('/http:\/\/[\w.]+[\w\/]*[\w.]*\??[\w=&\+\%]*/is',$str)?$str:'http://www.1688.com';
                    return "<a href='$str' title='$str' target='_blank'><i class='fa fa-fw fa-internet-explorer'></i></a>";
                }
            ],

            'create_id',

            [
                'label'=>'开发时间',
                'attribute' => 'create_timea',
                'value'=> function($model){ if($model->create_time){return $model->create_time; }else{ return '';} },
                //'filterType'=>GridView::FILTER_DATETIME ,
            ],

                'product_cost',

            [
                'attribute' => 'product_status',
                'value'=> function($model){
                    return SupplierGoodsServices::getProductStatus()[$model->product_status];
                },
                'filter'=>SupplierGoodsServices::getProductStatus(),
            ],
            [
                'attribute' => 'supply_status',
                'value'=> function($model){
                    return !empty($model->supply_status)?SupplierGoodsServices::getSupplyStatus()[$model->supply_status]:'';
                },
                'filter'=>SupplierGoodsServices::getSupplyStatus(),
            ],
/*'purchase_cost',
            [
                'attribute' => 'supplier_name',
                'value'=> function($model){
                    return !empty($model->supplier_name)?BaseServices::getSupplierName($model->supplier_name):'';
                },

            ],

'supplier_link',
'note',*/
            /*[
                'label' => '默认供应商',
                'value'=> function ($model) {
                    $suppstate=ProductProvider::findOne(['sku'=>$model->sku])['is_supplier'];
                    $num=$suppstate=='1' ? 1 : 0;
                    return Yii::$app->params['boolean'][$num];
                },
            ],*/


            [
                'class' => 'kartik\grid\ActionColumn',
                'dropdown' => false,
                'width'=>'180px',
                'template' => '{edit} {update} {download}',
                'buttons'=>[
                    'edit' => function ($url, $model, $key) {
                        return Html::a('<i class="glyphicon glyphicon-eye-open"></i> 查看', ['view','id'=>$key], [
                            'title' => Yii::t('app', '查看'),
                            'class' => 'btn btn-xs red'
                        ]);
                    },
                    'update' => function ($url, $model, $key) {
                        return Html::a('<i class="glyphicon glyphicon-pencil"></i> 更新', ['update','id'=>$key,'sku'=>$model->sku], [
                            'title' => Yii::t('app', '更新 '),
                            'class' => 'btn btn-xs purple'
                        ]);
                    },
                    'download'=>function($url,$model,$key){
                        return Html::a('<i class="glyphicon glyphicon-refresh"></i>图片重置', ['reload','sku'=>$model->sku], [
                            'title' => Yii::t('app', '图片重置'),
                            'class' => 'btn btn-xs purple'
                        ]);
                    }
                ],

            ],
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
    'id' => 'created-modal3',
    'header' => '<h4 class="modal-title">系统信息</h4>',
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
//$historys         = Url::toRoute(['tong-tool-purchase/get-history']);
$historys         = Url::toRoute(['purchase-suggest/histor-purchase-info']);
$delete         = Url::toRoute(['delete-sku']);
$js = <<<JS
$(document).on('click','.data-updatess', function () {
    $.get('{$historys}', {sku:$(this).attr('sku')},
        function (data) {
            $('#created-modal3').find('.modal-body').html(data);
        }
    );
    
});
$(document).on('click', '.stock-sales-purchase', function () {
        $('.modal-body').html('正在请求数据....');
        $.get($(this).attr('href'), {},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
JS;
$this->registerJs($js);
?>