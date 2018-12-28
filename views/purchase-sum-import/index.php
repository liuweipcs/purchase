<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\models\ProductCategory;
use app\services\BaseServices;
use yii\bootstrap\Modal;
use app\config\Vhelper;
use app\models\Product;
use app\models\PurchaseOrderItems;
use mdm\admin\components\Helper;
use kartik\select2\Select2;
use yii\web\JsExpression;
use app\services\PurchaseSuggestQuantityServices;

$this->title = '查看导入需求';
$this->params['breadcrumbs'][] = ['label' => '采购建议', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

Modal::begin([
    'id' => 'create-purchase-modal',
    'header' => '<h4 class="modal-title">系统提示</h4>',

    'footer' => '<a href="#" class="btn btn-primary" data-dismiss="modal">关闭</a>',
    'size'=>'modal-lg',
    'options'=>[
        'data-backdrop'=>'static',//点击空白处不关闭弹窗

    ],
]);
Modal::end();
?>
<div class="purchase-suggest-quantity-index">
    <?= $this->render('_ssearch', ['model' => $searchModel]); ?>

    <p class="clearfix"></p>
    <p>
        <?php
        if(Helper::checkRoute('purchase-sum-import'))
        {
            echo Html::a('采购需求导入', ['#'], ['class' => 'btn btn-success purchase-sum-import', 'data-toggle' => 'modal', 'data-target' => '#create-purchase-modal']);
        }
        ?>
        <?php
        if(Helper::checkRoute('export-csv'))
        {
            echo Html::a('导出需求', ['export-csv'], ['class' => 'btn btn-success export-csv']);
        }
        ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
//        'filterModel'  => $searchModel,
        'options'=>[
            'id'=>'grid_purchase_sum_import',
        ],
        'filterSelector' => "select[name='".$dataProvider->getPagination()->pageSizeParam."'],input[name='".$dataProvider->getPagination()->pageParam."']",
        'pager' => [
            'class' => \liyunfang\pager\LinkPager::className(),
            'options'=>[
                'class' => 'pagination',
//                'class'=>'hidden',//关闭自带分页
                'style'=> "display:block;", //块状显示，支持浮动
            ],
            'firstPageLabel'=>"首页",
            'prevPageLabel'=>'上一页',
            'nextPageLabel'=>'下一页',
            'lastPageLabel'=>'尾页',
            'template' => '{pageButtons} {customPage} {pageSize}', //分页栏布局
            'pageSizeList' => [10, 20, 30, 50], //页大小下拉框值
            'customPageWidth' => 50,            //自定义跳转文本框宽度
            'customPageBefore' => ' 跳转到第 ',
            'customPageAfter' => ' 页 ',
        ],
        'columns' => [
            //方法一：
            [
                'class' => 'kartik\grid\CheckboxColumn',
                'name'=>"id" ,
                'checkboxOptions' => function ($model, $key, $index, $column) {
                    return ['value' => $model->id];
                }

            ],
            //方法二：
//            ['class' => 'yii\grid\SerialColumn'],
            /*[
                'label'=>'产品图片',
                'attribute'=>'uploadimgss',
                'format'=>'raw',
                'value'=> function ($model, $key, $index, $column) {
                    $product = Html::img(Vhelper::downloadImg($model->sku,$model->uploadimgs,2),['width'=>'80px']);
                    return $product;
                }
            ],*/
            [
                'label'=>'sku',
                'attribute'=>'sku', //是否可以在表格中搜索
                'format'=>'raw',
                'value'=> function ($model, $key, $index, $column)
                {
                    //$product = Vhelper::toSkuImgUrl($model->sku,$model->uploadimgs);
                    return Html::a($model->sku, Yii::$app->params['SKU_ERP_Product_Detail'].$model->sku,['target'=>'blank']);
                    //return Html::a($model->sku, Yii::$app->params['SKU_ERP_Product_Detail'].$model->sku,['target'=>'blank']).Html::a('',$product,['target'=>'blank','class'=>'glyphicon glyphicon-picture']);

                }
            ],
            [
                'label'=>'平台号',
                'attribute'=>'purchase_warehouse',
                'format'=>'raw',
                'value'=> function ($model) {
                    return $model->platform_number;
                },
            ],
            [
                'label'=>'采购仓',
                'attribute'=>'purchase_warehouse',
                'format'=>'raw',
                'value'=> function ($model) {
                    /* 不完善：可搜索的下拉框
                     //$url = \yii\helpers\Url::to(['/supplier/search-supplier']);
                    $url = \yii\helpers\Url::to(['purchase-sum-import/get-warehouse-code']);
                    return Select2::widget([ 'name' => 'title',
                        'options' => ['placeholder' => '请输入采购仓...'],
                        //'value'    =>!empty($model->purchase_warehouse) ? $model->purchase_warehouse : '',
                        //'data'=>BaseServices::getWarehouseCode(),
                        'pluginOptions' => [
                            'placeholder' => 'search ...',
                            'allowClear' => true,
                            'language' => [
                                'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                            ],
                            'ajax' => [
                                'url' => $url,
                                'dataType' => 'json',
                                'data' => new JsExpression("function(params) { return {q:params.term}; }")
                            ],
                            'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                            'templateResult' => new JsExpression('function(res) { return res.text; }'),
                            'templateSelection' => new JsExpression('function (res) { return res.id; }'),
                        ],
                    ]);*/
                    /* 点击可弹框
                    $supplierName = !empty($model->defaultSupplierDetail) ? $model->defaultSupplierDetail->supplier_name : '';
                    $supplierCode = !empty($model->defaultSupplierDetail) ? $model->defaultSupplierDetail->supplier_code : '';
                    $supplier = \app\models\Supplier::find()->andFilterWhere(['supplier_code'=>$supplierCode])->one();
                    $html=Html::a($supplierName, ['#'],
                        [
                            'class' => 'btn btn-xs supplier',
                            'data-toggle' => 'modal',
                            'data-target' => '#create-modal',
                            'value' =>$supplier->id
                        ]);*/
                    return !empty($model->purchase_warehouse)?BaseServices::getWarehouseCode($model->purchase_warehouse):'';
                },
            ],
            [
                'label'=>'导入数量',
                'attribute'=>'purchase_quantity',
                'value'=> function ($model) {
                    return $model->purchase_quantity;
                },
                'pageSummary' => true
            ],
            [
                'label'=>'活动备货',
                'attribute'=>'activity_stock',
                'value'=> function ($model) {
                    return $model->activity_stock;
                },
                'pageSummary' => true
            ],
            [
                'label'=>'常规备货',
                'attribute'=>'routine_stock',
                'value'=> function ($model) {
                    return $model->routine_stock;
                },
                'pageSummary' => true
            ],
            [
                'label'=>'创建人',
                'attribute'=>'create_id',
                'value'=> function ($model) {
                    return $model->create_id;
                },
            ],
            [
                'label'=>'创建时间',
                'attribute'=>'create_time',
                'format'=>['date','php:Y-m-d H:i:s'],
                'filterType'=>GridView::FILTER_DATETIME , //搜索不出
                'format'=>'raw',
                'value'=> function ($model) {
                    return $model->create_time;
                },
            ],
            [
                'label'=>'销售备注',
                'attribute'=>'sales_note',
                'format'=>'raw',
                'value'=> function ($model) {
                    //方法一：
//                    return Html::input('text','sales_note',$model->sales_note,['readonly'=>'readonly','class'=>'sales_note','productId'=>$model->id,'style'=>'width:70px']);
                    //方法二：
                    return Html::input('text','sales_note',$model->sales_note,['class'=>'note','readonly'=>'readonly','applyId'=>$model->id/*,'style'=>'width:150px'*/]);
                    return $model->sales_note;
                },
            ],
            [
                'label'=>'数据系统使用状态',
                'attribute'=>'suggest_status',
                'format'=>'raw',
                'value'=> function ($model) {
                    /*if ($model->suggest_status === 1) {
                        return '未使用过';
                    } elseif ($model->suggest_status === 2) {
                        return '使用过';
                    }*/
                    return PurchaseSuggestQuantityServices::getSuggestStatus($model->suggest_status);
                },
                'filter'=>PurchaseSuggestQuantityServices::getSuggestStatusText(),
            ],
            [
                'class' => 'kartik\grid\ActionColumn',
                'class' => kartik\grid\ActionColumn::className(),
                'dropdown' => false, //动作：true生成下拉框  false全部展示
                'width'=>'180px',
                'template' => Helper::filterActionColumn('{delete}'),
                'buttons'=>[

                ]

            ],
        ],
        'containerOptions' => ["style"=>"overflow:auto"], // only set when $responsive = false
        'toolbar' =>  [

            '{export}', //导出excel
        ],

        'pjax' => false,
        'bordered' => true,
        'striped' => false,
        'condensed' => true,
        'responsive' => true,
        'hover' => true, //当鼠标悬浮到这条数据时，会有颜色变化 true会  false不会
        'floatHeader' => false, //是否可以自行调节表格的宽度 true不能  false能
        'showPageSummary' => true,

        'exportConfig' => [
            GridView::EXCEL => [],
        ],
        'panel' => [
            'heading'=>'<h3 class="panel-title"><i class="glyphicon glyphicon-globe"></i> Countries</h3>',
            'type'=>'success',
            // 'after'=>Html::a('<i class="glyphicon glyphicon-repeat"></i> 刷新', ['index'], ['class' => 'btn btn-info']),
            //'footer'=>true
        ],
    ]); ?>
