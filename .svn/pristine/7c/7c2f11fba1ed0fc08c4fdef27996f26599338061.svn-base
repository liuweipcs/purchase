<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\services\BaseServices;
use app\models\ProductSupplierChangeSearch;
?>
<style type="text/css">
    .modal-lg{width: 800px; !important;}
</style>
<div class="purchase-order-form">
    <table style="width:100%">
    	<tr>
    		<td width="25%">操作时间</td>
    		<td width="12%">操作人</td>
    		<td>变更内容</td>
    		<td style="padding-left:15px">详细</td>
    	</tr>
    	<?php foreach ($list as $v) : ?>
    	<tr>
    		<td><?php echo $v['operate_time']?></td>
    		<td><?php echo $v['operator']?></td>
    		<td><?php echo str_replace("\r\n",'<br>',$v['content']);?></td>
    		<td style="padding-left:15px">
    		<?php
    		  if ($v['update_data']) {
                  $value_update_date = $v['update_data'];
                  $value_update_date = (new ProductSupplierChangeSearch())->changeShowName($value_update_date);
                  $update_date = json_decode($value_update_date, true);
    		      if($update_date AND is_array($update_date)){
                      foreach ($update_date as $key => $v2) {
                          if(is_string($v2)){
                              echo $key.': '.$v2.'<br>';
                          }else{
                              echo serialize($v2).'<br>';
                          }
                      }

                  }else{
                      $update_date = explode(',',$value_update_date);
                      if($update_date AND is_array($update_date)){
                          foreach ($update_date as $v2) {
                              if(is_string($v2)){
                                  echo $v2.'<br>';
                              }else{
                                  echo serialize($v2).'<br>';
                              }
                          }
                      }else{
                          echo $value_update_date;
                      }
                  }

    		  }
    		?>
    		</td>
    	</tr>
    	<?php endforeach; ?>
    </table>
</div>