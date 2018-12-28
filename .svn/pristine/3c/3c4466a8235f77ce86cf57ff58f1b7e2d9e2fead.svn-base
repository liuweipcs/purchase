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

$this->title = '采购需求汇总';
$this->params['breadcrumbs'][] = $this->title;
?>
<style>
    .pps{
        font-size: 16px;
        padding: 0 0;
    }
</style>
<div class="">
    <!---全部、规则拦截搜索框和按钮 开始--->
    <input type="hidden" id="tab" value="<?=$tab ?>">
    <div id="all" >
        <?= $this->render('_search', ['model' => $searchModel,'authData'=>$authData]); ?>
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
        if(Helper::checkRoute('create-purchase-order'))
        {
            Html::a('创建采购计划单', ['create-purchase-order'], ['class' => 'btn btn-info create-purchase pp',/*'data-toggle' => 'modal', 'data-target' => '#create-modal'*/]);
        }
        ?>

        <?php
        if(Helper::checkRoute('agree'))
        {
            echo Html::a('批量同意', ['agree'], ['class' => 'btn btn-primary over-bulk-consent']);
        }
        ?>

        <?php
        if(Helper::checkRoute('disagree'))
        {
            echo Html::a('批量驳回', ['disagree'], ['class' => 'btn btn-warning over-dismiss-batches', 'data-toggle' => 'modal', 'data-target' => '#create-modal']);
        }
        ?>

        <?php
        if(Helper::checkRoute('purchase-sum-import'))
        {
           echo  Html::a('采购需求导入', ['purchase-sum-import'], ['class' => 'btn btn-success over-purchase-sum-import', 'data-toggle' => 'modal', 'data-target' => '#create-modal']);
        }
        ?>
        <?php
        if(Helper::checkRoute('demand-rule'))
        {
            echo  Html::a('编辑拦截规则', ['demand-rule'], ['class' => 'btn btn-success over-purchase-sum-import', 'data-toggle' => 'modal', 'data-target' => '#create-modal']);
        }
        if(Helper::checkRoute('all-update-status'))
        {
            echo  Html::a('拦截审核', ['#'], ['class' => 'btn btn-success all-update-status']);
        }
        ?>
        <p class="clearfix"></p>

        <?php if(!in_array(Yii::$app->user->id,$authData)){ ?>
        <div class="form-group">
            <div class="col-md-3" style="width: auto;">
                <label for="name">选择导出类型</label>
                <select class="form-control" style="width: auto; display:inline" name="demand_purchase[demand_purchase1]" id="demand_purchase">
                    <option value="">请选择导出类型</option>
                    <option value="1">已生成需求未采购</option>
                    <option value="2">已生成需求已采购</option>
                    <option value="3">已生成需求未审批</option>
                    <option value="4">已生成需求已审批未付款</option>
                    <option value="5">已生成需求已审批已付款</option>
                    <option value="6">采购需求汇总数据</option>
                </select>
                <select class="form-control" style="width: auto; display:inline" name="demand_purchase[demand_purchase2]" id="limit">
                     <option value="">请选择导出多少数据</option>
                     <option value="100">100</option>
                     <option value="500">500</option>
                     <option value="1000">1000</option>
                     <option value="2000">2000</option>
                     <option value="5000">5000</option>
                     <option value="all">所有</option>
                </select>
            </div>
            <div class="col-md-1">
                <?= Html::button('导出Excel',['class' => 'btn btn-success','id'=>'export-csv1']) ?>
            </div>
        </div>
        <?php }?>
    </div>
    <!---全部、规则拦截搜索框和按钮 结束--->

    <!---金额拦截搜索框和按钮 开始--->
    <div id="amount" style="display: none;">
        <?= $this->render('_search1', ['model' => $searchModel,'authData'=>$authData]); ?>
        <p class="clearfix"></p>
        <?php
        if(Helper::checkRoute('batch-revoke'))
        {
            echo Html::a('批量撤销', ['batch-revoke'], ['class' => 'btn btn-info batch-revoke','data-toggle' => 'modal', 'data-target' => '#create-modal']);
        }
        ?>
        <?php
        if(Helper::checkRoute('batch-update'))
        {
            echo Html::a('批量修改', ['batch-update'], ['class' => 'btn btn-info batch-update-1','data-toggle' => 'modal', 'data-target' => '#create-modal']);
        }
        ?>
        <p class="clearfix"></p>

        <?php if(!in_array(Yii::$app->user->id,$authData)){ ?>
            <div class="form-group">
                <div class="col-md-3" style="width: auto;">
                    <label for="name">选择导出类型</label>
                    <select class="form-control" style="width: auto; display:inline" name="demand_purchase[demand_purchase1]" id="demand_purchase">
                        <option value="">请选择导出类型</option>
                        <option value="1">已生成需求未采购</option>
                        <option value="2">已生成需求已采购</option>
                        <option value="3">已生成需求未审批</option>
                        <option value="4">已生成需求已审批未付款</option>
                        <option value="5">已生成需求已审批已付款</option>
                        <option value="6">采购需求汇总数据</option>
                    </select>
                    <select class="form-control" style="width: auto; display:inline" name="demand_purchase[demand_purchase2]" id="limit">
                        <option value="">请选择导出多少数据</option>
                        <option value="100">100</option>
                        <option value="500">500</option>
                        <option value="1000">1000</option>
                        <option value="2000">2000</option>
                        <option value="5000">5000</option>
                        <option value="all">所有</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <?= Html::button('导出Excel',['class' => 'btn btn-success','id'=>'export-csv1']) ?>
                </div>
            </div>
        <?php }?>
    </div>
    <!---金额拦截搜索框和按钮 结束--->

    <p class="clearfix"></p>

    <h4 style="z-index: 1000;"><p class="glyphicon glyphicon-heart pps" style="color: red" aria-hidden="true">温馨提示:</p><span style="color: red"><p class="pps">1.撤销的需求不可再还原,请看清楚了再撤销。</p><p class="pps">2.请选择相同的供应商生成采购单</p><p class="pps">3.默认展示未生成的,查看已生成请用上面的搜索</p></h4>
    <?php
    $items = [
        [
            'label'=>'<i class="glyphicon glyphicon-yen"></i>全部',
            'content'=>$this->render('all-index',['dataProvider'=>$dataProvider,'authData'=>$authData,'tab_index'=>1]),
            'active'=>$tab_index==1 ?true :false,
        ],
        
        [
            'label'=>((!in_array(Yii::$app->user->id,$authData)) ? '<i class="glyphicon glyphicon-yen"></i>规则拦截('.$count.')':' '),
            'content'=>$this->render('intercept-index',['ruleData'=>$ruleData,'tab_index'=>2,'count' => $count]),
            'active'=>$tab_index==2 ?true :false,

        ],

        [
            'label'=>((!in_array(Yii::$app->user->id,$authData)) ? '<i class="glyphicon glyphicon-yen" value="1"></i>金额拦截('.$amountTotal.')':' '),
            'content'=>$this->render('amount-intercept-index',['ruleData'=>$amountData,'tab_index'=>3,'count' => $amountTotal]),
            'active'=>$tab_index==3 ?true :false,
        ],

        [
            'label'=>((!in_array(Yii::$app->user->id,$authData)) ? '<i class="glyphicon glyphicon-yen"></i>7天3小时拦截('.$sevendays3hoursTotal.')':' '),
            'content'=>$this->render('sevendays-3hour-intercept-index',['ruleData'=>$sevendays3hoursData,'tab_index'=>4]),
            'active'=>$tab_index==4 ?true :false,
        ],

        [
            'label'=>((!in_array(Yii::$app->user->id,$authData)) ? '<i class="glyphicon glyphicon-yen"></i>产品信息不全('.$incompleteInfoTotal.')':' '),
            'content'=>$this->render('incomplete-info-intercept-index',['ruleData'=>$incompleteInfoData,'tab_index'=>5]),
            'active'=>$tab_index==5 ?true :false,
        ],


    ];

    echo \kartik\tabs\TabsX::widget([
        'items'=>$items,
        'position'=>\kartik\tabs\TabsX::POS_ABOVE,
        'encodeLabels'=>false
    ]);?>
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
$requestUrl = Url::toRoute('view');
$arrival='请选择需要标记到货日期的采购单';
$js = <<<JS
    $(function(){
            //跳转到当前点击搜索页
            var tab = $("#tab").val();
            $("a[href='#w6-tab"+tab+"']").trigger('click');
            $("input[name='PlatformSummarySearch[tab]']").val(tab);
            if(tab>1){
                $("#amount").show();
                $("#all").hide();
            }else{
                $("#amount").hide();
                $("#all").show();
            }
            //点击关闭刷新页面
            $(".closes,.close").click(function(){
                 //location=location;
                 var self_tab = $("input[name='PlatformSummarySearch[tab]']").val();
                 window.location.href="";
            })
            
            //根据页面定义tab的值
            $("a[href='#w6-tab0']").click(function(){
                $("input[name='PlatformSummarySearch[tab]']").val(0);
            })
            $("a[href='#w6-tab1']").click(function(){
                $("input[name='PlatformSummarySearch[tab]']").val(1);
            })
            $("a[href='#w6-tab2']").click(function(){
                $("input[name='PlatformSummarySearch[tab]']").val(2);
            })
            $("a[href='#w6-tab3']").click(function(){
                $("input[name='PlatformSummarySearch[tab]']").val(3);
            })
            $("a[href='#w6-tab4']").click(function(){
                $("input[name='PlatformSummarySearch[tab]']").val(4);
            })
            $("a#submit-audit").click(function(){
                var ids = $('#grid_overseas_purchase').yiiGridView('getSelectedRows');
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
            /*$("#w6").css('position','fixed');
            $("#w6").css('margin-top','10px');
            $("#w6").css('z-index','1000');
            $("#w6").css('background','white');*/
        });
    $(document).on('click', '.disagree', function () {

        $.get($(this).attr('href'), {},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
    
     $(document).on('click', '.pdisagree', function () {

        $.get($(this).attr('href'), {},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
            //批量同意
        $(".over-bulk-consent").click(function(){
            var ids = $('#grid_overseas_purchase').yiiGridView('getSelectedRows');
            if(ids==''){
                alert('请先选择数据!');
                return false;
            }else{
                var url=$(this).attr("href");
                $.post(url, {ids:ids},
                    function (data) {
                        $('.modal-body').html(data);
                    }
                );
               return false; 
            }
        });
        
        //批量驳回
        $(".over-dismiss-batches").click(function(){
            var ids = $('#grid_overseas_purchase').yiiGridView('getSelectedRows');
            if(ids==''){
                alert('请先选择数据!');
                return false;
            }else{
                var url=$(this).attr("href");
                $.post(url, {id:ids},
                    function (data) {
                        $('.modal-body').html(data);
                    }
                );
            }
        });
        //批量拦截审核
        $(".all-update-status").click(function(){
            var ids = $('#grid_overseas_purchase').yiiGridView('getSelectedRows');
            if(ids==''){
                layer.alert('请先选择数据！');
                return false;
            }else{
                //layer.confirm(content, options, yes, cancel) 
                layer.confirm('is not?', {icon: 3, title:'提示'}, function(index){
                  $.post('/overseas-purchase-demand/all-update-status', {ids:ids},function (data) {
                        $('.modal-body').html(data);
                    });
                   layer.close(index);

                });
            }
            return false;
        });
        
        //批量撤销
         $(document).on('click', '.batch-revoke', function () {
            var ids = '';
            var num = $("input[name='revoke_id']:checked").length;
            $("input[name='revoke_id']:checked").each(function(i){
                ids += $(this).val()+",";
            });
        
            var href = $("a[aria-expanded='true']").attr('href');
            var index = href.substr(href.length-1,1);
            if(ids==''){
                $('.modal-body').html('请先选择数据');
                return false;
            }else{
                $.get($(this).attr('href'), {ids:ids , num:num},
                    function (data) {
                        $('.modal-body').html(data);
                    }
                );
            }
        });
         //批量修改
         $(document).on('click', '.batch-update-1', function () {
            var href = $("a[aria-expanded='true']").attr('href');
            var index = href.substr(href.length-1,1);
            var ids = '';
            var num = $("#type_"+index+" input:checked").length;
            $("#type_"+index+" input:checked").each(function(i){
                ids += $(this).val()+",";
            });
            
            if(ids==''){
                $('.modal-body').html('请先选择数据');
                return false;
            }else{
                var type="";
                if(index==3){
                   type="seven_days";         
                }
                $.get($(this).attr('href'), {ids:ids , num:num ,type:type},
                    function (data) {
                        $('.modal-body').html(data);
                    }
                );
            }
        });
       
        
        //采购需求批量导入
        $(".over-purchase-sum-import").click(function(){
            $.get($(this).attr('href'), {},
            function (data) {
                $('.modal-body').html(data);
            });
        });
    
        $("a[data-toggle='tab']").click(function(){
            $("input[name='revoke_id']").attr("checked",false)
            $("#theadInp").attr("checked",false)
            $("#theadInp1").attr("checked",false)
            $("#theadInp2").attr("checked",false)
            var href = $(this).attr('href');
            var index = href.substr(href.length-1,1);
            //展示金额拦截按钮和输入框
            if(index == 2 || index == 3 || index == 4){
                $("#all").hide();
                $("#amount").show();
            }else{
                $("#amount").hide();
                $("#all").show();
            }
        })
        
    /*$(function(){
        //点击生成采购单
        $("a.create-purchase").click(function(){
            var ids = $('#grid_overseas_purchase').yiiGridView('getSelectedRows');
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
    });*/
    //根据选择，导出Excel表数据
     $('#export-csv1').click(function() {
         var demand_purchase_type = $("#demand_purchase option:selected").val();
         var limit = $("#limit option:selected").val(); //一次导出多少数据
         var ids = $('#grid_overseas_purchase').yiiGridView('getSelectedRows'); // 选中需要导出的数据
         var daterangepicker_start = $("input[name='daterangepicker_start']").val(); //时间段
         var daterangepicker_end = $("input[name='daterangepicker_end']").val();
         
            if(demand_purchase_type==''){
                alert('请先选择导出类型!');
                return false;
            } else if(limit==''){
                alert('请选择导出多少数据');
                return false;
            }else{
                 window.location.href='/overseas-purchase-demand/export-csv1?demand_purchase_type='+demand_purchase_type + '&limit=' + limit + '&daterangepicker_start=' + daterangepicker_start + '&daterangepicker_end=' + daterangepicker_end + '&ids=' + ids;
                 // window.location.href='/overseas-purchase-demand/export-csv1?demand_purchase_type='+demand_purchase_type + '&ids=' + ids;
            }
     });
     
     $(document).on('click', '.batch-update', function () {
     var supplierCode = $(this).attr('suplier_code');
     var warehouseCode = $(this).attr('warehouse_code');
     var transport = $(this).attr('transport');
     var tab_index = $(this).closest('div').prevAll('.tab_index').attr('tab_index');
     var num = $(this).attr('num');
     var a_at = $(this).closest('div').prev('div').find('.a_href').attr('name');
     var ids_list = $(this).attr('ids_list');
        $.get($(this).attr('href'), {supplier_code:supplierCode,warehouse_code:warehouseCode,num:num,transport:transport,a_at:a_at,tab_index:tab_index,ids_list:ids_list},
        function (data) {
            $('.modal-body').html(data);
        }
    );
     $("#create-modal").on("hidden.bs.modal", function() {
        $(this).removeData("modal");
     });
     
     
});

JS;
$this->registerJs($js);
?>
