<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use app\config\Vhelper;
use app\services\BaseServices;
use app\services\SupplierGoodsServices;
use app\models\ProductProvider;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ProductSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '分类与采购员关系列表');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="product-index">

    <h1><?php //echo Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?php echo Html::a(Yii::t('app', '创建'), ['create-purchase-bind'], ['class' => 'btn btn-success']) ?>
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
                'label'=>'产品线编号',
                //'attribute'=>'category_id',
                'format'=>'raw',
                'value' => function ($model) {
                    return $model->category_id;
                }
            ],
            [
                'label'=>'采购员编号',
                //'attribute'=>'buyer',
                'format'=>'raw',
                'value' => function ($model) {
                    return $model->buyer;
                }
            ],
            [
                'attribute'=>'cate_name',
                'format'=>'raw',
                'value' => function ($model) {
                    $line = \app\models\ProductLine::find()->where(['product_line_id'=>$model->category_id])->one();
                    return !empty($line) ? $line->linelist_cn_name : '';
                    //return $model->cate_name;
                }
            ],
            [
                'attribute'=>'buyer_name',
                'format'=>'raw',
                'value' => function ($model) {
                    return $model->buyer_name;
                }
            ],
            [
                'attribute'=>'bind_time',
                'format'=>'raw',
                'value' => function ($model) {
                    return $model->bind_time;
                }
            ],
            [
                'attribute'=>'bind_name',
                'format'=>'raw',
                'value' => function ($model) {
                    return $model->bind_name;
                }
            ],




            [
                'class' => 'kartik\grid\ActionColumn',
                'dropdown' => false,
                'width'=>'180px',
                'template' => '{update1}{delete1}',
                'buttons'=>[
                    'edit1' => function ($url, $model, $key) {
                        return Html::a('<i class="glyphicon glyphicon-eye-open"></i> 查看', ['view','id'=>$key], [
                            'title' => Yii::t('app', '查看'),
                            'class' => 'btn btn-xs red'
                        ]);
                    },
                    'update1' => function ($url, $model, $key) {
                        return Html::a('<i class="glyphicon glyphicon-pencil"></i> 更新', ['update1','id'=>$key], [
                            'title' => Yii::t('app', '更新 '),
                            'class' => 'btn btn-xs purple'
                        ]);
                    },

                    'delete1' => function ($url, $model, $key) {
                        return Html::a('<i class="glyphicon glyphicon-trash"></i>删除', ['delete1', 'id' => $key], [
                            'title' => Yii::t('app', '删除'),
                            'class' => 'btn btn-xs red ajax-get confirm'
                        ]);
                    },

                ],

            ],
        ],

        //'containerOptions' => ["style"=>"overflow:auto"], // only set when $responsive = false
        'toolbar' =>  [

            //'{export}',
        ],

        'pjax' => true,
        'bordered' => true,
        'striped' => false,
        'condensed' => true,
        'responsive' => true,
        'hover' => true,
        'floatHeader' => false,
        'showPageSummary' => false,

        'exportConfig' => [
            GridView::EXCEL => [],
        ],
        'panel' => [
            //'heading'=>'<h3 class="panel-title"><i class="glyphicon glyphicon-globe"></i> Countries</h3>',
            'type'=>'success',
            // 'after'=>Html::a('<i class="glyphicon glyphicon-repeat"></i> 刷新', ['index'], ['class' => 'btn btn-info']),
            //'footer'=>true
        ],
    ]); ?>

</div>