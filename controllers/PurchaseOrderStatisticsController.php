<?php

namespace app\controllers;

use app\config\Vhelper;
use app\models\PurchaseOrderItems;
use app\models\PurchaseOrderRefundQuantity;
use Yii;
use app\models\PurchaseOrderSearch;
use app\services\BaseServices;


/**
 * Created by PhpStorm.
 * User:
 * Date: 2017/8/3 0023
 * Time: 18:41
 */
class PurchaseOrderStatisticsController extends BaseController
{

    /**
     * 采购订单统计
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PurchaseOrderSearch();
        $params = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->searchOrderStatistics($params);
        if(isset($params['PurchaseOrderSearch']['purchase_type'])){
            $searchModel->purchase_type = $params['PurchaseOrderSearch']['purchase_type'];
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * excel导出
     * @throws \yii\web\HttpException
     */
    public function actionExportCsv()
    {
        set_time_limit(0);
        ini_set("memory_limit", "1024M"); // 设置php可使用内存

        $purchase_type = Yii::$app->request->get('purchase_type');
        $start_time = Yii::$app->request->get('start_time');
        $end_time = Yii::$app->request->get('end_time');
        //组装搜索参数
        $map = [];
        $map['PurchaseOrderSearch']['purchase_type'] = isset($purchase_type)?$purchase_type:1;
        $map['PurchaseOrderSearch']['start_time'] =  $start_time;
        $map['PurchaseOrderSearch']['end_time'] =  $end_time;
        $searchModel = new PurchaseOrderSearch();
        $query =  $searchModel->searchOrderStatistics($map,true);

        $objectPHPExcel = new \PHPExcel();
        //$objectPHPExcel->setActiveSheetIndex(0);
        $n = 0;

        //表格头的输出
        $cell_value = [
            '用户类型',
            'sku',
            '供应商名称',
            '产品名称',
            '最新报价',
            '采购数量',
            '开票点',
            '采购金额',
            '采购次数',
            '结算方式',
            '产品分类'
        ];

        $typeArray = [1=>'国内仓',2=>'海外仓',3=>'FBA'];

        $total = $query->count();
        $pageSize = 2000;
        $pages = ceil($total/$pageSize);
        for($i=0;$i<$pages;$i++){
            foreach ($cell_value as $k => $v) {
                $objectPHPExcel->setActiveSheetIndex($i)->setCellValue(chr($k+65) . '1',$v);
            }
            //设置数据水平靠左和垂直居中
            $objectPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objectPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

            //设置SHEET
            $objectPHPExcel->createSheet();
            $objectPHPExcel->setactivesheetindex($i);
            $offset = $i*$pageSize;
            $query->limit = $pageSize;
            $query->offset = $offset;

            $datas = $query->all();
            $n = 0;
            foreach ($datas as $model)
            {
                //明细的输出
                $objectPHPExcel->getActiveSheet()->setCellValue('A'.($n+2) ,isset($typeArray[$model->purchase_type])?$typeArray[$model->purchase_type]:'');
                $objectPHPExcel->getActiveSheet()->setCellValue('B'.($n+2) ,isset($model->sku)?$model->sku:'');
                $objectPHPExcel->getActiveSheet()->setCellValue('C'.($n+2) ,isset($model->supplier_name)?$model->supplier_name:'');
                $objectPHPExcel->getActiveSheet()->setCellValue('D'.($n+2) ,isset($model->name)?$model->name:'');
                $objectPHPExcel->getActiveSheet()->setCellValue('E'.($n+2) ,isset($model->price)?$model->price:0);
                $objectPHPExcel->getActiveSheet()->setCellValue('F'.($n+2) ,self::countCtq($model->sku,$model->purchase_type));
                $objectPHPExcel->getActiveSheet()->setCellValue('G'.($n+2) ,\app\models\PurchaseOrderTaxes::getABDTaxes($model->sku,$model->pur_number).'%');
                //采购金额
                $objectPHPExcel->getActiveSheet()->setCellValue('H'.($n+2) ,self::countPrice($model->sku,$model->purchase_type));
                $objectPHPExcel->getActiveSheet()->setCellValue('I'.($n+2) ,\app\models\PurchaseOrderItems::getQuotes($model->sku,$model->purchase_type));
                $objectPHPExcel->getActiveSheet()->setCellValue('J'.($n+2) ,$model->account_type ? \app\services\SupplierServices::getSettlementMethod($model->account_type) : '');
                $objectPHPExcel->getActiveSheet()->setCellValue('K'.($n+2) ,!empty($model->product_category_id)?\app\services\BaseServices::getCategory($model->product_category_id):'');

                $n = $n +1;
            }

            for ($a = 65; $a<78; $a++) {
                $objectPHPExcel->getActiveSheet($i)->getColumnDimension(chr($a))->setWidth(15);
                $objectPHPExcel->getActiveSheet($i)->getStyle( chr($a) . "1")->getFont()->setBold(true);
            }
            $objectPHPExcel->getActiveSheet($i)->getColumnDimension('C')->setWidth(30);
            $objectPHPExcel->getActiveSheet($i)->getColumnDimension('D')->setWidth(30);
        }




        //设置样式
        $objectPHPExcel->getActiveSheet()->getStyle('A1:K'.($n+2))->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        ob_end_clean();
        ob_start();
        header("Content-type:application/vnd.ms-excel;charset=UTF-8");
        header('Content-Type : application/vnd.ms-excel');
        header('Content-Disposition:attachment;filename="'.'采购订单统计-'.date("Y年m月j日").'.xls"');
        $objWriter= \PHPExcel_IOFactory::createWriter($objectPHPExcel,'Excel5');
        $objWriter->save('php://output');
        die;
    }

