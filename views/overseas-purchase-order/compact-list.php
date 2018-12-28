<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use kartik\grid\GridView;
$this->title = '合同管理';
$this->params['breadcrumbs'][] = '采购单';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="panel panel-success">
    <div class="panel-body">
        <?= $this->render('_search-compact', ['model' => $searchModel]); ?>
    </div>
</div>

<?php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'options' => ['id' => 'grid'],
    'columns' => [
        [
            'class' => 'kartik\grid\CheckboxColumn',
            'name' => 'id',
            'checkboxOptions' => function ($model) {
                return ['value' => $model->id];
            }
        ],
        [
            'label' => 'id',
            'attribute' => 'ids',
            'value' => function($model) {
                return $model->id;
            }
        ],
        [
            'label' => '合同号',
            'format' => 'raw',
            'value' => function($model) {
                $data = '<p>'.$model->compact_number.'</p>';
                if($model->payment_status == 4) {
                    $data .= '<p>'.Html::a('<i class="glyphicon glyphicon-refresh"></i>', ['refresh-comapct', 'cpn' => $model->compact_number], [
                         'title' => '刷新合同'
                    ]).'</p>';
                }
                return $data;
            }
        ],
        [
            'label' => '金额',
            'format' => 'raw',
            'value' => function($model) {
                $data = '<p style="color: #E06B26;font-weight: bold;">商品额：'.$model->product_money.'</p>';
                $data .= '<p style="color: #E06B26;font-weight: bold;">运费：'.$model->freight.'</p>';
                $data .= '<p style="color: #E06B26;font-weight: bold;">优惠：'.$model->discount.'</p>';
                $data .= '<p style="color: #E06B26;font-weight: bold;">实际金额：'.$model->real_money.'</p>';
                return $data;
            }
        ],
        [
            'label' => '状态',
            'format' => 'raw',
            'value' => function($model) {
                if($model->compact_status) {
                    return \app\services\PurchaseOrderServices::getCompactStatus($model->compact_status);
                }
            }
        ],
        [
            'label' => '是否退税',
            'format' => 'raw',
            'value' => function($model) {

                if($model->is_drawback == 1) {
                    $data = '<p><span class="label label-success">不退税</span></p>';
                } elseif($model->is_drawback == 2) {
                    $data = '<p><span class="label label-info">退税</span></p>';
                }
                return $data;
            }
        ],












        [
            'label' => '供应商',
            'headerOptions' => ['width' => '300px'],
            'value' => function($model) {
                return $model->supplier_name;
            }
        ],
        [
            'label' => '操作人',
            'format' => 'raw',
            'value' => function($model) {
                $data = '<p>创建人：'.$model->create_person_name.'</p>';
                $data .= '<p>审核人：'.$model->audit_person_name.'</p>';
                return $data;
            }
        ],
        [
            'label' => '操作时间',
            'format' => 'raw',
            'value' => function($model) {
                $data = '<p>创建：'.$model->create_time.'</p>';
                $data .= '<p>审核：'.$model->audit_time.'</p>';
                return $data;
            }
        ],
        [
            'class' => 'kartik\grid\ActionColumn',
            'width' => '180px',
            'header' => '操作',
            'template' => "{print}{view}{download}{delete}",
            'buttons' => [
                'print' => function($url, $model, $key) {
                    return Html::a('<i class="glyphicon glyphicon-print"></i> 打印', ['/purchase-compact/print-compact', 'id' => $model->id], [
                        'title' => '打印',
                        'class' => 'btn btn-xs red print-compact',
                        'target' => '_blank'
                    ]);
                },
                'view' => function($url, $model, $key) {
                    return Html::a('<i class="glyphicon glyphicon-map-marker"></i> 详情',['/purchase-compact/view', 'id' => $model->id], [
                        'title' => '详情',
                        'class' => 'btn btn-xs red view',
                    ]);
                },
                'download' => function($url, $model, $key) {
                    return Html::a('<i class="glyphicon glyphicon-download-alt"></i> 下载合同',['/purchase-compact/download-compact', 'id' => $model->id], [
                        'title' => '下载',
                        'class' => 'btn btn-xs red',
                    ]);
                },
                'delete' => function($url, $model, $key) {
                    $status = \app\controllers\PurchaseCompactController::actionAbleDel($model->id);
                    if($status == 1 && $model->tpl_id != 5){
                        return Html::a('<i class="fa fa-fw fa-close"></i> 删除',['/purchase-compact/revoke-compact', 'id' => $model->id], [
                            'title' => '删除',
                            'class' => 'btn btn-xs red',
                        ]);
                    }
                },
            ]
        ]
    ],

    'panelBeforeTemplate' => '<div>
            <div class="btn-toolbar kv-grid-toolbar" role="toolbar">
                {toolbar}
            </div>    
        </div>
            {before}
        <div class="clearfix"></div>',
    'toolbar' => [],
    'containerOptions' => ["style" => "overflow:auto"],
    'striped' => false,
    'condensed' => true,
    'hover' => true,
    'showPageSummary' => false,
    'panel' => [
        'type' => 'success',
    ]
]);
?>

