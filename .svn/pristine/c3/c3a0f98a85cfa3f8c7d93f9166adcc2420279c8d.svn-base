<?php

use yii\helpers\Html;

use kartik\grid\GridView;
use Yii\helpers\Url;
use yii\bootstrap\Modal;
use app\config\Vhelper;
use yii\widgets\ActiveForm;
use \app\models\SupplierUpdateApply;
use \app\services\SupplierServices;
use kartik\select2\Select2;
use yii\web\JsExpression;
/* @var $this yii\web\View */
/* @var $searchModel app\models\SupplierGoodsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '供应商结算方式列表');
$this->params['breadcrumbs'][] = $this->title;
?>

<div >
    <p class="clearfix"></p>
    <?= Html::button(Yii::t('app', '添加结算方式'), [
        'id' => 'create-settlement',
        'class' => 'btn btn-success',
        'data-toggle' => 'modal',
        'data-target' => '#create-modal'
    ]);?>
    <div >
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            //'filterModel' => $searchModel,
            "options" => ["class" => "grid-view","style"=>"overflow:auto", "id" => "grid"],
            'filterSelector' => "select[name='".$dataProvider->getPagination()->pageSizeParam."'],input[name='".$dataProvider->getPagination()->pageParam."']",
            'columns' => [
                [
                    'class' => 'kartik\grid\CheckboxColumn',
                    'name'=>"id" ,
                    'checkboxOptions' => function ($model, $key, $index, $column) {
                        return ['value' => $model->id];
                    }

                ],
                [
                    'label'=>'结算方式编码',
                    'value'=>'supplier_settlement_code'
                ],
                [
                    'label'=>'结算方式名称',
                    'value'=>'supplier_settlement_name',
                ],
                [
                    'label'=>'状态',
                    'format'=>'raw',
                    'value'=>function($model){
                        return $model->settlement_status == 1 ? '<span class="label label-success">'.'正常'.'</span>': '<span class="label label-danger">'.'禁用'.'</span>';
                    }
                ],
                [
                    'class' => 'kartik\grid\ActionColumn',
                    'dropdown' => false,
                    'width'=>'180px',
                    'template' => '{delete}{update}',
                    'buttons'=>[
                        'delete'=>function($url,$model,$key){
                            $status = $model->settlement_status ==1 ?'禁用':'启用';
                            return Html::a("<i class='glyphicon glyphicon-eye-open'></i>".$status, [$url], [
                                'class' => 'btn btn-xs red'
                            ]);
                        },
                        'update' => function ($url, $model, $key) {
                            return Html::a('<i class="glyphicon glyphicon-pencil"></i> 更新', ['update','id'=>$key], [
                                'title' => Yii::t('app', '更新 '),
                                'class' => 'btn btn-xs settlement-update',
                                'data-toggle' => 'modal',
                                'data-target' => '#create-modal'
                            ]);
                        },],

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

$requestUrl = Url::toRoute('create');
$js = <<<JS
$(document).on('click', '#create-settlement', function () {
        $.get('{$requestUrl}', {},
        function (data) {
            $('.modal-body').html(data);
        }
        );
    });
$(document).on('click', '.settlement-update', function () {
        var url = $(this).attr('href');
        $.get(url, {},
        function (data) {
            $('.modal-body').html(data);
        }
        );
    });

JS;
$this->registerJs($js);
?>
