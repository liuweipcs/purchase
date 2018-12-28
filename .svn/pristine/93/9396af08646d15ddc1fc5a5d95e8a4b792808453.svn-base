<?php

namespace app\controllers;


use app\api\v1\models\ProductLine;
use app\config\Vhelper;
use app\models\EyeCheckCompanyInfo;
use app\models\PurchaseCategoryBind;
use app\models\PurchaseOrder;
use app\models\PurchaseOrderItems;
use app\models\SupplierBuyer;
use app\models\SupplierContactInformation;
use app\models\SupplierImages;
use app\models\SupplierLog;
use app\models\SupplierPaymentAccount;
use app\models\SupplierProductLine;
use app\models\SupplierSettlement;
use app\models\SupplierSettlementLog;
use app\models\SupplierUpdateApply;
use app\models\Product;
use app\models\ProductProvider;
use app\models\SupplierUpdateLog;
use app\models\User;
use app\services\BaseServices;
use app\services\CommonServices;
use app\services\PurchaseOrderServices;
use app\services\SupplierServices;
use linslin\yii2\curl\Curl;
use m35\thecsv\theCsv;
use Yii;
use app\models\Supplier;
use app\models\SupplierSearch;
use yii\db\Exception;
use yii\helpers\ArrayHelper;
use yii\web\ConflictHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\UploadedFile;
use app\commands\zipfile;


/**
 * Created by PhpStorm.
 * User: ztt
 * Date: 2017/3/23 0023
 * Time: 18:41
 */
