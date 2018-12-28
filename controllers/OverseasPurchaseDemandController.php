<?php

namespace app\controllers;

/**
 *                             _ooOoo_
 *                            o8888888o
 *                            88" . "88
 *                            (| -_- |)
 *                            O\  =  /O
 *                         ____/`---'\____
 *                       .'  \\|     |//  `.
 *                      /  \\|||  :  |||//  \
 *                     /  _||||| -:- |||||-  \
 *                     |   | \\\  -  /// |   |
 *                     | \_|  ''\---/''  |   |
 *                     \  .-\__  `-`  ___/-. /
 *                   ___`. .'  /--.--\  `. . __
 *                ."" '<  `.___\_<|>_/___.'  >'"".
 *               | | :  `- \`.;`\ _ /`;.`/ - ` : | |
 *               \  \ `-.   \_ __\ /__ _/   .-` /  /
 *          ======`-.____`-.___\_____/___.-`____.-'======
 *                             `=---='
 *          ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
 *                     高山仰止,景行行止.虽不能至,心向往之。
 * User: ztt
 * Date: 2017/11/10 0023
 * Description: OverseasPurchaseDemandController.php
 */
use app\api\v1\models\PurchaseOrderPay;
use app\config\Vhelper;
use app\models\BoxSkuQty;
use app\models\DataControlConfig;
use app\models\DynamicTable;
use app\models\OverseasDemandPassRule;
use app\models\OverseasDemandRule;
use app\models\PlatformSummarySearch;
use app\models\Product;
use app\models\ProductCategory;
use app\models\ProductDescription;
use app\models\ProductProvider;
use app\models\PurchaseDemand;
use app\models\PurchaseDemandBak;
use app\models\PurchaseDemandCopy;
use app\models\PurchaseHistory;
use app\models\PurchaseLog;
use app\models\PurchaseNote;
use app\models\PurchaseOrder;
use app\models\PurchaseOrderItems;
use app\models\PurchaseOrderTaxes;
use app\models\PurchaseSuggest;
use app\models\PurchaseTemporary;
use app\models\SkuSalesStatistics;
use app\models\Stock;
use app\models\Supplier;
use app\models\SupplierBuyer;
use app\models\SupplierQuotes;
use app\models\User;
use app\models\Warehouse;
use app\services\BaseServices;
use app\services\CommonServices;
use mdm\admin\models\AuthItem;
use Yii;
use linslin\yii2\curl;
use yii\helpers\Json;
use yii\db\Exception;
use yii\helpers\FileHelper;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\PlatformSummary;
use app\models\PurchaseOrderShip;
use m35\thecsv\theCsv;
use app\services\PlatformSummaryServices;
use app\services\PurchaseOrderServices;
use app\services\SupplierServices;
use app\services\SupplierGoodsServices;
use app\models\PurchaseOrderPayType;

