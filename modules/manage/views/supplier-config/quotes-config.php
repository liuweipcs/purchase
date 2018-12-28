<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\web\JsExpression;
/* @var $this yii\web\View */
/* @var $searchModel app\models\SupplierGoodsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '配置报价');
$this->params['breadcrumbs'][] = $this->title;
$url = \yii\helpers\Url::to(['/supplier/search-supplier']);
?>
<?php $form = ActiveForm::begin(
    [
        'enableClientValidation'=>false,
        'fieldConfig' => [
            'template' => "<div class='col-md-2 text-right'>{label}</div><div class='col-md-9'>{input}</div><div class='col-md-1'>{error}</div>",
        ]
    ]
); ?>
<style>
    .tree {
        min-height:20px;
        padding:19px;
        margin-bottom:20px;
        background-color:#fbfbfb;
        border:1px solid #999;
        -webkit-border-radius:4px;
        -moz-border-radius:4px;
        border-radius:4px;
        -webkit-box-shadow:inset 0 1px 1px rgba(0, 0, 0, 0.05);
        -moz-box-shadow:inset 0 1px 1px rgba(0, 0, 0, 0.05);
        box-shadow:inset 0 1px 1px rgba(0, 0, 0, 0.05)
    }
    .tree li {
        list-style-type:none;
        margin:0;
        padding:10px 5px 0 5px;
        position:relative
    }
    .tree li::before, .tree li::after {
        content:'';
        left:-20px;
        position:absolute;
        right:auto
    }
    .tree li::before {
        border-left:1px solid #999;
        bottom:50px;
        height:100%;
        top:0;
        width:1px
    }
    .tree li::after {
        border-top:1px solid #999;
        height:20px;
        top:25px;
        width:25px
    }
    .tree li span {
        -moz-border-radius:5px;
        -webkit-border-radius:5px;
        border:1px solid #999;
        border-radius:5px;
        display:inline-block;
        padding:3px 8px;
        text-decoration:none
    }
    .tree li.parent_li>label {
        cursor:pointer
    }
    .tree>ul>li::before, .tree>ul>li::after {
        border:0
    }
    .tree li:last-child::before {
        height:30px
    }
    .tree li.parent_li>label:hover, .tree li.parent_li>span:hover+ul li label {
        background:#eee;
        border:1px solid #94a0b4;
        color:#000
    }
</style>
<div class="col-md-12">
    <label>按产品线配置</label>
    <div class="tree well">
        <ul>
            <?php  foreach ($productLineTreeDatas as $item){?>
                <li >
                    <label><i class="<?=!empty($item['items']) ? 'glyphicon glyphicon-plus': 'glyphicon glyphicon-minus' ?>"></i></label>
                    <input type="checkbox" level="1" value='<?= $item['product_line_id']?>' name="SupplierManageConfig[product_line_limit][]" <?=in_array($item['product_line_id'],$haveProductLine) ? 'checked="checked"':''?>> <?= $item['linelist_cn_name']?>
                    <?php if(!empty($item['items'])){?>
                        <ul>
                            <?php foreach ($item['items'] as $value){?>
                                <li style="display: none">
                                    <label><i class="<?=!empty($value['items']) ? 'glyphicon glyphicon-plus': 'glyphicon glyphicon-minus' ?>"></i></label>
                                    <input type="checkbox" level="2" value="<?= $value['product_line_id']?>" name="SupplierManageConfig[product_line_limit][]" <?=in_array($value['product_line_id'],$haveProductLine) ? 'checked="checked"':''?>>  <?= $value['linelist_cn_name']?>
                                    <ul>
                                        <?php if(!empty($value['items'])){?>
                                            <?php foreach ($value['items'] as $v){?>
                                                <li style="display: none"><input level="3" type="checkbox" value="<?=$v['product_line_id']?>" name="SupplierManageConfig[product_line_limit][]" <?=in_array($v['product_line_id'],$haveProductLine) ? 'checked="checked"':''?>><?=$v['linelist_cn_name']?></li>
                                            <?php }?>
                                        <?php }?>
                                    </ul>
                                </li>
                            <?php } ?>
                        </ul>
                    <?php }?>
                </li>
            <?php }?>
        </ul>
    </div>
</div>
<div class="col-md-12">
    <?= $form->field($model, 'supplier_code_limit')->widget(Select2::classname(), [
        'options' => ['placeholder' => '请输入供应商 ...'],
        'pluginOptions' => [
            'placeholder' => 'search ...',
            'multiple' => true,
            'allowClear' => true,
            'language' => [
                'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
            ],
            'ajax' => [
                'url' => $url,
                'dataType' => 'json',
                'data' => new JsExpression('function(params) { return {q:params.term}; }')
            ],
            'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
            'templateResult' => new JsExpression('function(res) { return res.text; }'),
            'templateSelection' => new JsExpression('function (res) { return res.text; }'),
        ],
    ])->label('供应商配置');
    ?>
</div>
<div class=" col-md-12">
    <?= Html::submitButton('提交配置', ['class' =>  'btn btn-success']) ?>
</div>
<?php ActiveForm::end(); ?>

<?php
$url = \yii\helpers\Url::toRoute('get-product-line');
$js = <<<JS
$(function () {
  $('.tree li:has(ul)').addClass('parent_li');
  $('.tree li.parent_li > label').on('click', function (e) {
    var children = $(this).parent('li.parent_li').find(' ul li');
    if (children.is(":visible")) {
         $(this).find(' > i').addClass('glyphicon-plus').removeClass('glyphicon-minus');
    }else {
         $(this).find(' > i').addClass('glyphicon-minus').removeClass('glyphicon-plus');
    }
    $(this).parent('li.parent_li').find(' > ul > li').each(function() {
      if ($(this).is(":visible")) {
      $(this).hide('fast');
    } else {
      $(this).show('fast');
    }
    });
    e.stopPropagation();
  });
  $('.tree li > input').on('click',function() {
    var level = $(this).attr('level');
    var checked = $(this).is(':checked');
    if(level==1){
        $(this).next('ul').find('input').each(function() {
            $(this).prop('checked',checked);
        });
    }
    if(level==2){
        $(this).next('ul').find('input').each(function() {
            $(this).prop('checked',checked);
        });
        var count = 0;
        $(this).closest('ul').find('input').each(function() {
            if($(this).is(':checked')){
              count++;
            }
        });
        if(count==0){
            $(this).closest('ul').closest('li').find('>input').prop('checked',false);
        }else {
            $(this).closest('ul').closest('li').find('>input').prop('checked',true);
        }
    }
    if(level==3){
        var count = 0;
        $(this).closest('ul').find('input').each(function() {
            if($(this).is(':checked')){
              count++;
            }
        });
        if(count==0){
            $(this).closest('ul').closest('li').find('>input').prop('checked',false);
        }else {
            $(this).closest('ul').closest('li').find('>input').prop('checked',true);
        }
        var parentCount=0;
        $(this).parents('li').last().find('input').each(function() {
          if($(this).is(':checked')){
              parentCount++;
            }
        });
        if(parentCount==1){
            $(this).parents('li').last().find('>input').prop('checked',false);
        }else {
            $(this).parents('li').last().find('>input').prop('checked',true);
        }
    }
    
  });
});

JS;
$this->registerJs($js);
?>
