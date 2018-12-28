<?php

namespace app\controllers;

use app\config\Vhelper;
use app\models\PurchaseOrderShip;
use Yii;
use app\models\LogisticsCarrier;
use app\models\ExchangeGoods;
use app\models\ExchangeGoodsSearch;
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
 *                     高山仰止,景行行止.虽不能至,心向往之。
 * User: ztt
 * Date: 2017/9/30 0030
 * Description: ExchangeGoodsController.php      
*/
class ExchangeGoodsController extends BaseController
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
     * Lists all ExchangeGoods models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ExchangeGoodsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 确认供应商已发货
     *
     * @return mixed
     */
    public function actionRefund()
    {
        $model         = new PurchaseOrderShip();
        if (Yii::$app->request->isPost)
        {
            $data =Yii::$app->request->post()['PurchaseOrderShip'];
            if($model->load(Yii::$app->request->post()) && $model->save(false))
            {
                ExchangeGoods::updateAll(['state'=>3],['pur_number'=>$data['pur_number']]);
                Yii::$app->getSession()->setFlash('success',"恭喜你,新增成功啦！",true);
            } else {
                Yii::$app->getSession()->setFlash('error',"我去！新增失败啦,请联系管理员！",true);
            }
            return $this->redirect(['index']);

        } else {

            $map['id']     = Yii::$app->request->get('id');
            $pur_number    = ExchangeGoods::find()
                            ->select('pur_number')
                            ->where($map)
                            ->asArray()
                            ->scalar();
            return $this->renderAjax('addlogistic', [
                'model' => $model,
                'pur_number'  => $pur_number,

            ]);
        }

    }

    /**
     * Finds the ExchangeGoods model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return ExchangeGoods the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ExchangeGoods::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     *添加物流页面
     * @return string
     */
    public function actionAddlogistic(){
        $model         = new ExchangeGoods();
        $model_carrier = new LogisticsCarrier();
        $map['id']     = Yii::$app->request->get('id');

        $data=$model->find()->where($map)->asArray()->one();
        $data_carrier=$model_carrier->find()->all();
        return $this->renderAjax('addaddress', [
            'data' => $data,
            'data_carrier'=>$data_carrier
        ]);
    }

    /**
     * 添加物流
     * @return \yii\web\Response
     */
    public function actionLogisticsave(){
        $data = Yii::$app->request->post();
        $datas =[
            'note'             => $data['note'],
            'state'            => 1,
        ];
        $status = ExchangeGoods::updateAll($datas,['pur_number'=>$data['id']]);
        if($status){
            Yii::$app->getSession()->setFlash('success',"恭喜你,仓库同学有地址发货了！",true);
            return $this->redirect(['index']);
        }else{
            Yii::$app->getSession()->setFlash('error',"我去,地址没有被新增成功,请联系管理员!",true);
            return $this->redirect(['index']);
        }
    }
}
