<?php
use app\config\Vhelper;
use mdm\admin\components\Helper;
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use app\models\User;

use app\models\PurchaseTactics;
use app\models\PurchaseTacticsSuggest;
use app\models\PurchaseTacticsWarehouse;
use app\models\PurchaseTacticsSearch;

$this->title = 'MRP备货逻辑配置';
$this->params['breadcrumbs'][] = $this->title;


Modal::begin([
    'id' => 'create-modal',
    'header' => '<h4 class="modal-title" id="modal-title">配置补货策略</h4>',
    'size'=>'modal-lg',
    'options'=>[
        'data-backdrop'=>'static',//点击空白处不关闭弹窗
    ],
]);
Modal::end();


Modal::begin([
    'id' => 'sku-tactics-create-modal',
    'header' => '<h4 class="modal-title" id="sku-tactics-modal-title">配置SKU补货策略</h4>',
    'size'=>'modal-lg',
    'options'=>[
        'data-backdrop'=>'static',//点击空白处不关闭弹窗
    ],
]);
Modal::end();


Modal::begin([
    'id' => 'view-modal',
    'header' => '<h4 class="modal-title" id="modal-title">查看配置详情</h4>',
    'size'=>'modal-lg',
    'options'=>[
        'data-backdrop'=>'static',//点击空白处不关闭弹窗
    ],
]);
Modal::end();

Modal::begin([
    'id' => 'import-modal',
    'header' => '<h4 class="modal-title" id="modal-title">配置SKU补货策略-导入SKU</h4>',
    'size'=>'modal-lg',
    'options'=>[
        'data-backdrop'=>'static',//点击空白处不关闭弹窗
    ],
]);
Modal::end();

?>

    <div class="panel panel-default">
        <div class="panel-body">
            <?php echo $this->render('_search', ['model' => $searchModel]); ?>
        </div>
        <p class="clearfix"></p>
        <div class="panel-footer">
            <?php
            if ($searchModel->tactics_type == 1 AND Helper::checkRoute('create')) {
                echo Html::a('创建MRP逻辑', ['create-data'], ['class' => 'btn btn-sm btn-primary', 'id' => 'create', 'data-toggle' => 'modal', 'data-target' => '#create-modal','title' => '仓库补货策略']);
            }?>
            <?php
            if ($searchModel->tactics_type == 2 AND Helper::checkRoute('create-sku-tactics')) {
                echo Html::a('创建SKU补货策略', ['create-sku-tactics'], ['class' => 'btn btn-sm btn-primary', 'id' => 'create-sku-tactics', 'data-toggle' => 'modal', 'data-target' => '#sku-tactics-create-modal','title' => 'SKU补货策略']);
            }?>
            <?php
            if (Helper::checkRoute('batch-delete')) {
                echo Html::button('批量删除', ['class' => 'btn btn-sm btn-warning', 'id' => 'batch-delete']);
            }
            echo Html::a('导入', ['import'], ['class' => 'btn btn-sm btn-success', 'id' => 'import', 'data-toggle' => 'modal', 'data-target' => '#import-modal','style' => 'display:none;']);
            ?>

        </div>
    </div>
    <?php if(isset($searchModel->tactics_type) AND $searchModel->tactics_type == 2){
            $switcher_type = '<div class="btn-group">
                <a href="?PurchaseTacticsSearch[tactics_type]=1" class="btn btn-sm btn-default">仓库补货策略列表</a>
                <span class="btn btn-sm btn-danger" disabled="disabled">sku补货策略列表</span>
            </div>';
        }else{
            $switcher_type = '<div class="btn-group" >
                <span class="btn btn-sm btn-danger" disabled="disabled">仓库补货策略列表</span>
                <a href="?PurchaseTacticsSearch[tactics_type]=2" class="btn btn-default btn-sm">sku补货策略列表</a>
            </div>';
    } ?>

<?php

if($searchModel->tactics_type == 2){
    $columns =
        [
            'label'=>'SKU',
            'attribute'=>'SKU',
            'value'=>function($model){
                $html = $model->sku;
                return $html;
            }
        ];
}else{
    $columns =
        [
            'label'=>'备货名称',
            'attribute'=>'tactics_name',
            'value'=>function($model){
                $html = $model->tactics_name;
                return $html;
            }
        ];
}

