<?php
use yii\widgets\ActiveForm;
use app\services\BaseServices;
use app\services\PurchaseOrderServices;
$this->title = '1688批量在线付款';
$this->params['breadcrumbs'][] = '出纳付款';
$this->params['breadcrumbs'][] = $this->title;
?>
<style type="text/css">
    h5 {
        font-weight: bold;
    }
    .container-fluid {
        background-color: #fff;
    }
    .row {
        border-top: 1px dashed #ccc;
        padding: 10px;
    }
    .mt-title {
        padding: 10px 0px;
    }
    .mt-title span {
        display: inline-block;
        margin-right: 20px;
        font-weight: bold;
    }
    .mt-title b {
        color: #FF9800;
    }
    .glyphicon-ok {
       color: #23ef23;
    }
    .glyphicon-remove {
        color: #f14732;
    }
    .label:hover {
        cursor: pointer;
    }
</style>

<?php $form = ActiveForm::begin(['id' => 'mul-payment', 'options' => ['target' => '_blank']]); ?>

<div class="container-fluid">

    <input type="hidden" name="applicant" value="<?= $applicant ?>">

    <?php
        $totalMoney = 0;
        foreach($list as $k=>$row):
            $dis = $row['order_discount'] ? $row['order_discount'] : 0;
            $fri = $row['order_freight'] ? $row['order_freight'] : 0;

        ?>

    <div class="row">

        <input class="payid" type="hidden" name="payment[<?= $k ?>][id]" value="<?= $row['id'] ?>">
        <input type="hidden" name="payment[<?= $k ?>][pur_number]" value="<?= $row['pur_number'] ?>">
        <input type="hidden" name="payment[<?= $k ?>][requisition_number]" value="<?= $row['requisition_number'] ?>">
        <input type="hidden" name="payment[<?= $k ?>][order_number]" value="<?= $row['order_number'] ?>">

        <div class="col-md-12">

            <div class="mt-title">
                <span>编号ID：<b><?= $row['id'] ?></b></span>
                <span>订单号：<b><?= $row['pur_number'] ?></b></span>
                <span>申请号：<b><?= $row['requisition_number'] ?></b></span>
                <span>申请人：<b><?= BaseServices::getEveryOne($row['applicant']) ?></b></span>
            </div>

            <table class="table table-bordered table-condensed">
                <colgroup>
                    <col class="col-md-1">
                    <col class="col-md-3">
                    <col class="col-md-1">
                    <col class="col-md-2">
                    <col class="col-md-1">
                    <col class="col-md-1">
                    <col class="col-md-1">
                    <col class="col-md-1">
                </colgroup>
                <thead>
                <tr style="background-color: #faf6f1;">
                    <th>#</th>
                    <th>供应商</th>
                    <th>账号</th>
                    <th>拍单号</th>
                    <th>状态</th>
                    <th>金额</th>
                    <th>应付</th>
                    <th>操作</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>本地</td>
                    <td><?= $row['supplier_name'] ?></td>
                    <td><?= $row['buyer_account'] ?></td>
                    <td><?= $row['order_number'] ?></td>
                    <td><?= PurchaseOrderServices::getPayStatusType($row['pay_status']) ?></td>
                    <td>
                        <span>金额：<?= $row['pay_price']; ?></span><br/>
                        <span>优惠：<?= $dis; ?></span><br/>
                        <span>运费：<?= $fri; ?></span><br/>
                    </td>
                    <?php
                        // 本地总额
                        $native_totalAmount = $row['pay_price']-$dis+$fri;
                        // 计价
                        $totalMoney += $native_totalAmount;
                        ?>
                    <td><?= (float)$native_totalAmount ?></td>
                    <td rowspan="2">
                        <p><span class="label label-success not-payment" data-payid="<?= $row['id'] ?>" data-paymoney="<?= $native_totalAmount ?>"><span class="glyphicon glyphicon-scissors"></span> 不支付此单</span></p>
                        <p><span class="label label-danger reject-payment" data-payid="<?= $row['id'] ?>" data-paymoney="<?= $native_totalAmount ?>"><span class="glyphicon glyphicon-arrow-left"></span> 驳回此单</span></p>
                    </td>
                </tr>

                <?php
                    if(isset($row['alibaba']['result'])):
                        $baseInfo = $row['alibaba']['result'];
                        $ali_buyerLoginId = isset($baseInfo['buyerLoginId']) ? $baseInfo['buyerLoginId'] : '';
                        $ali_id = isset($baseInfo['id']) ? $baseInfo['id'] : '';
                        $ali_status = isset($baseInfo['status']) ? PurchaseOrderServices::getAlibabaPayStatus($baseInfo['status']) : '';
                        $ali_sumProductPayment = isset($baseInfo['sumProductPayment']) ? $baseInfo['sumProductPayment'] : '';
                        $ali_discount = isset($baseInfo['discount']) ? $baseInfo['discount']/100 : '';
                        $ali_shippingFee = isset($baseInfo['shippingFee']) ? $baseInfo['shippingFee'] : '';
                        $ali_totalAmount = isset($baseInfo['totalAmount']) ? $baseInfo['totalAmount'] : '';
                    ?>
                <tr>
                    <td>1688</td>
                    <td>
                        <?php
                            if(isset($baseInfo['sellerContact']) && isset($baseInfo['sellerContact']['companyName'])) {
                                echo $baseInfo['sellerContact']['companyName'];
                            }
                        ?>
                    </td>
                    <td><?= $ali_buyerLoginId ?></td>
                    <td><?= $ali_id ?></td>
                    <td><?= $ali_status ?></td>
                    <td>
                        <span>金额：<?= number_format($ali_sumProductPayment, 2) ?></span><br/>
                        <span>优惠：<?= number_format($ali_discount, 2) ?></span><br/>
                        <span>运费：<?= number_format($ali_shippingFee, 2) ?></span>
                    </td>
                    <td><?= (float)$ali_totalAmount ?></td>
                </tr>

                <tr>
                    <td></td>
                    <td><span class="glyphicon glyphicon-user" title="请人工审核"></span></td>
                    <td>
                        <?php if($row['buyer_account'] == $ali_buyerLoginId): ?>
                            <span class="glyphicon glyphicon-ok"></span>
                        <?php else: ?>
                            <span class="glyphicon glyphicon-remove"></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($row['order_number'] == $ali_id): ?>
                            <span class="glyphicon glyphicon-ok"></span>
                        <?php else: ?>
                            <span class="glyphicon glyphicon-remove"></span>
                        <?php endif; ?>
                    </td>
                    <td><span class="glyphicon glyphicon-user" title="请人工审核"></span></td>


                    <td>
                        <?php if($row['pay_price'] == $ali_sumProductPayment && 100 == $ali_shippingFee && 10 == $ali_discount): ?>
                            <span class="glyphicon glyphicon-ok"></span>
                        <?php else: ?>
                            <span class="glyphicon glyphicon-remove"></span>
                        <?php endif; ?>
                    </td>


                    <td>

                        <?php if(bccomp($native_totalAmount, $ali_totalAmount, 2) == 0): ?>
                            <span class="glyphicon glyphicon-ok"></span>
                        <?php else: ?>
                            <span class="glyphicon glyphicon-remove"></span>
                            <a name="error"></a>    <!-- 放置错误标记点 -->
                        <?php endif; ?>
                    </td>

                    <td></td>
                </tr>

                <?php else: ?>
                <tr>
                    <td>1688</td>
                    <td colspan="7"><?= $row['alibaba'] ?></td>
                </tr>
                <a name="error"></a> <!-- 放置错误标记点 -->
                <?php endif; ?>

                </tbody>
            </table>

        </div>

    </div>

    <?php endforeach; ?>

    <div class="row">
        <div class="col-md-9">
            <a href="javascript:void(0)" class="btn btn-success" id="btn-payment"><span class="glyphicon glyphicon-yen"></span> 收银台</a>
            <a href="javascript:void(0)" class="btn btn-default" id="reset-refresh" title="点我找回取消的单"><span class="glyphicon glyphicon-refresh"></span> 重置并刷新</a>
            <span style="display: inline;padding-left: 10px;"><span class="glyphicon glyphicon-volume-up"></span> 重置并刷新按钮用于帮你找回你取消（逻辑上取消）支付的单</span>
        </div>
        <div class="col-md-2" style="text-align: center; height: 35px; line-height: 40px;">
            <span>总金额：<strong style="color: red;font-size: 18px;" id="theTotalMoney" data-totalmoney="<?= $totalMoney ?>"><?= number_format($totalMoney, 2) ?></strong></span>
        </div>
        <div class="col-md-1">
            <a href="javascript:void(0)" class="btn btn-primary" id="affirm-payment"><span class="glyphicon glyphicon-user"></span> 确认付款</a>
        </div>
    </div>

