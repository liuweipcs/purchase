<?php
use yii\helpers\Html;
use yii\bootstrap\Modal;
use app\services\BaseServices;
use app\services\PurchaseOrderServices;
use app\services\SupplierServices;
$order = $orders[0];
$this->title = '合同详情';
$this->params['breadcrumbs'][] = $this->title;

$skus = []; //已请款的sku

if (!empty($requisition_number)) {
    $skus = \app\models\OrderPayDemandMap::getCurrentPaySkus(['requisition_number'=>$requisition_number]); //获取当前请款的sku
}
?>
<style type="text/css">
    .box-span span {
        display: inline-block;
        padding: 0px 15px;
        color: red;
        font-size: 15px;
    }
</style>

<h3><?= $model->compact_number ?></h3>

<div class="my-box">
    <table class="my-table">
        <tr>
            <th colspan="7">基本信息</th>
        </tr>
        <tr>
            <td><strong>供应商名称</strong></td>
            <td colspan="3"><?= $model->supplier_name ?></td>
            <td><strong>生成时间</strong></td>
            <td><?= $model->create_time ?></td>
        </tr>
        <tr>
            <td><strong>结算比例</strong></td>
            <?php
                $arr = explode('+', $model->settlement_ratio);
                $settlement_ratio = '';
                if(count($arr)>=3){
                    $settlement_ratio = '结算方式(月结)+10%定金+发货前30%尾款+到货后60%尾款月结';
                }else{
                    $settlement_ratio = $model->settlement_ratio;
                }
            ?>
            <td><?= $settlement_ratio ?></td>
            <td><strong>结算方式</strong></td>
            <td><?= !empty($order->account_type) ? SupplierServices::getSettlementMethod($order->account_type) : ''; ?></td>
            <td><strong>支付方式</strong></td>
            <td><?= !empty($order->pay_type) ? SupplierServices::getDefaultPaymentMethod($order->pay_type) : ''; ?></td>
        </tr>

        <tr>
            <td><strong>是否退税</strong></td>
            <td>
                <?php
                if($model->is_drawback == 1) {
                    echo '<span class="label label-success">不退税</span>';
                } else {
                    echo '<span class="label label-info">退税</span>';
                }
                ?>
            </td>
            <td><strong>合同金额信息</strong></td>
            <td colspan="3">

                <div class="box-span">
                    <span>总商品额：<?= $model->product_money ?></span>
                    <span>总运费：<?= $model->freight ?></span>
                    <span>总优惠：<?= $model->discount ?></span>
                    <span>实际总额：<?= $model->real_money ?></span>
                </div>

            </td>
        </tr>

    </table>
</div>


<?php
if($model->source == 2) {
    echo $this->render('_order_list', ['orders' => $orders, 'is_drawback' => $model->is_drawback,'sku_list'=>$skus]);
} else {
    echo $this->render('_order_list1', ['orders' => $orders, 'sku_list'=>$skus]);
}
?>

<div class="my-box">
    <table class="my-table">
        <tr>
            <th colspan="6">付款记录</th>
        </tr>
        <tr>
            <td>id</td>
            <td>付款比例/金额</td>
            <td>付款状态</td>
            <td>付款人/时间</td>
            <td>备注</td>
            <td>操作</td>
        </tr>
        <?php if(empty($pay_list)): ?>

        <tr>
            <td colspan="6" style="text-align: center;color: #ccc;">没有付款记录</td>
        </tr>

        <?php
        else:
            foreach($pay_list as $pay):
            ?>

            <tr>
                <td><?= $pay->id ?></td>
                <td>
                    <p>付款比例：<?= $pay->pay_ratio ?></p>
                    <p>金额：<?= $pay->pay_price ?></p>
                </td>
                <td><?= PurchaseOrderServices::getPayStatusType($pay->pay_status) ?></td>
                <td>
                    <p>付款人：<?= !empty($pay->payer) ? BaseServices::getEveryOne($pay->payer) : '' ?></p>
                    <p>时间：<?= $pay->payer_time ?></p>
                </td>
                <td><?= $pay->payment_notice ?></td>
                <td>

                    <a class="show-images" style="margin-right: 20px;" href="/purchase-compact/show-images?id=<?= $pay->id ?>" data-toggle='modal' data-target='#create-modal'>查看付款回单</a>
                    <a class="show-form" style="margin-right: 20px;" href="/purchase-compact/show-form?id=<?= $pay->id ?>" data-toggle='modal' data-target='#create-modal'>查看付款申请书</a>

                    <?= Html::a('下载付款申请书', ['/purchase-compact/download-pay-form', 'id' => $pay->id]); ?>

                </td>
            </tr>
        <?php
            endforeach;
            endif;
            ?>
    </table>
</div>

<div class="my-box">
    <table class="my-table">
        <tr>
            <th colspan="5">操作日志</th>
        </tr>
        <tr>
            <td>操作人</td>
            <td>操作时间</td>
            <td>日志类型</td>
            <td>日志明细</td>
        </tr>
        <?php foreach($logs as $log): ?>
            <tr>
                <td><?= $log['create_user'] ?></td>
                <td><?= $log['create_time'] ?></td>
                <td>系统</td>
                <td><?= $log['note'] ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>





<?php
Modal::begin([
    'id' => 'create-modal',
    'header' => '<h4 class="modal-title">系统信息</h4>',
    'footer' => '<a href="#" class="btn btn-primary closes" data-dismiss="modal">关闭</a>',
    'size' => 'modal-lg',
    'options' => [
        'data-backdrop' => 'static',
    ],
]);
Modal::end();
$js = <<<JS
$(function() {
    $('.show-images').click(function() {
        $.get($(this).attr('href'), function(data) {
            var dom = '';
            if(!data) {
                dom = '财务还没有上传付款回执，请等待。';
            } else {
                for(var i=0;i<data.length;i++) {
                    dom += '<a href="'+data[i]+'" target="blank">付款回单</a>';
                }
            }
            $('.modal-body').html(dom);
        }, 'json');
    });
    
    $('.show-form').click(function() {
        $.get($(this).attr('href'), function(data) {
            $('.modal-body').html(data);
        });
    });
    
    $('.img').click(function() {
        var json = {
            "data": [{"src": $(this).find('img').attr('src')}]
        };
        layer.photos({
            photos: json,
            anim: 5 
        });
    });
    
});
JS;
$this->registerJs($js);
?>
