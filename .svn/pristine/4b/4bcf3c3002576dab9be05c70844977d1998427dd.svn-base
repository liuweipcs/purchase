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
 * Date: 2017/9/23 0023
 * Description: PlatformSummaryController.php
 */
use app\api\v1\models\ProductProvider;
use app\api\v1\models\Warehouse;
use app\config\Vhelper;
use app\models\PlatformSummarySearch;
use app\models\Product;
use app\models\PurchaseDemand;
use app\models\PurchaseHistory;
use app\models\PurchaseLog;
use app\models\PurchaseNote;
use app\models\PurchaseOrder;
use app\models\PurchaseOrderItems;
use app\models\PurchaseOrderPay;
use app\models\PurchaseOrderPaySearch;
use app\models\PurchaseOrderTaxes;
use app\models\PurchaseSuggestNote;
use app\models\PurchaseTemporary;
use app\models\SupervisorGroupBind;
use app\models\WarehouseResults;
use app\models\ArrivalRecord;
use app\models\PurchaseOrderCancelSub;
use app\models\UebExpressReceipt;
use app\services\BaseServices;
use app\services\CommonServices;
use app\services\PurchaseOrderServices;
use app\models\PurchaseEstimatedTime;
use app\services\SupplierServices;
use m35\thecsv\theCsv;
use Yii;
use yii\db\Exception;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use yii\log\EmailTarget;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\PlatformSummary;
use yii\web\UploadedFile;
use yii\helpers\Html;
use app\models\SupplierQuotes;
use app\models\Supplier;
use linslin\yii2\curl;
use yii\helpers\Json;

