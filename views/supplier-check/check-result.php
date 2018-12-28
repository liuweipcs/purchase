<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use app\services\BaseServices;
use kartik\select2\Select2;
use yii\web\JsExpression;
use \yii\bootstrap\Modal;
use app\services\SupplierServices;
/* @var $this yii\web\View */
/* @var $model app\models\Supplier */
/* @var $form yii\widgets\ActiveForm */

?>

<div class="row">
    <?php $form = ActiveForm::begin(); ?>
    <table class="table" width="90%" style="visibility:<?=$model->check_type==1 ? 'hidden' : 'visible' ?>" >
        <tbody>
        <tr>
            <td>SKU</td>
            <td>采购数量</td>
            <td>不良品</td>
            <td>合格质量标准</td>
        </tr>
        <?php if($model->checkPur){?>
        <?php foreach ($model->checkPur as $key=>$value){?>
                <tr>
                    <?= Html::hiddenInput("SupplierCheck[items][$key][type]",$value->type)?>
                    <td>
                        <?= Html::hiddenInput("SupplierCheck[items][$key][sku]",$value->sku)?>
                        <?=$value->sku?>
                    </td>
                    <td>
                        <?= Html::hiddenInput("SupplierCheck[items][$key][purchase_num]",$value->purchase_num)?>
                        <?= $value->purchase_num?>
                    </td>
                    <td><?= Html::input('number',"SupplierCheck[items][$key][bad_goods]",0,['min'=>0,'step'=>1,'max'=>$value->purchase_num,'class'=>"form-control"])?></td>
                    <td><?= $value->check_rate?></td>
                </tr>
        <?php }?>
        <?php }?>
        </tbody>
    </table>
    <table class="table">
        <tr style="background-color: #1087dd">
            <td>检验结果</td>
            <td><input type="radio" name="SupplierCheck[judgment_results]" value="1">合格</td>
            <td><input type="radio" name="SupplierCheck[judgment_results]" value="2">不合格</td>
        </tr>
        <tr>
            <td>检验结论</td>
            <td colspan="2"><?= $form->field($model,'evaluate')->textInput(['placeholder'=>'请填写检验结论','required'=>true])->label(false)?></td>
        </tr>
        <tr>
            <td>改善措施</td>
            <td colspan="2"><?= $form->field($model,'improvement_measure')->textInput(['placeholder'=>'请填写改善措施'])->label(false)?></td>
        </tr>
    </table>
<div>
    <hr>
    <div style="width: 80% ;border:solid 1px;margin-left: 10%">
        <table>
            <tr>
                <td style="padding: 0px 50px 0px 20px ">是否需要再次验货</td>
                <td>
                    <label></label>
                    <?= Html::radioList('SupplierCheck[is_check_again]',0,[0=>'否',1=>'是'],['class'=>'form-control'])?>
                </td>
            </tr>
            <tr>
                <td style="padding: 0px 50px 0px 20px ">再次验货原因</td>
                <td>
                    <label></label>
                    <?=Html::dropDownList('SupplierCheck[review_reason]','',SupplierServices::getSupplierReviewReason(),['class'=>'form-control','prompt'=>'请选择'])?>
                </td>
            </tr>
            <tr>
                <td style="padding: 0px 50px 0px 20px ">检验费用</td>
                <td>
                    <label></label>
                    <?=Html::input('number','SupplierCheck[check_price]','',['placeholder'=>'30','class'=>'form-control','min'=>0,'step'=>0.001])?>
                </td>
            </tr>
        </table>
    </div>
    <div style="width: 80% ;margin-left: 10%;padding-top: 10px">
        <div style="float: right"><?= Html::submitButton(Yii::t('app', '确认'), ['class' => 'btn btn-primary submit_button']) ?></div>
        <div style="float: right;margin-right: 10px"><a href="#" class="btn btn-warning closes" data-dismiss="modal">取消</a></div>
        <div style="clear: right"></div>
    </div>
</div>

</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<<JS
    $(document).on('change','[name="SupplierCheck[is_check_again]"]',function() {
        var is_check_again = $('[name="SupplierCheck[is_check_again]"]:checked').val();
        if(is_check_again==1){
            $('[name="SupplierCheck[review_reason]"]').prop('required',true);
            $('[name="SupplierCheck[check_price]"]').prop('required',true);
        }else {
            $('[name="SupplierCheck[review_reason]"]').prop('required',false);
            $('[name="SupplierCheck[check_price]"]').prop('required',false);
        }
    });
    $(document).on('click', '.submit_button', function () {
        var val=$('input:radio[name="SupplierCheck[judgment_results]"]:checked').val();
        if (val==1 || val==2) {
            return true;
        } else {
            layer.msg('请选择判定结果');
            return false;
        }
    });
JS;
$this->registerJs($js);
?>

