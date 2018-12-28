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
use app\models\SupplierUpdateLog;
use app\config\Vhelper;
/* @var $this yii\web\View */
/* @var $model app\models\Supplier */
/* @var $form yii\widgets\ActiveForm */
$this->title = Yii::t('app', '更新供应商');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', '供应商列表'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$supplier_code = !empty($model->supplier_code)?$model->supplier_code:'';

//判断是对公还是对私
$is_gong_si = '';
if (!empty($model->pay)) {
    $gong_si = [];
    foreach ($model->pay as $key => $value) {
        $gong_si[] = $value['account_type'];
    }
    $is_gong_si = implode(',', $gong_si);
}
if($model->payment_method == '2'){// 付款方式支付宝的只展示 非公非私
    $is_gong_si = ($is_gong_si)?$is_gong_si.',3':3;
}


//区分是erp还是采购系统进来的路由
$updateUrl = $this->context->action->id;
$is_erp = false;// 标记是否是ERP接口
if ($updateUrl == 'erp-update' or $updateUrl == 'erp-create' ) {
    $is_erp = true;
    # 如果是erp
    $action = $updateUrl . '?supplier_code=' . $supplier_code.'&user='.$user.'&num='.$user_number;// ERP接口必须添加权限
} elseif ($is_audit == true) {
    # 如果是审核
    $action = '/supplier/audit-supplier';
} else {
    $action = $updateUrl . '?id=' . $model->id;
}

//审核状态下
$auditInfo = [];
if ($is_audit == true) {
    $auditInfo = SupplierUpdateLog::getCurrentAuditInfo($supplier_code);

    $audit_status = $auditInfo['audit_status'];
    $is_update_bank = $auditInfo['is_update_bank'];
    //对公对私
    if (!empty($auditInfo['supplier_payment_account'])) {
        foreach ($auditInfo['supplier_payment_account'] as $k => $v) {
            if (!empty($v['account_type'])) {
                $is_gong_si .= ','.$v['account_type'];
            }
        }
    }
    if(!empty($auditInfo['supplier']['payment_method'])){// 展示支付宝
        if($auditInfo['supplier']['payment_method'] == 2){
            $is_gong_si .= ',3';
        }
    }
}

if ($is_readonly == true) {
    $is_disabled = '';
} else {
    $is_disabled = 'disabled';
}
?>

<?php $form = ActiveForm::begin([
    'action' =>  ($model->isNewRecord AND $is_erp === false)?['create']:[$action],
    'method' => 'post',
    "options" => ["enctype" => "multipart/form-data",'id'=>'supplier-update']
]); ?>
<input type="hidden" id='is_readonly' name="is_readonly" value="<?=$is_readonly?>">
<input type="hidden" id='is_audit' name="is_audit" value="<?=$is_audit?>">
<!-- 供应商是对公还是对私 -->
<input type="hidden" id='is_gong_si' name="is_gong_si" value="<?=$is_gong_si?>">
<!-- 审核时的对公对私 -->
<input type="hidden" id='audit_is_gong_si' name="audit_is_gong_si" value="<?=$is_gong_si?>">
<input type="hidden" id='supplier_code' name="supplier_code" value="<?=$supplier_code?>">
<input type="hidden" id='supplier_contact_delete' name="supplier_contact_delete" value="">

