<?php

namespace app\controllers;

use app\config\Vhelper;
use app\models\Product;
use app\models\PurchaseHistory;
use app\models\PurchaseNote;
use app\models\PurchaseOrderCancel;
use app\models\PurchaseOrderCancelSub;
use app\models\PurchaseOrderRefundQuantity;
use app\models\PurchaseSuggestNote;
use app\models\PurchaseSuggestQuantitySearch;
use app\models\SupplierQuotes;
use app\models\TablesChangeLog;
use app\services\BaseServices;
use Yii;
use app\models\PurchaseSuggest;
use app\models\PurchaseSuggestSearch;
use yii\caching\DbDependency;
use yii\db\Exception;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\PurchaseOrder;
use app\models\PurchaseOrderItems;
use app\services\CommonServices;
use app\models\User;
use app\models\PurchaseLog;
use app\models\Stock;
use yii\db\Query;
use app\models\Warehouse;
use app\models\Supplier;
use app\models\PurchaseSuggestQuantity;
use yii\helpers\FileHelper;

/**
 * PurchaseSuggestController implements the CRUD actions for PurchaseSuggest model.
 * @desc 采购建议
 */
class PurchaseSuggestController extends BaseController
{
    public $purchase_prefix='PO';//采购单前缀
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
     * Lists all PurchaseSuggest models.
     * @return mixed
     */
    public function actionIndex()
    {$startTime1 = microtime(true);
    $startTime2 = microtime(true);
        \yii::$app->response->format = 'raw';
        $searchModel = new PurchaseSuggestSearch();
        $map = Yii::$app->request->queryParams;
        if(!isset($map['PurchaseSuggestSearch']['state'])){
            $map['PurchaseSuggestSearch']['is_purchase']='Y';
        }
        if(!empty($map['PurchaseSuggestSearch']['sales_import'])){
            $searchModel->sales_import = $map['PurchaseSuggestSearch']['sales_import'];
        }
        /*//统计SKU总数量、采购总额、采购数量
        if(!empty($map['PurchaseSuggestSearch']['supplier_name'])){
            $suggestsum=PurchaseSuggest::find()
                ->select(['count(id) as total_snum, sum(qty) as total_num, sum(price) as total_price'])
                ->where(['like', 'supplier_name',$map['PurchaseSuggestSearch']['supplier_name']])
                ->andWhere(['=','is_purchase','Y'])
                ->andWhere(['in','warehouse_code',['DG','SZ_AA']])
                ->asArray()->all();
            $suggestsum['total_prices']=$suggestsum[0]['total_num']*$suggestsum[0]['total_price'];
        }else{
            $suggestsum=PurchaseSuggest::find()
                ->select(['count(id) as total_snum, sum(qty) as total_num, sum(price) as total_price'])
                ->andWhere(['=','is_purchase','Y'])
                ->andWhere(['in','warehouse_code',['DG','SZ_AA']])
                ->asArray()->all();
            $suggestsum['total_prices']=$suggestsum[0]['total_num']*$suggestsum[0]['total_price'];
        }

        $suggestsum[0]['total_num']=$suggestsum[0]['total_num'] ? $suggestsum[0]['total_num'] : 0;*/

        $dataProvider = $searchModel->search($map);
        $endTime2 = microtime(true);
        if (isset($_REQUEST['is_debug']))
            var_dump($endTime2 - $startTime2);
        $startTime3 = microtime(true);
        $query = $searchModel->search($map,true);
        $purchasedata = $query->select(['qty'=>'ifnull(qty,0)','price'=>'ifnull(price,0)'])->asArray()->all();
        $purchaseNum =0;
        $purchaseMoney =0;
        foreach ($purchasedata as $v){
            $purchaseNum+=$v['qty'];
            $purchaseMoney+=$v['qty']*$v['price'];
        }
        Yii::$app->session->set('suggest_total_num',$purchaseNum);
        Yii::$app->session->set('suggest_total_money',$purchaseMoney);
        $endTime3 = microtime(true);
        if (isset($_REQUEST['is_debug']))
            var_dump($endTime3 - $startTime3);
        $startTime4 = microtime(true);
        //产品状态统计
/*         $status['0']=PurchaseSuggest::getStatus(0);
        $status['1']=PurchaseSuggest::getStatus(1);
        $status['2']=PurchaseSuggest::getStatus(2);
        $status['3']=PurchaseSuggest::getStatus(3);
        $status['4']=PurchaseSuggest::getStatus(4);
        $status['5']=PurchaseSuggest::getStatus(5);
        $status['6']=PurchaseSuggest::getStatus(6);
        $status['7']=PurchaseSuggest::getStatus(7);
        $status['8']=PurchaseSuggest::getStatus(8);
        $status['9']=PurchaseSuggest::getStatus(9);
        $status['10']=PurchaseSuggest::getStatus(10);
        $status['11']=PurchaseSuggest::getStatus(11);
        $status['12']=PurchaseSuggest::getStatus(12);
        $status['14']=PurchaseSuggest::getStatus(14);
        $status['15']=PurchaseSuggest::getStatus(15);
        $status['16']=PurchaseSuggest::getStatus(16);
        $status['total']=PurchaseSuggest::getStatus(99,'1'); */
        $status = PurchaseSuggest::getStatusStatistics();
        $warehouseList = \app\services\BaseServices::getWarehouseCode();
        $endTime4 = microtime(true);
        if (isset($_REQUEST['is_debug']))
            var_dump($endTime4 - $startTime4);
        $startTime5 = microtime(true);
        $sug_total = PurchaseSuggest::getLeadSuggests()->count();
        $endTime5 = microtime(true);
        if (isset($_REQUEST['is_debug']))
            var_dump($endTime5 - $startTime5);
        $endTime1 = microtime(true);
        if (isset($_REQUEST['is_debug']))
            var_dump($endTime1 - $startTime1);
        return $this->render('index', [
            //'suggestsum' => $suggestsum,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'status' => $status,
            'sug_total' => $sug_total,
            'warehouseList' => $warehouseList
        ]);
    }


