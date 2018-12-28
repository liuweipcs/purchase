<?php
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use app\models\PurchaseTacticsAbnormalSearch;

/*use app\services\PurchaseOrderServices;
use app\services\SupplierServices;
use app\services\BaseServices;
use app\models\PurchaseOrderItems;*/
$this->title = "MRP异常列表";
$this->params['breadcrumbs'][] = $this->title;
$url_export  = Url::toRoute('export');// 导出URL
?>



<div>

    <?php echo $this->render('_search', ['model'=>$searchModel]); ?>
    <p class="clearfix"></p>
    <p>
       <?php echo Html::button(Yii::t('app', '导出'),["class" => "btn btn-success btn-export"]);?>
    </p>
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterSelector' => "select[name='".$dataProvider->getPagination()->pageSizeParam."'],input[name='".$dataProvider->getPagination()->pageParam."']",
    'options'=>[
        'id'=>'grid_purchase_order',
    ],
    'pager' => [
        'class' => \liyunfang\pager\LinkPager::className(),
        'options'=>['class' => 'pagination','style'=> "display:block;"],
        //'template' => '{pageButtons} {customPage} {pageSize}', //分页栏布局
        'pageSizeList' => [20,50,100,200,300], //页大小下拉框值
        'firstPageLabel' => '首页',
        'prevPageLabel' => '上一页',
        'nextPageLabel' => '下一页',
        'lastPageLabel' => '末页',

    ],

    'columns' => [
        [
            'class' => 'kartik\grid\CheckboxColumn',
            'name'=>"id" ,
            'checkboxOptions' => function ($model, $key, $index, $column) {
                return ['value' => $model->id];
            }
        ],
        'id',
        [
            'label'=>'用户类型',
            'value'=>function($model){
                if (!$model->warehouse_type) {
                    $type = "";
                    return $type;
                }
                $warehouse_type = $model->warehouse_type;
                $type = PurchaseTacticsAbnormalSearch::getWarehouseName($warehouse_type);
                return $type;
            }
        ],
        [
            'label'=>'sku',
            'value'=>function($model){
                return  $model->sku;
            }
        ],
        [
            'label'=>'产品名称',
            'value'=>function($model){
                return  $model->name;
            }
        ],
        [
            'label'=>'采购员',
            'value'=>function($model){
                return  $model->buyer;
            }
        ],
        [
            'label'=>'供应商',
            'value'=>function($model){
                return $model->supplier_name;
            }
        ],
        [
            'label'=>'仓库',
            'value'=>function($model){
                return $model->warehouse_name;
            }
        ],
        [
            'label'=>'异常原因',
            'value'=>function($model){
                return $model->reason;
            }
        ],
    ],
    'containerOptions' => ["style"=>"overflow:auto"], // only set when $responsive = false
    'toolbar' =>  [
    ],


    'pjax' => false,
    'bordered' => true,
    'striped' => false,
    'condensed' => false,
    'responsive' => true,
    'hover' => true,
    'floatHeader' => false,
    'showPageSummary' => false,

    'exportConfig' => [
        GridView::EXCEL => [],
    ],
    'panel' => [
        //'heading'=>'<h3 class="panel-title"><i class="glyphicon glyphicon-globe"></i> Countries</h3>',
        'type'=>'success',
        //'after'=>Html::a('<i class="glyphicon glyphicon-repeat"></i> 刷新', ['index'], ['class' => 'btn btn-info']),
        //'footer'=>true
    ],
]); ?>

</div>
<?php 

$js = <<<JS

$(function() {
    $(".btn-export").click(function(){
        var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
        var url = '{$url_export}';
        urls = url+'?ids='+ids;
        window.location.href = urls;

    })
});



JS;
$this->registerJs($js);


 ?>
