<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\services\BaseServices;
use kartik\select2\Select2;
use yii\web\JsExpression;
use yii\helpers\Url;

$platform = \app\models\PlatformSummarySearch::overseasPlatformList(null,true);
$model->transit_warehouse='AFN';
$model->is_transit=2;
?>
    <h4><span class="glyphicon glyphicon-heart" style="color: red" aria-hidden="true"></span>温馨小提示:<span style="color: red">如果填写sku关联不到产品类别和产品名请联系管理员<i class="fa fa-fw fa-smile-o"></i></h4>
    <div class="user-form">

        <?php $form = ActiveForm::begin(); ?>
        <div class="col-md-2"><?= $form->field($model, 'sku')->textInput(['maxlength'=>true]);?></div>
        <div class="col-md-2"><?= $form->field($model, 'platform_number')->dropDownList($platform)?></div>
        <div class="col-md-2"><?= $form->field($model, 'product_category')->widget(Select2::classname(), [
                'options' => ['placeholder' => '请输入产品分类 ...','value'=>$model->product_category],
                'data' =>BaseServices::getCategory(),
                'pluginOptions' => [

                    'language' => [
                        'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                    ],
                    /*'ajax' => [
                        'url' => $url,
                        'dataType' => 'json',
                        'data' => new JsExpression('function(params) { return {q:params.term}; }')
                    ],*/
                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                    'templateResult' => new JsExpression('function(res) { return res.text; }'),
                    'templateSelection' => new JsExpression('function (res) { return res.text; }'),
                ],
            ])?></div>
        <div class="col-md-2"><?= $form->field($model, 'product_name'); ?></div>



        <div class="col-md-2"><?= $form->field($model, 'purchase_quantity')->textInput();?></div>

        <div class="col-md-2"><?= $form->field($model, 'purchase_warehouse')->widget(Select2::classname(), [
                'options' => ['placeholder' => '请选仓库 ...'],
                'data' =>BaseServices::getWarehouseCode(),
                'pluginOptions' => [

                    'language' => [
                        'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                    ],
                    /*'ajax' => [
                        'url' => $url,
                        'dataType' => 'json',
                        'data' => new JsExpression('function(params) { return {q:params.term}; }')
                    ],*/
                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                    'templateResult' => new JsExpression('function(res) { return res.text; }'),
                    'templateSelection' => new JsExpression('function (res) { return res.text; }'),
                ],
            ])?></div>
        <div class="col-md-2"><?= $form->field($model, 'sales_note')->textarea(['placeholder' => '请备注一下销售名吧 ...']);?></div>

        <div class="form-group" style="clear: both">
            <?= Html::submitButton($model->isNewRecord ? Yii::t('app', '创建') : Yii::t('app', '更新'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
<?php
$url = Url::toRoute(['product/get-name']);
$boxqtyurl = Url::toRoute(['getboxqty']);
$token = Yii::$app->request->getCsrfToken();
$js=<<<JS

    $('.field-warehouse').show();
    $("#platformsummary-is_transit").change(function(){

        var status =$(this).find("option:selected").val();
        if(status==1){
            $('.field-warehouse').hide();
        }else{
            $('.field-warehouse').show();
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

                  }
            });
            
            $.ajax({
                  type:"post",
                  url:"$boxqtyurl",
                  dataType:"json",
                  data:{sku:$(this).val()},
                  success: function(data){
                      if(data.code==1){
                        $("#box-sku-qty").val(data.boxqty);
                      }else{
                        $("#box-sku-qty").val('0');
                      } 
                  }
            });

    });
JS;

$this->registerJs($js);
?>