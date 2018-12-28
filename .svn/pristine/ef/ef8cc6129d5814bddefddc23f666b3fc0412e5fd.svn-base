<?php
use yii\helpers\Html;
use app\controllers\StockDetailController;

?>
<style  type="text/css">
    div.daterangepicker.dropdown-menu.single.opensright.show-calendar{
        -moz-top: 73px; left: 495px; right: auto; display: block;width:320px;
    }
</style>
<div class="logistics-carrier-index">

    <?php  echo $this->render('_search', ['model'=>$searchModel,'application_time'=>$application_time]); ?>
    <div class="clearfix"></div>

    <input type="hidden" id="application_time" value="<?=$application_time?>">
    <div role="tabpanel" class="tab-pane active" id="compact">
        <div class="panel panel-success">
            <div class="panel-heading">
                <div class="clearfix"></div>
            </div>
            <div class="panel-body" style="padding: 15px;">
                <table class="table table-bordered" style="border-collapse: collapse;width: 100%;max-width: 100%;margin-bottom: 20px">
                    <thead>
                    <th style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: top;border-top: 1px solid #ddd;text-align: center">sku</th>
                    <th style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: top;border-top: 1px solid #ddd;text-align: center">商品名称</th>
                    <th style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: top;border-top: 1px solid #ddd;text-align: center">上架数量</th>
                    <th style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: top;border-top: 1px solid #ddd;text-align: center">产品线</th>
                    <th style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: top;border-top: 1px solid #ddd;text-align: center">运输方式</th>
                    <th style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: top;border-top: 1px solid #ddd;text-align: center">入库批次号</th>
                    <th style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: top;border-top: 1px solid #ddd;text-align: center">入库单号</th>
                    </thead>
                    <tbody style="text-align: center">
                        <?php foreach($data as $k => $v): ?>
                            <tr>
                                <td style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: top;border-top: 1px solid #ddd;"><?= isset($v['sku'])?$v['sku']:'' ?></td>
                                <td style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: top;border-top: 1px solid #ddd;"><?= isset($v['name'])?$v['name']:'' ?></td>
                                <td style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: top;border-top: 1px solid #ddd;"><?= isset($v['onsale_qty'])?$v['onsale_qty']:'' ?></td>
                                <td style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: top;border-top: 1px solid #ddd;"><?= StockDetailController::actionGetCat($v['product_linelist_id']) ?></td>
                                <td style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: top;border-top: 1px solid #ddd;"><?= isset($v['logitic_mode'])?$v['logitic_mode']:'' ?></td>
                                <td style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: top;border-top: 1px solid #ddd;"><?= isset($v['ship_number'])?$v['ship_number']:'' ?></td>
                                <td style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: top;border-top: 1px solid #ddd;"><?= isset($v['tracking_number'])?$v['tracking_number']:'' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="panel-footer">
                <?= \yii\widgets\LinkPager::widget([
                    'pagination' => $pager,
                    'firstPageLabel' => "首页",
                    'prevPageLabel' => '上一页',
                    'nextPageLabel' => '下一页',
                    'lastPageLabel' => '末页',
                    'options' => ['class' => 'pagination no-margin']
                ]);
                ?>
            </div>

        </div>
    </div>
</div>
<?php
$js = <<<JS
$(function(){
     //批量导出
     $('#export-csv').click(function() {
          //搜索条件
          var daterangepicker_start = $("#w1-start").val();
          var daterangepicker_end = $("#w1-end").val();
   
          var warehouse_code = '';
          $("#warehouse").find(".select2-selection__choice").each(function(){
              warehouse_code += $(this).attr('title')+",";
          })
          
          var warehouse_category = '';
          $("#warehouse_category").find(".select2-selection__choice").each(function(){
              warehouse_category += $(this).attr('title')+",";
          })
      
          window.location.href='/stock-detail/export-csv?warehouse_code='+warehouse_code+'&daterangepicker_start='+daterangepicker_start+'&warehouse_category='+warehouse_category+'&daterangepicker_end='+daterangepicker_end;
     });
     
     
     /*$("#w1").val('');
     var application_time = $("#application_time").val();
     if(application_time!=''){
         $("#w1").val(application_time);
     }*/
});
JS;
$this->registerJs($js);
?>