<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\SupervisorGroupBind */

$this->title = '查看';
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', '列表'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="supervisor-group-bind-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            //'id',
            //'supervisor_id',
            'supervisor_name',
            'group_id',
            'creator_id',
            'creator_name',
            'editor_name',
            'create_time:datetime',
            'edit_time:datetime',
        ],
    ]) ?>

</div>
