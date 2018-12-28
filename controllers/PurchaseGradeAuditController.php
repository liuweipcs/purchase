<?php

namespace app\controllers;

use Yii;
use app\models\PurchaseGradeAudit;
use app\models\PurchaseGradeAuditSearch;
use app\models\User;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\PurchaseUser;

/**
 * PurchaseGradeAuditController implements the CRUD actions for PurchaseGradeAudit model.
 */
class PurchaseGradeAuditController extends Controller
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
     * Lists all PurchaseGradeAudit models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PurchaseGradeAuditSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single PurchaseGradeAudit model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new PurchaseGradeAudit model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $post = yii::$app->request->post();
        $model = new PurchaseGradeAudit();

        if ($post) {
            $audit_user = $post['PurchaseGradeAudit']['audit_user'];
            $audit_id = User::find()->select('id')->where(['username'=>$audit_user])->scalar();

            $model->create_time=date('Y-m-d H:i:s', time());
            $model->create_user=Yii::$app->user->identity->username;
            $model->audit_id = $audit_id;
        }

        if ($model->load($post) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing PurchaseGradeAudit model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $post = yii::$app->request->post();

        if ($post) {
            $model->create_time=date('Y-m-d H:i:s', time());
            $model->create_user=Yii::$app->user->identity->username;
        }
        
        if ($model->load($post) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing PurchaseGradeAudit model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the PurchaseGradeAudit model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return PurchaseGradeAudit the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PurchaseGradeAudit::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
