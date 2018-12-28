<?php
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use app\services\PurchaseOrderServices;
use app\services\SupplierServices;
use app\services\BaseServices;
use app\models\PurchaseOrderItems;
use mdm\admin\components\Helper;
use app\models\PurchaseOrderPay;
use app\models\PurchaseOrderReceipt;
use app\models\WarehouseResults;

$this->title = 'FBA采购订单';
$this->params['breadcrumbs'][] = $this->title;


Modal::begin([
    'id' => 'create-modal',
    'header' => '<h4 class="modal-title">系统信息</h4>',
    'footer' => '<a href="#" class="btn btn-primary closes" data-dismiss="modal">关闭</a>',
    'size'=>'modal-lg',
    //'closeButton' =>false,
    'options'=>[
        'data-backdrop'=>'static',//点击空白处不关闭弹窗
    ],
]);
Modal::end();

?>
    <div class="panel panel-default">
        <div class="panel-body">
            <?= $this->render('_search', ['model' => $searchModel]); ?>
        </div>
        <div class="panel-footer">
            <?php  
            echo Html::a('采购确认', ['#'], ['class' => 'btn btn-success', 'id' => 'submit-audits', 'data-toggle' => 'modal', 'data-target' => '#create-modal',]);

            if(Helper::checkRoute('revoke-confirmation')) {
                echo Html::a('撤销确认', ['revoke-confirmation'], ['class' => 'btn btn-success','id'=>'submit-audit',]);
            }

            if(Helper::checkRoute('all-review')) {
                echo Html::a('批量审核', ['#', 'name' => 'audit'], ['class' => 'btn btn-info', 'id' => 'batch_review', 'data-toggle' => 'modal', 'data-target' => '#create-modal',]);
            }

            if(Helper::checkRoute('add-purchase-notes')) {
                echo Html::a('批量备注', ['#', 'name' => 'audit'], ['class' => 'btn btn-info', 'id' => 'add_purchase_notes', 'data-toggle' => 'modal', 'data-target' => '#create-modal',]);
            }

            if(Helper::checkRoute('revoke-purchase-order')) {
                echo Html::a('撤销采购单', ['revoke-purchase-order'], ['class' => 'btn btn-danger', 'id' => 'submit-audit']);
            }

            echo Html::a('申请批量付款', ['allpayment'], ['class' => 'btn btn-warning all-payment','id'=>'all-payment','data-toggle' => 'modal', 'data-target' => '#create-modal',]);

            echo Html::a('标记到货日期', ['arrival-date'], ['class' => 'btn btn-success','id'=>'arrival','data-toggle' => 'modal','data-target' => '#create-modal',]);
            
            if(Helper::checkRoute('order-export')) {
                echo Html::a('导出', ['#'], ['class' => 'btn btn-success','id'=>'order-export']);
            }

            if(Helper::checkRoute('audit-ship')) {
                echo '<a href="audit-ship" class="btn btn-warning">订单信息修改-审核</a>';
            }

            if(Helper::checkRoute('audit-ship')) {
                echo Html::button('创建合同', ['class' => 'btn btn-success all-info fba-create-compact']);
                // echo '<a href="audit-ship" class="btn btn-warning">生成合同</a>';
            }

            if(Helper::checkRoute('audit-ship')) {
                echo '<a href="/fba-purchase-order/compact-list" class="btn btn-info">合同列表</a>';
            }
            ?>
        </div>
    </div>
