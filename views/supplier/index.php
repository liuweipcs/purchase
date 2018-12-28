<?php

use yii\helpers\Html;

use kartik\grid\GridView;
use Yii\helpers\Url;
use yii\bootstrap\Modal;
use kartik\export\ExportMenu;
use app\services\SupplierServices;
use mdm\admin\components\Helper;
use app\models\SupplierUpdateLog;

/* @var $this yii\web\View */
/* @var $searchModel app\models\StockinSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '供应商管理');
$this->params['breadcrumbs'][] = $this->title;


Modal::begin([
    'id' => 'check-modal',
    'header' => '<h4 class="modal-title">审核供应商</h4>',
    'footer' => '<a href="#" class="btn btn-primary closes" data-dismiss="modal" style="display: none"></a>',
    'size' => 'modal-lg',
    //'closeButton' =>false,
    'options' => [
        'data-backdrop' => 'static',//点击空白处不关闭弹窗
    ],
]);
Modal::end();

?>
<div class="stockin-index">

    <style type="text/css">
        .modal-lg {
            width: 600px;
        !important;
        }
    </style>

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    <p class="clearfix"></p>
    <?php
    if (Helper::checkRoute('create')) {
        echo Html::a(Yii::t('app', '创建'), ['update-info'], ['class' => 'btn btn-success ']);
    }
    //    if(Helper::checkRoute('update-supplier')) {echo Html::a(Yii::t('app', '批量修改供应商基本信息'), ['update-supplier'], ["class" => "btn btn-success ",'data-toggle' => 'modal','data-target' => '#create-modal','id' => 'updates']);}
    //    echo Html::button(Yii::t('app', '供应商审核'), ["class" => "btn btn-info supplier-review"]);
    //    echo Html::button(Yii::t('app', '财务审核'), ["class" => "btn btn-info financial-audit"]);
    ?>

    <?php
    if (Helper::checkRoute('update-supplier')) {
        echo Html::a(Yii::t('app', '批量修改供应商基本信息'), ['update-supplier'], ["class" => "btn btn-success ", 'data-toggle' => 'modal', 'data-target' => '#create-modal', 'id' => 'updates']);
    }

    ?>
    <?php
    if (Helper::checkRoute('all-edit-supplier-attr')) {
        echo Html::a(Yii::t('app', '批量修改采购员'), '#', [
            'id' => 'create',
            'data-toggle' => 'modal',
            'data-target' => '#create-modal',
            'class' => 'btn btn-success gridview',
        ]);
    }
    if (Helper::checkRoute('ex-update-supplier')) {
        echo Html::a(Yii::t('app', '批量编辑供应商信息'), ['ex-update-supplier'], ["class" => "btn btn-success ", 'data-toggle' => 'modal', 'data-target' => '#create-modal', 'id' => 'creates']);
    }

    if (Helper::checkRoute('manage/supplier-config/quotes-config')) {
        echo Html::a(Yii::t('app', '配置供应商报价权限'), ['manage/supplier-config/quotes-config'], ["class" => "btn btn-success ", 'data-toggle' => 'modal', 'data-target' => '#create-modal', 'id' => 'config-quotes']);
    }
    ?>
    <?= Html::button(Yii::t('app', '拉取erp供应商'), ["class" => "btn btn-info erpsupplier", 'data-toggle' => 'modal', 'data-target' => '#create-modal']) ?>
    <?php
    if (Helper::checkRoute('export-csvs')) {
        echo Html::a(Yii::t('app', '导出供应商信息'), ['export-csvs'], ['class' => 'btn btn-warning ']);
    }
    if (Helper::checkRoute('/supplier/export-csv')) {
        echo Html::button('导出Excel', ['class' => 'btn btn-success', 'id' => 'export-csv']);
    }
    if (Helper::checkRoute('/supplier/check')) {
        echo Html::a('审核供应商', ['check'], ['class' => 'btn btn-success', 'id' => 'check', 'data-toggle' => 'modal', 'data-target' => '#check-modal']);
    }
    ?>
    <h1></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        "options" => ["class" => "grid-view", "style" => "overflow:auto;font-size:xx-small", "id" => "grid-supplier"],
        'filterSelector' => "select[name='" . $dataProvider->getPagination()->pageSizeParam . "'],input[name='" . $dataProvider->getPagination()->pageParam . "']",
        'pager' => [
            'options' => ['class' => 'pagination', 'style' => "display:block;"],
            'class' => \liyunfang\pager\LinkPager::className(),
            'pageSizeList' => [20, 50, 100, 200],
//                'options'=>['class'=>'hidden'],//关闭分页
            'firstPageLabel' => "首页",
            'prevPageLabel' => '上一页',
            'nextPageLabel' => '下一页',
            'lastPageLabel' => '末页',
        ],
        'columns' => [
            ['class' => 'kartik\grid\SerialColumn'],
            [
                'class' => 'kartik\grid\CheckboxColumn',
                'name' => "id",
                'checkboxOptions' => function ($model) {
                    return ['value' => $model->id];
                }
            ],
            [
                'label' => '状态',
                'attribute' => 'status',
                'value' => function ($model) {
                    return SupplierServices::getSupplierStatus($model->status);
                }
            ],
            [
                'label' => '供应商代码',
                'attribute' => 'supplier_codes',
                'value' =>
                    function ($model) {
                        return $model->supplier_code;   //主要通过此种方式实现
                    },

            ],
            [
                'label' => '供应商中文名',
                'attribute' => 'supplier_name',
                'format' => 'raw',
                'value' =>
                    function ($model) {
                        $oldstr = !empty($model->store_link) ? $model->store_link : '';
                        $oldLink = stristr($oldstr, 'http') ? $oldstr : 'https://' . $oldstr;
                        $oldLinkhtml = "<a href='$oldLink' title='$oldLink' target='_blank'><i class='fa fa-fw fa-internet-explorer'></i></a>";
                        $html = $model->supplier_name . $oldLinkhtml;   //主要通过此种方式实现

                        $html .= Html::a('<span class="glyphicon glyphicon-zoom-in"  style="font-size:20px;color:#00a65a;margin-right:10px;" title="单击，查看采购产品明细"></span>', ['#'], ['class' => 'view-details',
                            'data-toggle' => 'modal',
                            'data-target' => '#create-modal',
                            'value' => $model->id,
                        ]);
                        $html .= \app\models\SupplierSearch::flagCrossBorder(true, $model->supplier_code);
                        return $html;
                    },

            ],
            [
                'label' => '最近采购时间',
                'attribute' => 'purchase_time',
                'format' => 'raw',
                'value' => function ($model) {
                    return $model->purchase_time;

                    // $purOrder = \app\models\PurchaseOrder::find()->andFilterWhere(['supplier_code'=>$model->supplier_code])->andFilterWhere(['NOT IN','purchas_status',[1,2,4,10]])->orderBy('created_at DESC')->one();
                    // return !empty($purOrder) ? $purOrder->created_at : '';
                }
            ],
            [
                'label' => '近三个月合作金额',
                'format' => 'raw',
                'value' => function ($model) {
                    $startTime = date('Y-m-01 00:00:00', strtotime('-2 months'));
                    $endTime = date('Y-m-d H:i:s');
                    return \app\config\Vhelper::getSupplierPurchaseNum($model->supplier_code, $startTime, $endTime);
                }
            ],
            [
                'label' => '近一个月合作金额',
                'format' => 'raw',
                'value' => function ($model) {
                    $startTime = date('Y-m-01 00:00:00');
                    $endTime = date('Y-m-d H:i:s');
                    return \app\config\Vhelper::getSupplierPurchaseNum($model->supplier_code, $startTime, $endTime);
                }
            ],
            [
                'label' => '合作时间（月）',
                'attribute' => 'cooperation_time',
                'value' => function ($model) {
                    return $model->cooperation_time;
                    // return !empty($model->first_cooperation_time) ? ceil((time()-strtotime($model->first_cooperation_time))/2592000) : 0;
                }
            ],
            [
                'label' => '累计合作金额',
                'attribute' => 'cooperation_price',
                'value' => function ($model) {
                    return $model->cooperation_price;
                    // return \app\config\Vhelper::getSupplierPurchaseNum($model->supplier_code);
                }
            ],
            [
                'label' => '首次合作时间',
                'attribute' => 'first_cooperation_time',
                'format' => 'raw',
                'value' => function ($model) {
                    return !empty($model->first_cooperation_time) ? date('Y-m-d', strtotime($model->first_cooperation_time)) : '';
                }
            ],
            [
                'label' => '产品线',
                'format' => 'raw',
                'value' => function ($model) {
                    $data = \app\models\SupplierProductLine::find()->select('first_product_line')->andFilterWhere(['supplier_code' => $model->supplier_code, 'status' => 1])->with('firstLine')->all();
                    $str = '';
                    if (!empty($data)) {
                        foreach ($data as $v) {
                            $str .= !empty($v->firstLine->linelist_cn_name) ? $v->firstLine->linelist_cn_name . '/' : '';
                        }
                    }
                    return !empty($str) ? '<span title="' . $str . '">' . \app\config\Vhelper::toSubStr($str, 4, 4) : '';
                }
            ],
            [
                'attribute' => 'supplier_level',
                'value' =>
                    function ($model) {
                        return !empty($model->supplier_level) ? SupplierServices::getSupplierLevel($model->supplier_level) : '';   //主要通过此种方式实现
                    },

            ],
            [
                'attribute' => 'supplier_type',
                'value' =>
                    function ($model) {
                        return !empty($model->supplier_type) ? SupplierServices::getSupplierType($model->supplier_type) : '';   //主要通过此种方式实现
                    },

            ],
            [
                'label' => '供应商结算方式',
                'value' =>
                    function ($model) {
                        return !empty($model->supplier_settlement) ? SupplierServices::getSettlementMethod($model->supplier_settlement) : '';   //主要通过此种方式实现
                    },

            ],
            [
                'attribute' => 'payment_method',
                'value' =>
                    function ($model) {
                        return !empty($model->supplier_settlement) ? SupplierServices::getDefaultPaymentMethod($model->payment_method) : '';   //主要通过此种方式实现
                    },

            ],
            /*[
                'attribute' => 'cooperation_type',
                'value'=>
                    function($model){
                        return  !empty($model->cooperation_type)?SupplierServices::getCooperation($model->cooperation_type):'';   //主要通过此种方式实现
                    },

            ],*/
            /*[
                'label'=>'主营品类',
                'attribute' => 'main_categorys',
                'value'=>
                    function($model){
                        return  !empty($model->main_category)?SupplierServices::getCategory($model->main_category):'';   //主要通过此种方式实现
                    },


            ],*/
            [
                'label' => '采购员',
                'attribute' => 'buyers',
                'value' =>
                    function ($model) {
                        $buyers = \app\models\SupplierBuyer::find()->andFilterWhere(['supplier_code' => $model->supplier_code, 'status' => 1])->andWhere('NOT ISNUll(buyer) AND buyer!=""')->asArray()->all();
                        return implode(',', array_column($buyers, 'buyer'));
                        //return  !empty($model->buyer)? !empty(app\models\User::findIdentity($model->buyer)) ? app\models\User::findIdentity($model->buyer)->username: $model->buyer :'';   //主要通过此种方式实现
                    },


            ],
            /*[
                'attribute' => 'is_taxpayer',
                'value'=>
                    function($model){
                        return  !empty($model->is_taxpayer)?Yii::$app->params['boolean'][$model->is_taxpayer]:'';
                    },
            ],*/
            /*[
                'label'=>'跟单员',

                'attribute' => 'merchandisers',
                'value'=>
                    function($model){
                        return  app\models\User::findIdentity($model->merchandiser)->username;   //主要通过此种方式实现
                    },

            ],*/
            /* [
                 'class'=>'kartik\grid\BooleanColumn',
                 'attribute'=>'status',
                 'vAlign'=>'middle',
                 'trueLabel'=>'可用',
                 'falseLabel'=>'不可用',
                 'format'=>'raw',
                 'noWrap'=>true
             ],*/
            [
                'label' => '创建人',
                'value' => function ($model) {
                    $username = \app\models\User::find()->select('username')->where(['id' => $model->create_id])->scalar();
                    return $username ? $username : '';
                }
            ],
            [
                'label' => '来源',

                'attribute' => 'merchandisers',
                'value' =>
                    function ($model) {
                        if ($model->source == 1) {
                            return 'erp';   //主要通过此种方式实现
                        } elseif ($model->source == 2) {
                            return '当前系统';   //主要通过此种方式实现
                        } else {
                            return '通途系统';   //主要通过此种方式实现
                        }

                    },

            ],
            [
                'label' => '有效SKU数量',
                'attribute' => 'sku_num',
                'value' => function ($model) {
                    return $model->sku_num;
                    return \app\models\SupplierUpdateApply::getProduct($model->supplier_code);
                }
            ],
            [
                'label' => '审核状态',
                "format" => "raw",
                'visible' => true,
                'value' => function ($model) {
                    return SupplierUpdateLog::getAuditStatus($model->supplier_code);
                }
            ],
            [
                'label' => '审核备注',
                "format" => "raw",
                'visible' => false,
                'value' => function ($model) {
                    return SupplierUpdateLog::getAuditNote($model->supplier_code);
                }
            ],

            [
                'class' => 'kartik\grid\ActionColumn',
                'dropdown' => false,
                'width' => '180px',
                'template' => '{is-disable}{change-search}{update_info}{purchase_audit}{supply_chain_audit}{finance_audit}{view_operation_log}{view_info}{company_info}',//{edit}
                //'template' => \mdm\admin\components\Helper::filterActionColumn('{change}{change-search}{update}{purchase_audit}{supply_chain_audit}{finance_audit}{view_operation_log}'),//{edit}
                'buttons' => [
                    'is-disable' => function ($url, $model, $key) {
                        if ($model->status != 4) {
                            $status = $model->status == 1 ? '禁用' : '启用';
                            return Html::a("<i class='glyphicon glyphicon-eye-open'></i>" . $status, [$url], [
                                'class' => 'btn btn-xs red'
                            ]);
                        }
                    },
                    'change-search' => function ($url, $model, $key) {
                        $search = $model->search_status == 1 ? '禁用搜索' : '启用搜索';
                        return Html::a("<i class='glyphicon glyphicon-search'></i>" . $search, [$url], [
                            'class' => 'btn btn-xs red'
                        ]);
                    },
                    'view_info' => function ($url, $model, $key) {
                        return Html::a('<i class="glyphicon glyphicon-eye-open"></i> 查看', ['update-info', 'id' => $key, 'is_readonly' => true], [
                            'title' => Yii::t('app', '查看'),
                            'class' => 'btn btn-xs red',
                            'target' => '_blank'
                        ]);
                    },
                    'company_info' => function ($url, $model, $key) {
                        return Html::a('<i class="glyphicon glyphicon-info-sign"></i> 公司信息', ['company-info', 'credit_code' => $model->credit_code], [
                            'title' => Yii::t('app', '公司信息'),
                            'data-toggle' => 'modal',
                            'data-target' => '#create-modal',
                            'class' => 'company_info'
                        ]);
                    },
                    'update_info' => function ($url, $model, $key) {
                        return Html::a('<i class="glyphicon glyphicon-refresh"></i> 更新', ['update-info', 'id' => $key], [
                            'title' => Yii::t('app', '更新 '),
                            'class' => 'btn btn-xs purple'
                        ]);
                    },
                    'view_operation_log' => function ($url, $model, $key) { //采购审核
                        return Html::a('<i class="glyphicon glyphicon-list-alt"></i> 日志', ['view-operation-log', 'supplier_code' => $model->supplier_code], [
                            'title' => Yii::t('app', '修改供应商的操作日志 '),
                            'class' => 'btn btn-xs audit'
                        ]);
                    },
                    'purchase_audit' => function ($url, $model, $key) { //采购审核
                        $models = SupplierUpdateLog::find()
                            ->where(['supplier_code' => $model->supplier_code])
                            ->andWhere(['in', 'audit_status', ['1']])
                            ->orderBy('id desc')
                            ->one();

                        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->id);
                        $user_name = Yii::$app->user->identity->username;
                        if ($models && (in_array('采购组-海外', array_keys($roles)) || in_array('FBA采购组', array_keys($roles)) || in_array('采购组-国内', array_keys($roles)) || in_array('超级管理员组', array_keys($roles)) || ($user_name == '刘楚雯'))) {
                            if (Helper::checkRoute('audit-supplier')) {
                                return Html::a('<i class="glyphicon glyphicon-pawn"></i> 审核', ['update-info', 'supplier_code' => $model->supplier_code, 'id'=>$model->id,'is_readonly'=>true, 'is_audit'=>true], [
                                    'title' => Yii::t('app', '采购审核 '),
                                    'class' => 'btn btn-xs audit'
                                ]);
                            }
                        }
                    },
                    'supply_chain_audit' => function ($url, $model, $key) { //供应链审核
                        $models = SupplierUpdateLog::find()
                            ->where(['supplier_code' => $model->supplier_code])
                            ->andWhere(['in', 'audit_status', ['3']])
                            ->orderBy('id desc')
                            ->one();
                        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->id);
                        $user_name = Yii::$app->user->identity->username;
                        if ($models && (in_array('供应链', array_keys($roles)) || in_array('超级管理员组', array_keys($roles)) || ($user_name == '王开伟'))) {
                            if (Helper::checkRoute('audit-supplier')) {
                                return Html::a('<i class="glyphicon glyphicon-pawn"></i> 审核', ['update-info', 'supplier_code' => $model->supplier_code, 'id'=>$model->id,'is_readonly'=>true, 'is_audit'=>true], [
                                    'title' => Yii::t('app', '供应链审核 '),
                                    'class' => 'btn btn-xs audit'
                                ]);
                            }
                        }
                    },
                    'finance_audit' => function ($url, $model, $key) { //财务审核
                        $models = SupplierUpdateLog::find()
                            ->where(['supplier_code' => $model->supplier_code])
                            ->andWhere(['in', 'audit_status', ['5']])
                            ->orderBy('id desc')
                            ->one();
                        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->id);
                        if ($models && (in_array('财务组', array_keys($roles)) || in_array('超级管理员组', array_keys($roles)))) {
                            if (Helper::checkRoute('audit-supplier')) {
                                return Html::a('<i class="glyphicon glyphicon-pawn"></i> 审核', ['update-info', 'supplier_code' => $model->supplier_code, 'id'=>$model->id,'is_readonly'=>true, 'is_audit'=>true], [
                                    'title' => Yii::t('app', '财务审核 '),
                                    'class' => 'btn btn-xs audit'
                                ]);
                            }
                        }
                    },


                ],
            ],

        ],
        'containerOptions' => ["style" => "overflow:auto"], // only set when $responsive = false
        'toolbar' => [

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
//    'toggleDataOptions' =>[
//              'maxCount' => 10000,
//              'minCount' => 1000,
//              'confirmMsg' => Yii::t(
//                    'app',
//                  'There are {totalCount} records. Are you sure you want to display them all?',
//                  ['totalCount' => number_format($dataProvider->getTotalCount())]
//               ),
//        'all' => [
//            'icon' => 'resize-full',
//            'label' => Yii::t('app', '所有'),
//            'class' => 'btn btn-default',
//
//        ],
//        'page' => [
//            'icon' => 'resize-small',
//            'label' => Yii::t('app', '单页'),
//            'class' => 'btn btn-default',
//
//        ],
//    ],
        'exportConfig' => [
            GridView::EXCEL => [],
        ],
        'panel' => [
            //'heading'=>'<h3 class="panel-title"><i class="glyphicon glyphicon-globe"></i> Countries</h3>',
            'type' => 'success',
            //'before'=>false,
            //'after'=>false,
            //'after'=>Html::a('<i class="glyphicon glyphicon-repeat"></i> 刷新', ['index'], ['class' => 'btn btn-info']),
            //'footer'=>true
        ],
    ]); ?>
