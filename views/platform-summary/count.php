<?php
use kartik\grid\GridView;
use app\config\Vhelper;
/* @var $this yii\web\View */
/* @var $searchModel app\models\TodayListSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '采购KPI考核';
$this->params['breadcrumbs'][] = $this->title;
?>
<style>
    .pps{
        font-size: 16px;
        padding: 0 0;
    }
    .pur_order_count tr td{
    border:1px solid grey;height:25px;
    }
</style>

<div class="purchase-order-index">
    <?= $this->render('count-search', ['model' => $searchModel]); ?>
    <p class="clearfix"></p>
    <div>
    <table class="pur_order_count" style="width:85%"><?php// Vhelper::dump($data);?>
    <tr>
    <td>采购员</td>
    <td>需求产品数</td>
    <td>实际订货产品数</td>
    <td>订单个数</td>
    <td>驳货产品数</td>
    <td>未订货产品数</td>
    <td>下单率</td>
    <td>交期达成率</td>
    <td>异常总数</td>
    <td>异常率</td>
    <td>采购金额</td>
    
    </tr>
    <?php foreach($data as$key=> $val){
//            Vhelper::dump($data);
if(!is_array($val['total_price'])){
	$val['total_price']=array(0);
}
    	?>
    <tr>
    <td><?php echo $key ?></td>
    <td><?php echo $val['demand']?></td>
    <td><?php echo $val['final']?></td>
    <td><?php echo count($val['order'])?></td>
    <td><?php echo $val['wait']?></td>
    <td><?php echo $val['turned']?></td>
    <td><?php echo (round(($val['demand']-$val['wait'])/$val['demand'],4)*100).'%'?></td>
    <td><?php echo (round(count(isset($val['reached'])?$val['reached']:[])/count($val['order']),4)*100).'%'?></td>
    <td><?php echo count(isset($val['normal'])?$val['normal']:[])?></td>
    <td><?php echo (round(count(isset($val['normal'])?$val['normal']:[])/count($val['order']),4)*100).'%'?></td>
    <td><?php echo array_sum(array_filter($val['total_price']))?></td>
    </tr>
    <?php }?>
    </table>
    </div>
    <?php  //GridView::widget([
//         'dataProvider' => $dataProvider,
//         'options'=>[
//             'id'=>'sale-list',
//         ],
//         'filterSelector' => "select[name='".$dataProvider->getPagination()->pageSizeParam."'],input[name='".$dataProvider->getPagination()->pageParam."']",
//         'pager'=>[
//             'options'=>['class' => 'pagination','style'=> "display:block;"],
//             'class'=>\liyunfang\pager\LinkPager::className(),
//             'pageSizeList' => [20, 50, 100, 200],
//             'firstPageLabel'=>"首页",
//             'prevPageLabel'=>'上一页',
//             'nextPageLabel'=>'下一页',
//             'lastPageLabel'=>'末页',
//         ],
//         'rowOptions'=>function($model){
//           if($model['sales'] == '{{总计}}'){
//               return ['style'=>'background-color:#66FFFF;'];
//           }
//         },
//         'columns' => [
//             [
//                 'label'=>'分组',
//                 'value'=>function($model){
//                     return $model['group_id'];
//                 },
//                 'group'=>true,
//                 'vAlign'=>'button',
//             ],
//             [
//                 'label'=>'销售',
//                 'value'=>function($model){
//                     return $model['sales'];
//                 }
//             ],
//             [
//                 'label'=>'采购金额',
//                 'value'=>function($model){
//                     return $model['total'];
//                 },
//                 'pageSummary' => true
//             ],
//             [
//                 'label'=>'采购数量',
//                 'value'=>function($model){
//                     return $model['pur_num'];
//                 },
//                 'pageSummary' => true
//             ],
//             [
//                 'label'=>'在途金额',
//                 'value'=>function($model){
//                     return $model['left_arrive'];
//                 },
//                 'pageSummary' => true
//             ],
//             [
//                 'label'=>'在途数量',
//                 'value'=>function($model){
//                     return $model['left_num'];
//                 },
//                 'pageSummary' => true
//             ],
//             [
//                 'label'=>'库存金额',
//                 'value'=>function($model){
//                     return '暂无';
//                 }
//             ],
//             [
//                 'label'=>'库存数量',
//                 'value'=>function($model){
//                     return '暂无';
//                 }
//             ],

//         ],
//         'containerOptions' => ["style"=>"overflow:auto"], // only set when $responsive = false
//         'toolbar' =>  [],


//         'pjax' => false,
//         'bordered' => true,
//         'striped' => false,
//         'condensed' => true,
//         'responsive' => true,
//         'hover' => true,
//         'floatHeader' => false,
//         'showPageSummary' => true,

//         'exportConfig' => [
//             GridView::EXCEL => [],
//         ],
//         'panel' => [
//             //'heading'=>'<h3 class="panel-title"><i class="glyphicon glyphicon-globe"></i> Countries</h3>',
//             'type'=>'success',
//             //'after'=>Html::a('<i class="glyphicon glyphicon-repeat"></i> 刷新', ['index'], ['class' => 'btn btn-info']),
//         ],
//     ]); ?>
</div>
