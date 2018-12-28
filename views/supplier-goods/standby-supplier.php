<?php

use yii\helpers\Html;

use kartik\grid\GridView;
use app\services\BaseServices;
/* @var $this yii\web\View */
/* @var $searchModel app\models\SupplierGoodsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
?>
<div class="stockin-index">
    <?= Html::a('删除备用供应商', '#',['class' => 'btn btn-info delete-standby']) ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        "options" => ["class" => "grid-view","style"=>"overflow:auto", "id" => "stand"],
        'pager'=>[
            //'options'=>['class'=>'hidden']//关闭分页
            'firstPageLabel'=>"首页",
            'prevPageLabel'=>'上一页',
            'nextPageLabel'=>'下一页',
            'lastPageLabel'=>'末页',
        ],
        'columns' => [
            [
                'class' => 'kartik\grid\CheckboxColumn',
                'name'=>"standbyid" ,
                'checkboxOptions' => function ($model, $key, $index, $column) {
                    return ['id' => $model->id];
                }

            ],
            [
                'label'=>'供应商编码',
                'value'=>function($model){
                    return $model->supplier_code;
                }
            ],
            [
                'label'=>'供应商名称',
                'value'=>function($model){
                    return BaseServices::getSupplierName($model->supplier_code);
                }
            ],
            [
                'label'=>'单价',
                'value'=>function($model){
                    return !empty($model->quotes) ? $model->quotes->supplierprice : '';
                }
            ],
            [
                'label'=>'采购链接',
                'value'=>function($model){
                    return !empty($model->quotes) ? $model->quotes->supplier_product_address : '';
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
<?php
//$requestUrl = Url::toRoute('delete-staandby');
$js = <<<JS
    $(document).on('click', '.delete-standby', function () {
        var ids = new Array();
        $('[name="standbyid[]"]').each(function(){
            if($(this).is(':checked')){
                ids.push($(this).val());
            }
        });
        if(ids.length ==0){
            alert('至少勾选一个');
        }else {
            $.ajax({
                url:'delete-standby',
                data:{ids:ids},
                type: 'post',
                dataType:'json',
                success:function(data){
                    $("#create-modal").modal('hide');
                }
            });
        }
    });

JS;
$this->registerJs($js);
?>
