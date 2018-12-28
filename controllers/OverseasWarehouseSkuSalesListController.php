<?php

namespace app\controllers;

use app\config\Vhelper;
use app\models\SkuStatisticsLogSearch;
use app\models\ProductSearch;
use app\models\OverseasBasicTactics;
use app\services\CommonServices;
use app\services\PurchaseOrderServices;
use Yii;
use app\models\SkuSalesStatistics;
use app\models\PurchaseSkuSaleHandleRecord;
use app\models\SkuSalesStatisticsSearch;
use yii\helpers\VarDumper;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\config\Curd;
use app\models\Product;
use app\models\BasicTactics;
use app\models\Warehouse;
use app\models\Stock;
use app\models\PurchaseSuggest;
use app\models\ProductDescription;
use app\models\SupplierQuotes;
use app\models\ProductCategory;
use app\models\SkuStatisticsLog;
use app\models\User;
use app\models\PurchaseSkuSingleTacticMain;
use app\models\PurchaseSkuSingleTacticMainContent;
use app\models\WarehousePurchaseTactics;
use app\models\WarehouseMin;
use app\models\ProductProvider;
use app\models\Supplier;
use app\services\BaseServices;
use app\models\PurchaseHistory;
use yii\helpers\ArrayHelper;

/**
 * Created by PhpStorm.
 * 海外仓sku销售列表
 * User: ztt
 * Date: 2017/3/23 0023
 * Time: 18:41
 */
