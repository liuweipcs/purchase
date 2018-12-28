<?php

namespace app\api\v1\controllers;
use app\api\v1\models\ApiPageCircle;
use app\api\v1\models\ArrivalRecord;
use app\api\v1\models\FreightUpdate;
use app\api\v1\models\HwcAvgDeliveryTime;
use app\api\v1\models\ProductCategory;
use app\api\v1\models\ProductCombine;
use app\api\v1\models\ProductDescription;
use app\api\v1\models\Product;
use app\api\v1\models\ProductLine;
use app\api\v1\models\ProductProvider;
use app\api\v1\models\PurchaseOrder;
use app\api\v1\models\PurchaseOrderItems;
use app\api\v1\models\PurchaseOrderShip;
use app\api\v1\models\SkuMonthAvg;
use app\api\v1\models\SkuMonthAvgLog;
use app\api\v1\models\SkuSalesStatistics;
use app\api\v1\models\Stock;
use app\api\v1\models\Supplier;
use app\api\v1\models\SupplierQuotes;
use app\api\v1\models\SupplierSyncLog;
use app\models\PurchaseSuggest;
use app\models\PurchaseSuggestHistory;
use app\models\PurchaseSuggestMrp;
use app\models\PurchaseSuggestHistoryMrp;
use app\models\SampleInspect;
use app\models\SupplierBuyer;
use app\models\SupplierNum;
use yii;
use app\config\Vhelper;
use linslin\yii2\curl;
use yii\helpers\Json;
use app\models\ProductTicketedPointLog;
use app\models\PurchaseSuggestTask;
use app\models\ProductTaxRate;
use app\models\ProductRepackage;

/**
 * 产品
 * Created by PhpStorm.
 * User: ztt
 * Date: 2017/3/23 0023
 * Time: 18:41
 */
class ProductController extends BaseController
{
    public $modelClass = 'app\api\v1\models\Product';


    public function actionCreateProduct()
    {
        /*
        $datas = [];
        $datas[] = ['sku'=>'CW00007','tax_rate'=>0.16,'declare_cname'=>'test','declare_unit'=>'坨','quality_random'=>'A','quality_level'=>'A',
            'product_linelist_id'=>1,'product_category_id'=>1,'product_status'=>1,'uploadimgs'=>'','create_time'=>'2017-01-01 00:00:00',
            'product_cost'=>2.01,'create_id'=>0,'product_cn_link'=>'','product_en_link'=>'','product_type'=>1,'product_package_code'=>'',
            'purchase_packaging'=>'','product_is_multi'=>0,'create_user_id'=>1,'product_to_way_package'=>''
        ];
        $datas = json_encode($datas);
        */
        $datas       = Yii::$app->request->post()['ProductToPurchaseInfo'];
        if(!empty($datas))
        {
            $datas       = Json::decode($datas);
            $data        = Product::FindOnes($datas);

            echo json_encode($data);
            exit;
        } else {
            echo '没有任何的数据过来！';
            exit;
        }
    }

    /**
     * 接受数据中心过来的产品类目
     */
    public function actionCreateProductCate()
    {
        $datas       = Yii::$app->request->post()['categoryToPurchaseInfo'];
        if(!empty($datas))
        {
            $datas       = Json::decode($datas);
            $data        = ProductCategory::FindOnes($datas);

            return $data;
        } else {
            return '没有任何的数据过来！';
        }

    }
    /**
     * 接受数据中心过来的捆绑产品
     */
    public function actionCreateBinding()
    {
        $datas       = Yii::$app->request->post()['ProductCombine'];
        if(!empty($datas))
        {
            $datas       = Json::decode($datas);
            $data        = ProductCombine::FindOnes($datas);
            return $data;
        } else {
            return '没有任何的数据过来！';
        }

    }

    /**
     * 接受erp过来的产品线
     */
    public function actionGetProductLine()
    {
        $url = Yii::$app->params['ERP_URL'].'/services/products/product/productlinelist';
        $curl  = new curl\Curl();
        $s       = $curl->post($url);
        //验证json
        $sb = Vhelper::is_json($s);
        if(!$sb)
        {
            echo '请检查json'."\r\n";
            exit($s);
        } else {
            $_result = Json::decode($s);
            $status  = ProductLine::Saves($_result);
            if($status)
            {
                exit('数据拉取成功');

            } else{
                exit('数据拉取失败');
            }
        }


    }

    /**
     *      获取erp销量 每请求一次先从表里面找一下页码,按当天的时间去匹配，如果没有找到即为新的一天的销量,先删除后增加
     */
    public function  actionGetSkuSales()
    {
        ini_set('memory_limit', '1096M');
        set_time_limit(1800);
        $start =  strtotime(date('Y-m-d 00:00:00',time()));
        $end   =  strtotime(date('Y-m-d H:i:s'));
        $thread = (int)\yii::$app->request->get('thread');
        $page = (int)\yii::$app->request->get('page');
        if ($thread <= 0)
            $thread = 1;
        $startTime = date('Y-m-d');
        $endTime = $startTime . ' 23:59:59';
        $startTime = $startTime . ' 00:00:00';
        //$is= SupplierNum::find()->select('num')->where(['type'=>8])->andWhere(['between','time',$start,$end])->orderBy('id desc')->scalar();
        $pageInfos = [];
        $totalPage = 0;
        //查询当天数据总页数
        $totalPage = PurchaseSuggestTask::find()->select('total_page')
            ->where(['task_name' => PurchaseSuggestTask::TASK_GET_SKU_SALES])
            ->andWhere(['between', 'create_time', $startTime, $endTime])
            ->andWhere("total_page > 0")
            ->limit(1)
            ->scalar();
        if (empty($totalPage))
            $totalPage = 0;
        if (empty($totalPage))
            SkuSalesStatistics::deleteAll();        //删除之前数据
        if(!empty($page))
        {
            //检查当前页是不是正在拉取或者已经拉取成功，则获取下页页数
            if (!PurchaseSuggestTask::checkRunable(PurchaseSuggestTask::TASK_GET_SKU_SALES,
                 $page, $startTime, $endTime))
            {
                $page += $thread;
                $url = '/v1/product/get-sku-sales?page=' . $page . '&thread=' . $thread;
                Vhelper::throwTheader($url);
                exit('DONE');
            }
            //检查当前页有没有超过总页数
            if (!empty($totalPage) && $page > $totalPage)
                exit('DONE');
            $dateTime = date('Y-m-d H:i:s');
            $PurchaseSuggestTaskModel = new PurchaseSuggestTask;
            $PurchaseSuggestTaskModel->task_name = PurchaseSuggestTask::TASK_GET_SKU_SALES;
            $PurchaseSuggestTaskModel->task_status = PurchaseSuggestTask::TASK_STATUS_RUNNING;
            $PurchaseSuggestTaskModel->execute_time = $dateTime;
            $PurchaseSuggestTaskModel->create_time = $dateTime;
            $PurchaseSuggestTaskModel->page = $page;
            $PurchaseSuggestTaskModel->thread_number = $thread;
            if (!empty($PurchaseSuggestTaskModel->save()))
            {
                try 
                {
                    $url = Yii::$app->params['ERP_URL'] . '/services/api/Product/index/method/getSkuSalesStatistics?page='.$page;
                    $curl  = new curl\Curl();
                    $s       = $curl->post($url);
                    $_result = Json::decode($s);
                    if (empty($_result))
                    {
                        $PurchaseSuggestTaskModel->task_status = PurchaseSuggestTask::TASK_STATUS_FAILED;
                        $PurchaseSuggestTaskModel->error_message = 'Server Response Empty';
                    }
                    else
                    {
                        $status = SkuSalesStatistics::batchInsertData($_result['datas']);
                        $PurchaseSuggestTaskModel->total_page = isset($_result['totalPage']) ?
                            (int)$_result['totalPage'] : 0;
                        if ($status)
                            $PurchaseSuggestTaskModel->task_status = PurchaseSuggestTask::TASK_STATUS_SUCCESS;
                        else
                        {
                            $PurchaseSuggestTaskModel->task_status = PurchaseSuggestTask::TASK_STATUS_FAILED;
                            $PurchaseSuggestTaskModel->error_message = 'Save Data Failed';
                        }
                    }
                    $PurchaseSuggestTaskModel->end_time = date('Y-m-d H:i:s');
                    $PurchaseSuggestTaskModel->save();
                }
                catch (\Exception $e)
                {
                    $PurchaseSuggestTaskModel->end_time = date('Y-m-d H:i:s');
                    $PurchaseSuggestTaskModel->task_status = PurchaseSuggestTask::TASK_STATUS_FAILED;
                    $PurchaseSuggestTaskModel->error_message = $e->getMessage();
                    $PurchaseSuggestTaskModel->save();
                }
                //取下一页
                $page += $thread;
                $url = '/v1/product/get-sku-sales?page=' . $page . '&thread=' . $thread;
                Vhelper::throwTheader($url);
                exit('DONE');
            }
            else
            {
                exit('DONE');
            }
        }
        else
        {
            //判断整个拉取任务是否完成
            if (PurchaseSuggestTask::checkTaskDone(PurchaseSuggestTask::TASK_GET_SKU_SALES, $startTime, $endTime))
                exit('Task Has Completed');
            
            for ($i=1;$i<=$thread;$i++)
            {
                $page = $i;
                //检查当前页是不是正在拉取或者已经拉取成功，则获取下页页数
                if (!PurchaseSuggestTask::checkRunable(PurchaseSuggestTask::TASK_GET_SKU_SALES,
                    $i, $startTime, $endTime))
                    $page += $thread;
                $url = '/v1/product/get-sku-sales?page=' . $page . '&thread=' . $thread;
                Vhelper::throwTheader($url);
                usleep(100);
            }
            exit('DONE');
        }
    }
    

