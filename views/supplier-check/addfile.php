<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\tabs\TabsX;
use kartik\file\FileInput;
use yii\helpers\Url;
use app\services\SupplierGoodsServices;
use app\services\SupplierServices;
use app\services\BaseServices;

/* @var $this yii\web\View */
/* @var $model app\models\Supplier */
/* @var $form yii\widgets\ActiveForm */
?>



<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

<input type="hidden" value=<?= $checkId?> name="SupplierCheckUpload[check_id]">
<div class="row">
    <div class="col-md-12">
        <?= $form->field($model, 'inspection_report')->widget(FileInput::classname(), ['options' => ['multiple' => true],
            'pluginOptions' => [
                // 需要预览的文件格式
               // 'previewFileType' => 'image',
                // 预览的文件
                'initialPreview' =>$p1,
                // 需要展示的图片设置，比如图片的宽度等
                'initialPreviewConfig' =>$p2,
                // 是否展示预览图
                'initialPreviewAsData' => false,
                'allowedFileExtensions' => ['jpg', 'gif', 'png','pdf','xls','xlsx','csv'],
                // 异步上传的接口地址设置
                'uploadUrl' => Url::toRoute(['/supplier-check/image-asyn-upolad','name'=>'inspection_report']),
                'uploadAsync' => true,
                // 最少上传的文件个数限制
                'minFileCount' => 1,
                // 最多上传的文件个数限制
                'maxFileCount' => 2000,
                'maxFileSize' => 6000,//限制图片最大2000kB
                // 是否显示移除按钮，指input上面的移除按钮，非具体图片上的移除按钮
                'showRemove' => true,
                // 是否显示上传按钮，指input上面的上传按钮，非具体图片上的上传按钮
                'showUpload' => true,
                //是否显示[选择]按钮,指input上面的[选择]按钮,非具体图片上的上传按钮
                'showBrowse' => true,
                // 展示图片区域是否可点击选择多文件
                'browseOnZoneClick' => true,
                // 如果要设置具体图片上的移除、上传和展示按钮，需要设置该选项
                'fileActionSettings' => [
                    // 设置具体图片的查看属性为false,默认为true
                    'showZoom' => false,
                    // 设置具体图片的上传属性为true,默认为true
                    'showUpload' => false,
                    // 设置具体图片的移除属性为true,默认为true
                    'showRemove' => false,
                ],
            ],
            // 一些事件行为
            'pluginEvents' => [
                // 上传成功后的回调方法，需要的可查看data后再做具体操作，一般不需要设置
                'fileuploaded' => 'function(event, data, previewId, index) {
                        var count = data.response.length;
                        $.each(data.response,function(index,value){
                            $(event.currentTarget.closest("form")).append(value.imgfile);
                            $(event.currentTarget.closest("form")).append(value.imgfilename);
                            });
                    }',
                'filecleared'=>'function(event){
                    $("[name=\"SupplierCheckUpload[inspection_report][file][]\"]").remove();
                    $("[name=\"SupplierCheckUpload[inspection_report][filename][]\"]").remove();
                }',
            ],

        ])->label('1.上传检验报告')?>
    </div>
    <div class="col-md-12">
        <?= $form->field($model, 'product_img')->widget(FileInput::classname(), ['options' => ['multiple' => true],
            'pluginOptions' => [
                // 需要预览的文件格式
                // 'previewFileType' => 'image',
                // 预览的文件
                'initialPreview' =>$p1,
                // 需要展示的图片设置，比如图片的宽度等
                'initialPreviewConfig' =>$p2,
                // 是否展示预览图
                'initialPreviewAsData' => false,
                'allowedFileExtensions' => ['jpg', 'gif', 'png','pdf','xls','xlsx','csv'],
                // 异步上传的接口地址设置
                'uploadUrl' => Url::toRoute(['/supplier-check/image-asyn-upolad','name'=>'product_img']),
                'uploadAsync' => true,
                // 最少上传的文件个数限制
                'minFileCount' => 1,
                // 最多上传的文件个数限制
                'maxFileCount' => 2000,
                'maxFileSize' => 6000,//限制图片最大2000kB
                // 是否显示移除按钮，指input上面的移除按钮，非具体图片上的移除按钮
                'showRemove' => true,
                // 是否显示上传按钮，指input上面的上传按钮，非具体图片上的上传按钮
                'showUpload' => true,
                //是否显示[选择]按钮,指input上面的[选择]按钮,非具体图片上的上传按钮
                'showBrowse' => true,
                // 展示图片区域是否可点击选择多文件
                'browseOnZoneClick' => true,
                // 如果要设置具体图片上的移除、上传和展示按钮，需要设置该选项
                'fileActionSettings' => [
                    // 设置具体图片的查看属性为false,默认为true
                    'showZoom' => false,
                    // 设置具体图片的上传属性为true,默认为true
                    'showUpload' => false,
                    // 设置具体图片的移除属性为true,默认为true
                    'showRemove' => false,
                ],
            ],
            // 一些事件行为
            'pluginEvents' => [
                // 上传成功后的回调方法，需要的可查看data后再做具体操作，一般不需要设置
                'fileuploaded' => 'function(event, data, previewId, index) {
                        var count = data.response.length;
                        $.each(data.response,function(index,value){
                            $(event.currentTarget.closest("form")).append(value.imgfile);
                            $(event.currentTarget.closest("form")).append(value.imgfilename);
                            });
                    }',
                'filecleared'=>'function(event){
                    $("[name=\"SupplierCheckUpload[product_img][file][]\"]").remove();
                    $("[name=\"SupplierCheckUpload[product_img][filename][]\"]").remove();
                }',
            ],

        ])->label('2.上传产品及包装照片')?>
    </div>
    <div class="col-md-12">
        <?= $form->field($model, 'abnormal_img')->widget(FileInput::classname(), ['options' => ['multiple' => true],
            'pluginOptions' => [
                // 需要预览的文件格式
                // 'previewFileType' => 'image',
                // 预览的文件
                'initialPreview' =>$p1,
                // 需要展示的图片设置，比如图片的宽度等
                'initialPreviewConfig' =>$p2,
                // 是否展示预览图
                'initialPreviewAsData' => false,
                'allowedFileExtensions' => ['jpg', 'gif', 'png','pdf','xls','xlsx','csv'],
                // 异步上传的接口地址设置
                'uploadUrl' => Url::toRoute(['/supplier-check/image-asyn-upolad','name'=>'abnormal_img']),
                'uploadAsync' => true,
                // 最少上传的文件个数限制
                'minFileCount' => 1,
                // 最多上传的文件个数限制
                'maxFileCount' => 2000,
                'maxFileSize' => 6000,//限制图片最大2000kB
                // 是否显示移除按钮，指input上面的移除按钮，非具体图片上的移除按钮
                'showRemove' => true,
                // 是否显示上传按钮，指input上面的上传按钮，非具体图片上的上传按钮
                'showUpload' => true,
                //是否显示[选择]按钮,指input上面的[选择]按钮,非具体图片上的上传按钮
                'showBrowse' => true,
                // 展示图片区域是否可点击选择多文件
                'browseOnZoneClick' => true,
                // 如果要设置具体图片上的移除、上传和展示按钮，需要设置该选项
                'fileActionSettings' => [
                    // 设置具体图片的查看属性为false,默认为true
                    'showZoom' => false,
                    // 设置具体图片的上传属性为true,默认为true
                    'showUpload' => false,
                    // 设置具体图片的移除属性为true,默认为true
                    'showRemove' => false,
                ],
            ],
            // 一些事件行为
            'pluginEvents' => [
                // 上传成功后的回调方法，需要的可查看data后再做具体操作，一般不需要设置
                'fileuploaded' => 'function(event, data, previewId, index) {
                        var count = data.response.length;
                        $.each(data.response,function(index,value){
                            $(event.currentTarget.closest("form")).append(value.imgfile);
                            $(event.currentTarget.closest("form")).append(value.imgfilename);
                            });
                    }',
                'filecleared'=>'function(event){
                    $("[name=\"SupplierCheckUpload[abnormal_img][file][]\"]").remove();
                    $("[name=\"SupplierCheckUpload[abnormal_img][filename][]\"]").remove();
                }',
            ],

        ])->label('3.上传不良照片')?>
    </div>
</div>
<div class="form-group">
    <div style="margin-left: 10%;padding-top: 10px">
        <div style="float: right"><?= Html::submitButton(Yii::t('app', '确认'), ['class' => 'btn btn-primary commit_data']) ?></div>
        <div style="float: right;margin-right: 10px"><a href="#" class="btn btn-warning closes" data-dismiss="modal">取消</a></div>
        <div style="clear: right"></div>
    </div>
</div>
<?php ActiveForm::end(); ?>
<?php
$js = <<<JS
    $(document).on('click', '.commit_data', function () {
        var inspection_report=0;
        $('[name="SupplierCheckUpload[inspection_report][file][]"]').each(function() {
            if($(this).val()!=''){
                inspection_report++;
            }
        });
        if(inspection_report==0){
            layer.msg('检验报告不能为空');
            return false;
        }
    });
JS;
$this->registerJs($js);
?>



