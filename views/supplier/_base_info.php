<?php
use app\services\SupplierServices;
use app\services\BaseServices;
use kartik\date\DatePicker;
use app\models\SupplierProductLine;
use yii\helpers\Url;

$model_line = $model->isNewRecord ? new SupplierProductLine() : empty($model->supplierLine) ? new SupplierProductLine() : $model->supplierLine;

//获取更新的内容
if (!empty($auditInfo['supplier'])) {
    foreach ($auditInfo['supplier'] as $ak => $av) {
         $model->$ak = $av;
         echo '<style type="text/css" media="screen">';
             echo '.field-supplier-'.$ak.' label{
                color:red
             }';
         echo '</style>';
    }
}

if (!empty($auditInfo['supplier_product_line'])) {
    foreach ($model_line as $key => $value) {
        if (isset($auditInfo['supplier_product_line'][$key]) && $auditInfo['supplier_product_line'][$key]!=$value) {
            $model_line->$key = $auditInfo['supplier_product_line'][$key];
            echo '<style type="text/css" media="screen">';
            echo 'label[for=supplierproductline-'. $key .']{color:red}';
            echo '</style>';
        }
    }
}

?>
<input type="hidden" name="Supplier[supplier_code]" value="<?=$model->supplier_code?>">

<div class="row">
    <div class="col-md-4"><?= $form->field($model, 'credit_code')->textInput(['maxlength' => true, 'placeholder' => '社会信用代码','required'=>true]); ?></div>
    <div class="col-md-4"><?= $form->field($model, 'supplier_name')->textInput(['maxlength' => true,'placeholder'=>'填写统一社会信用代码后自动带出','readonly'=>true,'required'=>true]) ?></div>
    <div class="col-md-3">
        <?php
        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->id);
        if (!empty($model->id)) {
            echo $form->field($model, 'supplier_level')->dropDownList(SupplierServices::getSupplierLevel(), ['prompt' => '请选择供应商等级','required'=>true]);
        } elseif( (in_array('采购组-海外', array_keys($roles))||in_array('FBA采购经理组', array_keys($roles)) || in_array('FBA采购组', array_keys($roles)) || in_array('采购组-国内', array_keys($roles))) ) {
            echo $form->field($model, 'supplier_level')->dropDownList(['0'=>'', '4' =>'D','6'=>'L'], ['prompt' => '请选择供应商等级','required'=>true]);
        } else {
            echo $form->field($model, 'supplier_level')->dropDownList(['0'=>'','4' =>'D'], ['prompt' => '请选择供应商等级','required'=>true]);
        }
        ?>
    </div>
    <div class="col-md-4"><?= $form->field($model, 'supplier_type')->dropDownList(SupplierServices::getSupplierType(), ['prompt' => '请选择供应商类型','required'=>true, 'value'=>$model->supplier_type]) ?></div>
    <div class="col-md-4"> <label>首次合作时间</label>
        <?=
        DatePicker::widget([
            'name' => 'Supplier[first_cooperation_time]',
            'options' => ['placeholder' => ''],
            'value' => date('Y-m-d',time()),
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd',
                'todayHighlight' => true
            ]
        ])
        ?></div>
    <div class="col-md-3"><?= $form->field($model, 'store_link')->textInput(['maxlength' => true,'placeholder'=>'','required'=>true]) ?></div>


    <div class="col-md-4"><?= $form->field($model, 'province')->dropDownList(BaseServices::getCityList(1),[
            'prompt'=>'--请选择省--',

            'onchange'=>'
            $(".form-group.field-member-area").hide();
            $("#supplier-area").val("");
            $("#supplier-city").val("");
            var loading = layer.load(6 , {shade : [0.5 , "#BFE0FA"]});
            $.post("'.yii::$app->urlManager->createUrl('supplier/sites').'?typeid=1&pid="+$(this).val(),function(data){
                $("select#supplier-city").html(data);
                layer.close(loading);
            });','required'=>true
        ]) ?></div>
    <div class="col-md-4"><?= $form->field($model, 'city')->dropDownList(BaseServices::getCityList($model->province),
            [
                'prompt'=>'--请选择市--',
                'onchange'=>'
            $(".form-group.field-member-area").show();
            $("#supplier-area").val("");
            var loading = layer.load(6 , {shade : [0.5 , "#BFE0FA"]});
            $.post("'.yii::$app->urlManager->createUrl('supplier/sites').'?typeid=2&pid="+$(this).val(),function(data){
                $("select#supplier-area").html(data);
                layer.close(loading);
            });','required'=>true
            ]) ?></div>
    <div class="col-md-3"><?= $form->field($model, 'area')->dropDownList(BaseServices::getCityList($model->city),['prompt'=>'--请选择区--',]) ?></div>
    <div class="col-md-4">
        <?= $form->field($model_line, "first_product_line")->dropDownList(BaseServices::getProductLine(),['class' => 'form-control first ','prompt'=>'选择一级产品线',
            'onchange'=>'               
                var loading = layer.load(6 , {shade : [0.5 , "#BFE0FA"]});
                $.post("'.yii::$app->urlManager->createUrl('supplier/line').'?pid="+$(this).val(),function(data){
                $("#supplierproductline-second_product_line").html(data);
                $("#supplierproductline-second_product_line").trigger("change");
                layer.close(loading);
                });','required'=>true
        ])->label('一级产品线'); ?>
    </div>
    <div class="col-md-4">
        <?=$form->field($model_line, "second_product_line")->dropDownList(BaseServices::getProductLineList($model_line->first_product_line),['class' => 'form-control second ','prompt'=>'选择二级产品线',
            'onchange'=>'
        var loading = layer.load(6 , {shade : [0.5 , "#BFE0FA"]});
        $.post("'.yii::$app->urlManager->createUrl('supplier/line').'?pid="+$(this).val(),function(data){
        $("#supplierproductline-third_product_line").html(data);
        layer.close(loading);
        });',])->label('二级产品线');?>
    </div>
    <div class="col-md-3">
        <?=$form->field($model_line, "third_product_line")->dropDownList(BaseServices::getProductLineList($model_line->second_product_line),['class' => 'form-control third','prompt'=>'选择三级产品线'])->label('三级产品线')?>
    </div>
    <div class="col-md-4"><?= $form->field($model, 'supplier_address')->textarea(['maxlength' => true,'required'=>true])->label('公司地址') ?></div>
    <div class="col-md-4"><?= $form->field($model, 'invoice')->dropDownList([1=>'否',2=>'增值税发票',3=>'普票'],['required'=>true,'prompt'=>'请选择']) ?></div>
    <div class="col-md-3"><?= $form->field($model, 'business_scope')->textarea(['maxlength' => true,'required'=>true]) ?></div>
    <div class="col-md-12">
        <?=$form->field($model,'contract_notice')->textarea(['col'=>10,'rows'=>5,'max'=>500])->label('介绍')?>
    </div>
</div>
<div>
    <?= \yii\helpers\Html::button('下一步',['class'=>'btn btn-warning','id'=>'supplier_base_info_next']) ?>
    <?php $model->isNewRecord ?  \yii\helpers\Html::button('下一步',['class'=>'btn btn-warning','id'=>'supplier_base_info_next']) : ''?>
</div>

<?php
$buyerUrl = Url::toRoute('buyer');
$eyeCheckUrl = Url::toRoute('supplier/get-supplier-info');

$js = <<<JS
    $(function () {
        function getBuyer(){
            var id = $('.line').find('#supplierproductline-first_product_line').val();
            $.ajax({
                url:'{$buyerUrl}',
                data:{id:id},
                dataType:'json',
                success:function(data){
                    $('#FBA').val(data.buyer).trigger("change");
                }
            });
        };
        
        $('.FBABuyer').click(function(){
            if($(this).is(':checked')){
                getBuyer();
            }
        });

        $('[name="SupplierProductLine[first_product_line][]"]:first').click(function(){
            getBuyer();
        });
        $(document).on('blur','#supplier-credit_code',function() {
            var credit_code = $(this).val();
            if(credit_code==''){
                layer.msg('统一社会信用代码不能为空');
                return false;
            }
            // if(!checkSocialCreditCode(credit_code)){
            //     layer.msg('统一社会信用代码无法通过验证');
            //     return false;
            // }
            var loading = layer.load(6 , {shade : [0.5 , '#BFE0FA']});
          $.get("{$eyeCheckUrl}",{credit_code:credit_code,token:'ypHa2ONeXRP0YtjF'},function(data){
            layer.close(loading);
            var response = JSON.parse(data);
            if(response.status=='success'){
                $('#supplier-supplier_name').val(response.data.name);
                $('#supplier-business_scope').val(response.data.business_scope);
                $('#supplier-supplier_address').val(response.data.reg_location);
                $('[name="SupplierContactInformation[corporate][]"]').val(response.data.legal_person_name);
            }else {
                layer.msg(response.message);
                return false;
            }
            });
        });

        $('#supplier_base_info_next').on('click',function() {
            if( isnull($('#supplier-credit_code').val()) ){
                layer.msg('统一社会信用代码不能为空');
                return false;
            }
            if(checkDataHaveKg($('#supplier-credit_code').val())){
                layer.msg('统一社会信用代码不能包含空格');
                return false;
            }

            if( isnull($('#supplier-supplier_name').val()) ){
                layer.msg('供应商名称不能为空');
                return false;
            }
            if( isnull($('#supplier-supplier_level').val()) ){
                layer.msg('供应商等级不能为空');
                return false;
            }
            if( isnull($('#supplier-supplier_type').val()) ){
                layer.msg('供应商类型不能为空');
                return false;
            }
            if( isnull($('#supplier-store_link').val()) ){
                layer.msg('店铺链接不能为空');
                return false;
            }
            if( isnull($('#supplier-province').val()) ){
                layer.msg('供应商所在地省不能为空');
                return false;
            }
            if( isnull($('#supplier-city').val()) ){
                layer.msg('供应商所在地市不能为空');
                return false;
            }
            if( isnull($('#supplierproductline-first_product_line').val()) ){
                layer.msg('供应商一级产品线不能为空');
                return false;
            }
            if( isnull($('#supplier-supplier_address').val()) ){
                layer.msg('供应商详细地址不能为空');
                return false;
            }
            if( isnull($('#supplier-invoice').val()) ){
                layer.msg('供应商是否开票不能为空');
                return false;
            }
            if( isnull($('#supplier-business_scope').val()) ){
                layer.msg('供应商经营范围不能为空');
                return false;
            }
            if( isnull($('#supplier-contract_notice').val()) ){
                layer.msg('介绍不能为空');
                return false;
            }
            if($('#supplier-contract_notice').val().length>500){
                layer.msg('介绍过长,最多五百个字符');
            }
            $('#pay_info_li').tab('show');
            $('#pay_info_content').addClass('active in');
            $('#base_info_content').removeClass('active in');
        });

        function isnull(val) {
            var str = val.replace(/(^\s*)|(\s*$)/g, '');//去除空格;
            if (str == '' || str == undefined || str == null) {
                return true;
                console.log('空')
            } else {
                return false;
                console.log('非空');
            }
        }
    });
    
  
JS;
$this->registerJs($js);
?>