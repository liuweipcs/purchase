<?php
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use mdm\admin\components\Helper;
use app\models\PurchaseOrderItems;
use app\services\BaseServices;
use app\services\SupplierServices;
use app\services\PurchaseOrderServices;
use yii\widgets\LinkPager;

$this->title = '海外仓-采购审核';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php /*//if(!empty($grade) && $grade->grade==3){ */?><!--
<?php /*if(false){ */?>
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>组长未审核</th>
            <th>主管未审核</th>
        </tr>
        </thead>
        <tbody>
        <tr class="table-module-b1">
            <td><?/*=$leadsum*/?></td>
            <td><?/*=$supervissum*/?></td>
        </tr>
        </tbody>
    </table>
--><?php /*} */?>

<div class="panel panel-default">
    <div class="panel-body"><?= $this->render('_search', ['model' => $searchModel]); ?></div>
    <div class="panel-footer">
        <?php
        if(!empty($grade)) {
            if (false) {
                $g = $grade->grade;
                if ($g == 1) {
                    echo Html::a('组长审核', ['all-review', 'name' => 'audit'], ['class' => 'btn btn-success', 'id' => 'batch_review', 'data-toggle' => 'modal', 'data-target' => '#create-modal',]);
                }

                if ($g == 2) {
                    echo Html::a('主管审核', ['all-review', 'name' => 'audit'], ['class' => 'btn btn-success', 'id' => 'batch_review', 'data-toggle' => 'modal', 'data-target' => '#create-modal',]);
                }

                if ($g == 3) {
                    echo Html::a('经理审核', ['all-review', 'name' => 'audit'], ['class' => 'btn btn-success', 'id' => 'batch_review', 'data-toggle' => 'modal', 'data-target' => '#create-modal',]);
                }
            }
        }




        if(Helper::checkRoute('all-review') && $source == 2) {


            echo Html::a('批量审核', ['all-review','name'=>'audit'], ['class' => 'btn btn-success','id'=>'batch_review','data-toggle' => 'modal', 'data-target' => '#create-modal',]);


        }





        ?>
    </div>
</div>

<?php if($source == 1): ?>

<div class="btn-group" style="margin-bottom: 10px;">
    <span class="btn btn-danger" disabled="disabled">合同单</span>
    <a href="?source=2" class="btn btn-default">网采单</a>
</div>

<div class="panel panel-success">
    <div class="panel-heading">
        <div class="pull-right">共<b><?= $pagination->totalCount ?></b>条数据</div>
        <div class="clearfix"></div>
    </div>

        <table class="table table-bordered table-hover">
            <thead>
                <th>合同单号</th>
                <th>订单号</th>
                <th>订单状态</th>
                <th>仓库</th>
                <th>采购员</th>
                <th>供应商名称</th>
                <th>SKU数量</th>
                <th>采购数量</th>
                <th>总金额（RMB）</th>
                <th>运费</th>
                <th>结算方式</th>
                <th>确认备注</th>
                <th>审核备注</th>
                <th>审核操作记录</th>
                <th>操作</th>
            </thead>
            <tbody>

            <?php if(empty($list)): ?>

                <tr>
                    <td colspan="15" style="color: #ccc;">没有数据...</td>
                </tr>

            <?php else: ?>

            <?php
            foreach($list as $compact_number => $items):
                $r = count($items);
            ?>

                <?php foreach($items as $k => $i): ?>

                <tr>

                    <?php if($k == 0): ?>

                        <td rowspan="<?= $r ?>" style="vertical-align: middle;text-align: center;"><?= $compact_number ?></td>

                    <?php endif; ?>

                    <td><?= $i['pur_number'] ?></td>
                    <td><?= PurchaseOrderServices::getPurchaseStatus($i['purchas_status']) ?></td>
                    <td></td>
                    <td><?= $i['buyer'] ?></td>
                    <td><?= $i['supplier_name'] ?></td>
                    <td><?= PurchaseOrderItems::find()->where(['pur_number' => $i['pur_number']])->count(); ?></td>
                    <td></td>
                    <td><?= round(PurchaseOrderItems::getCountPrice($i['pur_number']),2); ?></td>

                    <td>
                        <?= !empty($i->purchaseOrderPayType) ? $i->purchaseOrderPayType->freight : 0; ?>
                    </td> <!-- 运费 -->

                    <td><?= SupplierServices::getSettlementMethod($i['account_type']); ?></td>
                    <td>
                        <?= $i->orderNote['note']; ?>
                    </td>
                    <td><?= $i['audit_note'] ?></td>
                    <td><?= $i['review_remarks'] ?></td>
                    <?php if($k == 0): ?>

                        <td rowspan="<?= $r ?>" style="vertical-align: middle;">
                            <a class="btn btn-xs compact-review" href="/overseas-purchase-order-audit/compact-review?cpn=<?= $compact_number ?>" data-toggle="modal" data-target="#create-modal"><i class="glyphicon glyphicon-ok"></i> 审核</a>
                        </td>

                    <?php endif; ?>

                </tr>

            <?php endforeach; ?>


            <?php endforeach; ?>

            <?php endif; ?>

            </tbody>
        </table>


    <div class="panel-footer">
        <?= LinkPager::widget([
            'pagination' => $pagination,
            'firstPageLabel' => "首页",
            'prevPageLabel' => '上一页',
            'nextPageLabel' => '下一页',
            'lastPageLabel' => '末页',
            'options' => ['class' => 'pagination no-margin']
        ]);
        ?>
    </div>

