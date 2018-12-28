<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\services\BaseServices;

/* @var $this yii\web\View */
/* @var $model app\models\Warehouse */

$this->title = '添加仓库';
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-create">
    <?= $this->render('_form', [
        'model' => $model
    ]) ?>
</div>
