<?php

use yii\helpers\Html;
use kartik\daterange\DateRangePicker;
/* @var $this yii\web\View */
/* @var $searchModel app\models\TodayListSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '销售需求统计';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="purchase-order-search">
    <div>
        <label><input name="type" type="radio" value="sales" checked="checked" />销售人员维度 </label>
        <label><input name="type" type="radio" value="group" />销售分组维度</label>
        <label><input name="styl" type="radio" value="line" checked="checked" />折线图 </label>
        <label><input name="styl" type="radio" value="bar" />柱状图</label>
        <label>
        <?php
        $addon = <<< HTML
<span class="input-group-addon">
    <i class="glyphicon glyphicon-calendar"></i>
</span>
HTML;
        echo '<div class="input-group drp-container">';
        echo DateRangePicker::widget([
                'name'=>'PlatformSummarySearch[create_time]',
                'useWithAddon'=>true,
                'convertFormat'=>true,
                'startAttribute' => 'PlatformSummarySearch[start_time]',
                'endAttribute' => 'PlatformSummarySearch[end_time]',
                'startInputOptions' => ['value' => date('Y-m-d H:i:s',strtotime("last month"))],
                'endInputOptions' => ['value' => date('Y-m-d H:i:s',time())],
                'pluginOptions'=>[
                    'locale'=>['format' => 'Y-m-d H:i:s'],
                ]
            ]).$addon ;
        ?>
        </label>
    </div>
    <div>
        <?= Html::button('全选',['class' =>'btn btn-info checkAll',])?>
        <?= Html::button('全不选',['class' =>'btn btn-info checkNo',])?>
        <?= Html::button('反选',['class' =>'btn btn-info checkInvert',])?>
    </div>

    <div>
        <?php foreach (\app\services\BaseServices::getAmazonGroup() as $key=>$value){?>
            <?php if($key==1){?>
                <label><input name="group" type="checkbox" value=<?=$key?> checked="checked" /><?=$value?></label>
            <?php }else{?>
                <label><input name="group" type="checkbox" value=<?=$key?> /><?=$value?></label>
            <?php }?>
        <?php } ?>
    </div>

    <div>
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary search']) ?>
    </div>
</div>
<div id="chartmain" style="width:1500px; height: 700px;"></div>
<?php $this->registerJsFile('/js/echarts.js');?>
<?php

$url = \yii\helpers\Url::toRoute(['get-data']);
$js = <<<JS
    var start = $('[name="PlatformSummarySearch[start_time]"]').val();
    var end = $('[name="PlatformSummarySearch[end_time]"]').val();
    var style = $('[name="styl"]').val();
  function arrSales(type,group,start,end,style){
      console.log(style);
      var arr1=[],arr2=[];arr3=[];arr4=[];arr5=[];
        $.ajax({
          type:"post",
          async:false,
          url:'{$url}',
          data:{type:type,group:group,start:start,end:end},
          dataType:"json",
          success:function(result){
            if (result) {
              for (var i = 0; i < result.length; i++) {
                if(type=='sales'){
                  arr1.push(result[i].sales);
                  }else {
                  arr1.push(result[i].group_id);
                  }
                  arr2.push(result[i].total);
                  arr3.push(result[i].left_arrive);
                  arr4.push(result[i].pur_num);
                  arr5.push(result[i].left_num);
              }
            }
          }
        });
        var option = {
            title:{
            text:'销售需求统计'
            },
            tooltip:{},
            xAxis:{
            scale:true,
            data:arr1,
            },
            yAxis:{
            name :'采购需求金额'
            },
            dataZoom: [
            {
            id: 'dataZoomX',
            type: 'inside',
            xAxisIndex: [0],
            filterMode: 'filter'
            }],
            legend: {
            data:['采购需求金额', '需求数量', '在途数量','在途金额']
            },
            series:[{
            name:'采购需求金额',
            type:style,
            stack:'金额',
            label:[{
            show:true
            }],
            data:arr2,
            },{
            name:'需求数量',
            type:style,
            stack:'数量',
            label:[{
            show:true
            }],
            data:arr4,
            },{
            name:'在途数量',
            type:style,
            stack:style=='bar'?'数量':null,
            data:arr5,
            }
            ,{
            name:'在途金额',
            type:style,
            stack:style=='bar'?'金额':null,
            data:arr3,
            }]
        };
        //初始化echarts实例
        var myChart = echarts.init(document.getElementById('chartmain'));
        //使用制定的配置项和数据显示图表
        myChart.setOption(option);
      }
      arrSales('sales',1,start,end,'line');
      $('.search').on('click',function(){
        start = $('[name="PlatformSummarySearch[start_time]"]').val();
        end = $('[name="PlatformSummarySearch[end_time]"]').val();
        var type='';
        $('[name="type"').each(function(){
          if($(this).is(':checked')){
            type  = $(this).val()
          }
        });
        $('[name="styl"]').each(function(){
            if($(this).is(':checked')){
                style = $(this).val()
            }
        });
        var group = '';
        $('[name="group"').each(function(){
            if($(this).is(':checked')){
                group+=(','+$(this).val());
            }
        });
        group = group.substr(1);
        echarts.dispose(document.getElementById('chartmain'));
        arrSales(type,group,start,end,style);
      });

      $('.checkAll').on('click',function(){
            $('[name="group"]').each(function(){
                $(this).prop('checked',true);
            });
      });
      $('.checkNo').on('click',function(){
            $('[name="group"]').each(function(){
                $(this).prop('checked',false);
            });
      });

      $('.checkInvert').on('click',function(){
            $('[name="group"]').each(function(){
                if($(this).is(':checked')){
                    $(this).prop('checked',false);
                }else{
                    $(this).prop('checked',true);
                }
            });
      });


JS;
$this->registerJs($js);
?>

