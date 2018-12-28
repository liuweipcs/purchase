<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\Url;
use yii\bootstrap\Modal;
/* @var $this yii\web\View */
/* @var $searchModel app\models\PurchaseSuggestSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '采购建议';
$this->params['breadcrumbs'][] = $this->title;

Modal::begin([
    'id' => 'create-purchase-modal',
    'header' => '<h4 class="modal-title">采购单产品</h4>',
    
    //'footer' => '<a href="#" class="btn btn-primary" data-dismiss="modal">Close</a>',
    'size'=>'modal-lg',
]);
Modal::end();
?>
<div class="purchase-suggest-index">
    <?php  echo $this->render('_search', ['model' => $searchModel]); ?>
    <p class="clearfix"></p>
    <p>
        <?= Html::a('生成采购单', ['create-purchase'], ['class' => 'btn btn-success','id'=>'create-purchase','data-toggle' => 'modal','data-target' => '#create-purchase-modal']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        'options'=>[
            'id'=>'grid_purchase',
        ],
        'columns' => [
            ['class' => 'yii\grid\CheckboxColumn','name'=>'id'],
            'id',
            [
                'label'=>'产品信息',
                'attribute'=>'sku',
                'format' => 'raw',
                'value'=>function($data){
                    $str='';
                    $str.='供应商:'.$data->supplier_code." [{$data->supplier_name}] "."<br/>";
                    $str.='SKU:'.$data->sku."<br/>";
                    $str.='名称:'.$data->name."<br/>";
                    $str.='单价:'.$data->price."<br/>";
                    return $str;
                }
            ],
            [
                'label'=>'品类',
                'attribute'=>'product_category_id',
                'format' => 'raw',
                'value'=>function($data){
                    $str='';
                    $str.=$data->product_category_id." [{$data->category_cn_name}] "."<br/>";
                    return $str;
                }
            ],
            [
                'label'=>'品类',
                'attribute'=>'warehouse_code',
                'format' => 'raw',
                'value'=> function($data){
                    return $data->warehouse_code.' ['.$data->warehouse_name.']';
                }
            ],
            [
                'label'=>'库存综合查询',
                'attribute'=>'stock',
                'format' => 'raw',
                'value'=> function($data){
                    $str='';
                    $str.='在途数量:('. $data->on_way_stock.")<br/>";
                    $str.='可用数量:('. $data->available_stock.")<br/>";
                    $str.='实际数量:('. $data->stock.")<br/>";
                    $str.='欠货数量:('. $data->left_stock.")<br/>";
                    return $str;
                }
            ],
            [
                'label'=>'销售统计',
                'attribute'=>'sku',
                'format' => 'raw',
                'value'=> function($data){
                    $str='';
                    $str.='3 天销量:('. $data->days_sales_3.")<br/>";
                    $str.='7 天销量:('. $data->days_sales_7.")<br/>";
                    $str.='15天销量:('. $data->days_sales_15.")<br/>";
                    $str.='30天销量:('. $data->days_sales_30.")<br/>";
                    return $str;
                }
            ],
            [
                'label'=>'建议数量',
                'attribute'=>'qty',
                'format' => 'raw',
                'value'=> function($data){
                    $str='';
                    $str.=Html::input('number', 'username', $data->qty, ['class' => 'input-small qty','min'=>1,'readonly'=>'true','id'=>$data->id,'old_value'=>$data->qty]) ;
                    return $str;
                }
            ],
   
            'ship_method',
            'replenish_type',
        ],
        'containerOptions' => ["style"=>"overflow:auto"], // only set when $responsive = false
        'toolbar' =>  [
             '{export}',
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
            'after'=>Html::a('<i class="glyphicon glyphicon-repeat"></i> 刷新', ['index'], ['class' => 'btn btn-info']),
            //'footer'=>true
        ],
    ]); ?>
</div>
<?php
$url_update_qty = Url::toRoute('update-qty');
$js = <<<JS
    $(function(){
        //点击生成采购单
        $("a#create-purchase").click(function(){
            var ids = $('#grid_purchase').yiiGridView('getSelectedRows');
            if(ids==''){
                alert('请先选择需要生成的数据!');
                return false;
            }else{
                var url=$(this).attr("href");
                url=url+'?ids='+ids;
                $(this).attr('href',url);
                $.post(url, {},
                    function (data) {
                        $('#create-purchase-modal').find('.modal-body').html(data);
                    }
                );
            }
        });
        
        //双击修改建议数量
        $("input.qty").dblclick(function(){
            $(this).removeAttr("readonly");
        });
        //失焦添加readonly
        $("input.qty").blur(function(){
            $(this).attr("readonly","true");
        });
        $("input.qty").change(function(){
            var input_obj=$(this);
            var id=$(this).attr('id');
            var qty=this.value;
            var old_value=$(this).attr("old_value");
            if(confirm("确认修改")){
                $.post("{$url_update_qty}",{id:id,qty:qty},function(result){
                    if(result){
                        alert("操作成功");
                    }else{
                        alert("操作失败");
                        input_obj.val(old_value);
                    }
                });
            }else{
                input_obj.val(old_value);
            }
        });
    });
JS;
$this->registerJs($js);
?>