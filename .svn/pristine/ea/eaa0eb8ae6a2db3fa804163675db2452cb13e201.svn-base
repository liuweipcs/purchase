<?php

namespace app\controllers;

use app\api\v1\models\ApiPageCircle;
use app\models\PurchaseTacticsWarehouse;
use app\models\SkuSaleDetails;
use Yii;
use app\config\Vhelper;
use app\models\Product;
use app\models\ProductDescription;
use app\models\ProductProvider;
use app\models\PurchaseTacticsAbnormal;
use app\models\SkuSalesStatisticsTotalMrp;
use app\models\PurchaseSuggestMrp;
use app\models\Supplier;
use app\models\SupplierBuyer;
use app\models\PurchaseCategoryBind;
use app\models\PurchaseOrder;
use app\models\PurchaseOrderItems;
use app\models\PurchaseSuggestQuantity;
use app\models\SkuSalesStatisticsTotal;
use app\models\SkuTotaPage;
use app\models\StockOwes;
use app\models\SupplierNum;
use app\models\SupplierProductLine;
use app\config\Curd;
use app\models\BasicTactics;
use yii\base\Exception;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\Warehouse;
use app\models\SkuSalesStatistics;
use app\models\Stock;
use app\models\PurchaseSuggest;
use app\models\SupplierQuotes;
use app\models\ProductCategory;
use app\models\SkuStatisticsLog;
use app\models\SkuStatisticsLogSearch;
use app\models\User;
use app\models\PurchaseSkuSaleHandleRecord;
use app\models\PurchaseSkuSingleTacticMain;
use app\models\PurchaseSkuSingleTacticMainContent;
use app\models\WarehousePurchaseTactics;
use app\models\WarehouseMin;
use app\services\BaseServices;
use app\models\SegmentedTable;
use app\services\PurchaseSuggestQuantityServices;
use app\models\PurchaseTactics;
use app\models\DataControlConfigSearch;
use app\models\DataControlConfig;


/**
 * sku销量统计
 * @author Administrator
 *
 */
class SkuSalesStatisticsTotalMrpController extends BaseController
{
    public $log_data;//用来记录配库数据日志
    public $po_number; //生成的采购单编号
    public $num ='' ; //每次开始的值
    public $suggest_warehouse = ['FBA-WW','SZ_AA','ZDXNC','HW_XNC','LAZADA-XNC'];//定义运行采购建议的仓库

    /**
     * MRP运行仓库列表（计划任务）
     * @return array
     *
     * @user Jolon
     * @date 2018-11-12
     */
    public static function getWarehouseListMrp(){
        return DataControlConfig::getMrpWarehouseList();
    }

    public function actionIndex()
    {
        /**
         * 备货量测试
         */
        $sku = \Yii::$app->request->getQueryParam('sku');
        $warehouse_code = \Yii::$app->request->getQueryParam('warehouse_code','SZ_AA');

        if(empty($sku)){ exit('请输入SKU');}

        $skuMrp     = new SkuSalesStatisticsTotalMrp();
        $skuMrpInfo = $skuMrp::find()->where(['sku' => $sku, 'warehouse_code' => $warehouse_code])->asArray()->one();
        print_r($skuMrpInfo);
        $res        = $skuMrp->createLogic($sku, $warehouse_code, $skuMrpInfo);
        print_r($res);
        $res        = $skuMrp->getArrivalTimeStandardDeviation($sku, $warehouse_code);
        print_r($res);
        $res        = $skuMrp->getAvgArrivalTime($sku, $warehouse_code);
        print_r($res);
        $res        = $skuMrp->getNewSkuSuggestNum($sku, $warehouse_code);
        print_r($res);
        $res        = $skuMrp->calculateDailySalesBySku($sku, $warehouse_code);
        print_r($res);
        $res        = $skuMrp->calculateSDValueBySku($sku, $warehouse_code,null,isset($res['data']['average'])?$res['data']['average']:null);
        print_r($res);

        exit('测试完成');
    }
    /**
     * 计划任务运行 已有销量的数据
     */
    //http://www.purchase.com/sku-sales-statistics-total-mrp/purchasing-advice
    public function  actionPurchasingAdvice()
    {
        set_time_limit(0);
        ini_set("memory_limit","-1");

        $page_size = 1000; //??5000
        $count_1 =SkuSalesStatisticsTotalMrp::find()
            ->where(['is_new' => 0])
            ->andWhere(['is_success' => 1])
            ->andWhere(['is_suggest' => 0])
            ->count();
        if($count_1==0) {
            SkuTotaPage::updateAll(['total'=>0,'num'=>0],['id'=>1]);
            die('已全部生成');
        }

        $count = SkuSalesStatisticsTotalMrp::find()->count();
        $page_total_prev = SkuTotaPage::find()->select('total')->where(['id'=>1])->scalar();
        $page_total = ceil($count/$page_size);
        if($page_total_prev == 0 && $page_total > 0)  SkuTotaPage::updateAll(['total'=>$page_total],['id'=>1]);

        $num= SkuTotaPage::find()->select('num')->where(['id'=>1])->scalar();
        if($num>=$page_total){
            SkuTotaPage::updateAll(['num'=>0],['id'=>1]);
        } else {
            SkuTotaPage::updateAll(['num' => $num+1], ['id' => 1]);
        }

        if($count_1 <= $page_size){
            SkuTotaPage::updateAll(['total'=>0,'num'=>0],['id'=>1]);
            $num=0;
        }

        $mrp_warehouse_list = DataControlConfig::getMrpWarehouseList();
        $handle_data = SkuSalesStatisticsTotalMrp::find()
            ->where(['is_new' => 0])
            ->andWhere(['is_success' => 1])
            ->andWhere(['is_suggest' => 0])
            ->andWhere(['warehouse_code' =>$mrp_warehouse_list])
            ->offset(($num)*$page_size)
            ->limit($page_size)
            ->asArray()
            ->all();
        if (!empty($handle_data)) {
            $this->Batchhandledata($handle_data);
            Yii::info("恭喜你,运行成功");
            exit('恭喜你,运行成功');
        } else {
            SkuTotaPage::updateAll(['total'=>0,'num'=>0],['id'=>1]);
            exit('哈哈！并没有可运行的数据哦');
        }
    }

