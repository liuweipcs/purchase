<?php

namespace app\controllers;

use app\config\Vhelper;
use app\models\PurchaseCategoryBind;
use app\models\PurchaseOrder;
use app\models\PurchaseOrderItems;
use app\models\PurchaseSuggestQuantity;
use app\models\SkuSalesStatisticsTotal;
use app\models\SkuTotaPage;
use app\models\StockOwes;
use app\models\SupplierBuyer;
use app\models\SupplierNum;
use app\models\SupplierProductLine;
use app\services\CommonServices;
use Yii;
use app\config\Curd;
use app\models\Product;
use app\models\ProductSearch;
use app\models\BasicTactics;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\Warehouse;
use app\models\SkuSalesStatistics;
use app\models\Stock;
use app\models\PurchaseSuggest;
use app\models\ProductDescription;
use app\models\SupplierQuotes;
use app\models\SkuSalesStatisticsSearch;
use app\models\ProductCategory;
use app\models\SkuStatisticsLog;
use app\models\SkuStatisticsLogSearch;
use app\models\User;
use app\models\PurchaseSkuSaleHandleRecord;
use app\models\PurchaseSkuSingleTacticMain;
use app\models\PurchaseSkuSingleTacticMainContent;
use app\models\WarehousePurchaseTactics;
use app\models\WarehouseMin;
use app\models\ProductProvider;
use app\models\Supplier;
use app\services\BaseServices;
use app\models\PurchaseHistory;
use app\models\SegmentedTable;
use yii\helpers\ArrayHelper;
use app\models\WarehouseOwedGoods;
use app\services\PurchaseSuggestQuantityServices;

/**
 * @desc 采购建议模块
 * @author Jimmy
 * @date 2017-03-29 19:28:11
 * PurchaseTacticsController implements the CRUD actions for Product model.
 */
