<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Stockin */
//账号信息
// 要删除的联系方式
$supplier_contact_delete = isset($auditInfo['supplier_contact_delete'])?$auditInfo['supplier_contact_delete']:'';
$supplier_contact_delete = explode(',',$supplier_contact_delete);

if (!empty($auditInfo['supplier_contact_information'])) {
    foreach ($model_contact as $mk => $mv) {
        if (!empty($auditInfo['supplier_contact_information'][$mv->contact_id])) {
            foreach ($auditInfo['supplier_contact_information'][$mv->contact_id] as $ak => $av) {
                 $mv->$ak = $av;
                 echo '<style type="text/css" media="screen">';
                     echo '.field-suppliercontactinformation-'.$ak.' input{
                        color:red
                     }';
                 echo '</style>';
            }
        }
    }
}


?>
<div class="stockin-update">
    <table class="table table-hover ">
        <div class="col-md-2"><?=Html::button('添加联系人', ['class' => 'btn btn-success add_contact']);?></div>
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
        <?php if($model->isNewRecord){?>
            <tr class="contact_list contact-person">
                <?= $form->field($model_contact, 'contact_id[]')->textInput()->hiddenInput()->label(false); ?>
                <td><?=$form->field($model_contact, "contact_person[]")->textInput(['class' => 'form-control ','placeholder'=>'联系人','required'=>true])->label('');?></td>
                <td><?=$form->field($model_contact, "corporate[]")->textInput(['class' => 'form-control ','placeholder'=>'法人代表','max'=>"100",'required'=>true])->label('');?></td>
                <td><?=$form->field($model_contact, "contact_number[]")->textInput(['class' => 'form-control ','placeholder'=>'联系电话','max'=>"20",'required'=>true])->label('');?></td>
                <td><?=$form->field($model_contact, "chinese_contact_address[]")->textInput(['class' => 'form-control ','placeholder'=>'发货地址','required'=>true])->label('');?></td>
                <td><?=$form->field($model_contact, "qq[]")->textInput(['class' => 'form-control ','placeholder'=>'QQ','max'=>"20"])->label('');?></td>
                <td><?=$form->field($model_contact, "micro_letter[]")->textInput(['class' => 'form-control ','placeholder'=>'微信','max'=>"20"])->label('');?></td>
                <td><?=$form->field($model_contact, "email[]")->input('email',['class' => 'form-control ','placeholder'=>'邮箱','max'=>"255"])->label('') ;?></td>
                <td><?=$form->field($model_contact, "want_want[]")->textInput(['class' => 'form-control ','placeholder'=>'旺旺','max'=>"20",'required'=>true])->label('');?></td>
                <td><?=Html::button('删除', ['class' => 'btn btn-danger form-control','style'=>'margin-top:20px;']);?></td>
            </tr>
        <?php }else{?>
            <?php if(isset($model_contact->isNewRecord) AND $model_contact->isNewRecord == true){
                if(empty($is_audit)){ ?>
                    <tr class="contact_list contact-person">
                        <?= $form->field($model_contact, 'contact_id[]')->textInput()->hiddenInput()->label(false); ?>
                        <td><?= $form->field($model_contact, "contact_person[]")->textInput(['class' => 'form-control ', 'placeholder' => '联系人', 'required' => true])->label(''); ?></td>
                        <td><?= $form->field($model_contact, "corporate[]")->textInput(['class' => 'form-control ', 'placeholder' => '法人代表', 'max' => "100", 'required' => true])->label(''); ?></td>
                        <td><?= $form->field($model_contact, "contact_number[]")->textInput(['class' => 'form-control ', 'placeholder' => '联系电话', 'max' => "20", 'required' => true])->label(''); ?></td>
                        <td><?= $form->field($model_contact, "chinese_contact_address[]")->textInput(['class' => 'form-control ', 'placeholder' => '发货地址', 'required' => true])->label(''); ?></td>
                        <td><?= $form->field($model_contact, "qq[]")->textInput(['class' => 'form-control ', 'placeholder' => 'QQ', 'max' => "20"])->label(''); ?></td>
                        <td><?= $form->field($model_contact, "micro_letter[]")->textInput(['class' => 'form-control ', 'placeholder' => '微信', 'max' => "20"])->label(''); ?></td>
                        <td><?= $form->field($model_contact, "email[]")->input('email', ['class' => 'form-control ', 'placeholder' => '邮箱', 'max' => "255"])->label(''); ?></td>
                        <td><?= $form->field($model_contact, "want_want[]")->textInput(['class' => 'form-control ', 'placeholder' => '旺旺', 'max' => "20", 'required' => true])->label(''); ?></td>
                        <td><?= Html::button('删除', ['class' => 'btn btn-danger form-control', 'style' => 'margin-top:20px;']); ?></td>
                    </tr>
                    <?php
                }
            }else{
                foreach ($model_contact as $contact){
                    $color = ($is_audit AND in_array($contact->contact_id,$supplier_contact_delete))?'#F7FFC4':'';
                    ?>
                    <tr class="contact_list contact-person" style="background-color:<?= $color?>">
                        <input type="hidden" id="suppliercontactinformation-contact_id" class="form-control " name="SupplierContactInformation[contact_id][]" value="<?=$contact->contact_id?>">
                        <td><?=$form->field($contact, "contact_person[]")->textInput(['value'=>$contact->contact_person,'class' => 'form-control ','placeholder'=>'联系人','required'=>true])->label('');?></td>
                        <td><?=$form->field($contact, "corporate[]")->textInput(['value'=>$contact->corporate,'class' => 'form-control ','placeholder'=>'法人代表','max'=>"100",'required'=>true])->label('');?></td>
                        <td><?=$form->field($contact, "contact_number[]")->textInput(['value'=>$contact->contact_number,'class' => 'form-control ','placeholder'=>'联系电话','max'=>"20",'required'=>true])->label('');?></td>
                        <td><?=$form->field($contact, "chinese_contact_address[]")->textInput(['value'=>$contact->chinese_contact_address,'class' => 'form-control ','placeholder'=>'发货地址','required'=>true])->label('');?></td>
                        <td><?=$form->field($contact, "qq[]")->textInput(['value'=>$contact->qq,'class' => 'form-control ','placeholder'=>'QQ','max'=>"20"])->label('');?></td>
                        <td><?=$form->field($contact, "micro_letter[]")->textInput(['value'=>$contact->micro_letter,'class' => 'form-control ','placeholder'=>'微信','max'=>"20"])->label('');?></td>
                        <td><?=$form->field($contact, "email[]")->input('email',['value'=>$contact->email,'class' => 'form-control ','placeholder'=>'邮箱','max'=>"255"])->label('') ;?></td>
                        <td><?=$form->field($contact, "want_want[]")->textInput(['value'=>$contact->want_want,'class' => 'form-control ','placeholder'=>'旺旺','max'=>"20",'required'=>true])->label('');?></td>
                        <td><?=Html::button('删除', ['class' => 'btn btn-danger form-control delete_cache_contact_id','style'=>'margin-top:20px;']);?></td>
                    </tr>
            <?php } } ?>
            
            <?php 
            if (!empty($auditInfo['supplier_contact_information_insert'])) {
                foreach ($auditInfo['supplier_contact_information_insert'] as $key => $value) {
            ?>
            <tr class="contact_list contact-person">
            <td><div class="form-control" readonly="readonly" style="color:red"><?=!empty($value['contact_person'])?$value['contact_person']:'';?></div></td>
            <td><div class="form-control" readonly="readonly" style="color:red"><?=!empty($value['corporate'])?$value['corporate']:'';?></div></td>
            <td><div class="form-control" readonly="readonly" style="color:red"><?=!empty($value['contact_number'])?$value['contact_number']:'';?></div></td>
            <td><div class="form-control" readonly="readonly" style="color:red"><?=!empty($value['chinese_contact_address'])?$value['chinese_contact_address']:'';?></div></td>
            <td><div class="form-control" readonly="readonly" style="color:red"><?=!empty($value['qq'])?$value['qq']:'';?></div></td>
            <td><div class="form-control" readonly="readonly" style="color:red"><?=!empty($value['micro_letter'])?$value['micro_letter']:'';?></div></td>
            <td><div class="form-control" readonly="readonly" style="color:red"><?=!empty($value['email'])?$value['email']:'';?></div></td>
            <td><div class="form-control" readonly="readonly" style="color:red"><?=!empty($value['want_want'])?$value['want_want']:'';?></div></td>
            </tr>

                <?php }
            } ?>

        <?php }?>

        </tbody>

    </table>
