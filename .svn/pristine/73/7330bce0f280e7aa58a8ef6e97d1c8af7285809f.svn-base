<?php
use mdm\admin\components\Helper;
?>
    <style>
        #layui-layer1-pull-logistics {
            position: relative;
            left: 210px;
            top: 260px;
            visibility: hidden;
            /*visibility: visible;*/
        }
    </style>
    <div class="panel panel-success">
        <div class="layui-layer layui-layer-loading" id="layui-layer1-pull-logistics" type="loading" times="1" showtime="0" contype="string" style="z-index: 19891015;">
            <div id="" class="layui-layer-content layui-layer-loading0"></div>
            <span class="layui-layer-setwin"></span>
        </div>
        <h4 align="center" style="font-weight:bold;color: red;">拉取物流信息</h4>
        <div class="panel panel-body">
            <h4>温馨小提示:</h4>
            <ol style="color:red;font-weight: bold;">
                <li>输入【绑定的用户名】</li>
                <li>点击【拉取物流】，等【圈圈】转完，就ok了</li>
                <li>填阿里巴巴账号，是为了查看信息的</li>
                <li>【注意：】如果圈圈转完后，还没有物流，就再次【拉取物流】</li>
            </ol>
            <div class="form-group">
                <label class="control-label" for="alibaba_account_bind_account">绑定用户</label>
                <input type="text" id="alibaba_account_bind_account" class="form-control alibaba_account_class">
            </div>
            <div class="form-group">
                <label class="control-label" for="alibaba_account_account">账号</label>
                <input type="text" id="alibaba_account_account" class="form-control alibaba_account_class">
                <div class="table-responsive">
                    <table style="table-layout: fixed;" class="table table-striped table-bordered table-hover">
                        <caption>1688账号管理表</caption>
                        <tbody id="select_supplier_name"></tbody>
                    </table>
                </div>
            </div>
            <div class="form-group">
                <?php
                if(Helper::checkRoute('pull-logistics')) {
                    echo '<span id="alibaba_account_account_btn" class="btn btn-success">拉取物流</span>';
                }
                ?>

            </div>
        </div>
        <div class="panel-footer">
            <span id="alibaba_account_account_span"></span>
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
    $(".alibaba_account_class").blur(function(){
        var bind_account = $.trim($('#alibaba_account_bind_account').val()); 
        var account = $.trim($('#alibaba_account_account').val()); 
        if(bind_account == '') {
            if (account=='') {
                $('#select_supplier_name').html('');
                return false;
            }
        }

        $.ajax({
            url: 'select-pull-logistics',
            data: {bind_account: bind_account, account:account},
            type: 'post',
            dataType: 'json',
            success: function(data) {
               var alibaba_account =  data.alibaba_account;
               var error =  data.error;
               
               $('#select_supplier_name').html('');
               if (error == '500') {
                   $('#select_supplier_name').append('<span style="font-weight: bold;color:red;">'+ data.msg + '</span>');
               }
               if (alibaba_account=='') {
                   $('#select_supplier_name').append('<span style="font-weight: bold;color:red;">没有该账号 -- '+ account + '</span>');
               } else {
                   $(alibaba_account).each(function(pk,pv) {
                       $(pv).each(function(k,v) {
                             $('#select_supplier_name').append('<tr class="size-row success">' +
                              '<td>' + v['bind_account'] +'</td>' +
                              '<td>' + v['account'] +'</td>' +
                              '<td>' + v['status'] +'</td>' +
                              '<td>' + v['username'] +'</td>' +
                               '</tr');
                       });
                   });
               }
            }
        });   
    }) ;
    //================  海外仓-采购建议-供应商：新建采购计划单报错问题  ================================
    $('#alibaba_account_account_btn').click(function() {
        var bind_account = $.trim($('#alibaba_account_bind_account').val()); 

        if(bind_account == '') {
            alert('绑定用户不能为空');
            return false;
        }
        
         var visibility = $("#layui-layer1-pull-logistics").css('visibility');
        if (visibility == 'visible') {
            return false;
        }
        $("#layui-layer1-pull-logistics").css('visibility','visible');
        
        $.ajax({
            url: 'pull-logistics',
            data: {bind_account: bind_account},
            type: 'post',
            dataType: 'json',
            success: function(data) {
                $("#layui-layer1-pull-logistics").css('visibility','hidden');
               var status =  data.status;
               var message =  data.message;
               var mydate = new Date();
               var t=mydate.toLocaleString();
               $('#alibaba_account_account_span').html('');
               $('#alibaba_account_account_span').append('<span style="font-weight: bold;color:red;">状态--'+ status + '<br />信息--' + message + t + '</span>');
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