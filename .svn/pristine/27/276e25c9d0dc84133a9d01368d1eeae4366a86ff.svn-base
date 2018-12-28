<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/12
 * Time: 16:32
 */
namespace app\api\v1\controllers;

use app\api\v1\models\ApiPageCircle;
use app\api\v1\models\ApiRequestLog;
use app\api\v1\models\OverseasDemandRule;
use app\api\v1\models\PlatformSummary;
use app\api\v1\models\Product;
use app\api\v1\models\ProductProvider;
use app\api\v1\models\ProductSourceStatus;
use app\api\v1\models\PurchaseDemand;
use app\api\v1\models\PurchaseOrder;
use app\api\v1\models\PurchaseOrderItems;
use app\api\v1\models\PurchaseOrderTaxes;
use app\api\v1\models\SkuBindInfo;
use app\api\v1\models\Supplier;
use app\api\v1\models\SupplierQuotes;
use app\config\Vhelper;
use app\models\OverseasDemandPassRule;
use app\models\PurchaseAvg;
use app\models\PurchaseOrderPayType;
use app\models\PurchaseSuggest;
use app\services\SupplierServices;
use linslin\yii2\curl\Curl;
use Yii;
use yii\db\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;


class ErpSyncController extends BaseController {
    //推送供应商信息到erp（同步指定的供应商，同步待推送的供应商 self::actionAutoPushSupplierSimpleInfoToErp）
    public function actionPushSupplierInfoToErp($supplier_name){
        $supplier_data = Supplier::find()->select('supplier_name,supplier_code,supplier_type,status')
                        ->andWhere(['supplier_name'=>$supplier_name,'status'=>1])->asArray()->all();
        if(empty($supplier_data)){
            exit('没有需要推送的数据');
        }
        $url = Yii::$app->params['SKU_ERP_URL'].'/services/api/purchase/addsupplier/method/AddSingleSupp';
        $curl = new Curl();
        $response = $curl->setPostParams(['supplier'=>json_encode($supplier_data)])->post($url);
        $status='no-status';
        if($response){
            $status='success';
            $successDatas = Vhelper::getSqlArrayString(array_column($supplier_data,'supplier_code'));
            Supplier::updateAll(['is_push_to_erp'=>1],"supplier_code in ({$successDatas})");
        }else{
            $status='error';
        }
        Yii::$app->db->createCommand()->insert(ApiRequestLog::tableName(),['post_content'=>json_encode($supplier_data),
            'response_content'=>serialize($response),'create_time'=>date('Y-m-d H:i:s',time()),'api_url'=>'erp-sync/push-supplier-info-to-erp','status'=>$status])->execute();
        Yii::$app->end('推送成功');
    }

    //推送sku绑定供应商及价格信息到erp
    public function actionPushProductSupplierToErp(){
        $datas = Product::find()->alias('t')
            ->select('b.id,t.sku,b.supplier_code,c.supplierprice,d.supplier_name,d.invoice')
            ->leftJoin(ProductProvider::tableName() . ' b', 't.sku=b.sku')
            ->leftJoin(SupplierQuotes::tableName() . ' c', 'b.quotes_id=c.id')
            ->leftJoin(Supplier::tableName() . ' d', 'b.supplier_code=d.supplier_code')
            ->where(['b.is_supplier' => 1])
            ->andWhere(['b.is_push_to_erp' => 0])
            ->limit(500)
            ->asArray()->all();
        if (empty($datas)) {
            exit('没有需要推送的数据');
        }
        $logData[] = json_encode($datas);
        $logData[] = date('Y-m-d H:i:s', time());
        $logData[] = 'erp-sync/push-product-supplier-to-erp';
        $url = Yii::$app->params['SKU_ERP_URL'] . '/services/amazon/amazonskuowinventory/SyncSupplier';
        $curl = new Curl();
        $curl->setOption(CURLOPT_TIMEOUT,120);
        $response = $curl->setPostParams(['supplier' => json_encode($datas)])->post($url);
        $response_content = '';
        $status = 'no-status';
        if (Vhelper::is_json($response)) {
            $response_content = $response;
            $responseDatas = json_decode($response);
            if (!empty($responseDatas->success) && is_array($responseDatas->success)) {
                $ids = Vhelper::getSqlArrayString($responseDatas->success);
                ProductProvider::updateAll(['is_push_to_erp' => 1],"id in ({$ids})");
                $status = 'success';
            } else {
                $status = 'error';
            }
        } else {
            $response_content = serialize($response);
            $status = 'error';
        }
        Yii::$app->db->createCommand()->insert(ApiRequestLog::tableName(), ['post_content' => json_encode($datas),
            'response_content' => $response_content, 'create_time' => date('Y-m-d H:i:s', time()), 'api_url' => 'erp-sync/push-product-supplier-to-erp', 'status' => $status])->execute();
        Yii::$app->end('数据推送成功');
    }