    /**
     * @param $handle_data
     * @param $is_export 是否是采购导入的需求，生成采购建议
     */
    protected function Batchhandledata($handle_data,$is_export=false)
    {
        set_time_limit(100);
        foreach ($handle_data as $key=>$value) {
            $value['sku']=addslashes($value['sku']);
            if($value['warehouse_code']=='FBA-WW'){
                $value['warehouse_code']='FBA_SZ_AA';//当销量数据是FBA销量汇总仓时，变成东莞FBA虚拟仓
            }
            $value = $this->getTypeMore($value);                    //获取波动上升，波动下降，持续上升，持续下降的类型； 只是销量的变化趋势，与补货模式无关
            $value = $this->calculateAvgSelling($value);            // 根据波动趋势，计算预计日销量
            $value = $this->getStock($value);                       //获取 由sku,仓库字段确定的库存量 增加四个库存参数
            $value = $this->getSupplier($value);                    //获取sku的供货商：如果有历史采购记录，就取历史采购记录的最后一条，如果没有历史采购记录，就暂时留空
            $value = $this->getSupplierCode($value);              //获取供应商编号
            $value = $this->getLatestQuote($value);               //获取最新的报价
            $value = $this->getProductCategory($value);            //获取产品分类ID
            
            $this->GeneratePurchaseList($value,$is_export);
        }
    }
    protected  function  getLatestQuote($data)
    {
        $rs = PurchaseOrderItems::find()->select('price,pur_number')->where(['sku'=>$data['sku']])->andWhere(['>','ctq',0])->orderBy('id desc')->asArray()->one();
        if(empty($rs)) return $data;

        $rs1 = PurchaseOrder::find()->select('supplier_code,supplier_name')->where(['pur_number'=>$rs['pur_number']])->andWhere(['>','id',4045])->andWhere(['not in','purchas_status',['1','2','4','10']])->orderBy('id desc')->asArray()->one();
        if(empty($rs1)) return $data;
        $data['price'] =$rs['price'];
        $data['supplier_code'] =$rs1['supplier_code'];
        $data['supplier_name'] =$rs1['supplier_name'];
        return $data;
    }
    /**
     * @param $data
     * @return array|null|\yii\db\ActiveRecord
     */
    protected function getSkuSales($data)
    {
        $result = SkuSalesStatisticsTotalMrp::find()->where(['sku'=>$data['sku'],'warehouse_code' => $data['warehouse_code']])->asArray()->one();
        $data['days_sales_3'] = $result['days_sales_3'];
        $data['days_sales_7'] = $result['days_sales_7'];
        $data['days_sales_15'] = $result['days_sales_15'];
        $data['days_sales_30'] = $result['days_sales_30'];
        return $data;
    }
    /**
     * 根据销售数据，归类销售过去的销售数据类型： 波动上升，波动下降，持续上升，持续下降等
     * @param $data
     * @return array|void
     */
    protected function getTypeMore($data)
    {
        if(!is_array($data)) return;
        $average_3  = $data['days_sales_3'] / 3;
        $average_7  = $data['days_sales_7'] / 7;
        $average_15 = $data['days_sales_15'] / 15;
        $average_30 = $data['days_sales_30'] / 30;

        $average_3_7 = $average_3+$average_7;
        $average_15_30 = $average_15+$average_30;
        if($average_3>$average_7 && $average_7>$average_15 && $average_15>$average_30) {
            $data['type'] = 'last_up';//持续上升
        } elseif ($average_3<$average_7 && $average_7<$average_15 && $average_15<$average_30){
            $data['type'] = 'last_down';//持续下降
        } elseif ($average_3_7>=$average_15_30){
            $data['type'] = 'wave_up';//波动上升
        } elseif ($average_3_7<$average_15_30){
            $data['type'] = 'wave_down';//波动下降
        } else {
            $data['type'] = 'abnormal';//判断出现异常
        }
        return $data;
    }
    /**
     * 获取sku，仓库里面的可用数量
     * @param $data
     */
    protected function getStock($data)
    {
        if($data['type']=='abnormal') return;
        $result = Stock::find()->select('left_stock, on_way_stock, stock,available_stock')->where(['sku'=>$data['sku'],'warehouse_code'=>$data['warehouse_code']])->asArray()->one();
        if ($result) {
            $avilable_stock          = $result['available_stock'] + $result['on_way_stock'];
            $data['stock_qty']       = $avilable_stock;
            $data['left_stock']      = $result['left_stock'];
            $data['on_way_stock']    = $result['on_way_stock'];
            $data['stock']           = $result['stock'];
            $data['available_stock'] = $result['available_stock'];      //可用库存，暂定为现有库存和在途库存
        } else {
            $avilable_stock          = 0;
            $data['stock_qty']       = 0;
            $data['left_stock']      = 0;
            $data['on_way_stock']    = 0;
            $data['stock']           = 0;
            $data['available_stock'] = $avilable_stock;      //可用库存，暂定为现有库存和在途库存
        }
        return $data;
    }
    protected function  getPurchaseUnpaid($data)
    {
        $pur_number = PurchaseOrderItems::find()->select('pur_number')->where(['sku'=>$data['sku']])->asArray()->all();
        $total =[];
        foreach($pur_number as $v) {
            $order  = PurchaseOrder::find()->where(['pur_number'=>$v['pur_number'],'purchas_status'=>[1,2]])->one();
            if(!empty($order)) $total[]= $order->pur_number;
        }

        $final =0  ;
        foreach($total as $v) {
            $num_single = PurchaseOrderItems::find()->select('qty,ctq')->where(['pur_number'=>$v,'sku'=>$data['sku']])->one();
            $finals = $num_single->ctq > 0 ? $num_single->ctq :$num_single->qty;
            $final +=$finals;
        }

        $data['on_way_stock']    = $data['on_way_stock']+$final;
        return $data;
    }
    //获取历史采购供货商
    protected function getSupplier($data)
    {
        //header("Content-type: text/html; charset=utf-8");
        $result                 = SupplierQuotes::find()->where(['product_sku'=>$data['sku']])->asArray()->one();
        $data['supplier_name']  = BaseServices::getSupplierName($result['suppliercode']) ? BaseServices::getSupplierName($result['suppliercode']) : '';
        $data['buyer']          = $result['default_buyer'];
        $data['price']          = $result['supplierprice'];
        //$data['buyer_id']         ='6';
        $data['replenish_type']      = '4';;
        $data['currency']            = $result['currency'] ? $result['currency'] : 'RMB';
        $data['supplier_settlement'] = '1';
        $data['ship_method']         = '2';
        return $data;
    }
    //获取供货商编号
    protected function getSupplierCode($data){
        //header("Content-type: text/html; charset=utf-8");
        $sealmodel             = New Curd();
        $pur_supplier_model    = New Supplier();
        $result                = $sealmodel->GetData($pur_supplier_model, 'supplier_code', 'one', " where supplier_name='" . $data['supplier_name'] . "'");
        $tem_code              = 'B' . rand('100000', '20000');
        $data['supplier_code'] = $result['supplier_code'] ? $result['supplier_code'] : $tem_code;
        return $data;
    }
    //获取产品分类ID
    protected function getProductCategory($data){
        $sealmodel              = New Curd();
        $product_model          = New Product();
        $product_cagetory_model = New ProductCategory();
        $result                 = $sealmodel->GetData($product_model, 'product_category_id', 'one', " where sku='" . $data['sku'] . "'");
        if (!empty($result['product_category_id'])) {
            $category_result             = $sealmodel->GetData($product_cagetory_model, 'category_cn_name', 'one', " where id=" . $result['product_category_id']);
        }
        
        $data['product_category_id'] = $result['product_category_id'] ? $result['product_category_id'] : '1';
        $data['category_cn_name']    = isset($category_result['category_cn_name']) ? $category_result['category_cn_name'] : '没有找到中文名字';
        return $data;
    }
    //计算单独设置采购策略时的采购数量
    protected function calculateSingleSkutacticQty($data)
    {
        $sealmodel                                      = New Curd();
        $purchase_sku_single_tactic_main_content_model  = New PurchaseSkuSingleTacticMainContent();
        $result = $sealmodel->GetData($purchase_sku_single_tactic_main_content_model,'safe_stock_days, resupply_span','one',"where single_tactic_main_id='".$data['single_tactic_main_id']."'");
        var_dump($result);
    }
    /**
     * 根据销售趋势，计算出平均销量
     * @param $data
     * @return mixed
     */
    protected function calculateAvgSelling($data)
    {
        //默认销量备货权重
        $defaultSalesReplenishWeight=
            [
                'wave_down'=>['days_3'=>0,'days_7'=>0.75,'days_15'=>0.15,'days_30'=>0.1],
                'wave_up'=>['days_3'=>0,'days_7'=>0.75,'days_15'=>0.15,'days_30'=>0.1],
                'last_up'=>['days_3'=>0,'days_7'=>0.75,'days_15'=>0.15,'days_30'=>0.1],
                'last_down'=>['days_3'=>0,'days_7'=>0.75,'days_15'=>0.15,'days_30'=>0.1],
            ];
        $sealmodel               = New Curd();
        $day_selling_parameter   = New BasicTactics();
        if(isset($data['warehouse_code'])){
            $result = $sealmodel->GetData($day_selling_parameter,'days_3, days_7, days_15, days_30','one',"where type ='".$data['type']."' and
             warehouse_code='".$data['warehouse_code']."'");
        }else{
            $result = ['days_3'=>0,'days_7'=>0.75,'days_15'=>0.15,'days_30'=>0.1];
        }
        if(empty($result)){
            $result = isset($defaultSalesReplenishWeight[$data['type']]) ? $defaultSalesReplenishWeight[$data['type']] : ['days_3'=>0,'days_7'=>0.75,'days_15'=>0.15,'days_30'=>0.1];
        }
        $avergage_selling        =  $data['days_sales_7']*$result['days_7']/7 + $data['days_sales_15']*$result['days_15']/15 + $data['days_sales_30']*$result['days_30']/30;
        $data['average_selling'] = number_format($avergage_selling, 2, '.', '');
        $data['weighted_3'] = $result['days_3'];
        $data['weighted_7'] = $result['days_7'];
        $data['weighted_15'] = $result['days_15'];
        $data['weighted_30'] = $result['days_30'];
        return $data;
    }
    /**
     * 生成采购建议单
     * @param $data
     */
    protected function GeneratePurchaseList($data,$is_export)
    {
        $sealmodel                     = New Curd();
        $purchase_suggest_model        = New PurchaseSuggestMrp();
        $warehouse_model               = New Warehouse();                   //仓库表
        $product_description_model     = New ProductDescription();          //产品信息描述表
        $product_supplier_model        = New ProductProvider();             //产品供货商编号表
        $product_supplier_detail_model = New Supplier();                    //产品供货商详表
        $product_category_model        = New ProductCategory();             //产品，所属目录表
        $product_model                 = New Product();                     //产品表模型
        $supplier_quotes_model         = New SupplierQuotes();              //供货商报价表
        $sku_resupply_log_model        = New SkuStatisticsLog();            //供货商补货策略记录
        //根据仓库编号，去仓库模型中获取仓库名称
        $warehousename = $sealmodel->GetData($warehouse_model, 'warehouse_name', 'one', "where warehouse_code='" . $data['warehouse_code'] . "'");
        //根据sku编号，去产品信息描述表中获取 所属语言code， 标题， 创建人， 创建时间
        $productinfo   = $sealmodel->GetData($product_description_model, 'language_code, title, create_user_id, create_time', 'one', "where sku = '" . $data['sku'] . "'");
        //根据sku编号，去 产品和供应商关系表中获取 供应商编码， 是否为默认供应商，是否免检， 是否推送
        $product_supply_info = $sealmodel->GetData($product_supplier_model, 'supplier_code, is_supplier, is_exemption,quotes_id, is_push', 'one', "where sku = '" . $data['sku'] . "' and is_supplier=1");
        //根据上面获取到的供应商编号， 从供货商表中获取 供应商名, 支付方式, 结算方式
        $product_supplier_detail_info_model = $sealmodel->GetData($product_supplier_detail_model, 'buyer,supplier_name, payment_method, supplier_settlement', 'one', "where supplier_code='" . $product_supply_info['supplier_code'] . "'");
        //根据sku编号，从产品表中获取产品所属的分类ID, 产品状态
        $product_category_relation_info = $sealmodel->GetData($product_model, 'product_category_id, product_status,uploadimgs', 'one', "where sku='" . $data['sku'] . "'");
        //获取供应商报价
        if (!empty($product_supply_info['quotes_id'])) {
            $supplier_quotes_info = $sealmodel->GetData($supplier_quotes_model, 'supplierprice, currency', 'one', "where id=" . $product_supply_info['quotes_id']);
        } else {
            $supplier_quotes_info = $sealmodel->GetData($supplier_quotes_model, 'supplierprice, currency', 'one', "where product_sku='" . $data['sku'] . "' and suppliercode='" . $data['supplier_code'] . "'");
        }

        if($data['warehouse_code']=='FBA_SZ_AA'){
            $supplierProductLine = SupplierProductLine::find()->select('first_product_line')->where(['supplier_code'=>$product_supply_info['supplier_code']])->scalar();
            $buyer=PurchaseCategoryBind::getBuyer($supplierProductLine);
            $buyer= $buyer ? $buyer : 'admin';
        }else{
            $buyer_s =SupplierBuyer::getBuyer($product_supply_info['supplier_code'],1);
            $buyer = !empty($buyer_s)?$buyer_s:'王开伟';
        }
        $buyer_id =User::findByUsername($buyer);

        /**
         * 备货量：采购建议数量
         */
        if ($is_export == true) {
            $qty = $data['qty'];
        } else {
            $skuMrp = new SkuSalesStatisticsTotalMrp();
            $skuMrpInfo = $skuMrp::find()->where(['sku'=>$data['sku'],'warehouse_code'=>$data['warehouse_code'],'is_new'=>0,'is_success'=>1,'is_suggest' => 0])->asArray()->one();
            $res = $skuMrp->createLogic($data['sku'],$data['warehouse_code'],$skuMrpInfo);
            if ($res['is_success'] == true) {
                $qty = $res['data']['qty'];
                $suggestion_logic = $res['data']['suggestion_logic'];
                $data['on_way_stock'] = $res['data']['on_way_stock'];//在途数量
                $data['available_stock'] = $res['data']['available_stock'];//可用数量
                //采购建议的欠货=可用+在途-缺貨
                $left_stock = $res['data']['available_stock'] + $res['data']['on_way_stock'] - $res['data']['left_stock'];
                $data['left_stock'] = ($left_stock>=0)?0:$left_stock;
                $data['average_selling'] = $res['data']['sales_avg'];
                $stock_logic_type = $res['data']['stock_logic_type']; //备货逻辑

                if($skuMrpInfo['is_stock_owes'] == 1){// 缺货SKU的采购数=实际缺货数量
                    if($left_stock >= 0){// 实际不缺货则不采购
                        if($data['warehouse_code']=='FBA_SZ_AA'){
                            SkuSalesStatisticsTotalMrp::updateAll(['is_suggest'=>1],['sku'=>$data['sku'],'warehouse_code'=>'FBA-WW']);
                        }else{
                            SkuSalesStatisticsTotalMrp::updateAll(['is_suggest'=>1],['sku'=>$data['sku'],'warehouse_code'=>$data['warehouse_code']]);
                        }
                        return false;
                    }else{
                        $qty = abs($left_stock);
                    }
                }
            } else {
                if($data['warehouse_code']=='FBA_SZ_AA'){
                    SkuSalesStatisticsTotalMrp::updateAll(['is_suggest'=>1],['sku'=>$data['sku'],'warehouse_code'=>'FBA-WW']);
                }else{
                    SkuSalesStatisticsTotalMrp::updateAll(['is_suggest'=>1],['sku'=>$data['sku'],'warehouse_code'=>$data['warehouse_code']]);
                }
                $params = ['sku'=>$data['sku'],'warehouse_code'=>$data['warehouse_code'],'supplier_code'=>$product_supply_info['supplier_code'],'buyer'=>$buyer,'reason'=>is_array($res['data'])?'未知原因':$res['data']];
                PurchaseTacticsAbnormal::saveAbnomal($params);
                return false; //数据异常
            }
        }

        $data['resupply_sku_quantity'] = $qty;
        $warehouse_type_domestic = DataControlConfig::getMrpWarehouseStockList();
        $add_data=[
            'warehouse_code'      => $data['warehouse_code'],
            'warehouse_name'      => $warehousename['warehouse_name'],
            'sku'                 => $data['sku'],
            'name'                => !empty($data['name']) ?  addslashes($data['name']) : addslashes($productinfo['title']),
            'supplier_code'       => !empty($product_supply_info['supplier_code']) ? $product_supply_info['supplier_code'] : $data['supplier_code'],
            'supplier_name'       => !empty($product_supplier_detail_info_model['supplier_name']) ?  $product_supplier_detail_info_model['supplier_name']:$data['supplier_name'],
            'buyer'               => $buyer,
            'buyer_id'            => !empty($buyer_id->id)?$buyer_id->id:'123',
            'replenish_type'      => isset($data['replenish_type'])?$data['replenish_type']:1,
            'qty'                 => $qty,
            'price'               => !empty($supplier_quotes_info['supplierprice']) ? $supplier_quotes_info['supplierprice'] : ($data['price'] ? $data['price'] :'0'),
            'currency'            => $data['currency'] ? $data['currency'] :  $supplier_quotes_info['currency'],
            'payment_method'      => $product_supplier_detail_info_model['payment_method']?$product_supplier_detail_info_model['payment_method']:'1',    //data 里面没有
            'supplier_settlement' => $data['supplier_settlement'] ? $data['supplier_settlement'] :  $product_supplier_detail_info_model['supplier_settlement'],
            'ship_method'         => $data['ship_method'],
            'is_purchase'         => isset($data['resupply_sku_quantity']) ? 'Y' : 'N',
            'created_at'          => date('Y-m-d H:i:s'),
            'creator'             => $buyer,
            'product_category_id' => $data['product_category_id'],
            'category_cn_name'    => $data['category_cn_name'],
            'on_way_stock'        => !empty($data['on_way_stock'])?$data['on_way_stock']:'0',  //在途
            'available_stock'     => !empty($data['available_stock'])?$data['available_stock']:'0',   //可用
            'stock'               => $data['stock'],
            'left_stock'          => $data['left_stock'],
            'product_img'         => $product_category_relation_info['uploadimgs'],
            'days_sales_3'        => isset($data['days_sales_3'])?$data['days_sales_3']:0,
            'days_sales_7'        => isset($data['days_sales_7'])?$data['days_sales_7']:0,
            'days_sales_15'       => isset($data['days_sales_15'])?$data['days_sales_15']:0,
            'days_sales_30'       => isset($data['days_sales_30'])?$data['days_sales_30']:0,
            'sales_avg'           => isset($data['average_selling'])?$data['average_selling']:0,
            'type'                => isset($data['type'])?$data['type']:'last_down',
            'weighted_3'          => isset($data['weighted_3'])?$data['weighted_3']:0,
            'weighted_7'          => isset($data['weighted_7'])?$data['weighted_7']:0,
            'weighted_15'         => isset($data['weighted_15'])?$data['weighted_15']:0,
            'weighted_30'         => isset($data['weighted_30'])?$data['weighted_30']:0,
            'weighted_60'         => isset($data['weighted_60'])?$data['weighted_60']:0,
            'weighted_90'         => isset($data['weighted_90'])?$data['weighted_90']:0,
            'purchase_type'       => in_array($data['warehouse_code'],$warehouse_type_domestic) ? 1 : ($data['warehouse_code']=='FBA_SZ_AA' ? 3 : 1),
            'qty_13'              => isset($data['qty_13'])?$data['qty_13']:$qty-(2*$data['average_selling']),
            'state'               => 0,
            'product_status'      => isset($product_category_relation_info['product_status'])?$product_category_relation_info['product_status']:'100',
            'safe_delivery'       => isset($data['safe_delivery'])?$data['safe_delivery']:12,
            'suggestion_logic'       => isset($suggestion_logic)?$suggestion_logic:'',
            'stock_logic_type'       => isset($stock_logic_type)?$stock_logic_type:0, //备货逻辑
        ];

        //设置五分钟缓存去重
        if(!Yii::$app->cache->add('MRP_'.$data['sku'].'_'.$data['warehouse_code'],date('Y-m-d H:i:s',time()),300)){
            return;
        }
        if ($is_export == true) {
            #采购导入的
            if(empty($data['warehouse_code'])) return;
            $sealmodel->Add($purchase_suggest_model, $add_data);
        } else {
            $rs=$sealmodel->GetData($purchase_suggest_model,'id','one',"where sku='".$data['sku']."' and purchase_type in (1,3) and warehouse_code='".$data['warehouse_code']."'");

            if($rs){
                $sealmodel->UpData($purchase_suggest_model, $add_data, "where sku='".$data['sku']."' and purchase_type in (1,3) and warehouse_code='".$data['warehouse_code']."'");
            } else {
                if(empty($data['warehouse_code'])) return;
                $sealmodel->Add($purchase_suggest_model, $add_data);
                //增加日志
                $temp                   = [];
                $temp['sku']            = $data['sku'];
                $temp['warehouse_code'] = $data['warehouse_code'];
                $temp['po_number']      = 'GN'.date('Ymd').mt_rand();
                $temp['created_at']     = date('Y-m-d H:i:s');
                $temp['status']         = isset($data['resupply_sku_quantity']) ? 'success' : 'failure';
                $temp['note']           = "补货成功，本次补货数量{$data['resupply_sku_quantity']}";
                $temp['creator']        = $buyer;
                $sealmodel->Add($sku_resupply_log_model, $temp);
            }

            if($data['warehouse_code']=='FBA_SZ_AA'){
                SkuSalesStatisticsTotalMrp::updateAll(['is_suggest'=>1],['sku'=>$data['sku'],'warehouse_code'=>'FBA-WW']);
            }else{
                SkuSalesStatisticsTotalMrp::updateAll(['is_suggest'=>1],['sku'=>$data['sku'],'warehouse_code'=>$data['warehouse_code']]);
            }
            //修改统计为1则不加入运算
            $model_stock = Stock::find()->where(['sku'=>$data['sku'],'warehouse_code'=>$data['warehouse_code']])->one();
            if ($model_stock) {
                $model_stock->is_suggest=1;
                $model_stock->save(false);
            }
        }
    }
    /**
     * 采购系统导入的需求
     * @desc 采购系统导入的需求数 汇总到 采购建议 记录中（有记录就累加数量，没有就插入新记录），同时更新需求状态为已生成建议
     */
    protected function suggestQuantity($suggest_quantity, $he_warehouse = false, $main_warehouse=['SZ_AA'])
    {
        $start_time = date('Y-m-d 00:00:00',time());
        $end_time = date('Y-m-d 23:59:59',time());
        $count = count($suggest_quantity);//计算该数组的元素有多少个
        if (empty($suggest_quantity)) return '采购系统导入需求--没有未使用的采购需求！！';
        foreach ($suggest_quantity as $k=>$v) {
            $qty = $v['purchase_quantity']+$v['activity_stock']+$v['routine_stock']; //导入数量+活动数量+常规数量
            if ( !empty($he_warehouse) && in_array($v['purchase_warehouse'], $he_warehouse) ) {
                # 将FBL虚拟仓(LAZADA-XNC)、执御虚拟仓及易佰东莞仓的采购数量进行汇总(汇总到易佰东莞仓库)
                $suggest = PurchaseSuggestMrp::find()
                    ->where(['sku'=> $v['sku']])
                    ->andWhere(['warehouse_code'=>$main_warehouse[0]])
                    ->andWhere(['purchase_type'=>1])
                    ->andWhere(['between','created_at', $start_time, $end_time])
                    ->exists();
                $v['purchase_warehouse'] = $main_warehouse[0];
                if (!empty($suggest)) { //如果采购建议中存在，就将采购数量相加
                    PurchaseSuggestMrp::updateAllCounters(['qty' => $qty], ['and', ['sku'=> $v['sku']], ['warehouse_code'=>$v['purchase_warehouse']]]);
                } else {
                    $handle_data[$k]['sku'] =  $v['sku'];
                    $handle_data[$k]['days_sales_3'] =  0;
                    $handle_data[$k]['days_sales_7'] =  0;
                    $handle_data[$k]['days_sales_15'] =  0;
                    $handle_data[$k]['days_sales_30'] =  0;
                    $handle_data[$k]['days_sales_60'] =  0;
                    $handle_data[$k]['days_sales_90'] =  0;
                    $handle_data[$k]['statistics_date'] =  null;
                    $handle_data[$k]['create_time'] =  null;
                    $handle_data[$k]['update_time'] =  null;
                    $handle_data[$k]['is_suggest'] =  0;
                    $handle_data[$k]['is_sum'] =  0;
                    $handle_data[$k]['warehouse_code'] =  $v['purchase_warehouse'];
                    $handle_data[$k]['warehouse_id'] =  null; //仓库id
                    $handle_data[$k]['platform_code'] =  $v['platform_number'];
                    $handle_data[$k]['resupply_sku_quantity'] =  $qty;
                    $handle_data[$k]['suggest_status'] =  2;
                    $handle_data[$k]['qty'] =  $qty;
                    $this->Batchhandledata($handle_data,true);
                    unset($handle_data);
                }
            } else {
                $suggest = PurchaseSuggestMrp::find()
                    ->where(['sku'=> $v['sku']])
                    ->andWhere(['warehouse_code'=>$v['purchase_warehouse']])
                    ->andWhere(['purchase_type'=>1])
                    ->andWhere(['between','created_at', $start_time, $end_time])
                    ->exists();

                if (!empty($suggest)) { //如果采购建议中存在，就将采购数量相加
                    PurchaseSuggestMrp::updateAllCounters(['qty' => $qty], ['and', ['sku'=> $v['sku']], ['warehouse_code'=>$v['purchase_warehouse']]]);
                } else {
                    $handle_data[$k]['sku'] =  $v['sku'];
                    $handle_data[$k]['days_sales_3'] =  0;
                    $handle_data[$k]['days_sales_7'] =  0;
                    $handle_data[$k]['days_sales_15'] =  0;
                    $handle_data[$k]['days_sales_30'] =  0;
                    $handle_data[$k]['days_sales_60'] =  0;
                    $handle_data[$k]['days_sales_90'] =  0;
                    $handle_data[$k]['statistics_date'] =  null;
                    $handle_data[$k]['create_time'] =  null;
                    $handle_data[$k]['update_time'] =  null;
                    $handle_data[$k]['is_suggest'] =  0;
                    $handle_data[$k]['is_sum'] =  0;
                    $handle_data[$k]['warehouse_code'] =  $v['purchase_warehouse'];
                    $handle_data[$k]['warehouse_id'] =  null; //仓库id
                    $handle_data[$k]['platform_code'] =  $v['platform_number'];
                    $handle_data[$k]['resupply_sku_quantity'] =  $qty;
                    $handle_data[$k]['suggest_status'] =  2;
                    $handle_data[$k]['qty'] =  $qty;
                    $this->Batchhandledata($handle_data,true);
                    unset($handle_data);
                }
            }
            PurchaseSuggestQuantity::updateAll(['suggest_status'=>2],['id'=>$v['id']]);
            if ($k == $count-1)  return '采购系统导入需求--已运行完成！！';
        }
    }
    /**
     * 仓库推送过来的需求
     * @desc 仓库推送过来的需求数 汇总到 采购建议 记录中（有记录就累加数量，没有就插入新记录），同时更新需求状态为已生成建议
     */
    public function warehouseQuantity()
    {
        $start_time = date('Y-m-d 00:00:00',time());
        $end_time = date('Y-m-d 23:59:59',time());

        //仓库导入的需求
        $warehouse_quantity = PurchaseSuggestQuantity::find()
            ->select('id, sku, purchase_warehouse, purchase_quantity, routine_stock, activity_stock, platform_number')
            ->where(['suggest_status'=>1])
            ->andWhere(['purchase_type'=>5])
            ->asArray()
            ->all();

        $count = count($warehouse_quantity);//计算该数组的元素有多少个
        if (empty($warehouse_quantity))  return '仓库导入需求--没有未使用的采购需求！！';
        foreach ($warehouse_quantity as $k=>$v) {
            $suggest = PurchaseSuggestMrp::find()
                ->where(['sku'=> $v['sku']])
                ->andWhere(['warehouse_code'=>$v['purchase_warehouse']])
                ->andWhere(['purchase_type'=>1])
                ->andWhere(['between','created_at', $start_time,$end_time])
                ->exists();

            $qty = $v['purchase_quantity']+$v['activity_stock']+$v['routine_stock']; //导入数量+活动数量+常规数量
            if (!empty($suggest)) { //如果采购建议中存在，就将采购数量相加
                PurchaseSuggestMrp::updateAllCounters(['qty' => $qty], ['and', ['sku'=> $v['sku']], ['warehouse_code'=>$v['purchase_warehouse']]]);
            } else {
                $handle_data[$k]['sku'] =  $v['sku'];
                $handle_data[$k]['days_sales_3'] =  0;
                $handle_data[$k]['days_sales_7'] =  0;
                $handle_data[$k]['days_sales_15'] =  0;
                $handle_data[$k]['days_sales_30'] =  0;
                $handle_data[$k]['days_sales_60'] =  0;
                $handle_data[$k]['days_sales_90'] =  0;
                $handle_data[$k]['statistics_date'] =  null;
                $handle_data[$k]['create_time'] =  null;
                $handle_data[$k]['update_time'] =  null;
                $handle_data[$k]['is_suggest'] =  0;
                $handle_data[$k]['is_sum'] =  0;
                $handle_data[$k]['warehouse_code'] =  $v['purchase_warehouse'];
                $handle_data[$k]['warehouse_id'] =  null; //仓库id
                $handle_data[$k]['platform_code'] =  $v['platform_number'];
                $handle_data[$k]['resupply_sku_quantity'] =  $qty;
                $handle_data[$k]['suggest_status'] =  2;
                $handle_data[$k]['qty'] =  $v['purchase_quantity'];
                $this->Batchhandledata($handle_data,true);
                unset($handle_data);
            }
            if ($k == $count-1) return '仓库导入需求--已运行完成！！';
        }
    }
    /**
     * 执行计划任务   如果采购建议中有匹配的数据，采购数量就增加，否则 新增采购建议
     * http://caigou.yibainetwork.com/sku-sales-statistics-total-mrp/purchasing-advice-owe
     */
    public function  actionPurchasingAdviceOwe()
    {
        set_time_limit(0);
        // ini_set("memory_limit","-1");
        $page_size = 1000;
        $start_time = date('Y-m-d 00:00:00',time()-86400);
        $end_time = date('Y-m-d 23:59:59',time()-86400);

        //主仓
        $main_warehouse = DataControlConfig::getMrpWarehouseMain();
        //合仓
        $he_warehouse = DataControlConfig::getMrpWarehouseHe();


        /**
         * 采购需求导入
         * @var [type]
         */
        $count_2 = PurchaseSuggestQuantity::find()
            ->where(['suggest_status'=>1])
            ->andWhere(['purchase_type'=>1])
            ->andWhere(['between','create_time', $start_time, $end_time])
            ->count();

        if($count_2==0) {
            SkuTotaPage::updateAll(['total'=>0,'num'=>0],['id'=>2]);
            die('已全部生成');
        } else {
            $count = PurchaseSuggestQuantity::find()
                ->andWhere(['between','create_time', $start_time, $end_time])
                ->count();
            $page_total_prev = SkuTotaPage::find()->select('total')->where(['id'=>2])->scalar();
            $page_total = ceil($count/$page_size);

            if($page_total_prev == 0 && $page_total > 0) {
                SkuTotaPage::updateAll(['total'=>$page_total],['id'=>2]);
            }

            $num= SkuTotaPage::find()->select('num')->where(['id'=>2])->scalar();
            if($num>=$page_total) {
                SkuTotaPage::updateAll(['num'=>0],['id'=>2]);
            } else {
                SkuTotaPage::updateAll(['num' => $num+1], ['id' => 2]);
            }

            if($count_2 <= $page_size){
                SkuTotaPage::updateAll(['total'=>0,'num'=>0],['id'=>2]);
                $num=0;
            }
            if ($num == 0) {
                # 第一次就运行仓库导入
                $warehouse_res = $this->warehouseQuantity(); //仓库导入
            }

            $offset = $num*$page_size;
            $suggest_quantity = PurchaseSuggestQuantity::find()
                ->select('id, sku, purchase_warehouse, purchase_quantity, routine_stock, activity_stock, platform_number')
                ->where(['suggest_status'=>1])
                ->andWhere(['purchase_type'=>1])
                ->andWhere(['between','create_time', $start_time, $end_time])
                ->offset($offset)
                ->limit($page_size)
                ->asArray()
                ->all();

            if (!empty($suggest_quantity)) {
                $suggest_res = $this->suggestQuantity($suggest_quantity, $he_warehouse, $main_warehouse);
                Yii::info("恭喜你,运行成功"); exit('恭喜你,运行成功');
            } else {
                SkuTotaPage::updateAll(['total'=>0,'num'=>0],['id'=>2]);
                exit('哈哈！并没有可运行的数据哦');
            }
        }
    }

    //多请求进行销量汇总
    public function actionCountTotal(){
        set_time_limit(0);
        $delete_date = date('Y-m-d 00:00:00');
        $warehouse_code = \Yii::$app->request->getQueryParam('warehouse_code','SZ_AA');
        $sockSize = \Yii::$app->request->getQueryParam('sockSize',10);

        // 根据仓库列表运行多仓库MRP @user Jolon @date 2018-11-12
        $warehouse_code_list = self::getWarehouseListMrp();
        if($warehouse_code_list){
            $pageBeign = ApiPageCircle::find()
                ->select('page')
                ->where(['type'=>'SKU_TOTAL_COUNT_PAGE_SALES'])
                ->andWhere(['>','create_time',date('Y-m-d 00:00:00')])
                ->orderBy('id DESC')->scalar();
            if(!$pageBeign){
                $pageBeign=0;
                SkuSalesStatisticsTotalMrp::deleteAll();// 删除今天之前的数据
            }

            ApiPageCircle::insertNewPage($pageBeign+$sockSize,'SKU_TOTAL_COUNT_PAGE_SALES');
            for ($i=$pageBeign;$i<$pageBeign+$sockSize;$i++){
                $url =Yii::$app->params['CAIGOU_URL'].'/sku-sales-statistics-total-mrp/get-sku-total';
                $result=Vhelper::throwTheader($url,['page'=>$i]);
                sleep(5);
            }
        }else{
            echo '请配置运行仓库列表';
            exit;
        }
    }

    public static function rangeArray(){
        $rangeArray = [
            3=>'days_sales_3',
            7=>'days_sales_7',
            15=>'days_sales_15',
            30=>'days_sales_30',
//            60=>'days_sales_60',
//            90=>'days_sales_90',
        ];

        return $rangeArray;
    }

    //汇总销量
    public function actionGetSkuTotal(){
        set_time_limit(1200);
        $date = \Yii::$app->request->getQueryParam('date',date('Y-m-d',time()));
        $limit = \Yii::$app->request->getQueryParam('limit',1500);
        $page = \Yii::$app->request->getQueryParam('page',0);
        $warehouse_code_list = \Yii::$app->request->getQueryParam('warehouse_code');
        if (empty($warehouse_code_list)) {
            // 根据仓库列表运行多仓库MRP @user Jolon @date 2018-11-12
            $warehouse_code_list = self::getWarehouseListMrp();
        } else {
            $warehouse_code_list = explode(',',$warehouse_code_list);
        }

        //合仓
        $mrp_warehouse_he = DataControlConfig::getMrpWarehouseHe();
        if (!empty($mrp_warehouse_he)) {
            $warehouse_code = array_unique(array_merge($warehouse_code_list, $mrp_warehouse_he));
        }

        //配置多少天的销量汇总保存在哪个字段
        $rangeArray = self::rangeArray();
        $save=SkuSalesStatisticsTotalMrp::countSkuSalesByFormat($date,$page,$limit,30,$rangeArray,$warehouse_code);
        if(!$save){
            exit('销量汇总完成');
        }else{
            ApiPageCircle::insertNewPage($page+1,'SALES_COUNT_PAGE_SALES');
        }
    }

    //多请求进行数据计算
    public function actionCountSkuInfo(){
        set_time_limit(1200);
        $warehouse_code = \Yii::$app->request->getQueryParam('warehouse_code','SZ_AA');
        $limit = \Yii::$app->request->getQueryParam('limit','500');
        $sockSize = \Yii::$app->request->getQueryParam('sockSize',10);

        // 根据仓库列表运行多仓库MRP @user Jolon @date 2018-11-12
        $warehouse_code_list = self::getWarehouseListMrp();
        if($warehouse_code_list){
            foreach($warehouse_code_list as $warehouse_code ){
                $pageBeign = ApiPageCircle::find()
                    ->select('page')
                    ->where(['type'=>'SKU_INFO_COUNT_TOTAL_PAGE'.$warehouse_code])
                    ->andWhere(['>','create_time',date('Y-m-d 00:00:00')])
                    ->orderBy('id DESC')->scalar();
                if(!$pageBeign){
                    $pageBeign=0;
                }
                ApiPageCircle::insertNewPage($pageBeign+$sockSize,'SKU_INFO_COUNT_TOTAL_PAGE'.$warehouse_code);
                for ($i=$pageBeign;$i<$pageBeign+$sockSize;$i++){
                    //$url ='http://local.caigou.cn/sku-sales-statistics-total-mrp/get-sku-info';
                    $url =Yii::$app->params['CAIGOU_URL'].'/sku-sales-statistics-total-mrp/get-sku-info';
                    Vhelper::throwTheader($url,['page'=>$i,'warehouse_code'=>$warehouse_code,'limit'=>$limit]);
                    sleep(10);
                }
            }
        }else{
            echo '请配置运行仓库列表';
            exit;
        }
    }

    //获取销量汇总后的基础数据 是否新品，交期标准差，日均销量，销量标准200条95秒 线上十二秒
    public function actionGetSkuInfo(){
        set_time_limit(1200);
        $beginTime = microtime(true);
        $limit = \Yii::$app->request->getQueryParam('limit',200);
        $warehouse_code = \Yii::$app->request->getQueryParam('warehouse_code','SZ_AA');
        $is_stock_owes = \Yii::$app->request->getQueryParam('is_stock_owes',0);
        $page = \Yii::$app->request->getQueryParam('page',0);
        $countDatas = SkuSalesStatisticsTotalMrp::find()
            ->offset($page*$limit)
            ->limit($limit)
            ->where(['warehouse_code'=>$warehouse_code])
            ->andWhere(['is_stock_owes' => $is_stock_owes])
            ->all();
        if(empty($countDatas)){
            exit('没有数据需要计算');
        }
        $skuSalesStatisticsTotalMrp = new SkuSalesStatisticsTotalMrp();
        foreach ($countDatas as $data){
            if($data['is_success'] == 1) continue;// 跑成功的不要再跑了
            $is_new = SkuSaleDetails::find()->select('is_new')->where(['sku'=>$data->sku,
                'warehouse_code'=>$data->warehouse_code,'sale_date'=>date('Y-m-d',time()-86400)])->scalar();
            $is_new = $is_new ? $is_new :0;
            $data->is_new = $is_new;
            $arrivalTimeSDInfo = SkuSalesStatisticsTotalMrp::getArrivalTimeStandardDeviation($data->sku,$data->warehouse_code);
            $arrivalTimeSD = isset($arrivalTimeSDInfo['is_success'])&&$arrivalTimeSDInfo['is_success'] ? $arrivalTimeSDInfo['arrival_time_standard_deviation'] : 0;
            if(!isset($arrivalTimeSDInfo['is_success'])||!$arrivalTimeSDInfo['is_success']){
                PurchaseTacticsAbnormal::saveAbnomal(['sku'=>$data->sku,'warehouse_code'=>$data->warehouse_code,'supplier_code'=>'','buyer'=>'','reason'=>$arrivalTimeSDInfo['message']]);
                continue;
            }
            $data->arrival_time_sd = $arrivalTimeSD;
            $salesAvgInfo = $skuSalesStatisticsTotalMrp->calculateDailySalesBySku($data->sku,$data->warehouse_code);
            if(!isset($salesAvgInfo['is_success'])||!$salesAvgInfo['is_success']){
                PurchaseTacticsAbnormal::saveAbnomal(['sku'=>$data->sku,'warehouse_code'=>$data->warehouse_code,'supplier_code'=>'','buyer'=>'','reason'=>$salesAvgInfo['message']]);
                continue;
            }
            $arrivalTimeAvgInfo = SkuSalesStatisticsTotalMrp::getAvgArrivalTime($data->sku,$data->warehouse_code);
            if(!isset($arrivalTimeAvgInfo['is_success']) || !$salesAvgInfo['is_success']){
                PurchaseTacticsAbnormal::saveAbnomal(['sku'=>$data->sku,'warehouse_code'=>$data->warehouse_code,'supplier_code'=>'','buyer'=>'','reason'=>$salesAvgInfo['message']]);
                continue;
            }
            $data->arrival_time_avg = $arrivalTimeAvgInfo['avg_arrival_time'];
            $avgSales = isset($salesAvgInfo['is_success'])&&$salesAvgInfo['is_success'] ? $salesAvgInfo['data']['average'] : 0;
            $data->avg_sales = $avgSales;
            $salesSDInfo = $skuSalesStatisticsTotalMrp->calculateSDValueBySku($data->sku,$data->warehouse_code);
            if(!isset($salesSDInfo['is_success'])||!$salesSDInfo['is_success']){
                PurchaseTacticsAbnormal::saveAbnomal(['sku'=>$data->sku,'warehouse_code'=>$data->warehouse_code,'supplier_code'=>'','buyer'=>'','reason'=>$salesSDInfo['message']]);
                continue;
            }
            $salesSD = isset($salesSDInfo['is_success'])&&$salesSDInfo['is_success'] ? $salesSDInfo['data']['sale_value_SD'] : 0;
            $data->sales_sd = $salesSD;
            $data->is_success=1;
            if($data->save()==false){
                PurchaseTacticsAbnormal::saveAbnomal(['sku'=>$data->sku,'warehouse_code'=>$data->warehouse_code,'supplier_code'=>'','buyer'=>'','reason'=>'统计数据写入失败']);
            }
        }
        ApiPageCircle::insertNewPage($page+1,'SKU_INFO_COUNT_PAGE'.$warehouse_code);
        $time = microtime(true)-$beginTime;
        echo $time.'秒';
    }

    /**
     * // 插入缺货的SKU 但是不在 SKU MRP中（这部分SKU也要跑采购建议）
     */
    public function actionStockOwesInsertMrp()
    {
        set_time_limit(1200);
        $beginTime = microtime(true);
        // 运行缺货数据
        SkuSalesStatisticsTotalMrp::stockOwesInsertMrp(self::rangeArray());// 插入缺货的SKU 但是不在 SKU MRP中（这部分SKU也要跑采购建议）
        self::countSkuInfoForStockOwes();
        $time = microtime(true)-$beginTime;
        echo $time.'秒';
    }

    /**
     * 缺货数量 SKU 生成采购建议基础数据
     * 获取销量汇总后的基础数据 是否新品，交期标准差，日均销量，销量标准等
     */
    public static function countSkuInfoForStockOwes(){
        set_time_limit(1200);
        $limit      = \Yii::$app->request->getQueryParam('limit',50000);
        $sockSize   = \Yii::$app->request->getQueryParam('sockSize',1);

        $warehouse_code_list = self::getWarehouseListMrp();
        if($warehouse_code_list){
            foreach($warehouse_code_list as $warehouse_code ){
                for ($i=0;$i<$sockSize;$i++){
                    $url =Yii::$app->params['CAIGOU_URL'].'/sku-sales-statistics-total-mrp/get-sku-info';
                    Vhelper::throwTheader($url,['page'=>$i,'warehouse_code'=>$warehouse_code,'limit'=>$limit,'is_stock_owes' => 1]);
                    sleep(10);
                }
            }
        }else{
            echo '请配置运行仓库列表';
            exit;
        }
    }

    /**
     * 新品备货逻辑代码
     */
    public  function actionGetProductStocking(){
        $limit = \Yii::$app->request->getQueryParam('limit',100);
        $warehouse_code = \Yii::$app->request->getQueryParam('warehouse_code','SZ_AA');

        // 根据仓库列表运行多仓库MRP @user Jolon @date 2018-11-12
        $warehouse_code_list = self::getWarehouseListMrp();
        if($warehouse_code_list){
            foreach($warehouse_code_list as $warehouse_code ){
                $page = ApiPageCircle::find()
                    ->select('page')
                    ->where(['>','create_time',date('Y-m-d 00:00:00',time())])
                    ->andWhere(['type'=>'MRP_NEW_SKU'.$warehouse_code])
                    ->orderBy('id DESC')->scalar();
                if(!$page){
                    $page =0;
                }
                ApiPageCircle::insertNewPage($page+1,'MRP_NEW_SKU'.$warehouse_code);
                //查询新品备货的数据
                $newProductData = SkuSalesStatisticsTotalMrp::find()
                    ->select('sku,warehouse_code')
                    ->where(['is_new'=>1])
                    ->andWhere(['is_success'=>1])
                    ->andWhere(['warehouse_code'=>$warehouse_code])
                    ->offset($limit*$page)
                    ->limit($limit)
                    ->asArray()->all();
                foreach ($newProductData as $productData){
                    $data['sku'] = $productData['sku'];
                    $data['warehouse_code'] = $productData['warehouse_code'];
                    //获取sku绑定供应商信息（suplier_code,supplier_name，payment_method，supplier_settlement）
                    $supplierData= PurchaseSuggestMrp::getProductSupplier($data);
                    if(isset($supplierData['is_success'])&&$supplierData['is_success']){
                        $data=$supplierData['data'];
                    }else{
                        //保存异常并跳过
                        PurchaseTacticsAbnormal::saveAbnomal(['sku'=>$productData['sku'],'warehouse_code'=>$productData['warehouse_code'],'supplier_code'=>'','buyer'=>'','reason'=>'sku供应商信息获取失败']);
                        continue;
                    }
                    //获取sku采购员信息（buyer,buyer_id）
                    $data = PurchaseSuggestMrp::getProductBuyer($data);
                    $data['warehouse_name'] = Warehouse::find()->select('warehouse_name')->where(['warehouse_code'=>$productData['warehouse_code']])->scalar();
                    //根据sku获取sku分类id分类名称，图片地址
                    $data = PurchaseSuggestMrp::getProductInfo($data);
                    $configInfo = PurchaseTacticsWarehouse::find()
                        ->select('pt.single_price,pt.inventory_holdings')
                        ->alias('t')
                        ->leftJoin(PurchaseTactics::tableName().' pt','t.tactics_id=pt.id')
                        ->where(['t.warehouse_code'=>$productData['warehouse_code'],'pt.status'=>1])
                        ->asArray()->orderBy('t.id DESC')->one();
                    if(empty($configInfo)){
                        //'sku'=>sku,'name'=>name,'warehouse_code'=>warehouse_code,'supplier_code'=>supplier_code,'buyer'=>buyer,'reason'=>reason
                        PurchaseTacticsAbnormal::saveAbnomal(['sku'=>$productData['sku'],'warehouse_code'=>$productData['warehouse_code'],
                            'supplier_code'=>$data['supplier_code'],'buyer'=>$data['buyer'],'reason'=>'仓库备货数量配置信息获取失败']);
                        continue;
                    }
                    $priceLimit = $configInfo['single_price'];
                    $data['new_price_limit'] = $priceLimit;
                    $stockLimit = $configInfo['inventory_holdings'];
                    $data['new_stock_hold'] = $stockLimit;
                    //获取备货数量
                    $parperNumData = SkuSalesStatisticsTotalMrp::getNewSkuSuggestNum($productData['sku'],$productData['warehouse_code'],null,$priceLimit,$stockLimit,null,1);
                    if(isset($parperNumData['is_success'])&&$parperNumData['is_success']){
                        $data['qty'] = isset($parperNumData['suggest_num']) ? $parperNumData['suggest_num'] :0;
                        $data['price'] = isset($parperNumData['price']) ? $parperNumData['price'] :0;
                        $data['left_stock'] = isset($parperNumData['left_stock']) ? $parperNumData['left_stock'] :0;
                    }else{
                        //保存异常并跳过
                        PurchaseTacticsAbnormal::saveAbnomal(['sku'=>$productData['sku'],'warehouse_code'=>$productData['warehouse_code'],
                            'supplier_code'=>$data['supplier_code'],'buyer'=>$data['buyer'],'reason'=>$parperNumData['message']]);
                        continue;
                    }
                    $model = PurchaseSuggestMrp::find()->where(['sku'=>$productData['sku'],'warehouse_code'=>$productData['warehouse_code']])->one();
                    if(!$model){
                        $model = new PurchaseSuggestMrp();
                    }
                    $saveStatus =PurchaseSuggestMrp::saveOne($model,$data);
                    if(!$saveStatus){
                        //保存异常
                        PurchaseTacticsAbnormal::saveAbnomal(['sku'=>$productData['sku'],'warehouse_code'=>$productData['warehouse_code'],
                            'supplier_code'=>$data['supplier_code'],'buyer'=>$data['buyer'],'reason'=>'采购建议保存失败']);
                        continue;
                    }
                    PurchaseSuggestMrp::updateStatus($data['sku'],$data['warehouse_code']);
                }
            }
        }else{
            echo '请配置运行仓库列表';
            exit;
        }
    }
}