    /**
     *      获取erp销量 每请求一次先从表里面找一下页码,按当天的时间去匹配，如果没有找到即为新的一天的销量,先删除后增加
     */
    public function  actionGetSkuSales_BAK($id=1)
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(1800);
        $start =  strtotime(date('Y-m-d 00:00:00',time()));
        $end   =  strtotime(date('Y-m-d H:i:s'));
        $is= SupplierNum::find()->select('num')->where(['type'=>8])->andWhere(['between','time',$start,$end])->orderBy('id desc')->scalar();

        if(!empty($is))
        {
            $id = $is;
        } else{
            $id = $id;
            SkuSalesStatistics::deleteAll();
            SupplierNum::deleteAll(['type'=>8]);
        }
        $url = Yii::$app->params['ERP_URL'].'/services/api/Product/index/method/getSkuSalesStatistics?page='.$id;

        $curl  = new curl\Curl();
        $s       = $curl->post($url);

        //验证json
        $sb = Vhelper::is_json($s);
        if(!$sb)
        {
            echo '请检查json'."\r\n";
            exit($s);
        } else {
            $_result = Json::decode($s);
            $status = SkuSalesStatistics::FindOnes($_result['datas']);
            if(in_array(1,$status))
            {
                $id       = $_result['page']+1;
                $mod      = new SupplierNum();
                $mod->num = $id;
                $mod->type = 8;
                $mod->time = time();
                $mod->save(false);
                $this->actionGetSkuSales();

            } else{
                $id       = $_result['page'];
                $mod      = new SupplierNum();
                $mod->num = $id;
                $mod->type = 8;
                $mod->time = time();
                $mod->save(false);
            }
        }
    }

    /**
     * 拉取产品
     */
    public function actionGetProduct()
    {
        exit();
        set_time_limit(50000);
        $is= SupplierNum::find()->select('num')->where(['type'=>5])->orderBy('id desc')->scalar();
        if(!empty($is))
        {
            $id = $is;
        } else{
            $id = 1;
        }
        //for ($i=$id;$i<=140;$i++) {

        //$curl  = new curl\Curl();
        $datas = [
            'token' => 'b24fe215-7a7b-4e83-85be-e917d59eef18',
            'data'  => [
                'merchantId' => '003498',
                'pageNo'     => $id,
                'productType'=> '0',
            ],
        ];
        try {
            $url     = Yii::$app->params['tongtool'] . '/process/resume/openapi/tongtool/goodsQuery';
//            $s       = $curl->setPostParams([
//                'q' => Json::encode($datas),
//            ])->post($url);
            $s = Vhelper::postResult($url,$datas);
            //验证json
            $sb = Vhelper::is_json($s);
            if(!$sb)
            {
                echo '请检查json'."\r\n";
                exit($s);
            } else {
                $_result = Json::decode($s);
                if (!is_array($_result['data']['array']))
                {
                    $mod      = new SupplierNum();
                    $mod->num = $id;
                    $mod->type = 5;
                    $mod->time = time();
                    $mod->save(false);
                    exit();
                } else {
                    SupplierQuotes::SaveTongTools($_result['data']['array']);
                    $mod      = new SupplierNum();
                    $mod->num = $id + 1;
                    $mod->type = 5;
                    $mod->time = time();
                    $mod->save(false);
                }
            }

        } catch (Exception $e) {

            exit('发生了错误');
        }
        //}
    }

    /**
     * 获取erp产品默认供应商及报价，采购链接
     * http://www.purchase.net/v1/product/get-product-supplier?sku=TJ08159
     */
    public function actionGetProductSupplier()
    {
        Yii::$app->response->format = 'raw';
        set_time_limit(0);
        $pageSize = 100;
        $sku = Yii::$app->request->getQueryParam('sku','');
        $debug = Yii::$app->request->getQueryParam('debug','');
        if(!empty($sku)){
            $data = Product::findBySql("SELECT t.sku,t.id FROM pur_product as t 
                            LEFT JOIN (SELECT * FROM pur_product_supplier WHERE is_supplier IN (1,2)) as a ON t.sku=a.sku 
                            WHERE  isNull(a.id) AND t.sku = '$sku' AND t.product_is_multi != 2 
                            AND t.product_type !=2 AND t.product_status NOT IN (0,7) 
                            AND (t.create_time > '2017-01-01 00:00:00' OR (t.product_status != 1 AND t.create_time<'2017-01-01 00:00:00')) 
                            ORDER BY t.id ASC  ")
                ->asArray()
                ->all();
        }else{
            $count = SupplierSyncLog::find()->where(['supplier_status' => '100'])->count();
            if(empty($count)){// 缓存需要同步的 SKU 插入队列（supplier_status=100）
                $connection     = Yii::$app->db;
                $latest_time    = date('Y-m-d H:i:s',strtotime(' - 2 hours'));// 两个小时内查询过的不再查询（避免查询失败的查询太频繁阻塞进程）

                // 插入不存在的记录 （0.审核不通过,7.已停售）
                $sql_insert     = " INSERT INTO  pur_supplier_sync_log(sku,supplier_status,sync_time) 
                            SELECT t.sku,'100','0000-00-00 00:00:00' 
                            FROM pur_product as t 
                            WHERE (SELECT COUNT(1) FROM pur_product_supplier AS a WHERE a.is_supplier IN (1,2) AND t.sku=a.sku)=0
                            AND t.product_is_multi != 2 AND t.product_type !=2 
                            AND t.product_status NOT IN (0,7) 
                            AND t.sku NOT IN(SELECT sku FROM pur_supplier_sync_log WHERE sync_time>'{$latest_time}')
                            AND (t.create_time > '2017-01-01 00:00:00' OR (t.product_status != 1 AND t.create_time<'2017-01-01 00:00:00'))";
                $command        = $connection->createCommand($sql_insert);
                $res            = $command->execute();
            }

            // 按批次查询 SKU，每次查询100个
            $data = SupplierSyncLog::find()
                ->select('sku')
                ->where(['supplier_status' => '100'])
                ->limit($pageSize)
                ->asArray()
                ->all();
        }
        if(empty($data)){
            ApiPageCircle::insertNewPage(0,'PRODUCT_SUPPLIER_OFFSET');
            SupplierSyncLog::deleteAll(['supplier_status' => '100']);// 缓存需要同步的 SKU 删除
        }else{
            ApiPageCircle::insertNewPage(count($data),'PRODUCT_SUPPLIER_OFFSET');
        }
        $this->getSupplierInfo(array_column($data,'sku'),$debug);
        echo '同步：'.count($data).'个';
        exit;
    }

    protected function getSupplierInfo($sku,$debug = 0){
        if($debug) print_r($sku);

        $curl = new curl\Curl();
        $url = Yii::$app->params['ERP_URL'].'/services/products/product/getsupplierinfo';
        if($debug) echo '<br/>'.$url.'<br/>';

        $s = $curl->setPostParams([
            'skus' => $sku
        ])->post($url);
        if($debug){// 调试信息
            echo '<pre/>打印返回的数据：<br/>';
            print_r(json_decode($s));
        }
        if (!empty(json_decode($s)->data)) {
            foreach (json_decode($s)->data as $value) {
                SupplierSyncLog::deleteAll(['sku' => $value->sku,'supplier_status' => '100']);// SKU 从队列中删除

                if (empty($value->provider)) {
                    SupplierSyncLog::saveOne($value->sku, $value->provider, '1');
                    continue;
                }
                $supplier = Supplier::find()->where(['supplier_name' => $value->provider,'status'=>1])->one();
                if (!empty($supplier)) {
                    //优先选择未被禁用供应商
                    SupplierSyncLog::saveOne($value->sku, $value->provider, '4');
                    $supplierCode = $supplier->supplier_code;
                } else {
                    $deleteSupplier = Supplier::find()->where(['supplier_name' => $value->provider])->one();
                    if(!empty($deleteSupplier)){
                        if(in_array($deleteSupplier->status,[0,4,5])){// 4.待审核  5.审核不通过 的供应商不能启用
                            SupplierSyncLog::saveOne($value->sku, $value->provider, '1');
                            continue;
                        }

                        //将禁用供应商启用
                        $deleteSupplier->status =1;
                        $deleteSupplier->save(false);
                        SupplierSyncLog::saveOne($value->sku, $value->provider, '4');
                        $supplierCode = $deleteSupplier->supplier_code;
                    }else{
                        //禁用启用都不存在则进行新增
                        $supplierCode = $this->actionGetSupplier($value->sku, $value->provider);
                        if ($supplierCode == false) {
                            continue;
                        }
                    }
                }
                // 判断是否退税、税点
                $tax_rate_model = ProductTaxRate::findOne(['sku' => $value->sku]);
                $tax_rate       = isset($tax_rate_model->tax_rate)?$tax_rate_model->tax_rate:0;
                $ticketed_point = isset($value->ticketed_point)?$value->ticketed_point:null;// 税点
                $is_back_tax    = Vhelper::getProductIsBackTax($tax_rate, $ticketed_point);// 是否退税

                $quotes = new SupplierQuotes();
                $quotes->product_sku = $value->sku;
                $quotes->supplierprice = $value->cost;
                $quotes->supplier_product_address = $value->link;
                $quotes->suppliercode = $supplierCode;
                $quotes->currency = 'RMB';
                $quotes->default_buyer = 1;
                $quotes->add_time = time();
                $quotes->default_Merchandiser = 1;
                $quotes->add_user = 1;
                $quotes->status = 1;
                $quotes->is_back_tax = $is_back_tax;
                if($ticketed_point !== null) $quotes->pur_ticketed_point = $ticketed_point;// 税点
                $quotes->save(false);
                ProductProvider::updateAll(['is_supplier'=>0],['sku'=>$value->sku]);
                $model = ProductProvider::find()->where(['sku' => $value->sku, 'supplier_code' => $supplierCode])->one();
                if (empty($model)) {
                    $model = new ProductProvider();
                }
                $model->sku = $value->sku;
                $model->is_supplier = 1;
                $model->supplier_code = $supplierCode;
                $model->quotes_id = $quotes->attributes['id'];
                $model->save(false);

                // 添加日志
                \app\models\ChangeLog::addLog([
                                                  'oper_id'   => $quotes->attributes['id'],
                                                  'oper_type' => 'SupplierQuotes',
                                                  'content'   => '获取ERP默认供应商和税点',
                                                  'is_show'   => 2,
                                              ]);
            }
        }
    }

    //对报价中的供应商采购系统不存在则进行添加，并绑定国内仓采购员王开伟
    public function actionGetSupplier($sku,$name){
        set_time_limit(0);
        $curl  = new curl\Curl();
        $url     = Yii::$app->params['ERP_URL'].'/services/purchase/purchase/supplierlist';
        $v       = $curl->setPostParams([
            'name' =>$name,
        ])->post($url);
        if(Vhelper::is_json($v)==false){
            return false;
        }
        $datas=json_decode($v)->data;
        if(isset($datas) && !empty($datas))
        {
            $model = new Supplier();
            $data   = Supplier::SaveOne($model,$datas);
            $buyerModel = new  SupplierBuyer();
            $buyerModel->buyer = '王开伟';
            $buyerModel->type  = 1;
            $buyerModel->status = 1;
            $buyerModel->supplier_code = $data;
            $buyerModel->supplier_name = $name;
            $buyerModel->save(false);
            SupplierSyncLog::saveOne($sku,$name,3);
            return $data;
        } else {
            SupplierSyncLog::saveOne($sku,$name,2);
            return false;
        }

    }

    //每晚11点拉取采购建议的数据，作为历史数据使用（采购建议表的数据每天更新成新数据）
    public function actionGetSuggest(){//die;
        set_time_limit(0);
        ini_set('memory_limit','999M');
        $total_data=PurchaseSuggest::find()->count();
        $limit=5000;
        $page_num=ceil($total_data/$limit);

        for($i=0; $i<=$page_num; $i++){
            $page=$i>0 ? $i*$limit-1 : 0;
            $suggest=PurchaseSuggest::find()->offset($page)->limit($limit)->asArray()->all();

            if(!empty($suggest)){
                foreach($suggest as $val){
                    $model=new PurchaseSuggestHistory();
                    $model->warehouse_code=$val['warehouse_code'];
                    $model->warehouse_name=$val['warehouse_name'];
                    $model->sku=$val['sku'];
                    $model->name=$val['name'];
                    $model->supplier_code=$val['supplier_code'];
                    $model->supplier_name=$val['supplier_name'];
                    $model->buyer=$val['buyer'];
                    $model->buyer_id=$val['buyer_id'];
                    $model->replenish_type=$val['replenish_type'];
                    $model->qty=$val['qty'];
                    $model->price=$val['price'];
                    $model->currency=$val['currency'];
                    $model->payment_method=$val['payment_method'];
                    $model->supplier_settlement=$val['supplier_settlement'];
                    $model->ship_method=$val['ship_method'];
                    $model->is_purchase=$val['is_purchase'];
                    $model->created_at=$val['created_at'];
                    $model->creator=$val['creator'];
                    $model->product_category_id=$val['product_category_id'];
                    $model->category_cn_name=$val['category_cn_name'];
                    $model->on_way_stock=$val['on_way_stock'];
                    $model->available_stock=$val['available_stock'];
                    $model->stock=$val['stock'];
                    $model->left_stock=$val['left_stock'];
                    $model->days_sales_3=$val['days_sales_3'];
                    $model->days_sales_7=$val['days_sales_7'];
                    $model->days_sales_15=$val['days_sales_15'];
                    $model->days_sales_30=$val['days_sales_30'];
                    $model->sales_avg=$val['sales_avg'];
                    $model->type=$val['type'];
                    $model->safe_delivery=$val['safe_delivery'];
                    $model->product_img=$val['product_img'];
                    $model->transit_code=$val['transit_code'];
                    $model->purchase_type=$val['purchase_type'];
                    $model->demand_number=$val['demand_number'];
                    $model->state=$val['state'];
                    $model->weighted_3=$val['weighted_3'];
                    $model->weighted_7=$val['weighted_7'];
                    $model->weighted_15=$val['weighted_15'];
                    $model->weighted_30=$val['weighted_30'];
                    $model->weighted_60=$val['weighted_60'];
                    $model->weighted_90=$val['weighted_90'];
                    $model->product_status=$val['product_status'];
                    $model->create_time=date('Y-m-d H:i:s',time());
                    $model->save(false);
                }
                unset($model);
                unset($suggest);
            }else{
                continue;
            }
        }

        exit('success');
    }

    //每晚11点15拉取采购建议(MRP)的数据，作为历史数据使用（采购建议表的数据每天更新成新数据）
    public function actionGetSuggestMrp(){//die;
        set_time_limit(0);
        ini_set('memory_limit','999M');
        $date = Yii::$app->request->getQueryParam('date',date('Y-m-d 00:00:00'));
        $sql = "INSERT INTO pur_purchase_suggest_history_mrp (data_id,sku,`name`,warehouse_code,warehouse_name,supplier_code,supplier_name,buyer,
	      buyer_id,replenish_type,qty,price,currency,payment_method,supplier_settlement,ship_method,is_purchase,created_at,creator,product_category_id,
	      category_cn_name,on_way_stock,available_stock,stock,left_stock,days_sales_3,days_sales_7,days_sales_15,days_sales_30,sales_avg,`type`,safe_delivery,
	      product_img,transit_code,purchase_type,demand_number,state,weighted_3,weighted_7,weighted_15,weighted_30,weighted_60,weighted_90,product_status,
	      lack_total,qty_13,is_change,untreated_time,new_price_limit,new_stock_hold,is_new,suggestion_logic,stock_logic_type,create_time)
		SELECT
		   id,sku,`name`,warehouse_code,warehouse_name,supplier_code,supplier_name,buyer,
	      buyer_id,replenish_type,qty,price,currency,payment_method,supplier_settlement,ship_method,is_purchase,created_at,creator,product_category_id,
	      category_cn_name,on_way_stock,available_stock,stock,left_stock,days_sales_3,days_sales_7,days_sales_15,days_sales_30,sales_avg,`type`,safe_delivery,
	      product_img,transit_code,purchase_type,demand_number,state,weighted_3,weighted_7,weighted_15,weighted_30,weighted_60,weighted_90,product_status,
	      lack_total,qty_13,is_change,untreated_time,new_price_limit,new_stock_hold,is_new,suggestion_logic,stock_logic_type,now()
		FROM
			pur_purchase_suggest_mrp
		WHERE
			created_at >'%s'";
        $sql=sprintf($sql,$date);
        $insert = Yii::$app->db->createCommand($sql)->execute();
    }

    //计算传入月份月平均采购成本和运费成本 date:计算月'2018-01'
    public function actionCalculateMonthAvg($date){
        set_time_limit(0);
//        $nowDate = date('Y-m-d',time());
//        $lastDate = date('Y-m-t',time());
//        if($nowDate!=$lastDate){
//            //如果当前时间不是月底最后一天则退出
//            exit();
//        }
        //$date = date('Y-m-01',time());
       // $dateArray=['2017-12-01','2018-01-01','2018-02-01','2018-03-01','2018-04-01'];
        $skuDatas = ArrivalRecord::find()
            ->alias('t')
            ->select(['sku'=>'p.sku'])
            ->leftJoin(PurchaseOrder::tableName().' a','a.pur_number=t.purchase_order_no')
            ->leftJoin(Product::tableName().' p','p.sku=t.sku') //利用查询不区分大小写解决大小写问题
            ->where(['between','t.delivery_time',date('Y-m-01 00:00:00',strtotime($date)),date('Y-m-t 23:59:59',strtotime($date))])
            ->andWhere(['<>','a.warehouse_code','de-yida'])
            ->asArray()
            ->all();
        $uniqueArray = array_unique(array_column($skuDatas,'sku'));
        $freightArray = FreightUpdate::find()->asArray()->all();
        $existFreight = yii\helpers\ArrayHelper::map($freightArray,'pur_number','avg_freight');
        $circleArray = array_chunk($uniqueArray,1000);
        foreach ($circleArray as $skus){
            $exist = SkuMonthAvg::find()->select('sku')->where(['month'=>date('Y-m-01',strtotime($date))])->andWhere(['in','sku',$skus])->asArray()->all();
            $existSku = array_column($exist,'sku');
            $skus = array_diff($skus,$existSku);
            if(empty($skus)){
                continue;
            }
            $insertData=[];
            $logInsert =[];
            //获取满足条件的到货数据
            $monthPurchaseData = ArrivalRecord::find()
                ->alias('t')
                ->select(['sku'=>'p.sku','delivery_qty'=>'ifnull(t.delivery_qty,0)','bad_products_qty'=>'ifnull(t.bad_products_qty,0)','price'=>'ifnull(a.price,0)','purchase_order_no'=>'t.purchase_order_no'])
                ->leftJoin(PurchaseOrderItems::tableName().' a','a.pur_number=t.purchase_order_no and a.sku=t.sku')
                ->leftJoin(PurchaseOrder::tableName().' b','t.purchase_order_no=b.pur_number')
                ->leftJoin(Product::tableName().' p','t.sku=p.sku')
                ->where(['in','t.sku',$skus])
                ->andWhere('NOT isnull(a.price)')
                ->andWhere(['<>','b.warehouse_code','de-yida'])
                ->andWhere(['between','t.delivery_time',date('Y-m-01 00:00:00',strtotime($date)),date('Y-m-t 23:59:59',strtotime($date))])
                ->asArray()
                ->all();

            //获取当月到货样品采购单数据
            $sampInspect = SampleInspect::find()
                            ->alias('t')
                            ->select(['pur_number'=>'a.purchase_order_no'])
                            ->leftJoin(ArrivalRecord::tableName().' a','a.purchase_order_no=t.pur_number and a.sku=t.sku')
                            ->andWhere(['between','a.delivery_time',date('Y-m-01 00:00:00',strtotime($date)),date('Y-m-t 23:59:59',strtotime($date))])
                            ->asArray()
                            ->all();
            $monthPurchaseCost=[];
            $arrive=[];
            $purchaseOrderArray = [];
            foreach ($monthPurchaseData as $value){
                //样品采购单则跳过
                if(!empty($sampInspect)&&in_array($value['purchase_order_no'],array_column($sampInspect,'pur_number'))){
                    continue;
                }
                //获取月采购到货总金额
                $monthPurchaseCost[$value['sku']] = isset($monthPurchaseCost[$value['sku']]) ? $monthPurchaseCost[$value['sku']] + ($value['delivery_qty']-$value['bad_products_qty'])*$value['price'] : ($value['delivery_qty']-$value['bad_products_qty'])*$value['price'];
                //获取月采购到货总数量
                $arrive[$value['sku']] = isset($arrive[$value['sku']]) ? $arrive[$value['sku']]+($value['delivery_qty']-$value['bad_products_qty']) : ($value['delivery_qty']-$value['bad_products_qty']);
                //到货采购单号
                $purchaseOrderArray[$value['sku']][]=$value['purchase_order_no'];
            }
            $arriveFre=[];
            $arriveCtq=[];
            foreach ($purchaseOrderArray as $skuK=>$skuOrderArray) {
                //获取该sku到货的所有采购单号
                $arrivePurnumber = array_unique($skuOrderArray);
                //根据采购单号获取每个满足条件的采购单总采购数量用于计算运费比例
                $items = PurchaseOrderItems::find()->select(['pur_number' => 'pur_number', 'ctq' => 'IFNULL(ctq,0)'])->where(['in', 'pur_number', $arrivePurnumber])->asArray()->all();
                foreach ($items as $item) {
                    $arriveCtq[$skuK][$item['pur_number']] = isset($arriveCtq[$skuK][$item['pur_number']]) ? $arriveCtq[$skuK][$item['pur_number']] + $item['ctq'] : $item['ctq'];
                }
                //根据采购单号获取每个满足条件的采购单总运费
                $ships = PurchaseOrderShip::find()->select(['pur_number' => 'pur_number', 'freight' => 'IFNULL(freight,0)'])->where(['in', 'pur_number', $arrivePurnumber])->asArray()->all();
                foreach ($ships as $ship) {
                    $arriveFre[$skuK][$ship['pur_number']] = isset($arriveFre[$skuK][$ship['pur_number']]) ? $arriveFre[$skuK][$ship['pur_number']] + $ship['freight'] : $ship['freight'];
                }
            }
            $freight=[];
            foreach ($monthPurchaseData as $value){
                if(!isset($arriveCtq[$value['sku']])){
                    continue;
                }
                if(isset($existFreight[$value['purchase_order_no']])){
                    $arriveFre[$value['sku']][$value['purchase_order_no']] =$arriveCtq[$value['sku']][$value['purchase_order_no']]*$existFreight[$value['purchase_order_no']];
                }
                $nowFreight = isset($arriveFre[$value['sku']][$value['purchase_order_no']]) ? ($value['delivery_qty']-$value['bad_products_qty'])/$arriveCtq[$value['sku']][$value['purchase_order_no']]*$arriveFre[$value['sku']][$value['purchase_order_no']] : 0 ;
                $freight[$value['sku']] = isset($freight[$value['sku']]) ? $freight[$value['sku']] + $nowFreight : $nowFreight;
            }
            //获取计算时sku的可用加在途
            $stockData = Stock::find()
                ->select(['sku'=>'sku','on_way_stock'=>'IFNULL(on_way_stock,0)','available_stock'=>'IFNULL(available_stock,0)'])
                ->where(['in','sku',$skus])
                ->asArray()
                ->all();
            $stock=[];
            foreach ($stockData as $s){
                $stock[$s['sku']] = isset($stock[$s['sku']]) ? $stock[$s['sku']]+($s['on_way_stock']+$s['available_stock']) : ($s['on_way_stock']+$s['available_stock']);
            }
            //获取sku最近月份数据
            $nearestMonthData   = SkuMonthAvg::find()
                ->select(['sku'=>'sku','month_avg_purchase'=>'IFNULL(month_avg_purchase,0)','month_avg_freight'=>'IFNULL(month_avg_freight,0)'])
                ->andWhere(['in','sku',$skus])
                ->andWhere(['<','month',date('Y-m-01',strtotime($date))])
                ->orderBy('id DESC')
                ->all();
            $lastMonthPurchaseAvg =[];
            $lastMonthFreightAvg =[];
            foreach ($nearestMonthData as $nearData){
                //获取sku最近月平均采购成本
                $lastMonthPurchaseAvg[$nearData['sku']]   = $nearData->month_avg_purchase;
                //获取sku最近月平均运费成本
                $lastMonthFreightAvg[$nearData['sku']]    =  $nearData->month_avg_freight;
            }
            //数组保存数据记录和日志记录
            foreach ($skus as $k=>$sku){
                if(!isset($arrive[$sku])||$arrive[$sku]==0||!isset($monthPurchaseCost[$sku])){
                    continue;
                }
                $skuLastMonthAvg = isset($lastMonthPurchaseAvg[$sku]) ? $lastMonthPurchaseAvg[$sku] : 0;
                $skuStock        = isset($stock[$sku]) ? $stock[$sku] : 0;
                $skuLastFreightAvg = isset($lastMonthFreightAvg[$sku]) ? $lastMonthFreightAvg[$sku] :0;
                $skufreight = isset($freight[$sku]) ? $freight[$sku] :0;
                $skuData = self::getAvgResult($monthPurchaseCost[$sku],$skuStock,$skuLastMonthAvg,$arrive[$sku],$skuLastFreightAvg,$skufreight);

                $insertData[$k][]=$sku;
                $insertData[$k][]=date('Y-m-01',strtotime($date));
                $insertData[$k][]=$skuData['avg_purchase'];
                $insertData[$k][]=$skuData['avg_freight'];
                $insertData[$k][]=date('Y-m-d H:i:s',time());

                $logInsert[$k][]=$sku;
                $logInsert[$k][]=$skuLastMonthAvg;
                $logInsert[$k][]=$skuData['avg_purchase'];
                $logInsert[$k][]=$skuLastFreightAvg;
                $logInsert[$k][]=$skuData['avg_freight'];
                $logInsert[$k][]=$skuStock;
                $logInsert[$k][]=$skufreight;
                $logInsert[$k][]=$monthPurchaseCost[$sku];
                $logInsert[$k][]=$arrive[$sku];
                $logInsert[$k][]=$date;
                $logInsert[$k][]=date('Y-m-d H:i:s',time());
            }
            //批量插入数据和日志
            Yii::$app->db->createCommand()->batchInsert(SkuMonthAvg::tableName(),['sku','month','month_avg_purchase','month_avg_freight','cacu_date'],$insertData)->execute();
            Yii::$app->db->createCommand()->batchInsert(SkuMonthAvgLog::tableName(),
                ['sku','last_month_purchase_avg','this_month_purchase_avg','last_month_freight_avg','this_month_freight_avg','stock','freight','purchase_cost','purchase_num','calc_date','create_date'],$logInsert)->execute();
            unset($monthPurchaseData);
            unset($purchaseData);
            unset($freightData);
            unset($insertData);
            unset($logInsert);
        }
    }
