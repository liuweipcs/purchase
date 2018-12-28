<?php
use kartik\file\FileInput;
use yii\helpers\Url;
use yii\helpers\Html;
use app\config\Vhelper;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/9
 * Time: 15:57
 */

$p1=\yii\helpers\ArrayHelper::toArray($model->img,[
    'app\models\SupplierImages' => [
        'image_url'
    ]
]);
$p1=\yii\helpers\ArrayHelper::getColumn($p1,'image_url');
$p1 = array_filter(array_unique($p1));

$all_show_img_list = !empty($auditInfo['supplier_images'])?$auditInfo['supplier_images']:[];
if(!empty($imageModel)){
    foreach($imageModel as $key => $value){
        if(empty($all_show_img_list[$key])){
            $all_show_img_list[$key] = (strpos($value,';') !== false)?explode(';',$value):$value;
        }
    }
}

?>

<h3 class="header_hint_message_show">
    <strong style='color:red;'>请先上传对公材料，再上传对私材料！</strong>
</h3>

<!-- 非公非私 -->
<div class="box box-info no_gong_no_si_img_show">
    <div class="box-header with-border">
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
        </div>
    </div>
    <div class="box-body">
        <div class="row">
            <?php
            $nogong_nosi = ['busine_licen_url'=>'营业执照'];
            foreach ($nogong_nosi as $k => $v) {
                $img_list = isset($all_show_img_list[$k])?$all_show_img_list[$k]:'';
                $img_list = Vhelper::extractFromImgForSupplierImg($img_list);

                echo '<div class="col-md-12">';
                echo $form->field($imageModel, $k)->widget(FileInput::classname(), [
                    'options' => ['multiple' => true,'name'=>'SupplierImages[' . $k . '][]'],
                    'pluginOptions' => [
                        // 需要预览的文件格式
                        'previewFileType' => 'image',
                        // 预览的文件
                        'initialPreview' =>!empty($img_list)?$img_list:[], //['图片1', '图片2', '图片3'],
                        // 需要展示的图片设置，比如图片的宽度等
                        'initialPreviewConfig' =>$p2, //['width' => '120px'],
                        // 是否展示预览图
                        'initialPreviewAsData' => true,
                        'allowedFileExtensions' => ['jpg', 'gif', 'png','pdf','xls','xlsx','csv','doc'],
                        // 异步上传的接口地址设置
                        'uploadUrl' => Url::toRoute(['/supplier/async-image']),
                        'uploadAsync' => true,
                        // 最少上传的文件个数限制
                        'minFileCount' => 1,
                        // 最多上传的文件个数限制
                        'maxFileCount' => ($k == 'busine_licen_url')?3:1,
                        'maxFileSize' => 200000,//限制图片最大200kB
                        // 是否显示移除按钮，指input上面的移除按钮，非具体图片上的移除按钮
                        'showRemove' => true,
                        // 是否显示上传按钮，指input上面的上传按钮，非具体图片上的上传按钮
                        'showUpload' => false,
                        //是否显示[选择]按钮,指input上面的[选择]按钮,非具体图片上的上传按钮
                        'showBrowse' => false,
                        // 展示图片区域是否可点击选择多文件
                        'browseOnZoneClick' => true,
                        // 如果要设置具体图片上的移除、上传和展示按钮，需要设置该选项
                        'fileActionSettings' => [
                            // 设置具体图片的查看属性为false,默认为true
                            'showZoom' => true,
                            // 设置具体图片的上传属性为true,默认为true
                            'showUpload' => true,
                            // 设置具体图片的移除属性为true,默认为true
                            'showRemove' => true,
                        ],
                        // 异步上传需要携带的其他参数，比如商品id等
                        'uploadExtraData' => [
                            'input_name' => 'SupplierImages[' . $k . ']',
                        ],
                    ],
                    // 一些事件行为
                    'pluginEvents' => [
                        // 上传成功后的回调方法，需要的可查看data后再做具体操作，一般不需要设置
                        'fileuploaded' => 'function(event, data, previewId, index) {
                            $(event.currentTarget.closest("form")).append(data.response.imgfile);
                        }',
                        'filecleared'=>'function(event){
                            $("[name=\"SupplierImages[$k]\"]").remove();
                            // $("[name=\"SupplierCheckUpload[abnormal_img][file][]\"]").remove();
                            // $("[name=\"SupplierCheckUpload[abnormal_img][filename][]\"]").remove();
                        }'
                    ],
                ])->label($v);
                if (!empty($imagesInfo[$k])) {
                    Vhelper::showHistoryImg($imagesInfo[$k],$v,$imageModel->$k);
                }
                echo '</div>';
            }
            ?>
        </div>
    </div>
</div>

