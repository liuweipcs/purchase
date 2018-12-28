<?php
use kartik\form\ActiveForm;
?>
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
<?php $form = ActiveForm::begin([
        'id'=>'perimission-update',
        'enableAjaxValidation' => false,
    ]
); ?>
    <div class="raw">

<div class="tree well">
    <ul>
        <?php foreach ($permissions as $item){?>
            <li >
                <label><i class="<?=!empty($item['items']) ? 'glyphicon glyphicon-plus': 'glyphicon glyphicon-minus' ?>"></i></label>
                <input type="checkbox" level="1" value='<?= $item['id']?>' name="Permission[]" <?=in_array($item['id'],$oldPerimissionItems) ? 'checked="checked"':''?>> <?= $item['permission_name']?>
                <?php if(!empty($item['items'])){?>
                    <ul>
                        <?php foreach ($item['items'] as $value){?>
                            <li style="display: none">
                                <label><i class="<?=!empty($value['items']) ? 'glyphicon glyphicon-plus': 'glyphicon glyphicon-minus' ?>"></i></label>
                                <input type="checkbox" level="2" value="<?= $value['id']?>" name="Permission[]" <?=in_array($value['id'],$oldPerimissionItems) ? 'checked="checked"':''?>>  <?= $value['permission_name']?>
                                <ul>
                                    <?php if(!empty($value['items'])){?>
                                        <?php foreach ($value['items'] as $v){?>
                                            <li style="display: none"><input level="3" type="checkbox" value="<?=$v['id']?>" name="Permission[]" <?=in_array($v['id'],$oldPerimissionItems) ? 'checked="checked"':''?>><?=$v['permission_name']?></li>
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
    <div class="form-group">
            <button class="btn btn-success  " type="submit">立即提交</button>
            <button type="button" class="btn btn-primary" data-dismiss="modal">关闭</button>
    </div>
<?php ActiveForm::end(); ?>
<?php
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