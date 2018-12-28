<?php

use yii\helpers\Html;

use kartik\grid\GridView;
use Yii\helpers\Url;
use yii\bootstrap\Modal;
use app\config\Vhelper;
/* @var $this yii\web\View */
/* @var $searchModel app\models\SupplierGoodsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '供应商产品管理');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="stockin-index">


    <?= $this->render('_search', ['model' => $searchModel]); ?>
    <p class="clearfix"></p>
    <?php Html::a(Yii::t('app', '批量更新默认供应商'), '#', [
        'id' => 'batch',
        'data-toggle' => 'modal',
        'data-target' => '#create-modal',
        'class' => 'btn btn-success',
    ]);?>
    <?= Html::a(Yii::t('app', '添加报价'), '#', [
        'id' => 'creates',
        'data-toggle' => 'modal',
        'data-target' => '#create-modal',
        'class' => 'btn btn-success creates',
    ]);?>
    <?php Html::a(Yii::t('app', '批量添加产品/报价'), '#', [
        'id' => 'product',
       'data-toggle' => 'modal',
       'data-target' => '#create-modal',
        'class' => 'btn btn-success',
    ]);?>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    "options" => ["class" => "grid-view","style"=>"overflow:auto", "id" => "grid"],
    'columns' => [
        //['class' => 'kartik\grid\SerialColumn'],

        [
            'label'=>'id',
            'attribute' => 'ids',
            'format'=>'raw',
            'value'=>
                function($model, $key, $index, $column){
                    return  $model->id;
                },

        ],

        [
            'label'=>'产品',
            'attribute'=>'uploadimgss',
            'format'=>'raw',
            'width' =>'22%',
            'value'=> function ($model, $key, $index, $column) {

                $product = Vhelper::toSkuImg($model->sku,$model->uploadimgs);
                $product.= '<div class="media-body">sku：'.$model->sku."<br/>";
                $product.= !empty($model->desc->title)?'名称：'.$model->desc->title."<br/>":'名称：'."<br/>";
                $product.= '产品状态：'.app\services\SupplierGoodsServices::getProductStatus($model->product_status)."<br/>";
                $product.= '品类：'.app\services\BaseServices::getCategory($model->product_category_id)."<br/>";
                $product.='</div></div>';
                return $product;
            }
        ],
        [
            'label'=>'供应商信息',
            'attribute' => 'skus',
            'format'=>'raw',
            'value'=> function($model){
                $searchModel = new \app\models\SupplierQuotesSearch();
                $searchModel->sku = $model->sku;
                $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
                return Yii::$app->controller->renderPartial('/supplier-goods/index-quotes',[
                   'searchModel'=> $searchModel,
                   'dataProvider'=>$dataProvider,
                   'sku'    =>$model->sku,
               ]);
            },

        ],

        [
            'class' => 'kartik\grid\CheckboxColumn',
            'name'=>"id" ,
            'checkboxOptions' => function ($model, $key, $index, $column) {
                return ['value' => $model->sku];
            }

        ],

    ],
    'containerOptions' => ["style"=>"overflow:auto"], // only set when $responsive = false
    'toolbar' =>  [

        //'{export}',
    ],


    'pjax' => false,
    'bordered' => false,
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
        'heading'=>'<h3 class="panel-title"><i class="glyphicon glyphicon-globe"></i> Countries</h3>',
        'type'=>'success',
        'after'=>Html::a('<i class="glyphicon glyphicon-repeat"></i> 刷新', ['index'], ['class' => 'btn btn-info']),
        //'footer'=>true
    ],
]);?>
</div>

<?php
Modal::begin([
    'id' => 'create-modal',
    'header' => '<h4 class="modal-title">系统信息</h4>',
    'footer' => '<a href="#" class="btn btn-primary closes" data-dismiss="modal">Close</a>',
    'size'=>'modal-lg',
    'options'=>[
        'data-backdrop'=>'static',//点击空白处不关闭弹窗

    ],
]);
Modal::end();

$requestUrl = Url::toRoute('create');
$batchUrl = Url::toRoute('all-default-supplier');
$batchUrls = Url::toRoute('all-add-product');
$msg ='请选择产品';
//$requestUrl = Url::toRoute('create');
$js = <<<JS
    $(document).on('click', '.creates', function () {

        $.get('{$requestUrl}', {},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
//    var img_flag = null;
//    $(document).on('mouseenter', '.img-rounded', function () {
//            alert(111);
//       // clearTimeout(img_flag);
//        //$(this).next().slideDown();
//    }).on('mouseleave', '.img-rounded', function () {
//        var _this = $(this);
//        clearTimeout(img_flag);
//        img_flag = setTimeout(function() {
//            _this.next().slideUp();
//        }, 300);
//    });
    $(document).on('click', '#batch', function () {
           var str='';
            //获取所有的值
            $("input[name='id[]']:checked").each(function(){
                str+=','+$(this).val();
                //alert(str);

            })
            str=str.substr(1);
            if (str&& str.length !=0)
            {
                     $.get('{$batchUrl}', {id: str},
                                function (data) {
                                    $('.modal-body').html(data);
                                }
                     );

            } else {
                     $('.modal-body').html('{$msg}');
                     return false;

            }
    });
    $(document).on('click', '#product', function () {

        $.get('{$batchUrls}', {},
            function (data) {
               $('.modal-body').html(data);
            }
       );
    });


JS;
$this->registerJs($js);
?>