class OverseasPurchaseDemandController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['GET'],
                ],
            ],
        ];
    }
    
    /**
     * Lists all PurchaseAbnomal models.
     * @return mixed
     */
    public function actionIndex()
    {
        
        $searchModel    = new PlatformSummarySearch();
        $tab_index      = Yii::$app->request->getQueryParam('tab_index',1);
        $dataProvider   = $searchModel->search2(Yii::$app->request->queryParams);
        $params         = Yii::$app->request->queryParams;
        $per_page       = isset($params['per-page'])?$params['per-page']:20;

        if(empty($params)){// 设置默认加载时间
            $params['PlatformSummarySearch']['start_time'] = date('Y-m-d 00:00:00',strtotime("-6 month"));
            $params['PlatformSummarySearch']['end_time'] = date('Y-m-d 23:59:59',time());
        }
        //规则拦截数据
        if(isset($params['PlatformSummarySearch']['tab']) && $params['PlatformSummarySearch']['tab']==1){
            $ruleData    = $searchModel->search10($params);
        }else{
            $ruleData    = $searchModel->search10($params);
        }
        
        $authRes = (new \yii\db\Query())
        ->select('a.user_id')
        ->from('pur_auth_item as i')
        ->leftJoin('pur_auth_assignment as a','i.name = a.item_name')
        ->where(['i.name'=>'产品开发组'])
        ->all();
        $authData = [];
        foreach ($authRes as $key => $value) {
            $authData[] = $value['user_id'];
        }
        //金额拦截数据
        if(isset($params['PlatformSummarySearch']['tab']) && $params['PlatformSummarySearch']['tab']==2){
            $amountInterceptData = $searchModel->searchAmountIntercept($params);
        }else{
            $amountInterceptData = $searchModel->searchAmountIntercept($params);
        }
        //7天3小时拦截数据
        if(isset($params['PlatformSummarySearch']['tab']) && $params['PlatformSummarySearch']['tab']==3){
            $sevendays3hoursData = $searchModel->search7days3hours($params);
        }else{
            $sevendays3hoursData = $searchModel->search7days3hours($params);
        }
        //产品信息不全数据
        if(isset($params['PlatformSummarySearch']['tab']) && $params['PlatformSummarySearch']['tab']==4){
            $incompleteInfoData = $searchModel->searchIncompleteInfo($params);
        }else{
            $incompleteInfoData = $searchModel->searchIncompleteInfo($params);
        }
        
        //当前在哪个页面
        $tab = isset(Yii::$app->request->queryParams['PlatformSummarySearch']['tab'])?Yii::$app->request->queryParams['PlatformSummarySearch']['tab']:0;
        $fresh_tab = isset(Yii::$app->request->queryParams['tab'])?Yii::$app->request->queryParams['tab']:0;
        if($fresh_tab>0){
            $tab = $fresh_tab;
        }

        // 分页展示 规则拦截数据
        $ruleDataTotal = count($ruleData);
        if($ruleDataTotal > $per_page){
            $ruleData = array_chunk($ruleData,$per_page,true);
            $ruleData = isset($ruleData[$per_page - 1])?$ruleData[$per_page - 1]:$ruleData[0];
        }
        // 分页展示 金额拦截数据
        $amountTotal = count($amountInterceptData);
        if($amountTotal > $per_page){
            $amountInterceptData = array_chunk($amountInterceptData,$per_page,true);
            $amountInterceptData = isset($amountInterceptData[$per_page - 1])?$amountInterceptData[$per_page - 1]:$amountInterceptData[0];
        }

        return $this->render('index', [
            'searchModel'          => $searchModel,
            'dataProvider'         => $dataProvider,
            'ruleData'             => $ruleData,
            'authData'             => $authData,
            'tab_index'            => $tab_index,
            'count'                => $ruleDataTotal,
            'amountData'           => $amountInterceptData,//金额拦截数据
            'amountTotal'          => $amountTotal,//金额拦截总数
            'incompleteInfoData'   => $incompleteInfoData,//产品信息不全数据
            'incompleteInfoTotal'  => count($incompleteInfoData),//产品信息不全总数
            'sevendays3hoursData'  => $sevendays3hoursData,//7天3小时拦截数据
            'sevendays3hoursTotal' => count($sevendays3hoursData),//7天3小时拦截总数
            'tab'                  => $tab//当前点击了哪一页
        ]);
    }
    
    
    /**
     * Displays a single PurchaseAbnomal model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }
    
    /**
     * Creates a new PurchaseAbnomal model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new PlatformSummary();
        
        if (Yii::$app->request->isPost) {
            $data= Yii::$app->request->post();
            $product = Product::find()->where(['sku'=>$data['PlatformSummary']['sku']])->one();
            $productStatusLimit = DataControlConfig::find()->select('values')->where(['type'=>'oversea_demand_product_status_limit'])->scalar();
            $productStatusLimit = !empty($productStatusLimit) ? explode(',',$productStatusLimit) : [4,10,12,2,3,14,15,16,17,18,19,20,27,29];
            $productStatusLimitMessage = DataControlConfig::find()->select('values')->where(['type'=>'oversea_demand_product_status_limit_message'])->scalar();
            $productStatusLimitMessage = !empty($productStatusLimitMessage) ? $productStatusLimitMessage : '产品不存在或者产品不在如下属性(状态:拍摄中,修图中,编辑中,预上线,在售中,设计审核中,文案审核中,文案主管审核中,试卖编辑中,试卖在售中,试卖文案终审中,预上线拍摄中,作图审核中,开发检查中)';
            if(empty($product)||!in_array($product->product_status,$productStatusLimit)){
                Yii::$app->getSession()->setFlash('error',$productStatusLimitMessage);
                return $this->redirect(Yii::$app->request->referrer);
            }
            $companyinfo = $this->getPurchasegs(array('sku'=>$data['PlatformSummary']['sku'],'purchase_warehouse'=>$data['PlatformSummary']['purchase_warehouse'],'purchase_quantity'=>$data['PlatformSummary']['purchase_quantity']));

            $model->demand_number =CommonServices::getNumber('RD');
            $model->purchase_type =2;
            $model->demand_status = 1;
            $model->ship_code = $companyinfo['ship_code'];
            $model->company   = $companyinfo['company'];
            if($model->load(Yii::$app->request->post())){
                if($model->is_transit == 1){
                    $model->transit_number = 0;
                }else{
                    $model->transit_number = $model->purchase_quantity;// 中转数量和采购数量保持一致
                }
                $model->save();
            }
            PurchaseOrderServices::writelog($model->demand_number, '创建需求');
            Yii::$app->getSession()->setFlash('success',"恭喜你添加成功！",true);
            return $this->redirect(['index']);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }
    /**
     * 一次性抓取物流系统物流公司和物流渠道
     * @return string
     */
    public function getPurchasegs($data)
    {
        $st = false;
        foreach (['us', 'gb', 'au', 'de', 'hw', 'es', 'ru', 'fr'] as $site) {
            $a = stripos($data['sku'], $site);
            $b = strripos($data['sku'], $site);

            if ($a === 0 || $b === 0 || strlen($data['sku']) - 2 === $b) {
                $st = $site;
                break;
            }
        }
        if ($st === false) {
            foreach (['us', 'gb', 'au', 'de', 'hw', 'es', 'ru', 'fr'] as $site) {
                if (preg_match("/^.+" . strtoupper($site) . "-\d{1,}$/i", $data['sku'])) {
                    $st = $site;
                    if ($st == 'hw') {
                        $st = 'gb';
                    }
                    break;
                }
            }
        }
        if( $st!== false ){
            $urls = 'http://www.dong.com/services/api/logistics/getFirstinfo';


            //查询仓库id

            $warehouse_id = Warehouse::find()->select('id')->where(['warehouse_code' => $data['purchase_warehouse']])->one();
            $da['country'] = $st;
            $da['warehouse'] = $warehouse_id->id;//'49';
            $da['skudata'][] = array('sku' => $data['sku'], 'quantity' => $data['purchase_quantity']);

            $response = $this->http_post_json_cont($urls,json_encode($da),'','data');
            if(Vhelper::is_json($response)) {
                $responses = json_decode($response,true);
                return ['ship_code'=>$responses[$data['sku']]['ship_code'],'company'=>$responses[$data['sku']]['company']];
            }

        }
        return ['ship_code'=>'','company'=>''];
    }
    public  function http_post_json_cont($url, $jsonStr, $token, $name)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $name . '=' . $jsonStr . '&token='.$token);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        // echo $response;exit;
        curl_close($ch);
        return $response;
    }
    /**
     * Updates an existing PurchaseAbnomal model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        if ($model->load(Yii::$app->request->post())) {
            $model->update_time = date('Y-m-d H:i:s',time());
            $model->update_user_name = Yii::$app->user->identity->username;
            $model->level_audit_status=0;
            $model->audit_note='';
            $update_data_param = [
                'sku' => 'sku',
                'platform_number' => '平台',
                'purchase_quantity' => '数量',
                'purchase_warehouse' => '采购仓',
                'is_transit' => ['是否中转', ['1'=>'直发',2=>'需要中转']],
                'transit_warehouse' => ['中转仓', ['shzz'=>'上海中转仓库','AFN'=>'东莞中转仓库']],
                'transit_number' => '中转数量',
                'transport_style' => ['物流类型', PurchaseOrderServices::getTransport()],
                'bh_type' => ['补货类型', PurchaseOrderServices::getBhTypes()],
            ];
            $update_data = CommonServices::getUpdateData($model, $update_data_param);
            $model->save();
            PurchaseOrderServices::writelog($model->demand_number, '修改需求', '', $update_data);
            return $this->redirect(['index']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }
    
    /**
     * Deletes an existing PurchaseAbnomal model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = PlatformSummary::findOne($id);
        $model->level_audit_status =5;
        $model->is_push =0;
        Yii::$app->getSession()->setFlash('success',"恭喜您操作成功！",true);
        $model->save(false);
        //$this->findModel($id)->delete();
        PurchaseOrderServices::writelog($model->demand_number, '删除需求');
        return $this->redirect(['index']);
    }
    
    /**
     * 拦截审核推送
     * @return \yii\web\Response
     */
    public function actionUpdateStatus($id)
    {
        
        $this->updateStatus($id);
        return $this->redirect(Yii::$app->request->referrer);
    }
    /**
     * 批量拦截审核推送
     */
    public function actionAllUpdateStatus()
    {
        $ids    = Yii::$app->request->post('ids');
        if (!$ids) return;
        $tran = Yii::$app->db->beginTransaction();
        try{
            foreach ($ids as $id) {
                $this->updateStatus($id);
            }
            $tran->commit();
        }catch(HttpException $e){
            $tran->rollBack();
            Yii::$app->getSession()->setFlash('error','恭喜你，审核失败');
        }
        return $this->redirect(Yii::$app->request->referrer);
    }
    
    public function updateStatus($id)
    {
        $model = PlatformSummary::findOne($id);
        if (in_array($model->level_audit_status,[6,7])) {
            $model->level_audit_status =1;
            $model->is_push =0;
            $model->update_time = date('Y-m-d H:i:s',time());
            $model->update_user_name = Yii::$app->user->identity->username;
            $model->audit_note='';
            $model->agree_time = date('Y-m-d H:i:s',time());
            
            $quoteid = ProductProvider::find()->where(['sku'=>$model->sku,'is_supplier'=>1])->select('quotes_id')->scalar();
            if (empty($quoteid)) {
                Yii::$app->getSession()->setFlash('error',"此sku没有供应商信息，请先添加供应商信息",true);
                return true;
            }
            $product_quote = SupplierQuotes::find()->where(['id'=>$quoteid])->one();
            $model->supplier_code = $product_quote->suppliercode;
            $model->pur_ticketed_point = $product_quote->pur_ticketed_point;
            $model->is_back_tax = $product_quote->is_back_tax == 1 ? 1 : 2;
            
            $status = $model->save(false);
            if ($status) {
                //修改采购单
                OverseasDemandRule::agreeUpdateOrder($model, $product_quote);
                
                PurchaseOrderServices::writelog($model->demand_number, '规则拦截审核成功');
                Yii::$app->getSession()->setFlash('success',"恭喜您操作成功！",true);
            } else {
                Yii::$app->getSession()->setFlash('error',"恭喜您操作失败！",true);
            }
        } else {
            Yii::$app->getSession()->setFlash('error',"只有审核状态为：规则拦截的才可以操作！",true);
        }
    }
    
    /**
     * Finds the PurchaseAbnomal model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return PurchaseAbnomal the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PlatformSummary::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    /**
     * 采购驳回
     */
    public function actionPurchaseDisagree($id)
    {
        $model = $this->findModel($id);
        if(Yii::$app->request->isPost)
        {
            $exist = PurchaseDemand::find()->where(['demand_number'=>$model->demand_number])->one();
            if($exist){
                $purchaseOrder = PurchaseOrder::find()->where(['pur_number'=>$exist->pur_number])->one();
                if(!in_array($purchaseOrder->purchas_status,[4,10])){
                    Yii::$app->getSession()->setFlash('error',"采购需求已生成采购单！",true);
                    return $this->redirect(Yii::$app->request->referrer);
                }
            }
            $model->level_audit_status = 4;
            $model->buyer              = Yii::$app->user->identity->username;
            $model->is_purchase        = 1;
            $model->is_push            = 0;
            $model->purchase_note      = Yii::$app->request->post()['PlatformSummary']['purchase_note'];
            $model->purchase_time      = date('Y-m-d H:i:s', time());
            $model->save();
            PurchaseOrderServices::writelog($model->demand_number, '采购驳回');
            Yii::$app->getSession()->setFlash('success',"恭喜您操作成功！",true);
            return $this->redirect(Yii::$app->request->referrer);
            
        } else{
            return $this->renderAjax('pnote',['model' =>$model]);
        }
    }
    /**
     * 审核-同意
     */
    public  function actionAgree()
    {
        Yii::$app->response->format = 'raw';
        $ids=Yii::$app->request->post('ids');
        $id=Yii::$app->request->get('id');
        if(!empty($id) || !empty($ids)){
            if (empty($ids)) {
                $ids = [$id];
            }

            $refuse = [];
            $tran = Yii::$app->db->beginTransaction();
            try{
                foreach($ids as $v){
                    $model = $this->findModel($v);
                    if($model->level_audit_status==1){
                        continue;
                    }
                    if (strtotime($model->create_time)>strtotime('2018-05-08 23:59:59')){
                        $ruleVerify = OverseasDemandRule::verifyInterceptRule($model->sku,$model->purchase_warehouse,1,$model->demand_number,$model->transport_style);
                    }
                    if(isset($ruleVerify)&&$ruleVerify['status']=='error'){
                        $model->level_audit_status = $ruleVerify['level'];
                        $model->audit_note         = $ruleVerify['message'];
                        $model->is_push         = 0;
                        $model->agree_user=Yii::$app->user->identity->username;
                        $model->agree_time=date('Y-m-d H:i:s',time());
                        $refuse[]=$model->sku;
                        PurchaseOrderServices::writelog($model->demand_number, '需求拦截:'.$model->audit_note);
                    }else{
                        $arr = ['0','3','6','7'];
                        if(in_array($model->level_audit_status,$arr)){
                            $model->push_to_erp = 0;
                            $model->level_audit_status=1;
                            $model->is_push=0;
                            $model->demand_status = 1;

                            $quoteid = ProductProvider::find()->where(['sku'=>$model->sku,'is_supplier'=>1])->select('quotes_id')->scalar();
                            $product_quote = SupplierQuotes::find()->where(['id'=>$quoteid])->one();
                            $model->supplier_code = $product_quote->suppliercode;
                            $model->pur_ticketed_point = $product_quote->pur_ticketed_point;
                            $model->is_back_tax = $product_quote->is_back_tax == 1 ? 1 : 2;
                            
                            $model->agree_user=Yii::$app->user->identity->username;
                            $model->agree_time=date('Y-m-d H:i:s',time());
                            PurchaseOrderServices::writelog($model->demand_number, '审核通过');
                            

                            OverseasDemandRule::agreeUpdateOrder($model, $product_quote);
                        }
                    }
                    $refuse = isset($ruleVerify['updateSku']) ? array_diff($refuse,$ruleVerify['updateSku']): $refuse;
                    $model->save(false);

                }
                $tran->commit();
            }catch(HttpException $e){
                $tran->rollBack();
                Yii::$app->getSession()->setFlash('warning','操作失败，请重试');
            }
            if(!empty($refuse)){
                Yii::$app->getSession()->setFlash('warning','部分sku被规则拦截：'.implode(',',$refuse),false);
            }else{
                Yii::$app->getSession()->setFlash('success',"恭喜您,批量操作成功",true);
            }
            
        }else{
            Yii::$app->getSession()->setFlash('error',"没有数据ID,操作失败！",true);
        }
        
        return $this->redirect(Yii::$app->request->referrer);
    }
    
    /**
     * 清除产品
     * @return \yii\web\Response
     */
    public function actionEliminate()
    {
        $data =[
            'user_id'=>Yii::$app->user->id,
        ];
        $rc =DynamicTable::Deletess($data);
        if(!$rc)
        {
            Yii::$app->getSession()->setFlash('error',"清除失败");
        }
        PurchaseTemporary::deleteAll(['create_id'=>Yii::$app->user->id]);
        Yii::$app->getSession()->setFlash('success',"清除成功");
        
        return $this->redirect(['overseas-purchase-demand/create-purchase-order']);
        
        //return $this->redirect(['addproduct']);
    }
    /**
     * 审核-驳回
     */
    public  function actionDisagree()
    {
        $post=Yii::$app->request->post('PlatformSummary');
        
        if(!empty($post)){
            $ids=explode(',',$post['id']);
            if(count($ids)>1){//批量
                foreach($ids as $v){
                    $post_model = $this->findModel($v);
                    if($post_model->level_audit_status==0){
                        $post_model->level_audit_status=2;
                        $post_model->is_push=0;
                    }
                    $post_model->agree_user=Yii::$app->user->identity->username;
                    $post_model->audit_note=$post['audit_note'];
                    $post_model->agree_time=date('Y-m-d H:i:s',time());
                    $post_model->save(false);
                    PurchaseOrderServices::writelog($post_model->demand_number, '驳回:'.$post_model->audit_note);
                }
                
                Yii::$app->getSession()->setFlash('success',"恭喜您,批量操作成功！",true);
                
                return $this->redirect(Yii::$app->request->referrer);
            }else{
                $model = $this->findModel($post['id']);
                if($model->level_audit_status==0){
                    $model->level_audit_status=2;
                }
                $model->agree_user=Yii::$app->user->identity->username;
                $model->audit_note=$post['audit_note'];
                $model->agree_time=date('Y-m-d H:i:s',time());
                $model->save(false);
                PurchaseOrderServices::writelog($model->demand_number, '驳回:'.$model->audit_note);
                Yii::$app->getSession()->setFlash('success',"恭喜您操作成功！",true);
                
                return $this->redirect(Yii::$app->request->referrer);
            }
        }else{
            $id=$_REQUEST['id'];
            //$page  = $_REQUEST['page'];
            if($id){
                $model=new PlatformSummary();
                if(is_array($id)){
                    $id=implode(',',$id);
                }
                return $this->renderAjax('note',['id' =>$id,'model'=>$model]);
            }
        }
        return false;
        
    }
    /**
     * 模板查看
     */
    public function actionTemplate()
    {
        
        $filename = Yii::$app->request->hostInfo . "/images/purchase.csv";//模板放的位置
        $file_name = "purchase.csv";
        $contents = file_get_contents($filename);
        // $file_size = filesize($filename);
        header("Content-type: application/octet-stream;charset=utf-8");
        header("Accept-Ranges: bytes");
        //header("Accept-Length: $file_size");
        header("Content-Disposition: attachment; filename=".$file_name);
        exit($contents);
        
        
    }
    
    /**
     * 撤销需求
     */
    public function  actionRevokeDemand()
    {
        $ids    = Yii::$app->request->get('ids');
        if (!$ids) return;
        $map['id']=strpos($ids,',') ? explode(',',$ids):$ids;
        $map['level_audit_status'] = ['0','4','2'];
        $map['is_purchase']        = 1;
        $orders=PlatformSummary::find()->select('id,demand_number,level_audit_status')->where($map)->all();
        if(!empty($orders))
        {
            foreach ($orders as $v)
            {
                $v->level_audit_status=3;
                $result =$v->save(false);
                if ($result) {
                    PurchaseOrderServices::writelog($v->demand_number, '撤销需求');
                }
            }
            if($result)
            {
                Yii::$app->getSession()->setFlash('success','恭喜你,撤销确认成功',true);
            }
            return $this->redirect(['index']);
        } else {
            Yii::$app->getSession()->setFlash('error','对不起少年,已经同意或者已是采购的不能再撤销了！');
            return $this->redirect(['index']);
        }
        
    }
    
    /**
     * 创建采购单
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionCreatePurchaseOrder()
    {
        
        
        $ordermodel   = new PurchaseOrder();
        $purchasenote = new PurchaseNote();
        
        if(!empty($_POST['PurchaseOrder']))
        {
            $data = [
                'user_id' => Yii::$app->user->id,
            ];
            DynamicTable::Deletess($data);
            $purdesc=$_POST['PurchaseOrder'];
            //生成采购订单主表、详情表数据
            $orderdata['purdesc']=$purdesc;
            
            $orderdata['purdesc']['supplier_code']     = preg_match("/[\x7f-\xff]/", $purdesc['supplier_code'])?SupplierQuotes::getFiled($purdesc['items'][0]['sku'],'suppliercode')->suppliercode:$purdesc['supplier_code'];
            //Vhelper::dump($orderdata);
            $transaction=\Yii::$app->db->beginTransaction();
            try {
                $pur_number   = $ordermodel::Savepurdata($orderdata);
                //加入备注
                $PurchaseNote =[
                    'pur_number'=>$pur_number,
                    'note'      =>$_POST['PurchaseNote']['note'],
                ];
                $purchasenote->saveNote($PurchaseNote);
                $demand_array =[];
                foreach($purdesc['items'] as $k=>$v)
                {
                    $demand_array [] = $v['demand_number'];
                    
                }
                PurchaseDemand::saveOne($pur_number,$purdesc['items']);
                $ordermodel::OrderItems($pur_number,$purdesc['items'],2);
                PlatformSummary::Updates($demand_array);
                $transaction->commit();
                Yii::$app->getSession()->setFlash('success', '恭喜你,手动创建采购单成功', true);
                return $this->redirect(['index']);
            }catch (Exception $e) {
                $transaction->rollBack();
                Yii::$app->getSession()->setFlash('error','数据异常！保存失败,请联系管理员');
                return $this->redirect(['index']);
            }
        }
        $temporay= PurchaseTemporary::find()->where(['create_id'=>Yii::$app->user->id])->all();
        return $this->render('addproduct', [
            
            'purchasenote' =>$purchasenote,
            'ordermodel'=>$ordermodel,
            'temporay'=>$temporay,
        ]);
        
        /* $ids                       = Yii::$app->request->get('ids');
         $map['id']                 = strpos($ids, ',') ? explode(',', $ids) : $ids;
         $map['level_audit_status'] = 1;
         $map['is_purchase']        = 1;
         $map['purchase_type']      = 2;
         $orders                    = PlatformSummary::find()->where($map)->asArray()->all();
         
         if(empty($orders))
         {
         Yii::$app->getSession()->setFlash('error','对不起少年,没有经过同意或者已是采购过了！');
         return $this->redirect(['index']);
         } else {
         $pur         = [];
         $transaction = \Yii::$app->db->beginTransaction();
         try {
         foreach ($orders as $k => $v) {
         $orderdata['purdesc']['warehouse_code']    = $v['purchase_warehouse'];
         $orderdata['purdesc']['is_transit']        = $v['is_transit'] == 1 ? 0 : 1;
         $orderdata['purdesc']['transit_warehouse'] = $v['transit_warehouse'];
         $orderdata['purdesc']['supplier_code']     = BaseServices::getSupplierCode(PurchaseHistory::getField($v['sku'], 'supplier_name'), 'supplier_code');
         $orderdata['purdesc']['is_expedited']      = 1;
         $orderdata['purdesc']['purchase_type']     = $v['purchase_type'];
         $pur['items'][$k]['sku']                   = $v['sku'];
         $pur['items'][$k]['title']                 = $v['product_name'];
         $pur['items'][$k]['purchase_quantity']     = PlatformSummary::getSku($v['sku'], $v['purchase_warehouse'], $v['transit_warehouse'], '*', 3);
         $pur_number                                = PurchaseOrder::Savepurdata($orderdata);
         //需求单号和采购单号关联
         $dats = [
         'pur_number'    => $pur_number,
         'demand_number' => $v['demand_number'],
         'create_id'     => $v['create_id'],
         'create_time'   => $v['create_time'],
         ];
         PurchaseDemand::saveOne($dats);
         PurchaseOrder::OrderItems($pur_number, $pur['items'], 2);
         $transaction->commit();
         Yii::$app->getSession()->setFlash('success', '恭喜你,手动创建采购单成功,请到采购管理下面的采购计划去查看吧！', true);
         return $this->redirect(['index']);
         }
         } catch (Exception $e) {
         $transaction->rollBack();
         }
         }*/
        
        
    }
    /**
     * 导入cvs
     * @return string|\yii\web\Response
     * @throws \yii\db\Exception
     */
    public function actionImportProduct()
    {
        
        $model = new PurchaseTemporary();
        if (Yii::$app->request->isPost)
        {
            $model->file_execl = UploadedFile::getInstance($model, 'file_execl');
            
            $data              = $model->upload();
            
            if(empty($data))
            {
                Yii::$app->getSession()->setFlash('error',"文件上传失败",true);
                return $this->redirect(['index']);
            }
            $file        = fopen($data, 'r');
            $line_number = 0;
            while ($datas = fgetcsv($file))
            {
                if ($line_number == 0)
                { //跳过表头
                    $line_number++;
                    continue;
                }
                $num = count($datas);
                for ($c = 0; $c < $num; $c++)
                {
                    
                    $Name[$line_number][] = mb_convert_encoding(trim($datas[$c]),'utf-8','gbk');
                    
                }
                $Name[$line_number][] = Yii::$app->user->identity->id;
                $line_number++;
            }
            $statu = Yii::$app->db->createCommand()->batchInsert(PurchaseTemporary::tableName(),['sku','purchase_quantity', 'purchase_price','title','create_id'], $Name)->execute();
            fclose($file);
            if ($statu)
            {
                Yii::$app->getSession()->setFlash('success',"恭喜你，导入成功！",true);
                return $this->redirect(['addproduct']);
            } else {
                Yii::$app->getSession()->setFlash('error','恭喜你，导入失败了！请联系管理员',true);
                return $this->redirect(['addproduct']);
            }
            
        } else {
            return $this->renderAjax('addfile', ['model' => $model]);
        }
    }
    /**
     * @return string
     */
    public function actionProductIndex()
    {
        $searchModel  = new PlatformSummarySearch();
        $dataProvider = $searchModel->search1(Yii::$app->request->queryParams);
        return $this->renderAjax('_orderform',[
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    public function actionAddTemporary()
    {
        $id = Yii::$app->request->post()['id'];
        $id = strpos($id,',')?explode(',',$id):$id;
        
        if (!is_string($id))
        {
            foreach($id as $v)
            {
                if(!empty($v))
                {
                    $data =[
                        'user_id'=>  Yii::$app->user->id,
                        'demand_number_id'=>$v,
                    ];
                    $rc = DynamicTable::Check($data);
                    if($rc)
                    {
                        return json_encode(['code'=>0,'msg'=>'其他采购人员正在操作此选中的部分需求单,添加失败']);
                    }
                    $rs = DynamicTable::Add($data);
                    if(!$rs)
                    {
                        return json_encode(['code'=>0,'msg'=>'采购需求单动态表部分添加失败,请刷新页面']);
                    }
                    $model            = new PurchaseTemporary;
                    $model->product_id       = $v;
                    $model->sku             = PlatformSummary::find()->select('sku')->where(['id'=>$v])->scalar();
                    $model->create_id = Yii::$app->user->id;
                    $status = $model->save(false);
                    
                }
                
            }
        } else {
            $data =[
                'user_id'=>  Yii::$app->user->id,
                'demand_number_id'=>$id,
            ];
            $rc = DynamicTable::Check($data);
            if($rc)
            {
                return json_encode(['code'=>0,'msg'=>'其他采购人员正在操作此采购需求单,你现在无法操作']);
            }
            $rs = DynamicTable::Add($data);
            if(!$rs)
            {
                return json_encode(['code'=>0,'msg'=>'采购需求单动态表添加失败！']);
            }
            $model                   = new PurchaseTemporary;
            $model->product_id       = $id;
            $model->sku              = PlatformSummary::find()->select('sku')->where(['id'=>$id])->scalar();
            $model->create_id        = Yii::$app->user->id;
            $status                  = $model->save(false);
        }
        
        if($status){
            return json_encode(['code'=>1,'msg'=>'恭喜你,产品添加成功']);
        }else{
            return json_encode(['code'=>0,'msg'=>'哦喔！产品添加失败了']);
        }
    }
    
    
    /**
     * 采购需求导入
     * @return string|\yii\web\Response
     */
    public function actionPurchaseSumImport(){
        $transport_list = PurchaseOrderServices::getTransport();
        $transport_list = array_flip($transport_list);
        $model = new PlatformSummary();
        $demand_numbers = [];
        if (Yii::$app->request->isPost && $_FILES)
        {
            $extension=pathinfo($_FILES['PlatformSummary']['name']['file_execl'], PATHINFO_EXTENSION);
            
            $filessize=$_FILES['PlatformSummary']['size']['file_execl']/1024/1024;
            $filessize=round($filessize,2);
            
            if($filessize>10)
            {
                Yii::$app->getSession()->setFlash('warning',"文件大小不能超过 10M，当前大小： $filessize M",true);
                return $this->redirect(['index']);
            }
            
            
            if($extension!='csv')
            {
                Yii::$app->getSession()->setFlash('warning',"格式不正确,只接受 .csv 格式的文件",true);
                return $this->redirect(['index']);
            }
            $name= 'PlatformSummary[file_execl]';
            $data = Vhelper::upload($name);
            
            if(empty($data))
            {
                Yii::$app->getSession()->setFlash('warning',"文件上传失败",true);
                return $this->redirect(['index']);
            }
            $file        = fopen($data, 'r');
            $line_number = 0;
            $s=0;
            $errorSku=[];
            $bhtypes = array_flip(PurchaseOrderServices::getBhTypes());
            $productStatusLimit = DataControlConfig::find()->select('values')->where(['type'=>'oversea_demand_product_status_limit'])->scalar();
            $productStatusLimit = !empty($productStatusLimit) ? explode(',',$productStatusLimit) : [4,10,12,2,3,14,15,16,17,18,19,20,27,29];
            $productStatusLimitMessage = DataControlConfig::find()->select('values')->where(['type'=>'oversea_demand_product_status_limit_message'])->scalar();
            $productStatusLimitMessage = !empty($productStatusLimitMessage) ? $productStatusLimitMessage : '产品不存在或者产品不在如下属性(状态:拍摄中,修图中,编辑中,预上线,在售中,设计审核中,文案审核中,文案主管审核中,试卖编辑中,试卖在售中,试卖文案终审中,预上线拍摄中,作图审核中,开发检查中)';
            while ($datas = fgetcsv($file)) {
                if ($line_number == 0) { //跳过表头
                    $line_number++;
                    continue;
                }
                
                $sku=Product::find()->where(['sku'=>trim($datas[0])])->asArray()->one()['sku'];
                if(!$sku){
                    $errorSku[]=trim($datas[0]);
                    $s++;
                    continue;
                }
                
                $num = count($datas);
                for ($c = 0; $c < $num; $c++) {
                    $Name[$line_number][] = trim(mb_convert_encoding(trim($datas[$c]),'utf-8','gbk'));
                }
                
                if(!empty($Name[$line_number][1]) && !is_numeric($Name[$line_number][1])){
                    $Name[$line_number][1] =strtoupper($Name[$line_number][1]);
                }
                
                if(!empty($Name[$line_number][4]) && !is_numeric($Name[$line_number][4])){
                    switch ($Name[$line_number][4]){
                        case '宁波中转仓库':
                            $Name[$line_number][4]='shzz';
                            break;
                        case '上海中转仓库':
                            $Name[$line_number][4]='shzz';
                            break;
                        case '东莞中转仓库':
                            $Name[$line_number][4]='AFN';
                            break;
                        default:
                            $Name[$line_number][4]='';
                    }
                }
                if(empty($Name[$line_number][4])){
                    Yii::$app->getSession()->setFlash('warning','填写的中转仓库有误--'.$sku);
                    return $this->redirect(['index']);
                }
                
                if(!empty($Name[$line_number][6]) && !is_numeric($Name[$line_number][6])){
                    $Name[$line_number][6] = isset($transport_list[trim($Name[$line_number][6])])?$transport_list[trim($Name[$line_number][6])]:'';
                }
                //判断是否在售状态
                $product = Product::find()->where(['sku'=>$sku])->one();
                if(empty($product)||!in_array($product->product_status,$productStatusLimit)){
                    Yii::$app->getSession()->setFlash('error',$productStatusLimitMessage.'--'.$sku,true);
                    return $this->redirect(['index']);
                }
                if(empty($Name[$line_number][6])){
                    Yii::$app->getSession()->setFlash('warning','请使用新的头程物流类型');
                    return $this->redirect(['index']);
                }
                if(!empty($Name[$line_number][7]) && isset($bhtypes[$Name[$line_number][7]])){
                    $Name[$line_number][7] = $bhtypes[$Name[$line_number][7]];
                } else {
                    Yii::$app->getSession()->setFlash('warning','补货类型有误--'.$sku,true);
                    return $this->redirect(['index']);
                }
                //是否加急
                $Name[$line_number][8] = $Name[$line_number][8] == '是' ? 1 : 2;
                if(!empty($Name[$line_number][3]) && !is_numeric($Name[$line_number][3])){
                    $Name[$line_number][3] = Warehouse::find()->select('id,warehouse_code,warehouse_name')->where(['use_status'=>1,'warehouse_name'=>$Name[$line_number][3]])->asArray()->one()['warehouse_code'];
                }
                if (empty($Name[$line_number][3])) {
                    Yii::$app->getSession()->setFlash('warning','仓库不能为空或所填仓库有误--'.$sku,true);
                    return $this->redirect(['index']);
                }
                
                $pmodel = Product::find()->joinWith(['desc','cat'])->where(['pur_product.sku'=>trim($datas[0])])->asArray()->one();
                $Name[$line_number][] = $pmodel['product_category_id'];
                $Name[$line_number][] = !empty($pmodel['desc']['title']) ? $pmodel['desc']['title'] : '';
                
                $Name[$line_number][] = 2;
                $Name[$line_number][] = 2;//是否中转，默认直发：1
                $Name[$line_number][] = $demand_numbers[] = CommonServices::getNumber('RD');
                $Name[$line_number][] = Yii::$app->user->identity->username;
                $Name[$line_number][] = date('Y-m-d H:i:s',time());
                $line_number++;
            }
            if (!empty($Name[1]) && count($Name[1])!=17) {
                Yii::$app->getSession()->setFlash('warning','文件格式错误：可能是文件右侧面多了很多无用的空数据',true);
                return $this->redirect(['index']);
            }
            if(empty($Name))
            {
                Yii::$app->getSession()->setFlash('warning','导入有sku不存在系统中，请联系管理员解决',true);
                return $this->redirect(['index']);
            }
            //数据一次性入库
            $transaction=\Yii::$app->db->beginTransaction();
            try{
                $array = ['sku','platform_number','purchase_quantity','purchase_warehouse','transit_warehouse','transit_number','transport_style','bh_type','demand_is_expedited','sales_note',
                    'product_category','product_name','purchase_type','is_transit','demand_number', 'create_id','create_time'];
                $statu= Yii::$app->db->createCommand()->batchInsert(PlatformSummary::tableName(), $array, $Name)->execute();
                
                $transaction->commit();
            }catch (Exception $e){
                $transaction->rollBack();
            }
            
            fclose($file);
            //
            //            $dir=Yii::getAlias('@app') .'/web/files/' . date('Ymd');
            //            if (file_exists($dir)){
            //                FileHelper::removeDirectory($dir);
            //            }
            if ($statu) {
                if($s>0){
                    $f_sku ="SKU错误：$s 条：".implode(',',$errorSku);
                } else {
                    $f_sku ="";
                }
                PurchaseOrderServices::writelog($demand_numbers, '导入需求');
                Yii::$app->getSession()->setFlash('success',"恭喜你，导入成功！$f_sku",true);
                return $this->redirect(['index']);
            } else {
                Yii::$app->getSession()->setFlash('warning','恭喜你，导入失败了！请联系管理员',true);
                return $this->redirect(['index']);
            }
            
        } else {
            return $this->renderAjax('purchase-sum-import', ['model' => $model]);
        }
    }
    
    /**
     * 根据需求生成采购建议
     */
    public function  actionPurchase()
    {
        $model = PlatformSummary::find()
        ->alias('t')
        ->where(['t.level_audit_status'=>1,'t.is_purchase'=>1,'t.purchase_type'=>2])
        ->andWhere(['<=','t.create_time',date('Y-m-d H:i:s',time()-3*60*60)])
        ->orderBy('t.id asc')->limit(300)->all();
        
        try {
            if ($model) {
                
                foreach ($model as $v) {
                    if(empty($v->supplierQuotes)||empty($v->desc)){
                        echo $v->sku.'没有报价或者产品名<br/>';
                        continue;
                    }
                    $sku = Product::find()->select('sku')->where(['sku'=>$v['sku']])->scalar();
                    if(!$sku){
                        echo $v->sku.'产品不存在<br/>';
                        continue;
                    }
                    if (!empty($v['supplierQuotes']) && !empty($v['desc']['title'])) {
                        $suggest_s = PurchaseSuggest::find()->where(['warehouse_code'=>$v->purchase_warehouse,'sku'=>$sku,
                            'purchase_type'=>4,'demand_number'=>$v->demand_number])->orderBy('id desc')->one();
                        $tran= Yii::$app->db->beginTransaction();
                        try {
                            if(empty($suggest_s))
                            {
                                $suggest                 = new PurchaseSuggest();
                                $suggest->warehouse_code = $v->purchase_warehouse;
                                $suggest->warehouse_name = BaseServices::getWarehouseCode($v->purchase_warehouse) ? BaseServices::getWarehouseCode($v->purchase_warehouse) : $v->purchase_warehouse;
                                $suggest->sku            = $sku;
                                $suggest->name           = $v['desc']['title'];
                                $suppliercode            = $v->supplierQuotes->supplier_code;
                                if ($suppliercode == 'aaa' || empty($suppliercode)) {
                                    echo '没有绑定供应商'.$sku.'<br/>';
                                    continue;
                                }
                                $suggest->supplier_code       = $suppliercode;
                                $buyer                        = SupplierBuyer::find()->select('buyer')->where(['supplier_code' => $suppliercode,'type'=>2,'status'=>1])->scalar();
                                $suggest->supplier_name       = BaseServices::getSupplierName($suppliercode);
                                $suggest->buyer               =  $buyer==false ? 'admin' : $buyer;
                                //$suggest->buyer               =  $buyer==false ? 'admin' : $buyer;
                                $suggest->buyer_id            = 1;
                                $suggest->replenish_type      = 3;
                                $suggest->qty                 = $v['purchase_quantity'];
                                $suggest->price               = SupplierQuotes::getFileds($v->supplierQuotes->quotes_id, 'supplierprice')->supplierprice;
                                $suggest->currency            = 'RMB';
                                $supplierInfo = Supplier::find()->select('supplier_settlement,payment_method')->where(['supplier_code'=>$suppliercode])->asArray()->all();
                                $suggest->payment_method      = isset($supplierInfo['payment_method'])&&!empty($supplierInfo['payment_method']) ? $supplierInfo['payment_method'] :'1';
                                $suggest->is_purchase         = 'Y';
                                $suggest->supplier_settlement = isset($supplierInfo['supplier_settlement'])&&!empty($supplierInfo['supplier_settlement']) ? $supplierInfo['supplier_settlement'] :'1';
                                $suggest->ship_method         = '2';
                                $suggest->safe_delivery       = '100';
                                $suggest->created_at          = date('Y-m-d H:i:s',time());
                                $suggest->creator             = $v->create_id;
                                $suggest->product_category_id = empty($v->productCategory->id) ? '0' :$v->productCategory->id;
                                //                                    $suggest->category_cn_name    = BaseServices::getCategory($v['product_category']);
                                $suggest->category_cn_name    = empty($v->productCategory->category_cn_name) ? '' :$v->productCategory->category_cn_name;
                                $suggest->type                = 'last_down';
                                $suggest->transit_code        = $v->transit_warehouse;
                                $suggest->demand_number       = $v->demand_number;
                                $suggest->purchase_type       = 4;
                                //如果保存成功的话
                                if ($suggest->save()) {
                                    $p = PlatformSummary::find()->where(['demand_number' =>$v->demand_number])->one();
                                    if ($p) {
                                        $p->is_purchase = 2;
                                        $p->product_name = $v->desc->title;
                                        $p->product_category = empty($v->productCategory->id) ? $p->product_category :$v->productCategory->id;
                                        $p->save(false);
                                    }
                                    echo $v['demand_number'].'生成成功'."<br/>";
                                } else {
                                    echo $v['demand_number'].'保存失败1'."<br/>";
                                }
                            } else {
                                $suggest_s->qty                 = $v->purchase_quantity;
                                $suggest_s->is_purchase         = 'Y';
                                $suggest_s->demand_number       = $v->demand_number;
                                $suggest_s->created_at          = date('Y-m-d H:i:s',time());
                                $suggest_s->buyer               = 'admin';
                                $suggest_s->buyer_id            = 1;
                                $suggest_s->purchase_type       = 4;
                                //如果保存成功的话
                                if ($suggest_s->save(false)) {
                                    $p = PlatformSummary::find()->where(['demand_number' =>$v->demand_number])->one();
                                    if ($p) {
                                        $p->is_purchase = 2;
                                        $p->product_name = $v->desc->title;
                                        $p->product_category = empty($v->productCategory->id) ? $p->product_category :$v->productCategory->id;
                                        $p->save(false);
                                        echo $v['demand_number'].'更新成功'."<br/>";
                                    }
                                    
                                } else {
                                    echo $v['demand_number'].'更新失败1'."<br/>";
                                }
                            }
                            $tran->commit();
                        } catch (InvalidParamException $e) {
                            $tran->rollBack();
                            print_r($e->getMessage());
                        }
                        
                    } else {
                        continue;
                    }
                    
                }
                
            }
        }catch (Exception $e) {
            print $e->getMessage();
            exit();
        }
    }
    
    public function  actionTest()
    {
        /*set_time_limit(0);
         $model = PlatformSummary::find()->where(['level_audit_status'=>[1],'is_purchase'=>2,'purchase_type'=>2,'is_push'=>0,])->andWhere(['>','create_time','2018-02-24 00:00:00'])->asArray()->orderBy('id asc')->all();
         
         foreach ($model as $k=>$v)
         {
         $se = PurchaseDemand::find()->where(['demand_number'=>$v['demand_number']])->one();
         if($se)
         {
         file_put_contents("pur_number1.txt", $k.'-----'.$se->pur_number."\r\n", FILE_APPEND);
         } else {
         
         file_put_contents("pur_number2.txt", '"'.$v['demand_number'].'",'."\r\n", FILE_APPEND);
         }
         }*/
        /*  $model = PlatformSummary::find()->select('demand_number')->where(['purchase_type'=>2,'is_push'=>0])->andWhere(['>','create_time','2018-03-06 00:00:00'])->asArray()->orderBy('id asc')->all();
        
        foreach ($model as $k=>$v)
        {
        $se = PurchaseDemand::find()->where(['demand_number'=>$v['demand_number']])->one();
        
        $arr =[];
        $arr2 =[];
        if(empty($se))
        {
        $seb =PlatformSummary::find()->where(['demand_number'=>$v['demand_number']])->one();
        if($seb)
        {
        $seb->is_purchase=1;
        $seb->save(false);
        echo $seb->demand_number."\r\n";
        } else{
        echo $seb->demand_number."<br/>";
        }
        
        } else {
        
        $arr2[] =$v['demand_number'];
        echo '<pre>';
        print_r($arr2);
        echo "<br/>";
        }
        }*/
        $model = PlatformSummary::find()->where(['purchase_type'=>2])->andWhere(['product_category'=>null])->asArray()->orderBy('id asc')->all();
        
        
        foreach ($model as $v)
        {
            $title =Product::find()->select('product_category_id')->where(['sku'=>$v['sku']])->scalar();
            PlatformSummary::updateAll(['product_category'=>$title],['id'=>$v['id']]);
        }
        
        
    }
    /**
     * 根据选择导出类型，导出数据
     * excel导出
     */
    public function actionExportCsv1()
    {
        $demand_purchase_type = Yii::$app->request->get('demand_purchase_type');
        $limit = Yii::$app->request->get('limit');
        $id = Yii::$app->request->get('ids');
        
        $daterangepicker_start = Yii::$app->request->get('daterangepicker_start');
        $daterangepicker_end = Yii::$app->request->get('daterangepicker_end');
        
        switch ($demand_purchase_type) {
            case 1:
                $this->demandPurchase($is_purchase=1, $limit, $daterangepicker_start, $daterangepicker_end);
                break;
            case 2:
                $this->demandPurchase($is_purchase=2, $limit, $daterangepicker_start, $daterangepicker_end);
                break;
            case 3:
                $this->demandAuditPay($pay_status=null, $limit, $daterangepicker_start, $daterangepicker_end);
                break;
            case 4:
                $this->demandAuditPay($pay_status=4, $limit, $daterangepicker_start, $daterangepicker_end);
                break;
            case 5:
                $this->demandAuditPay($pay_status=5, $limit, $daterangepicker_start, $daterangepicker_end);
                break;
            case 6:
                $this->demandCsv($limit, $id, $daterangepicker_start, $daterangepicker_end);
                break;
            default:
                Yii::$app->getSession()->setFlash('error','当我不知道会这招吗',true);
                return $this->redirect(['index']);
                break;
        }
    }
    
    /**
     *已 生成需求 未采购订单 或 已采购订单
     * 导出Excel
     */
    public function demandPurchase($is_purchase=2, $limit, $daterangepicker_start, $daterangepicker_end)
    {
        $sql = "SELECT `pur_platform_summary`.*,pur_supplier.supplier_name,pur_purchase_order.created_at as order_time,pur_purchase_order_pay.processing_time,pur_purchase_order_pay.payer_time,pur_product_category.category_cn_name,pur_product.product_status,pur_product.create_id as developer
        FROM `pur_platform_summary`
        LEFT JOIN `pur_purchase_demand` ON `pur_platform_summary`.`demand_number` = `pur_purchase_demand`.`demand_number`
        LEFT JOIN `pur_purchase_order` ON `pur_purchase_demand`.`pur_number` = `pur_purchase_order`.`pur_number`
        LEFT JOIN `pur_purchase_order_pay` ON `pur_purchase_demand`.`pur_number` = `pur_purchase_order_pay`.`pur_number`
        
        LEFT JOIN `pur_product_supplier` ON `pur_platform_summary`.`sku` = `pur_product_supplier`.`sku`
        LEFT JOIN `pur_supplier` ON `pur_product_supplier`.`supplier_code` = `pur_supplier`.`supplier_code`
        
        LEFT JOIN `pur_product` ON `pur_platform_summary`.`sku` = `pur_product`.`sku`
        LEFT JOIN `pur_product_category` ON `pur_product`.`product_category_id` = `pur_product_category`.`id`
        WHERE (`is_purchase` = $is_purchase)
        AND (`pur_platform_summary`.`purchase_type` = 2) ";
        
        if ($daterangepicker_start != null && $daterangepicker_end != null) {
            $sql .= "AND (`pur_platform_summary`.`create_time` BETWEEN '$daterangepicker_start' AND '$daterangepicker_end') ";
        }
        
        if ($limit !='all') {
            $sql .= "LIMIT $limit";
        }
        if ($limit == 'all' && $daterangepicker_end == null) {
            Yii::$app->getSession()->setFlash('error','请选择时间段',true);
            return $this->redirect(['index']);
        }
        $model = Yii::$app->db->createCommand($sql)->queryAll();
        
        if (empty($model)){
            Yii::$app->getSession()->setFlash('error','没有已生成需求 未采购或已采购的订单',true);
            return $this->redirect(['index']);
        }
        $table = [
            'SKU',
            '产品名',
            '产品线',
            '采购员', //
            '供应商',
            '产品状态',
            '开发人',
            '平台号',
            '采购数量',
            '采购仓',
            '中转仓',
            '是否中转',
            '需求单号',
            '需求人',
            '需求建立时间',
            '审核状态',
            '同意/驳回人',
            '同意/驳回时间',
            '原因', //
            '销售备注',
            
            '采购单生成时间', //
            '财务审核时间',
            '财务付款时间',
        ];
        
        $table_head = [];
        foreach($model as $k=>$v)
        {
            $table_head[$k][]=$v['sku'];
            $table_head[$k][]=$v['product_name'];
            $table_head[$k][]=$v['category_cn_name'];
            $table_head[$k][]=$v['buyer'];
            $table_head[$k][]=  $v['supplier_name'];
            $table_head[$k][]=!empty($v['product_status'])?SupplierGoodsServices::getProductStatus($v['product_status']) : '';
            $table_head[$k][]=!empty($v['developer'])?$v['developer'] : '';
            $table_head[$k][]=$v['platform_number'];
            $table_head[$k][]=$v['purchase_quantity'];
            $table_head[$k][]=BaseServices::getWarehouseCode($v['purchase_warehouse']);
            $table_head[$k][]=$v['transit_warehouse']?BaseServices::getWarehouseCode($v['transit_warehouse']):'';
            $table_head[$k][]=PlatformSummaryServices::getIsTransit($v['is_transit']);
            $table_head[$k][]=$v['demand_number'];
            $table_head[$k][]=$v['create_id'];
            $table_head[$k][]=$v['create_time'];
            $table_head[$k][]=PlatformSummaryServices::getLevelAuditStatus($v['level_audit_status']);  //审核状态（销售需求）
            $table_head[$k][]=$v['level_audit_status']==4 ?$v['buyer'] : $v['agree_user'];
            $table_head[$k][]=$v['level_audit_status']==4 ?$v['purchase_time'] : $v['agree_time'];
            $table_head[$k][]=$v['level_audit_status']==2 ? $v['audit_note'] : ($v['level_audit_status']==4?$v['purchase_note']:'');
            $table_head[$k][]=$v['sales_note'];
            
            $table_head[$k][]=!empty($v['order_time']) ? $v['order_time'] : '';
            $table_head[$k][]=!empty($v['processing_time']) ? $v['processing_time'] : '';
            $table_head[$k][]=!empty($v['payer_time']) ? $v['payer_time'] : '';
        }
        
        theCsv::export([
            'header' =>$table,
            'data' => $table_head,
            'name' => ($is_purchase==1 ? '已生成需求未' : '已生成需求已') . '采购订单--' . date('Y-m-d') . '.csv',  //Excel表名
        ]);
        die;
    }
    /**
     * excel导出
     * 导出 未审核 已审核未付款 或 已审核已付款的采购单）
     */
    public function demandAuditPay($pay_status=[4, 5], $limit, $daterangepicker_start, $daterangepicker_end)
    {
        $table = [
            'SKU',
            '产品名称',
            '采购单号',
            '仓库',
            '采购单状态',
            '采购员',
            '提交时间（采购日期）',
            '付款状态',
            '供应商名称',
            'SKU数量',
            '采购数量',
            '总金额',
            '运费',
            '结算方式',
            '审核时间',
            '订单号',
            '确认备注',
            '采购单生成时间',
        ];
        
        $table_head = [];
        
        $sql = "SELECT *,pur_purchase_order.id,pur_purchase_order.pur_number,pur_purchase_order.supplier_code,pur_purchase_order.auditor,pur_purchase_order.pay_type,pur_purchase_order.pay_status,order_number
                FROM `pur_purchase_order`
                LEFT JOIN `pur_purchase_order_items` ON `pur_purchase_order`.`pur_number` = `pur_purchase_order_items`.`pur_number`
                LEFT JOIN `pur_purchase_order_orders` ON `pur_purchase_order`.`pur_number` = `pur_purchase_order_orders`.`pur_number`
                WHERE (`purchase_type` = 2) ";
        
        if ($pay_status===null && $daterangepicker_start == null) {
            $sql .= "AND (`purchas_status` < 3)";
        } elseif($pay_status===null && $daterangepicker_start != null) {
            $sql .= "AND (`purchas_status` < 3) AND (`created_at` BETWEEN '$daterangepicker_start' AND '$daterangepicker_end')";
        } elseif($pay_status!==null && $daterangepicker_start == null) {
            $sql .= "AND (`purchas_status` > 2) AND (`pur_purchase_order`.`pay_status` = $pay_status) AND (`purchas_status` <> 10)";
        } else if($pay_status!==null && $daterangepicker_start != null) {
            $sql .= "AND (`purchas_status` > 2) AND (`pur_purchase_order`.`pay_status` = $pay_status) AND (`purchas_status` <> 10) AND (`created_at` BETWEEN '$daterangepicker_start' AND '$daterangepicker_end')";
        }
        if ($limit != 'all') {
            $sql .= " LIMIT $limit";
        }
        if ($limit == 'all' && $daterangepicker_end == null) {
            Yii::$app->getSession()->setFlash('error','请选择时间段',true);
            return $this->redirect(['index']);
        }
        
        $model = Yii::$app->db->createCommand($sql)->queryAll();
        if (empty($model)){
            Yii::$app->getSession()->setFlash('error','无未审核或已经审核的已经付款或未付款的采购单',true);
            return $this->redirect(['index']);
        }
        
        foreach($model as $k=>$v)
        {
            $table_head[$k][]=$v['sku'];
            $table_head[$k][]=$v['name'];
            $table_head[$k][]=$v['pur_number'];
            $table_head[$k][]=BaseServices::getWarehouseCode($v['warehouse_code']);
            $table_head[$k][]=strip_tags(PurchaseOrderServices::getPurchaseStatus($v['purchas_status']));
            $table_head[$k][]=$v['buyer'];
            $table_head[$k][]=$v['submit_time'];
            $table_head[$k][]=strip_tags(!empty($v['pay_status'])?PurchaseOrderServices::getPayStatus($v['pay_status']) : '');
            $table_head[$k][]=$v['supplier_name'];
            $table_head[$k][]=PurchaseOrderItems::find()->where(['pur_number'=>$v['pur_number']])->count('id');
            $table_head[$k][]=PurchaseOrderItems::find()->where(['pur_number'=>$v['pur_number']])->sum('ctq');
            $table_head[$k][]=PurchaseOrderItems::getCountPrice($v['pur_number']);
            $table_head[$k][]=round(PurchaseOrderShip::find()->where(['pur_number'=>$v['pur_number']])->sum('freight'),2);
            $table_head[$k][]=$v['account_type'] ? SupplierServices::getSettlementMethod($v['account_type']) : '';
            $table_head[$k][]=$v['audit_time'];
            $table_head[$k][]=$v['order_number'];
            $table_head[$k][]=$v['audit_note'];
            $table_head[$k][]=$v['created_at'];
        }
        
        theCsv::export([
            'header' =>$table,
            'data' => $table_head,
            'name' => ($pay_status==null ? '已生成需求未审批' : ($pay_status==4 ? '已生成需求已审批未付款' : '已生成需求已审批已付款')) . '采购单--' . date('Y-m-d') . '.csv',  //Excel表名
        ]);
        die;
    }
    /**
     * 导出当前页面的数据
     * excel导出
     */
    public function demandCsv($limit, $id, $daterangepicker_start, $daterangepicker_end)
    {
        $sql = "SELECT
                    `pur_platform_summary`.*,`supplier_name`
                FROM
                    `pur_platform_summary`
                LEFT JOIN `pur_product_supplier` ON `pur_platform_summary`.`sku` = `pur_product_supplier`.`sku`
                LEFT JOIN `pur_supplier` ON `pur_product_supplier`.`supplier_code` = `pur_supplier`.`supplier_code`
                WHERE
                    (`level_audit_status` <> 3)
                AND (`purchase_type` = 2) ";
        
        if ($id != null) {
            $sql .= "AND (`pur_platform_summary`.`id` IN ( $id )) ";
        }
        if ($daterangepicker_end != null) {
            $sql .= "AND (`pur_platform_summary`.`create_time` BETWEEN '$daterangepicker_start' AND '$daterangepicker_end') ";
        }
        if ($id == null && $daterangepicker_end == null) {
            $sql .= "AND (`is_supplier` = 1) ";
        }
        if ($limit != 'all') {
            $sql .= "LIMIT $limit";
        }
        if ($limit == 'all' && $daterangepicker_end == null) {
            Yii::$app->getSession()->setFlash('error','请选择时间段',true);
            return $this->redirect(['index']);
        }
        
        $model = Yii::$app->db->createCommand($sql)->queryAll();
        
        if (empty($model)){
            Yii::$app->getSession()->setFlash('error','无采购需求汇总数据！！',true);
            return $this->redirect(['index']);
        }
        
        $table = [
            'SKU',
            '产品名称（中文名称）',
            '产品分类',
            '需求单号',
            '供应商',
            '是否生成采购计划',
            '备货平台',
            '采购数量',
            '中转数量',
            '采购仓',
            '中转',
            '中转仓',
            '物流类型',
            '需求状态',
            '需求人',
            '需求时间',
            '同意（驳回）人',
            '同意（驳回）时间',
            '推送状态',
            '备注',
        ];
        $table_head = [];
        foreach($model as $k=>$v)
        {
            $table_head[$k][]=$v['sku'];
            $table_head[$k][]=$v['product_name'];
            $table_head[$k][]=$v['product_category']? (BaseServices::getCategory($v['product_category'])) : '';  //产品分类
            $table_head[$k][]=$v['demand_number']; //需求单号
            $table_head[$k][]=$v['supplier_name'];
            $table_head[$k][]=$v['is_purchase'] ==1 ? '未生成':'已生成';  //是否生成采购计划
            $table_head[$k][]=$v['platform_number'];  // 备货平台
            $table_head[$k][]=$v['purchase_quantity'];
            $table_head[$k][]=$v['transit_number'];
            $table_head[$k][]=BaseServices::getWarehouseCode($v['purchase_warehouse']);
            $table_head[$k][]=$v['is_transit']==1 ? '否':'是'; //中转
            $table_head[$k][]=$v['transit_warehouse']?BaseServices::getWarehouseCode($v['transit_warehouse']):'';
            $table_head[$k][]=PlatformSummaryServices::getTransportStyle($v['transport_style']);
            $table_head[$k][]=PlatformSummaryServices::getLevelAuditStatus($v['level_audit_status']);  //审核状态（销售需求）
            $table_head[$k][]=$v['create_id']; //需求人
            $table_head[$k][]=$v['create_time']; //需求时间
            if ($v['level_audit_status']==4) {
                $table_head[$k][]='采购驳回人：' . $v['buyer']; //驳回人
                $table_head[$k][]='采购驳回时间:'.$v['purchase_time']; //驳回时间
            } else {
                $table_head[$k][]='同意(驳回)人:'.$v['agree_user']; //驳回人
                $table_head[$k][]='同意(驳回)时间:'.$v['agree_time']; //驳回时间
            }
            
            $table_head[$k][]=PlatformSummaryServices::getIsPush($v['is_push']);
            $table_head[$k][]=$v['sales_note'];
        }
        
        theCsv::export([
            'header' =>$table, //表标头
            'data' => $table_head, //表中的内容
            'name' => '采购需求汇总表--' . date('Y-m-d') . '.csv',  //Excel表名字
        ]);
        die;
    }
    
    //获取sku一箱的数量
    public function actionGetboxqty(){
        $sku=Yii::$app->request->post('sku');
        if($sku){
            $boxqty=BoxSkuQty::getBoxQty($sku);
        }
        
        if(!empty($boxqty)){
            exit(json_encode(['code'=>1,'boxqty'=>$boxqty]));
        }else{
            exit(json_encode(['code'=>0,'msg'=>'failure']));
        }
    }
    //ajax 获取sku报价
    public function actionGetSkuPrice(){
        if(Yii::$app->request->isAjax){
            $sku = Yii::$app->request->getBodyParam('sku');
            $skuInfo = ProductProvider::find()
            ->select('q.supplierprice')
            ->alias('t')
            ->where(['t.sku'=>$sku])
            ->andWhere(['t.is_supplier'=>1])
            ->leftJoin(SupplierQuotes::tableName().' q','t.quotes_id=q.id')
            ->scalar();
            if($skuInfo!==false){
                echo json_encode(['status'=>'success','price'=>$skuInfo]);
                exit();
            }
            echo json_encode(['status'=>'error','price'=>'']);
            exit();
        }
    }
    
    public function actionCheckRule(){
        if(Yii::$app->request->isAjax){
            
            $ids = Yii::$app->request->getQueryParam('ids');
            $updateArr = Yii::$app->request->getQueryParam('updateArr');
            $ids = is_array($ids) ? $ids :explode(',',$ids);
            $updateData = [];
            foreach ($updateArr as $value){
                $dataArr = explode('-',$value);
                $updateData[$dataArr[0]]=$dataArr[1];
            }
            foreach($ids as $v){
                $model = $this->findModel($v);
                if($model->level_audit_status==1){
                    continue;
                }
                if (strtotime($model->create_time)>strtotime('2018-05-08 23:59:59')){
                    $ruleVerify = OverseasDemandRule::verifyInterceptRule($model->sku,$model->purchase_warehouse,1,$model->demand_number,$model->transport_style,false,$updateData);
                    if($ruleVerify['status']=='error'){
                        $transport = PlatformSummaryServices::getTransportStyle($model->transport_style);
                        $warehouseName = Warehouse::find()->select('warehouse_name')->where(['warehouse_code'=>$model->purchase_warehouse])->scalar();
                        echo json_encode(['status'=>$ruleVerify['status'],'message'=>$ruleVerify['supplier_name'].'<br/>仓库：'.$warehouseName.'<br/>物流类型：'.$transport.'<br/>'.$ruleVerify['message']]);
                        Yii::$app->end();
                    }
                }
            }
            echo json_encode(['status'=>'success']);
            Yii::$app->end();
        }
    }
    //拦截规则编辑
    public function actionDemandRule(){
        $model = OverseasDemandRule::find()->where(['status'=>1])->all();
        $passModel = OverseasDemandPassRule::find()->where(['status'=>1])->all();
        if(Yii::$app->request->isPost){
            $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->id);
            if(in_array('超级管理员组',array_keys($roles))||in_array('采购经理组',array_keys($roles))){
                $ruleResponse = OverseasDemandRule::saveRules(Yii::$app->request->getBodyParam('OverseasDemandRule'));
                $passRuleResponse = OverseasDemandPassRule::saveRules(Yii::$app->request->getBodyParam('OverseasDemandPassRule'));
                Yii::$app->getSession()->setFlash('success','编辑成功');
            }else{
                Yii::$app->getSession()->setFlash('error','当前登录用户权限不符');
            }
            return $this->redirect(Yii::$app->request->referrer);
        }
        if(Yii::$app->request->isAjax){
            return $this->renderAjax('demand-rule',['model'=>$model,'passModel'=>$passModel]);
        }
    }
    
    public function actionBatchUpdate(){
        $warehouse_code = Yii::$app->request->getQueryParam('warehouse_code');
        $supplier_code  = Yii::$app->request->getQueryParam('supplier_code');
        $transport      = Yii::$app->request->getQueryParam('transport');
        $a_at           = Yii::$app->request->getQueryParam('a_at', '');
        $tab_index      = Yii::$app->request->getQueryParam('tab_index', 1);
        $num            = Yii::$app->request->getQueryParam('num');
        $ids            = Yii::$app->request->getQueryParam('ids');
        $type           = Yii::$app->request->getQueryParam('type');
        $update_arr     = isset(Yii::$app->request->post()['update_arr']) ? Yii::$app->request->post()['update_arr'] : [];
        $ids_list       = Yii::$app->request->getQueryParam('ids_list');
        if($ids_list and empty($ids)){ $ids = $ids_list;}
        if($supplier_code == '默认供应商为空'){$supplier_code = '';}

        if(Yii::$app->request->isAjax && empty($update_arr)){
            if($ids){//批量修改，根据传入的id修改
                $query = PlatformSummary::find()
                ->alias('t')
                ->leftJoin(ProductProvider::tableName().' ds','ds.sku = t.sku')
                ->where(['t.is_purchase'=>1])
                ->andWhere(['in','t.level_audit_status',[6,7]])
                ->andWhere(['in','t.id',explode(",",rtrim($ids,","))])
                ->andWhere(['ds.is_supplier'=>1])
                ->andWhere(['t..purchase_type'=>2])
                ->orderBy('ds.supplier_code');
                
            }elseif(empty($warehouse_code)){//7天3小时拦截没有采购仓
                $type = 'seven_days';
                $query = PlatformSummary::find()
                ->alias('t')
                ->leftJoin(ProductProvider::tableName().' ds','ds.sku = t.sku')
                ->where(['t.is_purchase'=>1])
                ->andWhere(['in','t.level_audit_status',[6,7]])
                ->andWhere(['ds.supplier_code'=>$supplier_code])
                ->andWhere(['ds.is_supplier'=>1])
                ->andWhere(['t..purchase_type'=>2])
                ->andWhere("audit_note like '%小时少于%'")
                ->orderBy('ds.supplier_code');
            }else{
                $query = PlatformSummary::find()
                ->alias('t')
                ->leftJoin(ProductProvider::tableName().' ds','ds.sku = t.sku')
                ->where(['t.is_purchase'=>1])
                ->andWhere(['t.purchase_warehouse'=>$warehouse_code])
                ->andWhere(['in','t.level_audit_status',[6,7]])
                ->andWhere(['ds.supplier_code'=>$supplier_code])
                ->andFilterWhere(['t.transport_style'=>$transport])
                ->andWhere(['ds.is_supplier'=>1])
                ->andWhere(['t..purchase_type'=>2])
                ->orderBy('ds.supplier_code asc');;
            }
            $datas = $query->all();
            if(empty($datas)){
                Yii::$app->getSession()->setFlash('warning','没有找到可以修改的数据，您选择的数据可能不支持修改，如默认供应商缺失等');
                return $this->redirect(Yii::$app->request->referrer);
            }
            
            //根据供应商、采购仓计算总价格
            $total = [];
            if($type == 'seven_days') {
                //7天3小时，只根据供应商统计，根据供应商统计总价
                foreach ($datas as $key => $model) {
                    if (isset($total[$model->defaultSupplier->supplier_code])) {
                        $total[$model->defaultSupplier->supplier_code]['price'] += $model->purchase_quantity * ($model->defaultQuotes->supplierprice * 1000) / 1000;
                        $total[$model->defaultSupplier->supplier_code]['total'] += 1;
                    } else {
                        $total[$model->defaultSupplier->supplier_code]['price'] = $model->purchase_quantity * ($model->defaultQuotes->supplierprice * 1000) / 1000;
                        $total[$model->defaultSupplier->supplier_code]['total'] = 1;
                    }
                    $total[$model->defaultSupplier->supplier_code]['count'] = 0;
                }
                
            }else{
                //非7天3小时，只根据供应商统计，根据供应商、采购商统计总价
                foreach ($datas as $key => $model) {
                    if (isset($total[$model->defaultSupplier->supplier_code][$model->purchase_warehouse])) {
                        $total[$model->defaultSupplier->supplier_code][$model->purchase_warehouse]['price'] += $model->purchase_quantity * ($model->defaultQuotes->supplierprice * 1000) / 1000;
                        $total[$model->defaultSupplier->supplier_code][$model->purchase_warehouse]['total'] += 1;
                    } else {
                        $total[$model->defaultSupplier->supplier_code][$model->purchase_warehouse]['price'] = $model->purchase_quantity * ($model->defaultQuotes->supplierprice * 1000) / 1000;
                        $total[$model->defaultSupplier->supplier_code][$model->purchase_warehouse]['total'] = 1;
                    }
                    $total[$model->defaultSupplier->supplier_code][$model->purchase_warehouse]['count'] = 0;
                }
            }
            
            return $this->renderAjax('batch-update',['datas'=>$datas,'total'=>$total,'type'=>$type]);
        }
        
        
        if(Yii::$app->request->isPost && !empty($update_arr)){
            //$updateForm = Yii::$app->request->getBodyParam('PlatformSummary');
            //            if(count($updateForm)!=$num){
            //                Yii::$app->getSession()->setFlash('error','数据有变动请刷新页面后重新修改');
            //            }
                $res = ['status'=>0,'msg'=>''];
                $updateForm = json_decode($update_arr);
                $tran =Yii::$app->db->beginTransaction();
                try{
                    foreach ($updateForm as $data){
                        $data = (array)$data;
                        if($data['purchase_quantity']<$data['transit_number']){
                            throw new Exception('提交数据有误');
                        }
                        if(!$warehouse_code){
                            $warehouse_code = $data['purchase_warehouse'];
                        }
                        $model = PlatformSummary::findOne(['demand_number'=>$data['demand_number']]);
                        $model->purchase_quantity = $data['purchase_quantity'];
                        $model->transit_number = $data['transit_number'];
                        $model->update_time = date('Y-m-d H:i:s',time());
                        $model->update_user_name = Yii::$app->user->identity->username;


                        $update_data_param = [
                            'purchase_quantity' => '数量',
                            'transit_number' => '中转数量',
                        ];
                        $update_data = CommonServices::getUpdateData($model, $update_data_param);
                        PurchaseOrderServices::writelog($data['demand_number'], '修改采购数量', '', $update_data);
                        $model->save(false);
                        
                        $skuInfo = ProductProvider::find()
                        ->select('t.supplier_code,q.supplierprice,s.supplier_name')
                        ->alias('t')
                        ->where(['t.sku'=>$data['sku']])
                        ->andWhere(['t.is_supplier'=>1])
                        ->leftJoin(SupplierQuotes::tableName().' q','t.quotes_id=q.id')
                        ->leftJoin(Supplier::tableName().' s','t.supplier_code=s.supplier_code')
                        ->asArray()
                        ->one();
                        
                        
                        $supplierSku = ProductProvider::find()->select('sku')->where(['supplier_code'=>$skuInfo['supplier_code'],'is_supplier'=>1])->column();
                        OverseasDemandRule::updateSummary($supplierSku,$warehouse_code,$data['transport_style']);
                    }

                    $tran->commit();
                    //Yii::$app->getSession()->setFlash('success','批量修改成功');
                    $res['status'] = 1;
                    $res['msg'] = '批量修改成功';
                    die(json_encode($res));
                }catch (Exception $e){
                    $tran->rollBack();
                    //Yii::$app->getSession()->setFlash('error',$e->getMessage());
                    $res['msg'] = $e->getMessage();
                    die(json_encode($res));
                }
                // return $this->redirect('index?tab_index='.$tab_index.'#'.$a_at);
        }
    }
    
    //模拟执行批量同意操作
    public  function actionSuggestAgree()
    {
        $idDatas = PlatformSummary::find()->select('id')->where(['source'=>2,'level_audit_status'=>0,'purchase_type'=>2])->asArray()->all();
        $ids = array_column($idDatas,'id');
        if(!empty($ids)){
            if(!empty($ids)){//批量
                $refuse = [];
                foreach($ids as $v){
                    $model = $this->findModel($v);
                    if($model->level_audit_status==1){
                        continue;
                    }
                    if($model->transport_style==1){
                        $ruleVerify = OverseasDemandRule::verifyInterceptRule($model->sku,$model->purchase_warehouse,2,$model->demand_number);
                    }elseif (strtotime($model->create_time)>strtotime('2018-05-08 23:59:59')){
                        $ruleVerify = OverseasDemandRule::verifyInterceptRule($model->sku,$model->purchase_warehouse,1,$model->demand_number);
                    }
                    if(isset($ruleVerify)&&$ruleVerify['status']=='error'){
                        $model->level_audit_status = $ruleVerify['level'];
                        $model->audit_note         = $ruleVerify['message'];
                        $model->is_push         = 0;
                        $model->agree_user=Yii::$app->user->identity->username;
                        $model->agree_time=date('Y-m-d H:i:s',time());
                        $refuse[]=$model->sku;
                    }else{
                        $arr = ['0','3','6','7'];
                        if(in_array($model->level_audit_status,$arr)){
                            $model->level_audit_status=1;
                        }
                        $model->audit_note         = '';
                        $model->is_push = 0;
                        $model->agree_user=Yii::$app->user->identity->username;
                        $model->agree_time=date('Y-m-d H:i:s',time());
                    }
                    $refuse = isset($ruleVerify['updateSku']) ? array_diff($refuse,$ruleVerify['updateSku']): $refuse;
                    $model->save(false);
                }
                if(!empty($refuse)){
                    Yii::$app->end();
                }else{
                    Yii::$app->end();
                }
            }
        }else{
            Yii::$app->end();
        }
    }
    
    
    //批量撤销
    public function actionBatchRevoke(){
        $warehouse_code = Yii::$app->request->getQueryParam('warehouse_code');
        $supplier_code  = Yii::$app->request->getQueryParam('supplier_code');
        $num            = Yii::$app->request->getQueryParam('num');
        $ids            = Yii::$app->request->getQueryParam('ids');
        $ids_list       = Yii::$app->request->getQueryParam('ids_list');
        if($ids_list and empty($ids)){ $ids = $ids_list;};

        if(Yii::$app->request->isAjax){
            if($ids){
                $query = PlatformSummary::find()
                ->alias('t')
                ->where(['t.is_purchase'=>1])
                ->andWhere(['in','t.level_audit_status',[6,7]])
                ->andWhere(['in','t.id',explode(",",rtrim($ids,","))])
                ->andWhere(['t..purchase_type'=>2]);
            }else{
                Yii::$app->getSession()->setFlash('error','查询参数缺失');
                return $this->redirect(Yii::$app->request->referrer);
            }
            $datas = $query->all();
            return $this->renderAjax('batch-update',['datas'=>$datas,'view'=>'revoke']);
        }
        if(Yii::$app->request->isPost){
            $updateForm = Yii::$app->request->getBodyParam('PlatformSummary');
            if(count($updateForm)!=$num){
                Yii::$app->getSession()->setFlash('error','数据有变动请刷新页面后重新修改');
            }
            $tran =Yii::$app->db->beginTransaction();
            try{
                if(empty($updateForm)) throw new Exception('获取数据失败，请使用批量撤销！');
                foreach ($updateForm as $data){
//                    if($data['purchase_quantity']<$data['transit_number']){
//                        throw new Exception('提交数据有误');
//                    }
//                    if(!$warehouse_code){
//                        $warehouse_code = $data['purchase_warehouse'];
//                    }
                    Yii::$app->db->createCommand()->update(PlatformSummary::tableName(),
                        ['level_audit_status'=>3],
                        ['demand_number'=>$data['demand_number']])->execute();
                        
                        PurchaseOrderServices::writelog($data['demand_number'], '撤销需求');
                }
                $tran->commit();
                Yii::$app->getSession()->setFlash('success','批量撤销成功');
            }catch (Exception $e){
                $tran->rollBack();
                Yii::$app->getSession()->setFlash('error',"批量撤销失败\r".$e->getMessage());
            }
            return $this->redirect(Yii::$app->request->referrer);
        }
    }
}
