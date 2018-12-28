<?php

use yii\helpers\Html;

use kartik\grid\GridView;
use Yii\helpers\Url;
use yii\bootstrap\Modal;
use app\config\Vhelper;
use yii\widgets\ActiveForm;
use kartik\datetime\DateTimePicker;
/* @var $this yii\web\View */
/* @var $searchModel app\models\SupplierGoodsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '供应商整合');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="stockin-index">
    <?php $form = ActiveForm::begin([
        'action' => ['apply'],
        'method' => 'get',
    ]); ?>
    <div class="col-md-2">
    <?= $form->field($searchModel, 'create_time')->widget(DateTimePicker::className(),[
        'options' => ['placeholder' => '','readonly'=>true],
        'pluginOptions' => [
            'autoclose' => true,
            'todayHighlight' => true,
            'format' => 'yyyy-mm-dd hh:ii:ss',
        ]
    ])->label('申请提交时间'); ?>
    </div>
    <div class="col-md-1"><?=$form->field($searchModel, "product_status")->dropDownList(['1'=>'未审核','2'=>'已审核'],['class' => 'form-control','prompt' => '请选择'])->label('申请状态')?></div>

    <div class="col-md-1"><?= $form->field($searchModel,'sku')->textInput(['placeholder'=>''])->label('人员搜索') ?></div>

    <div class="col-md-1"><?= $form->field($searchModel,'sku')->textInput(['placeholder'=>''])->label('供应商搜索') ?></div>

    <div class="form-group col-md-2" style="margin-top: 24px;">
        <?= Html::submitButton(Yii::t('app', '搜索'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', '重置'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

    <?php //$this->render('_search', ['model' => $searchModel]); ?>
    <p class="clearfix"></p>
    <?= Html::a(Yii::t('app', '审核'), '#', [
        'id' => 'check',
        'class' => 'btn btn-success',
    ]);?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        "options" => ["class" => "grid-view","style"=>"overflow:auto", "id" => "grid"],
        'pager'=>[
            //'options'=>['class'=>'hidden']//关闭分页
            'firstPageLabel'=>"首页",
            'prevPageLabel'=>'上一页',
            'nextPageLabel'=>'下一页',
            'lastPageLabel'=>'末页',
        ],
        'columns' => [
            [
                'class' => 'kartik\grid\CheckboxColumn',
                'name'=>"id" ,
                'checkboxOptions' => function ($model, $key, $index, $column) {
                    return ['value' => $model->sku];
                }

            ],
            [
                'label' =>'审核状态',
                'format'=>'raw',
                'value' => function($model, $key, $index, $column){
                    return  '未审核';
                },

            ],
            [
                'label' => 'SKU创建时间',
                'format'=> 'raw',
                'value' => function($model){
                    return date('Y-m-d H:i:s',time());
                }
            ],
            [
                'label' => '上次修改供应商时间',
                'format'=> 'raw',
                'value' => function($model){
                    return date('Y-m-d H:i:s',time());
                }
            ],
            [
                'label' => 'SKU状态',
                'format'=> 'raw',
                'value' => function($model){
                    return '在售';
                }
            ],
            [
                'label' => '品类',
                'format'=> 'raw',
                'value' => function($model){
                    return '品类';
                }
            ],
            [
                'label' => '图片',
                'format'=> 'raw',
                'value' => function($model){
                    return '图片';
                }
            ],
            [
                'label' => 'SKU',
                'format'=> 'raw',
                'value' => function($model){
                    return 'SKU';
                }
            ],
            [
                'label' => '品名',
                'format'=> 'raw',
                'value' => function($model){
                    return '商品名称';
                }
            ],
            [
                'label' => '最后一次采购单价',
                'format'=> 'raw',
                'value' => function($model){
                    return '10';
                }
            ],
            [
                'label' => '原单价',
                'format'=> 'raw',
                'value' => function($model){
                    return '22';
                }

            ],
            [
                'label' => '原供应商',
                'format'=> 'raw',
                'value' => function($model){
                    return '旺仔'.Html::a('<span class="glyphicon glyphicon-list-alt" style="font-size:20px;color:#f2adb1;margin-right:10px;" title="单击，查看采购单明细"></span>', ['get-purchase-log'],['id' => 'logs',
                        'data-toggle' => 'modal',
                        'data-target' => '#create-modal','value' =>$model->sku,
                    ]);
                }
            ],
            [
                'label' => '现单价',
                'format'=> 'raw',
                'value' => function($model){
                    return '22';
                }

            ],
            [
                'label' => '现在供应商',
                'format'=> 'raw',
                'value' => function($model){
                    return '喜之郎'.Html::a('<span class="glyphicon glyphicon-list-alt" style="font-size:20px;color:#f2adb1;margin-right:10px;" title="单击，查看采购单明细"></span>', ['get-purchase-log'],['id' => 'logs',
                        'data-toggle' => 'modal',
                        'data-target' => '#create-modal','value' =>$model->sku,
                    ]);
                }
            ],
            [
                'label' => '供应链人员',
                'format'=> 'raw',
                'value' => function($mode){
                    return '职工B';
                }
            ],
            [
                'label' => '是否拿样',
                'format'=> 'raw',
                'value' => function($model){
                    return '否';
                }
            ],
            [
                'label' => '质检测试样品结果',
                'format'=> 'raw',
                'value' => function($model){
                    return '';
                }
            ]
        ],
        'containerOptions' => ["style"=>"overflow:auto"], // only set when $responsive = false
        'toolbar' =>  [

            //'{export}',
        ],


        'pjax' => false,
        'bordered' => true,
        'striped' => false,
        'condensed' => true,
        'responsive' => true,
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
?>
