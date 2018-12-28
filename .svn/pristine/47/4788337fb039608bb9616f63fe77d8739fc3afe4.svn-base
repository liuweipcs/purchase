<?php

namespace app\controllers;

use app\config\Vhelper;
use app\models\TablesChangeLog;
use app\models\WarehouseOwedGoods;
use Yii;
use app\models\WarehouseOwedGoodsSearch;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

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
 * Description: WarehouseOwedGoodsController.php      
*/
class WarehouseOwedGoodsController extends BaseController
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
     * 欠货列表
     * Lists all PurchaseOrder models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new WarehouseOwedGoodsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionDisabled()
    {

        if(Yii::$app->request->isPost)
        {

            $data = Yii::$app->request->post()['WarehouseOwedGoods'];
            $username = Yii::$app->user->identity->username;
            $update_time = date('Y-m-d H:i:s');

            //表修改日志-更新
            $change_data = [
                'table_name' => 'pur_warehouse_owed_goods', //变动的表名称
                'change_type' => '2', //变动类型(1insert，2update，3delete)
                'change_content' => "update:sku:{$data['sku']},is_purchase:=>{$data['is_purchase']},confirmor:=>{$username},note:=>{$data['note']},update_time:=>{$update_time}", //变更内容
            ];
            TablesChangeLog::addLog($change_data);

            $model = WarehouseOwedGoods::updateAll(['is_purchase'=>$data['is_purchase'],'confirmor'=>$username,'note'=>$data['note'],'update_time'=>$update_time],'sku=:sku',[':sku'=>$data['sku']]);
            if($model)
            {
                Yii::$app->getSession()->setFlash('success',"恭喜，操作成功！",true);
            } else {
                Yii::$app->getSession()->setFlash('error',"恭喜，操作失败！",true);
            }

            return $this->redirect(['index','page'=>$data['page']]);
        } else {

            $model = new WarehouseOwedGoods();
            $sku   = Yii::$app->request->get('sku');
            $page  = Yii::$app->request->get('page');
            return $this->renderAjax('note',['model'=>$model,'sku'=>$sku,'page'=>$page]);
        }



    }



}
