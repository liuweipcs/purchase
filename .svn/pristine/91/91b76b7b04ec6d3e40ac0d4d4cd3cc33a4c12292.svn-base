<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\tabs\TabsX;
use kartik\file\FileInput;
use yii\helpers\Url;
use app\services\SupplierServices;
use app\services\BaseServices;
use kartik\select2\Select2;
use yii\web\JsExpression;
use kartik\date\DatePicker;
/* @var $this yii\web\View */
/* @var $model app\models\Supplier */
/* @var $form yii\widgets\ActiveForm */
?>

<?php
$url= \yii\helpers\Url::to(['/ufx-fuiou/branch-bank',['masterBankCode'=>null]]);
$form = ActiveForm::begin(['id'=>'supplier-create','enableClientValidation'=>false,"options" => ["enctype" => "multipart/form-data"]]); ?>
<h3 class="fa-hourglass-3">基本信息</h3>
<div class="row">
    <div class="col-md-2"><?= $form->field($model, 'credit_code')->textInput(['maxlength' => true, 'placeholder' => '社会信用代码','required'=>true]); ?></div>
    <div class="col-md-2"><?= $form->field($model, 'supplier_name')->textInput(['maxlength' => true,'placeholder'=>'填写统一社会信用代码后自动带出','readonly'=>true]) ?></div>
    <div class="col-md-2">
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
    <div class="col-md-2"><?= $form->field($model, 'supplier_type')->dropDownList(SupplierServices::getSupplierType(), ['prompt' => '请选择供应商类型','required'=>true]) ?></div>
    <div class="col-md-2"> <label>首次合作时间</label>
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
    <div class="col-md-2"><?= $form->field($model, 'store_link')->textInput(['maxlength' => true,'placeholder'=>'','required'=>true]) ?></div>


    <div class="col-md-2"><?= $form->field($model, 'province')->dropDownList(BaseServices::getCityList(1),[
            'prompt'=>'--请选择省--',

            'onchange'=>'
            $(".form-group.field-member-area").hide();
            $.post("'.yii::$app->urlManager->createUrl('supplier/sites').'?typeid=1&pid="+$(this).val(),function(data){
                $("select#supplier-city").html(data);
            });','required'=>true
        ]) ?></div>
    <div class="col-md-2"><?= $form->field($model, 'city')->dropDownList(BaseServices::getCityList($model->province),
            [
                'prompt'=>'--请选择市--',
                'onchange'=>'
            $(".form-group.field-member-area").show();
            $.post("'.yii::$app->urlManager->createUrl('supplier/sites').'?typeid=2&pid="+$(this).val(),function(data){
                $("select#supplier-area").html(data);
            });','required'=>true
            ]) ?></div>
    <div class="col-md-2"><?= $form->field($model, 'area')->dropDownList(BaseServices::getCityList($model->city),['prompt'=>'--请选择区--',]) ?></div>
    <div class="col-md-2"><?= $form->field($model, 'supplier_address')->textarea(['maxlength' => true,'required'=>true])->label('公司地址') ?></div>
    <div class="col-md-1"><?= $form->field($model, 'invoice')->dropDownList([0=>"",1=>'否',2=>'增值税发票',3=>'普票'],['required'=>true]) ?></div>
    <div class="col-md-3"><?= $form->field($model, 'business_scope')->textarea(['maxlength' => true,'required'=>true]) ?></div>

</div>
<?php
//支付方式
$pay='
    <table class="table table-hover ">
    <tr>
   <td>'.$form->field($model, "supplier_settlement")->dropDownList(SupplierServices::getSettlementMethod(), ["prompt" => "请选择结算方式",'required'=>true]).'</td>
    <td>'. $form->field($model, "payment_method")->dropDownList(SupplierServices::getDefaultPaymentMethod(), ["prompt" => "请选择支付方式",'required'=>true]).'</td>
 </tr>