</div>
    <div>
        <?= \yii\helpers\Html::button('上一步',['class'=>'btn btn-info','id'=>'supplier_contact_info_up'])?>
        <?= \yii\helpers\Html::button('下一步',['class'=>'btn btn-warning','id'=>'supplier_contact_info_next'])?>
    </div>
<?php
$js = <<<JS
$(function () {
  $('#supplier_contact_info_up').on('click',function() {
      $('#pay_info_li').tab('show');
      $('#pay_info_content').addClass('active in');
      $('#contact_info_content').removeClass('active in');
  });
  $('#supplier_contact_info_next').on('click',function() {
      var contact_error=0;
      $('.contact-person').each(function() {
        var qq = $(this).find('[name="SupplierContactInformation[qq][]"]').val();
        var wechart = $(this).find('[name="SupplierContactInformation[micro_letter][]"]').val();
        var email = $(this).find('[name="SupplierContactInformation[email][]"]').val();
        if( isnull($(this).find('[name="SupplierContactInformation[contact_person][]"]').val()) ){
            layer.msg('联系人不能为空');
            contact_error++;
            return false;
        }
        if( isnull($(this).find('[name="SupplierContactInformation[corporate][]"]').val()) ){
            layer.msg('法人代表不能为空');
            contact_error++;
            return false;
        }
        if( isnull($(this).find('[name="SupplierContactInformation[contact_number][]"]').val()) ){
            layer.msg('联系电话不能为空');
            contact_error++;
            return false;
        }
        if( isnull($(this).find('[name="SupplierContactInformation[chinese_contact_address][]"]').val()) ){
            layer.msg('发货地址不能为空');
            contact_error++;
            return false;
        }
        if(email==''&&qq==''&&wechart==''){
            layer.msg('每个联系方式的微信QQ邮箱三者至少填写一个');
            contact_error++;
            return false;
        }
        if( isnull($(this).find('[name="SupplierContactInformation[want_want][]"]').val()) ){
            layer.msg('旺旺不能为空');
            contact_error++;
            return false;
        }
        if(email){
            var pattern= /^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
            if (!pattern.test(email)){ 
                layer.msg('邮箱格式不正确');
                contact_error++;
                return false;
            }
        }
        
      });
      if(contact_error>=1){
          return false;
      }
      $('#buyer_info_li').tab('show');
      $('#buyer_info_content').addClass('active in');
      $('#contact_info_content').removeClass('active in');
  });

  function isnull(val) {
        if (val==null) {
            return false;
        }
        var str = val.replace(/(^\s*)|(\s*$)/g, '');//去除空格;
        if (str == '' || str == undefined || str == null) {
            return true;
            console.log('空')
        } else {
            return false;
            console.log('非空');
        }
    }
});
JS;
$this->registerJs($js);
?>