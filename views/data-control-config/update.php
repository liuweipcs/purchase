<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\DataControlConfig */

$this->title = Yii::t('app', '编辑');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', '列表'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Yii::t('app', '编辑');
?>
<div class="data-control-config-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
