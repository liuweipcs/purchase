<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\services\BaseServices;
?>

<style>
td{border-bottom:1px solid #e3e3e3; line-height:22px; widht:100%;padding:2px;}
</style>

<div class="purchase-order-form">
    <table style="width:100%">
    	<tr>
    		<td width="150">操作时间</td>
    		<td width="80">操作人</td>
    		<td width="300">描述</td>
    		<td width="300" style="padding-left:15px">变更内容</td>
    	</tr>
    	<?php foreach ($list as $v) : ?>
    	<tr>
    		<td><?php echo $v['operate_time']?></td>
    		<td><?php echo $v['operator']?></td>
    		<td><?php echo str_replace("\r\n",'<br>',$v['message']);?></td>
    		<td style="padding-left:15px">
    		<?php 
    		  if ($v['update_data']) {
    		      $update_date = json_decode($v['update_data'], true);
    		      foreach ($update_date as $v2) {
                      echo $v2[1].': '.$v2[2].' => '.$v2[3].'<br>';	          
    		      }
    		  }
    		?>
    		</td>
    	</tr>
    	<?php endforeach; ?>
    </table>
</div>