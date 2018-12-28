<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use mdm\admin\components\Helper;
use yii\helpers\Url;
use app\services\BaseServices;

?>
<div class="panel panel-success">
    <h4 align="center" style="font-weight:bold;color: red;">修改采购需求</h4>
    <div class="panel panel-body">
        <h4>温馨小提示:</h4>
        <ol style="color:red;font-weight: bold;">
            <li>要求：知道【sku】和【需求单号】</li>
            <li>表名：pur_platform_summary</li>
        </ol>
        <div class="form-group">
            <label class="control-label">
                SKU
                <input type="text" id="update_platform_summary_sku_old" class="form-control update_platform_summary_class">
            </label>
            <label class="control-label">
                需求单号
                <input type="text" id="update_platform_summary_demand_number" class="form-control update_platform_summary_class">
            </label>
            <div class="table-responsive">
                <table style="table-layout: fixed;" class="table table-striped table-bordered table-hover">
                    <caption>采购需求表</caption>
                    <tbody id="select_platform_summary"></tbody>
                </table>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label">
                sku
                <input type="text" id="update_platform_summary_sku" class="form-control">
            </label>
            <label class="control-label">
                平台号
                <input type="text" id="update_platform_summary_platform_number" class="form-control">
            </label>
            <label class="control-label">
                产品名
                <input type="text" id="update_platform_summary_product_name" class="form-control">
            </label>
            <label class="control-label">
                采购数量
                <input type="number" id="update_platform_summary_purchase_quantity" class="form-control">
            </label>
            <label class="control-label">
                采购仓
                <select id="update_platform_summary_purchase_warehouse" class="form-control">
                    <option value="">请选择仓库</option>
                    <?php
                    foreach (\app\services\BaseServices::getWarehouseCode() as $k => $v) {
                        echo '<option value="' . $k . '">'. $v . ' 【' . $k .'】</option>';
                    }
                    ?>
                </select>
            </label>
            <label class="control-label">
                中转仓
                <select id="update_platform_summary_transit_warehouse" class="form-control">
                    <option value="">请选择仓库</option>
                    <?php
                    foreach (BaseServices::getWarehouseCode() as $k => $v) {
                        echo '<option value="' . $k . '">'. $v . ' 【' . $k .'】</option>';
                    }
                    ?>
                </select>
            </label>
            <label class="control-label">
                是否中转
                <select id="update_platform_summary_is_transit" class="form-control">
                    <option value="">请选择是否中转</option>
                    <option value="1">否【1】</option>
                    <option value="2">是【2】</option>
                </select>
            </label>
            <label class="control-label">
                创建人
                <select id="update_platform_summary_create_id" class="form-control">
                    <option value="">请选择创建人</option>
                    <?php
                    foreach (BaseServices::getEveryOne('','name') as $k => $v) {
                        echo '<option value="' . $k . '">'. $v . '</option>';
                    }
                    ?>
                </select>
            </label>
            <label class="control-label">
                审核状态
                <select id="update_platform_summary_level_audit_status" class="form-control">
                    <option value="">请选择审核状态</option>
                    <?php
                        foreach (Yii::$app->params['demand'] as $k=>$v) {
                            echo '<option value="' . $k . '">'. $v  . ' 【' . $k .'】</option>';
                        }
                    ?>
                </select>
            </label>
            <label class="control-label">
                采购员
                <select id="update_platform_summary_buyer" class="form-control">
                    <option value="">请选择采购员</option>
                    <?php
                    foreach (BaseServices::getEveryOne('','name') as $k => $v) {
                        echo '<option value="' . $k . '">'. $v  . '</option>';
                    }
                    ?>
                </select>
            </label>
            <label class="control-label hidden">
                产品类别
                <input type="hidden" id="update_platform_summary_product_category" class="form-control">
            </label>
            <label class="control-label">
                供应商code
                <select id="update_platform_summary_supplier_code" class="form-control">
                    <option value="">请选择供应商</option>
                    <?php
                    foreach (BaseServices::getSupplier() as $k => $v) {
                        echo '<option value="' . $k . '">'. $v  . ' 【' . $k .'】</option>';
                    }
                    ?>
                </select>
