<?php

namespace app\controllers;

use app\api\v1\models\HwcAvgDeliveryTime;
use app\models\TablesChangeLog;
use app\services\BaseServices;
use m35\thecsv\theCsv;
use Yii;
use app\models\PurchaseOrderSearch;
use yii\db\Exception;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;


class OverseasAvgArrivalController extends BaseController
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
     * Lists all PurchaseOrder models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new HwcAvgDeliveryTime();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionExport(){
        set_time_limit(0);
        $sku = Yii::$app->request->get('sku');
        $supplier_code = Yii::$app->request->get('suppliercode');
        $model = HwcAvgDeliveryTime::find()
            ->alias('t')
            ->joinWith(['defaultSupplier'])
            ->andFilterWhere(['t.sku'=>$sku])
            ->andFilterWhere(['supplier_code'=>$supplier_code])
            ->all();


        $table = [
            'sku',
            '供应商',
            '单价',
            '权均交期',
        ];

        $table_head = [];
        foreach($model as $k=>$v)
        {
            $table_head[$k][]=$v->sku;
            $table_head[$k][]=!empty($v->defaultSupplierDetail) ? $v->defaultSupplierDetail->supplier_name : '';
            $table_head[$k][]=!empty($v->supplierQuote) ? $v->supplierQuote->supplierprice : '';
            $table_head[$k][]=$v->purchase_time ==0 ? 0 : sprintf('%.2f',($v->delivery_total/$v->purchase_time)/(24*60*60));;
        }

        theCsv::export([
            'header' =>$table,
            'data' => $table_head,
        ]);
    }
    public function actionExArrive(){
        set_time_limit(0);
        $model = new  HwcAvgDeliveryTime();
        if (Yii::$app->request->isPost)
        {
            $model->file_execl = UploadedFile::getInstance($model, 'file_execl');
            if($model->file_execl->getExtension()!='csv')
            {
                Yii::$app->getSession()->setFlash('error',"格式不正确",true);
                return $this->redirect(['index']);
            }
            $data              = $model->upload();

            if(empty($data))
            {
                Yii::$app->getSession()->setFlash('error',"文件上传失败",true);
                return $this->redirect(['index']);
            }
            $file        = fopen($data, 'r');
            $line_number = 0;
            $str= '';
            $Name = [];
            while ($datas = fgetcsv($file)) {
                if ($line_number == 0) { //跳过表头
                    $line_number++;
                    continue;
                }
                $Name[$line_number]['sku'] = mb_convert_encoding(trim($datas[0]),'utf-8','gbk');
                $Name[$line_number]['avg_arrive'] = mb_convert_encoding(trim($datas[1]),'utf-8','gbk');
                $line_number++;
            }
            if(!empty($Name)){
                $tran = Yii::$app->db->beginTransaction();
                try {
                    foreach ($Name as $data){
                        $exist = HwcAvgDeliveryTime::find()->where(['sku'=>$data['sku']])->one();
                        if($exist){
                            $times = $exist->purchase_time ==0 ? 1 : $exist->purchase_time;
                            $delivery = is_numeric($data['avg_arrive']) ? $data['avg_arrive']*$times*24*60*60 : 0;
                            $update_data = ['delivery_total'=>$delivery,'purchase_time'=>$times,'is_push'=>0];

                            $update_str = BaseServices::getStrData($update_data);
                            //表修改日志-更新
                            $change_data = [
                                'table_name' => 'pur_hwc_avg_delivery_time', //变动的表名称
                                'change_type' => '2', //变动类型(1insert，2update，3delete)
                                'change_content' => "update:sku:{$data['sku']},{$update_str}", //变更内容
                            ];
                            TablesChangeLog::addLog($change_data);

                            Yii::$app->db->createCommand()->update(HwcAvgDeliveryTime::tableName(),$update_data,['sku'=>$data['sku']])->execute();
                        }
                    }

                    $tran->commit();
                } catch (\Exception $e) {
                    $tran->rollBack();
                }
            }
            fclose($file);
            return $this->redirect(['index']);
        } else {
            return $this->renderAjax('addfile', ['model' => $model]);
        }
    }

    }








