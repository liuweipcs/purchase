<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Product */

$this->title = '产品详情';
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', '产品列表'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-view">


    <p>
        <?= Html::a(Yii::t('app', '更新'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'sku',
            'product_category_id',
            'product_status',
            'uploadimgs',
            'product_cn_link',
            'product_en_link',
            'create_id',
            'create_time',
            'product_cost',
        ],
    ]) ?>

</div>
