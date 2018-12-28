<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\GroupAuditConfig */

$this->title = Yii::t('app', '编辑');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', '列表'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="group-audit-config-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
