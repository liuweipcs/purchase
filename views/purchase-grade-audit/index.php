<?php

use yii\helpers\Html;
use yii\grid\GridView;
use app\models\PurchaseUser;
use app\services\BaseServices;
use app\services\PurchaseOrderServices;

/* @var $this yii\web\View */
/* @var $searchModel app\models\PurchaseGradeAuditSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '采购审核管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="purchase-grade-audit-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?php
        if(\mdm\admin\components\Helper::checkRoute('create'))
        {
            echo Html::a('添加', ['create'], ['class' => 'btn btn-success']);
        }
        ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            [
                'attribute'=>'audit_user',
                'value'=>function($model){
                    return $model->audit_user;
                },
                'filter'=>BaseServices::getEveryOne(),
            ],
            [
                'attribute'=>'type',
                'value'=>function($model){
                    return PurchaseUser::getUserType()[$model->type];
                },
                'filter'=>PurchaseUser::getUserType(),
            ],
            [
                'label'=> '采购小组',
                'attribute'=>'audit_id',
                'value'=>function($model){
                    $group_id = PurchaseUser::getGroupId(null,$model->audit_user);
                    if (!empty($group_id)) {
                        return !empty($group_id) ? PurchaseOrderServices::getPurchaseGroup()[$group_id] : '';
                    }
                },
            ],
            [
                'label'=> '用户级别',
                'attribute'=>'audit_id',
                'value'=>function($model){
                    $grade = PurchaseUser::getGrade(null,$model->audit_user);
                    if (!empty(Yii::$app->params['grade'][$grade])) {
                        return Yii::$app->params['grade'][$grade];
                    }
                },
            ],
            'audit_price',
            'create_user',
            'create_time',

            [
                'header'=>'操作',
                'class' => 'yii\grid\ActionColumn',
                'template'=>\mdm\admin\components\Helper::filterActionColumn('{delete}{update}{create}'),
            ],
        ],
    ]); ?>
</div>
