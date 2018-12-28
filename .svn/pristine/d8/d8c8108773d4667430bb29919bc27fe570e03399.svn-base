<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\PurchaseReceive */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Purchase Receives', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="purchase-receive-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'pur_number',
            'supplier_code',
            'supplier_name',
            'buyer',
            'sku',
            'name',
            'qty',
            'delivery_qty',
            'presented_qty',
            'receive_type',
            'handle_type',
            'handler',
            'auditor',
            'bearer',
            'created_at',
            'time_handle',
            'time_audit',
            'receive_status',
            'creator',
            'price',
            'note',
            'note_handle',
            'note_audit',
        ],
    ]) ?>

</div>
