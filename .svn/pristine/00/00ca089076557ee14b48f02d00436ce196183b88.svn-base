<?php
use yii\helpers\Html;
use app\config\Vhelper;
use yii\widgets\ActiveForm;
use app\models\ProductTaxRate;

$this->title = '海外仓-创建合同';
if ($platform==3) $this->title = 'FBA-创建合同';
$this->params['breadcrumbs'][] = '海外仓';
$this->params['breadcrumbs'][] = '采购单';
$this->params['breadcrumbs'][] = $this->title;
?>

<style type="text/css">
    .label-box {
        position: absolute;
        top: 34px;
        left: 0;
        width: 500px;
        border: 1px solid #3c8dbc;
        padding: 8px;
        display: none;
        z-index: 500;
        background-color: #fff;
    }
    .label-box span:hover {
        background-color: red !important;
        cursor: pointer;
    }
</style>

<?php $form = ActiveForm::begin(['id' => 'fm', 'method' => 'get']); ?>
<?php if($platform==3):?>
    <div class="my-box" style="width: 300px;">
        <label>结算比例</label>
        <div class="input-group">
            <input type="text" id="settlement_ratio" name="_ratio" class="form-control" value="<?=$settlement_ratio?>" readonly>
            <div class="input-group-btn">
                <button class="btn btn-default" type="button"><span class="glyphicon glyphicon-remove"></span></button>
                <button class="btn btn-default" type="button">定义</button>
            </div>
        </div>

        <label>供应商</label>
        <div>
            <input type="text" id="supplier_name" name="supplier_name" class="form-control" value="<?=$supplier_name?>" readonly>
        </div>

    </div>

<?php else:?>
<div class="my-box" style="width: 300px;">
    <label>结算比例</label>
    <div class="input-group">
        <input type="text" id="settlement_ratio" name="_ratio" class="form-control" value="" readonly>
        <div class="input-group-btn">
            <button class="btn btn-default settlement_ratio_clear" type="button"><span class="glyphicon glyphicon-remove"></span></button>
            <button class="btn btn-default settlement_ratio_define" type="button">定义</button>
        </div>
        <div class="label-box">
            <span class="label label-info">10%</span>
            <span class="label label-info">20%</span>
            <span class="label label-info">30%</span>
            <span class="label label-info">40%</span>
            <span class="label label-info">50%</span>
            <span class="label label-info">60%</span>
            <span class="label label-info">70%</span>
            <span class="label label-info">80%</span>
            <span class="label label-info">90%</span>
            <span class="label label-info">100%</span>
            <span class="label label-danger">关闭</span>
        </div>
    </div>
</div>

<?php endif;?>
<div class="my-box">
    <label>合同模板</label>
    <table class="my-table">
        <thead>
        <tr>
            <th>#</th>
            <th>名称</th>
        </tr>
        </thead>
        <tbody>

        <?php foreach($tpls as $tpl): ?>
            <tr>
                <td width="50px"><input type="radio" name="tid" value="<?= $tpl['id'] ?>"></td>
                <td><?= $tpl['name'] ?></td>
            </tr>
        <?php endforeach; ?>

        </tbody>
    </table>
</div>

<div class="my-box">
<button type="button" id="btn-submit" class="btn btn-primary">创建合同</button>
<a href="<?=$referrer?>" class="btn btn-success">返回</a>
</div>
<?php ActiveForm::end(); ?>

<?php
$js = <<<JS
$(function() {
    
    // 定义结算比例
    $('.settlement_ratio_define').click(function() {
        $('.label-box').toggle();
    });
    
    // 清空结算比例
    $('.settlement_ratio_clear').click(function() {
        $('#settlement_ratio').val('');
    });
    
    // 设置结算比例
    $('.label-box span').click(function() {
        var parent = $(this).parents('.input-group');
        var _ratio = $('#settlement_ratio').val();

        if($(this).text() == '关闭') {
            $(this).parent().toggle();    
            return true;
        }
        if(_ratio == '') {
             _ratio += $(this).text();
        } else {
            var _ratioes = _ratio.split('+');
            if(_ratioes.length == 3) {
                layer.tips('最多只支持3个比例', $('#settlement_ratio'), {tips: 1});
                return false;
            }
            var total = parseInt($(this).text());
            for(i = 0; i < _ratioes.length; i++) {
                total += parseInt(_ratioes[i]);
            }
            if(total > 100) {
                layer.tips('总百分比不能超过100', $('#settlement_ratio'), {tips: 1});
                return false;
            }
             _ratio += '+'+$(this).text();
        }
        $('#settlement_ratio').val(_ratio);
    });
    
    // 表单提交验证
    $('#btn-submit').click(function() {
        var settlement_ratio = $('#settlement_ratio').val();
        if(settlement_ratio == '') {
            layer.alert('结算比例不能为空');
            return false;
        } 
        var _ratioes = settlement_ratio.split('+');
        var total = 0;
        for(i = 0; i < _ratioes.length; i++) {
            total += parseInt(_ratioes[i]);
        }
        if(total < 100) {
            layer.alert('结算比例总和必须等于100%');
            return false;
        }
        $('#fm').submit();
    });

});
JS;
$this->registerJs($js);
?>
