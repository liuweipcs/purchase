<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use kartik\datetime\DateTimePicker;
use kartik\select2\Select2;
use app\config\Vhelper;
use kartik\date\DatePicker;

$label = ['contact_person'=> '联系人', 'contact_number'=> '联系电话', 'chinese_contact_address'=>'地址',
'micro_letter'=> '微信','qq'=> 'QQ',
'want_want'=> '阿里旺旺',
'business_scope'=> '经营范围', //??
];
?>

<style type="text/css">
    .modal-lg{width: 55%; !important;}
    .row{padding:10px;}
</style>

<?php $form = ActiveForm::begin([]
); ?>
<h3 class="">系统信息</h3>
<p></p>
<div class="container-fluid" id="container-fluid?>" style="border: 2px solid #FF5722;margin-bottom: 10px;">
    <div class="row">
        <?php foreach ($label as $key => $value):?>
        <?php if($key=='business_scope'){?>
            <div class="form-group field-purchaseticketopen-note">
                <label class="control-label" for="purchaseticketopen-note"><?=$value?></label>
                <textarea id="purchaseticketopen-note" class="form-control" rows="6" readonly="readonly"><?=$model[$key]?></textarea>
                <div class="help-block"></div>
            </div>
        <?php }else{?>
            <div class="col-md-4">
                <label class="control-label" for="purchaseorder-carrier"><?=$value?></label>
                <div class="form-control"><?=!empty($model[$key])?$model[$key]:'';?>
                </div>
            </div>
        <?php }?>
        
        <?php endforeach;?>
    </div>

</div>
<?php
Modal::begin([
    'id' => 'created-modal',
    //'header' => '<h4 class="modal-title">系统信息</h4>',
    //'footer' => '<a href="#" class="btn btn-primary"  data-dismiss="modal"  >Close</a>',
    'closeButton' =>false,
    'size'=>'modal-lg',
    'options'=>[
        'z-index' =>'-1',

    ],
]);
Modal::end();
?>
<?php
$surl= Url::toRoute(['product/viewskusales']);
$js = <<<JS
$(function () {
    $(document).on('click', '.btn-success', function () {
        $('.PurchaseTicketOpen').attr('value','2');
    });
     $(document).on('click', '.btn-warning', function () {
        $('.PurchaseTicketOpen').attr('value','3');
    });
});

JS;
$this->registerJs($js);
?>