<?php
$items = [
    [
        'label'=>'<i class="glyphicon glyphicon-list-alt"></i> 基本信息',
        'content'=>$this->render('_base_info',['form'=>$form,'model'=>$model, 'auditInfo'=>$auditInfo]),
        'headerOptions' => ['class'=>$is_disabled,'id'=>'base_info_li'],
        'options'=>['id'=>'base_info_content']
    ],
    [
        'label'=>'<i class="glyphicon glyphicon-yen"></i> 财务结算',
        'content'=>$this->render('_pay',['form'=>$form,'model'=>$model, 'auditInfo'=>$auditInfo,'is_audit'=>$is_audit]),
        'headerOptions' => ['class'=>$is_disabled,'id'=>'pay_info_li'],
        'options'=>['id'=>'pay_info_content']
    ],
    [
        'label'=>'<i class="glyphicon glyphicon-contact"></i> 联系方式',
        'content'=>$this->render('_contact',['form'=>$form,'model'=>$model,'model_contact'=>$model_contact, 'auditInfo'=>$auditInfo,'is_audit'=>$is_audit]),
        'headerOptions' => ['class'=>$is_disabled,'id'=>'contact_info_li'],
        'options'=>['id'=>'contact_info_content']
        // 'content'=>$contact,
        // 'active'=>$view =='update'?false:true

    ],
    [
        'label'=>'<i class="glyphicon glyphicon-buyer"></i> 采购员(部门必选)',
        'content'=>$this->render('_buyer',['form'=>$form,'model'=>$model, 'auditInfo'=>$auditInfo]),
        'headerOptions' => ['class'=>$is_disabled,'id'=>'buyer_info_li'],
        'options'=>['id'=>'buyer_info_content']
        // 'content'=> $buyer,

    ],
    [
        'label'=>'<i class="glyphicon glyphicon-picture"></i> 附属图片',
        'content'=>$this->render('_img',['form'=>$form,'model'=>$model, 'imageModel'=>$imageModel, 'p2'=>$p2, 'view'=>$view, 'is_readonly'=>$is_readonly, 'is_audit'=>$is_audit, 'imagesInfo'=>$imagesInfo, 'auditInfo'=>$auditInfo]),
        'headerOptions' => ['class'=>$is_disabled,'id'=>'img_info_li'],
        'options'=>['id'=>'img_info_content']
        // 'content'=>$images_url
    ],
];

echo TabsX::widget([
    'items'=>$items,
    'position'=>TabsX::POS_ABOVE,
    'encodeLabels'=>false,
]);?>


<?php if ($is_audit == true):?>
   <input class="form-check-input audit_supplier_class" type="radio" name="is_audit" id="pass_id" value="1" checked>
    <label class="form-check-label audit_supplier_class" for="pass_id" style="margin-right: 50px;">通过</label>

    <input class="form-check-input audit_supplier_class" type="radio" name="is_audit" id="overrule_id" value="0">
    <label class="form-check-label audit_supplier_class" for="overrule_id">不通过</label>

    <textarea class="form-control audit_supplier_class" name="audit_note"></textarea>
    <input class="audit_supplier_class" type="hidden" name="audit_status" value="<?=$audit_status?>">
    <input class="audit_supplier_class" type="hidden" name="supplier_code" value="<?=$supplier_code?>">
    <input class="audit_supplier_class" type="hidden" name="is_update_bank" value="<?=$is_update_bank?>">

    <?php
    if($audit_status == 5) {
        echo '<button type="submit" class="btn btn-primary audit_supplier_class_class">待财务审核</button>';
    }else if($audit_status == 3) {
        echo '<button type="submit" class="btn btn-primary audit_supplier_class">待供应链审核</button>';
    } else if($audit_status == 1) {
        echo '<button type="submit" class="btn btn-primary audit_supplier_class">待采购审核</button>';
    }
     ?>
<?php endif; ?>

<?php ActiveForm::end(); ?>


<?php
$cityUrl = Url::toRoute('ufx-fuiou/get-city');

