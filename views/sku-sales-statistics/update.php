<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\SkuSalesStatistics */

$this->title = 'Update Sku Sales Statistics: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Sku Sales Statistics', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="sku-sales-statistics-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
