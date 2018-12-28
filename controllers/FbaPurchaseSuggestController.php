<?php

namespace app\controllers;

use app\config\Vhelper;
use app\models\Product;
use app\models\PurchaseDemand;
use app\models\PurchaseSuggestNote;
use app\services\BaseServices;
use Yii;
use app\models\PurchaseSuggest;
use app\models\PurchaseSuggestSearch;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\PurchaseOrder;
use app\models\PurchaseOrderItems;
use app\services\CommonServices;
use app\models\User;
use app\models\PurchaseLog;
use app\models\PurchaseNote;
/**
 * PurchaseSuggestController implements the CRUD actions for PurchaseSuggest model.
 * @desc 采购建议
 */
class FbaPurchaseSuggestController extends BaseController
{
    public $purchase_prefix='FBA';//采购单前缀
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
        $searchModel = new PurchaseSuggestSearch();
        $map = Yii::$app->request->queryParams;
        if(!isset($map['PurchaseSuggestSearch']['state'])){
            $map['PurchaseSuggestSearch']['is_purchase']='Y';
        }
        if(!empty($map['PurchaseSuggestSearch']['sales_import'])){
            $searchModel->sales_import = $map['PurchaseSuggestSearch']['sales_import'];
        }
        $dataProvider = $searchModel->searchFba($map);
        $query = $searchModel->searchFba($map,true);
        $purchasedata = $query->select(['qty'=>'ifnull(qty,0)','price'=>'ifnull(price,0)'])->asArray()->all();
        $purchaseNum =0;
        $purchaseMoney =0;
        foreach ($purchasedata as $v){
            $purchaseNum+=$v['qty'];
            $purchaseMoney+=$v['qty']*$v['price'];
        }
        Yii::$app->session->set('fba_suggest_total_num',$purchaseNum);
        Yii::$app->session->set('fba_suggest_total_money',$purchaseMoney);
        $status = PurchaseSuggest::getFbaStatusStatistics();
        $warehouseList = \app\services\BaseServices::getWarehouseCode();
        return $this->render('index', [
            //'suggestsum' => $suggestsum,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'status' => $status,
            'warehouseList' => $warehouseList
        ]);
    }

    public function actionExportCsv()
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        $id = Yii::$app->request->get('ids');
        $id = strpos($id,',')?explode(',',$id):$id;
        if (!empty($id))
            $model = PurchaseSuggestSearch::find()->where(['in','id',$id])->asArray()->all();
        else
        {
            $searchData = \Yii::$app->session->get('FbaPurchaseSuggestSearchData');
            $purchaseSuggestSearch = new PurchaseSuggestSearch();
            $query = $purchaseSuggestSearch->searchFba($searchData, true);
            $model = $query->asArray()->all();
        }
        $objectPHPExcel = new \PHPExcel();
        $objectPHPExcel->setActiveSheetIndex(0);
        $n = 0;

        //表格头的输出  采购需求建立时间、财务审核时间、财务付款时间、销售备注
        $cell_value = ['采购员','SKU','产品类别','仓库名称','产品状态','产品名称','供应商', '单价', '安全交期',
            '日均销量','可用库存','在途库存','欠货','采购数量','需求生成时间','SKU创建时间','预计到货时间','处理状态','未处理原因',];
        foreach ($cell_value as $k => $v) {
            $objectPHPExcel->setActiveSheetIndex(0)->setCellValue(chr($k+65) . '1',$v);
        }
        //设置数据水平靠左和垂直居中
        $objectPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $objectPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $row = 2;
        foreach ( $model as $v )
        {
            $productCreateTime = Product::find()->select('create_time')->where(['sku'=>$v['sku']])->one();
            $createTime = PurchaseOrderItems::getOrderOneInfo($v['sku'],'audit_time',[3,4,5,6,7,8,9,10]);
            $receiveTime = PurchaseOrderItems::getOrderOneInfo($v['sku'],'date_eta',[3,4,5,6,7,8,9,10]);
            $receiveTimeText = '创建时间：' . $createTime . "\r\n";
            $receiveTimeText .= '预计到货时间：' . $receiveTime;
            $undisposedReason = PurchaseSuggestNote::find()
                ->select("suggest_note")
                ->where(['and', ['=', 'sku', $v['sku']], ['=', 'warehouse_code', $v['warehouse_code']]])
                ->andWhere(['status'=>1])
                ->scalar();
            if (empty($undisposedReason))
                $undisposedReason = '';
            $objectPHPExcel->getActiveSheet()->setCellValue('A'. $row  ,$v['buyer']);
            $objectPHPExcel->getActiveSheet()->setCellValue('B'. $row  ,$v['sku']);
            $objectPHPExcel->getActiveSheet()->setCellValue('C'. $row  ,$v['category_cn_name']);
            $objectPHPExcel->getActiveSheet()->setCellValue('D'. $row  ,$v['warehouse_name']);
            $objectPHPExcel->getActiveSheet()->setCellValue('E'. $row  ,$v['product_status']);
            $objectPHPExcel->getActiveSheet()->setCellValue('F'. $row  ,$v['name']);
            $objectPHPExcel->getActiveSheet()->setCellValue('G'. $row  ,$v['supplier_name']);
            $objectPHPExcel->getActiveSheet()->setCellValue('H'. $row  ,$v['price']);
            $objectPHPExcel->getActiveSheet()->setCellValue('I'. $row  ,$v['safe_delivery']);
            $objectPHPExcel->getActiveSheet()->setCellValue('J'. $row  ,$v['sales_avg']);
            $objectPHPExcel->getActiveSheet()->setCellValue('K'. $row  ,$v['available_stock']);
            $objectPHPExcel->getActiveSheet()->setCellValue('L'. $row  ,$v['on_way_stock']);
            $objectPHPExcel->getActiveSheet()->setCellValue('M'. $row  ,$v['left_stock']);
            $objectPHPExcel->getActiveSheet()->setCellValue('N'. $row  ,$v['qty']);
            $objectPHPExcel->getActiveSheet()->setCellValue('O'. $row  ,$v['created_at']);
            $objectPHPExcel->getActiveSheet()->setCellValue('P'. $row  ,isset($productCreateTime->create_time)?$productCreateTime->create_time:'');
            $objectPHPExcel->getActiveSheet()->setCellValue('Q'. $row  ,$receiveTimeText);
            $objectPHPExcel->getActiveSheet()->setCellValue('R'. $row  ,\app\services\PurchaseOrderServices::getProcesStatus()[$v['state']]);
            $objectPHPExcel->getActiveSheet()->setCellValue('S'. $row  ,$undisposedReason);
            $row++;
        }

        for ($i = 65; $i<83; $i++) {
            $objectPHPExcel->getActiveSheet()->getColumnDimension(chr($i))->setWidth(15);
        }
        $objectPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
        $objectPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);

        //设置样式
        $objectPHPExcel->getActiveSheet()->getStyle('B2:H'.($n+4))->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        ob_end_clean();
        ob_start();
        header("Content-type:application/vnd.ms-excel;charset=UTF-8");
        header('Content-Type : application/vnd.ms-excel');
        header('Content-Disposition:attachment;filename="'.'采购建议-'.date("Y年m月j日").'.xls"');
        $objWriter= \PHPExcel_IOFactory::createWriter($objectPHPExcel,'Excel5');
        $objWriter->save('php://output');
        die;
    }



}
