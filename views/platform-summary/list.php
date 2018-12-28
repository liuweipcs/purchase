<?php
use kartik\grid\GridView;
/* @var $this yii\web\View */
/* @var $searchModel app\models\TodayListSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '销售备货统计';
$this->params['breadcrumbs'][] = $this->title;
?>
<style>
    .pps{
        font-size: 16px;
        padding: 0 0;
    }
</style>

<div class="purchase-order-index">
    <?= $this->render('_list-search', ['model' => $searchModel]); ?>
    <p class="clearfix"></p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'options'=>[
            'id'=>'sale-list',
        ],
        'filterSelector' => "select[name='".$dataProvider->getPagination()->pageSizeParam."'],input[name='".$dataProvider->getPagination()->pageParam."']",
        'pager'=>[
            'options'=>['class' => 'pagination','style'=> "display:block;"],
            'class'=>\liyunfang\pager\LinkPager::className(),
            'pageSizeList' => [20, 50, 100, 200],
            'firstPageLabel'=>"首页",
            'prevPageLabel'=>'上一页',
            'nextPageLabel'=>'下一页',
            'lastPageLabel'=>'末页',
        ],
        'rowOptions'=>function($model){
          if($model['sales'] == '{{总计}}'){
              return ['style'=>'background-color:#66FFFF;'];
          }
        },
        'columns' => [
            [
                'label'=>'分组',
                'value'=>function($model){
                    return $model['group_id'];
                },
                'group'=>true,
                'vAlign'=>'button',
            ],
            [
                'label'=>'销售',
                'value'=>function($model){
                    return $model['sales'];
                }
            ],
            [
                'label'=>'采购金额',
                'value'=>function($model){
                    return $model['total'];
                },
                'pageSummary' => true
            ],
            [
                'label'=>'采购数量',
                'value'=>function($model){
                    return $model['pur_num'];
                },
                'pageSummary' => true
            ],
            [
                'label'=>'在途金额',
                'value'=>function($model){
                    return $model['left_arrive'];
                },
                'pageSummary' => true
            ],
            [
                'label'=>'在途数量',
                'value'=>function($model){
                    return $model['left_num'];
                },
                'pageSummary' => true
            ],
            [
                'label'=>'库存金额',
                'value'=>function($model){
                    return '暂无';
                }
            ],
            [
                'label'=>'库存数量',
                'value'=>function($model){
                    return '暂无';
                }
            ],

        ],
        'containerOptions' => ["style"=>"overflow:auto"], // only set when $responsive = false
        'toolbar' =>  [],


        'pjax' => false,
        'bordered' => true,
        'striped' => false,
        'condensed' => true,
        'responsive' => true,
        'hover' => true,
        'floatHeader' => false,
        'showPageSummary' => true,

        'exportConfig' => [
            GridView::EXCEL => [],
        ],
        'panel' => [
            //'heading'=>'<h3 class="panel-title"><i class="glyphicon glyphicon-globe"></i> Countries</h3>',
            'type'=>'success',
            //'after'=>Html::a('<i class="glyphicon glyphicon-repeat"></i> 刷新', ['index'], ['class' => 'btn btn-info']),
        ],
    ]); ?>
</div>
