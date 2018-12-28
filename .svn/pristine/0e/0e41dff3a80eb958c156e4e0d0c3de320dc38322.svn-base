<?php

use yii\helpers\Html;

use kartik\grid\GridView;
use Yii\helpers\Url;
use yii\bootstrap\Modal;
use app\models\SupplierCheck;
/* @var $this yii\web\View */
/* @var $searchModel app\models\SupplierGoodsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '供应商验货验厂列表');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="stockin-index">
    <p class="clearfix"></p>
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
                    'class' => 'kartik\grid\CheckboxColumn',
                    'name'=>"id" ,
                    'checkboxOptions' => function ($model, $key, $index, $column) {
                        return ['value' => $model->id];
                    }

                ],
                [
                    'label'=>'供应商',
                    'format'=>'raw',
                    'value'=>function($model){
                        return Html::a($model->supplier->supplier_name,'#',['class'=>'adress-detail','supplierCode'=>$model->supplier_code,'data-toggle' => 'modal',
                            'data-target' => '#supplier-adress',]);
                    }
                ]
            ],
            'containerOptions' => ["style"=>"overflow:auto"], // only set when $responsive = false
            'toolbar' =>  [

                //'{export}',
            ],


            'pjax' => false,
            'bordered' => true,
            'striped' => false,
            'condensed' => true,
            'responsive' => false,
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
    'id' => 'supplier-adress',
    'header' => '<h4 class="modal-title">系统信息</h4>',
    'footer' => '<a href="#" class="btn btn-primary closes" data-dismiss="modal">Close</a>',
    'size'=>'modal-lg',
    'options'=>[
        'tabindex' => false
        //'data-backdrop'=>'static',//点击空白处不关闭弹窗

    ],
]);
Modal::end();
$detail = Url::toRoute('adress-detail');
$js = <<<JS

$(document).on('click','.adress-detail',function() {
    var supplier_code = $(this).attr('supplierCode');
  $.get('{$detail}',{supplier_code:supplier_code},function(data) {
        $('.modal-body').html(data);
    });
})

JS;
$this->registerJs($js);
?>
