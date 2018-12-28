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
use app\models\PurchaseOrderTaxes;
use app\models\PurchaseSuggestNote;
use app\models\PurchaseTemporary;
use app\models\SupervisorGroupBind;
use app\models\WarehouseResults;
use app\services\BaseServices;
use app\services\CommonServices;
use app\services\PurchaseOrderServices;
use m35\thecsv\theCsv;
use Yii;
use yii\db\Exception;
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
            $model->level_audit_status=0;
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
                        if($model->save(false)==false && Yii::$app->request->isAjax){
                            $res['status'] = 0;
                            $res['msg'] = '对不起,批量审核失败';
                            die(json_encode($res));
                        }else if($model->save(false)==false){
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
                if (!empty($ids) && Yii::$app->request->isAjax){
                    $res['msg'] = '恭喜你,批量审核成功';
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
                        //判断sku金额大于等于20000且备货数量大于等于100，满足这个条件，庄总审核完成后需求才能到采购需求汇总里
                        if(empty($model->defaultSupplier->supplier_code)){
                            throw new HttpException(500,'有需求没有报价请先维护,需求单号：'.$model->demand_number);
                        }
                        $price = self::actionGetPrice($model->defaultSupplier->supplier_code,$model->sku,$model->purchase_quantity);
                        if($model->purchase_quantity >= 100 || $price >= 10000){
                            if($model->level_audit_status==0){
                                $model->level_audit_status=1;
                                $model->init_level_audit_status=0;
                            }
                        }
                        $model->agree_user=Yii::$app->user->identity->username;
                        $model->agree_time=date('Y-m-d H:i:s',time());
                        if($model->save(false)==false && Yii::$app->request->isAjax){
                            $res['status'] = 0;
                            $res['msg'] = '对不起,批量审核失败';
                            die(json_encode($res));
                        }else if($model->save(false)==false){
                            throw new HttpException(500,'需求同意失败！');
                        }
                    }
                    PlatformSummary::pushSummaryPriority($ids);
                }else{
                    $model = $this->findModel($id);

                    //判断sku金额大于等于10000且备货数量大于等于100，满足这个条件，庄总审核完成后需求才能到采购需求汇总里
                    if(empty($model->defaultSupplier->supplier_code)){
                        throw new HttpException(500,'有需求没有报价请先维护,需求单号：'.$model->demand_number);
                    }
                    $price = self::actionGetPrice($model->defaultSupplier->supplier_code,$model->sku,$model->purchase_quantity);
                    if($model->purchase_quantity >= 100 || $price >= 10000){
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
                    PlatformSummary::pushSummaryPriority($id);
                }
                $tran->commit();
                if (!empty($ids) && Yii::$app->request->isAjax){
                    $res['msg'] = '恭喜你,批量审核成功';
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
            if(count($ids)>1){//批量
                foreach($ids as $v){
                    $post_model = $this->findModel($v);
                    if($post_model->level_audit_status==0){
                        $post_model->level_audit_status=2;
                    }
                    $post_model->agree_user=Yii::$app->user->identity->username;
                    $post_model->audit_note=$post['audit_note'];
                    $post_model->agree_time=date('Y-m-d H:i:s',time());
                    $post_model->save();
                }

                Yii::$app->getSession()->setFlash('success',"恭喜您,批量操作成功！",true);
                return $this->redirect(['sales-index','page'=>$post['page']]);
            }else{
                $model = $this->findModel($post['id']);
                if($model->level_audit_status==0){
                    $model->level_audit_status=2;
                }
                $model->agree_user=Yii::$app->user->identity->username;
                $model->audit_note=$post['audit_note'];
                $model->agree_time=date('Y-m-d H:i:s',time());
                $model->save();
                Yii::$app->getSession()->setFlash('success',"恭喜您操作成功！",true);
                return $this->redirect(['sales-index','page'=>$post['page']]);
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
            if(count($ids)>1){//批量
                foreach($ids as $v){
                    $post_model = $this->findModel($v);
                    if($post_model->level_audit_status==0){
                        $post_model->level_audit_status=2;
                    }
                    $post_model->agree_user=Yii::$app->user->identity->username;
                    $post_model->audit_note=$post['audit_note'];
                    $post_model->agree_time=date('Y-m-d H:i:s',time());
                    $post_model->save();
                }

                Yii::$app->getSession()->setFlash('success',"恭喜您,批量操作成功！",true);
                return $this->redirect(['sales-index','page'=>$post['page']]);
            }else{
                $model = $this->findModel($post['id']);

                //判断sku金额大20000且备货数量大于500，庄总驳回改变初步审核状态，其他流程不变
                $price = self::actionGetPrice($model->defaultSupplier->supplier_code,$model->sku,$model->purchase_quantity);
                if($model->purchase_quantity >= 100 || $price >= 10000){
                    if($model->init_level_audit_status==1){
                        $model->init_level_audit_status=0;
                        $model->level_audit_status=4;
                    }
                }
                $model->agree_user=Yii::$app->user->identity->username;
                $model->audit_note=$post['audit_note'];
                $model->purchase_note=$post['audit_note'];
                $model->agree_time=date('Y-m-d H:i:s',time());
                $model->save();
                Yii::$app->getSession()->setFlash('success',"恭喜您操作成功！",true);
                return $this->redirect(['sales-index','page'=>$post['page']]);
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
        $map['level_audit_status'] = ['2','0','4'];
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
        $ids                       = Yii::$app->request->getBodyParam('ids');
        $map['id']                 = $ids;
        $map['level_audit_status'] = 1;
        $map['is_purchase']        = 1;
        $map['purchase_type']      = 3;
        $orders                    = PlatformSummary::find()->where($map)->all();
        $response = PurchaseOrder::createFbaPurOrder($orders);
        Yii::$app->getSession()->setFlash($response['status'], $response['message']);
        return $this->redirect(['index']);
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
        $model = new PlatformSummary();
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
                $product=Product::find()->where(['sku'=>trim($datas[0])])->one();
                if(!empty($product)&&trim($datas[0])!==$product->sku){
                    Yii::$app->getSession()->setFlash('warning','导入的sku:' . $datas[0].'与系统sku：'.$product->sku.'不符',false);
                    return $this->redirect(['sales-index']);
                }
                if(empty($product)||!in_array($product->product_status,[4,18,3,20])||$product->product_is_multi==2||$product->product_type==2){
                    Yii::$app->getSession()->setFlash('warning','产品不存在或者产品包含如下属性(状态:非在售，非试卖在售，非预上线,非预上线拍摄中,捆绑sku,主sku)：' . $datas[0],false);
                    return $this->redirect(['sales-index']);
                }

                //预上线拍摄中的状态，在创建销售需求的时候，判断采购有没有记录，如果有，则不能创建需求
                if(in_array($product->product_status,[20])){
                    $is_exist = PurchaseOrderItems::find()
                        ->alias('items')
                        ->leftJoin(PurchaseOrder::tableName().' order','items.pur_number=order.pur_number')
                        ->where(['in','order.purchas_status',[1,2,3,5,6,7,8,9]])
                        ->andWhere(['items.sku'=>$datas[0]])
                        ->count();
                    if($is_exist > 0){
                        Yii::$app->getSession()->setFlash('warning','产品为预上线拍摄中的状态,有对应的采购记录,不能创建需求：' . $datas[0],false);
                        return $this->redirect(['sales-index']);
                    }
                }
                $group = SupervisorGroupBind::find()->where(['supervisor_name'=>mb_convert_encoding(trim($datas[6]),'utf-8','gbk')])->asArray()->all();
                $userGroup = ArrayHelper::getColumn($group,'group_id');
                $user_group = mb_convert_encoding(trim($datas[4]),'utf-8','gbk');
                if(empty($userGroup)||!in_array($user_group,$userGroup)){
                    Yii::$app->getSession()->setFlash('warning','用户组不对：' . trim($datas[0]),false);
                    return $this->redirect(['sales-index']);
                }
                if(empty($user_group)){
                    Yii::$app->getSession()->setFlash('warning','采购系统没有该产品（sku）：' . trim($datas[0]),false);
                    return $this->redirect(['sales-index']);
                }

                $num = count($datas);
                for ($c = 0; $c < $num; $c++) {
                    $Name[$line_number][] = mb_convert_encoding(trim($datas[$c]),'utf-8','gbk');
                }

                if(!empty($Name[$line_number][1]) && !is_numeric($Name[$line_number][1])){
                    $Name[$line_number][1] =strtoupper($Name[$line_number][1]);
                }
                $defaultQuotes = ProductProvider::find()->alias('t')
                    ->select('t.supplier_code,sq.pur_ticketed_point')
                    ->leftJoin(SupplierQuotes::tableName().' sq','sq.id=t.quotes_id')
                    ->where(['t.is_supplier'=>1,'t.sku'=>trim($datas[0])])
                    ->asArray()->one();
                $tax_rate  = Product::find()->select('tax_rate')->where(['sku'=>trim($datas[0])])->scalar();
                if(empty($defaultQuotes)||empty($defaultQuotes['pur_ticketed_point'])){
                    Yii::$app->getSession()->setFlash('warning','采购系统没有该产品（sku）：' . trim($datas[0]).'的报价信息',false);
                    return $this->redirect(['sales-index']);
                }
                $is_back_tax = Vhelper::getProductIsBackTax($tax_rate,$defaultQuotes['pur_ticketed_point']);
                $Name[$line_number][3] = $is_back_tax==1 ? 'TS':'FBA_SZ_AA';
//                if(!empty($Name[$line_number][3]) && !is_numeric($Name[$line_number][3])){
//                    $Name[$line_number][3] = Warehouse::find()->select('id,warehouse_code,warehouse_name')->where(['use_status'=>1,'warehouse_name'=>trim($Name[$line_number][3])])->asArray()->one()['warehouse_code'];
//                }

                $pmodel = Product::find()->joinWith(['desc','cat'])->where(['pur_product.sku'=>trim($datas[0])])->asArray()->one();
                $Name[$line_number][] = $pmodel['product_category_id'];
                $Name[$line_number][] = !empty($pmodel['desc']['title']) ? $pmodel['desc']['title'] : '';

                $Name[$line_number][] = 3;
                $Name[$line_number][] = 1;//是否中转，默认直发：1
                $Name[$line_number][] = CommonServices::getNumber('RD');
                $Name[$line_number][] = Yii::$app->user->identity->username;
                $Name[$line_number][] = date('Y-m-d H:i:s',time());
                $Name[$line_number][] = isset($defaultQuotes['supplier_code']) ? $defaultQuotes['supplier_code']: '';
                //pur_ticketed_point
                $Name[$line_number][] = $defaultQuotes['pur_ticketed_point'];
                //is_back_tax
                $Name[$line_number][] = $is_back_tax;
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
                $statu= Yii::$app->db->createCommand()->batchInsert(PlatformSummary::tableName(), ['sku', 'platform_number', 'purchase_quantity', 'purchase_warehouse','group_id','sales_note','sales','product_category','product_name',
                    'purchase_type', 'is_transit', 'demand_number', 'create_id', 'create_time','supplier_code','pur_ticketed_point','is_back_tax'], $Name)->execute();

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
                Yii::$app->getSession()->setFlash('error',$error_message,true);
                return $this->redirect(['sales-index']);
            }

        } else {
            return $this->renderAjax('purchase-sum-import', ['model' => $model]);
        }
    }


    //FBA订单追踪
    public function actionSummaryDetail(){
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
        $query = $searchModel->search4($searchParams,true);
        $model=$query->all();
        $table = [
            'SKU',
            '产品名称',
            '需求数量',
            '入库数量',
            '订单审核通过时间',
            '预计到货时间',
            '采购员',
            '采购单状态',
            '采购单号',
            '权均交期',
            '权均交期是否超时',
            '采购到货超时'
        ];

        $table_head = [];
        foreach($model as $k=>$v)
        {
            $table_head[$k][]=$v->sku;
            $table_head[$k][]=!empty($v->desc) ? $v->desc->title : '';
            $table_head[$k][]=$v->purchase_quantity;
            $pur_number = empty($v->purOrder) ? '' : $v->purOrder->pur_number;
            $instock_num = empty($pur_number) ? '' : WarehouseResults::find()->select('instock_qty_count')->where(['pur_number'=>$pur_number,'sku'=>$v->sku])->scalar();
            $table_head[$k][]=empty($instock_num) ? 0 : $instock_num;
            $table_head[$k][]=empty($v->purOrder->audit_time) ? '' : $v->purOrder->audit_time;
            $table_head[$k][]=empty($v->purOrder->date_eta) ? '' : date('Y-m-d',strtotime($v->purOrder->date_eta));
            $table_head[$k][]=empty($v->purOrder->buyer) ? '' : $v->purOrder->buyer;
            $table_head[$k][]=empty($v->purOrder) ? '' : PurchaseOrderServices::getPurchaseStatusText($v->purOrder->purchas_status);
            $table_head[$k][]=empty($v->purOrder) ? '' : $v->purOrder->pur_number;
            $table_head[$k][]=empty($v->fbaAvgArrival) ? 0 : sprintf('%.2f',($v->fbaAvgArrival->avg_delivery_time)/(24*60*60));
            $data = !empty($v->purOrder) ? \app\models\WarehouseResults::find()->select('instock_date')->where(['pur_number'=>$v->purOrder->pur_number,'sku'=>$v->sku])->scalar() : '';
            $avg = !empty($v->fbaAvgArrival) ? $v->fbaAvgArrival->avg_delivery_time :0;
            $audit_time = !empty($v->purOrder) ? !empty($v->purOrder->audit_time)? $v->purOrder->audit_time :'': '';
            $instock = $data ? strtotime($data):time();
            $table_head[$k][]=empty($audit_time) ? '否': (((strtotime($audit_time)+$avg) <= $instock)? '是':'否');
            $date = empty($v->purOrder->date_eta) ? $v->purOrder->date_eta :'';
            $table_head[$k][]=empty($date)||empty($data) ? '否' : ((strtotime($data)-strtotime($date))>3*24*60*60 ? '是':'否');
        }
        theCsv::export([
            'header' =>$table,
            'data' => $table_head,
        ]);
    }
}