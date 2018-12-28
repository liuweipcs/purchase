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
use app\models\SupervisorGroupBind;
/* @var $this yii\web\View */
/* @var $searchModel app\models\PurchaseSuggestSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'FBA采购建议';
$this->params['breadcrumbs'][] = $this->title;
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
            总采购数:<span class="reds"><?=  Yii::$app->session->get('fba_suggest_total_num',0);?></span>
            总采购金额:<span class="reds"><?= Yii::$app->session->get('fba_suggest_total_money',0);?></span>
        </h4>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
//            'filterModel' => $searchModel,
            'options'=>[
                'id'=>'grid_purchase',
            ],
            'filterSelector' => "select[name='".$dataProvider->getPagination()->pageSizeParam."'],input[name='".$dataProvider->getPagination()->pageParam."']",
            'pager'=>[
                'options'=>['class' => 'pagination','style'=> "display:block;"],
                'class'=>\liyunfang\pager\LinkPager::className(),
                'pageSizeList' => [20, 50, 100, 200,500],
//                'options'=>['class'=>'hidden'],//关闭分页
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
                        return Html::a($data->sku,['purchase-suggest/paddress', 'sku' => $data->sku,'img' => $data->product_img], ['class' => "sku", 'style'=>'margin-right:5px;', 'title' => 'sku','data-toggle' => 'modal', 'data-target' => '#FBA-suggest-modal']);
                    }
                ],

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
                    'value'=> function($data){
                        $url=Yii::$app->params['SKU_ERP_Product_Detail'].$data->sku;
                        return "<a target='_blank' href=$url>$data->name</a>";
                    }
                ],

                [
                    'label'=>'供应商',
                    'attribute'=>'supplier_code',
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
                        $defaultPrice = \app\models\ProductProvider::find()
                            ->select('q.supplierprice')
                            ->alias('t')
                            ->leftJoin(\app\models\SupplierQuotes::tableName().'q','t.quotes_id=q.id')
                            ->where(['t.sku'=>$data->sku,'t.is_supplier'=>1])->scalar();
                        return $defaultPrice ? $defaultPrice :'';
                    }
                ],
                [
                    'label'=>'备货天数',
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
                        //return $data->lack_total;
                        return ($data->left_stock<0)?$data->left_stock:'0';
                    }
                ],
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
                            return $data->qty.'&nbsp&nbsp'.Html::a('',['purchase-suggest/qty-view', 'sku' => $data->sku,'warehouse_code' => $data->warehouse_code], ['class' => "data-qty glyphicon glyphicon-list", 'style'=>'margin-right:5px;', 'title' => 'sku','data-toggle' => 'modal', 'data-target' => '#FBA-suggest-modal']);;
                        } else {
                            return $data->qty.'&nbsp&nbsp'.Html::a('',['purchase-suggest/qty-view', 'sku' => $data->sku,'warehouse_code' => $data->warehouse_code], ['class' => "data-qty 	glyphicon glyphicon-plus", 'style'=>'margin-right:5px;color:red;', 'title' => 'sku','data-toggle' => 'modal', 'data-target' => '#FBA-suggest-modal']);;
                        }
                    }
                ],
                [
                    'label'=>'采购金额',
                    'format'=>'raw',
                    'pageSummary'=>true,
                    'value'=>function($data){
                        $defaultPrice = \app\models\ProductProvider::find()
                            ->select('q.supplierprice')
                            ->alias('t')
                            ->leftJoin(\app\models\SupplierQuotes::tableName().'q','t.quotes_id=q.id')
                            ->where(['t.sku'=>$data->sku,'t.is_supplier'=>1])->scalar();
                        return $defaultPrice ? $defaultPrice*$data->qty  : '';
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
                    'label'=>'历史采购信息',
                    'format' => 'raw',
                    'value'=> function($data){
                        return Html::a('查看',['purchase-suggest/histor-purchase-info', 'sku' => $data->sku], ['class' => "histor-purchase-info", 'style'=>'margin-right:5px;', 'title' => 'sku','data-toggle' => 'modal', 'data-target' => '#FBA-suggest-modal']);
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
Modal::begin([
    'id' => 'FBA-suggest-modal',
    //'header' => '<h4 class="modal-title">系统信息</h4>',
    //'footer' => '<a href="#" class="btn btn-primary"  data-dismiss="modal"  >Close</a>',
    'closeButton' =>false,
    'size'=>'modal-lg',
    'options'=>[
        'z-index' =>'-1',

    ],
]);
Modal::end();

$js = <<<JS
    $(document).on('click', '.img', function () {
        $.get($(this).attr('href'), {},
            function (data) {
               $('#FBA-suggest-modal').find('.modal-body').html(data);
            }
        );
    });
    $(document).on('click', '.sku', function () {
        $.get($(this).attr('href'), {},
            function (data) {
               $('#FBA-suggest-modal').find('.modal-body').html(data);
            }
        );
    });
    $(document).on('click', '.histor-purchase-info', function () {
        $.get($(this).attr('href'), {},
            function (data) {
               $('#FBA-suggest-modal').find('.modal-body').html(data);
            }
        );
    });
    //查看数量详情
    $(document).on('click', '.data-qty', function () {
        $.get($(this).attr('href'), {},
            function (data) {
               $('#FBA-suggest-modal').find('.modal-body').html(data);
            }
        );
    });
    $('#export-csv').click(function() {
            var ids = $('#grid_purchase').yiiGridView('getSelectedRows');
            window.location.href='/fba-purchase-suggest/export-csv?ids='+ids;
     })
JS;
$this->registerJs($js);
?>