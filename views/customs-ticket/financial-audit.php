<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use kartik\datetime\DateTimePicker;
use kartik\select2\Select2;
use app\config\Vhelper;
use kartik\date\DatePicker;

$this->title = '财务审核';
$this->params['breadcrumbs'][] = 'FBA采购';
$this->params['breadcrumbs'][] = '报关&开票';
$this->params['breadcrumbs'][] = $this->title;

$label = ['id'=> 'id','pur_number'=> '采购单号', 'sku'=> 'sku', 'open_number'=>'开票单号',
'open_time'=> '开票日期','ticket_name'=> '开票品名',
'issuing_office'=> '开票单位','total_par'=> '票面总金额',
'tickets_number'=> '开票数量','invoice_code'=> '发票编码',
// 'note' => '备注','status' => '状态','create_user' => '创建人','create_time' => '创建时间',
];
?>

<style type="text/css">
    .modal-lg{width: 55%; !important;}
    .row{padding:10px;}
</style>

<?php $form = ActiveForm::begin([]
); ?>
<h3 class="">财务审核</h3>
<p></p>
<div class="container-fluid" id="container-fluid?>" style="border: 2px solid #FF5722;margin-bottom: 10px;">
    <div class="row">
        <?php foreach ($label as $key => $value):?>
        <div>
            <label class="control-label" for="purchaseorder-carrier"><?=$value?></label>
            <div class="form-control">
            <?php if($key=='open_time'){
                echo date('Y-m-d', strtotime($model->$key));
            }else{
                echo $model->$key;
            }?>
            </div>
        </div>
        <?php endforeach;?>
    </div>
    <div>
        <?= $form->field($model, 'note')->textArea(['rows' => '6', 'placeholder'=>'审批不通过请填写原因'])->label('审批备注') ?>
    </div>

    <input type="hidden" class="PurchaseTicketOpen" name="PurchaseTicketOpen[status]" style="display:none">
    <input type="hidden" name="PurchaseTicketOpen[id]" value="<?=$model->id?>" style="display:none">

    <div class="form-group " style="clear: both">
        <?= Html::submitButton('审批通过(Ok)',['class' => 'btn btn-success']) ?>
        <?= Html::submitButton('审批不通过(Rollback)', ['class' => 'btn btn-warning']) ?>
    </div>
</div>
<?php ActiveForm::end(); ?>
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