    //推送海外仓拦截规则到erp
    public function actionPushDemandRuleToErp(){
        $demandRuleDatas= OverseasDemandRule::find()->andWhere(['is_push_to_erp'=>0])->asArray()->all();
//        $passRuleDatas  = OverseasDemandPassRule::find()->where(['status'=>1])->andWhere(['is_push_to_erp'=>0])->asArray()->all();
        if(empty($demandRuleDatas)&&empty($passRuleDatas)){
            exit('没有需要推送的数据');
        }
        $url = Yii::$app->params['ERP_URL'].'/services/amazon/amazonskuowinventory/SyncDemandRule';
        $curl = new Curl();
        $response = $curl->setPostParams(['demand_rule'=>json_encode($demandRuleDatas)])->post($url);
        $response_content = '';
        $status = 'no-status';
        if(Vhelper::is_json($response)){
            $response_content= $response;
            $responseDatas = json_decode($response);

            if(!empty($responseDatas->success)&&is_array($responseDatas->success)){
                $ids = Vhelper::getSqlArrayString($responseDatas->success);
                OverseasDemandRule::updateAll(['is_push_to_erp'=>1],"id in ({$ids})");
                $status='success';
            }else{
                $status='error';
            }
        }else{
            $response_content=serialize($response);
            $status='error';
        }
        Yii::$app->db->createCommand()->insert(ApiRequestLog::tableName(),['post_content'=>json_encode($demandRuleDatas),
            'response_content'=>$response_content,'create_time'=>date('Y-m-d H:i:s',time()),'api_url'=>'erp-sync/push-demand-rule-to-erp','status'=>$status])->execute();
        Yii::$app->end('数据推送成功');
    }

    //推送产品货源状态到erp
    public function actionPushSourceStatusToErp(){
        $sourceDatas = ProductSourceStatus::find()->select('sku,sourcing_status')->andFilterWhere(['status'=>1,'is_push_to_erp'=>0])->limit(500)->asArray()->all();
        if(empty($sourceDatas)){
            exit('没有需要推送的数据');
        }
        $postData=[];
        foreach ($sourceDatas as $key=>$data){
            if(isset($data['sourcing_status'])&&$data['sourcing_status']==1){
                $postData[1][]=$data['sku'];
            }
            if(isset($data['sourcing_status'])&&$data['sourcing_status']==2){
                $postData[2][]=$data['sku'];
            }
            if(isset($data['sourcing_status'])&&$data['sourcing_status']==3){
                $postData[3][]=$data['sku'];
            }
        }
        $url = Yii::$app->params['ERP_URL'].'/services/products/product/getproviderstatus';
        //$url = 'http://www.yibai.com/services/products/product/getproviderstatus';
        $curl = new Curl();
        $response = $curl->setPostParams(['source'=>json_encode($postData)])->post($url);
        $response_content = '';
        $status = 'no-status';
        if($response&&Vhelper::is_json($response)){
            $response_content= $response;
            $responseDatas = json_decode($response);
            if(property_exists($responseDatas,'success')&&is_array($responseDatas->success)){
                $skus = Vhelper::getSqlArrayString($responseDatas->success);
                ProductSourceStatus::updateAll(['is_push_to_erp'=>1],"sku in ({$skus}) and status=1 and is_push_to_erp=0");
                $status='success';
            }else{
                $status='error';
            }
        }else{
            $response_content=serialize($response);
            $status='error';
        }
        Yii::$app->db->createCommand()->insert(ApiRequestLog::tableName(),['post_content'=>json_encode($postData),
            'response_content'=>$response_content,'create_time'=>date('Y-m-d H:i:s',time()),'api_url'=>'erp-sync/push-source-status-to-erp','status'=>$status])->execute();
        Yii::$app->end('数据推送成功');
    }

