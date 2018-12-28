<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\PurchaseOrder */

$this->title = '编辑权限';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="purchase-order-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>