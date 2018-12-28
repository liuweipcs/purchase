<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use app\services\PurchaseOrderServices;
use app\services\SupplierServices;
use app\services\BaseServices;
use app\config\Vhelper;
use mdm\admin\components\Helper;
use app\models\SupplierQuotes;
/* @var $this yii\web\View */
/* @var $searchModel app\models\TodayListSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '销售需求列表';
$this->params['breadcrumbs'][] = $this->title;
?>
<style>
    .pps{
        font-size: 16px;
        padding: 0 0;
    }
</style>
<div class="purchase-order-index">

    <?= $this->render('_ssearch', ['model' => $searchModel]); ?>
    <p class="clearfix"></p>
    <?php
    if(Helper::checkRoute('create'))
    {
        echo Html::a('创建需求', ['create'], ['class' => 'btn btn-info'/*,'id'=>'create' ,'data-toggle' => 'modal', 'data-target' => '#create-modal'*/]);
    }
    ?>
    <?php
    if(Helper::checkRoute('revoke-demand'))
    {
        echo Html::a('撤销需求', ['revoke-demand'], ['class' => 'btn btn-info', 'id' => 'submit-audit' /*,'data-toggle' => 'modal', 'data-target' => '#create-modal'*/]);
    }
    ?>

    <?php
    if(Helper::checkRoute('push-priority'))
    {
       echo  Html::button('优先推送',['class' => 'btn btn-danger', 'id' => 'push-priority' /*,'data-toggle' => 'modal', 'data-target' => '#create-modal'*/]);
    }
    ?>

    <?php
    if(Helper::checkRoute('purchase-sum-import'))
    {
        echo Html::a('采购需求导入', ['purchase-sum-import'], ['class' => 'btn btn-success purchase-sum-import', 'data-toggle' => 'modal', 'data-target' => '#create-modal']);
    }

    ?>

    <?php
    if(Helper::checkRoute('agree'))
    {
        echo Html::a('批量同意', ['agree'], ['class' => 'btn btn-info bulk-consent'/*,'data-toggle' => 'modal', 'data-target' => '#create-modal'*/]);
    }
    ?>

    <?php
    if(Helper::checkRoute('submit'))
    {
        echo Html::button('批量提交', ['class' => 'btn btn-success print batch-commit']);
    }
    ?>

    <?php
    if(Helper::checkRoute('init-agree'))
    {
        echo Html::button('批量审核', ['class' => 'btn btn-success print batch-init-agree']);
    }
    ?>

    <h4><p class="glyphicon glyphicon-heart pps" style="color: red" aria-hidden="true">温馨提示:</p><span style="color: red">
            <p class="pps">1.撤销的需求不可再还原,请看清楚了再撤销。驳回请重新修改,只有同意了才能创建采购单</p>
            <p class="pps">2.备货金额大于等于10000或者备货数量大于等于100,需待采购审核</p>
            <p class="pps">3.选择退税仓必须经销售主管同意，批量导入需特别注意仓库</p>
    </h4>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        'options'=>[
            'id'=>'grid_purchase_order',
        ],
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
        'columns' => [
            [
                'class' => 'kartik\grid\CheckboxColumn',
                'name'=>"id" ,
                'checkboxOptions' => function ($model, $key, $index, $column) {
                    return ['value' => $model->id,'level_audit_status'=>$model->level_audit_status,'demand_number'=>$model->demand_number];
                },
            ],
            [
                'label'=>'产品图片',
                'attribute'=>'uploadimgs',
                'format'=>'raw',
                'value' => function ($model) {
                    return \toriphes\lazyload\LazyLoad::widget(['src'=>Vhelper::getSkuImage($model->sku),'width'=>'110px','height'=>'110px']);
                    //return Html::img(Vhelper::downloadImg($model->sku,$model->product->uploadimgs,2),['width'=>'110px']);
                    //return Vhelper::toSkuImg($model->sku,$model->uploadimgs);
                }
            ],

            [
                'label'=>'产品信息',
                'attribute' => 'product_name',
                "format" => "raw",
                'value'=>
                    function($model){
                          //$firstLine = $model->product&&!empty($model->product->product_linelist_id) ? BaseServices::getProductLineFirst($model->product->product_linelist_id) : '';
                          //$suppliercode =!empty($model->supplierQuotes['quotes_id'])?SupplierQuotes::getFileds($model->supplierQuotes['quotes_id'],'suppliercode')->suppliercode:'';
                          $productName = !empty($model->desc) ? $model->desc->title : '';
                          $data = '产品名：'.$productName.'<br/>';
                          $data.= $model->product&&!empty($model->product->product_linelist_id)?'产品线：'.BaseServices::getProductLine($model->product->product_linelist_id).'<br/>':'';
                          //$data.= $model->product_category?'采购员：'.\app\models\PurchaseCategoryBind::getBuyer($firstLine).'<br/>':'';
                          $data.= $model->defaultSupplierLine?'采购员：'.\app\models\PurchaseCategoryBind::getBuyer($model->defaultSupplierLine->first_product_line).'<br/>':'';
                        //$data.= $model->product_category?'采购员：'.\app\models\PurchaseCategoryBind::getBuyer($model->product_category).'<br/>':'';
                          $link = !empty($model->defaultQuotes) ? $model->defaultQuotes->supplier_product_address : "https://1688.com";
                          $subHtml = \app\models\ProductRepackageSearch::getPlusWeightInfo($model->sku,true);// 加重SKU标记
                          $data.= '<span style="color:red">sku:'.Html::a($model->sku, Yii::$app->params['SKU_ERP_Product_Detail_Hide'].$model->sku,['target'=>'blank']).$subHtml.'</span><br/>';
                            $data .=Html::a('<span class="glyphicon glyphicon-stats" style="font-size:10px;color:cornflowerblue;" title="销量库存"></span>', ['product/get-stock-sales','sku'=>$model->sku],
                                    [
                                        'class' => 'btn btn-xs stock-sales-purchase',
                                        'data-toggle' => 'modal',
                                        'data-target' => '#create-modal',
                                    ]);
                            $data .=Html::a('<span class="glyphicon glyphicon-eye-open" style="font-size:10px;color:cornflowerblue;" title="历史采购记录"></span>', ['purchase-suggest/histor-purchase-info','sku'=>$model->sku,'role'=>'sales'],[
                                'data-toggle' => 'modal',
                                'data-target' => '#create-modal',
                                'class'=>'btn btn-xs stock-sales-purchase',
                            ]).'<br/>';
                          $data.= '<span style="color:#00a65a">需求单号:'.$model->demand_number.'</span><br/>';
//                          $data.= '<span style="color:#00a65a">供应商:'.BaseServices::getSupplierName($suppliercode).'</span></br>';
                        $data.= $model->is_purchase==1?'是否生成采购计划：<span style="color:red">未生成</span><br/>':'是否生成采购计划：<span style="color:#00a65a">已生成</span><Br/>';
                        //$data.= '销量统计:'. Html::a('',['product/viewskusales'], ['class' => "glyphicon glyphicon-signal b",'data'=>$model->sku , 'title' => '销量统计','data-toggle' => 'modal', 'data-target' => '#create-modal',]).'&nbsp;';
                          return $data;
                    },

            ],
            [
                'label' => '总金额',
                'value' =>function($model){
                    return empty($model->defaultSupplier->supplier_code) ? '无产品报价':app\controllers\PlatformSummaryController::actionGetPrice($model->defaultSupplier->supplier_code,$model->sku,$model->purchase_quantity);
                }
            ],
            [
                'attribute' => 'platform_number',
                "format" => "raw",
                'value'=>
                    function($model){
                        return  $model->platform_number;
                    },

            ],
            [
                'label' => '分组',
                'format'=> 'raw',
                'value' =>function($model){
                    return BaseServices::getAmazonGroupName($model->group_id);
                }
            ],
            [
                'attribute' => 'purchase_quantity',
                "format" => "raw",
                'value'=>
                    function($model){
                        return  '<span style="color:red">'.$model->purchase_quantity.'</span>';
                    },

            ],
            [
                'attribute' => 'purchase_warehouse',
                "format" => "raw",
                'value'=>
                    function($model){
                        return  $model->purchase_warehouse?BaseServices::getWarehouseCode($model->purchase_warehouse):'';
                    },

            ],
            [
                'attribute' => 'is_transit',
                "format" => "raw",
                'value'=>
                    function($model){
                        return  $model->is_transit==1?'<span style="color:red">否</span>':'<span style="color:#00a65a">是</span>';   //主要通过此种方式实现
                    },

            ],
            [
                'attribute' => 'is_back_tax',
                "format" => "raw",
                'value'=>
                    function($model){
                        return  $model->is_back_tax==1 ? '<span style="color:red">是</span>': ($model->is_back_tax==2 ? '<span style="color:#00a65a">否</span>' : '未知');
                    },
            ],
           /* [
                'attribute' => 'transit_warehouse',
                "format" => "raw",
                'value'=>
                    function($model){
                        return  $model->transit_warehouse?BaseServices::getWarehouseCode($model->transit_warehouse):'';
                    },

            ],*/
            [
                'label'=>'需求信息',
                'attribute' => 'create_id',
                "format" => "raw",
                'value'=>
                    function($model){
                        $data = '需求人:'.$model->create_id.'<br/>';
                        $data .='需求时间:'.$model->create_time;
                        return  $data;
                    },

            ],

            [
                'attribute' => 'level_audit_status',
                "format" => "raw",
                'value'=>
                    function($model){
                        if($model->init_level_audit_status==1){
                            return '待采购主管审核';
                        }
                        if($model->level_audit_status==1)
                        {
                            $str ='';
                            $str .= '<span style="color:#00a65a">'.Yii::$app->params['demand'][$model->level_audit_status].'</span>';
                            return $str;

                        } elseif($model->level_audit_status==2){

                            $str = '<span style="color:red">'.Yii::$app->params['demand'][$model->level_audit_status].'</span><br/>';
                            $str .= '原因：'.$model->audit_note;
                            return $str;

                        } elseif($model->level_audit_status==4){

                            $str = '<span style="color:red">'.Yii::$app->params['demand'][$model->level_audit_status].'</span><br/>';
                            $str .= '原因：'.$model->purchase_note;
                            return $str;

                        } else{
                            return  Yii::$app->params['demand'][$model->level_audit_status];
                        }

                    },

            ],
            [
                'label'=>'同意(驳回)信息',
                'attribute' => 'agree_user',
                "format" => "raw",
                'value'=>
                    function($model){
                        if($model->level_audit_status==4)
                        {
                            $data = '采购驳回人:'.$model->buyer.'<br/>';
                            $data .= '采购驳回时间:'.$model->purchase_time;
                            return  $data;
                        } else{

                            $data = '同意(驳回)人:'.$model->agree_user.'<br/>';
                            $data .= '同意(驳回)时间:'.$model->agree_time;
                            return  $data;
                        }

                    },

            ],
            [
                'label' => '销售信息',
                //'attribute' => 'sales_note',
                "format" => "raw",
                'value'=>
                    function($model){
                        $str = '销售备注：'.$model->sales_note.'<br>销售:'.$model->sales;
                        if(!empty($model->audit_note)){
                            $str .= "<br>驳回备注:".$model->audit_note;;
                        }
                        $str .= '<br />销售账号：' . $model->xiaoshou_zhanghao;
                        return  $str;
                    },

            ],
            [
                'label'=>'优先推送',
                'format'=>'raw',
                'value'=>function($model){
                    return $model->is_push_priority==1 ? '是' :'否';
                }
            ],
            [
                'class' => 'kartik\grid\ActionColumn',
                'dropdown' => false,
                'width'=>'180px',
                'template' => Helper::filterActionColumn('{update}{agree}{disagree}{init-agree}{init-disagree}{cancel}{submit}'),
                'buttons'=>[
                    'update' => function ($url, $model, $key)
                    {
                        if($model->init_level_audit_status != 1 && Yii::$app->user->identity->username != '刘楚雯'){
                            $arr= [2, 7];
                            if(in_array($model->level_audit_status,$arr)) {
                                return Html::a('<i class="fa fa-fw fa-check"></i>编辑', ['update', 'id' => $key], [
                                    'title' => Yii::t('app', '编辑'),
                                    'class' => 'btn btn-xs red'
                                ]);
                            }
                        }
                    },
                    'cancel' => function ($url, $model, $key)
                    {
                        if($model->init_level_audit_status != 1 && Yii::$app->user->identity->username != '刘楚雯'){
                            $arr= [2, 7];// 不允许编辑和提交
                            // '0'=>'待同意','1'=>'同意','3'=>'撤销','4'=>'采购驳回','5'=>'删除',
                            // '2'=>'驳回','6'=>'规则拦截','7'=>'待提交'
                            if(in_array($model->level_audit_status,$arr)) {
                                return Html::a('<i class="fa fa-fw fa-check"></i>撤销', ['cancel', 'id' => $key], [
                                    'title' => Yii::t('app', '撤销'),
                                    'class' => 'btn btn-xs red'
                                ]);
                            }
                        }
                    },
                    'submit' => function ($url, $model, $key)
                    {
                        if($model->init_level_audit_status != 1 && Yii::$app->user->identity->username != '刘楚雯'){
                            $arr= [2, 7];
                            if(in_array($model->level_audit_status,$arr)) {
                                return Html::a('<i class="fa fa-fw fa-check"></i>提交', ['submit', 'id' => $key], [
                                    'title' => Yii::t('app', '提交'),
                                    'class' => 'btn btn-xs red'
                                ]);
                            }
                        }
                    },
                    'agree' => function ($url, $model, $key)
                    {
                        if(Yii::$app->user->identity->username != '刘楚雯' && $model->init_level_audit_status != 1) {
                            $arr= [0];
                            if(in_array($model->level_audit_status,$arr)) {
                                $page=Yii::$app->request->get('page') ? Yii::$app->request->get('page') : 1;
                                return Html::a('<i class="fa fa-fw fa-check"></i>同意', ['agree', 'id' => $key,'page'=>$page], [
                                    'title' => Yii::t('app', '同意'),
                                    'class' => 'btn btn-xs red'
                                ]);
                            }
                        }
                    },
                    'disagree' => function ($url, $model, $key)
                    {
                        if(Yii::$app->user->identity->username != '刘楚雯' && $model->init_level_audit_status != 1) {
                            $arr= [0];
                            if(in_array($model->level_audit_status,$arr)) {
                                $page=Yii::$app->request->get('page') ? Yii::$app->request->get('page') : 1;
                                return Html::a('<i class="fa fa-fw fa-close"></i>驳回', ['disagree', 'id' => $key,'page'=>$page], [
                                    'title'       => Yii::t('app', '驳回'),
                                    'class'       => 'btn btn-xs disagree',
                                    'data-toggle' => 'modal',
                                    'data-target' => '#create-modal',
                                ]);
                            }
                        }
                    },
                    'init-agree' => function ($url, $model, $key)
                    {
                        if($model->init_level_audit_status == 1) {
                            if(Yii::$app->user->identity->username == '刘楚雯' || BaseServices::getIsAdmin()) {
                                $page = Yii::$app->request->get('page') ? Yii::$app->request->get('page') : 1;
                                return Html::a('<i class="fa fa-fw fa-check"></i>审核通过', ['init-agree', 'id' => $key, 'page' => $page], [
                                    'title' => Yii::t('app', '同意'),
                                    'class' => 'btn btn-xs red'
                                ]);
                            }
                        }
                    },
                    'init-disagree' => function ($url, $model, $key)
                    {
                        if($model->init_level_audit_status == 1) {
                            if(Yii::$app->user->identity->username == '刘楚雯' || BaseServices::getIsAdmin()) {
                                $page = Yii::$app->request->get('page') ? Yii::$app->request->get('page') : 1;
                                return Html::a('<i class="fa fa-fw fa-close"></i>审核驳回', ['init-disagree', 'id' => $key, 'page' => $page], [
                                    'title' => Yii::t('app', '驳回'),
                                    'class' => 'btn btn-xs disagree',
                                    'data-toggle' => 'modal',
                                    'data-target' => '#create-modal',
                                ]);
                            }
                        }
                    },
                ]

            ],
        ],
        'containerOptions' => ["style"=>"overflow:auto"], // only set when $responsive = false
        'toolbar' =>  [],


        'pjax' => false,
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
            //'heading'=>'<h3 class="panel-title"><i class="glyphicon glyphicon-globe"></i> Countries</h3>',
            'type'=>'success',
            //'after'=>Html::a('<i class="glyphicon glyphicon-repeat"></i> 刷新', ['index'], ['class' => 'btn btn-info']),
        ],
    ]); ?>