    //历史数据
    public function actionHistory(){
        $sku = Yii::$app->request->get('sku');
        $purchase_type = Yii::$app->request->get('purchase_type');

        //查询历史采购数据
        $items = PurchaseOrderItems::find()
            ->alias('items')
            ->select('items.*,order.supplier_name')
            ->leftJoin('pur_purchase_order order','order.pur_number=items.pur_number')
            ->andFilterWhere(['in','order.purchas_status',['3', '5', '6', '7', '8', '9', '10']])
            ->andFilterWhere(['order.purchase_type'=>$purchase_type,'items.sku'=>$sku])
            ->orderBy('items.id desc')
            ->all();

        //组装参数
        $history = [];
        $total = 0;
        $arr = [];
        if($items){
            /*foreach ($items as $item){
                if(!isset($history[$item->supplier_name])){
                    $history[$item->supplier_name]['count'] = 1;
                    $history[$item->supplier_name]['price'][] = $item->price;
                    $history[$item->supplier_name]['ctq'][] = $item->ctq;
                }else{
                    $history[$item->supplier_name]['count'] += 1;
                    $history[$item->supplier_name]['price'][] = $item->price;
                    $history[$item->supplier_name]['ctq'][] = $item->ctq;
                }
            }*/
            $total = count($items);
            foreach ($items as $item){
                if(!isset($history[$item->supplier_name][$item->price."_".$item->ctq])){
                    $arr['count'] = 1;
                    $arr['price'] = $item->price;
                    $arr['ctq'] = $item->ctq;
                    $history[$item->supplier_name][$item->price."_".$item->ctq] = $arr;
                }else{
                    $history[$item->supplier_name][$item->price."_".$item->ctq]['count'] += 1;
                    $total -= 1;
                }
            }
        }

        return $this->renderAjax('history',[
            'sku' => $sku,
            'history' => $history,
            'total' => $total,
            'history_total' => count($history)
        ]);
    }

    /**
     *
     * DATE: 2018-08-09
     * @param $sku
     * @param $purchase_type采购类型(1国内2海外3FBA)
     */
    public static function countCtq($sku , $purchase_type){
        //查询历史采购数据
        $total_ctq = PurchaseOrderItems::find()
            ->alias('items')
            ->select('sum(items.ctq) as total_ctq')
            ->leftJoin('pur_purchase_order order','order.pur_number=items.pur_number')
            ->andFilterWhere(['in','order.purchas_status',['3', '5', '6', '7', '8', '9', '10']])
            ->andFilterWhere(['order.purchase_type'=>$purchase_type,'items.sku'=>$sku])
            ->scalar();

        return isset($total_ctq)?$total_ctq:0;
    }

    /**
     *
     * DATE: 2018-08-09
     * @param $sku
     * @param $purchase_type采购类型(1国内2海外3FBA)
     */
    public static function countPrice($sku , $purchase_type){
        //查询历史采购数据
        $total_price = PurchaseOrderItems::find()
            ->alias('items')
            ->select('sum(items.ctq*items.price) as total_ctq')
            ->leftJoin('pur_purchase_order order','order.pur_number=items.pur_number')
            ->andFilterWhere(['in','order.purchas_status',['3', '5', '6', '7', '8', '9', '10']])
            ->andFilterWhere(['order.purchase_type'=>$purchase_type,'items.sku'=>$sku])
            ->scalar();

        return isset($total_price)?$total_price:0;
    }
}
