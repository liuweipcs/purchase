<?php

namespace app\controllers;

use app\config\Vhelper;
use app\models\PlatformSummary;
use app\models\PurchaseDemand;
use app\models\PurchaseSuggestNote;
use app\services\BaseServices;
use Yii;
use app\models\PurchaseSuggest;
use app\controllers\PurchaseSuggestController;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\PurchaseSuggestSupplierSearch;
use app\models\User;

/**
 * PurchaseSuggestSupplierController implements the CRUD actions for PurchaseSuggest model.
 */
class OverseasPurchaseSuggestSupplierController extends PurchaseSuggestController
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
     * Lists all PurchaseSuggest models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PurchaseSuggestSupplierSearch();
        $map=Yii::$app->request->queryParams;
        $map['PurchaseSuggestSupplierSearch']['is_purchase']='Y';
        $dataProvider = $searchModel->search1($map);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @desc 根据选中的仓库及供应商信息生成
     * @author ztt
     * @date 2017-04-18 19:21:11
     */
    public function actionCreatePurchaseSupplier()
    {
        $model=new PurchaseSuggest();
        $map['supplier_code']=Yii::$app->request->get('supplier_code');
        $map['warehouse_code']=Yii::$app->request->get('warehouse_code');
        $map['buyer']=Yii::$app->request->get('buyer');
        $map['is_purchase']='Y';
        $map['purchase_type']=4;
        $flag=Yii::$app->request->get('flag');
        $data=$model->find()->where($map)->asArray()->all();
        $user=new User();
        $users=$user->find()->all();
       /* echo '<pre>';
        print_r($map);echo '***';
        print_r($data);exit;*/
        return $this->renderAjax('create-purchase', ['data' => $data,'users'=>$users,'flag'=>$flag]);
    }
    /**
     * @desc 根据选中的采购建议生成相应的采购单，对选中的数据进行验证：同一供应商，同一仓库的才能生成同一个采购单
     * @author
     * @date 2017-04-17 14:33:11
     */
    public function actionCreatePurchase()
    {

        if( Yii::$app->request->isAjax || Yii::$app->request->isGet)
        {
            $res = [];
            $this->checkPurchaseData($res);
            $model = new User();
            $users = $model->find()->all();
            return $this->renderAjax('create-purchase', ['data' => $res,'users'=>$users]);
        } elseif(Yii::$app->request->isPost) {

            $data = Yii::$app->request->post();
            $this->actionSavePurchase($data);
        }
    }

    /**
     * @desc 根据选中的采购建议生成相应的采购单，对选中的数据进行验证：同一供应商，同一仓库的才能生成同一个采购单
     * @author
     * @date 2017-04-07 09:42:11
     */
    protected function actionSavePurchase($res)
    {
        //拼凑主表数据

        $model_order = new PurchaseOrder();
        $model_order->load($res);
        $model_order->pur_number      = CommonServices::getNumber($this->purchase_prefix);
        $model_order->operation_type  ='2';
        $model_order->created_at      = date('Y-m-d H:i:s');
        $model_order->creator         = Yii::$app->user->identity->username;
        $model_order->buyer           = Yii::$app->user->identity->username;
        $model_order->merchandiser    = $res['PurchaseOrder']['merchandiser'];
        $model_order->purchas_status  = 1;//待确认
        $model_order->create_type     = 1;//创建类型
        $model_order->is_transit      = 1;//中转
        $model_order->purchase_type   = 2;//海外
        $model_order->transit_warehouse      = $res['PurchaseOrder']['transit_warehouse'];//中转
        $model_order->is_expedited    = $res['PurchaseOrder']['is_expedited'];//加急
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
        $transactions=Yii::$app->db->beginTransaction();
        if(false==$model_order->save())
        {
            $errors=$model_order->getFirstErrors();
            $str="</br>";
            foreach ($errors as $error)
            {
                $str.=$error."</br>";
            }
            Yii::$app->getSession()->setFlash('error','我去,操作失败了,请联系管理员1:'.$str,true);
            $transactions->rollBack();
            return $this->redirect(['index']);
        }
        foreach ($res['PurchaseOrder']['items'] as $val)
        {


            $sb= PurchaseOrderItems::findOne(['pur_number'=>$model_order->pur_number,'sku'=>$val['sku']]);
            //Vhelper::dump($sb);
            if($sb)
            {
                $sb->qty += $val['qty'];
                $sb->save();
            } else {
                $model_items=new PurchaseOrderItems();
                $model_items->load(['PurchaseOrderItems'=>$val]);
                $model_items->pur_number=$model_order->pur_number;
                $model_items->items_totalprice =$val['qty']*$val['price'];
                $model_items->product_img= Product::find()->select('uploadimgs')->where(['sku'=>$val['sku']])->scalar();
                //插入条目数据
                if(false==$model_items->save(false)){

                    $errors=$model_items->getFirstErrors();
                    $str="</br>";
                    foreach ($errors as $error){
                        $str.=$error."</br>";
                    }
                    Yii::$app->getSession()->setFlash('error','我去,操作失败了,请联系管理员2:'.$str,true);
                    $transactions->rollBack();
                    return $this->redirect(['index']);
                }
            }

            //需求单号和采购单号关联
            $dats[] = [
                'demand_number' => $val['demand_number'],
                'create_id'     => Yii::$app->user->identity->username,
                'create_time'   => date('Y-m-d H:i:s'),
            ];
            PlatformSummary::updateAll(['is_push'=>0],['demand_number'=>$val['demand_number']]);
            PurchaseDemand::saveOne($model_order->pur_number,$dats);
            //回写采购建议，标识已经生产采购单
            $model_suggest=new PurchaseSuggest();
            $where['sku']=$val['sku'];
            $where['demand_number']=$val['demand_number'];
            $where['warehouse_code']=$model_order->warehouse_code;
            $where['is_purchase']='Y';
            $where['purchase_type']=4;
            $id=$model_suggest->find()->where($where)->one();
            $id->load(['PurchaseSuggest'=>['is_purchase'=>'N']]);
            if($id->save(false)==false )
            {
                $errors=$model_suggest->getFirstErrors();
                $str="</br>";
                foreach ($errors as $error)
                {
                    $str.=$error."</br>";
                }
                Yii::$app->getSession()->setFlash('error','我去,操作失败,请联系管理员3:'.$str,true);
                $transactions->rollBack();
                return $this->redirect(['index']);
            }
        }
        $transactions->commit();
        Yii::$app->getSession()->setFlash('success',"恭喜，操作成功！生成的采购单为: {$model_order->pur_number}",true);
        if(Yii::$app->request->post('flag'))
        {
            return $this->redirect(Yii::$app->request->referrer);
        }else{
            return $this->redirect(['index']);
        }

    }
    /**
     * @desc 对提交过来的数据信息进行相关核查
     * @return array $data 选中的需要备货的数据信息
     * @author
     * @date 2017-04-07 11:49:11
     */
    protected function checkPurchaseData(&$data)
    {
        $ids=Yii::$app->request->get('ids');
        if(empty($ids)){
            Yii::$app->getSession()->setFlash('error', '少年,缺少参数是不会让你通过的！',true);
            return $this->redirect(['index']);
        }
        $model=new PurchaseSuggest();
        $map['id']=explode(',',$ids);
        $num=$model->find()->groupBy(['supplier_code','warehouse_code','buyer'])->where($map)->count();
        if($num>1)
        {
            Yii::$app->getSession()->setFlash('error','少年,同一供应商，同一仓库的才能生成同一个采购单!',true);
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
     * @author
     * @date 2017-04-13 13:50:11
     */
    public function actionUpdateQty()
    {
        $id= Yii::$app->request->post('id');
        $qty= Yii::$app->request->post('qty');
        $model=new PurchaseSuggest();
        $res=$model->updateAll(['qty'=>$qty],['id'=>$id]);
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

            foreach ($arrid as $k=>$v){
                PurchaseSuggest::updateAll($data,['id'=>$v]);
            }

            Yii::$app->getSession()->setFlash('success', '恭喜你,采购员被你修改成功了！', true);
            return $this->redirect(['index']);
        }else{
            $ids= Yii::$app->request->post('id');
            $ids=implode(',',$ids);
            $model=new PurchaseSuggest();

            return $this->renderAjax('editbuyer',[
                'model'=>$model,
                'ids'=>$ids,
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
        return PurchaseSuggestNote::updateSuggestNote($data,$purchase_type=4);
    }

    public function actionPurchaseDisagree($demand_number,$id){
        $model = PlatformSummary::find()->where(['demand_number'=>$demand_number])->one();
        if(Yii::$app->request->isPost)
        {
            $tran = Yii::$app->db->beginTransaction();
            try{
                $exist = PurchaseDemand::find()->where(['demand_number'=>$demand_number])->exists();
                if($exist){
                    throw new HttpException(500,'需求已经生成采购单');
                }
                if(!in_array($model->level_audit_status,[1,4])){
                    throw new HttpException(500,'需求状态异常不可驳回');
                }
                $model->level_audit_status = 4;
                $model->buyer              = Yii::$app->user->identity->username;
                $model->is_push              = 0;
                $model->is_purchase        = 1;
                $model->purchase_note      = Yii::$app->request->post()['PlatformSummary']['purchase_note'];
                $model->purchase_time      = date('Y-m-d H:i:s', time());
                if($model->save()==false){
                    throw new HttpException(500,'需求状态变更失败');
                }
                $updateNum = PurchaseSuggest::updateAll(['is_purchase'=>'N'],['demand_number'=>$demand_number,'is_purchase'=>'Y','id'=>$id]);
                if($updateNum != 1){
                    throw new HttpException(500,'采购建议状态变更失败！');
                }
                Yii::$app->getSession()->setFlash('success',"恭喜您操作成功！",true);
                $tran->commit();
            }catch (HttpException $e){
                Yii::$app->getSession()->setFlash('error',$e->getMessage());
                $tran->rollBack();
            }
            return $this->redirect(Yii::$app->request->referrer);
        } else{
            return $this->renderAjax('disagree',['model' =>$model]);
        }
    }

    public function actionChangeBuyer(){
        if(Yii::$app->request->isAjax){
            $model = new PurchaseSuggest();
            $data = Yii::$app->request->getBodyParam('data');
            
            return $this->renderAjax('change-buyer',['model'=>$model,'data'=>$data]);
        }else{
            $changeData = Yii::$app->request->getBodyParam('changeBuyer');
            $buyerData  = Yii::$app->request->getBodyParam('PurchaseSuggest');
            $response = PurchaseSuggest::changeBuyer($changeData,$buyerData['buyer']);
            Yii::$app->getSession()->setFlash($response['status'],$response['message']);
            return $this->redirect(Yii::$app->request->referrer);
        }
    }

    //对$product_is_new 赋值 0:非新品   1:新品
    //1.SKU上架时间在2个月内，未下过采购单的SKU标记为新品
    //2.SKU有采购审核通过生成正式的采购订单，新品标识消失（只查海外仓有没有下过）
    //3.如果超过2个月还未下单的，也不显示新品标识
    public function actionNewProduct($is_where = true)
    {   
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        $where =$pwhere= [];
        if ($is_where) {
            $where = ['>=', 'unix_timestamp(create_time) + 3600*24*60', time()];
            $pwhere = ['>=', 'unix_timestamp(p.create_time) + 3600*24*60', time()];
        }

        $db = new \yii\db\Query;
        $all_sku = $db->select('id,sku,create_time')->from('pur_product')->andFilterWhere($where)->all();

        //2个月已下单的 sku
        $already_sku = $db->select('o.pur_number, i.sku, p.id ,p.create_time')
                          ->from('pur_purchase_order_items as i')
                          ->leftJoin('pur_purchase_order as o','o.pur_number = i.pur_number')
                          ->leftJoin('pur_product as p','p.sku = i.sku')
                          ->where(['o.purchase_type' => '2','p.product_is_new'=>'1'])
                          ->andwhere(['in','o.purchas_status',[3,5,6,7,8,9]])
                          ->andFilterWhere($pwhere)
                          ->all();

        //更改product_is_new值
        foreach ($already_sku as $key => $value) {
            $db = \Yii::$app->db->createCommand();
            $db->update('pur_product' , ['product_is_new'=>'0'] , "id=:id" , [':id' => $value['id']])->execute();
        }

        //2. 2个月外未下单的
        $dbs = new \yii\db\Query;
        $not_sku = $dbs->select('id,sku')->from('pur_product')->where(['product_is_new' => '1'])
            ->andFilterWhere($where)
            ->all();

        //更改product_is_new值
        foreach ($not_sku as $key => $value) {
            $db = \Yii::$app->db->createCommand();
            $db->update('pur_product' , ['product_is_new'=>'0'] , "id=:id" , [':id' => $value['id']])->execute();
        } 

        echo '数据执行完毕';            
    }


}
