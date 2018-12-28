<?php

use app\services\PurchaseOrderServices;
use app\config\Vhelper;
use mdm\admin\components\Helper;
?>

    <div class="panel panel-success">
        <h4 align="center" style="font-weight:bold;color: red;">财务-【收款】状态</h4>
        <div class="panel panel-body">
            <h4>温馨小提示:</h4>
            <ol style="color:red;font-weight: bold;">
                <li>要求：知道采购单单号</li>
                <li>注意：修改的是【收款状态】</li>
            </ol>
            <div class="form-group">
                <label class="control-label" for="update_purchase_order_receipt_pay_status_pur_number">采购单单号</label>
                <input type="text" id="update_purchase_order_receipt_pay_status_pur_number" class="form-control">
                <div class="table-responsive"> <!--class="panel-footer"-->
                    <!-- table-condensed  精简-->
                    <table style="table-layout: fixed;" class="table table-striped table-bordered table-hover">
                        <caption>采购单收款表</caption>
                        <!--<thead>
                            <tr>
                                <th>sku</th>
                                <th>产品状态</th>
                                <th>开发人员</th>
                                <th>是否捆绑</th>
                            </tr>
                        </thead>-->
                        <tbody id="select_purchase_order_receipt_pay_status_pur_number"></tbody>
                    </table>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label" for="update_purchase_order_receipt_pay_status_old">收款状态--以前的</label>
                <select id="update_purchase_order_receipt_pay_status_old" class="form-control">
                    <option value="">请选择收款状态</option>
                    <?php
                    foreach (PurchaseOrderServices::getReceiptStatus() as $k => $v) {
                        echo '<option value="' . $k . '">'. $v .'</option>';
                    }
                    ?>
                </select>
                <div class="help-block"></div>
            </div>
            <div class="form-group">
                <label class="control-label" for="update_purchase_order_receipt_pay_status_new">收款状态--修改之后的</label>
                <select id="update_purchase_order_receipt_pay_status_new" class="form-control">
                    <option value="">请选择收款状态</option>
                    <?php
                    foreach (PurchaseOrderServices::getReceiptStatus() as $k => $v) {
                        echo '<option value="' . $k . '">'. $v .'</option>';
                    }
                    ?>
                </select>
                <div class="help-block"></div>
            </div>
            <div class="form-group">
                <?php
                if(Helper::checkRoute('update-purchase-order-receipt-pay-status')) {
                    echo '<span id="update_purchase_order_receipt_pay_status_btn" class="btn btn-success">修改收款状态</span>';
                }
                ?>

            </div>
        </div>
        <div class="panel-footer">
            <span id="update_purchase_order_receipt_pay_status_span"></span>
        </div>
    </div>

<?php
//$viewUrl = Url::toRoute('/waste-all-arrival/waste-all-arrival');
$js = <<<JS
$(function() {
    var kkk = {
        red: 'red',
        green: 'green',
        dark: 'dark',
    };
    //===============  展示产品详情 ===================
    // $("input").focus(); 或$("input").focus(function(){这里是获取焦点时的事件}) 
    // $("input").blur(); 或$("input").blur(function(){这里是失去焦点时的事件}) 
    $("#update_purchase_order_receipt_pay_status_pur_number").blur(function(){
        var pur_number = $.trim($('#update_purchase_order_receipt_pay_status_pur_number').val()); 
        if(pur_number == '') {
            $('#select_purchase_order_receipt_pay_status_pur_number').html('');
            return false;
        }

        $.ajax({
            url: 'select-purchase-order-receipt-pay-status',
            data: {pur_number: pur_number},
            type: 'post',
            dataType: 'json',
            success: function(data) {
               var purchase_order =  data.order_receipt;
               $('#select_purchase_order_receipt_pay_status_pur_number').html('');

               if (purchase_order=='') {
                   // alert('空空如也');
                   $('#select_purchase_order_receipt_pay_status_pur_number').append('<span style="font-weight: bold;color:red;">没有该订单 -- '+ pur_number + '</span>');
               } else {
                   $(purchase_order).each(function(pk,pv) {
                       $(pv).each(function(k,v) {
                             $('#select_purchase_order_receipt_pay_status_pur_number').append('<tr class="size-row success">' +
                              '<td>' + v['pur_number'] +'</td>' +
                              '<td>' + v['pay_status'] +'</td>' +
                              '<td>' + v['pay_price'] +'</td>' +
                              '<td>' + v['step'] +'</td>' +
                               '</tr');
                       });
                   });
               }
            }
        });   
    }) ;
    //================ 修改采购付款状态  ================================
    $('#update_purchase_order_receipt_pay_status_btn').click(function() {
        var pur_number = $.trim($('#update_purchase_order_receipt_pay_status_pur_number').val()); //采购单单号
        var pay_status_old = $.trim($('#update_purchase_order_receipt_pay_status_old').val()); //采购付款状态
        var pay_status_new = $.trim($('#update_purchase_order_receipt_pay_status_new').val()); //采购付款状态
        // $('#testSelect option:selected') .val();//选中的值
        if(pur_number == '' || pay_status_old == '' || pay_status_new == '') {
            alert('采购单单号 或 采购付款状态 不能为空');
            return false;
        }
        
        $.ajax({
            url: 'update-purchase-order-receipt-pay-status',
            data: {pur_number: pur_number, pay_status_old: pay_status_old,pay_status_new : pay_status_new},
            type: 'post',
            dataType: 'json',
            success: function(data) {
               var d =  data.message;
               $('#update_purchase_order_receipt_pay_status_span').html('');
               $(d).each(function(k,v) {
                     var color = kkk[v['color']];
                     $('#update_purchase_order_receipt_pay_status_span').append('<p style="font-weight:bold;color:'+color+'">'+v['msg']+'</p>');
               });
            }
        });   
    });
    //=============================================================================
});
JS;
$this->registerJs($js);
$this->beginContent('@app/views/layouts/waste.php');
$this->endContent();
?>