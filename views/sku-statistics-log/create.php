<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\SkuStatisticsLog */

$this->title = 'Create Sku Statistics Log';
$this->params['breadcrumbs'][] = ['label' => 'Sku Statistics Logs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sku-statistics-log-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
