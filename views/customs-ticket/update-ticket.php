<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use kartik\datetime\DateTimePicker;
use kartik\select2\Select2;
use app\config\Vhelper;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $model app\models\Supplier */
/* @var $form yii\widgets\ActiveForm */
$this->title = '更新开票';
$this->params['breadcrumbs'][] = 'FBA采购';
$this->params['breadcrumbs'][] = '报关&开票';
$this->params['breadcrumbs'][] = $this->title;?>

<style type="text/css">
    .modal-lg{width: 55%; !important;}
    .row{padding:10px;}
</style>

<?php $form = ActiveForm::begin([/*'id' => 'form-id','enableAjaxValidation' => true,'validationUrl' => Url::toRoute(['validate-form']),*/]); ?>

<h3 class="">更新开票</h3>

<?= $form->field($model, 'key_id')->textInput(['value'=> $get['key_id'],'style' => 'display:none;','readonly'=>true])->label(false) ?>
<?php echo '关联信息&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;报关单号['.$declare_model->custom_number.'] : SKU['.$declare_model->sku.'] : 数量['.$declare_model->amounts.']' ?>

<p></p>
<div class="container-fluid" id="container-fluid?>" style="border: 2px solid #FF5722;margin-bottom: 10px;">
    <div class="row">
        <div class="col-md-2">
            <?=$form->field($model,'pur_number')->textInput(['value'=>$get['pur_number'],'readonly'=>true]) ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'sku')->textInput(['value'=> $get['sku'],'readonly'=>true]) ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'open_number')->textInput(['placeholder'=> '手动输入'])->label('开票单号') ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'open_time')->widget(DatePicker::classname(), [ 
                'options' => ['placeholder' => ''], 
                'pluginOptions' => [ 
                    'autoclose' => true, 
                    'todayHighlight' => true, 
                    'format' => 'yyyy-mm-dd', 
                ] 
            ])->label('开票日期'); ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'ticket_name')->textInput(['placeholder'=> '手动输入'])->label('开票品名') ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'issuing_office')->textInput(['placeholder'=> '手动输入'])->label('开票单位') ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'total_par')->textInput(['placeholder'=> '手动输入'])->label('票面总金额') ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'tickets_number')->textInput(['placeholder'=> '手动输入'])->label('开票数量') ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'invoice_code')->textInput(['placeholder'=> '手动输入'])->label('发票编码') ?>
        </div>

        <div class="col-md-2">
            <?= $form->field($model, 'status')->radioList(['0'=>'保存','1'=>'提交'], ['value'=> 0])->label('提交操作') ?>
        </div>
    </div>
    
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? '创建' : '确认', ['class' => ($model->isNewRecord ? 'btn btn-success' : 'btn btn-primary') . ' submit',]) ?>
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
    
});
JS;
$this->registerJs($js);
?>




