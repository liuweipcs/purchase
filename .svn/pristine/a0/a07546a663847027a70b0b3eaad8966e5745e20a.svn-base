<?php

use yii\helpers\Html;
use kartik\daterange\DateRangePicker;
/* @var $this yii\web\View */
/* @var $searchModel app\models\TodayListSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->title = '采购需求统计';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="purchase-order-search">
    <div>
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
                    'startAttribute' => 'begin_time',
                    'endAttribute' => 'end_time',
                    'startInputOptions' => ['value' => date('Y-m-d',strtotime("last month"))],
                    'endInputOptions' => ['value' => date('Y-m-d',time())],
                    'pluginOptions'=>[
                        'locale'=>['format' => 'Y-m-d'],
                    ]
                ]).$addon ;
            ?>
        </label>
    </div>
    <div>
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary search']) ?>
    </div>
</div>
<div id="chartmain" style="width:1500px; height: 700px;"></div>
<div id="chartbranch"style="height: 700px;width: 1500px"></div>
<?php $this->registerJsFile('/js/echarts.js');?>
<?php

$url = \yii\helpers\Url::toRoute(['get-data']);
$demandurl = \yii\helpers\Url::toRoute(['get-demand']);
$orderurl = \yii\helpers\Url::toRoute(['get-order']);
$js = <<<JS
    var start = $('[name="begin_time"]').val();
    var end = $('[name="end_time"]').val();
    function purchase(start,end){
      var dateData=[],demandData=[];orderData=[];arr4=[];arr5=[];
        $.ajax({
          type:"post",
          async:false,
          url:'{$url}',
          data:{begin_time:start,end_time:end},
          dataType:"json",
          success:function(result){
            if (result) {
                 dateData= result.date;
                 demandData= result.demand;
                 orderData= result.order;
            }
          }
        });
        var option = {
            title: {
                text: '采购需求统计'
            },
            tooltip: {
            trigger: 'axis',
            axisPointer: {
                type: 'cross',
                label: {
                    backgroundColor: '#6a7985'
                }
            }
            },
            legend: {
                data:['订单数量','采购需求数量']
            },
            toolbox: {
                feature: {
                    saveAsImage: {}
                }
            },
            grid: {
                left: '3%',
                right: '4%',
                bottom: '3%',
                containLabel: true
            },
            xAxis : [
                {
                    type : 'category',

                    data : dateData
                }
            ],
            yAxis : [
                {
                    type : 'value'
                }
            ],
            series : [
                {
                    name:'订单数量',
                    type:'line',    
                    showAllSymbol:true,
                    label: {
                        normal: {
                            show: true,
                            position: 'insideBottomLeft',
                            color:'blue',
                        }
                    },   
                    smooth: true  ,    
                    data:orderData
                },
                {
                    name:'采购需求数量',
                    showAllSymbol:true,         
                    type:'line',
                    label: {
                        normal: {
                            show: true,
                            position: 'insideBottomRight',
                            color:'black'
                        }
                    },
                    smooth: true,
                    data:demandData
                }
              
            ]
        };
        //初始化echarts实例
        var myChart = echarts.init(document.getElementById('chartmain'));
        //使用制定的配置项和数据显示图表
        myChart.setOption(option);
        
        myChart.on('dblclick', function (handler){ 
            var type = handler.seriesName;
            if(type=='订单数量'){
                chartAjax(handler.name,'{$orderurl}',handler.name+'当日订单统计','当日订单数量')
            }else if(type=='采购需求数量'){
                chartAjax(handler.name,'{$demandurl}',handler.name+'当日需求统计','当日需求数量');
            }else{
                layer.msg('未知类别');
            }
           // chartbranch();
            
        }); 
      }
      
      function chartAjax(time,url,text,title) {
        $.ajax({
          type:"post",
          async:false,
          url:url,
          data:{time:time},
          dataType:"json",
          success:function(result){
            if (result) {
                 chartbranch(result.Xdata,result.Ydata,text,title);
            }
          }
        });
      }
      
      function chartbranch(Xdata,Ydata,text,title) {
        var branchOption = {
            title: {
                text: text
            },
            tooltip: {
            trigger: 'axis',
            axisPointer: {
                type: 'cross',
                label: {
                    backgroundColor: '#6a7985'
                }
            }
            },
            legend: {
                data:[title]
            },
            toolbox: {
                feature: {
                    saveAsImage: {}
                }
            },
            grid: {
                left: '3%',
                right: '4%',
                bottom: '3%',
                containLabel: true
            },
            xAxis : [
                {
                    type : 'category',
                    data : Xdata
                }
            ],
            yAxis : [
                {
                    type : 'value'
                }
            ],
            dataZoom: [
            {
            id: 'dataZoomX',
            type: 'inside',
            xAxisIndex: [0],
            filterMode: 'filter'
            }],
            series : [
                {
                    name:title,
                    type:'line',    
                    showAllSymbol:true, 
                    label: {
                        normal: {
                            show: true,
                            position: 'insideBottomLeft',
                            color:'blue',
                        }
                    },   
                    smooth: true,      
                    data:Ydata
                }, 
            ]
        };
        var branchChart = echarts.init(document.getElementById('chartbranch'));
        branchChart.setOption(branchOption);
      }
      purchase(start,end);
      $('.search').on('click',function(){
        start = $('[name="begin_time"]').val();
        end = $('[name="end_time"]').val();
        echarts.dispose(document.getElementById('chartmain'));
        purchase(start,end);
      });
       
      

JS;
$this->registerJs($js);
?>

