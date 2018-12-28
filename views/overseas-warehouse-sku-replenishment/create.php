<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\SkuSingleTacticMain */

$this->title = Yii::t('app', '创建SKU补货策略');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'SKU补货策略'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sku-single-tactic-main-create">

    <h1><?php //echo Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
