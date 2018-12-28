<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Stockin */


?>

<div class="stockin-update">
<table class="table table-bordered">
    <!--<div class="col-md-2"><?/*= Html::button('添加支付帐号', ['class' => 'btn btn-success add_user'])*/?></div>-->
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
        <tr class="pay_list">

            <td><?= Html::activeDropDownList($model_pay, 'payment_method[]',['2'=>'在线','3'=>'银行卡'],['class' => 'form-control pay_method']) ?></td>
            <td>
                <?= Html::activeDropDownList($model_pay, 'payment_platform[]',['1'=>'paypal','3'=>'财付通','3'=>'支付宝','4'=>'快钱','5'=>'网银'],['class' => 'form-control payment_platform']) ?>
                <?= Html::activeDropDownList($model_pay, 'payment_platform_bank[]',\app\services\SupplierServices::getPayBank(),['class' => 'form-control pay_bank']) ?>
                <?= Html::activeTextInput($model_pay, 'payment_platform_branch[]',['class' => 'form-control pay_bank','placeholder'=>'请录入支行名称']) ?></td>
            <td><?= Html::activeTextInput($model_pay, 'account[]',['class' => 'form-control','placeholder'=>'必填项']) ?></td>
            <td><?= Html::activeTextInput($model_pay, 'account_name[]',['class' => 'form-control','placeholder'=>'必填项'])?></td>
            <td><?= Html::activeDropDownList($model_pay, 'status[]',['1'=>'可用','2'=>'不可用'],['class' => 'form-control']) ?></td>
            <td><?= Html::button('删除', ['class' => 'btn btn-danger form-control']) ?></td>
        </tr>

    </tbody>

</table>

    <?php
    $js = <<<EOF
        var spotMax = 10;
        if($(".pay_list").size() >= spotMax) {
          $(obj).hide();
        }
        $("button.add_user").click(function(){

         addSpot(this, spotMax,'pay','pay_list');
        });

        switchBank();

        //银行切换
        function switchBank()
        {

         //支付方式切换
             $(".pay_bank").hide();
             $(".pay_method").change(function(){

                    if ($(this).val() == 3){

                        $(".pay_bank").show();
                        $(".payment_platform").hide();

                    }else{
                        $(".payment_platform").show();
                        $(".pay_bank").hide();


                    }
            });
        }

EOF;

    $this->registerJs($js);
    ?>

</div>
