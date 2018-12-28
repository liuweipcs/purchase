<?php

use app\services\SupplierGoodsServices;
use app\config\Vhelper;
use mdm\admin\components\Helper;
?>
<style>
    #layui-layer1-pull-erp-sku {
        position: relative;
        left: 210px;
        top: 260px;
        visibility: hidden;
        /*visibility: visible;*/
    }
</style>
    <div class="panel panel-success">
        <div class="layui-layer layui-layer-loading" id="layui-layer1-pull-erp-sku" type="loading" times="1" showtime="0" contype="string" style="z-index: 19891015;">
            <div id="" class="layui-layer-content layui-layer-loading0"></div>
            <span class="layui-layer-setwin"></span>
        </div>
        <h4 align="center" style="font-weight:bold;color: red;">拉取erp中的sku</h4>
        <div class="panel panel-body">
            <h4>温馨小提示:</h4>
            <ol style="color:red;font-weight: bold;">
                <li>要求：知道sku</li>
                <li>先拉取erp中sku</li>
                <li>再拉取中间件的sku</li>
                <li>最后推送sku</li>
                <li>【注意：】推送sku时，当转的时间为1s时，就代表推送成功；当要等好几秒时，就代表要继续推送sku</li>
            </ol>
            <div class="form-group">
                <label class="control-label" for="pull_erp_sku_sku">sku</label>
                <input type="text" id="pull_erp_sku_sku" class="form-control">
                <div class="table-responsive">
                    <table style="table-layout: fixed;" class="table table-striped table-bordered table-hover">
                        <caption>产品列表</caption>
                        <tbody id="select_pull_erp_sku_sku"></tbody>
                    </table>
                </div>
            </div>

            <!--<div class="progress">
                <div id="p1" class="progress-bar" role="progressbar" aria-valuenow="60"
                     aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                    <span class="sr-only">40% 完成</span>
                </div>
            </div>-->

            <div class="form-group">
                <?php
                if(Helper::checkRoute('pull-erp-sku')) {
                    echo '<span id="pull_erp_sku_btn" class="btn btn-success">拉取erp中sku</span>';
                }
                ?>
                <?php
                if(Helper::checkRoute('pull-erp-sku-center')) {
                    echo '<span id="pull_erp_sku_center_btn" class="btn btn-success">拉取中间件的sku</span>';
                }
                ?>
                <?php
                if(Helper::checkRoute('push-erp-sku')) {
                    echo '<span id="push_erp_sku_btn" class="btn btn-success">推送sku</span>';
                }
                ?>
            </div>
        </div>
        <div class="panel-footer">
            <span id="pull_erp_sku_span"></span>
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
    $("#pull_erp_sku_sku").blur(function(){
        var sku = $.trim($('#pull_erp_sku_sku').val()); 
        if(sku == '') {
            $('#select_pull_erp_sku_sku').html('');
            return false;
        }
        $.ajax({
            url: 'select-pull-erp-sku',
            data: {sku: sku},
            type: 'post',
            dataType: 'json',
            success: function(data) {
               var product =  data.product;
               $('#select_pull_erp_sku_sku').html('');
               if (product=='') {
                   $('#select_pull_erp_sku_sku').append('<span style="font-weight: bold;color:red;">没有该产品 -- '+ sku + '</span>');
               } else {
                   $(product).each(function(pk,pv) {
                       $(pv).each(function(k,v) {
                             $('#select_pull_erp_sku_sku').append('<tr class="size-row success">' +
                              '<td>' + v['sku'] +'</td>' +
                              '<td>' + v['product_status'] +'</td>' +
                              '<td>' + v['create_id'] +'</td>' +
                              '<td>' + v['product_type'] +'</td>' +
                               '</tr');
                       });
                   });
               }
            }
        });   
    }) ;
    
    //================ 修改产品状态  ================================
    //将erp中的sku推送到仓库
    $('#pull_erp_sku_btn').click(function() {
        var sku = $.trim($('#pull_erp_sku_sku').val()); 
        if(sku == '') {
            alert('sku 不能为空');
            return false;
        }
        
        var visibility = $("#layui-layer1-pull-erp-sku").css('visibility');
        if (visibility == 'visible') {
            return false;
        }
        $("#layui-layer1-pull-erp-sku").css('visibility','visible');
        
        $.ajax({
            url: 'pull-erp-sku',
            data: {sku: sku},
            type: 'post',
            dataType: 'json',
            success: function(data) {
                console.log(data);
                $("#layui-layer1-pull-erp-sku").css('visibility','hidden');
                console.log(data);
                var d =  data.message;
                $('#pull_erp_sku_span').html('');
                $(d).each(function(k,v) {
                     var color = kkk[v['color']];
                     $('#pull_erp_sku_span').append('<p style="font-weight:bold;color:'+color+'">'+v['msg']+'</p>');
                });
            }
        });   
    });
    //拉取中间件中的sku
    $('#pull_erp_sku_center_btn').click(function() {
        var sku = $.trim($('#pull_erp_sku_sku').val()); 
        if(sku == '') {
            alert('sku 不能为空');
            return false;
        }
        $.ajax({
            url: 'pull-erp-sku-center',
            data: {sku: sku},
            type: 'post',
            dataType: 'json',
            success: function(data) {
                console.log(data);
            }
        });   
    });
    //将仓库中的sku推送过来
    $('#push_erp_sku_btn').click(function() {
        var sku = $.trim($('#pull_erp_sku_sku').val()); 
        if(sku == '') {
            alert('sku 不能为空');
            return false;
        }
        
        // var index = layer.load(0, {shade: false}); //开启加载层
        // layer.close(index); //关闭加载层
        // $('#layui-layer1-pull-erp-sku').is(':visible');
        var visibility = $("#layui-layer1-pull-erp-sku").css('visibility');
        if (visibility == 'visible') {
            return false;
        }
        $("#layui-layer1-pull-erp-sku").css('visibility','visible');
        
        $.ajax({
            url: 'push-erp-sku',
            data: {sku: sku},
            type: 'post',
            dataType: 'json',
            success: function(data) {
                $("#layui-layer1-pull-erp-sku").css('visibility','hidden');
                console.log(data);
                var d =  data.message;
                $('#pull_erp_sku_span').html('');
                $(d).each(function(k,v) {
                     var color = kkk[v['color']];
                     $('#pull_erp_sku_span').append('<p style="font-weight:bold;color:'+color+'">'+v['msg']+'</p>');
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