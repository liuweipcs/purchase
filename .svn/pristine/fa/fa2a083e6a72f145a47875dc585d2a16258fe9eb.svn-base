<?php
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use app\services\PurchaseOrderServices;
use app\services\BaseServices;
use app\models\PurchaseOrderItemsV2;
use app\models\PurchaseUser;

$this->title = '采购订单';
$this->params['breadcrumbs'][] = $this->title;
?>
    <div class="purchase-order-index">

        <?php  echo $this->render('_search', ['model' => $searchModel]); ?>
        <p class="clearfix"></p>
        <p>

            <?php
                echo  Html::a('采购确认', ['#'], ['class' => 'btn btn-success', 'id' => 'submit-audits', 'data-toggle' => 'modal', 'data-target' => '#create-modal',]);
            ?>
            <?php
                echo Html::a('撤销确认', ['revoke-confirmation'], ['class' => 'btn btn-warning','id'=>'submit-audit',]);
            ?>
            <?php
                echo Html::a('撤销采购单', ['revoke-purchase-order'], ['class' => 'btn btn-danger', 'id' => 'submit-audit']);
            ?>
            <?php
                echo Html::a('打印采购单', ['print-data'], ['class' => 'btn btn-primary print', 'id' => 'submit-audit', 'target' => '_blank']);
            ?>
            <?php
                echo  Html::a('创建采购单', ['addproduct'], ['class' => 'btn btn-info']);
            ?>

            <?php
                $check_url=Yii::$app->authManager->checkAccess(Yii::$app->user->id,'/purchase-orders-v2/all-review');

                if(false){
                    if(!empty($grade) && $g=$grade->grade){
                        if($g==1){
                            echo Html::a('组长审核', ['#'], ['class' => 'btn btn-success','id'=>'batch_review','data-toggle' => 'modal', 'data-target' => '#create-modal',]);
                        }

                        if($g==2){
                            echo Html::a('主管审核', ['#'], ['class' => 'btn btn-success','id'=>'batch_review','data-toggle' => 'modal', 'data-target' => '#create-modal',]);
                        }

                        if($g==3){
                            echo Html::a('经理审核', ['#'], ['class' => 'btn btn-success','id'=>'batch_review','data-toggle' => 'modal', 'data-target' => '#create-modal',]);
                        }
                    }else{
                        echo Html::a('批量审核', ['#'], ['class' => 'btn btn-success','id'=>'batch_review','data-toggle' => 'modal', 'data-target' => '#create-modal',]);
                    }
                }
            ?>

            <?= Html::a('申请批量付款', ['#'], ['class' => 'btn btn-success all-payment','id'=>'all-payment','data-toggle' => 'modal', 'data-target' => '#create-modal',]) ?>


        <h4><span class="glyphicon glyphicon-heart" style="color: red" aria-hidden="true"></span>温馨小提示:<span style="color: red">谁执行了采购确认,采购员将是谁<i class="fa fa-fw fa-smile-o"></i></h4>
        </p>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            //'filterModel' => $searchModel,
            'options'=>[
                'id'=>'grid_purchase_order',
            ],
            'pager'=>[
                'firstPageLabel'=>"首页",
                'prevPageLabel'=>'上一页',
                'nextPageLabel'=>'下一页',
                'lastPageLabel'=>'末页',
            ],
            'columns' => [
                [
                    'class' => 'yii\grid\CheckboxColumn',
                    'name'=>"id" ,
                    'checkboxOptions' => function ($model, $key, $index, $column) {
                        return ['value' => $model->pur_number];
                    }

                ],

                [
                    'label'=>'PO号',
                    'attribute' => 'pur_numbers',
                    "format" => "raw",
                    'value'=> function($model){
                        if($model->purchas_status==1 && $model->buyer==Yii::$app->user->identity->username){
                            $data = Html::a($model->pur_number,
                                ['#'],
                                ['data-id' => $model->id,
                                'value' =>$model->pur_number,
                                'data-ps' =>$model->purchas_status,
                                'class'=>'submitaudits',
                                'data-toggle' => 'modal',
                                'data-target' => '#create-modal',
                                ]);
                        }else{
                            $grade=PurchaseUser::findOne(['pur_user_id'=>Yii::$app->user->id]);

                            if($model->all_status > 1 && $model->all_status < 5 && !empty($grade) && $grade->grade){
                                $data = Html::a($model->pur_number,
                                    ['#'],
                                    [
                                        'class' => 'purple',
                                        'data-toggle' => 'modal',
                                        'data-target' => '#create-modal',
                                        'id' =>$model->id,
                                    ]
                                );
                            }else{
                                $data = Html::a($model->pur_number,
                                    ['#'],
                                    [
                                        'class' => 'details',
                                        'data-toggle' => 'modal',
                                        'data-target' => '#create-modal',
                                        'id' =>$model->id,
                                    ]
                                );
                            }
                        }

                        $data.=Html::a('<span class="fa fa-fw fa-truck" style="font-size:20px;color:coral" title="物流信息"></span>', ['#'],
                              [ 'id' => 'logistics',
                                'data-toggle' => 'modal',
                                'data-target' => '#create-modal',
                                'pur_number'=>$model->pur_number
                              ]);

                        $data .= Html::a('<span class="fa fa-fw fa-comment" style="font-size:20px;color:#f2adb1;margin-right:10px;" title="备注"></span>', ['#'], [
                               'title' => Yii::t('app', '备注'),
                                'class' => 'note',
                                'data-toggle' => 'modal',
                                'data-target' => '#create-modal',
                                'pur_number'=>$model->pur_number
                          ]);

                        return $data;
                    },
                ],

                [
                    'label'=>'仓库',
                    'attribute' => 'ids',
                    "format" => "raw",
                    'value'=>function($model){
                        if(!empty($model->is_transit) && $model->is_transit==1 && $model->transit_warehouse)
                        {
                            $data   = BaseServices::getWarehouseCode($model->transit_warehouse);
                            $data  .=!empty($model->warehouse_code)?'-'.BaseServices::getWarehouseCode($model->warehouse_code):'<br/>';

                        } else {
                            $data  =!empty($model->warehouse_code)?BaseServices::getWarehouseCode($model->warehouse_code):'<br/>';
                        }
                        return  $data;
                    },
                ],

                [
                    'label'=>'采购员',
                    'value'=>'buyer',
                ],

                [
                    'label'=>'供应商名称',
                    'format'=>'raw',
                    'value'=>function($model){
                        $style='';
                        if(!empty($model->e_supplier_name) && $model->review_status != 3){
                            $style="style='color:red'";
                        }
                        return $model->supplier_name ? "<span $style>$model->supplier_name</span>" : '';
                    },
                ],

                [
                    'label'=>'SKU数量',
                    'value'=> function($model){
                        return PurchaseOrderItemsV2::find()->where(['pur_number'=>$model->pur_number])->count('id');
                    },
                ],

                [
                    'label'=>'采购数量',
                    'value'=> function($model){
                        $ctq=PurchaseOrderItemsV2::find()->where(['pur_number'=>$model->pur_number])->sum('ctq');
                        if(!empty($ctq)){
                            return $ctq;
                        }else{
                            return PurchaseOrderItemsV2::find()->where(['pur_number'=>$model->pur_number])->sum('qty');
                        }
                    },
                ],

                [
                    'label'=>'总金额( RMB )',
                    "format" => "raw",
                    'value'=>function($model){
                        $tp=PurchaseOrderItemsV2::getCountPrice($model->pur_number);
                        $tqp=PurchaseOrderItemsV2::find()->select('sum(qty*price)')->where(['pur_number'=>$model->pur_number])->scalar();

                       return !empty($tp) ? sprintf('%.2f',$tp) : sprintf('%.2f',$tqp);
                    },
                ],

                [
                    'label'=>'运费',
                    'value'=>function($model){
                        $freight=\app\models\PurchaseOrderShip::find()->where(['pur_number'=>$model->pur_number])->sum('freight');
                        return round($freight,2);
                    },
                ],

                [
                    'label' => '结算方式',
                    'format'=>'raw',
                    'value'=>function($model){
                        $style='';
                        if(!empty($model->e_account_type) && $model->review_status != 3){
                            $style="style='color:red'";
                        }
                        $atype=\app\services\SupplierServices::getSettlementMethod($model->account_type);
                        return $model->account_type ? "<span $style>$atype</span>" : '';
                    },
                ],

                [
                    'label'=>'订单号',
                    "format" => "raw",
                    'value'=>function($model){
                        $findone=\app\models\PurchaseOrderOrders::findOne(['pur_number'=>$model->pur_number]);
                        return !empty($findone) ? $findone->order_number : '';
                    },
                ],

                [
                    'label'=>'确认备注',
                    'attribute' => 'created_ats',
                    "format" => "raw",
                    'value'=>function($model){
                        return $model->orderNote['note'];
                    },
                ],

                [
                    'label'=>'状态',
                    'attribute' => 'created_ats',
                    "format" => "raw",
                    'value'=>function($model){
                        if ($model->pay_status == '1' && $model->buyer==Yii::$app->user->identity->username && $model->all_status==5) {
                            return Html::a('<i class="glyphicon glyphicon-yen"></i> 申请付款', ['#'], [
                                'title' => Yii::t('app', '申请付款'),
                                'class' => 'btn btn-xs payment',
                                'data-toggle' => 'modal',
                                'data-target' => '#create-modal',
                                'pur_number' => $model->pur_number
                            ]);
/*                        }elseif(in_array($model->purchas_status,[6,7,8,9,10,99])){
                            return strip_tags(PurchaseOrderServices::getPurchaseStatus($model->purchas_status));*/
                        }else if(in_array($model->pay_status,[3,4,5,6])){
                            return strip_tags(PurchaseOrderServices::getPayStatus($model->pay_status));
                        }else{
                            return PurchaseOrderServices::getAllOrdersStatus()[$model->all_status];
                        }
                    },
                ],

                /*[
                    'class' => 'kartik\grid\ActionColumn',
                    'dropdown' => false,
                    'width'=>'180px',
                    'template' => '{review}{payment}',
                    'buttons'=>[
                        'review' => function ($url, $model, $key) {
                            $findone=\app\models\PurchaseUser::findOne(['pur_user_id'=>Yii::$app->user->id]);

                            if(empty($findone->grade) && $model->purchas_status>1 && $model->purchas_status<3){
                                return Html::a('<i class="glyphicon glyphicon-ok"></i> 审核',
                                    ['#'],
                                    [
                                        'title' => Yii::t('app', '审核'),
                                        'class' => 'btn btn-xs purple',
                                        'data-toggle' => 'modal',
                                        'data-target' => '#create-modal',
                                        'id' =>$key,
                                    ]
                                );
                            }

                            if(!empty($findone->grade)){
                                if($model->purchas_status>1 && $model->purchas_status<4 && $model->review_status==0){
                                    return Html::a('<i class="glyphicon glyphicon-ok"></i> 组长审核',
                                        //['review','name'=>'audit'],
                                        ['#'],
                                        [
                                            'title' => Yii::t('app', '组长审核'),
                                            'class' => 'btn btn-xs purple',
                                            'data-toggle' => 'modal',
                                            'data-target' => '#create-modal',
                                            'id' =>$key,
                                        ]
                                    );

                                }

                                if($model->purchas_status>1 && $model->purchas_status<4 && $model->review_status==1){
                                    return Html::a('<i class="glyphicon glyphicon-ok"></i> 主管审核',
                                        ['#'],
                                        [
                                            'title' => Yii::t('app', '主管审核'),
                                            'class' => 'btn btn-xs purple',
                                            'data-toggle' => 'modal',
                                            'data-target' => '#create-modal',
                                            'id' =>$key,
                                        ]
                                    );
                                }

                                if($model->purchas_status>1 && $model->purchas_status<4 && $model->review_status==2){
                                    return Html::a('<i class="glyphicon glyphicon-ok"></i> 经理审核',
                                        ['#'],
                                        [
                                            'title' => Yii::t('app', '经理审核'),
                                            'class' => 'btn btn-xs purple',
                                            'data-toggle' => 'modal',
                                            'data-target' => '#create-modal',
                                            'id' =>$key,
                                        ]
                                    );
                                }
                            }


                        },

                        'payment' => function ($url, $model, $key) {
                            //全付款了就不能再付款了
                            if ($model->pay_status=='1' && $model->buyer==Yii::$app->user->identity->username && $model->purchas_status == 3 && $model->review_status == 3) {
                                return Html::a('<i class="glyphicon glyphicon-yen"></i> 申请付款', ['#'], [
                                    'title' => Yii::t('app', '申请付款'),
                                    'class' => 'btn btn-xs payment',
                                    'data-toggle' => 'modal',
                                    'data-target' => '#create-modal',
                                    'pur_number' => $model->pur_number
                                ]);
                            }
                        },

                    ],

                ],*/
            ],

            'containerOptions' => ["style"=>"overflow:auto"],
            'toolbar' =>  [
                //'{export}',
            ],


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
                'type'=>'success',
            ],
        ]); ?>
    </div>