<!-- 对公 -->
<div class="box box-info duigong_img_show">
    <div class="box-header with-border">
        <h3 class="box-title">对公</h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
        </div>
    </div>
    <div class="box-body">
        <div class="row">
            <?php
            $duigong = ['public_busine_licen_url'=>'营业执照', 'verify_book_url'=> '一般纳税人认定书', 'ticket_data_url'=>'开票资料'];
                foreach ($duigong as $k => $v) {
                    $img_list = isset($all_show_img_list[$k])?$all_show_img_list[$k]:'';
                    $img_list = Vhelper::extractFromImgForSupplierImg($img_list);

                    echo '<div class="col-md-12">';
                    echo $form->field($imageModel, $k)->widget(FileInput::classname(), [
                        'options' => ['multiple' => true,'name'=>'SupplierImages[' . $k . '][]'],
                        'pluginOptions' => [
                            // 需要预览的文件格式
                            'previewFileType' => 'image',
                            // 预览的文件
                            'initialPreview' =>!empty($img_list)?$img_list:[], //['图片1', '图片2', '图片3'],
                            // 需要展示的图片设置，比如图片的宽度等
                            'initialPreviewConfig' =>$p2, //['width' => '120px'],
                            // 是否展示预览图
                            'initialPreviewAsData' => true,
                            'allowedFileExtensions' => ['jpg', 'gif', 'png','pdf','xls','xlsx','csv','doc'],
                            // 异步上传的接口地址设置
                            'uploadUrl' => Url::toRoute(['/supplier/async-image']),
                            'uploadAsync' => true,
                            // 最少上传的文件个数限制
                            'minFileCount' => 1,
                            // 最多上传的文件个数限制
                            'maxFileCount' => ($k == 'public_busine_licen_url')?3:1,
                            'maxFileSize' => 200000,//限制图片最大200kB
                            // 是否显示移除按钮，指input上面的移除按钮，非具体图片上的移除按钮
                            'showRemove' => true,
                            // 是否显示上传按钮，指input上面的上传按钮，非具体图片上的上传按钮
                            'showUpload' => false,
                            //是否显示[选择]按钮,指input上面的[选择]按钮,非具体图片上的上传按钮
                            'showBrowse' => false,
                            // 展示图片区域是否可点击选择多文件
                            'browseOnZoneClick' => true,
                            // 如果要设置具体图片上的移除、上传和展示按钮，需要设置该选项
                            'fileActionSettings' => [
                                // 设置具体图片的查看属性为false,默认为true
                                'showZoom' => true,
                                // 设置具体图片的上传属性为true,默认为true
                                'showUpload' => true,
                                // 设置具体图片的移除属性为true,默认为true
                                'showRemove' => true,
                            ],
                            // 异步上传需要携带的其他参数，比如商品id等
                            'uploadExtraData' => [
                                'input_name' => 'SupplierImages[' . $k . ']',
                            ],
                        ],
                        // 一些事件行为
                        'pluginEvents' => [
                            // 上传成功后的回调方法，需要的可查看data后再做具体操作，一般不需要设置
                            'fileuploaded' => 'function(event, data, previewId, index) {
                                $(event.currentTarget.closest("form")).append(data.response.imgfile);
                            }',
                            'filecleared'=>'function(event){
                                $("[name=\"SupplierImages[$k]\"]").remove();

                                // $("[name=\"SupplierCheckUpload[abnormal_img][file][]\"]").remove();
                                // $("[name=\"SupplierCheckUpload[abnormal_img][filename][]\"]").remove();
                            }'
                        ],
                    ])->label($v);
                    if (!empty($imagesInfo[$k])) {
                        Vhelper::showHistoryImg($imagesInfo[$k],$v,$imageModel->$k);
                    }
                    echo '</div>';
                }
            ?>
        </div>
    </div>
</div>