</table>
    <table class="table table-hover ">
    
      <div class="col-md-2">'.Html::button('添加帐号', ['class' => 'btn btn-success add_user']).'</div>
        <thead>
        <tr>
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
        </thead>

        <tbody class="pay">
        <tr class="pay_list ">
            <td>'.$form->field($model_pay, "payment_platform[]")->dropDownList(SupplierServices::getPaymentPlatform(),['class' => 'form-control payment_platform pay_info check_pay','prompt'=>'请选择'])->label(false).'</td><td>'.
            $form->field($model_pay, "payment_platform_bank[]")->dropDownList(\app\models\UfxFuiou::getMasterBankInfo(),['class' => 'form-control pay_info pay_bank check_pay','prompt'=>'请选择'])->label(false).'</td><td>'
            .$form->field($model_pay, 'payment_platform_branch[]')->textInput(['id'=>'pay_branch_1','class'=>'pay_branch form-control pay_info',"maxlength" => true,"placeholder"=>"请填写支行"])->label(false) .'</td>
             <td>'.$form->field($model_pay, "prov_code[]")->dropDownList(\app\models\UfxFuiou::getProvInfo(),['class' => 'form-control prov pay_info','prompt'=>'请选择省'])->label(false).'</td>
             <td>'.$form->field($model_pay, "city_code[]")->dropDownList([],['class' => 'form-control city pay_info','prompt'=>'请选择市'])->label(false).'</td>
             <td>'.$form->field($model_pay, "account_type[]")->dropDownList(['1'=>'对公','2'=>'对私'],['class' => 'form-control account_type pay_info','prompt'=>'请选择'])->label(false).'</td>
            <td>'.$form->field($model_pay, "account_name[]")->textInput(['class'=>'pay_info form-control',"maxlength" => true,"placeholder"=>""])->label(false).'</td>
            <td>'.$form->field($model_pay, "account[]")->textInput(['class'=>'pay_info form-control',"maxlength" => true,"placeholder"=>""])->label(false).'</td>
            <td>'.$form->field($model_pay, "phone_number[]")->textInput(['class'=>'pay_info form-control',"maxlength" => true,"placeholder"=>"手机号码"])->label(false).'</td>
            <td>'.$form->field($model_pay, "id_number[]")->textInput(['class'=>'pay_info form-control','title'=>'对私账户为收款人身份证号,对公账户为收款公司组织机构代码',"maxlength" => true,"placeholder"=>"对私账户为收款人身份证号,对公账户为收款公司组织机构代码"])->label(false).'</td>
              <td>'.Html::button('删除', ['class' => 'btn btn-danger form-control']).'</td>


        </tbody>

    </table>
    ';
//产品线
$line='
    <table class="table table-hover ">
        <thead>
        <tr>
           <th>一级产品线</th>
            <th>二级产品线</th>
            <th>三级产品线</th>
        </tr>
        </thead>
        <tbody class="line">
        <tr class="line_list ">
                    <td>'.$form->field($model_line, "first_product_line[]")->dropDownList(BaseServices::getProductLine(),['class' => 'form-control first ','prompt'=>'选择一级产品线',
        'onclick'=>'
                        var second = $(this).closest("tr").find(".second");
                        $.post("'.yii::$app->urlManager->createUrl('supplier/line').'?pid="+$(this).val(),function(data){
                            second.html(data);
                            second.trigger("click");
                        });','required'=>true
    ])->label('').'</td>
                    <td>'.$form->field($model_line, "second_product_line[]")->dropDownList(BaseServices::getProductLineList($model_line->first_product_line),['class' => 'form-control second ','prompt'=>'选择二级产品线',
        'onclick'=>'
                        var third = $(this).closest("tr").find(".third");
                        $.post("'.yii::$app->urlManager->createUrl('supplier/line').'?pid="+$(this).val(),function(data){
                            third.html(data);
                        });',])->label('').'</td>
                    <td>'.$form->field($model_line, "third_product_line[]")->dropDownList(BaseServices::getProductLineList($model_line->second_product_line),['class' => 'form-control third','prompt'=>'选择三级产品线'])->label('').'</td>
                    <td>'.Html::button('删除', ['class' => 'btn btn-danger form-control','style'=>'margin-top:20px;']).'</td>


        </tbody>

    </table>';
