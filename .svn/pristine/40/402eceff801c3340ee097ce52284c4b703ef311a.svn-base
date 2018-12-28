<?php

use yii\helpers\Html;

use kartik\grid\GridView;
use Yii\helpers\Url;
use yii\bootstrap\Modal;
use app\models\ReplenishWarehouse;
use app\models\ReplenishWarehouseOrderProductRatio;
use app\models\ReplenishWarehousePurchaseRatio;
use app\models\ReplenishWarehouseSaleRatio;
/* @var $this yii\web\View */
/* @var $searchModel app\models\StockinSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '补货策略');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="stockin-index">


   
	<?php echo $this->render('_warehouse',[
							'warehouse'					=> new ReplenishWarehouse(),
							'warehouseOrderProductRatio'=> new ReplenishWarehouseOrderProductRatio(),
							'warehousePurchaseRatio'	=> new ReplenishWarehousePurchaseRatio(),
							'warehouseSaleRatio'		=> new ReplenishWarehouseSaleRatio(),
	]); ?>
    <p>


    </p>
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,



    'pjax' => true,
    'bordered' => true,
    'striped' => false,
    'condensed' => false,
    'responsive' => true,
    'hover' => true,
    'floatHeader' => true,
    'showPageSummary' => true,

    'panel' => [
        'type'=>'primary',
        'after'=>Html::a('<i class="glyphicon glyphicon-repeat"></i> 刷新', ['index'], ['class' => 'btn btn-info']),
        'footer'=>false
    ],
]);?>
</div>



