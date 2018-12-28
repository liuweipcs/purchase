<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
// use yii\widgets\LinkPager;
use liyunfang\pager\LinkPager;
use app\models\PurchaseOrderItems;
use kartik\grid\GridView;


Modal::begin([
     'id' => 'create-modal',
     'header' => '<h4 class="modal-title">系统信息</h4>',
     'footer' => '<a href="#" class="btn btn-primary closes" data-dismiss="modal">关闭</a>',
     'size'=>'modal-lg',
     'options'=>[
         'data-backdrop'=>'static',
     ],
 ]);
Modal::end();

$head_list = [
    'xh'       => '序号',
    'sku'      => 'sku',
    'cpx'      => '产品线',
    'cgdh'     => '采购单号',
    'spmc'     => '商品名称',
    'ztkc'     => '在途库存',
    'kykc'     => '可用库存',
    'qjdhsj'   => '权均到货时间',
    'cgdscsj'  => '采购单生成时间',
    'cgdfksj'  => '采购单付款时间',
    'skudqxjq' => 'sku的权限交期',
    'sfcgqxjq' => '是否超过权限交期',
    'ckbm'     => '仓库编码',
    'cgdzt'    => '采购单状态',
    'zxgx'     => '最近更新',
    'sjsj'     => '上架时间',
    'fcckky'   => '分仓仓库/可用',
];

$fields_params = [// 展示和排序的字段
    'xh'       => '序号',
    'sku'      => 'sku',
    'cpx'      => '产品线',
    'cgdh'     => '采购单号',
    'spmc'     => '商品名称',
    'ztkc'     => '在途库存',
    'kykc'     => '可用库存',
    'qjdhsj'   => '权均到货时间',
    'cgdscsj'  => '采购单生成时间',
    'cgdfksj'  => '采购单付款时间',
    'skudqxjq' => 'sku的权限交期',
    'sfcgqxjq' => '是否超过权限交期',
    'ckbm'     => '仓库编码',
    'cgdzt'    => '采购单状态',
    'zxgx'     => '最近更新',
    'sjsj'     => '上架时间',
    'fcckky'   => '分仓仓库/可用',

    'cgjysl'   => '建议采购数量',
    'sjcgsl'   => '实际采购数量',
    'hyzt'     => '货源状态',
    'cgfkzt'   => '采购付款状态',
    'cgdhzt'   => '采购到货状态',
    'ysfs'     => '运输方式',
    'yjzt'     => '预警状态',
    'cjr'      => '创建人',
    'cgy'      => '采购员',
];

if(isset($fields) and $fields){
    foreach($fields as $key_f => $value_f){
        $field_sort[$key_f] = $value_f['sort'];
        if($value_f['show'])
            $field_show[$key_f] = $value_f['show'];
    }
}else{
    $field_sort = $fields_params;
    $field_show = $fields_params;
}
$field_sort_all = $field_sort;
$field_sort     = array_intersect(array_keys($field_sort),array_keys($head_list));// 表头排序

$fields_params['table'] = 'purchase_order_real_time_sku_list';
$fields_params['user_id'] = isset($user_id)?$user_id:'';
$fields_params['user'] = isset($user)?$user:'';
$fields_params['user_number'] = isset($user_number)?$user_number:'';
$fields_params = json_encode($fields_params);

$warehouse_list = \app\services\BaseServices::getWarehouseCode();
$sku_stock_list_cache = [];
?>

<div class="box box-success">
    <div class="box-body">
        <?=$this->render('_search-new', ['model' => $model, 'params'=>$params,'user' => $user,'user_number' => $user_number]); ?>
    </div>
</div>

<div class="box box-info">
    <div class="box-header with-border">
        <div class="box-tools pull-right">
            <?php
                $offset1 = $pagination->offset+1;
                $offset2 = $pagination->offset+$pagination->limit;
            ?>
            <div class="summary" style="padding: 10px 0px;">第<b><?= $offset1.'-'.$offset2 ?></b>条，共<b><?= $pagination->totalCount ?></b>条数据.</div>
        </div>
    </div>
    <div class="box-body">