    public  function actionPushPurchasingData(){
        set_time_limit(300);
        $datas = PlatformSummary::find()
            ->select(['sku'=>'p.sku','num'=>'t.purchase_quantity'])
            ->alias('t')
            ->leftJoin(PurchaseDemand::tableName().' pd','pd.demand_number=t.demand_number')
            ->leftJoin(Product::tableName().' p','t.sku=p.sku')
            ->where('isnull(pd.id)')
            ->andWhere(['in','t.level_audit_status',[0,1,6,7]])
            ->asArray()->all();
        $orderDatas = PlatformSummary::find()
            ->select(['sku'=>'p.sku','qty'=>'ifnull(pt.qty,0)','ctq'=>'ifnull(pt.ctq,0)'])
            ->alias('t')
            ->leftJoin(PurchaseDemand::tableName().' pd','pd.demand_number=t.demand_number')
            ->leftJoin(Product::tableName().' p','t.sku=p.sku')
            ->leftJoin(PurchaseOrder::tableName().' o','o.pur_number=pd.pur_number')
            ->leftJoin(PurchaseOrderItems::tableName().' pt','pt.sku=t.sku and pt.pur_number=o.pur_number ')
            ->where(['t.purchase_type'=>2])
            ->andWhere(['t.level_audit_status'=>1])
            ->andWhere('not isnull(pd.id)')
            ->andWhere(['in','o.purchas_status',[1,2]])
            ->groupBy('pt.id')->asArray()->all();
        $skuArray=[];
        if(!empty($datas)){
            foreach ($datas as $key=>$value){
                $skuArray[$value['sku']] = isset($skuArray[$value['sku']]) ? $skuArray[$value['sku']] + $value['num'] : $value['num']+0;
            }
        }
        if(!empty($orderDatas)){
            foreach ($orderDatas as $k=>$v){
                if(empty($v['qty'])&&empty($v['ctq'])){
                    continue;
                }
                $v['qty'] = intval($v['qty']);
                $v['ctq'] = intval($v['ctq']);
                $skuArray[$v['sku']] = isset($skuArray[$v['sku']]) ? ($v['ctq']==0 ? $skuArray[$v['sku']]+$v['qty'] : $skuArray[$v['sku']]+$v['ctq']) : ($v['ctq']==0 ? $v['qty']+0 :$v['ctq']+0);
            }
        }
        //var_dump($datas);
        var_dump($skuArray);
        exit();
    }

    /*
     * 抓取erp_sku绑定关系
     */
    public function actionGetSkuFamily(){
        $page = ApiPageCircle::find()->select('page')
                ->where(['type'=>'ERP_SKU_BIND_INFO'])
                ->orderBy('id DESC')->scalar();
        if(!$page){
            $page =0;
        }
        $url = Yii::$app->params['ERP_URL'].'/services/products/product/skumulti';
        $curl = new Curl();
        $datas = $curl->setPostParams(['page'=>$page])->post($url);
        if(empty($datas)){
            exit('没有数据需要抓取');
        }
        if($datas&&Vhelper::is_json($datas)){
            $status='success';
            $response_content= $datas;
            $responseDatas = json_decode($datas,true);
            SkuBindInfo::saveDatas($responseDatas);
            if(count($responseDatas)==1000){
                ApiPageCircle::insertNewPage($page+1,'ERP_SKU_BIND_INFO');
            }
        }else{
            $response_content=serialize($datas);
            $status='error';
        }
        Yii::$app->db->createCommand()->insert(ApiRequestLog::tableName(),['post_content'=>$page,
            'response_content'=>$response_content,'create_time'=>date('Y-m-d H:i:s',time()),'api_url'=>'erp-sync/get-sku-family','status'=>$status])->execute();
        Yii::$app->end('数据抓取成功');
    }

