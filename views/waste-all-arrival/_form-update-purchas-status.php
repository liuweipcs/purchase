<?php

use app\services\PurchaseOrderServices;
use app\config\Vhelper;
use mdm\admin\components\Helper;
use app\services\BaseServices;
?>

    <div class="panel panel-success">
        <h4 align="center" style="font-weight:bold;color: red;">修改采购单状态</h4>
        <div class="panel panel-body">
            <h4>温馨小提示:</h4>
            <ol style="color:red;font-weight: bold;">
                <li>要求：知道采购单单号</li>
                <li>注意：修改的是【采购单状态】</li>
            </ol>
            <div class="form-group">
                <label class="control-label" for="update_order_purchas_status_pur_number">采购单单号</label>
                <input type="text" id="update_order_purchas_status_pur_number" class="form-control">
                <div class="table-responsive">
                    <table style="table-layout: fixed;" class="table table-striped table-bordered table-hover">
                        <caption>采购订单主表</caption>
                        <tbody id="select_order_purchas_status_pur_number"></tbody>
                    </table>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label" for="update_order_purchas_status_new">采购单状态
                    <select id="update_order_purchas_status_new" class="form-control">
                        <option value="">请选择采购单状态</option>
                    <?php
                        foreach (PurchaseOrderServices::getPurchaseStatus() as $k => $v) {
                            echo '<option value="' . $k . '">'. $v . '--' . $k .'</option>';
                        }
                    ?>
                    </select>
                </label>
                <label class="control-label" for="update_order_refund_status_new">退款状态
                <select id="update_order_refund_status_new" class="form-control">
                    <option value="">请选择退款状态</option>
                <?php
                    foreach (PurchaseOrderServices::getReceiptStatusCss() as $k => $v) {
                        echo '<option value="' . $k . '">'. $v . '--' . $k .'</option>';
                    }
                ?>
                </select>
                </label>
                <label class="control-label" for="update_order_buyer_new">采购员
                <select id="update_order_buyer_new" class="form-control">
                    <option value="">请选择采购员</option>
                <?php
                    foreach (BaseServices::getEveryOne('','name') as $k => $v) {
                        echo '<option value="' . $k . '">'. $v .'</option>';
                    }
                ?>
                </select>
                </label>
            </div>
            <div class="form-group">
                <?php
                if(Helper::checkRoute('update-purchas-status')) {
                    echo '<span id="update_purchas_status_btn" class="btn btn-success">修改采购单状态</span>';
                }
                ?>

            </div>


        </div>
        <div class="panel-footer">
            <span id="update_purchas_status_span"></span>
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
    $("#update_order_purchas_status_pur_number").blur(function(){
        var pur_number = $.trim($('#update_order_purchas_status_pur_number').val()); 
        if(pur_number == '') {
            $('#select_order_purchas_status_pur_number').html('');
            return false;
        }
        $.ajax({
            url: 'select-purchas-status',
            data: {pur_number: pur_number},
            type: 'post',
            dataType: 'json',
            success: function(data) {
               var purchase_order =  data.purchase_order;
               $('#select_order_purchas_status_pur_number').html('');
               if (purchase_order=='') {
                   $('#select_order_purchas_status_pur_number').append('<span style="font-weight: bold;color:red;">没有该采购单 -- '+ pur_number + '</span>');
               } else {
                   $(purchase_order).each(function(pk,pv) {
                       $(pv).each(function(k,v) {
                             $('#select_order_purchas_status_pur_number').append('<tr class="size-row success">' +
                              '<td>' + v['pur_number'] +'</td>' +
                              '<td>' + v['purchas_status'] +'</td>' +
                              '<td>' + v['refund_status'] +'</td>' +
                              '<td>' + v['buyer'] +'</td>' +
                              '<td>' + v['is_push'] +'</td>' +
                              '<td>' + v['created_at'] +'</td>' +
                               '</tr');
                       });
                   });
               }
            }
        });   
    }) ;
    //================ 修改采购单状态  ================================
    $('#update_purchas_status_btn').click(function() {
        var pur_number = $.trim($('#update_order_purchas_status_pur_number').val()); //采购单单号
        var purchas_status = $.trim($('#update_order_purchas_status_new').val()); //采购状态
        var refund_status = $.trim($('#update_order_refund_status_new').val()); //退款状态
        var buyer = $.trim($('#update_order_buyer_new').val()); //退款状态
        // $('#testSelect option:selected') .val();//选中的值
        if(pur_number == '') {
            alert('采购单单号不能为空');
            return false;
        }
        
        $.ajax({
            url: 'update-purchas-status',
            data: {pur_number: pur_number, purchas_status: purchas_status,refund_status:refund_status,buyer:buyer},
            type: 'post',
            dataType: 'json',
            success: function(data) {
               var d =  data.message;
               var mydate = new Date();
               var t=mydate.toLocaleString();
               $('#update_purchas_status_span').html('');
               $(d).each(function(k,v) {
                     var color = kkk[v['color']];
                     $('#update_purchas_status_span').append('<p style="font-weight:bold;color:'+color+'">'+v['msg']+ t +'</p>');
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