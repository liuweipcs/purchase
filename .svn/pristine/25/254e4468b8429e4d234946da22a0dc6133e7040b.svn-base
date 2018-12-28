<?php

namespace app\controllers;

use app\config\Vhelper;
use app\models\LargeWarehouse;
use app\models\ProductCategory;
use app\models\ProductLine;
use app\models\PurchaseOrderSearch;
use app\models\Warehouse;
use Yii;
use yii\data\Pagination;
use linslin\yii2\curl;
use yii\helpers\Json;

use app\services\BaseServices;


/**
 * Created by PhpStorm.
 * User: ztt
 * Date: 2017/3/23 0023
 * Time: 18:41
 */
class StockDetailController extends BaseController
{

    /**
     * 每日库存上架详情
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PurchaseOrderSearch();

        $curl = new curl\Curl();
        $url = Yii::$app->params['server_ip'] . '/index.php/oversea/queryPerDayAddStock';
        //组装请求参数
        $params = Yii::$app->request->queryParams;
        if(isset($params['PurchaseOrderSearch']['warehouse_code'])){
            $searchModel->warehouse_code = $params['PurchaseOrderSearch']['warehouse_code'];
        }
        if(isset($params['PurchaseOrderSearch']['warehouse_category_id'])){
            $searchModel->warehouse_category_id = $params['PurchaseOrderSearch']['warehouse_category_id'];
        }
        if(isset($params['PurchaseOrderSearch']['start_time'])){
            $searchModel->start_time = $params['PurchaseOrderSearch']['start_time'];
        }
        if(isset($params['PurchaseOrderSearch']['end_time'])){
            $searchModel->end_time = $params['PurchaseOrderSearch']['end_time'];
        }

        $data = [];
        $data['page_size'] = isset($params['per-page'])?$params['per-page']:20;
        $data['page'] = isset($params['page'])?$params['page']:1;
        $data['warehouse_code'] = isset($params['PurchaseOrderSearch']['warehouse_code'])?$params['PurchaseOrderSearch']['warehouse_code']:[];
        $data['warehouse_category_id'] = isset($params['PurchaseOrderSearch']['warehouse_category_id'])?$params['PurchaseOrderSearch']['warehouse_category_id']:[];
        $data['start_time'] = isset($params['PurchaseOrderSearch']['start_time'])?$params['PurchaseOrderSearch']['start_time']:'';
        $data['end_time'] = isset($params['PurchaseOrderSearch']['end_time'])?$params['PurchaseOrderSearch']['end_time']:'';

        $result = $curl->setPostParams([
            'search_data' => Json::encode($data),
        ])->post($url);
        $arr = Json::decode($result);

        //页码
        $pagination = new Pagination(['totalCount' => isset($arr['total'])?$arr['total']:0, 'pageSize' => isset($params['per-page'])?$params['per-page']:20, 'pageParam' => 'page']);
        return $this->renderAjax('index', [
            'searchModel' => $searchModel,
            'data' => isset($arr['success_list'])?$arr['success_list']:[],
            'application_time' => isset($params['application_time'])?$params['application_time']:'',
            'pager' => $pagination
        ]);
    }



    /**
     * PHPExcel导出
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function actionExportCsv()
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        //curl
        $curl = new curl\Curl();
        $url = Yii::$app->params['server_ip'] . '/index.php/oversea/queryPerDayAddStock';
        //组装参数
        $params = Yii::$app->request->queryParams;
        $data = [];
        $data['page_size'] = 2000;
        $data['page'] = 1;
        //大仓code
        if(isset($params['warehouse_category'])){
            $warehouse_category = explode(",",rtrim($params['warehouse_category'],","));
            $warehouse_category = LargeWarehouse::find()->select('category_id')->where(['in','name',$warehouse_category])->all();
            $arr_warehouse = [];
            if($warehouse_category){
                foreach ($warehouse_category as $v){
                    if(!in_array($v->category_id,$arr_warehouse)){
                        $arr_warehouse[] = $v->category_id;
                    }
                }
                $data['warehouse_category_id'] = $arr_warehouse;
            }

        }
        //仓库code
        if(isset($params['warehouse_code'])){
            $warehouse_code = explode(",",rtrim($params['warehouse_code'],","));
            $warehouse_code = Warehouse::find()->select('warehouse_code')->where(['in','warehouse_name',$warehouse_code])->all();
            $arr_warehouse = [];
            if($warehouse_code){
                foreach ($warehouse_code as $v){
                    if(!in_array($v->warehouse_code,$arr_warehouse)){
                        $arr_warehouse[] = $v->warehouse_code;
                    }
                }
                $data['warehouse_code'] = $arr_warehouse;
            }

        }
        //日期
        $data['start_time'] = isset($params['daterangepicker_start'])?$params['daterangepicker_start']:'';
        $data['end_time'] = isset($params['daterangepicker_end'])?$params['daterangepicker_end']:'';

        $result = $curl->setPostParams([
            'search_data' => Json::encode($data),
        ])->post($url);
        $arr = Json::decode($result);
        $data = isset($arr['success_list'])?$arr['success_list']:[];

        $objectPHPExcel = new \PHPExcel();
        $objectPHPExcel->setActiveSheetIndex(0);
        $n = 0;

        //表格头的输出
        $cell_value = ['sku','商品名称','上架数量','产品线','运输方式','采入库批次号','入库单号'];
        foreach ($cell_value as $k => $v) {
            $objectPHPExcel->setActiveSheetIndex(0)->setCellValue(chr($k+65) . '1',$v);
        }
        //设置数据水平靠左和垂直居中
        $objectPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $objectPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $statusArray = [0=>'未处理',1=>'已处理'];
        foreach ($data as $v )
        {
            //明细的输出
            $objectPHPExcel->getActiveSheet()->setCellValue('A'.($n+2) ,isset($v['sku'])?$v['sku']:'');
            $objectPHPExcel->getActiveSheet()->setCellValue('B'.($n+2) ,isset($v['name'])?$v['name']:'');
            $objectPHPExcel->getActiveSheet()->setCellValue('C'.($n+2) ,isset($v['onsale_qty'])?$v['onsale_qty']:'');
            $objectPHPExcel->getActiveSheet()->setCellValue('D'.($n+2) ,self::actionGetCat($v['product_linelist_id']));
            $objectPHPExcel->getActiveSheet()->setCellValue('E'.($n+2) ,isset($v['logitic_mode'])?$v['logitic_mode']:'');
            $objectPHPExcel->getActiveSheet()->setCellValue('F'.($n+2) ,isset($v['ship_number'])?$v['ship_number']:'');
            $objectPHPExcel->getActiveSheet()->setCellValue('G'.($n+2) ,isset($v['tracking_number'])?$v['tracking_number']:'');

            $n = $n +1;
        }

        for ($i = 65; $i<78; $i++) {
            $objectPHPExcel->getActiveSheet()->getColumnDimension(chr($i))->setWidth(15);
            $objectPHPExcel->getActiveSheet()->getStyle( chr($i) . "1")->getFont()->setBold(true);
        }
        $objectPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        $objectPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);

        //设置样式
        $objectPHPExcel->getActiveSheet()->getStyle('A1:E'.($n+2))->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        ob_end_clean();
        ob_start();
        header("Content-type:application/vnd.ms-excel;charset=UTF-8");
        header('Content-Type : application/vnd.ms-excel');
        header('Content-Disposition:attachment;filename="'.'每日库存上架详情-'.date("Y年m月j日").'.xls"');
        $objWriter= \PHPExcel_IOFactory::createWriter($objectPHPExcel,'Excel5');
        $objWriter->save('php://output');
        die;
    }

    public static function actionGetCat($id){
        if(empty($id)){
            return '';
        }
        $category = ProductLine::find()->where(['product_line_id'=>$id])->one();
        $cn_name = '';
        if($category){
            $cn_name = $category->linelist_cn_name;
        }
        return $cn_name;
    }
}
