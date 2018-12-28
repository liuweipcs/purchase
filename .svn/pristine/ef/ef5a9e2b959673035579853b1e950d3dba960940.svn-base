<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\file\FileInput;
use app\models\User;
use kartik\tabs\TabsX;

/* @var $this yii\web\View */
/* @var $model app\models\Stockin */

$this->title=$model->supplier_name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', '供应商列表'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="stockin-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', '更新'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
       <!-- --><?/*= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->stockin_id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) */?>
    </p>
    <?php $form = ActiveForm::begin(); ?>
    <h3 class="fa-hourglass-3">基本信息</h3>
    <div class="row">


        <div class="col-md-4"><?= $form->field($model, 'supplier_name')->textInput(['maxlength' => true,'placeholder'=>'易佰网络', 'disabled'=>'disabled']) ?></div>

        <div class="col-md-4"><?= $form->field($model, 'supplier_level')->dropDownList(\app\services\SupplierServices::getSupplierLevel(), ['prompt' => '请选择供应商等级','disabled'=>'disabled']) ?></div>
        <div class="col-md-4"><?= $form->field($model, 'esupplier_name')->textInput(['maxlength' => true,'placeholder'=>'yibai network','disabled'=>'disabled']) ?></div>
        <div class="col-md-4"><?= $form->field($model, 'cooperation_type')->dropDownList(\app\services\SupplierServices::getCooperation(), ['prompt' => '请选择合作类型','disabled'=>'disabled']) ?></div>
        <div class="col-md-4"><?= $form->field($model, 'main_category')->dropDownList(\app\services\SupplierServices::getCooperation(), ['prompt' => '请选择主营品类','disabled'=>'disabled']) ?></div>
        <div class="col-md-4"><?= $form->field($model, 'buyer')->dropDownList(\app\services\BaseServices::getEveryOne(), ['prompt' => '请选择采购员','disabled'=>'disabled']) ?></div>
        <div class="col-md-4"><?= $form->field($model, 'supplier_type')->dropDownList(\app\services\SupplierServices::getSupplierType(), ['prompt' => '请选择供应商类型','disabled'=>'disabled']) ?></div>
        <div class="col-md-4"><?= $form->field($model, 'merchandiser')->dropDownList(\app\services\BaseServices::getEveryOne(), ['prompt' => '请选择跟单员','disabled'=>'disabled']) ?></div>
        <div class="col-md-4"><?= $form->field($model, 'payment_cycle')->dropDownList(\app\services\SupplierServices::getPaymentCycle(), ['prompt' => '请选择支付周期类型','disabled'=>'disabled']) ?></div>
        <div class="col-md-4"><?= $form->field($model, 'supplier_settlement')->dropDownList(\app\services\SupplierServices::getSettlementMethod(), ['prompt' => '请选择结算方式','disabled'=>'disabled']) ?></div>
        <div class="col-md-4"><?= $form->field($model, 'payment_method')->dropDownList(\app\services\SupplierServices::getDefaultPaymentMethod(), ['prompt' => '请选择支付方式','disabled'=>'disabled']) ?></div>
        <div class="col-md-4"><?= $form->field($model, 'transport_party')->dropDownList(\app\services\SupplierServices::getTransportParty(), ['prompt' => '请选择运输承担方','disabled'=>'disabled']) ?></div>
        <div class="col-md-4"><?= $form->field($model, 'product_handling')->dropDownList(\app\services\SupplierServices::getBadProductHandling(), ['prompt' => '请选择不良品处理方式','disabled'=>'disabled']) ?></div>
        <div class="col-md-4"><?= $form->field($model, 'commission_ratio')->textInput(['maxlength' => true,'disabled'=>'disabled']) ?></div>
        <div class="col-md-4"><?= $form->field($model, 'purchase_amount')->textInput(['maxlength' => true,'placeholder'=>'RMB','disabled'=>'disabled']) ?></div>
    </div>
    <?php
   //支付方式
    if($model->pay)
    {

        $pay ="<table class='table table-hover'>
                    <thead>
                    <tr>
                        <th>支付方式</th>
                        <th>支行/平台</th>
                        <th>账户</th>
                        <th>账户名</th>
                        <th>状态</th>
                    </tr>
                    </thead><tbody class='pay'>";

        foreach($model->pay as $k=> $v)
        {
            $pay.= "
                    <tr class='pay_list'>";
            $pay.= '<td><input type="text" id="supplier-supplier_name" class="form-control" name="Supplier[supplier_name]" value="'.app\services\SupplierServices::getDefaultPaymentMethod($v->payment_method).'" disabled="disabled" maxlength="30"  aria-required="true"></td>';
            $pay.= '<td><input type="text" id="supplier-supplier_name" class="form-control" name="Supplier[supplier_name]" value="'.app\services\SupplierServices::getPaymentPlatform($v->payment_platform).'" disabled="disabled" maxlength="30"  aria-required="true"></td>';
            $pay.= '<td><input type="text" id="supplier-supplier_name" class="form-control" name="Supplier[supplier_name]" value="'.$v->account.'" disabled="disabled" maxlength="30"  aria-required="true"></td>';
            $pay.= '<td><input type="text" id="supplier-supplier_name" class="form-control" name="Supplier[supplier_name]" value="'.$v->account_name.'" disabled="disabled" maxlength="30"  aria-required="true"></td>';
            $pay.= '<td><input type="text" id="supplier-supplier_name" class="form-control" name="Supplier[supplier_name]" value="'.app\services\SupplierServices::getStatus($v->status).'" disabled="disabled" maxlength="30"  aria-required="true"></td>';


            $pay.='</tr>';



        }
        $pay.='</tbody></table>';


    }

    //结束
    //联系我们
    if($model->contact)
    {
        $contact = "
    <table class='table table-hover '>

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

        </tr>
        </thead><tbody class='contact'>";
        foreach ($model->contact as $k => $v) {
            $contact .= "
            <tr class='contact_list'>";

            $contact .= '<td><input type="text"  class="form-control"  value="' . $v->contact_person . '" disabled="disabled" maxlength="30"  aria-required="true"></td>';
            $contact .= '<td><input type="text"  class="form-control"  value="' . $v->contact_number . '" disabled="disabled" maxlength="30"  aria-required="true"></td>';
            $contact .= '<td><input type="text"  class="form-control"  value="' . $v->contact_fax . '" disabled="disabled" maxlength="30"  aria-required="true"></td>';
            $contact .= '<td><input type="text"  class="form-control"  value="' . $v->chinese_contact_address . '" disabled="disabled" maxlength="30"  aria-required="true"></td>';
            $contact .= '<td><input type="text"  class="form-control"  value="' . $v->english_address . '" disabled="disabled" maxlength="30"  aria-required="true"></td>';
            $contact .= '<td><input type="text"  class="form-control"  value="' . $v->contact_zip . '" disabled="disabled" maxlength="30"  aria-required="true"></td>';
            $contact .= '<td><input type="text"  class="form-control"  value="' . $v->qq . '" disabled="disabled" maxlength="30"  aria-required="true"></td>';
            $contact .= '<td><input type="text"  class="form-control"  value="' . $v->micro_letter . '" disabled="disabled" maxlength="30"  aria-required="true"></td>';
            $contact .= '<td><input type="text"  class="form-control"  value="' . $v->want_want . '" disabled="disabled" maxlength="30"  aria-required="true"></td>';
            $contact .= '<td><input type="text"  class="form-control"  value="' . $v->skype . '" disabled="disabled" maxlength="30"  aria-required="true"></td>';

            $contact .= '</tr>';
        }

        $contact .= '</tbody></table>';
        //结束
    }
    ?>

    <?php

    $img_url=\yii\helpers\ArrayHelper::toArray($model->img,[
        'app\models\SupplierImages' => [
            'image_url'
        ]
    ]);
    $img_url=\yii\helpers\ArrayHelper::getColumn($img_url,'image_url');
   $items = [
        [
            'label'=>'<i class="glyphicon glyphicon-yen"></i> 支付方式',
            //'content'=>$this->render('_pay',['model_pay'=>$model_pay]),
            'content'=> !empty($pay)?$pay:'',

        ],
        [
            'label'=>'<i class="glyphicon glyphicon-user"></i> 联系方式',
            //'content'=>$this->render('_contact',['model_pay'=>$model_contact]),
            'content'=>!empty($contact)?$contact:'',

        ],
        [
            'label'=>'<i class="glyphicon glyphicon-book"></i> 合同注意事项',
            'content'=>$form->field($model,'contract_notice')->textarea(['col'=>10,'rows'=>5,'disabled'=>"disabled"]),

        ],
       [
            'label'=>'<i class="glyphicon glyphicon-picture"></i> 附属图片',
            'content'=>$form->field($model, 'image_url')->widget(FileInput::classname(), ['options' => ['multiple' => true,],
                'pluginOptions' => [
                    // 需要预览的文件格式
                    'previewFileType' => 'image',
                    // 预览的文件
                    'initialPreview' =>$img_url,
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
                    'showRemove' => false,
                    // 是否显示上传按钮，指input上面的上传按钮，非具体图片上的上传按钮
                    'showUpload' => false,
                    //是否显示[选择]按钮,指input上面的[选择]按钮,非具体图片上的上传按钮
                    'showBrowse' => false,
                    // 展示图片区域是否可点击选择多文件
                    'browseOnZoneClick' => false,
                    // 如果要设置具体图片上的移除、上传和展示按钮，需要设置该选项
                    'fileActionSettings' => [
                        // 设置具体图片的查看属性为false,默认为true
                        'showZoom' => false,
                        // 设置具体图片的上传属性为true,默认为true
                        'showUpload' => false,
                        // 设置具体图片的移除属性为true,默认为true
                        'showRemove' => false,
                    ],
                ],
                // 一些事件行为
                'pluginEvents' => [
                    // 上传成功后的回调方法，需要的可查看data后再做具体操作，一般不需要设置
                    'fileuploaded' => 'function(event, data, previewId, index) {
                        $(event.currentTarget.closest("form")).append(data.response.imgfile);
                    }',
                ],

            ])->label('附属图片'),

        ],

    ];

    echo TabsX::widget([
        'items'=>$items,
        'position'=>TabsX::POS_ABOVE,
        'encodeLabels'=>false
    ]);?>

    <?php ActiveForm::end(); ?>




</div>

