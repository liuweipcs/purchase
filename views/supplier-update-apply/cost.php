<?php

use yii\helpers\Html;

use kartik\grid\GridView;
use yii\bootstrap\Modal;
use yii\widgets\ActiveForm;
use kartik\daterange\DateRangePicker;
use kartik\select2\Select2;
use yii\web\JsExpression;
use app\models\PurchaseOrderItems;

$url = \yii\helpers\Url::to(['/supplier/search-supplier']);
/* @var $this yii\web\View */
/* @var $searchModel app\models\SupplierGoodsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'SKU降本');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="stockin-index">
    <?php $form = ActiveForm::begin([
        'action' => ['cost'],
        'method' => 'get',
    ]); ?>
    <div class="col-md-1" ><label class="control-label" for="purchaseorderpaysearch-applicant">首次计算时间：</label><?php
        $add = <<< HTML
        <span class="input-group-addon">
            <i class="glyphicon glyphicon-calendar"></i>
        </span>
HTML;
        echo '<div class="input-group drp-container">';
        echo DateRangePicker::widget([
                'name'=>'CostPurchaseNum[time]',
                'useWithAddon'=>true,
                'convertFormat'=>true,
                'startAttribute' => 'CostPurchaseNum[check_start_time]',
                'endAttribute' => 'CostPurchaseNum[check_end_time]',
                'startInputOptions' => ['value' => !empty($searchModel->check_start_time) ? $searchModel->check_start_time : date('Y-m-d H:i:s',strtotime("-3 month"))],
                'endInputOptions' => ['value' => !empty($searchModel->check_end_time) ? $searchModel->check_end_time : date('Y-m-d H:i:s',time())],
                'pluginOptions'=>[
                    'locale'=>['format' => 'Y-m-d H:i:s'],
                ]
            ]).$add ;
        echo '</div>';
        ?></div>
    <div class="col-md-1">
        <?=$form->field($searchModel, "group")->dropDownList(['0'=>'全部','1'=>'供应链团队','2'=>'FBA','3'=>'国内仓','4'=>'海外仓'],['class' => 'form-control'])->label('部门')?>
    </div>
    <div class="col-md-1">
        <?=$form->field($searchModel, "type")->dropDownList(['0'=>'全部','1'=>'降价','2'=>'涨价'],['class' => 'form-control'])->label('价格变化趋势')?>
    </div>
    <div class="col-md-1">
        <?= $form->field($searchModel, 'new_supplier_code')->widget(Select2::classname(), [
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
        ])->label('现供货商');
        ?>
    </div>

    <div class="col-md-1"><?= $form->field($searchModel,'create_user_name')->textInput(['placeholder'=>''])->label('人员搜索') ?></div>
    <div class="col-md-2">
        <?php echo '<label>统计时间</label>';
        echo \kartik\date\DatePicker::widget([
            'name' => 'CostPurchaseNum[date]',
            'value' => $searchModel->date?$searchModel->date :'',
            'options' => ['placeholder' => ''],
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-01 00:00:00',
                'todayHighlight' => true
            ]
        ]);?></div>

    <div class="col-md-1"><?= $form->field($searchModel,'sku')->textInput(['placeholder'=>''])->label('SKU') ?></div>
    <div class="form-group col-md-2" style="margin-top: 24px;">
        <?= Html::submitButton(Yii::t('app', '搜索'), ['class' => 'btn btn-primary']) ?>
        <?= Html::a('重置',['cost'],['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

    <?php //$this->render('_search', ['model' => $searchModel]); ?>
    <p class="clearfix"></p>
    <?php
    if(\mdm\admin\components\Helper::checkRoute('export-csv')) {
        echo Html::button('导出Excel',['class' => 'btn btn-success','id'=>'export-csv']);
    }

    ?>
    <input type="hidden" value="<?=$total?>" id="total">
    <input type="hidden" value="<?=$is_show?>" id="is_show">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        "options" => ["class" => "grid-view","style"=>"overflow:auto", "id" => "grid"],
        'filterSelector' => "select[name='".$dataProvider->getPagination()->pageSizeParam."'],input[name='".$dataProvider->getPagination()->pageParam."']",
        'pager'=>[
            'options'=>['class' => 'pagination','style'=> "display:block;"],
            'class'=>\liyunfang\pager\LinkPager::className(),
            'pageSizeList' => [20, 50, 100, 200],
            'firstPageLabel'=>"首页",
            'prevPageLabel'=>'上一页',
            'nextPageLabel'=>'下一页',
            'lastPageLabel'=>'末页',
        ],
        'columns' => [
            [
                'class' => 'kartik\grid\CheckboxColumn',
                'name'=>"id" ,
                'checkboxOptions' => function ($model, $key, $index, $column) {
                    return ['value' => $model->id];
                }

            ],
            [
                'label' =>'供应链人员',
                'format'=>'raw',
                'value' => function($model, $key, $index, $column){
                    return  $model->apply->create_user_name;
                },

            ],
            [
                'label' => 'SKU',
                'format'=> 'raw',
                'value' => function($model){
                    $html  = $model->sku;

                    $html .='<br>'.Html::a('<span class="glyphicon glyphicon-stats" style="font-size:10px;color:cornflowerblue;" title="销量库存"></span>', ['product/get-stock-sales','sku'=>$model->sku],
                            [
                                'class' => 'btn btn-xs stock-sales-purchase',
                                'data-toggle' => 'modal',
                                'data-target' => '#cost-modal',
                            ]);
                    $html .=Html::a('<span class="glyphicon glyphicon-eye-open" style="font-size:10px;color:cornflowerblue;" title="历史采购记录"></span>', ['purchase-suggest/histor-purchase-info','sku'=>$model->sku],[
                        'data-toggle' => 'modal',
                        'data-target' => '#cost-modal',
                        'class'=>'btn btn-xs stock-sales-purchase',
                    ]);
                    return $html;
                }
            ],
            [
                'label' => '品名',
                'format'=> 'raw',
                'value' => function($model){
                    return !empty($model->apply->productDetail) ? !empty($model->apply->productDetail->desc) ? $model->apply->productDetail->desc->title : '' : '';
                }
            ],
            [
                'label' => '供应商',
                'format'=> 'raw',
                'value' => function($model){
                    return !empty($model->apply->newSupplier) ? $model->apply->newSupplier->supplier_name : '';
                }
            ],
            [
                'label' => '价格变化时间',
                'format'=> 'raw',
                'value' => function($model){
                    return $model->apply->update_time;
                }
            ],
            [
                'label'=>'首次计算时间',
                'value'=>function($model){
                    return $model->apply->cost_begin_time;
                }
            ],
            [
                'label'=>'统计时间',
                'value'=>function($model){
                    return date('Y-m',strtotime($model->date));
                }
            ],
            [
                'label' => '原价',
                'format'=> 'raw',
                'value' => function($model){
                    return !empty($model->apply->oldQuotes) ? $model->apply->oldQuotes->supplierprice : '';
                }
            ],
            [
                'label' => '现价',
                'format'=> 'raw',
                'value' => function($model){
                    return !empty($model->apply->newQuotes) ? $model->apply->newQuotes->supplierprice : '';
                }
            ],
            [
                'label' => '价格变化幅度',
                'format'=> 'raw',
                'value' => function($model){
                    $oldPrice = !empty($model->apply->oldQuotes) ? $model->apply->oldQuotes->supplierprice : 0;
                    $newPrice = !empty($model->apply->newQuotes) ? $model->apply->newQuotes->supplierprice : 0;
                    $result  = $newPrice-$oldPrice;
                    return $result<0 ? "<span style='color: green'>$result</span>" : "<span style='color: red'>$result</span>";
                }
            ],
            [
                'label' => '降本比例',
                'format'=> 'raw',
                'value' => function($model, $key, $index, $column){
                    // 降价幅度 = 原价-现价
                    $old_price = !empty($model->apply->oldQuotes) ? $model->apply->oldQuotes->supplierprice : 0;
                    $new_price = !empty($model->apply->newQuotes) ? $model->apply->newQuotes->supplierprice : 0;
                    $jj_fd = $old_price-$new_price;
                    // 降本比例=降价幅度/原价*100%;
                    $bili = $old_price==0 ? ($jj_fd*100).'%': round(($jj_fd/$old_price), 4) * 100 . '%';
                    if ($bili < 0) {
                        $color = 'red';//涨价标红
                    } elseif ($bili > 0) {
                        $color = 'green';//降价标绿
                    } else {
                        $color = 'black';
                    }
                    return "<span style='color: $color'>$bili</span>";

                }
            ],
            [
                'label' => '采购数量',
                'format'=> 'raw',
                'value' => function($model, $key, $index, $column){
                    return $model->purchase_num;
                }
            ],
            [
                'label' => '价格变化金额',
                'format'=> 'raw',
                'pageSummary'=>true,
                'value' => function($model){
                    $oldPrice = !empty($model->apply->oldQuotes) ? $model->apply->oldQuotes->supplierprice : 0;
                    $newPrice = !empty($model->apply->newQuotes) ? $model->apply->newQuotes->supplierprice : 0;
                    $result   = (1000*$newPrice-1000*$oldPrice)/1000;
                    $num      = $model->purchase_num;
                    return  $result*$num;
                }

            ],
        ],
        'containerOptions' => ["style"=>"overflow:auto"], // only set when $responsive = false
        'toolbar' =>  [

            //'{export}',
        ],


        'pjax' => false,
        'bordered' => true,
        'striped' => false,
        'condensed' => true,
        'responsive' => true,
        'hover' => false,
        'floatHeader' => false,
        'showPageSummary' => true,

        'exportConfig' => [
            GridView::EXCEL => [],
        ],
        'panel' => [
            //'heading'=>'<h3 class="panel-title"><i class="glyphicon glyphicon-globe"></i> Countries</h3>',
            'type'=>'success',
            //'before'=>false,
            //'after'=>false,
            //'after'=>Html::a('<i class="glyphicon glyphicon-repeat"></i> 刷新', ['index'], ['class' => 'btn btn-info']),
            //'footer'=>true
        ],
    ]);?>
</div>
<!--<div style="float: right">--><?//= '降本总金额：11元';?><!--</div>-->
<!--<div style="float: clear"></div>-->
<?php
Modal::begin([
    'id' => 'cost-modal',
    'header' => '<h4 class="modal-title">系统信息</h4>',
    'footer' => '<a href="#" class="btn btn-primary closes" data-dismiss="modal">Close</a>',
    'size'=>'modal-lg',
    'options'=>[
        //'data-backdrop'=>'static',//点击空白处不关闭弹窗

    ],
]);
Modal::end();
?>
<?php
$historys         = \yii\helpers\Url::toRoute(['purchase-suggest/histor-purchase-info']);
$js = <<<JS
$(document).on('click','.stock-sales-purchase', function () {
    $('.modal-body').html('正在请求数据....');
    $.get($(this).attr('href'), {},
        function (data) {
            $('#cost-modal').find('.modal-body').html(data);
        }
    );
});

$(function() {
    var total = $("#total").val();
    var is_show = $("#is_show").val();
    if(is_show == 1){
        $(".summary").append(" 总金额:<b>"+total+"</b>");   
    }
  
  //批量导出
     $('#export-csv').click(function() {
            var ids = $('#grid').yiiGridView('getSelectedRows');
            /*if(ids==''){
                alert('请先选择!');
                return false;
            }else{*/
                 
                window.location.href='export-csv?ids='+ids;
            /*}*/
     });
});
JS;
$this->registerJs($js);
?>
