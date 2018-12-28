<?php
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use app\services\BaseServices;
/* @var $this yii\web\View */
/* @var $searchModel app\models\SupervisorGroupBindSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '亚马逊销售-分组绑定');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="supervisor-group-bind-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Yii::t('app', '新增'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?php Pjax::begin(); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute'=>'group_id',
                'value'=>function($mode){
                    return $mode->group_id;
                },
               // 'filter'=>BaseServices::getAmazonGroup(),
            ],

            'supervisor_name',

            'creator_name',

            [
                'attribute'=>'create_time',
                'value'=>function($mode){
                    return date('Y-m-d H:i:s',$mode->create_time);
                },
            ],

            'editor_name',

            [
                'attribute'=>'edit_time',
                'value'=>function($mode){
                    return $mode->edit_time ? date('Y-m-d H:i:s',$mode->edit_time) : '';
                },
            ],

            [
                'header'=>'操作',
                'class' => 'yii\grid\ActionColumn',
                'template'=>'{update}{delete}',
                'buttons'=>[],
            ],
        ],

        'containerOptions' => ["style"=>"overflow:auto"], // only set when $responsive = false
        'toolbar' =>  [
            //'{export}',
        ],

        'pjax' => true,
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