class PlatformSummaryController extends BaseController
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
                    'delete' => ['POST'],
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
        $searchModel  = new PlatformSummarySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
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
            if(!empty($product)&&$product->sku!==$data['PlatformSummary']['sku']){
                Yii::$app->getSession()->setFlash('warning','提交的sku：'.$data['PlatformSummary']['sku'].'与系统sku：'.$product->sku.'不符');
                return $this->redirect(Yii::$app->request->referrer);
            }
            if(empty($product)||!in_array($product->product_status,[4,18,3,20])||$product->product_is_multi==2||$product->product_type==2){
                Yii::$app->getSession()->setFlash('warning','产品不存在或者产品包含如下属性(状态:非在售,非试卖在售,非预上线,非预上线拍摄中,捆绑sku,主sku)');
                return $this->redirect(Yii::$app->request->referrer);
            }
            if(in_array($product->product_status,[20])){//预上线拍摄中的状态，在创建销售需求的时候，判断采购有没有记录，如果有，则不能创建需求
                $is_exist = PurchaseOrderItems::find()
                    ->alias('items')
                    ->leftJoin(PurchaseOrder::tableName().' order','items.pur_number=order.pur_number')
                    ->where(['in','order.purchas_status',[1,2,3,5,6,7,8,9]])
                    ->andWhere(['items.sku'=>$data['PlatformSummary']['sku']])
                    ->count();
                if($is_exist > 0){
                    Yii::$app->getSession()->setFlash('warning','产品为预上线拍摄中的状态,有对应的采购记录,不能创建需求');
                    return $this->redirect(Yii::$app->request->referrer);
                }
            }
            $model->demand_number = CommonServices::getNumber('RD');
            $model->supplier_code = ProductProvider::find()->select('supplier_code')->where(['sku'=>$data['PlatformSummary']['sku'],'is_supplier'=>1])->scalar();
            $model->purchase_type = 3;
            $model->load(Yii::$app->request->post());
            $is_back_tax = $pur_ticketed_point = 0;
            $supplier_model = \app\models\ProductProvider::find()->where(['sku'=>$data['PlatformSummary']['sku'],'is_supplier'=>1])->one();
            if ($supplier_model) {
                $supplier_info = SupplierQuotes::find()->select('is_back_tax,pur_ticketed_point')->where(['id'=>$supplier_model->quotes_id])->one();
                $is_back_tax = $supplier_info['is_back_tax'] == 1 ? 1 : 2;
                $pur_ticketed_point = $supplier_info['pur_ticketed_point'];
            }
            if (empty($model->purchase_warehouse)) {
                $model->purchase_warehouse = 'FBA_SZ_AA';
            }
            if ($model->purchase_warehouse == 'TS' && $is_back_tax != 1) {
                Yii::$app->getSession()->setFlash('error','产品是否退税发生了变化，请重新创建');
                return $this->redirect(Yii::$app->request->referrer);
            }
            // $model->is_back_tax = $model->purchase_warehouse == 'TS' ? 1 : 2;
            $model->is_back_tax = $data['PlatformSummary']['is_back_tax_post'] ?: 2;
            $model->pur_ticketed_point = $model->purchase_warehouse == 'TS' ? $pur_ticketed_point : 0;
            if ($model->purchase_warehouse == 'TS') {
                //判断供应商的付款方式是否为银行转账
                $payment_method = Supplier::find()->select('payment_method')->where(['supplier_code'=>$model->supplier_code])->scalar();
                if ($payment_method != 3) {
                    Yii::$app->getSession()->setFlash('error','该sku当前供应商支付方式不是【银行卡转账】，不能创建【退税】需求');
                    return $this->redirect(Yii::$app->request->referrer);
                }
            }
            $model->level_audit_status = 7;
            $model->xiaoshou_zhanghao = $data['PlatformSummary']['xiaoshou_zhanghao'] ?: '';
            $result = $model->save();
            Yii::$app->getSession()->setFlash('success',"恭喜你添加成功！",true);
            return $this->redirect(['sales-index']);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
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
            $data= Yii::$app->request->post();
            $product = Product::find()->where(['sku'=>$data['PlatformSummary']['sku']])->one();
            if(empty($product)||!in_array($product->product_status,[4,18,3,20])||$product->product_is_multi==2||$product->product_type==2){
                Yii::$app->getSession()->setFlash('error','产品不存在或者产品包含如下属性(状态:非在售,非试卖在售,非预上线,非预上线拍摄中,捆绑sku,主sku)');
                return $this->redirect(Yii::$app->request->referrer);
            }
            $model->level_audit_status=7;
            $model->audit_note='';
            $model->save();
            return $this->redirect(['sales-index']);
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
        $this->findModel($id)->delete();

        return $this->redirect(['sales-index']);
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
     * 审核-同意
     */
    public function actionAgree()
    {
        Yii::$app->response->format = 'raw';
        $ids=Yii::$app->request->post('ids');
        $id=Yii::$app->request->get('id');

        if(!empty($id) || !empty($ids)){
            $userGroup = SupervisorGroupBind::find()->where(['supervisor_name'=>Yii::$app->user->identity->username])->all();

            $groupId   = [];
            if(!empty($userGroup)){
                foreach($userGroup  as $value){
                    $groupId[] = $value->group_id;
                }
            }

            $tran = Yii::$app->db->beginTransaction();
            try{
                if(!empty($ids)){//批量
                    foreach($ids as $v){
                        $model = $this->findModel($v);
                        if(empty($model)|| empty($groupId)||empty($model->group_id) || !in_array($model->group_id,$groupId)){
                            throw new HttpException(500,'数据异常或者分组权限不足！');
                        }
                        //判断sku金额大于等于10000且备货数量大于等于100，满足这个条件，庄总审核完成后需求才能到采购需求汇总里
                        if(empty($model->defaultSupplier->supplier_code)){
                            throw new HttpException(500,'有需求没有报价请先维护,需求单号：'.$model->demand_number);
                        }
                        $price = self::actionGetPrice($model->defaultSupplier->supplier_code,$model->sku,$model->purchase_quantity);
                        if($model->purchase_quantity >= 100 || $price >= 10000){
                            if($model->init_level_audit_status==0){
                                $model->init_level_audit_status=1;
                            }
                        }else{
                            if($model->level_audit_status==0){
                                $model->level_audit_status=1;
                                $model->init_level_audit_status=0;
                            }
                        }
                        $model->agree_user=Yii::$app->user->identity->username;
                        $model->agree_time=date('Y-m-d H:i:s',time());
                        if($model->save(false)==false){
                            throw new HttpException(500,'需求同意失败！');
                        }
                    }
                }else{
                    $model = $this->findModel($id);
                    if(empty($model)|| empty($groupId)||empty($model->group_id) || !in_array($model->group_id,$groupId)){
                        throw new HttpException(500,'数据异常或者分组权限不足！');
                    }

                    //判断sku金额大于等于10000且备货数量大于等于100，满足这个条件，庄总审核完成后需求才能到采购需求汇总里
                    if(empty($model->defaultSupplier->supplier_code)){
                        throw new HttpException(500,'有需求没有报价请先维护,需求单号：'.$model->demand_number);
                    }
                    $price = self::actionGetPrice($model->defaultSupplier->supplier_code,$model->sku,$model->purchase_quantity);
                    if($model->purchase_quantity >= 100 || $price >= 10000){
                        if($model->init_level_audit_status==0){
                            $model->init_level_audit_status=1;
                        }
                    }else{
                        if($model->level_audit_status==0){
                            $model->level_audit_status=1;
                            $model->init_level_audit_status=0;
                        }
                    }
                    $model->agree_user=Yii::$app->user->identity->username;
                    $model->agree_time=date('Y-m-d H:i:s',time());
                    if($model->save(false) == false){
                        throw new HttpException(500,'需求同意失败！');
                    };
                }
                $tran->commit();
                Yii::$app->getSession()->setFlash('success',"操作成功！",true);
            }catch(HttpException $e){
                $tran->rollBack();
                Yii::$app->getSession()->setFlash('error',$e->getMessage(),true);
            }
        }else{
            Yii::$app->getSession()->setFlash('error',"没有数据ID,操作失败！",true);
        }
        $page = $_REQUEST['page'];
        return $this->redirect(['sales-index','page'=>$page]);
    }

    /**
     * 审核-同意(数量大于500，金额大于20000)
     */
    public function actionInitAgree()
    {
        $ids=Yii::$app->request->post('ids');
        $id=Yii::$app->request->get('id');

        if(!empty($id) || !empty($ids)){
            $tran = Yii::$app->db->beginTransaction();
            try{
                $res = ['status'=>1,'msg'=>''];
                if(!empty($ids)){//批量
                    foreach($ids as $v){
                        $model = $this->findModel($v);
                        if(empty($model->defaultSupplier->supplier_code)){
                            $tran->rollBack();
                            throw new HttpException(500,'有需求没有报价请先维护,需求单号：'.$model->demand_number);
                        }
                        if ($model->init_level_audit_status==1) {
                            $model->level_audit_status=1;
                            $model->init_level_audit_status=0;
                            $model->agree_user=Yii::$app->user->identity->username;
                            $model->agree_time=date('Y-m-d H:i:s',time());
                            if($model->save(false)==false){
                                $tran->rollBack();
                                throw new HttpException(500,'需求同意失败！');
                            }
                        } else {
                            $tran->rollBack();
                            throw new HttpException(500,$model->demand_number . ' 该需求不在审核权限范围内');
                        }
                    }
                    PlatformSummary::pushSummaryPriority($ids);
                }else{
                    $model = $this->findModel($id);
                    if(empty($model->defaultSupplier->supplier_code)){
                        throw new HttpException(500,'有需求没有报价请先维护,需求单号：'.$model->demand_number);
                    }
                    if ($model->init_level_audit_status==1) {
                        $model->level_audit_status=1;
                        $model->init_level_audit_status=0;
                        $model->agree_user=Yii::$app->user->identity->username;
                        $model->agree_time=date('Y-m-d H:i:s',time());
                        if($model->save(false) == false){
                            $tran->rollBack();
                            throw new HttpException(500,'需求同意失败！');
                        };
                        PlatformSummary::pushSummaryPriority($id);
                    } else {
                        throw new HttpException(500,$model->demand_number . ' 该需求不在审核权限范围内');
                    }
                }
                $tran->commit();
                Yii::$app->getSession()->setFlash('success',"操作成功！",true);
            }catch(HttpException $e){
                $tran->rollBack();
                Yii::$app->getSession()->setFlash('error',$e->getMessage(),true);
            }
        }else{
            Yii::$app->getSession()->setFlash('error',"没有数据ID,操作失败！",true);
        }
        $page = $_REQUEST['page'];
        return $this->redirect(['sales-index','page'=>$page]);
    }


    /**
     * 审核-驳回
     */
    public function actionDisagree()
    {
        /*$model = $this->findModel($id);
        if(Yii::$app->request->isPost)
        {
            $model->level_audit_status=2;
            $model->agree_user=Yii::$app->user->identity->username;
            $model->audit_note=Yii::$app->request->post()['PlatformSummary']['audit_note'];
            $model->agree_time=date('Y-m-d H:i:s',time());
            $model->save();
            Yii::$app->getSession()->setFlash('success',"恭喜您操作成功！",true);
            return $this->redirect(['index']);

        } else{

            return $this->renderAjax('note',['model' =>$model]);

        }*/


        $post=Yii::$app->request->post('PlatformSummary');

        if(!empty($post)){
            $ids=explode(',',$post['id']);
            $tran = Yii::$app->db->beginTransaction();
            try{
                if(count($ids)>1){//批量
                    foreach($ids as $v){
                        $post_model = $this->findModel($v);
                        if($post_model->level_audit_status==0){
                            $post_model->level_audit_status=2;
                        }
                        $model->sale_audit_status = 0;
                        $post_model->agree_user=Yii::$app->user->identity->username;
                        $post_model->audit_note=$post['audit_note'];
                        $post_model->agree_time=date('Y-m-d H:i:s',time());
                        $post_model->save();
                    }
                }else{
                    $model = $this->findModel($post['id']);
                    if($model->level_audit_status==0){
                        $model->level_audit_status=2;
                    }
                    $model->sale_audit_status = 0;
                    $model->agree_user=Yii::$app->user->identity->username;
                    $model->audit_note=$post['audit_note'];
                    $model->agree_time=date('Y-m-d H:i:s',time());
                    $model->save();
                }
                $tran->commit();
                Yii::$app->getSession()->setFlash('success',"恭喜您操作成功！",true);
                return $this->redirect(['sales-index','page'=>$post['page']]);
            }catch(HttpException $e){
                $tran->rollBack();
                Yii::$app->getSession()->setFlash('error',$e->getMessage(),true);
            }
        }else{
            $id=$_REQUEST['id'];
            $page  = $_REQUEST['page'];
            if($id){
                $model=new PlatformSummary();
                if(is_array($id)){
                    $id=implode(',',$id);
                }
                return $this->renderAjax('note',['id' =>$id,'model'=>$model,'page'=>$page]);
            }
        }
        return false;
    }

    /**
     * 审核-驳回(数量大于500，金额大于20000)
     */
    public function actionInitDisagree()
    {
        $post=Yii::$app->request->post('PlatformSummary');

        if(!empty($post)){
            $ids=explode(',',$post['id']);
            $tran = Yii::$app->db->beginTransaction();
            try{
                if(count($ids)>1){//批量
                    foreach($ids as $v){
                        if ($post_model->init_level_audit_status==1) {
                            $post_model = $this->findModel($v);
                            $post_model->level_audit_status=4;
                            $post_model->init_level_audit_status=0;
                            $post_model->buyer=Yii::$app->user->identity->username;
                            $post_model->purchase_note=$post['audit_note'];
                            $post_model->purchase_time=date('Y-m-d H:i:s',time());
                            $post_model->save();
                        } else {
                            $tran->rollBack();
                            throw new HttpException(500,$post_model->demand_number . ' 该需求不在审核权限范围内');
                        }
                    }
                }else{
                    $model = $this->findModel($post['id']);
                    if ($model->init_level_audit_status==1) {
                        $model->level_audit_status=4;
                        $model->init_level_audit_status=0;
                        $model->buyer=Yii::$app->user->identity->username;
                        $model->purchase_note=$post['audit_note'];
                        $model->purchase_time=date('Y-m-d H:i:s',time());
                        $model->save();
                    } else {
                        $tran->rollBack();
                        throw new HttpException(500,$model->demand_number . ' 该需求不在审核权限范围内');
                    }
                }
                $tran->commit();
                Yii::$app->getSession()->setFlash('success',"恭喜您操作成功！",true);
                return $this->redirect(['sales-index','page'=>$post['page']]);
            }catch(HttpException $e){
                $tran->rollBack();
                Yii::$app->getSession()->setFlash('error',$e->getMessage(),true);
            }
        }else{
            $id=$_REQUEST['id'];
            $page  = $_REQUEST['page'];
            if($id){
                $model=new PlatformSummary();
                if(is_array($id)){
                    $id=implode(',',$id);
                }
                return $this->renderAjax('note',['id' =>$id,'model'=>$model,'page'=>$page]);
            }
        }
        return false;
    }

    public function actionCheck(){
        $id=$_REQUEST['id'];
        $page  = $_REQUEST['page'];
        if($id){
            $model=new PlatformSummary();
            if(is_array($id)){
                $id=implode(',',$id);
            }
            return $this->renderAjax('check',['id' =>$id,'model'=>$model,'page'=>$page]);
        }
    }
    /**
     * 撤销需求
     */
    public function  actionRevokeDemand()
    {
        $ids    = Yii::$app->request->get('ids');
        if (!$ids) return;
        $map['id']=strpos($ids,',') ? explode(',',$ids):$ids;
        $map['level_audit_status'] = [2, 7];
        $map['is_purchase']        = 1;
        $orders=PlatformSummary::find()->select('id,level_audit_status,sku')->where($map)->all();

        if(!empty($orders))
        {   $rd=0;
            $rs=0;
            foreach ($orders as $v)
            {
                if($v->level_audit_status!=4){
                    $v->level_audit_status=3;
                    $result =$v->save(false);
                    $rs++;
                }else{
                    $rd++;
                    $pname[]=$v->sku;
                }
            }

            if($rd){
                $pname=implode(', ',$pname);
                $msgp="采购驳回的不允许撤销的SKU有：$pname";
            }else{
                $msgp='';
            }

            if(!empty($result))
            {
                Yii::$app->getSession()->setFlash('success',"恭喜你,撤销确认成功: $rs 条! $msgp");
            }else{
                Yii::$app->getSession()->setFlash('success',"恭喜你,撤销确认成功: $rs 条! $msgp");
            }
            return $this->redirect(['sales-index']);
        } else {
            Yii::$app->getSession()->setFlash('error','对不起少年,已经同意或者已是采购的不能再撤销了！');
            return $this->redirect(['sales-index']);
        }

    }

    /**
     * 撤销(仅销售)
     */
    public function  actionCancel()
    {
        $id    = Yii::$app->request->get('id');
        if (!$id) return;
        $map['id']=$id;
        $map['level_audit_status'] = ['2', '7'];
        $map['is_purchase']        = 1;
        $orders=PlatformSummary::find()->select('id,level_audit_status,sku')->where($map)->one();
        if(!empty($orders))
        {
            if($orders->level_audit_status!=4){
                $orders->level_audit_status=3;
                $orders->sale_audit_status=0;
                $result = $orders->save(false);
            }

            if(!empty($result))
            {
                Yii::$app->getSession()->setFlash('success',"恭喜你,撤销需求成功!");
            }else{
                Yii::$app->getSession()->setFlash('error',"对不起,撤销需求失败!");
            }
            return $this->redirect(['sales-index']);
        } else {
            Yii::$app->getSession()->setFlash('error','对不起少年,已经同意或者已是采购的不能再撤销了！');
            return $this->redirect(['sales-index']);
        }

    }

    /**
     * 提交（仅销售 销售审核通过后  销售经理才能看到）
     */
    public function actionSubmit()
    {
        Yii::$app->response->format = 'raw';
        $ids=Yii::$app->request->post('ids');
        $id=Yii::$app->request->get('id');
        $arr= [2, 7];


        if(!empty($id) || !empty($ids)){
            $tran = Yii::$app->db->beginTransaction();
            try{
                $groupId = BaseServices::getGroupByUserName(2);
                if(!empty($ids)){//批量
                    foreach($ids as $id){
                        $model = $this->findModel($id);
                        if(!in_array($model->level_audit_status,$arr)) {
                            continue;
                        }
                        $group_id = $model->group_id;
                        if (!in_array($group_id, $groupId)) {
                            # 需求不属于当前用户所属组
                            $tran->rollBack();
                            $res['msg'] = $model->demand_number . ':只能提交自己组下的需求（没有分组则不能提交）';
                            die(json_encode($res));
                        }
                        
                        if($model && $model->sale_audit_status == 0){
                            $model->sale_audit_status = 1;
                        }
                        $model->level_audit_status = 0;
                        $model->agree_user=Yii::$app->user->identity->username;
                        $model->agree_time=date('Y-m-d H:i:s',time());

                        if($model->save(false)==false && Yii::$app->request->isAjax){
                            $res['status'] = 0;
                            $res['msg'] = '对不起,批量提交失败';
                            die(json_encode($res));
                        }else if($model->save(false)==false){
                            throw new HttpException(500,'需求提交失败！');
                        }
                    }
                }else{
                    $model = $this->findModel($id);
                    $group_id = $model->group_id;
                    if (!in_array($group_id, $groupId)) {
                        # 需求不属于当前用户所属组
                        $tran->rollBack();
                        Yii::$app->getSession()->setFlash('warning', $model->demand_number . ':只能提交自己组下的需求（没有分组则不能提交）',true);
                        return $this->redirect(Yii::$app->request->referrer);
                    }

                    if($model && $model->sale_audit_status == 0){
                       $model->sale_audit_status = 1;
                    }else{
                        throw new HttpException(500,'需求已提交,不需重复提交！');
                    }
                    $model->level_audit_status = 0;
                    $model->agree_user=Yii::$app->user->identity->username;
                    $model->agree_time=date('Y-m-d H:i:s',time());
                    if($model->save(false) == false){
                        throw new HttpException(500,'需求提交失败！');
                    };
                }
                $tran->commit();
                if (!empty($ids) && Yii::$app->request->isAjax){
                    $res['msg'] = '恭喜你,批量提交成功';
                    die(json_encode($res));
                }else{
                    Yii::$app->getSession()->setFlash('success',"操作成功！",true);
                }
            }catch(HttpException $e){
                $tran->rollBack();
                Yii::$app->getSession()->setFlash('error',$e->getMessage(),true);
            }
        }else{
            Yii::$app->getSession()->setFlash('error',"没有数据ID,操作失败！",true);
        }
        return $this->redirect(Yii::$app->request->referrer);
    }


    /**
     * 创建采购单
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionCreatePurchaseOrder()
    {


        $ids                       = Yii::$app->request->get('ids');
        $map['id']                 = strpos($ids, ',') ? explode(',', $ids) : $ids;
        $map['level_audit_status'] = 1;
        $map['is_purchase']        = 1;
        $map['purchase_type']      = 3;
        $orders                    = PlatformSummary::find()->where($map)->asArray()->groupBy('sku,purchase_warehouse')->all();


        if(isset($orders) && !empty($orders))
        {

            foreach($orders as $av)
            {
                $sdata[]=$av['sku'];
                $wdata[]=Warehouse::find()->select('id,warehouse_code,warehouse_name')->where(['use_status'=>1,'warehouse_code'=>$av['purchase_warehouse']])->one()->warehouse_name;
            }

            $whouse  = array_unique($wdata);
            $sku     = array_unique($sdata);

            if(count($whouse)>1 && count($sku)==1)
            {
                $whouse=implode(', ',$whouse);
                $sku=implode(',',$sku);
                Yii::$app->getSession()->setFlash('error', "相同的SKU( $sku ), 有多个不同仓库( 分别是：$whouse ), 不能建单！");
                return $this->redirect(['index']);
            }

            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $demand_array =[];
                foreach ($orders as $k => $v)
                {


                    $orderdata['purdesc']['warehouse_code']    = $v['purchase_warehouse'];
                    $orderdata['purdesc']['is_transit']        = $v['is_transit'] == 1 ? 0 : 1;
                    $orderdata['purdesc']['transit_warehouse'] = $v['transit_warehouse'];
                    $orderdata['purdesc']['supplier_code']     = $v['supplier_code'];
                    $orderdata['purdesc']['is_expedited']      = 1;
                    $orderdata['purdesc']['purchase_type']     = $v['purchase_type'];

                    $pur_number                                = PurchaseOrder::Savepurdata($orderdata);
                    $map['sku']                                 = $v['sku'];
                    $map['is_purchase']               = 1;
                    $map['level_audit_status']        = 1;
                    $map['purchase_type']             = 3;
                    $map['purchase_warehouse']        = $v['purchase_warehouse'];
                    //$map['transit_warehouse']         = $purdesc['transit_warehouse'];
                    $items                            = PlatformSummary::find()->where($map)->asArray()->all();
                   /* foreach($purdesc['items'] as $k=>$v)
                    {
                        $demand_array [] = $v['demand_number'];

                    }*/
                    PlatformSummary::Updates($demand_array);
                    PurchaseDemand::saveOne($pur_number,$items);
                    PurchaseOrder::OrderItems($pur_number, $items,3);

                }
                $transaction->commit();
                Yii::$app->getSession()->setFlash('success', '恭喜你,手动创建采购单成功');
                return $this->redirect(['fba-purchase-order/index']);
            } catch (Exception $e) {
                Yii::$app->getSession()->setFlash('error', '恭喜你,手动创建采购单没有成功');
                $transaction->rollBack();
            }
        } else {
            Yii::$app->getSession()->setFlash('warning', '采购驳回的需求，不允许建单！');
            return $this->redirect(['index']);
        }


    }

    //FBA创建采购单
    public function actionCreateFbaPurchaseOrder()
    {
        $searchModel = new PlatformSummarySearch();
        $params = Yii::$app->session->get('FBA_SUMMARY_SEARCH');
        $query = $searchModel->search($params,true);
        $orders= $query
            ->andwhere(['is_purchase'=>1])
            ->andWhere(['level_audit_status'=>1])
            ->andWhere(['purchase_type'=>3])
            ->all();
        $response = PurchaseOrder::createFbaPurOrder2($orders);
        return json_encode($response);
    }

    // FBA一键创建采购单-根据勾选的采购需求生成
    public function actionCreateFbaPurchaseOrder2()
    {
        $request = Yii::$app->request;
        if($request->isAjax) {
            $ids                       = Yii::$app->request->getBodyParam('ids');
            $map['id']                 = $ids;
            $map['level_audit_status'] = 1;
            $map['is_purchase']        = 1;
            $map['purchase_type']      = 3;
            $orders                    = PlatformSummary::find()->where($map)->all();
            $response = PurchaseOrder::createFbaPurOrder2($orders);
            return json_encode($response);
        } else {
            throw new HttpException(403,'改页禁止访问');
            exit;
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

    /**
     * @return 销售页面
     */
    public function actionSalesIndex()
    {
        $searchModel  = new PlatformSummarySearch();
        $dataProvider = $searchModel->search3(Yii::$app->request->queryParams);
        return $this->render('sales-index',[
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 采购驳回
     */
    public function actionPurchaseDisagree($id,$page)
    {
        $model = $this->findModel($id);
        if(Yii::$app->request->isPost)
        {
            $model->level_audit_status = 4;
            $model->buyer              = Yii::$app->user->identity->username;
            $model->is_purchase        = 1;
            $model->purchase_note      = Yii::$app->request->post()['PlatformSummary']['purchase_note'];
            $model->purchase_time      = date('Y-m-d H:i:s', time());
            $model->save(false);
            Yii::$app->getSession()->setFlash('success',"恭喜您操作成功！",true);
            return $this->redirect(['index','page'=>$page]);
        } else{

            return $this->renderAjax('pnote',['model' =>$model]);
        }
    }

    /**
     * 采购需求导入
     * @return string|\yii\web\Response
     */
    public function actionPurchaseSumImport(){
        $fba_warehouse_list =  ['FBA_SZ_AA'=>'东莞仓FBA虚拟仓','TS'=>'退税仓'];
        $model = new PlatformSummary();
        if (Yii::$app->request->isPost && $_FILES)
        {
            $fba_warehouse_list = array_flip($fba_warehouse_list);// 键值交换
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
                Yii::$app->getSession()->setFlash('error',"文件上传失败",true);
                return $this->redirect(['index']);
            }
            $file        = fopen($data, 'r');
            $line_number = 0;
            $Name = [];
            while ($datas = fgetcsv($file)) {
                if ($line_number == 0) { //跳过表头
                    $line_number++;
                    continue;
                }
                $now_sku = trim($datas[0]);
                $product=Product::find()->where(['sku'=>$now_sku])->one();
                if(!empty($product)&&trim($datas[0])!==$product->sku){
                    Yii::$app->getSession()->setFlash('warning','导入的sku:' . $now_sku.'与系统sku：'.$product->sku.'不符',false);
                    return $this->redirect(['sales-index']);
                }
                if(empty($product)||!in_array($product->product_status,[4,18,3,20])||$product->product_is_multi==2||$product->product_type==2){
                    Yii::$app->getSession()->setFlash('warning','产品不存在或者产品包含如下属性(状态:非在售，非试卖在售，非预上线,非预上线拍摄中,捆绑sku,主sku)：' . $now_sku,false);
                    return $this->redirect(['sales-index']);
                }

                //预上线拍摄中的状态，在创建销售需求的时候，判断采购有没有记录，如果有，则不能创建需求
                if(in_array($product->product_status,[20])){
                    $is_exist = PurchaseOrderItems::find()
                        ->alias('items')
                        ->leftJoin(PurchaseOrder::tableName().' order','items.pur_number=order.pur_number')
                        ->where(['in','order.purchas_status',[1,2,3,5,6,7,8,9]])
                        ->andWhere(['items.sku'=>$now_sku])
                        ->count();
                    if($is_exist > 0){
                        Yii::$app->getSession()->setFlash('warning','产品为预上线拍摄中的状态,有对应的采购记录,不能创建需求：' . $now_sku,false);
                        return $this->redirect(['sales-index']);
                    }
                }
                $group = SupervisorGroupBind::find()->where(['supervisor_name'=>mb_convert_encoding(trim($datas[6]),'utf-8','gbk')])->asArray()->all();
                $userGroup = ArrayHelper::getColumn($group,'group_id');
                $user_group = mb_convert_encoding(trim($datas[4]),'utf-8','gbk');
                if(empty($userGroup)||!in_array($user_group,$userGroup)){
                    Yii::$app->getSession()->setFlash('warning','用户组不对：' . $now_sku,false);
                    return $this->redirect(['sales-index']);
                }
                if(empty($user_group)){
                    Yii::$app->getSession()->setFlash('warning','采购系统没有该产品（sku）：' . $now_sku,false);
                    return $this->redirect(['sales-index']);
                }

                // 读取 填写的数据
                $num = count($datas);
                for ($c = 0; $c < $num; $c++) {
                    $Name[$line_number][] = mb_convert_encoding(trim($datas[$c]),'utf-8','gbk');
                }

                if(!empty($Name[$line_number][1]) && !is_numeric($Name[$line_number][1])){
                    $Name[$line_number][1] =strtoupper($Name[$line_number][1]);
                }


                // 获取SKU的 默认供应商编码
                $supplier_code = ProductProvider::find()->select('supplier_code')->where(['sku'=>$now_sku,'is_supplier'=>1])->scalar();

                // 判断供应商是否退税，以及税点
                $is_back_tax = $pur_ticketed_point = 0;
                $supplier_model = \app\models\ProductProvider::find()->where(['sku'=>$now_sku,'is_supplier'=>1])->one();
                if ($supplier_model) {
                    $supplier_info = SupplierQuotes::find()->select('is_back_tax,pur_ticketed_point')->where(['id'=>$supplier_model->quotes_id])->one();
                    $is_back_tax = $supplier_info['is_back_tax'] == 1 ? 1 : 2;
                    $pur_ticketed_point = $supplier_info['pur_ticketed_point'];
                }
                // 采购仓库
                $purchase_warehouse = '';
                if(in_array($Name[$line_number][3],array_keys($fba_warehouse_list))){
                    $purchase_warehouse = $fba_warehouse_list[$Name[$line_number][3]];
                }elseif($Name[$line_number][3]){
                    Yii::$app->getSession()->setFlash('error','采购仓库不存在，只能是东莞仓FBA虚拟仓或退税仓，SKU：'.$now_sku);
                    return $this->redirect(['sales-index']);
                }
                if(empty($purchase_warehouse)){// 默认  东莞仓FBA虚拟仓 非退税
                    $purchase_warehouse = 'FBA_SZ_AA';
                }
                // 是否退税 必须与仓库是否退税一致
                if ( $purchase_warehouse == 'TS' && $is_back_tax != 1 ) {
                    Yii::$app->getSession()->setFlash('error','产品是否退税发生了变化，请重新创建，SKU：'.$now_sku);
                    return $this->redirect(['sales-index']);
                }
                $Name[$line_number][3] = $purchase_warehouse;// 采购仓库

                $pur_ticketed_point = $purchase_warehouse == 'TS' ? $pur_ticketed_point : 0;// 税点
                if ($purchase_warehouse == 'TS') {
                    //判断供应商的付款方式是否为银行转账
                    $payment_method = Supplier::find()->select('payment_method')->where(['supplier_code'=>$supplier_code])->scalar();
                    if ($payment_method != 3) {
                        Yii::$app->getSession()->setFlash('error','当前供应商支付方式不是【银行卡转账】，不能创建【退税】需求，SKU：'.$now_sku);
                        return $this->redirect(['sales-index']);
                    }
                }



                //销售名称，销售账号
                $sales = mb_convert_encoding($datas[6],'utf-8','gbk');
                $xiaoshou_zhanghao = mb_convert_encoding(trim($datas[7]),'utf-8','gbk');

                if (!empty($sales) && !empty($xiaoshou_zhanghao)) {
                    $zhangHaos = BaseServices::getXiaoshouZhanghao($sales);

                    if (!empty($zhangHaos)) {
                        $is_zhangHao = in_array($xiaoshou_zhanghao, $zhangHaos);
                        if (!empty($is_zhangHao)) {
                            # 合法
                        } else {
                            # 不合法：销售对应的账号有误
                            Yii::$app->getSession()->setFlash('warning','销售对应的账号有误：' . $now_sku,false);
                            return $this->redirect(['sales-index']);
                        }
                    } else {
                        # 不合法：没有改销售对应的账号
                        Yii::$app->getSession()->setFlash('warning','没有改销售对应的账号：' . $now_sku,false);
                        return $this->redirect(['sales-index']);
                    }
                } else {
                    # 不合法：销售名称不能为空
                    Yii::$app->getSession()->setFlash('warning','销售名称或账号不能为空：' . $now_sku,false);
                    return $this->redirect(['sales-index']);
                }
                $Name[$line_number][7] = $xiaoshou_zhanghao;



                $pmodel = Product::find()->joinWith(['desc','cat'])->where(['pur_product.sku'=> $now_sku ])->asArray()->one();
                $Name[$line_number][] = $pmodel['product_category_id'];
                $Name[$line_number][] = !empty($pmodel['desc']['title']) ? $pmodel['desc']['title'] : '';
                $Name[$line_number][] = 3;// 采购类型 3.FBA
                $Name[$line_number][] = 1;//是否中转，默认直发：1
                $Name[$line_number][] = CommonServices::getNumber('RD');
                $Name[$line_number][] = Yii::$app->user->identity->username;
                $Name[$line_number][] = date('Y-m-d H:i:s',time());
                $Name[$line_number][] = !empty($supplier_code) ? $supplier_code: '';
                $Name[$line_number][] = $pur_ticketed_point;
                $Name[$line_number][] = $is_back_tax;
                $Name[$line_number][] = 7;
                $line_number++;
            }
            if(empty($Name))
            {
                Yii::$app->getSession()->setFlash('warning','导入有sku不存在系统中，请联系管理员解决',false);
                return $this->redirect(['sales-index']);
            }

/*            echo '<pre>';
            var_dump($Name);*/

            //数据一次性入库
            $error_message='导入失败了';
            $transaction=\Yii::$app->db->beginTransaction();
            try{
                $statu= Yii::$app->db->createCommand()->batchInsert(PlatformSummary::tableName(), ['sku', 'platform_number', 'purchase_quantity', 'purchase_warehouse','group_id','sales_note','sales','xiaoshou_zhanghao', 'product_category','product_name',
                    'purchase_type', 'is_transit', 'demand_number', 'create_id', 'create_time','supplier_code','pur_ticketed_point','is_back_tax', 'level_audit_status'], $Name)->execute();

                $transaction->commit();
            }catch (Exception $e){
                $statu =false;
                $error_message=$e->getMessage();
                $transaction->rollBack();
            }

            fclose($file);


           // die;
            if ($statu) {
                Yii::$app->getSession()->setFlash('success',"恭喜你，导入成功!",true);
                return $this->redirect(['sales-index']);
            } else {
                Yii::$app->getSession()->setFlash('warning',$error_message,true);
                return $this->redirect(['sales-index']);
            }

        } else {
            return $this->renderAjax('purchase-sum-import', ['model' => $model]);
        }
    }


    //FBA订单追踪
    public function actionSummaryDetail(){
        set_time_limit(60);

        $searchModel  = new PlatformSummarySearch();
        $dataProvider = $searchModel->search4(Yii::$app->request->queryParams);
        return $this->render('detail',[
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    //FBA详情查询
    public function actionGetPlatformDetail(){
        if(Yii::$app->request->isAjax && Yii::$app->request->isGet){
            $id    = Yii::$app->request->getQueryParam('id');
            $model = PlatformSummary::find()->where(['id'=>$id])->one();
            return $this->renderAjax('platform-detail', ['model' => $model]);
        }
    }

    //根据组别获取销售名
    public function actionGetSales(){
        if(Yii::$app->request->isAjax){
            $groupId = Yii::$app->request->getQueryParam('group');
            $sales = SupervisorGroupBind::find()->where(['group_id'=>$groupId])->asArray()->all();
            $select = '';
            if(!empty($sales)){
                foreach($sales as $sale){
                   $select .= Html::tag('option',Html::encode($sale['supervisor_name']),['value'=>$sale['supervisor_name']]);
                }
            }
            echo json_encode(array('sales'=>$select));
        }
    }
    /**
     * 根据销售名称获取销售账号
     * @return [type] [description]
     */
    public function actionGetXiaoshouZhanghao(){
        if(Yii::$app->request->isAjax){
            $sales = Yii::$app->request->getQueryParam('sales');
            $xiaoshou_zhanghao = BaseServices::getXiaoshouZhanghao($sales);
            $select = '';
            if(!empty($xiaoshou_zhanghao)){
                foreach($xiaoshou_zhanghao as $v){
                   $select .= Html::tag('option',Html::encode($v),['value'=>$v]);
                }
            }
            echo json_encode(array('xiaoshou_zhanghao'=>$select));
        }
    }

    public function actionSaleStat(){
        return $this->render('sale-stat');
    }

    public function actionGetData(){
        if(Yii::$app->request->isAjax){
            $type  = Yii::$app->request->getBodyParam('type');
            $group = Yii::$app->request->getBodyParam('group');
            $start = Yii::$app->request->getBodyParam('start');
            $end   = Yii::$app->request->getBodyParam('end');
            $model = new PlatformSummarySearch();
            $data = $model->search5($type,$group,$start,$end);
            echo json_encode($data);
        }
    }

    public function actionList(){
        $searchModel  = new PlatformSummarySearch();
        $dataProvider = $searchModel->search6(Yii::$app->request->queryParams);
        return $this->render('list',[
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
   public function actionCount(){
	   	$searchModel  = new PlatformSummarySearch();
	   	$data = $searchModel->search9(Yii::$app->request->queryParams);
	   	return $this->render('count',[
	   			'searchModel' => $searchModel,
	   			'data' => $data,
	   	]);
   }

   public static function actionGetPrice($supplier_code,$sku,$purchase_quantity){
        $price = ProductProvider::find()
            ->select('sq.supplierprice')
            ->alias('t')
            ->leftJoin(SupplierQuotes::tableName().' sq','sq.id=t.quotes_id')
            ->where(['t.is_supplier'=>1,'t.sku'=>$sku])
            ->scalar();
        return $price?bcmul($price,$purchase_quantity,3):0;
   }

    /**
     * 备注   FBA采购需求汇总-新增采购备注操作
     * @return string
     */
    public function actionUpdateSuggestNote($data=null)
    {
        if (empty($data)) {
            $data = Yii::$app->request->get();
        }
        return PurchaseSuggestNote::updateFbaSuggestNote($data,$purchase_type=1);
    }


    /**fba销售需求同意后优先推送
     * @throws \yii\base\ExitException
     */
    public function actionPushPriority(){
        if(Yii::$app->request->isAjax){
            $ids = Yii::$app->request->getQueryParam('ids','');
            if(empty($ids)){
                echo json_encode(['status'=>'error','message'=>'缺少必要参数']);
                Yii::$app->end();
            }
            $response = PlatformSummary::pushSummaryPriority($ids);
            echo json_encode($response);
            Yii::$app->end();
        }
    }

    public function actionSummaryExport(){
        $searchModel  = new PlatformSummarySearch();
        $searchParams = Yii::$app->session->get('FBA-plat_summary_detail');
        $id = Yii::$app->request->get('ids');
        $id = strpos($id,',')?explode(',',$id):$id;
        $query = $searchModel->search4($searchParams,$id,true);
        $model = $query->all();

        $table = [
            'SKU',
            '产品名称',
            '单价',
            '需求单号',
            '采购单号',
            '采购员',
            '结算方式',
            '是否含税',
            '供应商名称',
            '销售组别',
            '销售',
            '仓库',
            '需求数量',
            '采购数量',
            '取消数量',
            '收货数量',
            '上架数量',
            '需求状态',
            '订单状态',
            '付款状态',
            '需求审核时间',
            '订单审核时间',
            '请款时间',
            '付款时间',
            '签收时间',
            '收货时间',
            '入库时间',
            '首次预计到货时间',
            '预计到货时间',
            '权均交期时间',
            '权均交期天数',
            '预计到货是否超时(差额天数)',
            '权均交期是否超时(差额天数)',
        ];

        $table_head = [];
        $drawArray = ['1'=>'不含税','2'=>'含税'];
        $demand_status_list = \app\services\PlatformSummaryServices::getLevelAuditStatus();
        foreach($model as $k=>$v)
        {
            $pur_number = isset($v->purOrder->pur_number)?$v->purOrder->pur_number:'';
            if($pur_number){
                $model_arrival_record = ArrivalRecord::find()->where(['purchase_order_no' => $pur_number])->orderBy('id desc')->one();
                $delivery_time       = isset($model_arrival_record->delivery_time)?$model_arrival_record->delivery_time:''; //收货
                $receipt    = UebExpressReceipt::findOne(['relation_order_no' => $pur_number]); //签收
                $results    = WarehouseResults::getResults($pur_number,$v->sku,'create_time,instock_user,instock_date'); //入库 
            }
            $avg = isset($v->fbaAvgArrival->avg_delivery_time) ? $v->fbaAvgArrival->avg_delivery_time :0; //权均交期天数
            $audit_time = !empty($v->purOrder) ? $v->purOrder->audit_time : ''; //权均交期时间
            $audit_time_format = empty($audit_time) ? '' : date('Y-m-d H:i:s',strtotime($audit_time)+round($avg,2));
            $purchase_status = isset($v->purOrder)?$v->purOrder->purchas_status:'';
            $int_3_days = 3 * 24 * 60 * 60;
            $estimated_time = PurchaseEstimatedTime::getEstimatedTime($v->sku,$pur_number);// 首次预计到货时间
            $int_estimated_time = strtotime($estimated_time);
            // 最后一次入库时间
            $last_instock_date  = !empty($pur_number)?WarehouseResults::find()->select('instock_date')->where(['pur_number' => $pur_number,'sku'=>$v->sku])->orderBy('id desc')->scalar() : '';
            $int_last_instock_date = $last_instock_date?strtotime($last_instock_date):time();// 入库时间为空 说明还没入过库
            $int_now_time = time();// 当前时间
            $is_timeout = '';
            if(in_array($purchase_status,[4,6,9,10])){// 已完结(预计到货时间与最后一次入库时间做对比)
                $is_timeout_diff = $int_estimated_time - $int_last_instock_date;//预计到货时间 - 最后一次入库时间
                $is_timeout_diff = abs(sprintf("%.1f",$is_timeout_diff/86400));
                if($int_estimated_time <= $int_last_instock_date){
                    $is_timeout = '超时';
                }elseif( $int_estimated_time > $int_last_instock_date + $int_3_days){
                    $is_timeout = '超时';
                }else{
                    $is_timeout = '未超时';
                }
            }else{// 未完结(预计到货时间与当前时间作对比)
                $is_timeout_diff = $int_estimated_time - $int_now_time;//预计到货时间 - 当前时间
                $is_timeout_diff = abs(sprintf("%.1f",$is_timeout_diff/86400));
                if($int_estimated_time <= $int_now_time){
                    $is_timeout = '超时';
                }elseif( $int_estimated_time > $int_now_time AND $int_estimated_time < $int_now_time + $int_3_days){
                    $is_timeout = '超时';
                }else{
                    $is_timeout = '未超时';
                }
            }

            $int_avg_delivery_date = (strtotime($audit_time)+$avg);// 权均交期
            $int_avg_delivery_diff = $int_avg_delivery_date - $int_last_instock_date;// 权均交期 - 最后一次入库时间
            $int_avg_delivery_diff_days = abs(sprintf("%.1f",$int_avg_delivery_diff/86400));

            if(empty($audit_time)){// 采购单未审核不计算超时
                $int_avg_delivery_diff_days = '采购单未审核';
            }elseif($int_avg_delivery_diff > 0){
                $instock = '未超时';
                $int_avg_delivery_diff_days = $int_avg_delivery_diff_days;
            }else{
                $instock = '超时';
                $int_avg_delivery_diff_days = $int_avg_delivery_diff_days;
            }

            $table_head[$k][]=$v->sku;
            $table_head[$k][]=!empty($v->desc) ? $v->desc->title : '';
            $table_head[$k][]=(!empty($v->purOrderItem) ? !empty($v->purOrderItem->price) ?  $v->purOrderItem->price : '' : '');
            $table_head[$k][]=$v->demand_number;
            $table_head[$k][]=(!empty($v->purOrder) ? $v->purOrder->pur_number : '');
            $table_head[$k][]=(\app\models\PurchaseCategoryBind::getBuyer($v->defaultSupplierLine->first_product_line))?:'';
            $table_head[$k][]=(!empty($v->purOrder)?SupplierServices::getSettlementMethod($v->purOrder->account_type):'');
            $table_head[$k][]=(!empty($v->purOrder) AND isset($drawArray[$v->purOrder->is_drawback]))?$drawArray[$v->purOrder->is_drawback]:'' ;
            $table_head[$k][]=(!empty($v->purOrder->supplier_code) ? SupplierServices::getSupplierName($v->purOrder->supplier_code) : '');
            $table_head[$k][]=(BaseServices::getAmazonGroupName($v->group_id)) ? :'';
            $table_head[$k][]=$v->sales;
            $table_head[$k][]=!empty($v->purchase_warehouse) ? BaseServices::getWarehouseCode($v->purchase_warehouse) : '';
            $table_head[$k][]=$v->purchase_quantity;
            $table_head[$k][]=!empty($v->purOrderItem) ? (!empty($v->purOrderItem->ctq) ?  $v->purOrderItem->ctq : 0) : 0;;
            $table_head[$k][]=PurchaseOrderCancelSub::getCancelCtq($v->purOrder->pur_number,$v->sku);
            $table_head[$k][]=intval(isset($v->purOrderItem->rqy)?$v->purOrderItem->rqy:0);
            $table_head[$k][]=intval(isset($v->purOrderItem->cty)?$v->purOrderItem->cty:0);
            $table_head[$k][]=isset($demand_status_list[$v->level_audit_status])?$demand_status_list[$v->level_audit_status]:'状态异常';
            $table_head[$k][]=!empty($v->purOrder) ? (PurchaseOrderServices::getPurchaseStatusText($v->purOrder->purchas_status)) : '' ;
            $table_head[$k][]=(!empty($v->purOrder)&&!empty($v->purOrder->pay_status)?PurchaseOrderServices::getPayStatus($v->purOrder->pay_status):'' );
            $table_head[$k][]=empty($v->agree_time)?'':$this->format_date($v->agree_time);
            $table_head[$k][]=$audit_time;
            $table_head[$k][]=!empty($v->pay) ? $this->format_date(date('Y-m-d H:i:s',strtotime($v->pay->application_time))) : '';
            $table_head[$k][]=!empty($v->pay) ? $this->format_date($v->pay->payer_time) : '';
            $table_head[$k][]=$this->format_date(isset($receipt->add_time)?$receipt->add_time:'');
            $table_head[$k][]=$this->format_date(isset($delivery_time)?$delivery_time:'');
            $table_head[$k][]=$this->format_date(isset($results->instock_date)?$results->instock_date:'');
            $table_head[$k][]=$this->format_date(!empty($estimated_time)? ($estimated_time): '');
            $table_head[$k][]=$this->format_date((!empty($v->purOrder) AND !empty($v->purOrder->date_eta))? ($v->purOrder->date_eta):($estimated_time));
            $table_head[$k][]=$this->format_date($audit_time_format);
            $table_head[$k][]=($avg === null)?"":(($avg>0)?sprintf('%.2f',$avg/86400):0);
            $table_head[$k][]=$is_timeout.$is_timeout_diff;
            $table_head[$k][]=$instock.$int_avg_delivery_diff_days;
        }

        theCsv::export([
            'header' =>$table,
            'data' => $table_head,
        ]);
    }

    // 格式化输出时间
    public function format_date($date){
        $date = trim($date);
        if(is_string($date)){
            if(strlen($date) == 10) $date = $date.' 00:00:00';
            return $date;
            return substr($date,0,10);
        }elseif(is_numeric($date)){
            return date('Y-m-d H:i:s',$date);
        }
        return '';
    }

    /**
     * FBA 需求跟踪 页面 采购数量、请款、付款、入库、收货详情
     * @return string
     */
    public function actionShowDetail(){
        $data           = Yii::$app->request->get();
        $sku_number     = $data['sku_number'];
        $sku_number_arr = explode('&',$sku_number);
        $sku            = $sku_number_arr[0];
        $demand_number  = $sku_number_arr[1];
        $pur_number     = $sku_number_arr[2];
        $show_type      = strtoupper($data['show_type']);

        switch ($show_type){
            case 'CGSL':// 采购数量  当前采购单下 所有关联的当前SKU的需求 列表
                $header = ['demand_number' => '需求单号','ctq' => '需求数量'];
                $query = (new Query())
                    ->select('p_p_s.demand_number,p_p_s.purchase_quantity,p_p_s.sku,p_p_i.ctq')
                    ->from('pur_platform_summary as p_p_s')
                    ->innerJoin('pur_purchase_demand as p_p_d','p_p_d.demand_number=p_p_s.demand_number')
                    ->leftJoin('pur_purchase_order_items as p_p_i','p_p_i.pur_number=p_p_d.pur_number AND p_p_i.sku=p_p_s.sku')
                    ->where("p_p_d.pur_number=:pur_number",[':pur_number' => $pur_number])
                    ->andWhere("p_p_s.demand_number=:demand_number",[':demand_number' => $demand_number])
                    ->andWhere("p_p_s.sku=:sku",[':sku' => $sku])
                    ->andWhere("p_p_s.level_audit_status=1")
                    ->andWhere("p_p_s.purchase_type=3");

                $results = $query->all();
                break;

            case 'QKSJ':
                $header = ['pur_number' => '订单号','requisition_number' => '请款单号','price_list' => '金额','application_time' => '请款时间','payer_time' => '付款时间'];
                $results = PurchaseOrderPaySearch::find()
                    ->select('*')
                    ->where("pur_number=:pur_number",[':pur_number' => $pur_number])
                    ->all();

                $results_tmp = [];
                foreach($results as $value){
                    $now_data = [];
                    $now_data['pur_number']         = $value['pur_number'];
                    $now_data['requisition_number'] = $value['requisition_number'];

                    $model_pay = PurchaseOrderPaySearch::find()->where(['requisition_number' => $value['requisition_number']])->one();

                    $now_data['price_list']         = PurchaseOrderPay::getPrice($model_pay,true);
                    $now_data['application_time']   = $value['application_time'];
                    $now_data['payer_time']         = $value['payer_time'];

                    $results_tmp[] = $now_data;
                }
                $results = $results_tmp;
                break;

            case 'FKSJ':
                $header = ['pur_number' => '订单号','requisition_number' => '请款单号','price_list' => '金额','application_time' => '请款时间','payer_time' => '付款时间'];
                $results = PurchaseOrderPaySearch::find()
                    ->select('*')
                    ->where("pur_number=:pur_number",[':pur_number' => $pur_number])
                    ->andWhere('pay_status=5')
                    ->all();

                $results_tmp = [];
                foreach($results as $value){
                    $now_data = [];
                    $now_data['pur_number']         = $value['pur_number'];
                    $now_data['requisition_number'] = $value['requisition_number'];

                    $model_pay = PurchaseOrderPaySearch::find()->where(['requisition_number' => $value['requisition_number']])->one();

                    $now_data['price_list']         = PurchaseOrderPay::getPrice($model_pay,true);
                    $now_data['application_time']   = $value['application_time'];
                    $now_data['payer_time']         = $value['payer_time'];

                    $results_tmp[] = $now_data;
                }
                $results = $results_tmp;
                break;

            case 'SHSJ':
                $header = ['pur_number' => '订单号','sku' => 'SKU','express_no' => '收货单号','delivery_qty' => '数量','delivery_user' => '收货人员','delivery_time' => '收货时间'];

                $now_map = [];
                $now_map['pur_purchase_order.pur_number'] = $pur_number;
                $now_model  = PurchaseOrder::find()->joinWith(['purchaseOrderItems','platformSummary'])->where($now_map)->one();

                $results = [];
                if(isset($now_model->arrival)){
                    foreach ($now_model->arrival as $v) {
                        if($v->sku != $sku ) continue;
                        $now_data                   = [];
                        $now_data['pur_number']     = $v->purchase_order_no;
                        $now_data['sku']            = $v->sku;
                        $now_data['express_no']     = $v->express_no;
                        $now_data['delivery_qty']   = $v->delivery_qty;
                        $now_data['delivery_user']  = $v->delivery_user;
                        $now_data['delivery_time']  = $v->delivery_time;

                        $results[] = $now_data;
                    }
                }

                break;
            case 'RKSJ':
                $header = ['pur_number' => '订单号','sku' => 'SKU','receipt_number' => '入库单号','instock_qty_count' => '数量','instock_user' => '入库人员','instock_date' => '入库时间'];
                $results = WarehouseResults::find()
                    ->select('*')
                    ->where("pur_number=:pur_number",[':pur_number' => $pur_number])
                    ->andWhere("sku=:sku",[':sku' => $sku])
                    ->all();

                break;
            default :
                echo '请求类型错误';
                break;
        }

        if(isset($header)){
            return $this->renderAjax('show-detail', [
                'header' => $header,
                'results' => $results
            ]);
        }

    }

    /**
     * 查询缓存数据表最大ID
     * @return int
     */
    public static function getMaxId(){

        $connection = Yii::$app->db;
        $command    = $connection->createCommand("SELECT MAX(id) AS max_id FROM pur_fab_purchase_order_trace");
        $result     = $command->queryColumn();
        $max_id     = !empty($result[0])?$result[0]:0;

        return $max_id;
    }

    /**
     * @desc 生成统计数据 并缓存到表中[FBA订单跟踪统计：权均交期是否超时、预计到货是否超时、是否新品]
     * http://caigou.yibainetwork.com/platform-summary/create-cache-data  Used:34s
     */
    public function actionCreateCacheData(){
        set_time_limit(0);

        $start_time = time();
        $connection = Yii::$app->db;
        $command    = $connection->createCommand("TRUNCATE `pur_fab_purchase_order_trace`");
        $command->execute();


        /***************************************************************************************************************/
        $max_id = self::getMaxId();
        //预计到货是否超时
        // 查询未超时
        $subQuery_Timeout_1 = (new Query())->select("p_p_o.pur_number,p_o_i.sku")
            ->from("pur_purchase_order AS p_p_o")
            ->leftJoin("pur_purchase_order_items AS p_o_i","p_o_i.pur_number=p_p_o.pur_number")
            ->leftJoin("pur_purchase_estimated_time AS p_e_t","p_o_i.pur_number=p_e_t.pur_number AND p_o_i.sku=p_e_t.sku")
            ->where("p_p_o.purchas_status NOT IN(4,6,9,10)")
            ->andWhere("p_p_o.purchase_type=3")
            ->andWhere(['or',"p_p_o.audit_time IS NULL","UNIX_TIMESTAMP(p_e_t.estimated_time)-UNIX_TIMESTAMP(NOW())>259200"]);

        $subQuery_Timeout_2 = (new Query())->select("p_p_o.pur_number,p_o_i.sku")
            ->from("pur_purchase_order AS p_p_o")
            ->leftJoin("pur_purchase_order_items AS p_o_i","p_o_i.pur_number=p_p_o.pur_number")
            ->leftJoin("pur_purchase_estimated_time AS p_e_t","p_o_i.pur_number=p_e_t.pur_number AND p_o_i.sku=p_e_t.sku")
            ->leftJoin("pur_warehouse_results AS p_w_r","p_w_r.pur_number=p_p_o.pur_number AND p_w_r.sku=p_e_t.sku")
            ->where("p_p_o.purchas_status IN(4,6,9,10)")
            ->andWhere("p_p_o.purchase_type=3")
            ->andWhere(['or',"p_p_o.audit_time IS NULL","UNIX_TIMESTAMP(p_e_t.estimated_time)-UNIX_TIMESTAMP(p_w_r.instock_date) BETWEEN 0 AND 259200"]);

        $subQuery_Timeout_1->union($subQuery_Timeout_2,true);

        $subSql     = (new Query())->select("pur_number,sku")->from(['tmp' => $subQuery_Timeout_1]);
        $subSql     = $subSql->groupBy('pur_number,sku');
        $select_sql = $subSql->createCommand()->getRawSql();

        $insert_sql = "INSERT INTO pur_fab_purchase_order_trace (`pur_number`,`sku`) $select_sql ";

        $connection = Yii::$app->db;
        $command    = $connection->createCommand($insert_sql);
        $result     = $command->execute();
        echo "<span style='color: green'>生成数据[预计到货是否超时]：$result 条</span><br/><br/>";

        $connection = Yii::$app->db;
        $command    = $connection->createCommand("UPDATE pur_fab_purchase_order_trace SET `data_type`=1 WHERE id>'$max_id'");
        $result     = $command->execute();
        /**************************************************************************************************************/



        /**************************************************************************************************************/
        $max_id = self::getMaxId();
        // 权均交期是否超时
        // 查询未超时
        // 采购单未审核
        $subQuery_Avg_Timeout_1 = (new Query())->select("p_p_o.pur_number,p_o_i.sku")
            ->from("pur_purchase_order AS p_p_o")
            ->innerJoin("pur_purchase_order_items AS p_o_i","p_o_i.pur_number=p_p_o.pur_number")
            ->leftJoin("pur_warehouse_results AS p_w_r","p_w_r.pur_number=p_o_i.pur_number AND p_w_r.sku=p_o_i.sku")
            ->leftJoin("pur_fba_avg_deliery_time AS p_f_a","p_f_a.sku=p_o_i.sku")
            ->where("p_p_o.purchase_type=3")
            ->andWhere("p_p_o.audit_time IS NULL");

        // 采购已审核 但是未超时
        $subQuery_Avg_Timeout_2 = (new Query())->select("p_p_o.pur_number,p_o_i.sku")
            ->from("pur_purchase_order AS p_p_o")
            ->innerJoin("pur_purchase_order_items AS p_o_i","p_o_i.pur_number=p_p_o.pur_number")
            ->innerJoin("pur_warehouse_results AS p_w_r","p_w_r.pur_number=p_o_i.pur_number AND p_w_r.sku=p_o_i.sku")
            ->leftJoin("pur_fba_avg_deliery_time AS p_f_a","p_f_a.sku=p_o_i.sku")
            ->where("p_p_o.purchase_type=3")
            ->andWhere("p_p_o.audit_time IS NULL")
            ->andWhere("UNIX_TIMESTAMP(p_p_o.audit_time)+IFNULL(p_f_a.avg_delivery_time,0) > UNIX_TIMESTAMP(IFNULL(p_w_r.instock_date,'3001-01-01'))");

        $subQuery_Avg_Timeout_1->union($subQuery_Avg_Timeout_2,true);

        $subSql2    = (new Query())->select("pur_number,sku")->from(['tmp' => $subQuery_Avg_Timeout_1]);

        $subSql2    = $subSql2->groupBy('pur_number,sku');
        $select_sql = $subSql2->createCommand()->getRawSql();

        $insert_sql = "INSERT INTO pur_fab_purchase_order_trace (`pur_number`,`sku`) $select_sql ";

        $connection = Yii::$app->db;
        $command    = $connection->createCommand($insert_sql);
        $result     = $command->execute();
        echo "<span style='color: green'>生成数据[权均交期是否超时]：$result 条</span><br/><br/>";

        $connection = Yii::$app->db;
        $command    = $connection->createCommand("UPDATE pur_fab_purchase_order_trace SET `data_type`=2 WHERE id>'$max_id'");
        $result     = $command->execute();
        /**************************************************************************************************************/



        /**************************************************************************************************************/
        $max_id = self::getMaxId();
        // 是否采购过
        // 查询采购过
        $subQuery_sku_2 = (new Query())->select("sku")
            ->from("pur_purchase_order_items")
            ->groupBy('sku');

        $subSql3    = (new Query())->select("sku")->from(['tmp' => $subQuery_sku_2]);
        $subSql3    = $subSql3->groupBy('sku');
        $select_sql = $subSql3->createCommand()->getRawSql();

        $insert_sql = "INSERT INTO pur_fab_purchase_order_trace (`sku`) $select_sql ";

        $connection = Yii::$app->db;
        $command    = $connection->createCommand($insert_sql);
        $result     = $command->execute();
        echo "<span style='color: green'>生成数据[是否采购过]：$result 条</span><br/><br/>";

        $connection = Yii::$app->db;
        $command    = $connection->createCommand("UPDATE pur_fab_purchase_order_trace SET `data_type`=3 WHERE id>'$max_id'");
        $result     = $command->execute();
        /**************************************************************************************************************/


        $used = time() - $start_time;
        echo "<span style='color: green'>耗时：$used 秒</span><br/><br/>";
        exit;
    }


}
