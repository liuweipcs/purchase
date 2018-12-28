<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use mdm\admin\components\Helper;
/* @var $this yii\web\View */
/* @var $searchModel app\models\BulletinBoardSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '公告栏列表');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bulletin-board-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?php
        if(Helper::checkRoute('create')) {
            echo Html::a(Yii::t('app', '创建公告'), ['create'], ['class' => 'btn btn-success']);
        }

        ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        'pager'=>[
            //'options'=>['class'=>'hidden']//关闭分页
            'firstPageLabel'=>"首页",
            'prevPageLabel'=>'上一页',
            'nextPageLabel'=>'下一页',
            'lastPageLabel'=>'末页',
        ],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'title',
            /*[
                'attribute' => 'content',
                "format" => "raw",
                'value'=>
                    function($model){
                        return  $model->content;   //主要通过此种方式实现
                    },

            ],*/
            'create_id',
            'create_time',
            // 'update_time',

            ['class' => 'yii\grid\ActionColumn',
             'template' => Helper::filterActionColumn('{view}{update}{delete}'),
            ],
        ],
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
