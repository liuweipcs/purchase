<?php

use yii\helpers\Html;

use kartik\grid\GridView;
use Yii\helpers\Url;
use yii\bootstrap\Modal;
use app\config\Vhelper;
use yii\widgets\ActiveForm;
use \app\services\SupplierGoodsServices;
use \app\models\SupplierUpdateApply;
/* @var $this yii\web\View */
/* @var $searchModel app\models\SupplierGoodsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '供应商信息修改审核');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="stockin-index">
    <?php $form = ActiveForm::begin([
        'action' => ['apply'],
        'method' => 'get',
    ]); ?>
    <div class="col-md-1"><?=$form->field($searchModel, "type")->dropDownList(['1'=>'价格修改','2'=>'供应商修改'],['class' => 'form-control','prompt' => '请选择'])->label('修改类型')?></div>

    <div class="col-md-1"><?= $form->field($searchModel,'update_user_name')->textInput(['placeholder'=>''])->label('人员搜索') ?></div>

    <div class="form-group col-md-2" style="margin-top: 24px;">
        <?= Html::submitButton(Yii::t('app', '搜索'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', '重置'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

    <?php //$this->render('_search', ['model' => $searchModel]); ?>
    <p class="clearfix"></p>
    <?= Html::a(Yii::t('app', '审核通过'), '#', [
        'id' => 'check',
        'class' => 'btn btn-success',
    ]);?>
    <?= Html::a(Yii::t('app', '审核不通过'), '#', [
        'id' => 'checkno',
        'class' => 'btn btn-danger',
    ]);?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        "options" => ["class" => "grid-view","style"=>"overflow:auto", "id" => "grid"],
        'pager'=>[
            //'options'=>['class'=>'hidden']//关闭分页
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
                    return ['value' => $model->sku];
                }

            ],
            [
                'label' =>'SKU创建时间',
                'format'=>'raw',
                'value' => function($model, $key, $index, $column){
                        return  $model->productDetail->create_time;
                    },

            ],
            [
                'label' => '上次修改供应商时间',
                'format'=> 'raw',
                'value' => function($model){
                    $data = SupplierUpdateApply::find()->where(['status'=>2,'sku'=>$model->sku])->orderBy('update_time DESC')->one();
                    return !empty($data) ? $data->update_time : '';
                }
            ],
            [
                'label' => 'SKU状态',
                'format'=> 'raw',
                'value' => function($model){
                    return !empty($model->productDetail) ? SupplierGoodsServices::getProductStatus($model->productDetail->product_status) : '';
                }
            ],
            [
                'label' => '品类',
                'format'=> 'raw',
                'value' => function($model){
                    return !empty($model->productDetail) ? !empty($model->productDetail->cat) ? $model->productDetail->cat->category_cn_name : '' : '';
                }
            ],
            [
                'label' => '图片',
                'format'=> 'raw',
                'value' => function($model){
                    $product = Vhelper::toSkuImg($model->sku,$model->productDetail->uploadimgs);
                    return $product;
                }
            ],
            [
                'label' => 'SKU',
                'format'=> 'raw',
                'value' => function($model){
                    return $model->sku;
                }
            ],
            [
                'label' => '商品名称',
                'format'=> 'raw',
                'value' => function($model){
                    return !empty($model->productDetail) ? !empty($model->productDetail->desc) ? $model->productDetail->desc->title : '' : '';
                }
            ],
            [
                'label' => '原单价',
                'format'=> 'raw',
                'value' => function($model){
                    return !empty($model->oldQuotes) ? $model->oldQuotes->supplierprice : '';
                }
            ],
            [
                'label' => '原供应商',
                'format'=> 'raw',
                'value' => function($model){
                    $supplier = !empty($model->oldSupplier) ? $model->oldSupplier->supplier_name : '';
                    $data = $supplier.'<br/>'.'供应商绑定sku数量：'.$model->old_product_num;
                    return !empty($model->oldSupplier) ? $data : '';
                }
            ],
            [
                'label' => '现单价',
                'format'=> 'raw',
                'value' => function($model){
                    return !empty($model->newQuotes) ? $model->newQuotes->supplierprice : '';
                }

            ],
            [
                'label' => '现供应商',
                'format'=> 'raw',
                'value' => function($model){
                    $supplier = !empty($model->newSupplier) ? $model->newSupplier->supplier_name : '';
                    $data = $supplier.'<br/>'.'供应商绑定sku数量：'.$model->new_product_num;
                    return !empty($model->newSupplier) ? $data : '';
                }
            ],
            [
                'label' => '申请人员',
                'format'=> 'raw',
                'value' => function($model){
                    return $model->create_user_name;
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
