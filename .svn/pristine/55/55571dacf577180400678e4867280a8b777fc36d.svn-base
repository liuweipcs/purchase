<?php
use yii\helpers\Html;
use yii\bootstrap\Modal;
use kartik\grid\GridView;
use mdm\admin\components\Helper;
use app\services\BaseServices;
$this->title = 'FBA停售缺货产品列表';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="panel panel-success">
    <div class="panel-body">
        <?= $this->render('_search', ['model' => $searchModel]); ?>
    </div>
    <div class="panel-footer">
        <?php
        if(Helper::checkRoute('create-order'))
        {
            echo Html::a('生成采购单', ['create-order'], ['class' => 'btn btn-success','id'=>'create-order','data-toggle' => 'modal', 'data-target' => '#amazon-outofstock']);
        }
        ?>
        <?= Html::button('导出Excel',['class' => 'btn btn-success','id'=>'export-csv']) ?>
    </div>
</div>
<?php
echo GridView::widget([
    'dataProvider'=>$dataProvider,
    'filterSelector' => "select[name='".$dataProvider->getPagination()->pageSizeParam."'],input[name='".$dataProvider->getPagination()->pageParam."']",
    'pager' => [
        'class' => \liyunfang\pager\LinkPager::className(),
        'options'=>['class' => 'pagination','style'=> "display:block;"],
        'template' => '{pageButtons} {customPage} {pageSize}', //分页栏布局
        'pageSizeList' => [50,100,200,300,500,1000], //页大小下拉框值
        'customPageWidth' => 50,            //自定义跳转文本框宽度
        'customPageBefore' => ' 跳转到第 ',
        'customPageAfter' => ' 页 ',
    ],
    'options'=>[
        'id'=>'amazon',
    ],
    'columns'=>[
        [
            'class'=>'kartik\grid\CheckboxColumn',
            'name'=>"id",
            'checkboxOptions'=>function ($model,$key,$index,$column){
                return ['value'=>$model->id];
            }
        ],
        'amazon_order_id',
        'demand_number',
        [
            'label'=>'SKU',
            'format'=>'raw',
            'attribute'=>'sku',
            'value'=>function($model){
                $html = $model->sku;
                $html .= \app\models\ProductRepackageSearch::getPlusWeightInfo($model->sku,true);// 加重SKU标记
                $html .='<br>'.Html::a('<span class="glyphicon glyphicon-stats" style="font-size:10px;color:cornflowerblue;" title="销量库存"></span>', ['product/get-stock-sales','sku'=>$model->sku],
                        [
                            'class' => 'btn btn-xs stock-sales-purchase',
                            'data-toggle' => 'modal',
                            'data-target' => '#amazon-outofstock',
                        ]);
                $html .=Html::a('<span class="glyphicon glyphicon-eye-open" style="font-size:10px;color:cornflowerblue;" title="历史采购记录"></span>', ['purchase-suggest/histor-purchase-info','sku'=>$model->sku],[
                    'data-toggle' => 'modal',
                    'data-target' => '#amazon-outofstock',
                    'class'=>'btn btn-xs stock-sales-purchase',
                ]);
                return $html;
            }
        ],
        [
            'label'=>'绑定供应商',
            'visible'=>in_array(Yii::$app->user->id,$accessUser) ?true :false,
            'value'=>function($model){
                return !empty($model->product->defaultSupplierDetail) ? $model->product->defaultSupplierDetail->supplier_name :'';
            }
        ],
        [
            'label'=>'采购员',
            'value'=>function($model){
               return $model->defaultSupplierLine?\app\models\PurchaseCategoryBind::getBuyer($model->defaultSupplierLine->first_product_line):'';
            }
        ],
        [
            'label'=>'产品名称',
            'value'=>function($model){
                return !empty($model->product) ? !empty($model->product->desc->title) ?  $model->product->desc->title : '无该产品名称' : '采购系统无该产品';
            }
        ],
        'purchase_num',
        'outofstock_num',
        [
            'label'=>'付款时间',
            'attribute'=>'pay_time',
            'value'=>function($model){
                return $model->pay_time;
            }
        ],
        [
            'label'=>'备注',
            'format'=>'raw',
            'value'=>function($model){
                return $model->note;
            }
        ],
        [
            'label'=>'状态',
            'format'=>'raw',
            'value'=>function($model){
                $statusArray = [0=>'未处理',1=>'已处理'];
                return isset($statusArray[$model->status]) ? $statusArray[$model->status] : '未知状态';
            }
        ],
        [
            'label'=>'采购单状态',
            'format'=>'raw',
            'value'=>function($model){
                $order =  \app\models\PurchaseDemand::find()
                    ->alias('t')
                    ->select('o.purchas_status')
                    ->leftJoin(\app\models\PurchaseOrder::tableName().' o','o.pur_number=t.pur_number')
                    ->where(['t.demand_number'=>$model->demand_number])
                    ->scalar();
                return $order ? \app\services\PurchaseOrderServices::getPurchaseStatusText($order) :'';
            }
        ],
        'create_time',
        'update_time',
        [
            'class'=>'kartik\grid\ActionColumn',
            'width'=>'180px',
            'header'=>'操作',
            'template'=>"{note}",
            'buttons'=>[
                'note' => function($url, $model, $key) {
                    return Html::a('<i class="glyphicon glyphicon-open"></i> 追加备注',['note','id' => $model->id], [
                        'title' => '提交',
                        'class' => 'btn btn-xs red note',
                        'data-toggle' => 'modal',
                        'data-target' => '#amazon-outofstock'
                    ]);
                },
            ]
        ]
    ],
    'containerOptions'=>["style"=>"overflow:auto"],
    'pjax'=>false,
    'bordered'=>true,
    'striped'=>false,
    'condensed'=>true,
    'responsive'=>true,
    'hover'=>true,
    'floatHeader'=>false,
    'showPageSummary'=>false,
    'exportConfig'=>[
        GridView::EXCEL=>[],
    ],
    'panel'=>[
        'type'=>'success',
    ]
]);
?>
<?php
Modal::begin([
    'id'=>'amazon-outofstock',
    'header'=>'',
    'footer'=>'<a href="#" class="btn btn-primary closes" data-dismiss="modal">关闭</a>',
    'size'=>'modal-lg',
    'options'=>[
        //'data-backdrop'=>'static',
    ]
]);
Modal::end();
$createUrl = \yii\helpers\Url::toRoute('create-order');
$js = <<<JS
$(function(){
   //批量导出
     $('#export-csv').click(function() {
          //搜索条件
          var buyer = $("select[name='AmazonOutofstockOrder[buyer]'] :selected").val();
          var supplier_code = $("select[name='AmazonOutofstockOrder[supplier_code]'] :selected").val();
          var sku = $("input[name='AmazonOutofstockOrder[sku]']").val();
          var amazon_order_id = $("input[name='AmazonOutofstockOrder[amazon_order_id]']").val();
          var status = $("select[name='AmazonOutofstockOrder[status]'] :selected").val();
          //勾选了id
          var ids = '';
          var len = $("input[name='id[]']:checked").length;
          if(len > 0){
              $("input[name='id[]']:checked").each(function(i){
        		ids += $(this).val()+",";
    		  });
              window.location.href='/fba-outofstock-order/export-csv?ids='+ids;  
          }else{
              window.location.href='/fba-outofstock-order/export-csv?buyer='+buyer+'&supplier_code='+supplier_code+'&sku='+sku+'&amazon_order_id='+amazon_order_id+'&status='+status;   
          }
     });
         
   $(document).on('click','#create-order',function() {
     var ids = $('#amazon').yiiGridView('getSelectedRows');
     if(ids.length==0){
         $('#amazon-outofstock').find('.modal-body').html('至少选择一个数据');
         return false;
     }else {
         $.get('{$createUrl}',{ids:ids.join(',')},function(data) {
            $('#amazon-outofstock').find('.modal-body').html(data);
            $('#amazon-outofstock').find('.modal-header').html('创建采购单');
         });
     }
   });
   $(document).on('click','.note',function() {
     $.get($(this).attr('href'),{},function(data) {
        $('#amazon-outofstock').find('.modal-body').html(data);
        $('#amazon-outofstock').find('.modal-header').html('追加备注');
     });
   });
   $(document).on('click', '.stock-sales-purchase', function () {
        $('#amazon-outofstock').find('.modal-body').html('正在请求数据....');
        $.get($(this).attr('href'), {},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
});
JS;
$this->registerJs($js);
?>

