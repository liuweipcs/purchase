<?php
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use app\config\Vhelper;
use yii\helpers\Url;
use app\services\PurchaseOrderServices;
$this->title = '报损信息';
$this->params['breadcrumbs'][] = '采购管理';
$this->params['breadcrumbs'][] = '采购异常';
$this->params['breadcrumbs'][] = $this->title;
?>


    <div class="panel panel-default">
        <div class="panel-body">
            <?php echo $this->render('_search', ['model'=>$searchModel]); ?>
        </div>
    </div>

<?= GridView::widget([
    'dataProvider' => $dataProvider,

    'options'=>[
        'id'=>'grid_purchase_order',
    ],



    'columns' => [
        [
            'class' => 'kartik\grid\CheckboxColumn',
            'name'=>"id" ,
        ],
        [
            'label'=>'id',
            'attribute' => 'ids',
            'value'=>
                function($model){
                    return $model->id;
                },

        ],
        [
            'label' => '采购单号',
            "format" => "raw",
            'value' => function($model) {
                return $model->pur_number;
            },
        ],
        [
            'label' => '状态',
            "format" => "raw",
            'value' => function($model) {
                return PurchaseOrderServices::getBreakageStatus($model->status);
            },
        ],
        [
            'label' => '商品',
            "format" => "raw",
            'value' => function($model) {
                $data = '<p>SKU：'.$model->sku.'</p>';
                $data .= '<p>'.$model->name.'</p>';
                return $data;
            },

        ],
        [
            'label' => '单价',
            "format" => "raw",
            'value' => function($model) {
                return $model->price;
            }
        ],
        [
            'label' => '确认数量',
            "format" => "raw",
            'value' => function($model) {
                return $model->ctq;
            },
        ],
        [
            'label' => '入库数量',
            "format" => "raw",
            'value' => function($model) {
                return $model->qty;
            },
        ],
        [
            'label'=>'报损数量',
            "format" => "raw",
            'value' => function($model) {
                return $model->breakage_num;
            }
        ],
        [
            'label' => '报损金额',
            'value' => function($model) {
                return $model->items_totalprice;
            }
        ],
        [
            'label' => '申请人/时间',
            "format" => "raw",
            'value' => function($model) {
                $data = '<p>'.$model->apply_person.'</p>';
                $data .= '<p>'.$model->apply_time.'</p>';
                return $data;
            }
        ],
        [
            'label' => '审核人/时间',
            "format" => "raw",
            'value' => function($model) {
                $data = '<p>'.$model->audit_person.'</p>';
                $data .= '<p>'.$model->audit_time.'</p>';
                return $data;
            }
        ],
        [
            'class' => 'kartik\grid\ActionColumn',
            'dropdown' => false,
            'width' => '180px',
            'template' => '{view}{caiwu-view}{edit}',
            'buttons' => [

                'view' => function ($url, $model, $key) {
                    return Html::a('<i class=" fa fa-fw fa-plus-square"></i> 采购审核', ['view', 'id' => $model->id], [
                        'title' => Yii::t('app', '采购审核'),
                        'class' => 'btn btn-xs view',
                        'data-toggle' => 'modal',
                        'data-target' => '#create-modal',
                    ]);
                },

                'caiwu-view' => function ($url, $model, $key) {
                    return Html::a('<i class=" fa fa-fw fa-plus-square"></i> 财务审核', ['caiwu-view', 'id' => $model->id], [
                        'title' => Yii::t('app', '财务审核'),
                        'class' => 'btn btn-xs caiwu-view',
                        'data-toggle' => 'modal',
                        'data-target' => '#create-modal',
                    ]);
                },

                'edit' => function ($url, $model, $key) {
                    if($model->status !== 1) {
                        return Html::a('<i class=" fa fa-fw fa-plus-square"></i> 编辑', ['edit', 'id' => $model->id], [
                            'title' => Yii::t('app', '编辑'),
                            'class' => 'btn btn-xs edit',
                            'data-toggle' => 'modal',
                            'data-target' => '#create-modal',
                        ]);
                    }
                },
            ],
        ],
    ],
    'containerOptions' => ["style"=>"overflow:auto"], // only set when $responsive = false
    'toolbar' =>  [
        '{export}',
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
        //'heading'=>'<h3 class="panel-title"><i class="glyphicon glyphicon-globe"></i> Countries</h3>',
        'type'=>'success',
        //'after'=>Html::a('<i class="glyphicon glyphicon-repeat"></i> 刷新', ['index'], ['class' => 'btn btn-info']),
        //'footer'=>true
    ],
]); ?>
<?php
Modal::begin([
    'id' => 'create-modal',
    'header' => '<h4 class="modal-title">系统信息</h4>',
    'footer' => '<a href="#" class="btn btn-primary closes" data-dismiss="modal">关闭</a>',
    'size'=>'modal-lg',
    'options'=>[
        'data-backdrop'=>'static',
    ],
]);
Modal::end();

$js = <<<JS
$(function() {
    
    $('.view').click(function() {
        $('#create-modal .modal-body').html('');
        $('#create-modal .modal-body').load($(this).attr('href'));
    });
    $('.edit').click(function() {
        $('#create-modal .modal-body').html('');
        $('#create-modal .modal-body').load($(this).attr('href'));
    });
    
        $('.caiwu-view').click(function() {
        $('#create-modal .modal-body').html('');
        $('#create-modal .modal-body').load($(this).attr('href'));
    });
    
    
    
    
});     

JS;
$this->registerJs($js);
?>