echo GridView::widget([
    'dataProvider'=>$dataProvider,
    'filterSelector' => "select[name='".$dataProvider->getPagination()->pageSizeParam."'],input[name='".$dataProvider->getPagination()->pageParam."']",

    'pager'=>[
        'options'=>['class' => 'pagination','style'=> "display:block;"],
        'class'=>\liyunfang\pager\LinkPager::className(),
        'pageSizeList' => [5,20, 50, 100, 200],
        'firstPageLabel'=>"首页",
        'prevPageLabel'=>'上一页',
        'nextPageLabel'=>'下一页',
        'lastPageLabel'=>'末页',
    ],
    'options'=>[
        'id'=>'tactics_list',
    ],
    'columns'=>[
        [
            'class'=>'kartik\grid\CheckboxColumn',
            'name'=>"id",
            'checkboxOptions'=>function ($model,$key,$index,$column){
                return ['value'=>$model->id];
            }
        ],

        $columns,

        [
            'label'=>'适用仓库',
            'format'=>'raw',
            'attribute'=>'warehouse_list',
            'width' => '25%',
            'value'=>function($model){
                $warehouseList = PurchaseTacticsSearch::getWarehouseList();

                $purchaseTacticsWarehouse = $model->purchaseTacticsWarehouse;

                $html = '';
                foreach($purchaseTacticsWarehouse as $value){
                    $html .= $warehouseList[$value->warehouse_code].'、';
                }
                return trim($html,'、');
            }
        ],
        [
            'label'=>'创建人',
            'format'=>'raw',
            'attribute'=>'creator',
            'value'=>function($model){
                $userInfo = User::findOne($model->creator);
                if($userInfo){
                    return $userInfo->username;
                }
            }
        ],
        [
            'label'=>'创建时间',
            'format'=>'raw',
            'attribute'=>'created_at',
            'value'=>function($model){
                $html = $model->created_at;
                return $html;
            }
        ],
        [
            'label'=>'是否启用',
            'format'=>'raw',
            'attribute'=>'status',
            'width' => '100px;',
            'value'=>function($model){
                if($model->status == 1){
                    $html = '<span class="glyphicon glyphicon-ok" style="color: green"></span>';
                }else{
                    $html = '<span class="glyphicon glyphicon-remove" style="color: red"></span>';
                }
                return $html;
            }
        ],
        [
            'header' => '操作',
            'class' => 'yii\grid\ActionColumn',
            'template'=> ' {status} {edit} {view}',
            'headerOptions' => ['width' => '140'],
            'buttons' => [
                'edit' => function ($url, $model, $key) {
                    if($model->tactics_type == 1){
                        return Html::a('编辑',['create-data', 'id' => $model->id], ['class' => "btn btn-xs btn-success",'id' => 'create', 'title' => '编辑', 'data-toggle' => 'modal', 'data-target' => '#create-modal']);
                    }else{
                        return Html::a('编辑', ['create-sku-tactics','id' => $model->id], ['class' => 'btn btn-xs btn-success', 'id' => 'create-sku-tactics','title' => '编辑', 'data-toggle' => 'modal', 'data-target' => '#sku-tactics-create-modal']);
                    }
                },
                'view' => function ($url, $model, $key) {
                    return Html::a('查看',['view', 'id' => $model->id], ['class' => "btn btn-xs btn-warning",'id' => 'view', 'title' => '查看', 'data-toggle' => 'modal', 'data-target' => '#view-modal']);
                },
                'status' => function ($url, $model, $key) {
                    if($model->status == 1){
                        return Html::a('禁用',['change-status', 'id' => $model->id,'status' => 2], ['class' => "btn btn-xs btn-primary",'id' => 'change-status', 'title' => '禁用此策略']);
                    }else{
                        return Html::a('启用',['change-status', 'id' => $model->id,'status' => 1], ['class' => "btn btn-xs btn-primary",'id' => 'change-status', 'title' => '启用此策略']);
                    }
                },
            ]
        ],
    ],
    'containerOptions' => ["style" => "overflow:auto"],
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
        'before' => false,
        'after' => false,
        'heading' => '<h4 class="panel-title">'.$switcher_type.' </h4>',
        'type' => 'success',
        //'after'=>Html::a('<i class="glyphicon glyphicon-repeat"></i> 刷新', ['index'], ['class' => 'btn btn-info']),
    ]
]);
?>

<?php

$tactics_id     = Yii::$app->request->getQueryParam('tactics_id');
$operator       = Yii::$app->request->getQueryParam('operator');
if(isset($tactics_id) AND isset($operator) AND $operator == 'importSku'){
    $jss = <<<JS
    $(function() {
        $('#import-modal').modal('show');
        
        $('#import-modal').on('hide.bs.modal', function () {
          layer.alert('嘿，该策略还没有导入SKU，请不要关闭我哦...');
          location.reload(force);
       });
        
        function open_import_sku_view(){
            $.get($(this).attr('import'), {tactics_id:"$tactics_id" },function (data) {
                $('.modal-body').html(data);
            });
        }
        open_import_sku_view();
       
    });
JS;
    $this->registerJs($jss);
}



$deleteUrl           = Url::toRoute(['batch-delete']);


$js = <<<JS
$(function() {
    /**
     * 修改状态
     */
    $(document).on('click', '#change-status', function () {
        $.get($(this).attr('href'), { },function (data) {
            
        });
    });
    
    /**
     * 创建MRP补货策略
     */
    $(document).on('click', '#create', function () {
        $.get($(this).attr('href'), { },function (data) {
            $('#create-modal .modal-body').html(data);
        });
    });
    
    /**
     * 查看
     */
    $(document).on('click', '#view', function () {
        $.get($(this).attr('href'), { },function (data) {
            $('#view-modal .modal-body').html(data);
        });
    });
    
    /**
     * 创建SKU补货策略
     */
    $(document).on('click', '#create-sku-tactics', function () {
        $.get($(this).attr('href'), { },function (data) {
            $('#sku-tactics-create-modal .modal-body').html(data);
        });
    });
    // $('#sku-tactics-create-modal,#create-modal').on('hide.bs.modal', function () {
    //     location.reload();
    //  });
    
    
    /**
     * 删除数据
     */
    $(document).on('click',"#batch-delete",function () {
        var ids = $('#tactics_list').yiiGridView('getSelectedRows');
        if(ids == ''){
            layer.msg('请先选择！');
            return false;
        }
        
        var num = ids.length;
        if(num > 0) {
            layer.confirm('您选中'+num+'条记录，此操作不可恢复哦，是否确认删除吗？', {
                btn: ['确定','取消']
            }, function() {
                $.get("$deleteUrl", {ids:ids}, function(result) {
                    if(result) {
                        layer.msg('删除成功');
                        // window.location.reload();
                    } else {
                        layer.msg('删除失败');
                    }
                });
            });
        }
    });
    
   
});
JS;
$this->registerJs($js);


?>