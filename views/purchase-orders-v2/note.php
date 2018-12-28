<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\services\BaseServices;

$this->title='添加备注';
?>

<div class="purchase-order-form">
    <?php $form = ActiveForm::begin(); ?>
    <h4 class="modal-title">采购单备注</h4>
    <div class="row">
        <input type="hidden" name="page" value="<?=$page?>" />

        <table class="table table-bordered">
            <thead>
            <tr>
                <th>序号</th>
                <th>采购单号</th>
                <th>内容</th>
                <th>添加人</th>
                <th>添加时间</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if(is_array($models))
            {
                foreach($models as $k=>$v){
                    ?>
                    <tr>
                        <td><?=$k+1?></td>
                        <td><?=$v->pur_number?></td>
                        <td><?=$v->note?></td>
                        <td><?=BaseServices::getEveryOne($v->create_id)?></td>
                        <td><?=$v->create_time?></td>
                        <td>
                            <?php if($k>0 && $v->create_id==Yii::$app->user->id ){ ?>
                                <a href="javascript:void(0);" onclick="del(<?=$v->id?>,this)" class="profile-link" >删除</a>
                            <?php } ?>
                        </td>
                    </tr>
                <?php }?>
            <?php }?>
            </tbody>
        </table>
    </div>

    <input type="hidden"  class="form-control" name="PurchaseNote[pur_number]" value="<?=$pur_number?>">
    <input type="hidden"  class="form-control" name="flag" value="<?=$flag?>">

    <div class="col-md-12"><?= $form->field($model, 'note')->textarea(['rows'=>3,'cols'=>10,'required'=>true])->label('添加备注') ?></div>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? '提交' : '修改', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
<?php
$toUrl=Url::toRoute('delete-note');

?>
<script type="text/javascript">
    function del(id,t){
            if(confirm("确认删除")){
                $.get("<?=$toUrl?>",{id:id},function(result){
                    if(result.code==1){
                        var tr=t.parentNode.parentNode;
                        var tbody=tr.parentNode;
                        tbody.removeChild(tr);
                        alert('删除成功');
                    }else{
                        alert('删除失败');
                    }
                },'json');
            }else{
                return false;
            }
    }
</script>