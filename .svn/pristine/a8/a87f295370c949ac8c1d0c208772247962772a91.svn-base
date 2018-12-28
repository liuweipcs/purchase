<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;
use kartik\daterange\DateRangePicker;
use app\services\BaseServices;
use app\services\SupplierServices;
use app\services\PurchaseOrderServices;
use kartik\select2\Select2;
use yii\web\JsExpression;
$url = \yii\helpers\Url::to(['/supplier/search-supplier']);
use yii\widgets\LinkPager;
$this->title = '入库数量异常';
$this->params['breadcrumbs'][] = '采购管理';
$this->params['breadcrumbs'][] = '采购异常';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="panel panel-default">

    <div class="panel-body">

        <?php $form = ActiveForm::begin(['action' => ['ruku-num-handler'], 'method' => 'get']); ?>

        <div class="col-md-1"><?= $form->field($model, 'sku')->label('sku') ?></div>

        <div class="col-md-1"><?= $form->field($model, 'pur_number')->label('采购单号') ?></div>

        <div class="col-md-1"><?= $form->field($model, 'status')->dropDownList(PurchaseOrderServices::getReadingStatus(), ['prompt' => '请选择'])->label('读取状态') ?></div>

        <div class="col-md-1">
            <?= $form->field($model, 'inform_user')->widget(Select2::classname(), [
                'options' => ['placeholder' => '请输入采购员...'],
                'data' => BaseServices::getEveryOne('','name'),
                'pluginOptions' => [
                    'language' => [
                        'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                    ],
                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                    'templateResult' => new JsExpression('function(res) { return res.text; }'),
                    'templateSelection' => new JsExpression('function (res) { return res.text; }'),
                ],
            ])->label('采购员');
            ?>
        </div>

        <div class="col-md-3">
            <label class="control-label">创建时间</label>
            <?php
            $addon = <<< HTML
<span class="input-group-addon">
    <i class="glyphicon glyphicon-calendar"></i>
</span>
HTML;
            echo '<div class="input-group drp-container">';
            echo DateRangePicker::widget([
                    'name' => 'create_time',
                    'value' => $model->create_time,
                    'useWithAddon'=> false,
                    'convertFormat'=> true,
                    'initRangeExpr' => true,
                    'startAttribute' => 'start_time',
                    'endAttribute' => 'end_time',
                    'startInputOptions' => ['value' => !empty($model->start_time) ? $model->start_time : date('Y-m-d H:i',strtotime("last month"))],
                    'endInputOptions' => ['value' => !empty($model->end_time) ? $model->end_time : date('Y-m-d 23:59', time())],
                    'pluginOptions' => [
                        'locale' => ['format' => 'Y-m-d H:i'],
                        'ranges' => [
                            '今天' => ["moment().startOf('day')", "moment()"],
                            '昨天' => ["moment().startOf('day').subtract(1,'days')", "moment().endOf('day').subtract(1,'days')"],
                            '最近7天' => ["moment().startOf('day').subtract(6, 'days')", "moment()"],
                            '最近30天' => ["moment().startOf('day').subtract(29, 'days')", "moment()"],
                            '本月' => ["moment().startOf('month')", "moment().endOf('month')"],
                            '上月' => ["moment().subtract(1, 'month').startOf('month')", "moment().subtract(1, 'month').endOf('month')"],
                        ]
                    ],
                ]).$addon;
            echo '</div>';
            ?>
        </div>


        <div class="form-group col-md-2" style="margin-top: 24px;float:left">
            <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('重置', ['ruku-num-handler'],['class' => 'btn btn-default']) ?>
        </div>
        <?php ActiveForm::end(); ?>


    </div>


</div>

<div class="panel panel-success">

    <div class="panel-heading">
        <div class="pull-right">
            共<b><?= $pager->totalCount ?></b>条数据
        </div>
        <div class="clearfix"></div>
    </div>

    <div class="panel-body">
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>#</th>
                <th>SKU</th>
                <th>读取状态</th>
                <th>订单号</th>
                <th>供应商名称</th>
                <th>采购员</th>
                <th>采购数量</th>
                <th>正常数</th>
                <th>异常数</th>
                <th>历史入库数量</th>
                <th>原因</th>
                <th>创建时间</th>
                <th>跟进人/时间</th>
                <th>跟进备注</th>
                <th>操作</th>
            </tr>

            </thead>
            <tbody>
            <?php
            foreach($list as $v):
                $supplier_name = \app\models\PurchaseOrder::find()->select('supplier_name')->where(['pur_number' => $v['pur_number']])->scalar();
                ?>
                <tr>
                    <td><?= $v['id'] ?></td>
                    <td><?= $v['sku'] ?></td>
                    <td><?= PurchaseOrderServices::getReadingStatus($v['status'], false) ?></td>
                    <td><?= $v['pur_number'] ?></td>
                    <td><?= $supplier_name ?></td>
                    <td><?= $v['inform_user'] ?></td>
                    <td><?= $v['purchase_total'] ?></td>
                    <td><?= $v['normal_num'] ?></td>
                    <td><?= $v['excep_num'] ?></td>
                    <td><?= $v['history_intock'] ?></td>
                    <td><?= $v['reason'] ?></td>
                    <td><?= $v['create_time'] ?></td>
                    <td><?= $v['gj_person']?>  <?= $v['gj_time'] ?></td>
                    <td><?= $v['gj_note'] ?></td>
                    <td><a style="cursor: pointer;" class="cl" href="/exp-ruku/ruku-num-gj?id=<?= $v['id'] ?>" data-toggle = 'modal', data-target = '#create-modal'>跟进</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>

        </table>

    </div>

    <div class="panel-footer">
        <?= LinkPager::widget([
                'pagination' => $pager,
                'firstPageLabel' => "首页",
                'prevPageLabel' => '上一页',
                'nextPageLabel' => '下一页',
                'lastPageLabel' => '末页',
                'options' => ['class' => 'pagination no-margin']
            ]);
        ?>
    </div>

</div>

<?php
Modal::begin([
    'id' => 'create-modal',
    'header' => '<h4 class="modal-title">系统信息</h4>',
    'footer' => '<a href="#" class="btn btn-primary closes" data-dismiss="modal">关闭</a>',
    'size' => 'modal-lg',
    'options' => [
        'data-backdrop' => 'static',
    ],
]);
Modal::end();


$js = <<<JS
$(function() {
    
    $('.cl').click(function() {
        $('#create-modal .modal-body').html('');
        $('#create-modal .modal-body').load($(this).attr('href'));
    });
    
});     

JS;
$this->registerJs($js);
?>




