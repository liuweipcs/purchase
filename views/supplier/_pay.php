<?php

use yii\helpers\Html;
use app\services\SupplierServices;
use app\models\SupplierPaymentAccount;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\Stockin */

$model_pay = $model->isNewRecord ? new SupplierPaymentAccount() : empty($model->pay) ? new SupplierPaymentAccount() : $model->pay;


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

//账号信息
if (!empty($auditInfo['supplier_payment_account'])) {
    // foreach ($model_pay as $mk => $mv) {
    //     if (!empty($auditInfo['supplier_payment_account'][$mv->pay_id])) {
    //         foreach ($auditInfo['supplier_payment_account'][$mv->pay_id] as $ak => $av) {
    //              $mv->$ak = $av;
    //              echo '<style type="text/css" media="screen">';
    //                  echo '.field-supplierpaymentaccount-'.$ak.' input{
    //                     color:red
    //                  }';
    //              echo '</style>';
    //         }
    //     }
    // }
    $model_pay_insert = [];
    foreach ($auditInfo['supplier_payment_account'] as $k => $v) {
        $payment_update_flag = false;
        foreach ($v as $sk => $sv) {
            foreach ($model_pay as $pk => $pv) {

                if (!empty($pv->pay_id) && $pv->pay_id == $k) {
                    $pv->$sk = $sv;
                    $payment_update_flag=true;
                }
            }
        }

        if ($payment_update_flag==false) {
            # 新增
            $model_pay_insert[] = $v;
        }
    }
}
?>

