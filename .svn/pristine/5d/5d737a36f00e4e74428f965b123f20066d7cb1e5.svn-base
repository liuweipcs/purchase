<?php

use app\config\Vhelper;
use mdm\admin\components\Helper;
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use app\models\Product;
use app\models\Supplier;
use app\models\ProductSupplierChangeSearch;
use app\services\BaseServices;
use toriphes\lazyload\LazyLoad;
use app\models\SkuSalesStatistics;

$this->title = 'SKU屏蔽申请列表';
$this->params['breadcrumbs'][] = $this->title;

Modal::begin([
    'id' => 'create-modal',
    'header' => '<h4 class="modal-title">SKU 录入</h4>',
    'footer' => '<a href="#" class="btn btn-primary closes" data-dismiss="modal" style="display: none"></a>',
    'size'=>'modal-lg',
    //'closeButton' =>false,
    'options'=>[
        'data-backdrop'=>'static',//点击空白处不关闭弹窗
    ],
]);
Modal::end();

Modal::begin([
    'id' => 'audit-modal',
    'header' => '<h4 class="modal-title">审核申请</h4>',
    'footer' => '<a href="#" class="btn btn-primary closes" data-dismiss="modal" style="display: none"></a>',
    'size'=>'modal-lg',
    //'closeButton' =>false,
    'options'=>[
        'data-backdrop'=>'static',//点击空白处不关闭弹窗
    ],
]);
Modal::end();

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

