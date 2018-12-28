<?php
use yii\widgets\ListView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\daterange\DateRangePicker;
use app\services\BaseServices;
use app\services\SupplierServices;
use app\services\PurchaseOrderServices;
use kartik\select2\Select2;
use yii\web\JsExpression;
use yii\bootstrap\Modal;
use app\models\PurchaseOrderPayType;

$this->title = '审核-订单信息修改申请';
$this->params['breadcrumbs'][] = '海外仓';
$this->params['breadcrumbs'][] = '采购单';
$this->params['breadcrumbs'][] = $this->title;
?>

<style type="text/css">
    span.old {
        display: inline-block;
        margin-right: 30px;
        font-weight: bold;
        color: #03A9F4;
        min-width: 100px;
    }
    span.new {
        display: inline-block;
        margin-right: 30px;
        font-weight: bold;
        color: #03A9F4;
        min-width: 100px;
    }
</style>

<div class="panel panel-default">

    <div class="panel-body">

        <?php $form = ActiveForm::begin([
            'action' => ['audit-ship'],
            'method' => 'get',

        ]); ?>

        <div class="col-md-1"><?= $form->field($searchModel, 'username')->widget(Select2::classname(), [
                'options' => ['placeholder' => '请输入采购员 ...'],
                'data' =>BaseServices::getEveryOne('','name'),
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
            ])->label('采购员');
            ?>
        </div>

        <div class="col-md-1"><?= $form->field($searchModel, 'pur_number') ?></div>

        <div class="form-group col-md-1" style="margin-top: 24px;float:left">
            <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('重置', ['audit-ship'],['class' => 'btn btn-default']) ?>
        </div>
        <?php ActiveForm::end(); ?>

    </div>

</div>


<div class="my-box" style="color: red;">
    <h4>系统提示：</h4>
    <p>审核操作不可逆，审核通过的数据，会真实的修改订单的数据</p>
</div>


<?php
echo ListView::widget([
    'dataProvider' => $dataProvider,
    'itemView' => '_operat-log',
    'viewParams' => [
        'fullView' => true,
        'context' => 'main-page'
    ]
]);

Modal::begin([
    'id' => 'created-modal',
    'header' => '<h4 class="modal-title">系统信息</h4>',
    //'footer' => '<a href="#" class="btn btn-primary"  data-dismiss="modal"  >Close</a>',
    //'closeButton' =>false,
    'size'=>'modal-lg',
    'options'=>[
        //'data-backdrop'=>'static',//点击空白处不关闭弹窗
        'z-index' =>'-1',

    ],
]);
?>



