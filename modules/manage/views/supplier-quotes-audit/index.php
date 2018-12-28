<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use \app\config\Vhelper;
use app\models\ProductProvider;
use toriphes\lazyload\LazyLoad;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use app\services\FormatService;
use mdm\admin\components\Helper;
/* @var $this yii\web\View */
/* @var $searchModel app\models\ProductSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '报价审核');
$this->params['breadcrumbs'][] = $this->title;
?>

    <div class="product-index">
        <div class="panel panel-default">
            <div class="panel-body">
                <?= $this->render('_search', ['model' => $searchModel]); ?>
            </div>
            <div class="panel-footer">
                <?php
                if(Helper::checkRoute('compare-quotes')) {
                    echo Html::a('对比报价',['compare-quotes'],['class'=>'btn btn-warning compare-quotes','data-toggle' => 'modal','data-target' => '#quotes-audit']);
                }
                ?>
                <?php
                if(Helper::checkRoute('export')) {
                    echo '<a href="#" class="btn btn-info export">导出</a>';
                }
                ?>
            </div>
        </div>
        <?= GridView::widget([
            'id'=>'quotes-index',
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'pager'=>[
                'firstPageLabel'=>"首页",
                'prevPageLabel'=>'上一页',
                'nextPageLabel'=>'下一页',
                'lastPageLabel'=>'末页',
            ],
            'columns' => [
                [
                    'class' => 'kartik\grid\CheckboxColumn',
                    'name'=>"id" ,
                    'checkboxOptions' => function ($model, $key, $index, $column) {
                        return ['value' => $model->id];
                    }

                ],
                [
                    'label'=>'产品图',
                    'format'=>'raw',
                    'value'=>function($model){
                        return LazyLoad::widget(['src'=>Vhelper::getSkuImage($model->sku),'width'=>'100px']);
                    }
                ],
                [
                    'label'=>'产品线',
                    'format'=>'raw',
                    'value'=>function($model){
                        return $model->linelist_cn_name;
            // return !empty($model->product->productLine) ? $model->product->productLine->linelist_cn_name : '';
                    }
                ],
                [
                    'label'=>'SKU',
                    'format'=>'raw',
                    'value'=>function($model){
                        return $model->sku;
                    }
                ],
                [
                    'label'=>'产品名称',
                    'format'=>'raw',
                    'value'=>function($model){
                        return !empty($model->product->desc) ? "<span title='{$model->product->desc->title}' >".Vhelper::toSubStr($model->product->desc->title,10,10).'</span>' : '';
                    }
                ],
                [
                    'label'=>'默认供应商',
                    'format'=>'raw',
                    'value'=>function($model){
                        return !empty($model->product->defaultSupplierDetail) ? $model->product->defaultSupplierDetail->supplier_name : '';
                    }
                ],
                [
                    'label'=>'报价供应商',
                    'format'=>'raw',
                    'value'=>function($model){
                        return !empty($model->manageSupplier) ? $model->manageSupplier->supplier_name : '';
                    }
                ],
                [
                    'label'=>'现单价',
                    'format'=>'raw',
                    'value'=>function($model){
                        return !empty($model->product->supplierQuote) ? $model->product->supplierQuote->supplierprice : '';
                    }
                ],
                [
                    'label'=>'供应商报价',
                    'format'=>'raw',
                    'value'=>function($model){
                        $supplierPrice='';
                        if(!empty($model->quotesItems)){
                            if($model->type==1){
                                foreach ($model->quotesItems as $item){
                                    $supplierPrice .= $item->supplier_price.'<br/>';
                                }
                            }
                            if($model->type==2){
                                foreach ($model->quotesItems as $item){
                                    $supplierPrice .= $item->amount_min.'-'.$item->amount_max.'件：单价：'.$item->supplier_price.'元<br/>';
                                }
                            }
                        }
                        return $supplierPrice;
                    }
                ],
                [
                    'label'=>'是否现货/交期',
                    'format'=>'raw',
                    'value'=>function($model){
                        $html = '';
                        $model->is_in_stock ==1 ? $html.='是否现货：<span class="label label-success">是</span><br/><br/>' : $html.='是否现货：<span class="label label-danger">否</span><br/><br/>';
                        $html.='交期：'.$model->delivery_time.'天';
                        return $html;
                    }
                ],
                [
                    'label'=>'报价时间/原因',
                    'format'=>'raw',
                    'value'=>function($model){
                        $html = '';
                        $html .= '报价时间：'.$model->create_time.'<br/>';
                        $html .= '报价原因：'."<span title={$model->reason} >".Vhelper::toSubStr($model->reason,10,10).'</span>'.'<br/>';
                        return $html;
                    }
                ],
                [
                    'label'=>'状态',
                    'format'=>'raw',
                    'value'=>function($model){
                        $statsuArray=[
                            0=>'<span class="label label-default">待审核</span>',
                            1=>'<span class="label label-info">待拿样</span>',
                            2=>'<span class="label label-primary">样品检测中</span>',
                            3=>'<span class="label label-danger">已取消</span>',
                            4=>'<span class="label label-success">完成</span>',
                            5=>'<span class="label label-danger">审核失败</span>',
                            6=>'<span class="label label-danger">样品检测失败</span>'
                        ];
                        return isset($statsuArray[$model->status]) ? $statsuArray[$model->status] : '<span class="label label-danger"> 未知错误</span>';
                    }
                ],
                [
                    'label'=>'审核时间',
                    'format'=>'raw',
                    'value'=>function($model){
                        return $model->check_time;
                    }
                ],
                [
                    'class' => 'kartik\grid\ActionColumn',
                    'dropdown' => false,
                    'width'=>'180px',
                    'template' =>  Helper::filterActionColumn('{check-result}{sample-result}{sample-commit}'),
                    'buttons'=>[
                        'check-result' => function ($url, $model, $key) {
                            if($model->status==0){
                                return Html::a('<i class="glyphicon glyphicon-pencil"></i> 审核', ['check-result','id'=>$model->id], [
                                    'title' => Yii::t('app', '审核'),
                                    'class' => 'btn btn-xs check-result',
                                    'data-toggle' => 'modal',
                                    'data-target' => '#quotes-audit',
                                ]);
                            }
                        },
                        'sample-result'=>function($url,$model,$key){
                            if($model->status==1) {
                                return Html::a('<i class="glyphicon glyphicon-pencil"></i>提交样品结果', ['sample-result', 'id' => $model->id], [
                                    'title' => Yii::t('app', '提交样品结果'),
                                    'class' => 'btn btn-xs sample-result',
                                    'data-toggle' => 'modal',
                                    'data-target' => '#quotes-audit',
                                ]);
                            }
                        },
                        'sample-commit'=>function($url,$model,$key){
                            if($model->status==2){
                                return Html::a('<i class="glyphicon glyphicon-pencil"></i>拿样确认', ['#'], [
                                    'title' => Yii::t('app', '拿样确认'),
                                    'class' => 'btn btn-xs sample-commit',
                                    'id'=>$model->id,
                                    'sku'=>$model->sku,
                                    'supplier'=>!empty($model->manageSupplier) ? $model->manageSupplier->supplier_name : '',
                                ]);
                            }
                        }
                    ],

                ],
            ],
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
                //'heading'=>'<h3 class="panel-title"><i class="glyphicon glyphicon-globe"></i> Countries</h3>',
                'type'=>'success',
                // 'after'=>Html::a('<i class="glyphicon glyphicon-repeat"></i> 刷新', ['index'], ['class' => 'btn btn-info']),
                //'footer'=>true
            ],
        ]); ?>

    </div>
<?php
Modal::begin([
    'id' => 'quotes-audit',
    'header' => '',
    //'footer' => '<a href="#" class="btn btn-primary"  data-dismiss="modal"  >Close</a>',
    'closeButton' =>false,
    'size'=>'modal-lg',
    'options'=>[
        'z-index' =>'-1',

    ],
]);
Modal::end();
$commitUrl  = Url::toRoute('sample-commit');
?>
<?php
$js = <<<JS

$(document).on('click','.check-result',function() {
    $.get($(this).attr('href'),{},function(data) {
      $('#quotes-audit').find('.modal-body').html(data);
      $('#quotes-audit').find('.modal-header').html('审核报价');
    });
});
$(document).on('click','.sample-result',function() {
    $.get($(this).attr('href'),{},function(data) {
      $('#quotes-audit').find('.modal-body').html(data);
      $('#quotes-audit').find('.modal-header').html('提交样品检验结果');
      });
});
$(document).on('click','.sample-commit',function() {
    var sku = $(this).attr('sku');
    var supplier = $(this).attr('supplier');
    var id = $(this).attr('id');
  layer.confirm('sku:  '+sku+'  确认已经在供应商:  '+supplier+'  拿样了吗？',function() {
        $.post('{$commitUrl}',{id:id});
  });
});

$(document).on('click','.export',function() {
  var ids =  $("#quotes-index").yiiGridView("getSelectedRows")
  if(ids.length ==0){
      $(this).attr('href','./export');
  }else {
      $(this).attr('href','./export?ids='+ids.join(','));
  }
});

$(document).on('click','.compare-quotes',function() {
  var ids =  $("#quotes-index").yiiGridView("getSelectedRows")
  if(ids.length ==0){
      layer.msg('请选择一条数据');
      return false;
  }else {
      $.get($(this).attr('href'),{ids:ids.join(',')},function(data) {
      $('#quotes-audit').find('.modal-body').html(data);
      $('#quotes-audit').find('.modal-header').html('对比报价');
      });
  }
});

JS;
$this->registerJs($js);
?>