<h4><span class="glyphicon glyphicon-heart" style="color: red" aria-hidden="true"></span> 温馨提示：</h4>
<p style="color: red">
1.执行顺序:采购确认----->批量审核---->批量申请付款---->等待财务审批付款。
2.全额退款需要上级审核,部分退款直接进入财务收款模块
3.默认30分钟请求物流信息<br/>
4.只有待确认情况下是可以修改供应商,采购员是自己才能修改供应商
5.建议单个采购确认,多了会拉很长，不利于观察
6：被驳回的退款单，可以点击退款状态，进行编辑，保存后会重新回到待财务收款。</p>


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
$sumbitUrl  = Url::toRoute(['submit-audit']);
$batchUrl  = Url::toRoute(['all-review']);
$addPurchaseNotes  = Url::toRoute(['add-purchase-notes']);
$arrival='请选择需要标记到货日期的采购单';
$msg ='请选择采购单';
$exportUrl  = Url::toRoute('order-export');
$js = <<<JS
$(function() {
    /**
     * 采购确认
     */
    $(document).on('click', '#submit-audits', function () {
        var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
        if (ids&& ids.length !=0) {
            $('#create-modal').find('.modal-body').html('正在请求数据....');
            $.get('{$sumbitUrl}', {id: ids},function (data) {
                $('.modal-body').html(data);
                $("#created-modal3").on("hidden.bs.modal",function(){
                    $(document.body).addClass("modal-open");
                });
            });
        } else {
            $('.modal-body').html('{$msg}');
            return false;
        }
    });
    /**
     * 撤销确认
     */
    $("a#submit-audit").click(function(){
        var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
        if(ids==''){
            alert('请先选择!');
            return false;
        }else{
            var url = $(this).attr("href");
            if($(this).hasClass("print"))
            {
                url = '/purchase-order/print-data';
            }
            url     = url+'?ids='+ids;
            $(this).attr('href',url);
        }
    });
    /**
     * 批量审核
     */
    $(document).on('click','#batch_review',function() {
        var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
        if(ids==''){
            layer.alert('请先选择!');
            return ;
        }else{
             $('#create-modal').find('.modal-body').html('正在请求数据....');
            $.get('{$batchUrl}', {id: ids},
                function (data) {

                    $('.modal-body').html(data);
                    $("#created-modal2").on("hidden.bs.modal",function(){
                        $(document.body).addClass("modal-open");
                        });
                }
            );
        }
    });
    /**
     * 批量备注
     */
      $(document).on('click','#add_purchase_notes',function() {
        var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
        if (ids&& ids.length !=0) {
            $('#create-modal').find('.modal-body').html('正在请求数据....');
            $.get('{$addPurchaseNotes}', {id: ids},function (data) {
                $('.modal-body').html(data);
                $("#created-modal2").on("hidden.bs.modal",function(){
                    $(document.body).addClass("modal-open");
                });
            });
        } else {
            $('.modal-body').html('{$msg}');
            return false;
        }
    });
    /**
     * 批量申请付款
     */
    $("a#all-payment").click(function() {
        var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
        if(ids.length == 0) {
            layer.alert('请选择要申请付款的订单！');
            return false;
        } else {
            var str = ids.join(',');
            $('.modal-body').html('');
            $.get($(this).attr('href'), {ids: str}, function(html) {
                $('.modal-body').html(html);
            });
        }
    });
    /**
     * 标记到货日期
     */
    $(document).on('click', '#arrival', function () {
        var str='';
        //获取所有的值
        $("input[name='id[]']:checked").each(function(){
            str+=','+$(this).val();
        })
        str=str.substr(1);
        if (str == ''){
            $('.modal-body').html('$arrival');
        }else{
            $.get($(this).attr('href'), {id:str},function (data) {
                $('.modal-body').html(data);
            });
        }
    });
    /**
     * 导出
     */
    $(document).on('click','#order-export',function() {
        var str='';
        //获取所有的值
        $("input[name='id[]']:checked").each(function(){
            str+=','+$(this).val();
        })
        str=str.substr(1);
        if(str ==''){
            alert('请至少选择一个')
        }else{
            $(this).attr('href',"{$exportUrl}?purNumber="+str);
        }
    });
    /**
     * 创建合同
     */
    $('.fba-create-compact').click(function() {
        var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
        if(ids.length == 0) {
            layer.alert('请选择要生成合同的订单。');
            return false;
        }
        window.open('/overseas-purchase-order/create-compact?ids='+ids+'&platform=3');
    });


});
JS;
$this->registerJs($js);
?>