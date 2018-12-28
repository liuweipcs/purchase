<?php

use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\LogisticsCarrierSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '快递管理');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="logistics-carrier-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Yii::t('app', '创建'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
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

            'id',
            'name',
            'carrier_code',
            'create_time',
            //'update_time',
            [
                'label'=>'创建人',
                'attribute' => 'supplier_codes',
                "format" => "raw",
                'value'=>
                    function($model){
                        $data  = \app\services\BaseServices::getEveryOne($model->create_id);
                        return $data;
                    },

            ],

            // 'update_id',
            //'site_url:url',

            ['class' => 'yii\grid\ActionColumn'],
        ],
        'pjax' => true,
        'bordered' => true,
        'striped' => false,
        'condensed' => true,
        'responsive' => true,
        'hover' => true,
        'floatHeader' => true,
        'showPageSummary' => false,
        'toggleDataOptions' =>[
            'maxCount' => 10000,
            'minCount' => 1000,
            'confirmMsg' => Yii::t(
                'app',
                'There are {totalCount} records. Are you sure you want to display them all?',
                ['totalCount' => number_format($dataProvider->getTotalCount())]
            ),
            'all' => [
                'icon' => 'resize-full',
                'label' => Yii::t('app', '所有'),
                'class' => 'btn btn-default',

            ],
            'page' => [
                'icon' => 'resize-small',
                'label' => Yii::t('app', '单页'),
                'class' => 'btn btn-default',

            ],
        ],
        'exportConfig' => [
            GridView::EXCEL => [],
        ],
        'panel' => [
            //'heading'=>'<h3 class="panel-title"><i class="glyphicon glyphicon-globe"></i> Countries</h3>',
            'type'=>'success',
            //'after'=>Html::a('<i class="glyphicon glyphicon-repeat"></i> 刷新', ['index'], ['class' => 'btn btn-info']),
            //'footer'=>true
        ],
    ]); ?>
</div>
