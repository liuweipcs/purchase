<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\BulletinBoard */

$this->title ='更新帐号';
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', '帐号列表'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="bulletin-board-update">


    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
