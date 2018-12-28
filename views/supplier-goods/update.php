<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Stockin */
$this->title = Yii::t('app', '更新报价');
?>
<div class="stockin-create">


    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