    /**
     * Finds the PurchaseSuggest model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return PurchaseSuggest the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PurchaseSuggest::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    /**
     * @desc 根据选中的采购建议生成相应的采购单，对选中的数据进行验证：同一供应商，同一仓库的才能生成同一个采购单
     * @author Jimmy
     * @date 2017-04-17 14:33:11
     */
    public function actionCreatePurchase()
    {

        if(Yii::$app->request->isAjax||Yii::$app->request->isGet){
            $res=[];
            $this->checkPurchaseData($res);
            $model=new User();
            $users=$model->find()->all();
            
            $data=Yii::$app->request->get();
            //var_dump($data);
            //exit;

            
            return $this->renderAjax('create-purchase', ['data' => $res,'users'=>$users]);
        } elseif(Yii::$app->request->isPost) {
            //echo '1';
            //exit;
            $data=Yii::$app->request->post();
            $this->actionSavePurchase($data);
        }
    }

    /**
     * @desc 根据选中的采购建议生成相应的采购单，对选中的数据进行验证：同一供应商，同一仓库的才能生成同一个采购单
     * @author Jimmy
     * @date 2017-04-07 09:42:11
     */
    protected function actionSavePurchase($res)
    {
        $supplierSettlement = Supplier::find()->select('supplier_settlement')->where(['supplier_code'=>$res['PurchaseOrder']['supplier_code']])->scalar();
        //拼凑主表数据
        $model_order=new PurchaseOrder();
        $model_order->load($res);
        $model_order->pur_number      = CommonServices::getNumber('PO');
        $model_order->operation_type  ='2';
        $model_order->created_at      = date('Y-m-d H:i:s');
        $model_order->creator         = Yii::$app->user->identity->username;
        $model_order->buyer           = Yii::$app->user->identity->username;
        $model_order->merchandiser    = $res['PurchaseOrder']['merchandiser'];
        $model_order->account_type    = $supplierSettlement ? $supplierSettlement : $res['PurchaseOrder']['account_type'];
        $model_order->purchas_status  = 1;//待确认
        $model_order->create_type     = 1;//创建类型
        $model_order->is_expedited    = $res['PurchaseOrder']['is_expedited'];//加急
        $transactions=Yii::$app->db->beginTransaction();
        try {
            //加入备注
            $PurchaseNote=[
                'pur_number'=>$model_order->pur_number,
                'note'      =>$res['PurchaseNote']['note'],
            ];
            $model_note = new PurchaseNote();
            $model_note->saveNote($PurchaseNote);
            //加入采购单日志
            $data=[
                'pur_number'=>$model_order->pur_number,
                'note'      =>'生成采购计划单',
            ];
            PurchaseLog::addLog($data);
            //插入主表数据

            if(false==$model_order->save())
            {
                $errors=$model_order->getFirstErrors();
                $str="</br>";
                foreach ($errors as $error)
                {
                    $str.=$error."</br>";
                }
                Yii::$app->getSession()->setFlash('error','操作失败了,请联系管理员:'.$str,true);
                $transactions->rollBack();
                return $this->redirect(['index']);
            }
            foreach ($res['PurchaseOrder']['items'] as $val)
            {
                $sb= PurchaseOrderItems::findOne(['pur_number'=>$model_order->pur_number,'sku'=>$val['sku']]);
                if($sb)
                {
                    $sb->qty         += $val['qty'];

                    //表修改日志-更新
                    $change_content = TablesChangeLog::updateCompare($sb->attributes, $sb->oldAttributes);
                    $change_data = [
                        'table_name' => 'pur_purchase_order_items', //变动的表名称
                        'change_type' => '2', //变动类型(1insert，2update，3delete)
                        'change_content' => $change_content, //变更内容
                    ];
                    TablesChangeLog::addLog($change_data);
                    $sb->save(false);
                } else {
                    $model_items = new PurchaseOrderItems();
                    $model_items->load(['PurchaseOrderItems' => $val]);
                    $model_items->pur_number       = $model_order->pur_number;
                    $model_items->items_totalprice = $val['qty'] * $val['price'];
                    $model_items->product_img      = Product::find()->select('uploadimgs')->where(['sku' => $val['sku']])->scalar();
                    //插入条目数据
                    if (false == $model_items->save(false)) {
                        $errors = $model_items->getFirstErrors();
                        $str    = "</br>";
                        foreach ($errors as $error) {
                            $str .= $error . "</br>";
                        }
                        Yii::$app->getSession()->setFlash('error', '操作失败了,请联系管理员:' . $str, true);
                        $transactions->rollBack();
                        return $this->redirect(['index']);
                    }

                    //表修改日志-新增
                    $change_content = "insert:新增id值为{$model_items->id}的记录";
                    $change_data = [
                        'table_name' => 'pur_purchase_order_items', //变动的表名称
                        'change_type' => '1', //变动类型(1insert，2update，3delete)
                        'change_content' => $change_content, //变更内容
                    ];
                    TablesChangeLog::addLog($change_data);

                    //回写采购建议，标识已经生产采购单
                    $model_suggest           = new PurchaseSuggest();
                    $where['sku']            = $val['sku'];
                    $where['warehouse_code'] = $model_order->warehouse_code;
                    //$where['is_purchase']    = 'Y';

                    $id = $model_suggest->find()->where($where)->one();
                    $id->load(['PurchaseSuggest' => ['is_purchase' => 'N','state'=>1,'demand_number' =>$model_order->pur_number]]);

                    $suggest_data = ['is_purchase' => 'N','state'=>1];
                    $model_suggest::updateAll($suggest_data, $where);

                    //更新在途库存 暂时关闭
                    //$mods = PurchaseOrderItems::getSKUc($model_order->pur_number);
                    //Stock::saveStock($mods, $model_order->warehouse_code);
                    if ($id->save(false) == false) {
                        $errors = $model_suggest->getFirstErrors();
                        $str    = "</br>";
                        foreach ($errors as $error) {
                            $str .= $error . "</br>";
                        }
                        Yii::$app->getSession()->setFlash('error', '操作失败,请联系管理员:' . $str, true);
                        $transactions->rollBack();
                        return $this->redirect(['index']);
                    }

                    //表修改日志-新增
                    $change_content = "insert:新增id值为{$id->id}的记录";
                    $change_data = [
                        'table_name' => 'pur_purchase_suggest', //变动的表名称
                        'change_type' => '1', //变动类型(1insert，2update，3delete)
                        'change_content' => $change_content, //变更内容
                    ];
                    TablesChangeLog::addLog($change_data);
                }
            }
            $transactions->commit();
            Yii::$app->getSession()->setFlash('success',"恭喜，操作成功！生成的采购单为: {$model_order->pur_number}",true);
            if(Yii::$app->request->post('flag'))
            {
                return $this->redirect(['purchase-suggest-supplier/index']);
            }else{
                return $this->redirect(['purchase-order-confirm/index']);
            }
        } catch (Exception $e) {
            $transactions->rollBack();
            Yii::$app->getSession()->setFlash('success',"生成采购单失败",true);
            return $this->redirect(['purchase-suggest/index']);
        }

        
    }
    /**
     * @desc 对提交过来的数据信息进行相关核查
     * @return array $data 选中的需要备货的数据信息
     * @author  Jimmy
     * @date 2017-04-07 11:49:11
     */
    protected function checkPurchaseData(&$data)
    {
        $ids=Yii::$app->request->get('ids');
        if(empty($ids)){
            Yii::$app->getSession()->setFlash('error', '是的,缺少参数是不会让你通过的！',true);
            return $this->redirect(['index']);
        }
        $model=new PurchaseSuggest();
        $map['id']=explode(',',$ids);
        $num=$model->find()->groupBy(['supplier_code','warehouse_code'])->where($map)->count();
        if($num>1)
        {
            Yii::$app->getSession()->setFlash('error','是的,同一供应商，同一仓库的才能生成同一个采购单!',true);
            return $this->redirect(['index']);
        }
        $data=$model->find()->where($map)->asArray()->all();
        if(count($data)==0)
        {
            return $this->redirect(['index']);
        }
    }
    /**
     * @desc Ajax 修改采购建议数量
     * @author Jimmy
     * @date 2017-04-13 13:50:11
     */
    public function actionUpdateQty()
    {
        $id= Yii::$app->request->post('id');
        $qty= Yii::$app->request->post('qty');
        $model=new PurchaseSuggest();

        //表修改日志-更新
        $change_data = [
            'table_name' => 'pur_purchase_order_suggest', //变动的表名称
            'change_type' => '2', //变动类型(1insert，2update，3delete)
            'change_content' => "update:id:{$id},qty:=>{$qty}", //变更内容
        ];

        $transactions=Yii::$app->db->beginTransaction();
        try {
            TablesChangeLog::addLog($change_data);
            $res = $model->updateAll(['qty' => $qty], ['id' => $id]);
            $transactions->commit();
        } catch (Exception $e) {
            $transactions->rollBack();
            $res = false;
        }
        return $res;
    }

