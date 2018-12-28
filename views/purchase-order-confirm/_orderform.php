<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\models\ProductCategory;
use app\services\BaseServices;

$this->title = '创建采购计划单';
$this->params['breadcrumbs'][] = ['label' => '采购订单确认', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
    <div class="purchase-order-index">


        <p class="clearfix"></p>
        <p>
            <?= Html::a('添加产品', ['add-temporary'], ['class' => 'btn btn-success','id'=>'adds']) ?>
        </p>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel'  => $searchModel,
            'options'=>[
                'id'=>'grid_purchase_addproduct',
            ],
            'pager'=>[
                'options'=>['class'=>'hidden'],//关闭自带分页
                'firstPageLabel'=>"First",
                'prevPageLabel'=>'Prev',
                'nextPageLabel'=>'Next',
                'lastPageLabel'=>'Last',
            ],
            'columns' => [
                [
                    'class' => 'kartik\grid\CheckboxColumn',
                    'name'=>"id" ,
                    'checkboxOptions' => function ($model, $key, $index, $column) {
                        return ['value' => $model->id];
                    }

                ],
                'desc.title',
                'sku',
                [
                    'label'=>'产品分类',
                    'attribute'=>'product_category_ids',
                    'format'=>'raw',
                    'value'=> function ($model) {
                        return !empty($model->product_category_id)?BaseServices::getCategory($model->product_category_id):'';
                    },
                    //'filter'=>BaseServices::getCategory(),
                ],
                'skusales.days_sales_7',
                'skusales.days_sales_15',
                'skusales.days_sales_30',
                //'skusales.days_sales_60',
                //'skusales.days_sales_90',
                'stock.on_way_stock',
                'stock.available_stock',
            ],
            'containerOptions' => ["style"=>"overflow:auto"], // only set when $responsive = false
            'toolbar' =>  [

                //'{export}',
            ],

            'pjax' => true,
            'bordered' => true,
            'striped' => false,
            'condensed' => true,
            'responsive' => true,
            'hover' => true,
            'floatHeader' => false,
            'showPageSummary' => false,

            'exportConfig' => [
                GridView::EXCEL => [],
            ],
            'panel' => [
                'heading'=>'<h3 class="panel-title"><i class="glyphicon glyphicon-globe"></i> Countries</h3>',
                'type'=>'success',
                // 'after'=>Html::a('<i class="glyphicon glyphicon-repeat"></i> 刷新', ['index'], ['class' => 'btn btn-info']),
                //'footer'=>true
            ],
        ]); ?>
    </div>

<?php
$url = Url::toRoute(['addproduct']);
$token = Yii::$app->request->getCsrfToken();
$js = <<<JS

    $(document).on('click', '#adds', function () {
            var str='';
            //获取所有的值
            $("input[name='id[]']:checked").each(function(){
                str+=','+$(this).val();
                //alert(str);

            })
            str=str.substr(1);
        var url=$(this).attr("href");
        if(str =='')
        {
            alert('请选择产品');
            return false;
        } else{

            $.ajax({

                        type:"post",
                        url:url,
                        data:{id:str,_csrf: "$token"},
                        dataType:"json",
                        success: function(data) {
                            if(data.code==1){
                                alert(data.msg);
                               window.location.href="$url";
                            }else{
                                alert(data.msg);
                            }
                        },
                        error:function() {
                            console.log(321)
                        }
                    })
                return false;
            }
    });

JS;

$this->registerJs($js);
?>