<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\tabs\TabsX;
use kartik\file\FileInput;
use yii\helpers\Url;
use app\services\SupplierServices;
use app\services\BaseServices;

/* @var $this yii\web\View */
/* @var $model app\models\Supplier */
/* @var $form yii\widgets\ActiveForm */
?>



    <?php $form = ActiveForm::begin(["options" => ["enctype" => "multipart/form-data"]]); ?>
<h3 class="fa-hourglass-3">基本信息</h3>
<div class="row">


    <div class="col-md-2"><?= $form->field($model, 'supplier_name')->textInput(['maxlength' => true,'placeholder'=>'']) ?></div>

    <div class="col-md-2"><?= $form->field($model, 'supplier_level')->dropDownList(SupplierServices::getSupplierLevel(), ['prompt' => '请选择供应商等级']) ?></div>
   <!-- <div class="col-md-2"><?/*= $form->field($model, 'esupplier_name')->textInput(['maxlength' => true,'placeholder'=>'yibai network']) */?></div>-->
    <div class="col-md-2"><?= $form->field($model, 'cooperation_type')->dropDownList(SupplierServices::getCooperation(), ['prompt' => '请选择合作类型']) ?></div>
    <div class="col-md-2"><?= $form->field($model, 'main_category')->dropDownList(BaseServices::getCategory(), ['prompt' => '请选择主营品类']) ?></div>
    <div class="col-md-2"><?= $form->field($model, 'buyer')->dropDownList(BaseServices::getEveryOne(), ['prompt' => '请选择采购员']) ?></div>
    <div class="col-md-2"><?= $form->field($model, 'supplier_type')->dropDownList(SupplierServices::getSupplierType(), ['prompt' => '请选择供应商类型']) ?></div>
  <!--  <div class="col-md-2"><?/*= $form->field($model, 'merchandiser')->dropDownList(BaseServices::getEveryOne(), ['prompt' => '请选择跟单员']) */?></div>-->
    <div class="col-md-2"><?= $form->field($model, 'payment_cycle')->dropDownList(SupplierServices::getPaymentCycle(), ['prompt' => '请选择支付周期类型']) ?></div>
    <div class="col-md-2"><?= $form->field($model, 'supplier_settlement')->dropDownList(\app\services\SupplierServices::getSettlementMethod(), ['prompt' => '请选择结算方式']) ?></div>
    <div class="col-md-2"><?= $form->field($model, 'payment_method')->dropDownList(\app\services\SupplierServices::getDefaultPaymentMethod(), ['prompt' => '请选择支付方式']) ?></div>
   <!-- <div class="col-md-2"><?/*= $form->field($model, 'transport_party')->dropDownList(\app\services\SupplierServices::getTransportParty(), ['prompt' => '请选择运输承担方']) */?></div>-->
    <div class="col-md-2"><?= $form->field($model, 'product_handling')->dropDownList(\app\services\SupplierServices::getBadProductHandling(), ['prompt' => '请选择不良品处理方式']) ?></div>
   <!-- <div class="col-md-2"><?/*= $form->field($model, 'commission_ratio')->textInput(['maxlength' => true]) */?></div>-->
    <div class="col-md-2"><?= $form->field($model, 'purchase_amount')->textInput(['maxlength' => true,'placeholder'=>'RMB']) ?></div>

    <!--<div class="col-md-2"><?/*= $form->field($model, 'province')->dropDownList(BaseServices::getCityList(1),[
            'prompt'=>'--请选择省--',
            'onchange'=>'
            $(".form-group.field-member-area").hide();
            $.post("'.yii::$app->urlManager->createUrl('supplier/sites').'?typeid=1&pid="+$(this).val(),function(data){
                $("select#supplier-city").html(data);
            });',
        ]) */?></div>
    <div class="col-md-2"><?/*= $form->field($model, 'city')->dropDownList(BaseServices::getCityList($model->province),
            [
                'prompt'=>'--请选择市--',
                'onchange'=>'
            $(".form-group.field-member-area").show();
            $.post("'.yii::$app->urlManager->createUrl('supplier/sites').'?typeid=2&pid="+$(this).val(),function(data){
                $("select#supplier-area").html(data);
            });',
            ]) */?></div>
    <div class="col-md-2"><?/*= $form->field($model, 'area')->dropDownList(BaseServices::getCityList($model->city),['prompt'=>'--请选择区--',]) */?></div>-->
    <div class="col-md-2"><?= $form->field($model, "status")->dropDownList(['1'=>'可用','0'=>'不可用'],['class' => 'form-control pay_method']) ?></div>
    <div class="col-md-2"><?= $form->field($model, "is_taxpayer")->dropDownList(Yii::$app->params['boolean'],['class' => 'form-control pay_method']) ?></div>
    <div class="col-md-2"><?= $form->field($model, 'taxrate')->textInput(['maxlength' => true,'placeholder'=>'']) ?></div>
    <div class="col-md-4"><?= $form->field($model, 'supplier_address')->textarea(['maxlength' => true])->label('详细地址') ?></div>
