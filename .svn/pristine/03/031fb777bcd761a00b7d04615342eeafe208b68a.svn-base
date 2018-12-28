<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use app\config\Vhelper;
use app\services\BaseServices;
use app\services\SupplierGoodsServices;
use mdm\admin\components\Helper;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use app\models\Product;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ProductSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '缺货列表');
$this->params['breadcrumbs'][] = $this->title;

?>

    <div class="product-index">
    <div class="panel-body">
        <?php echo $this->render('_excep_search', ['model' => $searchModel]); ?>
    </div>
        <?php
        if(Helper::checkRoute('lack-goods/export'))
        {
            echo Html::a('导出', ['lack-goods/export'], ['class' => 'btn btn-info create-purchase pp']);
        }
        ?>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
           // 'filterModel' => $searchModel,
            'pager'=>[
                //'options'=>['class'=>'hidden']//关闭分页
                'firstPageLabel'=>"首页",
                'prevPageLabel'=>'上一页',
                'nextPageLabel'=>'下一页',
                'lastPageLabel'=>'末页',
            ],
            'columns' => [
                ['class' => 'kartik\grid\CheckboxColumn'],
                [
                    'label'=>'图片',
                    'format'=>'raw',
                    'value'=>function($model){
                        return \toriphes\lazyload\LazyLoad::widget(['src'=>Vhelper::getSkuImage($model->sku)]);
                    }
                ],
                [
                    'label' => '产品名称',
                    'width' => '25%',
                    'value'=> function($model){
                        return !empty($model->productDesc) ? $model->productDesc->title : '';
                    }
                ],
                [
                    'label'=>'默认供应商名称',
                    'format'=>'raw',
                    'value'=>function($model){
                        $productModel = Product::find()->where(['sku' => $model->sku])->one();
                        $supplierName = !empty($productModel->defaultSupplierDetail) ? $productModel->defaultSupplierDetail->supplier_name : '';
                        $supplierCode = !empty($productModel->defaultSupplierDetail) ? $productModel->defaultSupplierDetail->supplier_code : '';
                        $html         = $supplierName;
                        return $html;
                    }
                ],
                [
                    'label' => 'SKU',
                    'value'=> function($model){
                        return $model->sku;
                    }
                ],
                [
                        'label'=>'货源状态',
                        'value'=>function($model){
                            $productStatusModel=\app\models\ProductSourceStatus::find()->select('sourcing_status')->where(['sku'=>$model->sku,'status'=>1])->one();
                            $sourceStatus='未知';
                            if(!empty($productStatusModel)){
                               switch ($productStatusModel['sourcing_status']){
                                   case 1:
                                       $sourceStatus='正常';
                                       break;
                                   case 2:
                                       $sourceStatus='停产';
                                       break;
                                   case 3:
                                       $sourceStatus='断货';
                                       break;
                                   default:
                                       $sourceStatus='未知状态';
                                       break;
                               }
                            }

                            return $sourceStatus;
                        }
                ],
                [
                    'label' => '产品状态',
                    'value'=> function($model){
                        return !empty($model->product)? SupplierGoodsServices::getProductStatus($model->product->product_status) : '';
                    }
                ],
                [
                    'label' => '在途库存',
                    'pageSummary'=>true,
                    'value'=> function($model){
                        return !empty($model->szStock) ? $model->szStock->on_way_stock :0;
                    }
                ],
                [
                    'label'=>'可用库存',
                    'pageSummary'=>true,
                    'value'=>function($model){
                        return !empty($model->szStock) ? $model->szStock->available_stock :0;
                    }
                ],
                [
                    'label'=>'缺货数量',
                    'attribute'=>'left_stock',
                    'pageSummary'=>true,
                    'value'=>function($model){
                        //return $model->left_stock;
                        return Html::a($model->left_stock, 'javascript:;', ['class' => 'platform','id'=>$model->sku,'is_show'=>0]);
                    },
                    'format' => 'raw'
                ],
                [
                    'label'=>'开发员',
                    'value'=>function($model){
                        return !empty($model->product) ? $model->product->create_id : '';
                    }
                ],
                [
                    'label'=>'采购员',
                    'value'=>function($model){
                        return !empty($model->buyer) ? $model->buyer->buyer :'';
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
            'showPageSummary' => true,

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
    $(function(){
        var left_stock = 0;
        $('#w1-container table tr').find('td[data-col-seq="7"]').each(function() {
            var number = $(this).find('a').html();

            left_stock+=parseFloat(number);
        })
        $(".kv-page-summary").find('td').eq(7).html(left_stock);
    })
    $(document).on('click', '.platform', function () {
        var sku = $(this).attr('id');
        var is_show = $(this).attr('is_show');
        var warehouse_code = $(this).attr('warehouse');
        $.get('/lack-goods/platform', {sku:sku,warehouse:warehouse_code},
            function (data) {
                if(is_show == 1){
                    $("#platform_"+sku).empty();
                    $("a[id='"+sku+"']").attr('is_show',0);
                }else{
                    $("a[id='"+sku+"']").after("<span id='platform_"+sku+"'>"+data+'</span>');
                    $("a[id='"+sku+"']").attr('is_show',1);
                }
            }
        );
    });  
JS;
$this->registerJs($js);
?>
