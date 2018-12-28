<?php
use yii\helpers\Html;
use yii\bootstrap\Modal;
use kartik\grid\GridView;
use app\services\SupplierServices;
use app\services\PurchaseOrderServices;
use app\services\BaseServices;
use app\models\PurchaseOrderPay;
$this->title = 'FBA请款单';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="panel panel-success">
    <div class="panel-body">
        <?= $this->render('_search', ['model' => $searchModel]); ?>
    </div>
</div>
<?php if($source == 1): ?>
    <div class="btn-group" style="margin-bottom: 10px;">
        <span class="btn btn-danger" disabled="disabled">合同单</span>
        <a href="?source=2" class="btn btn-default">网采单</a>
    </div>
    <?= $this->render('index-con', ['searchModel' => $searchModel, 'dataProvider'=>$dataProvider]); ?>
<?php else: ?>
    <div class="btn-group" style="margin-bottom: 10px;">
        <a href="?source=1" class="btn btn-default">合同单</a>
        <span class="btn btn-danger" disabled="disabled">网采单</span>
    </div>
    <?= $this->render('index-net', ['searchModel' => $searchModel, 'dataProvider'=>$dataProvider]); ?>
<?php endif; ?>
<?php
Modal::begin([
    'id'=>'create-modal',
    'header'=>'<h4 class="modal-title">系统信息</h4>',
    'size'=>'modal-lg',
    'options'=>[
    ]
]);
Modal::end();
$js = <<<JS
$(function(){
    $('.submit').click(function(){
        $('#create-modal .modal-body').html('');
        $('#create-modal .modal-body').load($(this).attr('href'));
    });
    $('.audit').click(function(){
        $('#create-modal .modal-body').html('');
        $('#create-modal .modal-body').load($(this).attr('href'));
    });
    $('.view').click(function(){
        $('#create-modal .modal-body').html('');
        $('#create-modal .modal-body').load($(this).attr('href'));
    });
    $('.edit').click(function(){
        $('#create-modal .modal-body').html('');
        $('#create-modal .modal-body').load($(this).attr('href'));
    });
    $('.delete').click(function(){
        var self=this,
            id=$(self).attr('data-payid'),
            msg='确定删除ID为 <b style="color:red;">'+id+'</b> 的数据吗？';
        layer.confirm(msg,{icon:3,title:'温馨提示'},function(index){
            layer.load(0,{shade:false});
            $.ajax({
                url:'/fba-purchase-order-pay/delete',
                data:{id:id},
                type:'post',
                dataType:'json',
                success:function(data){
                    if(data.error==0){
                        location.reload();
                    }else{
                        layer.alert(data.message);
                    }
                }
            });
        });
    });
});
JS;
$this->registerJs($js);
?>

