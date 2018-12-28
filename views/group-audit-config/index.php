<?php
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use app\services\PurchaseOrderServices;

$this->title = Yii::t('app', '审核分组配置');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="group-audit-config-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Yii::t('app', '添加'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?php Pjax::begin(); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,

        'options'=>[
            'id'=>'grid_purchase_operat',
        ],

        'pager'=>[
            'firstPageLabel'=>"首页",
            'prevPageLabel'=>'上一页',
            'nextPageLabel'=>'下一页',
            'lastPageLabel'=>'末页',
        ],

        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute'=>'group',
                'value'=>function($model){
                    return Yii::$app->params['re_group'][$model->group];
                },
                'filter'=>Yii::$app->params['re_group'],
            ],

            [
                'attribute'=>'values',
                'value'=>function($model){
                    return Yii::$app->params['num_range'][$model->values];
                },
                'filter'=>Yii::$app->params['num_range'],
            ],

            'remark',

            [
                'attribute'=>'cdate',
                'format'=>['date','php:Y-m-d H:i:s'],
            ],

            [
                'header'=>'操作',
                'class' => 'yii\grid\ActionColumn',
                'template'=>'{update} {delete}',
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
        'hover' => true,
        'floatHeader' => false,
        'showPageSummary' => false,

        'panel' => [
            'type'=>'success',
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>