class SupplierController extends BaseController
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
    public function init(){
        $this->enableCsrfValidation = false;
    }


    /**
     * Lists all Stockin models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new SupplierSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        //供应商搜索
        if(isset(Yii::$app->request->queryParams['SupplierSearch']['order_type'])){
            $searchModel->order_type = Yii::$app->request->queryParams['SupplierSearch']['order_type'];
        }
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 查看详情
     * Displays a single Stockin model.
     * @param integer $id
     * @return mixed
     */
    public function actionViewInfo($id)
    {
        $layout = Yii::$app->request->getQueryParam('layout','');
        if(!empty($layout)){
            Yii::$app->layout = $layout;
        }
        $model= Supplier::find()->where(['id'=>$id])->with(['pay','contact','img'])->one();

        return $this->render('view', [
            'p1' =>[],
            'p2'  =>[],
            'model' => $model,
        ]);
    }

    /**
     * 图片异步上传
     */
    public function actionAsyncImage ()
    {
        $post = Yii::$app->request->post();
        $input_name = $post['input_name'];

        if (Yii::$app->request->isPost) {
            Vhelper::ImageAsynUpolad($input_name, true);
        }
    }
    /**
     * 供应商创建
     * Creates a new Supplier model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model        = new Supplier();

        $model_pay    = new SupplierPaymentAccount();
        $model_contact= new SupplierContactInformation();
        $model_img    = new SupplierImages();
        $model_buyer  = new SupplierBuyer();
        $model_line   = new SupplierProductLine();
        $p1 = $p2 = [];
        if (Yii::$app->request->isPost) {
            $transaction=\Yii::$app->db->beginTransaction();
            try {
                //入主
                $data = $model->saveSupplier(Yii::$app->request->Post(),null,4,2);
                if(empty($data)||!is_array($data)){
                    throw new HttpException(500,'供应商添加失败'.$data);
                }
                //附图表
                $model_img->saveSupplierImg(Yii::$app->request->Post(),$data);
                //支付方式
                $model_pay->saveSupplierPay(Yii::$app->request->Post(),$data);
                //联系方式
                $model_contact->saveSupplierContact(Yii::$app->request->Post(),$data);
                //采购员
                $model_buyer->saveSupplierBuyer(Yii::$app->request->Post(),$data);
                //产品线
                $model_line->saveSupplierLine(Yii::$app->request->Post(),$data);
                SupplierLog::saveSupplierLog('supplier/create','success:'.$data['code'].'-'.$data['name'].'添加成功[来源：采购系统]',false,$data['name'],$data['code']);
                $transaction->commit();

                /* 供应商新增/审核 生成记录:start */
                $result_data = [
                    'res_type' => 1,
                    'res_source' => 2,
                    'related_id' => $data['id'],
                    'supplier_code' => $data['code'],
                    'supplier_name' => $data['name']
                ];
                \app\models\SupplierAuditResults::addOneResult($result_data);
                /* 供应商新增/审核 生成记录:end */

                Yii::$app->getSession()->setFlash('success', '恭喜你,添加成功',true);
                return $this->redirect(['index']);

            } catch (HttpException $e) {
                $transaction->rollBack();
                Yii::$app->getSession()->setFlash('warning',$e->getMessage(),true);
                return $this->redirect(Yii::$app->request->referrer);

            }

        }
        return $this->render('create', [
            'model'         => $model,
            'model_pay'     => $model_pay,
            'model_contact' => $model_contact,
            'model_img'     => $model_img,
            'model_line'    => $model_line,
            'model_buyer'   => $model_buyer,
            'p1'            => $p1,
            'p2'            => $p2,
        ]);

    }
    //erp嵌入供应商添加页面
    public function actionErpCreate()
    {
        $this->layout='ajax';
        $user = $user_number = '';
        if(Yii::$app->request->getQueryParam('user')&&Yii::$app->request->getQueryParam('num')){
            $user = Yii::$app->request->getQueryParam('user');
            $user_number = Yii::$app->request->getQueryParam('num');
            if(!empty($user)&&!empty($user_number)){
                if($user == 'admin'){
                    Yii::$app->end('admin用户不能登录');
                }
                if(!User::loginByErpToken('Erp_Create_Supplier',2)){
                    return $this->redirect('login');
                }
                $caigouUser = User::find()->select('id')->where(['user_number'=>$user_number,'status'=>10])->scalar();
                if(!$caigouUser){
                    $caigouUser =  User::addUsernameRandompassword($user,$user_number);
                }
            }else{
                Yii::$app->end('必要参数为空');
            }
        }else{
            Yii::$app->end('当前用户登录受限');
        }
        $model        = new Supplier();
        $model_pay    = new SupplierPaymentAccount();
        $model_contact= new SupplierContactInformation();
        $model_img    = new SupplierImages();
        $model_buyer  = new SupplierBuyer();
        $model_line   = new SupplierProductLine();
        $imageModel = new SupplierImages();
        $payTypeArray = $imagesInfo = $p1 = $p2 = [];
        $is_readonly = $is_audit = false;
        if (Yii::$app->request->isPost) {
            $transaction=\Yii::$app->db->beginTransaction();
            try {
                //入主
                $data = $model->saveSupplier(Yii::$app->request->Post(),$caigouUser,4,1);
                if(empty($data)||!is_array($data)){
                    throw new HttpException(500,'供应商添加失败'.$data);
                }
                //附图表
                $model_img->saveSupplierImg(Yii::$app->request->Post(),$data,$caigouUser);
                //支付方式
                $model_pay->saveSupplierPay(Yii::$app->request->Post(),$data);
                //联系方式
                $model_contact->saveSupplierContact(Yii::$app->request->Post(),$data);
                //采购员
                $model_buyer->saveSupplierBuyer(Yii::$app->request->Post(),$data);
                //产品线
                $model_line->saveSupplierLine(Yii::$app->request->Post(),$data);
                SupplierLog::saveSupplierLog('supplier/create','success:'.$data['code'].'-'.$data['name'].'添加成功[来源：ERP]',false,$data['name'],$data['code']);
                $transaction->commit();

                /* 供应商新增/审核 生成记录:start */
                $result_data = [
                    'res_type' => 1,
                    'res_source' => 1,
                    'related_id' => $data['id'],
                    'supplier_code' => $data['code'],
                    'supplier_name' => $data['name']
                ];
                \app\models\SupplierAuditResults::addOneResult($result_data);
                /* 供应商新增/审核 生成记录:end */

                Yii::$app->getSession()->setFlash('success', '恭喜你,添加成功',true);
                return $this->redirect(Yii::$app->request->referrer);
            } catch (HttpException $e) {
                $transaction->rollBack();
                Yii::$app->getSession()->setFlash('warning',$e->getMessage(),true);
                return $this->redirect(Yii::$app->request->referrer);
            }

        }
        return $this->render('update', [
            'model'         => $model,
            'model_pay'     => $model_pay,
            'model_contact' => $model_contact,
            'model_img'     => $model_img,
            'model_line'    => $model_line,
            'model_buyer'   => $model_buyer,
            'payTypeArray'=>$payTypeArray,
            'p1'    =>$p1,
            'p2'    =>$p2,
            'imageModel' => $imageModel,
            'imagesInfo'    =>$imagesInfo,
            'is_readonly' =>$is_readonly,
            'is_audit' =>$is_audit,
            'view'=>'update',
            'user'=> $user,
            'user_number'=> $user_number,

        ]);

        // return $this->render('create', [
        //     'model'         => $model,
        //     'model_pay'     => $model_pay,
        //     'model_contact' => $model_contact,
        //     'model_img'     => $model_img,
        //     'model_line'    => $model_line,
        //     'model_buyer'   => $model_buyer,
        //     'p1'            => $p1,
        //     'p2'            => $p2,
        // ]);

    }


    public function actionErpUpdate($supplier_code){
        $this->layout='ajax';
        $user = $user_number = '';
        if(Yii::$app->request->getQueryParam('user')&&Yii::$app->request->getQueryParam('num')){
            $user = Yii::$app->request->getQueryParam('user');
            $user_number = Yii::$app->request->getQueryParam('num');
            if(!empty($user)&&!empty($user_number)){
                if($user == 'admin'){
                    Yii::$app->end('admin无法登录');
                }
                if(!User::loginByErpToken('Erp_Create_Supplier',2)){
                    return $this->redirect('login');
                }
                $caigouUser = User::find()->select('id')->where(['user_number'=>$user_number,'status'=>10])->scalar();
                if(!$caigouUser){
                    $caigouUser =  User::addUsernameRandompassword($user,$user_number);
                }
            }else{
                Yii::$app->end('必要参数为空');
            }
        }else{
            Yii::$app->end('当前用户登录受限');
        }
        $updateUser = User::find()->select('username,id')->where(['id'=>$caigouUser])->asArray()->one();
        $model = Supplier::find()->where(['supplier_code'=>$supplier_code])->with(['pay','contact','img','line','buyerList'])->one();
        if(empty($model)){
            Yii::$app->end('不好意思采购系统没有这个供应商');
        }

        //保存操作的数据
        $supplier_update_log = [];
        $id = $model->id;
        $is_readonly = $is_audit = false;

        //联表查询
        $imageModel = SupplierImages::find()->where(['supplier_id'=>$id, 'image_status'=>1])->one();
        $imagesInfo = [];
        if (empty($imageModel)) {
            $imageModel = new SupplierImages();
        } else {
            $supplierImagesModel = SupplierImages::find()->where(['supplier_id'=>$id])->asArray()->all();

            $imagesInfo['image_url'] = array_filter(array_unique(array_column($supplierImagesModel, 'image_url')));
            $imagesInfo['public_busine_licen_url'] = array_filter(array_unique(array_column($supplierImagesModel, 'public_busine_licen_url')));
            $imagesInfo['verify_book_url'] = array_filter(array_unique(array_column($supplierImagesModel, 'verify_book_url')));
            $imagesInfo['ticket_data_url'] = array_filter(array_unique(array_column($supplierImagesModel, 'ticket_data_url')));
            $imagesInfo['receipt_entrust_book_url'] = array_filter(array_unique(array_column($supplierImagesModel, 'receipt_entrust_book_url')));
            $imagesInfo['card_copy_piece_url'] = array_filter(array_unique(array_column($supplierImagesModel, 'card_copy_piece_url')));
            $imagesInfo['bank_scan_price_url'] = array_filter(array_unique(array_column($supplierImagesModel, 'bank_scan_price_url')));
            $imagesInfo['fuyou_record_data_url'] = array_filter(array_unique(array_column($supplierImagesModel, 'fuyou_record_data_url')));
            $imagesInfo['private_busine_licen_url'] = array_filter(array_unique(array_column($supplierImagesModel, 'private_busine_licen_url')));
            $imagesInfo['busine_licen_url'] = array_filter(array_unique(array_column($supplierImagesModel, 'busine_licen_url')));
        }

        if(empty($model)){
            Yii::$app->end('更新的供应商不存在');
        }
        $model_pay = $model->pay;
        $model_contact = $model->contact;
        if(empty($model_contact)){// 联系人缺失无法新增
            $model_contact = new SupplierContactInformation();
        }
        $model_img  = $model->img;
        $model_line = $model->supplierLine;
        $model_buyer = $model->buyerList;
        $payTypeArray = [];
        if(!empty($model_pay)){
            foreach ($model_pay as $pay){
                $payTypeArray[]=$pay->account_type;
            }
        }
        $p1    = $p2 = [];


        if(Yii::$app->request->isPost){
            $supplierform = Yii::$app->request->getBodyParam('Supplier');
            if(isset($supplierform['supplier_settlement'])){
                $oldSettlement = $model->supplier_settlement;
                $newSettlement = $supplierform['supplier_settlement'];
                if(empty($newSettlement)){
                    throw new Exception('结算方式不能为空');
                }
                if($oldSettlement!=$newSettlement){
                    $supplierSettleModel = new SupplierSettlementLog();
                    $supplierSettleModel->supplier_code = $model->supplier_code;
                    $supplierSettleModel->old_settlement =$oldSettlement;
                    $supplierSettleModel->new_settlement = $newSettlement;
                    $supplierSettleModel->create_user_name = $updateUser['username'];
                    $supplierSettleModel->create_user_id = $updateUser['id'];
                    $supplierSettleModel->create_time = date('Y-m-d H:i:s',time());

                    $supplier_settlement_log_info = [
                        'supplier_code' => $model->supplier_code, //根据查找的supplier中的数据，获得供应商编码
                        'old_settlement' => $oldSettlement, //旧的结算方式
                        'new_settlement' => $newSettlement, //新的结算方式
                        'create_user_name' => $updateUser['username'], //修改人
                        'create_user_id' => $updateUser['id'], //修改人
                    ];
                    //====================== 结算日志添加 ========================
                    $supplier_update_log['supplier_settlement_log'] = $supplierSettleModel->attributes;
                }
            }

            //加载主表并验证保存
            if ($model->load(Yii::$app->request->post()) && $model->validate())
            {
                //==================== 供应商信息修改 ===========================
                $old_supplier_info = $model->oldAttributes;
                $new_supplier_info = $model->attributes;
                $supplier_update_log['supplier'] = ['old'=>$old_supplier_info,'new'=>$new_supplier_info];

                $id = $model->attributes['id'];
                if(!empty(Yii::$app->request->Post()['SupplierPaymentAccount']))
                {
                    foreach (Yii::$app->request->Post()['SupplierPaymentAccount'] as $c => $v)
                    {
                        $paydata =[];
                        foreach ($v as $paykey => $payvalue)
                        {
                            $paydata[] = [
                                'pay_id'                  => isset(Yii::$app->request->Post()['SupplierPaymentAccount']['pay_id'][$paykey]) ? Yii::$app->request->Post()['SupplierPaymentAccount']['pay_id'][$paykey] :'',
                                'supplier_id'             => $model->attributes['id'],
                                'supplier_code'           => $model->attributes['supplier_code'],
                                'payment_method'          => isset(Yii::$app->request->Post()['SupplierPaymentAccount']['payment_method'][$paykey]) ?Yii::$app->request->Post()['SupplierPaymentAccount']['payment_method'][$paykey] :'',
                                'payment_platform'        => isset(Yii::$app->request->Post()['SupplierPaymentAccount']['payment_platform'][$paykey]) ? Yii::$app->request->Post()['SupplierPaymentAccount']['payment_platform'][$paykey] :'',
                                'payment_platform_bank'   => isset(Yii::$app->request->Post()['SupplierPaymentAccount']['payment_platform_bank'][$paykey]) ? Yii::$app->request->Post()['SupplierPaymentAccount']['payment_platform_bank'][$paykey] :'',
                                'payment_platform_branch' => isset(Yii::$app->request->Post()['SupplierPaymentAccount']['payment_platform_branch'][$paykey]) ? Yii::$app->request->Post()['SupplierPaymentAccount']['payment_platform_branch'][$paykey] : '',
                                'account'                 => Yii::$app->request->Post()['SupplierPaymentAccount']['account'][$paykey],
                                'account_name'            => Yii::$app->request->Post()['SupplierPaymentAccount']['account_name'][$paykey],
                                'account_type'            => Yii::$app->request->Post()['SupplierPaymentAccount']['account_type'][$paykey],
                                'prov_code'               => Yii::$app->request->Post()['SupplierPaymentAccount']['prov_code'][$paykey],
                                'city_code'               => Yii::$app->request->Post()['SupplierPaymentAccount']['city_code'][$paykey],
                                'id_number'               => Yii::$app->request->Post()['SupplierPaymentAccount']['id_number'][$paykey],
                                'phone_number'               => Yii::$app->request->Post()['SupplierPaymentAccount']['phone_number'][$paykey],
                                'status'                  => isset(Yii::$app->request->Post()['SupplierPaymentAccount']['status'][$paykey]) ? Yii::$app->request->Post()['SupplierPaymentAccount']['status'][$paykey] : 1,
                            ];
                        }
                    }
                    foreach ($paydata as $k => $v)
                    {
                        if(!empty($v['pay_id'])){
                            $payModel = SupplierPaymentAccount::find()->where(['pay_id'=>$v['pay_id']])->one();
                            foreach ($v as $payItemKey=>$payItemValue){
                                $payModel->setAttribute($payItemKey, $payItemValue);
                            }
                            $old_supplier_payment_account_info[$k] = $payModel->oldAttributes;
                            $new_supplier_payment_account_info[$k] = $payModel->attributes;
                            //===============  修改支付方式 SupplierPaymentAccount ================
                            $supplier_update_log['supplier_payment_account_update'] = ['old'=>$old_supplier_payment_account_info,'new'=>$new_supplier_payment_account_info];
                        }else{
                            //============== 新增支付方式！！！
                            $supplier_update_log['supplier_payment_account_insert'][] = $v;

                        }

                    }
                    $payIds = array_column($paydata,'pay_id');
                    foreach ($model->pay as $value){
                        if(!in_array($value->pay_id,$payIds)){
                            $supplier_update_log['supplier_payment_account_delete'][]= $value->attributes;
                        }

                    }

                }

                //保存联系
                if(!empty(Yii::$app->request->Post()['SupplierContactInformation']))
                {
                    $supplierContactInformation = Vhelper::changeData(Yii::$app->request->Post()['SupplierContactInformation']);
                    foreach ($supplierContactInformation as $k => $v)
                    {
                        if (!empty($v)&&!empty($model->contact))
                        {
                            foreach ($v as $d => $c)
                            {
                                $model->contact[$k]->setAttribute($d, $c);
                                $old_supplier_contact_information_info[$k] = $model->contact[$k]->oldAttributes;
                                $new_supplier_contact_information_info[$k] = $model->contact[$k]->attributes;
                            }
                            //============= 修改联系方式 SupplierContactInformation !!!!!!!!!!!!!!!!
                            $supplier_update_log['supplier_contact_information_update'] = ['old'=>$old_supplier_contact_information_info,'new'=>$new_supplier_contact_information_info];
                        }
                    }
                    if(empty($model->contact)){
                        $model_contact= new SupplierContactInformation();
                        $supplier_contact_information_info = $model_contact->saveSupplierContact(Yii::$app->request->Post(),['id'=>$model->id,'code'=>$model->supplier_code],true);
                        //================= 新增联系方式 SupplierContactInformation
                        $supplier_update_log['supplier_contact_information_insert'] = $supplier_contact_information_info;
                    }
                }
                //批量插入图片
                if(!empty(Yii::$app->request->Post()['SupplierImages']))
                {
                    $supplier_images_info = [];
                    $getSupplierImagesInfo = Yii::$app->request->Post()['SupplierImages'];
                    foreach ($getSupplierImagesInfo as $key => $value) {
                        if (!empty($value)) {
                            $is_null = false;
                            break;
                        } else {
                            $is_null = true;
                        }
                    }
                    if ($is_null == false) {
                        $getSupplierImagesInfo['supplier_id'] = $id;
                        $supplier_images_info = $getSupplierImagesInfo;
                        $supplier_update_log['supplier_images'] = $supplier_images_info;
                    }
                }

                if(!empty(Yii::$app->request->Post()['SupplierBuyer']))
                {
                    $buyer_model = new SupplierBuyer();
                    $supplier_buyer_info = $buyer_model->saveSupplierBuyer(Yii::$app->request->Post(),['name'=>$model->supplier_name,'code'=>$model->supplier_code],true);
                    //================== 供应商采购员 =======================
                    $supplier_update_log['supplier_buyer'] = $supplier_buyer_info;
                }
                if(!empty(Yii::$app->request->Post()['SupplierProductLine']))
                {
                    $lineForm = Yii::$app->request->Post()['SupplierProductLine'];
                    if(is_array(Yii::$app->request->Post()['SupplierProductLine']['first_product_line'])){
                        foreach (Yii::$app->request->Post()['SupplierProductLine']['first_product_line'] as $linek=>$value){
                            $supplier_product_line_info[$linek]['first_product_line'] = $value;
                            $supplier_product_line_info[$linek]['supplier_code'] = $model->attributes['supplier_code'];
                            $supplier_product_line_info[$linek]['second_product_line'] = !empty($lineForm['second_product_line'][$linek]) ? $lineForm['second_product_line'][$linek] :null;
                            $supplier_product_line_info[$linek]['third_product_line'] = !empty($lineForm['third_product_line'][$linek]) ? $lineForm['third_product_line'][$linek] :null;
                        }
                    }else{
                        $supplier_product_line_info[0]['first_product_line']=Yii::$app->request->Post()['SupplierProductLine']['first_product_line'];
                        $supplier_product_line_info[0]['supplier_code']=$model->attributes['supplier_code'];
                        $supplier_product_line_info[0]['second_product_line']=Yii::$app->request->Post()['SupplierProductLine']['second_product_line'];
                        $supplier_product_line_info[0]['third_product_line']=Yii::$app->request->Post()['SupplierProductLine']['third_product_line'];
                    }
                    foreach ($supplier_product_line_info as $line){
                        $supplierModel = SupplierProductLine::find()->where(
                            [
                                'first_product_line'=>$line['first_product_line'],
                                'second_product_line'=>$line['second_product_line'],
                                'third_product_line'=>$line['third_product_line'],
                                'supplier_code'=>$line['supplier_code'],
                                'status'=>1
                            ]
                        )->exists();
                        if(!$supplierModel){
                            $supplier_update_log['supplier_product_line_insert'][] = $line;
                            if(!empty($model->line)) {
                                $supplier_update_log['supplier_product_line_delete'][] = $model->line[0]->attributes;
                            }
                        }
                    }
                    //====================== 供应商产品线 ==============================
                }
                if(!empty(Yii::$app->request->Post()['supplier_contact_delete'])){
                    $supplier_update_log['supplier_contact_delete'] = Yii::$app->request->Post()['supplier_contact_delete'];
                }
                $supplier_log_info = SupplierLog::saveSupplierLog('supplier/update','success:'.$model->supplier_code.'更新成功',true);

                //================== 修改日志  ======================
                $supplier_update_log['supplier_log'] = $supplier_log_info;
                $supplier_update_log_info = [
                    'supplier_name'=>$model->supplier_name,
                    'supplier_code'=>$model->supplier_code,
                    'action'=>'supplier/update',
                    'message'=>json_encode($supplier_update_log),
                ];
                $status = SupplierUpdateLog::saveSupplierUpdateLog($supplier_update_log_info,$caigouUser);
                if ($status===true) {
                    Supplier::updateAll(['is_push_to_erp' => 0],['supplier_code' => $model->supplier_code]);
                    Yii::$app->getSession()->setFlash('success', '提交成功，待审核', true);
                } elseif($status===0) {
                    Yii::$app->getSession()->setFlash('error', '待审核中，禁止修改', true);
                } else {
                    Yii::$app->getSession()->setFlash('error', '提交失败，请重新提交', true);
                }
                return $this->redirect(Yii::$app->request->referrer);
            }
        }
        return $this->render('update', [
            'model' => $model,
            'model_pay' => $model_pay,
            'model_contact' => $model_contact,
            'model_img' => $model_img,
            'model_line'=>$model_line,
            'model_buyer'=>$model_buyer,
            'payTypeArray'=>$payTypeArray,
            'p1'    =>$p1,
            'p2'    =>$p2,
            'imageModel' => $imageModel,
            'imagesInfo'    =>$imagesInfo,
            'is_readonly' =>$is_readonly,
            'is_audit' =>$is_audit,
            'view'=>'update',
            'user'=> $user,
            'user_number'=> $user_number,

        ]);

        // $p1    = $p2 = [];
        // return $this->render('update', [
        //     'model' => $model,
        //     'p1'    =>$p1,
        //     'p2'    =>$p2,
        //     'view'=>'update'

        // ]);
    }

    /**
     * 修改操作--最新代码
     * Updates an existing Supplier model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdateInfo($id=false)
    {

        //是否是只读
        $is_readonly = Yii::$app->request->get('is_readonly', false);
        //是否是审核
        $is_audit = Yii::$app->request->get('is_audit', false);

        if (empty($id)) {
            # 如果没有id则是创建供应商
            $model = new Supplier();
            $model_pay = new SupplierPaymentAccount();
            $model_contact = new SupplierContactInformation();
            $model_img = new SupplierImages();
            $model_line = new SupplierProductLine();
            $model_buyer = new SupplierBuyer();
            $imageModel = new SupplierImages();
            $payTypeArray = $imagesInfo = $p1 = $p2 = [];
        } else {
            //保存操作的数据
            $supplier_update_log = [];

            //联表查询
            $model = Supplier::find()->where(['id'=>$id])->with(['pay','contact','img', 'line','buyerList'])->one();
            $imageModel = SupplierImages::find()->where(['supplier_id'=>$id, 'image_status'=>1])->one();
            $imagesInfo = [];
            if (empty($imageModel)) {
                $imageModel = new SupplierImages();
            } else {
                $supplierImagesModel = SupplierImages::find()->where(['supplier_id'=>$id])->asArray()->all();

                $imagesInfo['image_url'] = array_filter(array_unique(array_column($supplierImagesModel, 'image_url')));
                $imagesInfo['public_busine_licen_url'] = array_filter(array_unique(array_column($supplierImagesModel, 'public_busine_licen_url')));
                $imagesInfo['verify_book_url'] = array_filter(array_unique(array_column($supplierImagesModel, 'verify_book_url')));
                $imagesInfo['ticket_data_url'] = array_filter(array_unique(array_column($supplierImagesModel, 'ticket_data_url')));
                $imagesInfo['receipt_entrust_book_url'] = array_filter(array_unique(array_column($supplierImagesModel, 'receipt_entrust_book_url')));
                $imagesInfo['card_copy_piece_url'] = array_filter(array_unique(array_column($supplierImagesModel, 'card_copy_piece_url')));
                $imagesInfo['bank_scan_price_url'] = array_filter(array_unique(array_column($supplierImagesModel, 'bank_scan_price_url')));
                $imagesInfo['fuyou_record_data_url'] = array_filter(array_unique(array_column($supplierImagesModel, 'fuyou_record_data_url')));
                $imagesInfo['private_busine_licen_url'] = array_filter(array_unique(array_column($supplierImagesModel, 'private_busine_licen_url')));
                $imagesInfo['busine_licen_url'] = array_filter(array_unique(array_column($supplierImagesModel, 'busine_licen_url')));
            }

            if(empty($model)){
                Yii::$app->end('更新的供应商不存在');
            }
            $model_pay = $model->pay;
            $model_contact = $model->contact;
            if(empty($model_contact)){// 联系人缺失无法新增
                $model_contact = new SupplierContactInformation();
            }
            $model_img  = $model->img;
            $model_line = $model->supplierLine;
            $model_buyer = $model->buyerList;
            $payTypeArray = [];
            if(!empty($model_pay)){
                foreach ($model_pay as $pay){
                    $payTypeArray[]=$pay->account_type;
                }
            }
            $p1    = $p2 = [];
        }

        if(Yii::$app->request->isPost){
            $supplierform = Yii::$app->request->getBodyParam('Supplier');
            if(isset($supplierform['supplier_settlement'])){
                $oldSettlement = $model->supplier_settlement;
                $newSettlement = $supplierform['supplier_settlement'];
                if(empty($newSettlement)){
                    throw new Exception('结算方式不能为空');
                }
                if($oldSettlement!=$newSettlement){
                    $supplierSettleModel = new SupplierSettlementLog();
                    $supplierSettleModel->supplier_code = $model->supplier_code;
                    $supplierSettleModel->old_settlement =$oldSettlement;
                    $supplierSettleModel->new_settlement = $newSettlement;
                    $supplierSettleModel->create_user_name = Yii::$app->user->identity->username;
                    $supplierSettleModel->create_user_id = Yii::$app->user->id;
                    $supplierSettleModel->create_time = date('Y-m-d H:i:s',time());

                    $supplier_settlement_log_info = [
                        'supplier_code' => $model->supplier_code, //根据查找的supplier中的数据，获得供应商编码
                        'old_settlement' => $oldSettlement, //旧的结算方式
                        'new_settlement' => $newSettlement, //新的结算方式
                        'create_user_name' => Yii::$app->user->identity->username, //修改人
                        'create_user_id' => Yii::$app->user->id, //修改人
                    ];
                    //====================== 结算日志添加 ========================
                    $supplier_update_log['supplier_settlement_log'] = $supplierSettleModel->attributes;
                }
            }
            //加载主表并验证保存
            if ($model->load(Yii::$app->request->post()) && $model->validate())
            {
                //==================== 供应商信息修改 ===========================
                $old_supplier_info = $model->oldAttributes;
                $new_supplier_info = $model->attributes;
                $supplier_update_log['supplier'] = ['old'=>$old_supplier_info,'new'=>$new_supplier_info];

                $id = $model->attributes['id'];
                if(!empty(Yii::$app->request->Post()['SupplierPaymentAccount']))
                {
                    foreach (Yii::$app->request->Post()['SupplierPaymentAccount'] as $c => $v)
                    {
                        $paydata =[];
                        foreach ($v as $paykey => $payvalue)
                        {
                            $paydata[] = [
                                'pay_id'                  => isset(Yii::$app->request->Post()['SupplierPaymentAccount']['pay_id'][$paykey]) ? Yii::$app->request->Post()['SupplierPaymentAccount']['pay_id'][$paykey] :'',
                                'supplier_id'             => $model->attributes['id'],
                                'supplier_code'           => $model->attributes['supplier_code'],
                                'payment_method'          => isset(Yii::$app->request->Post()['SupplierPaymentAccount']['payment_method'][$paykey]) ? Yii::$app->request->Post()['SupplierPaymentAccount']['payment_method'][$paykey] :'',
                                'payment_platform'        => isset(Yii::$app->request->Post()['SupplierPaymentAccount']['payment_platform'][$paykey]) ? Yii::$app->request->Post()['SupplierPaymentAccount']['payment_platform'][$paykey] :'',
                                'payment_platform_bank'   => isset(Yii::$app->request->Post()['SupplierPaymentAccount']['payment_platform_bank'][$paykey]) ? Yii::$app->request->Post()['SupplierPaymentAccount']['payment_platform_bank'][$paykey] :'',
                                'payment_platform_branch' => isset(Yii::$app->request->Post()['SupplierPaymentAccount']['payment_platform_branch'][$paykey]) ? Yii::$app->request->Post()['SupplierPaymentAccount']['payment_platform_branch'][$paykey] : '',
                                'account'                 => Yii::$app->request->Post()['SupplierPaymentAccount']['account'][$paykey],
                                'account_name'            => Yii::$app->request->Post()['SupplierPaymentAccount']['account_name'][$paykey],
                                'account_type'            => Yii::$app->request->Post()['SupplierPaymentAccount']['account_type'][$paykey],
                                'prov_code'               => Yii::$app->request->Post()['SupplierPaymentAccount']['prov_code'][$paykey],
                                'city_code'               => Yii::$app->request->Post()['SupplierPaymentAccount']['city_code'][$paykey],
                                'id_number'               => Yii::$app->request->Post()['SupplierPaymentAccount']['id_number'][$paykey],
                                'phone_number'               => Yii::$app->request->Post()['SupplierPaymentAccount']['phone_number'][$paykey],
                                'status'                  => isset(Yii::$app->request->Post()['SupplierPaymentAccount']['status'][$paykey]) ? Yii::$app->request->Post()['SupplierPaymentAccount']['status'][$paykey] : 1,
                            ];
                        }
                    }
                    foreach ($paydata as $k => $v)
                    {
                        if(!empty($v['pay_id'])){
                            $payModel = SupplierPaymentAccount::find()->where(['pay_id'=>$v['pay_id']])->one();
                            foreach ($v as $payItemKey=>$payItemValue){
                                $payModel->setAttribute($payItemKey, $payItemValue);
                            }
                            $old_supplier_payment_account_info[$k] = $payModel->oldAttributes;
                            $new_supplier_payment_account_info[$k] = $payModel->attributes;
                            //===============  修改支付方式 SupplierPaymentAccount ================
                            $supplier_update_log['supplier_payment_account_update'] = ['old'=>$old_supplier_payment_account_info,'new'=>$new_supplier_payment_account_info];
                        }else{
                            //============== 新增支付方式！！！
                            $supplier_update_log['supplier_payment_account_insert'][] = $v;

                        }

                    }
                    $payIds = array_column($paydata,'pay_id');
                    foreach ($model->pay as $value){
                        if(!in_array($value->pay_id,$payIds)){
                            $supplier_update_log['supplier_payment_account_delete'][]= $value->attributes;
                        }

                    }

                }

                //保存联系
                if(!empty(Yii::$app->request->Post()['SupplierContactInformation']))
                {
                    $supplierContactInformation = Vhelper::changeData(Yii::$app->request->Post()['SupplierContactInformation']);

                    foreach ($supplierContactInformation as $k => $v) {
                        $contact_update_flag = false;
                        if (!empty($v)) {
                            if (!empty($model->contact)) {
                                foreach ($model->contact as $mk => $mv) {
                                    if ($mv->contact_id==$v['contact_id']) {
                                        # 系统存在更新
                                        $contact_update_flag = true;
                                        foreach ($v as $d => $c) {
                                            $model->contact[$mk]->setAttribute($d, $c);
                                            $old_supplier_contact_information_info[$k] = $model->contact[$mk]->oldAttributes;
                                            $new_supplier_contact_information_info[$k] = $model->contact[$mk]->attributes;
                                        }
                                    }
                                }
                                //============= 修改联系方式 SupplierContactInformation !!!!!!!!!!!!!!!!
                                if (!empty($old_supplier_contact_information_info)) {
                                    $supplier_update_log['supplier_contact_information_update'] = ['old'=>$old_supplier_contact_information_info,'new'=>$new_supplier_contact_information_info];
                                }
                            }
                            
                        }
                        if ($contact_update_flag==true) {
                            unset($supplierContactInformation[$k]);
                        }
                    }
                    if (!empty($supplierContactInformation)) {
                        # 新增
                        $supplierContactInformationPost = [];
                        foreach ($supplierContactInformation as $key => $value) {
                            foreach ($value as $sk => $sv) {
                                $supplierContactInformationPost['SupplierContactInformation'][$sk][] = $sv;
                            }
                        }
                        $model_contact= new SupplierContactInformation();
                        $supplier_contact_information_info = $model_contact->saveSupplierContact($supplierContactInformationPost,['id'=>$model->id,'code'=>$model->supplier_code],true);
                        //================= 新增联系方式 SupplierContactInformation
                        $supplier_update_log['supplier_contact_information_insert'] = $supplier_contact_information_info;
                    }
                    if(empty($model->contact)){
                        $model_contact= new SupplierContactInformation();
                        $supplier_contact_information_info = $model_contact->saveSupplierContact(Yii::$app->request->Post(),['id'=>$model->id,'code'=>$model->supplier_code],true);
                        //================= 新增联系方式 SupplierContactInformation
                        $supplier_update_log['supplier_contact_information_insert'] = $supplier_contact_information_info;
                    }
                }
                //批量插入图片
                if(!empty(Yii::$app->request->Post()['SupplierImages']))
                {
                    $is_null = true;
                    $supplier_images_info = [];
                    $getSupplierImagesInfo = Yii::$app->request->Post()['SupplierImages'];
                    foreach ($getSupplierImagesInfo as $key => &$value) {
                        if(is_array($value)){
                            $value = array_diff($value,array(''));
                            $value = array_merge($value);

                        }
                        if(empty($value)){
                            continue;
                        }
                        $is_null = false;
                    }
                    if ($is_null == false) {
                        $getSupplierImagesInfo['supplier_id'] = $id;
                        $supplier_images_info = $getSupplierImagesInfo;
                        $supplier_update_log['supplier_images'] = $supplier_images_info;
                    }
                }

                if(!empty(Yii::$app->request->Post()['SupplierBuyer']))
                {
                    $buyer_model = new SupplierBuyer();
                    $supplier_buyer_info = $buyer_model->saveSupplierBuyer(Yii::$app->request->Post(),['name'=>$model->supplier_name,'code'=>$model->supplier_code],true);
                    //================== 供应商采购员 =======================
                    $supplier_update_log['supplier_buyer'] = $supplier_buyer_info;
                }
                if(!empty(Yii::$app->request->Post()['SupplierProductLine']))
                {
                    $lineForm = Yii::$app->request->Post()['SupplierProductLine'];
                    if(is_array(Yii::$app->request->Post()['SupplierProductLine']['first_product_line'])){
                        foreach (Yii::$app->request->Post()['SupplierProductLine']['first_product_line'] as $linek=>$value){
                            $supplier_product_line_info[$linek]['first_product_line'] = $value;
                            $supplier_product_line_info[$linek]['supplier_code'] = $model->attributes['supplier_code'];
                            $supplier_product_line_info[$linek]['second_product_line'] = !empty($lineForm['second_product_line'][$linek]) ? $lineForm['second_product_line'][$linek] :null;
                            $supplier_product_line_info[$linek]['third_product_line'] = !empty($lineForm['third_product_line'][$linek]) ? $lineForm['third_product_line'][$linek] :null;
                        }
                    }else{
                        $supplier_product_line_info[0]['first_product_line']=Yii::$app->request->Post()['SupplierProductLine']['first_product_line'];
                        $supplier_product_line_info[0]['supplier_code']=$model->attributes['supplier_code'];
                        $supplier_product_line_info[0]['second_product_line']=Yii::$app->request->Post()['SupplierProductLine']['second_product_line'];
                        $supplier_product_line_info[0]['third_product_line']=Yii::$app->request->Post()['SupplierProductLine']['third_product_line'];
                    }
                    foreach ($supplier_product_line_info as $line){
                        $supplierModel = SupplierProductLine::find()->where(['status' => 1,'supplier_code' => $line['supplier_code']]);
                        if($line['first_product_line']){// 一级产品线
                            $supplierModel->andWhere(['first_product_line'=>$line['first_product_line']]);
                        }else{
                            $supplierModel->andWhere("first_product_line='' OR first_product_line IS NULL");
                        }
                        if($line['second_product_line']){// 二级产品线
                            $supplierModel->andWhere(['second_product_line'=>$line['second_product_line']]);
                        }else{
                            $supplierModel->andWhere("second_product_line='' OR second_product_line IS NULL");
                        }
                        if($line['third_product_line']){// 三级产品线
                            $supplierModel->andWhere(['third_product_line'=>$line['third_product_line']]);
                        }else{
                            $supplierModel->andWhere("third_product_line='' OR third_product_line IS NULL");
                        }
                        $supplierModel = $supplierModel->exists();
                        if(!$supplierModel){
                            $supplier_update_log['supplier_product_line_insert'][] = $line;
                            if(!empty($model->line)){
                            $supplier_update_log['supplier_product_line_delete'][] = $model->line[0]->attributes;
                            }
                        }
                    }
                   //====================== 供应商产品线 ==============================
                }

                // 删除供应商联系人
                if(!empty(Yii::$app->request->Post()['supplier_contact_delete'])){
                    $supplier_update_log['supplier_contact_delete'] = Yii::$app->request->Post()['supplier_contact_delete'];
                    $supplier_contact_delete = explode(',',$supplier_update_log['supplier_contact_delete']);

                    foreach ($supplier_contact_delete as $contact_id){
                        $contact_model = SupplierContactInformation::findOne(['contact_id' => $contact_id ]);
                        $supplier_update_log['supplier_contact_delete_list_info'][]= $contact_model->attributes;
                    }
                }

                $supplier_log_info = SupplierLog::saveSupplierLog('supplier/update','success:'.$model->supplier_code.'更新成功',true);

                //================== 修改日志  ======================
                $supplier_update_log['supplier_log'] = $supplier_log_info;
                $supplier_update_log_info = [
                    'supplier_name'=>$model->supplier_name,
                    'supplier_code'=>$model->supplier_code,
                    'action'=>'supplier/update',
                    'message'=>json_encode($supplier_update_log),
                ];
                $status = SupplierUpdateLog::saveSupplierUpdateLog($supplier_update_log_info);
                if ($status===true) {
                    Supplier::updateAll(['is_push_to_erp' => 0],['supplier_code' => $model->supplier_code]);
                    Yii::$app->getSession()->setFlash('success', '提交成功，待审核', true);
                } elseif($status===0) {
                    Yii::$app->getSession()->setFlash('error', '待审核中，禁止修改', true);
                } else {
                    Yii::$app->getSession()->setFlash('error', '提交失败，请从新提交', true);
                }
                return $this->redirect(['index']);
            } else {
                $getErrors = serialize($model->getErrors());
                throw new Exception($getErrors);
            }
        }


        if(Yii::$app->request->isAjax){
            return $this->renderAjax('update', [
                'model' => $model,
                'model_pay' => $model_pay,
                'model_contact' => $model_contact,
                'model_img' => $model_img,
                'model_line'=>$model_line,
                'model_buyer'=>$model_buyer,
                'payTypeArray'=>$payTypeArray,
                'p1'    =>$p1,
                'p2'    =>$p2,
                'view' =>'list'
            ]);
        }
        return $this->render('update', [
            'model' => $model,
            'model_pay' => $model_pay,
            'model_contact' => $model_contact,
            'model_img' => $model_img,
            'model_line'=>$model_line,
            'model_buyer'=>$model_buyer,
            'payTypeArray'=>$payTypeArray,
            'p1'    =>$p1,
            'p2'    =>$p2,
            'imageModel' => $imageModel,
            'imagesInfo'    =>$imagesInfo,
            'is_readonly' =>$is_readonly,
            'is_audit' =>$is_audit,
            'view'=>'update'

        ]);
    }

    /**
     * Finds the Stockin model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Stockin the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Supplier::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    /**
     * 删除上传到临时目录的图片
     * @return string
     */
    public function actionDeletePic()
    {
        $error = '';
        if (Yii::$app->request->isPost) {
            $uploadpath = 'Uploads/' . date('Ymd') . '/';  //上传路径
            // 图片保存在本地的路径：images/Uploads/当天日期/文件名，默认放置在basic/web/下
            $dir = '/images/' . $uploadpath;
            $filename = yii::$app->request->post("filename");
            $filename = $dir . $filename;
            if (file_exists(Yii::getAlias('@app') . '/web' . $filename)) {
                unlink(Yii::getAlias('@app') . '/web' . $filename);
            }

        }
        echo json_encode($error);
    }

    /**
     * 批量修改供应商属性
     * @return string|\yii\web\Response
     */
    public  function actionAllEditSupplierAttr()
    {
        $model        = new Supplier();
        $model_buyer  = new SupplierBuyer();
        if (Yii::$app->request->isPost)
        {
            $supplier = Yii::$app->request->getBodyParam('Supplier');
            $buyer    = Yii::$app->request->getBodyParam('SupplierBuyer');
            $id = isset($supplier['id']) ? $supplier['id'] : '';
            $type = isset($buyer['type'])? $buyer['type'] : '';
            if(!empty($id)&&!empty($type)){
                $ids = explode(',',$id);
                foreach($ids as $v){
                    $updateSupplier= Supplier::find()->andFilterWhere(['id'=>$v])->one();
                    if(empty($updateSupplier)){
                        continue;
                    }
                    $supplierBuyer = SupplierBuyer::find()->andFilterWhere(['supplier_code'=>$updateSupplier->supplier_code,'type'=>$type])->one();
                    if(empty($supplierBuyer)){
                        $supplierBuyer = new SupplierBuyer();
                        $old_buyer = '';
                    } else {
                        $old_buyer = !empty($supplierBuyer->buyer) ? $supplierBuyer->buyer : '';
                    }
                    $supplierBuyer->type = $type;
                    $supplierBuyer->supplier_code = $updateSupplier->supplier_code;
                    $supplierBuyer->supplier_name = $updateSupplier->supplier_name;
                    $supplierBuyer->buyer         = isset($buyer['buyer']) ? $buyer['buyer'] : '';
                    $supplierBuyer->status        = 1;
                    $supplierBuyer->save();
                }

                if (!empty($type)) {
                    $purchas_status = PurchaseOrderServices::getPurchaseType($type);
                } else {
                    $purchas_status = '';
                }
                $supplier_log_message = '修改采购员：success:id'.$id.'修改采购员,部门：'.$purchas_status.',new采购员:'.$buyer['buyer'] . ',old采购员:' .$old_buyer;
                SupplierLog::saveSupplierLog('supplier/all-edit-supplier-attr',$supplier_log_message,false,$updateSupplier->supplier_name,$updateSupplier->supplier_code);
                Yii::$app->getSession()->setFlash('success','恭喜你!修改成功');
            }
            return $this->redirect(['index']);
        } else {
            $id = Yii::$app->request->get('id');
            return $this->renderAjax('attributes', [
                'model' => $model,
                'model_buyer'=>$model_buyer,
                'id' =>$id,
            ]);
        }
    }
    /**ajax进行验证
     * @return array
     */
    public function actionValidateForm () {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $model = new Supplier();
        $model->load(Yii::$app->request->post());
        return \yii\widgets\ActiveForm::validate($model);
    }
    /**
     * 省市区三级联动
     * Function output the site that you selected.
     * @param int $pid
     * @param int $typeid
     */
    public function actionSites($pid, $typeid = 0)
    {
        $model = BaseServices::getCityList($pid);
        if($typeid == 1){$aa="--请选择市--";}else if($typeid == 2 && $model){$aa="--请选择区--";}

        echo Html::tag('option',$aa, ['value'=>'']) ;
        if(!empty($pid)){
            foreach($model as $value=>$name)
            {
                echo Html::tag('option',Html::encode($name),array('value'=>$value));
            }
        }
    }

    /**
     * 支付方式二级联动
     * Function output the site that you selected.
     * @param int $pid
     * @param int $typeid
     */
    public function actionPayMethod($typeid = 0)
    {

        if($typeid==2){
            $model = ['1'=>'paypal','2'=>'财付通','3'=>'支付宝','4'=>'快钱','5'=>'网银'];
        }elseif($typeid==3){
            $model = SupplierServices::getPayBank();
        }else{
            $model = ['请先选择支付方式'];
        }

        if($typeid == 2){$aa="--请选择支付方式--";}else if($typeid == 3&& $model){$aa="--请选择银行类型--";}

        echo Html::tag('option',$aa, ['value'=>'empty']) ;

        foreach($model as $value=>$name)
        {
            echo Html::tag('option',Html::encode($name),array('value'=>$value));
        }
    }

    /**
     * 产品线三级联动
     * Function output the site that you selected.
     * @param int $pid
     * @param int $typeid
     */
    public function actionLine($pid)
    {
        if(empty($pid)){
            echo Html::tag('option','请选择', ['value'=>'']) ;
            Yii::$app->end();
        }
        $model = BaseServices::getProductLineList($pid);
        //echo Html::tag('option',$aa, ['value'=>'empty']) ;
        if(!empty($model)){
           // echo Html::tag('option','--请选择--', ['value'=>'']) ;
            foreach($model as $value=>$name)
            {
                echo Html::tag('option',Html::encode($name),array('value'=>$value));
            }
        }else{
            echo Html::tag('option',['无子级产品线'], ['value'=>'empty']) ;
        }
    }

    public function actionBuyer(){
        $id = Yii::$app->request->getQueryParam('id');
        $model = PurchaseCategoryBind::find()->andFilterWhere(['category_id'=>$id])->one();
        $buyer ='';
        if(!empty($model)){
            $buyer = $model->buyer_name;
        }
        echo json_encode(['status'=>'success','buyer'=>$buyer]);
    }

    /**
     * 搜索供应商
     * @param $q
     * @return array
     */
    public function actionSearchSupplier($q = null, $id = null,$status=1,$searchStatus=1)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = ['results' => ['id' => '', 'text' => '']];
        if (!is_null($q)) {
            $data=[];
            $supplierStatus = SupplierServices::getSupplierStatus(null);
            $sqlText='case status';
            foreach ($supplierStatus as $key=>$value){
                $text =' when '.$key.' then concat(supplier_name,"('.$value.')") ';
                $sqlText .= $text;
            }
            $sqlText.=' else supplier_name end';
            $query = Supplier::find()
                ->select(['id'=>'supplier_code','text'=>$sqlText])
                ->andFilterWhere(['like', 'supplier_name', $q]);
            $query->andFilterWhere(['status'=>$status]);
            $query->andFilterWhere(['search_status'=>$searchStatus]);
            $query->distinct()
                  ->limit(50)
                  ->asArray();
            $data = $query->all();
            if(!empty($data)){
                foreach ($data as &$val){
                     $val['text']=htmlspecialchars_decode($val['text']);
                }
            }

            $out['results'] = array_values($data);
        }elseif ($id > 0) {
            $out['results'] = ['id' => $id, 'text' => Supplier::findOne($id)->supplier_name];
        }
        return $out;
    }


    /**
     * 上传供应商
     * @return string|\yii\web\Response
     * @throws \yii\db\Exception
     */
    public function actionExUpdateSupplier()
    {
        //exit();
        set_time_limit(0);
        $model = new Supplier();
        if (Yii::$app->request->isPost)
        {
            $model->file_execl = UploadedFile::getInstance($model, 'file_execl');

            if($model->file_execl->getExtension()!='csv')
            {
                Yii::$app->getSession()->setFlash('error',"格式不正确",true);
                return $this->redirect(['index']);
            }
            $data              = $model->upload();

            if(empty($data))
            {
                Yii::$app->getSession()->setFlash('error',"文件上传失败",true);
                return $this->redirect(['index']);
            }
            $file        = fopen($data, 'r');
            $line_number = 0;
            while ($datas = fgetcsv($file)) {
                if ($line_number == 0) { //跳过表头
                    $line_number++;
                    continue;
                }
                $num = count($datas);
                for ($c = 0; $c < $num; $c++) {

                    $Name[$line_number][] = mb_convert_encoding(trim($datas[$c]),'utf-8','gbk');

                }
                $line_number++;
            }
            $tran = Yii::$app->db->beginTransaction();
            $code=[];
            try{
                foreach($Name as $k=>$value){
                    if(count($value)!=5){
                        throw new HttpException(500,'信息缺失!');
                    }
                    $supplier = Supplier::find()->andFilterWhere(['supplier_name'=>trim($value[0])])->one();
                    $supplierCode = empty($value[1])&&!empty($supplier)?$supplier->supplier_code : $value[1];
                    if(empty($supplierCode)){
                        continue;
                        //throw new HttpException(500,'供应商信息错误!');
                    }
                    $code[]=$supplierCode;
                    if(!empty($value[2])){
                        //添加产品线，如果已经存在就变更状态。不存在就添加
                        $productLine = ProductLine::find()->andFilterWhere(['linelist_cn_name'=>$value[2]])->one();
                        if(empty($productLine)){
                            throw new HttpException(500,'产品线数据错误!');
                        }
                        //SupplierProductLine::updateAll(['status'=>2],['supplier_code'=>$supplierCode]);
                        $model = SupplierProductLine::find()->andFilterWhere(['first_product_line'=>BaseServices::getProductLineFirst($productLine->product_line_id),
                                                                              'second_product_line'=> $productLine->linelist_parent_id,
                                                                              'third_product_line' => $productLine->product_line_id,
                                                                              'supplier_code'      => $supplierCode
                        ])->one();
                        if(empty($model)){
                            $model = new SupplierProductLine();
                            $model->third_product_line = $productLine->product_line_id;
                            $model->first_product_line = BaseServices::getProductLineFirst($productLine->product_line_id);
                            $model->second_product_line= $productLine->linelist_parent_id;
                            $model->supplier_code      = $supplierCode;
                            $model->status =1;
                        }else{
                            $model->status = $model->status ==1 ? 2 : 1;
                        }
                        if($model->save()==false){
                            throw new HttpException(500,'产品线添加失败');
                        }
                    }
                    $old_buyer = '';
                    if(!empty($value[3])){
                        $arr = [1=>'国内仓',2=>'海外仓',3=>'FBA'];
                        if(!in_array($value[3],$arr)){
                            throw new HttpException(500,'部门信息错误');
                        }
                        $arr1 = array_flip($arr);
                        $buyerModel = SupplierBuyer::find()->andFilterWhere(['supplier_code'=>$supplierCode,'type'=>$arr1[$value[3]]])->one();
                        if(empty($buyerModel)){
                            $buyerModel=new SupplierBuyer();
                        }
                        $old_buyer = $buyerModel->buyer; //旧的采购员

                        $buyerModel->supplier_code = $supplierCode;
                        $buyerModel->type          = $arr1[$value[3]];
                        $buyerModel->status        = 1;
                        $buyerModel->buyer         = trim($value[4]);
                        $buyerModel->supplier_name = $value[0];
                        if($buyerModel->save() == false){
                            throw new HttpException(500,'采购员添加失败');
                        }
                    }
                    //$supplier_log_message = "批量编辑供应商信息，{$value[0]}，{$supplierCode}";
                    $supplier_log_message = "{$value[0]}（修改采购员，部门：{$value[3]}，修改前采购员：{$old_buyer}，修改后采购员：{$value[4]}）";

                    SupplierLog::saveSupplierLog('ex-update-supplier', $supplier_log_message,false,trim($value[0]),$supplierCode);
                }
                $response = ['status'=>'success','message'=>'数据编辑成功'];
                $tran->commit();
            }catch(HttpException $e){
                $response = ['status'=>'error','message'=>$e->getMessage()];
                $tran->commit();
            }
            fclose($file);
            $supplier_log_message = $response['status'].$response['message'].'供应商编码：'.implode(',',array_unique($code));
            SupplierLog::saveSupplierLog('ex-update-supplier', $supplier_log_message);
            Yii::$app->getSession()->setFlash($response['status'],$response['message'],true);
            return $this->redirect(['index']);
        } else {
            return $this->renderAjax('addfile', ['model' => $model]);
        }
    }

    //供应商审核
    public function actionSupplierReview(){
        $ids=Yii::$app->request->post('ids');

        if(!empty($ids)){//批量
            foreach($ids as $v){
                $model = $this->findModel($v);

                if($model->supplier_status==1){
                    $model->supplier_status=2;
                }
                //$model->agree_user=Yii::$app->user->identity->username;
                //$model->agree_time=date('Y-m-d H:i:s',time());
                $model->save(false);
                Supplier::updateAll(['is_push_to_erp' => 0],['id' => $v]);
            }

            Yii::$app->getSession()->setFlash('success',"恭喜您,审核成功！");
            SupplierLog::saveSupplierLog('supplier/review','success:id'.implode(',',$ids).'审核成功');
        }else{
            Yii::$app->getSession()->setFlash('error',"审核失败！");
            SupplierLog::saveSupplierLog('supplier/review','error:id'.implode(',',$ids).'审核失败');
        };
        return $this->redirect(['index']);
    }

    //财务审核
    public function actionFinancialAudit(){
        $ids=Yii::$app->request->post('ids');
        if(!empty($ids)){//批量
            foreach($ids as $v){
                $model = $this->findModel($v);
                if($model->financial_status==1){
                    $model->financial_status=2;
                }
                //$model->agree_user=Yii::$app->user->identity->username;
                //$model->agree_time=date('Y-m-d H:i:s',time());
                $model->save(false);
                Supplier::updateAll(['is_push_to_erp' => 0],['id' => $v]);
            }

            Yii::$app->getSession()->setFlash('success',"恭喜您,审核成功！");
        }else{
            Yii::$app->getSession()->setFlash('error',"审核失败！");
        }
        return $this->redirect(['index']);
    }

    public function actionIsDisable(){
        try{
            $supplier = Supplier::find()->where(['id'=>Yii::$app->request->getQueryParam('id')])->one();
            if(empty($supplier)){
                throw new HttpException(500,'供货商信息异常');
            }
            $updateSupplier =  SupplierUpdateApply::find()
                ->where(['status'=>1])
                ->andFilterWhere(['new_supplier_code'=>$supplier->supplier_code])
                ->all();
            if(!empty($updateSupplier)){
                throw new HttpException(500,'当前供货商被作为新供货商待审中，无法禁用');
            }
            if($supplier->status==1&&SupplierUpdateApply::getProduct($supplier->supplier_code)!=0){
                throw new HttpException(500,'该供货商无法禁用,已与可用sku绑定');
            }
            $message = $supplier->status ==1 ? '禁用供应商成功' : '启用供应商成功';
            $supplier->status = $supplier->status == 1 ? 2 : 1;
            $supplier->save(false);
            $response = ['status'=>'success','message'=>$message];
            $logMessage = Yii::$app->user->identity->username."，{$message}，code:".$supplier->supplier_code;
            SupplierLog::saveSupplierLog('supplier/change',$logMessage,false,$supplier->supplier_name,$supplier->supplier_code);
            Supplier::updateAll(['is_push_to_erp' => 0],['supplier_code' => $supplier->supplier_code]);
        }catch(HttpException $e){
            $response = ['status'=>'error','message'=>$e->getMessage()];
        }
        Yii::$app->getSession()->setFlash($response['status'],$response['message']);
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionChangeSearch(){
        try{
            $supplier = Supplier::find()->where(['id'=>Yii::$app->request->getQueryParam('id')])->one();
            if(empty($supplier)){
                throw new HttpException(500,'供货商信息异常');
            }
            $message = $supplier->search_status ==1 ? '禁用搜索成功' : '启用搜索成功';
            $supplier->search_status = $supplier->search_status == 1 ? 2 : 1;
            $supplier->save(false);
            $response = ['status'=>'success','message'=>$message];
            $logMessage = Yii::$app->user->identity->username."，{$message}，code:" . $supplier->supplier_code;
            SupplierLog::saveSupplierLog('supplier/change',$logMessage,false,$supplier->supplier_name,$supplier->supplier_code);
            Supplier::updateAll(['is_push_to_erp' => 0],['supplier_code' => $supplier->supplier_code]);
        }catch(HttpException $e){
            $response = ['status'=>'error','message'=>$e->getMessage()];
        }
        Yii::$app->getSession()->setFlash($response['status'],$response['message']);
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionErpSupplier(){
        if(Yii::$app->request->isPost){
            $tran = Yii::$app->db->beginTransaction();
            try{
                $supplierName = trim(Yii::$app->request->getBodyParam('supplierName'));
                if(empty($supplierName)){
                    throw new HttpException(500,'供应商名字不能为空');
                }
                $exist = Supplier::find()->andFilterWhere(['supplier_name'=>$supplierName])->one();
                if($exist){
                    throw new HttpException(500,'该供应商已经在采购系统存在!');
                }
                $curl  = new Curl();
                $url     = Yii::$app->params['ERP_URL'].'/services/purchase/purchase/supplierlist';
                $v       = $curl->setPostParams([
                    'name' =>$supplierName,
                ])->post($url);
                $datas=json_decode($v)->data;
                if(empty($datas)){
                    throw new HttpException(500,'请确定输入供应商已经在erp过审!');
                }
                if(isset($datas) && !empty($datas)){
                    $model = new Supplier();
                    $supplierCode = $model->erpSave($model,$datas);
                }
                $response=['status'=>'success','message'=>'拉取供应商成功'];
                $tran->commit();
            }catch (HttpException $e){
                $response=['status'=>'error','message'=>$e->getMessage()];
                $tran->rollBack();
            }
            SupplierLog::saveSupplierLog('erp-supplier','拉取erp供应商 name : '.$supplierName);
            Yii::$app->session->setFlash($response['status'],$response['message']);
            return $this->redirect('index');
        }
        return $this->renderAjax('erp-supplier.php');
    }

    /**
     * excel导出
     * @throws \yii\web\HttpException
     */
    public function actionExportCsvs()
    {
        set_time_limit(0);
        $model = Supplier::find()->all();
        $table = [
            '供应商',
            '状态',
            'sku数量',
            '创建时间'
        ];
        $table_head = [];
        foreach($model as $k=>$v)
        {
            $table_head[$k][]=$v->supplier_name;
            $table_head[$k][]=$v->status==1 ? '正常':'禁用';
            $table_head[$k][]=SupplierUpdateApply::getProduct($v->supplier_code);
            $table_head[$k][]=date('Y-m-d H:i:s',$v->create_time);
        }
        theCsv::export([
            'header' =>$table,
            'data' => $table_head,
        ]);

    }
    /**
     * PHPExcel导出
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function actionExportCsv()
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');
        $id = Yii::$app->request->get('ids');
        $id = strpos($id,',')?explode(',',$id):$id;

        if (!empty($id))
            $model = Supplier::find()
                ->alias('t')
                ->where(['in','t.id',$id])
                ->all();
        else
        {
            $searchData = \Yii::$app->session->get('SupplierSearch');
            $searchModel = new SupplierSearch();
            $query = $searchModel->search($searchData,true);
            $model = $query->all();
        }

        $objectPHPExcel = new \PHPExcel();
        $objectPHPExcel->setActiveSheetIndex(0);
        $n = 0;
        //报表头的输出
        $objectPHPExcel->getActiveSheet()->mergeCells('A1:E1'); //合并单元格
//        $objectPHPExcel->getActiveSheet()->mergeCells('A1:A2');
        $objectPHPExcel->getActiveSheet()->setCellValue('A1','供货商管理');  //设置表标题
        $objectPHPExcel->setActiveSheetIndex(0)->getStyle('A1')->getFont()->setSize(24); //设置字体大小
        $objectPHPExcel->setActiveSheetIndex(0)->getStyle('A1')
            ->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        //表格头的输出  采购需求建立时间、财务审核时间、财务付款时间、销售备注
        $cell_value = ['供应商名称','供应商状态','结算方式','支付方式','sku有效数量'];
        foreach ($cell_value as $k => $v) {
            $objectPHPExcel->setActiveSheetIndex(0)->setCellValue(chr($k+65) . '3',$v);
        }
        //设置表头居中
        $objectPHPExcel->getActiveSheet()->getStyle('A3:E3')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //设置数据水平靠左和垂直居中
        $objectPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $objectPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        foreach ( $model as $v )
        {
            //明细的输出
            $objectPHPExcel->getActiveSheet()->setCellValue('A'.($n+4) ,$v->supplier_name);
            $objectPHPExcel->getActiveSheet()->setCellValue('B'.($n+4) ,$v->status ==1 ?'启用':'禁用');
            $objectPHPExcel->getActiveSheet()->setCellValue('C'.($n+4) ,!empty($v->supplier_settlement)?SupplierServices::getSettlementMethod($v->supplier_settlement):'');
            $objectPHPExcel->getActiveSheet()->setCellValue('D'.($n+4) ,!empty($v->supplier_settlement)?SupplierServices::getDefaultPaymentMethod($v->payment_method):'');
            $objectPHPExcel->getActiveSheet()->setCellValue('E'.($n+4) ,\app\models\SupplierUpdateApply::getProduct($v->supplier_code));
            $n = $n +1;
        }

        for ($i = 65; $i<70; $i++) {
            $objectPHPExcel->getActiveSheet()->getColumnDimension(chr($i))->setWidth(15);
            $objectPHPExcel->getActiveSheet()->getStyle( chr($i) . "3")->getFont()->setBold(true);
        }
        $objectPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);

        //设置样式
        $objectPHPExcel->getActiveSheet()->getStyle('A1:E'.($n+4))->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        ob_end_clean();
        ob_start();
        header("Content-type:application/vnd.ms-excel;charset=UTF-8");
        header('Content-Type : application/vnd.ms-excel');
        header('Content-Disposition:attachment;filename="'.'供货商管理-'.date("Y年m月j日").'.xls"');
        $objWriter= \PHPExcel_IOFactory::createWriter($objectPHPExcel,'Excel5');
        $objWriter->save('php://output');
        die;
    }


    public function actionGetSupplierIssettlement(){
        $supplierCode = Yii::$app->request->getQueryParam('supplierCode');
        $supplier = Supplier::find()->where(['supplier_code'=>$supplierCode])->one();
        $exist = SupplierSettlementLog::find()
            ->where(['supplier_code'=>$supplierCode])
            ->andWhere(['is_exec'=>1])
            ->andWhere(['in','update_user_name',['王曼','王范彬']])
            ->exists();
        if($exist){
            echo json_encode(['status'=>'error','account_type'=>$supplier->supplier_settlement]);
            Yii::$app->end();
        }
        echo json_encode(['status'=>'success','account_type'=>$supplier->supplier_settlement]);
        Yii::$app->end();
    }
    //导入编辑供应商基本信息
    public function actionUpdateSupplier()
    {
        set_time_limit(0);
        $model = new Supplier();
        if (Yii::$app->request->isPost)
        {
            $model->file_execl = UploadedFile::getInstance($model, 'file_execl');

            if($model->file_execl->getExtension()!='csv')
            {
                Yii::$app->getSession()->setFlash('error',"格式不正确",true);
                return $this->redirect(['index']);
            }
            $data              = $model->upload();

            if(empty($data))
            {
                Yii::$app->getSession()->setFlash('error',"文件上传失败",true);
                return $this->redirect(['index']);
            }
            $file        = fopen($data, 'r');
            $line_number = 0;
            while ($datas = fgetcsv($file)) {
                if ($line_number == 0) { //跳过表头
                    $line_number++;
                    continue;
                }
                $num = count($datas);
                for ($c = 0; $c < $num; $c++) {

                    $Name[$line_number][] = mb_convert_encoding(trim($datas[$c]),'utf-8','gbk');

                }
                $line_number++;
            }
            /*$Name
                [0=>供应商名称,1=>一级产品名称,2=>联系人,3=>联系电话,4=>QQ,5=>旺旺,6=>公司地址,7=>链接,8=>结算方式,9=>支付方式]
             * */
            $tran = Yii::$app->db->beginTransaction();
            $paymentArraay =[
                '1' =>'现金',
                '2' =>'支付宝',
                '3' =>'银行卡转账',
                '4' =>'PayPal',
                '5' =>'未知',

            ];
            try{
                $errorSupplier =[];
                $errorPayment = [];
                foreach($Name as $k=>$value){
                    if(count($value)!=10){
                        throw new HttpException(500,'信息缺失!');
                    }
                    $supplier = Supplier::find()->andFilterWhere(['supplier_name'=>$value[0]])->all();
                    if(empty($supplier)){
                        $errorSupplier[]=$value[0];
                        continue;
                    }
                    foreach ($supplier as $val){
                        if(!empty($value[2])){
                            $contact = SupplierContactInformation::find()->where(['supplier_id'=>$val->id])->one();
                            if(empty($contact)){
                                $contact = new SupplierContactInformation();
                            }
                            $contact->supplier_id   = $val->id;
                            $contact->contact_person = $value[2];
                            $contact->contact_number = !empty($value[3]) ? $value[3]: '无';
                            $contact->qq = $value[4];
                            $contact->want_want = $value[5];
                            $contact->supplier_code = $val->supplier_code;
                            if($contact->save()==false){
                                throw new HttpException(500,'联系方式保存失败!');
                            }
                        }
                        $val->supplier_type = empty($val->supplier_type) ? 7 : $val->supplier_type;
                        $val->supplier_level = empty($val->supplier_type) ? 1 : $val->supplier_type;
                        $val->supplier_address = !empty($value[6])? $value[6]:$val->supplier_address;
                        $val->store_link = empty($value[7]) ?!empty($val->store_link)?$val->store_link:'http://www.1688.com' :$value[7];
                        $val->business_scope = empty($val->business_scope) ? empty($value[1]) ? '无': $value[1] : $val->business_scope;
                        $settlement = SupplierSettlement::find()->select('supplier_settlement_code')->where(['supplier_settlement_name'=>$value[8]])->scalar();
                        $setexist = SupplierSettlementLog::find()->where(['supplier_code'=>$val->supplier_code,'is_exec'=>1])->exists();
                        if(!$setexist){
                            $val->supplier_settlement =  $settlement?$settlement:$val->supplier_settlement;
                            $val->payment_method = !empty(array_keys($paymentArraay,$value[9])) ? array_keys($paymentArraay,$value[9])[0] :$val->payment_method;
                            if(empty(array_keys($paymentArraay,$value[9]))){
                                $errorPayment[] =  $value[9];
                            }
                        }
                        if($val->save()==false){
                            var_dump($val->getErrors());
                            exit();
                            throw new HttpException(500,'供应商基本信息保存失败');
                        }
                        // 供应商修改记录
                        \app\models\SupplierLog::saveSupplierLog('supplier::actionUpdateSupplier',$val->supplier_settlement,false,$val->supplier_name,$val->supplier_code);
                        Supplier::updateAll(['is_push_to_erp' => 0],['supplier_code' => $val->supplier_code]);
                    }
                }
                $response = ['status'=>'success','message'=>'供应商信息更新成功'.implode(',',$errorSupplier).implode(',',$errorPayment)];
                $tran->commit();
            }catch(HttpException $e){
                $response = ['status'=>'error','message'=>$e->getMessage()];
                $tran->commit();
            }
            fclose($file);
            Yii::$app->getSession()->setFlash($response['status'],$response['message'],false);
            return $this->redirect(['index']);
        } else {
            return $this->renderAjax('addfile', ['model' => $model]);
        }
    }

    /**
     * 计划任务-推送所有采购员信息到erp
     * @return string|\yii\web\Response
     */
    public function actionPushBuyerInfo()
    {   

        set_time_limit(0);//程序执行时间无限制
        ini_set('memory_limit', '512M');

        $offset=(int)\Yii::$app->request->get('offset',0);
        $limit=(int)\Yii::$app->request->get('limit',1000);
        $skus=Product::find()->select('sku,id')->offset($offset)->orderBy('id')->limit($limit)->asArray()->all();
        $skuss=array_column($skus,'sku');
        //海外仓和国内仓的采购员是根据供应商获取的，FBA的是根据产品线获取的
        //sku 采购员
        //1.获取国内采购员信息
        $buyer_china=ProductProvider::find()
                         ->alias('a') 
                         ->select('a.sku,b.buyer')
                         ->leftJoin('pur_supplier_buyer b','a.supplier_code=b.supplier_code')
                         ->where(['=','b.type',1])
                         ->andwhere(['=','b.status',1])
                         ->andwhere(['in','a.sku',$skuss])
                         ->asArray()
                         ->all();
        $buyer_china=array_column($buyer_china, 'buyer','sku');


        //2.获取海外仓采购员信息
        $buyer_oversea=ProductProvider::find()
                             ->alias('a')
                             ->select('a.sku,b.buyer')
                             ->leftJoin('pur_supplier_buyer b','a.supplier_code=b.supplier_code')
                             ->where(['=','b.type',2])
                             ->andwhere(['=','b.status',1])
                             ->andwhere(['in','a.sku',$skuss])
                             ->asArray()
                             ->all();
        $buyer_oversea=array_column($buyer_oversea, 'buyer','sku');

        //3.获取FBA采购员信息
        $buyer_fba=ProductProvider::find()
                             ->alias('a')
                             ->select('a.sku,b.buyer_name')
                             ->leftJoin('pur_supplier_product_line l','a.supplier_code=l.supplier_code')
                             ->leftJoin('pur_purchase_category_bind b','l.first_product_line=b.category_id')
                             ->where(['=','l.status',1])
                             ->andwhere(['in','a.sku',$skuss])
                             ->asArray()
                             ->all();
        $buyer_fba=array_column($buyer_fba, 'buyer_name','sku');

/*        print_r($buyer_china);echo '***';
        print_r($buyer_oversea);echo '***';
        print_r($buyer_fba);exit;*/

        $list = [];
        foreach ($skus as $val){
               if(isset($buyer_china[$val['sku']])){
                 $list[$val['sku']][]=$buyer_china[$val['sku']];
               }
               if(isset($buyer_oversea[$val['sku']])){
                 $list[$val['sku']][]=$buyer_oversea[$val['sku']];
               }
               if(isset($buyer_fba[$val['sku']])){
                 $list[$val['sku']][]=$buyer_fbshuja[$val['sku']];
               }
        }
        //Vhelper::dump($list);
        return $list?$list:'没有数据了';

        //Vhelper::dump($skus->createCommand()->getRawSql());
        //Vhelper::dump($skus);

                
    }
    /**
     * 供应商修改信息--审核
     */
    public function actionAuditSupplier()
    {
        Yii::$app->response->format = 'raw';
        if(Yii::$app->request->isGet){
            $supplier_code = Yii::$app->request->get('supplier_code');
            $audit_status = SupplierUpdateLog::find()->select('audit_status')
                ->where(['supplier_code'=>$supplier_code])
                ->andWhere(['audit_status'=>[1,3,5]])
                ->scalar();
            $rolesArray = array_keys(Yii::$app->authManager->getRolesByUser(Yii::$app->user->id));
            if($audit_status==5&&!in_array('财务组',$rolesArray)&&!in_array('超级管理员组',$rolesArray)){
                Yii::$app->end('当前登录用户无法进行财务审核');
            }
            return $this->render('_audit', ['supplier_code' => $supplier_code]);
        } else if(Yii::$app->request->isPost) {
            $is_audit = Yii::$app->request->post('is_audit'); //是否审核通过，1审核通过，0审核不通过
            $is_audit_status = Yii::$app->request->post('audit_status'); //审核状态
            $supplier_code = Yii::$app->request->post('supplier_code'); //供应商代码
            $audit_note = Yii::$app->request->post('audit_note'); //审核备注
            $is_update_bank = Yii::$app->request->post('is_update_bank'); //是否修改银行信息
            $rolesArray = array_keys(Yii::$app->authManager->getRolesByUser(Yii::$app->user->id));
            if($is_audit_status==5&&!in_array('财务组',$rolesArray)&&!in_array('超级管理员组',$rolesArray)){
                Yii::$app->end('当前登录用户无法进行财务审核');
            }
            $model = SupplierUpdateLog::find()
                ->where(['supplier_code'=>$supplier_code])
                ->andWhere(['not in','audit_status',['2','4','7']])
                ->orderBy('id desc')
                ->one();
            $message_obj = json_decode($model->message);

            $tran = Yii::$app->db->beginTransaction();
            try{
                // 通过
                if ($is_audit == 1) {
                    if ($is_audit_status == 1) {
                        //待采购审核人
                        $audit_status = 3;
                        $type = 'purchase';
                    } elseif ($is_audit_status == 3) {
                        //待供应链审核
                        //是否修改银行信息
                        if ($is_update_bank === '1') {
                            $audit_status = 5;
                        } else {
                            //如果没有修改银行信息，就审核修改！！=============================
                            $audit_status = 7;
                            $status = SupplierUpdateLog::updateSupplierInfo($supplier_code);
                        }
                        $type = 'supply_chain';
                    } elseif ($is_audit_status == 5) {
                        //待财务审核-修改 =================== 修改数据!!
                        $audit_status = 7;
                        $type = 'finance';
                        $status = SupplierUpdateLog::updateSupplierInfo($supplier_code);
                    }
                } elseif ($is_audit ==0) {
                    if ($is_audit_status == 1) {
                        //待采购审核人-驳回
                        $audit_status = 2;
                        $type = 'purchase';
                    } elseif ($is_audit_status == 3) {
                        //待供应链审核-驳回
                        $audit_status = 4;
                        $type = 'supply_chain';
                    } elseif ($is_audit_status == 5) {
                        //待供应链审核-驳回
                        $audit_status = 6;
                        $type = 'finance';
                    }
                    $note = trim($audit_note);
                    if ( empty($note) ) {
                        Yii::$app->getSession()->setFlash('error',"驳回需要填备注！");
                        return $this->redirect(Yii::$app->request->referrer);
                    }
                }

                $status = SupplierUpdateLog::updateSupplierUpdateLog($model,$audit_status,$audit_note,$type);

                // 供应商审核记录
                if($type == 'supply_chain'){
                    $result_data = [
                        'res_type' => '2',
                        'related_id' => $model->id,
                        'audit_status' => ($audit_status == 7)? 10 : 20,
                        'supplier_code' => $model->supplier_code,
                        'supplier_name' => $model->supplier_name,
                    ];
                    \app\models\SupplierAuditResults::updateOneResult($result_data);
                }
                Supplier::updateAll(['is_push_to_erp' => 0],['supplier_code' => $model->supplier_code]);// 更新后需要重新同步到ERP

                if($audit_status == 7 AND array_key_exists('supplier_buyer',$message_obj)){// 修改采购员并审核通过
                    // 获取 供应商采购员
                    $supplier_buyer = SupplierBuyer::find()
                        ->andFilterWhere(['supplier_code' => $model->supplier_code,'status'=>1])
                        ->select('type,buyer')
                        ->indexBy('type')
                        ->asArray()
                        ->all();
                    $push_data = [
                        'supplier_code'     => $model->supplier_code,
                        'buyer_list'        => ArrayHelper::map($supplier_buyer,'type','buyer')
                    ];
                    \app\api\v1\models\Product::pushProductInfo($push_data);// 即时推送到ERP系统@author Jolon @date 2018-10-15 17:25
                }

                if ($status) {
                    Yii::$app->getSession()->setFlash('success',"恭喜您,审核成功！");
                } else {
                    Yii::$app->getSession()->setFlash('error',"恭喜您,审核失败！");
                }
                $tran->commit();
            }catch(HttpException $e){
                Yii::$app->getSession()->setFlash('error',"恭喜您,审核失败！");
                $tran->rollBack();
            }

            return $this->redirect(['index']);
        }
    }
    /**
     * 查看操作日志
     */
    public function actionViewOperationLog()
    {
        $supplier_code = Yii::$app->request->get('supplier_code');
        return $this->render('operation-log', ['supplier_code' => $supplier_code]);
    }


    // 导出所有供应商数据
    public function actionLeadData()
    {
        $list = Supplier::find()
            ->select(['id', 'supplier_code', 'supplier_name'])
            ->asArray()
            ->all();
        $objPHPExcel = new \PHPExcel();

        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '编号')
            ->setCellValue('B1', 'ID')
            ->setCellValue('C1', '编码')
            ->setCellValue('D1', '供应商名')
            ->setCellValue('E1', '统一社会信用码');

        foreach($list as $k => $v) {
            $objPHPExcel->getActiveSheet()->setCellValue('A'.($k+2), $k+1);
            $objPHPExcel->getActiveSheet()->setCellValue('B'.($k+2), $v['id']);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.($k+2), $v['supplier_code']);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.($k+2), $v['supplier_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('E'.($k+2), '');
        }




        header("Content-type: application/vnd.ms-excel;charset=UTF-8");
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition:attachment;filename="'.'出纳付款表-'.date("Y年m月j日").'.xls"');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel5');
        $objWriter->save('php://output');
        exit;

    }

    /**
     * 新增供应商审核
     */
    public function actionCheck(){
        if(Yii::$app->request->isPost){
            $data           = Yii::$app->request->post()['Supplier'];
            $ids            = $data['id'];
            $ids            = explode(',',$ids);
            $status         = $data['status'];
            $contract_notice    = $data['contract_notice'];

            if(empty($ids)){
                Yii::$app->getSession()->setFlash('error','请至少选择一个供应商');
                return $this->redirect(Yii::$app->request->referrer);
            }
            if($status == 5 AND empty($contract_notice)){
                Yii::$app->getSession()->setFlash('error','审核不通过必须填写原因');
                return $this->redirect(Yii::$app->request->referrer);
            }
            $supplier_code_list = Supplier::find()->select('supplier_code')->where(['in','id',$ids])->andWhere(['status'=>4])->column();
            if(empty($supplier_code_list)){
                Yii::$app->getSession()->setFlash('error','只有【待审】状态的供应商才能审核');
                return $this->redirect(Yii::$app->request->referrer);
            }
            foreach ($supplier_code_list as $supplier_code){
                Supplier::updateAll(['status'=>$status],['supplier_code'=>$supplier_code]);
                Supplier::updateAll(['is_push_to_erp' => 0],['supplier_code' => $supplier_code]);
                if($status == 5){
                    SupplierLog::saveSupplierLog('supplier/check','Disagree:审核不通过-'.$contract_notice,false,false,$supplier_code);
                }else{
                    SupplierLog::saveSupplierLog('supplier/check','Agree:审核通过',false,false,$supplier_code);
                }

                // 供应商审核记录
                $result_data = [
                    'related_id'    => Supplier::find()->select('id')->where(['supplier_code'=>$supplier_code])->scalar(),
                    'res_type'      => 1,
                    'supplier_code' => $supplier_code,
                    'audit_status'  => ($status == 5)? 20 : 10
                ];
                \app\models\SupplierAuditResults::updateOneResult($result_data);
            }
            Yii::$app->getSession()->setFlash('success','供应商审核成功');
            return $this->redirect(Yii::$app->request->referrer);
        } else {
            $model        = new Supplier();
            $data         = Yii::$app->request->get();
            $ids          = $data['ids'];
            $ids_str      = implode(',',$ids);

            return $this->renderAjax('check',['model' => $model,'id' => $ids_str]);
        }
    }
    /**
     * 查看联系方式详情
     */
    public function actionViewDetails($id)
    {
        $model = SupplierContactInformation::find()
            ->where(['supplier_id' => $id])
            ->asArray()
            ->one();
        $business_scope = Supplier::find()->select('business_scope')->where(['id'=>$id])->scalar();
        $model['business_scope'] = $business_scope;
        if (!$model) return '信息不存在';
        return $this->renderAjax('view-details', ['model' => $model,]);
    }
    /**
     * 下载附属文件
     */
    public function actionDownload(){
        $images_url    = Yii::$app->request->getQueryParam('filename',null);
        $dfile =  tempnam('/tmp', 'tmp');//产生一个临时文件，用于缓存下载文件
        $zip = new zipfile();
        //----------------------
        $filename = 'image.zip'; //下载的默认文件名

        //以下是需要下载的图片数组信息，将需要下载的图片信息转化为类似即可
        foreach ($images_url as $key => $value) {
            // $path = 'D:\WWW\purchase\web\\'; //Yii::$app->basePath
            $path = Yii::$app->basePath . '/web/'; //Yii::$app->basePath
            $url = $path . $value;
            $image_name = substr($value,strripos($value,"/")+1);
            $image[] = ['image_src' => $url, 'image_name' => $image_name];
        }
        
        foreach($image as $v){
            $zip->add_file(file_get_contents($v['image_src']),  $v['image_name']);
            // 添加打包的图片，第一个参数是图片内容，第二个参数是压缩包里面的显示的名称, 可包含路径
            // 或是想打包整个目录 用 $zip->add_path($image_path);
        }
        //----------------------
        $zip->output($dfile);

        // 下载文件
        ob_clean();
        header('Pragma: public');
        header('Last-Modified:'.gmdate('D, d M Y H:i:s') . 'GMT');
        header('Cache-Control:no-store, no-cache, must-revalidate');
        header('Cache-Control:pre-check=0, post-check=0, max-age=0');
        header('Content-Transfer-Encoding:binary');
        header('Content-Encoding:none');
        header('Content-type:multipart/form-data');
        header('Content-Disposition:attachment; filename="'.$filename.'"'); //设置下载的默认文件名
        header('Content-length:'. filesize($dfile));
        $fp = fopen($dfile, 'r');
        while(connection_status() == 0 && $buf = @fread($fp, 8192)){
            echo $buf;
        }
        fclose($fp);
        @unlink($dfile);
        @flush();
        @ob_flush();
        exit();
    }

    public function actionGetSupplierInfo(){
        $credit_code = Yii::$app->request->getQueryParam('credit_code','');
        $token = Yii::$app->request->getQueryParam('token');
        if(empty($token)){
            echo json_encode(['status'=>'error','message'=>'用户无法通过验证','data'=>[]]);
            Yii::$app->end();
        }
        if($token!=='ypHa2ONeXRP0YtjF'){
            echo json_encode(['status'=>'error','message'=>'用户验证失败','data'=>[]]);
            Yii::$app->end();
        }
        if(empty($credit_code)){
            echo json_encode(['status'=>'error','message'=>'缺少必要参数','data'=>[]]);
            Yii::$app->end();
        }
//        if(!EyeCheckCompanyInfo::check_group($credit_code)){
//            echo json_encode(['status'=>'error','message'=>'统一社会信用代码有误','data'=>[]]);
//            Yii::$app->end();
//        }
        $exist = EyeCheckCompanyInfo::find()
            ->alias('t')
            ->where(['credit_code'=>$credit_code])
            ->andWhere(['>','refresh_time',date('Y-m-d H:i:s',time()-60*86400)])
            ->asArray()->one();
        if($exist&&$exist['is_refresh_forward']==0){
            echo json_encode(['status'=>'success','message'=>'请求成功','data'=>$exist]);
            Yii::$app->end();
        }else{
            $data = EyeCheckCompanyInfo::getCompanyInfo($credit_code);
            echo json_encode($data);
            Yii::$app->end();
        }
    }

    public function actionCompanyInfo(){
        if(Yii::$app->request->isAjax){
            $creditCode =  Yii::$app->request->getQueryParam('credit_code');
            if(empty($creditCode)){
                echo '没有该公司信息';
                Yii::$app->end();
            }
//            if(!EyeCheckCompanyInfo::check_group($creditCode)){
//                echo '供应商社会统一信息代码有误';
//                Yii::$app->end();
//            }
            $companyInfo = EyeCheckCompanyInfo::find()->where(['credit_code'=>$creditCode])
                ->andWhere(['>','refresh_time',date('Y-m-d H:i:s',time()-60*86400)])
                ->one();
            if(empty($companyInfo)||$companyInfo->is_refresh_forward==1){
                $data = EyeCheckCompanyInfo::getCompanyInfo($creditCode);
                if($data['status']=='error'){
                    echo $data['message'];
                    Yii::$app->end();
                }
                $companyInfo = EyeCheckCompanyInfo::find()->where(['credit_code'=>$creditCode])
                    ->andWhere(['>','refresh_time',date('Y-m-d H:i:s',time()-60*86400)])
                    ->one();
            }
            return $this->renderAjax('company-info',['company_info'=>$companyInfo]);
        }else{
            echo '请求方式不符合要求';
            Yii::$app->end();
        }
    }
}
