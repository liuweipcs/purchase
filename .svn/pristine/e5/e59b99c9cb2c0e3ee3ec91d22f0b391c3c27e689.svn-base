<?php

use yii\helpers\Html;

use kartik\grid\GridView;
use yii\bootstrap\Modal;
use yii\widgets\ActiveForm;
use kartik\daterange\DateRangePicker;
use kartik\select2\Select2;
use yii\web\JsExpression;
use mdm\admin\components\Helper;

$url = \yii\helpers\Url::to(['/supplier/search-supplier']);
/* @var $this yii\web\View */
/* @var $searchModel app\models\SupplierGoodsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'SKU降本(申请)');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="stockin-index">
    <?php $form = ActiveForm::begin([
        'action' => ['apply-cost'],
        'method' => 'get',
    ]); ?>
    <div class="col-md-1" ><label class="control-label" for="purchaseorderpaysearch-applicant">价格变化时间：</label><?php
        $add = <<< HTML
        <span class="input-group-addon">
            <i class="glyphicon glyphicon-calendar"></i>
        </span>
HTML;
        echo '<div class="input-group drp-container">';
        echo DateRangePicker::widget([
                'name'=>'SupplierUpdateApplySearch[time]',
                'useWithAddon'=>true,
                'convertFormat'=>true,
                'startAttribute' => 'SupplierUpdateApplySearch[check_start_time]',
                'endAttribute' => 'SupplierUpdateApplySearch[check_end_time]',
                'startInputOptions' => ['value' => !empty($searchModel->check_start_time) ? $searchModel->check_start_time : date('Y-m-d H:i:s',strtotime("-3 month"))],
                'endInputOptions' => ['value' => !empty($searchModel->check_end_time) ? $searchModel->check_end_time : date('Y-m-d H:i:s',time())],
                'pluginOptions'=>[
                    'locale'=>['format' => 'Y-m-d H:i:s'],
                ]
            ]).$add ;
        echo '</div>';
        ?></div>
    <div class="col-md-1">
        <?=$form->field($searchModel, "group")->dropDownList(['0'=>'全部','1'=>'供应链团队','2'=>'FBA','3'=>'国内仓',4=>'海外仓'],['class' => 'form-control'])->label('部门')?>
    </div>
    <div class="col-md-1">
        <?=$form->field($searchModel, "tendency")->dropDownList(['0'=>'全部','1'=>'降价','2'=>'涨价'],['class' => 'form-control'])->label('价格变化趋势')?>
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

    <div class="col-md-1"><?= $form->field($searchModel,'sku')->textInput(['placeholder'=>''])->label('SKU') ?></div>
    <div class="form-group col-md-2" style="margin-top: 24px;">
        <?= Html::submitButton(Yii::t('app', '搜索'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', '重置'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

    <?php //$this->render('_search', ['model' => $searchModel]); ?>
    <p class="clearfix"></p>
    <div class="panel-footer">
        <?php
        if (Helper::checkRoute('export-csv-apply-cost')) {
            echo Html::button('导出',['class'=>'btn btn-success export-apply-cost']);
        }
        ?>
    </div>

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
                'name' => 'id',
                'checkboxOptions' => function ($model,$key,$index,$column) {
                    return ['value' => $model->id];
                }
            ],
            [
                'label' =>'供应链人员',
                'format'=>'raw',
                'value' => function($model, $key, $index, $column){
                    return  $model->create_user_name;
                },

            ],
            [
                'label' => 'SKU',
                'format'=> 'raw',
                'value' => function($model){
                    $html = $model->sku;
                    $html .='<br>'.Html::a('<span class="glyphicon glyphicon-stats" style="font-size:10px;color:cornflowerblue;" title="销量库存"></span>', ['product/get-stock-sales','sku'=>$model->sku],
                            [
                                'class' => 'btn btn-xs stock-sales-purchase',
                                'data-toggle' => 'modal',
                                'data-target' => '#create-modal',
                            ]);
                    $html .=Html::a('<span class="glyphicon glyphicon-eye-open" style="font-size:10px;color:cornflowerblue;" title="历史采购记录"></span>', ['purchase-suggest/histor-purchase-info','sku'=>$model->sku],[
                        'data-toggle' => 'modal',
                        'data-target' => '#create-modal',
                        'class'=>'btn btn-xs stock-sales-purchase',
                    ]);
                    return $html;
                }
            ],
            [
                'label' => '品名',
                'format'=> 'raw',
                'value' => function($model){
                    return !empty($model->productDetail) ? !empty($model->productDetail->desc) ? $model->productDetail->desc->title : '' : '';
                }
            ],
            [
                'label' => '供应商',
                'format'=> 'raw',
                'value' => function($model){
                    return !empty($model->newSupplier) ? $model->newSupplier->supplier_name : '';
                }
            ],
            [
                'label' => '价格变化时间',
                'format'=> 'raw',
                'value' => function($model){
                    return $model->update_time;
                }
            ],
            [
                'label'=>'首次计算时间',
                'value'=>function($model){
                    return $model->cost_begin_time;
                }
            ],
            [
                'label' => '原价',
                'format'=> 'raw',
                'value' => function($model){
                    return !empty($model->oldQuotes) ? $model->oldQuotes->supplierprice : '';
                }
            ],
            [
                'label' => '现价',
                'format'=> 'raw',
                'value' => function($model){
                    return !empty($model->newQuotes) ? $model->newQuotes->supplierprice : '';
                }
            ],
            [
                'label' => '价格变化幅度',
                'format'=> 'raw',
                'value' => function($model){
                    $oldPrice = !empty($model->oldQuotes) ? $model->oldQuotes->supplierprice : 0;
                    $newPrice = !empty($model->newQuotes) ? $model->newQuotes->supplierprice : 0;
                    $result  = (1000*$newPrice-1000*$oldPrice)/1000;
                    return $result<0 ? "<span style='color: green'>$result</span>" : "<span style='color: red'>$result</span>";
                }
            ],
            [
                'label' => '采购数量',
                'format'=> 'raw',
                'value' => function($model, $key, $index, $column){

                    $html = '';
                    if(!empty($model->skuCost)){
                        foreach ($model->skuCost as $data){
                            $html .= date('Y-m',strtotime($data->date)).'采购数量:'.$data->purchase_num."<br/>";
                        }
                    }
                    return $html;
                }
            ],
            [
                'label' => '价格变化金额',
                'format'=> 'raw',
                'value' => function($model){
                    $oldPrice = !empty($model->oldQuotes) ? $model->oldQuotes->supplierprice : 0;
                    $newPrice = !empty($model->newQuotes) ? $model->newQuotes->supplierprice : 0;
                    $result   = (1000*$newPrice-1000*$oldPrice)/1000;
                    $html = '';
                    if(!empty($model->skuCost)){
                        foreach ($model->skuCost as $data){
                            $html .= date('Y-m',strtotime($data->date)).'金额:'.$data->purchase_num*$result."<br/>";
                        }
                    }
                    return $html;
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
        'showPageSummary' => false,

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
    'id' => 'create-modal',
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
$js = <<<JS
$(function() {
      //批量导出
      $('.export-apply-cost').click(function() {
            var ids = $('#grid').yiiGridView('getSelectedRows');
            window.location.href = 'export-csv-apply-cost?ids=' + ids;
      });
});

$(document).on('click', '.stock-sales-purchase', function () {
        $('.modal-body').html('正在请求数据....');
        $.get($(this).attr('href'), {},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
JS;
$this->registerJs($js);
?>