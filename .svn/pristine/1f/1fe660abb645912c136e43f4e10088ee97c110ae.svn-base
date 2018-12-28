<?php

use app\config\Vhelper;
use mdm\admin\components\Helper;
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use app\models\SupplierAuditResults;
use app\services\BaseServices;
use toriphes\lazyload\LazyLoad;
use app\models\SupplierSearch;


$this->title = '供应商审核列表';
$this->params['breadcrumbs'][] = $this->title;


Modal::begin([
     'id' => 'show-modal',
     'header' => '<h4 class="modal-title">查看日志</h4>',
     'footer' => '<a href="#" class="btn btn-primary closes" data-dismiss="modal" style="display: none"></a>',
     'size'=>'modal-lg',
     //'closeButton' =>false,
     'options'=>[
         'data-backdrop'=>'static',//点击空白处不关闭弹窗
     ],
 ]);
Modal::end();

?>
    <div class="panel panel-default">
        <div class="panel-body">
            <?= $this->render('_search', ['model' => $searchModel]); ?>
        </div>
    </div>

<?php
echo GridView::widget([
    'dataProvider'=>$dataProvider,
    'filterSelector' => "select[name='".$dataProvider->getPagination()->pageSizeParam."'],input[name='".$dataProvider->getPagination()->pageParam."']",

    'pager'=>[
        'options'=>['class' => 'pagination','style'=> "display:block;"],
        'class'=>\liyunfang\pager\LinkPager::className(),
        'pageSizeList' => [5,20, 50, 100, 200],
        'firstPageLabel'=>"首页",
        'prevPageLabel'=>'上一页',
        'nextPageLabel'=>'下一页',
        'lastPageLabel'=>'末页',
    ],
    'options'=>[
        'id'=>'list-ids',
    ],
    'columns'=>[
        [
            'class'=>'kartik\grid\CheckboxColumn',
            'name'=>"id",
            'width' => '5%',
            'checkboxOptions'=>function ($model,$key,$index,$column){
                return ['value'=>$model->id];
            }
        ],
        [
            'label'=>'ID',
            'format'=>'raw',
            'attribute'=>'id',
            'width' => '5%',
            'value'=>function($model){
                return $model->id;
            }
        ],
        [
            'label'=>'供应商',
            'format'=>'raw',
            'attribute'=>'supplier_code',
            'width' => '15%',
            'value'=>function($model){
                $supplier_name  = SupplierSearch::find()->select('supplier_name')->where(['supplier_code' => $model->supplier_code])->scalar();
                $supplier_name  = ($supplier_name)?$supplier_name:$model->supplier_name;
                return Html::a($supplier_name, ['supplier/view-operation-log','supplier_code'=>$model->supplier_code]).SupplierSearch::flagCrossBorder(true,$model->supplier_code);
            }
        ],
        [
            'label'=>'审核人',
            'format'=>'raw',
            'attribute'=>'audit_user',
            'width' => '5%',
            'value'=>function($model){
                return $model->audit_user;
            }
        ],
        [
            'label'=>'审核时间',
            'format'=>'raw',
            'attribute'=>'audit_date',
            'width' => '10%',
            'value'=>function($model){
                return $model->audit_date;
            }
        ],
        [
            'label'=>'来源',
            'format'=>'raw',
            'attribute'=>'res_source',
            'width' => '8%',
            'value'=>function($model){
                return SupplierAuditResults::getResSourceList($model->res_source);
            }
        ],
        [
            'label'=>'申请时间',
            'format'=>'raw',
            'attribute'=>'apply_time',
            'width' => '10%',
            'value'=>function($model){
                return $model->apply_time;
            }
        ],
        [
            'label'=>'审核状态',
            'format'=>'raw',
            'attribute'=>'audit_status',
            'width' => '8%',
            'value'=>function($model){
                return SupplierAuditResults::getStatusList($model->audit_status);
            }
        ],
        [
            'label'=>'审核类型',
            'format'=>'raw',
            'width' => '8%',
            'value'=>function($model){
                return SupplierAuditResults::getResTypeList($model->res_type);
            }
        ],
        [
            'label'=>'审核时效(H)',
            'format'=>'raw',
            'width' => '8%',
            'attribute'=>'audit_used',
            'value'=>function($model){
                $html = sprintf('%.1f',$model->audit_used);
                return $html.' H';
            }
        ],
    ],
    'containerOptions' => ["style" => "overflow:auto"],
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
        'before' => false,
        'after' => false,
        'heading' => '<h3 class="panel-title"><i class="glyphicon glyphicon-stats"></i> </h3>',
        'type' => 'success',
        //'after'=>Html::a('<i class="glyphicon glyphicon-repeat"></i> 刷新', ['index'], ['class' => 'btn btn-info']),
    ]
]);
?>

<?php
$msg                = '请选择要操作的记录';
$js = <<<JS
$(function() {
    
    $(document).on('click', '#show-log', function () {
        var key_id = $(this).attr('data-key-id');
        $.get($(this).attr('href'),{ key_id:key_id},function (data) {
            $('#show-modal .modal-body').html(data);
        });
    });

});
JS;
$this->registerJs($js);
?>