</div>
<?php
Modal::begin([
    'id' => 'create-modal',
    'header' => '<h4 class="modal-title">系统信息</h4>',
    'footer' => '<a href="#" class="btn btn-primary closes" data-dismiss="modal">关闭</a>',
    'size'=>'modal-lg',
    'options'=>[
        'data-backdrop'=>'static',//点击空白处不关闭弹窗

    ],
]);
Modal::end();

$page=Yii::$app->request->get('page') ? Yii::$app->request->get('page') : 1;
$requestUrl = Url::toRoute('view');
$pushUrl = Url::toRoute('push-priority');
$arrival='请选择需要标记到货日期的采购单';
$js = <<<JS
    $(function(){
            $("a#submit-audit").click(function(){
                var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
                if(ids==''){
                    alert('请先选择!');
                    return false;
                }else{
                    var url = $(this).attr("href");
                    if($(this).hasClass("print"))
                    {
                        url = '/purchase-order/print-data';
                    }
                    url     = url+'?ids='+ids;
                    $(this).attr('href',url);
                }
            });
        });
    $(document).on('click', '.disagree', function () {

        $.get($(this).attr('href'), {},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
    $(document).on('click', '.b', function () {

        $.get($(this).attr('href'), {sku:$(this).attr('data')},
            function (data) {
               $('.modal-body').html(data);
            }
        );
    });
    //同意的销售需求优先推送到采购需求页面
    $(document).on('click', '#push-priority', function () {
        var pushArray = new Array();
        var pushIds   = new Array();
        var noArray   = new Array();
        $('[name="id[]"]').each(function() {
          if($(this).is(':checked')){
              pushIds.push($(this).val());
              if($(this).attr('level_audit_status') != 1&&$.inArray($(this).attr('level_audit_status'),['4','5'])==-1){
                  pushArray.push($(this).attr('demand_number'));
              }
              if($.inArray($(this).attr('level_audit_status'),['4','5']) != -1){
                  noArray.push($(this).attr('demand_number'));
              }
          }
        });
        var message='';
        if(pushArray.length>0){
             message=message+'请先通过审核,需求单号：<br/>'+pushArray.join(',')+'<br/>如果已通过审核请刷新页面再操作';
        }
        if(noArray.length>0){
             message = message+'<br/>其中有几个需求单已删除货采购驳回不可操作：<br/>'+noArray.join(',')
        }
        if(message!=''){
             layer.alert(message);
             return false;
        }
        if(pushIds.length<=0){
            layer.alert('请选中要推送的需求');
            return false;
        }
        layer.confirm('是否优先推送选中的这几条需求？',{
         btn: ['优先推送','取消']
         ,cancel: function(index, layero){
             layer.msg('取消成功');
        }},function() {
          $.ajax({
            url:'push-priority',
            data:{ids:pushIds.join(',')},
            type: 'get',
            dataType:'json',
            success:function(data) {
              layer.msg(data.message);
              if(data.status=='success'){
                  for (var i=0;i<data.success_id.length;i++){
                      $('tr[data-key="'+data.success_id[i]+'"]').find('[data-col-seq="14"]').text('是');
                  }
              }
            }
            });
        },function() {
          layer.msg('取消成功');
        });
    });
    $(function(){
        //点击生成采购单
        $("a.create-purchase").click(function(){
            var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
            if(ids==''){
                alert('请先选择需要生成的数据!');
                return false;
            }else{
                var url=$(this).attr("href");
                if($(this).hasClass("pp"))
                    {
                        url = '/platform-summary/create-purchase-order';
                    }
                url=url+'?ids='+ids;
                $(this).attr('href',url);
                $.get(url, {},
                    function (data) {
                        $('.modal-body').html(data);
                    }
                );
            }
        });

        //批量同意
        $(".bulk-consent").click(function(){
            var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
            if(ids==''){
                alert('请先选择数据!');
                return false;
            }else{
                var url=$(this).attr("href");
                $.post(url, {ids:ids,page:"{$page}"},
                    function (data) {
                        $('.modal-body').html(data);
                    }
                );
               return false;
            }
        });

        //批量驳回
        $(".dismiss-batches").click(function(){
            var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
            if(ids==''){
                alert('请先选择数据!');
                return false;
            }else{
                var url=$(this).attr("href");
                $.post(url, {id:ids,page:"{$page}"},
                    function (data) {
                        $('.modal-body').html(data);
                    }
                );
            }
        });

        // 批量审核
        $(document).on('click', '.batch-init-agree', function() {
            var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
            if(ids == '') {
                layer.alert('请先勾选要批量审核的数据');
                return false;
            }

            $.ajax({
                url:'/platform-summary/init-agree',
                data:{ids:ids,page:"{$page}"},
                type: 'post',
                dataType:'json',
                success: function (data) {
                    layer.msg(data.msg);
                    window.location.reload();
                }
            });
        });

        // 批量提交
        $(document).on('click', '.batch-commit', function() {
            var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
            if(ids == '') {
                layer.alert('请先勾选要批量提交的数据');
                return false;
            }

            $.ajax({
                url:'/platform-summary/submit',
                data:{ids:ids,page:"{$page}"},
                type: 'post',
                dataType:'json',
                success: function (data) {
                    layer.msg(data.msg);
                    window.location.reload();
                }
            });
        });

        //采购需求批量导入
        $(".purchase-sum-import").click(function(){
            $.get($(this).attr('href'), {},
            function (data) {
                $('.modal-body').html(data);
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
    });



JS;
$this->registerJs($js);
?>
