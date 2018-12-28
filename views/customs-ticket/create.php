<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\PurchaseOrderItems */

$this->title = 'Create Purchase Order Items';
$this->params['breadcrumbs'][] = ['label' => 'Purchase Order Items', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="purchase-order-items-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
