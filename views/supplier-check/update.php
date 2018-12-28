<?php
/* @var $this yii\web\View */
/* @var $model app\models\Stockin */
$this->title = Yii::t('app', '编辑验厂验货供应商');
$this->params['breadcrumbs'][] = ['label' =>'验厂验货供应商列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="stockin-create">
    <?= $this->renderAjax('_form', [
        'model' => $model,
    ]) ?>
</div>
