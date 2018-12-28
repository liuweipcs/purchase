<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\BasicTactics */

$this->title = 'Create Basic Tactics';
$this->params['breadcrumbs'][] = ['label' => 'Basic Tactics', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="basic-tactics-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