//获取sku月平均采购成本和月平均运费成本
/*
 * params:
 *  $monthPurchaseCost
 *  $stock
 *  $lastMonthPurchaseAvg
 *  $monthPurchaseCost
 *  $monthPurchaseCost
 */
    protected static function getAvgResult($monthPurchaseCost,$stock,$lastMonthPurchaseAvg,$arrive,$lastMonthFreightAvg,$freight){
        $result = $lastMonthPurchaseAvg==0 ? floatval($monthPurchaseCost)/floatval($arrive) : (floatval($lastMonthPurchaseAvg)*floatval($stock)+floatval($monthPurchaseCost))/(floatval($arrive)+$stock);
        $data['avg_purchase'] = ceil(sprintf( "%.4f ",$result)*1000)/1000;
        $result = $lastMonthFreightAvg==0 ? $freight/$arrive : ($lastMonthFreightAvg*$stock+$freight)/($arrive+$stock);
        $data['avg_freight'] = ceil(sprintf( "%.4f ",$result)*1000)/1000;
        return $data;
    }


    public function actionPushMonthAvg(){
        $date =  Yii::$app->request->getQueryParam('date');
        $data['month']= date('Y-m',strtotime($date));
        $data['data'] = SkuMonthAvg::find()->select('sku,month_avg_purchase,month_avg_freight')->where(['month'=>date('Y-m-01',strtotime($date))])->asArray()->all();
        return $data;
    }

    //获取海外仓平均到货时间
    public function actionGetHwcAvgArrival(){
        exit();
        set_time_limit(0);
        $data = Product::find()
            ->select('sku')
            ->where(['like','sku','%US',false])
            ->orWhere(['like','sku','US%',false])
            ->orWhere(['like','sku','%AU',false])
            ->orWhere(['like','sku','AU%',false])
            ->orWhere(['like','sku','%GB',false])
            ->orWhere(['like','sku','GB%',false])
            ->orWhere(['like','sku','%DE',false])
            ->orWhere(['like','sku','DE%',false])
            ->orWhere(['like','sku','%ES',false])
            ->orWhere(['like','sku','ES%',false])
            ->orWhere(['like','sku','%RU',false])
            ->orWhere(['like','sku','RU%',false])
            ->orWhere(['like','sku','%AU',false])
            ->orWhere(['like','sku','AU%',false])->asArray()->all();
        $hwcSku = HwcAvgDeliveryTime::find()->select('sku')->asArray()->all();
        $insertSku = array_diff(array_column($data,'sku'),array_column($hwcSku,'sku'));
        $insertData=[];
        foreach ($insertSku as $k=>$sku){
            $insertData[$k][]=$sku;
            $insertData[$k][]=0;
            $insertData[$k][]=0;
            $insertData[$k][]=0;
        }
        Yii::$app->db->createCommand()->batchInsert(HwcAvgDeliveryTime::tableName(),['sku','avg_delivery_time','delivery_total','purchase_time'],$insertData)->execute();
        $datas = PurchaseOrderItems::find()
            ->select('t.sku,t.pur_number,order.audit_time,ifnull(orderArrival.delivery_time,unix_timestamp(now())) as delivery_time')
            ->alias('t')
            ->leftJoin(PurchaseOrder::tableName().' order','order.pur_number=t.pur_number')
            ->leftJoin(ArrivalRecord::tableName().' orderArrival','t.sku=orderArrival.sku and t.pur_number=orderArrival.purchase_order_no')
            ->where('t.pur_number like "ABD%"')
            ->andWhere(['not in','order.purchas_status',[4,10]])
            ->andWhere(['or',['<','t.avg_time_check_time',time()-5*24*60*60],['t.avg_time_check_time'=>null]])
            ->orderBy('orderArrival.delivery_time DESC')
            ->groupBy('t.sku,t.pur_number')
            ->createCommand()->getRawSql();
        echo $datas;
        exit();
          //  ->asArray()->all();
        var_dump($datas);
        exit();
//        foreach ($arrive as $value){
//            $exist =  HwcAvgDeliveryTime::find()->where(['sku'=>$value['sku']])->exists();
//            if($exist){
//                self::getAvgDeliveryTime($value['sku'],$value['purchase_order_no'],$value['delivery_time']);
//            }
//            ArrivalRecord::updateAll(['is_caculate'=>1],['id'=>$value['id']]);
//        }
    }

    public static function  getAvgDeliveryTime($sku,$pur_number,$deliveryTime){
        $haveCalcul = ArrivalRecord::find()->where(['sku'=>$sku,'purchase_order_no'=>$pur_number,'is_caculate'=>1])->orderBy('delivery_time DESC')->one();
        if($haveCalcul){
            $diffArriveTime = strtotime($deliveryTime)-strtotime($haveCalcul->delivery_time);
            HwcAvgDeliveryTime::updateAllCounters(['delivery_total'=>$diffArriveTime],['sku'=>$sku]);
        }else{
            $auditTime = PurchaseOrder::find()->select('audit_time')->where(['pur_number'=>$pur_number])->scalar();
            if($auditTime){
                $arriveTime = strtotime($deliveryTime)-strtotime($auditTime);
                HwcAvgDeliveryTime::updateAllCounters(['delivery_total'=>$arriveTime,'purchase_time'=>1],['sku'=>$sku]);
            }else{
                $arriveTime = time()-strtotime($auditTime);
                HwcAvgDeliveryTime::updateAllCounters(['delivery_total'=>$arriveTime,'purchase_time'=>1],['sku'=>$sku]);
            }
        }
        HwcAvgDeliveryTime::updateAll(['is_push'=>0],['sku'=>$sku,'is_push'=>1]);
    }

    public function actionPushAvgArrive(){
        $query = HwcAvgDeliveryTime::find()->select('id,sku,delivery_total,purchase_time')->where(['is_push'=>0])->limit(1000)->asarray()->all();
        if(empty($query)){
            exit('没有需要推送的数据');
        }
        $url = Yii::$app->params['server_ip'] . '/index.php/purchases/receiveDeliveryTimeData';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//这个是重点。
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array(
            'deliveryTimeData'=>json_encode($query)
        ));
        $output = curl_exec($ch);
        curl_close($ch);
        if(Vhelper::is_json($output)){
            $successList = json_decode($output)->data->success_list;
            HwcAvgDeliveryTime::updateAll(['is_push'=>1],['id'=>$successList,'is_push'=>0]);
        }else{
            exit('error');
        }
    }

    public function actionGetErpSupplier(){
        $sku= json_decode(Yii::$app->request->getBodyParam('sku'));
        if(!empty($sku)){
            $havePurchaseSku =  PurchaseOrderItems::find()
                ->alias('t')
                ->select('t.sku')
                ->leftJoin(PurchaseOrder::tableName().' o','o.pur_number=t.pur_number')
                ->where(['in','o.purchas_status',[1,2,3,5,6,7,8,9]])
                ->where(['in','t.sku',$sku])
                ->column();
            $updateSku = array_diff($sku,array_unique($havePurchaseSku));
            $this->getSupplierInfo($updateSku);
        }
        echo json_encode(['success'=>$sku]);
        exit();
    }
    
    /**
     * 推送税点到erp purchase_billing_points
     * http://www.purchase.net/v1/product/push-tax-point
     */
    public function actionPushTaxPoint() {
        $debug    = Yii::$app->request->getQueryParam('debug','');
        $datalist = ProductTicketedPointLog::find()->where(['is_push'=>0])->orderBy("id asc")->limit(1000)->all();
        if(empty($datalist)) exit('没有需要推送的数据！');

        $params = $ids = [];
        foreach ($datalist as $v) {
            $sku = strtoupper($v['sku']);
            $params[$sku]['sku'] = $v['sku'];
            $params[$sku]['whether_to_do_tax_rebates'] = $v['is_back_tax'] == 1 ? 1 : 2;
            $params[$sku]['purchase_billing_points'] = $v['pur_ticketed_point'];
            $params[$sku]['id'] = $v['id'];
        }
        $pushUrl = Yii::$app->params['ERP_URL'].'/services/products/product/getticketedpoint';
        echo '<pre><br/>'.$pushUrl.'<br/>';

        $curl = new curl\Curl();
        $s = $curl->setPostParams([
            'info' => Json::encode(array_values($params)),
            'token' => Json::encode(Vhelper::stockAuth()),
        ])->post($pushUrl);
        //vd($curl->getInfo());
        //echo $s;exit;
        //验证json

        $sb = Vhelper::is_json($s);
	
        if($s == '发生了错误') {
            echo '请检查json'."\r\n";
            exit($s);
        } else {
            $res = Json::decode($s);
            if($debug){
                print_r($res);
                echo '<br/>';
            }

            if(!empty($res)){
				foreach($res as $vl){
					ProductTicketedPointLog::updateAll(['is_push'=>1,'push_time' => date('Y-m-d H:i:s')], ['and',['=','sku',$vl['sku']],['<=','id',$vl['id']]]);
				}

				echo '推送成功个数：'.count($res).'<br/>';
            }
        }

        exit('推送成功');
    }
    
    public function actionGetSkuCustom()
    {
        $data = Yii::$app->request->post('custom');
        $data = json_decode($data, true);
        if (empty($data)) {
            exit(jsonReturn(0,'数据异常'));
        }
        //$data = ['AC00028-WD-XS' => ['sku'=>'GS00023','id'=>666,'declare_unit'=>'eee','export_cname'=>'eeeddd','tax_rate'=>12],];
        $success_list = $fail_list = [];
        foreach ($data as $v) {
            $sku = $v['sku'];
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $model = Product::findOne(['sku'=>$sku]);
                if (empty($model)) {
                    $success_list[] = $sku;
                    continue;
                }
                $model->tax_rate = round($v['tax_rate'],2);
                $model->declare_unit = empty($v['declare_unit']) ? '' : $v['declare_unit'];
                $model->export_cname = empty($v['export_cname']) ? '' : $v['export_cname'];
                $model->save(false);
                
                $tax_model = ProductTaxRate::find()->where(['sku'=>$sku])->one();
                if (empty($tax_model)) {
                    $tax_model = new ProductTaxRate();
                    $tax_model->sku = $sku;
                    $tax_model->create_time = date('Y-m-d H:i:s',time());
                }
                $tax_model->tax_rate = $model->tax_rate;
                $tax_model->update_time = date('Y-m-d H:i:s',time());
                $tax_model->save(false);
                
                $supplier_model = ProductProvider::find()->where(['sku'=>$sku,'is_supplier'=>1])->one();
                if (!empty($supplier_model)) {
                    $quote_model = SupplierQuotes::find()->select('id,pur_ticketed_point,is_back_tax')->where(['id'=>$supplier_model->quotes_id])->one();
                    if ($quote_model && $quote_model->pur_ticketed_point > 0) {
                        $is_back_tax = Vhelper::getProductIsBackTax($model->tax_rate, $quote_model->pur_ticketed_point);
                        if ($is_back_tax != $quote_model->is_back_tax) {
                            $quote_model->is_back_tax = $is_back_tax;
                            $quote_model->save(false);
                            ProductTicketedPointLog::insertLog($sku, $quote_model->pur_ticketed_point, $is_back_tax);
                        }
                    }
                }
                $transaction->commit();
                $success_list[] = $sku;
            } catch (Exception $e) {
                $transaction->rollBack();
                $fail_list[] = $sku;
            }
        }
        exit(jsonReturn(1,'success',['success_list'=>$success_list,'fail_list'=>$fail_list]));
    }


    /**
     * 即时接收 ERP同步过来的产品 数据
     */
    public function actionReceiveProductInfo(){
        $data = Yii::$app->request->getBodyParam('data');
        $data = json_decode($data,true);

        if(!empty($data) AND $data['sku']){
            $sku            = $data['sku'];
            $product_cost   = $data['product_cost'];
            $new_price      = $data['new_price'];

            // 修改 SKU默认供应商的单价
            $defaultSupplier    = ProductProvider::findOne(['sku' => $sku,'is_supplier' => 1]);
            if($defaultSupplier){
                $quotes_id          = $defaultSupplier->quotes_id;
                $supplierQuotes     = SupplierQuotes::findOne(['id' => $quotes_id]);
                if($supplierQuotes->supplierprice != $new_price){
                    $supplierQuotes->supplierprice = $new_price;// 更新供应商报价
                    $res = $supplierQuotes->save(false);

                    if($res){
                        // 修改 国内仓、待确认状态下采购单（采购计划单）
                        $purchaseList = PurchaseOrder::find()
                            ->select('p_o_i.id')
                            ->from(PurchaseOrder::tableName().' AS p_o')
                            ->innerJoin(PurchaseOrderItems::tableName() .' AS p_o_i',"p_o_i.pur_number=p_o.pur_number")
                            ->where(['p_o.purchase_type' => 1])
                            ->andWhere(['p_o.purchas_status' => 1])
                            ->andWhere(['p_o_i.sku' => $sku])
                            ->asArray()
                            ->column();

                        $push_data = ['price' => $new_price,'base_price' => $new_price];
                        if($purchaseList){ PurchaseOrderItems::updateAll($push_data,['in','id', $purchaseList]);}

                        $push_data = ['price' => $new_price];
                        // 改变当天 未生成采购单的 采购建议
                        $date = date('Y-m-d');
                        PurchaseSuggest::updateAll($push_data,"is_purchase='N' AND state=0 AND sku='{$sku}' AND LEFT(created_at,10)='$date'");

//                        if($product_cost){
//                            // 更新 产品成本价
//                            Product::updateAll(['product_cost' => $product_cost],"sku='{$sku}'");
//                        }
                    }
                }
            }

        }

        echo json_encode(['status' => 'success']);
        exit();
    }


    /**
     * 推送产品 加重标记（重包精新）信息到 数据中心
     * @return string
     * http://caigou.yibainetwork.com/v1/product/push-sku-weight-mark-to-data-center Used:8s
     */
    public function actionPushSkuWeightMarkToDataCenter(){
        $init   = Yii::$app->request->getQueryParam('init',0);
        $sku    = Yii::$app->request->getQueryParam('sku', '');// 是否执行指定 SKU

        set_time_limit(0);
        ini_set('memory_limit', '512M');

        // 验证表是否存在
        $connection     = Yii::$app->db;

        if($init == 1){// 初始化数据表
            // 初始化缺失的数据
            $time           = date('Y-m-d H:i:s');
            $sql_insert     = "INSERT INTO pur_cache_product_mark_data(sku,is_weightdot,is_boutique,is_repackage,is_new,update_time)
                                SELECT sku,is_weightdot,is_boutique,is_repackage,(SELECT COUNT(1) FROM pur_fab_purchase_order_trace AS c_data 
                                WHERE data_type=3 AND c_data.sku=pur_product.sku) AS is_new,'$time'
                                FROM pur_product 
                                WHERE sku NOT IN(SELECT sku FROM pur_cache_product_mark_data)";

            $command        = $connection->createCommand($sql_insert);
            $res            = $command->execute();
            echo '插入数据：'.($res).'<br/>';


            // 删除重复SKU数据，保留ID最小的
            $sql_delete     = "DELETE FROM pur_cache_product_mark_data
                             WHERE id IN(
                                SELECT id FROM (
                                    SELECT id FROM pur_cache_product_mark_data
                                    WHERE  sku IN (SELECT sku     FROM pur_cache_product_mark_data GROUP BY sku HAVING COUNT(1) > 1)
                                    AND id NOT IN (SELECT MIN(id) FROM pur_cache_product_mark_data GROUP BY sku HAVING COUNT(1) > 1) 
                                ) AS tmp
                             )";

            $command        = $connection->createCommand($sql_delete);
            $res            = $command->execute();

            echo '删除重复SKU数据，保留ID最小的：'.($res).'<br/>';

            $res            = Product::skuIsNewMarkUpdate();
            echo '更新SKU加重标记是否是新品：'.($res).'<br/>';

            $res            = Product::skuIsWeightDot();
            echo '更新SKU加重标记是否是重点SKU：'.($res).'<br/>';

            echo '<br/><br/>Success';
            exit;
        }


        // 推送数据
        for($i = 0; $i < 5; $i++){
            //偏移量用于截取数组
            $page_size  = 300;
            $offset     = ApiPageCircle::find()->select('page')->where(['type' => 'PRODUCT_WEIGHT_MARK_OFFSET'])->orderBy('id DESC')->scalar();
            if (!$offset) {
                $offset = 0;
                Product::skuIsNewMarkUpdate();// 更新 SKU 加重标记  是否是新品
            }
            if (!empty($sku)) {
                $data_list = Product::findBySql("SELECT sku,is_weightdot,is_boutique,is_repackage,is_new FROM pur_cache_product_mark_data WHERE sku='$sku' ")
                    ->asArray()
                    ->all();

                $res = $this->do_push_mark_to_dc($data_list);
                print_r($data_list);

                echo $res.'<br/>';
                break;
            } else {
                $data_list = Product::findBySql("SELECT sku,is_weightdot,is_boutique,is_repackage,is_new FROM pur_cache_product_mark_data WHERE is_sync=0 ORDER BY id ASC limit $offset,$page_size")
                    ->asArray()
                    ->all();

                if (empty($data_list)) {
                    ApiPageCircle::insertNewPage(0, 'PRODUCT_WEIGHT_MARK_OFFSET');
                    echo '推送完成';
                } else {
                    $res = $this->do_push_mark_to_dc($data_list);

                    ApiPageCircle::insertNewPage($offset + $page_size, 'PRODUCT_WEIGHT_MARK_OFFSET');
                }
            }
        }

        echo '数据推送完成';
        exit;
    }

    /**
     * 【执行推送】推送产品 加重标记（重包精新）信息到 数据中心
     * @param array $data_list 数据列表
     * @return string
     */
    public function do_push_mark_to_dc($data_list)
    {
        $curl   = new curl\Curl();
        $url    = Yii::$app->params['server_ip'] . '/index.php/purchases/purchaseSkuMarkToMysql';

        if(empty($data_list)){
            return 'Not found results';
        }

        try {
            // 执行推送数据
            $s = $curl->setPostParams([
                'purchase_sku'  => Json::encode($data_list),
                'token'         => Json::encode(Vhelper::stockAuth()),
            ])->post($url);

            //验证json
            $sb = Vhelper::is_json($s);

            if(!$sb)
            {
                echo '请检查json'."\r\n";
                exit($s);
            } else {
                // 回写推送结果
                $_result = Json::decode($s);
                if ($_result['success_list'] && !empty($_result['success_list'])) {
                    // print_r($_result['success_list']);exit;

                    // 更新 执行结果
                    $time           = date('Y-m-d H:i:s');

                    $sku_arr        = array_values($_result['success_list']);
                    $sku_str        = implode("','",$sku_arr);

                    $connection     = Yii::$app->db;

                    $sql_update     = "UPDATE pur_cache_product_mark_data SET is_sync=1,sync_time='$time' WHERE sku IN('$sku_str')";
                    $command        = $connection->createCommand($sql_update);
                    $res            = $command->execute();

                    return '推送成功，个数 '.count($_result['success_list']);
                } else {
                    return 0;
                }
            }
        } catch (\Exception $e) {
            return '发生了错误';
        }
    }


}
