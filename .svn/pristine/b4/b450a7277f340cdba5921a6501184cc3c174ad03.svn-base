<?php

use yii\helpers\Html;

use kartik\grid\GridView;
use Yii\helpers\Url;
use yii\bootstrap\Modal;
/* @var $this yii\web\View */
/* @var $searchModel app\models\SupplierGoodsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '账期管理');
$this->params['breadcrumbs'][] = $this->title;
?>

<div >
    <?= $this->render('_search', ['model' => $searchModel]); ?>
    <p class="clearfix"></p>
    <?= Html::button(Yii::t('app', '编辑账期'), [
        'id' => 'log-update',
        'class' => 'btn btn-success',
        'data-toggle' => 'modal',
        'data-target' => '#create-modal'
    ]);?>
    <div >
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            //'filterModel' => $searchModel,
            "options" => ["class" => "grid-view","style"=>"overflow:auto", "id" => "grid"],
            'filterSelector' => "select[name='".$dataProvider->getPagination()->pageSizeParam."'],input[name='".$dataProvider->getPagination()->pageParam."']",
            'columns' => [
                [
                    'class' => 'kartik\grid\CheckboxColumn',
                    'name'=>"id" ,
                    'checkboxOptions' => function ($model, $key, $index, $column) {
                        return ['value' => $model->id];
                    }

                ],
                [
                    'label'=>'供应商',
                    'format'=>'raw',
                    'value'=>function($model){
                        return $model->supplier ? $model->supplier->supplier_name : '';
                    }
                ],
                [
                    'label'=>'原账期',
                    'format'=>'raw',
                    'value'=>function($model){
                        return \app\services\SupplierServices::getSettlementMethod($model->old_settlement);
                    }
                ],
                [
                    'label'=>'现账期',
                    'format'=>'raw',
                    'value'=>function($model){
                        return \app\services\SupplierServices::getSettlementMethod($model->new_settlement);
                    }
                ],
                [
                    'label'=>'结款时间',
                    'format'=>'raw',
                    'value'=>function($model){
                        return $model->pay_time;
                    }
                ],
                [
                    'label'=>'是否提交资料',
                    'format'=>'raw',
                    'value'=>function($model){
                        return $model->means_upload ===0 ? '否' : '是';
                    }
                ],[
                    'label'=>'是否执行',
                    'format'=>'raw',
                    'value'=>function($model){
                        return $model->is_exec ===0 ? '否' : '是';
                    }
                ],[
                    'label'=>'提交人',
                    'format'=>'raw',
                    'value'=>function($model){
                        return $model->create_user_name;
                    }
                ],[
                    'label'=>'提交时间',
                    'format'=>'raw',
                    'value'=>function($model){
                        return $model->create_time;
                    }
                ],
                [
                    'label'=>'更新人',
                    'format'=>'raw',
                    'value'=>function($model){
                        return $model->update_user_name;
                    }
                ],[
                    'label'=>'更新时间',
                    'format'=>'raw',
                    'value'=>function($model){
                        return $model->update_time;
                    }
                ],
            ],
            'containerOptions' => ["style"=>"overflow:auto"], // only set when $responsive = false
            'toolbar' =>  [

                //'{export}',
            ],


            'pjax' => false,
            'bordered' => true,
            'striped' => false,
            'condensed' => true,
            'responsive' => false,
            'hover' => false,
            'floatHeader' => false,
            'showPageSummary' => false,

            'exportConfig' => [
                GridView::EXCEL => [],
            ],
            'panel' => [
                //'heading'=>'<h3 class="panel-title"><i class="glyphicon glyphicon-globe"></i> Countries</h3>',
                'type'=>'success',
                //'before'=>false,
                //'after'=>false,
                //'after'=>Html::a('<i class="glyphicon glyphicon-repeat"></i> 刷新', ['index'], ['class' => 'btn btn-info']),
                //'footer'=>true
            ],
        ]);?>
    </div>
</div>

<?php
Modal::begin([
    'id' => 'create-modal',
    'header' => '<h4 class="modal-title">系统信息</h4>',
    'footer' => '<a href="#" class="btn btn-primary closes" data-dismiss="modal">Close</a>',
    'size'=>'modal-lg',
    'options'=>[
        //'data-backdrop'=>'static',//点击空白处不关闭弹窗

    ],
]);
Modal::end();

$requestUrl = Url::toRoute('update-log');
$js = <<<JS
$(document).on('click', '#log-update', function () {
        var  ids= new Array();
        $("[name='id[]']").each(function() {
          if($(this).is(':checked')){
              ids.push($(this).val());
          }
        });
        if(ids.length ==0){
            $('.modal-body').html('至少选择一个');
        }else {
            $.get("{$requestUrl}", {ids:ids.join(',')},
                function (data) {
            $('.modal-body').html(data);
            }
        );
        }
        
        
    });

JS;
$this->registerJs($js);
?>
