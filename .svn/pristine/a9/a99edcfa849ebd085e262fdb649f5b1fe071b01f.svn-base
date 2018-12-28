<?php

namespace app\controllers;


use app\config\Vhelper;
use app\models\Product;
use app\models\ProductDescription;
use app\models\ProductImgDownload;
use app\models\ProductProvider;
use app\models\ProductSourceStatus;
use app\models\Supplier;
use app\models\SupplierLog;
use app\models\SupplierQuotes;
use app\models\SupplierQuotesSearch;
use app\models\SupplierUpdateApply;
use app\models\SupplierUpdateApplySearchSearch;
use app\models\Tongji;
use app\services\BaseServices;
use app\services\SupplierGoodsServices;
use Yii;
use app\models\SupplierGoodsSearch;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\UploadedFile;
use app\config\MyExcel;
use m35\thecsv\theCsv;

/**
 * Created by PhpStorm.
 * User: ztt
 * Date: 2017/3/23 0023
 * Time: 18:41
 */
class SupplierGoodsController extends BaseController
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
     * Lists all Stockin models.
     * @return mixed
     */
    public function actionIndex()
    {

        $searchModel = new SupplierGoodsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        //判断当前登录账户是否为销售
        $role = Yii::$app->authManager->getRolesByUser(Yii::$app->user->identity->id);//383     Yii::$app->user->identity->id
        $usernameRule = ['张翔宇'];
        $is_visible = 1;
        if ($role && is_array($role)&&!in_array(Yii::$app->user->identity->username,$usernameRule)) {
            foreach ($role as $key => $value) {
                if(preg_match('/销售/',$key)){
                    $is_visible = 0;
                }
            }
        }
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'is_visible' => $is_visible
        ]);
    }

    /**
     * 查看历史报价
     * Displays a single Stockin model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($code,$sku)
    {
        $model= SupplierQuotes::find()->where(['suppliercode'=>$code,'product_sku'=>$sku])->all();

        return $this->renderAjax('allhistor', [
            'model' => $model,
        ]);
    }

    /**
     * 添加产品/报价
     * Creates a new SupplierQuotes model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($sku=null)
    {
        $model        = new SupplierQuotes();

        if ($model->load(Yii::$app->request->post()) && $model->save())
        {
            if(Yii::$app->request->post()['SupplierQuotes']['default_vendor']=='1') {

                $status = ProductProvider::SaveOne(Yii::$app->request->post()['SupplierQuotes']);
                if ($status) {
                    Yii::$app->getSession()->setFlash('success', '恭喜你！添加成功');
                } else {
                    Yii::$app->getSession()->setFlash('error', '添加失败，数据中存在了sku与供应商这条记录');
                }
            }
            Yii::$app->getSession()->setFlash('success', '恭喜你！添加成功');
            return $this->redirect(['index']);
        } else {
             !empty($sku) ? $model->product_sku =$sku : '';
            return $this->render('create', [
                'model' => $model,
            ]);
        }

    }

    /**ajax进行验证
     * @return array
     */
    public function actionValidateForm () {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $model = new SupplierQuotes();
        $model->load(Yii::$app->request->post());
        return \yii\widgets\ActiveForm::validate($model);
    }

    /**
     * 修改操作
     * Updates an existing Supplier model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->default_vendor=ProductProvider::find()->select('is_supplier')->where(['sku'=>$model->product_sku,'supplier_code'=>$model->suppliercode])->scalar();
        if ($model->load(Yii::$app->request->post()) && $model->save())
        {
            $sd = Yii::$app->request->post()['SupplierQuotes'];
            if ($sd['default_vendor']=='1')
            {
                ProductProvider::SaveOne($sd);
            }
            Yii::$app->getSession()->setFlash('success','恭喜你！更新成功');
            return $this->redirect(['index']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Finds the Stockin model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Stockin the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SupplierQuotes::findOne($id)) !== null)
        {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    /**
     * 删除操作
     * @return string
     */
    public function actionDelete($id)
    {
        $model= $this->findModel($id);
        //检测当前此条数据是不是默认供应商
        $default = ProductProvider::findOne(['sku' => $model->product_sku,'supplier_code' => $model->suppliercode,'is_supplier'=>1]);
        if (!empty($default))
        {
            //不为空，表示是默认供应商,当前默认供应商要删除了，所以要找一条修改成默认供应商
            $f = ProductProvider::findOne(['sku' => $model->product_sku,'is_supplier'=>0]);
            if(!empty($f))
            {
                $f->is_supplier=1;
                $f->save();
                $model->delete();
            } else {

                Yii::$app->getSession()->setFlash('error','当前默认供应商被删除，没有可设定的供应商！');
            }
        } else {
            $model->delete();
            Yii::$app->getSession()->setFlash('success','恭喜你!删除成功');
        }
        return $this->redirect(['index']);
    }

    /**
     * 所有的历史报价
     */
    public function actionAllHistoricalOffer($sku)
    {
        $model = SupplierQuotes::find()->where(['product_sku'=>$sku])->all();
        return $this->renderAjax('allhistor',['model'=>$model]);

    }

    /**
     * 批量更新所有的默认供应商
     */
    public function actionAllDefaultSupplier()
    {
        $model = new SupplierQuotes();
        if (Yii::$app->request->isPost)
        {
            $models= $model->saveSupplierOne(Yii::$app->request->post());

            if ($models['error'] == 0)
            {
                $msg ="更新失败,sku:{$models['sku']}并没有存的供应商报价";
                Yii::$app->getSession()->setFlash('error',"{$msg}");

            } else {

                Yii::$app->getSession()->setFlash('success','恭喜你！更新成功');
            }
            return $this->redirect(['index']);
        } else {
            $id = Yii::$app->request->get('id');
            return $this->renderAjax('supplier', [
                'model' => $model,
                'id'    =>$id,
            ]);
        }
    }

    //提交修改申请
    public function  actionApply(){
        if (Yii::$app->request->isPost&&Yii::$app->request->isAjax)
        {
            $datas = Yii::$app->request->getBodyParam('data');
            $type  = Yii::$app->request->getBodyParam('type');
            $remark  = Yii::$app->request->getBodyParam('remark');
            $saveResult = SupplierUpdateApply::saveApply($datas,$type,$remark);
            Yii::$app->getSession()->setFlash($saveResult['status'],$saveResult['message'].'其中有'.$saveResult['count'].'不符合申请要求');
            //写入供应商日志
            SupplierLog::saveSupplierLog('supplier-goods/apply',$saveResult['status'].':'.implode(',',ArrayHelper::getColumn($datas,'sku')).$saveResult['message'].'count:'.$saveResult['count'].'type:'.$type);
            return $this->redirect(Yii::$app->request->referrer);

        }
    }

    //产品数据导出
    public function actionExport(){
        set_time_limit(0);
        $limit = 5000;
        $form = Yii::$app->request->queryParams;
        $dataProvider = Product::find();
        if(empty($form['suppliercode'])&&empty($form['sku'])&&empty($form['line'])){
            Yii::$app->session->setFlash('error','请筛选后再导出');
            return $this->redirect('index');
        }
        if(!empty($form['sku'])){
            $dataProvider->andFilterWhere(['pur_product.sku'=>$form['sku']]);
        }
        if(!empty($form['line'])){
            if(!Yii::$app->cache->get('export_supplier_goods'.$form['line'])){
                $offset = 0;
            }else{
                $offset = Yii::$app->cache->get('export_supplier_goods'.$form['line']);
            }
            $dataProvider->andFilterWhere(['in','pur_product.product_linelist_id',BaseServices::getProductLineChild($form['line'])]);
        }
        if(!empty($form['product_status'])){
            $dataProvider->andFilterWhere(['pur_product.product_status'=>$form['product_status']]);
        }
        if(!empty($form['suppliercode'])){
            $dataProvider->joinWith('defaultSupplier');
            $dataProvider->andFilterWhere(['pur_product_supplier.supplier_code'=>$form['suppliercode']]);
        }
        $dataProvider->andFilterWhere(['not in','pur_product.product_status',['0','7']]);
        $dataProvider->andFilterWhere(['<>','product_is_multi',2]);
        $objectPHPExcel = new \PHPExcel();
        $objectPHPExcel->setActiveSheetIndex(0);
        $n = 0;
        //报表头的输出
        $objectPHPExcel->getActiveSheet()->mergeCells('B1:K1');
        $objectPHPExcel->getActiveSheet()->setCellValue('B1','产品信息表');
        $objectPHPExcel->setActiveSheetIndex(0)->getStyle('B1')->getFont()->setSize(24);
        $objectPHPExcel->setActiveSheetIndex(0)->getStyle('B1')
            ->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objectPHPExcel->setActiveSheetIndex(0)->getStyle('K2')
            ->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        //表格头的输出
        $objectPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
        $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('B3','序号');
        $objectPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(6.5);
        $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('C3','图片');
        $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('D3','SKU');
        $objectPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objectPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('E3','产品状态');
        $objectPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('F3','sku创建时间');
        $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('G3','产品品名');
        $objectPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(5);
        $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('H3','单价');
        $objectPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('I3','供货商');
        $objectPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('J3','产品线');
        $objectPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
        $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('K3','采购链接');
        $objectPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
        $objectPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        if(!isset($offset)){
            $offset=0;
            $limit = 20000;
        }
        $s = $dataProvider->offset($offset)->limit($limit)->all();
        foreach ( $s as $product )
        {
            $offset++;
            //明细的输出
            $objectPHPExcel->getActiveSheet()->setCellValue('B'.($n+4) ,$n+1);
            if(empty($form['line'])){
                $url = Vhelper::downloadImg($product->sku,$product->uploadimgs);
                if($url){
                    $img=new \PHPExcel_Worksheet_Drawing();
                    $img->setPath($url);//写入图片路径
                    $img->setHeight(50);//写入图片高度
                    $img->setWidth(100);//写入图片宽度
                    $img->setOffsetX(2);//写入图片在指定格中的X坐标值
                    $img->setOffsetY(2);//写入图片在指定格中的Y坐标值
                    $img->setRotation(1);//设置旋转角度
                    //$img->getShadow()->setVisible(true);//
                    $img->getShadow()->setDirection(50);//
                    $img->setCoordinates('C'.($n+4));//设置图片所在表格位置
                    $img->setWorksheet($objectPHPExcel->getActiveSheet());//把图片写到当前的表格中
                }
            }
            $objectPHPExcel->getActiveSheet()->setCellValue('D'.($n+4) ,$product->sku);
            $objectPHPExcel->getActiveSheet()->setCellValue('E'.($n+4) ,SupplierGoodsServices::getProductStatus($product->product_status));
            $objectPHPExcel->getActiveSheet()->setCellValue('F'.($n+4) ,$product->create_time);
            $objectPHPExcel->getActiveSheet()->setCellValue('G'.($n+4) ,!empty($product->desc->title)?$product->desc->title:'');
            $objectPHPExcel->getActiveSheet()->setCellValue('H'.($n+4) ,!empty($product->supplierQuote) ? $product->supplierQuote->supplierprice : '');
            $objectPHPExcel->getActiveSheet()->setCellValue('I'.($n+4) ,!empty($product->defaultSupplierDetail) ? $product->defaultSupplierDetail->supplier_name : '');
            $objectPHPExcel->getActiveSheet()->setCellValue('J'.($n+4) ,!empty($product->product_linelist_id) ? BaseServices::getProductLine($product->product_linelist_id): '');
            $objectPHPExcel->getActiveSheet()->setCellValue('K'.($n+4) ,!empty($product->supplierQuote) ? $product->supplierQuote->supplier_product_address : '');
            if(empty($form['line'])){
                $objectPHPExcel->getActiveSheet()->getRowDimension($n+4)->setRowHeight(100);
            }
            $n = $n +1;
        }
        if(!empty($form['line'])){
            Yii::$app->cache->set('export_supplier_goods'.$form['line'],$offset,86400);
        }
        //设置样式
        $objectPHPExcel->getActiveSheet()->getStyle('B2:K'.($n+4))->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objectPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        ob_end_clean();
        ob_start();
        header("Content-type:application/vnd.ms-excel;charset=UTF-8");
        header('Content-Type : application/vnd.ms-excel');
        header('Content-Disposition:attachment;filename="'.'产品信息表-'.date("Y年m月j日").'.xls"');
        $objWriter= \PHPExcel_IOFactory::createWriter($objectPHPExcel,'Excel5');
        $objWriter->save('php://output');

    }


    //产品备注添加
    public function actionNote(){
        if(Yii::$app->request->isAjax && Yii::$app->request->isPost){
            try{
                $note      = Yii::$app->request->getBodyParam('note');
                $productId = Yii::$app->request->getBodyParam('id');
                if(empty($productId)){
                    throw new HttpException(500,'参数缺失！');
                }
                $product = Product::find()->where(['id'=>$productId])->one();
                if(empty($product)){
                    throw new HttpException(500,'产品数据异常！');
                }
                $product->note = $note;
                if($product->save() == false){
                    throw new HttpException(500,'产品备注添加失败！');
                }
                Yii::$app->getSession()->setFlash('success','产品备注添加成功！');
                SupplierLog::saveSupplierLog('supplier-goods/note','success:productId-'.$productId.'-note-'.$note);
            }catch(HttpException $e){
                Yii::$app->getSession()->setFlash('error',$e->getMessage());
                SupplierLog::saveSupplierLog('supplier-goods/note','error:productId-'.$productId.'-note-'.$note);
            }
            return $this->redirect(Yii::$app->request->referrer);
        }
    }

    //产品提交报价修改导入
    public function actionExQuotes(){
        set_time_limit(0);
        $model = new  SupplierQuotes();
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
                $defaultSupplier = ProductProvider::find()->where(['sku'=>mb_convert_encoding(trim($datas[0]),'utf-8','gbk'),'is_supplier'=>1])->one();
                $quotes = !empty($defaultSupplier) ? $defaultSupplier->quotes_id : 0;
                $supplier = Supplier::find()->andFilterWhere(['supplier_name'=>mb_convert_encoding(trim($datas[1]),'utf-8','gbk')])->one();
                $supplierCode = empty(mb_convert_encoding(trim($datas[2]),'utf-8','gbk'))? !empty($supplier) ? $supplier->supplier_code : '' : mb_convert_encoding(trim($datas[2]),'utf-8','gbk');
                if(empty($supplierCode)){
                    $str .= ($line_number+1).',';
                    continue;
                }
                if(empty(mb_convert_encoding(trim($datas[0]),'utf-8','gbk'))){
                    $str .= ($line_number+1).',';
                    continue;
                }
                if(empty(mb_convert_encoding(trim($datas[3]),'utf-8','gbk'))){
                    $str .= ($line_number+1).',';
                    continue;
                }
                if(empty(mb_convert_encoding(trim($datas[4]),'utf-8','gbk'))){
                    $str .= ($line_number+1).',';
                    continue;
                }
                $Name[$line_number]['quoteId'] = $quotes;
                $Name[$line_number]['sku'] = mb_convert_encoding(trim($datas[0]),'utf-8','gbk');
                $Name[$line_number]['suppliercode'] = $supplierCode;
                $Name[$line_number]['price'] = mb_convert_encoding(trim($datas[3]),'utf-8','gbk');
                $Name[$line_number]['link'] = mb_convert_encoding(trim($datas[4]),'utf-8','gbk');
                $line_number++;
            }
            $message = '';
            if(!empty($Name)){
                $saveResult = SupplierUpdateApply::saveApply($Name);
                Yii::$app->getSession()->setFlash($saveResult['status'],$saveResult['message'].'其中有'.$saveResult['count'].'已有待审核或是备用供应商');
                $message .= $saveResult['status'].':sku-'.implode(',',ArrayHelper::getColumn($Name,'sku')).$saveResult['message'].'其中有'.$saveResult['count'].'已有待审核或是备用供应商in';
            }
            if(!empty($str)){
                Yii::$app->getSession()->setFlash('warning','第'.$str.'行关键信息缺失');
                $message .='warning:第'.$str.'行关键信息缺失';
            }
            SupplierLog::saveSupplierLog('supplier-goods/ex-quotes',$message);
            fclose($file);
            return $this->redirect(['index']);
        } else {
            return $this->renderAjax('addfile', ['model' => $model]);
        }
    }

    //展示备用供应商
    public function actionStandbySupplier(){
        $searchModel = new ProductProvider();
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());
        return $this->renderAjax('standby-supplier',[
            'searchModel'=>$searchModel,
            'dataProvider'=>$dataProvider
        ]);
    }

    //删除备用供应商
    public function actionDeleteStandby(){
        if(Yii::$app->request->isPost && Yii::$app->request->isAjax){
            $ids = Yii::$app->request->getBodyParam('ids');
            if(!empty($ids)){
                foreach($ids as $id){
                    $defaulrSupplier = ProductProvider::find()->andFilterWhere(['id'=>$id])->one();
                    if(!empty($defaulrSupplier)){
                        $defaulrSupplier->is_supplier = 0;
                        $defaulrSupplier->save(false);
                        $defaulrQuotes = SupplierQuotes::find()->andFilterWhere(['id'=>$defaulrSupplier->quotes_id])->one();
                        if(!empty($defaulrQuotes)){
                            $defaulrQuotes->status = 2;
                            $defaulrQuotes->save(false);
                        }
                    }
                }
            }
            echo json_encode(['status'=>'success']);
        }
    }

    //修改货源状态
    public function actionChangeSourceStatus()
    {
        if(Yii::$app->request->isPost&&Yii::$app->request->isAjax){
            $sourceStatus = Yii::$app->request->getBodyParam('change_value');
            $sku          = Yii::$app->request->getBodyParam('sku');
            if(empty($sourceStatus)||empty($sku)){
                echo json_encode(['status'=>'error','message'=>'提交数据有误']);
                Yii::$app->end();
            }
            $response = Product::changeSourceStatus([['sku'=>$sku,'source_status'=>$sourceStatus]]);
            echo json_encode($response);
            Yii::$app->end();
        }
    }

    //批量修改货源状态
    public function actionBatchChangeSourceStatus()
    {
        if(Yii::$app->request->isAjax&&Yii::$app->request->isGet){
            $skus = Yii::$app->request->getQueryParam('skus');
            if(empty($skus)){
                Yii::$app->end('至少选择一个sku');
            }
            $model = new ProductSourceStatus();
            return $this->renderAjax('change-source-status',['model'=>$model,'skus'=>$skus]);
        }
        if(Yii::$app->request->isPost){
            $form = Yii::$app->request->getBodyParam('ProductSourceStatus');
            $skus = isset($form['sku']) ? $form['sku'] :'';
            $sourcingStatus = isset($form['sourcing_status']) ? $form['sourcing_status'] :'';
            if(empty($sourcingStatus)||empty($skus)){
                Yii::$app->getSession()->setFlash('error','提交数据有误',false);
                return $this->redirect(Yii::$app->request->referrer);
            }
            $skuArray = explode(',',$skus);
            $changeDatas = [];
            foreach ($skuArray as $key=>$sku){
                $changeDatas[$key]['sku'] =$sku;
                $changeDatas[$key]['source_status']= $sourcingStatus;
            }
            $response = Product::changeSourceStatus($changeDatas);
            Yii::$app->getSession()->setFlash($response['status'],$response['message']);
            return $this->redirect(Yii::$app->request->referrer);
        }
    }
}
