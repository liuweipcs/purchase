<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\tabs\TabsX;
use kartik\file\FileInput;
use yii\helpers\Url;
use app\services\BaseServices;
use app\services\SupplierServices;
use kartik\select2\Select2;
use kartik\date\DatePicker;
use yii\web\JsExpression;
use app\models\SupplierPaymentAccount;
use app\config\Vhelper;

/* @var $this yii\web\View */
/* @var $model app\models\Stockin */

$this->title=$model->supplier_name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', '供应商列表'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="stockin-view">
    <?php $form = ActiveForm::begin(); ?>
    <h3 class="fa-hourglass-3">基本信息</h3>
    <div class="row">
        
        <div class="col-md-2"><?= $form->field($model, 'supplier_name')->textInput(['maxlength' => true,'placeholder'=>'易佰网络', 'readonly'=>'readonly']) ?></div>

        <div class="col-md-2"><?= $form->field($model, 'supplier_level')->dropDownList(\app\services\SupplierServices::getSupplierLevel(), ['prompt' => '请选择供应商等级','disabled'=>'disabled']) ?></div>
        <div class="col-md-2"><?= $form->field($model, 'supplier_type')->dropDownList(\app\services\SupplierServices::getSupplierType(), ['prompt' => '请选择供应商类型','disabled'=>'disabled']) ?></div>
        <div class="col-md-2"> <label>首次合作时间</label>
            <?=
            DatePicker::widget([
                'name' => 'Supplier[first_cooperation_time]',
                'options' => ['placeholder' => ''],
                'value' => !empty($model->first_cooperation_time)?date('Y-m-d',strtotime($model->first_cooperation_time)):date('Y-m-d',time()),
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd',
                    'todayHighlight' => true
                ]
                ,'disabled'=>'disabled'
            ])
        ?></div>
        <!--<div class="col-md-4"><?/*= $form->field($model, 'merchandiser')->dropDownList(BaseServices::getEveryOne(), ['prompt' => '请选择跟单员','disabled'=>'disabled']) */?></div>-->
        <div class="col-md-2"><?= $form->field($model, 'store_link')->textInput(['maxlength' => true,'placeholder'=>'','readonly'=>'readonly']) ?></div>
        <div class="col-md-2"><?= $form->field($model, 'province')->dropDownList(BaseServices::getCityList(1),[
                'prompt'=>'--请选择省--',
                'onchange'=>'
            $(".form-group.field-member-area").hide();
            $.post("'.yii::$app->urlManager->createUrl('supplier/sites').'?typeid=1&pid="+$(this).val(),function(data){
                $("select#supplier-city").html(data);
            });',
                'disabled'=>'disabled'
        ]) ?></div>
        <div class="col-md-2"><?= $form->field($model, 'city')->dropDownList(BaseServices::getCityList($model->province),
                [
                    'prompt'=>'--请选择市--',
                    'onchange'=>'
            $(".form-group.field-member-area").show();
            $.post("'.yii::$app->urlManager->createUrl('supplier/sites').'?typeid=2&pid="+$(this).val(),function(data){
                $("select#supplier-area").html(data);
            });',
               'disabled'=>'disabled'
        ]) ?></div>
        <div class="col-md-2"><?= $form->field($model, 'area')->dropDownList(BaseServices::getCityList($model->city),['prompt'=>'--请选择区--','disabled'=>'disabled']) ?></div>
        <div class="col-md-2"><?= $form->field($model, 'supplier_address')->textarea(['maxlength' => true,'readonly'=>'readonly'])->label('详细地址') ?></div>
        <div class="col-md-2"><?= $form->field($model, 'invoice')->dropDownList([1=>'否',2=>'增值税发票',3=>'普票'],['prompt'=>'--是否开票--','disabled'=>'disabled']) ?></div>
        <div class="col-md-4"><?= $form->field($model, 'business_scope')->textarea(['maxlength' => true,'readonly'=>'readonly']) ?></div>
    <?php
    //支付方式

    $pay='
    <table class="table table-hover ">
    <tr>
   <td>'.$form->field($model, "supplier_settlement")->dropDownList(SupplierServices::getSettlementMethod(), ["prompt" => "请选择结算方式",'required'=>true,'disabled'=>true]).'</td>
    <td>'. $form->field($model, "payment_method")->dropDownList(SupplierServices::getDefaultPaymentMethod(), ["prompt" => "请选择支付方式",'required'=>true,'disabled'=>true]).'</td>
    </tr>
