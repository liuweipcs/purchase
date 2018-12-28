<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use app\models\LogisticsImport;
use mdm\admin\components\Helper;

/* @var $this yii\web\View */
/* @var $searchModel app\models\LogisticsCarrierSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '加急包裹导入');
$this->params['breadcrumbs'][] = $this->title;
?>
<style type="text/css">
    .table-bordered tr, .table-bordered td, .table-bordered th{border: 1px solid #cccccc !important; background-color: white}
</style>
<div class="logistics-import-index">
<?php  echo $this->render('_search', ['model' => $searchModel]); ?><!--搜索框-->
<p class="clearfix"></p>

<?php 
    if(Helper::checkRoute('batch-import')) {
        echo Html::a(Yii::t('app', '批量导入加急单号'), ['batch-import'], ["class" => "btn btn-success ",'data-toggle' => 'modal','data-target' => '#create-modal','id' => 'creates']);
    }
 ?>

<?php if(Helper::checkRoute('push-logistics-data')) {
        echo Html::a(Yii::t('app', '手动推送'), ['push-logistics-data'], ["class" => "btn btn-success ",'data-toggle' => 'modal','data-target' => '#create-modal','id' => 'push']);
    } 
?>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    //'filterModel' => $searchModel,
    'pager'=>[
        'firstPageLabel'=>"首页",
        'prevPageLabel'=>'上一页',
        'nextPageLabel'=>'下一页',
        'lastPageLabel'=>'末页',
    ],
    'options'=>[
        'id'=>'logistics_num',
    ],
    'columns' => [
        [
            'class' => 'yii\grid\CheckboxColumn', //CheckboxColumn 显示复选框
            'name'=>"id" ,
            'checkboxOptions' => function ($model) {
                return ['value' => $model->logistics_num];
            }
        ],

        /*[
            'label'=>'id',
            'attribute' => 'ids',
            'value'=>
                function($model){
                    return  $model->id;   //主要通过此种方式实现
                },

        ],*/

        [
            'label'=>'物流单号',
            'value'=>'logistics_num',

        ],
        [
            'label'=>'采购单号',
            'value'=>'purchase_order_num',

        ],
        [
            'label'=>'导入人',
            'value'=>'create_name',
        ],
        [
            'label'=>'导入时间',
            'attribute' => 'create_time',
            "format" => "raw",
            'value'=> function($model){

                    return  $model->create_time;   
                },

        ],
        [
            'label'=>'更新时间',
            'attribute' => 'update_time',
            "format" => "raw",
            'value'=> function($model){
                    return  $model->update_time;   
                },

        ],
        [
            'label'=>'是否推送到仓库',
            'attribute' => 'push_status',
            "format" => "raw",
            'value'=>
                function($model){
                    return  ($model->push_status) ? '是':'否';   //主要通过此种方式实现
                },

        ],  
        [
            'label'=>'推送结果',
            //'value'=>'push_res',
            'format'=>"html",
            'value'=>
                    function($model){
                        switch ($model->push_res) {
                            case '未推送':
                                return '<font style="color:#00a65a">未推送</font>';
                                break;
                            case '推送成功':
                                return '<font style="color:blue">推送成功</font>';
                                break;
                            default:
                                return '<font style="color:red">'.$model->push_res.'</font>';
                                break;
                        }
                        
                    },
                   
        ],
        [
            'class' => 'kartik\grid\ActionColumn',
            'dropdown' => false,
            'header' => "操作",
            'width'=>'180px',
            'template' => \mdm\admin\components\Helper::filterActionColumn('{view}'),
            'buttons'=>[
                'view' => function ($url, $model, $key) {
                    return (($model->push_res !='此条数据已经加急了')&&($model->is_deleted&&($model->push_status!=1))) ? (Html::a('<i class="glyphicon glyphicon-trash"></i>删除', ['logistics-import/delete','id'=>$model->id], [
                        'title' => Yii::t('app', '删除'),
                        'class' => 'btn btn-xs red _delete',
                        'data-toggle'=>"modal", 
                        'data-target'=>"#_delete",
                        'data-method'=>"post",
                        'data-confirm' => '您确定要删除此项吗？'
                    ])):'';
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
        //'footer'=>true
    ],
]); 

?>
    
</div>
<?php 
    Modal::begin([
    'id' => 'create-modal',
    'header' => '<h4 class="modal-title">系统信息</h4>',
    'size'=>'modal-lg',
    'footer' => '<a href="#" class="btn btn-primary closes" data-dismiss="modal">关闭</a>',
]);
Modal::end();

$page=Yii::$app->request->get('page') ? Yii::$app->request->get('page') : 1;

$js = <<<JS
    //ajax获取弹出框内容
    $(document).on('click', '#creates', function () {
        $.get($(this).attr('href'), {id:$(this).attr('value')},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });

    $(function(){
        $("#push").on('click', function(){
            var ids = $('#logistics_num').yiiGridView('getSelectedRows');
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
    });
    
    //手动推送关闭时 重新加载页面
    $('.closes , .close').on('click',function(){
        window.location.reload();

    });

JS;
$this->registerJs($js);

 ?>


