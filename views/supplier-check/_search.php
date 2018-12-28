<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\daterange\DateRangePicker;
use kartik\select2\Select2;
use yii\web\JsExpression;
$url = \yii\helpers\Url::to(['/supplier/search-supplier']);
/* @var $this yii\web\View */
/* @var $model app\models\PurchaseOrderSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="supplier-check-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',

    ]); ?>
    <div class="row">
    <div class="col-md-1">
        <?=
            $form->field($model,'status')->dropDownList([1=>'未安排',2=>'已安排',3=>'已完成',4=>'已删除','5'=>'待评价',6=>'待采购确认',7=>'无资料'],['prompt'=>'请选择'])->label('申请状态');
        ?>
    </div>

    <div class="col-md-1">
        <?=
        $form->field($model,'check_type')->dropDownList([1=>'验厂',2=>'验货'],['prompt'=>'请选择'])->label('类型');
        ?>
    </div>
    <div class="col-md-1">
        <?=
        $form->field($model,'group')->dropDownList([1=>'国内',2=>'海外',3=>'FBA'],['prompt'=>'请选择'])->label('采购类型');
        ?>
    </div>

    <div class="col-md-1">
        <?=
        $form->field($model,'pur_number')->textInput()->label('采购单');
        ?>
    </div>
    <div class="col-md-1">
        <?=
        $form->field($model,'sku')->textInput()->label('SKU');
        ?>
    </div>
    <div class="col-md-1">
        <?=
        $form->field($model,'check_code')->textInput()->label('编号');
        ?>
    </div>
    <div class="col-md-1">
        <?=
        $form->field($model,'judgment_results')->dropDownList([0=>'待确认',1=>'合格',2=>'不合格'],['prompt'=>'请选择'])->label('检验结果');
        ?>
    </div>
    <div class="col-md-1">
        <?=
        $form->field($model,'supplier_name')->textInput()->label('供应商名称');
        ?>
    </div>
    <div class="col-md-1">
        <?= $form->field($model, 'supplier_code')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入供应商 ...'],
            'pluginOptions' => [
                'placeholder' => 'search ...',
                'allowClear' => true,
                'language' => [
                    'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                ],
                'ajax' => [
                    'url' => $url,
                    'dataType' => 'json',
                    'data' => new JsExpression('function(params) { return {q:params.term}; }')
                ],
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(res) { return res.text; }'),
                'templateSelection' => new JsExpression('function (res) { return res.text; }'),
            ],
        ])->label('供应商');
        ?>
    </div>

    <div class="col-md-1"><?= $form->field($model, 'apply_user_name')->widget(Select2::classname(), [
        'options' => ['placeholder' => '请输入申请人 ...'],
        'data' =>\app\services\BaseServices::getEveryOne('','name'),
        'pluginOptions' => [
            'language' => [
                'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
            ],
            /*'ajax' => [
                'url' => $url,
                'dataType' => 'json',
                'data' => new JsExpression('function(params) { return {q:params.term}; }')
            ],*/
            'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
            'templateResult' => new JsExpression('function(res) { return res.text; }'),
            'templateSelection' => new JsExpression('function (res) { return res.text; }'),
        ],
    ])->label('申请人');
    ?>
    </div>
    <div class="col-md-1"><?= $form->field($model, 'check_user')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入检验员 ...'],
            'data' =>\app\services\BaseServices::getEveryOne('','name'),
            'pluginOptions' => [
                'language' => [
                    'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                ],
                /*'ajax' => [
                    'url' => $url,
                    'dataType' => 'json',
                    'data' => new JsExpression('function(params) { return {q:params.term}; }')
                ],*/
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(res) { return res.text; }'),
                'templateSelection' => new JsExpression('function (res) { return res.text; }'),
            ],
        ])->label('检验员');
        ?>
    </div>

</div>
<div class="row">
    <div class="col-md-1">
        <label class="control-label">申请时间</label>
        <input class="create_time form-control"  name='SupplierCheckSearch[create_time]' value="<?=$model->create_time?>">
    </div>
    <div class="col-md-1">
        <label class="control-label">期望时间</label>
        <input class="expect_time form-control"  name='SupplierCheckSearch[expect_time]' value="<?=$model->expect_time?>">
    </div>
    <div class="col-md-1">
        <label class="control-label">确认时间</label>
        <input class="confirm_time form-control"  name='SupplierCheckSearch[confirm_time]' value="<?=$model->confirm_time?>">
    </div>
    <div class="col-md-1">
        <label class="control-label">报告时间</label>
        <input class="report_time form-control"  name='SupplierCheckSearch[report_time]' value="<?=$model->report_time?>">
    </div>
    <div class="col-md-1">
        <?= $form->field($model,'is_urgent')->dropDownList([1=>'是',0=>'否'],['prompt'=>'请选择'])->label('是否加急')?>
    </div>
    <div class="col-md-1">
        <?= $form->field($model,'times')->dropDownList([1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7],['prompt'=>'请选择'])->label('验货次数')?>
    </div>
    <div class="form-group col-md-2"  style="margin-top: 24px;float:left">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('重置',['index'],['class' => 'btn btn-default']) ?>
    </div>
</div>
    
    <?php ActiveForm::end(); ?>

</div>
<?php
$js = <<<JS


layui.use('laydate', function(){
        var laydate = layui.laydate;
        //执行一个laydate实例
        laydate.render({
            elem: '.report_time', //指定元素
            range:'~',
            type:'date'
        });
        laydate.render({
            elem: '.expect_time', //指定元素
            range:'~',
            type:'date'
        });
        laydate.render({
            elem: '.confirm_time', //指定元素
            range:'~',
            type:'date'
        });
        laydate.render({
            elem: '.create_time', //指定元素
            range:'~',
            type:'date'
        });
        });
JS;
$this->registerJs($js);
?>