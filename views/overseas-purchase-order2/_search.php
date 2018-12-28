<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\daterange\DateRangePicker;
use app\services\BaseServices;
use app\services\SupplierServices;
use app\services\PurchaseOrderServices;
use kartik\select2\Select2;
use yii\web\JsExpression;
$url = \yii\helpers\Url::to(['/supplier/search-supplier']);
?>

<?php $form = ActiveForm::begin([
    'action' => ['index'],
    'method' => 'get',
]); ?>

<div class="col-md-1">
    <?= Html::dropDownList('',['99'=>'未全部到货','3'=>'已审批','6'=>'全到货','7'=>'等待到货','8'=>'部分到货等待剩余','9'=>'部分到货不等待剩余','10'=>'已作废'],['prompt' => '请选择'])->label('仓库到货状态') ?>
</div>

<div class="col-md-1">
    <label>合同号</label>
    <input type="text" name="compact_number" value="" class="form-control">
</div>


<div class="form-group" style="margin-top: 24px;">
    <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
    <?= Html::a('重置', ['index'], ['class' => 'btn btn-default']) ?>
</div>
<?php ActiveForm::end(); ?>
