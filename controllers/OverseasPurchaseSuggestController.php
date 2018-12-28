<?php

namespace app\controllers;

use app\config\Vhelper;
use app\models\PlatformSummary;
use app\models\Product;
use app\models\PurchaseDemand;
use app\services\BaseServices;
use Yii;
use app\models\PurchaseSuggest;
use app\models\PurchaseSuggestSearch;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\PurchaseOrder;
use app\models\PurchaseOrderItems;
use app\services\CommonServices;
use app\models\User;
use app\models\PurchaseLog;
use app\models\PurchaseNote;
/**
 * PurchaseSuggestController implements the CRUD actions for PurchaseSuggest model.
 * @desc 采购建议
 */
class OverseasPurchaseSuggestController extends BaseController
{
    public $purchase_prefix='ABD';//采购单前缀
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
        $searchModel = new PurchaseSuggestSearch();
        $dataProvider = $searchModel->search1(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
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
        $model_order->account_type    = 2;//结算方式
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
            if(isset($val['is_purchase'])&&$val['is_purchase']==0){
                continue;
            }
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
        $num=$model->find()->groupBy(['supplier_code','warehouse_code'])->where($map)->count();
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
}
