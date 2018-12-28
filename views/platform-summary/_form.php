<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\services\BaseServices;
use kartik\select2\Select2;
use yii\web\JsExpression;
$platform = \app\models\PlatformSummarySearch::overseasPlatformList(null,true);
?>
<h4><span class="glyphicon glyphicon-heart" style="color: red" aria-hidden="true"></span>温馨小提示:<br /><span style="color: red">1.如果填写sku关联不到产品类别和产品名请联系管理员<br /><h3>2.选择退税仓必须经销售主管同意</h3></h4>
<div class="user-form">
    <?php $form = ActiveForm::begin(); ?>
    <div class="col-md-2"><?= $form->field($model, 'sku')->textInput(['maxlength'=>true]);?></div>
    <div class="col-md-2"><?= $form->field($model, 'platform_number')->dropDownList($platform,['value'=>'AMAZON'])?></div>
    <div class="col-md-2"><?= $form->field($model, 'group_id')->dropDownList(BaseServices::getAmazonGroup(),['value'=>BaseServices::getGroupByUserName(Yii::$app->user->identity->username)])?></div>
    <div class="col-md-2">
        <?= $form->field($model, 'sales')->dropDownList([],['prompt'=>'请选择'])?>
    </div>
    <div class="col-md-2">
        <?= $form->field($model, 'xiaoshou_zhanghao')->dropDownList([],['prompt'=>'请选择', 'required'=>true])->label('销售账号')?>
    </div>

    <div class="col-md-2"><?= $form->field($model, 'product_category')->widget(Select2::classname(), [
        'options' => ['placeholder' => '请输入产品分类 ...','value'=>$model->product_category],
        'data' =>BaseServices::getCategory(),
        'pluginOptions' => [

            'language' => [
                'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
            ],
            'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
            'templateResult' => new JsExpression('function(res) { return res.text; }'),
            'templateSelection' => new JsExpression('function (res) { return res.text; }'),
        ],
    ])?></div>
    <div class="col-md-2"><?= $form->field($model, 'product_name'); ?></div>
    
    <div class="col-md-2">
        <?= $form->field($model, 'is_back_tax')->dropDownList(['2'=>'否','1'=>'是'],['disabled'=>'disabled']);?>
        <?= $form->field($model, 'is_back_tax_post')->hiddenInput([])->label(''); ?>  
    </div>
    <div class="col-md-2"><?= $form->field($model, 'purchase_quantity')->textInput();?></div>
    <div class="col-md-2"><?= $form->field($model, 'purchase_warehouse')->widget(Select2::classname(), [
        'options' => ['placeholder' => '请选仓库 ...','value'=>!empty($model->purchase_warehouse) ? $model->purchase_warehouse : 'FBA_SZ_AA'],
        'data' => ['FBA_SZ_AA'=>'东莞仓FBA虚拟仓','TS'=>'退税仓'],
        'pluginOptions' => [
            'language' => [
                'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
            ],
            'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
            'templateResult' => new JsExpression('function(res) { return res.text; }'),
            'templateSelection' => new JsExpression('function (res) { return res.text; }'),
        ],
    ])?></div>
    <div class="col-md-2"><?= $form->field($model, 'is_transit')->dropDownList(['1'=>'直发','2'=>'需要中转']);?></div>
    <div class="col-md-2"><?= $form->field($model, 'transit_warehouse')->dropDownList(['shzz'=>'上海中转仓库','AFN'=>'东莞中转仓库'],['prompt' => '请选中转仓','id'=>'warehouse']);?></div>
    <div class="col-md-2"><?= $form->field($model, 'sales_note')->textarea(['placeholder' => '请备注一下销售名吧 ...']);?></div>

    <div class="form-group" style="clear: both">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', '创建') : Yii::t('app', '更新'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<?php