</div>
<?php
//支付方式
$pay='
    <table class="table table-hover ">
      <div class="col-md-2">'.Html::button('添加帐号', ['class' => 'btn btn-success add_user']).'</div>
        <thead>
        <tr>
            <th>支付方式</th>
            <th>支行/平台</th>
            <th>账户</th>
            <th>账户名</th>
            <th>状态</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody class="pay">
        <tr class="pay_list ">

            <td>'.$form->field($model_pay, "payment_method[]")->dropDownList(['2'=>'在线','3'=>'银行卡'],['class' => 'form-control pay_method'])->label(false).'</td>
            <td>'.$form->field($model_pay, "payment_platform[]")->dropDownList(['1'=>'paypal','2'=>'财付通','3'=>'支付宝','4'=>'快钱','5'=>'网银'],['class' => 'form-control payment_platform'])->label(false).
            $form->field($model_pay, "payment_platform_bank[]")->dropDownList(\app\services\SupplierServices::getPayBank(),['class' => 'form-control pay_bank','style'=>'display:none'])->label(false)
            .$form->field($model_pay, "payment_platform_branch[]")->textInput(['class' => 'form-control pay_bank','style'=>'display:none','placeholder'=>'请录入支行名称'])->label(false).'</td>
            <td>'.$form->field($model_pay, "account[]")->textInput(["maxlength" => true,"placeholder"=>""])->label(false).'</td>
            <td>'.$form->field($model_pay, "account_name[]")->textInput(["maxlength" => true,"placeholder"=>""])->label(false).'</td>
            <td>'.$form->field($model_pay, "status[]")->dropDownList(['1'=>'可用','2'=>'不可用'],['class' => 'form-control pay_method'])->label(false).'</td>
              <td>'.Html::button('删除', ['class' => 'btn btn-danger form-control']).'</td>


        </tbody>

    </table>
    ';