<!--                <input type="text" id="update_platform_summary_supplier_code" class="form-control">-->
            </label>
        </div>
        <div class="form-group">
            <?php
            if(Helper::checkRoute('update-platform-summary')) {
                echo '<span id="update_platform_summary_btn" class="btn btn-success">确认修改</span>';
            }
            ?>
        </div>
    </div>
    <div class="panel-footer">
        <span id="update_platform_summary_span"></span>
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
    $(".update_platform_summary_class").blur(function(){
        var sku = $.trim($('#update_platform_summary_sku_old').val()); 
        var demand_number = $.trim($('#update_platform_summary_demand_number').val()); 
        if(sku == '') {
            if (demand_number == '') {
                $('#select_order_items_price').html('');
                return false;
            }
        }
        
        $.ajax({
            url: 'select-platform-summary',
            data: {sku: sku,demand_number:demand_number},
            type: 'post',
            dataType: 'json',
            success: function(data) {
                //采购单物流信息表
               var platform_summary_info =  data.platform_summary_info;
               $('#select_platform_summary').html('');
               if (platform_summary_info=='') {
                   $('#select_platform_summary').append('<span style="font-weight: bold;color:red;">没有该需求</span>');
               } else {
                   $(platform_summary_info).each(function(pk,pv) {
                       $(pv).each(function(k,v) {
                             $('#select_platform_summary').append('<tr class="size-row success">' +
                              '<td>' + v['sku'] +'</td>' +
                              '<td>' + v['demand_number'] +'</td>' +
                              '<td>' + v['platform_number'] +'</td>' +
                              '<td>' + v['product_name'] +'</td>' +
                              '<td>' + v['purchase_quantity'] +'</td>' +
                              '<td>' + v['purchase_warehouse'] +'</td>' +
                              '<td>' + v['transit_warehouse'] +'</td>' +
                              '<td>' + v['is_transit'] +'</td>' +
                              '<td>' + v['create_id'] +'</td>' +
                              '<td>' + v['level_audit_status'] +'</td>' +
                              '<td>' + v['buyer'] +'</td>' +
                              // '<td>' + v['product_category'] +'</td>' +
                              '<td>' + v['supplier_code'] +'</td>' +
                              '<td>' + v['supplier_name'] +'</td>' +
                               '</tr');
                       });
                   });
               }
            }
        });   
    }) ;
    //===============  处理 作废单被入错库   ======================
    $('#update_platform_summary_btn').click(function() {
        var sku_old = $.trim($('#update_platform_summary_sku_old').val()); 
        var demand_number = $.trim($('#update_platform_summary_demand_number').val()); 
        var sku = $.trim($('#update_platform_summary_sku').val()); 
        var platform_number = $.trim($('#update_platform_summary_platform_number').val()); 
        var product_name = $.trim($('#update_platform_summary_product_name').val()); 
        var purchase_quantity = $.trim($('#update_platform_summary_purchase_quantity').val()); 
        var purchase_warehouse = $.trim($('#update_platform_summary_purchase_warehouse').val());
        var transit_warehouse = $.trim($('#update_platform_summary_transit_warehouse').val());
        var is_transit = $.trim($('#update_platform_summary_is_transit').val());
        var create_id = $.trim($('#update_platform_summary_create_id').val());
        var level_audit_status = $.trim($('#update_platform_summary_level_audit_status').val());
        var buyer = $.trim($('#update_platform_summary_buyer').val());
        var product_category = $.trim($('#update_platform_summary_product_category').val());
        var supplier_code = $.trim($('#update_platform_summary_supplier_code').val());
        if(demand_number == '' || sku_old=='') {
            alert('请输入sku和需求单号');
            return false;
        }
                
        $.ajax({
            url: 'update-platform-summary',
            data: {sku_old: sku_old,demand_number:demand_number, sku:sku,platform_number: platform_number,product_name:product_name,purchase_quantity:purchase_quantity,purchase_warehouse:purchase_warehouse, transit_warehouse:transit_warehouse, is_transit:is_transit, create_id:create_id, level_audit_status:level_audit_status,buyer:buyer,product_category:product_category,supplier_code:supplier_code},
            type: 'post',
            dataType: 'json',
            success: function(data) {
               var d =  data.message;
               $('#update_platform_summary_span').html('');
               $('#update_platform_summary_sku').val('');
               $('#update_platform_summary_platform_number').val('');
               $('#update_platform_summary_product_name').val('');
               $('#update_platform_summary_purchase_quantity').val('');
               $('#update_platform_summary_purchase_warehouse').val('');
               $('#update_platform_summary_transit_warehouse').val('');
               $('#update_platform_summary_is_transit').val('');
               $('#update_platform_summary_create_id').val('');
               $('#update_platform_summary_level_audit_status').val('');
               $('#update_platform_summary_buyer').val('');
               $('#update_platform_summary_product_category').val('');
               $('#update_platform_summary_supplier_code').val('');
               $(d).each(function(k,v) {
                     var color = kkk[v['color']];
                     console.log(color,v['msg']);
                     $('#update_platform_summary_span').append('<p style="font-weight:bold;color:'+color+'">'+v['msg']+'</p>');
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

