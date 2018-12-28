<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\Url;
use yii\bootstrap\Modal;
/* @var $this yii\web\View */
/* @var $searchModel app\models\PurchaseSuggestSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '海外仓-采购建议';
$this->params['breadcrumbs'][] = $this->title;

Modal::begin([
    'id' => 'create-purchase-modal',
    'header' => '<h4 class="modal-title">系统信息</h4>',

    'footer' => '<a href="#" class="btn btn-primary" data-dismiss="modal">关闭</a>',
    'size'=>'modal-lg',
    'options'=>[
        'data-backdrop'=>'static',//点击空白处不关闭弹窗

    ],
]);
Modal::end();
?>
    <div class="purchase-suggest-index">
        <?php  echo $this->render('_search', ['model' => $searchModel]); ?>
        <p class="clearfix"></p>
        <p>
            <?php Html::a('海外仓-生成采购计划单', ['create-purchase'], ['class' => 'btn btn-success pp','id'=>'create-purchase','data-toggle' => 'modal','data-target' => '#create-purchase-modal']) ?>
            <?php Html::a('海外仓-修改采购员', ['editbuyer'], ['class' => 'btn btn-success','id'=>'edit-buyer','data-toggle' => 'modal','data-target' => '#create-purchase-modal']) ?>
        </p>
        <h4><span class="glyphicon glyphicon-heart" style="color: red" aria-hidden="true"></span>温馨小提示:建议数量为零的我们都不会显示的！<!--还有,你发现日均销量为零的话,恭喜你！这是一个刚开发的新品,因为出库才算销量,所以缺的数量就成了我们要采购的数量(你也可以算上安全交期)--></h4>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            //'filterModel' => $searchModel,
            'options'=>[
                'id'=>'grid_purchase',
            ],
            'pager'=>[
                //'options'=>['class'=>'hidden']//关闭分页
                'firstPageLabel'=>"首页",
                'prevPageLabel'=>'上一页',
                'nextPageLabel'=>'下一页',
                'lastPageLabel'=>'末页',
            ],
            'columns' => [
                ['class' => 'yii\grid\CheckboxColumn','name'=>'id'],

                [
                    'label'=>'采购员',
                    'attribute'=>'skus',
                    'format' => 'raw',
                    'value'=>function($data){
                        return $data->buyer;
                    }
                ],
                [
                    'label'=>'SKU',
                    'attribute'=>'product_category_ids',
                    'format' => 'raw',
                    'value'=>function($data){

                        return $data->sku;
                    }
                ],
                [
                    'label'=>'产品分类',
                    'attribute'=>'category_cn_names',
                    'format' => 'raw',
                    'value'=>function($data){

                        return $data->category_cn_name;
                    }
                ],
                [
                    'label'=>'产品名称',
                    'attribute'=>'warehouse_codes',
                    'format' => 'raw',
                    'value'=> function($data){
                        return $data->name;
                    }
                ],
                [
                    'label'=>'供应商',
                    'attribute'=>'stocks',
                    'format' => 'raw',
                    'value'=> function($data){

                        $str=$data->supplier_name;
                        return $str;
                    }
                ],
                [
                    'label'=>'单价',
                    'attribute'=>'skus',
                    'format' => 'raw',
                    'value'=> function($data){

                        return $data->price;
                    }
                ],
                [
                    'label'=>'建议采购量',
                    'attribute'=>'qtys',
                    'format' => 'raw',
                    'value'=> function($data){
                        return $data->qty;
                    }
                ],
                [
                    'label'=>'安全交期',
                    'attribute'=>'qtsy',
                    'format' => 'raw',
                    'value'=> function($data){
                        return $data->safe_delivery;
                    }
                ],
                [
                    'label'=>'日均销量',
                    'attribute'=>'qtys',
                    'format' => 'raw',
                    'value'=> function($data){

                        return $data->sales_avg;
                    }
                ],

                [
                    'label'=>'当前库存',
                    'attribute'=>'qtys',
                    'format' => 'raw',
                    'value'=> function($data){

                        return $data->available_stock;
                    }
                ],
                [
                    'label'=>'在途库存',
                    'attribute'=>'qtys',
                    'format' => 'raw',
                    'value'=> function($data){
                        return $data->on_way_stock;
                    }
                ],

                [
                    'label'=>'订单缺货总数',
                    'attribute'=>'qtys',
                    'format' => 'raw',
                    'value'=> function($data){
                        return ($data->left_stock<0)?$data->left_stock:'0';
                    }
                ],

                [
                    'label'=>'采购数量',
                    'attribute'=>'qtys',
                    'format' => 'raw',
                    'value'=> function($data){
                        $str='';
                        $str.=Html::input('number', 'username', $data->qty, ['class' => 'input-small qty','min'=>1,'readonly'=>'true','id'=>$data->id,'old_value'=>$data->qty]) ;
                        return $str;
                    }
                ],




            ],
            'containerOptions' => ["style"=>"overflow:auto"], // only set when $responsive = false
            'toolbar' =>  [
                '{export}',
                //'{toggleData}'
            ],


            'pjax' => false,
            'bordered' => true,
            'striped' => false,
            'condensed' => true,
            'responsive' => true,
            'hover' => true,
            'floatHeader' => false,
            'showPageSummary' => false,
            'toggleDataOptions' =>[
                      'maxCount' => 5000,
                      'minCount' => 1000,
                      'confirmMsg' => Yii::t(
                            'app',
                          '有{totalCount} 记录. 您确定要全部显示?',
                          ['totalCount' => number_format($dataProvider->getTotalCount())]
                       ),
                'all' => [
                    'icon' => 'resize-full',
                    'label' => Yii::t('app', '所有'),
                    'class' => 'btn btn-default',

                ],
                'page' => [
                    'icon' => 'resize-small',
                    'label' => Yii::t('app', '单页'),
                    'class' => 'btn btn-default',

                ],
            ],
            'exportConfig' => [
                GridView::EXCEL => [],
            ],
            'panel' => [
                //'heading'=>'<h3 class="panel-title"><i class="glyphicon glyphicon-globe"></i> Countries</h3>',
                'type'=>'success',
                //'after'=>Html::a('<i class="glyphicon glyphicon-repeat"></i> 刷新', ['index'], ['class' => 'btn btn-info']),
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
                 if($(this).hasClass("pp"))
                    {
                        url = '/overseas-purchase-suggest/create-purchase';
                    }
                url=url+'?ids='+ids;
                $(this).attr('href',url);
                $.post(url, {},
                    function (data) {
                        $('#create-purchase-modal').find('.modal-body').html(data);
                    }
                );
            }
        });
        
        //修改采购员
        $("a#edit-buyer").click(function(){
            var url=$(this).attr("href");
            var ids = $('#grid_purchase').yiiGridView('getSelectedRows');
            if(ids==''){
               alert('请选择要修改的采购员');
               return false;
            }else{
               $.post(url, {id: ids},
                    function (data) {
                        $('.modal-body').html(data);
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