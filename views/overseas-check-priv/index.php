<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\User */

$this->title = Yii::t('app', '审核配置');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-create">
	<div class="user-form" style="background:white;padding:10px;">
		<?php $form = ActiveForm::begin(['method'=>'post','action'=>['index']]); ?>
		<table class="kv-grid-table table table-hover table-bordered table-condensed kv-table-wrap">
			<tr>
				<td width="150">权限</td><td width="140">金额</td><td>修改时间</td>
			</tr>
			<?php foreach ($data as $priv) : ?>
			<tr>
				<td>
					<?php echo $priv->priv_name;?>
				</td>
				<td>
					<input type="number" style="width:60px" name="prices[<?php echo $priv->id;?>]" min="0" value="<?php echo $priv->price;?>" />
				</td>
				<td>
					<?php echo $priv->update_time;?>
				</td>
			</tr>
			<?php endforeach; ?>
			<tr>
				<td colspan="3" style="padding-left:160px">
					<input type="submit" class="btn btn-success" value="保存" /> &nbsp;
					<input type="button" class="btn btn-primary" value="刷新" />
				</td>
			</tr>
		</table>
		<?php ActiveForm::end(); ?>
	</div>
</div>

<?php 
$js=<<<JS
    $(".btn-primary").click(function(){
        window.location.reload();
    })
JS;

$this->registerJs($js);
?>