<table class="table table-bordered table-hover" style="margin-top: 10px;">
    <thead>
        <tr>
            <?php foreach($field_sort as $key_sort){
                if(!isset($field_show[$key_sort])) continue;
                ?>
                <th><?php echo $head_list[$key_sort];?></th>
            <?php
            }?>
        </tr>
    </thead>
    <?php foreach($data as $k => $v):
        $sku = $v['sku'];
        $productInfo = \app\models\Product::findOne(['sku' => $v['sku']]);
        if(isset($productInfo->product_linelist_id) and $productInfo->product_linelist_id){
            $product_linelist_id = \app\services\BaseServices::getProductLine($productInfo->product_linelist_id);
        }
        $avg = !empty($v['avg_delivery_time']) ? $v['avg_delivery_time'] :0;
        $audit_time = !empty($v['audit_time']) ? $v['audit_time'] : '';
        $avg_arrival_time = empty($audit_time) ? '' : date('Y-m-d H:i:s',strtotime($audit_time)+round($avg,0));

        $data = \app\models\WarehouseResults::find()->select('instock_date')->where(['pur_number'=>$v['pur_number'],'sku'=>$v['sku']])->scalar();
        $avg = !empty($v['avg_delivery_time']) ? $v['avg_delivery_time'] :0;
        $audit_time = !empty($v['audit_time']) ? $v['audit_time'] : '';
        $fou = '<span style="color: green">否</span>';
        $shi = '<span style="color: red">是</span>';
        $instock = $data ? strtotime($data):time();
        $is_time = empty($audit_time) ? $fou: (((strtotime($audit_time)+$avg) <= $instock)? $shi:$fou );

        $now_data = [];
        $now_data['xh'] = $offset1+$k;
        $now_data['sku'] = $v['sku'];
        $now_data['cpx'] = isset($product_linelist_id)?$product_linelist_id:'';
        $now_data['cgdh'] = $v['pur_number'];
        $now_data['spmc'] = $v['name'];
        $now_data['ztkc'] = $v['on_way_stock'];
        $now_data['kykc'] = $v['available_stock'];
        $now_data['qjdhsj'] = $avg_arrival_time;
        $now_data['cgdscsj'] = $v['created_at'];
        $now_data['cgdfksj'] = $v['payer_time'];
        $now_data['skudqxjq'] = !empty($v['avg_delivery_time'])? round($v['avg_delivery_time']/86400, 2) : 0;
        $now_data['sfcgqxjq'] = $is_time;
        $now_data['ckbm'] = $v['warehouse_name'];

        // 最近更新、上架时间、分仓仓库/可用
        if(array_intersect(array_keys($field_show),['zxgx','sjsj','fcckky'])){// 含有三个中的一个则调接口获取数据
            $sku_stock_list = '';
            $now_data['zxgx'] = '<table border="1" >';
            $now_data['sjsj'] = '<table border="1" >';
            $now_data['fcckky'] = '<table border="1" cellpadding="10" cellspacing="10">';

            if(isset($sku_stock_list_cache[$sku])){// 缓存一下API数据，避免同一个SKU多次调用
                $sku_stock_list[$sku] = $sku_stock_list_cache[$sku];
            }else{
                $sku_stock_list = \app\api\v1\controllers\PurchaseOrderController::getSkuStorageStockFromWms($sku);
            }
            if(isset($sku_stock_list[$sku]['location_list']) and $sku_stock_list[$sku]['location_list']){
                foreach($sku_stock_list[$sku]['location_list'] as $value_list){
                    $now_data['zxgx'] .= '<tr><td style="padding: 3px;">'.$value_list['last_operate_type'].'</td></tr>';
                    $now_data['sjsj'] .= '<tr><td style="padding: 3px;">'.substr($value_list['puton_time'],0,10).'</td></tr>';
                }
            }
            if(isset($sku_stock_list[$sku]['stock_list']) and $sku_stock_list[$sku]['stock_list']){
                foreach($sku_stock_list[$sku]['stock_list'] as $k_list2 => $value_list2){
                    $k_list2 = isset($warehouse_list[$k_list2])?$warehouse_list[$k_list2]:$k_list2;
                    $now_data['fcckky'] .= '<tr><td style="min-width: 120px; padding: 3px;">'.$k_list2.'</td><td style="padding: 3px;">'.$value_list2.'</td></tr>';
                }
            }
            $now_data['zxgx'] .= '</table>';
            $now_data['sjsj'] .= '</table>';
            $now_data['fcckky'] .= '</table>';
            $sku_stock_list_cache[$sku] = isset($sku_stock_list[$sku])?$sku_stock_list[$sku]:'';
        }

        $html = PurchaseOrderItems::getOrderOne($v,$field_show,$field_sort_all);
        $now_data['cgdzt'] = $html;

        ?>
    <tr>
        <?php
        foreach($field_sort as $k_sort){
            if(!isset($field_show[$k_sort])) continue;
            echo '<td>'.(isset($now_data[$k_sort])?$now_data[$k_sort]:'').'</td>';
        }
        ?>
    </tr>
    <?php endforeach; ?>
    <tbody>
        
    </tbody>
</table>
</div>
    <div class="box-footer">
        <?php echo LinkPager::widget([
            'pagination' => $pagination,
            'firstPageLabel' => '首页',
            'prevPageLabel' => '上一页',
            'nextPageLabel' => '下一页',
            'lastPageLabel' => '末页',
            // 'maxButtonCount' =>10,
            'options'=>['class' => 'pagination pageSizeList','style'=> "display:block;"],
            'pageSizeList' => [20,100,200,300,500,1000], //页大小下拉框值
        ]);
        ?>
    </div>
</div>

<?php
$js = <<<JS
$(function() {
    $("*[name=per-page]").change(function(){
        var pageSize=$(this).val();
        var url = '/purchase-order-real-time-sku/test?pageSize=' + pageSize;
        window.location.href=url;
    });
    
    
    $(".btn-edit-fields").click(function(){
        $("#create-modal .modal-title").text('编辑显示内容');
        $.post('/member/update-table-fields-show', {$fields_params}, function (data) {
            $('#create-modal .modal-body').html(data);
        });
    })
});
JS;
$this->registerJs($js);
?>