<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use mdm\admin\components\Helper;
use yii\helpers\Url;
/* @var $this yii\web\View */
/* @var $model app\models\OverseasWarehouseGoodsTaxRebate */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="panel panel-success">
    <h4 align="center" style="font-weight:bold;color: red;">海外仓-采购建议-供应商：新建采购计划单报错问题</h4>
    <div class="panel panel-body">
        <h4>温馨小提示:</h4>
        <ol style="color:red;font-weight: bold;">
            <li>【海外仓-采购建议-供应商】栏目--新建采购计划单报错</li>
            <li>【海外仓-采购建议-供应商】操作--采购单:找到【供应商名】和【sku】</li>
        </ol>
        <div class="form-group">
            <label class="control-label" for="supplier_name">需求单号</label>
            <input type="text" id="supplier_name" class="form-control">
            <div class="table-responsive">
                <table style="table-layout: fixed;" class="table table-striped table-bordered table-hover">
                    <caption>采购建议表</caption>
                    <tbody id="select-is-purchase"></tbody>
                </table>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label" for="sku">sku</label>
            <input type="text" id="sku" class="form-control">
        </div>
        <div class="form-group">
            <?php
            if(Helper::checkRoute('update-is-purchase')) {
                echo '<span id="hwc_create_order" class="btn btn-success">Create</span>';
            }
            ?>
        </div>
    </div>
    <div class="panel-footer">
        <span id="hwc_create_order_span"></span>
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
    $("#supplier_name").blur(function(){
        var supplier_name = $.trim($('#supplier_name').val()); 
        if(supplier_name == '') {
            $('#select-is-purchase').html('');
            return false;
        }

        $.ajax({
            url: 'select-is-purchase',
            data: {supplier_name: supplier_name},
            type: 'post',
            dataType: 'json',
            success: function(data) {
               var suggest =  data.suggest;
               
               $('#select-is-purchase').html('');
               if (suggest=='') {
                   $('#select-is-purchase').append('<span style="font-weight: bold;color:red;">没有该供应商 -- '+ supplier_name + '</span>');
               } else {
                   $(suggest).each(function(pk,pv) {
                       $(pv).each(function(k,v) {
                             $('#select-is-purchase').append('<tr class="size-row success">' +
                              '<td>' + v['supplier_name'] +'</td>' +
                              '<td>' + v['sku'] +'</td>' +
                              '<td>' + v['is_purchase'] +'</td>' +
                              '<td>' + v['state'] +'</td>' +
                              '<td>' + v['product_status'] +'</td>' +
                              '<td>' + v['demand_number'] +'</td>' +
                               '</tr');
                       });
                   });
               }
            }
        });   
    }) ;
    //================  海外仓-采购建议-供应商：新建采购计划单报错问题  ================================
    $('#hwc_create_order').click(function() {
        var v1 = $.trim($('#supplier_name').val()); //供应商名称
        var v2 = $.trim($('#sku').val()); //sku
        if(v1 == '' || v2 == '') {
            alert('供应商名称 或 sku 不能为空');
            return false;
        }
        
        $.ajax({
            url: 'update-is-purchase',
            data: {supplier_name: v1, sku: v2},
            type: 'post',
            dataType: 'json',
            success: function(data) {
               var d =  data.message;
               $('#hwc_create_order_span').html('');
               $(d).each(function(k,v) {
                     var color = kkk[v['color']];
                     $('#hwc_create_order_span').append('<p style="font-weight:bold;color:'+color+'">'+v['msg']+'</p>');
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