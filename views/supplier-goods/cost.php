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
        ])->label('价格变化时间'); ?>
    </div>

    <div class="col-md-1"><?= $form->field($searchModel,'sku')->textInput(['placeholder'=>''])->label('人员搜索') ?></div>

    <div class="col-md-1"><?= $form->field($searchModel,'sku')->textInput(['placeholder'=>''])->label('供应商搜索') ?></div>

    <div class="form-group col-md-2" style="margin-top: 24px;">
        <?= Html::submitButton(Yii::t('app', '搜索'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', '重置'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

    <?php //$this->render('_search', ['model' => $searchModel]); ?>
    <p class="clearfix"></p>

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
                'label' =>'Souring人员',
                'format'=>'raw',
                'value' => function($model, $key, $index, $column){
                    return  '职工C';
                },

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
                    return date('Y-m-d H:i:s',time());
                }
            ],
            [
                'label' => '供应商',
                'format'=> 'raw',
                'value' => function($model){
                    return '在售';
                }
            ],
            [
                'label' => '价格变化时间',
                'format'=> 'raw',
                'value' => function($model){
                    return date('Y-m-d H:i:s',time());
                }
            ],
            [
                'label' => '原价',
                'format'=> 'raw',
                'value' => function($model){
                    return '11';
                }
            ],
            [
                'label' => '现价',
                'format'=> 'raw',
                'value' => function($model){
                    return '22';
                }
            ],
            [
                'label' => '价格变化幅度',
                'format'=> 'raw',
                'value' => function($model){
                    return '11';
                }
            ],
            [
                'label' => '采购数量',
                'format'=> 'raw',
                'value' => function($model){
                    return '10';
                }
            ],
            [
                'label' => '价格变化金额',
                'format'=> 'raw',
                'value' => function($model){
                    return '110';
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
<div style="float: right"><?= '降本总金额：11元';?></div>
<div style="float: clear"></div>
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