<?php
Modal::begin([
    'id' => 'create-modal',
    'class' => 'create-modal',
    'header' => '<h4 class="modal-title">系统信息</h4>',
    'footer' => '<a href="#" class="btn btn-primary closes" data-dismiss="modal">关闭</a>',
    'size'=>'modal-lg',
    'options'=>[
        'data-backdrop'=>'static',//点击空白处不关闭弹窗
    ],
]);
Modal::end();

$requestUrl = Url::toRoute(['view']);
$sumbitUrl  = Url::toRoute(['submit-audit']);
$reviewUrl  = Url::toRoute(['review']);
$allreviewUrl  = Url::toRoute(['all-review']);
$detailsUrl  = Url::toRoute(['details']);
$paymentUrl  = Url::toRoute(['payment']);
$allpaymentUrl  = Url::toRoute(['allpayment']);

$noteUrl = Url::toRoute('add-purchase-note');
$trackUrl = Url::toRoute('get-tracking');

$page=Yii::$app->request->get('page') ? Yii::$app->request->get('page') : 1;

$backUrl= Url::toRoute(['index','page'=>$page]);

$msg ='请选择采购单';
$js = <<<JS

    $('.modal').removeAttr('tabindex');
    $('.closes').click(function() {
        window.location.href='$backUrl';
    });

    $('.close').click(function() {
        window.location.href='$backUrl';
    });
    
    $(document).on('click', '.note', function () {
         $.get('{$noteUrl}', {pur_number:$(this).attr('pur_number'),flag:1,page:'$page'},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });

    $(document).on('click', '#submit-audit', function () {
            var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
            if(ids==''){

                alert('{$msg}');
                return false;
            }else{
                var url = $(this).attr("href");
                if($(this).hasClass("print"))
                {
                    url = '/purchase-orders-v2/print-data';
                }
                url     = url+'?ids='+ids;
                $(this).attr('href',url);
            }
    });

//修改供应商
$(document).on('click',"#update-supplier",function(){
    var url=$(this).attr("href");
    var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
    if(ids==''){
      $('.modal-body').html('{$msg}');
       return false;
    }else{
       $.get(url, {id: ids},
            function (data) {
                $('.modal-body').html(data);
            }
         );
    }
});
$(document).on('click', '#logs', function () {
    $.get($(this).attr('href'), {id:$(this).attr('value')},
        function (data) {
            $('.modal-body').html(data);
        }
    );
});


$(document).on('click', '.submitaudits', function () {
    if($(this).attr('data-ps')>1){
        alert('已经确认过了！');
        return;
    }
     var id=$(this).attr('data-id');
     $.get('{$sumbitUrl}', {id: id,page:'$page'},
             function (data) {
                 $('.modal-body').html(data);
            }
     ); 
     
     /*var url='{$sumbitUrl}?id='+id;
     window.open(url);
     window.location.reload();*/   
});

$(document).on('click', '#submit-audits', function () {
    var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
    if (ids && ids.length !=0){
         $.get('{$sumbitUrl}', {id: ids,page:'$page'},
                    function (data) {
                        $('.modal-body').html(data);
                    }
         );
         
         /*var url='{$sumbitUrl}?id='+ids;
         window.open(url);
         window.location.reload();*/   
    } else {
        $('.modal-body').html('{$msg}');
        return false;
    }

});

 $("#create-modal").on("hidden.bs.modal", function() {
    $(this).removeData("bs.modal");
 });
 
//star 审核
$("#batch_review").on('click', function(){
    var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
    if(ids==''){
        alert('请先选择!');
        return false;
    }else{
        $.get('{$allreviewUrl}', {id: ids,name:'audit',page:'{$page}'},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    }
});

$(document).on('click', '.purple', function () {
    $.get('{$reviewUrl}', {id:$(this).attr('id'),name:'audit',page:'{$page}'},
        function (data) {
            $('.modal-body').html(data);
        }
    );
});
//end 审核

$(document).on('click', '.details', function () {//查看详情
    $.get('{$detailsUrl}', {id:$(this).attr('id'),page:'$page'},
        function (data) {
            $('.modal-body').html(data);
        }
    );
});

$(document).on('click', '.payment', function () {//单个申请
    $.get('{$paymentUrl}',{pur_number:$(this).attr('pur_number'),page:'$page'},function (data) {
         $('.modal-body').html(data);
    });
});

 //批量申请付款
$("a#all-payment").click(function(){
    var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
    if(ids==''){
        alert('请先选择!');
        return false;
    }else{
        $.get('{$allpaymentUrl}',{ids:ids,page:'$page'},function (data){
            $('.modal-body').html(data);
        });
    }
});
    
    $(document).on('click', '#logistics', function () {
        $.get("$trackUrl", {id:$(this).attr('pur_number'),page:'$page'},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });

JS;
$this->registerJs($js);
?>