<?php
namespace app\controllers;
use app\models\TablesChangeLog;
use app\models\PurchaseOrder;
use Yii;
use app\config\Vhelper;
use yii\web\Controller;
use app\models\PurchaseOrderBreakage;

class ExpBreakageController extends Controller
{

    public function actionIndex()
    {
        $searchModel = new PurchaseOrderBreakage();
        $args = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($args);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
    }

    // 编辑信息
    public function actionEdit()
    {
        $request = Yii::$app->request;
        if($request->isPost) {
            $post = $request->post();
            $model = PurchaseOrderBreakage::findOne($post['id']);
            $model->status = 0;
            $model->apply_notice     = $post['apply_notice'];
            $model->apply_person     = Yii::$app->user->identity->username;
            $model->apply_time       = date('Y-m-d H:i:s', time());
            $model->breakage_num     = $post['breakage_num'];
            $model->items_totalprice = $post['breakage_num']*$post['price'];

            //表修改日志-更新
            $change_content = TablesChangeLog::updateCompare($model->attributes, $model->oldAttributes);
            $change_data = [
                'table_name' => 'pur_purchase_order_breakage', //变动的表名称
                'change_type' => '2', //变动类型(1insert，2update，3delete)
                'change_content' => $change_content, //变更内容
            ];
            $tran = Yii::$app->db->beginTransaction();
            try {
                TablesChangeLog::addLog($change_data);
                $res = $model->save(false);
                $tran->commit();
            } catch(Exception $e){
                $tran->rollBack();
            }

            if($res) {
                Yii::$app->getSession()->setFlash('success',"恭喜你，编辑成功！",true);
                return $this->redirect(Yii::$app->request->referrer);
            } else {
                Yii::$app->getSession()->setFlash('error',"对不起，编辑失败！",true);
                return $this->redirect(Yii::$app->request->referrer);
            }
        } else {
            $id = $request->get('id');
            $mod = PurchaseOrderBreakage::findOne($id);
            if(!$mod) {
                return '数据不存在';
            }
            $img = \app\models\PurchaseOrderItems::find()
                ->select('product_img')
                ->where(['pur_number' => $mod->pur_number, 'sku' => $mod->sku])
                ->scalar();
            return $this->renderAjax('edit', ['mod' => $mod, 'img' => $img]);
        }
    }

    // 审核信息
    public function actionView()
    {
        $request = Yii::$app->request;
        if($request->isPost) {
            $post = $request->post();
            $model = PurchaseOrderBreakage::findOne($post['id']);
            $model->status = $post['status'];
            $model->audit_notice = $post['audit_notice'];
            $model->audit_person = Yii::$app->user->identity->username;
            $model->audit_time = date('Y-m-d H:i:s', time());

            //表修改日志-更新
            $change_content = TablesChangeLog::updateCompare($model->attributes, $model->oldAttributes);
            $change_data = [
                'table_name' => 'pur_purchase_order_breakage', //变动的表名称
                'change_type' => '2', //变动类型(1insert，2update，3delete)
                'change_content' => $change_content, //变更内容
            ];
            $tran = Yii::$app->db->beginTransaction();
            try {
                TablesChangeLog::addLog($change_data);
                $res = $model->save(false);
                $tran->commit();
            } catch(Exception $e){
                $tran->rollBack();
            }

            if($res) {
                Yii::$app->getSession()->setFlash('success',"恭喜你，审核成功！",true);
                return $this->redirect(Yii::$app->request->referrer);
            } else {
                Yii::$app->getSession()->setFlash('error',"对不起，审核失败！",true);
                return $this->redirect(Yii::$app->request->referrer);
            }
        } else {
            $id = $request->get('id');
            $mod = PurchaseOrderBreakage::findOne($id);
            if(!$mod) {
                return '数据不存在';
            }
            $img = \app\models\PurchaseOrderItems::find()
                ->select('product_img')
                ->where(['pur_number' => $mod->pur_number, 'sku' => $mod->sku])
                ->scalar();
            return $this->renderAjax('view', ['mod' => $mod, 'img' => $img]);
        }
    }

    // 审核信息
    public function actionCaiwuView()
    {
        $request = Yii::$app->request;
        if($request->isPost) {
            $post = $request->post();
            $model = PurchaseOrderBreakage::findOne($post['id']);
            $model->status = $post['status'];
            $model->approval_notice = $post['approval_notice'];
            $model->approval_person = Yii::$app->user->identity->username;
            $model->approval_time = date('Y-m-d H:i:s', time());

            //订单，入库，报损
            $is_all_cancel = PurchaseOrder::isAllCancel($model->pur_number,$post['id'],1); //是否全部取消

            //表修改日志-更新
            $change_content = TablesChangeLog::updateCompare($model->attributes, $model->oldAttributes);
            $change_data = [
                'table_name' => 'pur_purchase_order_breakage', //变动的表名称
                'change_type' => '2', //变动类型(1insert，2update，3delete)
                'change_content' => $change_content, //变更内容
            ];
            $tran = Yii::$app->db->beginTransaction();
            try {
                TablesChangeLog::addLog($change_data);
                $res = $model->save(false);
                //审核通过时，才改变订单状态
                if ( ($is_all_cancel['is_all_cancel'] == 1) && ($post['status'] == 3) ) PurchaseOrder::updateAll(['purchas_status'=>$is_all_cancel['purchase_status'],'is_push'=>0],['pur_number'=>$model->pur_number]);
                $tran->commit();
            } catch(Exception $e){
                $tran->rollBack();
            }
            if($res) {
                Yii::$app->getSession()->setFlash('success',"恭喜你，审批成功！",true);
                return $this->redirect(Yii::$app->request->referrer);
            } else {
                Yii::$app->getSession()->setFlash('error',"对不起，审批失败！",true);
                return $this->redirect(Yii::$app->request->referrer);
            }
        } else {
            $id = $request->get('id');
            $mod = PurchaseOrderBreakage::findOne($id);
            if(!$mod) {
                return '数据不存在';
            }
            $img = \app\models\PurchaseOrderItems::find()
                ->select('product_img')
                ->where(['pur_number' => $mod->pur_number, 'sku' => $mod->sku])
                ->scalar();
            return $this->renderAjax('caiwu-view', ['mod' => $mod, 'img' => $img]);
        }
    }







}