class OverseasWarehouseSkuSalesListController extends BaseController
{
    public $po_number; //生成的采购单编号
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['purchasing-advice'],
                'rules' => [
                    [
                        'actions' => ['purchasing-advice'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
//                    [
//                        'allow' => true,
//                        'actions' => ['logout','index','change-password','login'],
//                        'roles' => ['@'],
//                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all PurchaseOrder models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new SkuSalesStatisticsSearch();
        $dataProvider = $searchModel->search1(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    /**
     * @desc 查看产品的配库日志
     * @author ztt
     * @date 2017-04-14 16:23:11
     */
    public function actionViewLog()
    {
        $map=[];
        $map['sku']=Yii::$app->request->get('sku');
        $map['warehouse_code']=Yii::$app->request->get('warehouse_code');
        $searchModel = new SkuStatisticsLogSearch();
        $dataProvider = $searchModel->search(['SkuStatisticsLogSearch'=>$map]);
        return $this->renderAjax('view-log', ['searchModel' => $searchModel,'dataProvider'=>$dataProvider]);
    }

    /**
     * @desc 基础数据设置
     * @author ztt
     * @date 2017-04-01 09:30:11
     */
    public function actionBasicCreate()
    {
        $model = new OverseasBasicTactics();
        if (Yii::$app->request->isGet||Yii::$app->request->isAjax)
        {
            //ajax请求
            $res = $model::find()->asArray()->all();
            $data=[];
            foreach ($res as $val){
                $data[$val['type']]=$val;
            }
            return $this->renderAjax('basic-create', ['model' => $model,'data'=>$data]);
        } elseif(Yii::$app->request->isPost) {
            $data_post=Yii::$app->request->Post();
            //数据重组
            $data= Vhelper::changeData($data_post['OverseasBasicTactics']);
            try {
                //循环插入
                $model->batchInsert($data);
                Yii::$app->getSession()->setFlash('success', '恭喜你,添加成功',true);
                return $this->redirect(['index']);
            } catch (Exception $e) {
                //抛出异常
                Yii::$app->getSession()->setFlash('error', $e->getMessage(),true);
            }
        }
    }


    /**
     * @desc 以下的代码用于生成产品采购建议
     * @author wuyang
     * @date 2017-05-15 20:43
     * @purpose: 便于以后更好地维护代码，在原功能基础上做修改
     * 批量获取订单，每次只获取20条，每次运行，在前次运行的基础上，向后面获取，用数据表来记录所获取的ID的最大的数值。
     * 获取数据和循环数据时，每次要考虑到内存消耗的问题，不能一次取全部的数据
     */

    public function actionBatchGetData()
    {

        set_time_limit(5000);
        ini_set('memory_limit', '3000M');

        $sealmodel                      = New Curd();
        $sku_sale_handle_record_model   = New PurchaseSkuSaleHandleRecord(); //sku 处理进程记录表
        $ids                            = Yii::$app->request->get();

        //如果有参数传送过来
        if(is_array($ids) && !empty($ids['ids']))
        {
            //选中的就走这里
            $id_arr      = explode(',', $ids['ids']);
            $handle_data = SkuSalesStatistics::find()
                            ->where(['in','id',$id_arr])
                            ->andWhere(['not in','warehouse_code',['SZ_AA']])
                            ->andWhere(['is_suggest'=>0])
                            ->limit(20)
                            ->orderBy('id asc')
                            ->asArray()
                            ->all();
            $this->actionBatchhandledata($handle_data);
            Yii::$app->getSession()->setFlash('success', '恭喜你,操作成功',true);
            return $this->redirect(['index']);

        } else {

            //没有的话就20条20条运行
            $last        = PurchaseSkuSaleHandleRecord::find()->asArray()->one();
            $handle_data = SkuSalesStatistics::find()
                           ->where(['>','id',$last['sku_sale_id']])
                           ->andWhere(['not in','warehouse_code',['SZ_AA']])
                           ->andWhere(['is_suggest'=>0])
                           ->limit(20)
                           ->orderBy('id asc')
                           ->asArray()
                           ->all();
            $num         = count($handle_data);
            if($num>0)
            {
                $arr_id  = $num-1;
                $uparr   = [
                    'sku_sale_id'      => $handle_data[$arr_id]['id'],
                    'last_update_date' => date('Y:m:d H:i:s')
                ];
                //获取刚刚取得的销售产品列表里面的产品ID.并在处理表中更新此产品ID
                $sealmodel->UpData($sku_sale_handle_record_model, $uparr,"where id=1");
                //-------- start ----------------------- 在这里插入所有的要对订单进出处理的功能
                $this->actionBatchhandledata($handle_data);
                //           exit;
                //--------- end  -----------------------
                //继续调用功能本身
                $handle_data='';
                //$this->actionBatchGetData();

            } else {

                $uparr=[
                    'sku_sale_id'=>0,
                    'last_update_date'=>date('Y:m:d H:i:s')
                ];
                $sealmodel->UpData($sku_sale_handle_record_model, $uparr,"where id=1");
            }

            Yii::$app->getSession()->setFlash('success', '恭喜你,操作成功',true);
            return $this->redirect(['index']);
        }



    }
    /**
     * 销量与欠货数量对比，不存在于销量说明是刚开发的产品,不走正常的建议流程
     * @author ztt
     */
    public function actionOwedGoodsSales()
    {
        //找出欠货的sku
        $Stock =  Stock::find()
            ->select('sku,warehouse_code,left_stock')
            ->Where(['not in','warehouse_code',['SZ_AA']])
            ->andWhere(['is_suggest'=>0])
            ->andWhere(['<','left_stock',0])
            ->limit(100)
            ->asArray()
            ->all();

        if (!$Stock)
        {
            exit('哈哈！数据不存在');
        }

        //转换为一维
        $sku_s          = ArrayHelper::getColumn($Stock, 'sku');
        $warehouse_code = ArrayHelper::getColumn($Stock, 'warehouse_code');
        //存在于销售统计说明是已从仓库出过货了,那就不是刚开发的产品了,否则是刚开的的产品
        $sku            = SkuSalesStatistics::find()->where(['in', 'sku', $sku_s])->andWhere(['in','warehouse_code',$warehouse_code])->asArray()->all();

        if (!$sku)
        {

            foreach ($Stock as $v)
            {
                $value                          = $this->getSupplier($v);
                $value                          = $this->getProductCategory($value);
                $value                          = $this->getSupplierCode($value);
                $value['resupply_sku_quantity'] = 0;
                $this->GeneratePurchaseList($value);
            }
            exit('恭喜你,运行成功');
        } else {

            $ss   = ArrayHelper::getColumn($sku, 'sku');
            $sb   = array_diff($sku_s, $ss);
            $data = Stock::find()->where(['in', 'sku', $sb])->andwhere(['in', 'sku', $sku_s])->andWhere(['in', 'warehouse_code', $warehouse_code])->andWhere(['is_suggest' => 0])->asArray()->all();
            foreach ($data as $v)
            {
                $value                          = $this->getSupplier($v);
                $value                          = $this->getProductCategory($value);
                $value                          = $this->getSupplierCode($value);
                $value['resupply_sku_quantity'] = 0;
                $this->GeneratePurchaseList($value);
            }
            exit('恭喜你,运行成功');
        }
    }

    /**
     * 海外仓计划任务
     * @author ztt
     */
    public function  actionPurchasingAdvice()
    {

        $handle_data = SkuSalesStatistics::find()
            ->andWhere(['not in','warehouse_code',['SZ_AA']])
            ->andWhere(['is_suggest'=>0])
            ->limit(100)
            ->orderBy('id asc')
            ->asArray()
            ->all();
        if(!empty($handle_data))
        {
            $this->actionBatchhandledata($handle_data);
            Yii::info("恭喜你,运行成功");
            exit('恭喜你,运行成功');
        } else {
            exit('哈哈！并没有可运行的数据哦');
        }


    }

    protected function actionBatchhandledata($handle_data)
    {


        foreach ($handle_data as $key=>$value)
        {

            $value = $this->getSinglesettactic($value);             //检查是此sku是否已经单独设置补货策略


            $value = $this->getWarehousepurchasetactic($value);    //获取补货类型
            $value = $this->getTypeMore($value);                    //获取波动上升，波动下降，持续上升，持续下降的类型； 只是销量的变化趋势，与补货模式无关

            $value = $this->calculateAvgSelling($value);            // 根据波动趋势，计算预计日销量

            $value = $this->getStock($value);                       //获取 由sku,仓库字段确定的库存量 增加四个库存参数


            $value = $this->getSupplier($value);                    //获取sku的供货商：如果有历史采购记录，就取历史采购记录的最后一条，                                                                          如果没有历史采购记录，就暂时留空

            $value = $this->getSupplierCode($value);               //获取供应商编号

            $value = $this->getProductCategory($value);            //获取产品分类ID

            if ($value['singletactic']=='Y')
            {                                                       //如果补货模式为单独补货，计算补货量
                $value = $this->calculateSingleSkuResupplyQty($value);
            }

            if ($value['singletactic']=='N' && $value['pattern']=='min')
            {                                                       //如果补货模式为最小补货,计算最小补货量
                $value = $this->calculateMinResupplySkuQty($value);
            }

//            if ($value['singletactic']=='N' && $value['pattern']=='def')
//            {                                                       //如果补货模式为默认补货，计算补货量
//                $value = $this->calculateDefWarehouseSkuResupplyQty($value);
//            }
            $value['safe_delivery'] = !empty($value['safe_delivery'])?$value['safe_delivery']:90;



            $this->GeneratePurchaseList($value);

        }

    }



    /**
     * 根据销售数据，归类销售过去的销售数据类型： 波动上升，波动下降，持续上升，持续下降等
     * @param $data
     * @return array|void
     */
    protected function getTypeMore($data)
    {

        if(!is_array($data))
        {
            return;
        }
        $average_7  = $data['days_sales_7'] / 7;
        $average_30 = $data['days_sales_30'] / 30;
        $average_90 = $data['days_sales_90'] / 90;
        if($average_7>$average_30 && $average_30>$average_90)
        {
            $data['type'] = 'last_up';//持续上升
        } elseif ($average_7<$average_30 && $average_30<$average_90){
            $data['type'] = 'last_down';//持续下降
        } elseif (($average_7+$average_30)>=($average_30+$average_90)){
            $data['type'] = 'wave_up';//波动上升
        } elseif (($average_7+$average_30)<($average_30+$average_90)){
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

        if($data['type']=='abnormal')
        {
            return;
        }

        $result = Stock::find()->select('left_stock, on_way_stock, stock,available_stock')->where(['sku'=>$data['sku']])->asArray()->one();
        if ($result)
        {
            //$result['stock']= 5;
            $avilable_stock          = $result['stock'] + $result['on_way_stock'];
            $data['stock_qty']       = $avilable_stock;
            $data['left_stock']      = $result['left_stock'];
            $data['on_way_stock']    = $result['on_way_stock'];
            $data['stock']           = $result['stock'];
            $data['available_stock'] = $avilable_stock;      //可用库存，暂定为现有库存和在途库存
        } else{

            $avilable_stock          = 0;
            $data['stock_qty']       = 0;
            $data['left_stock']      = 0;
            $data['on_way_stock']    = 0;
            $data['stock']           = 0;
            $data['available_stock'] = $avilable_stock;      //可用库存，暂定为现有库存和在途库存
        }

        // Vhelper::dump($data);
        return $data;
    }



    /**
     * 获取仓库采购策略
     * @param $data
     * @return mixed
     */
    protected function getWarehousepurchasetactic($data)
    {
        $result          = Warehouse::find()->select('pattern')->where(['warehouse_code' => $data['warehouse_code']])->one();
        $data['pattern'] = $result['pattern'];
        return $data;
    }

    /**
     * 检查是否设置过单独补货策略
     * @param $data
     * @return mixed
     */
    protected function getSinglesettactic($data)
    {

        $result = PurchaseSkuSingleTacticMain::find()->joinWith('content')->where(['sku'=>$data['sku'],'warehouse'=>$data['warehouse_code']])->asArray()->one();
        if($result)
        {
            $data['singletactic']='Y';
            $data['single_tactic_main_id']=$result['id'];
            $data['safe_delivery']= $result['content']['supply_days'];
        }else{
            $data['singletactic']='N';
        }
        return $data;
    }


    //获取历史采购供货商

    protected function getSupplier($data){
        header("Content-type: text/html; charset=utf-8");
        $sealmodel                                      = New Curd();
        $purchase_history_model = New PurchaseHistory();
        $result = $sealmodel->GetData($purchase_history_model, 'supplier_name, pur_number, buyer, purchase_price, currency, product_name', 'one', " where sku='" . $data['sku'] . "'", 'purchase_time desc');
        $data['supplier_name'] = $result['supplier_name'] ? $result['supplier_name'] : '';
        $data['buyer'] = $result['buyer'];
        $data['price'] = $result['purchase_price'];
        //$data['buyer_id']         ='6';
        $data['replenish_type']      = '4';
        $data['currency']            = $result['currency'] ? $result['currency'] : 'CNY';
        $data['supplier_settlement'] = '1';
        $data['ship_method']         = '2';
        $data['name']                = $result['product_name'] ? $result['product_name'] : '';

        return $data;
    }

    //获取供货商编号

    protected function getSupplierCode($data){
        header("Content-type: text/html; charset=utf-8");
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

        $category_result             = $sealmodel->GetData($product_cagetory_model, 'category_cn_name', 'one', " where id='" . $result['product_category_id'] . "'");
        $data['product_category_id'] = $result['product_category_id'] ? $result['product_category_id'] : '1';
        $data['category_cn_name']    = $category_result['category_cn_name'] ? $category_result['category_cn_name'] : '没有找到中文名字';
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
        $sealmodel               = New Curd();
        $day_selling_parameter   = New OverseasBasicTactics();
        $result = $sealmodel->GetData($day_selling_parameter,'days_3,days_7,days_15,days_90, days_30','one',"where type ='".$data['type']."'");
        $avergage_selling        = $data['days_sales_7']*$result['days_7']/7 + $data['days_sales_30']*$result['days_30']/30 + $data['days_sales_90']*$result['days_90']/90;
        $data['average_selling'] = number_format($avergage_selling, 2, '.', '');
        $data['weighted_3'] = $result['days_3'];
        $data['weighted_7'] = $result['days_7'];
        $data['weighted_15'] = $result['days_15'];
        $data['weighted_30'] = $result['days_30'];
        $data['weighted_90'] = $result['days_90'];
        return $data;
    }

    /**
     * 根据单独sku设置的补货策略，计算出单个sku需要补货的数量
     * @param $data
     */
    protected function calculateSingleSkuResupplyQty($data)
    {


        $sealmodel                                     = New Curd();
        $purchase_sku_single_tactic_main_content_model = New PurchaseSkuSingleTacticMainContent();
        $purchase_content                              = $sealmodel->GetData($purchase_sku_single_tactic_main_content_model, 'supply_days', 'one', "where single_tactic_main_id='" . $data['single_tactic_main_id'] . "'");

        //计算出来的补货数量 = 预计销量*预计销量天数
        $calculate_supply_qty                          = $purchase_content['supply_days']*$data['average_selling'];
        //目前仓库的库存        = 现在可用的仓库库存 + 在途库存
        $stock_para = $data['on_way_stock'] + $data['stock'];

        //需要补货数量为： 计算出来的需要补充的库存的数量减去现在仓库里面可用的库存数量
        $data['resupply_sku_quantity'] = ceil($calculate_supply_qty) - $stock_para;
        $left_stock = $data['left_stock'] < 0?abs($data['left_stock']):0;
        $data['resupply_sku_quantity'] = ($data['resupply_sku_quantity']<0 )? 0+$left_stock : $data['resupply_sku_quantity']+ $left_stock;

        return $data;

    }


    /**
     * 根据仓库设置的默认补货策略，计算补货数量
     * 默认：原补货数量 = (生产天数 + 物流天数 + 安全库存 + 采购频率) * 平均销量 - 已有库存;
     * @param $data
     */
    protected function calculateDefWarehouseSkuResupplyQty($data)
    {
        if($data['pattern']!='def')
        {
            return;
        }
        $sealmodel                        =  New Curd();
        $warehouse_purchase_tactics_model =  New WarehousePurchaseTactics();
        $result = $sealmodel->GetData($warehouse_purchase_tactics_model,'days_product, days_logistics,
            days_safe_stock, days_frequency_purchase','one',
            "where warehouse_code='".$data['warehouse_code']."' and type='".$data['type']."'");

        $product_and_transfer             = $result['days_product'] + $result['days_logistics'];

        if($product_and_transfer==0)
        {
            return $data;
        }

        $transport_time                   = $product_and_transfer > $result['days_frequency_purchase'] ? $product_and_transfer : $result['days_frequency_purchase'];

        $resupply_sku_quantity            = $data['average_selling']*$transport_time*$result['days_frequency_purchase']/$product_and_transfer;
        $data['resupply_sku_quantity']    = ceil($resupply_sku_quantity)-$data['stock_qty']; //减去仓库原来有的库存数量
        $data['safe_delivery']            = $product_and_transfer;
        return $data;

    }

    /**
     * 根据最小补货量，计算补货数量
     * 原补货数量 = (安全库存天数 + 最小补货天数) * 平均销量 - 已有库存; 按照易仓的公式来计算
     * @param $data
     * @return mixed
     */

    protected function calculateMinResupplySkuQty($data)
    {
        $sealmodel                     = New Curd();
        $warehouse_min_model           = New WarehouseMin();
        $result                        = $sealmodel->GetData($warehouse_min_model, 'days_safe, days_min', 'one', "where warehouse_code='" . $data['warehouse_code'] . "'");

        //计算出来的补货数量 = 预计销量*预计销量天数
        $calculate_supply_qty                          = ($result['days_safe']+$result['days_min'])*$data['average_selling'];
        //目前仓库的库存        = 现在可用的仓库库存 + 在途库存
        $stock_para = $data['on_way_stock'] + $data['stock'];

        $data['resupply_sku_quantity'] = ceil($calculate_supply_qty) - $stock_para;
        $data['safe_delivery']  = $result['days_safe']+$result['days_min'];
        $left_stock = $data['left_stock'] < 0?abs($data['left_stock']):0;
        $data['resupply_sku_quantity'] = ($data['resupply_sku_quantity']<0 )? 0 : $data['resupply_sku_quantity'] + $left_stock;

        return $data;
    }


    /**
     * 生成采购建议单
     * @param $data
     */
    protected function GeneratePurchaseList($data)
    {
        //$po_number                      = CommonServices::getNumber('ABD');
        //$this->po_number                = $po_number;
        $sealmodel                     = New Curd();
        $purchase_suggest_model        = New PurchaseSuggest();
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
        $product_supply_info = $sealmodel->GetData($product_supplier_model, 'supplier_code, is_supplier, is_exemption, is_push', 'one', "where sku = '" . $data['sku'] . "'");
        //根据上面获取到的供应商编号， 从供货商表中获取 供应商名, 支付方式, 结算方式
        $product_supplier_detail_info_model = $sealmodel->GetData($product_supplier_detail_model, 'supplier_name, payment_method, supplier_settlement', 'one', "where supplier_code='" . $product_supply_info['supplier_code'] . "'");

        //根据sku编号，从产品表中获取产品所属的分类ID, 产品状态
        $product_category_relation_info = $sealmodel->GetData($product_model, 'product_category_id, product_status,uploadimgs', 'one', "where sku='" . $data['sku'] . "'");
        //根据上面获取的产品所属的分类ID,去 产品分类表中获取类目中文名称
        $product_category_info = $sealmodel->GetData($product_category_model, 'category_cn_name', 'one', "where id='" . $product_category_relation_info['product_category_id'] . "'");
        //根据sku编号， 去供应商报价表中获取 供应商单价
        $supplier_quotes_info = $sealmodel->GetData($supplier_quotes_model, 'supplierprice, currency', 'one', "where product_sku='" . $data['sku'] . "'");

        if(isset($data['resupply_sku_quantity']) && $data['resupply_sku_quantity']>0)
        {
            $qty = $data['resupply_sku_quantity'];
        } elseif($data['left_stock']<0 && isset($data['average_selling']) && $data['average_selling'] <0) {
            $qty = abs($data['left_stock']);
        } else{
            $qty = 0;
        }

        $buyer = isset(Yii::$app->user->identity->username)?Yii::$app->user->identity->username:'admin';
        $add_data=[
            'warehouse_code'      => $data['warehouse_code'],
            'warehouse_name'      => $warehousename['warehouse_name'],
            'sku'                 => $data['sku'],
            'name'                => $data['name'] ? addslashes($data['name']) : $productinfo['title'],
            'supplier_code'       => $data['supplier_code'] ? $data['supplier_code'] : $product_supply_info['supplier_code'],
            'supplier_name'       => $data['supplier_name'] ? $data['supplier_name'] : $product_supplier_detail_info_model['supplier_name'],
            'buyer'               => $buyer,
            'buyer_id'            => isset(Yii::$app->user->id)?Yii::$app->user->id:'1',
            'replenish_type'      => isset($data['replenish_type'])?$data['replenish_type']:1,
            'qty'                 => ceil($qty),
            'price'               => $data['price'] ? $data['price'] :'0', //$supplier_quotes_info['supplierprice'],
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
            'days_sales_3'        => '0',
            'purchase_type'       => '2',
            'days_sales_3'        => isset($data['days_sales_3'])?$data['days_sales_3']:0,
            'days_sales_7'        => isset($data['days_sales_7'])?$data['days_sales_7']:0,
            'days_sales_15'       => isset($data['days_sales_15'])?$data['days_sales_15']:0,
            'days_sales_30'       => isset($data['days_sales_30'])?$data['days_sales_30']:0,
            'days_sales_60'       => isset($data['days_sales_60'])?$data['days_sales_60']:0,
            'days_sales_90'       => isset($data['days_sales_90'])?$data['days_sales_90']:0,
            'sales_avg'           => isset($data['average_selling'])?$data['average_selling']:0,
            'type'                => isset($data['type'])?$data['type']:'last_down',
            'weighted_3'          => isset($data['weighted_3'])?$data['weighted_3']:0,
            'weighted_7'          => isset($data['weighted_7'])?$data['weighted_7']:0,
            'weighted_15'         => isset($data['weighted_15'])?$data['weighted_15']:0,
            'weighted_30'         => isset($data['weighted_30'])?$data['weighted_30']:0,
            'weighted_60'         => isset($data['weighted_60'])?$data['weighted_60']:0,
            'weighted_90'         => isset($data['weighted_90'])?$data['weighted_90']:0,
            'safe_delivery'       => isset($data['safe_delivery'])?$data['safe_delivery']:90,
        ];

        $rs=$sealmodel->GetData($purchase_suggest_model,'id','one',"where sku='".$data['sku']."' and warehouse_code='".$data['warehouse_code']."'");
        if($rs){
            $sealmodel->UpData($purchase_suggest_model, $add_data, "where sku='".$data['sku']."' and warehouse_code='".$data['warehouse_code']."'");
        } else {
            $sealmodel->Add($purchase_suggest_model, $add_data);
            //增加日志
            $temp                   = [];
            $temp['sku']            = $data['sku'];
            $temp['warehouse_code'] = $data['warehouse_code'];
            $temp['po_number']      = 'HW'.date('Ymd').mt_rand();
            $temp['created_at']     = date('Y-m-d H:i:s');
            $temp['status']         = isset($data['resupply_sku_quantity']) ? 'success' : 'failure';
            $temp['note']           = "补货成功，本次补货数量{$qty}";
            $temp['creator']        = $buyer;
            $sealmodel->Add($sku_resupply_log_model, $temp);

        }
        //修改统计为1则不加入运算
        $model       = SkuSalesStatistics::find()->where(['sku'=>$data['sku'],'warehouse_code'=>$data['warehouse_code']])->one();
        $model_stock = Stock::find()->where(['sku'=>$data['sku'],'warehouse_code'=>$data['warehouse_code']])->one();
        if ($model)
        {
            $model->is_suggest=1;
            $model->save(false);
        }
        if ($model_stock)
        {
            $model_stock->is_suggest=1;
            $model_stock->save(false);
        }



    }


}
