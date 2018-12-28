<?php
namespace app\modules\manage\controllers;

use app\config\Vhelper;
use app\controllers\BaseController;
use app\models\Supplier;
use app\modules\manage\models\SupplierQuotesManage;
use app\modules\manage\models\SupplierQuotesManageSearch;
use Yii;
use yii\helpers\Html;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/19
 * Time: 18:15
 */
class SupplierQuotesAuditController extends BaseController{
    public function actionIndex(){
        $searchModel = new SupplierQuotesManageSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,false);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
        return $this->render('index');
    }

    public function actionCheckResult(){
        if(Yii::$app->request->isAjax&&Yii::$app->request->isGet){
            $id = Yii::$app->request->getQueryParam('id');
            $model = SupplierQuotesManage::find()->where(['id'=>$id])->one();
            if($model->status !=0){
                Yii::$app->end('当前数据已经开始审核');
            }
            return $this->renderAjax('check-result-form',['model'=>$model]);
        }
        if(Yii::$app->request->isPost){
            $response = SupplierQuotesManage::saveCheckResult(Yii::$app->request->getBodyParam('SupplierQuotesManage'));
            Yii::$app->getSession()->setFlash($response['status'],$response['message']);
            return $this->redirect(Yii::$app->request->referrer);
        }
    }

    public function actionSampleCommit(){
        if(Yii::$app->request->isAjax&&Yii::$app->request->isPost){
            $response = SupplierQuotesManage::sampleCommit(Yii::$app->request->getBodyParam('id'));
            Yii::$app->getSession()->setFlash($response['status'],$response['message']);
            return $this->redirect(Yii::$app->request->referrer);
        }
    }

    public function actionSampleResult(){
        if(Yii::$app->request->isAjax&&Yii::$app->request->isGet){
            $id = Yii::$app->request->getQueryParam('id');
            $model = SupplierQuotesManage::find()->where(['id'=>$id])->one();
            if(empty($model)||$model->status!=2){
                Yii::$app->end('当前状态无法提交样品检验结果');
            }
            return $this->renderAjax('sample-result-form',['model'=>$model]);
        }
        if(Yii::$app->request->isPost){
            $response = SupplierQuotesManage::saveSampleResult(Yii::$app->request->getBodyParam('SupplierQuotesManage'));
            Yii::$app->getSession()->setFlash($response['status'],$response['message']);
            return $this->redirect(Yii::$app->request->referrer);
        }
    }

    public function actionExport(){
        set_time_limit(0);
        ini_set('memory_limit', '1024M');
        $ids = Yii::$app->request->getQueryParam('ids','');
        $model = new  SupplierQuotesManageSearch();
        if(empty($ids)){
            $query = $model->search(Yii::$app->getSession()->get('quotes-manage-search'),true);
        }else{
            $query = $model->search('',true);
            $query ->andFilterWhere(['in','t.id',explode(',',$ids)]);
        }
        $datas = $query->all();
        $objectPHPExcel = new \PHPExcel();
        $objectPHPExcel->setActiveSheetIndex(0);
        $n = 0;
        //报表头的输出
        $objectPHPExcel->getActiveSheet()->mergeCells('A1:O1'); //合并单元格
        $objectPHPExcel->getActiveSheet()->mergeCells('A1:A2');
        $objectPHPExcel->getActiveSheet()->setCellValue('A1','供应商报价数据');  //设置表标题
        $objectPHPExcel->setActiveSheetIndex(0)->getStyle('A1')->getFont()->setSize(24); //设置字体大小
        $objectPHPExcel->setActiveSheetIndex(0)->getStyle('A1')
            ->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $cell_value = ['','产品图','产品线','SKU','产品名称','默认供应商','报价供应商','现单价','供应商报价','是否现货','交期(天)','报价时间','原因','状态','审核时间'];
        foreach ($cell_value as $k => $v) {
            $objectPHPExcel->setActiveSheetIndex(0)->setCellValue(chr($k+65) . '3',$v);
        }
        //设置表头居中
        $objectPHPExcel->getActiveSheet()->getStyle('A3:T3')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //设置数据水平靠左和垂直居中
        $objectPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $objectPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        foreach ( $datas as $data )
        {
            //明细的输出
            $objectPHPExcel->getActiveSheet()->setCellValue('A'.($n+4) ,$n+1);
            $url = Vhelper::downloadImg($data->uploadimgs,$data->uploadimgs);
            if($url ){
                $img=new \PHPExcel_Worksheet_Drawing();
                $img->setPath($url);//写入图片路径
                $img->setHeight(50);//写入图片高度
                $img->setWidth(100);//写入图片宽度
                $img->setOffsetX(2);//写入图片在指定格中的X坐标值
                $img->setOffsetY(2);//写入图片在指定格中的Y坐标值
                $img->setRotation(1);//设置旋转角度
                $img->getShadow()->setDirection(50);//
                $img->setCoordinates('B'.($n+4));//设置图片所在表格位置
                $objectPHPExcel->getActiveSheet()->getRowDimension(($n+4))->setRowHeight(80);
                $img->setWorksheet($objectPHPExcel->getActiveSheet());//把图片写到当前的表格中
            }
            $objectPHPExcel->getActiveSheet()->setCellValue('C'.($n+4) ,$data->linelist_cn_name);
            $objectPHPExcel->getActiveSheet()->setCellValue('D'.($n+4) ,$data->sku);
            $objectPHPExcel->getActiveSheet()->setCellValue('E'.($n+4) ,!empty($data->product->desc) ?$data->product->desc->title:'');
            $objectPHPExcel->getActiveSheet()->setCellValue('F'.($n+4) ,!empty($data->product->defaultSupplierDetail) ? $data->product->defaultSupplierDetail->supplier_name : '');
            $objectPHPExcel->getActiveSheet()->setCellValue('G'.($n+4) ,!empty($data->manageSupplier) ? $data->manageSupplier->supplier_name : '');
            $objectPHPExcel->getActiveSheet()->setCellValue('H'.($n+4) ,!empty($data->product->supplierQuote) ? $data->product->supplierQuote->supplierprice : '');
            $supplierPrice='';
            if(!empty($data->quotesItems)){
                if($data->type==1){
                    foreach ($data->quotesItems as $item){
                        $supplierPrice .= $item->supplier_price."\r\n";
                    }
                }
                if($data->type==2){
                    foreach ($data->quotesItems as $item){
                        $supplierPrice .= $item->amount_min.'-'.$item->amount_max.'件：单价：'.$item->supplier_price."元\r\n";
                    }
                }
            }
            $objectPHPExcel->getActiveSheet()->setCellValue('I'.($n+4) ,$supplierPrice);
            $objectPHPExcel->getActiveSheet()->setCellValue('J'.($n+4) ,$data->is_in_stock==1?'是':'否');
            $objectPHPExcel->getActiveSheet()->setCellValue('K'.($n+4) ,$data->delivery_time);
            $objectPHPExcel->getActiveSheet()->setCellValue('L'.($n+4) ,$data->create_time);
            $objectPHPExcel->getActiveSheet()->setCellValue('M'.($n+4) ,$data->reason);
            $statsuArray=[
                0=>'待审核',
                1=>'待拿样',
                2=>'样品检测中',
                3=>'已取消',
                4=>'完成',
                5=>'审核失败',
                6=>'样品检测失败'
            ];
            $objectPHPExcel->getActiveSheet()->setCellValue('N'.($n+4) ,isset($statsuArray[$data->status]) ? $statsuArray[$data->status] :  '未知状态');
            $objectPHPExcel->getActiveSheet()->setCellValue('O'.($n+4) ,$data->check_time);
            $objectPHPExcel->getActiveSheet()->getStyle('I'.($n+4))->getAlignment()->setWrapText(true);
            $n = $n +1;

        }

        $objectPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
        $objectPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
        $objectPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(25);

        //设置样式
        $objectPHPExcel->getActiveSheet()->getStyle('B2:O'.($n+4))->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        ob_end_clean();
        ob_start();
        header("Content-type:application/vnd.ms-excel;charset=UTF-8");
        header('Content-Type : application/vnd.ms-excel');
        header('Content-Disposition:attachment;filename="'.'供应商报价信息-'.date("Y年m月j日").'.xls"');
        $objWriter= \PHPExcel_IOFactory::createWriter($objectPHPExcel,'Excel5');
        $objWriter->save('php://output');
        die;
    }

    public function actionCompareQuotes(){
        $ids = Yii::$app->request->getQueryParam('ids');
        $datas = SupplierQuotesManage::find()->select('sku,create_supplier_code')
                ->where(['in','id',explode(',',$ids)])->asArray()->all();
        $supplier_code = array_unique(array_column($datas,'create_supplier_code'));
        if(count($supplier_code)>4){
            Yii::$app->end('最多只能有四家供应商加入对比');
        }
        $skus = array_unique(array_column($datas,'sku'));
        $compareDatas = SupplierQuotesManage::getCompareDatas($skus,$supplier_code);
        return $this->renderAjax('compare-quotes',['compareDatas'=>$compareDatas,'supplier_code'=>$supplier_code]);
    }
}