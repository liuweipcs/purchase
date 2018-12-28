<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use app\config\Vhelper;
use app\models\Product;
use app\services\PurchaseOrderServices;
use app\services\SupplierGoodsServices;
use app\models\PurchaseOrderItems;
use app\models\SupervisorGroupBind;
/* @var $this yii\web\View */
/* @var $searchModel app\models\PurchaseSuggestHistorySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '采购历史建议';
$this->params['breadcrumbs'][] = $this->title;

Modal::begin([
    'id' => 'create-purchase-modal',
    'header' => '<h4 class="modal-title">系统提示</h4>',

    'footer' => '<a href="#" class="btn btn-primary" data-dismiss="modal">关闭</a>',
    'size'=>'modal-lg',
    'options'=>[
        'data-backdrop'=>'static',//点击空白处不关闭弹窗

    ],
]);
Modal::end();

$bool = SupervisorGroupBind::getGroupPermissions(38);
?>
    <style type="text/css">
        .table-bordered tr, .table-bordered td, .table-bordered th{border: 1px solid #cccccc !important; background-color: white}
        .img-rounded{width: 60px; height: 60px; !important;}
        .reds{
            color: red;
        }
    </style>
       <!-- <table class="table table-bordered">
            <thead>
            <tr>
                <th>SKU总数量</th>
                <th>采购总额</th>
                <th>采购数量</th>
            </tr>
            </thead>
            <tbody>
            <tr class="table-module-b1">
                <td><?/*=$suggestsum[0]['total_snum']*/?></td>
                <td><?/*=$suggestsum['total_prices']*/?></td>
                <td><?/*=$suggestsum[0]['total_num']*/?></td>
            </tr>
            </tbody>
        </table>-->
    <div class="purchase-suggest-index">
        <?php  echo $this->render('_search', ['model' => $searchModel]); ?>
        <p class="clearfix"></p>
        <!--<p>
            /*= Html::a('生成采购单', ['create-purchase'], ['class' => 'btn btn-success pp','id'=>'create-purchase','data-toggle' => 'modal','data-target' => '#create-purchase-modal']) */
            /*= Html::a('修改采购员', ['#'], ['class' => 'btn btn-success','id'=>'edit-buyer',/*'data-toggle' => 'modal','data-target' => '#create-purchase-modal'*/]) */
        </p>-->
        <?= Html::a('导出Excel', ['#'], ['class' => 'btn btn-success print','id'=>'bulk-execl']) ?>
        <h4><span class="glyphicon glyphicon-heart" style="color: red" aria-hidden="true"></span>温馨小提示:建议数量为零的我们都不会显示的！</h4>
        <!--<h4>
            总计:<span class="reds"><?/*=$status['total']*/?></span>
            审核不通过:<span class="reds"><?/*=$status['0']*/?></span>
            刚开发:<span class="reds"><?/*=$status['1']*/?></span>
            编辑中:<span class="reds"><?/*=$status['2']*/?></span>
            预上线:<span class="reds"><?/*=$status['3']*/?></span>
            在售中:<span class="reds"><?/*=$status['4']*/?></span>
            已滞销:<span class="reds"><?/*=$status['5']*/?></span>
            待清仓:<span class="reds"><?/*=$status['6']*/?></span>
            刚买样:<span class="reds"><?/*=$status['8']*/?></span>
            待品检:<span class="reds"><?/*=$status['9']*/?></span>
            拍摄中:<span class="reds"><?/*=$status['10']*/?></span>
            产品审核中:<span class="reds"><?/*=$status['11']*/?></span>
            修图中:<span class="reds"><?/*=$status['12']*/?></span>
            设计审核中:<span class="reds"><?/*=$status['14']*/?></span>
            文案审核中:<span class="reds"><?/*=$status['15']*/?></span>
            文案主管终审中:<span class="reds"><?/*=$status['16']*/?></span>
        </h4>-->
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
//                ['class' => 'yii\grid\CheckboxColumn','name'=>'id'],

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
                        return Html::a($data->sku,['purchase-suggest/paddress', 'sku' => $data->sku,'img' => $data->product_img], ['class' => "sku", 'style'=>'margin-right:5px;', 'title' => 'sku','data-toggle' => 'modal', 'data-target' => '#created-modal']);
                    }
                ],
                [
                    'label'=>'产品类别',
                    'value'=>function($data){
                        return $data->category_cn_name;
                    }
                ],
                [
                    'label'=>'仓库名称',
                    'value'=>function($model){
                        return \app\services\BaseServices::getWarehouseCode($model->warehouse_code);
                    }
                ],

                [
                    'label'=>'产品状态',
                    'attribute'=>'category_cn_names',
                    'format' => 'raw',
                    'value'=>function($data){
                        //$status = !empty($data->product_status)?$data->product_status:'';
                        return isset($data->product_status)?SupplierGoodsServices::getProductStatus($data->product_status):"未知";
                    }
                ],
                [
                    'label'=>'产品名称',
                    'attribute'=>'name',
                    'format' => 'raw',
                    'value'=> function($data){
                        $url=Yii::$app->params['SKU_ERP_Product_Detail'].$data->sku;
                        return "<a target='_blank' href=$url>$data->name</a>";
                    }
                ],

                [
                    'label'=>'供应商',
                    'attribute'=>'supplier_name',
                    'format' => 'raw',
                    'visible'=>$bool,
                    'value'=> function($data){

                        $str=$data->supplier_name;
                        return $str;
                    }
                ],
                [
                    'label'=>'单价',
                    'format' => 'raw',
                    'visible'=>$bool,
                    'value'=> function($data){
                        return $data->price;
                    }
                ],
                [
                    'label'=>'备货天数',
                    'format' => 'raw',
                    'value'=> function($data){
                        return $data->safe_delivery;
                    }
                ],
                [
                    'label'=>'日均销量',
                    'format' => 'raw',
                    'value'=> function($data){

                        return $data->sales_avg;
                    }
                ],
                [
                    'label'=>'可用库存',
                    'format' => 'raw',
                    'value'=> function($data){
                        return $data->available_stock;
                    }
                ],
                [
                    'label'=>'在途库存',
                    'format' => 'raw',
                    'value'=> function($data){
                        return $data->on_way_stock;
                    }
                ],

               [
                    'label'=>'欠货',
                    'format' => 'raw',
                    'value'=> function($data){
                        return $data->available_stock+$data->on_way_stock+$data->left_stock;
                        //return ($data->left_stock<0)?$data->left_stock:'0';
                    }
                ],
                [
                    'label'=>'采购数量',
                    'attribute'=>'qty',
                    'format' => 'raw',
                    'value'=> function($data){
                        $status = \app\models\PurchaseSuggestQuantity::isExportQuantity($data->sku,$data->warehouse_code);
                        if (empty($status)) {
                            return $data->qty.'&nbsp&nbsp'.Html::a('',['purchase-suggest/qty-view', 'sku' => $data->sku,'warehouse_code' => $data->warehouse_code], ['class' => "data-qty glyphicon glyphicon-list", 'style'=>'margin-right:5px;', 'title' => 'sku','data-toggle' => 'modal', 'data-target' => '#created-modal']);;
                        } else {
                            return $data->qty.'&nbsp&nbsp'.Html::a('',['purchase-suggest/qty-view', 'sku' => $data->sku,'warehouse_code' => $data->warehouse_code], ['class' => "data-qty 	glyphicon glyphicon-plus", 'style'=>'margin-right:5px;color:red;', 'title' => 'sku','data-toggle' => 'modal', 'data-target' => '#created-modal']);;
                        }
                    }
                ],
                [
                    'label'=>'需求生成时间',
                    'attribute'=>'created_at',
                    'format' => 'raw',
                    'value'=> function($data){
                        return $data->created_at;
                    }
                ],
                [
                    'label'=>'预计到货时间',
                    'format' => 'raw',
                    'value'=> function($data){
                        $res = PurchaseOrderItems::getOrderOneInfo($data->sku,[3,5,6,7,8,9,10]);
                        $data = '更新时间：'. (!empty($res->audit_time)?$res->audit_time:'') . '<br />'; //审核通过时间
                        $data .= '预计到货时间：'. (!empty($res->date_eta)?$res->date_eta:'') . '<br />'; //预计到货时间
                        $data .= '创建人：'. (!empty($res->creator)?$res->creator:'') . '<br />'; //创建人
                        return $data;
                    }
                ],
                [
                    'attribute'=>'state',
                    'format' => 'raw',
                    'value'=> function($data){
                    if(isset($data->state)){
                        return !empty(PurchaseOrderServices::getProcesStatus()[$data->state]) ? PurchaseOrderServices::getProcesStatus()[$data->state] : '';
                    }else{
                        return '';
                    }
                    }
                ],
                [
                    'label'=>'未处理原因',
                    'format' => 'raw',
                    'value'=> function($data){
                        return Html::a('查看未处理原因',['purchase-suggest-history/get-history-note', 'sku' => $data->sku,'warehouse_code'=>$data->warehouse_code,'create_time'=>$data->created_at], ['class' => "histor-purchase-info", 'style'=>'margin-right:5px;', 'title' => 'sku','data-toggle' => 'modal', 'data-target' => '#created-modal']);
                    }
                ],
                [
                    'label'=>'历史采购信息',
                    'format' => 'raw',
                    'value'=> function($data){
                        return Html::a('查看',['purchase-suggest/histor-purchase-info', 'sku' => $data->sku], ['class' => "histor-purchase-info", 'style'=>'margin-right:5px;', 'title' => 'sku','data-toggle' => 'modal', 'data-target' => '#created-modal']);

                        //return "<a href='histor-purchase-info?sku=$data->sku'>查看历史采购信息</a>";
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
Modal::begin([
    'id' => 'created-modal',
    //'header' => '<h4 class="modal-title">系统信息</h4>',
    //'footer' => '<a href="#" class="btn btn-primary"  data-dismiss="modal"  >Close</a>',
    'closeButton' =>false,
    'size'=>'modal-lg',
    'options'=>[
        'z-index' =>'-1',

    ],
]);
Modal::end();

$url_update_qty = Url::toRoute('update-qty');
$editbuyer = Url::toRoute('editbuyer');
$js = <<<JS

    $(document).on('click', '.img', function () {
        $.get($(this).attr('href'), {},
            function (data) {
               $('#created-modal').find('.modal-body').html(data);
            }
        );
    });


    $(document).on('click', '.sku', function () {
        $.get($(this).attr('href'), {},
            function (data) {
               $('#created-modal').find('.modal-body').html(data);
            }
        );
    });
    
    $(document).on('click', '.histor-purchase-info', function () {
        $.get($(this).attr('href'), {},
            function (data) {
               $('#created-modal').find('.modal-body').html(data);
            }
        );
    });


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
                        url = '/purchase-suggest-history/create-purchase';
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
              /* $.post(url, {id: ids},
                    function (data) {
                        $('.modal-body').html(data);
                    }
                 );   */
             var url='{$editbuyer}?id='+ids;
             window.open(url);
            }
        });
        
        //双击修改建议数量
        $("input.qty").click(function(){
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
    
     $(document).on('click', '#bulk-execl', function () {
       /*var daterangepicker_start = $("input[name='daterangepicker_start']").val(); //时间段
        var daterangepicker_end = $("input[name='daterangepicker_end']").val(); //时间结束
        var state = $("#purchasesuggesthistorysearch-state option:selected").val(); //处理状态
        var sku = $("input[name='PurchaseSuggestHistorySearch[sku]']").val(); //sku
        var supplier_code = $("#select2-purchasesuggesthistorysearch-supplier_code-container").attr('title');//供应商
        var left = $("#purchasesuggesthistorysearch-left option:selected").val(); //是否欠货
        var product_status = $("#purchasesuggesthistorysearch-product_status option:selected").val(); //产品状态
        var buyer_id = $("#select2-purchasesuggesthistorysearch-buyer_id-container").attr('title'); //采购员*/

        var url = $(this).attr("href");
        if($(this).hasClass("print"))
        {
            url = '/purchase-suggest-history/export';
        }
        // url=url + '?daterangepicker_start=' + daterangepicker_start + '&daterangepicker_end=' + daterangepicker_end + '&sku=' + sku + '&state=' + state + '&supplier_code=' + supplier_code + '&left=' + left + '&product_status=' + product_status + '&buyer_id=' + buyer_id;
        $(this).attr('href',url);
    });
      //查看数量详情
    $(document).on('click', '.data-qty', function () {
        $.get($(this).attr('href'), {},
            function (data) {
               $('#created-modal').find('.modal-body').html(data);
            }
        );
    });
JS;
$this->registerJs($js);
?>