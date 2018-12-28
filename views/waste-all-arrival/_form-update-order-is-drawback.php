<?php

use app\services\SupplierGoodsServices;
use app\config\Vhelper;
use mdm\admin\components\Helper;
use app\services\SupplierServices;
use app\services\PurchaseOrderServices;
?>

    <div class="panel panel-success">
        <h4 align="center" style="font-weight:bold;color: red;">采购单：修改关税</h4>
        <div class="panel panel-body">
            <h4>温馨小提示:</h4>
            <ol style="color:red;font-weight: bold;">
                <li>要求：知道采购单号</li>
            </ol>
            <div class="form-group">
                <label class="control-label" for="update_purchase_order_is_drawback_pur_number">采购单号</label>
                <input type="text" id="update_purchase_order_is_drawback_pur_number" class="form-control update_purchase_order_is_drawback_pur_numbers">
                <label class="control-label" for="update_purchase_order_is_drawback_sku">SKU</label>
                <input type="text" id="update_purchase_order_is_drawback_sku" class="form-control update_purchase_order_is_drawback_pur_numbers">
                <div class="table-responsive"> <!--class="panel-footer"-->
                    <!-- table-condensed  精简-->
                    <table style="table-layout: fixed;" class="table table-striped table-bordered table-hover">
                        <caption>采购订单主表</caption>
                        <tbody id="select_purchase_order_is_drawback_pur_number"></tbody>
                    </table>
                    <table style="table-layout: fixed;" class="table table-striped table-bordered table-hover">
                        <caption>请款单表</caption>
                        <tbody id="select_purchase_order_pay"></tbody>
                    </table>
                    <table style="table-layout: fixed;" class="table table-striped table-bordered table-hover">
                        <caption>产品税率表 -- pur_product_tax_rate</caption>
                        <tbody id="select_roduct_tax_rate"></tbody>
                    </table>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label" for="update_purchase_order_is_drawback">是否退税
                <select id="update_purchase_order_is_drawback" class="form-control">
                    <option value="">请选择产品状态</option>
                    <?php
                    foreach (PurchaseOrderServices::getIsDrawback() as $k => $v) {
                        //echo '<option value="' . $k . '">'. $v . '--' . $k .'</option>';
                    }
                    ?>
                    <option value="1">不退税 【1】</option>
                    <option value="2">退税 【2】</option>
                </select>
                </label>
                <label class="control-label">税点
                <input type="text" id="ticketed_point" class="form-control">
                </label>
                <label class="control-label" for="update_purchase_order_is_push">是否推送
                <select id="update_purchase_order_is_push" class="form-control">
                    <option value="">请选择产品状态</option>
                    <option value="0">未推送 【0】</option>
                    <option value="1">推送 【1】</option>
                    <option value="2">不推送 【2】</option>
                </select>
                </label>
                <label class="control-label" for="update_purchase_order_account_type">结算方式
                <select id="update_purchase_order_account_type" class="form-control">
                    <option value="">请选择结算方式</option>
                    <?php
                    foreach (SupplierServices::getSettlementMethod() as $k => $v) {
                        echo '<option value="' . $k . '">'. $v . ' 【' . $k .'】</option>';
                    }
                    ?>
                </select>
                </label>
                </label>
                <label class="control-label" for="update_purchase_order_pay_type">支付方式
                <select id="update_purchase_order_pay_type" class="form-control">
                    <option value="">请选择支付方式</option>
                    <?php
                    foreach (SupplierServices::getDefaultPaymentMethod() as $k => $v) {
                        echo '<option value="' . $k . '">'. $v . ' 【' . $k .'】</option>';
                    }
                    ?>
                </select>
                </label>
            </div>
            <div class="form-group">
                <?php
                if(Helper::checkRoute('update-purchase-order-is-drawback')) {
                    echo '<span id="update_purchase_order_is_drawback_btn" class="btn btn-success">确认修改</span>';
                }
                //select-purchase-order-is-drawback
                ?>
            </div>
        </div>
        <div class="panel-footer">
            <span id="update_purchase_order_is_drawback_span"></span>
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
    $(".update_purchase_order_is_drawback_pur_numbers").blur(function(){
        var pur_number = $.trim($('#update_purchase_order_is_drawback_pur_number').val()); 
        var sku = $.trim($('#update_purchase_order_is_drawback_sku').val()); 
        if(pur_number == '') {
            $('#select_purchase_order_is_drawback_pur_number').html('');
            $('#select_purchase_order_pay').html('');
            $('#select_roduct_tax_rate').html('');
            return false;
        }
        $.ajax({
            url: 'select-purchase-order-is-drawback',
            data: {pur_number: pur_number,sku:sku},
            type: 'post',
            dataType: 'json',
            success: function(data) {
               // var d =  data.message;
               // var order_info =  data.order_info;
               var purchase_order =  data.purchase_order;
               $('#select_purchase_order_is_drawback_pur_number').html('');

               if (purchase_order=='') {
                   // alert('空空如也');
                   $('#select_purchase_order_is_drawback_pur_number').append('<span style="font-weight: bold;color:red;">没有该订单 -- '+ pur_number + '</span>');
               } else {
                   $(purchase_order).each(function(pk,pv) {
                       $(pv).each(function(k,v) {
                             $('#select_purchase_order_is_drawback_pur_number').append('<tr class="size-row success">' +
                              '<td>' + v['pur_number'] +'</td>' +
                              '<td>' + v['is_drawback'] +'</td>' +
                              '<td>' + v['is_push'] +'</td>' +
                              '<td>' + v['account_type'] +'</td>' +
                              '<td>' + v['pay_type'] +'</td>' +
                              '<td>' + v['buyer'] +'</td>' +
                               '</tr');
                       });
                   });
               }
               
               var purchase_order_pay =  data.purchase_order_pay;
               $('#select_purchase_order_pay').html('');

               if (purchase_order_pay=='') {
                   // alert('空空如也');
                   $('#select_purchase_order_pay').append('<span style="font-weight: bold;color:red;">没有该订单 -- '+ pur_number + '</span>');
               } else {
                   $(purchase_order_pay).each(function(pk,pv) {
                       $(pv).each(function(k,v) {
                             $('#select_purchase_order_pay').append('<tr class="size-row success">' +
                              '<td>' + v['pur_number'] +'</td>' +
                              '<td>' + v['pay_price'] +'</td>' +
                              '<td>' + v['pay_status'] +'</td>' +
                              '<td>' + v['settlement_method'] +'</td>' +
                              '<td>' + v['pay_type'] +'</td>' +
                              '<td>' + v['supplier_code'] +'</td>' +
                               '</tr');
                       });
                   });
               }
               var product_tax_rate =  data.product_tax_rate;
               $('#select_roduct_tax_rate').html('');

               if (product_tax_rate=='') {
                   // alert('空空如也');
                   $('#select_roduct_tax_rate').append('<span style="font-weight: bold;color:red;">没有数据</span>');
               } else {
                   $(product_tax_rate).each(function(pk,pv) {
                       $(pv).each(function(k,v) {
                             $('#select_roduct_tax_rate').append('<tr class="size-row success">' +
                              '<td>' + v['pur_number'] +'</td>' +
                              '<td>' + v['sku'] +'</td>' +
                              // '<td>' + v['is_taxes'] +'</td>' +
                              '<td>' + v['taxes'] +'</td>' +
                              '<td>' + v['create_id'] +'</td>' +
                              '<td>' + v['create_time'] +'</td>' +
                               '</tr');
                       });
                   });
               }
            }
        });   
    }) ;
    
    //================ 修改是否退税和推送  ================================
    $('#update_purchase_order_is_drawback_btn').click(function() {
        var pur_number = $.trim($('#update_purchase_order_is_drawback_pur_number').val()); 
        var is_drawback = $.trim($('#update_purchase_order_is_drawback').val()); 
        var ticketed_point = $.trim($('#ticketed_point').val()); 
        var is_push = $.trim($('#update_purchase_order_is_push').val()); 
        var account_type = $.trim($('#update_purchase_order_account_type').val()); 
        var pay_type = $.trim($('#update_purchase_order_pay_type').val()); 
        var sku = $.trim($('#update_purchase_order_is_drawback_sku').val()); 

        if (pur_number == '') {
            alert('订单号不能为空');
            return false;
        }
        /*if(is_drawback == '' && is_push == '' && account_type == '' && pay_type== '' && ticketed_point) {
            alert('是否退税 或 是否推送 或 结算方式 或 税点不能为空');
            return false;
        }*/
        
        $.ajax({
            url: 'update-purchase-order-is-drawback',
            data: {pur_number: pur_number,is_drawback: is_drawback, ticketed_point:ticketed_point, is_push: is_push,account_type:account_type,pay_type:pay_type,sku:sku},
            type: 'post',
            dataType: 'json',
            success: function(data) {
               var d =  data.message;
               $('#update_purchase_order_is_drawback_span').html('');
               $(d).each(function(k,v) {
                     var color = kkk[v['color']];
                     $('#update_purchase_order_is_drawback_span').append('<p style="font-weight:bold;color:'+color+'">'+v['msg']+'</p>');
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