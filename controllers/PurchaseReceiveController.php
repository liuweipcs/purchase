<?php

namespace app\controllers;

use app\config\Vhelper;
use app\models\PurchaseAbnormals;
use Yii;
use app\models\PurchaseReceive;
use app\models\PurchaseReceiveSearch;
use app\models\PurchaseOrderReceipt;
use app\models\PurchaseOrder;
use yii\web\NotFoundHttpException;
use app\services\BaseServices;
use yii\filters\VerbFilter;

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
 * Date: 2017/8/29 0029
 * Description: PurchaseReceiveController.php      
*/
class PurchaseReceiveController extends BaseController
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
     * Lists all PurchaseReceive models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PurchaseReceiveSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,1);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'view'=>'index'
        ]);
    }

    public function actionErpIndex()
    {
        $searchModel = new PurchaseReceiveSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,2);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'view'=>'erp-index'
        ]);
    }


    /**
     * @desc 查看详情
     * @author Jimmy
     * @date 2017-04-20 20:57:11
     */
    public function actionViewDetail()
    {

        $model=new PurchaseReceive();
        $map['express_no']=Yii::$app->request->get('express_no');
        $map['pur_number']=Yii::$app->request->get('pur_number');
        $map['handle_type']=Yii::$app->request->get('handle_type');
        $map['sku']=Yii::$app->request->get('sku');
        $data=$model->find()->where($map)->asArray()->all();
        return $this->renderAjax('view-detail', [
            'data' => $data,
        ]);
    }
    /**
     * @desc 处理异常
     * @author Jimmy
     * @date 2017-04-21 14:33:11
     */
    public function actionHandleDetail()
    {
        $model=new PurchaseReceive();
        $map['express_no']=Yii::$app->request->get('express_no');
        $map['pur_number']=Yii::$app->request->get('pur_number');
        $map['handle_type']=Yii::$app->request->get('handle_type');
        $map['sku']=Yii::$app->request->get('sku');
        $data=$model->find()->where($map)->asArray()->all();
        return $this->renderAjax('handle-detail', [
            'data' => $data,
        ]);
    }
    /**
     * @desc 保存异常处理
     * @author Jimmy
     * @date 2017-04-21 16:13:11
     */
    public function actionHandleSave()
    {
        $data                      = Yii::$app->request->post('PurchaseReceive');
        foreach ($data as $key=>$val) {
            $model                  = new PurchaseReceive;
            $map['id']              = $key;
            $vals['note_center']    = $val['note_handle'];
            $vals['note_handle']    = $val['note_handle'];
            $vals['is_receipt']     = $val['is_receipt'];
            //$vals['handle_type']    = isset($val['handle_type']) ? $val['handle_type'] : 3;
            //$vals['is_return']      = !empty($val['is_return']) ? $val['is_return'] : '0';
           // $vals['refund_amount']  = !empty($val['refund_amount']) ? $val['refund_amount'] : '0';
            $vals['receive_status'] = '2';//已确认
            $vals['handler']        = Yii::$app->user->identity->username;
            $vals['time_handle']    = date('Y-m-d H:i:s');

            if (false == $model->updateAll($vals, $map)) {

                Yii::$app->getSession()->setFlash('error', '我去！操作失败,请联系管理员1:', true);
                // $transaction->rollBack();
                $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->id);
                if(in_array('erp用户',array_keys($roles))){
                    return $this->redirect(['erp-index']);
                }
                return $this->redirect(['index']);
            }

            //审核通过而且是终止来货并退款 则生成收款通知
            $purchaseReceiveInfo = $model->find()->where($map)->asArray()->one();
            PurchaseAbnormals::UpdateOne($purchaseReceiveInfo['pur_number']);
            $this->savePurchaseStatus($purchaseReceiveInfo);
           /* $arr=['1','2'];
            if (in_array($purchaseReceiveInfo['handle_type'],$arr) && $purchaseReceiveInfo['is_return'] == 1)
            {
                $purchaseOrderMap = ['pur_number' => $purchaseReceiveInfo['pur_number']];
                $purchaseOrderInfo = $purchaseModel->find()->where($purchaseOrderMap)->asArray()->one();
                $userInfo = BaseServices::getInfoByCondition(['username' => $purchaseReceiveInfo['handler']]);
                $purchaseOrderInfo = array_merge($purchaseOrderInfo, [
                    'pay_price'        => $purchaseReceiveInfo['refund_amount'],
                    'applicant'        => empty($userInfo['id']) ? 0 : $userInfo['id'],
                    'application_time' => $purchaseReceiveInfo['time_handle'],
                    'review_notice'    => $purchaseReceiveInfo['note_handle'],
                    'pay_name'         => $purchaseReceiveInfo['note_handle'],
                    'step'             => 1,
                ]);
                $res = $purchaseOrderReceiptModel->saveOne($purchaseOrderInfo);

                if($res == false)
                {

                    Yii::$app->getSession()->setFlash('error','我去！操作失败,请联系管理员2:',true);
                    $transaction->rollBack();
                    return $this->redirect(['index']);
                }
            }*/
        //}
            //$transaction->commit();
        }
        Yii::$app->getSession()->setFlash('success',"恭喜你，操作成功！",true);
        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->id);
        if(in_array('erp用户',array_keys($roles))){
            return $this->redirect(['erp-index']);
        }
        return $this->redirect(['index']);
    }
    /**
     * 更新采购状态
     * @param $status
     */
    protected  function  savePurchaseStatus($status)
    {

        PurchaseOrder::updateAll(['receiving_exception_status'=>6], 'pur_number = :pur_number', [':pur_number' => $status['pur_number']]);

    }
}