    /**
     * 修改采购员
     * @return bool|string|\yii\web\Response
     */
    public function actionEditbuyer(){
        $post = Yii::$app->request->post('PurchaseSuggest');
        if(!empty($post)){
            $buyer=BaseServices::getEveryOne($post['buyer']);
            $arrid=explode(',',$post['id']);

            $data['buyer']=$buyer;
            $data['buyer_id']=$post['buyer'];

            $update_data = '';
            foreach ($data as $k=>$v) {
                $update_data .= "{$k}:=>{$v},";
            }

            $transactions=Yii::$app->db->beginTransaction();
            try {
                foreach ($arrid as $k => $v) {
                    //表修改日志-更新
                    $change_data = [
                        'table_name' => 'pur_purchase_order_pay_type', //变动的表名称
                        'change_type' => '2', //变动类型(1insert，2update，3delete)
                        'change_content' => "update:id:{$v},{$update_data}", //变更内容
                    ];
                    TablesChangeLog::addLog($change_data);
                    PurchaseSuggest::updateAll($data, ['id' => $v]);
                }
                $transactions->commit();
                Yii::$app->getSession()->setFlash('success', '恭喜你,采购员被你修改成功了！', true);
            } catch(Exception $e) {
                $transactions->rollBack();
                Yii::$app->getSession()->setFlash('error', '恭喜你,采购员修改失败！', true);
            }
            return $this->redirect(Yii::$app->request->referrer);
        }else{
            $ids= Yii::$app->request->get('id');
            $ids=implode(',',$ids);

            $model=new PurchaseSuggest();
            return $this->renderAjax('editbuyer',[
                'model'=>$model,
                'ids'=>$ids,
            ]);
        }
    }

