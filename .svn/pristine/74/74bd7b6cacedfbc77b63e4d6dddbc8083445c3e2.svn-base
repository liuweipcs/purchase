<?php

use app\services\SupplierGoodsServices;
use app\config\Vhelper;
use mdm\admin\components\Helper;
?>

    <div class="panel panel-success">
        <h4 align="center" style="font-weight:bold;color: red;">修改--产品状态</h4>
        <div class="panel panel-body">
            <h4>温馨小提示:</h4>
            <ol style="color:red;font-weight: bold;">
                <li>要求：知道sku</li>
                <li>注意：修改的是【采购建议的产品状态】</li>
                <li>第一步：看看【产品列表】中的状态是什么</li>
                <li>第二步：看看【今天的采购建议】中的状态是什么</li>
                <li>第二步：修改【今天的采购建议】中的状态是</li>
            </ol>
            <div class="form-group">
                <label class="control-label" for="update_suggest_product_status_sku">sku</label>
                <input type="text" id="update_suggest_product_status_sku" class="form-control update_suggest_product_status">
                <label class="control-label" for="update_suggest_product_status_warehouse_name">仓库名称</label>
                <input type="text" id="update_suggest_product_status_warehouse_name" class="form-control update_suggest_product_status">
                <div class="table-responsive"> <!--class="panel-footer"-->
                    <!-- table-condensed  精简-->
                    <table style="table-layout: fixed;" class="table table-striped table-bordered table-hover">
                        <caption>产品列表</caption>
                        <!--<thead>
                            <tr>
                                <th>sku</th>
                                <th>产品状态</th>
                                <th>开发人员</th>
                                <th>是否捆绑</th>
                            </tr>
                        </thead>-->
                        <tbody id="select_product_product_status_sku_span"></tbody>
                    </table>
                    <table style="table-layout: fixed;" class="table table-striped table-bordered table-hover">
                        <caption>采购建议</caption>
                        <!--<thead>
                            <tr>
                                <th>sku</th>
                                <th>产品状态</th>
                                <th>仓库名称</th>
                                <th>采购员</th>
                            </tr>
                        </thead>-->
                        <tbody id="select_suggest_product_status_sku_span"></tbody>
                    </table>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label" for="update_suggest_product_status_sku_new">产品状态-修改之后的</label>
                <select id="update_suggest_product_status_sku_new" class="form-control">
                    <option value="">请选择产品状态</option>
                    <?php
                    foreach (SupplierGoodsServices::getProductStatus() as $k => $v) {
                        echo '<option value="' . $k . '">'. $v .'</option>';
                    }
                    ?>
                </select>
                <div class="help-block"></div>
            </div>
            <div class="form-group">
                <?php
                if(Helper::checkRoute('update-suggest-product-status')) {
                    echo '<span id="update_suggest_product_status_sku_btn" class="btn btn-success">修改产品状态</span>';
                }
                ?>

            </div>
        </div>
        <div class="panel-footer">
            <span id="update_suggest_product_status_sku_span"></span>
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
    $(".update_suggest_product_status").blur(function(){
        var sku = $.trim($('#update_suggest_product_status_sku').val()); 
        var warehouse_name = $.trim($('#update_suggest_product_status_warehouse_name').val()); 
        if(sku == '') {
            if (warehouse_name == '') {
                 $('#select_product_product_status_sku_span').html('');
                 $('#select_suggest_product_status_sku_span').html('');
                 return false;
            }
        }
        $.ajax({
            url: 'select-suggest-product-status',
            data: {sku: sku,warehouse_name:warehouse_name},
            type: 'post',
            dataType: 'json',
            success: function(data) {
               // var d =  data.message;
               var product =  data.product;
               var suggest =  data.suggest;
               $('#select_product_product_status_sku_span').html('');

               if (product=='') {
                   // alert('空空如也');
                   $('#select_product_product_status_sku_span').append('<span style="font-weight: bold;color:red;">没有该产品 -- '+ sku + '</span>');
               } else {
                   $(product).each(function(pk,pv) {
                       $(pv).each(function(k,v) {
                             $('#select_product_product_status_sku_span').append('<tr class="size-row success">' +
                              '<td>' + v['sku'] +'</td>' +
                              '<td>' + v['product_status'] +'</td>' +
                              '<td>' + v['create_id'] +'</td>' +
                              '<td>' + v['product_type'] +'</td>' +
                              // '<td>' + v['create_time'] +'</td>' +
                              // '<td>' + v['supplier_name'] +'</td>' +
                               '</tr');
                       });
                   });
               }
               //$('#select_suggest_product_status_sku_span').append('<br /><br />');
               $('#select_suggest_product_status_sku_span').html('');
               if (suggest=='') {
                   // alert('空空如也');
                   $('#select_suggest_product_status_sku_span').append('<span style="font-weight: bold;color:red;">找不到对应的采购建议 -- '+ sku + '</span>');
               } else {
                   $(suggest).each(function(sk,sv) {
                       $(sv).each(function(k,v) {
                             $('#select_suggest_product_status_sku_span').append('<tr class="size-row warning">' +
                              '<td>' + v['sku'] +'</td>' +
                              '<td>' + v['product_status'] +'</td>' +
                              // '<td>' + v['name'] +'</td>' +
                              '<td>' + v['warehouse_name'] +'</td>' +
                              // '<td>' + v['supplier_name'] +'</td>' +
                              '<td>' + v['buyer'] +'</td>' +
                               '</tr');
                       });
                   });
               }
            }
        });   
    }) ;
    
    //================ 修改产品状态  ================================
    $('#update_suggest_product_status_sku_btn').click(function() {
        var sku = $.trim($('#update_suggest_product_status_sku').val()); 
        var warehouse_name = $.trim($('#update_suggest_product_status_warehouse_name').val()); 
        var product_status = $.trim($('#update_suggest_product_status_sku_new').val()); //采购状态
        // $('#testSelect option:selected') .val();//选中的值
        if(sku == '' || product_status == '') {
            alert('sku 或 产品状态 不能为空');
            return false;
        }
        
        $.ajax({
            url: 'update-suggest-product-status',
            data: {sku: sku, warehouse_name:warehouse_name,product_status: product_status},
            type: 'post',
            dataType: 'json',
            success: function(data) {
               var d =  data.message;
               $('#update_suggest_product_status_sku_span').html('');
               $(d).each(function(k,v) {
                     var color = kkk[v['color']];
                     $('#update_suggest_product_status_sku_span').append('<p style="font-weight:bold;color:'+color+'">'+v['msg']+'</p>');
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