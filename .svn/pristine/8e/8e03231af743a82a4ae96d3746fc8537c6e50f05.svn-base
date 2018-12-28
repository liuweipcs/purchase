<?php

namespace app\controllers;

use app\api\v1\models\Product;
use app\config\Vhelper;
use app\models\GroupAuditConfig;
use app\models\OperatLog;
use app\models\ProductSearch;
use app\models\PurchaseDemand;
use app\models\PurchaseOrderItemsV2;
use app\models\PurchaseOrderOrders;
use app\models\PurchaseOrderPay;
use app\models\PurchaseOrdersV2;
use app\models\PurchaseOrdersV2Search;
use app\models\PurchasePlanChangeRecord;
use app\models\PurchaseUser;
use app\services\CommonServices;
use Yii;
use yii\filters\VerbFilter;
use app\models\PurchaseLog;
use app\models\PurchaseOrderShip;
use app\models\PurchaseNote;
use app\models\PurchaseTemporary;
use app\models\PurchaseSuggest;
use app\services\BaseServices;
use yii\helpers\Json;
use yii\web\UploadedFile;

/**
 * Created by PhpStorm.
 * User: ztt
 * Date: 2017/3/23 0023
 * Time: 18:41
 */
class PurchaseOrdersV2Controller extends BaseController
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
     * Lists all PurchaseOrder models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PurchaseOrdersV2Search();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $grade=PurchaseUser::findOne(['pur_user_id'=>Yii::$app->user->id]);

        return $this->render('index', [
            'grade'=>$grade,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 修改采购确认
     * @return \yii\web\Response
     */
    public function actionSubmitAudit()
    {
        $model        = new PurchaseOrdersV2();
        $model_ship   = new PurchaseOrderShip();
        $model_note   = new PurchaseNote();
        $model_orders = new PurchaseOrderOrders();

        $page = (int)$_REQUEST['page'];

        if (Yii::$app->request->isPost)
        {
            $PurchaseOrder      = Vhelper::changeData(Yii::$app->request->post()['PurchaseOrdersV2']);
            $purchaseOrderItems = Vhelper::changeData(Yii::$app->request->post()['purchaseOrderItems']);
            $PurchaseOrderShip  = Vhelper::changeData(Yii::$app->request->post()['PurchaseOrderShip']);
            $PurchaseNote       = Vhelper::changeData(Yii::$app->request->post()['PurchaseNote']);
            $Purchase_orders       = Vhelper::changeData(Yii::$app->request->post()['PurchaseOrderOrders']);
            $transaction=\Yii::$app->db->beginTransaction();
            try {
                $model->PurchaseOrder($PurchaseOrder);
                $model->PurchaseOrderItems($purchaseOrderItems);
                $model_ship->saveShip($PurchaseOrderShip);
                $model_note->saveNotes($PurchaseNote);
                $model_orders->saveOrders($Purchase_orders);

                $transaction->commit();

                Yii::$app->getSession()->setFlash('success','恭喜操作成功');
                return $this->redirect(['index','page'=>$page]);
            }catch (Exception $e) {
                $transaction->rollBack();

                $data['type']=4;
                $data['pid']='';
                $data['pur_number']='';
                $data['module']='采购管理';
                $pur_num=implode(',',Yii::$app->request->post()['PurchaseOrdersV2']['pur_number']);
                $data['content']="确认采购单数据：单号( $pur_num ),数据异常！【失败】！";
                Vhelper::setOperatLog($data);


                Yii::$app->getSession()->setFlash('error','数据异常！保存失败');
                return $this->redirect(['index','page'=>$page]);
            }

        } else {
            $id = Yii::$app->request->get('id');
            $id= is_string($id) && strpos($id,',') ? explode(',',$id) : $id;

            $map['pur_domestic_purchase_order.id']       = is_string($id) ? (array)$id : $id;
            $map['pur_domestic_purchase_order.purchas_status'] =1;
            $models     = PurchaseOrdersV2::find()->joinWith(['purchaseOrderItems','orderNote','orderOrders','orderShip'])->where($map)->all();

            if (!empty($models)){
                return $this->renderAjax('attributes', [
                    'models' => $models,
                    'page' =>$page,
                    'id' =>$id,
                    'model_ship'=>$model_ship,
                ]);
            } else {
                $msg= '存在重复进行采购确认的采购单.请勿勾选状态为采购确认的单据';

                $data['type']=4;
                $data['pid']='';
                $data['module']='采购管理';
                $data['content']=$msg;
                Vhelper::setOperatLog($data);

                Yii::$app->session->setFlash('error',$msg);
                return $this->redirect(['index','page'=>$page]);
            }

        }
    }

    /**
     * 撤销确认
     * @return \yii\web\Response
     */
    public function actionRevokeConfirmation()
    {
        $ids = Yii::$app->request->get('ids');
        $result = PurchaseOrdersV2::UpdatePurchaseStatus($ids,1);
        if ($result) {
            Yii::$app->getSession()->setFlash('success', '恭喜你,撤销确认成功', true);
        }else{
            Yii::$app->getSession()->setFlash('error', '没有你要撤销的确认采购单');
        }
        return $this->redirect(['index']);
    }

    /**
     * 撤销采购单  此单当作废
     * @return \yii\web\Response
     */
    public function actionRevokePurchaseOrder()
    {

        $ids = Yii::$app->request->get('ids');

        $model  = PurchaseOrdersV2::find()->where(['id'=>$ids,'purchas_status'=>1])->all();
        //$transaction=\Yii::$app->db->beginTransaction();
        try {
            if($model)
            {
                //撤销采购单
                PurchaseOrdersV2::UpdatePurchaseStatus($ids,4);
                foreach ($model as $v)
                {
                    $demand = PurchaseDemand::find()->where(['in','pur_number',$v->pur_number])->all();
                    $number ='';
                    if($demand)
                    {
                        foreach($demand as $b)
                        {
                            $findone=PurchaseDemand::find()->where(['pur_number'=>$v->pur_number])->one();
                            if($findone){
                                $findone->delete();
                            }
                            $number.= $b->demand_number.',';
                        }
                    }
                }

                if($number)
                {
                    $number=rtrim($number,',');
                    Yii::$app->getSession()->setFlash('success', "恭喜你,撤销采购单成功( $number ),此单作废,请重新再提需求");
                } else {
                    Yii::$app->getSession()->setFlash('success', '恭喜你,撤销采购单成功,此单作废', true);
                }

            } else {
                Yii::$app->getSession()->setFlash('error', '恭喜你,撤销失败了,只有未确认的才能撤销', true);

            }
            //$transaction->commit();
        }catch (Exception $e) {
            //$transaction->rollBack();
        }
        return $this->redirect(['index']);

    }

    public function actionAddTemporary()
    {
        $id = Yii::$app->request->post()['id'];
        if(empty($id))
        {
            return json_encode(['code'=>0,'msg'=>'哦喔！请选择产品']);
        }
        $id = strpos($id,',')?explode(',',$id):$id;
        if (!is_string($id))
        {
            foreach($id as $v)
            {
                if(!empty($v))
                {
                    $model            = new PurchaseTemporary;
                    $model->product_id       = $v;
                    $model->sku             = Product::find()->select('sku')->where(['id'=>$v])->scalar();
                    $model->create_id = Yii::$app->user->id;
                    $status = $model->save(false);
                }

            }
        } else {
            $model            = new PurchaseTemporary;
            $model->product_id       = $id;
            $model->sku             = Product::find()->select('sku')->where(['id'=>$id])->scalar();
            $model->create_id = Yii::$app->user->id;
            $status = $model->save(false);
        }

        if($status){
            return json_encode(['code'=>1,'msg'=>'恭喜你,产品添加成功']);
        }else{
            return json_encode(['code'=>0,'msg'=>'哦喔！产品添加失败了']);
        }
    }
    /**
     * 手动创建采购单
     * @return string
     */
    public function actionAddproduct()
    {
        $ordermodel  = new PurchaseOrdersV2();
        $purchasenote = new PurchaseNote();

        if(!empty($_POST['PurchaseOrdersV2']))
        {
            $purdesc=$_POST['PurchaseOrdersV2'];
            //生成采购订单主表、详情表数据
            $orderdata['purdesc']=$purdesc;
            $transaction=\Yii::$app->db->beginTransaction();
            try {
                $pur_number = $ordermodel::Savepurdata($orderdata);
                //加入备注
                $PurchaseNote=[
                    'pur_number'=>$pur_number,
                    'note'      =>$_POST['PurchaseNote']['note'],
                ];
                $purchasenote->saveNote($PurchaseNote);
                $ordermodel::OrderItems($pur_number,$purdesc['items']);
                $transaction->commit();
                Yii::$app->getSession()->setFlash('success', '恭喜你,手动创建采购单成功', true);
                return $this->redirect(['index']);
            }catch (Exception $e) {
                $transaction->rollBack();
                Yii::$app->getSession()->setFlash('error','数据异常！保存失败,请联系管理员');
                return $this->redirect(['index']);
            }
        }
        $temporay= PurchaseTemporary::find()->where(['create_id'=>Yii::$app->user->id])->groupBy('sku')->all();
        return $this->render('addproduct', [

            'purchasenote' =>$purchasenote,
            'ordermodel'=>$ordermodel,
            'temporay'=>$temporay,
        ]);
    }
    public function actionProductIndex()
    {
        $searchModel  = new ProductSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->renderAjax('_orderform',[
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
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
     * 清除产品
     * @return \yii\web\Response
     */
    public function actionEliminate()
    {
        PurchaseTemporary::deleteAll(['create_id'=>Yii::$app->user->id]);
        Yii::$app->getSession()->setFlash('success',"清除成功");
        if(Yii::$app->request->get('flat'))
        {
            return $this->redirect(['platform-summary/create-purchase-order']);
        }
        return $this->redirect(['addproduct']);
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
                $Name[$line_number][5]=Product::find()->select('id')->where(['sku'=>$datas[0]])->scalar();
                $line_number++;
            }
            $statu = Yii::$app->db->createCommand()->batchInsert(PurchaseTemporary::tableName(),['sku','purchase_quantity', 'purchase_price','title','create_id','product_id'], $Name)->execute();
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
     * 修改供应商
     * @return bool|string|\yii\web\Response
     */
    public function actionUpdateSupplier()
    {
        $model=new PurchaseOrdersV2;
        if(Yii::$app->request->isPost)
        {
            $post              = Yii::$app->request->post('PurchaseOrdersV2');
            $supplier_name     = BaseServices::getSupplierName($post['supplier_code']);
            $arrid             = $post['id'];
            $mo                = PurchaseOrdersV2::findOne(['id' => $arrid]);
            $mo->supplier_name = $supplier_name;
            $mo->supplier_code = $post['supplier_code'];
            $mo->save(false);
            Yii::$app->getSession()->setFlash('success', '恭喜你,供应商被你修改成功了！', true);
            return $this->redirect(['index']);
        }else{
            $ids= Yii::$app->request->get('id');
            return $this->render('update-supplier',[
                'model'=>$model,
                'id'=>$ids,
            ]);
        }
    }

    /**
     * 修改备注
     * @param $id
     * @return string|\yii\web\Response
     */
    public function actionEditNote($id)
    {
        $model = PurchaseNote::find()->where(['pur_number'=>$id])->one();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->getSession()->setFlash('success', '恭喜你,备注被你修改成功了！', true);
            return $this->redirect(['index']);
        } else {
            return $this->render('edit-note', [
                'model' => $model,
            ]);
        }
    }

    /**
     * 删除sku
     */
    public function actionEditSku()
    {
        $data =Yii::$app->request->post();
        $model =PurchaseOrderItemsV2::find()->where(['pur_number'=>$data['pur'],'sku'=>$data['sku']])->one();

        if($model->delete())
        {

            $data['type']=3;
            $fmodel=PurchaseOrdersV2::findOne(['pur_number'=>$data['pur']]);
            if(!empty($fmodel)){
                $data['pid']=$fmodel->id;
            }else{
                $data['pid']='';
            }
            $data['pur_number']=$data['pur'];
            $data['module']='采购管理';
            $data['content']="删除SKU($model->sku)数据成功:".OperatLog::subLogstr($model).'恭喜你【成功】！';
            Vhelper::setOperatLog($data);

            exit(json_encode(['code'=>1,'msg'=>'成功']));

        } else{
            $data['type']=3;
            $fmodel=PurchaseOrdersV2::findOne(['pur_number'=>$data['pur']]);
            if(!empty($fmodel)){
                $data['pid']=$fmodel->id;
            }else{
                $data['pid']='';
            }
            $data['pur_number']=$data['pur'];
            $data['module']='采购管理';
            $data['content']="删除SKU($model->sku)数据失败:".OperatLog::subLogstr($model).'恭喜你【失败】！';
            Vhelper::setOperatLog($data);

            exit(json_encode(['code'=>0,'msg'=>'失败']));
        }
    }


    /**
     * 采购审核-审核操作
     * Displays a single PurchaseOrder model.
     * @return mixed
     */
    public  function actionReview()
    {

        $page = $_REQUEST['page'];

        if (Yii::$app->request->isAjax)
        {
            $id = Yii::$app->request->get('id');
            $map['pur_domestic_purchase_order.id'] = $id;
            //单个审核
            $ordersitmes = PurchaseOrdersV2::find()->joinWith(['purchaseOrderItems','supplier','orderNote'])->where($map)->one();

            $grade=PurchaseUser::findOne(['pur_user_id'=>Yii::$app->user->id]);

            if(!empty($id)){
                $ponumber=PurchaseOrdersV2::find()->select('pur_number')->where(['id'=>$id])->scalar();
                $log=OperatLog::findAll(['pur_number'=>$ponumber]);
            }

            return $this->renderAjax('review', [
                'model' =>$ordersitmes,
                'page' =>$page,
                'grade' =>$grade,
                'log' =>$log,
                'name'  =>Yii::$app->request->get('name'),
            ]);


        } elseif(Yii::$app->request->isPost) {
            $id                                 = Yii::$app->request->post()['PurchaseOrdersV2']['id'];
            $purchas_status                     = Yii::$app->request->post()['PurchaseOrdersV2']['purchas_status'];
            $audit_note                         = Yii::$app->request->post()['PurchaseOrdersV2']['audit_note'];
            $ordersitmes                        = PurchaseOrdersV2::find()->where(['in','id',$id])->all();

            $grader=PurchaseUser::findOne(['pur_user_id'=>Yii::$app->user->id]);

            foreach ($ordersitmes as $ordersitem)
            {
                if ($purchas_status==3)
                {
                    //更新在途库存 暂时关闭
                    //$mods = PurchaseOrderItems::getSKUc($ordersitem->pur_number);
                    //Stock::saveStock($mods,$ordersitem->warehouse_code);
                    //已审核
                    $ordersitem->purchas_status=3;
                    $ordersitem->pay_status=1;

                    $ordersitem->audit_return  =2;
                    $ordersitem->audit_note    =$audit_note;
                    $ordersitem->audit_time    =date('Y-m-d H:i:s');

                    if(!empty($grader) && $grader->grade){
                        $grade=$grader->grade;

                        $range=GroupAuditConfig::findOne(['group'=>$grade]);

                        if($grade==3){
                            $ordersitem->all_status=5;
                            $ordersitem->review_status=3;
                        }

                        if($grade==2){
                            if(!empty($range) && $range->values && $ordersitem->total_price<=$range->values){
                                $ordersitem->all_status=5;
                                $ordersitem->review_status=3;
                            }else{
                                $ordersitem->all_status=4;
                                $ordersitem->review_status=$grade;
                            }
                        }

                        if($grade==1){
                            if(!empty($range) && $range->values && $ordersitem->total_price<=$range->values){
                                $ordersitem->all_status=5;
                                $ordersitem->review_status=3;
                            }else{
                                $ordersitem->all_status=3;
                                $ordersitem->review_status=$grade;
                            }
                        }

                        $remarks=date('Y-m-d H:i:s').' '.Yii::$app->params['grade'][$grade].':'.Yii::$app->user->identity->username.' 审核';
                        if(!empty($ordersitem->review_remarks)){
                            $ordersitem->review_remarks=$ordersitem->review_remarks.','.$remarks;
                        }else{
                            $ordersitem->review_remarks=$remarks;
                        }
                    }else{
                        $ordersitem->all_status=5;
                        $ordersitem->review_status=3;
                        $ordersitem->review_remarks=date('Y-m-d H:i:s').' '.Yii::$app->user->identity->username.' 审核';
                    }

                    //采购单日志添加
                    $s = [
                        'pur_number' => $ordersitem->pur_number,
                        'note'       =>'采购审核通过',
                    ];
                    PurchaseLog::addLog($s);
                } elseif ($purchas_status==4) {
                    //审核退回标志
                    $ordersitem->purchas_status=1;
                    $ordersitem->audit_return =1;

                    $ordersitem->audit_note    =$audit_note;

                    //回退采购状态到待确认
                    if(!empty($grader) && $grader->grade){
                        $grade=$grader->grade;

                        if($grade==3){
                            $ordersitem->all_status=12;
                        }

                        if($grade==2){
                            $ordersitem->all_status=11;
                        }

                        if($grade==1){
                            $ordersitem->all_status=10;
                        }

                        $remarks=date('Y-m-d H:i:s').' '.Yii::$app->params['grade'][$grade].':'.Yii::$app->user->identity->username.' 审核';
                        if(!empty($ordersitem->review_remarks)){
                            $ordersitem->review_remarks=$ordersitem->review_remarks.','.$remarks;
                        }else{
                            $ordersitem->review_remarks=$remarks;
                        }
                    }else{
                        $ordersitem->all_status=1;
                        $ordersitem->review_remarks=date('Y-m-d H:i:s').' '.Yii::$app->user->identity->username.' 审核';
                    }

                    //采购单日志添加
                    $s = [
                        'pur_number' => $ordersitem->pur_number,
                        'note'       =>'采购审核回退至采购确认',
                    ];
                    PurchaseLog::addLog($s);
                } else {
                    //复审3
                    $ordersitem->audit_return  =2;
                    $ordersitem->audit_note    =$audit_note;
                    //采购单日志添加
                    $s = [
                        'pur_number' => $ordersitem->pur_number,
                        'note'       =>'采购复审',
                    ];
                    PurchaseLog::addLog($s);

                }

                $ordersitem->save(false);

                $data['type']=6;
                $data['pid']=$ordersitem->id;
                $data['pur_number']=$ordersitem->pur_number;
                $data['module']='采购管理';
                $data['content']='审核数据-'.$s['note'].'：'.OperatLog::subLogstr($ordersitem).' 【成功】！';
                Vhelper::setOperatLog($data);

                if($ordersitem->purchas_status==3 && $ordersitem->review_status==3){//更新采购建议的状态
                    $poi = PurchaseOrderItemsV2::getSKUc($ordersitem->pur_number);
                    if(!empty($poi)){
                        foreach ($poi as $pv){
                            $suggest_model=PurchaseSuggest::findOne(['sku'=>$pv['sku']]);
                            if(empty($suggest_model)) continue;
                            $suggest_model->state=2;
                            $suggest_model->save();
                        }
                    }
                }

            }

            Yii::$app->getSession()->setFlash('success','恭喜你,审核成功');
            return $this->redirect(['index','page'=>$page]);
        }

    }

    /**
     * 批量审核
     */
    public  function actionAllReview()
    {
        $page=$_REQUEST['page'];
        if (Yii::$app->request->isAjax)
        {
            $id = Yii::$app->request->get('id');
            $map['pur_domestic_purchase_order.id'] = $id;
            $ordersitmes = PurchaseOrdersV2::find()
                ->joinWith(['purchaseOrderItems','supplier','orderNote'])
                ->where($map)->andWhere(['<','purchas_status','4'])
                ->andWhere(['>','purchas_status','1'])
                ->andWhere(['<','review_status','3'])
                ->asArray()->all();
            $grade=PurchaseUser::findOne(['pur_user_id'=>Yii::$app->user->id]);

            return $this->renderAjax('batch-review', [
                'page' =>$page,
                'grade' =>$grade,
                'model' =>$ordersitmes,
                'name'  =>Yii::$app->request->get('name'),
            ]);
        }else{

            $id                                 = Yii::$app->request->post()['PurchaseOrdersV2']['id'];
            $purchas_status                     = Yii::$app->request->post()['PurchaseOrders']['purchas_status'];
            $b= Vhelper::changeData(Yii::$app->request->post()['PurchaseOrdersV2']);
            $ordersitmes                        = PurchaseOrdersV2::find()->where(['in','id',$id])->all();

            $grader=PurchaseUser::findOne(['pur_user_id'=>Yii::$app->user->id]);

            foreach ($ordersitmes as $k=>$ordersitem)
            {
                if ($purchas_status==3)
                {
                    //更新在途库存 暂时关闭
                    //$mods = PurchaseOrderItems::getSKUc($ordersitem->pur_number);
                    //Stock::saveStock($mods,$ordersitem->warehouse_code);
                    //已审核
                    $ordersitem->purchas_status=3;
                    $ordersitem->pay_status=1;
                    $ordersitem->audit_return  =2;
                    $ordersitem->audit_note    =$b[$k]['audit_note'];
                    $ordersitem->audit_time    =date('Y-m-d H:i:s');

                    if(!empty($grader) && $grader->grade){
                        $grade=$grader->grade;

                        $range=GroupAuditConfig::findOne(['group'=>$grade]);

                        if($grade==3){
                            $ordersitem->all_status=5;
                            $ordersitem->review_status=3;
                        }

                        if($grade==2){
                            if(!empty($range) && $range->values && $ordersitem->total_price<=$range->values){
                                $ordersitem->all_status=5;
                                $ordersitem->review_status=3;
                            }else{
                                $ordersitem->all_status=4;
                                $ordersitem->review_status=$grade;
                            }
                        }

                        if($grade==1){
                            if(!empty($range) && $range->values && $ordersitem->total_price<=$range->values){
                                $ordersitem->all_status=5;
                                $ordersitem->review_status=3;
                            }else{
                                $ordersitem->all_status=3;
                                $ordersitem->review_status=$grade;
                            }
                        }

                        $remarks=date('Y-m-d H:i:s').' '.Yii::$app->params['grade'][$grade].':'.Yii::$app->user->identity->username.' 审核';
                        if(!empty($ordersitem->review_remarks)){
                            $ordersitem->review_remarks=$ordersitem->review_remarks.','.$remarks;
                        }else{
                            $ordersitem->review_remarks=$remarks;
                        }
                    }else{
                        $ordersitem->all_status=5;
                        $ordersitem->review_status=3;
                        $ordersitem->review_remarks=date('Y-m-d H:i:s').' '.Yii::$app->user->identity->username.' 审核';
                    }

                    //采购单日志添加
                    $s = [
                        'pur_number' => $ordersitem->pur_number,
                        'note'       =>'采购审核通过',
                    ];
                    PurchaseLog::addLog($s);
                } elseif ($purchas_status==4) {
                    //审核退回标志
                    $ordersitem->purchas_status=1;
                    $ordersitem->audit_return =1;

                    $ordersitem->audit_note    =$b[$k]['audit_note'];

                    //回退采购状态到待确认
                    if(!empty($grader) && $grader->grade){
                        $grade=$grader->grade;
                        if($grade==3){
                            $ordersitem->all_status=12;
                        }

                        if($grade==2){
                            $ordersitem->all_status=11;
                        }

                        if($grade==1){
                            $ordersitem->all_status=10;
                        }

                        $remarks=date('Y-m-d H:i:s').' '.Yii::$app->params['grade'][$grade].':'.Yii::$app->user->identity->username.' 审核';
                        if(!empty($ordersitem->review_remarks)){
                            $ordersitem->review_remarks=$ordersitem->review_remarks.','.$remarks;
                        }else{
                            $ordersitem->review_remarks=$remarks;
                        }
                    }else{
                        $ordersitem->all_status=1;
                        $ordersitem->review_remarks=date('Y-m-d H:i:s').' '.Yii::$app->user->identity->username.' 审核';
                    }

                    //采购单日志添加
                    $s = [
                        'pur_number' => $ordersitem->pur_number,
                        'note'       =>'批量采购审核回退至采购确认',
                    ];
                    PurchaseLog::addLog($s);
                } else {
                    //复审3
                    $ordersitem->audit_return  =2;
                    $ordersitem->audit_note    =$b[$k]['audit_note'];
                    //采购单日志添加
                    $s = [
                        'pur_number' => $ordersitem->pur_number,
                        'note'       =>'批量采购复审',
                    ];
                    PurchaseLog::addLog($s);
                }

                $ordersitem->save(false);

                $data['type']=6;
                $data['pid']=$ordersitem->id;
                $data['pur_number']=$ordersitem->pur_number;
                $data['module']='采购管理';
                $data['content']='审核数据-'.$s['note'].'：'.OperatLog::subLogstr($ordersitem).' 【成功】！';
                Vhelper::setOperatLog($data);

                if($ordersitem->purchas_status==3 && $ordersitem->review_status==3){//更新采购建议的状态
                    $poi = PurchaseOrderItemsV2::getSKUc($ordersitem->pur_number);
                    if(!empty($poi)){
                        foreach ($poi as $pv){
                            $suggest_model=PurchaseSuggest::findOne(['sku'=>$pv['sku']]);
                            if(empty($suggest_model)) continue;
                            $suggest_model->state=2;
                            $suggest_model->save();
                        }
                    }
                }

            }

            Yii::$app->getSession()->setFlash('success','恭喜你,操作成功');
            return $this->redirect(['index','page'=>$page]);
        }
    }

    /**
     * 申请付款
     * @return string
     */
    public function actionPayment()
    {
        $model  = new PurchaseOrderPay();
        $session = Yii::$app->session;
        $session->open();

        $page = (int)$_REQUEST['page'];

        if (Yii::$app->request->isPost)
        {
            if(Yii::$app->request->post('paytoken')==$session->get('paytoken')){//防重复提交
                $prm= PurchaseOrdersV2::find()->where(['pur_number'=>Yii::$app->request->post()['PurchaseOrderPay']['po_code']])->one();

                $paymodel=PurchaseOrderPay::findOne(['pur_number'=>Yii::$app->request->post()['PurchaseOrderPay']['po_code']]);
                $model=$paymodel ? $paymodel : new PurchaseOrderPay();
                $model->requisition_number = $paymodel ? $paymodel->pur_number : CommonServices::getNumber('PP');
                $model->pur_number         = Yii::$app->request->post()['PurchaseOrderPay']['po_code'];
                $model->settlement_method  = $prm->account_type;
                $model->supplier_code      = $prm->supplier_code;
                //已申请付款(待审批)
                $model->pay_status         = 2;
                $model->pay_name           = '采购费用';
                $model->pay_price          = PurchaseOrderItemsV2::find()->where(['pur_number'=>Yii::$app->request->post()['PurchaseOrderPay']['po_code']])->sum('items_totalprice');
                //$model->create_notice      = $prm->confirm_note;
                $model->currency           = $prm->currency_code;
                $model->pay_type           = $prm->pay_type;
                //未到货
                $prm->purchas_status       = 7;
                $prm->all_status         = 6;

                //已申请付款(待审批)
                $prm->pay_status           = 2;
                //填写采购日志
                $s = [
                    'pur_number'=>Yii::$app->request->post()['PurchaseOrderPay']['po_code'],
                    'note' =>'申请付款',
                ];
                PurchaseLog::addLog($s);
                $prm->save(false);
                $model->save(false);

                PurchaseOrdersV2::SaveForders($prm);

                $session->remove('paytoken');

                $data['type']=7;
                $data['pid']=$prm->id;
                $data['pur_number']=$prm->pur_number;
                $data['module']='采购管理';
                $data['content']='申请数据-'.$s['note'].'：'.OperatLog::subLogstr($prm).' 【成功】！';
                Vhelper::setOperatLog($data);


                Yii::$app->getSession()->setFlash('success','恭喜您，申请付款成功，等待出纳付款');
                return $this->redirect(['index']);
            }else{
                Yii::$app->getSession()->setFlash('error',Yii::t('app','请不要重复提交'));
                return $this->redirect(['index']);
            }

        } else {
            $session->set('paytoken','paytoken'.time());

            $pur_number = Yii::$app->request->get('pur_number');
            $models     = PurchaseOrdersV2::find()->joinWith(['purchaseOrderItems','supplier'])->where(['pur_domestic_purchase_order.pur_number'=>$pur_number])->one();
            return $this->renderAjax('payment',['models' =>$models,'model'=>$model,'page'=>$page]);
        }

    }

    /**
     *批量付款申请
     * @return string|\yii\web\Response
     */
    public function actionAllpayment(){
        $model  = new PurchaseOrderPay();

        $session = Yii::$app->session;
        $session->open();

        $page = (int)$_REQUEST['page'];

        if (Yii::$app->request->isPost)
        {
            if(Yii::$app->request->post('allpaytoken')==$session->get('allpaytoken')){//防重复提交

                $post=Yii::$app->request->post();
                //多条付款申请记录入库
                $pum   = array_unique($post['pnum_pto']);
                $prm   = PurchaseOrdersV2::find()->where(['in','pur_number',$pum])->all();
                foreach($prm as $kp=>$vp){

                    $paymodel=PurchaseOrderPay::findOne(['pur_number'=>$vp->pur_number]);

                    $model=$paymodel ? $paymodel : new PurchaseOrderPay();

                    $model->requisition_number = $paymodel ? $vp->pur_number : CommonServices::getNumber('PP');

                    $model->pur_number         = $vp->pur_number;
                    $model->settlement_method  = $vp->account_type;
                    $model->supplier_code      = $vp->supplier_code;
                    //已申请付款(待审批)
                    $model->pay_status         = 2;
                    $model->pay_name           = '采购费用';
                    $model->pay_price          = PurchaseOrderItemsV2::find()->where(['pur_number'=>$vp->pur_number])->sum('items_totalprice');
                    $model->create_notice      = $vp->confirm_note;
                    $model->currency           = $vp->currency_code;
                    $model->pay_type           = $vp->pay_type;
                    //未到货
                    $vp->purchas_status       = 7;
                    $vp->all_status         = 6;

                    //已申请付款(待审批)
                    $vp->pay_status           = 2;
                    $s = [
                        'pur_number'=>$vp->pur_number,
                        'note' =>'批量付款',
                    ];
                    PurchaseLog::addLog($s);
                    $vp->save(false);
                    $model->save(false);

                    PurchaseOrdersV2::SaveForders($vp);

                    $data['type']=7;
                    $data['pid']=$vp->id;
                    $data['pur_number']=$vp->pur_number;
                    $data['module']='采购管理';
                    $data['content']='申请数据-'.$s['note'].'：'.OperatLog::subLogstr($vp).' 【成功】！';
                    Vhelper::setOperatLog($data);

                    $session->remove('allpaytoken');
                }
                Yii::$app->getSession()->setFlash('success','恭喜您，申请付款成功，等待出纳付款');
                return $this->redirect(['index']);
            }else{
                Yii::$app->getSession()->setFlash('error',Yii::t('app','请不要重复提交'));
                return $this->redirect(['index']);
            }

        } else {
            $session->set('allpaytoken','allpaytoken'.time());

            //多条申请付款
            $ids=Yii::$app->request->get('ids');
            if(!empty($ids))
            {
                //$ids    = strpos($ids,',') ? explode(',',$ids):$ids;
                $models = PurchaseOrdersV2::find()
                    ->joinWith(['purchaseOrderItems','orderShip'])
                    ->where(['in', 'pur_domestic_purchase_order.id', $ids])
                    ->andWhere(['pur_domestic_purchase_order.pay_status' => 1, 'pur_domestic_purchase_order.purchas_status' => 3,'pur_domestic_purchase_order.buyer'=>Yii::$app->user->identity->username])
                    ->asArray()
                    ->all();
            } else {
                exit('参数错误');
            }
            if(empty($models))
            {
                exit('付款过了不能重复付款或者你不是此单的采购员');
            }

            return $this->renderAjax('all-payment',[
                'models' =>$models,
                'model'=>$model,
                'page'=>$page,
            ]);
        }

    }

    /**
     * 获取运费和金额
     */
    public  function  actionGetPurchaseAmount()
    {
        //获取运费
        $pay_ship_amount = PurchaseOrderShip::find()->select('sum(freight)')->where(['pur_number'=>Yii::$app->request->post()['ast'][0]])->scalar();

        $pay_ship_amount=!empty($pay_ship_amount) ? round($pay_ship_amount,2) : 0;

        //统计金额
        /* $price = PurchaseOrderItems::find()->select('items_totalprice')->where(['pur_number'=>Yii::$app->request->post()['ast'][0]])->asArray()->all();

         $price = ArrayHelper::getColumn($price,'items_totalprice');


         $price = array_sum($price);*/

        $price = PurchaseOrderItemsV2::find()->select('sum(items_totalprice)')->where(['pur_number'=>Yii::$app->request->post()['ast'][0]])->scalar();

        $price=!empty($price) ? round($price,2) : 0;

        if (Yii::$app->request->post()['ast'][1] && Yii::$app->request->post()['ast'][2])
        {
            //sku金额加上运费
            $data['status'] =1;
            $data['amount'] =$pay_ship_amount + $price;

        } elseif(Yii::$app->request->post()['ast'][1]){
            //sku金额
            $data['status'] =1;
            $data['amount'] = $price;
        } else {
            //运费
            $data['status'] =1;
            $data['amount'] =$pay_ship_amount;
        }
        echo Json::encode($data);

    }

    public  function actionDetails()
    {
        $id = Yii::$app->request->get('id');
        $map['pur_domestic_purchase_order.id'] = $id;
        //单个审核
        $ordersitmes = PurchaseOrdersV2::find()->joinWith(['purchaseOrderItems','supplier','orderNote'])->where($map)->one();

        $grade=PurchaseUser::findOne(['pur_user_id'=>Yii::$app->user->id]);

        if(!empty($id)){
            $ponumber=PurchaseOrdersV2::find()->select('pur_number')->where(['id'=>$id])->scalar();
            $log=OperatLog::findAll(['pur_number'=>$ponumber]);
        }

        $page = (int)$_REQUEST['page'];

        return $this->renderAjax('details', [
            'model' =>$ordersitmes,
            'page' =>$page,
            'grade' =>$grade,
            'log' =>$log,
            'name'  =>Yii::$app->request->get('name'),
        ]);
    }

    /**
     * 打印采购单
     * @return string
     */
    public  function actionPrintData()
    {
        $ids             = Yii::$app->request->get('ids');
        $map['pur_domestic_purchase_order.id']       = strpos($ids,',') ? explode(',',$ids):$ids;
        $ordersitmes     = PurchaseOrdersV2::find()->joinWith(['purchaseOrderItems','supplier'])->where($map)->all();
        return $this->renderPartial('print', ['ordersitmes'=>$ordersitmes]);
    }

    /**
     * 获取跟踪记录
     * @return string
     */
    public  function actionGetTracking()
    {
        $id = Yii::$app->request->get('id');
        $model= PurchaseOrderShip::findAll(['pur_number'=>$id]);
        return $this->renderAjax('get-tracking',['model' =>$model]);
    }

    /**
     * 增加采购备注
     * @return string|\yii\web\Response
     */
    public function actionAddPurchaseNote()
    {
        $model = new PurchaseNote();
        $id = Yii::$app->request->get('pur_number');
        $model_get= PurchaseNote::findAll(['pur_number'=>$id]);

        $page = (int)$_REQUEST['page'];

        if ($model->load(Yii::$app->request->post()) && $model->save(false))
        {

            $data['type']=1;
            $data['pid']=$model->id;
            $data['pur_number']=$model->pur_number;
            $data['module']='采购管理';
            $data['content']='添加数据：'.OperatLog::subLogstr($model).' 【成功】！';
            Vhelper::setOperatLog($data);

            $flag = Yii::$app->request->post()['flag'];
            Yii::$app->getSession()->setFlash('success','恭喜你,新增备注成功');
            $data = [
                '1' => Yii::$app->request->referrer,
                '2' => Yii::$app->request->referrer,
                '3' => Yii::$app->request->referrer,
                '4' => Yii::$app->request->referrer,
            ];
            return $this->redirect($data[$flag]);
        } else {
            $pur_number = Yii::$app->request->get('pur_number');
            $flag       = Yii::$app->request->get('flag');
            $models= PurchaseNote::findAll(['pur_number'=>$pur_number]);

            if(!empty($flag) && $flag > 1){
                return $this->render('note',['model' =>$model,'pur_number'=>$pur_number,'flag'=>$flag,'models'=>$models,'model_get' =>$model_get]);
            }else{
                return $this->renderAjax('note',[
                    'model' =>$model,
                    'page' =>$page,
                    'pur_number'=>$pur_number,
                    'flag'=>$flag,
                    'models'=>$models,
                    'model_get' =>$model_get]);
            }
        }
    }

    /**
     * 删除备注 第一条备注不能被删除,当前用户只能删除自己的备注
     * @param $id
     * @return \yii\web\Response
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionDeleteNote($id){
        $model  = PurchaseNote::find()->where(['id'=>$id,'create_id'=>Yii::$app->user->id])->one();
        if($model){
            $model->delete();

            $data['type']=3;
            $data['pid']=$model->id;
            $data['pur_number']=$model->pur_number;
            $data['module']='采购管理';
            $data['content']='删除数据：'.OperatLog::subLogstr($model).' 【成功】！';
            Vhelper::setOperatLog($data);

            exit(json_encode(['code'=>1,'msg'=>'Success']));
        }else{
            exit(json_encode(['code'=>0,'msg'=>'Failure']));
        }
    }
}