</div>

<?php else: ?>

<div class="btn-group" style="margin-bottom: 10px;">
    <a href="?source=1" class="btn btn-default">合同单</a>
    <span class="btn btn-danger" disabled="disabled">网采单</span>
</div>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'options' => ['id' => 'grid_purchase_order'],
    'pager' => [
        'firstPageLabel' => "首页",
        'prevPageLabel' => '上一页',
        'nextPageLabel' => '下一页',
        'lastPageLabel' => '末页',
    ],
    'columns' => [
        [
            'class' => 'yii\grid\CheckboxColumn',
            'name'=>"id" ,
            'checkboxOptions' => function ($model) {
                return ['value' => $model->pur_number];
            }
        ],
        [
            'label'=>'PO号',
            'attribute' => 'pur_numbers',
            "format" => "raw",
            'value'=> function($model){
                return Html::a($model->pur_number, ['review','name'=>'audit'],['class' => 'purple',
                    'data-toggle' => 'modal',
                    'data-target' => '#create-modal',
                    'value' =>$model->pur_number,
                    'id' =>$model->id,
                ]);
            },

        ],
        [
            'label'=>'创建类型',
            'attribute' => 'create_type',
            "format" => "raw",
            'value'=> function($model){
                if ($model->create_type == 1) {
                    $data = '<span class="label label-success">系统</span>&nbsp;&nbsp;';

                } elseif ($model->create_type == 2) {
                    $data = '<span class="label label-info">手工</span>&nbsp;&nbsp;';
                } else {
                    $data = '';
                }
                return $data;
            },
        ],
        [
            'label'=>'订单状态',
            'attribute' => 'pur_numbers',
            "format" => "raw",
            'value'=> function($model){
                return $data = '<span class="label label-primary">'.PurchaseOrderServices::getPurchaseStatus($model->purchas_status).'</span>&nbsp;&nbsp;';

            },

        ],
        [
            'label'=>'仓库',
            'attribute' => 'ids',
            "format" => "raw",
            'value'=>
                function($model){

                    if(!empty($model->is_transit) && $model->is_transit==1 && $model->transit_warehouse)
                    {
                        $data   = BaseServices::getWarehouseCode($model->transit_warehouse);
                        $data  .=!empty($model->warehouse_code)?'-'.BaseServices::getWarehouseCode($model->warehouse_code):'<br/>';

                    } else {
                        $data  =!empty($model->warehouse_code)?BaseServices::getWarehouseCode($model->warehouse_code):'<br/>';
                    }
                    return  $data;   //主要通过此种方式实现
                },

        ],
        'buyer',

        [
            'label'=>'供应商名称',
            'value'=>'supplier_name',
        ],

        [
            'label'=>'SKU数量',
            'value'=> function($model){
                return PurchaseOrderItems::find()->where(['pur_number'=>$model->pur_number])->count('id');
            },
        ],

        [
            'label'=>'采购数量',
            'value'=> function($model){
                return PurchaseOrderItems::find()->where(['pur_number'=>$model->pur_number])->sum('ctq');
            },
        ],

        [
            'label'=>'总金额( RMB )',
            "format" => "raw",
            'value'=>function($model){
                return round(PurchaseOrderItems::getCountPrice($model->pur_number),2);
            },
        ],

        [
            'label'=>'运费',
            'value'=>function($model){

                $freight = 0;
                $freight1 = \app\models\PurchaseOrderShip::find()->where(['pur_number'=>$model->pur_number])->sum('freight');
                $freight2 = 0;
                if(!empty($model->purchaseOrderPayType)) {
                    $freight2 = $model->purchaseOrderPayType->freight ? $model->purchaseOrderPayType->freight : 0;
                }
                if($freight2) {
                    $freight = $freight2;
                } else {
                    $freight = $freight1;
                }

                return $freight;

            },
        ],

        [
            'label' => '结算方式',
            'format'=>'raw',
            'value'=>function($model){
                $style='';
                $grade=\app\models\PurchaseUser::findOne(['pur_user_id'=>Yii::$app->user->id]);
                if(!empty($model->e_account_type) && !empty($grade) && $grade->grade<3){
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
            'value'=>
                function($model){
                    return $model->orderNote['note'];
                },
        ],
        [
            'label'=>'审核备注',
            'attribute' => 'created_ats',
            "format" => "raw",
            'value'=>
                function($model){
                    return $model->audit_note;
                },
        ],
        [
            'label'=>'审核操作记录',
            "format" => "raw",
            'value'=>
                function($model){
                    $rr=explode(',',$model->review_remarks);

                    $html='';
                    if(!empty($rr)){
                        if($rr[0]){
                            return $html=$rr[0];
                        }

                        if($rr[0] && $rr[1]){
                            return $html=$rr[0]."<br/>".$rr[1];
                        }

                        if($rr[0] && $rr[1] && $rr[2]){
                            return $html=$rr[0]."<br/>".$rr[1]."<br/>".$rr[2];
                        }
                    }
                    return $html;
                },
        ],
        [
            'class' => 'kartik\grid\ActionColumn',
            'dropdown' => false,
            'width'=>'180px',
            'template' => \mdm\admin\components\Helper::filterActionColumn('{review}{view}'),
            'buttons'=>[
                'view' => function ($url, $model, $key) {
                    return Html::a('<i class="glyphicon glyphicon-eye-open"></i> 详细', ['purchase-order/views','id'=>$key], [
                        'title' => Yii::t('app', '详细'),
                        'class' => 'btn btn-xs red view',
                        'data-toggle' => 'modal',
                        'data-target' => '#create-modal',
                    ]);
                },
                'review' => function ($url, $model, $key) {
                    return Html::a('<i class="glyphicon glyphicon-ok"></i> 审核', ['review','name'=>'audit'], [
                        'title' => Yii::t('app', '审核 '),
                        'class' => 'btn btn-xs purple',
                        'data-toggle' => 'modal',
                        'data-target' => '#create-modal',
                        'id' =>$key,
                    ]);
                },
            ],
        ],
    ],
    'containerOptions' => ["style"=>"overflow:auto"], // only set when $responsive = false
    'toolbar' =>  [
        //'{export}',
    ],

    'pjax' => false,
    'bordered' =>true,
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
        // 'footer' => '<a></a>'
    ],
]); ?>

