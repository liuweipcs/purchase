<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use app\services\CommonServices;
use mdm\admin\components\Helper;
/* @var $this yii\web\View */
/* @var $searchModel app\models\WarehouseSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
use app\services;
$this->title = '仓库管理';
$this->params['breadcrumbs'][] = $this->title;
//仓库补货策略
Modal::begin([
    'id' => 'warehouse-create',
    'header' => '<h4 class="modal-title">系统信息</h4>',
    'size'=>'modal-lg',
    'footer' => '<a href="#" class="btn btn-primary" data-dismiss="modal">Close</a>',
]);
Modal::end();
$url_warehouse = Url::toRoute('warehouse-create');
$url_warehouse_add = Url::toRoute('warehouse-add');
$js = <<<JS
$("a#warehouse-create").click(function(){
    var ids = $('#grid_warehouse').yiiGridView('getSelectedRows');
    if(ids==''){
        alert('请先选择需要生成的数据!');
        return false;
    }else{
        var url=$(this).attr("href");
        url=url+'?ids='+ids;
        $(this).attr('href',url);
    }
    $.get('{$url_warehouse}', {ids:ids},
        function (tmp) {
            $('#warehouse-create').find('.modal-body').html(tmp);
        }
    );
});
   $(".warehouse-create").click(function(){
        var url=$(this).attr("href");
        $.get(url, {},
            function (tmp) {
                $('#warehouse-create').find('.modal-body').html(tmp);
            }
        );
   });
   
    $("#warehouse_add").on('click', function(){
        $.get('{$url_warehouse_add}', function (tmp) {
            $('#warehouse-create').find('.modal-body').html(tmp);
        });
    });
JS;
$this->registerJs($js);
?>
<div class="warehouse-index">
    <?php  echo $this->render('_search', ['model' => $searchModel]); ?>
    <p class="clearfix"></p>
    <p>
        <?= Html::a('仓库补货策略', '#', ['class' => 'btn btn-success ','id'=>'warehouse-create','data-toggle' => 'modal','data-target' => '#warehouse-create']) ?>
        <?php if(Helper::checkRoute('warehouse-add'))
        {
            echo Html::a('添加仓库', '#', ['class' => 'btn btn-success ', 'id' => 'warehouse_add', 'data-toggle' => 'modal', 'data-target' => '#warehouse-create']);
        }
        ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'options'=>[
            'id'=>'grid_warehouse',
        ],
        'pager'=>[
            //'options'=>['class'=>'hidden']//关闭分页
            'firstPageLabel'=>"首页",
            'prevPageLabel'=>'上一页',
            'nextPageLabel'=>'下一页',
            'lastPageLabel'=>'末页',
        ],
        //'filterModel' => $searchModel,
        'columns' => [
             ['class' => 'yii\grid\CheckboxColumn','name'=>'warehouse_code'],
            'id',
            'warehouse_name',
            [
                'attribute'=>'warehouse_type',
                'value'=>function($data){
                    return Yii::$app->params['warehouse'][$data->warehouse_type];
                }
            ],
            [
                'attribute'=>'is_custody',
                'value'=>function($data){
                    return $data->is_custody?'是':'否';
                }
            ],
            'warehouse_code',
            // 'country',
            // 'state',
            // 'city',
            // 'address',
            // 'telephone',
            // 'fax',
            // 'zip_code',
            // 'remark',
            // 'use_status',
            // 'create_user_id',
            // 'modify_user_id',
             'create_time',
            // 'modify_time',
            [
                'attribute'=>'pattern',
                'value'=>function($data){
                    $tmp='';
                    if($data->pattern=='min'){
                        $tmp='最小';
                    }elseif($data->pattern=='def'){
                        $tmp='默认';
                    }else{
                        $tmp='无';
                    }
                    return $tmp;
                }
            ],
            [
                'label'=>'补货策略',
                'attribute'=>'SKU',
                'format' => 'raw',
                'value'=>function($data){
                    if(isset($data->warehouseMin['warehouse_code'])){
                        $res = CommonServices::getTactics($data->warehouseMin['warehouse_code'], 'min');
                        return $res;
                    }else{
                        return '无';
                    }
                }
            ],
            [
                'attribute'=>'use_status',
                'format' => 'raw',
                'value'=>function($data){
                    return $data->use_status?'<span style="color: #00a65a">是</span>':'<span style="color: red">否</span>';
                }
            ],
            [
                'header' => '操作',
                'class' => 'yii\grid\ActionColumn',
                'template'=> '{view}&nbsp;{update}',
                'headerOptions' => ['width' => '140'],
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        return Html::a('补货策略', ['warehouse-create', 'ids' => $key], ['class' => "btn btn-xs btn-success warehouse-create",'data-toggle' => 'modal','data-target' => '#warehouse-create', 'title' => '查看']);
                    },
                    'update' => function ($url, $model, $key) {
                        return Html::a('编辑', ['update', 'id' =>$key], ['class' => "btn btn-xs btn-success", 'title' => '编辑']);
                    },
//                    'update' => function ($url, $model, $key) {
//                        return Html::a('操作日志', ['warehouse-create', 'ids' => $key], ['class' => "btn btn-xs btn-info warehouse-create",'data-toggle' => 'modal','data-target' => '#warehouse-create',]);
//                    },
                ]
            ],
        ],
        'containerOptions' => ["style"=>"overflow:auto"], // only set when $responsive = false
        'toolbar' =>  [

           // '{export}',
           // '{toggleData}'
        ],


        'pjax' => true,
        'bordered' => true,
        'striped' => false,
        'condensed' => true,
        'responsive' => true,
        'hover' => true,
        'floatHeader' => false,
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
            //'heading'=>'<h3 class="panel-title"><i class="glyphicon glyphicon-globe"></i> Countries</h3>',
            'type'=>'success',
            //'after'=>Html::a('<i class="glyphicon glyphicon-repeat"></i> 刷新', ['index'], ['class' => 'btn btn-info']),
            //'footer'=>true
        ],
    ]);

    ?>
</div>