<!-- 对私 -->
<div class="box box-info duigsi_img_show">
    <div class="box-header with-border">
        <h3 class="box-title">对私</h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
        </div>
    </div>
    <div class="box-body">
        <div class="row">
            <?php
                $duisi = ['private_busine_licen_url'=>'营业执照', 'receipt_entrust_book_url'=>'收款委托书', 'card_copy_piece_url'=>'身份证复印件', 'bank_scan_price_url'=>'银行卡的扫描件', 'fuyou_record_data_url'=>'富友备案资料'];
                foreach ($duisi as $k => $v) {
                    $img_list = isset($all_show_img_list[$k])?$all_show_img_list[$k]:'';
                    $img_list = Vhelper::extractFromImgForSupplierImg($img_list);

                    echo '<div class="col-md-12">';
                    echo $form->field($imageModel, $k)->widget(FileInput::classname(), [
                        'options' => ['multiple' => true,'name'=>'SupplierImages[' . $k . '][]', 'class'=>'duisi_class'],
                        'pluginOptions' => [
                            // 需要预览的文件格式
                            'previewFileType' => 'image',
                            // 预览的文件
                            'initialPreview' =>!empty($img_list)?$img_list:[], //['图片1', '图片2', '图片3'],
                            // 需要展示的图片设置，比如图片的宽度等
                            'initialPreviewConfig' =>$p2, //['width' => '120px'],
                            // 是否展示预览图
                            'initialPreviewAsData' => true,
                            'allowedFileExtensions' => ['jpg', 'gif', 'png','pdf','xls','xlsx','csv','doc'],
                            // 异步上传的接口地址设置
                            'uploadUrl' => Url::toRoute(['/supplier/async-image']),
                            'uploadAsync' => true,
                            // 最少上传的文件个数限制
                            'minFileCount' => 1,
                            // 最多上传的文件个数限制
                            'maxFileCount' => ($k == 'private_busine_licen_url')?3:1,
                            'maxFileSize' => 200000,//限制图片最大200kB
                            // 是否显示移除按钮，指input上面的移除按钮，非具体图片上的移除按钮
                            'showRemove' => false,
                            // 是否显示上传按钮，指input上面的上传按钮，非具体图片上的上传按钮
                            'showUpload' => false,
                            //是否显示[选择]按钮,指input上面的[选择]按钮,非具体图片上的上传按钮
                            'showBrowse' => false,
                            // 展示图片区域是否可点击选择多文件
                            'browseOnZoneClick' => true,
                            // 如果要设置具体图片上的移除、上传和展示按钮，需要设置该选项
                            'fileActionSettings' => [
                                // 设置具体图片的查看属性为false,默认为true
                                'showZoom' => true,
                                // 设置具体图片的上传属性为true,默认为true
                                'showUpload' => true,
                                // 设置具体图片的移除属性为true,默认为true
                                'showRemove' => true,
                            ],
                            // 异步上传需要携带的其他参数，比如商品id等
                            'uploadExtraData' => [
                                'input_name' => 'SupplierImages[' . $k . ']',
                            ],
                        ],
                        // 一些事件行为
                        'pluginEvents' => [
                            // 上传成功后的回调方法，需要的可查看data后再做具体操作，一般不需要设置
                            'fileuploaded' => 'function(event, data, previewId, index) {
                                $(event.currentTarget.closest("form")).append(data.response.imgfile);
                            }',
                            'filecleared'=>'function(event){
                                $("[name=\"SupplierImages[$k]\"]").remove();

                                // $("[name=\"SupplierCheckUpload[abnormal_img][file][]\"]").remove();
                                // $("[name=\"SupplierCheckUpload[abnormal_img][filename][]\"]").remove();
                            }'
                        ],
                    ])->label($v);
                    if (!empty($imagesInfo[$k])) {
                        Vhelper::showHistoryImg($imagesInfo[$k],$v,$imageModel->$k);
                    }
                    echo '</div>';
                }
            ?>
        </div>
    </div>
</div>

<!-- 历史附属图片 -->
<?php
echo $p1 ? Html::a('下载附属文件',['download','filename'=>$p1]):'';
echo $form->field($model, 'image_url')->widget(FileInput::classname(), ['options' => ['multiple' => true,'name'=>'SupplierImages[image_url]'],
    'pluginOptions' => [
        // 需要预览的文件格式
        'previewFileType' => 'image',
        // 预览的文件
        'initialPreview' =>$p1,
        // 需要展示的图片设置，比如图片的宽度等
        'initialPreviewConfig' =>$p2,
        // 是否展示预览图
        'initialPreviewAsData' => true,
        'allowedFileExtensions' => [],
        // 异步上传的接口地址设置
        // 'uploadUrl' => Url::toRoute(['/supplier/async-image']),
        'uploadAsync' => false,
        // 最少上传的文件个数限制
        'minFileCount' => 0,
        // 最多上传的文件个数限制
        'maxFileCount' => 0,
        'maxFileSize' => 100000,//限制图片最大200kB
        // 是否显示移除按钮，指input上面的移除按钮，非具体图片上的移除按钮
        'showRemove' => false,
        // 是否显示上传按钮，指input上面的上传按钮，非具体图片上的上传按钮
        'showUpload' => false,
        //是否显示[选择]按钮,指input上面的[选择]按钮,非具体图片上的上传按钮
        'showBrowse' => false,
        // 展示图片区域是否可点击选择多文件
        'browseOnZoneClick' => false,
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
            $(event.currentTarget.closest("form")).append(data.response.imgfile);
        }',
    ],
])->label('附属图片(旧图片)');
?>

<div>
    <?= \yii\helpers\Html::button('上一步',['class'=>'btn btn-info','id'=>'supplier_img_info_up'])?>
</div>
<?php if($view=='update' && $is_readonly==false && $is_audit==false){ ?>
    <div class="form-group" style="margin-top: 10px;margin-bottom: 100px;">
        <?= \yii\helpers\Html::submitButton($model->isNewRecord ? Yii::t('app', '创建') : Yii::t('app', '更新'), ['class' => $model->isNewRecord ? 'btn btn-success update' : 'btn btn-primary update']) ?>
    </div>
    <br><br><br>
<?php } ?>
<?php
$js = <<<JS
    $(function () {
        $('#supplier_img_info_up').on('click',function() {
            $('#buyer_info_li').tab('show');
            $('#buyer_info_content').addClass('active in');
            $('#img_info_content').removeClass('active in');
        });
        
    });
JS;
$this->registerJs($js);
?>