<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\BasicTactics */

$this->title = 'Update Basic Tactics: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Basic Tactics', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="basic-tactics-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