</div>

<?php ActiveForm::end(); ?>

<div id="reject-html" style="padding: 15px;display: none;">

    <div class="form-group">
        <label>编号ID</label>
        <input type="text" name="id" value="" class="form-control" disabled>
    </div>
    <div class="form-group">
        <label>备注</label>
        <textarea rows="3" name="payment_notice" class="form-control"></textarea>
    </div>

</div>

<?php
$js = <<<JS
$(function() {
    
    function setCookie(cname, cvalue, exdays)
    {
        var d = new Date();
        d.setTime(d.getTime() + (exdays*24*60*60*1000));
        var expires = "expires=" + d.toGMTString();
        document.cookie = cname + "=" + cvalue + "; " + expires;
    }
    
    function getCookie(cname)
    {
        var name = cname + "=";
        var ca = document.cookie.split(';');
        for(var i=0; i<ca.length; i++) {
            var c = ca[i].trim();
            if(c.indexOf(name) == 0) { 
                return c.substring(name.length, c.length); 
            }
        }
        return "";
    }
    
    function floatSub(arg1, arg2)
    {    
        var r1,r2,m,n;    
        try{r1=arg1.toString().split(".")[1].length}catch(e){r1=0}    
        try{r2=arg2.toString().split(".")[1].length}catch(e){r2=0}    
        m=Math.pow(10,Math.max(r1,r2));    
        //动态控制精度长度    
        n=(r1>=r2)?r1:r2;    
        return ((arg1*m-arg2*m)/m).toFixed(n);    
    }
    
    $('.not-payment').click(function() {
        var that = $(this);
        var node = $(this).parents('.row');
        var id = $(this).attr('data-payid');
        layer.confirm('确定不支付这个单吗？', function(index) {
            var ids = getCookie('not-pay');
            if(!ids) {
                setCookie('not-pay', id, 1);
            } else {
                var str = getCookie('not-pay');
                str += ','+ id;
                setCookie('not-pay', str, 1);
            }
            
            var money = floatSub(parseFloat($('#theTotalMoney').attr('data-totalmoney')), parseFloat(that.attr('data-paymoney')));
            
            $('#theTotalMoney').text(money);
            $('#theTotalMoney').attr('data-totalmoney', money);
            
            layer.close(index);
            node.remove();
        });
    });
    
    $('.reject-payment').click(function() {
        var that = $(this);
        var html = $('#reject-html');
        var inode = $('#reject-html').find('input[name="id"]');
        var row = $(this).parents('.row');
        var id = $(this).attr('data-payid');
        inode.val(id);
        layer.open({
            type: 1,
            title: '驳回请款单',
            area: ['600px', '350px'],
            content: html,
            btn: ['驳回', '取消'],
            yes: function(index, layero) {
                var notice = $('#reject-html').find('textarea[name="payment_notice"]').val();
                $.ajax({
                    'url': '/purchase-order-cashier-pay/cashier-reject',
                    'type': 'post',
                    'dataType': 'json',
                    'data': {id: id, payment_notice: notice},
                    'success': function(data) {
                        if(data.error == 0) {
                            var ids = getCookie('not-pay');
                            if(!ids) {
                                setCookie('not-pay', id, 1);
                            } else {
                                var str = getCookie('not-pay');
                                str += ','+ id;
                                setCookie('not-pay', str, 1);
                            }
                            
                            layer.msg(data.message);
                            
                            var money = floatSub(parseFloat($('#theTotalMoney').attr('data-totalmoney')), parseFloat(that.attr('data-paymoney')));
            
                            $('#theTotalMoney').text(money);
                            $('#theTotalMoney').attr('data-totalmoney', money);
            
                            layer.close(index);
                            row.remove();

                        } else {
                            layer.msg(data.message);
                        }
                    }
                });
            },
            cancel: function(index, layero) {
                inode.val('');
                $('#reject-html').find('textarea[name="payment_notice"]').val();
            }
        });
    });
    
    $('#reset-refresh').click(function() {
        var ids = getCookie('not-pay');
        if(ids) {
            setCookie('not-pay', '', -1);
            location.reload();
        } else {
            layer.msg('没有可重置项');
        }
    });
    
    $('#btn-payment').click(function() {
        var errors = $('a[name="error"]').length;
        /*if(errors > 0) {
            layer.alert("抱歉，系统发现你有 "+ errors +"条请款单数据和1688不相符，请处理完以后再去收银台付款。");
            return false;
        }*/
        $('#mul-payment').submit();
    });
    
    
    $('#affirm-payment').click(function() {
        layer.confirm('确认付款后将会变更请款单的状态为已支付，你确定要这样做吗？', function(index1) {
            var ids = [];
            $('.payid').each(function() {
                ids.push($(this).val());
            });
            var index2 = layer.load(1, {
                shade: [0.1, '#fff'] 
            });
            layer.close(index1);
            $.ajax({
                'url': '/purchase-order-cashier-pay/affirm-payment',
                'type': 'post',
                'dataType': 'json',
                'data': {ids: ids},
                'success': function(data) {
                    layer.close(index2)
                    if(data.error == 0) {
                        layer.msg('恭喜你，操作成功');
                    } else {
                        layer.msg(data.message);
                    }
                }
            });
        });
    });

});
JS;
$this->registerJs($js);
?>





