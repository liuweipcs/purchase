<?php

namespace app\controllers;

use app\models\BankConfig;
use Yii;
use app\models\BankCardManagement;
use app\models\BankCardManagementSearch;
use yii\web\NotFoundHttpException;
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
 *                     ���汣��        ����BUG
 * User: ztt
 * Date: 2017/3/23 0023
 * Time: 18:41
*/
class BankCardManagementController extends BaseController
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
     * Lists all BankCardManagement models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new BankCardManagementSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single BankCardManagement model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new BankCardManagement model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new BankCardManagement();

        if (Yii::$app->request->isPost) {
            $form = Yii::$app->request->getBodyParam('BankCardManagement');
            $model->head_office = BankConfig::getMasterBankId($form['head_office']);
            $model->branch      = isset($form['branch']) ? trim($form['branch']) : '';
            $model->account_holder = isset($form['account_holder']) ? trim($form['account_holder']) : '';
            $model->account_sign = isset($form['account_sign']) ? trim($form['account_sign']) : '';
            $model->account_number = isset($form['account_number']) ? trim($form['account_number']) : '';
            $model->account_abbreviation = isset($form['account_abbreviation']) ? trim($form['account_abbreviation']) : '';
            $model->payment_password = isset($form['payment_password']) ? trim($form['payment_password']) : '';
            $model->payment_types = isset($form['payment_types']) ? trim($form['payment_types']) : '';
            $model->status = isset($form['status']) ? trim($form['status']) : 1;
            $model->application_business = isset($form['application_business']) ? trim($form['application_business']) : '';
            $model->k3_bank_account = isset($form['k3_bank_account']) ? trim($form['k3_bank_account']) : '';
            $model->remarks = isset($form['remarks']) ? trim($form['remarks']) : '';
            if($model->save()==false){
                Yii::$app->session->setFlash('warning',implode(',',$model->getFirstErrors()));
                return $this->redirect(['index']);
            }
            Yii::$app->session->setFlash('success','银行信息添加成功');
            return $this->redirect(['index']);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing BankCardManagement model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->request->isPost) {
            $form = Yii::$app->request->getBodyParam('BankCardManagement');
            $model->head_office = BankConfig::getMasterBankId($form['head_office']);
            $model->branch      = isset($form['branch']) ? trim($form['branch']) : '';
            $model->account_holder = isset($form['account_holder']) ? trim($form['account_holder']) : '';
            $model->account_sign = isset($form['account_sign']) ? trim($form['account_sign']) : '';
            $model->account_number = isset($form['account_number']) ? trim($form['account_number']) : '';
            $model->account_abbreviation = isset($form['account_abbreviation']) ? trim($form['account_abbreviation']) : '';
            $model->payment_password = isset($form['payment_password']) ? trim($form['payment_password']) : '';
            $model->payment_types = isset($form['payment_types']) ? trim($form['payment_types']) : '';
            $model->status = isset($form['status']) ? trim($form['status']) : 1;
            $model->application_business = isset($form['application_business']) ? trim($form['application_business']) : '';
            $model->k3_bank_account = isset($form['k3_bank_account']) ? trim($form['k3_bank_account']) : '';
            $model->remarks = isset($form['remarks']) ? trim($form['remarks']) : '';
            if($model->save()==false){
                Yii::$app->session->setFlash('warning',implode(',',$model->getFirstErrors()));
                return $this->redirect(['index']);
            }
            Yii::$app->session->setFlash('success','银行信息编辑成功');
            return $this->redirect(['index']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }
    public function actionDisabled($id,$status)
    {
        $model = $this->findModel($id);
        $model->setAttribute('status',$status);
        $model->save();
        return $this->redirect(['index']);
    }

    /**
     * Finds the BankCardManagement model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return BankCardManagement the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = BankCardManagement::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