$js = <<<JS

        $(function () {
            $("div.file-upload-indicator").each(function() {// 删除旧的图片的未上传的提示信息
                $(this).attr('title','');
            });
            is_gong_si = $('#is_gong_si').val();
            $(".duigong_img_show").hide();
            $(".duigsi_img_show").hide();
            $(".no_gong_no_si_img_show").hide();
            if (is_gong_si.indexOf("1") != -1) {
                //对公
                $(".duigong_img_show").show();
            }
            if (is_gong_si.indexOf("2") != -1) {
                //对私
                $(".duigsi_img_show").show();
            }
            if (is_gong_si.indexOf("3") != -1) {
                //非公非私
                $(".no_gong_no_si_img_show").show();
            }


            var is_readonly = $('#is_readonly').val(); 
            if (is_readonly == 1) {
                $('form').find('input,textarea,select,text').attr('readonly',true);
                $('form').find('input,textarea,select,text').attr('disabled','disabled');
            }
            
            var is_audit = $('#is_audit').val(); 
            if (is_audit == 1) {
                $('.audit_supplier_class').attr('readonly',false);
                $('.audit_supplier_class').attr('disabled',false);
            }
        });


        var spotMax = 2;
        //支付方式
        if($(".pay_list").size() >= 4) {
          $("button.add_user").hide();
        }
        var branch_click_index = 1; 
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
          $('button.add_contact').hide();
        }
        if($(".contact_list").size() >= spotMax) {
          $("button.add_line").hide();
        }
        $("button.add_contact").click(function(){

         addSpot(this, spotMax,'contact','contact_list');
        });
        $("button.btn-danger").click(function(){
            //保留最后一个不让删除
            if($(".pay_list").size() > 1)
            {
                $(this).parent().parent().remove();
            }
            if($(".pay_list").size() < spotMax)
            {
                $("button.add_user").show();
            }
            if($(".line_list").size() < spotMax)
            {
                $("button.add_line").show();
            }
            //保留最后一个不让删除

            if($(".contact_list").size() > 1)
            {
                var delete_id = $(this).parent().parent().children('input').eq(0).val();
                cache_delete_id('supplier_contact_delete',delete_id);
                $(this).parent().parent().remove();
            }
            if($(".line_list").size() > 1)
            {
                $(this).parent().parent().remove();
            }
        });

        /*公用复制*/
        function addSpot(obj, sm, father_class,child_class) {
            if($("."+child_class).size() <= 0)
            {

                alert('没有一行数据可进行复制');
            }
            var clone  = $("."+child_class).first().clone();
            clone.find('input').val('');
            clone.find('input').prop('readonly',false);
            clone.find('input.pay_id').prop('readonly',true);
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
                $('button.btn-success').hide();
            }
        };

        $('button.update').click(function(){
            var is_gong_si = $('#is_gong_si').val();
            console.log(is_gong_si);
            if ($('#supplier-payment_method').val() == '2') {
                // 对私
                var no_gong_no_flag = dui_not();
                if (no_gong_no_flag == false) {
                    layer.msg('信息缺失，无法创建并推送财务审核-支付宝');
                    return false;
                }
            }else{
                if (is_gong_si.indexOf("1") != -1) {
                    //对公
                    var duigong_flag = duigong();
                    if (duigong_flag == false) {
                        layer.msg('信息缺失，无法创建并推送财务审核-对公');
                        return false;
                    }
                }
                if (is_gong_si.indexOf("2") != -1) {
                    // 对私
                    var duisi_flag = duisi();
                    if (duisi_flag == false) {
                        layer.msg('信息缺失，无法创建并推送财务审核-对私');
                        return false;
                    }
                }
            }
            // 验证图片是否已经上传（多图上传可能会漏掉文件）
            var img_upload_flag = true;
            $("div.file-upload-indicator").each(function() {
                var title = $(this).attr('title');
                if(title == '没有上传'){
                    img_upload_flag = false;
                }
            });
            if(img_upload_flag == false){
                layer.msg('添加的图片还没有上传，请先上传所有图片');
                return false;
            }

            var payplatcount=0;
            $("[name='SupplierPaymentAccount[payment_platform][]']").each(function() {
              console.log($(this).val());
              if($(this).val()==6){
                  payplatcount++;
              }
            });
            if(payplatcount>1){
                layer.msg('富友平台银行只能维护一张');
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
        $(document).on('change','#supplier-payment_method',function() {
            var paymethodArray = ['3','5'];
          if($.inArray($(this).val(),paymethodArray)!==-1){
              $('.pay_info').prop('required',true);
          }else{
              $('.pay_info').prop('required',false);
          }
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
        var  update_submit_index = 1;
        $(document).on('submit','#supplier-update',function() {
            update_submit_index++;
            if(update_submit_index>2){
                layer.msg('数据已提交不要重复点击');
                return false;
            }
        });

        /**
         * 对公资料上传检验
         * @return [type] [description]
         */
        function duigong()
        {
            // duigong = ['public_busine_licen_url'=>'营业执照', 'verify_book_url'=> '一般纳税人认定书', 'ticket_data_url'=>'开票资料'];
            var duigong_sum = 0;
            var duigong=new Array('public_busine_licen_url', 'verify_book_url', 'ticket_data_url');
            var duigong_res = duigong.forEach(function (item,index,input) {
                var duigong_sum_item = 0;
                $('[name="SupplierImages['+item+'][]"]').each(function(){
                    var value = $(this).attr('value');
                    console.log(value, duigong_sum);
                    if (value !='') {
                        if (value != undefined) {
                            duigong_sum_item = 1;
                        }
                    }
                });
                duigong_sum = parseInt(duigong_sum) + parseInt(duigong_sum_item);
            });
            console.log('对公：'+duigong_sum+'----'+duigong.length);
            if (duigong_sum >= duigong.length) {
                // alert('资料填写完整');
                return true;
            } else {
                // alert('资料不完全');
                return false;
            }
        }
        /**
         * 对私资料上传检验
         * @return [type] [description]
         */
        function duisi()
        {
            // duisi = ['private_busine_licen_url'=>'营业执照', 'receipt_entrust_book_url'=>'收款委托书', 'card_copy_piece_url'=>'身份证复印件', 'bank_scan_price_url'=>'银行卡的扫描件', 'fuyou_record_data_url'=>'富友备案资料'];
            var duisi_sum = 0;
            var duisi = new Array('private_busine_licen_url','receipt_entrust_book_url','card_copy_piece_url','bank_scan_price_url','fuyou_record_data_url');
            var duisi_res = duisi.forEach(function (item,index,input) {
                var duisi_sum_item = 0;
                $('[name="SupplierImages['+item+'][]"]').each(function(){
                    var value = $(this).attr('value');
                    if (value !='') {
                        if (value != undefined) {
                            duisi_sum_item = 1;
                        }
                    }
                });
                duisi_sum = parseInt(duisi_sum) + parseInt(duisi_sum_item);
            });
            console.log('对私：'+duisi_sum+'----'+duisi.length);
            if (duisi_sum >= duisi.length) {
                //资料填写完整
                return true;
            } else {
                //资料不完全
                return false;
            }
        }
        /**
         * 非公非私 资料上传检验
         * @return [type] [description]
         */
        function dui_not()
        {
            var no_gong_no_si_sum = 0;
            var no_gong_no_ = new Array('busine_licen_url');
            var duisi_res = no_gong_no_.forEach(function (item,index,input) {
                var no_gong_no_si_sum_item = 0;
                $('[name="SupplierImages['+item+'][]"]').each(function(){
                    var value = $(this).attr('value');
                    if (value !='') {
                        if (value != undefined) {
                            no_gong_no_si_sum_item  = 1;
                        }
                    }
                });
                no_gong_no_si_sum = parseInt(no_gong_no_si_sum) + parseInt(no_gong_no_si_sum_item);
            });
            console.log('非公非私：'+no_gong_no_si_sum+'----'+no_gong_no_.length);
            if (no_gong_no_si_sum >= no_gong_no_.length) {
                //资料填写完整
                return true;
            } else {
                //资料不完全
                return false;
            }
        }
        
        /* 自动替换掉 输入框中的非数字 字符 */
        $(document).on("change",".number_letter",function(){
            var reg = /[^\w]/ig;
            if(reg.test( $(this).val()) ){
                layer.msg('只能输入数字或字母');
                $(this).val($(this).val().replace(/[^\w]/ig,''));
            }
        });
        /* 自动替换掉 输入框中的非数字 字符 */
        $(document).on("keyup",".number_letter",function(){
            var reg = /[^\w]/ig;
            if(reg.test( $(this).val()) ){
                layer.msg('只能输入数字或字母');
                $(this).val($(this).val().replace(/[^\w]/ig,''));
            }
        });
        
        // 鼠标移入移出提示信息
        var tip_index = 0;
        $(document).on('mouseenter','.show_all', function(){
            tip_index = layer.tips($(this).val(),this,{time: 0});
        });
        $(document).on('mouseleave','.show_all', function(){
            layer.close(tip_index);
        });
        // 缓存删除记录的ID
        function cache_delete_id(input_id,delete_id){
            var old_id = $("#"+input_id).val();
            if(delete_id != '' && delete_id != 'undefined'){
                if(old_id == ''){
                    old_id = delete_id;
                }else{
                    old_id = old_id + ',' + delete_id;
                }
                $("#"+input_id).val(old_id);
            }
        }
JS;
$this->registerJs($js);
?>