<div class="stockin-update">
    <div>
        <div class="col-md-5">
            <?=$form->field($model, "supplier_settlement")->dropDownList(SupplierServices::getSettlementMethod(), ["prompt" => "请选择结算方式",'required'=>true]);?>
        </div>
        <div class="col-md-5">
            <?=$form->field($model, "payment_method")->dropDownList(SupplierServices::getDefaultPaymentMethod(), ["prompt" => "请选择支付方式",'required'=>true]);?>
        </div>
    </div>
    <table class="table table-hover ">
        <div class="col-md-10"><?=Html::button('添加帐号', ['class' => 'btn btn-success add_user col-md-1'])?>

        </div>
        <tr>
            <th>编号</th>
            <th>支付平台</th>
            <th>主行</th>
            <th>支行</th>
            <th colspan="2" style="text-align: center">支行所在区域</th>
            <th>账户类型</th>
            <th>开户名</th>
            <th>账号</th>
            <th>到账通知手机号</th>
            <th>证件号</th>
            <th>操作</th>
        </tr>
        <tbody class="pay">
        <?php if($model->isNewRecord || empty($model->pay)){?>
            <tr class="pay_list ">
                <td><?=$form->field($model_pay, "pay_id[]")->input('text',['value'=>'','readonly'=>true,'class'=>'form-control pay_id'])->label(false);?></td>
                <td><?=$form->field($model_pay, "payment_platform[]")->dropDownList(SupplierServices::getPaymentPlatform(),['class' => 'form-control payment_platform pay_info check_pay','prompt'=>'请选择'])->label(false);?></td>
                <td><?=$form->field($model_pay, "payment_platform_bank[]")->dropDownList(\app\models\UfxFuiou::getMasterBankInfo(),['class' => 'form-control pay_info pay_bank check_pay','prompt'=>'请选择'])->label(false)?></td>
                <td>
                    <?=$form->field($model_pay, 'payment_platform_branch[]')->textInput(['id'=>'pay_branch_1','class'=>'pay_branch form-control pay_info',"maxlength" => true,"placeholder"=>"请填写支行"])->label(false);?>
                    <span style="color: red;font-size: 11px;">请填写主行 支行名称</span>
                </td>
                <td><?=$form->field($model_pay, "prov_code[]")->dropDownList(\app\models\UfxFuiou::getProvInfo(),['class' => 'form-control prov pay_info','prompt'=>'请选择省'])->label(false);?></td>
                <td><?=$form->field($model_pay, "city_code[]")->dropDownList([],['class' => 'form-control city pay_info','prompt'=>'请选择市'])->label(false);?></td>
                <td><?=$form->field($model_pay, "account_type[]")->dropDownList(['1'=>'对公','2'=>'对私'],['class' => 'form-control account_type pay_info','prompt'=>'请选择'])->label(false);?></td>
                <td><?=$form->field($model_pay, "account_name[]")->textInput(['class'=>'pay_info form-control account_name',"maxlength" => true,"placeholder"=>""])->label(false);?></td>
                <td><?=$form->field($model_pay, "account[]")->textInput(['class'=>'pay_info form-control account',"maxlength" => true,"placeholder"=>""])->label(false);?></td>
                <td><?=$form->field($model_pay, "phone_number[]")->textInput(['class'=>'pay_info form-control',"maxlength" => true,"placeholder"=>"手机号码"])->label(false);?></td>
                <td>
                    <?=$form->field($model_pay, "id_number[]")->textInput(['class'=>'pay_info form-control','title'=>'对私账户为收款人身份证号,对公账户为收款公司组织机构代码',"maxlength" => true,"placeholder"=>"对私账户为收款人身份证号,对公账户为收款公司组织机构代码"])->label(false); ?>
                    <div class="pay_message" style="color: red;padding-left: 5px;font-size: 10px">对私账户为收款人身份证号,对公账户为收款公司组织机构代码</div>
                </td>
                <td><?=Html::button('删除', ['class' => 'btn btn-danger form-control']);?></td>
            </tr>
        <?php }else{?>
            <?php foreach ($model->pay as $pay){?>
                <tr class="pay_list ">
                    <td><?=$form->field($pay, "pay_id[]")->input('text',['value'=>$pay->pay_id,'readonly'=>true,'class'=>'form-control pay_id'])->label(false);?></td>
                    <td><?=$form->field($pay, "payment_platform[]")->dropDownList(SupplierServices::getPaymentPlatform(),['class' => 'form-control payment_platform pay_info check_pay','prompt'=>'请选择','value'=>$pay->payment_platform])->label(false);?></td>
                    <td><?=$form->field($pay, "payment_platform_bank[]")->dropDownList(\app\models\UfxFuiou::getMasterBankInfo(),['class' => 'form-control pay_info pay_bank check_pay','prompt'=>'请选择','value'=>$pay->payment_platform_bank])->label(false)?></td>
                    <td>
                        <?=$form->field($pay, 'payment_platform_branch[]')->textInput(['id'=>'pay_branch_1','class'=>'pay_branch form-control pay_info',"maxlength" => true,"placeholder"=>"请填写支行",'value'=>$pay->payment_platform_branch])->label(false);?>
                        <span style="color: red;font-size: 11px;">请填写主行 支行名称</span>
                    </td>
                    <td><?=$form->field($pay, "prov_code[]")->dropDownList(\app\models\UfxFuiou::getProvInfo(),['class' => 'form-control prov pay_info','prompt'=>'请选择省','value'=>$pay->prov_code])->label(false);?></td>
                    <td><?=$form->field($pay, "city_code[]")->dropDownList(\app\models\UfxFuiou::getCityInfo(null,$pay->prov_code),['class' => 'form-control city pay_info','prompt'=>'请选择市','value'=>$pay->city_code])->label(false);?></td>
                    <td><?=$form->field($pay, "account_type[]")->dropDownList(['1'=>'对公','2'=>'对私'],['class' => 'form-control account_type pay_info','prompt'=>'请选择','value'=>$pay->account_type])->label(false);?></td>
                    <td><?=$form->field($pay, "account_name[]")->textInput(['class'=>'pay_info form-control account_name',"maxlength" => true,"placeholder"=>"",'value'=>$pay->account_name])->label(false);?></td>
                    <td><?=$form->field($pay, "account[]")->textInput(['class'=>'pay_info form-control account show_all',"maxlength" => true,"placeholder"=>"",'value'=>$pay->account])->label(false);?></td>
                    <td><?=$form->field($pay, "phone_number[]")->textInput(['class'=>'pay_info form-control',"maxlength" => true,"placeholder"=>"手机号码",'value'=>$pay->payment_platform==5&&$pay->account_type==1 ? '' :$pay->phone_number,'readonly'=>$pay->payment_platform==5&&$pay->account_type==1 ? true : false])->label(false);?></td>
                    <td>
                        <?=$form->field($pay, "id_number[]")->textInput(['class'=>'pay_info form-control show_all','title'=>'对私账户为收款人身份证号,对公账户为收款公司组织机构代码',"maxlength" => true,"placeholder"=>"对私账户为收款人身份证号,对公账户为收款公司组织机构代码",'value'=>$pay->payment_platform==5&&$pay->account_type==1 ? '' :$pay->id_number,'readonly'=>$pay->payment_platform==5&&$pay->account_type==1 ? true : false])->label(false);?>
                        <div class="pay_message" style="color: red;padding-left: 5px;font-size: 10px">
                            <?php
                                if($pay->payment_platform==6&&$pay->account_type==1){
                                    echo '请填写组织机构代码';
                                }
                                if($pay->account_type==2){
                                    echo '请填写开户人身份证号码';
                                }
                                if($pay->payment_platform==5&&$pay->account_type==1){
                                    echo '不需要填写';
                                }
                            ?>
                        </div>
                    </td>
                    <td><?=Html::button('删除', ['class' => 'btn btn-danger form-control']);?></td>
                </tr>
            <?php }?>
        <?php }?>
        <?php 
        if(!empty($model_pay_insert)){
            foreach ($model_pay_insert as $key => $value) {
        ?>

            <tr class="pay_list ">
                <td></td>
                <td><div class="form-control" readonly="readonly" style="color:red"><?=!empty($value['payment_platform'])?SupplierServices::getPaymentPlatform($value['payment_platform']):'';?></div></td>
                <td><div class="form-control" readonly="readonly" style="color:red"><?=!empty($value['payment_platform_bank'])?\app\models\UfxFuiou::getMasterBankInfo($value['payment_platform_bank']):'';?></div></td>
                <td><div class="form-control" readonly="readonly" style="color:red"><?=!empty($value['payment_platform_branch'])?$value['payment_platform_branch']:'';?></div></td>
                <td><div class="form-control" readonly="readonly" style="color:red"><?=!empty($value["prov_code"])?\app\models\UfxFuiou::getProvInfo($value["prov_code"]):'';?></div></td>
                <td><div class="form-control" readonly="readonly" style="color:red"><?=!empty($value["city_code"])?\app\models\UfxFuiou::getCityInfo($value["city_code"],$value["prov_code"]):''?></div></td>
                <td><div class="form-control" readonly="readonly" style="color:red"><?=(!empty($value["account_type"]) && $value["account_type"]==1)?'对公':'对私'?></div></td>
                <td><div class="form-control" readonly="readonly" style="color:red"><?=!empty($value["account_name"])? $value["account_name"] : ''; ?></div></td>
                <td><div class="form-control" readonly="readonly" style="color:red"><?=!empty($value["account"])?$value["account"]:''; ?></div></td>
                <td><div class="form-control" readonly="readonly" style="color:red"><?=!empty($value["phone_number"])?$value["phone_number"]:''; ?></div></td>
                <td><div class="form-control" readonly="readonly" style="color:red"><?=!empty($value["id_number"])?$value["id_number"]:''; ?></div></td>
                <td></td>
            </tr>
        <?php }} ?>
        </tbody>
    </table>
