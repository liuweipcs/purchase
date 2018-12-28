<?php
use yii\helpers\Html;
use yii\bootstrap\Modal;
use kartik\grid\GridView;
use app\models\LockWarehouseConfig;
use app\services\BaseServices;
$this->title = '锁仓列表';
$this->params['breadcrumbs'][] = '锁仓列表';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="box box-success">
    <div class="box-body">
        <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>
    <div class="box-footer clearfix">

    </div>
</div>

<input type="hidden" value="<?= $msg ?>" id="msg">
<?php
    $gridColumns = [
        [
            'class' => 'kartik\grid\CheckboxColumn',
            'name' => "id" ,
            'checkboxOptions' => function ($data) {
                return ['value' => $data->id];
            }
        ],
        [
            'label'=>'sku',
            'value'=>function($data){
                return $data->sku;
            }
        ],
        [
            'label'=>'仓库名称',
            'value'=>function($data){
                $name  =!empty($data->warehouse_code)?BaseServices::getWarehouseCode($data->warehouse_code):'';
                return $name;
            }
        ],
        [
            'label'=>'创建人',
            'value'=>function($data){
                return $data->create_user;
            }
        ],
        [
            'label'=>'创建时间',
            'value'=>function($data){
                return $data->create_time;
            }
        ],
        [
            'label'=>'更新人',
            'value'=>function($data){
                return $data->update_user;
            }
        ],
        [
            'label'=>'更新时间',
            'value'=>function($data){
                return $data->update_time;
            }
        ],
        [
            'label'=>'是否锁定',
            "format" => "raw",
            'value'=>function($data){
                return LockWarehouseConfig::getSkuStatus($data->is_lock);
            }
        ],
        [
            'label'=>'操作',
            "format" => "raw",
            'value'=>function($data){
                $arr = LockWarehouseConfig::getStatus('',$data->id);
                $html = '';
                if(!empty($arr)){
                    foreach ($arr as $key=>$value){
                        if($data->is_lock == $key) continue;
                        $html .= $value;
                    }
                }
                return $html;
            }
        ],
    ];
    ?>

    <?= GridView::widget([
    'dataProvider' => $dataProvider,
    'options'=>[
        'id'=>'grid_purchase_order',
    ],
    'filterSelector' => "select[name='".$dataProvider->getPagination()->pageSizeParam."'],input[name='".$dataProvider->getPagination()->pageParam."']",
    'pager' => [
        'options' => ['class' => 'pagination', 'style' => 'display:block;'],
        'class' => \liyunfang\pager\LinkPager::className(),
        'pageSizeList' => [10, 20, 30, 50, 100],
        'firstPageLabel' => '首页',
        'prevPageLabel' => '上一页',
        'nextPageLabel' => '下一页',
        'lastPageLabel' => '末页',
    ],
    'columns' => $gridColumns,
    'toolbar' => [],
    'condensed' => true,
    'hover' => true,
    'panel' => [
        'type' => 'success',
    ]
]);
?>

<?php
Modal::begin([
    'id' => 'create-modal',
    'header' => '<h4 class="modal-title">系统信息</h4>',
    'footer' => '<a href="#" class="btn btn-primary closes" data-dismiss="modal">关闭</a>',
    'size'=>'modal-lg',
    'options'=>[
        'data-backdrop'=>'static',//点击空白处不关闭弹窗

    ],
]);
Modal::end();

$token = Yii::$app->request->getCsrfToken();

$js = <<<JS
    //新增
    /*$(document).on('click', '.create', function() {
        layer.prompt({title: '新增', value: '', formType: 3}, function (sku, index) {
            $.ajax({
                url:'/lock-warehouse-config/create',
                data:{sku:sku},
                type: 'post',
                dataType:'json',
                success: function (data) {
                    if(data.status==1){
                        layer.msg('新增成功');
                        window.location.reload();
                    }else{
                        layer.alert(data.msg);
                    }
                }
            });
            layer.close(index);
        });
    });*/
    var msg = $("#msg").val();
    if(msg!=''){
        alert(msg);
    }
    $(document).on('click', '.create', function() {
        $.get($(this).attr('href'), {},function (data) {
            $('.modal-body').html(data);
        });
    });

    // 删除
    $(document).on('click', '.delete', function() {
        var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
        if(ids == '') {
            alert('请先勾选要删除的数据');
            return false;
        }
        if(confirm('是否确认删除')){
            $.ajax({
                url:'/lock-warehouse-config/delete-sku',
                data:{ids:ids,_csrf:"$token"},
                type: 'post',
                dataType:'json',
                success: function (data) {
                    if(data.status==1){
                        alert('删除成功');
                        window.location.reload();
                    }else{
                        alert(data.msg);
                    }
                }
            });
        }
    });
    
    //导入
    $(".import").click(function(){
        $.get($(this).attr('href'), {},
        function (data) {
            $('.modal-body').html(data);
        });
    });
    
    //更改sku状态
    $(document).on('click', '.change-status', function() {
        //需要修改的状态
        var status = $(this).attr('status');
        var id = $(this).attr('id');
        
        if(confirm('是否确认修改状态？')){
            $.ajax({
                url:'/lock-warehouse-config/change-status',
                data:{id:id,status:status,_csrf:"$token"},
                type: 'post',
                dataType:'json',
                success: function (data) {
                    if(data.status==1){
                        alert('修改成功');
                        window.location.reload();
                    }else{
                        alert(data.msg);
                    }
                }
            });
        }
    });
JS;
$this->registerJs($js);
?>