    /** 图片查看
     * @param $img
     * @param $sku
     */
    public function actionImg()
    {
            $Img = Yii::$app->request->get();
            if($Img['sku'])
            {
                $img = Vhelper::toSkuImgBig($Img['sku'],'');
                return $this->renderAjax('view',['img'=>$img]);
            } else{
                return 'sku传值有问题';
            }

    }

    /**
     * 半年内下单的供应商产品地址
     * @return string
     */
    public function actionPaddress(){
        $sku=Yii::$app->request->get('sku');

        $etime=date('Y-m-d H:i:s',time());
        $stime=date('Y-m-d H:i:s',time()-86400*30*6);

        if(!empty($sku)){
            $model=SupplierQuotes::find()
                ->select('supplier_product_address')
                ->where(['product_sku'=>$sku])
                ->andFilterWhere(['between','add_time',$stime,$etime])
                ->asArray()->all();

            $model2=PurchaseHistory::find()
                ->select('features')
                ->where(['sku'=>$sku])
                ->andFilterWhere(['between','purchase_time',$stime,$etime])
                ->asArray()->all();
        }

        return $this->renderAjax('paddress',[
            'model'=>$model,
            'model2'=>$model2,
        ]);
    }

    /**
     * 历史采购信息
     * @return string
     */
    public function actionHistorPurchaseInfo(){
        $sku=Yii::$app->request->get('sku');
        $role = Yii::$app->request->get('role','purchase');
        $data = PurchaseOrderItems::find()
            ->alias('t')
            ->select(['ctq'=>'t.ctq','cty'=>'t.cty','price'=>'t.price','base_price'=>'ifnull(t.base_price,0)','cancel_num'=>'ifnull(b.cancel_ctq,0)','cancel_num2'=>'ifnull(refund_qty,0)',
                'pur_number'=>'a.pur_number','audit_time'=>'a.audit_time','buyer'=>'a.buyer','cancel_audit_time'=>'c.audit_time','supplier_name'=>'s.supplier_name'])
            ->leftJoin(PurchaseOrder::tableName().' a','a.pur_number=t.pur_number')
            ->leftJoin(PurchaseOrderCancelSub::tableName().' b','t.pur_number=b.pur_number and t.sku=b.sku')
            ->leftJoin(PurchaseOrderCancel::tableName().' c','c.id = b.cancel_id')
            ->leftJoin(PurchaseOrderRefundQuantity::tableName().' d','t.sku=d.sku and t.pur_number=d.pur_number')
            ->leftJoin(Supplier::tableName().' s','a.supplier_code=s.supplier_code')
            ->andWhere(['or',['c.audit_status'=>2],['c.audit_status'=>null]])
            ->andFilterWhere(['t.sku'=>$sku])
            ->andFilterWhere(['NOT IN','a.purchas_status',[1,2,4,10]])
            ->orderBy('a.audit_time desc')
            ->asArray()->all();
        $pur_items=[];
        if(!empty($data)){
            $i=0;
            foreach ($data as $value){
                if(preg_match('/^ABD/',$value['pur_number'])&&!empty($value['cancel_audit_time'])&&strtotime($value['cancel_audit_time'])<=strtotime('2018-11-28 12:00:00')){
                    $cancel = 0;
                }else {
                    $cancel =   $value['cancel_num2'] + $value['cancel_num'];
                }
                if(($value['ctq']-$cancel)<=0){
                    continue;
                }
                $price = $value['base_price']==0 ? $value['price'] : $value['base_price'];
                $pur_items[$i]['supplier_name'] = !empty($value['supplier_name']) ? $value['supplier_name'] : '';
                $pur_items[$i]['pur_number'] = !empty($value['pur_number']) ? $value['pur_number'] : '';
                $pur_items[$i]['supplier_name'] = !empty($value['supplier_name']) ? $value['supplier_name'] : '';
                $pur_items[$i]['price'] = $price;
                $pur_items[$i]['ctq'] = $value['ctq']-$cancel;
                $pur_items[$i]['cty'] = $value['cty'];
                $pur_items[$i]['audit_time'] = $value['audit_time'];
                $pur_items[$i]['buyer'] = $value['buyer'];
                $i++;
            }

        }

        $model2=PurchaseHistory::find()->where(['sku'=>$sku])->orderBy('purchase_time desc')->all();

        //判断当前登录账户是否为销售
        $check_role = Yii::$app->authManager->getRolesByUser(Yii::$app->user->identity->id);//383     Yii::$app->user->identity->id
        $is_visible = 1;
        if ($check_role && is_array($check_role)) {
            foreach ($check_role as $key => $value) {
                if(preg_match('/销售/',$key)){
                    $is_visible = 0;
                }
            }
        }
        return $this->renderAjax('histor-purchase-info',[
            'model'=>$pur_items,
            'model2'=>$model2,
            'sku'=>$sku,
            'role'=>$role,
            'is_visible' => $is_visible
        ]);
    }