</div>
    <div>
        <?= \yii\helpers\Html::button('上一步',['class'=>'btn btn-info','id'=>'supplier_pay_info_up'])?>
        <?= \yii\helpers\Html::button('下一步',['class'=>'btn btn-warning','id'=>'supplier_pay_info_next'])?>

        <?php $model->isNewRecord ? \yii\helpers\Html::button('上一步',['class'=>'btn btn-info','id'=>'supplier_pay_info_up']) : ''?>
        <?php $model->isNewRecord ? \yii\helpers\Html::button('下一步',['class'=>'btn btn-warning','id'=>'supplier_pay_info_next']) : ''?>
    </div>
<?php

$cityUrl = Url::toRoute('ufx-fuiou/get-city');
$bankCityUrl = Url::toRoute('ufx-fuiou/get-branch-bank-city');

$js = <<<JS
$(function () {
        //input自动完成
        layui.config({
          base: '/js/layExtend/' 
        });
        var complete = function(elem,url) {
          layui.use(['autocomplete'], function(){
            var autocomplete = layui.autocomplete;
            autocomplete.render({
                elem: elem,
                url:url,
                cache: false,
                template_val: '{{d.text}}',
                template_txt: '{{d.text}}',
                onselect: function (resp) {
                    var loading = layer.load(6 , {shade : [0.5 , '#BFE0FA']});
                    $.ajax({
                    url:'{$bankCityUrl}',
                    data:{unionBankCode:resp.bank_union_code},
                    dataType:'json',
                    success:function(data){
                        console.log(data);
                        elem.closest('tr').find('.prov').val(data.provNo);
                        $.get("{$cityUrl}",{prov:data.provNo,city:data.cityNo},function(data){
                            elem.closest('tr').find('.city').html(data);
                        });
                        layer.close(loading);
                    }
                    });
                }
            });
            });
        }
        complete($('#pay_branch_1'),'/ufx-fuiou/branch-bank');

    $('.pay_list').each(function() {
        $(this).find('.pay_info').each(function(i) {
            if (i==0 || i==1) {

            } else {
                $(this).after('<span style="color:red;position: absolute;margin-left: -18px; margin-top: -41px; font-size: 22px;">*</span>');
            }
        });
    });

    $('#supplier_pay_info_up').on('click',function() {
        $('#base_info_li').tab('show');
        $('#base_info_content').addClass('active in');
        $('#pay_info_content').removeClass('active in');
    });
    var clearPhoneNumber = function(e) {
         if(e.closest('.pay_list').find('[name="SupplierPaymentAccount[phone_number][]"]').val()==''){
             e.closest('.pay_list').find('[name="SupplierPaymentAccount[phone_number][]"]').val('');
             e.closest('.pay_list').find('[name="SupplierPaymentAccount[phone_number][]"]').attr('readonly',false);
         }
         if(e.closest('.pay_list').find('[name="SupplierPaymentAccount[id_number][]"]').val()==''){
            e.closest('.pay_list').find('[name="SupplierPaymentAccount[id_number][]"]').val('');
            e.closest('.pay_list').find('[name="SupplierPaymentAccount[id_number][]"]').attr('readonly',false);  
         }
    }
    var check_pay_info = function(e) {
         var payment_platform = e.closest('.pay_list').find('.payment_platform').val();
         var account_type = e.closest('.pay_list').find('.account_type').val();
         var accountName =  e.closest('.pay_list').find('[name="SupplierPaymentAccount[account_name][]"]').val();
         var idNumber =  e.closest('.pay_list').find('[name="SupplierPaymentAccount[id_number][]"]').val();
         var account = e.closest('.pay_list').find('[name="SupplierPaymentAccount[account][]"]').val();
         if(idNumber!=''&&checkDataHaveKg(idNumber)){
               layer.msg('证件号不要使用空格!');
               return false;
         }
         if(accountName!=''&&checkDataHaveKg(accountName)){
               layer.msg('开户名不要使用空格!');
               return false;
         }
         if(phoneNumber!=''&&checkDataHaveKg(phoneNumber)){
               layer.msg('到账通知手机号不要使用空格!');
               return false;
         }
         
         if(account!=''&&checkDataHaveKg(account)){
               layer.msg('银行卡账号不要使用空格!');
               return false;
         }
         if(payment_platform!=''&&account_type!=''){
             //支付平台 6 富友转账 5 网银
             //账户类型 1对公   2 对私
             if(payment_platform==6&&account_type==1){
                 e.closest('.pay_list').find('.pay_message').html('请填写组织机构代码');
                 clearPhoneNumber(e);
                 var phoneNumber = e.closest('.pay_list').find('[name="SupplierPaymentAccount[phone_number][]"]').val();
                 if(!checkPhoneNumber(phoneNumber)&&phoneNumber!=''){
                     layer.msg('手机号不符合规范');
                     return false;
                 }
                 // if(  !checkChinaString(e.closest('.pay_list').find('[name="SupplierPaymentAccount[id_number][]"]'),'组织机构代码不能包含中文')){
                 if( funcChina(e.closest('.pay_list').find('[name="SupplierPaymentAccount[id_number][]"]')) == true ){
                    layer.msg('组织机构代码不能包含中文');
                    return false;
                 }
             }
             if(payment_platform==6&&account_type==2){
                 e.closest('.pay_list').find('.pay_message').html('请填写开户人身份证号码');
                 clearPhoneNumber(e);
                 var phoneNumber = e.closest('.pay_list').find('[name="SupplierPaymentAccount[phone_number][]"]').val();
                 if(!checkPhoneNumber(phoneNumber)&&phoneNumber!=''){
                     layer.msg('手机号不符合规范');
                     return false;
                 }
                 if( funcChina(e.closest('.pay_list').find('[name="SupplierPaymentAccount[id_number][]"]'))  == true ){
                    layer.msg('身份证号不能包含中文');
                    return false;
                 }
             }

             if(payment_platform==5&&account_type==2){
                e.closest('.pay_list').find('.pay_message').html('请填写开户人身份证号码');
                clearPhoneNumber(e);
                var phoneNumber = e.closest('.pay_list').find('[name="SupplierPaymentAccount[phone_number][]"]').val();
                if(!checkPhoneNumber(phoneNumber)&&phoneNumber!=''){
                 layer.msg('手机号不符合规范');
                 return false;
                }
                if(funcChina(e.closest('.pay_list').find('[name="SupplierPaymentAccount[id_number][]"]')) == true ){
                    layer.msg('身份证号不能包含中文');
                    return false;
                }
             }
             if(payment_platform==5&&account_type==1){
                 e.closest('.pay_list').find('.pay_message').html('不需要填写');
                 e.closest('.pay_list').find('[name="SupplierPaymentAccount[phone_number][]"]').val('');
                 e.closest('.pay_list').find('[name="SupplierPaymentAccount[phone_number][]"]').attr('readonly',true);
                 e.closest('.pay_list').find('[name="SupplierPaymentAccount[id_number][]"]').val('');
                 e.closest('.pay_list').find('[name="SupplierPaymentAccount[id_number][]"]').attr('readonly',true);
             }
         }else {
            clearPhoneNumber(e);
             e.closest('.pay_list').find('.pay_message').html('对私账户为收款人身份证号,对公账户为收款公司组织机构代码');
         }
         return true;
    }
    $(document).on('change','.payment_platform',function() {
        check_pay_info($(this));
    });
    $(document).on('change','.account_type',function() {
        check_pay_info($(this));
    });
    $(document).on('change','#supplier-payment_method',function() {
        if($('#supplier-payment_method').val() == '2'){
            $('.header_hint_message_show').hide();
            $('.duigong_img_show').hide();
            $('.duigsi_img_show').hide();
            $('.no_gong_no_si_img_show').show();
        }else{
            $('.no_gong_no_si_img_show').hide();
        }
    });
    
    $('#supplier_pay_info_next').on('click',function() {        
        $('.delegation').hide();
        $('.ufx_information').hide();
        $('.id_number').hide();
        $('.taxpayer_letter').hide();
        $('.invoice_information').hide();
        if( isnull($('#supplier-supplier_settlement').val()) ){
            layer.msg('供应商结算方式不能为空');
            return false;
        }
        if( isnull($('#supplier-payment_method').val()) ){
            layer.msg('支付方式不能为空');
            return false;
        }
        var pay_method = $('#supplier-payment_method').val();
        var payMethodArray = ['3','5'];
        var pay_info_error = 0;
        var is_gong_si = [];
        if($.inArray(pay_method,payMethodArray)!==-1){
            $('.pay_list').each(function(i) {
                var account_type = $(this).find('.account_type').val();
                var pay_platform=$(this).find('.payment_platform').val();
                is_gong_si[i] = account_type;
                $(this).find('.pay_info').each(function() {
                    // 支付方式：银行卡转账  账户类型：对公  支付平台：网银
                   if($.trim($(this).val())=='' && pay_method == '3' && account_type== '1' && pay_platform == '5'){
                        if( $.trim($(this).attr('id')) == 'supplierpaymentaccount-phone_number' ||
                            $.trim($(this).attr('id')) == 'supplierpaymentaccount-id_number'){
                            return true;
                         }else{
                            layer.msg('支付方式为银行卡转账时，支付信息不能为空');
                            pay_info_error++;
                            return false;
                         }
                   }
                   if(pay_method == '3' && account_type== ''){
                        layer.msg('支付方式为银行卡转账时，支付信息不能为空');
                        pay_info_error++;
                        return false;
                   }
                    if($.trim($(this).val())==''){
                        layer.msg('支付方式为银行卡转账时，支付信息不能为空');
                        pay_info_error++;
                        return false;
                    }
                    if(!check_pay_info($(this))){
                        pay_info_error++;
                        return false;
                    }
                });
            });
        } else {
            $('.pay_list').each(function(i) {
                var account_type = $(this).find('.account_type').val();
                is_gong_si[i] = account_type;
            });
        }

        //组织机构代码不能包含中文
        var is_number_flag = is_account_flag = false;
        $('.pay_list').each(function(i) {
            var account_type = $(this).find('.account_type').val();
            var account = $(this).find('.account').val();
            var account_name = $(this).find('.account_name').val();
            var payment_platform = $(this).find('.payment_platform').val();
            var id_number = $(this).find('[name="SupplierPaymentAccount[id_number][]"]').val();

            if (account!='' || account_name!='') {
                if( isTeShu(account) || isTeShu(account_name) ){
                    is_account_flag = true;
                }
            }

            // if(account_type==2 || (payment_platform==6 && account_type==1) ){
            //     if( funcChina(id_number) == true ){
            //         is_number_flag = true;
            //     }
            // }

            if (id_number !='') {
                if( funcChina(id_number) == true || isnull(id_number)==true || isTeShu(id_number) ){
                    is_number_flag = true;
                }
            }
        });
        if (is_number_flag== true) {
            layer.msg('身份证号码输入不能有中文/不能有空格/不能有特殊字符');
            return false;
        }
        if (is_account_flag==true) {
            layer.msg('开户名或账号不能有特殊字符');
            return false;
        }

        //账户类型
        $('#is_gong_si').val(is_gong_si);

        var payplatcount=0;
        $("[name='SupplierPaymentAccount[payment_platform][]']").each(function() {
          if($(this).val()==6){
              payplatcount++;
          }
        });
        if(payplatcount>1){
            layer.msg('富友平台银行只能维护一张');
            return false;
        }
        var accountTypePublic=0
        var accountTypePrivate=0
        var pay_type= new Array();
        $("[name='SupplierPaymentAccount[account_type][]']").each(function() {
          if($(this).val()==1){
              accountTypePublic++;
              if($.inArray('对公',pay_type)==-1){
                pay_type.push('对公');
              }
          }
          if($(this).val()==2){
              accountTypePrivate++;
              if($.inArray('对私',pay_type)==-1){
                pay_type.push('对私');
              }
          }
        });
        if(accountTypePrivate>1||accountTypePublic>1){
            layer.msg('对公对私卡暂时只支持各维护一张');
            return false;
        }
        if(pay_info_error>0){
            return false;
        }

        //上一步：基本信息
        // $('#base_info_li').tab('show');
        // $('#base_info_content').addClass('active in');
        // $('#pay_info_content').removeClass('active in');
        //下一步：财务结算
        // $('#pay_info_li').tab('show');
        // $('#pay_info_content').addClass('active in');
        // $('#base_info_content').removeClass('active in');

        is_gong_si = $('#is_gong_si').val();
        var audit_is_gong_si = $('#audit_is_gong_si').val();
        var is_audit = $('#is_audit').val();
        $(".duigong_img_show").hide();
        $(".duigsi_img_show").hide();
        $(".no_gong_no_si_img_show").hide();
        $(".header_hint_message_show").hide();
        
        if($('#supplier-payment_method').val() == '2'){
            $(".no_gong_no_si_img_show").show();
        }else{
            $(".header_hint_message_show").show();
            if (is_audit==true) {
                //审核
                if (audit_is_gong_si.indexOf("1") != -1) {
                    //对公
                    $(".duigong_img_show").show();
                }
                if (audit_is_gong_si.indexOf("2") != -1) {
                    //对私
                    $(".duigsi_img_show").show();
                }
            } else {
                //创建和更新
                if (is_gong_si.indexOf("1") != -1) {
                    //对公
                    $(".duigong_img_show").show();
                }
                if (is_gong_si.indexOf("2") != -1) {
                    //对私
                    $(".duigsi_img_show").show();
                }
            }
        }

        //下一步：联系方式
        $('#contact_info_li').tab('show');
        $('#contact_info_content').addClass('active in');
        $('#pay_info_content').removeClass('active in');
    });

    function isnull(val) {
        if (val==null) {
            return false;
        }
        var str = val.replace(/(^\s*)|(\s*$)/g, '');//去除空格
        if (str == '' || str == undefined || str == null) {
            return true;
            console.log('空')
        } else {
            return false;
            console.log('非空');
        }
    }

    function funcChina(str){
        if (escape(str).indexOf( "%u" )<0) {
          // alert( "没有包含中文" );
          return false;
        } else {
          // alert( "包含中文" );
          return true;
        }
    }

    function isTeShu(str)
    {
        var re =/[`~!@#$%^&*_+<>{}\/'[\]]/im;
        if (re.test(str)){
            // alert('存在特殊字符');
            return true;
        } else {
            return false;
        }
    }
});
JS;
$this->registerJs($js);
?>