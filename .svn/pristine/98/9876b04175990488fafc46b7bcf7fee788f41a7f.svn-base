<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use \app\config\Vhelper;
use app\models\ProductProvider;
use toriphes\lazyload\LazyLoad;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use app\services\FormatService;
/* @var $this yii\web\View */
/* @var $searchModel app\models\ProductSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '供应商报价系统权限管理');
$this->params['breadcrumbs'][] = $this->title;
?>
    <div class="product-index">
        <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <div class="panel panel-default">
<!--        <div class="panel-body">-->
<!--            <?php // = $this->render('_search', ['model' => $searchModel]); ?>-->
<!--        </div>-->
        <div class="panel-footer">
            <?php
            if(\mdm\admin\components\Helper::checkRoute('create')) {
                echo Html::a('新增',['create'],['class'=>'btn btn-warning create','data-toggle' => 'modal','data-target' => '#supplier-permission']);
            }
            ?>
        </div>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'pager'=>[
                'firstPageLabel'=>"首页",
                'prevPageLabel'=>'上一页',
                'nextPageLabel'=>'下一页',
                'lastPageLabel'=>'末页',
            ],
            'columns' => [
                'id',
                'module',
                'controller',
                'action',
                'parent_id',
                'type',
                'permission_name',
                [
                    'label'=>'is_show',
                    'attribute'=>'is_show',
                    'value'=>function($model){
                        return $model->is_show ==0 ? '否' :'是';
                    }
                ],
                [
                    'class' => 'kartik\grid\ActionColumn',
                    'dropdown' => false,
                    'width'=>'180px',
                    'template' =>  \mdm\admin\components\Helper::filterActionColumn('{update}'),
                    'buttons'=>[
                        'update' => function ($url, $model, $key) {
                                return Html::a('<i class="glyphicon glyphicon-pencil"></i> 编辑', ['update','id'=>$model->id], [
                                    'title' => Yii::t('app', '编辑'),
                                    'class' => 'btn btn-xs update',
                                    'data-toggle' => 'modal',
                                    'data-target' => '#supplier-permission',
                                ]);
                        },
                    ],

                ],
            ],
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
    <div id="mouse_right_menu"></div>
<?php
Modal::begin([
    'id' => 'supplier-permission',
    'header' => '',
    //'footer' => '<a href="#" class="btn btn-primary"  data-dismiss="modal"  >Close</a>',
    'closeButton' =>false,
    'size'=>'modal-lg',
    'options'=>[
        'z-index' =>'-1',

    ],
]);
Modal::end();
$commitUrl  = Url::toRoute('sample-commit');
?>
<?php
$js = <<<JS

$(document).on('click','.create',function() {
    $.get($(this).attr('href'),{},function(data) {
      $('#supplier-permission').find('.modal-body').html(data);
      $('#supplier-permission').find('.modal-header').html('新增权限');
    });
});

$(document).on('click','.update',function() {
    $.get($(this).attr('href'),{},function(data) {
      $('#supplier-permission').find('.modal-body').html(data);
      $('#supplier-permission').find('.modal-header').html('编辑权限');
    });
});

JS;
$this->registerJs($js);
?>