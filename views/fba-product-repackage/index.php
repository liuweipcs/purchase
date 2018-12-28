<?php

use app\config\Vhelper;
use mdm\admin\components\Helper;
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use app\models\ProductRepackageSearch;
use app\models\SupplierSearch;
use app\models\Product;
use app\models\User;

$this->title = 'FBA二次包装列表';
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
            if (Helper::checkRoute('create-data')) {
                echo Html::a('新增', ['create-data'], ['class' => 'btn btn-sm btn-success', 'id' => 'create', 'data-toggle' => 'modal', 'data-target' => '#create-modal']);
            } ?>
            <?php
            if (Helper::checkRoute('delete')) {
                echo Html::button('删除', ['class' => 'btn btn-sm btn-danger', 'id' => 'delete']);
            } ?>
            <?php
            if (Helper::checkRoute('import')) {
                echo Html::a('导入', ['import'], ['class' => 'btn btn-sm btn-success', 'id' => 'import', 'data-toggle' => 'modal', 'data-target' => '#create-modal']);
            } ?>
            <?php
            if (Helper::checkRoute('audit')) {
                echo Html::button('审核通过', ['class' => 'btn btn-sm btn-primary', 'id' => 'audit']);
            } ?>
            <?php
            if (Helper::checkRoute('audit')) {
                echo Html::button('审核不通过', ['class' => 'btn btn-sm btn-primary', 'id' => 'audit_no']);
            }
            ?>
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
            'checkboxOptions'=>function ($model,$key,$index,$column){
                return ['value'=>$model->id];
            }
        ],
        [
            'label'=>'SKU',
            'format'=>'raw',
            'attribute'=>'sku',
            'value'=>function($model){
                $html = $model->sku;
                $weightSku = (isset($model->audit_status) AND $model->audit_status == 1)?'包':'';
                $subHtml = "<span style='position:absolute; color:red;font-size: 10px;'>$weightSku</span>";
                return $html.$subHtml;
            }
        ],
        [
            'label'=>'审核状态',
            'format'=>'raw',
            'attribute'=>'audit_status',
            'value'=>function($model){

                $audit_status_list = ProductRepackageSearch::auditStatusList();
                $html = isset($audit_status_list[$model->audit_status])?$audit_status_list[$model->audit_status]:'';
                return $html;
            }
        ],
        [
            'label'=>'供应商名称',
            'format'=>'raw',
            'value'=>function($model){
                $defaultSupplier = $model->defaultSupplier;
                if(isset($defaultSupplier->supplier_code)){
                    $supplier_code = $defaultSupplier->supplier_code;
                    $supplierInfo = SupplierSearch::findOne(['supplier_code' => $supplier_code]);
                    $html =  $supplierInfo->supplier_name;
                }else{
                    $html = '';
                }

                return $html;
            }
        ],
        [
            'label'=>'产品名称',
            'format'=>'raw',
            'width' => '35%;',
            'attribute'=>'audit_status',
            'value'=>function($model){

                $productInfo = Product::findOne(['sku' => $model->sku]);
                return isset($productInfo->desc)?$productInfo->desc->title:'';
            }
        ],
        [
            'label'=>'创建人',
            'format'=>'raw',
            'attribute' => 'add_user',
            'value'=>function($model){
                $userInfo = User::findOne($model->add_user);
                return $userInfo->username;
            }
        ],
        [
            'label'=>'创建时间',
            'format'=>'raw',
            'attribute'=>'audit_status',
            'value'=>function($model){
                return $model->add_time;
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
$auditUrl          = Url::toRoute(['audit']);
$deleteUrl           = Url::toRoute(['delete']);
$addPurchaseNotes   = Url::toRoute(['add-purchase-notes']);
$create             = '请选择需要标记到货日期的采购单';
$msg                = '请选择要操作的记录';
$exportUrl          = Url::toRoute('order-export');
$js = <<<JS
$(function() {
    /**
     * 经理审核
     */
    $(document).on('click', '#audit', function () {
        var ids = $('#amazon_fba').yiiGridView('getSelectedRows');
        if(ids == ''){
            layer.msg('请先选择！');
            return false;
        }
        var num = ids.length;
        if(num > 0) {
            layer.confirm('您选中'+num+'条记录，是否确认审核通过？', {
                btn: ['确定','取消']
            }, function() {
                $.get("$auditUrl", {ids:ids, audit_status: 1}, function(result) {
                    if(result) {
                        layer.msg('审核通过成功');
                        // window.location.reload();
                    } else {
                        layer.msg('审核通过失败');
                    }
                });
            });
        }
    });
    
    $(document).on('click', '#audit_no', function () {
        var ids = $('#amazon_fba').yiiGridView('getSelectedRows');
        if(ids == ''){
            layer.msg('请先选择！');
            return false;
        }
        var num = ids.length;
        if(num > 0) {
            layer.confirm('您选中'+num+'条记录，是否确认审核不通过？', {
                btn: ['确定','取消']
            }, function() {
                $.get("$auditUrl", {ids:ids, audit_status: 2}, function(result) {
                    if(result) {
                        layer.msg('审核不通过成功');
                        // window.location.reload();
                    } else {
                        layer.msg('审核不通过失败');
                    }
                });
            });
        }
    });
    
    /**
     * 删除数据
     */
    $(document).on('click', '#delete', function () {
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
                $.get("$deleteUrl", {ids:ids}, function(result) {
                    if(result) {
                        layer.msg('删除成功');
                        // window.location.reload();
                    } else {
                        layer.msg('删除失败');
                    }
                });
            });
        }
    });
    
    /**
     * 新增
     */
    $(document).on('click', '#create', function () {
        $.get($(this).attr('href'), { },function (data) {
            $('.modal-body').html(data);
        });
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