<?php endif; ?>




<?php
Modal::begin([
    'id' => 'create-modal',
    'header' => '<h4 class="modal-title">系统信息</h4>',
    'footer' => '<a href="#" class="btn btn-primary closes" data-dismiss="modal">关闭</a>',
    'size' => 'modal-lg',
    'options' => [
        'data-backdrop' => 'static',
    ],
]);
Modal::end();
$historys = Url::toRoute(['tong-tool-purchase/get-history']);
$page = Yii::$app->request->get('page') ? Yii::$app->request->get('page') : 1;

?>

<?php
$js = <<<JS

$(function() {
    
    
    $("#batch_review").on('click', function(){
        var ids = $('#grid_purchase_order').yiiGridView('getSelectedRows');
        if(ids==''){
            alert('请先选择!');
            return false;
        }else{
            $.get($(this).attr('href'), {id: ids,page: '{$page}'},
                function (data) {
                    $('.modal-body').html(data);
                }
            );
        }
    });
    
    // 合同单审核
    $('a.review').click(function() {
        $.get($(this).attr('href'), function(data) {
            $('.modal-body').html(data);
        });
    });
    
    
    
    
    
    
});





    $(document).on('click', '.b', function () {

        $.get($(this).attr('href'), {sku:$(this).attr('data')},
            function (data) {
               $('.modal-body').html(data);
            }
        );
    });
    $(document).on('click','.data-updates', function () {

        $.get('{$historys}', {sku:$(this).attr('data')},
            function (data) {
               $('.modal-body').html(data);

            }
        );
    });
    $(document).on('click', '#views', function () {
        $.get($(this).attr('href'), {id:$(this).attr('value'),status:$(this).attr('status'),currency_code:$(this).attr('currency_code')},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
    $(document).on('click', '.purple', function () {
        $.get($(this).attr('href'), {id:$(this).attr('id'),page:'{$page}'},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
     $(document).on('click', '.view', function () {
        $.get($(this).attr('href'), {id:$(this).attr('id')},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
          $(document).on('click', '.compact-review', function () {
              $('.modal-body').html('Waiting...');
        $.get($(this).attr('href'), {id:$(this).attr('id')},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
     
     
     
     



JS;
$this->registerJs($js);
?>
