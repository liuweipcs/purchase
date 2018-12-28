<?php

use app\services\PurchaseOrderServices;
use app\config\Vhelper;
use app\services\BaseServices;
use mdm\admin\components\Helper;
?>

    <div class="panel panel-success">
        <h4 align="center" style="font-weight:bold;color: red;">修改：采购单仓库</h4>
        <div class="panel panel-body">
            <h4>温馨小提示:</h4>
            <ol style="color:red;font-weight: bold;">
                <li>要求：知道采购单单号</li>
            </ol>
            <div class="form-group">
                <label class="control-label" for="update_purchase_order_warehouse_code_pur_number">采购单单号</label>
                <input type="text" id="update_purchase_order_warehouse_code_pur_number" class="form-control">
                <div class="table-responsive">
                    <table style="table-layout: fixed;" class="table table-striped table-bordered table-hover">
                        <caption>采购订单主表</caption>
                        <tbody id="select_purchase_order_warehouse_code_pur_number"></tbody>
                    </table>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label" for="update_purchase_order_warehouse_code_new">采购单仓库
                <select id="update_purchase_order_warehouse_code_new" class="form-control">
                    <option value="">请选择仓库</option>
                    <?php
                    foreach (BaseServices::getWarehouseCode() as $k => $v) {
                        echo '<option value="' . $k . '">'. $v . ' 【' . $k .'】</option>';
                    }
                    ?>
                </select>
                </label>
                <label class="control-label" for="update_purchase_order_transit_warehouse_new">中转仓
                <select id="update_purchase_order_transit_warehouse_new" class="form-control">
                    <option value="">请选择中转仓库</option>
                    <?php
                    foreach (BaseServices::getWarehouseCode() as $k => $v) {
                        echo '<option value="' . $k . '">'. $v. ' 【' . $k  .'】</option>';
                    }
                    ?>
                </select>
                </label>
            </div>
            <div class="form-group">
                <?php
                if(Helper::checkRoute('update-purchase-order-warehouse-code')) {
                    echo '<span id="update_purchase_order_warehouse_code_btn" class="btn btn-success">修改采购单仓库</span>';
                }
                ?>

            </div>
        </div>
        <div class="panel-footer">
            <span id="update_purchase_order_warehouse_code_span"></span>
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
    $("#update_purchase_order_warehouse_code_pur_number").blur(function(){
        var pur_number = $.trim($('#update_purchase_order_warehouse_code_pur_number').val()); 
        if(pur_number == '') {
            $('#select_purchase_order_warehouse_code_pur_number').html('');
            return false;
        }
        $.ajax({
            url: 'select-purchase-order-warehouse-code',
            data: {pur_number: pur_number},
            type: 'post',
            dataType: 'json',
            success: function(data) {
               var purchase_order =  data.purchase_order;
               $('#select_purchase_order_warehouse_code_pur_number').html('');
               if (purchase_order=='') {
                   $('#select_purchase_order_warehouse_code_pur_number').append('<span style="font-weight: bold;color:red;">没有该采购单 -- '+ pur_number + '</span>');
               } else {
                   $(purchase_order).each(function(pk,pv) {
                       $(pv).each(function(k,v) {
                             $('#select_purchase_order_warehouse_code_pur_number').append('<tr class="size-row success">' +
                              '<td>' + v['pur_number'] +'</td>' +
                              '<td>' + v['warehouse_code'] +'</td>' +
                              '<td>' + v['transit_warehouse'] +'</td>' +
                              '<td>' + v['purchas_status'] +'</td>' +
                              '<td>' + v['is_push'] +'</td>' +
                              '<td>' + v['buyer'] +'</td>' +
                              '<td>' + v['created_at'] +'</td>' +
                               '</tr');
                       });
                   });
               }
            }
        });   
    }) ;
    //================ 修改采购单状态  ================================
    $('#update_purchase_order_warehouse_code_btn').click(function() {
        var pur_number = $.trim($('#update_purchase_order_warehouse_code_pur_number').val()); //采购单单号
        var warehouse_code = $.trim($('#update_purchase_order_warehouse_code_new').val()); //采购仓库
        var transit_warehouse = $.trim($('#update_purchase_order_transit_warehouse_new').val()); //采购中转仓


        if(pur_number == '') {
            alert('采购单单号');
            return false;
        }

        $.ajax({
            url: 'update-purchase-order-warehouse-code',
            data: {pur_number: pur_number, warehouse_code: warehouse_code,transit_warehouse:transit_warehouse},
            type: 'post',
            dataType: 'json',
            success: function(data) {
               var d =  data.message;
               $('#update_purchase_order_warehouse_code_span').html('');
               $(d).each(function(k,v) {
                     var color = kkk[v['color']];
                     $('#update_purchase_order_warehouse_code_span').append('<p style="font-weight:bold;color:'+color+'">'+v['msg']+'</p>');
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