    public function actionFbaHistorPurchaseInfo(){
        $sku=Yii::$app->request->get('sku');
        $pur_items=PurchaseOrderItems::find()
            ->alias('b')
            ->leftJoin('pur_purchase_order as a','a.pur_number = b.pur_number')
            ->where(['NOT IN','a.purchas_status',[1,2,4,10]])
            ->andFilterWhere(['b.sku'=>$sku])
            ->orderBy('submit_time desc')
            ->all();

        $model2=PurchaseHistory::find()->where(['sku'=>$sku])->orderBy('purchase_time desc')->all();

        return $this->render('histor-purchase-info',[
            'model'=>$pur_items,
            'model2'=>$model2,
        ]);
    }

    public function actionLeadSuggest()
    {
        $request = Yii::$app->request;
        if($request->isAjax) {
            //获取勾选的单号id
            $ids=Yii::$app->request->post('ids');
            $query = PurchaseSuggest::getLeadSuggests($ids);
            $total = $query->count();
            $user = new User();
            $users = $user->find()->all();
            if(empty($ids)){
                return $this->renderAjax('lead-suggest', ['total' => $total, 'users' => $users ,'ids' => '']);
            }else{
                return $this->renderAjax('lead-suggest', ['total' => $total, 'users' => $users ,'ids' => join(',',$ids)]);
            }
        } else {
            //获取勾选的单号id
            $ids=Yii::$app->request->get('ids');
            if(!empty($ids)){
                $ids = explode(',',$ids);
            }
            return $this->lead_suggest_to_order($ids);
        }
    }

