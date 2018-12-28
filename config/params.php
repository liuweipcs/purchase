<?php
return [
    'adminEmail' => 'admin@example.com',
    'admin' => '1',
    //采购用户级别
    'grade'=>[0=>'一般组员',1=>'组长',2=>'主管',3=>'经理'],
    //采购审核分组
    're_group'=>[1=>'组长组',2=>'主管组',3=>'经理组'],
    //数字范围
    'num_range'=>[1000=>'1000以下',3000=>'3000以下',3001=>'3000以上'],
    //海外仓货物状态
    'owhouse_state'=>[0=>'未付款',1=>'已付款',2=>'已发货',3=>'已到中转仓',4=>'已发往海外',5=>'已到海外仓'],
    'pattern' => ['def' => '默认', 'min' => '最小'],
    'boolean' => [0 => '否', 1 => '是'],
    'type' => ['last_down' => '持续下降', 'last_up' => '持续上升', 'wave_down' => '波动下降', 'wave_up' => '波动上升'],
    'currency_code' => ['RMB' => '人民币', 'AUD' => '欧元', 'GBP' => '英镑', 'HKD' => '港币', 'JPY' => '日元', 'MXN' => 'MXN', 'USD' => '美元'],
    //补货类型
    'pur_type'=>[1=>'缺货入库',2=>'警报入库',3=>'特采入库',4=>'正常入库',5=>'样品采购入库',6=>'备货采购',7=>'试销采购'],
    //运输方式
    'shipping_method'=>[1=>'自提',2=>'快递',3=>'物流',4=>'送货'],
    //采购收货异常单状态
    'status'=>[3=>'待处理',4=>'完成'],
    //采购收货异常单状态
    'receive_status'=>[1=>'未处理',2=>'已确认',3=>'已审核','4'=>'审核退回'],
    //采购订单收货异常类型
    'receive_type'=>['1'=>'收货收多','2'=>'部分到货','3'=>'与图片不符','4'=>'来货无包装','5'=>'来货混装','6'=>'需二次包装','0'=>'其他','7'=>'有次品'],
    //采购订单收货异常处理方式
    'handle_type'=>['0'=>'无','1'=>'终止来货(全额退款)','2'=>'部分到货不等待剩余','3'=>'部分到货等待剩余','4'=>'入库','5'=>'退货'],
    //qc异常单状态
    'qc_status'=>['1'=>'未处理','2'=>'不良品上架','3'=>'已确认','4'=>'已审核','5'=>'审核退回'],
    //qc 异常处理方式
    'handle_type_qc'=>['1'=>'销毁，采购方承担','2'=>'销毁，供应商承担','3'=>'退回，供应商退回款项','4'=>'换货，供应商重新发货','5'=>'不良品上架',null=>'无'],
    //qc 品检类型
    'check_type'=>['1'=>'抽检','2'=>'全检','3'=>'免检'],
    'is_receipt'=>['1'=>'收货','2'=>'不收'],
    //收款平台
    'platform'=>['BANK'=>'银行','ALIPAY'=>'支付宝','PAYPAL'=>'PAYPAL','TENPAY'=>'财付通','99BILL'=>'快钱','CASH'=>'现金'],
    'warehouse' =>['1'=>'本地仓','2'=>'海外仓','3'=>'第三方仓库'],
    //账期单
    'account_period' => [7=>'周结',8=>'半月结',9=>'月结',6=>'两月结',1=>'货到付款'], //周结、半月结、月结、两月结 pur_supplier_settlement

    //销售需求

    'demand'=>['0'=>'待同意','1'=>'同意','2'=>'驳回','3'=>'撤销','4'=>'采购驳回','5'=>'删除','6'=>'规则拦截','7'=>'待提交','8'=>'需求作废'],
    //入数据中心IP
    'server_ip' => YII_DEBUG ? 'http://192.168.71.216' : 'http://dc.yibainetwork.com',
    'tongtool' =>'https://openapi.tongtool.com',
    'SKU_BIG_IMG_PATH' =>'http://images.yibainetwork.com/upload/image/assistant/',
    'SKU_THUMB_IMG_PATH' =>'http://images.yibainetwork.com/upload/image/Thumbnails/',
    'SKU_Development_IMG_PATH' =>'http://images.yibainetwork.com/upload/image/productImages/',
    //'SKU_ERP_Product_Detail'   =>'http://120.24.249.36/product/index/sku/',
    'SKU_ERP_Product_Detail'   =>'http://120.78.243.154/product/index/sku/',
    'SKU_ERP_Product_Detail_Hide'   =>'http://120.78.243.154/product/purchase/sku/',
    //'ERP_URL'   =>'http://120.24.249.36',
    //'ERP_URL'   =>'http://119.23.218.76',
    'ERP_URL'   =>YII_DEBUG ? 'http://192.168.71.210:30080' : 'http://120.78.243.154',
    //拉取sku的erp服务器
    'SKU_ERP_URL' =>'http://120.24.249.36',
    'ERP_IMAGE_URL'   =>'http://images.yibainetwork.com',
    'CAIGOU_URL'   =>'http://caigou.yibainetwork.com',
    'before'=>'刘智勇',
    'just_now' =>'王瑞,刘伟,鄢胜,王维',
    //作用于签名
    'UEB_STOCK_KEYID' =>'yibai',
    'UEB_STOCK_TIMESTAMP'=>1800,

    // 仓库域名
    'wms_domain' => YII_DEBUG ? 'http://192.168.71.217':'http://wms.yibainetwork.com',
    //海外仓-中转仓
    'wms_abd' => 'http://hwc.yibainetwork.com',
];
