<?php

use app\services\PurchaseOrderServices;
use app\config\Vhelper;
use mdm\admin\components\Helper;
?>

    <div class="panel panel-success">
        <h4 align="center" style="font-weight:bold;color: red;">财务-【付款】状态</h4>
        <div class="panel panel-body">
            <h4>温馨小提示:</h4>
            <ol style="color:red;font-weight: bold;">
                <li>要求：知道采购单单号</li>
                <li>注意：修改的是【采购付款状态】</li>
            </ol>
            <div class="form-group">
                <label class="control-label" for="update_order_pay_status_pur_number">采购单单号</label>
                <input type="text" id="update_order_pay_status_pur_number" class="form-control">
                <div class="table-responsive">
                    <!-- table-condensed  精简-->
                    <table style="table-layout: fixed;" class="table table-striped table-bordered table-hover">
                        <caption>采购订单主表</caption>
                        <tbody id="select_order_pay_status_pur_number"></tbody>
                    </table>
                    <table style="table-layout: fixed;" class="table table-striped table-bordered table-hover">
                        <caption>采购单支付表</caption>
                        <tbody id="select_order_pay_pay_status_pur_number"></tbody>
                    </table>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label" for="update_order_pay_status_old">采购付款状态--以前的</label>
                <select id="update_order_pay_status_old" class="form-control">
                    <option value="">请选择采购付款状态</option>
                    <?php
                    foreach (PurchaseOrderServices::getPayStatus() as $k => $v) {
                        echo '<option value="' . $k . '">'. $v .'</option>';
                    }
                    ?>
                </select>
                <div class="help-block"></div>
            </div>
            <div class="form-group">
                <label class="control-label" for="update_order_pay_status_new">采购付款状态--修改之后的</label>
                <select id="update_order_pay_status_new" class="form-control">
                    <option value="">请选择采购付款状态</option>
                    <?php
                    foreach (PurchaseOrderServices::getPayStatus() as $k => $v) {
                        echo '<option value="' . $k . '">'. $v .'</option>';
                    }
                    ?>
                </select>
                <div class="help-block"></div>
            </div>
            <div class="form-group">
                <?php
                if(Helper::checkRoute('update-pay-status')) {
                    echo '<span id="update_order_pay_status_btn" class="btn btn-success">修改付款状态</span>';
                }
                ?>

            </div>
        </div>
        <div class="panel-footer">
            <span id="update_order_pay_status_span"></span>
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
    $("#update_order_pay_status_pur_number").blur(function(){
        var pur_number = $.trim($('#update_order_pay_status_pur_number').val()); 
        if(pur_number == '') {
            $('#select_order_pay_status_pur_number').html('');
            $('#select_order_pay_pay_status_pur_number').html('');
            return false;
        }
        $.ajax({
            url: 'select-purchase-order-pay-status',
            data: {pur_number: pur_number},
            type: 'post',
            dataType: 'json',
            success: function(data) {
               // var d =  data.message;
               var purchase_order =  data.purchase_order;
               $('#select_order_pay_status_pur_number').html('');
               if (purchase_order=='') {
                   $('#select_order_pay_status_pur_number').append('<span style="font-weight: bold;color:red;">没有该采购单 -- '+ pur_number + '</span>');
               } else {
                   $(purchase_order).each(function(pk,pv) {
                       $(pv).each(function(k,v) {
                             $('#select_order_pay_status_pur_number').append('<tr class="size-row success">' +
                              '<td>' + v['pur_number'] +'</td>' +
                              '<td>' + v['pay_status'] +'</td>' +
                              '<td>' + v['purchas_status'] +'</td>' +
                              '<td>' + v['is_push'] +'</td>' +
                              '<td>' + v['buyer'] +'</td>' +
                              '<td>' + v['created_at'] +'</td>' +
                               '</tr');
                       });
                   });
               }
               
               var purchase_order_pay =  data.purchase_order_pay;
               $('#select_order_pay_pay_status_pur_number').html('');
               if (purchase_order_pay=='') {
                   $('#select_order_pay_pay_status_pur_number').append('<span style="font-weight: bold;color:red;">没有该采购单 -- '+ pur_number + '</span>');
               } else {
                   $(purchase_order_pay).each(function(sk,sv) {
                       $(sv).each(function(k,v) {
                             $('#select_order_pay_pay_status_pur_number').append('<tr class="size-row warning">' +
                              '<td>' + v['pur_number'] +'</td>' +
                              '<td>' + v['pay_status'] +'</td>' +
                              '<td>' + v['supplier_code'] +'</td>' +
                              '<td>' + v['pay_price'] +'</td>' +
                               '</tr');
                       });
                   });
               }
            }
        });   
    }) ;
    //================ 修改采购付款状态  ================================
    $('#update_order_pay_status_btn').click(function() {
        var pur_number = $.trim($('#update_order_pay_status_pur_number').val()); //采购单单号
        var pay_status_old = $.trim($('#update_order_pay_status_old').val()); //采购付款状态
        var pay_status_new = $.trim($('#update_order_pay_status_new').val()); //采购付款状态
        // $('#testSelect option:selected') .val();//选中的值
        if(pur_number == '' || pay_status_old == '' || pay_status_new == '') {
            alert('采购单单号 或 采购付款状态 不能为空');
            return false;
        }
        
        $.ajax({
            url: 'update-pay-status',
            data: {pur_number: pur_number, pay_status_old: pay_status_old,pay_status_new : pay_status_new},
            type: 'post',
            dataType: 'json',
            success: function(data) {
               var d =  data.message;
               $('#update_order_pay_status_span').html('');
               $(d).each(function(k,v) {
                     var color = kkk[v['color']];
                     $('#update_order_pay_status_span').append('<p style="font-weight:bold;color:'+color+'">'+v['msg']+'</p>');
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