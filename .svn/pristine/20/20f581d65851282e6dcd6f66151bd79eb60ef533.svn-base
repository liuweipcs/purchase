<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use mdm\admin\components\Helper;
use yii\helpers\Url;

?>
<div class="panel panel-success">
    <h4 align="center" style="font-weight:bold;color: red;">修改入库信息</h4>
    <div class="panel panel-body">
        <h4>温馨小提示:</h4>
        <ol style="color:red;font-weight: bold;">
            <li>要求：知道【采购单单号】</li>
            <li>【注意】：国内和FBA的入库数量是【上架数量】</li>
            <li>【注意】：海外的入库数量是【到货数量】</li>
            <li>表名：pur_warehouse_results</li>
        </ol>
        <div class="form-group">
            <label class="control-label" for="update_warehouse_results_pur_number">
                采购单单号
                <input type="text" id="update_warehouse_results_pur_number" class="form-control update_order_items_price_class">
            </label>
            <label class="control-label" for="update_warehouse_results_sku">
                SKU
                <input type="text" id="update_warehouse_results_sku" class="form-control update_order_items_price_class">
            </label>
            <div class="table-responsive">
                <table style="table-layout: fixed;" class="table table-striped table-bordered table-hover">
                    <caption>入库结果表</caption>
                    <tbody id="select_warehouse_results"></tbody>
                </table>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label" for="update_warehouse_results_purchase_quantity">
                采购数量
                <input type="text" id="update_warehouse_results_purchase_quantity" class="form-control">
            </label>
            <label class="control-label" for="update_warehouse_results_arrival_quantity">
                到货数量
                <input type="number" id="update_warehouse_results_arrival_quantity" class="form-control">
            </label>
            <label class="control-label" for="update_warehouse_results_nogoods">
                不良品数量
                <input type="number" id="update_warehouse_results_nogoods" class="form-control">
            </label>
            <label class="control-label" for="update_warehouse_results_have_sent_quantity">
                赠送数量
                <input type="number" id="update_warehouse_results_have_sent_quantity" class="form-control">
            </label>
            <label class="control-label" for="update_warehouse_results_instock_qty_count">
                上架数量
                <input type="number" id="update_warehouse_results_instock_qty_count" class="form-control">
            </label>
            <label class="control-label" for="update_warehouse_results_instock_user">
                入库人
                <input type="text" id="update_warehouse_results_instock_user" class="form-control">
            </label>
            <label class="control-label" for="update_warehouse_results_instock_date">
                入库时间
                <input type="date" id="update_warehouse_results_instock_date" class="form-control">
            </label>
        </div>
        <div class="form-group">
            <?php
            if(Helper::checkRoute('update-warehouse-results')) {
                echo '<span id="update_warehouse_results_btn" class="btn btn-success">确认修改</span>';
            }
            ?>
        </div>
    </div>
    <div class="panel-footer">
        <span id="update_warehouse_results_span"></span>
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
    $(".update_order_items_price_class").blur(function(){
        var pur_number = $.trim($('#update_warehouse_results_pur_number').val()); 
        var sku = $.trim($('#update_warehouse_results_sku').val()); 
        if(pur_number == '') {
            if (sku == '') {
                $('#select_order_items_price').html('');
                return false;
            }
        }
        
        $.ajax({
            url: 'select-warehouse-results',
            data: {pur_number: pur_number,sku:sku},
            type: 'post',
            dataType: 'json',
            success: function(data) {
                //采购单物流信息表
               var purchase_warehouse_results_info =  data.purchase_warehouse_results_info;
               $('#select_warehouse_results').html('');
               if (purchase_warehouse_results_info=='') {
                   $('#select_warehouse_results').append('<span style="font-weight: bold;color:red;">没有该采购单 -- '+ pur_number + '</span>');
               } else {
                   $(purchase_warehouse_results_info).each(function(pk,pv) {
                       $(pv).each(function(k,v) {
                             $('#select_warehouse_results').append('<tr class="size-row success">' +
                              '<td>' + v['pur_number'] +'</td>' +
                              '<td>' + v['sku'] +'</td>' +
                              '<td>' + v['purchase_quantity'] +'</td>' +
                              '<td>' + v['arrival_quantity'] +'</td>' +
                              '<td>' + v['nogoods'] +'</td>' +
                              '<td>' + v['have_sent_quantity'] +'</td>' +
                              '<td>' + v['instock_qty_count'] +'</td>' +
                              '<td>' + v['instock_user'] +'</td>' +
                              '<td>' + v['instock_date'] +'</td>' +
                               '</tr');
                       });
                   });
               }
            }
        });   
    }) ;
    //===============  处理 作废单被入错库   ======================
    $('#update_warehouse_results_btn').click(function() {
        var pur_number = $.trim($('#update_warehouse_results_pur_number').val()); 
        var sku = $.trim($('#update_warehouse_results_sku').val()); 
        var purchase_quantity = $.trim($('#update_warehouse_results_purchase_quantity').val()); 
        var arrival_quantity = $.trim($('#update_warehouse_results_arrival_quantity').val()); 
        var nogoods = $.trim($('#update_warehouse_results_nogoods').val());
        var have_sent_quantity = $.trim($('#update_warehouse_results_have_sent_quantity').val());
        var instock_qty_count = $.trim($('#update_warehouse_results_instock_qty_count').val());
        var instock_user = $.trim($('#update_warehouse_results_instock_user').val());
        var instock_date = $.trim($('#update_warehouse_results_instock_date').val());
        if(pur_number == '' || sku=='') {
            alert('请输入采购单单号和sku');
            return false;
        }
                
        $.ajax({
            url: 'update-warehouse-results',
            data: {pur_number: pur_number, sku: sku,purchase_quantity:purchase_quantity,arrival_quantity:arrival_quantity,nogoods:nogoods, have_sent_quantity:have_sent_quantity, instock_qty_count:instock_qty_count, instock_user:instock_user, instock_date:instock_date},
            type: 'post',
            dataType: 'json',
            success: function(data) {
               var d =  data.message;
               $('#update_warehouse_results_span').html('');
               $('#update_warehouse_results_purchase_quantity').val('');
               $('#update_warehouse_results_arrival_quantity').val('');
               $('#update_warehouse_results_nogoods').val('');
               $('#update_warehouse_results_have_sent_quantity').val('');
               $('#update_warehouse_results_instock_qty_count').val('');
               $('#update_warehouse_results_instock_user').val('');
               $('#update_order_items_price_order_name').val('');
               $('#update_warehouse_results_instock_date').val('');
               $(d).each(function(k,v) {
                     var color = kkk[v['color']];
                     console.log(color,v['msg']);
                     $('#update_warehouse_results_span').append('<p style="font-weight:bold;color:'+color+'">'+v['msg']+'</p>');
               });
            }
        });   
    });
});
JS;
$this->registerJs($js);
$this->beginContent('@app/views/layouts/waste.php');
$this->endContent();
?>

