<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\SkuStatisticsLog */

$this->title = 'Update Sku Statistics Log: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Sku Statistics Logs', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="sku-statistics-log-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
