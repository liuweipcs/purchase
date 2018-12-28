<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\BasicTacticsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Basic Tactics';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="basic-tactics-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Basic Tactics', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'type',
            'days_3',
            'days_7',
            'days_14',
            // 'days_30',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
