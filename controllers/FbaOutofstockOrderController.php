<?php
namespace app\controllers;

use app\models\AmazonOutofstockOrder;
use app\models\PurchaseOrder;
use Yii;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/14
 * Time: 17:22
 */

class FbaOutofstockOrderController extends BaseController {
    public function actionIndex(){
        $accessUser1 = Yii::$app->authManager->getUserIdsByRole('FBA采购组');
        $accessUser2 = Yii::$app->authManager->getUserIdsByRole('FBA采购经理组');
        $accessUser = array_unique(array_merge($accessUser1,$accessUser2));
        $searchModel = new AmazonOutofstockOrder();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'accessUser'=>$accessUser
        ]);
    }

    public function actionCreateOrder($ids){
        if(Yii::$app->request->isAjax&&Yii::$app->request->isGet){
            $datas = AmazonOutofstockOrder::getCreateOrderData($ids);
            if($datas['status']=='error'){
                Yii::$app->end($datas['message']);
            }
            return $this->renderAjax('create-order',['datas'=>$datas,'ids'=>$ids]);
        }
        if(Yii::$app->request->isPost){
            $orderIds = Yii::$app->request->getBodyParam('AmazonOrderId');
            $response = PurchaseOrder::createFbaErpOrder($orderIds);
            Yii::$app->getSession()->setFlash($response['status'],$response['message']);
            return $this->redirect(Yii::$app->request->referrer);
        }
    }

    public function actionNote($id){
        if(Yii::$app->request->isAjax&&Yii::$app->request->isGet){
            $model = AmazonOutofstockOrder::find()->where(['id'=>$id])->one();
            return $this->renderAjax('note',['model'=>$model]);
        }
        if(Yii::$app->request->isPost){
            $formData = Yii::$app->request->getBodyParam('AmazonOutofstockOrder');
            $model = AmazonOutofstockOrder::find()->where(['id'=>$id])->one();
            $model->note =$model->note.$formData['addnote'].'-'.Yii::$app->user->identity->username.date('Y-m-d H:i:s',time()).'<br/>';
            if($model->save()==false){
                Yii::$app->getSession()->setFlash('error','备注新增失败');
            }else{
                Yii::$app->getSession()->setFlash('success','备注新增成功');
            }
            return $this->redirect(Yii::$app->request->referrer);
        }
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

        $id = Yii::$app->request->get('ids');
        $id = strpos($id,',')?explode(',',trim($id,',')):$id;

        $params = [];
        $searchModel = new AmazonOutofstockOrder();
        if (!empty($id)) {
            $params['AmazonOutofstockOrder']['id'] = $id;
            $query = $searchModel->search($params, true);
            $data  = $query->all();
        }else {
            $params['AmazonOutofstockOrder'] = Yii::$app->request->queryParams;
            $query                           = $searchModel->search($params, true);
            $data                            = $query->all();
        }


        $objectPHPExcel = new \PHPExcel();
        $objectPHPExcel->setActiveSheetIndex(0);
        $n = 0;

        //表格头的输出
        $cell_value = ['亚马逊订单号','需求单号','Sku','绑定供应商','采购员','产品名称','订单数量','缺货数量','付款时间','备注','状态','创建时间','更新时间'];
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
            $objectPHPExcel->getActiveSheet()->setCellValue('A'.($n+2) ,$v->amazon_order_id);
            $objectPHPExcel->getActiveSheet()->setCellValue('B'.($n+2) ,$v->demand_number);
            $objectPHPExcel->getActiveSheet()->setCellValue('C'.($n+2) ,$v->sku);
            $objectPHPExcel->getActiveSheet()->setCellValue('D'.($n+2) ,!empty($v->product->defaultSupplierDetail) ? $v->product->defaultSupplierDetail->supplier_name :'');
            $objectPHPExcel->getActiveSheet()->setCellValue('E'.($n+2) ,$v->defaultSupplierLine?\app\models\PurchaseCategoryBind::getBuyer($v->defaultSupplierLine->first_product_line):'');
            $objectPHPExcel->getActiveSheet()->setCellValue('F'.($n+2) ,!empty($v->product) ? !empty($v->product->desc->title) ?  $v->product->desc->title : '无该产品名称' : '采购系统无该产品');
            $objectPHPExcel->getActiveSheet()->setCellValue('G'.($n+2) ,$v->purchase_num);
            $objectPHPExcel->getActiveSheet()->setCellValue('H'.($n+2) ,$v->outofstock_num);
            $objectPHPExcel->getActiveSheet()->setCellValue('I'.($n+2) ,$v->pay_time);
            $objectPHPExcel->getActiveSheet()->setCellValue('J'.($n+2) ,$v->note);
            $objectPHPExcel->getActiveSheet()->setCellValue('K'.($n+2) ,isset($statusArray[$v->status]) ? $statusArray[$v->status] : '未知状态');
            $objectPHPExcel->getActiveSheet()->setCellValue('L'.($n+2) ,$v->create_time);
            $objectPHPExcel->getActiveSheet()->setCellValue('M'.($n+2) ,$v->update_time);


            $n = $n +1;
        }

        for ($i = 65; $i<78; $i++) {
            $objectPHPExcel->getActiveSheet()->getColumnDimension(chr($i))->setWidth(15);
            $objectPHPExcel->getActiveSheet()->getStyle( chr($i) . "1")->getFont()->setBold(true);
        }
        $objectPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        $objectPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);

        //设置样式
        $objectPHPExcel->getActiveSheet()->getStyle('A1:M'.($n+2))->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        ob_end_clean();
        ob_start();
        header("Content-type:application/vnd.ms-excel;charset=UTF-8");
        header('Content-Type : application/vnd.ms-excel');
        header('Content-Disposition:attachment;filename="'.'FBA停售缺货产品-'.date("Y年m月j日").'.xls"');
        $objWriter= \PHPExcel_IOFactory::createWriter($objectPHPExcel,'Excel5');
        $objWriter->save('php://output');
        die;
    }
}