Modal::begin([
     'id' => 'affirm-modal',
     'header' => '<h4 class="modal-title">采购确认</h4>',
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
        <div class="panel-footer">
            <?php
            if (Helper::checkRoute('create-data')) {
                echo Html::a('新增', ['create-data'], ['class' => 'btn btn-sm btn-success', 'id' => 'create', 'data-toggle' => 'modal', 'data-target' => '#create-modal']);
            } ?>&nbsp;&nbsp;
            <?php
            if (Helper::checkRoute('batch-delete')) {
                //echo Html::button('删除', ['class' => 'btn btn-sm btn-danger', 'id' => 'batch-delete']);
            } ?>&nbsp;&nbsp;
            <?php
            if (Helper::checkRoute('audit')) {
                echo Html::a('审核', ['audit'], ['class' => 'btn btn-sm btn-primary', 'id' => 'audit', 'data-toggle' => 'modal', 'data-target' => '#audit-modal']);
            } ?>&nbsp;&nbsp;
            <?php
            if (Helper::checkRoute('export-csv')) {
                echo Html::button('导出', ['class' => 'btn btn-sm btn-success', 'id' => 'export-csv','title' => '按查询条件导出']);
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
        'id'=>'list-ids',
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
            'label'=>'ID',
            'format'=>'raw',
            'attribute'=>'id',
            'width' => '50px;',
            'value'=>function($model){
                return $model->id;
            }
        ],
        [
            'label'=>'状态',
            'format'=>'raw',
            'attribute'=>'status',
            'width' => '110px;',
            'value'=>function($model){
                $status_list        = ProductSupplierChangeSearch::getStatusList();
                $status_flag_list   = ProductSupplierChangeSearch::getStatusFlagList();
                $html   = isset($status_list[$model->status])?$status_list[$model->status]:'';
                if($model->status == 70){
                    $html   .= isset($status_flag_list[$model->status_flag])?'<br/>('.$status_flag_list[$model->status_flag].')':'';
                }

                return Html::a($html, ['show-log'], ['class'=>'show-log', 'id' => 'show-log','data-key-id'=>$model->id,'data-toggle'=>'modal','data-target'=>"#show-modal"]);

            }
        ],
        [
            'label'=>'SKU',
            'format'=>'raw',
            'attribute'=>'sku',
            'width' => '100px;',
            'value'=>function($model){
                return $model->sku;
            }
        ],
        [
            'label'=>'产品线',
            'format'=>'raw',
            'attribute'=>'sku',
            'value'=>function($model){
                $html = '';
                if(isset($model->productInfo->product_linelist_id) and $model->productInfo->product_linelist_id){
                    $html = BaseServices::getProductLine($model->productInfo->product_linelist_id);
                }
                return $html;
            }
        ],
        [
            'label'=>'申请信息',
            'format'=>'raw',
            'value'=>function($model){
                $html = '申请人：'.$model->apply_user;
                $html .= '<br/>申请时间：'.$model->apply_time;
                $html .= '<br/>申请备注：'.$model->apply_remark;
                return $html;
            }
        ],
        [
            'label'=>'开发员',
            'format'=>'raw',
            'value'=>function($model){
                return isset($model->productInfo->create_id)?$model->productInfo->create_id:'';
            }
        ],
        [
            'label'=>'产品图片',
            'format'=>'raw',
            'value'=>function($model){
                $img = \toriphes\lazyload\LazyLoad::widget(['src'=>Vhelper::getSkuImage($model->sku)]);
                return Html::a($img,['#'], ['class' => "img"]);
                //return LazyLoad::widget(['src'=>Yii::$app->params['ERP_URL'].'/services/api/system/index/method/getimage/sku/' . $model->sku]);
            }
        ],
        [
            'label'=>'30天销量',
            'format'=>'raw',
            'value'=>function($model){
                $salesCount = SkuSalesStatistics::find()
                    ->select('sum(days_sales_30) as days_sales_30')
                    ->andFilterWhere(['sku' => $model->sku])
                    ->scalar();
                return $salesCount;
            }
        ],
        [
            'label'=>'产品名称',
            'format'=>'raw',
            'width' => '10%',
            'value'=>function($model){
                $productInfo = Product::findOne(['sku' => $model->sku]);
                return isset($productInfo->desc)?$productInfo->desc->title:'';
            }
        ],
        [
            'label'=>'原供应商/单价',
            'format'=>'raw',
            'width' => '10%',
            'value'=>function($model){
                $html = null;
                if($model->old_supplier_name){ $html .=  $model->old_supplier_name.'<br/>';}
                if($model->old_price){ $html .= '单价：'.$model->old_price;}
                return $html;
            }
        ],
        [
            'label'=>'替换供应商/单价',
            'format'=>'raw',
            'width' => '10%',
            'value'=>function($model){
                $html = null;
                if($model->new_supplier_name){ $html .=  $model->new_supplier_name.'<br/>';}
                if($model->new_price){ $html .= '单价：'.$model->new_price;}
                return $html;
            }
        ],
        [
            'label'=>'ERP 开发审核',
            'format'=>'raw',
            'value'=>function($model){
                $html = null;
                if($model->erp_oper_user){ $html .= '操作人：'.$model->erp_oper_user.'<br/>';}
                if($model->erp_oper_time){ $html .= '时间：'.($model->erp_oper_time != '0000-00-00 00:00:00'?$model->erp_oper_time:'').'<br/>';}
                if($model->erp_remark)   { $html .= '驳回原因：'.$model->erp_remark.'<br/>';}
                if($model->erp_result)   { $html .= '操作结果：'.$model->erp_result.'<br/>';}
                return $html;
            }
        ],
        [
            'label'=>'确认人/时间',
            'format'=>'raw',
            'value'=>function($model){
                $html = null;
                if($model->affirm_user){ $html .= $model->affirm_user.'<br/>';}
                if($model->affirm_time){ $html .= $model->affirm_time;}
                return $html;
            }
        ],
        [
            'header' => '操作',
            'class' => 'yii\grid\ActionColumn',
            'template'=> ' {affirm} ',
            'headerOptions' => ['width' => '140'],
            'buttons' => [
                'affirm' => function ($url, $model, $key) {
                    if($model->status == 50 AND $model->new_supplier_name AND $model->apply_user == Yii::$app->user->identity->username){
                        $supplierStatus = Supplier::find()->select('status')->where(['supplier_name' => $model->new_supplier_name])->scalar();
                        if($supplierStatus == 1){
                            return Html::a('采购确认',['affirm-supplier', 'id' => $model->id],['class' => "btn btn-xs btn-primary",'id' => 'affirm', 'data-toggle' => 'modal', 'data-target' => '#affirm-modal']);
                        }else{
                            return Html::button('采购确认',['class' => "btn btn-xs btn-primary",'disabled' => 'disabled','title' => '供应链审核审核供应商通过后才能确认']);
                        }
                    }
                },
            ]
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
$auditUrl           = Url::toRoute(['audit']);
$batchDeleteUrl     = Url::toRoute(['batch-delete']);
$msg                = '请选择要操作的记录';
$js = <<<JS
$(function() {
    /**
     * 经理审核
     */
    $(document).on('click', '#audit', function () {
         var ids = $('#list-ids').yiiGridView('getSelectedRows');
        if(ids == ''){
            $('#audit-modal').modal('hide');
            layer.msg('请先选择！');
            return false;
        }
        
        var num = ids.length;
        
        $.get($(this).attr('href'), {ids:ids},function (data) {
            $('#audit-modal .modal-body').html(data);
        });
    });
    
    /**
     * 删除数据
     */
    $(document).on('click', '#batch-delete', function () {
        var ids = $('#list-ids').yiiGridView('getSelectedRows');
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
    
    $(document).on('click', '#affirm', function () {
        $.get($(this).attr('href'),{ },function (data) {
            $('#affirm-modal .modal-body').html(data);
        });
    });
    
    $(document).on('click', '#show-log', function () {
        var key_id = $(this).attr('data-key-id');
        $.get($(this).attr('href'),{ key_id:key_id},function (data) {
            $('#show-modal .modal-body').html(data);
        });
    });
    
    //批量导出
    $('#export-csv').click(function() {
        var ids = $('#list-ids').yiiGridView('getSelectedRows');
        if(ids == ''){
            layer.confirm('您未选中任何记录，将按查询条件导出', {
                btn: ['确认','取消'] //按钮
            }, function(index){
                window.location.href = 'export-csv';
                layer.close(index);
            }, function(index){
                layer.close(index);
            });
        }else{
            window.location.href = 'export-csv?ids='+ids;
        }
    });

});
JS;
$this->registerJs($js);
?>