$url = \yii\helpers\Url::toRoute(['product/get-name']).'?tax=1';
$sale = \yii\helpers\Url::toRoute(['get-sales']);
$xiaoshou_zhanghao = \yii\helpers\Url::toRoute(['get-xiaoshou-zhanghao']);
$token = Yii::$app->request->getCsrfToken();
$js=<<<JS

    $('.field-warehouse').parent().hide();
    $("#platformsummary-is_transit").change(function(){
        var status =$(this).find("option:selected").val();
        if(status==1){
            $('.field-warehouse').parent().hide();
        }else{
            $('.field-warehouse').parent().show();
        }
    });

    $("#platformsummary-purchase_warehouse").change(function(){
        if ($(this).val() == 'TS') {
             var sku = $("#platformsummary-sku").val();
             var is_back_tax = $("#platformsummary-is_back_tax").val();
             $.ajax({
              type:"GET",
              url:"$url",
              dataType:"json",
              data:{sku:sku},
              success: function(data){
                    if ( (is_back_tax == 1) && (data.payment_method != 3) ) {
                        $("#platformsummary-purchase_warehouse").val('FBA_SZ_AA');
                        $("#select2-platformsummary-purchase_warehouse-container").attr('title','东莞仓FBA虚拟仓');
                        $("#select2-platformsummary-purchase_warehouse-container").html('东莞仓FBA虚拟仓');
                        $("#platformsummary-purchase_warehouse").attr('disabled',false);
                        layer.msg('该sku可退税，但当前供应商支付方式不是【银行卡转账】，不能创建【退税】需求');
                } 
              },
          });
            // $("#platformsummary-is_back_tax").val(1);
        } else {
            // $("#platformsummary-is_back_tax").val(2);
        }
    });

    $("#platformsummary-sku").blur(function(){
        var sku =$(this).val();
        $.ajax({
              type:"GET",
              url:"$url",
              dataType:"json",
              data:{sku:$(this).val()},
              success: function(data){
                    $("#platformsummary-product_category").find("option[value='"+data.cid+"']").attr("selected",true);
                    $("#select2-platformsummary-product_category-container").html(data.name);
                    $("#platformsummary-product_name").attr("value",data.title);
                    if (data.is_back_tax == 0 || data.is_back_tax == 2) {
                        $("#platformsummary-is_back_tax").val(2);
                        $("#platformsummary-is_back_tax_post").val(2);
                        $("#platformsummary-purchase_warehouse").val('FBA_SZ_AA');
                        $("#select2-platformsummary-purchase_warehouse-container").attr('title','东莞仓FBA虚拟仓');
                        $("#select2-platformsummary-purchase_warehouse-container").html('东莞仓FBA虚拟仓');
                        $("#platformsummary-purchase_warehouse").attr('disabled','disabled');
                    } else {
                        $("#platformsummary-is_back_tax").val(data.is_back_tax);
                        $("#platformsummary-is_back_tax_post").val(data.is_back_tax);
                        if (data.is_back_tax==1 && data.payment_method != 3)  {
                            $("#platformsummary-purchase_warehouse").val('FBA_SZ_AA');
                            $("#select2-platformsummary-purchase_warehouse-container").attr('title','东莞仓FBA虚拟仓');
                            $("#select2-platformsummary-purchase_warehouse-container").html('东莞仓FBA虚拟仓');
                        } else {
                            $("#platformsummary-purchase_warehouse").val('TS');
                            $("#select2-platformsummary-purchase_warehouse-container").attr('title','退税仓');
                            $("#select2-platformsummary-purchase_warehouse-container").html('退税仓');
                        }
                        $("#platformsummary-purchase_warehouse").attr('disabled',false);
                    }
              },
        });
    });

    $("#platformsummary-group_id").click(function(){
        $.ajax({
                  type:"GET",
                  url:"$sale",
                  dataType:"json",
                  data:{group:$(this).val()},
                  success: function(data){
                    $('[name="PlatformSummary[sales]"]').html(data.sales);
                  },
            });
    });
    /**
     * 获取销售账号
     */
    $("#platformsummary-sales").click(function(){
        $.ajax({
                  type:"GET",
                  url:"$xiaoshou_zhanghao",
                  dataType:"json",
                  data:{sales:$(this).val()},
                  success: function(data){
                    $('[name="PlatformSummary[xiaoshou_zhanghao]"]').html(data.xiaoshou_zhanghao);
                  },
            });
    });
    $("#platformsummary-group_id").trigger('click');
    $("#platformsummary-sku").trigger('blur');
JS;

$this->registerJs($js);
?>