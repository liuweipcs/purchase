<?php

use yii\helpers\Html;

use kartik\grid\GridView;
use Yii\helpers\Url;
use yii\bootstrap\Modal;
use app\config\Vhelper;
use yii\widgets\ActiveForm;
use \app\models\SupplierUpdateApply;
use \app\models\SupplierCheck;
use \app\services\SupplierServices;
use kartik\select2\Select2;
use yii\web\JsExpression;
/* @var $this yii\web\View */
/* @var $searchModel app\models\SupplierGoodsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->params['time'] = $params;
$this->title = Yii::t('app', '绩效管理');
$this->params['breadcrumbs'][] = $this->title;
?>

<div >
    <?= $this->render('_stat-search', ['model' => $searchModel]); ?>
    <p class="clearfix"></p>
    <div >
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                [
                    'label' => '采购员',
                    'format'=> 'raw',
                    'value' => function($model){
                        return $model['username'];
                    }
                ],
                [
                    'label' => '整合数量',
                    'format'=> 'raw',
                    'pageSummary'=>true,
                    'value' => function($model){
                        return $model['integrat_num'];
                    }
                ],
                [
                    'label' => '账期数量',
                    'format'=> 'raw',
                    'value' => function($model){
                        return $model['settlement_num'];
                    }
                ],
                [
                    'label' => 'SKU降价数量',
                    'format'=> 'raw',
                    'pageSummary'=>true,
                    'value' => function($model){
                        return $model['down_num'];
                    }
                ],
                [
                    'label' => 'SKU降价金额',
                    'format'=> 'raw',
                    'value' => function($model){
                        return '';
                    }
                ],
                [
                    'label' => '验厂验货数量',
                    'format'=> 'raw',
                    'value' => function($model){
                        if (!empty($this->params['time'])) {
                            $check_start_time = $this->params['time']['SupplierUpdateApplySearch']['check_start_time'];
                            $check_end_time = $this->params['time']['SupplierUpdateApplySearch']['check_end_time'];
                        } else {
                            $check_start_time = date('Y-m-d 00:00:00');
                            $check_end_time = date('Y-m-d 23:59:59');
                        }
                        return SupplierCheck::getSupplierCheck($model['username'], $check_start_time, $check_end_time);
                    }
                ],
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
            'showPageSummary' => true,

            'exportConfig' => [
                //GridView::EXCEL => [],
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
    'id' => 'create-modal',
    'header' => '<h4 class="modal-title">系统信息</h4>',
    'footer' => '<a href="#" class="btn btn-primary closes" data-dismiss="modal">Close</a>',
    'size'=>'modal-lg',
    'options'=>[
        //'data-backdrop'=>'static',//点击空白处不关闭弹窗

    ],
]);
Modal::end();
$js = <<<JS

JS;
$this->registerJs($js);
?>
