<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\PurchaseReceive */

$this->title = 'Create Purchase Receive';
$this->params['breadcrumbs'][] = ['label' => 'Purchase Receives', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="purchase-receive-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
