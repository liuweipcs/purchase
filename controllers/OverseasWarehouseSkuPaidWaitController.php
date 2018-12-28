<?php

namespace app\controllers;

use Yii;
use yii\filters\VerbFilter;
use app\models\Supplier;


use app\models\OverseasWarehouseSkuPaidWaitSearch;

/**
 * Created by PhpStorm.
 * 海外仓 sku 已付款未到货列表
 * User: zwl
 * Date: 2018/09/04
 * Time: 14:59
 */
class OverseasWarehouseSkuPaidWaitController extends BaseController
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
        $searchModel = new OverseasWarehouseSkuPaidWaitSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @desc 查看产品的配库日志
     * @author ztt
     * @date 2017-04-14 16:23:11
     */
    public function actionViewSku()
    {
        $this->layout = false;
        $supplier_id = Yii::$app->request->get('su_id');

        $supplierModel = new Supplier();

        $map['id'] = $supplier_id;
        $supplierInfo = $supplierModel->find()->select('supplier_code')->where($map)->scalar();
        $supplier_code = $supplierInfo;

        $sql     = "SELECT a.pur_number,b.sku,b.name,SUM(b.ctq) - SUM(IFNULL(b.rqy,0)) AS totalCount,SUM(b.price*(b.ctq - IFNULL(b.rqy,0))) AS  totalAmount
                FROM pur_purchase_order a
                INNER JOIN pur_purchase_order_items b ON a.pur_number=b.pur_number
                WHERE a.pay_status=5 AND a.supplier_code='$supplier_code'
                AND b.ctq-IFNULL(b.rqy,0)>0
                AND a.purchas_status NOT IN(4,6,9,10) AND IFNULL(a.refund_status,0) !=2 
                GROUP BY a.pur_number,b.sku
                HAVING totalCount>0
                ORDER BY a.pur_number ASC,b.sku ASC";
//        echo $sql;exit;
        $resultList  = Yii::$app->db->createCommand($sql)->queryAll();

        $resultListCount = array();
        foreach($resultList as $v_list){
            if(isset($resultListCount[$v_list['pur_number']])){
                $resultListCount[$v_list['pur_number']] ++;
            }else{
                $resultListCount[$v_list['pur_number']] = 1;
            }
        }

//        print_r($resultListCount);exit;
        return $this->render('view-sku', [
            'resultList' => $resultList,'resultListCount' => $resultListCount
        ]);
    }


    /**
     * 导出 SKU 已付款未到货列表数据
     */
    public function actionExportSkuPaidWait(){

        $ids = Yii::$app->request->get('ids');

        if($ids){// 根据供应商ID查询
            $idsArr = explode(',',$ids);
            $idsStr = implode(',',$idsArr);

            $paramsData = ['supplier_ids' => $idsStr];
        }else{
            // 获取查询条件缓存的参数
            $paramsData = \Yii::$app->session->get('OverseasWarehouseSkuPaidWaitSearch');
        }

        // 使用缓存的参数去获取数据
        $searchModel    = new OverseasWarehouseSkuPaidWaitSearch();
        $query          = $searchModel->search($paramsData,true);
        $resultList     = $query->all();


        $objectPHPExcel = new \PHPExcel();
        $objectPHPExcel->setActiveSheetIndex(0);
        $objectPHPExcel->getActiveSheet()->mergeCells('A1:E1'); //合并单元格
        $objectPHPExcel->getActiveSheet()->setCellValue('A1','SKU已付款未到货列表');  //设置表标题
        $objectPHPExcel->setActiveSheetIndex(0)->getStyle('A1')->getFont()->setSize(24); //设置字体大小
        $objectPHPExcel->setActiveSheetIndex(0)->getStyle('A1')
            ->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        //表格头的输出
        $cell_value = ['ID','供应商代码','供应商名称','未到货总数量','未到货总金额'];
        foreach ($cell_value as $k => $v) {
            $objectPHPExcel->getActiveSheet()->setCellValue(chr($k+65) . '2',$v);
            $objectPHPExcel->getActiveSheet()->getColumnDimension(chr($k+65))->setWidth(20);
        }

        // 明细的输出
        foreach ( $resultList as $key => $v )
        {
            $num = $key + 3;
            $objectPHPExcel->getActiveSheet()->setCellValue('A'.$num ,$v->id);
            $objectPHPExcel->getActiveSheet()->setCellValue('B'.$num ,$v->supplier_code);
            $objectPHPExcel->getActiveSheet()->setCellValue('C'.$num ,$v->supplier_name);
            $objectPHPExcel->getActiveSheet()->setCellValue('D'.$num ,$v->totalCount);
            $objectPHPExcel->getActiveSheet()->setCellValue('E'.$num ,$v->totalAmount);
        }

        header("Content-type:application/vnd.ms-excel;charset=UTF-8");
        header('Content-Type : application/vnd.ms-excel');
        header('Content-Disposition:attachment;filename="'.'SKU已付款未到货列表-'.date("Y年m月j日").'.xls"');
        $objWriter= \PHPExcel_IOFactory::createWriter($objectPHPExcel,'Excel5');
        $objWriter->save('php://output');
        die;

    }


    /**
     * AJAX返回数据 用来嵌入到其他系统
     * @return mixed
     */
    public function actionIndexEmbedded()
    {
        set_time_limit(0); //用来限制页面执行时间的,以秒为单位
        ini_set('memory_limit', '1024M');
        $cache = Yii::$app->cache;


        $searchModel = new OverseasWarehouseSkuPaidWaitSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->renderAjax('index-embedded', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);

    }

}
