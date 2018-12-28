<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\BasicTactics */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Basic Tactics', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="basic-tactics-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'type',
            'days_3',
            'days_7',
            'days_14',
            'days_30',
        ],
    ]) ?>

</div>
