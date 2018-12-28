<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\BulletinBoard */

$this->title = Yii::t('app', '创建公告');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', '公告列表'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bulletin-board-create">


    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
