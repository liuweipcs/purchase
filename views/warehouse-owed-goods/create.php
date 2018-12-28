<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\PurchaseOrderPay */

$this->title = Yii::t('app', 'Create Purchase Order Pay');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Purchase Order Pays'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="purchase-order-pay-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