    public function actionTest($pur_number=null){
        if(!empty($pur_number)){
            PurchaseOrder::updateOrderStatus($pur_number);exit('状态更新成功');
        }
        //修复bug造成的数据错误2018-08-17王瑞
        $purchase = ['PO258208','PO264667','PO279085','PO280353','PO286023','PO286945','PO288389','PO291440','PO292116','PO293087','PO293679','PO295201','PO295490','PO295745','PO296002','PO296235','PO296894','PO297144','PO297144','FBA034378','FBA034508','PO298362','PO299031','PO299086','PO299203','PO299258','PO299585','PO300054','PO300123','PO300765','PO300765','PO300837','PO300969','FBA034942','FBA034942','FBA034942','PO301341','PO302030','PO302224','PO302386','PO302386','PO303363','PO303440','PO303632','PO304542','PO304659','PO304659','PO305203'];
        $purchase = array_unique($purchase);
        foreach ($purchase as $value){
            PurchaseOrder::updateOrderStatus($value);
        }

    }
    //返回sku需求信息
    public function actionGetSkuSummaryInfo(){
        $platCode = Yii::$app->request->getQueryParam('plat_code','');
        $sku = Yii::$app->request->getQueryParam('sku','');
        $warehouseCode = Yii::$app->request->getQueryParam('warehouse_code','');
        if(empty($sku)||empty($warehouseCode)){
            echo json_encode(['status'=>'error','data'=>[],'message'=>'关键参数为空']);
            Yii::$app->end();
        }
        $purchaseSuggestNum = PurchaseSuggest::find()
                                ->alias('t')
                                ->leftJoin(PlatformSummary::tableName().' p','p.demand_number=t.demand_number')
                                ->where(['t.is_purchase'=>'Y'])
                                ->andWhere(['t.sku'=>$sku])
                                ->andWhere(['t.warehouse_code'=>$warehouseCode])
                                ->andWhere(['p.level_audit_status'=>1])
                                ->andFilterWhere(['p.platform_number'=>$platCode])
                                ->sum('qty');
        $purchaseSuggestNum = empty($purchaseSuggestNum) ? 0 : $purchaseSuggestNum;
        echo json_encode(['status'=>'success',
            'data'=>['sku'=>$sku,'warehouse_code'=>$warehouseCode,'plat_code'=>$platCode,'suggest_num'=>$purchaseSuggestNum],
            'message'=>'数据请求成功']);
        Yii::$app->end();
    }

    //推送采购开票点到数据中心，写好未使用
    public function actionPushOrderTaxesToDataCenter(){
        exit();
        $url = 'http://middleware.com/index.php/Purchases/insertPurchaseRateToMysql';
        $datas = PurchaseOrderTaxes::find()
            ->where(['is_push_to_data_center'=>0])->limit(1000)->asArray()->all();
        $curl = new Curl();
        $curl->setPostParams(['taxeDatas'=>json_encode($datas)]);
        $response = $curl->post($url);
        if(Vhelper::is_json($response)&&$response!=false){
            $responseData = json_decode($response,true);
            if(isset($responseData['success_list'])){
                PurchaseOrderTaxes::updateAll(['is_push_to_data_center'=>1],['id'=>$responseData['success_list']]);
            }
        }
    }

