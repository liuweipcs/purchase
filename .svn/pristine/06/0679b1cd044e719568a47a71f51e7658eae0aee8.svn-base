<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\BulletinBoard */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Bulletin Boards'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bulletin-board-view">


<!--    <p>
        <?/*= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) */?>
        <?/*= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) */?>
    </p>-->

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'title',
            [
                'attribute' => 'content',
                "format" => "raw",
                'value'=>
                    function($model){
                        return  $model->content;   //主要通过此种方式实现
                    },

            ],
            'create_id',
            'create_time',
            //'update_time',
            [
                'label'=>'类型',
                'value'=>
                    function($model){
                       return $model->bulletinBoardType($model->bulletin_board_type);
                    },
            ],
        ],
    ]) ?>

</div>
