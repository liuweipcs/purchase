<?php
namespace app\controllers;
use app\models\PurchaseCompactItems;
use app\models\TablesChangeLog;
use Yii;
use yii\filters\AccessControl;
use app\models\PurchaseOrderItems;
use app\models\PurchaseOrder;
use app\config\Vhelper;
use app\models\PurchaseLog;
use app\models\PurchaseNote;
use app\models\Template;
use app\models\PurchaseOrderPay;
use app\models\PurchaseCompact;
use app\models\PurchaseCompactSearch;

class PurchaseCompactController extends BaseController
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['update-compact'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['update-compact'],
                        'matchCallback' => function ($rule, $action) {
                            if(in_array(Yii::$app->user->getId(), [307, 253, 140])) {
                                return true;
                            }
                        }
                    ],
                ],
            ],
        ];
    }

    // compact list
    public function actionCompactList()
    {
        $args = Yii::$app->request->queryParams;
        $searchModel = new PurchaseCompactSearch();
        $dataProvider = $searchModel->search($args);
        return $this->render('compact-list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    // print compact
    public function actionPrintCompact()
    {
        $id = Yii::$app->request->get('id');
        $cpn = Yii::$app->request->get('cpn');
        if($id) {
            $model = PurchaseCompact::findOne($id);
        } elseif($cpn) {
            $model = PurchaseCompact::find()->where(['compact_number' => $cpn])->one();
        }
        $tplModel = Template::findOne($model->tpl_id);
        if(empty($tplModel)) {
            exit('The compact template is not defined.');
        }
        $items = $model->purchaseCompactItems;
        $products = [];
        foreach($items as $m) {
            $pur_number = $m->pur_number;
            $skus = PurchaseOrderItems::find()->where(['pur_number' => $pur_number])->asArray()->all();
            if($skus) {
                $products[$pur_number] = $skus;
            }
        }
        if ($model->tpl_id == 5) {
            return $this->render("//template/tpls/HT-OVERSEAS", [
                'model' => $model,
                'purchaseItems' => $products,
                'purchase' => PurchaseOrder::findOne(['pur_number'=>$pur_number]),
                'print' => true
            ]);
        }
        $tpl = $tplModel->style_code.'.php';
        $data = [];
        return $this->render("//template/tpls/{$tpl}", [
            'model' => $model,
            'products' => $products,
            'print' => true
        ]);
    }

    // 查看合同详情
    public function actionView()
    {
        $id = Yii::$app->request->get('id');
        $cpn = Yii::$app->request->get('cpn');
        if($id) {
            $model = PurchaseCompact::findOne($id);
        } elseif($cpn) {
            $model = PurchaseCompact::find()->where(['compact_number' => $cpn])->one();
        }
        $pay_list = $model->purchaseCompactPay;
        $pos = PurchaseCompact::getPurNumbers($model->compact_number);
        $orders = PurchaseOrder::find()->where(['in', 'pur_number', $pos])->all();
        $logs = PurchaseLog::find()->where(['pur_number' => $model->compact_number])->asArray()->all();
        return $this->render('compact-view', [
            'model' => $model,
            'orders' => $orders,
            'pay_list' => $pay_list,
            'logs' => $logs
        ]);
    }

    // 下载合同
    public function actionDownloadCompact($id)
    {
        $model = PurchaseCompact::findOne($id);
        $items = $model->purchaseCompactItems;
        $products = [];
        foreach($items as $m) {
            $pur_number = $m->pur_number;
            $skus = PurchaseOrderItems::find()->where(['pur_number' => $pur_number])->asArray()->all();
            if($skus) {
                $products[$pur_number] = $skus;
            }
        }
        if ($model->tpl_id == 5) {
            $content = $this->renderPartial("//template/tpls/HT-OVERSEAS", [
                'model' => $model,
                'purchaseItems' => $products,
                'purchase' => PurchaseOrder::findOne(['pur_number'=>$pur_number]),
                'print' => true
            ]);
        } else {
            $tplModel = Template::findOne($model->tpl_id);
            $tpl = $tplModel->style_code.'.php';
            $content = $this->renderPartial("//template/tpls/{$tpl}", [
                'model' => $model,
                'products' => $products,
                'print' => true
            ]);
        }
        PurchaseCompact::output($content, $model->compact_number);
    }

    // 查看付款回执
    public function actionShowImages($id)
    {
        $model = PurchaseOrderPay::findOne($id);
        if(!empty($model->images)) {
            return $model->images;
        } else {
            return 0;
        }
    }

    // 查看付款申请书
    public function actionShowForm($id)
    {
        $res = PurchaseCompact::getPayFormContent($id);
        if(!$res) {
            return '没有付款申请书';
        }
        return $this->renderAjax($res['tpl'], $res['data']);
    }

    // 下载付款申请书
    public function actionDownloadPayForm($id)
    {
        $res = PurchaseCompact::getPayFormContent($id);
        if(!$res) {
            throw new \yii\web\NotFoundHttpException('没有找到付款申请书信息');
            exit;
        }
        $filename = $res['data']['model']['compact_number'].'_'.$res['data']['model']['pay_id'];
        $content = $this->renderPartial($res['tpl'], $res['data']);
        PurchaseCompact::output($content, $filename);
    }

    // add compact note
    public function actionAddCompactNote()
    {
        $cpn = Yii::$app->request->get('cpn');
        $model = new PurchaseNote();
        if(Yii::$app->request->isPost) {
            $data = Yii::$app->request->post();
            $res = $model->saveNote($data);
            if($res) {
                Yii::$app->getSession()->setFlash('success','添加成功');
                return $this->redirect(Yii::$app->request->referrer);
            } else {
                Yii::$app->getSession()->setFlash('error','添加失败');
                return $this->redirect(Yii::$app->request->referrer);
            }
        } else {
            $notes = $model::find()->where(['pur_number' => $cpn])->asArray()->all();
            return $this->renderAjax('add-compact-note', ['notes' => $notes, 'cpn' => $cpn]);
        }
    }

    /*
     * 修改合同含税与不含税
     * http://caigou.yibainetwork.com/purchase-compact/Update-Compact?cpn=&sui=1
     */
    public function actionUpdateCompact()
    {
        $cpn = Yii::$app->request->get('cpn');
        $sui = Yii::$app->request->get('sui');

        if(empty($cpn) || empty($sui)) {
            throw new \yii\web\NotFoundHttpException;
            exit;
        }
        $model = PurchaseCompact::find()->where(['compact_number' => $cpn])->one();
        if(empty($model)) {
            throw new \yii\web\NotFoundHttpException;
            exit;
        }

        if($sui == 1) {
            $tpl_id = 4;
        } elseif($sui == 2) {
            $tpl_id = 3;
        } else {
            exit('ERROR');
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $model->is_drawback = $sui;
            $model->payment_status = 4;
            $model->tpl_id = $tpl_id;
            $model->save(false);

            $pays = PurchaseOrderPay::find()->where(['pur_number' => $cpn])->all();
            if(!empty($pays)) {
                foreach($pays as $pay) {
                    if (in_array($pay->pay_status, [5, 6])) {
                        exit('存在已支付的请款单了啊');
                    }
                    $pay->pay_status = 0;
                    $pay->save(false);
                }
            }
            $transaction->commit();
            exit('SUCCESS');
        } catch(\Exception $e) {
            $transaction->rollBack();
            echo $e->getMessage();
            exit;
        }
    }


    /**
     * 删除合同
     * @return \yii\web\Response
     */
    public function actionRevokeCompact()
    {
        \yii::$app->response->format = 'raw';
        $id = Yii::$app->request->get('id');
        $model = PurchaseCompact::findOne($id);
        if ($model->tpl_id == 5) {
            return $this->redirect(['/overseas-purchase-order/compact-list']);
        }
        $transaction=\Yii::$app->db->beginTransaction();
        try {
            $result = 1;
            if($model && isset($model->compact_number))
            {
                $pos = PurchaseCompact::getPurNumbers($model->compact_number);
                $orders = PurchaseOrder::find()->where(['in', 'pur_number', $pos])->all();
                if(count($orders)>0) {


                    /*//查询合同单绑定信息
                    $compact = PurchaseCompactItems::find()->where(['compact_number'=>$model->compact_number])->all();

                    if(!$compact){
                        $result = 0;
                    }
                    //之前的合同号进行解绑
                    $compact->bind = 2;
                    $result = $compact->save(false);*/

                    $model->compact_status = 10; // 作废合同

                    // 表修改日志-更新
                    $change_data = [
                        'table_name' => 'pur_purchase_compact', // 变动的表名称
                        'change_type' => '2', // 变动类型(1insert，2update，3delete)
                        'change_content' => "update:compact_number:{$model->compact_number},bind:=>0", // 变更内容
                    ];

                    TablesChangeLog::addLog($change_data);
                    PurchaseCompactItems::updateAll(['bind' => 0], ['compact_number' => $model->compact_number]);

                    $model->audit_time = date('Y-m-d H:i:s', time());
                    $model->audit_person_name = Yii::$app->user->identity->username;
                    $model->audit_person_id = Yii::$app->user->id;

                    // 表修改日志-更新
                    $change_content = TablesChangeLog::updateCompare($model->attributes, $model->oldAttributes);
                    $change_data = [
                        'table_name' => 'pur_purchase_compact', // 变动的表名称
                        'change_type' => '2', // 变动类型(1insert，2update，3delete)
                        'change_content' => $change_content, // 变更内容
                    ];
                    TablesChangeLog::addLog($change_data);
                    $result = $model->save(false);

                    if($result){
                        foreach ($orders as $v) {
                            $datas             = [];
                            $msg               = '在' . date('Y-m-d H:i:s') . '由' . Yii::$app->user->identity->username . '=====' . $model->compact_number . '进行了删除';
                            $datas['type']     = 11;
                            $datas['pid']      = $id;
                            $datas['order_id'] = $v->id;
                            $datas['module']   = '海外仓-合同管理-删除';
                            $datas['content']  = $msg;
                            Vhelper::setOperatLog($datas);

                            //采购订单修改内容 更改订单字段
                            $v->purchas_status  = 1;
                            $v->source          = 2;
                            $v->e_date_eta      = '';
                            $v->e_account_type  = '';
                            $v->e_supplier_name = '';
                            //$v->purchas_status = '';
                            //变更内容
                            $change_content = TablesChangeLog::updateCompare($v->attributes, $v->oldAttributes);
                            $res            = $v->save();
                            if ($res) {
                                //表修改日志-删除
                                $change_data = [
                                    'table_name' => 'pur_purchase_order', //变动的表名称
                                    'change_type' => '2', //变动类型(1insert，2update，3delete)
                                    'change_content' => $change_content, //变更内容
                                ];
                                $logRes = TablesChangeLog::addLog($change_data);
                                if(!$logRes){
                                    $result = 0;
                                    break;
                                }
                            } else {
                                $result = 0;
                                break;
                            }

                            //采购订单详内容变更
                            if (isset($v->purchaseOrderItems) && count($v->purchaseOrderItems) > 0) {
                                foreach ($v->purchaseOrderItems as $val) {
                                    $val->e_price = 0;
                                    $val->e_ctq   = '';
                                    //变更内容
                                    $change_content = TablesChangeLog::updateCompare($val->attributes, $val->oldAttributes);
                                    $res            = $val->save();
                                    if ($res) {
                                        //表修改日志-删除
                                        $change_data = [
                                            'table_name' => 'pur_purchase_order_items', //变动的表名称
                                            'change_type' => '2', //变动类型(1insert，2update，3delete)
                                            'change_content' => $change_content, //变更内容
                                        ];
                                        $logRes = TablesChangeLog::addLog($change_data);
                                        if(!$logRes){
                                            $result = 0;
                                            break;
                                        }
                                    } else {
                                        $result = 0;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }else{
                    $result = 0;
                }


                if($result)
                {
                    $transaction->commit();
                    Yii::$app->getSession()->setFlash('success', '恭喜你,删除合同成功', true);
                } else {
                    $transaction->rollBack();
                    Yii::$app->getSession()->setFlash('success', '对不起,删除合同失败', true);
                }

            } else {
                $transaction->rollBack();
                Yii::$app->getSession()->setFlash('success', '对不起,删除合同失败', true);
            }
        }catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->getSession()->setFlash('success', '对不起,删除合同失败', true);
        }
        return $this->redirect(['/overseas-purchase-order/compact-list']);

    }


    //判断是否可删除
    public static function actionAbleDel($compact_id){
        $model = PurchaseCompact::findOne($compact_id);
        $status = 1;
        if($model){
            $pos = PurchaseCompact::getPurNumbers($model->compact_number);
            $orders = PurchaseOrder::find()->where(['in', 'pur_number', $pos])->all();
            if(isset($orders) && count($orders)>0){
                foreach ($orders as $val){ //
                    //6全到货,8部分到货等待剩余,9部分到货不等待剩余 状态下不能删除.
                    if(in_array($val->pay_status,[2,4,5,6,10]) || in_array($val->purchas_status,[6,8,9])){//待财务审批、待财务付款、已付款、已部分付款、待经理审核不能删除
                        $status = 0;
                        break;
                    }
                }
            }
        }else{
            $status = 0;
        }
        return $status;
    }

}
