<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use app\services\SupplierServices;
use app\services\PurchaseOrderServices;

/* @var $this yii\web\View */
/* @var $searchModel app\models\LogisticsCarrierSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '银行卡管理');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="logistics-carrier-index">

    <?php  echo $this->render('_search', ['model' => $searchModel]); ?>
    <p class="clearfix"></p>
    <p>
        <?= Html::a(Yii::t('app', '创建'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pager'=>[
            //'options'=>['class'=>'hidden']//关闭分页
            'firstPageLabel'=>"首页",
            'prevPageLabel'=>'上一页',
            'nextPageLabel'=>'下一页',
            'lastPageLabel'=>'末页',
        ],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'label'=>'开户银行',
                'attribute' => 'branch',
                "format" => "raw",
                'value'=>
                    function($model){
                        $data  = Yii::t('app','总行:').SupplierServices::getPayBank($model->head_office).'<br/>';
                        $data .= Yii::t('app','支行:').$model->branch.'<br/>';
                        return $data;
                    },

            ],
            [
                'label'=>'开户账号信息',
                'attribute' => 'account_number',
                "format" => "raw",
                'value'=>
                    function($model){
                        $data  = Yii::t('app','开户人:').$model->account_holder.'<br/>';
                        $data .= Yii::t('app','帐号:').$model->account_number.'<br/>';
                        return $data;
                    },

            ],
            [
                'label'=>'账号标志',
                'attribute' => 'account_sign',
                'value'=>
                    function($model){


                        $a=$model->account_sign ? $model->account_sign : 1;

                        return  PurchaseOrderServices::getAccountSign($a);   //主要通过此种方式实现
                    },

            ],
            [
                'label'=>'账号简称',
                'attribute'=>'account_abbreviation',
                'value'=>function($model){
                    return $model->account_abbreviation;
                }
            ],
            [
                'label'=>'支付类型',
                'attribute' => 'payment_types',
                'value'=>
                    function($model){
                        if (empty($model->payment_types)){
                            return '';
                        }

                        return  PurchaseOrderServices::getPaymentTypes($model->payment_types);   //主要通过此种方式实现
                    },

            ],
            [
                'label'=>'k3账号',
                'attribute'=>'k3_bank_account',
                'value'=>
                    function($model){
                        return $model->k3_bank_account;   //主要通过此种方式实现
                    },

            ],
            [
                'label'=>'状态',
                'attribute' => 'supplier_codes',
                'value'=>
                    function($model){
                        if (empty($model->status)){
                            return '';
                        }

                        return  SupplierServices::getStatus($model->status);   //主要通过此种方式实现
                    },

            ],
            [
                'label'=>'备注',
                'attribute' => 'supplier_codes',
                'value'=>
                    function($model){
                        return  $model->remarks;   //主要通过此种方式实现
                    },

            ],
            [
                'label'=>'时间',
                'attribute' => 'supplier_codes',
                "format" => "raw",
                'value'=>
                    function($model){
                        $data  = Yii::t('app','创建时间:').$model->create_time.'<br/>';
                        $data .= Yii::t('app','更新时间:').$model->update_time.'<br/>';
                        return $data;
                    },

            ],


            [
                'class' => 'kartik\grid\ActionColumn',
                'dropdown' => false,
                'width'=>'180px',
                'template' => '{update} {disabled}',
                'buttons'=>[
                    'update' => function ($url, $model, $key) {
                        return Html::a('<i class="glyphicon glyphicon-eye-open"></i> 编辑', ['update','id'=>$key], [
                            'title' => Yii::t('app', '编辑'),
                            'class' => 'btn btn-xs red'
                        ]);
                    },
                    'disabled' => function ($url, $model, $key) {
                        if ($model->status == 1)
                        {
                            return Html::a('<i class="glyphicon glyphicon-pencil"></i> 停用', ['disabled', 'id' => $key,'status'=>'2'], [
                                'title' => Yii::t('app', '停用'),
                                'class' => 'btn btn-xs purple',
                                'data' => [
                                    'confirm' => '确定要停用么?',
                                ],
                            ]);
                        }else {
                            return Html::a('<i class="glyphicon glyphicon-pencil"></i> 启用', ['disabled', 'id' => $key,'status'=>'1'], [
                                'title' => Yii::t('app', '启用'),
                                'class' => 'btn btn-xs purple',
                                'data' => [
                                    'confirm' => '确定要启用么?',
                                ],
                            ]);
                        }
                    },],

            ],
        ],
        'pjax' => true,
        'bordered' => true,
        'striped' => false,
        'condensed' => true,
        'responsive' => true,
        'hover' => true,
        'floatHeader' => true,
        'showPageSummary' => false,
        'toggleDataOptions' =>[
            'maxCount' => 10000,
            'minCount' => 1000,
            'confirmMsg' => Yii::t(
                'app',
                'There are {totalCount} records. Are you sure you want to display them all?',
                ['totalCount' => number_format($dataProvider->getTotalCount())]
            ),
            'all' => [
                'icon' => 'resize-full',
                'label' => Yii::t('app', '所有'),
                'class' => 'btn btn-default',

            ],
            'page' => [
                'icon' => 'resize-small',
                'label' => Yii::t('app', '单页'),
                'class' => 'btn btn-default',

            ],
        ],
        'exportConfig' => [
            GridView::EXCEL => [],
        ],
        'panel' => [
            'heading'=>'<h3 class="panel-title"><i class="glyphicon glyphicon-globe"></i> Countries</h3>',
            'type'=>'success',
            'after'=>Html::a('<i class="glyphicon glyphicon-repeat"></i> 刷新', ['index'], ['class' => 'btn btn-info']),
            //'footer'=>true
        ],
    ]); ?>
</div>
