<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Stockin */


?>
<div class="stockin-update">
    <table class="table table-bordered">
        <div class="col-md-2"><?= Html::button('添加联系人', ['class' => 'btn btn-success add_contact']) ?></div>
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
        <tr class="contact_list">
            <td><?= Html::activeTextInput($model_pay, 'contact_person[]',['class' => 'form-control','placeholder'=>'必填项']) ?></td>
            <td><?= Html::activeTextInput($model_pay, 'contact_number[]',['class' => 'form-control','placeholder'=>'必填项']) ?></td>
            <td><?= Html::activeTextInput($model_pay, 'contact_fax[]',['class' => 'form-control ']) ?></td>
            <td><?= Html::activeTextInput($model_pay, 'chinese_contact_address[]',['class' => 'form-control',]) ?></td>
            <td><?= Html::activeTextInput($model_pay, 'english_address[]',['class' => 'form-control']) ?></td>
            <td><?= Html::activeTextInput($model_pay, 'contact_zip[]',['class' => 'form-control',])?></td>
            <td><?= Html::activeTextInput($model_pay, 'qq[]',['class' => 'form-control',])?></td>
            <td><?= Html::activeTextInput($model_pay, 'micro_letter[]',['class' => 'form-control',])?></td>
            <td><?= Html::activeTextInput($model_pay, 'want_want[]',['class' => 'form-control',])?></td>
            <td><?= Html::activeTextInput($model_pay, 'skype[]',['class' => 'form-control',])?></td>

            <td><?= Html::button('删除', ['class' => 'btn btn-danger form-control']) ?></td>
        </tr>

        </tbody>

    </table>

    <?php
    $js = <<<EOF
        var spotMax = 10;
        if($(".contact_list").size() >= spotMax) {
          $(obj).hide();
        }
        $("button.add_contact").click(function(){

         addSpot(this, spotMax,'contact','contact_list');
        });






EOF;

    $this->registerJs($js);
    ?>

</div>
