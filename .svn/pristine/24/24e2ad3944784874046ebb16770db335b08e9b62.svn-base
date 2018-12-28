<?php

namespace app\controllers;

use app\api\v1\models\Product;
use app\config\Vhelper;
use app\models\PlatformSummary;
use app\models\ProductDescription;
use app\models\ProductSearch;
use app\models\PurchaseDemand;
use app\models\PurchaseDiscount;
use app\models\PurchaseEstimatedTime;
use app\models\PurchaseOrderAccount;
use app\models\PurchaseOrderItems;
use app\models\PurchaseOrderOrders;
use app\models\PurchasePlanChangeRecord;
use app\models\PurchaseSuggestMrp;
use app\models\Supplier;
use app\models\TablesChangeLog;
use app\services\CommonServices;
use app\services\PurchaseOrderServices;
use Yii;
use app\models\PurchaseOrder;
use app\models\PurchaseOrderSearch;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\PurchaseLog;
use app\models\PurchaseOrderShip;
use app\models\PurchaseNote;
use app\models\PurchaseTemporary;
use app\models\PurchaseSuggest;
use app\services\BaseServices;
use yii\web\UploadedFile;
use m35\thecsv\theCsv;
use app\models\ProductImgDownload;
use app\services\SupplierServices;
use app\models\SupplierQuotes;
use app\models\PurchaseOrderPayType;
use app\models\Template;
use app\models\PurchaseCompact;
use app\models\PurchaseCompactItems;

/**
 * Created by PhpStorm.
 * User: ztt
 * Date: 2017/3/23 0023
 * Time: 18:41
 */