//采购员
$buyer='
    <table class="table table-hover ">
        <thead>
        <tr>
           <th></th>
            <th>部门</th>
            <th>采购员</th>
        </tr>
        </thead>
        <tbody class="buyer">
            <tr class="buyer_list ">
                <td><input type="checkbox" name="SupplierBuyer[type][]" value=1></td>
                <td>国内仓</td>
                <td>'.$form->field($model_buyer, 'buyer[]')->widget(Select2::classname(), [
        'options' => ['placeholder' => '请输入采购员 ...','id'=>'IN'],
        'data' =>BaseServices::getEveryOne('','name'),
        'pluginOptions' => [
            'language' => [
                'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),],
        ],])->label('') .'
                </td>
            </tr>
            <tr class="buyer_list ">
                <td><input type="checkbox" name="SupplierBuyer[type][]" value=2></td>
                <td>海外仓</td>
                <td>'.$form->field($model_buyer, 'buyer[]')->widget(Select2::classname(), [
        'options' => ['placeholder' => '请输入采购员 ...','id'=>'HWC'],
        'data' =>BaseServices::getEveryOne('','name'),
        'pluginOptions' => [
            'language' => [
                'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),],
        ],])->label('') .'
                </td>
            </tr>
            <tr class="buyer_list ">
                <td><input type="checkbox" name="SupplierBuyer[type][]" value=3 class="FBABuyer"></td>
                <td>FBA</td>
                <td>'.$form->field($model_buyer, 'buyer[]')->widget(Select2::classname(), [
        'options' => ['placeholder' => '请输入采购员 ...','id'=>'FBA'],
        'data' =>BaseServices::getBuyerByRoleName(['FBA采购组','FBA采购经理组'],'name'),
        'pluginOptions' => [
            'language' => [
                'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),],
        ],])->label('') .'
                </td>
            </tr>
        </tbody>
    </table>';
//结束
//联系我们
$contact='
    <table class="table table-hover ">
     <div class="col-md-2">'.Html::button('添加联系人', ['class' => 'btn btn-success add_contact']).'</div>
        <thead>
        <tr>
           <th>联系人</th>
           <th>法人代表</th>
            <th>联系电话</th>
            <th>发货地址</th>
            <th>QQ</th>
            <th>微信</th>
            <th>邮箱</th>
            <th>旺旺</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody class="contact">
        <tr class="contact_list contact-person">
                    <td>'.$form->field($model_contact, "contact_person[]")->textInput(['class' => 'form-control ','placeholder'=>'联系人','required'=>true])->label('').'</td>
                    <td>'.$form->field($model_contact, "corporate[]")->textInput(['class' => 'form-control ','placeholder'=>'法人代表','max'=>"100",'required'=>true])->label('').'</td>
                    <td>'.$form->field($model_contact, "contact_number[]")->textInput(['class' => 'form-control ','placeholder'=>'联系电话','max'=>"20",'required'=>true])->label('').'</td>
                    <td>'.$form->field($model_contact, "chinese_contact_address[]")->textInput(['class' => 'form-control ','placeholder'=>'发货地址','required'=>true])->label('').'</td>
                    <td>'.$form->field($model_contact, "qq[]")->textInput(['class' => 'form-control ','placeholder'=>'QQ','max'=>"20"])->label('').'</td>
                    <td>'.$form->field($model_contact, "micro_letter[]")->textInput(['class' => 'form-control ','placeholder'=>'微信','max'=>"20"])->label('').'</td>
                    <td>'.$form->field($model_contact, "email[]")->input('email',['class' => 'form-control ','placeholder'=>'邮箱','max'=>"255"])->label('') .'</td>
                    <td>'.$form->field($model_contact, "want_want[]")->textInput(['class' => 'form-control ','placeholder'=>'旺旺','max'=>"20",'required'=>true])->label('').'</td>

              <td>'.Html::button('删除', ['class' => 'btn btn-danger form-control','style'=>'margin-top:20px;']).'</td>


        </tbody>

    </table>';
//结束
?>