</table>';

    if($model->pay) {
        $pay .= "<table class='table table-hover'>
	
                    <thead>
      
	                    <th>编号</th>
                        <th>支付平台</th>
                        <th>主行</th>                   
                       <th>支行</th>
                       <th colspan='2'style='text-align: center'>支行所在区域</th>
                        <td>账户类型	</td>
                        <th>开户名</th>
                        <th>账号</th>
                        <th>到账通知手机号</th>
                        <th>证件号</th>
                        <th>操作</th>
	
                    </thead>";
        $pay .= "<tbody class='pay'>";
        foreach ($model->pay as $k => $v) {
            $pay .=  "<tr class='pay_list'>";
            $pay .='<td>'.$form->field($v, "pay_id[]")->input('text',['value'=>$v->pay_id,'readonly'=>true,'class'=>'form-control pay_id'])->label(false).'</td>';
            $pay .= '<td>'.$form->field($v, "payment_platform[]")->dropDownList(SupplierServices::getPaymentPlatform(),['class'=>'pay_info form-control','value'=>$v->payment_platform,'prompt'=>'请选择','required'=>in_array($model->payment_method,[3,5])?true:false,'disabled'=>true])->label(false).'</td>';
            '</td>';
            $pay .= '<td>'.$form->field($v, "payment_platform_bank[]")->dropDownList(\app\models\UfxFuiou::getMasterBankInfo(),['class'=>'pay_info form-control','prompt'=>'请选择','value'=>!empty($v->payment_platform_bank)?$v->payment_platform_bank:'','required'=>in_array($model->payment_method,[3,5])?true:false,'disabled'=>true])->label(false);
            $pay .= '<td>'.$form->field($v, "payment_platform_branch[]")->textInput(['class'=>'pay_info form-control','value'=> $v->payment_platform_branch,'placeholder'=>'请录入支行名称','required'=>in_array($model->payment_method,[3,5])?true:false,'readonly'=>true])->label(false).'</td>';
            $pay .= '<td>'.$form->field($v, "prov_code[]")->dropDownList(\app\models\UfxFuiou::getProvInfo(),['class' => 'form-control pay_info prov','value'=> $v->prov_code,'required'=>in_array($model->payment_method,[3,5])?true:false,'disabled'=>true])->label(false).'</td>';
            $pay .= '<td>'.$form->field($v, "city_code[]")->dropDownList(\app\models\UfxFuiou::getCityInfo(),['class' => 'form-control  pay_info city','value'=> $v->city_code,'required'=>in_array($model->payment_method,[3,5])?true:false,'disabled'=>true])->label(false).'</td>';
            $pay .=  '<td>'.$form->field($v, "account_type[]")->dropDownList(['1'=>'对公','2'=>'对私'],['class' => 'form-control pay_info','value'=>$v->account_type,'prompt'=>'请选择','required'=>in_array($model->payment_method,[3,5])?true:false,'disabled'=>true])->label(false).'</td>';
            $pay .='<td>'.$form->field($v, "account_name[]")->textInput(['class'=>'pay_info form-control',"maxlength" => true,"placeholder"=>"",'value'=>$v->account_name,'required'=>in_array($model->payment_method,[3,5])?true:false,'readonly'=>true])->label(false).'</td>';
            $pay .='<td>'.$form->field($v, "account[]")->textInput(['class'=>'pay_info form-control',"maxlength" => true,"placeholder"=>"",'value'=>$v->account,'required'=>in_array($model->payment_method,[3,5])?true:false,'readonly'=>true])->label(false).'</td>';
            $pay .= '<td>'.$form->field($v, "phone_number[]")->textInput(['class'=>'pay_info form-control',"maxlength" => true,"placeholder"=>"手机号",'value'=>$v->phone_number,'required'=>in_array($model->payment_method,[3,5])?true:false,'readonly'=>true])->label(false).'</td>';
            $pay .= '<td>'.$form->field($v, "id_number[]")->textInput(['class'=>'pay_info form-control',"maxlength" => true,"placeholder"=>"证件号",'value'=>$v->id_number,'required'=>in_array($model->payment_method,[3,5])?true:false,'readonly'=>true])->label(false).'</td>';
            $pay.='</tr>';
        }

        $pay .= '   </tbody>
    </table>';
    }else {
        $payModel = new SupplierPaymentAccount();
        $pay='
    <table class="table table-hover ">
    <tr>
   <td>'.$form->field($model, "supplier_settlement")->dropDownList(SupplierServices::getSettlementMethod(), ["prompt" => "请选择结算方式",'disabled'=>true]).'</td>
    <td>'. $form->field($model, "payment_method")->dropDownList(SupplierServices::getDefaultPaymentMethod(), ["prompt" => "请选择支付方式",'disabled'=>true]).'</td>
 </tr>
</table>
    <table class="table table-hover ">
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
        </tr>
        </thead>
        <tbody class="pay">
        <tr class="pay_list ">
            <td>'.$form->field($payModel, "payment_platform[]")->dropDownList(SupplierServices::getPaymentPlatform(),['class' => 'form-control payment_platform pay_info check_pay','prompt'=>'请选择','disabled'=>true])->label(false).'</td><td>'.
            $form->field($payModel, "payment_platform_bank[]")->dropDownList(\app\models\UfxFuiou::getMasterBankInfo(),['class' => 'form-control pay_info pay_bank check_pay','prompt'=>'请选择','disabled'=>true])->label(false).'</td><td>'

            .$form->field($payModel, "payment_platform_branch[]")->textInput(['class' => 'form-control pay_info','placeholder'=>'请录入支行名称','readonly'=>true])->label(false).'</td>
             <td>'.$form->field($payModel, "prov_code[]")->dropDownList(\app\models\UfxFuiou::getProvInfo(),['class' => 'form-control prov pay_info','prompt'=>'请选择省','disabled'=>true])->label(false).'</td>
             <td>'.$form->field($payModel, "city_code[]")->dropDownList([],['class' => 'form-control city pay_info','prompt'=>'请选择市','disabled'=>true])->label(false).'</td>
             <td>'.$form->field($payModel, "account_type[]")->dropDownList(['1'=>'对公','2'=>'对私'],['class' => 'form-control account_type pay_info','prompt'=>'请选择','disabled'=>true])->label(false).'</td>
            <td>'.$form->field($payModel, "account_name[]")->textInput(['class'=>'pay_info form-control',"maxlength" => true,"placeholder"=>"",'readonly'=>true])->label(false).'</td>
            <td>'.$form->field($payModel, "account[]")->textInput(['class'=>'pay_info form-control',"maxlength" => true,"placeholder"=>"",'readonly'=>true])->label(false).'</td>
            <td>'.$form->field($payModel, "phone_number[]")->textInput(['class'=>'pay_info form-control',"maxlength" => true,"placeholder"=>"手机号码",'readonly'=>true])->label(false).'</td>
            <td>'.$form->field($payModel, "id_number[]")->textInput(['class'=>'pay_info form-control','title'=>'对私账户为收款人身份证号,对公账户为收款公司组织机构代码',"maxlength" => true,"placeholder"=>"对私账户为收款人身份证号,对公账户为收款公司组织机构代码",'readonly'=>true])->label(false).'</td>
 </tbody> 

    </table>
    ';
    }

    //采购员开始
    $supplier_buyer = \app\models\SupplierBuyer::find()->andFilterWhere(['supplier_code'=>$model->supplier_code,'status'=>1])->select('type,buyer')->indexBy('type')->asArray()->all();
    $inbuyer = isset($supplier_buyer[1])&&!empty($supplier_buyer[1]) ? 'checked="checked"' : '';
    $inbuyername = isset($supplier_buyer[1])&&!empty($supplier_buyer[1]) ? $supplier_buyer[1]['buyer'] : '';
    $hwcbuyer = isset($supplier_buyer[2])&&!empty($supplier_buyer[2]) ? 'checked="checked"' : '';
    $hwcbuyername = isset($supplier_buyer[2])&&!empty($supplier_buyer[2]) ? $supplier_buyer[2]['buyer'] : '';
    $FBAbuyer = isset($supplier_buyer[3])&&!empty($supplier_buyer[3]) ? 'checked="checked"' : '';
    $FBAbuyername = isset($supplier_buyer[3])&&!empty($supplier_buyer[3]) ? $supplier_buyer[3]['buyer']: '';
    $model_buyer = new \app\models\SupplierBuyer();
    $inbuyerhtml = $inbuyername;
    $hwcbuyerhtml =$hwcbuyername;
    $FBAbuyerhtml = $FBAbuyername;
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
                <td><input type="checkbox" name="SupplierBuyer[type][]" value=1 '.$inbuyer.' disabled="disabled"></td>
                <td>国内仓</td>
                <td>'.$inbuyerhtml.'
                </td>
            </tr>
            <tr class="buyer_list ">
                <td><input type="checkbox" name="SupplierBuyer[type][]" value=2 '.$hwcbuyer.' disabled="disabled"></td>
                <td>海外仓</td>
                <td>'.$hwcbuyerhtml.'
                </td>
            </tr>
            <tr class="buyer_list ">
                <td><input type="checkbox"  class="FBABuyer" name="SupplierBuyer[type][]" value=3 '.$FBAbuyer.' disabled="disabled"></td>
                <td>FBA</td>
                <td>'.$FBAbuyerhtml.'
                </td>
            </tr>
        </tbody>
    </table>';
    //采购员结束

    $modelline = new  \app\models\SupplierProductLine();
    if(empty($model->line)){
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
                    <td>'.$form->field($modelline, "first_product_line[]")->dropDownList(BaseServices::getProductLine(),['class' => 'form-control ','prompt'=>'选择一级产品线','disabled'=>'disabled',
                'onclick'=>'
                        $.post("'.yii::$app->urlManager->createUrl('supplier/line').'?pid="+$(this).val(),function(data){
                            $(".second").html(data);
                            $(".second").trigger("click");
                        });',
            ])->label('').'</td>
                    <td>'.$form->field($modelline, "second_product_line[]")->dropDownList(BaseServices::getProductLineList($modelline->first_product_line),['class' => 'form-control second ','prompt'=>'选择二级产品线','disabled'=>'disabled',
                'onclick'=>'
                        $.post("'.yii::$app->urlManager->createUrl('supplier/line').'?pid="+$(this).val(),function(data){
                            $(".third").html(data);
                        });',])->label('').'</td>
                    <td>'.$form->field($modelline, "third_product_line[]")->dropDownList(BaseServices::getProductLineList($modelline->second_product_line),['class' => 'form-control third','prompt'=>'选择三级产品线','disabled'=>'disabled'])->label('').'</td>
        </tbody>

    </table>';
    }else{
        $line='
    <table class="table table-hover ">
        <thead>
        <tr>
           <th>一级产品线</th>
            <th>二级产品线</th>
            <th>三级产品线</th>
        </tr>
        </thead>
        <tbody class="line">';
        foreach($model->line as $linevalue){
            $line.='<tr class="line_list ">
                    <td>'.$form->field($linevalue, "first_product_line[]")->dropDownList(BaseServices::getProductLine(),['class' => 'form-control ','value'=>$linevalue->first_product_line,'disabled'=>'disabled',
                    'onclick'=>'
                        var second = $(this).closest("tr").find(".second");
                        $.post("'.yii::$app->urlManager->createUrl('supplier/line').'?pid="+$(this).val(),function(data){
                            second.html(data);
                            second.trigger("click");
                        });',
                ])->label('').'</td>
                    <td>'.$form->field($linevalue, "second_product_line[]")->dropDownList(BaseServices::getProductLineList($linevalue->first_product_line),['class' => 'form-control second ','value'=>$linevalue->second_product_line,'disabled'=>'disabled',
                    'onclick'=>'
                        var third = $(this).closest("tr").find(".third");
                        $.post("'.yii::$app->urlManager->createUrl('supplier/line').'?pid="+$(this).val(),function(data){
                            third.html(data);
                        });',])->label('').'</td>
                    <td>'.$form->field($linevalue, "third_product_line[]")->dropDownList(BaseServices::getProductLineList($linevalue->second_product_line),['class' => 'form-control third','value'=>$linevalue->third_product_line,'disabled'=>'disabled'])->label('').'</td>
                    </tr>';
        }
        $line.='</tbody>
    </table>';
    }

    //结束
    //联系我们
    if($model->contact)
    {
        //联系我们
        $contact="
    <table class='table table-hover'>

        <thead>
        <tr>

            <th>联系人</th>
            <th>法人代表</th>
            <th>联系电话</th>
            <th>中文联系地址</th>
            <th>QQ</th>
            <th>微信</th>
            <th>邮箱</th>
            <th>旺旺</th>
        </tr>
        </thead>";
        $contact.="<tbody class='contact' >";
        if($model->contact)
        {
            foreach ($model->contact as $k => $v)
            {

                $contact.= "<tr class='contact_list' >";
                $contact.= '<td > '.$form->field($model, "contact_person")->textInput(['class' => 'form-control ','placeholder'=>'联系人','value'=>!empty($v->contact_person)?$v->contact_person:'','name'=>"SupplierContactInformation[$k][contact_person]",'readonly'=>'readonly'])->label('').' </td >';
                $contact.= '<td > '.$form->field($model, "corporate")->textInput(['class' => 'form-control ','placeholder'=>'法人代表','value'=>!empty($v->corporate)?$v->corporate:'','name'=>"SupplierContactInformation[$k][corporate]",'readonly'=>'readonly'])->label('').' </td >';

                $contact.= '<td > '.$form->field($model, "contact_number")->textInput(['class' => 'form-control ','max'=>"20",'placeholder'=>'联系电话','value'=>!empty($v->contact_number)?$v->contact_number:'','name'=>"SupplierContactInformation[$k][contact_number]",'readonly'=>'readonly'])->label('').' </td >';
                $contact.='<td > '.$form->field($model, "chinese_contact_address")->textInput(['class' => 'form-control ','placeholder'=>'中文联系地址','value'=>!empty($v->chinese_contact_address)?$v->chinese_contact_address:'','name'=>"SupplierContactInformation[$k][chinese_contact_address]",'readonly'=>'readonly'])->label('').' </td >';
                $contact.='<td > '.$form->field($model, "qq")->textInput(['class' => 'form-control ','placeholder'=>'QQ','max'=>"20",'value'=>!empty($v->qq)?$v->qq:'','name'=>"SupplierContactInformation[$k][qq]",'readonly'=>'readonly'])->label('').' </td >';
                $contact.='<td > '.$form->field($model, "micro_letter")->textInput(['class' => 'form-control ','placeholder'=>'微信','max'=>"20",'value'=>!empty($v->micro_letter)?$v->micro_letter:'','name'=>"SupplierContactInformation[$k][micro_letter]",'readonly'=>'readonly'])->label('').' </td >';
                $contact.='<td > '.$form->field($model, "email")->textInput(['class' => 'form-control ','placeholder'=>'邮箱','max'=>"20",'value'=>!empty($v->email)?$v->email:'','name'=>"SupplierContactInformation[$k][email]",'readonly'=>'readonly'])->label('').' </td >';
                $contact.='<td > '.$form->field($model, "want_want")->textInput(['class' => 'form-control ','placeholder'=>'旺旺','max'=>"20",'value'=>!empty($v->want_want)?$v->want_want:'','name'=>"SupplierContactInformation[$k][want_want]",'readonly'=>'readonly'])->label('').' </td >';
                $contact .= '</tr>';
            }
        }else{
            $contact.= "<tr class='contact_list' >";
            $contact.= '<td > '.$form->field($model, "contact_person[]")->textInput(['class' => 'form-control ','placeholder'=>'联系人','value'=>!empty($model->contact_person)?$model->contact_person:'','name'=>"SupplierContactInformation[contact_person][]",'disabled'=>'disabled'])->label('').' </td >';
            $contact.= '<td > '.$form->field($model, "corporate[]")->textInput(['class' => 'form-control ','placeholder'=>'法人代表','value'=>!empty($model->corporate)?$model->corporate:'','name'=>"SupplierContactInformation[corporate][]",'disabled'=>'disabled'])->label('').' </td >';

            $contact.= '<td > '.$form->field($model, "contact_number[]")->textInput(['class' => 'form-control ','max'=>"20",'placeholder'=>'联系电话','value'=>!empty($model->contact_number)?$model->contact_number:'','name'=>"SupplierContactInformation[contact_number][]",'disabled'=>'disabled'])->label('').' </td >';
            $contact.='<td > '.$form->field($model, "chinese_contact_address[]")->textInput(['class' => 'form-control ','placeholder'=>'中文联系地址','value'=>!empty($model->chinese_contact_address)?$model->chinese_contact_address:'','name'=>"SupplierContactInformation[chinese_contact_address][]",'disabled'=>'disabled'])->label('').' </td >';
            $contact.='<td > '.$form->field($model, "qq[]")->textInput(['class' => 'form-control ','placeholder'=>'QQ','max'=>"20",'value'=>!empty($model->qq)?$model->qq:'','name'=>"SupplierContactInformation[qq][]",'disabled'=>'disabled'])->label('').' </td >';
            $contact.='<td > '.$form->field($model, "micro_letter[]")->textInput(['class' => 'form-control ','placeholder'=>'微信','max'=>"20",'value'=>!empty($model->micro_letter)?$model->micro_letter:'','name'=>"SupplierContactInformation[micro_letter][]",'disabled'=>'disabled'])->label('').' </td >';
            $contact.='<td > '.$form->field($model, "email[]")->textInput(['class' => 'form-control ','placeholder'=>'邮箱','max'=>"20",'value'=>!empty($model->email)?$model->email:'','name'=>"SupplierContactInformation[email][]"])->label('').' </td >';
            $contact.='<td > '.$form->field($model, "want_want[]")->textInput(['class' => 'form-control ','placeholder'=>'旺旺','max'=>"20",'value'=>!empty($model->want_want)?$model->want_want:'','name'=>"SupplierContactInformation[want_want][]"])->label('').' </td >';


            $contact .= '</tr>';
        }
        $contact.= '</tbody></table>';
//结束*/
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
           'label'=>'<i class="glyphicon glyphicon-user"></i> 联系方式',
           //'content'=>$this->render('_contact',['model_pay'=>$model_contact]),
           'content'=>!empty($contact)?$contact:'',

       ],
        [
            'label'=>'<i class="glyphicon glyphicon-yen"></i> 支付方式',
            //'content'=>$this->render('_pay',['model_pay'=>$model_pay]),
            'content'=> !empty($pay)?$pay:'',

        ],
       [
           'label'=>'<i class="glyphicon glyphicon-yen"></i> 产品线',
           //'content'=>$this->render('_pay',['model_pay'=>$model_pay]),
           'content'=> $line,

       ],
       [
           'label'=>'<i class="glyphicon glyphicon-buyer"></i> 采购员(部门必选)',
           //'content'=>$this->render('_pay',['model_pay'=>$model_pay]),
           'content'=> $buyer,

       ],
        [
            'label'=>'<i class="glyphicon glyphicon-book"></i> 合同注意事项',
            'content'=>$form->field($model,'contract_notice')->textarea(['col'=>10,'rows'=>5,'readonly'=>"readonly"]),

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

