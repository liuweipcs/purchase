<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\ExchangeGoods */

$this->title = Yii::t('app', 'Create Exchange Goods');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Exchange Goods'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="exchange-goods-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
