<?php

namespace app\controllers;

use app\config\Vhelper;
use app\models\PurchaseHistory;
use Yii;
use app\models\TongToolPurchaseSearch;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * Created by PhpStorm.
 * User: ztt
 * Date: 2017/3/23 0023
 * Time: 18:41
 */
class TongToolPurchaseController extends BaseController
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
     * 通途采购单
     * Lists all PurchaseOrder models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new TongToolPurchaseSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 读取通途采购单
     */
    public function actionReadPurchase()
    {
        $model = new PurchaseHistory();
        if (Yii::$app->request->isPost) {
            $model->file_execl = UploadedFile::getInstance($model, 'file_execl');

            $data              = $model->upload();

            if(empty($data))
            {
                Yii::$app->getSession()->setFlash('error',"文件上传失败",true);
                return $this->redirect(['index']);
            }
            $file        = fopen($data, 'r');
            $line_number = 0;
            while ($datas = fgetcsv($file)) {
                if ($line_number == 0) { //跳过表头
                    $line_number++;
                    continue;
                }
                $num = count($datas);
                for ($c = 0; $c < $num; $c++) {

                    $Name[$line_number][] = mb_convert_encoding(trim($datas[$c]),'utf-8','gbk');

                }
                $Name[$line_number][] = Yii::$app->user->identity->username;
                $Name[$line_number][] = date('Y-m-d H:i:s');
                $line_number++;
            }
            $statu = Yii::$app->db->createCommand()->batchInsert(PurchaseHistory::tableName(), ['pur_number','tracking_number', 'external_water','warehouse', 'buyer', 'purchase_time', 'cargo_location', 'sku', 'sku_alias', 'product_name', 'specification', 'purchase_link', 'features', 'currency', 'purchase_price', 'latest_offer', 'purchase_quantity', 'actual_arrival_quantity', 'actual_storage_quantity', 'actually_pay_money', 'freight', 'supplier_code', 'supplier_name', 'settlement_method', 'contact_person', 'contact_number', 'fax_number', 'qq', 'ali_want', 'email', 'country', 'province_state', 'city', 'address', 'remarks', 'expected_arrival_date', 'payment_status', 'purchasing_status', 'create_id', 'create_time'], $Name)->execute();
            fclose($file);
            if ($statu) {
                Yii::$app->getSession()->setFlash('success',"恭喜你，导入成功！",true);
                return $this->redirect(['index']);
            } else {
                Yii::$app->getSession()->setFlash('error','恭喜你，导入失败了！请联系管理员',true);
                return $this->redirect(['index']);
            }

        } else {
            return $this->renderAjax('addfile', ['model' => $model]);
        }

    }
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
     * Date: 2017/10/10 0010
     * Description: 获取采购以前的历史记录
    */
    public function actionGetHistory($sku)
    {
        if(empty($sku))
        {
            return '对不住,sku不能为空';
        }
        $model = PurchaseHistory::find()->select('purchase_time,purchase_price,latest_offer,purchase_quantity,buyer')->where(['sku'=>$sku])->orderBy('purchase_time desc')->limit(10)->all();
        if(!$model)
        {
            return '对不住,没有找到以前的历史记录';
        }
        return $this->renderAjax('allhistor', [
            'model' => $model,
        ]);
    }




}
