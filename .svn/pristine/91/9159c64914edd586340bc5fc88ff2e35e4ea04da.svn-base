<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use Yii\helpers\Url;
use yii\bootstrap\Modal;
/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $model app\models\LoginForm */
?>

<p>
<?= 
  Html::a(Yii::t('app', '仓库补货策略'),'#',
   [
       'id'	=> 'warehouse-replenish',
        'data-toggle'	=> 'modal',
        'data-target'	=> '#warehouse-replenish-data',
        'class' => 'btn btn-primary'
        				
   ]);
?>
</p>
<div id="update-form" style="display: none" >
<?php 
$form = ActiveForm::begin([
		'id' => 'login-form1',
		'options' => ['class' => 'form-horizontal'],
]) ?>
    <?= $form->field($warehouse, 'id')->textInput(['placeholder'=>'如A001']) ?>
    <?= $form->field($warehouse, 'warehouse_id')->passwordInput() ?>

    <div class="form-group">
        <div class="col-lg-offset-1 col-lg-11">
            <?= Html::submitButton('Login', ['class' => 'btn btn-primary']) ?>
        </div>
    </div>
<?php ActiveForm::end() ?>


</div>
<?php 
//仓库补货策略数据显示
$warehouseIndexUrl = Url::toRoute('/replenish/index');
$requestUrl = Url::toRoute('replenish-warehouse/show-warehouse-replenish');
$deleteUrl = Url::toRoute('replenish-warehouse/delete');
$updateDataUrl = Url::toRoute('replenish-warehouse/get-data');
$js = <<<JS

	var html='<table class="table table-striped table-bordered table-hover"><tr><td>仓库</td><td>补货模式</td><td>操作</td></tr>';
    $(document).on('click', '#warehouse-replenish', function () {
        $.post('{$requestUrl}', {},
            function (data) {
        		var warehouse = data.warehouse;
        		var mode = data.mode;
        		for(var i in warehouse){
        			html+='<tr><td>'+warehouse[i].id+'</td><td>'+mode[warehouse[i].replenish_mode]+'</td><td><a href="#" data-toggle="modal" data-target="#warehouse-replenish-update" class="warehouse-update" value="'+warehouse[i].id+'">修改</a> <a style="padding-left:50px;" data-toggle="modal" data-target="#warehouse-replenish-delete" class="warehouse-delete" value="'+warehouse[i].id+'">删除</a></td></tr>';
				}
				html+='</table>';
        		$('#warehouse-replenish-data').find('.modal-body').html(html);
                
            }  
        );
    });
        					
   $(document).on('click','.warehouse-delete',function(){
        var warehouse_prenish_id = $(this).attr('value');
		$('#warehouse-replenish-delete').find('.modal-body').html("确定要删除嘛？");
        $('#warehouse-replenish-delete').find('.modal-footer').html('<a id="delete-warehouse-prenish" value="'+warehouse_prenish_id+'"  data-toggle="modal" data-target="#warehouse-replenish-delete-message" class="btn btn-danger">确定</a><a class="btn btn-primary" data-dismiss="modal">取消</a>');
	});		
	
	$(document).on('click','.warehouse-update',function(){
		var warehouse_prenish_id = $(this).attr('value');
		$.post('{$updateDataUrl}',{id:warehouse_prenish_id},
			 function (data) {
        		if(data == 1){
        			
					$('#warehouse-replenish-update').find('.modal-body').html($('#update-form').html());
					//window.setTimeout( window.location = '{$warehouseIndexUrl}', 5000);
				}else{
					$('#warehouse-replenish-update').find('.modal-body').html($('#update-form').html());
        			//window.setTimeout( window.location = '{$warehouseIndexUrl}', 5000);
				}
                
            }
		);
        
		//$('#warehouse-replenish-update').find('.modal-body').html("更新数据");
        //$('#warehouse-replenish-update').find('.modal-footer').html('<a id="delete-warehouse-prenish" value="'+warehouse_prenish_id+'"  data-toggle="modal" data-target="#warehouse-replenish-delete-message" class="btn btn-danger">确定</a><a class="btn btn-primary" data-dismiss="modal">取消</a>');
	});	
   
    $(document).on('click', '#delete-warehouse-prenish', function () {
        $.post('{$deleteUrl}', {id:$(this).attr('value')},
            function (data) {
        		if(data == 1){
					$('#warehouse-replenish-delete-message').find('.modal-body').html("删除成功！");
					window.setTimeout( window.location = '{$warehouseIndexUrl}', 5000);
				}else{
					$('#warehouse-replenish-delete-message').find('.modal-body').html("删除失败！");
        			window.setTimeout( window.location = '{$warehouseIndexUrl}', 5000);
				}
                
            }  
        );
    });		
        					
    
JS;
$this->registerJs($js);
//仓库补货策略数据弹出层
Modal::begin([
		'id' 		=> 'warehouse-replenish-data',
		'size'		=> Modal::SIZE_LARGE,
		'header' 	=> '<h4 class="modal-title">仓库补货策略</h4>',
		'footer' 	=> '<a href="#" class="btn btn-primary" data-dismiss="modal">Close</a>',
]);
Modal::end();

//删除仓库补货删除弹出层
Modal::begin([
		'id'		=> 'warehouse-replenish-delete',
		'size'		=> Modal::SIZE_SMALL,
		'header'	=> '<h4 class="modal-title">删除策略</h4>',
		'footer' 	=> '<a href="#" class="btn btn-primary" data-dismiss="modal">Close</a>',
]);
Modal::end();
//删除仓库补货删除信息弹出层
Modal::begin([
		'id'		=> 'warehouse-replenish-delete-message',
		'size'		=> Modal::SIZE_SMALL,
		'header'	=> '<h4 class="modal-title">删除信息</h4>',
		'footer' 	=> '<a href="#" class="btn btn-primary" data-dismiss="modal">Close</a>',
]);
Modal::end();

//删除仓库补货更新信息弹出层
Modal::begin([
		'id'		=> 'warehouse-replenish-update',
		'size'		=> Modal::SIZE_LARGE,
		'header'	=> '<h4 class="modal-title">修改仓库补货策略</h4>',
		'footer' 	=> '<a href="#" class="btn btn-primary" data-dismiss="modal">Close</a>',
]);
Modal::end();

    	
?>
    