<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Product */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-view">

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
            'sku',
            'product_category_id',
            'product_brand_id',
            'product_cost',
            'last_price',
            'last_provider_id',
            'provider_type',
            'currency',
            'product_status',
            'product_type',
            'product_weight',
            'product_length',
            'product_width',
            'product_height',
            'product_combine_code',
            'product_combine_num',
            'product_bind_code',
            'product_line_id',
            'keywords',
            'measure_info',
            'product_is_attach',
            'product_is_bak',
            'product_bak_type',
            'product_prearrival_days',
            'product_bak_days',
            'original_material_type_id',
            'product_pack_code',
            'product_package_code',
            'product_package_max_nums',
            'product_is_storage',
            'product_original_package',
            'product_is_new',
            'product_is_multi',
            'provider_level_id',
            'create_user_id',
            'modify_user_id',
            'create_time',
            'modify_time',
            'drop_shipping',
            'drop_shipping_sku',
            'product_cn_link',
            'product_en_link',
            'sku_mark',
            'product_to_way_package',
            'stock_reason',
            'product_label_proces',
            'pack_product_length',
            'pack_product_width',
            'pack_product_height',
            'gross_product_weight',
            'is_to_mid',
            'to_mid_time',
            'state_type',
            'checked_time',
            'uploadimgs',
            'label',
            'buycomp_note',
            'quality_note',
            'hot_rank',
            'min_purchase',
            'inquirer_id',
            'purchase_id',
            'aliases_name',
            'instructions',
            'quality_standard:ntext',
            'quality_lable',
            'quality_remark:ntext',
            'image_remark',
            'buy_sample_type',
            'reference_price',
            'picking_name',
            'picking_ename',
            'customs_code',
            'declare_ename',
            'declare_cname',
            'declare_price',
            'tariff',
            'tax_rate',
            'onlie_remark',
            'source',
            'is_push',
        ],
    ]) ?>

</div>