    public function lead_suggest_to_order($ids=null)
    {
        set_time_limit(3600);
        $model_suggest = new PurchaseSuggest();
        $query = $model_suggest::getLeadSuggests($ids);
        $total = $query->count();
        if($total ==0 ) {
            throw new \yii\web\ForbiddenHttpException('该页禁止访问，可能没有符合条件的采购建议！');
            exit;
        }
        $suggest_list = $query->asArray()->all();
        $db = Yii::$app->db;
        $counter = 0;
        foreach($suggest_list as $v) {
            try {
                $t = $db->beginTransaction();
                $a = $b = $c = $d = true; //checkpoint
                $model_order = new PurchaseOrder();
                $model_order->attributes = $v;
                $model_order->pur_number      = CommonServices::getNumber('PO');
                $model_order->operation_type  = '2';
                $supplier = Supplier::find()->select('supplier_name,supplier_settlement')->where(['supplier_code'=>$v['supplier_code']])->one();
                if($supplier){
                    $model_order->supplier_name = $supplier->supplier_name;//供应商名字
                    $model_order->account_type = $supplier->supplier_settlement;//供应商结算方式
                } else {
                    $model_order->account_type = 1;//供应商结算方式
                }
                $model_order->created_at      = date('Y-m-d H:i:s');
                $model_order->creator         = Yii::$app->user->identity->username;
                $model_order->buyer           = Yii::$app->user->identity->username;
                $model_order->purchas_status  = 1; //待确认
                $model_order->shipping_method = '2'; //供应商运输
                $model_order->create_type     = 1; //创建类型
                $model_order->is_expedited = 1;
                $model_order->pur_type = $v['replenish_type'];
                $a = $model_order->save();

                //表修改日志-新增
                $change_content = "insert:新增id值为{$model_order->id}的记录";
                $change_data = [
                    'table_name' => 'pur_purchase_order', //变动的表名称
                    'change_type' => '1', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);
                if($a === false) {
                    $errors = $model_order->getFirstErrors();
                    $str = '';
                    foreach($errors as $error) {
                        $str.= $error."</br>";
                    }
                    Yii::$app->getSession()->setFlash('error', '操作失败了，请联系管理员：'.$str, true);
                    $t->rollBack();
                    return $this->redirect(['index']);
                }
                //加入备注
                $PurchaseNote = [
                    'pur_number' => $model_order->pur_number,
                    'note'       => '一键生成采购单',
                ];
                $model_note = new PurchaseNote();
                $model_note->saveNote($PurchaseNote);
                //加入采购单日志
                $data = [
                    'pur_number' => $model_order->pur_number,
                    'note'       => '生成采购计划单',
                ];
                PurchaseLog::addLog($data);

                $ids = explode(',', $v['ids']);
                $items = $model_suggest->find()
                    ->select(['sku', 'name', 'qty', 'price'])
                    ->where(['in', 'id', $ids])
                    ->asArray()
                    ->all();

                // 订单子表
                foreach($items as $i) {
                    $defaultPrice = \app\models\ProductProvider::find()
                        ->select('q.supplierprice')
                        ->alias('t')
                        ->leftJoin(\app\models\SupplierQuotes::tableName().'q','t.quotes_id=q.id')
                        ->where(['t.sku'=>$i['sku'],'t.is_supplier'=>1])->scalar();
                    $i['price'] = $defaultPrice ? $defaultPrice :$i['price'];
                    $sb = PurchaseOrderItems::findOne(['pur_number' => $model_order->pur_number, 'sku' => $i['sku']]);
                    if($sb) {
                        $sb->qty += $i['qty'];
                        $sb->items_totalprice += $i['qty'] * $i['price'];

                        //表修改日志-更新
                        $change_content = TablesChangeLog::updateCompare($sb->attributes, $sb->oldAttributes);
                        $change_data = [
                            'table_name' => 'pur_purchase_order_items', //变动的表名称
                            'change_type' => '2', //变动类型(1insert，2update，3delete)
                            'change_content' => $change_content, //变更内容
                        ];
                        TablesChangeLog::addLog($change_data);
                        $b = $sb->save(false);
                        if(!$b) {
                            break; // 写入失败，跳出循环
                        }
                    } else {
                        $model_order_items = new PurchaseOrderItems();
                        $model_order_items->attributes = $i;
                        $model_order_items->pur_number = $model_order-> pur_number;
                        $model_order_items->items_totalprice = $i['qty']*$i['price'];
                        $model_order_items->product_img = Product::find()->select('uploadimgs')->where(['sku' => $i['sku']])->scalar();
                        $c = $model_order_items->save(false);

                        //表修改日志-新增
                        $change_content = "insert:新增id值为{$model_order_items->id}的记录";
                        $change_data = [
                            'table_name' => 'pur_purchase_order_items', //变动的表名称
                            'change_type' => '1', //变动类型(1insert，2update，3delete)
                            'change_content' => $change_content, //变更内容
                        ];
                        TablesChangeLog::addLog($change_data);

                        if(!$c) {
                            break; // 写入失败，跳出循环
                        }
                    }
                }
                //更新在途库存 暂时关闭
                //$mods = PurchaseOrderItems::getSKUc($model_order->pur_number);
                //Stock::saveStock($mods, $model_order->warehouse_code);

                //表修改日志-更新
                $change_data = [
                    'table_name' => 'pur_purchase_order_pay_type', //变动的表名称
                    'change_type' => '2', //变动类型(1insert，2update，3delete)
                    'change_content' => "update:id:{$v['ids']},state:=>1, is_purchase:=>N,demand_number:=>{$model_order->pur_number}", //变更内容
                ];
                TablesChangeLog::addLog($change_data);
                $d = $db->createCommand()->update('pur_purchase_suggest', ['state' => 1, 'is_purchase' => 'N', 'demand_number' => $model_order->pur_number], ['in', 'id', $ids])->execute();
                if(!$d) {
                    Yii::$app->getSession()->setFlash('error', '操作失败了，请联系管理员：更新采购建议状态时出错', true);
                    $t->rollBack();
                    return $this->redirect(['index']);
                }
                $is_commit = $a && $b && $c && $d;
                if($is_commit) {
                    $counter++; // 记录事务提交次数
                    $t->commit();
                } else {
                    $t->rollBack();
                    continue;
                }
            } catch(\Exception $e) {
                $t->rollBack();
                throw $e;
                break;
            }
        }
        Yii::$app->getSession()->setFlash('success', "恭喜，操作成功！共生成采购单: {$counter} 条", true);
        return $this->redirect(['index']);
    }

