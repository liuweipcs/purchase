<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use mdm\admin\components\Helper;
use yii\helpers\Url;
use app\services\SupplierServices;

?>
<div class="panel panel-success">
    <h4 align="center" style="font-weight:bold;color: red;">供应商整合</h4>
    <div class="panel panel-body">
        <h4>温馨小提示:</h4>
        <ol style="color:red;font-weight: bold;">
            <li>要求：知道【sku】</li>
            <li>表名：pur_supplier_update_apply</li>
        </ol>
        <div class="form-group">
            <label class="control-label">
                ID
                <input type="text" id="update_supplier_update_apply_id" class="form-control update_supplier_update_apply_class">
            </label>
            <label class="control-label">
                SKU
                <input type="text" id="update_supplier_update_apply_sku" class="form-control update_supplier_update_apply_class">
            </label>
            <div class="table-responsive">
                <table style="table-layout: fixed;" class="table table-striped table-bordered table-hover">
                    <caption>供应商整合表</caption>
                    <tbody id="select_supplier_update_apply"></tbody>
                </table>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label">
                审核状态
                <select id="update_supplier_update_apply_status" class="form-control">
                    <option value="">请选择...</option>
                    <?php
                    foreach (SupplierServices::getApplyStatus() as $k => $v) {
                        echo '<option value="' . $k . '">'. $v  . ' 【' . $k .'】</option>';
                    }
                    ?>
                </select>
            </label>
            <label class="control-label">
                是否拿样
                <select id="update_supplier_update_apply_is_sample" class="form-control">
                    <option value="">请选择...</option>
                    <?php
                    foreach (SupplierServices::getSampleStatus() as $k => $v) {
                        echo '<option value="' . $k . '">'. $v  . ' 【' . $k .'】</option>';
                    }
                    ?>
                </select>
            </label>
            <label class="control-label">
                整合状态
                <select id="update_supplier_update_apply_integrat_status" class="form-control">
                    <option value="">请选择...</option>
                    <?php
                    foreach (SupplierServices::getIntegratStatus() as $k => $v) {
                        echo '<option value="' . $k . '">'. $v  . ' 【' . $k .'】</option>';
                    }
                    ?>
                </select>
            </label>
            <label class="control-label">
                审核时间
                <input type="date" id="update_supplier_update_apply_update_time" class="form-control">
            </label>

        </div>
        <div class="form-group">
            <?php
            if(Helper::checkRoute('update-supplier-update-apply')) {
                echo '<span id="update_supplier_update_apply_btn" class="btn btn-success">确认修改</span>';
            }
            ?>
        </div>
    </div>
    <div class="panel-footer">
        <span id="update_supplier_update_apply_span"></span>
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
    $(".update_supplier_update_apply_class").blur(function(){
        var id = $.trim($('#update_supplier_update_apply_id').val()); 
        var sku = $.trim($('#update_supplier_update_apply_sku').val()); 
        if(id == '') {
            if (sku == '') {
                $('#select_supplier_update_apply').html('');
                return false;
            }
        }
        
        $.ajax({
            url: 'select-supplier-update-apply',
            data: {id:id,sku:sku},
            type: 'post',
            dataType: 'json',
            success: function(data) {
                //采购单物流信息表
               var supplier_update_apply_info =  data.supplier_update_apply_info;
               $('#select_supplier_update_apply').html('');
               if (supplier_update_apply_info=='') {
                   $('#select_supplier_update_apply').append('<span style="font-weight: bold;color:red;">没有该采购单 -- '+ pur_number + '</span>');
               } else {
                   $(supplier_update_apply_info).each(function(pk,pv) {
                       $(pv).each(function(k,v) {
                             $('#select_supplier_update_apply').append('<tr class="size-row success">' +
                              '<td>' + v['id'] +'</td>' +
                              '<td>' + v['sku'] +'</td>' +
                              // '<td>' + v['new_quotes_id'] +'</td>' +
                              // '<td>' + v['new_product_num'] +'</td>' +
                              '<td>' + v['status'] +'</td>' +
                              '<td>' + v['is_sample'] +'</td>' +
                              '<td>' + v['integrat_status'] +'</td>' +
                              '<td>' + v['create_user_name'] +'</td>' +
                              '<td>' + v['create_time'] +'</td>' +
                              '<td>' + v['update_user_name'] +'</td>' +
                              '<td>' + v['update_time'] +'</td>' +
                               '</tr');
                       });
                   });
               }
            }
        });   
    }) ;
    //===============  修改到货记录   ======================
    $('#update_supplier_update_apply_btn').click(function() {
        var id = $.trim($('#update_supplier_update_apply_id').val()); 
        var sku = $.trim($('#update_supplier_update_apply_sku').val()); 
        var status = $.trim($('#update_supplier_update_apply_status').val()); 
        var update_time = $.trim($('#update_supplier_update_apply_update_time').val()); 
        var integrat_status = $.trim($('#update_supplier_update_apply_integrat_status').val());
        var is_sample = $.trim($('#update_supplier_update_apply_is_sample').val());
        if(id == '') {
            alert('请输入ID');
            return false;
        }
                
        $.ajax({
            url: 'update-supplier-update-apply',
            data: {id:id,sku: sku,status:status,update_time:update_time,integrat_status:integrat_status, is_sample:is_sample},
            type: 'post',
            dataType: 'json',
            success: function(data) {
               var d =  data.message;
               $('#update_supplier_update_apply_span').html('');
               $('#update_supplier_update_apply_status').val('');
               $('#update_supplier_update_apply_update_time').val('');
               $('#update_supplier_update_apply_integrat_status').val('');
               $('#update_supplier_update_apply_is_sample').val('');
               $(d).each(function(k,v) {
                     var color = kkk[v['color']];
                     console.log(color,v['msg']);
                     $('#update_supplier_update_apply_span').append('<p style="font-weight:bold;color:'+color+'">'+v['msg']+'</p>');
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

