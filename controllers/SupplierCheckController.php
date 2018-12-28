<?php
namespace app\controllers;

use app\assets\AppAsset;
use app\commands\zipfile;
use app\config\Vhelper;
use app\models\Product;
use app\models\ProductDescription;
use app\models\ProductTaxRate;
use app\models\PurchaseOrder;
use app\models\PurchaseOrderItems;
use app\models\SampleCode;
use app\models\SampleRule;
use app\models\Supplier;
use app\models\SupplierCheck;
use app\models\SupplierCheckSearch;
use app\models\SupplierCheckSku;
use app\models\SupplierCheckUpload;
use app\models\SupplierCheckUser;
use app\models\SupplierContactInformation;
use app\models\SupplierLog;
use app\models\User;
use app\services\SupplierServices;
use Yii;
use yii\db\Exception;
use yii\filters\VerbFilter;
use app\config\MyExcel;
use yii\grid\ActionColumn;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\HttpException;
use yii\web\UploadedFile;
use app\models\SupplierCheckNote;
/**
 * Created by PhpStorm.
 * User: wr
 * Date: 2018/03/08
 * Time: 18:46
 */
class SupplierCheckController extends BaseController
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
                ],
            ],
        ];
    }

    /**
     * 验厂申请列表
     */
    public function  actionIndex()
    {
        $searchModel = new SupplierCheckSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    //创建验厂申请
    public function actionCreate(){
        $model = new SupplierCheck();
        if(Yii::$app->request->isPost){
            //var_dump($_POST);exit();
            $response = $model->saveApply($model,Yii::$app->request->getBodyParam('SupplierCheck'));
            Yii::$app->getSession()->setFlash($response['status'],$response['message']);
            SupplierLog::saveSupplierLog('supplier-check/create',$response['message']);
            return $this->redirect('index');
        }
        return $this->render('create',['model'=>$model]);
    }

    //自动验证验厂类型
    public function actionCheckTimes($supplier_code,$type,$check_type,$check_code){
        if(!empty($check_code)){
            $times = SupplierCheck::find()->select('check_times')->where(['check_code'=>$check_code])->scalar();
            echo json_encode(['status'=>'success','times'=>$times]);
            Yii::$app->end();
        }
        $times = SupplierCheck::find()->select('count(check_code)')
            ->andFilterWhere(['supplier_code'=>$supplier_code])
            ->andFilterWhere(['check_type'=>$check_type])
            ->andFilterWhere(['<>','status',4])->scalar();
        echo json_encode(['status'=>'success','times'=>$times+1]);
    }

    //上传验厂数据文件
    public function actionUpload(){
        $model = new SupplierCheckUpload();
        $checkId = Yii::$app->request->getQueryParam('checkId');
        if(Yii::$app->request->isPost){
            $form = Yii::$app->request->getBodyParam('SupplierCheckUpload');
            if(empty($form['check_id'])){
                Yii::$app->getSession()->setFlash('warning','缺少必要参数');
                return $this->redirect(Yii::$app->request->referrer);
            }
            //上传检验报告图片
            if(!empty($form['inspection_report'])){
                $inspection_report_file_array = Vhelper::changeData($form['inspection_report']);
                if(!empty($inspection_report_file_array)){
                    foreach ($inspection_report_file_array as $inspection_report_file){
                        if(!isset($inspection_report_file['file'])||!isset($inspection_report_file['filename'])){
                            continue;
                        }
                        SupplierCheckUpload::saveFile($inspection_report_file['file'],$inspection_report_file['filename'],4,$form['check_id']);
                    }
                }
            }
            //保存产品图片
            if(!empty($form['product_img'])){
                $product_img_file_array = Vhelper::changeData($form['product_img']);
                if(!empty($product_img_file_array)){
                    foreach ($product_img_file_array as $product_img_file){
                        if(!isset($product_img_file['file'])||!isset($product_img_file['filename'])){
                            continue;
                        }
                        SupplierCheckUpload::saveFile($product_img_file['file'],$product_img_file['filename'],5,$form['check_id']);
                    }
                }
            }
            if(!empty($form['abnormal_img'])){
                $abnormal_img_file_array = Vhelper::changeData($form['abnormal_img']);
                if(!empty($abnormal_img_file_array)){
                    foreach ($abnormal_img_file_array as $abnormal_img_file){
                        if(!isset($abnormal_img_file['file'])||!isset($abnormal_img_file['filename'])){
                            continue;
                        }
                        SupplierCheckUpload::saveFile($abnormal_img_file['file'],$abnormal_img_file['filename'],6,$form['check_id']);
                    }
                }
            }
            //更新验厂验货申请状态
            SupplierCheck::updateAll(['status'=>3],['id'=>$form['check_id']]);
            Yii::$app->getSession()->setFlash('success','资料上传成功');
            SupplierLog::saveSupplierLog('supplier-check/upload','checkId:'.$checkId);
            return $this->redirect(Yii::$app->request->referrer);
        }
        $p1=$p2=[];
        return $this->renderAjax('addfile',['model'=>$model,'checkId'=>$checkId,'p1'=>$p1,'p2'=>$p2]);
    }

    //验厂文件上传
    public function  actionImageAsynUpolad()
    {
        $name = Yii::$app->request->getQueryParam('name');
        if(empty($name)){
            $html_name='SupplierCheckUpload[file]';
        }else{
            $html_name='SupplierCheckUpload['.$name.']';
        }
        $images = UploadedFile::getInstancesByName($html_name);
        $res = [];
        $p1 = $p2 = [];
        if (count($images) > 0) {
            foreach ($images as $key => $image) {
                $uploadpath = 'Uploads/' . date('Ymd') . '/';  //上传路径
                // 图片保存在本地的路径：images/Uploads/当天日期/文件名，默认放置在basic/web/下
                $dir = '/images/' . $uploadpath;
                //生成唯一uuid用来保存到服务器上图片名称
                $pickey = Vhelper::genuuid();
                $filename = $pickey . '.' . $image->getExtension();
                //如果文件夹不存在，则新建文件夹
                $filepath= Vhelper::fileExists(Yii::getAlias('@app') . '/web' . $dir);
                $file = $filepath.$filename;
                if ($image->saveAs($file)) {
                    $imgpath = $dir . $filename;
                    $p1[$key] = $imgpath;

                    $config = [
                        'caption' => $filename,
                        'width' => '120px',
                        'url' => '/supplier/delete-pic', // server delete action
                        'key' => $pickey,
                        'extra' => ['filename' => $filename]
                    ];
                    $p2[$key] = $config;

                    $res[] = [
                        "initialPreview" => $p1,
                        "initialPreviewConfig" => $p2,
                        "imgfile" => "<input name='".$html_name."[file][]' id='" . $pickey . "' type='hidden' value='" . $imgpath . "'/>",
                        "imgfilename" => "<input name='".$html_name."[filename][]' type='hidden' value='" . $image->name . "'/>"
                    ];
                }


            }
        }

        echo Json::encode($res);
    }

    //下载验厂数据文件
    public function actionDownload(){
        $id = Yii::$app->request->getQueryParam('id');
        SupplierLog::saveSupplierLog('supplier-check/download','id:'.$id);
        $fileData = SupplierCheckUpload::find()->andFilterWhere(['id'=>$id,'status'=>1])->one();
        $file=fopen($fileData->url,"r");
        header("Content-Type: application/octet-stream");
        header("Accept-Ranges: bytes");
        header("Accept-Length: ".filesize($fileData->url));
        header("Content-Disposition: attachment; filename='$fileData->file_name'");
        echo fread($file,filesize($fileData->url));
        fclose($file);
    }

    //删除验厂验货资料状态删除
    public function actionDeleteFile(){
        $checkId = Yii::$app->request->getQueryParam('checkId');
        $type    = Yii::$app->request->getQueryParam('type');
        $file_name    = Yii::$app->request->getQueryParam('filename',null);
        SupplierLog::saveSupplierLog('supplier-check/delete-file','type:'.$type.'checkId:'.$checkId.'filename:'.$file_name);
        SupplierCheckUpload::updateAll(['status'=>2],['check_id'=>$checkId,'type'=>$type,'url'=>$file_name]);
        return $this->redirect(Yii::$app->request->referrer);
    }

    //验厂数据文件列表
    public function  actionView(){
        $checkId = Yii::$app->request->getQueryParam('checkId');
       // $type    = Yii::$app->request->getQueryParam('type');
        $data = SupplierCheckUpload::find()->andFilterWhere(['check_id'=>$checkId,'status'=>1])->asArray()->all();
        return $this->renderAjax('view',['data'=>$data]);
    }

    //添加验厂时间
    public function actionUpdateTime(){
        exit();
        if(Yii::$app->request->isAjax&&Yii::$app->request->isPost){
            try{
                $id = Yii::$app->request->getBodyParam('checkId');
                $date = Yii::$app->request->getBodyParam('date');
                $check = SupplierCheck::find()->andFilterWhere(['id'=>$id])->one();
                if(empty($check)){
                    throw new HttpException(500,'申请不存在');
                }
                $checkUserId = SupplierCheckUser::find()->andFilterWhere(['check_id'=>$id])->asArray()->all();
                $userId = array_column($checkUserId,'check_user_id');
                if(!in_array(Yii::$app->user->id,$userId)){
                    throw new HttpException(500,'当前登录用户无法编辑改申请验厂时间');
                }
                $check ->check_time = $date;
                if($check->save()==false){
                    throw new HttpException(500,'验厂时间编辑失败');
                }
                $response = ['status'=>'success','message'=>'验厂时间编辑成功'];
            }catch (HttpException $e){
                $response = ['status'=>'error','message'=>$e->getMessage()];
            }
            SupplierLog::saveSupplierLog('supplier-check/update-time','checkId:'.$id);
            echo json_encode($response);
            exit();
        }
    }

    //编辑验厂申请
    public function actionUpdate($id){
        $model = SupplierCheck::find()->where(['id'=>$id])->one();
        //$model->type = SupplierServices::getCheckType($model->check_times);
        $checkUser = SupplierCheckUser::find()->where(['check_id'=>$id,'status'=>1])->asArray()->all();
        $model->check_user = array_column($checkUser,'check_user_name');
        if(Yii::$app->request->isPost){
            $data = Yii::$app->request->getBodyParam('SupplierCheck');
            $response = $model->saveApply($model,$data,1);
            Yii::$app->session->setFlash($response['status'],$response['message']);
            SupplierLog::saveSupplierLog('supplier-check/update','checkId:'.$id.'message:'.$response['message']);
            return $this->redirect(['index']);
        }
        return $this->render('update',['model'=>$model]);
    }

    //变更验厂申请状态
    public function actionCheck($id,$status){
        try{
            $model = SupplierCheck::find()->where(['id'=>$id])->one();
            if(empty($model)){
                throw new HttpException(500,'更新的数据不存在！');
            }
            if($model->status > $status){
                throw new HttpException(500,'申请状态暂不支持逆变更');
            }
            $checkUserId = SupplierCheckUser::find()->andFilterWhere(['check_id'=>$id])->asArray()->all();
            $userId = array_column($checkUserId,'check_user_id');
            if(!in_array(Yii::$app->user->id,$userId)){
                throw new HttpException(500,'当前登录用户无法变更该申请状态');
            }
            $model->status = $status;
            if($status==2){
                $model->check_time = date('Y-m-d H:i:s',time());
            }
            $model->save(false);
            $response = ['status'=>'success','message'=>'申请状态变更成功'];
        }catch (HttpException $e){
            $response = ['status'=>'error','message'=>$e->getMessage()];
        }
        Yii::$app->session->setFlash($response['status'],$response['message']);
        SupplierLog::saveSupplierLog('supplier-check/check','id:'.$id.'message:'.$response['message'].'status:'.$status);
        return $this->redirect(Yii::$app->request->referrer);
    }

    //  批量下载
    public function actionBatchDownload(){
        $ids    = Yii::$app->request->getQueryParam('ids',null);
        if(empty($ids)){
            Yii::$app->end('缺少必要参数');
        }
        $dfile =  tempnam('/tmp', 'tmp');//产生一个临时文件，用于缓存下载文件
        $zip = new zipfile();
        //----------------------
        $filename = 'supplier_files.zip'; //下载的默认文件名
        $images_url = SupplierCheckUpload::find()->where(['id'=>explode(',',$ids)])->all();
        //以下是需要下载的图片数组信息，将需要下载的图片信息转化为类似即可
        foreach ($images_url as $key => $value) {
            // $path = 'D:\WWW\purchase\web\\'; //Yii::$app->basePath
            $image[] = ['image_src' => $value->url, 'image_name' => iconv('utf-8', 'gbk', $value->file_name)];
        }

        foreach($image as $v){
            $zip->add_file(file_get_contents($v['image_src']),  $v['image_name']);
            // 添加打包的图片，第一个参数是图片内容，第二个参数是压缩包里面的显示的名称, 可包含路径
            // 或是想打包整个目录 用 $zip->add_path($image_path);
        }
        //----------------------
        $zip->output($dfile);

        // 下载文件
        ob_clean();
        header('Pragma: public');
        header('Last-Modified:'.gmdate('D, d M Y H:i:s') . 'GMT');
        header('Cache-Control:no-store, no-cache, must-revalidate');
        header('Cache-Control:pre-check=0, post-check=0, max-age=0');
        header('Content-Transfer-Encoding:binary');
        header('Content-Encoding:none');
        header('Content-type:multipart/form-data');
        header('Content-Disposition:attachment; filename="'.$filename.'"'); //设置下载的默认文件名
        header('Content-length:'. filesize($dfile));
        $fp = fopen($dfile, 'r');
        while(connection_status() == 0 && $buf = @fread($fp, 8192)){
            echo $buf;
        }
        fclose($fp);
        @unlink($dfile);
        @flush();
        @ob_flush();
        exit();
    }

    //删除验厂申请，状态删除4
    public function actionDelete($id){
        try{
            $model = SupplierCheck::find()->where(['id'=>$id])->one();
            if(empty($model)){
                throw new HttpException(500,'删除的数据不存在！');
            }
            $model->status = 4;
            $model->save(false);
            $response = ['status'=>'success','message'=>'申请删除成功'];
        }catch (HttpException $e){
            $response = ['status'=>'error','message'=>$e->getMessage()];
        }
        Yii::$app->session->setFlash($response['status'],$response['message']);
        SupplierLog::saveSupplierLog('supplier-check/delete','id:'.$id.'message:'.$response['message']);
        return $this->redirect(Yii::$app->request->referrer);
    }

    //导出验厂模板
    public function actionDownloadExport(){
        $checkId = Yii::$app->request->getQueryParam('checkId');
        $check = SupplierCheck::find()->where(['id'=>$checkId])->one();
        if(empty($check)){
            exit('error');
        }
        $checkUser = array_column(SupplierCheckUser::find()->where(['check_id'=>$checkId,'status'=>1])->asArray()->all(),'check_user_name');
        $filePath = './images/supplier.xls';
        //读取文件
        if (!file_exists($filePath)) {
            exit("you dont have ");
        }
        $objPHPExcel = \PHPExcel_IOFactory::load($filePath);
        $objPHPExcel1 = \PHPExcel_IOFactory::load($filePath);
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A2', '供应商名称：'.($check->supplier ? $check->supplier->supplier_name : $check->supplier_name))
            //->setCellValue('D2', '主要生产类目：'.($check->supplier ? $check->supplier->supplier_name : ''))
            ->setCellValue('A3', '供应商地址：'.($check->contact_address ? $check->contact_address : ''))
            ->setCellValue('A4', '联系人：'.($check->contact_person ? $check->contact_person : ''))
            ->setCellValue('C4', '联系电话：'.($check->phone_number ? $check->phone_number : ''))
            ->setCellValue('D4', '验厂时间：'.($check->check_time ? $check->check_time : ''))
            //->setCellValue('A5', '验厂负责人：'.($check->supplier ? $check->supplier->supplier_name : ''))
            ->setCellValue('C5', '参与人员：'.implode(',',$checkUser));
            //->setCellValue('A6', '每月最大产能：'.($check->supplier ? $check->supplier->supplier_name : ''))
        $sheet = $objPHPExcel1->getSheetByName('内部');
        $sheet->setTitle('sheet11');
        $objPHPExcel->addExternalSheet($sheet);
        unset($sheet);
        $objPHPExcel->setActiveSheetIndex(1)
            ->setCellValue('A2', '供应商名称：'.($check->supplier ? $check->supplier->supplier_name : $check->supplier_name))
            //->setCellValue('D2', '主要生产类目：'.($check->supplier ? $check->supplier->supplier_name : ''))
            ->setCellValue('A3', '供应商地址：'.($check->contact_address ? $check->contact_address : ''))
            ->setCellValue('A4', '联系人:'.$check->contact_person)
            ->setCellValue('C4', '联系电话：'.($check->phone_number ? $check->phone_number : ''))
            ->setCellValue('D4', '验厂时间：'.($check->check_time ? $check->check_time : ''))
            //->setCellValue('A5', '验厂负责人：'.($check->supplier ? $check->supplier->supplier_name : ''))
            ->setCellValue('C5', '参与人员：'.implode(',',$checkUser));
        header("Content-type:application/vnd.ms-excel;charset=UTF-8");
        header('Content-Type : application/vnd.ms-excel');
        $ua = $_SERVER["HTTP_USER_AGENT"];
        $filename = '验厂报告-'.date("Ymj").'.xls';
        $encoded_filename = urlencode($filename);
        $encoded_filename = str_replace("+", "%20", $encoded_filename);
        header('Content-Type: application/octet-stream');

        if (preg_match("/MSIE/", $ua)) {
            header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
        } else if (preg_match("/Firefox/", $ua)) {
            header('Content-Disposition: attachment; filename*="utf8\'\'' . $filename . '"');
        } else {
            header('Content-Disposition: attachment; filename="' . $filename . '"');
        }
        $objWriter= \PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel5');
        $objWriter->save('php://output');
    }

    //检测采购单信息
    public function actionCheckPur(){
        $sampleCodeArray = [
            '1'=>['quality_random'=>'S-4','quality_level'=>1.5],
            '2'=>['quality_random'=>'Ⅱ','quality_level'=>2.5],
            '3'=>['quality_random'=>'Ⅱ','quality_level'=>1.5],
        ];
        $purNumber = Yii::$app->request->getQueryParam('pur_number');
        $supplier_code = Yii::$app->request->getQueryParam('supplier_code');
        $check_id   = Yii::$app->request->getQueryParam('check_id');
        try{
            $pur_number = explode(',',$purNumber);
            if(empty($pur_number)||empty($supplier_code)){
                throw new Exception('关键数据为空');
            }
            $datas=[];
            foreach ($pur_number as $value){
                $purOrderItems = PurchaseOrderItems::find()->alias('t')
                    ->select('o.pur_number,o.purchase_type,t.sku,t.ctq,o.supplier_code')
                    ->leftJoin(PurchaseOrder::tableName().' o','o.pur_number=t.pur_number')
                    ->where(['t.pur_number'=>$value])
                    ->andWhere(['o.supplier_code'=>$supplier_code])
                    ->andWhere(['not in','o.purchas_status',[4,10]])
                    ->asArray()->all();
                if(empty($purOrderItems)){
                    throw new Exception('采购单数据与供应商不一致');
                }
                if(empty($purOrderItems)){
                    throw new Exception('采购单数据有误');
                }
                foreach ($purOrderItems as $v) {
                    $datas[$v['purchase_type']][$v['sku']] = isset($datas[$v['purchase_type']][$v['sku']]) ? $datas[$v['purchase_type']][$v['sku']] + $v['ctq'] : 0+$v['ctq'];
                }
            }
            $html = '';
            $i =0;
            foreach ($datas as $kv=>$data){
                foreach ($data as $key=>$skuv){
                    $haveData = SupplierCheckSku::find()->where(['type'=>$kv,'sku'=>$key,'check_id'=>$check_id,'status'=>0])->asArray()->one();
                    $html.="<tr class='append'>
                               <input class='form-control append-type' type='hidden' name='SupplierCheck[items][$i][type]' value=".$kv.">
                               ";
                    $html.="<td class='form'><div class='form-group'><label></label><input class='form-control' readonly='readonly' name='SupplierCheck[items][$i][sku]' type='text' value=".$key."></div></td>";
                    $quality_random = $sampleCodeArray[$kv]['quality_random'];
                    $quality_level = $sampleCodeArray[$kv]['quality_level'];
                    $sampleCode = SampleRule::find()->select('sample_code')->where('min_num <= :num and max_num>= :num and type=:type and status=0 and quality_random = :quality_random',[':num'=>$skuv,':type'=>$kv,':quality_random'=>$quality_random])->scalar();
                    $sampleCode = !empty($sampleCode) ? $sampleCode : '';
                    $sampleNum = SampleCode::find()->select('sample_num')->where(['sample_code'=>$sampleCode,'type'=>$kv,'aql'=>$quality_level])->scalar();
                    $ac = SampleCode::find()->select('ac_num')->where(['sample_code'=>$sampleCode,'type'=>$kv,'aql'=>$quality_level])->scalar();
                    $ac = !empty($ac) ? $ac : 0;
                    $re = SampleCode::find()->select('re_num')->where(['sample_code'=>$sampleCode,'type'=>$kv,'aql'=>$quality_level])->scalar();
                    $re = !empty($re) ? $re : 0;
                    $sampleNum = !empty($sampleNum) ? $sampleNum :0;
                    $check_standard = !empty($haveData['check_standard'])?$haveData['check_standard']:$quality_random.'||'.$quality_level;
                    $html.="<td class='form'><div class='form-group'><label></label><input class='form-control' readonly='readonly' name='SupplierCheck[items][$i][purchase_num]' type='text' value='$skuv'></div></td>";
                    $html.="<td class='form'><div class='form-group'><label></label><input class='form-control' readonly='readonly' name='SupplierCheck[items][$i][check_standard]' type='text' value='$check_standard'></div></td>";
                    $check_num=!empty($haveData['check_num'])?$haveData['check_num']:$sampleNum;
                    $html.="<td class='form'><div class='form-group'><label></label><input class='form-control' readonly='readonly' name='SupplierCheck[items][$i][check_num]' type='number' value='$check_num'></div></td>";
                    $check_rate=!empty($haveData['check_rate'])?$haveData['check_rate']:'AC:'.$ac.'--'.'RE:'.$re;
                    $html.="<td class='form'><div class='form-group'><label></label><input class='form-control' readonly='readonly' name='SupplierCheck[items][$i][check_rate]' type='text' step='0.001' value='$check_rate'></div></td>";
                    $complaint_rate=!empty($haveData['complaint_rate'])?$haveData['complaint_rate']:0;
                    $complaint_point=!empty($haveData['complaint_point'])?$haveData['complaint_point']:'';
                    $html.="<td class='form'><div class='form-group'><label></label><input class='form-control' name='SupplierCheck[items][$i][complaint_rate]' type='number' step='0.001' value='$complaint_rate'></div></td>";
                    $html.="<td class='form'><div class='form-group'><label></label><input class='form-control' name='SupplierCheck[items][$i][complaint_point]' type='text' value='$complaint_point'></div></td>";
                    $html.="<td></td>";
                    $html.="</tr>";
                    $i++;
                }
            }
            $response = ['status'=>'success','data'=>$html,'message'=>'获取成功'];
        }catch (Exception $e){
            $response = ['status'=>'error','data'=>'','message'=>$e->getMessage()];
        }
        echo json_encode($response);
        Yii::$app->end();
    }

    //获取供应商联系方式
    public function actionGetSupplierInfo(){
        $supplier_code = Yii::$app->request->getQueryParam('supplier_code');
        $info = SupplierContactInformation::find()->select('contact_person,contact_number,chinese_contact_address')->where(['supplier_code'=>$supplier_code])->asArray()->one();
        if(empty($info)){
            echo json_encode(['contact_person'=>'','contact_number'=>'','chinese_contact_address'=>'']);
        }else{
            echo json_encode($info);
        }
        Yii::$app->end();
    }

    /**
     * 品控或供应链评价
     * @return [type] [description]
     */
    public function actionAuditNote(){
        $check_id = Yii::$app->request->getQueryParam('checkId');
        $model = SupplierCheck::find()->where(['id'=>$check_id])->one();
        if(Yii::$app->request->isAjax){
            return $this->renderAjax('check-note',['model'=>$model]);
        }
        if(Yii::$app->request->isPost){
            $form = Yii::$app->request->getBodyParam('SupplierCheck');
            $data['check_id'] = $check_id;
            $data['check_note'] = isset($form['evaluate'])?$form['evaluate']:'';

            $tran = Yii::$app->db->beginTransaction();
            try{
                SupplierLog::saveSupplierLog('supplier-check/check-result',serialize($form));

                if($model->status!=5){
                    throw new Exception('验厂数据错误');
                }

                //保存结果评价
                SupplierCheckNote::saveAuditNote($data);
                
                Yii::$app->getSession()->setFlash('success','提交成功');
                $tran->commit();
            }catch (Exception $e){
                Yii::$app->getSession()->setFlash('error',$e->getMessage());
                $tran->rollBack();
            }
            return $this->redirect(Yii::$app->request->referrer);
        }
    }
    //提交验厂结果
    public function actionCheckResult(){
        $check_id = Yii::$app->request->getQueryParam('checkId');
        $model = SupplierCheck::find()->where(['id'=>$check_id])->one();
        if(Yii::$app->request->isAjax){
            return $this->renderAjax('check-result',['model'=>$model]);
        }
        if(Yii::$app->request->isPost){
            $form = Yii::$app->request->getBodyParam('SupplierCheck');
            $tran = Yii::$app->db->beginTransaction();
            try{
                SupplierLog::saveSupplierLog('supplier-check/check-result',serialize($form));
                if($model->status!=2){
                    throw new Exception('验厂数据错误');
                }
                $model->judgment_results      = isset($form['judgment_results']) ? $form['judgment_results'] :0;
                $model->evaluate              = isset($form['evaluate']) ? $form['evaluate'] : '';
                $model->status = 7; //无资料
                $model->report_time = date('Y-m-d H:i:s', time()); //报告时间
                $model->improvement_measure = isset($form['improvement_measure']) ? $form['improvement_measure'] : '';
                $model->is_check_again = isset($form['is_check_again']) ? $form['is_check_again'] : 0;//默认不再次验货
                //再次验货
                if(isset($form['is_check_again'])&&$form['is_check_again']==1){
                    $copeApply = SupplierCheck::copyCheckData($model,$form['check_price'],$form['review_reason']);
                    if(isset($copeApply['status'])&&$copeApply['status']=='error'){
                        throw new Exception($copeApply['message']);
                    }
                }
                //更新不良品数量
                if($model->check_type==2){
                    if(!empty($form['items'])){
                        foreach ($form['items'] as $value){
                            Yii::$app->db->createCommand()->update(SupplierCheckSku::tableName(),
                            ['bad_goods'=>$value['bad_goods']],['type'=>$value['type'],'sku'=>$value['sku'],'check_id'=>$model->id]
                            )->execute();
                        }
                    }
                }

                if($model->save()==false){
                    throw new Exception('验厂状态修改失败'.implode(',',$model->getFirstErrors()));
                }
                Yii::$app->getSession()->setFlash('success','提交成功');
                $tran->commit();
            }catch (Exception $e){
                Yii::$app->getSession()->setFlash('error',$e->getMessage());
                $tran->rollBack();
            }
            return $this->redirect(Yii::$app->request->referrer);
        }
    }

    //查看验货sku详情
    public function  actionSkuDetail(){
        $chekId = Yii::$app->request->getQueryParam('check_id');
        $skuDetail = SupplierCheckSku::find()->where(['check_id'=>$chekId,'status'=>0])->asArray()->all();
        return $this->renderAjax('sku-detail',['data'=>$skuDetail]);
    }

    //导出验货模板
    public function actionDownloadskuExport(){
        $checkId = Yii::$app->request->getQueryParam('checkId');
        $check = SupplierCheck::find()->where(['id'=>$checkId])->one();
        if(empty($check)){
            exit('error');
        }
        $filePath = './images/supplier_sku.xlsx';
        //读取文件
        if (!file_exists($filePath)) {
            exit("you dont have ");
        }
        $objPHPExcel = \PHPExcel_IOFactory::load($filePath);

        $sampleCodeArray = [
            '1'=>['quality_random'=>'S-4','quality_level'=>1.5],
            '2'=>['quality_random'=>'Ⅱ','quality_level'=>2.5],
            '3'=>['quality_random'=>'Ⅱ','quality_level'=>1.5],
        ];
        $standard = in_array($sampleCodeArray[$check->group]['quality_random'],['S-1','S-2','S-3','S-4'])? '特殊检验'.$sampleCodeArray[$check->group]['quality_random'].'级检验标准   AQL='.$sampleCodeArray[$check->group]['quality_level']:'一般检验'.$sampleCodeArray[$check->group]['quality_random'].'级检验标准   AQL='.$sampleCodeArray[$check->group]['quality_level'];
        $checkData = '抽检数量：  AC:    RE:   ';
        $objPHPExcel->setActiveSheetIndex(0)
            ->setTitle('验货报告')
            ->setCellValue('C2', $check->contact_address)
            ->setCellValue('C3', $check->contact_person)
            ->setCellValue('G3', $check->phone_number)
            ->setCellValue('G5', $check->pur_number)
            ->setCellValue('A10','抽检计划：'.$standard.' '.$checkData )
            ->setCellValue('A21','抽检计划：'.$standard.' '.$checkData )
            ->setCellValue('A38','抽检计划：'.$standard.' '.$checkData )
            ->setCellValue('A49','抽检计划：'.$standard.' '.$checkData )
            ->setCellValue('A63','抽检计划：'.$standard.' '.$checkData )
            ->setCellValue('A74','抽检计划：'.$standard.' '.$checkData )
            ->setCellValue('A93','抽检计划：'.$standard.' '.$checkData )
            ->setCellValue('A109','抽检计划：'.$standard.' '.$checkData )
            ->setCellValue('A119','抽检计划：'.$standard.' '.$checkData )
            ->setCellValue('A137','检验照片标准：
            1、产品功能测试照片
            2、产品配件照片
            3、产品外观照片
            4、产品包装照片
            5、产品SKU贴码照片，抽检过程中需对SKU条码进行扫描检测能否正常读取
            6、产品如果有进行数值类测试，如电流、电压、噪音、转速等测试，需对数值拍照留证       
            7、检验结果必须当天反馈给采购员:'.$check->apply_user_name);
        header("Content-type:application/vnd.ms-excel;charset=UTF-8");
        header('Content-Type : application/vnd.ms-excel');
        $ua = $_SERVER["HTTP_USER_AGENT"];
        $filename = '验货报告-'.$check->check_code.'-'.date("Ymj").'.xlsx';
        $encoded_filename = urlencode($filename);
        $encoded_filename = str_replace("+", "%20", $encoded_filename);
        header('Content-Type: application/octet-stream');

        if (preg_match("/MSIE/", $ua)) {
            header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
        } else if (preg_match("/Firefox/", $ua)) {
            header('Content-Disposition: attachment; filename*="utf8\'\'' . $filename . '"');
        } else {
            header('Content-Disposition: attachment; filename="' . $filename . '"');
        }
        $objWriter= \PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel2007');
        $objWriter->save('php://output');
    }

    //验证表单内容
    public function actionCheckForm()
    {
        $formData = Yii::$app->request->getBodyParam('SupplierCheck');
        // Vhelper::dump($formData);
        $errorMessage = [];
        if(empty($formData['check_type'])){
            $errorMessage[]='类型不能为空';
        }
        if(empty($formData['group'])){
            $errorMessage[]='部门不能为空';
        }
        if(isset($formData['supplier_code'])&&empty($formData['supplier_code'])&&empty($formData['supplier_name'])){
            $errorMessage[]='供应商信息不能为空';
        }
        if(empty($formData['check_times'])){
            $errorMessage[]='验厂次数不能为空';
        }
        //20181120修改
//        if(empty($formData['check_user'])){
//            $errorMessage[]='参与人员不能为空';
//        }
        if(isset($formData['check_type'])&&$formData['check_type']==2&&empty($formData['pur_number'])){
            $errorMessage[]='验货时采购单号不能为空';
        }
        if(isset($formData['check_type'])&&$formData['check_type']==2&&empty($formData['items'])){
            $errorMessage[]='验货时sku不能为空';
        }
        if(isset($formData['expect_time'])&& empty($formData['expect_time'])){
            $errorMessage[]='期望时间不能为空';
        }
        if(!empty($errorMessage)){
            echo json_encode(['status'=>'error','message'=>implode('<br/>',$errorMessage)]);
            Yii::$app->end();
        }else{
            echo json_encode(['status'=>'success']);
            Yii::$app->end();
        }

    }

    //  非采购系统采购单获取验货标准
    public function actionGetStandard(){

        $sku  = Yii::$app->request->getQueryParam('sku');
        $type = Yii::$app->request->getQueryParam('type',1);
        $num  = Yii::$app->request->getQueryParam('num');
        $product = Product::find()->where(['sku'=>$sku])->one();
        if(empty($product)){
            echo json_encode(['status'=>'error','message'=>'产品在当前系统不存在！']);
            Yii::$app->end();
        }
        $sampleCodeArray = [
            '1'=>['quality_random'=>'S-4','quality_level'=>1.5],
            '2'=>['quality_random'=>'Ⅱ','quality_level'=>2.5],
            '3'=>['quality_random'=>'Ⅱ','quality_level'=>1.5],
        ];
        $quality_random = $sampleCodeArray[$type]['quality_random'];
        $quality_level = $sampleCodeArray[$type]['quality_level'];
        $sampleCode = SampleRule::find()->select('sample_code')->where('min_num <= :num and max_num>= :num and type=:type and status=0 and quality_random = :quality_random',[':num'=>$num,':type'=>$type,':quality_random'=>$quality_random])->scalar();
        $sampleCode = !empty($sampleCode) ? $sampleCode : '';
        $sampleNum = SampleCode::find()->select('sample_num')->where(['sample_code'=>$sampleCode,'type'=>$type,'aql'=>$quality_level])->scalar();
        $ac = SampleCode::find()->select('ac_num')->where(['sample_code'=>$sampleCode,'type'=>$type,'aql'=>$quality_level])->scalar();
        $ac = !empty($ac) ? $ac : 0;
        $re = SampleCode::find()->select('re_num')->where(['sample_code'=>$sampleCode,'type'=>$type,'aql'=>$quality_level])->scalar();
        $re = !empty($re) ? $re : 0;
        $sampleNum = !empty($sampleNum) ? $sampleNum :0;
        $check_standard = $quality_random.'||'.$quality_level;
        $check_rate='AC:'.$ac.'--'.'RE:'.$re;
        echo json_encode(['status'=>'success','check_standard'=>$check_standard,'sample_num'=>$sampleNum,'check_rate'=>$check_rate]);
        Yii::$app->end();
    }

    //提交改善措施
    public function actionImprovementMeasure($checkId){
        $model = SupplierCheck::find()->where(['id'=>$checkId])->one();
        if(Yii::$app->request->isAjax){
            return $this->renderAjax('measure',['model'=>$model]);
        }
        if(Yii::$app->request->isPost){
            if(empty($model)){
                Yii::$app->getSession()->setFlash('error','数据错误!');
                return $this->redirect(Yii::$app->request->referrer);
            }
            $form = Yii::$app->request->getBodyParam('SupplierCheck');
            $model->improvement_measure = isset($form['improvement_measure']) ? $form['improvement_measure'] : '';
            if($model->save()==false){
                Yii::$app->getSession()->setFlash('error','改善措施保存失败！');
                return $this->redirect(Yii::$app->request->referrer);
            }
            SupplierLog::saveSupplierLog('supplier-check/improvement-measure','提交改善措施成功');
            Yii::$app->getSession()->setFlash('success','改善措施提交成功');
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
        $id = strpos($id,',')?explode(',',rtrim($id,',')):$id;

        if (!empty($id)) {
            $model = SupplierCheck::find()
                ->alias('t')
                ->select('*')
                ->where(['in', 't.id', $id])
                ->all();
        }else{
            $params = Yii::$app->session->get('supplier_check_search_params');
            $searchModel = new SupplierCheckSearch();
            $query = $searchModel->search($params,true);
            $model = $query->all();
        }

        $objectPHPExcel = new \PHPExcel();
        $objectPHPExcel->setActiveSheetIndex(0);
        $n = 0;

        //表格头的输出
        //$cell_value = ['申请时间','执行时间','类别','申请部门','供应商名称','供应商地址','参与人员','判定结果','评价','改善措施'];
        $cell_value = ['采购员','申请时间','PO号','次数','期望时间','确认时间','报告时间','类别','供应商名称','供应商地址','供应商联系人','供应商电话','参与人员','判定结果','评价','改善措施','检验次数','验货原因','检验费用'];
        foreach ($cell_value as $k => $v) {
            $objectPHPExcel->setActiveSheetIndex(0)->setCellValue(chr($k+65) . '1',$v);
        }
        //设置数据水平靠左和垂直居中
        $objectPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $objectPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $arr = [
            0=>'待确认',
            1=>'合格',
            2=>'不合格'
        ];
        $group = [
            1=>'国内仓',
            2=>'海外仓',
            3=>'FBA'
        ];
        foreach ( $model as $v )
        {

            //明细的输出
            $objectPHPExcel->getActiveSheet()->setCellValue('A'.($n+2) ,$v->apply_user_name);
            $objectPHPExcel->getActiveSheet()->setCellValue('B'.($n+2) ,!empty($v->create_time)?date('Y-m-d',strtotime($v->create_time)):'');
            $objectPHPExcel->getActiveSheet()->setCellValue('C'.($n+2) ,$v->pur_number);
            $objectPHPExcel->getActiveSheet()->setCellValue('D'.($n+2) ,$v->check_times);
            $objectPHPExcel->getActiveSheet()->setCellValue('E'.($n+2) ,!empty($v->expect_time) ? date('Y-m-d', strtotime($v->expect_time)) : '');
            $objectPHPExcel->getActiveSheet()->setCellValue('F'.($n+2) ,!empty($v->confirm_time) ? date('Y-m-d', strtotime($v->confirm_time)) : '');
            $objectPHPExcel->getActiveSheet()->setCellValue('G'.($n+2) ,!empty($v->report_time) ? date('Y-m-d', strtotime($v->report_time)) : '');
            $objectPHPExcel->getActiveSheet()->setCellValue('H'.($n+2) ,$v->getCheckType());
            $objectPHPExcel->getActiveSheet()->setCellValue('I'.($n+2) ,$v->supplier ? $v->supplier->supplier_name : $v->supplier_name);
            $objectPHPExcel->getActiveSheet()->setCellValue('J'.($n+2) ,$v->contact_address);
            $objectPHPExcel->getActiveSheet()->setCellValue('K'.($n+2) ,$v->contact_person);
            $objectPHPExcel->getActiveSheet()->setCellValue('L'.($n+2) ,$v->phone_number);

            $data = '';
            if($v->checkUser){
                $data = implode(',',array_column($v->checkUser,'check_user_name'));
            }
            $objectPHPExcel->getActiveSheet()->setCellValue('M'.($n+2) ,$data);

            $judgment_results = isset($arr[$v->judgment_results]) ? $arr[$v->judgment_results] : '';
            $objectPHPExcel->getActiveSheet()->setCellValue('N'.($n+2) ,$judgment_results);
            $html = '';
            $res = SupplierCheckNote::getAuditNote(['check_id'=>$v->id]);
            if (!empty($res)) {
                foreach ($res as $key => $value) {
                    $html .= $value['role'] . '评价：' . $value['check_note'] . "\r\n";
                }
            }
            $objectPHPExcel->getActiveSheet()->setCellValue('O'.($n+2) ,$html);//$v->evaluate
            $objectPHPExcel->getActiveSheet()->setCellValue('P'.($n+2) ,$v->improvement_measure);

            //备注
            $objectPHPExcel->getActiveSheet()->setCellValue('Q'.($n+2) ,$v->times);
            $objectPHPExcel->getActiveSheet()->setCellValue('R'.($n+2) ,\app\services\SupplierServices::getSupplierReviewReason($v->review_reason,'string'));
            $objectPHPExcel->getActiveSheet()->setCellValue('S'.($n+2) ,$v->check_price);

            $n = $n +1;
        }

        for ($i = 65; $i<82; $i++) {
            $objectPHPExcel->getActiveSheet()->getColumnDimension(chr($i))->setWidth(15);
            $objectPHPExcel->getActiveSheet()->getStyle( chr($i) . "1")->getFont()->setBold(true);
        }
        $objectPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
        $objectPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);

        //设置样式
        $objectPHPExcel->getActiveSheet()->getStyle('A1:S'.($n+2))->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        ob_end_clean();
        ob_start();
        header("Content-type:application/vnd.ms-excel;charset=UTF-8");
        header('Content-Type : application/vnd.ms-excel');
        header('Content-Disposition:attachment;filename="'.'验货验厂数据-'.date("Y年m月j日").'.xls"');
        $objWriter= \PHPExcel_IOFactory::createWriter($objectPHPExcel,'Excel5');
        $objWriter->save('php://output');
        die;
    }
    /**
     * 添加备注
     */
    public function actionCreateNote()
    {
        $get = yii::$app->request->get();
        $post = yii::$app->request->post();
        if ($post) {
            $note_info = $post['SupplierCheckNote'];
            $status = SupplierCheckNote::saveNote($note_info);
            if ($status) {
                yii::$app->getSession()->setFlash('success', '恭喜你添加备注成功');
            } else {
                yii::$app->getSession()->setFlash('error', '恭喜你添加备注失败');
            }
            return $this->redirect(yii::$app->request->referrer);
        } else {
            $check_id = $get['check_id'];
            $supplier_code = $get['supplier_code'];
            $model = new SupplierCheckNote();
            return $this->renderAjax('note', [
                'check_id' => $check_id,
                'supplier_code' => $supplier_code,
                'model' => $model,
            ]);
        }
    }
    /**
     * 更新确认时间
     */
    public function actionUpdateConfirmTime()
    {
        $get_info = yii::$app->request->get();
        $confirm_time = $get_info['confirm_time'];
        $id = $get_info['id'];
        $status = $get_info['status'];

        $format = "Y-m-d";
        $time = strtotime($confirm_time);  //转换为时间戳
        $checkstr = date($format, $time); //在转换为时间格式

        if($confirm_time == $checkstr || empty($confirm_time)){
            $update['confirm_time'] = $confirm_time;
            if ($status == 1) {
                $update['status'] = 2;
            }

            $res = SupplierCheck::updateAll($update,['id'=>$id]);
            if (!empty($res)) {
                Yii::$app->getSession()->setFlash('success', '确认时间添加/修改成功');
            } else {
                Yii::$app->getSession()->setFlash('error', '确认时间添加/修改失败');
            }
        }else{
            Yii::$app->getSession()->setFlash('error', '确认时间添加/修改失败');
            // return -1;
        }

        return $this->redirect(Yii::$app->request->referrer);
    }


    /*
     * 新版验货模板导出
     */

    public function actionDownloadskuExportNew(){
        $checkId = Yii::$app->request->getQueryParam('id');
        $check = SupplierCheck::find()->where(['id'=>$checkId])->one();
        $orderType = $check->group;
        $checkSkus = SupplierCheckSku::find()->select('p.sku,t.purchase_num')
            ->alias('t')
            ->leftJoin(Product::tableName().' p','t.sku=p.sku')
            ->where(['t.check_id'=>$checkId,'t.status'=>0])->asArray()->all();
        $circleArray = SupplierCheck::getExportSkus(ArrayHelper::map($checkSkus,'sku','purchase_num'));
        if(empty($check)){
            exit('error');
        }
        $filePath = './images/supplier_sku.xlsx';
        //读取文件
        if (!file_exists($filePath)) {
            exit("you dont have ");
        }
        $objPHPExcel = \PHPExcel_IOFactory::load($filePath);
        $objPHPExcel1 = \PHPExcel_IOFactory::load($filePath);
        $sheet = 0;
        foreach ($circleArray as  $key=>$value){
            //['quality_random'=>$quality_random,'quality_level'=>$quality_level,'sampleNum'=>$sampleNum,'ac'=>$ac,'re'=>$re]
            $productName = ProductDescription::find()->select('title')->where(['sku'=>$key,'language_code'=>'Chinese'])->scalar();
            if(!$productName&&isset($value['sku'][0])){
                $productName = ProductDescription::find()->select('title')->where(['sku'=>$value['sku'][0],'language_code'=>'Chinese'])->scalar();
            }
            $sqlInfo = SupplierCheck::getSkuAql($orderType,$value['purchase_num']);
            $standard = in_array($sqlInfo['quality_random'],['S-1','S-2','S-3','S-4'])?
                '特殊检验'.$sqlInfo['quality_random'].'级检验标准   AQL='.$sqlInfo['quality_level']:
                '一般检验'.$sqlInfo['quality_random'].'级检验标准   AQL='.$sqlInfo['quality_level'];
            $checkData = '抽检数量：'.$sqlInfo['sampleNum'].'  AC:'.$sqlInfo['ac'].'    RE:'.$sqlInfo['re'];
            if($sheet==0){
                $objPHPExcel->setActiveSheetIndex($sheet)
                    ->setTitle($key.'--'.$sheet)
                    ->setCellValue('C2', $check->contact_address)
                    ->setCellValue('C3', $check->contact_person)
                    ->setCellValue('G3', $check->phone_number)
                    ->setCellValue('C4', $productName ? $productName :'')
                    ->setCellValue('G4', implode(',',$value['sku']))
                    //->setCellValue('C5', $check->check_time)
                    ->setCellValue('G5', $check->pur_number)
                    ->setCellValue('G8', $value['purchase_num'])
                    ->setCellValue('A10','抽检计划：'.$standard.' '.$checkData )
                    ->setCellValue('A21','抽检计划：'.$standard.' '.$checkData )
                    ->setCellValue('A38','抽检计划：'.$standard.' '.$checkData )
                    ->setCellValue('A49','抽检计划：'.$standard.' '.$checkData )
                    ->setCellValue('A63','抽检计划：'.$standard.' '.$checkData )
                    ->setCellValue('A74','抽检计划：'.$standard.' '.$checkData )
                    ->setCellValue('A93','抽检计划：'.$standard.' '.$checkData )
                    ->setCellValue('A109','抽检计划：'.$standard.' '.$checkData )
                    ->setCellValue('A119','抽检计划：'.$standard.' '.$checkData );

            }else{
                $objClonedWorksheet = clone $objPHPExcel1->getSheetByName('Sheet1');
                $objClonedWorksheet->setTitle($key.'--'.$sheet);
                $objPHPExcel->addSheet($objClonedWorksheet);
                $objPHPExcel->setActiveSheetIndexByName($key.'--'.$sheet)
                    ->setCellValue('C2', $check->contact_address)
                    ->setCellValue('C3', $check->contact_person)
                    ->setCellValue('G3', $check->phone_number)
                    ->setCellValue('C4', $productName ? $productName :'')
                    ->setCellValue('G4',implode(',',$value['sku']))
                    ->setCellValue('C5', $check->check_time)
                    ->setCellValue('G5', $check->pur_number)
                    ->setCellValue('G8', $value['purchase_num'])
                    ->setCellValue('A10','抽检计划：'.$standard.' '.$checkData )
                    ->setCellValue('A21','抽检计划：'.$standard.' '.$checkData )
                    ->setCellValue('A38','抽检计划：'.$standard.' '.$checkData )
                    ->setCellValue('A49','抽检计划：'.$standard.' '.$checkData )
                    ->setCellValue('A63','抽检计划：'.$standard.' '.$checkData )
                    ->setCellValue('A74','抽检计划：'.$standard.' '.$checkData )
                    ->setCellValue('A93','抽检计划：'.$standard.' '.$checkData )
                    ->setCellValue('A109','抽检计划：'.$standard.' '.$checkData )
                    ->setCellValue('A119','抽检计划：'.$standard.' '.$checkData );
            }
            $sheet++;
        }
        header("Content-type:application/vnd.ms-excel;charset=UTF-8");
        header('Content-Type : application/vnd.ms-excel');
        $ua = $_SERVER["HTTP_USER_AGENT"];
        $filename = '验货报告-'.date("Ymj").'.xlsx';
        $encoded_filename = urlencode($filename);
        $encoded_filename = str_replace("+", "%20", $encoded_filename);
        header('Content-Type: application/octet-stream');

        if (preg_match("/MSIE/", $ua)) {
            header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
        } else if (preg_match("/Firefox/", $ua)) {
            header('Content-Disposition: attachment; filename*="utf8\'\'' . $filename . '"');
        } else {
            header('Content-Disposition: attachment; filename="' . $filename . '"');
        }
        $objWriter= \PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel2007');
        $objWriter->save('php://output');
    }

    //确认时间，人员
    public function actionConfirmDateUser($id){
        if(Yii::$app->request->isAjax){
            $checkModel = SupplierCheck::findOne($id);
            $checkUserArray = SupplierCheckUser::find()->select('check_user_id')->where(['check_id'=>$id,'status'=>1])->column();
            return $this->renderAjax('confirm',['model'=>$checkModel,'check_user'=>$checkUserArray]);
        }
        if(Yii::$app->request->isPost){
            $form = Yii::$app->request->getBodyParam('SupplierCheck');
            if (isset($form['check_user']) && !empty($form['check_user'])&&!empty($form['confirm_time'])) {
                $checkUser = array_column(SupplierCheckUser::find()->select('check_user_id')->where(['check_id'=>$id,'status'=>1])->asArray()->all(),'check_user_id');
                $addDiff = array_diff($form['check_user'],$checkUser);
                $delDiff = array_diff($checkUser,$form['check_user']);
                $tran = Yii::$app->db->beginTransaction();
                try{
                    foreach ($addDiff as $userId) {
                        $user = User::find()->andFilterWhere(['id' => $userId])->one();
                        if (!$user) {
                            continue;
                        }
                        $userModel = new SupplierCheckUser();
                        $userModel->check_id = $id;
                        $userModel->check_user_id = $user->id;
                        $userModel->check_user_name = $user->username;
                        $userModel->create_time = date('Y-m-d H:i:s', time());
                        $userModel->create_user_name = Yii::$app->user->identity->username;
                        $userModel->status = 1;
                        if ($userModel->save() == false) {
                            throw new HttpException(500, '验厂用户添加失败');
                        }
                    }
                    SupplierCheckUser::updateAll(['status'=>2,'update_user_name'=>Yii::$app->user->identity->username,'update_time'=>date('Y-m-d H:i:s',time())],['check_user_id'=>$delDiff,'check_id'=>$id]);
                    $update = SupplierCheck::updateAll(['confirm_time'=>date('Y-m-d H:i:s',strtotime($form['confirm_time'])),'status'=>2],['id'=>$id,'status'=>[1,2]]);
                    $tran->commit();
                    Yii::$app->session->setFlash('success','检验人员,确认时间更新成功');
                    return $this->redirect('index');
                }catch (HttpException $e){
                    $tran->rollBack();
                    Yii::$app->session->setFlash('success',$e->getMessage());
                    return $this->redirect('index');
                }

            }else{
                Yii::$app->session->setFlash('warning','检验人员和确认时间不能为空');
                return $this->redirect('index');
            }
        }
    }
}