    //推送采购单运费到数据中心
    public function actionPushFreightToDataCenter(){
        $datas = PurchaseOrderPayType::find()
            ->select(['id'=>'id','pur_number'=>'pur_number','freight'=>'ifnull(freight,0)'])
            ->where(['freight_is_push_to_dc'=>0])
            ->limit(1000)
            ->asArray()->all();
        $url = Yii::$app->params['server_ip'].'/purchases/acceptsPurchaseFreight';
        $curl = new Curl();
        $s = $curl->setPostParams([
            'freightData' => Json::encode($datas),
            'token'    => Json::encode(Vhelper::stockAuth()),
        ])->post($url);
        if(Vhelper::is_json($s)&&$s!=false){
            $response = json_decode($s,true);
            if(isset($response['data']['success'])&&!empty($response['data']['success'])){
                PurchaseOrderPayType::updateAll(['freight_is_push_to_dc'=>1],['id'=>$response['data']['success']]);
            }else{
                var_dump($s);exit();
            }
        }else{
            var_dump($s);exit();
        }
        Yii::$app->end('推送成功');
    }

    //推送前一天计算出来产品平均采购单价到erp
    public function actionPushAvgPriceToErp(){
        $queryMax = PurchaseAvg::find()
            ->where(['is_push_to_erp'=>0])
            ->limit(1000)
            ->orderBy('id ASC');
        $data['data'] = $queryMax->asArray()->all();
        if(empty($data['data'])){
            exit('没有需要推送的数据');
        }
        foreach ($data['data'] as $k=>$v)
        {
            $data['data'][$k]['product_status'] =Product::find()->select('product_status')->where(['sku'=>$v['sku']])->scalar();
            $latest_purchase_price = PurchaseOrderItems::find()->alias('t')->select('t.base_price')->leftJoin(PurchaseOrder::tableName().' a','t.pur_number=a.pur_number')->where(['t.sku'=>$v['sku']])->andWhere(['not in','a.purchas_status',[1,2,4,10]])->orderBy('a.audit_time DESC')->offset(0)->limit(1)->scalar();
            if(empty($latest_purchase_price)||$latest_purchase_price==0){
                $latest_purchase_price = PurchaseOrderItems::find()->alias('t')->select('t.price')->leftJoin(PurchaseOrder::tableName().' a','t.pur_number=a.pur_number')->where(['t.sku'=>$v['sku']])->andWhere(['not in','a.purchas_status',[1,2,4,10]])->orderBy('a.audit_time DESC')->offset(0)->limit(1)->scalar();
            }
            $nearest_purchase_price = PurchaseOrderItems::find()->alias('t')->select('t.base_price')->leftJoin(PurchaseOrder::tableName().' a','t.pur_number=a.pur_number')->where(['t.sku'=>$v['sku']])->andWhere(['not in','a.purchas_status',[1,2,4,10]])->orderBy('a.audit_time DESC')->offset(1)->limit(1)->scalar();
            if(empty($nearest_purchase_price)||$nearest_purchase_price==0){
                $nearest_purchase_price = PurchaseOrderItems::find()->alias('t')->select('t.price')->leftJoin(PurchaseOrder::tableName().' a','t.pur_number=a.pur_number')->where(['t.sku'=>$v['sku']])->andWhere(['not in','a.purchas_status',[1,2,4,10]])->orderBy('a.audit_time DESC')->offset(1)->limit(1)->scalar();
            }
            $data['data'][$k]['latest_purchase_price'] = $latest_purchase_price;
            $data['data'][$k]['nearest_purchase_price']= $nearest_purchase_price;
        }
        $updateIds = array_column($data['data'],'id');
        PurchaseAvg::updateAll(['lastest_push_date'=>date('Y-m-d H:i:s',time())],['id'=>$updateIds]);
        $curl = new Curl();
        $curl->setPostParams(['data'=>json_encode($data['data'])]);
        $response = $curl->post(Yii::$app->params['SKU_ERP_URL'].'/services/products/productstock/getloadlastprice');
        if(Vhelper::is_json($response)&&$response!=false){
            PurchaseAvg::updateAll(['is_push_to_erp'=>1],['id'=>json_decode($response)]);
        }else{
            var_dump($response);exit('返回数据异常');
        }
        var_dump($response);exit('推送成功');
    }


