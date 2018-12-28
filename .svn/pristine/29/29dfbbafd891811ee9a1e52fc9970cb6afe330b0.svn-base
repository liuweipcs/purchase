<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/31
 * Time: 15:37
 */

namespace app\controllers;

use app\models\TablesChangeLog;
use Yii;
use app\models\Stock;
use app\models\StockSearch;

use app\config\Vhelper;
use app\models\Product;
use app\services\BaseServices;
use app\models\PurchaseSuggest;
use yii\db\Exception;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\PurchaseOrderItems;
use app\services\CommonServices;
use app\models\User;
use app\models\PurchaseLog;


class PurchaseOrderRealTimeSkuController extends BaseController
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
        $searchModel = new StockSearch();
        $dataProvider = $searchModel->search2(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    /**
     * 国内仓（新版）
     * @return [type] [description]
     */
    public function actionIndexNew()
    {
        set_time_limit(0); //用来限制页面执行时间的,以秒为单位
        ini_set('memory_limit', '1024M');
        $searchModel = new StockSearch();
        $data = $searchModel->search5(Yii::$app->request->queryParams);
        if (isset($data['error'])) {
            if ($data['error'] == 1) {
                Yii::$app->getSession()->setFlash('error','必须要填搜索条件');
                return $this->redirect(Yii::$app->request->referrer);
            }
        }
        return $this->render('index-new',$data);
    }
    public function actionTest()
    {
        set_time_limit(0); //用来限制页面执行时间的,以秒为单位
        ini_set('memory_limit', '1024M');
        $cache = Yii::$app->cache;

        // 用户权限认证
        $user        = Yii::$app->request->getQueryParam('user');
        $user_number = Yii::$app->request->getQueryParam('num');
        $user_check  = User::accessCheckByErpUser($user,$user_number);
        if($user_check['code'] == 'error'){
            echo "<span style='color: red'>".$user_check['message']."</span>";
            exit;
        }
        $userid = isset($user_check['user_id'])?$user_check['user_id']:'';
        if(empty($userid)){
            $userid = isset(Yii::$app->user->identity)?Yii::$app->user->identity->getId():0;
        }

        $pageSize = Yii::$app->request->get('pageSize');
        if (!empty($pageSize)) {
            $params = $cache->get('test_params');
            $params['pageSize'] = $pageSize;
        } else {
            $params = Yii::$app->request->queryParams;
            $cache->set('test_params', $params);
            \Yii::$app->session->set('test_params', $params);

        }
        $searchModel = new StockSearch();
        $data = $searchModel->search5($params);
        if (isset($data['error'])) {
            if ($data['error'] == 1) {
                Yii::$app->getSession()->setFlash('error','必须要填搜索条件');
                return $this->redirect(Yii::$app->request->referrer);
            }
        }

        $fields = \app\models\TableFields::find()->where(['userid'=>$userid,'table_name'=>'purchase_order_real_time_sku_list'])->select('data')->scalar();
        $fields = $fields ? json_decode($fields, true) : [];
        $data['fields'] = $fields;
        $data['user_id'] = $userid;
        $data['user'] = $user;
        $data['user_number'] = $user_number;

        return $this->renderAjax('index-new',$data);


        //旧的数据
        $searchModel = new StockSearch();
        $dataProvider = $searchModel->search2(Yii::$app->request->queryParams);
        return $this->renderAjax('test', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $tran = Yii::$app->db->beginTransaction();
        try {
            if ($model->load(Yii::$app->request->post()) && $model->save())
            {
                //表修改日志-更新
                $change_data = [
                    'table_name' => 'pur_stock', //变动的表名称
                    'change_type' => '2', //变动类型(1insert，2update，3delete)
                    'change_content' => "update:id:{$id}", //变更内容
                ];
                TablesChangeLog::addLog($change_data);
                $tran->commit();
                Yii::$app->getSession()->setFlash('success','恭喜你！更新成功');
                return $this->redirect(['index']);
            } else {
                return $this->render('update', [
                    'model' => $model,
                ]);
            }

        } catch (\Exception $e) {
            $tran->rollBack();
            Yii::$app->getSession()->setFlash('error','恭喜你！更新失败');
        }
        return $this->render('update', [
            'model' => $model,
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
        if (($model = Stock::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}