//结束
//联系我们
$contact='
    <table class="table table-hover ">
     <div class="col-md-2">'.Html::button('添加联系人', ['class' => 'btn btn-success add_contact']).'</div>
        <thead>
        <tr>
           <th>联系人</th>
            <th>联系电话</th>
            <th>Fax</th>
            <th>中文联系地址</th>
            <th>英文联系地址</th>
            <th>联系邮编</th>
            <th>QQ</th>
            <th>微信</th>
            <th>旺旺</th>
            <th>Skype</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody class="contact">
        <tr class="contact_list ">
                    <td>'.$form->field($model_contact, "contact_person[]")->textInput(['class' => 'form-control ','placeholder'=>'联系人'])->label('').'</td>
                    <td>'.$form->field($model_contact, "contact_number[]")->textInput(['class' => 'form-control ','placeholder'=>'联系电话'])->label('').'</td>
                    <td>'.$form->field($model_contact, "contact_fax[]")->textInput(['class' => 'form-control ','placeholder'=>'Fax'])->label('').'</td>
                    <td>'.$form->field($model_contact, "chinese_contact_address[]")->textInput(['class' => 'form-control ','placeholder'=>'中文联系地址'])->label('').'</td>
                    <td>'.$form->field($model_contact, "english_address[]")->textInput(['class' => 'form-control ','placeholder'=>'英文联系地址'])->label('').'</td>
                    <td>'.$form->field($model_contact, "contact_zip[]")->textInput(['class' => 'form-control ','placeholder'=>'联系邮编'])->label('').'</td>
                    <td>'.$form->field($model_contact, "qq[]")->textInput(['class' => 'form-control ','placeholder'=>'QQ'])->label('').'</td>
                    <td>'.$form->field($model_contact, "micro_letter[]")->textInput(['class' => 'form-control ','placeholder'=>'微信'])->label('').'</td>
                    <td>'.$form->field($model_contact, "want_want[]")->textInput(['class' => 'form-control ','placeholder'=>'旺旺'])->label('').'</td>
                    <td>'.$form->field($model_contact, "skype[]")->textInput(['class' => 'form-control ','placeholder'=>'Skype'])->label('').'</td>

              <td>'.Html::button('删除', ['class' => 'btn btn-danger form-control','style'=>'margin-top:20px;']).'</td>


        </tbody>

    </table>';
//结束
?>

<?php
$items = [
    [
        'label'=>'<i class="glyphicon glyphicon-yen"></i> 支付方式',
        //'content'=>$this->render('_pay',['model_pay'=>$model_pay]),
        'content'=> $pay,

    ],
    [
        'label'=>'<i class="glyphicon glyphicon-user"></i> 联系方式',
        //'content'=>$this->render('_contact',['model_pay'=>$model_contact]),
        'content'=>$contact,

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
             'maxFileSize' => 200,//限制图片最大200kB
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
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', '创建') : Yii::t('app', '更新'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>


    <?php ActiveForm::end(); ?>


<?php
$js = <<<EOF

        var spotMax = 10;
        //支付方式
        if($(".pay_list").size() >= spotMax) {
          $(obj).hide();
        }
        $("button.add_user").click(function(){

         addSpot(this, spotMax,'pay','pay_list');
        });
        switchBank();
        //联系我们
        if($(".contact_list").size() >= spotMax) {
          $(obj).hide();
        }
        $("button.add_contact").click(function(){

         addSpot(this, spotMax,'contact','contact_list');
        });
        //银行切换
        function switchBank()
        {
         //支付方式切换
             $.each($(".pay_method"), function() {
                    if($(this).val() == "3"){

                         $(".pay_bank", $(this).parents('tr')).css({"margin-top":"-15px"});
                         $(".pay_bank", $(this).parents('tr')).show();
                         $(".payment_platform", $(this).parents('tr')).hide();
                    } else {
                        $(".pay_bank", $(this).parents('tr')).hide();
                        $(".payment_platform", $(this).parents('tr')).show();
                    }
		    });
             $(".pay").find(".pay_method").change(function(){

                    if ($(this).val() == 3){
                         $(".pay_bank", $(this).parents('tr')).css({"margin-top":"-15px"});
                         $(".pay_bank", $(this).parents('tr')).show();
                         $(".payment_platform", $(this).parents('tr')).hide();

                    }else{
                          $(".pay_bank", $(this).parents('tr')).hide();
                          $(".payment_platform", $(this).parents('tr')).show();
                    }
            });
        }

        /*公用复制*/
        function addSpot(obj, sm, father_class,child_class) {

            $("."+father_class).append($("."+child_class).first().clone())
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
            if(father_class=='pay'){
                switchBank();
            }


        };



EOF;

$this->registerJs($js);
?>
