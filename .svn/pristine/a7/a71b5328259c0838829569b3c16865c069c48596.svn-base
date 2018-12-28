<?php
$startTime10 = microtime(true);

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use app\config\Vhelper;
use app\models\Product;
use app\models\PurchaseOrderItems;
use mdm\admin\components\Helper;
use app\services\SupplierGoodsServices;
use app\services\PurchaseOrderServices;
use app\services\BaseServices;
use app\models\SupervisorGroupBind;

/* @var $this yii\web\View */
/* @var $searchModel app\models\PurchaseSuggestSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '采购建议-new';
$this->params['breadcrumbs'][] = $this->title;
$bool = SupervisorGroupBind::getGroupPermissions(38);


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
?>
    <style type="text/css">
        .table-bordered tr, .table-bordered td, .table-bordered th{border: 1px solid #cccccc !important; background-color: white}
        .img-rounded{width: 60px; height: 60px; !important;}
        .reds{
            color: red;
        }
    </style>
    <div class="purchase-suggest-index">
        <?php  echo $this->render('_search', ['model' => $searchModel]); ?>
        <p class="clearfix"></p>
        <p>
            <?= Html::a('生成采购单', ['create-purchase'], ['class' => 'btn btn-success pp','id'=>'create-purchase','data-toggle' => 'modal','data-target' => '#create-purchase-modal']) ?>
            <?= Html::a('修改采购员', ['#'], ['class' => 'btn btn-success','id'=>'edit-buyer','data-toggle' => 'modal','data-target' => '#create-purchase-modal']) ?>
            <a href="javascript:void(0)" class="btn btn-success" data-toggle="modal" data-target="#create-purchase-modal" id='batch-create-purchase'>批量生成采购单 <span class="badge"><?= $sug_total ?></span></a>
            <?php
            if(Helper::checkRoute('suggest-notes')) {
                echo Html::a('提交未处理原因', ['suggest-notes', 'name' => 'suggest-notes'], ['class' => 'btn btn-info', 'id' => 'suggest-notes', 'data-toggle' => 'modal', 'data-target' => '#created-modal']);
            }
            ?>
            <?= Html::button('导出Excel',['class' => 'btn btn-success','id'=>'export-csv']) ?>
        </p>

        <h4><span class="glyphicon glyphicon-heart" style="color: red" aria-hidden="true"></span>温馨小提示:建议数量为零的我们都不会显示的！</h4>
        <h4>
            总计:<span class="reds"><?=isset($status['total']) ? $status['total'] : 0?></span>
            审核不通过:<span class="reds"><?=isset($status['0']) ? $status['0'] : 0?></span>
            刚开发:<span class="reds"><?=isset($status['1']) ? $status['1'] : 0?></span>
            编辑中:<span class="reds"><?=isset($status['2']) ? $status['2'] : 0?></span>
            预上线:<span class="reds"><?=isset($status['3']) ? $status['3'] : 0?></span>
            在售中:<span class="reds"><?=isset($status['4']) ? $status['4'] : 0?></span>
            已滞销:<span class="reds"><?=isset($status['5']) ? $status['5'] : 0?></span>
            待清仓:<span class="reds"><?=isset($status['6']) ? $status['6'] : 0?></span>
            已停售:<span class="reds"><?=isset($status['7']) ? $status['7'] : 0?></span>
            刚买样:<span class="reds"><?=isset($status['8']) ? $status['8'] : 0?></span>
            待品检:<span class="reds"><?=isset($status['9']) ? $status['9'] : 0?></span>
            拍摄中:<span class="reds"><?=isset($status['10']) ? $status['10'] : 0?></span>
            产品审核中:<span class="reds"><?=isset($status['11']) ? $status['11'] : 0?></span>
            修图中:<span class="reds"><?=isset($status['12']) ? $status['12'] : 0?></span>
            设计审核中:<span class="reds"><?=isset($status['14']) ? $status['14'] : 0?></span>
            文案审核中:<span class="reds"><?=isset($status['15']) ? $status['15'] : 0?></span>
            文案主管终审中:<span class="reds"><?=isset($status['16']) ? $status['16'] : 0?></span>
            总采购数:<span class="reds"><?=  Yii::$app->session->get('suggest_total_num',0);?></span>
            总采购金额:<span class="reds"><?= Yii::$app->session->get('suggest_total_money',0);?></span>
        </h4>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'options'=>[
                'id'=>'grid_purchase',
            ],
            'filterSelector' => "select[name='".$dataProvider->getPagination()->pageSizeParam."'],input[name='".$dataProvider->getPagination()->pageParam."']",
            'pager'=>[
                'options'=>['class' => 'pagination','style'=> "display:block;"],
                'class'=>\liyunfang\pager\LinkPager::className(),
                'pageSizeList' => [20, 50, 100, 200,500],
                'firstPageLabel'=>"首页",
                'prevPageLabel'=>'上一页',
                'nextPageLabel'=>'下一页',
                'lastPageLabel'=>'末页',
            ],
            'columns' => [
                ['class' => 'kartik\grid\CheckboxColumn','name'=>'id'],

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
                        $html = Html::a($data->sku,['purchase-suggest/paddress', 'sku' => $data->sku,'img' => $data->product_img], ['class' => "sku", 'style'=>'margin-right:5px;', 'title' => 'sku','data-toggle' => 'modal', 'data-target' => '#created-modal']).SupplierGoodsServices::getSkuStatus($data->sku).SupplierGoodsServices::getSkuDown($data->sku);
                        $html .= \app\models\ProductRepackageSearch::getPlusWeightInfo($data->sku,true,1);// 加重SKU标记
                        return $html;
                    }
                ],

                /*[
                    'label'=>'图片',
                    'attribute'=>'product_img',
                    'format' => 'raw',
                    'value'=> function($data){
                        $img=Vhelper::toSkuImg($data->sku,$data->product_img);
                        return Html::a($img,['purchase-suggest/img', 'sku' => $data->sku,'img' => $data->product_img], ['class' => "img", 'style'=>'margin-right:5px;', 'title' => '大图查看','data-toggle' => 'modal', 'data-target' => '#created-modal']);
                    }
                ],*/
                [
                    'label'=>'产品类别',
                    'value'=>function($model){

                        return $model->category_cn_name;
                    }
                ],
                [
                    'label'=>'仓库名称',
                    'value'=>function($model){
                        return $model->warehouse_name;
                    }
                ],
                [
                    'label'=>'产品状态',
                    'attribute'=>'category_cn_names',
                    'format' => 'raw',
                    'value'=>function($data){
                        //$status = !empty($data->product_status)?$data->product_status:'';
                        return isset($data->product_status)?\app\services\SupplierGoodsServices::getProductStatus($data->product_status):"未知";
                    }
                ],
                [
                    'label'=>'货源状态',
                    'format'=>'raw',
                    'value'=>function($data){
                        $sourceStatus = SupplierGoodsServices::getProductSourceStatus();
                        return !empty($data->product->sourceStatus)&& isset($sourceStatus[$data->product->sourceStatus->sourcing_status]) ? $sourceStatus[$data->product->sourceStatus->sourcing_status] :'正常';
                    }
                ],
                [
                    'label'=>'产品名称',
                    'attribute'=>'name',
                    'format' => 'raw',
                    'width' => '5%',
                    'value'=> function($data){
                        $url=Yii::$app->params['SKU_ERP_Product_Detail'].$data->sku;
                        return "<a target='_blank' href=$url>$data->name</a>";
                    }
                ],

                [
                    'label'=>'供应商',
                    'attribute'=>'supplier_code',
                    'format' => 'raw',
                    'visible'=>$bool,
                    'value'=> function($data){
                        $str=$data->supplier_name;
                        if (empty($str)) {

                            $str = BaseServices::getSupplierName($data->supplier_code) ? : "请完善供应商资料";
                        }
                        return $str;
                    }
                ],
                [
                    'label'=>'单价',
                    'attribute'=>'skus',
                    'format' => 'raw',
                    'visible'=>$bool,
                    'value'=> function($data){
                        $defaultPrice = \app\models\ProductProvider::find()
                                        ->select('q.supplierprice')
                                        ->alias('t')
                                        ->leftJoin(\app\models\SupplierQuotes::tableName().'q','t.quotes_id=q.id')
                                        ->where(['t.sku'=>$data->sku,'t.is_supplier'=>1])->scalar();
                        //$pro_price=\app\models\Product::findOne(['sku'=>$data->sku])['product_cost'];
                        $pro_price=$data->product_cost;
                        if(!empty($defaultPrice)){//优先显示最新采购价
                            $price=$defaultPrice;
                        }elseif(!empty($data->price)){
                            $price=$data->price;
                        }else{
                            $price=$pro_price;
                        }
                        return $price;
                    }
                ],
                [
                    'label'=>'备货逻辑',
                    'attribute'=>'qtsy',
                    'format' => 'raw',
                    'value'=> function($data){
                        return PurchaseOrderServices::getStockLoginType($data->stock_logic_type);
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
                    'label'=>'可用库存',
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
                    'label'=>'欠货',
                    'attribute'=>'qtys',
                    'format' => 'raw',
                    'value'=> function($data){
                        return ($data->left_stock>=0)?0:$data->left_stock;
                    }
                ],

//                [
//                    'label'=>'采购数量',
//                    'attribute'=>'qty',
//                    'format' => 'raw',
//                    'value'=> function($data){
//                        $str='';
//                        $str.=Html::input('number', 'username', $data->qty, ['class' => 'input-small qty','min'=>1,'readonly'=>'true','id'=>$data->id,'old_value'=>$data->qty]) ;
//                        return $str;
//                    }
//                ],
                [
                    'label'=>'采购数量',
                    'attribute'=>'qty',
                    'format' => 'raw',
                    'width'=>'80px',
                    'pageSummary'=>true,
                    'value'=> function($data){
                      // return
                        $status = \app\models\PurchaseSuggestQuantity::isExportQuantity($data->sku,$data->warehouse_code);
                        if (empty($status)) {
                            return $data->qty.'&nbsp&nbsp'.Html::a('',['purchase-suggest-mrp/qty-view', 'sku' => $data->sku,'warehouse_code' => $data->warehouse_code], ['class' => "data-qty glyphicon glyphicon-list", 'style'=>'margin-right:5px;', 'title' => 'sku','data-toggle' => 'modal', 'data-target' => '#created-modal']);;
                        } else {
                            return $data->qty.'&nbsp&nbsp'.Html::a('',['purchase-suggest-mrp/qty-view', 'sku' => $data->sku,'warehouse_code' => $data->warehouse_code], ['class' => "data-qty 	glyphicon glyphicon-plus", 'style'=>'margin-right:5px;color:red;', 'title' => 'sku','data-toggle' => 'modal', 'data-target' => '#created-modal']);;
                        }
                    }
                ],
                [
                    'label'=>'采购金额',
                    'format'=>'raw',
                    'visible'=>$bool,
                    'pageSummary'=>true,
                    'value'=>function($data){
                        // 查询默认供应商的报价
                        $pur_price = \app\models\ProductProvider::find()
                            ->select('q.supplierprice')
                            ->alias('t')
                            ->leftJoin(\app\models\SupplierQuotes::tableName().'q','t.quotes_id=q.id')
                            ->where(['t.sku'=>$data->sku,'t.is_supplier'=>1])->scalar();
                        return $pur_price*$data->qty;
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
                    'label'=>'SKU创建时间',
                    'format' => 'raw',
                    'value'=> function($data){
                        return $data->create_time;
                    }
                ],
                [
                    'label'=>'预计到货时间',
                    'format' => 'raw',
                    'value'=> function($data){
                        $platform_order_number = \app\models\PurchaseOrder::getLastPlatformNum($data->sku);//$data->sku   部分页面增加账号的显示
                        $res = PurchaseOrderItems::getOrderOneInfo($data->sku,[3,5,6,7,8,9,10]);
                        $data = '更新时间：'. (!empty($res->audit_time)?$res->audit_time:'') . '<br />'; //审核通过时间
                        $data .= '预计到货时间：'. (!empty($res->date_eta)?$res->date_eta:'') . '<br />'; //预计到货时间
                        $data .= '创建人：'. (!empty($res->creator)?$res->creator:'') . '<br />'; //创建人

                        $data .= '拍单号：'. $platform_order_number . '<br />'; //拍单号*/

                        return $data;
                    }
                ],
                [
                    'attribute' => 'untreated_time',
                    'format' => 'raw',
                    'value' => function ($data) {
                        return $data->untreated_time  . '天';
                    }
                ],
                [
                    'attribute'=>'state',
                    'format' => 'raw',
                    'value'=> function($data){
                    if(isset($data->state)){
                        return \app\services\PurchaseOrderServices::getProcesStatus()[$data->state];
                    }else{
                        return '';
                    }
                    }
                ],

                [
                    'label'=>'未处理原因',
                    'format' => 'raw',
                    'value'=> function($data){
                        $create_time = !empty($data->purchaseSuggestNote)?$data->purchaseSuggestNote->create_time:'';
                        $creator = !empty($data->purchaseSuggestNote->creator)?$data->purchaseSuggestNote->creator:'';
                        return Html::input('text','suggest-note',!empty($data->purchaseSuggestNote)?$data->purchaseSuggestNote->suggest_note:'',['readonly'=>'readonly','sku'=>$data->sku,'warehouse_code'=>$data->warehouse_code,'style'=>'width:100px','note'=>!empty($data->purchaseSuggestNote)?$data->purchaseSuggestNote->suggest_note:'']) . '<br />' . $create_time . '<br />' . $creator;
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
            'floatHeader' => true,
            'showPageSummary' => true,
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
$endTime10 = microtime(true);
if (isset($_REQUEST['is_debug']))
{
    echo 'Point 10:';
    var_dump($endTime10 - $startTime10);
    exit;
}
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
$urls = Url::toRoute('purchase-sum-import');
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
                if($(this).hasClass("pp")){
                    url = '/purchase-suggest-mrp/create-purchase';
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
        //批量生产采购单
        $('#batch-create-purchase').click(function() {
            var total = $(this).find('span.badge').html();
            var ids = $('#grid_purchase').yiiGridView('getSelectedRows');
            //有勾选生成单号
            if(ids!=''){
                $.post('/purchase-suggest-mrp/lead-suggest', {ids: ids},function(data){
                    $('#create-purchase-modal').find('.modal-body').html(data);
                });
            }else if(parseInt(total) <= 0) {
                alert('当前没有符合条件的采购建议');
                return false;
            } else {
                $.post('/purchase-suggest-mrp/lead-suggest', function(data){
                    $('#create-purchase-modal').find('.modal-body').html(data);
                });
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
               $.get('/purchase-suggest-mrp/editbuyer', {id: ids},function (data) {
                        $('#create-purchase-modal').find('.modal-body').html(data);
                        // $('.modal-body').html(data);
                    });
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
    //查看数量详情
    $(document).on('click', '.data-qty', function () {
        $.get($(this).attr('href'), {},
            function (data) {
               $('#created-modal').find('.modal-body').html(data);
            }
        );
    });
    //双击编辑报价
    $("input[name='suggest-note']").dblclick(function(){
            $(this).removeAttr("readonly");
        });
    //失焦添加readonly
    $("input[name='suggest-note']").change(function(){
        $(this).attr("readonly","true");
        var suggest_note = $(this).val();
        var sku   = $(this).attr('sku');
        var warehouse_code   = $(this).attr('warehouse_code');
        var old_note = $(this).attr('note');
        var obj = $(this);
        var message = '是否更新采购建议备注!<br/>'+'sku:'+sku+'<br/>仓库编码：'+warehouse_code+'<br/>原备注内容:'+old_note+'<br/>新备注内容:'+suggest_note;
        layer.confirm(message,{
         btn: ['提交','取消']
         ,cancel: function(index, layero){
             layer.msg('取消成功');
             obj.val(old_note);
        }
        },function() {
          $.ajax({
            url:'update-suggest-note',
            data:{suggest_note:suggest_note,sku:sku,warehouse_code:warehouse_code},
            type: 'get',
            dataType:'json',
            success:function(data) {
              if(data.status=='success'){
                  obj.attr('note',suggest_note);
              }
              layer.msg(data.message);
            }
            });
        },function() {
          layer.msg('取消成功');
          obj.val(old_note);
        });
       });

     $(document).on('click', '#suggest-notes', function () {
         var ids = $('#grid_purchase').yiiGridView('getSelectedRows');
          if(ids==''){
                alert('请先选择!');
                return false;
            }else{
               $.get($(this).attr('href'), {id: ids},
                    function (data) {
                        $('.modal-body').html(data);
                    }
                );
            }
    });
     //批量导出
     $('#export-csv').click(function() {
            var ids = $('#grid_purchase').yiiGridView('getSelectedRows');
            /*if(ids==''){
                alert('请先选择!');
                return false;
            }else{*/

                window.location.href='/purchase-suggest-mrp/export-csv?ids='+ids;
            /*}*/
     })
JS;
$this->registerJs($js);
?>