class PurchaseOrderConfirmController extends BaseController
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
        $searchModel = new PurchaseOrderSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    // 确认计划单
    public function actionSubmitAudit()
    {
       // ini_set('memory_limit','2048M');
        Yii::$app->response->format = 'raw';
        $model        = new PurchaseOrder();
        $model_note   = new PurchaseNote();
        $model_order_pay_type = new PurchaseOrderPayType();
        if(Yii::$app->request->isPost) {
            $POST = Yii::$app->request->post();
            $popost = $POST['PurchaseOrder'];
            foreach($popost['supplier_code'] as $cv) {
                $popost['supplier_name'][] = BaseServices::getSupplierName($cv);
            }
            $PurchaseOrder      = Vhelper::changeData($popost);
            $purchaseOrderItems = Vhelper::changeData($POST['purchaseOrderItems']);
            $PurchaseNote       = Vhelper::changeData($POST['PurchaseNote']);

            // 获取订单实际金额：商品总额 + 运费 - 优惠
            foreach($PurchaseOrder as $k => &$v) {
                $skuPrice = 0;
                $v['freight'] = $v['freight'] ? $v['freight'] : 0;
                $v['discount'] = $v['discount'] ? $v['discount'] : 0;
                foreach($purchaseOrderItems as $m => $n) {
                    if($v['pur_number'] == $n['pur_number']) {
                        $skuPrice += $n['totalprice'];
                    }
                }
                $v['real_price'] = $skuPrice + $v['freight'] - $v['discount'];
            }
            $transaction = Yii::$app->db->beginTransaction();

            try {

                $model->PurchaseOrder($PurchaseOrder);                    // 订单主表

                $model_order_pay_type->saveOrderPayType2($PurchaseOrder);  // 订单子表

                $model->PurchaseOrderItems($purchaseOrderItems);          // 订单商品表

                $model_note->saveNotes($PurchaseNote);

                $transaction->commit();
                Yii::$app->getSession()->setFlash('success','恭喜你确认成功');
                return $this->redirect(['index']);

            } catch (Exception $e) {

                $transaction->rollBack();
                Yii::$app->getSession()->setFlash('error','数据异常！保存失败');
                return $this->redirect(['index']);
            }
        } else {
            $id = Yii::$app->request->get('id');
            $id = explode(',', $id);
            $map['pur_purchase_order.id'] = (array)$id;
            $map['pur_purchase_order.purchas_status'] = 1;
            $query = PurchaseOrder::find()
                ->joinWith(['purchaseOrderItems', 'orderNote', 'purchaseOrderPayType'])
                ->where($map);
            $models = $query->all();

            $models_array = $query->asArray()->all();

            $default_supplier_count = [];// 默认供应商个数大于1的采购单
            if($models){
                foreach($models as $key => &$model_value){
                    // 获取SKU的最新供应商
                    $supplier_list = [];// 所有SKU的默认列表
                    if($model_value->purchaseOrderItems){
                        foreach($model_value->purchaseOrderItems as $orderItem ){
                            if(isset($orderItem->defaultSupplier->supplier_code)){
                                $supplier_list[] = $orderItem->defaultSupplier->supplier_code;
                            }
                        }
                    }
                    // 设置 采购单的默认供应商
                    if(count(array_unique($supplier_list)) == 1){
                        $model_value->supplier_code             = $supplier_list[0];
                        $model_value->supplier_name             = Supplier::find()->select('supplier_name')
                            ->where(['supplier_code' => $model_value->supplier_code])
                            ->scalar();
                        $models_array[$key]['supplier_code']    = $model_value->supplier_code;
                        $models_array[$key]['supplier_code']    = $model_value->supplier_name;
                    }elseif(count(array_unique($supplier_list)) > 1){
                        $default_supplier_count[] = $model_value->pur_number;
                    }
                    // 供应商最新的结算方式、支付方式
                    $supplierInfo = Supplier::findOne(['supplier_code' => $model_value->supplier_code]);
                    if($supplierInfo){
                        $model_value->account_type = $supplierInfo->supplier_settlement;// 结算方式
                        $model_value->pay_type     = $supplierInfo->payment_method;

                        $models_array[$key]['account_type'] = $supplierInfo->supplier_settlement;// 结算方式
                        $models_array[$key]['pay_type']     = $supplierInfo->payment_method;
                    }
                }
            }

            $models_json = json_encode($models_array);
            if($models) {
                return $this->render('attributes', [
                    'id' => $id,
                    'models' => $models,
                    'models_json' => $models_json,
                    'default_supplier_count' => implode('<br/>',$default_supplier_count),
                ]);
            } else {
                $msg = '存在重复进行确认的计划单';
                Yii::$app->session->setFlash('error', $msg);
                return $this->redirect(['index']);
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
        $result = PurchaseOrder::UpdatePurchaseStatus($ids,1);
        if ($result) {
            Yii::$app->getSession()->setFlash('success', '恭喜你,撤销确认成功', true);

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
        $id  = strpos($ids,',')?explode(',',$ids):$ids;

        $model  = PurchaseOrder::find()->where(['purchas_status'=>1])->andWhere(['in','id',$id])->all();


        //$transaction=\Yii::$app->db->beginTransaction();
        try {
            if($model)
            {
                //撤销采购单
                PurchaseOrder::UpdatePurchaseStatus($ids,4);

                foreach ($model as $v)
                {
                    $datas =[];
                    $msg             = '在' . date('Y-m-d H:i:s') . '由' . Yii::$app->user->identity->username . '=====' . $v->pur_number . '进行了撤销';
                    $datas['type']    = 11;
                    $datas['pid']     = $ids;
                    $datas['module']  = '采购单撤销';
                    $datas['content'] = $msg;
                    Vhelper::setOperatLog($datas);
                    //获取采购单类型
                    $type = Vhelper::getNumber($v->pur_number);
                    $number ='';
                    //海外 回退需求
                    if($type==2)
                    {
                                $demand = PurchaseDemand::find()->where(['in','pur_number',$v->pur_number])->all();

                                if($demand)
                                {
                                    foreach($demand as $b)
                                    {
                                        //PlatformSummary::updateAll(['is_push'=>0],['demand_number'=>$b->demand_number]);
                                        $findone=PurchaseDemand::find()->where(['pur_number'=>$v->pur_number])->one();
                                        if($findone)
                                        {
                                            $change_content = "delete:删除id值为{$findone->id}的记录";
                                            $change_data = [
                                                'table_name' => 'pur_purchase_demand', //变动的表名称
                                                'change_type' => '3', //变动类型(1insert，2update，3delete)
                                                'change_content' => $change_content, //变更内容
                                            ];
                                            TablesChangeLog::addLog($change_data);

                                            $findone->delete();
                                        }
                                        $number.= $b->demand_number.',';
                                    }

                                } else {
                                    Yii::$app->getSession()->setFlash('error', '恭喜你,撤销失败了,此单不是通过需求创建', true);
                                }
                    } elseif($type==1) {
                       //国内 回退采购建议

                        $number .= PurchaseSuggestMrp::updateAll(['is_purchase'=>'Y','state'=>'0'],['demand_number'=>$v->pur_number]);
                        //表修改日志-更新
                        $change_data = [
                            'table_name' => 'pur_purchase_suggest_mrp', //变动的表名称
                            'change_type' => '2', //变动类型(1insert，2update，3delete)
                            'change_content' => "update:demand_number:{$v->pur_number},is_purchase:=>Y,state:=>0", //变更内容
                        ];
                        TablesChangeLog::addLog($change_data);

                    } else {

                    }

                }
                //Vhelper::dump($number);
                if($number)
                {
                    Yii::$app->getSession()->setFlash('success', '恭喜你,撤销采购单成功,此单作废,请重新再提需求', true);
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
        $id = Yii::$app->request->post('id');
//        $id = Yii::$app->request->post()['id'];
//        $arr = Yii::$app->request->post();
//        return json_encode($arr);
        if(empty($id))
        {
            return json_encode(['code'=>0,'msg'=>'哦喔！请选择产品']);
        }
        $id = strpos($id,',')?explode(',',$id):$id;

        $tran = Yii::$app->db->beginTransaction();
        try {
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
                        //表修改日志-新增
                        $change_content = "insert:新增id值为{$model->id}的记录";
                        $change_data = [
                            'table_name' => 'pur_purchase_temporary', //变动的表名称
                            'change_type' => '1', //变动类型(1insert，2update，3delete)
                            'change_content' => $change_content, //变更内容
                        ];
                        TablesChangeLog::addLog($change_data);
                    }

                }
            } else {
                $model            = new PurchaseTemporary;
                $model->product_id       = $id;
                $model->sku             = Product::find()->select('sku')->where(['id'=>$id])->scalar();
                $model->create_id = Yii::$app->user->id;
                $status = $model->save(false);
                //表修改日志-新增
                $change_content = "insert:新增id值为{$model->id}的记录";
                $change_data = [
                    'table_name' => 'pur_purchase_temporary', //变动的表名称
                    'change_type' => '1', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);
            }
            $tran->commit();
            if($status){
                return json_encode(['code'=>1,'msg'=>'恭喜你,产品添加成功']);
            }else{
                return json_encode(['code'=>0,'msg'=>'哦喔！产品添加失败了']);
            }
        } catch (Exception $e) {
            $tran->rollBack();
            return json_encode(['code'=>0,'msg'=>'哦喔！产品添加失败了']);
        }



    }
    /**
     * 手动创建采购单
     * @return string
     */
    public function actionAddproduct()
    {
        $ordermodel  = new PurchaseOrder();
        $purchasenote = new PurchaseNote();

        if(!empty($_POST['PurchaseOrder']))
        {
            $purdesc=$_POST['PurchaseOrder'];

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
        $transaction=\Yii::$app->db->beginTransaction();
        $user_id = Yii::$app->user->id;
        try {
            //表修改日志-删除
            $change_content = "delete:删除create_id值为{$user_id}的记录";
            $change_data = [
                'table_name' => 'pur_purchase_temporary', //变动的表名称
                'change_type' => '3', //变动类型(1insert，2update，3delete)
                'change_content' => $change_content, //变更内容
            ];
            TablesChangeLog::addLog($change_data);
            PurchaseTemporary::deleteAll(['create_id' => $user_id]);
            $transaction->commit();
        }catch (Exception $e) {
            $transaction->rollBack();
        }
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


            $tran = Yii::$app->db->beginTransaction();
            try {
                $log_array = array_column($Name,0);
                $log_data = implode(',', $log_array);

                //表修改日志-新增
                $change_content = "insert:sku:{$log_data}";
                $change_data = [
                    'table_name' => 'pur_purchase_temporary', //变动的表名称
                    'change_type' => '1', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);
                $statu = Yii::$app->db->createCommand()->batchInsert(PurchaseTemporary::tableName(),['sku','purchase_quantity', 'purchase_price','title','create_id','product_id'], $Name)->execute();
                $tran->commit();
            } catch (\Exception $e) {
                $tran->rollBack();
                Yii::$app->getSession()->setFlash('error','恭喜你，导入失败了！请联系管理员',true);
                return $this->redirect(['addproduct']);
            }

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
        $model=new PurchaseOrder;
        if(Yii::$app->request->isPost)
        {
            $post              = Yii::$app->request->post('PurchaseOrder');
            $supplier_name     = BaseServices::getSupplierName($post['supplier_code']);
            $arrid             = $post['id'];
            $mo                = PurchaseOrder::findOne(['id' => $arrid]);
            $mo->supplier_name = $supplier_name;
            $mo->supplier_code = $post['supplier_code'];

            //表修改日志-更新
            $change_content = TablesChangeLog::updateCompare($mo->attributes, $mo->oldAttributes);
            $change_data = [
                'table_name' => 'pur_purchase_order', //变动的表名称
                'change_type' => '2', //变动类型(1insert，2update，3delete)
                'change_content' => $change_content, //变更内容
            ];
            $tran = yii::$app->db->beginTransaction();
            try {
                TablesChangeLog::addLog($change_data);
                $mo->save(false);
                $tran->commit();
                Yii::$app->getSession()->setFlash('success', '恭喜你,供应商被你修改成功了！', true);
            } catch (Exception $e) {
                $tran->rollBack();
                Yii::$app->getSession()->setFlash('error', '恭喜你,供应商被你修改失败了！', true);

            }

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
        $tran = yii::$app->db->beginTransaction();
        try {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                //表修改日志-更新
                $change_data = [
                    'table_name' => 'pur_purchase_order_note', //变动的表名称
                    'change_type' => '2', //变动类型(1insert，2update，3delete)
                    'change_content' => "update:id:{$model->id}", //变更内容
                ];
                TablesChangeLog::addLog($change_data);
                $tran->commit();

                Yii::$app->getSession()->setFlash('success', '恭喜你,备注被你修改成功了！', true);
                return $this->redirect(['index']);
            } else {
                return $this->render('edit-note', [
                    'model' => $model,
                ]);
            }
        } catch (Exception $e) {
            $tran->rollBack();
            Yii::$app->getSession()->setFlash('error', '恭喜你,供应商被你修改失败了！', true);

        }
    }

    /**
     * 删除sku
     */
    public function actionEditSku()
    {
        $data    = Yii::$app->request->get();
        $model   = PurchaseOrderItems::find()->where(['pur_number'=>$data['pur'],'sku'=>$data['sku']])->one();
        $suggest = PurchaseSuggestMrp::find()->where(['demand_number'=>$data['pur'],'sku'=>$data['sku']])->one();
        if($model)
        {
            $tran = Yii::$app->db->beginTransaction();
            try {

                //表修改日志-删除
                $change_content = "delete:删除id值为{$model->id}的记录,pur_number:{$data['pur']},sku:{$data['sku']}";
                $change_data = [
                    'table_name' => 'pur_purchase_order_items', //变动的表名称
                    'change_type' => '3', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);
                //采购单详细删除成功
                if ($model->delete()) {
                    $datas = [];
                    $msg = '在' . date('Y-m-d H:i:s') . '由' . Yii::$app->user->identity->username . '=====' . $data['pur'] . '----' . $data['sku'] . '进行了单品删除';
                    $datas['type'] = 13;
                    $datas['pid'] = '';
                    $datas['module'] = '采购单单品删除';
                    $datas['content'] = $msg;
                    Vhelper::setOperatLog($datas);
                    //采购单没有有这个采购建议,有的话就更新,没有的话直接返回ture
                    if ($suggest) {
                        $suggest->is_purchase = 'Y';
                        $suggest->state = 0;

                        //表修改日志-更新
                        $change_content = TablesChangeLog::updateCompare($suggest->attributes, $suggest->oldAttributes);
                        $change_data = [
                            'table_name' => 'pur_purchase_suggest_mrp', //变动的表名称
                            'change_type' => '2', //变动类型(1insert，2update，3delete)
                            'change_content' => $change_content, //变更内容
                        ];
                        TablesChangeLog::addLog($change_data);
                        $suggest->save(false);
                        $tran->commit();
                        return true;
                    } else {
                        $tran->commit();
                        return true;
                    }
                    $tran->commit();
                } else {
                    return false;
                }
            } catch (Exception $e) {
                $tran->rollBack();
                return false;
            }
        } else{
            return false;
        }
    }

    /**
     * 查看产品明细
     * Displays a single PurchaseOrder model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
        $map['pur_purchase_order.pur_number']       = strpos($id,',') ? explode(',',$id):$id;
        $model= PurchaseOrder::find()->joinWith(['purchaseOrderItems'])->where($map)->one();
        if (!$model)
        {
            return '信息不存在';
        } else {
            return $this->renderAjax('view', [
                'model' => $model,
            ]);
        }
    }

    /**
     * 采购审核详细确认
     * Displays a single PurchaseOrder model.
     * @param string $id
     * @return mixed
     */
    public function actionViews($id)
    {
        $map['pur_purchase_order.id']       = strpos($id,',') ? explode(',',$id):$id;

        $ordersitmes     = PurchaseOrder::find()->joinWith(['purchaseOrderItems','supplier'])->where($map)->one();

        //var_dump($ordersitmes);die;

        return $this->renderAjax('views', [
            'model' =>$ordersitmes,
        ]);
    }

    /**
     * PHPExcel导出
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function actionExport()
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');
        $orderItems = new PurchaseOrderItems();
        $orderShip = new PurchaseOrderShip();
        $orderOrders = new PurchaseOrderOrders();

        $id = Yii::$app->request->get('id');
        $id = strpos($id,',')?explode(',',$id):$id;
        if (!empty($id))
        {
            $query = PurchaseOrder::find()
                ->joinWith(['purchaseOrderItems', 'orderNote'])
                ->where(['and', ['in', 'purchas_status', 1], ['<>', 'pur_purchase_order.purchase_type', 3]])
                ->andWhere(['in','pur_purchase_order.id',$id]);
        }
        else
        {
            $searchData = \Yii::$app->session->get('PurchaseOrderConfirmSearchData');
            $purchaseOrderSearch = new PurchaseOrderSearch();
            $query = $purchaseOrderSearch->search($searchData, true);
            $query->joinWith(['purchaseOrderItems', 'orderNote'])
                ->andWhere(['in', 'purchas_status', 1]);
        }

/*         $user = ['左良良','张涛涛','刘伟'];
        if(in_array(Yii::$app->user->identity->username,$user))
        {
            if (!empty($id)) {
                $query->andWhere(['in','pur_purchase_order.id',$id]);
            }
        } else {
            if (!empty($id)) {
                $query->andWhere(['and', ['in','pur_purchase_order.id',$id], ['=', 'buyer', Yii::$app->user->identity->username]]);
            } else {
                $query->andWhere(['=', 'buyer', Yii::$app->user->identity->username]);
            }
        } */
        $model = $query->asArray()->all();
        $objectPHPExcel = new \PHPExcel();
        $objectPHPExcel->setActiveSheetIndex(0);

        $n = 0;
        //报表头的输出
        $objectPHPExcel->getActiveSheet()->mergeCells('A1:T1'); //合并单元格
        $objectPHPExcel->getActiveSheet()->mergeCells('A1:A2');
        $objectPHPExcel->getActiveSheet()->setCellValue('A1','采购计划单表');  //设置表标题
        $objectPHPExcel->setActiveSheetIndex(0)->getStyle('A1')->getFont()->setSize(24); //设置字体大小
        $objectPHPExcel->setActiveSheetIndex(0)->getStyle('A1')
            ->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        //表格头的输出  采购需求建立时间、财务审核时间、财务付款时间
        $cell_value = ['序号','图片','PO号','SKU','产品品名','单价','采购数量','总金额（RMB）','产品链接','采购日期','仓库','采购员','供应商名称','SKU数量','运费','结算方式','订单状态','订单号','确认备注','审核备注'];
        foreach ($cell_value as $k => $v) {
            $objectPHPExcel->setActiveSheetIndex(0)->setCellValue(chr($k+65) . '3',$v);
        }
        //设置表头居中
        $objectPHPExcel->getActiveSheet()->getStyle('A3:T3')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //设置数据水平靠左和垂直居中
        $objectPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $objectPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        foreach ( $model as $v )
        {
            // 供应商最新的结算方式、支付方式
            $supplierInfo = Supplier::findOne(['supplier_code' => $v['supplier_code']]);
            if($supplierInfo){
                $v['account_type'] = $supplierInfo->supplier_settlement;// 结算方式
                $v['pay_type']     = $supplierInfo->payment_method;
            }
            foreach ($v['purchaseOrderItems'] as $c=>$vb)
            {
                //明细的输出
                $objectPHPExcel->getActiveSheet()->setCellValue('A'.($n+4) ,$n+1);
                $imgUrl = ProductImgDownload::find()->where(['sku'=>$vb['sku'],'status'=>1])->one();
                $url = !empty($imgUrl) ? $imgUrl->image_url : Vhelper::downloadImg($vb['sku'],$vb['product_img']);
                if(!file_exists($url)){
                    $url = Vhelper::downloadImg($vb['sku'],$vb['product_img']);
                }
                if($url ){
                    $img=new \PHPExcel_Worksheet_Drawing();
                    $img->setPath($url);//写入图片路径
                    $img->setHeight(50);//写入图片高度
                    $img->setWidth(100);//写入图片宽度
                    $img->setOffsetX(2);//写入图片在指定格中的X坐标值
                    $img->setOffsetY(2);//写入图片在指定格中的Y坐标值
                    $img->setRotation(1);//设置旋转角度
                    $img->getShadow()->setDirection(50);//
                    $img->setCoordinates('B'.($n+4));//设置图片所在表格位置
                    $objectPHPExcel->getActiveSheet()->getRowDimension(($n+4))->setRowHeight(80);
                    $img->setWorksheet($objectPHPExcel->getActiveSheet());//把图片写到当前的表格中
                }
                $objectPHPExcel->getActiveSheet()->setCellValue('C'.($n+4) ,$v['pur_number']);
                $objectPHPExcel->getActiveSheet()->setCellValue('D'.($n+4) ,$vb['sku']);
                $objectPHPExcel->getActiveSheet()->setCellValue('E'.($n+4) ,$vb['name']);
                $objectPHPExcel->getActiveSheet()->setCellValue('F'.($n+4) ,$vb['price']);
                $num = (!empty($vb['ctq']) ? $vb['ctq'] : $vb['qty']);
                $objectPHPExcel->getActiveSheet()->setCellValue('G'.($n+4) ,$num);
                $objectPHPExcel->getActiveSheet()->setCellValue('H'.($n+4) ,$vb['price'] * $num);
//                $objectPHPExcel->getActiveSheet()->setCellValue('H'.($n+4) ,round($orderItems->getCountPrice($v['pur_number']),2));
                $plink=!empty($vb['product_link'])?$vb['product_link']:(SupplierQuotes::getUrl($vb['sku']));
                $objectPHPExcel->getActiveSheet()->setCellValue('I'.($n+4) ,$plink);
                $objectPHPExcel->getActiveSheet()->getCell('I'.($n+4))->getHyperlink()->setUrl($plink);
                $objectPHPExcel->getActiveSheet()->setCellValue('J'.($n+4) ,$v['created_at']);
                $objectPHPExcel->getActiveSheet()->setCellValue('K'.($n+4) ,(!empty($v['is_transit']) && $v['is_transit']==1 && $v['transit_warehouse'])?(BaseServices::getWarehouseCode($v['transit_warehouse']) . (!empty($v['warehouse_code'])?'-'.BaseServices::getWarehouseCode($v['warehouse_code']):'<br/>')):(!empty($v['warehouse_code'])?BaseServices::getWarehouseCode($v['warehouse_code']):'<br/>'));
                $objectPHPExcel->getActiveSheet()->setCellValue('L'.($n+4) ,$v['creator']);
                $objectPHPExcel->getActiveSheet()->setCellValue('M'.($n+4) ,$v['supplier_name']);
                $objectPHPExcel->getActiveSheet()->setCellValue('N'.($n+4) ,$orderItems->find()->where(['pur_number'=>$v['pur_number']])->count('id'));
                $objectPHPExcel->getActiveSheet()->setCellValue('O'.($n+4) ,round($orderShip::find()->where(['pur_number'=>$v['pur_number']])->sum('freight'),2));
                $objectPHPExcel->getActiveSheet()->setCellValue('P'.($n+4) ,($v['account_type'] ? (SupplierServices::getSettlementMethod($v['account_type']) ) : '')); //结算方式
                $objectPHPExcel->getActiveSheet()->setCellValue('Q'.($n+4) ,strip_tags(PurchaseOrderServices::getPurchaseStatus($v['purchas_status'])));
                $findone=$orderOrders::findOne(['pur_number'=>$v['pur_number']]);
                $objectPHPExcel->getActiveSheet()->setCellValue('R'.($n+4) ,!empty($findone) ? $findone->order_number : '');
                $objectPHPExcel->getActiveSheet()->setCellValue('S'.($n+4) ,$v['orderNote']['note']);
                $objectPHPExcel->getActiveSheet()->setCellValue('T'.($n+4) ,$v['audit_note']);

                $n = $n +1;
            }
        }

        for ($i = 65; $i<85; $i++) {
            $objectPHPExcel->getActiveSheet()->getColumnDimension(chr($i))->setWidth(15);
            $objectPHPExcel->getActiveSheet()->getStyle( chr($i) . "3")->getFont()->setBold(true);
        }
        $objectPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(6.5);
        $objectPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
        $objectPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(40);

        //设置样式
        $objectPHPExcel->getActiveSheet()->getStyle('B2:H'.($n+4))->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objectPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        ob_end_clean();
        ob_start();
        header("Content-type:application/vnd.ms-excel;charset=UTF-8");
        header('Content-Type : application/vnd.ms-excel');
        header('Content-Disposition:attachment;filename="'.'采购单计划表-'.date("Y年m月j日").'.xls"');
        $objWriter= \PHPExcel_IOFactory::createWriter($objectPHPExcel,'Excel5');
        $objWriter->save('php://output');
        die;
    }
    /**
     * @desc Ajax 修改采购建议数量
     * @author
     * @date 2017-04-13 13:50:11
     */
    public function actionUpdateCtq()
    {
        $sku= Yii::$app->request->get('sku');
        $pur= Yii::$app->request->get('pur');
        $ctq= Yii::$app->request->get('ctq');

        $model=new PurchaseOrderItems();

        //表修改日志-更新
        $change_data = [
            'table_name' => 'pur_purchase_order_items', //变动的表名称
            'change_type' => '2', //变动类型(1insert，2update，3delete)
            'change_content' => "update:sku:{$sku},pur_number:{$pur},ctq:=>{$ctq}", //变更内容
        ];
        $tran = Yii::$app->db->beginTransaction();
        try {
            TablesChangeLog::addLog($change_data);
            $res = $model->updateAll(['ctq' => $ctq], ['sku' => $sku, 'pur_number' => $pur]);
            $tran->commit();
        }catch(\Exception $e){
            $tran->rollBack();
        }
        return $res;
    }

    public function actionPurchaseMerge(){
        $purNumber = Yii::$app->request->getQueryParam('purNumber');
        $data = explode(',',$purNumber);
        $purOrderData = PurchaseOrder::find()->andFilterWhere(['pur_number'=>$data])->groupBy('create_type,purchas_status,warehouse_code,is_transit,transit_warehouse,purchase_type')->asArray()->all();
        $purOrderItemsData = PurchaseOrderItems::find()->select('*,sum(qty) as qty')->andFilterWhere(['pur_number'=>$data])->groupBy('sku')->asArray()->all();
        if(Yii::$app->request->isPost&&Yii::$app->request->isAjax){
            $updateData = Yii::$app->request->getBodyParam('purNumber');
            $updateArray = explode(',',$updateData);
            $tran = Yii::$app->db->beginTransaction();
            try{
                $purchaseNote = new PurchaseNote();
                if(empty($updateArray)||count($updateArray)<2){
                    throw new HttpException(500,'至少合并两个采购单');
                }

                //合并采购单主表
                $pur_number = PurchaseOrder::mergePurchase($updateArray);

                if(empty($pur_number)){
                    throw new HttpException(500,'采购单合并失败！');
                }

                //加入备注
                $PurchaseNote=[
                    'pur_number'=>$pur_number['pur_number'],
                    'note'      =>'合并采购单'.$pur_number['merge_number'],
                ];

                $purchaseNote->saveNote($PurchaseNote);
                //合并采购单附表
                PurchaseOrder::mergeOrderItems($pur_number['pur_number'],$updateArray);

                PurchaseOrder::updateAll(['purchas_status'=>4],['pur_number'=>$updateArray]);

                $log_data = BaseServices::getStrData($updateArray);
                //表修改日志-更新
                $change_data = [
                    'table_name' => 'pur_purchase_order', //变动的表名称
                    'change_type' => '2', //变动类型(1insert，2update，3delete)
                    'change_content' => "update:pur_number:{$log_data},purchas_status:=>4", //变更内容
                ];
                TablesChangeLog::addLog($change_data);


                PurchaseLog::addLog(['pur_number'=>$pur_number['merge_number'],'note'=>'合并生成采购单'.$pur_number['pur_number'].'后撤销']);
                $tran->commit();
                $response = ['status'=>'success','message'=>'采购单合并成功'];
                Yii::$app->getSession()->setFlash('success', $response['message'], true);
                return $this->redirect(['index']);
            }catch(HttpException $e){
                $tran->rollBack();
                $response = ['status'=>'error','message'=>$e->getMessage()];
            }
            echo json_encode($response);
            exit();
        }
        return $this->renderAjax('mergeorder',['orderData'=>$purOrderData,'itemsData'=>$purOrderItemsData,'purNumber'=>$purNumber]);
    }


    /***************合同流程相关的 开始********************/
    // 合同采购确认（通过订单的 source 字段，标识订单的属性）
    public function actionCompactConfirm()
    {
        $model        = new PurchaseOrder();
        $model_note   = new PurchaseNote();
        $model_order_pay_type = new PurchaseOrderPayType();
        if(Yii::$app->request->isPost) {
            $POST = Yii::$app->request->post();
            $baseData = $POST['PurchaseOrder']; // 订单基础数据（多个订单共享）
            $baseData['supplier_name'] = BaseServices::getSupplierName($baseData['supplier_code']);
            $PurchaseOrders     = Vhelper::changeData($POST['PurchaseOrders']); // 多个订单信息
            $PurchaseOrderItems = Vhelper::changeData($POST['PurchaseOrderItems']); // 多个订单sku信息
            $PurchaseNote       = Vhelper::changeData($POST['PurchaseNote']); // 多个订单确认备注
            $pos = [];
            foreach($PurchaseOrders as $k => &$v) {
                $skuPrice = 0;
                $v['freight']  = $v['freight']  ? $v['freight']  : 0; // 运费
                $v['discount'] = $v['discount'] ? $v['discount'] : 0; // 优惠
                foreach($PurchaseOrderItems as $m => $n) {
                    if($v['pur_number'] == $n['pur_number']) {
                        $skuPrice += $n['totalprice'];
                    }
                }
                $v['real_price'] = $skuPrice + $v['freight'] - $v['discount'];
                $v = array_merge($v, $baseData);
                $pos[] = $v['pur_number'];
            }
            $top_pur_number = $pos[0]; // 日志表中记录的采购单号
            $pos = implode(',', $pos);
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $model->PurchaseOrder($PurchaseOrders); // 订单主表
                $model->PurchaseOrderItems($PurchaseOrderItems); // 订单商品表
                $model_order_pay_type->saveOrderPayType3($PurchaseOrders); // 订单子表
                $model_note->saveNotes($PurchaseNote); // 订单备注
                PurchaseLog::addLog([
                    'pur_number' => $top_pur_number,
                    'note' => '采购批量确认了这些单：'.$pos
                ]);

                $transaction->commit();
                return $this->redirect(["create-compact?pos={$pos}"]); // 确认成功后前往合同确认页

            } catch (Exception $e) {
                $transaction->rollBack();
                Yii::$app->getSession()->setFlash('error','数据异常！保存失败');
                return $this->redirect(['index']);
            }
        } else {
            $ids = Yii::$app->request->get('ids');
            if(!$ids) {
                throw new \yii\web\NotFoundHttpException('参数错误');
            }
            $ids = explode(',', $ids);
            $map['pur_purchase_order.id'] = $ids;
            $map['pur_purchase_order.purchas_status'] = 1;
            $models = PurchaseOrder::find()->where($map)->all();
            if(empty($models)) {
                exit('存在重复进行确认的计划单');
            }
            $spc = array_column($models, 'supplier_code');
            $arr = array_unique($spc);
            if(count($arr) > 1) {
                exit('只能选择同一家供应商的订单');
            }
            return $this->render('compact-confirm', [
                'ids' => $ids,
                'models' => $models,
            ]);
        }
    }

    // 国内仓，合同生成，唯一入口
    public function actionCreateCompact()
    {
        $request = Yii::$app->request;
        $pos = $request->get('pos') ? $request->get('pos') : 0; // 订单号
        $tid = $request->get('tid') ? $request->get('tid') : 0; // 模板id
        $model = new PurchaseCompact();
        if($request->isPost) {
            $POST = $request->post();
            $model->attributes = $POST['Compact'];
            if(!$model->validate()) {
                $errors = $model->errors;
                foreach($errors as $v) {
                    echo implode(',', $v)."<br/>";
                }
                exit;
            }
            $system = $POST['System']; // 系统级参数
            if($system['tid'] > 0) {
                $tran = Yii::$app->db->beginTransaction();
                try {
                    $model->compact_number = CommonServices::getNumber('PO-HT');
                    $model->source = 1; // 国内
                    $model->compact_status = 2; // 待审核
                    $model->create_time = date('Y-m-d H:i:s', time());
                    $model->create_person_name = Yii::$app->user->identity->username;
                    $model->create_person_id = Yii::$app->user->id;
                    $model->tpl_id = $system['tid'];
                    $a = $model->save();

                    //表修改日志-新增
                    $change_content = "insert:新增id值为{$model->id}的记录";
                    $change_data = [
                        'table_name' => 'pur_purchase_compact', //变动的表名称
                        'change_type' => '1', //变动类型(1insert，2update，3delete)
                        'change_content' => $change_content, //变更内容
                    ];
                    TablesChangeLog::addLog($change_data);

                    $items = [];
                    $pos = explode(',', $system['pos']);
                    foreach($pos as $pur_number) {
                        $items[] = [
                            'compact_number' => $model->compact_number,
                            'pur_number' => $pur_number
                        ];

                        //表修改日志-新增
                        $change_content = "insert:compact_number=>{$model->compact_number},pur_number=>{$pur_number}";
                        $change_data = [
                            'table_name' => 'pur_purchase_compact_items', //变动的表名称
                            'change_type' => '1', //变动类型(1insert，2update，3delete)
                            'change_content' => $change_content, //变更内容
                        ];
                        TablesChangeLog::addLog($change_data);
                    }
                    $num = Yii::$app->db->createCommand()
                        ->batchInsert('pur_purchase_compact_items', ['compact_number', 'pur_number'], $items)
                        ->execute();

                    // 订单初始化
                    //表修改日志-更新
                    $log_data = implode(',',$pos);
                    $change_data = [
                        'table_name' => 'pur_purchase_order_pay', //变动的表名称
                        'change_type' => '2', //变动类型(1insert，2update，3delete)
                        'change_content' => "update:pur_number:{$log_data},purchas_status:=>2,pay_status:=>1,'source:=>1", //变更内容
                    ];
                    TablesChangeLog::addLog($change_data);
                    $c = PurchaseOrder::updateAll(['purchas_status' => 2, 'pay_status' => 1, 'source' => 1], ['in', 'pur_number', $pos]);

                    // 写合同日志
                    PurchaseLog::addLog([
                        'pur_number' => $model->compact_number,
                        'note' => '创建合同'
                    ]);

                    if($a && $num && $num == count($items) && $c) {
                        $tran->commit();
                        Yii::$app->getSession()->setFlash('success','恭喜你，合同采购确认成功，请等待审核');
                        return $this->redirect(['index']);
                    } else {
                        $tran->rollBack();
                        Yii::$app->getSession()->setFlash('error','对不起，数据保存失败');
                        return $this->redirect(['index']);
                    }
                } catch (\Exception $e) {
                    $tran->rollBack();
                    Yii::$app->getSession()->setFlash('error',$e->getMessage());
                    return $this->redirect(['index']);
                }
            }
        }

        // 新建合同
        if($tid) {
            $result = PurchaseOrder::getCompactGeneralData($pos);
            if($result['error'] > 0) {
                exit($result['message']);
            }
            $tpl = Template::findOne($tid);
            if(empty($tpl)) {
                exit('模板数据不存在');
            }
            $tplPath = $tpl->style_code;
            return $this->render("//template/tpls/{$tplPath}", ['data' => $result['data'], 'pos' => $pos, 'tid' => $tid]);
        }
        $tpls = Template::find()
            ->where(['platform' => 1, 'type' => 'DDHT', 'status' => 1])
            ->asArray()
            ->all();
        return $this->render('create-compact', [
            'tpls' => $tpls,
            'pos' => $pos,
            'model' => $model
        ]);
    }

    // 将订单绑定到合同
    public function actionBindCompact()
    {
        $request = Yii::$app->request;
        if($request->isPost) {
            $POST = $request->post();
            // 接收表单数据
            $system = $POST['System'];
            $order = $POST['PurchaseOrder'];
            $orderNote = $POST['PurchaseNote'];
            $orderPayType = $POST['PurchaseOrderPayType'];
            $PurchaseOrderItems = Vhelper::changeData($POST['PurchaseOrderItems']);
            // 重置金额
            $orderPayType['freight'] = $orderPayType['freight']  ? $orderPayType['freight'] : 0;
            $orderPayType['discount'] = $orderPayType['discount'] ? $orderPayType['discount'] : 0;
            $totalMoney = 0;
            foreach($PurchaseOrderItems as $m => $n) {
                $ctq = $n['ctq'] ? $n['ctq'] : 0;
                $price = $n['price'] ? $n['price'] : 0;
                $totalMoney += $ctq*$price;
            }
            $compactModel = PurchaseCompact::find()->where(['compact_number' => $system['compact_number']])->one();
            $product_money = $compactModel->product_money;
            $product_money += $totalMoney;

            $freight = $compactModel->freight;
            $freight += $orderPayType['freight'];

            $discount = $compactModel->discount;
            $discount += $orderPayType['discount'];

            $real_money = $product_money+$freight-$discount;
            $orderPayType['real_price'] = $totalMoney+$orderPayType['freight']-$orderPayType['discount'];

            // 获取结算计划
            $plan = PurchaseCompact::PaymentPlan3($orderPayType['settlement_ratio'], $totalMoney, $freight, $discount);
            $model = new PurchaseOrder();
            $model_note = new PurchaseNote();
            $model_order_pay_type = new PurchaseOrderPayType();
            $model_compact_items = new PurchaseCompactItems();
            $tran = Yii::$app->db->beginTransaction();
            try {
                $model->PurchaseOrder([$order]);
                $model->PurchaseOrderItems($PurchaseOrderItems);
                $model_order_pay_type->saveOrderPayType3([$orderPayType]);
                $model_note->saveNotes([$orderNote]);
                $compactModel->compact_status = 2; // 有订单关联进合同，合同改为待审核状态
                $compactModel->product_money = $product_money;
                $compactModel->freight = $freight;
                $compactModel->discount = $discount;
                $compactModel->real_money = $real_money;
                $compactModel->dj_money = $plan['dj'];
                $compactModel->wk_money = $plan['wk'];
                $compactModel->wk_total_money = $plan['wwk'];

                //表修改日志-更新
                $change_content = TablesChangeLog::updateCompare($compactModel->attributes, $compactModel->oldAttributes);
                $change_data = [
                    'table_name' => 'pur_purchase_compact', //变动的表名称
                    'change_type' => '2', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);
                $compactModel->save(false);

                // 添加绑定关系
                $mm = PurchaseCompactItems::find()
                    ->where(['compact_number' => $system['compact_number'], 'pur_number' => $system['compact_number']])
                    ->one();
                if(!empty($mm)) {
                    $mm->bind = 1;

                    //表修改日志-更新
                    $change_content = TablesChangeLog::updateCompare($mm->attributes, $mm->oldAttributes);
                    $change_data = [
                        'table_name' => 'pur_purchase_compact_items', //变动的表名称
                        'change_type' => '2', //变动类型(1insert，2update，3delete)
                        'change_content' => $change_content, //变更内容
                    ];
                    TablesChangeLog::addLog($change_data);

                    $mm->save(false);
                } else {
                    $model_compact_items->compact_number = $system['compact_number'];
                    $model_compact_items->pur_number = $system['pur_number'];
                    $model_compact_items->bind = 1;
                    $model_compact_items->save(false);

                    //表修改日志-新增
                    $change_content = "insert:新增id值为{$model_compact_items->id}的记录";
                    $change_data = [
                        'table_name' => 'pur_purchase_compact_items', //变动的表名称
                        'change_type' => '1', //变动类型(1insert，2update，3delete)
                        'change_content' => $change_content, //变更内容
                    ];
                    TablesChangeLog::addLog($change_data);
                }
                PurchaseLog::addLog([
                    'pur_number' => $system['compact_number'],
                    'note' => $system['pur_number'].'关联了这个合同，系统已经重置了合同的各项金额信息'
                ]);
                $tran->commit();
                Yii::$app->getSession()->setFlash('success','恭喜你，订单已经与合同关联，请等待审核');
                return $this->redirect(['index']);
            } catch(\Exception $e) {
                $tran->rollBack();
                Vhelper::dump($e->getMessage());
                Yii::$app->getSession()->setFlash('success','对不起，关联失败了');
                return $this->redirect(['index']);
            }
        } else {
            $opn = $request->get('pur_number');
            $cpn = $request->get('compact_number');
            if(!$cpn || !$opn) {
                exit('对不起，参数错误');
            }
            $cpn = trim($cpn);
            $model = PurchaseCompact::find()
                ->select('id')
                ->where(['compact_number' => $cpn])
                ->andWhere(['<', 'compact_status', 9])
                ->one();
            if(empty($model)) {
                exit('合同不存在或已作废，或者合同处于冻结状态');
            }
            $pay = PurchaseOrderPay::find()->where(['pur_number' => $cpn])->count();
            if($pay > 0) {
                exit('合同已经有过请款记录了');
            }
            $pos = PurchaseCompact::getPurNumbers($cpn);
            $firstModel = PurchaseOrder::find()->where(['pur_number' => $pos[0]])->one();
            $order = PurchaseOrder::find()->where(['pur_number' => $opn])->one();
            $orderItems = $order->purchaseOrderItems;
            $compact = PurchaseCompact::find()
                ->select(['compact_number', 'settlement_ratio'])
                ->where(['compact_number' => $cpn])->one();

            return $this->render('bind-compact', [
                'order' => $order,
                'orderItems' => $orderItems,
                'model' => $firstModel,
                'compact' => $compact,
                'cpn' => $cpn,
                'opn' => $opn
            ]);
        }
    }
    /***************合同流程相关的 结束********************/
    /**
     * 修改采购员
     * @return bool|string|\yii\web\Response
     */
    public function actionEditBuyer(){
        $post = Yii::$app->request->post('PurchaseOrder');
        if(!empty($post)){
            $arrid=explode(',',$post['id']);

            $data['buyer']=$post['buyer'];
            //$data['buyer_id']=$post['buyer'];

            foreach ($arrid as $k=>$v){
                $order_model = PurchaseOrder::findOne(['in','id',$v]);
                $order_model->buyer = $data['buyer'];
                //表修改日志-更新
                $change_content = TablesChangeLog::updateCompare($order_model->attributes, $order_model->oldAttributes);
                $change_data = [
                    'table_name' => 'pur_purchase_order', //变动的表名称
                    'change_type' => '2', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);

                $order_model->save();
                //PurchaseOrder::updateAll($data,['id'=>$v]);
            }

            Yii::$app->getSession()->setFlash('success', '恭喜你,采购员被你修改成功了！', true);
            return $this->redirect(['index']);
        }else{
            $ids= Yii::$app->request->post('id');
            $ids=implode(',',$ids);
            $model=new PurchaseOrder();

            return $this->renderAjax('edit-buyer',[
                'model'=>$model,
                'ids'=>$ids,
            ]);
        }
    }
    /**
     * 打印采购计划单
     */
    public  function actionPrintConfirm()
    {
        $models_json             = Yii::$app->request->get('models_json');
        $models_array = json_decode($models_json);
        return $this->renderPartial('print-comfirm', ['models_array'=>$models_array]);
    }
}
