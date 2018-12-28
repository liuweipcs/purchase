<?php
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use app\services\PurchaseOrderServices;
use app\models\PurchaseUser;

/* @var $this yii\web\View */
/* @var $searchModel app\models\PurchaseUserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '采购用户管理');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="purchase-user-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Yii::t('app', '新增'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?php Pjax::begin(); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            //'id',
            //'pur_user_id',
            'pur_user_name',
            [
                'attribute'=>'group_id',
                'value'=>function($mode){
                    return !empty($mode->group_id) ? PurchaseOrderServices::getPurchaseGroup()[$mode->group_id] : '';
                },
                'filter'=>PurchaseOrderServices::getPurchaseGroup(),
            ],
            [
                'attribute'=>'grade',
                'value'=>function($mode){
                    return Yii::$app->params['grade'][$mode->grade];
                },
                'filter'=>Yii::$app->params['grade'],
            ],

            [
                'attribute'=>'type',
                'value'=>function($mode){
                    return PurchaseUser::getUserType()[$mode->type];
                },
                'filter'=>PurchaseUser::getUserType(),
            ],

            [
                'attribute'=>'crate_time',
                'format'=>['date','php:Y-m-d H:i:s'],
            ],

            //'crate_time:datetime',
            // 'edit_time:datetime',
            // 'creator',
            // 'editor',
            [
                'header'=>'操作',
                'class' => 'yii\grid\ActionColumn',
                'template'=>'{update} {delete}',
                'buttons'=>[],
            ],
        ],

        'containerOptions' => ["style"=>"overflow:auto"], // only set when $responsive = false
        'toolbar' =>  [
            //'{export}',
        ],

        'pjax' => false,
        'bordered' => true,
        'striped' => false,
        'condensed' => true,
        'responsive' => true,
        'hover' => true,
        'floatHeader' => false,
        'showPageSummary' => false,

        'panel' => [
            'type'=>'success',
        ],
    ]); ?>
<?php Pjax::end(); ?>
</div>