    /**
     * 点击采购数量，查看采购数量的组成
     * @return string
     */
    public function actionQtyView()
    {
        $sku = Yii::$app->request->get('sku');
        $warehouse_code = Yii::$app->request->get('warehouse_code');
        $model= PurchaseSuggestQuantity::find()
            ->where(['sku'=> $sku])
            ->andWhere(['purchase_warehouse'=>$warehouse_code])->orderBy('create_time desc')->all();
        $model_suggest= PurchaseSuggest::find()
            ->where(['sku'=> $sku])
            ->andWhere(['warehouse_code'=>$warehouse_code])
            ->one();
        return $this->renderAjax('qty-view', [
            'model' => $model,
            'model_suggest' => $model_suggest,
        ]);
    }

    /**
     * 查看自己当前导入的需求数据
     * @return string
     */
    public function actionPurchaseSumView()
    {
        $searchModel = new PurchaseSuggestQuantitySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('purchase-sum-view', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        } else {
            return $this->render('purchase-sum-view', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
            ]);
        }
    }
    /**
     * 未处理原因
     * @return string
     */
    public function actionUpdateSuggestNote($data=null)
    {
        if (empty($data)) {
            $data = Yii::$app->request->get();
        }
        return PurchaseSuggestNote::updateSuggestNote($data,$purchase_type=1);
    }
    /**
     * 批量修改未处理原因
     * @return string
     */
    public function actionSuggestNotes()
    {
        if (Yii::$app->request->isAjax) {
            $ids=Yii::$app->request->get('id');
            $ids = implode($ids,',');
            $model = new PurchaseSuggestNote();
            return $this->renderAjax('suggest-notes', [
                'model' =>$model,
                'id' =>$ids,
            ]);
        } elseif (Yii::$app->request->post()) {

            $ids = Yii::$app->request->post()['PurchaseSuggestNote']['ids'];
            $data['suggest_note'] = Yii::$app->request->post()['PurchaseSuggestNote']['suggest_note'];


            $ids    = strpos($ids,',') ? explode(',',$ids):$ids;
            $suggest = PurchaseSuggest::find()
                ->where(['in', 'id', $ids])
                ->all();

            $transaction = Yii::$app->db->beginTransaction();
            try {
                foreach ($suggest as $k => $v) {
                    $data['sku'] = $v['sku'];
                    $data['warehouse_code'] = $v['warehouse_code'];
                    $status = $this->actionUpdateSuggestNote($data);
                }
                $transaction->commit();
            } catch (Exception $e) {
                $transaction->rollBack();
            }

            if ($status) {
                Yii::$app->getSession()->setFlash('success','恭喜你,新增备注成功');
            } else {
                Yii::$app->getSession()->setFlash('error','备注失败');
            }
            return $this->redirect(Yii::$app->request->referrer);
        }
    }

    /**
     * 获取建议数量
     */
    public function actionSuggestQuantity()
    {
        $sku=Yii::$app->request->get('sku');

        $date_start = date('Y-m-d 00:00:00');
        $date_end   = date('Y-m-d 23:23:59');
        $model2=PurchaseSuggest::find()->where(['sku'=>$sku,'purchase_type'=>1])->andWhere(['>','qty',0])->andWhere(['between','created_at',$date_start,$date_end])->one();
        if(empty($model2))
        {
            return '不是采购建议生成的单';
        }

        return $this->renderAjax('suggest_qty',[
            'model2'=>$model2,
        ]);
    }
    
    /**
     * @desc 导出数据
     */
    public function actionExportCsv()
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');
        
        $id = Yii::$app->request->get('ids');
        $id = strpos($id,',')?explode(',',$id):$id;
        if (!empty($id))
            $model = PurchaseSuggestSearch::find()->where(['in','id',$id])->asArray()->all();
        else
        {
            $searchData = \Yii::$app->session->get('PurchaseSuggestSearchData');
            $purchaseSuggestSearch = new PurchaseSuggestSearch();
            $query = $purchaseSuggestSearch->search($searchData, true);
            $model = $query->asArray()->all();
        }
        $objectPHPExcel = new \PHPExcel();
        $objectPHPExcel->setActiveSheetIndex(0);
        $n = 0;
        //报表头的输出