</div>
<?php
Modal::begin([
    'id' => 'create-modal',
    'header' => '<h4 class="modal-title">系统信息</h4>',
    'size' => 'modal-lg',
    'footer' => '<a href="#" class="btn btn-primary" data-dismiss="modal">关闭</a>',
]);
Modal::end();
$requestUrl = Url::toRoute('all-edit-supplier-attr');
$surl = Url::toRoute('supplier-review');
$furl = Url::toRoute('financial-audit');
$erpurl = Url::toRoute('erp-supplier');
$checkUrl = Url::toRoute('check');
$viewUrl = Url::toRoute('view-details');
$msg = '请选择需要修改的供应商';
$js = <<<JS
    $(document).on('click', '.view-details', function () {
        $.get('{$viewUrl}', {id:$(this).attr('value')},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });

    $(document).on('click', '#create', function () {
        var str='';
        //获取所有的值
        $("input[name='id[]']:checked").each(function(){
            str+=','+$(this).val();
            //alert(str);
    
        })
        str=str.substr(1);
        if (str&& str.length !=0)
        {
            $.get('{$requestUrl}', {id: str}, function (data) {
                $('.modal-body').html(data);
                $('.modal-body').find("#supplierbuyer-buyer").select2({
                    placeholder: '请输入采购员 ...'
                });
            });
        } else {
            $('.modal-body').html('{$msg}');
            return false;
        }
    });

    $(document).on('click', '#creates', function () {

        $.get($(this).attr('href'), {id:$(this).attr('value')},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
    
    $(document).on('click', '.company_info', function () {
        $('.modal-body').html('正在请求中。。。。。。');
        $.get($(this).attr('href'), {},
            function (data) {
                $('.modal-body').html(data);
                $('.modal-title').html('供应商基本资料');
            }
        );
    });
    $(document).on('click', '#config-quotes', function () {
        var ids = $('#grid-supplier').yiiGridView('getSelectedRows');
        $.get($(this).attr('href'), {ids:ids.join(',')},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
    
    $(document).on('click', '#updates', function () {

        $.get($(this).attr('href'), {id:$(this).attr('value')},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
    $(document).on('click', '.erpsupplier', function () {
        $.get('{$erpurl}',{},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
        //供应商审核    
        $(".supplier-review").click(function(){
            var ids = $('#grid-supplier').yiiGridView('getSelectedRows');
            if(ids==''){
                alert('请先选择数据!');
                return false;
            }else{
                $.post('{$surl}', {ids:ids},
                    function (data) {
                        $('.modal-body').html(data);
                    }
                );
               return false; 
            }
        });
        
        //财务审核
         $(".financial-audit").click(function(){
            var ids = $('#grid-supplier').yiiGridView('getSelectedRows');
            if(ids==''){
                alert('请先选择数据!');
                return false;
            }else{
                $.post('{$furl}', {ids:ids},
                    function (data) {
                        $('.modal-body').html(data);
                    }
                );
               return false; 
            }
        });
         
         $(document).on('click','#check',function() {
            var ids = $('#grid-supplier').yiiGridView('getSelectedRows');
           if(ids.length==0){
                $('#check-modal').modal('hide');
               layer.alert('请至少选择一个待审供应商');
               return false;
           }else {
                $.get('{$checkUrl}', {ids:ids},function (data) {
                    $('#check-modal .modal-body').html(data);
                });
           }
        });

        $(function() {
            //批量导出
            $('#export-csv').click(function() {
                var ids = $('#grid-supplier').yiiGridView('getSelectedRows');
                /*if(ids==''){
                    alert('请先选择!');
                    return false;
                }else{*/
                     
                    window.location.href='/supplier/export-csv?ids='+ids;
                /*}*/
            });
        });
JS;
$this->registerJs($js);
?>