    /**
     * 推送 更新后的供应商信息到erp
     * @throws Exception
     * @throws \yii\base\ExitException
     * http://www.purchase.net/v1/erp-sync/auto-push-supplier-simple-info-to-erp
     */
    public function actionAutoPushSupplierSimpleInfoToErp(){
        set_time_limit(0);
        ini_set('memory_limit', '1024M');
        $start_time = time();

        $statusArray = SupplierServices::getSupplierStatus(null);// 获取供应商所有状态列表
        $limit       = (int)Yii::$app->request->get('limit',500);
        $offset     = ApiPageCircle::find()->select('page')->where(['type' => 'PUSH_SUPPLIER_SIMPLE_INFO_OFFSET'])->orderBy('id DESC')->scalar();

        $supplier_data_list = Supplier::find()->select('supplier_name,supplier_code,supplier_type,status')
            ->andWhere(['is_push_to_erp' => 0])
            ->offset($offset)
            ->limit($limit)
            ->asArray()
            ->all();

        if (empty($supplier_data_list)) {
            ApiPageCircle::insertNewPage(0, 'PUSH_SUPPLIER_SIMPLE_INFO_OFFSET');
            echo '推送完成';
            exit;
        } else {
            ApiPageCircle::insertNewPage($offset + $limit, 'PUSH_SUPPLIER_SIMPLE_INFO_OFFSET');
        }

        $url        = Yii::$app->params['ERP_URL'].'/services/api/purchase/addsupplier/method/AddSingleSupp';
        $curl       = new Curl();

        $success    = 0;
        $failure    = 0;
        $count      = count($supplier_data_list);
        foreach($supplier_data_list as $supplier_data){
            $reject_reason = '';
            if($supplier_data['status'] == 5){// 审核不通过原因,只查询最新一条
                $reject_reason = Yii::$app->db->createCommand(
                    "SELECT substring_index(message, '-', -1)  AS reject_reason FROM pur_supplier_log
                    WHERE supplier_code='{$supplier_data['supplier_code']}' AND `action`='supplier/check'
                    AND message LIKE 'Disagree%' ORDER BY id DESC  LIMIT 1
                ")->queryScalar();
                $reject_reason = str_replace('Disagree:','',$reject_reason);
            }
            $supplier_data['reject_reason'] = $reject_reason;// 审核不通过原因
            $supplier_data['status_label']  = ($supplier_data['status'])?SupplierServices::getSupplierStatus($supplier_data['status']):'';// 采购系统状态名称

            $supplier_data = [0 => $supplier_data];

            $response = $curl->setPostParams(['supplier' => json_encode($supplier_data)])->post($url);
            if($response){// 接口返回的结果有误，无法识别是否成功
                $success        ++;
                $status         = 'success';
                $successDatas   = Vhelper::getSqlArrayString(array_column($supplier_data, 'supplier_code'));
                Supplier::updateAll(['is_push_to_erp' => 1], "supplier_code in ({$successDatas})");
            }else{
                $failure        ++;
                $status         = 'error';
            }
            Yii::$app->db->createCommand()->insert(ApiRequestLog::tableName(),['post_content'=>json_encode($supplier_data),
                'response_content'=>serialize($response),'create_time'=>date('Y-m-d H:i:s',time()),'api_url'=>'erp-sync/push-supplier-info-to-erp','status'=>$status])
                ->execute();

        }

        echo "本次推送[$count]个，推送成功[$success]个数<br/>";
        echo '推送结束，耗时: '.(time()-$start_time).' 秒<br/>';
        exit;
    }

}