</div>
<?php
Modal::begin([
    'id' => 'create-modal',
    //'header' => '<h4 class="modal-title">系统信息</h4>',
    //'footer' => '<a href="#" class="btn btn-primary"  data-dismiss="modal"  >Close</a>',
//    'footer' => '<a href="#" class="btn btn-primary closes" data-dismiss="modal">Close</a>',
    'closeButton' =>false,
    'size'=>'modal-lg',
    'options'=>[
        'z-index' =>'-1',

    ],
]);
Modal::end();
$arrival='请选择';

$urls = Url::toRoute('purchase-sum-import');
$supplierUrl  = Url::toRoute('supplier/update');

$js = <<<JS
    //导出
    $(document).on('click', '.export-csv', function () {
        var ids = $('#grid_purchase_sum_import').yiiGridView('getSelectedRows');
        var url = $(this).attr("href");
        /*if (ids == '') {
            alert('请先勾选ID!');
             return false;
        }*/
        if($(this).hasClass("print"))
        {
            url = '/purchase-sum-import/export-csv';
        }
        url=url + '?ids=' + ids;
        $(this).attr('href',url);
    });
    //采购需求批量导入
    $(".purchase-sum-import").click(function(){
        $.get("$urls", {},
        function (data) {
            $('.modal-body').html(data);
        });
    });
    //点击可弹框
    /*$(document).on('click', '.supplier', function () {
        var id = $(this).attr('value');
        $.get('{$supplierUrl}', {id:id},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });*/
    //方法一：双击编辑，刷新整个页面
    //双击编辑报价
    $("input[name='sales_note']").dblclick(function(){
        $(this).removeAttr("readonly");
    });
    //失焦添加readonly
    $("input[name='sales_note']").change(function(){
        $(this).attr("readonly","true");
        var sales_note = $(this).val();
        var id   = $(this).attr('productId');
        $.ajax({
            url:'edit-sales-note',
            data:{sales_note:sales_note,id:id},
            type: 'post',
            dataType:'json',
        });
    });
    //方法二：双击编辑，刷新局部页面，ajax
   //双击编辑报价
    $(".note").dblclick(function(){
            $(this).removeAttr("readonly");
        });
    //失焦添加readonly
    $(".note").change(function(){
        $(this).attr("readonly","true");
        var note = $(this).val();
        var id   = $(this).attr('applyId');
        $.ajax({
            url:'edit-sales-note',
            data:{note:note,id:id},
            type: 'post',
            dataType:'json',
            success:function(data){
                $(this).val(data.note);
            }
        });
       });
JS;
$this->registerJs($js);
?>