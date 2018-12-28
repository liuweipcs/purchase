<?php
use yii\helpers\Html;
use kartik\grid\GridView;
use Yii\helpers\Url;
use yii\bootstrap\Modal;
use app\models\User;

$this->title = Yii::t('app', '供货商KPI');
$this->params['breadcrumbs'][] = $this->title;
?>
<style type="text/css">
    .border-class tr td{border:1px solid #AFAFAF; padding:6px}
</style>
<div class="stockin-index">
    <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    <p class="clearfix"></p>
    <?= GridView::widget([
    'dataProvider' => $dataProvider,
    //'filterModel' => $searchModel,
    "options" => ["class" => "grid-view","style"=>"overflow:auto", "id" => "grid"],
    'pager'=>[
        'firstPageLabel'=>"首页",
        'prevPageLabel'=>'上一页',
        'nextPageLabel'=>'下一页',
        'lastPageLabel'=>'末页',
    ],
    'columns' => [
        ['class' => 'kartik\grid\SerialColumn'],
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
            'attribute' => 'supplier_codes',
            'value'=> function($model){
                    $html="代码：$model->supplier_code <br/>";
                    $html.="名称：$model->supplier_name <br/>";
                    $html.="采购员：".User::findOne(['id' => $model->buyer])->username."<br/>";
                    return  $html;
            },
        ],
        [
            'label'=>'采购信息',
            'format'=>'raw',
            'attribute' => 'supplier_name',
            'value'=>function($model){
/*                $html="采购单数(个): 7 <br/>";
                $html.="交期调整数按单(次): 0 <br/>";
                $html.="到货不符次数按单(次): 1 <br/>";
                $html.="交付准时率按单(%): 85.71 <br/>";
                $html.="一次合格率按单(%): 85.71 <br/>";*/

                $html="<table class='border-class'>
                            <tr>
                                <td>采购单数</td>
                                <td>交期调整数按单</td>
                                <td>到货不符次数按单</td>
                                <td>交付准时率按单</td>
                                <td>一次合格率按单</td>
                            </tr>
                            <tr>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                            </tr>
                       </table>"
                ;

                return  $html;
            },
        ],
        [
            'label'=>'产品质量',
            'format'=>'raw',
            'attribute' => 'supplier_name',
            'value'=>function($model){

                $html="<table class='border-class'>
                            <tr>
                                <td>来货一次性合格率</td>
                                <td>不良品率</td>
                                <td>批量不良率</td>
                                <td>海外不良率</td>
                                <td>包装问题</td>
                            </tr>
                            <tr>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                            </tr>
                       </table>";

                    return  $html;
            },
        ],
        [
            'label'=>'降本',
            'format'=>'raw',
            'attribute' => 'buyers',
            'value'=>function($model){
                $html="<table class='border-class'>
                            <tr>
                                <td>成本</td>
                                <td>运费</td>
                            </tr>
                            <tr>
                                <td>0</td>
                                <td>0</td>
                            </tr>
                       </table>";

                return  $html;
            },
        ],
        [
            'label'=>'产品交期',
            'format'=>'raw',
            'attribute' => 'buyers',
            'value'=>function($model){
                $html="<table class='border-class'>
                            <tr>
                                <td>准时交货率</td>
                                <td>异常准时交货次数</td>
                            </tr>
                            <tr>
                                <td>0</td>
                                <td>0</td>
                            </tr>
                       </table>";

                return  $html;
            },
        ],
        [
            'label'=>'结算方式',
            'attribute' => 'buyers',
            'value'=>function($model){
                return  '货到付款';
            },
        ],
        [
            'label'=>'服务',
            'format'=>'raw',
            'attribute' => 'supplier_name',
            'value'=>function($model){
                $html="<table class='border-class'>
                            <tr>
                                <td>发货次数</td>
                                <td>来货异常率</td>
                                <td>不良品退换货时间</td>
                                <td>信息快速沟通反馈</td>
                                <td>提供开票</td>
                            </tr>
                            <tr>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                            </tr>
                       </table>";

                return  $html;
            },
        ],
        [
            'label'=>'我方配合',
            'format'=>'raw',
            'attribute' => 'supplier_name',
            'value'=>function($model){
                $html="<table class='border-class'>
                            <tr>
                                <td>准时付款率</td>
                                <td>及时下单率</td>
                                <td>异常反馈速度</td>
                                <td>供应商新品开发力度</td>
                            </tr>
                            <tr>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                            </tr>
                       </table>";

                return  $html;
            },
        ],

/*        [
            'class' => 'kartik\grid\ActionColumn',
            'dropdown' => false,
            'width'=>'180px',
            'template' => '{edit} {update}',
            'buttons'=>[
                'edit' => function ($url, $model, $key) {
                return Html::a('<i class="glyphicon glyphicon-eye-open"></i> 查看', ['view','id'=>$key], [
                    'title' => Yii::t('app', '查看'),
                    'class' => 'btn btn-xs red'
                ]);
                },
                'update' => function ($url, $model, $key) {
                    return Html::a('<i class="glyphicon glyphicon-pencil"></i> 更新', ['update','id'=>$key], [
                        'title' => Yii::t('app', '更新 '),
                        'class' => 'btn btn-xs purple'
                    ]);
                },],

        ],*/
    ],
    'containerOptions' => ["style"=>"overflow:auto"], // only set when $responsive = false
    'toolbar' =>  [
        '{export}',
        //'{toggleData}'
    ],

    'pjax' => true,
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
]);?>
</div>
<?php
Modal::begin([
    'id' => 'create-modal',
    'header' => '<h4 class="modal-title">系统信息</h4>',
    'size'=>'modal-lg',
    'footer' => '<a href="#" class="btn btn-primary" data-dismiss="modal">关闭</a>',
]);
Modal::end();
$requestUrl = Url::toRoute('all-edit-supplier-attr');
$msg ='请选择需要修改的供应商';
$js = <<<JS

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
                     $.get('{$requestUrl}', {id: str},
                                function (data) {
                                    $('.modal-body').html(data);
                                }
                     );

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

JS;
$this->registerJs($js);
?>