<?php
$items = [
    [
        'label'=>'<i class="glyphicon glyphicon-yen"></i> 财务结算',
        //'content'=>$this->render('_pay',['model_pay'=>$model_pay]),
        'content'=> $pay,

    ],
    [
        'label'=>'<i class="glyphicon glyphicon-user"></i> 联系方式',
        //'content'=>$this->render('_contact',['model_pay'=>$model_contact]),
        'content'=>$contact,

    ],
    [
        'label'=>'<i class="glyphicon glyphicon-yen"></i> 产品线',
        'content'=>$line
    ],
    [
        'label'=>'<i class="glyphicon glyphicon-yen"></i> 采购员（部门必选）',
        'content'=>$buyer
    ],

    [
        'label'=>'<i class="glyphicon glyphicon-book"></i> 介绍',
        'content'=>$form->field($model,'contract_notice')->textarea(['col'=>10,'rows'=>5])->label(''),

    ],
    [
        'label'=>'<i class="glyphicon glyphicon-picture"></i> 附属图片',
        'content'=>$form->field($model_img, 'image_url')->widget(FileInput::classname(), ['options' => ['multiple' => true,],
            'pluginOptions' => [
                // 需要预览的文件格式
                'previewFileType' => 'image',
                // 预览的文件
                'initialPreview' =>$p1,
                // 需要展示的图片设置，比如图片的宽度等
                'initialPreviewConfig' =>$p2,
                // 是否展示预览图
                'initialPreviewAsData' => true,
                'allowedFileExtensions' => ['jpg', 'gif', 'png'],
                // 异步上传的接口地址设置
                'uploadUrl' => Url::toRoute(['/supplier/async-image']),
                'uploadAsync' => true,
                // 最少上传的文件个数限制
                'minFileCount' => 1,
                // 最多上传的文件个数限制
                'maxFileCount' => 10,
                'maxFileSize' => 2000,//限制图片最大2000kB
                // 是否显示移除按钮，指input上面的移除按钮，非具体图片上的移除按钮
                'showRemove' => true,
                // 是否显示上传按钮，指input上面的上传按钮，非具体图片上的上传按钮
                'showUpload' => false,
                //是否显示[选择]按钮,指input上面的[选择]按钮,非具体图片上的上传按钮
                'showBrowse' => true,
                // 展示图片区域是否可点击选择多文件
                'browseOnZoneClick' => true,
                // 如果要设置具体图片上的移除、上传和展示按钮，需要设置该选项
                'fileActionSettings' => [
                    // 设置具体图片的查看属性为false,默认为true
                    'showZoom' => false,
                    // 设置具体图片的上传属性为true,默认为true
                    'showUpload' => true,
                    // 设置具体图片的移除属性为true,默认为true
                    'showRemove' => true,
                ],
            ],
            // 一些事件行为
            'pluginEvents' => [
                // 上传成功后的回调方法，需要的可查看data后再做具体操作，一般不需要设置
                'fileuploaded' => 'function(event, data, previewId, index) {
                        $(event.currentTarget.closest("form")).append(data.response.imgfile);
                    }',
            ],

        ]),

    ],

];

echo TabsX::widget([
    'items'=>$items,
    'position'=>TabsX::POS_ABOVE,
    'encodeLabels'=>false
]);?>
<div class="form-group">
    <?= Html::submitButton($model->isNewRecord ? Yii::t('app', '创建') : Yii::t('app', '更新'), ['class' => $model->isNewRecord ? 'btn btn-success create' : 'btn btn-primary create']) ?>
</div>


<?php ActiveForm::end(); ?>


