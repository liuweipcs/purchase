<?php
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use app\services\PurchaseOrderServices;
use app\services\SupplierServices;
use app\services\BaseServices;
use app\models\PurchaseOrderItems;
$this->title = '质检不合格';
$this->params['breadcrumbs'][] = $this->title;
?>
    <style type="text/css">
        em {
            font-style: normal;
            color: red;
        }
    </style>



    <div class="panel panel-success">
    <div class="panel-body">
        <?php echo $this->render('_search', ['model'=>$searchModel]); ?>
    </div>
</div>

<?= GridView::widget([
        'dataProvider' => $dataProvider,

        'options'=>[
            'id'=>'grid_purchase_order',
        ],


        'pager'=>[
            'options'=>['class' => 'pagination','style'=> "display:block;"],
            'class'=>\liyunfang\pager\LinkPager::className(),
            //'pageSizeList' => [20, 50, 100, 200],
//                'options'=>['class'=>'hidden'],//关闭分页
            'firstPageLabel'=>"首页",
            'prevPageLabel'=>'上一页',
            'nextPageLabel'=>'下一页',
            'lastPageLabel'=>'末页',
        ],
        'columns' => [
            [
                'label'=>'id',
                'attribute' => 'ids',
                'value'=>
                    function($model){
                        return  $model->id;   //主要通过此种方式实现
                    },

            ],
            [
                'label' => '图片',
                'format' => 'raw',
                'value' => function($data){
                    if($data->img_path_data) {
                        $img = json_decode($data['img_path_data']);

                        $domain = Yii::$app->params['wms_domain'];

                        $url = $domain.$img[0];

                        return "<a href='javascript:void(0)' class='img' title='点击查看大图'><img src='{$url}' width='100px' height='100px'></a>";
                    }
                }
            ],
            [
                'label' => '单号',
                'width' => '200px',
                "format" => "raw",
                'value' => function($model, $url, $key) {
                    $s = !empty($value['cargo_company_id']) ? $value['cargo_company_id'] : '';
                    $url = 'https://www.kuaidi100.com/chaxun?com=' . $s . '&nu=' . $model->express_code;
                    $data = '<p>异常单号：'.$model->defective_id.'</p>';
                    $data .= "<p>快递单号：<a target='_blank' href='{$url}'>".$model->express_code."</a></p>";
                    $data .= '<p>异常货位：'.$model->position.'</p>';
                    return $data;
                },

            ],
            [
                'label' => '信息',
                'width' => '200px',
                "format" => "raw",
                'value' => function($model, $url, $key) {
                    $data = '<p>采购单号：'.$model->purchase_order_no.'</p>';
                    $data .= '<p>SKU：'.$model->sku.'</p>';
                    $data .= '<p>SKU数量：'.$model->num.'</p>';
                    $data .= '<p>采购员：<em>'.$model->buyer.'</em></p>';
                    return $data;
                },

            ],
            [
                'label'=>'是否处理',
                "format" => "raw",
                'value'=> function($model, $url, $key) {
                    $span = [
                        '0' => '<span class="label label-default">未处理</span>',
                        '1' => '<span class="label label-success">已处理</span>',
                        '2' => '<span class="label label-danger">处理错误</span>'
                    ];
                    return isset($span[$model->is_handler]) ? $span[$model->is_handler] : '';
                },
            ],
            [
                'label' => '处理类型',
                "format" => "raw",
                'value' => function($model, $url, $key) {
                    if($model->handler_type) {
                        return PurchaseOrderServices::getExpHandlerType($model->handler_type);
                    }
                },
            ],
            [
                'label' => '处理人',
                'value' => function($model) {
                    return $model->handler_person;
                }
            ],
            [
                'label' => '异常信息',
                'width' => '300px',
                "format" => "raw",
                'value' => function($model, $url, $key) {
                    $data = '<p>创建人：'.$model->add_username.'</p>';
                    $data .= '<p>拉取时间：'.$model->pull_time.'</p>';
                    $data .= '<p>异常原因：'.$model->abnormal_depict.'</p>';
                    return $data;
                },
            ],
            [
                'label' => '仓库处理结果',
                'width' => '300px',
                "format" => "raw",
                'value' => function($model) {
                    $push_info = '';
                    if($model->is_push_to_warehouse==0 && $model->is_handler==1  && empty($model->warehouse_handler_result)){
                        $push_info = '未推送';
                    }elseif($model->is_handler==2){
                        $push_info = '推送失败:';
                        if(!empty($model->warehouse_handler_result)){
                            $push_info = '推送失败:';
                        }
                    }elseif($model->is_handler==1 && $model->is_push_to_warehouse==1){
                        $push_info = '已推送';
                        if(!empty($model->warehouse_handler_result)){
                            $push_info = '已推送:';
                        }
                    }
                    if(!empty($model->warehouse_handler_result)){
                        $push_info .= $model->warehouse_handler_result;
                    }
                    return $push_info;
                },
            ],
            [
                'class' => 'kartik\grid\ActionColumn',
                'dropdown' => false,
                'width'=>'180px',
                'template' => '{view}{handler}{return-info}',
                'buttons'=>[

                    'view' => function($url, $model, $key) {
                        if($model->is_handler == 1) {
                            return Html::a('<i class=" fa fa-fw fa-plus-square"></i> 查看', ['view', 'defective_id' => $model->defective_id], [
                                'title' => Yii::t('app', '查看'),
                                'class' => 'btn btn-xs view',
                                'data-toggle' => 'modal',
                                'data-target' => '#create-modal',
                            ]);
                        }
                    },
                    'return-info'=>function($url,$model,$key){
                        if(in_array($model->handler_type,[2,7])){
                            return Html::a('<i class=" fa fa-fw fa-plus-square"></i> 退货信息', ['/exp-cipin/excep-return-info','excep_number'=>$model->defective_id], [
                                'title' => Yii::t('app', '退货信息'),
                                'class' => 'btn btn-xs info',
                                'data-toggle' => 'modal',
                                'data-target' => '#create-modal',
                            ]);
                        }
                    },
                    'handler' => function ($url, $model, $key) {
                        if($model->is_handler == 0 || $model->is_push_to_warehouse == 0 || $model->handler_type == 15) {
                            return Html::a('<i class=" fa fa-fw fa-plus-square"></i> 采购员处理', ['handler','defective_id'=>$model->defective_id], [
                                'title' => Yii::t('app', '采购员处理'),
                                'class' => 'btn btn-xs handler',
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
        'data-backdrop'=>'static',//点击空白处不关闭弹窗
    ],
]);
Modal::end();

$js = <<<JS
$(function() {
    
    $('.handler').click(function() {
        $('#create-modal .modal-body').html('');
        $('#create-modal .modal-body').load($(this).attr('href'));
    });
    
    $('.info').click(function() {
        $('#create-modal .modal-body').html('');
        $('#create-modal .modal-body').load($(this).attr('href'));
    });
    
    $('.view').click(function() {
        $('#create-modal .modal-body').html('');
        $('#create-modal .modal-body').load($(this).attr('href'));
    });
    
    $('.img').click(function() {
        var json = {
            "data": [{"src": $(this).find('img').attr('src')}]
        };
        layer.photos({
            photos: json,
            anim: 5 
        });
    });
    
});     

JS;
$this->registerJs($js);
?>