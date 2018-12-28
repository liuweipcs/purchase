<?php
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use app\services\PurchaseOrderServices;
use app\services\SupplierServices;
use app\services\BaseServices;
use app\models\PurchaseOrderItems;
$this->title = $title;
$this->params['breadcrumbs'][] = $this->title;
?>



    <div class="panel panel-default">
        <div class="panel-body">
            <?php echo $this->render('_search', ['model'=>$searchModel,'search_url'=>$search_url]); ?>
        </div>
    </div>
<?php
if(\mdm\admin\components\Helper::checkRoute('fba-export')&&$search_url=='fba-index') {
    echo Html::a(Yii::t('app', '导出'),['fba-export'], ["class" => "btn btn-info button-a"]);
}
if(\mdm\admin\components\Helper::checkRoute('inland-export')&&$search_url=='inland-index') {
    echo Html::a(Yii::t('app', '导出'),['inland-export'], ["class" => "btn btn-info button-a"]);
}
?>
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'options'=>[
        'id'=>'grid_purchase_order',
    ],
    'filterSelector' => "select[name='".$dataProvider->getPagination()->pageSizeParam."'],input[name='".$dataProvider->getPagination()->pageParam."']",

    'pager' => [
        'class' => \liyunfang\pager\LinkPager::className(),
        'options'=>['class' => 'pagination','style'=> "display:block;"],
        'template' => '{pageButtons} {customPage} {pageSize}', //分页栏布局
        'pageSizeList' => [20,100,200,300,500,1000], //页大小下拉框值
        'customPageWidth' => 50,            //自定义跳转文本框宽度
        'customPageBefore' => ' 跳转到第 ',
        'customPageAfter' => ' 页 ',
    ],
    'columns' => [
        [
            'label'=>'sku',
            'format'=>'raw',
            'value'=>function($model){
                $subHtml = \app\models\ProductRepackageSearch::getPlusWeightInfo($model->sku,true);// 加重SKU标记
                return $model->sku . $subHtml;
            }
        ],
        [
            'label'=>'产品状态',
            'value'=>function($model){
                return is_array(\app\services\SupplierGoodsServices::getProductStatus($model->product_status)) ?'':\app\services\SupplierGoodsServices::getProductStatus($model->product_status);
            }
        ],
        [
            'label'=>'供应商名称',
            'value'=>function($model){
                return $model->supplier_name;
            }
        ],
        [
            'label'=>'权均交期(天)',
            'attribute'=>'avg_delivery_time',
            'value'=>function($model){
                return $model->avg_delivery_time ==0 ? 0 : sprintf('%.2f',($model->avg_delivery_time)/(24*60*60));
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