class PurchaseTacticsController extends BaseController
{
    public $log_data;//用来记录配库数据日志
    public $po_number; //生成的采购单编号
    public $num ='' ; //每次开始的值
    public $suggest_warehouse = ['FBA-WW','SZ_AA','ZDXNC','HW_XNC'];//定义运行采购建议的仓库

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['purchasing-advice', 'getsuggestnumbers'],
                'rules' => [
                    [
                        'actions' => ['purchasing-advice', 'getsuggestnumbers'],
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
     * Lists all Product models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new SkuSalesStatisticsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    /**
     * @desc 基础数据设置
     * @author Jimmy
     * @date 2017-04-01 09:30:11
     */
    public function actionBasicCreate()
    {
        $model = new BasicTactics();
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
            $data= Vhelper::changeData($data_post['BasicTactics']);
            try {
                //循环插入
                $status = $model->batchInsert($data);
                if($status)
                {
                    Yii::$app->getSession()->setFlash('success', '恭喜你,操作成功',true);
                } else{
                    Yii::$app->getSession()->setFlash('error', '恭喜你,操作失败,请检查一下系数',true);
                }

                return $this->redirect(['index']);
            } catch (Exception $e) {
                //抛出异常
                Yii::$app->getSession()->setFlash('error', $e->getMessage(),true);
            }
        }
    }

    /**
     * @desc 仓库补货策略
     * @author Jimmy
     * @date 2017-04-02 09:38:11
     */
    public function actionWarehouseCreate()
    {
        $model = new Warehouse();
        if (Yii::$app->request->isGet||Yii::$app->request->isAjax)
        {
            //ajax请求
            //ajax请求
            $data = $model::find()->asArray()->all();
            return $this->renderAjax('warehouse-create', ['data'=>$data]);
        } elseif(Yii::$app->request->isPost) {
            $data_post=Yii::$app->request->Post();
            //数据重组
            $data= Vhelper::changeData($data_post['BasicTactics']);
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
     * @desc 查看产品的配库日志
     * @author Jimmy
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
     * 补货说明
     * @return string
     */

    public function actionDesc()
    {
        return $this->renderPartial('description');
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
        //$po_number                      = CommonServices::getNumber('PO');
        //$this->po_number                = $po_number;
        $sealmodel                      = New Curd();
        $sku_sale_statistic_model       = new SkuSalesStatistics();//销售统计表
        $sku_sale_handle_record_model   = New PurchaseSkuSaleHandleRecord(); //sku 处理进程记录表
        $ids                            = Yii::$app->request->get();

        //如果有参数传送过来
        if(is_array($ids) && !empty($ids['ids']))
        {

            //选中的就走这里
            $id_arr      = explode(',', $ids['ids']);
            $handle_data = SkuSalesStatistics::find()
                ->where(['in','id',$id_arr])
                ->andWhere(['in','warehouse_code',['SZ_AA']])
                ->andWhere(['is_suggest'=>0])
                ->limit(20)
                ->asArray()
                ->all();
            foreach($handle_data as $v)
            {
                $smodel =SkuSalesStatistics::findOne($v['id']);
                $smodel->is_suggest =1;
                $smodel->save();
            }
            $this->Batchhandledata($handle_data);
            Yii::$app->getSession()->setFlash('success', '恭喜你,操作成功',true);
            return $this->redirect(['index']);

        } else {

            //没有的话就20条20条运行
            $last        = PurchaseSkuSaleHandleRecord::find()->asArray()->one();
            $handle_data = SkuSalesStatistics::find()
                ->where(['>','id',$last['sku_sale_id']])
                ->andWhere(['in','warehouse_code',['SZ_AA']])
                ->andWhere(['is_suggest'=>0])
                ->limit(20)
                ->asArray()
                ->all();
            foreach($handle_data as $v)
            {
                $smodel =SkuSalesStatistics::findOne($v['id']);
                $smodel->is_suggest =1;
                $smodel->save();
            }
            $num         = count($handle_data);
            if($num>0)
            {
                $arr_id  = $num-1;

                $uparr=[
                    'sku_sale_id'=>$handle_data[$arr_id]['id'],
                    'last_update_date'=>date('Y:m:d H:i:s')
                ];

                //获取刚刚取得的销售产品列表里面的产品ID.并在处理表中更新此产品ID
                $sealmodel->UpData($sku_sale_handle_record_model, $uparr,"where id=1");
                //-------- start ----------------------- 在这里插入所有的要对订单进出处理的功能
                $this->Batchhandledata($handle_data);
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
     * 计算库存欠货的,过5分钟计算
     */
    public function actionOwedGoods()
    {

        //找出欠货的sku
        $Stock =  Stock::find()
            ->select('*')
            ->where(['warehouse_code'=>'SZ_AA'])
            ->andWhere(['is_suggest'=>0])
            ->andWhere(['<','left_stock',0])
            ->orderBy('left_stock asc')
            ->asArray()
            ->limit(100)
            ->all();
        //Vhelper::dump($Stock);

        if (!$Stock)
        {
            exit('哈哈！没有欠货的哦');
        }

        foreach($Stock as $value)
        {
            $value = $this->getSinglesettactic($value); //检查是此sku是否已经单独设置补货策略

            if($value['singletactic']=='N')
            {   //如果为仓库补货策略，获取仓库补货策略
                $value = $this->getWarehousepurchasetactic($value); //获取仓库的补货策略
            }

            $value = $this->getSkuSales($value);                              //获取销量
            $value = $this->getTypeMore($value);                    //获取波动上升，波动下降，持续上升，持续下降的类型； 只是销量的变化趋势，与补货模式无关
            $value = $this->calculateAvgSelling($value);            // 根据波动趋势，计算预计日销量
            $value = $this->getSupplier($value);                    //获取sku的供货商：如果有历史采购记录，就取历史采购记录的最后一条，如果没有历史采购记录，就暂时留空
            $value = $this->getSupplierCode($value);              //获取供应商编号

            $value = $this->getProductCategory($value);            //获取产品分类ID
            if ($value['singletactic']=='Y')
            {                                                       //如果补货模式为单独补货，计算补货量
                $value = $this->calculateSingleSkuResupplyQty($value);
            }

            if ($value['singletactic']=='N')
            {                                                       //如果补货模式为最小补货,计算最小补货量
                $value = $this->calculateMinResupplySkuQty($value);
            }
            $this->GeneratePurchaseList($value);
        }
    }

    public function actionSessionCount()
    {

        $session = Yii::$app->session;
        $count                      = SkuSalesStatistics::find()->where(['warehouse_code'=>'SZ_AA'])->count();
        $counts                     = ceil($count/200);
        $session['data']  = $counts;


    }
    /**
     * 根据session获取值的变化
     */
    public function actionSessionRecord()
    {




        Yii::$app->session['data']=Yii::$app->session['data']-1;
        //$this->actionPurchasingAdvice();



    }

    /**
     * 计划任务运行 已有销量的数据
     */
    //http://purc.yibai.com/purchase-tactics/purchasing-advice
    public function  actionPurchasingAdvice()
    {
        $page_size = 5000;
        $count_1 =SkuSalesStatisticsTotal::find()
            ->andWhere(['is_sum' => 1])
            ->andWhere(['is_suggest' => 0])
            ->count();
        if($count_1==0)
        {
            SkuTotaPage::updateAll(['total'=>0,'num'=>0],['id'=>1]);
            die('已全部生成');

        }

        $count = SkuSalesStatisticsTotal::find()->count();

        $page_total_prev = SkuTotaPage::find()->select('total')->where(['id'=>1])->scalar();
        $page_total = ceil($count/$page_size);

        if($page_total_prev == 0 && $page_total > 0)
        {
            SkuTotaPage::updateAll(['total'=>$page_total],['id'=>1]);
        }

        $num= SkuTotaPage::find()->select('num')->where(['id'=>1])->scalar();


        if($num>=$page_total)
        {
            SkuTotaPage::updateAll(['num'=>0],['id'=>1]);

        }
        else {
            SkuTotaPage::updateAll(['num' => $num+1], ['id' => 1]);
        }

        if($count_1 <= $page_size){
            SkuTotaPage::updateAll(['total'=>0,'num'=>0],['id'=>1]);
            $num=0;
        }

        $handle_data = SkuSalesStatisticsTotal::find()
            ->andWhere(['is_sum' => 1])
            ->andWhere(['is_suggest' => 0])
            ->andWhere(['warehouse_code' =>$this->suggest_warehouse])
            ->offset(($num)*$page_size)
            ->limit($page_size)
            ->asArray()
            ->all();
        if (!empty($handle_data)) {
            /* foreach ($handle_data as $v) {
                 $smodel              = SkuSalesStatisticsTotal::findOne($v['id']);
                 $smodel->is_suggest  = 1;
                 $smodel->update_time = date('Y-m-d H:i:s', time());
                 $smodel->save(false);
             }*/

            $this->Batchhandledata($handle_data);
            Yii::info("恭喜你,运行成功");
            exit('恭喜你,运行成功');
        } else {

            SkuTotaPage::updateAll(['total'=>0,'num'=>0],['id'=>1]);
            exit('哈哈！并没有可运行的数据哦');

        }

    }
    public function actionDelQian()
    {
        StockOwes::deleteAll();
        SupplierNum::deleteAll(['type'=>12]);
    }

    /**
     * 定时任务运行欠货
     */
    public  function  actionQianHuo()
    {
        $model = StockOwes::find()->where(['status'=>0])->asArray()->limit(1000)->all();
        //Vhelper::dump($model);
        if($model)
        {

            foreach($model as $v)
            {
                $v['sku'] = trim($v['sku']);

                $re = SkuSalesStatisticsTotal::find()->where(['sku'=>$v['sku'],'warehouse_code'=>$v['warehouse_code']])->one();
                $s  = Product::find()->where(['sku'=>$v['sku'],'product_type'=>1,'product_is_multi'=>[0,1]])->scalar();

                if(empty($re) && $s)
                {
                    //Vhelper::dump(22222);
                    $re2 = new SkuSalesStatisticsTotal();
                    $re2->sku                 = $v['sku'];
                    $re2->days_sales_3        = 0;
                    $re2->days_sales_7        = 0;
                    $re2->days_sales_15       = 0;
                    $re2->days_sales_30       = 0;
                    $re2->days_sales_60       = 0;
                    $re2->days_sales_90       = 0;
                    $re2->is_sum              = 1;
                    $re2->is_suggest          = 0;
                    $re2->statistics_date     = date('Y-m-d H:i:s');
                    $re2->warehouse_code      = $v['warehouse_code'];
                    $re2->save(false);

                } else{

                    //continue;
                    SkuSalesStatisticsTotal::updateAll(['is_suggest'=>0], ['sku' =>$v['sku'],'warehouse_code'=>$v['warehouse_code']]);
                }

                StockOwes::updateAll(['status'=>1], ['id' =>$v['id']]);
                //StockOwes::deleteAll(['id' =>$v['id']]);

            }
        } else{

        }
        /*$models = StockOwes::find()->where(['status'=>1])->asArray()->limit(1000)->all();
        if($models)
        {
            foreach($models as $v)
            {
                $rs = PurchaseSuggest::find()->where(['sku'=>$v['sku'],'warehouse_code'=>$v['warehouse_code']])->asArray()->one();
                if($rs)
                {
                    $lack_total = $rs['on_way_stock']+$rs['available_stock']+$rs['left_stock'];
                    PurchaseSuggest::updateAll(['lack_total'=>$lack_total,'created_at'=>date('Y-m-d H:i:s')],['sku'=>$v['sku'],'warehouse_code'=>$v['warehouse_code']]);
                } else{

                }

            }
        } else{

        }*/




    }

    public function actionCheck()
    {

        $model = SkuSalesStatisticsTotal::find()->select('id,sku,warehouse_code')->where(['is_sum'=>0])->asArray()->limit(3000)->all();

        foreach($model as $v)
        {
            $days_sales_3=0;
            $days_sales_7=0;
            $days_sales_15=0;
            $days_sales_30=0;
            $days_sales_60=0;
            $days_sales_90=0;
            $re= SkuSalesStatistics::find()->where(['sku'=>$v['sku'],'warehouse_code'=>$v['warehouse_code']])->asArray()->all();
            foreach($re as $c)
            {
                $days_sales_3  +=$c['days_sales_3'];
                $days_sales_7  +=$c['days_sales_7'];
                $days_sales_15 +=$c['days_sales_15'];
                $days_sales_30 +=$c['days_sales_30'];
                $days_sales_60 +=$c['days_sales_60'];
                $days_sales_90 +=$c['days_sales_90'];

            }
            $models = SkuSalesStatisticsTotal::findOne($v['id']);

            $models->days_sales_3=$days_sales_3;
            $models->days_sales_7=$days_sales_7;
            $models->days_sales_15=$days_sales_15;
            $models->days_sales_30=$days_sales_30;
            $models->days_sales_60=$days_sales_60;
            $models->days_sales_90=$days_sales_90;
            $models->is_sum=1;
            $models->statistics_date=date('Y-m-d H:i:s');
            $models->save(false);



            // Vhelper::dump($re);


        }

    }
    /**
     *  统计欠货
     */
    public function  actionStatisticsOwedGoods()
    {
        /*$fields                     = ['*','sum(quantity_goods) as resupply_sku_quantity'];
        $WarehouseOwedGoods         =  WarehouseOwedGoods::find()
            ->select($fields)
            ->andWhere(['is_purchase'=>0])
            ->asArray()
            ->groupBy('sku')
            ->limit(100)
            ->all();
        if (!$WarehouseOwedGoods)
        {
            exit('哈哈！没有欠货的哦');
        }
        $this->GeneratePurchaseList($WarehouseOwedGoods);*/

    }
    protected function Batchhandledata($handle_data)
    {

        set_time_limit(100);

        foreach ($handle_data as $key=>$value)
        {
            $value['sku']=addslashes($value['sku']);
            if($value['warehouse_code']=='FBA-WW'){
                $value['warehouse_code']='FBA_SZ_AA';//当销量数据是FBA销量汇总仓时，变成东莞FBA虚拟仓
            }
            //设置五分钟缓存去重
            if(!Yii::$app->cache->add($value['sku'].'_'.$value['warehouse_code'],date('Y-m-d H:i:s',time()),300)){
                continue;
            }
            $value = $this->getSinglesettactic($value);             //检查是此sku是否已经单独设置补货策略

            if($value['singletactic']=='N')
            {   //如果为仓库补货策略，获取仓库补货策略
                $value = $this->getWarehousepurchasetactic($value); //获取仓库的补货策略
            }

            $value = $this->getTypeMore($value);                    //获取波动上升，波动下降，持续上升，持续下降的类型； 只是销量的变化趋势，与补货模式无关

            $value = $this->calculateAvgSelling($value);            // 根据波动趋势，计算预计日销量

            $value = $this->getStock($value);                       //获取 由sku,仓库字段确定的库存量 增加四个库存参数

            //$value = $this->getPurchaseUnpaid($value);              //添加采购下单确认,经理未审批的sku数量到在途库存

            $value = $this->getSupplier($value);                    //获取sku的供货商：如果有历史采购记录，就取历史采购记录的最后一条，如果没有历史采购记录，就暂时留空


            $value = $this->getSupplierCode($value);              //获取供应商编号
            $value = $this->getLatestQuote($value);               //获取最新的报价

            $value = $this->getProductCategory($value);            //获取产品分类ID
            //Vhelper::dump($value);
            if (empty($value['suggest_status'])) { //如果不是从导入的需求中单独新建采购建议
                if ($value['singletactic'] == 'Y') {                                                       //如果补货模式为单独补货，计算补货量
                    $value = $this->calculateSingleSkuResupplyQty($value);
                }

                if ($value['singletactic'] == 'N') {                                                       //如果补货模式为最小补货,计算最小补货量
                    $value = $this->calculateMinResupplySkuQty($value);
                }
            }

//            if ($value['singletactic']=='N' && $value['pattern']=='def')
//            {                                                       //如果补货模式为默认补货，计算补货量
//                $value = $this->calculateDefWarehouseSkuResupplyQty($value);
//            }

            $value['safe_delivery'] = !empty($value['safe_delivery'])?$value['safe_delivery']:12;

//            if (empty($value['suggest_status'])) { //如果不是从导入的需求中单独新建采购建议
//                $value = $this->getSuggestNum($value);                  //如果历史采购建议表中存在导入数据的sku和仓库对应的建议，就将数据相加
//            }
//           Vhelper::dump($value);
//            var_dump($value);

            $this->GeneratePurchaseList($value);

        }

    }
    protected  function  getLatestQuote($data)
    {
        $rs = PurchaseOrderItems::find()->select('price,pur_number')->where(['sku'=>$data['sku']])->andWhere(['>','ctq',0])->orderBy('id desc')->asArray()->one();

        if(empty($rs))
        {
            // echo 1;
            return $data;
        }

        $rs1 = PurchaseOrder::find()->select('supplier_code,supplier_name')->where(['pur_number'=>$rs['pur_number']])->andWhere(['>','id','4045'])->andWhere(['not in','purchas_status',['1','2','4','10']])->orderBy('id desc')->asArray()->one();
        if(empty($rs1))
        {
            return $data;
        }
        //var_dump($data);
        $data['price'] =$rs['price'];
        $data['supplier_code'] =$rs1['supplier_code'];
        $data['supplier_name'] =$rs1['supplier_name'];
        //var_dump($data);
        return $data;
    }
    /**
     * @param $data
     * @return array|null|\yii\db\ActiveRecord
     */
    protected function getSkuSales($data)
    {
        $result = SkuSalesStatisticsTotal::find()->where(['sku'=>$data['sku'],'warehouse_code' => $data['warehouse_code']])->asArray()->one();
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

        if(!is_array($data))
        {
            return;
        }
        $average_3  = $data['days_sales_3'] / 3;
        $average_7  = $data['days_sales_7'] / 7;
        $average_15 = $data['days_sales_15'] / 15;
        $average_30 = $data['days_sales_30'] / 30;
        if($average_3>$average_7 && $average_7>$average_15 && $average_15>$average_30)
        {
            $data['type'] = 'last_up';//持续上升
        } elseif ($average_3<$average_7 && $average_7<$average_15 && $average_15<$average_30){
            $data['type'] = 'last_down';//持续下降
        } elseif (($average_3+$average_7)>=($average_15+$average_30)){
            $data['type'] = 'wave_up';//波动上升
        } elseif (($average_3+$average_7)<($average_15+$average_30)){
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

        $result = Stock::find()->select('left_stock, on_way_stock, stock,available_stock')->where(['sku'=>$data['sku'],'warehouse_code'=>$data['warehouse_code']])->asArray()->one();

        if ($result)
        {
            $avilable_stock          = $result['available_stock'] + $result['on_way_stock'];
            $data['stock_qty']       = $avilable_stock;
            $data['left_stock']      = $result['left_stock'];
            $data['on_way_stock']    = $result['on_way_stock'];
            $data['stock']           = $result['stock'];
            $data['available_stock'] = $result['available_stock'];      //可用库存，暂定为现有库存和在途库存
        } else{

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

        // Vhelper::dump($data);
        $pur_number = PurchaseOrderItems::find()->select('pur_number')->where(['sku'=>$data['sku']])->asArray()->all();

        $total =[];
        foreach($pur_number as $v)
        {
            $order  = PurchaseOrder::find()->where(['pur_number'=>$v['pur_number'],'purchas_status'=>[1,2]])->one();
            if(!empty($order))
            {
                $total[]= $order->pur_number;
            } else{

            }

        }
        $final =0  ;
        foreach($total as $v)
        {
            $num_single = PurchaseOrderItems::find()->select('qty,ctq')->where(['pur_number'=>$v,'sku'=>$data['sku']])->one();
            $finals = $num_single->ctq > 0 ? $num_single->ctq :$num_single->qty;
            $final +=$finals;


        }

        $data['on_way_stock']    = $data['on_way_stock']+$final;
        return $data;
    }

    /**
     * 获取仓库采购策略
     * @param $data
     * @return mixed
     */
    protected function getWarehousepurchasetactic($data)
    {
        $result          = Warehouse::find()->select('pattern')->where(['warehouse_code' => $data['warehouse_code']])->asArray()->one();
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
        $sealmodel                             = New Curd();
        $purchase_sku_single_tactic_main_model = New  PurchaseSkuSingleTacticMain();

        $result          = PurchaseSkuSingleTacticMain::find()->where(['warehouse' => $data['warehouse_code'],'sku'=>$data['sku']])->asArray()->one();
        /* $result                                = $sealmodel->GetData($purchase_sku_single_tactic_main_model,'id','one',"where sku='".$data['sku']."' and warehouse='".$data['warehouse_code']."'");*/

        if($result)
        {
            $data['singletactic']='Y';
            $data['single_tactic_main_id']=$result['id'];
        }else{
            $data['singletactic']='N';
        }
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
     * 根据单独sku设置的补货策略，计算出单个sku需要补货的数量
     * @param $data
     */
    protected function calculateSingleSkuResupplyQty($data)
    {


        $sealmodel                                     = New Curd();
        $purchase_sku_single_tactic_main_content_model = New PurchaseSkuSingleTacticMainContent();
        $purchase_content                              = $sealmodel->GetData($purchase_sku_single_tactic_main_content_model, 'supply_days,minimum_safe_stock_days', 'one', "where single_tactic_main_id='" . $data['single_tactic_main_id'] . "'");

        /**
         * 最低安全库存天数  补货数量计算
         */
        //计算出来的最低补货数量 = 最低安全库存天数*平均销量
        $minimum_calculate_supply_qty = $purchase_content['minimum_safe_stock_days']*$data['average_selling'];
        //目前仓库的库存 = 现在可用的仓库库存 + 在途库存
        $stock_para = $data['on_way_stock'] + $data['available_stock'];
        //
        //需要补货数量为： 计算出来的需要补充的库存的数量减去现在仓库里面可用的库存数量
        $data['minimum_resupply_sku_quantity'] = ceil($minimum_calculate_supply_qty) - $stock_para;
        $left_stock = $data['left_stock'] < 0?abs($data['left_stock']):0;



        /**
         * 补货天数  补货数量计算
         */
        //计算出来的补货数量 = 预计销量*预计销量天数
        $calculate_supply_qty                          = $purchase_content['supply_days']*$data['average_selling'];
        //目前仓库的库存        = 现在可用的仓库库存 + 在途库存
        $stock_para = $data['on_way_stock'] + $data['available_stock'];
        //
        //需要补货数量为： 计算出来的需要补充的库存的数量减去现在仓库里面可用的库存数量
        $data['resupply_sku_quantity'] = ceil($calculate_supply_qty) - $stock_para;
        $left_stock = $data['left_stock'] < 0?abs($data['left_stock']):0;
        //$data['resupply_sku_quantity'] = ($data['resupply_sku_quantity']<0 )? 0 : $data['resupply_sku_quantity'] + $left_stock;



        // 当最低补货数量<0  时，补货数量为0
        // 否则：补货数量为：补货天数的补货数量+欠货
        $data['resupply_sku_quantity'] = ($data['minimum_resupply_sku_quantity']<0 )? 0 : $data['resupply_sku_quantity'] + $left_stock;
        $data['safe_delivery'] = !empty($purchase_content['supply_days']) ? $purchase_content['supply_days'] : 15; //安全交期

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
     * 原补货数量 = (安全库存天数 + 最小补货天数) * 平均销量 - 已有库存; 按照公式来计算
     * @param $data
     * @return mixed
     */

    protected function calculateMinResupplySkuQty($data)
    {
        $sealmodel                     = New Curd();
        $warehouse_min_model           = New WarehouseMin();
        $result                        = $sealmodel->GetData($warehouse_min_model, 'days_safe, days_min,warehouse_code', 'one', "where warehouse_code='" . $data['warehouse_code'] . "'");
        if( ($result['warehouse_code'] == 'SZ_AA') && (!empty($result['days_min']) && $result['days_min']>0) ) {
            /**
             * 最低安全库存天数  补货数量计算
             */
            //最低补货数量 = 最低安全库存天数*平均销量
            $minimum_calculate_supply_qty = $result['days_min']*$data['average_selling'];
            //目前仓库的库存 = 现在可用的仓库库存 + 在途库存
            $stock_para = $data['on_way_stock'] + $data['available_stock'];
            //需要补货数量为： 计算出来的需要补充的库存的数量减去现在仓库里面可用的库存数量
            //需要补货数量 = 最低补货数量 - 目前仓库的库存
            $data['minimum_resupply_sku_quantity'] = ceil($minimum_calculate_supply_qty) - $stock_para;
            //$left_stock = $data['left_stock'] < 0?abs($data['left_stock']):0;

            /**
             * 补货天数  补货数量计算
             */
            //补货天数补货数量 = 安全库存天数*平均销量
            $calculate_supply_qty  = $result['days_safe']*$data['average_selling'];
            //目前仓库的库存 = 现在可用的仓库库存 + 在途库存
            $stock_para = $data['on_way_stock'] + $data['available_stock'];
            //需要补货数量为： 计算出来的需要补充的库存的数量减去现在仓库里面可用的库存数量
            //需要补货数量 = 补货数量 - 目前仓库的库存
            $data['resupply_sku_quantity'] = ceil($calculate_supply_qty) - $stock_para;
            //欠货数量 = 欠货数量<0 ? 欠货数量 : 0
            $left_stock = $data['left_stock'] < 0?abs($data['left_stock']):0;


            // 需要补货数量 = 最低补货数量-目前仓库的库存+欠货
            // 补货天数补货数量+欠货数量
            //获取缺货数量
            $s = StockOwes::find()->select('left_stock')->where(['sku'=> $data['sku'], 'warehouse_code' => 'SZ_AA'])->scalar();
            $s = !empty($s) ? $s : 0;
            
            if ( ($minimum_calculate_supply_qty-$stock_para+$s)  >=0)
            {
                if ($left_stock > 0) {
                    $data['resupply_sku_quantity'] = $calculate_supply_qty+$left_stock;
                } else {
                    
                    $data['resupply_sku_quantity'] = $calculate_supply_qty + $s-$stock_para;
                }
            } else {
                $data['resupply_sku_quantity'] = 0;
            }
            
            $data['safe_delivery'] = $result['days_safe']; //安全交期


            /*$info = [
                'sku' => $data['sku'],
                '最低安全库存天数' => $result['days_min'],
                '平均销量' => $data['average_selling'],
                '最低补货数量' => $minimum_calculate_supply_qty,
                'xix' => '',


                '现在可用的仓库库存' => $data['on_way_stock'],
                '在途库存' => $data['available_stock'],
                '目前仓库的库存' => $stock_para,
                '补货天数补货数量' => $calculate_supply_qty,

                '欠货数量' => $left_stock,
                '缺货数量' => isset($s) ? $s : '未定义',
                '需要补货数量' => $data['resupply_sku_quantity'],


            ];
            vd($info);*/
        } else {
            //计算出来的补货数量 = 预计销量*预计销量天数
            $calculate_supply_qty                          = ($result['days_safe']+$result['days_min'])*$data['average_selling'];
            $data['qty13']                                = (($result['days_safe']+$result['days_min'])-2)*$data['average_selling'];

            //目前仓库的库存        = 现在可用的仓库库存 + 在途库存
            $stock_para = $data['on_way_stock'] + $data['available_stock'];
            //补货数量减去再途数量
            $data['resupply_sku_quantity'] = ceil($calculate_supply_qty) - $stock_para;
            $data['qty13'] = ceil($data['qty13']) - $stock_para;
            $data['safe_delivery']         = $result['days_safe'] + $result['days_min'];
            $left_stock = $data['left_stock'] < 0?abs($data['left_stock']):0;

            if($data['resupply_sku_quantity'] < 0 && $left_stock>0){
                $data['resupply_sku_quantity'] = $left_stock+$calculate_supply_qty;
                $data['qty_13'] = $left_stock+$data['qty13'];
            }else {
                $data['resupply_sku_quantity'] = ($data['resupply_sku_quantity'] < 0) ? 0 : $data['resupply_sku_quantity'] + $left_stock;
                $data['qty_13'] = ($data['qty13'] < 0) ? 0 : $data['qty13'] + $left_stock;
            }
        }
        return $data;
    }


    /**
     * 生成采购建议单
     * @param $data
     */
    protected function GeneratePurchaseList($data)
    {

        //$po_number                      = CommonServices::getNumber('PO');
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
        $product_supply_info = $sealmodel->GetData($product_supplier_model, 'supplier_code, is_supplier, is_exemption,quotes_id, is_push', 'one', "where sku = '" . $data['sku'] . "' and is_supplier=1");
        //根据上面获取到的供应商编号， 从供货商表中获取 供应商名, 支付方式, 结算方式
        $product_supplier_detail_info_model = $sealmodel->GetData($product_supplier_detail_model, 'buyer,supplier_name, payment_method, supplier_settlement', 'one', "where supplier_code='" . $product_supply_info['supplier_code'] . "'");

        //根据sku编号，从产品表中获取产品所属的分类ID, 产品状态
        $product_category_relation_info = $sealmodel->GetData($product_model, 'product_category_id, product_status,uploadimgs', 'one', "where sku='" . $data['sku'] . "'");
        //根据上面获取的产品所属的分类ID,去 产品分类表中获取类目中文名称
        $product_category_info = $sealmodel->GetData($product_category_model, 'category_cn_name', 'one', "where id='" . $product_category_relation_info['product_category_id'] . "'");


        //获取供应商报价
        if (!empty($product_supply_info['quotes_id'])) {
            $supplier_quotes_info = $sealmodel->GetData($supplier_quotes_model, 'supplierprice, currency', 'one', "where id='" . $product_supply_info['quotes_id'] . "'");
        } else {
            $supplier_quotes_info = $sealmodel->GetData($supplier_quotes_model, 'supplierprice, currency', 'one', "where product_sku='" . $data['sku'] . "' and suppliercode='" . $data['supplier_code'] . "'");

        }

        /*//根据sku编号， 去供应商报价表中获取 供应商单价【错误的方法】
        if(empty($product_supply_info))
        {
            $supplier_quotes_info = $sealmodel->GetData($supplier_quotes_model, 'supplierprice, currency', 'one', "where product_sku='" . $data['sku'] . "'");
        } else{
            $supplier_quotes_info = $sealmodel->GetData($supplier_quotes_model, 'supplierprice, currency', 'one', "where id='" . $product_supply_info['quotes_id'] . "'");
        }*/

        //Vhelper::dump($data);
        if(isset($data['resupply_sku_quantity']) && $data['resupply_sku_quantity'] > 0)
        {
            $qty = $data['resupply_sku_quantity'];
        } elseif($data['left_stock']<0 && isset($data['average_selling']) && $data['average_selling'] <0) {
            $qty = abs($data['left_stock']);
        } else{
            $qty = 0;
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
        $add_data=[
            'warehouse_code'      => $data['warehouse_code'],
            'warehouse_name'      => $warehousename['warehouse_name'],
            'sku'                 => $data['sku'],
            'name'                => !empty($data['name']) ?  addslashes($data['name']) : $productinfo['title'],
            'supplier_code'       => !empty($product_supply_info['supplier_code']) ? $product_supply_info['supplier_code'] : $data['supplier_code'],
            'supplier_name'       => !empty($product_supplier_detail_info_model['supplier_name']) ?  $product_supplier_detail_info_model['supplier_name']:$data['supplier_name'],
            'buyer'               => $buyer,
            'buyer_id'            => !empty($buyer_id->id)?$buyer_id->id:'123',
            'replenish_type'      => isset($data['replenish_type'])?$data['replenish_type']:1,
            'qty'                 => $qty,
//            'price'               => $data['price'] ? $data['price'] :'0', //$supplier_quotes_info['supplierprice'],  //通过最新的订单的sku价格
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
            'purchase_type'       => in_array($data['warehouse_code'],['SZ_AA','ZDXNC','HW_XNC']) ? 1 : ($data['warehouse_code']=='FBA_SZ_AA' ? 3 : 1),
            'qty_13'              => isset($data['qty_13'])?$data['qty_13']:$qty-(2*$data['average_selling']),
            'state'               => 0,
            'product_status'      => isset($product_category_relation_info['product_status'])?$product_category_relation_info['product_status']:'100',
            'safe_delivery'       => isset($data['safe_delivery'])?$data['safe_delivery']:12,
        ];
        $rs=$sealmodel->GetData($purchase_suggest_model,'id','one',"where sku='".$data['sku']."' and purchase_type in (1,3) and warehouse_code='".$data['warehouse_code']."'");
        if($rs){
            $sealmodel->UpData($purchase_suggest_model, $add_data, "where sku='".$data['sku']."' and purchase_type in (1,3) and warehouse_code='".$data['warehouse_code']."'");
        } else {
            if(empty($data['warehouse_code']))
            {
                return;
            }
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
            SkuSalesStatisticsTotal::updateAll(['is_suggest'=>1],['sku'=>$data['sku'],'warehouse_code'=>'FBA-WW']);
        }else{
            SkuSalesStatisticsTotal::updateAll(['is_suggest'=>1],['sku'=>$data['sku'],'warehouse_code'=>$data['warehouse_code']]);
        }
        //修改统计为1则不加入运算
        $model_stock = Stock::find()->where(['sku'=>$data['sku'],'warehouse_code'=>$data['warehouse_code']])->one();

        if ($model_stock)
        {
            $model_stock->is_suggest=1;
            $model_stock->save(false);
        }
    }

    /**
     * 更新总欠货为空
     */
    public  function  actionUpdateJiSuan()
    {
        PurchaseSuggest::updateAll(['lack_total'=>null]);
    }
    /**
     * 采购建议计算总欠货
     */
    public function actionJiSuan()
    {

        $limit = 5000;
        $stat = date('Y-m-d 00:00:00',time());
        $end  = date('Y-m-d 23:59:59',time());
        $model = PurchaseSuggest::find()->where(['is_purchase'=>'Y','purchase_type'=>1])->andWhere(['>','qty',0])->andWhere(['in','warehouse_code',['DG','SZ_AA','xnc','ZDXNC','CDxuni','ZMXNC_WM','ZMXNC_EB','HW_XNC']])->andWhere(['between','created_at',$stat,$end])->andWhere(['lack_total' => null])->limit($limit)->all();
        if($model)
        {

            foreach($model as $v)
            {
                $lack_total = $v['on_way_stock']+$v['available_stock']+$v['left_stock'];
                PurchaseSuggest::updateAll(['lack_total'=>$lack_total],['id'=>$v['id']]);
                $lack_total = '';
            }
        } else{


        }

    }
    /**
     * 导入的需求中在历史采购建议中如果存在对应的sku和仓库的数据，就将采购建议数量相加
     */
    public function getSuggestNum($data)
    {
        $sql = "SELECT
	id,sku,purchase_warehouse,purchase_quantity,SUM(purchase_quantity) as sum
FROM
	`pur_purchase_suggest_quantity`
WHERE `suggest_status` = 1 
AND 
";
        $sql .= "(`sku` = '" . $data['sku'] . "') and ";
        $sql .= "(
                `create_time` BETWEEN '" . date('Y-m-d 00:00:00',time()-86400) . "'
                AND '" . date('Y-m-d 23:59:59',time()-86400) . "'
            ) ";
        $sql .= "GROUP BY sku,purchase_warehouse";
        $model = Yii::$app->db->createCommand($sql)->queryAll();

        //如果导入的数据能够查询到
        if (!empty($model)) {
            foreach ($model as $k=>$v) {
                $exists = PurchaseSuggest::find()->where(['sku'=> $v['sku']])->andWhere(['warehouse_code'=>$v['purchase_warehouse']])->one();
                //如果采购建议中存在
                if (!empty($exists)) { //如果采购建议中存在，就将采购数量相加
//                    echo '一';
//                    print_r($v['sum']); echo '&nbsp;';
//                    print_r($data['resupply_sku_quantity']); echo '&nbsp;&nbsp;';
                    $data['resupply_sku_quantity'] = $v['sum'] + $data['resupply_sku_quantity'];
                    PurchaseSuggestQuantity::updateAll(['suggest_status'=>2],['sku'=>$v['sku'],'purchase_warehouse'=>$v['purchase_warehouse'],'suggest_status'=>1]);

                    $this->GeneratePurchaseList($data);
                } else { //如果采购建议中不存在，就新建采购单
//                    echo '二';
//                    print_r($v['sum']); echo '&nbsp;&nbsp;';
//                    $data['resupply_sku_quantity'] = $data['stock_qty'] >0 ?($data['stock_qty'] - $v['purchase_quantity']) : $v['purchase_quantity'];
                    $data['warehouse_code'] = $v['purchase_warehouse'];
                    $data['resupply_sku_quantity'] = $v['sum'];
                    $data['average_selling'] = 0;
                    $data['left_stock'] = 0;
                    PurchaseSuggestQuantity::updateAll(['suggest_status'=>2],['sku'=>$v['sku'],'purchase_warehouse'=>$v['purchase_warehouse'],'suggest_status'=>1]);

                    $this->GeneratePurchaseList($data);
                }
//                PurchaseSuggestQuantity::updateAll(['suggest_status'=>2],['id'=>$v['id']]);
            }
        } else {
//            echo '三';
//            print_r($data['resupply_sku_quantity']); echo '&nbsp;&nbsp;';

            return $data;
        }
//        return $data;
    }
    /**
     * 导入的需求中在历史采购建议中如果存在对应的sku和仓库的数据，就将采购建议数量相加
     */
    public function getSuggestNum1($data)
    {
        $model= PurchaseSuggestQuantity::find()
            ->where(['sku'=>$data['sku']])
//            ->andWhere(['purchase_warehouse'=>$data['warehouse_code']])
            ->andWhere(['suggest_status'=>1])
            ->andWhere(['between','create_time',date('Y-m-d 00:00:00',time()-86400),date('Y-m-d 23:59:59',time()-86400)])
            ->all();
        //如果导入的数据能够查询到
        if (!empty($model)) {
            foreach ($model as $k=>$v) {
                $exists = PurchaseSuggest::find()->where(['sku'=> $v['sku']])->andWhere(['warehouse_code'=>$v['purchase_warehouse']])->one();
                //如果采购建议中存在
                if (!empty($exists)) { //如果采购建议中存在，就将采购数量相加
                    echo '一';
                    print_r($v['purchase_quantity']); echo '&nbsp;';
                    print_r($data['resupply_sku_quantity']); echo '&nbsp;&nbsp;';
                    $data['resupply_sku_quantity'] = $v['purchase_quantity']+$data['resupply_sku_quantity'];
                    PurchaseSuggestQuantity::updateAll(['suggest_status'=>2],['id'=>$v['id']]);

                    $this->GeneratePurchaseList($data);
                } else { //如果采购建议中不存在，就新建采购单
                    echo '二';
                    print_r($v['purchase_quantity']); echo '&nbsp;&nbsp;';
//                    $data['resupply_sku_quantity'] = $data['stock_qty'] >0 ?($data['stock_qty'] - $v['purchase_quantity']) : $v['purchase_quantity'];
                    $data['warehouse_code'] = $v['purchase_warehouse'];
                    $data['resupply_sku_quantity'] = $v['purchase_quantity'];
                    $data['average_selling'] = 0;
                    $data['left_stock'] = 0;
                    PurchaseSuggestQuantity::updateAll(['suggest_status'=>2],['id'=>$v['id']]);

                    $this->GeneratePurchaseList($data);
                }
//                PurchaseSuggestQuantity::updateAll(['suggest_status'=>2],['id'=>$v['id']]);
            }
        } else {
            echo '三';
            print_r($data['resupply_sku_quantity']); echo '&nbsp;&nbsp;';

        }
//        return $data;
    }

    /**
     * 执行计划任务   如果采购建议中有匹配的数据，采购数量就增加，否则 新增采购建议
     * http://caigou.yibainetwork.com/purchase-tactics/purchasing-advice-owe
     */
    public function  actionPurchasingAdviceOwe()
    {
        set_time_limit(0);
        $suggest_res = $this->suggestQuantity();
        $warehouse_res = $this->warehouseQuantity();

        echo $suggest_res . '<br />';
        echo $warehouse_res . '<br />';
    }

    /**
     * 采购系统导入的需求
     */
    public function suggestQuantity()
    {
        $suggest_quantity = PurchaseSuggestQuantity::find()
            ->where(['suggest_status'=>1])
            ->andWhere(['purchase_type'=>1])
            ->andWhere(['between','create_time',date('Y-m-d 00:00:00',time()-86400),date('Y-m-d 23:59:59',time()-86400)])
            ->all();
        $count = count($suggest_quantity);//计算该数组的元素有多少个

        if (empty($suggest_quantity)) {
            return '采购系统导入需求--没有未使用的采购需求！！';
        }

        foreach ($suggest_quantity as $k=>$v) {
            $suggest = PurchaseSuggest::find()
                ->where(['sku'=> $v['sku']])
                ->andWhere(['warehouse_code'=>$v['purchase_warehouse']])
                ->andWhere(['purchase_type'=>1])
                ->andWhere(['between','created_at',date('Y-m-d 00:00:00',time()),date('Y-m-d 23:59:59',time())])
                ->one();

            if (!empty($suggest)) { //如果采购建议中存在，就将采购数量相加
//                echo '一&nbsp;&nbsp;';
//                print_r($v['purchase_quantity']); echo '&nbsp;';
//                print_r($suggest['qty']); echo '&nbsp;&nbsp;';
//                print_r($v['purchase_quantity']+$suggest['qty']);
                $suggest->qty = $v['purchase_quantity']+$suggest['qty'];
                $suggest->created_at = date('Y-m-d H:i:s');
                $suggest->save(false);
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
                $handle_data[$k]['resupply_sku_quantity'] =  $v['purchase_quantity'];
                $handle_data[$k]['suggest_status'] =  2;
                $this->Batchhandledata($handle_data);
            }
            PurchaseSuggestQuantity::updateAll(['suggest_status'=>2],['id'=>$v['id']]);

            if ($k == $count-1) {
                return '采购系统导入需求--已运行完成！！';
            }
        }
    }

    /**
     * 仓库推送过来的需求
     */
    public function warehouseQuantity()
    {
        //仓库导入的需求
        $warehouse_quantity = PurchaseSuggestQuantity::find()
            ->where(['suggest_status'=>1])
            ->andWhere(['purchase_type'=>5])
            ->all();

        $count = count($warehouse_quantity);//计算该数组的元素有多少个

        if (empty($warehouse_quantity)) {
            return '仓库导入需求--没有未使用的采购需求！！';
        }

        foreach ($warehouse_quantity as $k=>$v) {
            $suggest = PurchaseSuggest::find()
                ->where(['sku'=> $v['sku']])
                ->andWhere(['warehouse_code'=>$v['purchase_warehouse']])
                ->andWhere(['purchase_type'=>1])
                ->andWhere(['between','created_at',date('Y-m-d 00:00:00',time()),date('Y-m-d 23:59:59',time())])
                ->one();

            if (!empty($suggest)) { //如果采购建议中存在，就将采购数量相加
//                echo '一&nbsp;&nbsp;';
//                print_r($v['purchase_quantity']); echo '&nbsp;';
//                print_r($suggest['qty']); echo '&nbsp;&nbsp;';
//                print_r($v['purchase_quantity']+$suggest['qty']);
                $suggest->qty = $v['purchase_quantity']+$suggest['qty'];
                $suggest->created_at = date('Y-m-d H:i:s');
                $suggest->save(false);
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
                $handle_data[$k]['resupply_sku_quantity'] =  $v['purchase_quantity'];
                $handle_data[$k]['suggest_status'] =  2;
                $this->Batchhandledata($handle_data);
            }
            if ($k == $count-1) {
                return '仓库导入需求--已运行完成！！';
            }
        }
    }
    
    /**
     * @desc 查询国内仓采购建议条数
     */
    public function actionGetsuggestnumbers()
    {
        $query = PurchaseSuggest::find();
        //$query->select('count(1) as total');
    
        $warehouse_code = PurchaseSuggestQuantityServices::getSuggestWarehouseCode();
        $query->andWhere(['in','pur_purchase_suggest.warehouse_code',$warehouse_code]);
        $query->andWhere(['>','pur_purchase_suggest.qty',0]);
        $query->andWhere(['!=','pur_purchase_suggest.sku','XJFH0000']);
        $query->andWhere(['in','pur_purchase_suggest.purchase_type',1]);
        //判断创建时间
        if(empty($this->start_time) || empty($this->end_time))
        {
            $start_time = date('Y-m-d 00:00:00');
            $end_time   = date('Y-m-d 23:59:59');
            $query->andFilterWhere(['between', 'pur_purchase_suggest.created_at', $start_time, $end_time]);
        }
        $query->andWhere(['not in','pur_purchase_suggest.product_status',['0','5','6','7','100']]);
        echo 'Total:' . $query->count();
        exit;
    }
}