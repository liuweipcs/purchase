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

$this->title = Yii::t('app', '供应商用户管理');
$this->params['breadcrumbs'][] = $this->title;
?>
    <div class="product-index">
        <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

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
                [
                    'label'=>'供应商编码',
                    'format'=>'raw',
                    'value'=>function($model){
                        return $model->supplier_code;
                    }
                ],
                [
                    'label'=>'供应商名称',
                    'format'=>'raw',
                    'value'=>function($model){
                        return $model->supplier_name;
                    }
                ],
                [
                    'label'=>'状态',
                    'format'=>'raw',
                    'value'=>function($model){
                        return $model->is_commit_quotes;
                     }
                ],
                [
                    'class' => 'kartik\grid\ActionColumn',
                    'dropdown' => false,
                    'width'=>'180px',
                    'template' => '{update} {delete}',
                    'buttons'=>[
                        'update' => function ($url, $model, $key) {
                            return Html::a('<i class="glyphicon glyphicon-pencil"></i> 授权', ['update','supplier_code'=>$model->supplier_code], [
                                'title' => Yii::t('app', '授权'),
                                'class' => 'btn btn-xs update',
                                'data-toggle' => 'modal',
                                'data-target' => '#supplier-user',
                            ]);
                        },
                        'delete'=>function($url,$model,$key){
                            return Html::a('<i class="glyphicon glyphicon-pencil"></i>禁用', ['delete','supplier_code'=>$model->supplier_code], [
                                'title' => Yii::t('app', '提交样品结果'),
                                'class' => 'btn btn-xs sample-result',
                                'data-toggle' => 'modal',
                                'data-target' => '#supplier-user',
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
<?php
Modal::begin([
    'id' => 'supplier-user',
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
$(document).on('click','.update',function() {
  $.get($(this).attr('href'),{},function(data) {
        $('#supplier-user').find('.modal-body').html(data);
  });
})
JS;
$this->registerJs($js);
?>