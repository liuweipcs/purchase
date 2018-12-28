<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use mdm\admin\components\Helper;
/* @var $this yii\web\View */
/* @var $searchModel app\models\BulletinBoardSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '帐号列表');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bulletin-board-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?php
        if(Helper::checkRoute('create')) {
            echo Html::a(Yii::t('app', '创建帐号'), ['create'], ['class' => 'btn btn-success']);
        }

        ?>
    </p>

<!--
    <a href="?zzh=1">子账号分流</a>


-->


    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pager'=>[
            //'options'=>['class'=>'hidden']//关闭分页
            'firstPageLabel'=>"首页",
            'prevPageLabel'=>'上一页',
            'nextPageLabel'=>'下一页',
            'lastPageLabel'=>'末页',
        ],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'account',

            'access_token',
            'refresh_token',

            // 'update_time',
            [
                'attribute' => 'status',
                "format" => "raw",
                'value'=>
                    function($model){
                        return  $model->status == 1 ? '启用' : '停用';
                    },

            ],
            'app_key',
            'secret_key',
            [
                'attribute' => 'bind_account',
                "format" => "raw",
                'value'=>
                    function($model) {
                        if($model->bind_account == 0) {
                            return '<span class="label label-success">主账号</span>';
                        } else {
                            return  \app\services\BaseServices::getEveryOne($model->bind_account);
                        }
                    },

            ],

            ['class' => 'yii\grid\ActionColumn',
             'template' => Helper::filterActionColumn('{view}{update}{delete}'),
             'buttons'=>[
                 'view' => function ($url, $model, $key) {
                     return Html::a('<i class="glyphicon glyphicon-eye-open"></i> 授权', ['view','id'=>$key], [
                         'title' => Yii::t('app', '编辑'),
                         'class' => 'btn btn-xs red',
                         'target'=>'_blank',
                     ]);
                 },
             ],
            ],
        ],
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
    ]); ?>
</div>
