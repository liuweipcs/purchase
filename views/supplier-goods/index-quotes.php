<?php

use yii\helpers\Html;

use kartik\grid\GridView;
use Yii\helpers\Url;
use yii\bootstrap\Modal;
use app\services\BaseServices;
/* @var $this yii\web\View */
/* @var $searchModel app\models\StockinSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

?>
<div class="stockin-index">



    <p class="clearfix"></p>
    <?= Html::a(Yii::t('app', '添加供应商报价'), ['create','sku'=>$sku], [

        //'data-toggle' => 'modal',
        //'data-target' => '#create-modal',
            'value' =>$sku,
        'class' => 'btn btn-success createss']
    ) ?>
    <?= Html::a(Yii::t('app', '所有历史报价'), "#", [
        'id' => 'history',
        'data-toggle' => 'modal',
        'data-target' => '#create-modal',
        'value' =>$sku,
        'class' => 'btn btn-success history'
    ]) ?>
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    "options" => ["class" => "grid-view","style"=>"overflow:auto", "id" => "grid"],
    'columns' => [
        //['class' => 'kartik\grid\SerialColumn'],

        [
            'label'=>'id',
            'attribute' => 'suppliercodes',
            'value'=>
                function($model, $key, $index, $column){
                    return  $model->id;
                },
        ],
        [
            'label'=>'供应商',
            'attribute' => 'suppliercodes',
            "format" => "raw",
            'value'=>
                function($model, $key, $index, $column){
                    //\app\config\Vhelper::dump($model->items);
                            $default=BaseServices::getDefaultSupplier($model->suppliercode, $model->product_sku);
                    return  BaseServices::getSupplierName($model->suppliercode).'&nbsp;'.$default;
                },
        ],
        [
            'label'=>'单价',
            'attribute' => 'suppliercodes',
            'value'=>
                function($model, $key, $index, $column){
                    return  $model->supplierprice;
                },
        ],
        [
            'label'=>'币种',
            'attribute' => 'suppliercodes',
            'value'=>
                function($model, $key, $index, $column){
                    return  $model->currency;
                },
        ],
       /* [
            'label'=>'交期',
            'attribute' => 'suppliercodes',
            'value'=>
                function($model, $key, $index, $column){
                    return  $model->purchase_delivery;
                },
        ],
        [
            'label'=>'最低采购量',
            'attribute' => 'suppliercodes',
            'value'=>
                function($model, $key, $index, $column){
                    return  $model->minimum_purchase_amount;
                },
        ],*/
        [
            'label'=>'默认采购员',
            'attribute' => 'default_buyers',
            'value'=>
                function($model, $key, $index, $column){
                    return  app\services\BaseServices::getEveryOne($model->default_buyer);
                },
        ],
        [
            'label'=>'默认跟单员',
            'attribute' => 'default_Merchandisers',
            'value'=>
                function($model, $key, $index, $column){
                    return  app\services\BaseServices::getEveryOne($model->default_Merchandiser);
                },
        ],
        [
            'label'=>'商品地址',
            'attribute' => 'supplier_product_addresss',
            "format" => "raw",
            'value'=>
                function($model, $key, $index, $column){
                    return !empty($model->supplier_product_address) ? Html::a('<i class="glyphicon glyphicon-picture" ></i>', $model->supplier_product_address, ["target" => "_blank" ,'class' => 'btn btn-xs red']):'';


                },
        ],
        [
            'label'=>'创建时间',
            'attribute' => 'add_times',
            'value'=>
                function($model, $key, $index, $column){
                    return  date('Y-m-d H:i:s',$model->add_time);
                },
        ],
        [
            'label'=>'修改时间',
            'attribute' => 'add_times',
            'value'=>
                function($model, $key, $index, $column){
                    return  $dates = !empty($model->update_time)?date('Y-m-d H:i:s',$model->update_time):'';
                },
        ],



        [
            'class' => 'kartik\grid\ActionColumn',
            'dropdown' => false,
            'width'=>'250px',
            'template' => '{update} {delete} {views}',
            'buttons'=>[
                'update' => function ($url, $model, $key) {

                 return Html::a('<i class="glyphicon glyphicon-pencil"></i>编辑', ['update','id'=>$key,
                     //'data-toggle' => 'modal',
                     //'data-target' => '#create-modal',
                     //'class' => 'data-update',
                     //'data-id' => $key,
                 ]);
                },
                'delete' => function ($url, $model, $key) {
                    return Html::a('<i class="glyphicon glyphicon glyphicon-trash"></i> 删除', ['delete','id'=>$key], [
                        'title' => Yii::t('app', '删除 '),
                        'data-confirm' => '确认删除吗？',
                        'data-method' => 'post',
                    ]);
                },
                'views' => function ($url, $model, $key) {
                    return Html::a('<i class="glyphicon glyphicon-eye-open"></i> 历史报价','#', [
                        'data-toggle' => 'modal',
                        'data-target' => '#create-modal',
                        'class' => 'data-updates',
                       'code' => $model->suppliercode,
                       'sku'  => $model->product_sku,
                    ]);
                },
            ],

        ],


    ],
    'containerOptions' => ["style"=>"overflow:auto"], // only set when $responsive = false
    'toolbar' =>  [

        '{export}',
    ],


    'pjax' => false,
    'bordered' => false,
    'striped' => false,
    'condensed' => false,
    'responsive' => true,
    'hover' => true,
    'floatHeader' => false,
   // 'showPageSummary' => false,

    'panel' => [

    ],
]);?>
</div>
<?php

$requestUrl       = Url::toRoute('create');
$requestUpdateUrl = Url::toRoute('update');
$historys         = Url::toRoute('view');
$history          = Url::toRoute('all-historical-offer');
$js = <<<JS

    $(document).on('click', '.createss', function () {


        $.get('{$requestUrl}', {sku: $(this).attr('value')},
            function (data) {
                $('#create-modal').find('.modal-body').html(data);
            }
        );
    });
    $(document).on('click', '.history', function () {

        $.get('{$history}', {sku: $(this).attr('value')},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
    $(document).on('click','.data-update', function () {

        $.get('{$requestUpdateUrl}', { id: $(this).closest('tr').data('key') },
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
    $(document).on('click','.data-updates', function () {

        $.get('{$historys}', {code:$(this).attr('code'),sku:$(this).attr('sku')},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
    function Inclass(id)
    {
        $('.' + id).css({
          'display': 'block',
          'padding-right': '17px',
       });
       $(".close").on('click',function(){

           $('.' + id).css({'display': 'none'});
        });

        $(".closes").on('click',function(){
           $('.'+ id).css({'display': 'none'});
       });
    }
JS;
$this->registerJs($js);
?>
