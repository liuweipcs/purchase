<?php

namespace app\controllers;

use app\config\Vhelper;
use app\models\Product;
use app\models\PurchaseSuggestQuantitySearch;
use app\models\TablesChangeLog;
use app\services\BaseServices;
use app\services\PurchaseSuggestQuantityServices;
use m35\thecsv\theCsv;
use Yii;
use yii\filters\VerbFilter;
use app\models\Warehouse;
use app\models\PurchaseSuggestQuantity;
use app\models\DataControlConfig;
use yii\helpers\FileHelper;

class PurchaseSumImportController extends BaseController
{
    public $purchase_prefix='PO';//采购单前缀
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
        $searchModel = new PurchaseSuggestQuantitySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 采购需求导入
     * @return string|\yii\web\Response
     */
    public function actionPurchaseSumImport(){
        $model = new PurchaseSuggestQuantity();

        if (Yii::$app->request->isPost && $_FILES)
        {
            $extension=pathinfo($_FILES['PurchaseSuggestQuantity']['name']['file_execl'], PATHINFO_EXTENSION);

            $filessize=$_FILES['PurchaseSuggestQuantity']['size']['file_execl']/1024/1024;
            $filessize=round($filessize,2);

            if($filessize>10)
            {
                Yii::$app->getSession()->setFlash('warning',"文件大小不能超过 10M，当前大小： $filessize M",true);
                return $this->redirect(['index']);
            }


            if($extension!='csv')
            {
                Yii::$app->getSession()->setFlash('warning',"格式不正确,只接受 .csv 格式的文件",true);
                return $this->redirect(['index']);
            }
            $name= 'PurchaseSuggestQuantity[file_execl]';
            $data = Vhelper::upload($name);

            if(empty($data))
            {
                Yii::$app->getSession()->setFlash('warning',"文件上传失败",true);
                return $this->redirect(['index']);
            }
            $file        = fopen($data, 'r');
            $line_number = 0;
            $s=0;

            while ($datas = fgetcsv($file)) {
                if ($line_number == 0) { //跳过表头
                    $line_number++;
                    continue;
                }

                $count = count($datas); //列
                if ($count != 6) {
                    Yii::$app->getSession()->setFlash('warning','导入模板有误',true);
                    return $this->redirect(['index']);
                }

                $sku_sku = mb_convert_encoding(trim($datas[0]),'utf-8','gbk');
                $sku=Product::find()->where(['sku'=>$sku_sku])->asArray()->one()['sku'];
                if(!$sku){
                    $s++;
                    continue;
                }
                $num = count($datas);
                for ($c = 0; $c < $num; $c++) {
                    $Name[$line_number][] = mb_convert_encoding(trim($datas[$c]),'utf-8','gbk');
                }

                if(!empty($Name[$line_number][1]) && !is_numeric($Name[$line_number][1])){
                    $Name[$line_number][1] =strtoupper($Name[$line_number][1]);
                }

                if (empty($Name[$line_number][2])) {
                    $Name[$line_number][2] = 0;
                }
                if (empty($Name[$line_number][3])) {
                    $Name[$line_number][3] = 0;
                }


                if(!empty($Name[$line_number][4]) && !is_numeric($Name[$line_number][4])){
                    $purchase_warehouse = Warehouse::find()->select('id,warehouse_code,warehouse_name')->where(['use_status'=>1,'warehouse_name'=>$Name[$line_number][4]])->asArray()->one()['warehouse_code'];
                    $arr_warehouse = DataControlConfig::getPoDemandImportWarehouse();
                    if (empty($purchase_warehouse)) {
                        Yii::$app->getSession()->setFlash('warning',$Name[$line_number][4] . ' -- 采购仓有误或为停运状态',true);
                        return $this->redirect(['index']);
                    } elseif (!in_array($purchase_warehouse,$arr_warehouse)) {
                        $str_warehouse = Warehouse::find()->select("GROUP_CONCAT(warehouse_name SEPARATOR ',`') as warehouse_name")->where(['use_status'=>1,'warehouse_code'=>$arr_warehouse])->asArray()->scalar();
                        Yii::$app->getSession()->setFlash('warning','仓库只能为： ' . $str_warehouse,true);
                        return $this->redirect(['index']);
                    }
                    $Name[$line_number][4] = $purchase_warehouse;
                } else {
                    Yii::$app->getSession()->setFlash('warning','仓库不能为空--'.$sku_sku,true);
                    return $this->redirect(['index']);
                }

                $pmodel = Product::find()->joinWith(['desc','cat'])->where(['pur_product.sku'=>$Name[$line_number][0]])->asArray()->one();

                $Name[$line_number][] = Yii::$app->user->identity->username;
                $Name[$line_number][] = date('Y-m-d H:i:s',time());
                $Name[$line_number][] = 1;
                $line_number++;

            }

            if (!empty($Name[1]) && count($Name[1])!=9) {
                Yii::$app->getSession()->setFlash('warning','文件格式错误：可能是文件右侧面多了很多无用的空数据',true);
                return $this->redirect(['index']);
            }
            if (empty($Name)) {
                Yii::$app->getSession()->setFlash('warning','导入失败',true);
                return $this->redirect(['index']);
            }
            if (count($Name) > 150) {
                Yii::$app->getSession()->setFlash('warning','一次性导入数据多，请分批导入',true);
                return $this->redirect(['index']);
            }
            //数据一次性入库
            $transaction=\Yii::$app->db->beginTransaction();
            try{
                //一次性入库
                $statu= Yii::$app->db->createCommand()->batchInsert(PurchaseSuggestQuantity::tableName(), ['sku', 'platform_number', 'activity_stock', 'routine_stock', 'purchase_warehouse','sales_note', 'create_id', 'create_time','suggest_status',], $Name)->execute();

                $skus = array_column($Name,'0');
                $skus = implode(',', $skus);
                //表修改日志-新增
                $change_content = "insert:新增sku:{$skus}";
                $change_data = [
                    'table_name' => 'pur_purchase_suggest_quantity', //变动的表名称
                    'change_type' => '1', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);
                $transaction->commit();
            }catch (Exception $e){
                $transaction->rollBack();
            }

            fclose($file);

            $dir=Yii::getAlias('@app') .'/web/files/' . date('Ymd');
            if (file_exists($dir)){
                FileHelper::removeDirectory($dir);
            }
            if ($statu) {
                if($s>0){
                    $f_sku ="SKU错误：$s 条";
                } else {
                    $f_sku ="";
                }

                Yii::$app->getSession()->setFlash('success',"恭喜你，导入成功！$f_sku",true);
                return $this->redirect(['index']);
            } else {
                Yii::$app->getSession()->setFlash('warning','恭喜你，导入失败了！请联系管理员',true);
                return $this->redirect(['index']);
            }

        } else {
            return $this->renderAjax('purchase-sum-import', ['model' => $model]);
        }
    }
    /**
     * 导出excel表格
     */
    public function actionExportCsv()
    {
        //以写入追加的方式打开
        $id = Yii::$app->request->get('ids');
        $id = strpos($id, ',') ? explode(',', $id) : $id;
        if (!empty($id)) {
            $model = PurchaseSuggestQuantity::find()
                ->where(['in','id',$id])
                ->asArray()->all();
        } else {
            $searchData = \Yii::$app->session->get('PurchaseSuggestQuantitySearchData');
            $purchaseOrderSearch = new PurchaseSuggestQuantitySearch();
            $query = $purchaseOrderSearch->search($searchData, true);
            $model = $query->asArray()->all();
        }
        if(empty($model)){
            Yii::$app->session->setFlash('warning','导出无数据');
            return $this->redirect(Yii::$app->request->referrer);
        }
        $table = [
            'SKU',
            '平台号',
            '采购数量',
            '采购仓',
            '创建人',
            '创建时间',
            '采购建议状态',
            '销售备注',
        ];

        $table_head = [];
        foreach ($model as $k => $v) {
            $table_head[$k][] = $v['sku'];
            $table_head[$k][] = $v['platform_number'];
            $table_head[$k][] = $v['purchase_quantity'];
            $table_head[$k][] = !empty($v['purchase_warehouse'])?BaseServices::getWarehouseCode($v['purchase_warehouse']):''; //采购仓
            $table_head[$k][] = $v['create_id'];
            $table_head[$k][] = $v['create_time'];
            $table_head[$k][] = PurchaseSuggestQuantityServices::getSuggestStatusText($v['suggest_status']);
            $table_head[$k][] = $v['sales_note'];
        }
        theCsv::export([
            'header' =>$table,
            'data' => $table_head,
            'name' => '国内采购需求导入表--' . date('Y-m-d') . '.csv',  //Excel表名字
        ]);
        die;
    }
    /**
     * 删除
     */
    public function actionDelete($id)
    {
        $tran = Yii::$app->db->beginTransaction();
        try {
            $this->findModel($id)->delete();

            //表修改日志-删除
            $change_content = "delete:删除id值为{$id}的记录";
            $change_data = [
                'table_name' => 'pur_purchase_suggest_quantity', //变动的表名称
                'change_type' => '3', //变动类型(1insert，2update，3delete)
                'change_content' => $change_content, //变更内容
            ];
            TablesChangeLog::addLog($change_data);
            $tran->commit();
        } catch (Exception $e) {
            $tran->rollBack();
        }

        return $this->redirect(Yii::$app->request->referrer);
    }
    protected function findModel($id)
    {
        if (($model = PurchaseSuggestQuantity::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}