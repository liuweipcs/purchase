<?php
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use app\models\OperatLog;

/* @var $this yii\web\View */
/* @var $searchModel app\models\OperatLogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '系统操作日志');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="operat-log-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?php // Html::a(Yii::t('app', 'Create Operat Log'), ['create'], ['class' => 'btn btn-success']) ?>
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
                'attribute'=>'type',
                'value'=>function($model){
                    return OperatLog::getType()[$model->type];
                },
                'filter'=>OperatLog::getType(),
                'headerOptions'=>['width'=>'8%'],
            ],

            [
                'attribute'=>'ip',
                'headerOptions'=>['width'=>'10%'],
            ],

            [
                'attribute'=>'module',
                'headerOptions'=>['width'=>'10%'],
            ],

            [
                'attribute'=>'username',
                'headerOptions'=>['width'=>'10%'],
            ],

            [
                'attribute'=>'content',
                'headerOptions'=>['width'=>'50%'],
            ],

            'create_date',

        ],
        'containerOptions' => ["style"=>"overflow:auto"], // only set when $responsive = false
        'toolbar' =>  [
            //'{export}',
        ],

        'pjax' => false,
        'bordered' =>true,
        'striped' => false,
        'condensed' => true,
        'responsive' => true,
        'hover' => true,
        'floatHeader' => false,
        'showPageSummary' => false,

        'exportConfig' => [
            //GridView::EXCEL => [],
        ],
        'panel' => [
            'type'=>'success',
        ],
    ]); ?>
    <?php Pjax::end(); ?></div>
