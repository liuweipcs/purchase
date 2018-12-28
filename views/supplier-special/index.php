<?php

use mdm\admin\components\Helper;
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use app\models\SupplierSearch;

$this->title = '跨境宝供应商列表';
$this->params['breadcrumbs'][] = $this->title;

Modal::begin([
    'id' => 'create-modal',
    'header' => '<h4 class="modal-title">新增</h4>',
    'footer' => '<a href="#" class="btn btn-primary closes" data-dismiss="modal">关闭</a>',
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
        <div class="panel-footer">
            <?php
            if (Helper::checkRoute('import')) {
                echo Html::a('导入', ['import'], ['class' => 'btn btn-sm btn-success', 'id' => 'import', 'data-toggle' => 'modal', 'data-target' => '#create-modal']);
            } ?>
            <?php
            if (Helper::checkRoute('batch-delete')) {
                echo Html::button('删除', ['class' => 'btn btn-sm btn-danger', 'id' => 'batch-delete']);
            } ?>
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
        'id'=>'amazon_fba',
    ],
    'columns'=>[
        [
            'class'=>'kartik\grid\CheckboxColumn',
            'name'=>"id",
            'width' => '5%;',
            'checkboxOptions'=>function ($model,$key,$index,$column){
                return ['value'=>$model->id];
            }
        ],
        [
            'label'=>'供应商代码',
            'format'=>'raw',
            'width' => '15%;',
            'attribute'=>'supplier_code',
            'value'=>function($model){
                return $model->supplier_code;
            }
        ],
        [
            'label'=>'供应商',
            'format'=>'raw',
            'attribute'=>'supplier_name',
            'value'=>function($model){
                $sub_html = SupplierSearch::flagCrossBorder(true,$model->supplier_code);
                $html = $model->supplier_name.$sub_html;
                return $html;
            }
        ],
        [
            'label'=>'添加人',
            'format'=>'raw',
            'width' => '20%;',
            'attribute'=>'special_flag_user',
            'value'=>function($model){
                return $model->special_flag_user;
            }
        ],
        [
            'label'=>'添加时间',
            'format'=>'raw',
            'width' => '20%;',
            'attribute'=>'special_flag_time',
            'value'=>function($model){
                return $model->special_flag_time;
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
$batchDeleteUrl     = Url::toRoute(['batch-delete']);
$js = <<<JS
$(function() {    
    /**
     * 删除数据
     */
    $(document).on('click', '#batch-delete', function () {
        var ids = $('#amazon_fba').yiiGridView('getSelectedRows');
        if(ids == ''){
            layer.msg('请先选择！');
            return false;
        }
        var num = ids.length;
        if(num > 0) {
            layer.confirm('您选中'+num+'条记录，是否确认删除吗？', {
                btn: ['确定','取消']
            }, function() {
                $.get("$batchDeleteUrl", {ids:ids}, function(result) {
                    if(result) {
                        layer.msg('删除成功');
                    } else {
                        layer.msg('删除失败');
                    }
                });
            });
        }
    });    
    
    /**
     * 导入
     */
    $(document).on('click', '#import', function () {
        $.get($(this).attr('href'), { },function (data) {
            $('.modal-body').html(data);
        });
    });

});
JS;
$this->registerJs($js);
?>