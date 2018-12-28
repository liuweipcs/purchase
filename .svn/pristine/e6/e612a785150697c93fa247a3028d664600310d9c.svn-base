<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\PurchaseQc */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Purchase Qcs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="purchase-qc-view">

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
            'express_no',
            'pur_number',
            'warehouse_code',
            'supplier_code',
            'supplier_name',
            'sku',
            'name',
            'buyer',
            'handle_type',
            'qty',
            'delivery_qty',
            'presented_qty',
            'check_qty',
            'good_products_qty',
            'bad_products_qty',
            'check_type',
            'note',
            'created_at',
            'creator',
            'time_handle',
            'handler',
            'time_audit',
            'auditor',
            'note_audit',
        ],
    ]) ?>

</div>
