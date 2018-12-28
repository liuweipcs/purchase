<?php
use yii\widgets\ActiveForm;
$handler_type = !empty($model->handler_type) ? $model->handler_type : 6;
?>
<?php ActiveForm::begin(['id' => 'handler-form']); ?>

<div class="box box-success">

    <div class="box-body">

        <div class="alert alert-info" role="alert">
            <strong>系统提示：</strong> 处理类型为退货的，必须选择省份、城市，并填写详细地址，联系人以及联系电话。
        </div>

        <div class="form-group">
            <label>异常单号</label>
            <input type="text" class="form-control" name="defective_id" value="<?= $model->defective_id ?>" style="width:300px;" readonly>
        </div>

        <div class="form-group">

            <label>处理类型</label><br/>

            <input type="radio" name="handler_type" value="6" <?php if($handler_type == 6){ ?> checked <?php } ?>> 优品入库

            <input type="radio" name="handler_type" value="7" <?php if($handler_type == 7){ ?> checked <?php } ?>> 整批退货

            <input type="radio" name="handler_type" value="8" <?php if($handler_type == 8){ ?> checked <?php } ?>> 二次包装

            <input type="radio" name="handler_type" value="9" <?php if($handler_type == 9){ ?> checked <?php } ?>> 正常入库

            <input type="radio" name="handler_type" value="10" <?php if($handler_type == 10){ ?> checked <?php } ?>> 不做处理

        </div>

        <div class="form-group">
            <label>采购单号</label>
            <input type="text" class="form-control" name="purchase_order_no" id="order_no" value="<?= $model->purchase_order_no ?>" style="width:300px;">
        </div>

        <div class="form-group">
            <label>退货地址</label>
            <div class="clearfix"></div>
            <div class="col-md-3" style="padding-left: 0px;">
                <select class="form-control" name="return_province" id="province">
                    <option value="">请选择省份...</option>
                    <?php foreach($pro as $k => $v): ?>
                        <option value="<?= $k ?>"><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3" style="padding-left: 0px;">
                <select class="form-control" name="return_city" id="city">
                    <option value="">请先选择省份...</option>
                </select>
            </div>
            <div class="col-md-5" style="padding-left: 0px;">
                <input type="text" class="form-control" placeholder="请填写详细地址，不含省份城市" name="return_address" id="address" value="<?= $model->return_address ?>">
            </div>
            <div class="clearfix"></div>
        </div>

        <div class="form-group">
            <label>联&nbsp;系&nbsp;人</label>
            <input type="text" class="form-control" placeholder="请填写退货联系人" id="linkman" name="return_linkman" value="<?= $model->return_linkman ?>" style="width:300px;">
        </div>

        <div class="form-group">
            <label>电&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;话</label>
            <input type="text" class="form-control" placeholder="请填写退货联系人电话" id="phone" name="return_phone" value="<?= $model->return_phone ?>" style="width:300px;">
        </div>

        <div class="form-group">
            <label>采购描述</label>
            <textarea rows="3" class="form-control" name="handler_describe" id="handler_describe" placeholder="请输入处理备注"><?= $model->handler_describe ?></textarea>
        </div>

        <div class="form-group">
            <label></label>
            <button type="button" class="btn btn-success" id="btn-submit">提交</button>
            <button type="button" class="btn btn-info" id="waiting">处理等待中</button>
        </div>

    </div>
</div>

<?php ActiveForm::end(); ?>
<?php
$js=<<<JS
$(function(){
    
    $('#waiting').click(function() {
        $('input[name="handler_type"]').val(15);
        $('#handler-form').submit();
    });

    $("#order_no").change(function(){
        var order_no = $('#order_no').val();    
        $.ajax({
            url: '/exp-ruku/check-purnumber',
            type: 'post',
            data: {'pur_number': $('#order_no').val()},
            dataType: 'json',
            success: function(data) {
                if(data.status==1){
                    layer.tips(data.msg, $('#order_no'), {
                        tips: [1, '#3595CC'],
                        time: 4000
                    });
                    return false;
                }
              
            }
        });
    })
    
    $('#btn-submit').click(function() {
        var obj = $('input[name="handler_type"]');
        var t;
        for(var i=0; i<obj.length; i++) {
            if(obj[i].checked) {
                t = $(obj[i]).val();
            }
        }
        var order_no = $('#order_no').val();
        if($.trim(order_no) == '') {
            layer.tips('请填写采购单号', $('#order_no'), {
                tips: [1, '#3595CC'],
                time: 4000
            });
            return false;
        }
        
        //只有退货的为必填
        if(t==7){
            //退货地址  省份验证
            var province = $("#province :checked").val();
            if($.trim(province) == ''){
                layer.tips('请选择省份', $('#province'), {
                    tips: [1, '#3595CC'],
                    time: 4000
                });
                return false;
            }
            //城市验证
            var city = $("#city :checked").val();
            if($.trim(city) == ''){
                layer.tips('请选择城市', $('#province'), {
                    tips: [1, '#3595CC'],
                    time: 4000
                });
                return false;
            }
            //城市验证
            var address = $("#address").val();
            if($.trim(address) == ''){
                layer.tips('请填写详细地址', $('#address'), {
                    tips: [1, '#3595CC'],
                    time: 4000
                });
                return false;
            }
            //城市联系人
            var linkman = $("#linkman").val();
            if($.trim(linkman) == ''){
                layer.tips('请填写退货联系人', $('#linkman'), {
                    tips: [1, '#3595CC'],
                    time: 4000
                });
                return false;
            }
            //城市联系人
            var phone = $("#phone").val();
            if($.trim(phone) == ''){
                layer.tips('请填写退货联系人电话', $('#phone'), {
                    tips: [1, '#3595CC'],
                    time: 4000
                });
                return false;
            }
            //城市联系人
            var handler_describe = $("#handler_describe").val();
            if($.trim(handler_describe) == ''){
                layer.tips('请输入处理备注', $('#phone'), {
                    tips: [1, '#3595CC'],
                    time: 4000
                });
                return false;
            }
        }    
            
        $('#handler-form').submit();
    });
    
    $('#province').change(function() {
        var id = $(this).val();
        if(id == 0) {
            $('#city').html("<option value='0'>请先选择省份...</option>");
        } else {
            $.ajax({
                url: 'handler',
                data: {pid: id},
                dataType: 'json',
                success: function(data) {
                    var opt = '';
                    $(data).each(function(index, item) {
                        opt += "<option value='"+ item.name +"'>"+ item.name +"</option>";
                    });
                    $('#city').html(opt);
                }
            });
        }
    });

});
JS;
$this->registerJs($js);
?>