/*         $objectPHPExcel->getActiveSheet()->mergeCells('A1:R1'); //合并单元格
        $objectPHPExcel->getActiveSheet()->mergeCells('A1:A2');
        $objectPHPExcel->getActiveSheet()->setCellValue('A1','采购单表');  //设置表标题
        $objectPHPExcel->setActiveSheetIndex(0)->getStyle('A1')->getFont()->setSize(24); //设置字体大小
        $objectPHPExcel->setActiveSheetIndex(0)->getStyle('A1')
        ->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER); */
    
        //表格头的输出  采购需求建立时间、财务审核时间、财务付款时间、销售备注
        $cell_value = ['采购员','SKU','产品类别','仓库名称','产品状态','产品名称','供应商', '单价', '安全交期',
            '日均销量','可用库存','在途库存','欠货','采购数量','需求生成时间','SKU创建时间','预计到货时间','处理状态','未处理原因',];
        foreach ($cell_value as $k => $v) {
            $objectPHPExcel->setActiveSheetIndex(0)->setCellValue(chr($k+65) . '1',$v);
        }
        //设置数据水平靠左和垂直居中
        $objectPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $objectPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        
        $row = 2;
        foreach ( $model as $v )
        {
                $productCreateTime = Product::find()->select('create_time')->where(['sku'=>$v['sku']])->one();
                $createTime = PurchaseOrderItems::getOrderOneInfo($v['sku'],'audit_time',[3,4,5,6,7,8,9,10]);
                $receiveTime = PurchaseOrderItems::getOrderOneInfo($v['sku'],'date_eta',[3,4,5,6,7,8,9,10]);
                $receiveTimeText = '创建时间：' . $createTime . "\r\n";
                $receiveTimeText .= '预计到货时间：' . $receiveTime;
                $undisposedReason = PurchaseSuggestNote::find()
                    ->select("suggest_note")
                    ->where(['and', ['=', 'sku', $v['sku']], ['=', 'warehouse_code', $v['warehouse_code']]])
                    ->andWhere(['status'=>1])
                    ->scalar();
                if (empty($undisposedReason))
                    $undisposedReason = '';
                $objectPHPExcel->getActiveSheet()->setCellValue('A'. $row  ,$v['buyer']);
                $objectPHPExcel->getActiveSheet()->setCellValue('B'. $row  ,$v['sku']);
                $objectPHPExcel->getActiveSheet()->setCellValue('C'. $row  ,$v['category_cn_name']);
                $objectPHPExcel->getActiveSheet()->setCellValue('D'. $row  ,$v['warehouse_name']);
                $objectPHPExcel->getActiveSheet()->setCellValue('E'. $row  ,$v['product_status']);
                $objectPHPExcel->getActiveSheet()->setCellValue('F'. $row  ,$v['name']);
                $objectPHPExcel->getActiveSheet()->setCellValue('G'. $row  ,$v['supplier_name']);
                $objectPHPExcel->getActiveSheet()->setCellValue('H'. $row  ,$v['price']);
                $objectPHPExcel->getActiveSheet()->setCellValue('I'. $row  ,$v['safe_delivery']);
                $objectPHPExcel->getActiveSheet()->setCellValue('J'. $row  ,$v['sales_avg']);
                $objectPHPExcel->getActiveSheet()->setCellValue('K'. $row  ,$v['available_stock']);
                $objectPHPExcel->getActiveSheet()->setCellValue('L'. $row  ,$v['on_way_stock']);
                $objectPHPExcel->getActiveSheet()->setCellValue('M'. $row  ,$v['left_stock']);
                $objectPHPExcel->getActiveSheet()->setCellValue('N'. $row  ,$v['qty']);
                $objectPHPExcel->getActiveSheet()->setCellValue('O'. $row  ,$v['created_at']);
                $objectPHPExcel->getActiveSheet()->setCellValue('P'. $row  ,isset($productCreateTime->create_time)?$productCreateTime->create_time:'');
                $objectPHPExcel->getActiveSheet()->setCellValue('Q'. $row  ,$receiveTimeText);
                $objectPHPExcel->getActiveSheet()->setCellValue('R'. $row  ,\app\services\PurchaseOrderServices::getProcesStatus()[$v['state']]);
                $objectPHPExcel->getActiveSheet()->setCellValue('S'. $row  ,$undisposedReason);
                $row++;
        }
    
        for ($i = 65; $i<83; $i++) {
            $objectPHPExcel->getActiveSheet()->getColumnDimension(chr($i))->setWidth(15);
        }
        $objectPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
        $objectPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
    
        //设置样式
        $objectPHPExcel->getActiveSheet()->getStyle('B2:H'.($n+4))->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        ob_end_clean();
        ob_start();
        header("Content-type:application/vnd.ms-excel;charset=UTF-8");
        header('Content-Type : application/vnd.ms-excel');
        header('Content-Disposition:attachment;filename="'.'采购建议-'.date("Y年m月j日").'.xls"');
        $objWriter= \PHPExcel_IOFactory::createWriter($objectPHPExcel,'Excel5');
        $objWriter->save('php://output');
        die;
    }
}