<?php
$buyerUrl = Url::toRoute('buyer');
$cityUrl = Url::toRoute('ufx-fuiou/get-city');
$bankUrl = Url::toRoute('ufx-fuiou/branch-bank');
$bankCheckUrl = Url::toRoute('ufx-fuiou/check-bank');
$bankCityUrl = Url::toRoute('ufx-fuiou/get-branch-bank-city');
$eyeCheckUrl = Url::toRoute('supplier/get-supplier-info');
$js = <<<JS
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
        $('.pay_branch').on('blur',function() {
          
        })
        var branch_click_index = 1; 
        var spotMax = 2;
        if($(".pay_list").size() >= 4) {
          $(obj).hide();
        }
        $("button.add_user").click(function(){
            branch_click_index++;
            addSpot(this, 4,'pay','pay_list');
             complete($('#pay_branch_'+branch_click_index),'/ufx-fuiou/branch-bank');
        });
        $("button.add_line").click(function(){

         addSpot(this, spotMax,'line','line_list');
        });
        //联系我们
        if($(".contact_list").size() >= spotMax) {
          $(obj).hide();
        }
        $("button.add_contact").click(function(){

         addSpot(this, spotMax,'contact','contact_list');
        });
     
        /*公用复制*/
        var selectId = 1;
        function addSpot(obj, sm, father_class,child_class) {
            var clone  = $("."+child_class).first().clone();
            clone.find('input').val('');
            clone.find('input').prop('readonly',false);
            clone.find('select').val('');
            clone.find(".city option").remove()
            clone.find(".city").append("<option value=''>请选择市</option>");
            clone.find(".pay_branch").attr('id','pay_branch_'+branch_click_index);
            $("."+father_class).append(clone)
                .find("button.btn-danger").click(function(){
                    //保留最后一个不让删除
                    if($("."+ child_class).size() > 1){
                        $(this).parent().parent().remove();
                    }
                    $('button.btn-success').show();
            });

            if($("."+ child_class).size() >= sm) {
                $(obj).hide();
            }
        };

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
        
        $(document).on('change','.prov',function() {
        var prov = $(this).val();
        obj = $(this);
        $(this).closest('tr').find('.city').html('<option value="">请选择</option>');
        var loading = layer.load(6 , {shade : [0.5 , '#BFE0FA']});
        $.get("{$cityUrl}",{prov:prov},function(data){
            layer.close(loading);
            obj.closest('tr').find('.city').html(data);
        });
    });
        $(document).on('change','#supplier-payment_method',function() {
            var paymethodArray = ['3','5'];
          if($.inArray($(this).val(),paymethodArray)!==-1){
              $('.pay_info').prop('required',true);
          }else{
              $('.pay_info').prop('required',false);
          }
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
        $('.create').on('click',function() {
         
          var invoice = $('#supplier-invoice').val()
          if (invoice === '0') {
            layer.msg('请选择开票类型');
            return false;
          }

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
            $("[name='SupplierPaymentAccount[account_type][]']").each(function() {
              if($(this).val()==1){
                  accountTypePublic++;
              }
              if($(this).val()==2){
                  accountTypePrivate++;
              }
            });
            if(payplatcount>1){
                layer.msg('富友平台银行只能维护一张');
                return false;
            }
            if(accountTypePrivate>1||accountTypePublic>1){
                layer.msg('对公对私卡暂时只支持各维护一张');
                return false;
            }
            //联系方式限制
            var index=0;
            $('.contact-person').each(function() {
              var qq = $(this).find('[name="SupplierContactInformation[qq][]"]').val();
              var wechart = $(this).find('[name="SupplierContactInformation[micro_letter][]"]').val();
              var email = $(this).find('[name="SupplierContactInformation[email][]"]').val();
              if(email==''&&qq==''&&wechart==''){
                  layer.msg('每个联系方式微信QQ邮箱三者至少填写一个');
                  index++;
                  return false;
              }
            });
            if(index>=1){
                return false;
            }
            //支付信息验证
            var paymentInfo=0
            $('.pay_list').each(function() {
                var account = $(this).find('[name="SupplierPaymentAccount[account][]"]').val();
                var accountName = $(this).find('[name="SupplierPaymentAccount[account_name][]"]').val();
                var phoneNumber = $(this).find('[name="SupplierPaymentAccount[phone_number][]"]').val();
                var idNumber = $(this).find('[name="SupplierPaymentAccount[id_number][]"]').val();
                if(checkDataHaveKg(idNumber)){
                   layer.msg('证件号不要使用空格!');
                   paymentInfo++;
                   return false;
                }
                if(checkDataHaveKg(accountName)){
                   layer.msg('开户名不要使用空格!');
                   paymentInfo++;
                   return false;
                }
                if(checkDataHaveKg(phoneNumber)){
                   layer.msg('到账通知手机号不要使用空格!');
                   paymentInfo++;
                   return false;
                }
                if(phoneNumber!='' &&!checkPhoneNumber(phoneNumber)){
                    paymentInfo++;
                    layer.msg(phoneNumber+'到账通知手机号验证失败');
                    return false;
                }
                if(checkDataHaveKg(account)){
                   layer.msg('银行卡不要使用空格!');
                   paymentInfo++;
                   return false;
                }
            });
            if(paymentInfo>=1){
                return false;
            }
            var str = '';
            $('[name="SupplierBuyer[type][]"]').each(function(){
                if($(this).is(':checked')){
                    str+=$(this).val();
                }
            });
            if(str==''){
                layer.msg('必须选择一个部门');
                return false;
            }
        });
        var  supplier_create_submit_index = 1;
        $(document).on('submit','#supplier-create',function() {
            supplier_create_submit_index++;
            if(supplier_create_submit_index>2){
                layer.msg('数据已提交不要重复点击');
                return false;
            }
        });
        
      
       
JS;

$this->registerJs($js);
?>
