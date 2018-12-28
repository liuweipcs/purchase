<?php

namespace app\controllers;


use app\config\Vhelper;
use app\models\CostPurchaseNum;
use app\models\Product;
use app\models\ProductDescription;
use app\models\ProductImgDownload;
use app\models\ProductProvider;
use app\models\ProductTicketedPointLog;
use app\models\PurchaseOrder;
use app\models\PurchaseOrderCancel;
use app\models\PurchaseOrderCancelSub;
use app\models\PurchaseOrderItems;
use app\models\PurchaseOrderRefundQuantity;
use app\models\SampleInspect;
use app\models\SampleInspectSearch;
use app\models\Supplier;
use app\models\SupplierLog;
use app\models\SupplierQuotes;
use app\models\SupplierQuotesSearch;
use app\models\SupplierUpdateApply;
use app\models\SupplierUpdateApplySearch;
use app\services\SupplierGoodsServices;
use app\services\SupplierServices;
use Yii;
use app\models\SupplierGoodsSearch;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;
use yii\log\EmailTarget;
use yii\web\ConflictHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\UploadedFile;
use app\config\MyExcel;
use m35\thecsv\theCsv;

/**
 * Created by PhpStorm.
 * User: wr
 * Date: 2017/12/27
 * Time: 16:28
 */
class SupplierUpdateApplyController extends BaseController
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
     * 供应商或报价修改申请审核列表
     */
    public function  actionIndex()
    {
        $searchModel = new SupplierUpdateApplySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 审核通过
     */
    public function actionCheck(){
        Yii::$app->response->format = 'raw';
        if(Yii::$app->request->isAjax&&Yii::$app->request->isPost){
            $datas = Yii::$app->request->getBodyParam('data');
            //判断这个SKU，在国内仓有没有待审核状态的采购单
            $purnumberCount=SupplierUpdateApply::getPurNumber($datas);
            if($purnumberCount['status']=='success'){
                $checkResult = SupplierUpdateApply::checkApply($datas);
                Yii::$app->getSession()->setFlash($checkResult['status'],$checkResult['message']);
                SupplierLog::saveSupplierLog('supplier-update-apply/check',$checkResult['status'].':applyId-'.implode(',',$datas).$checkResult['message']);
                return $this->redirect(Yii::$app->request->referrer);
            }
            Yii::$app->getSession()->setFlash($purnumberCount['status'],$purnumberCount['message']);
            return $this->redirect(Yii::$app->request->referrer);
        }
    }

    /**
     * 审核不通过
     */
    public function actionRefuse($id){
        $ids = explode(',',$id);
        $model = SupplierUpdateApply::find()->andFilterWhere(['in','id',$ids])->all();
        if (Yii::$app->request->isPost)
        {
            $tran = Yii::$app->db->beginTransaction();
            try{
                $form = Yii::$app->request->getBodyParam('SupplierUpdateApply');
                $count = 0;
                if(!empty($form)){
                    foreach($form as $value){
                        if(!isset($value['id'])||empty($value['id'])||!isset($value['refuse_reason'])||empty($value['refuse_reason'])){
                            $count++;
                            continue;
                        }
                        $apply = SupplierUpdateApply::find()->andFilterWhere(['id'=>$value['id']])->one();
                        if(empty($apply)||$apply->status != 1){
                            throw new HttpException(500,'审核数据异常,请认真操作！');
                        }
                        $apply->status        =3;
                        $apply->update_time   = date('Y-m-d H:i:s',time());
                        $apply->update_user_name = Yii::$app->user->identity->username;
                        $apply->refuse_reason = $value['refuse_reason'];
                        if($apply->save()==false){
                            throw new HttpException(500,'拒绝失败！');
                        }
                    }
                    $result = ['status'=>'success','message'=>"审核操作成功！其中失败".$count."条"];
                }else{
                    $result = ['status'=>'warning','message'=>'无审核数据！'];
                }
                $tran->commit();
            }catch(HttpException $e){
                $tran->rollBack();
                $result = ['status'=>'error','message'=>$e->getMessage()];
            }
            Yii::$app->getSession()->setFlash($result['status'],$result['message']);
            SupplierLog::saveSupplierLog('supplier-update-apply/refuse',$result['status'].':applyId-'.$id.$result['message']);
            return $this->redirect(Yii::$app->request->referrer);
        } else {
            return $this->renderAjax('refuse', [
                'model' => $model,
            ]);
        }
    }
    /**
     * 供应商整合
     */
    public function  actionSupplierIntegrat(){
        $searchModel = new SupplierUpdateApplySearch();
        $dataProvider = $searchModel->search1(Yii::$app->request->queryParams);
        return $this->render('integrat', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 整合成功操作
     * @return \yii\web\Response
     */
    public function actionIntegrat(){
        if(Yii::$app->request->isAjax&&Yii::$app->request->isGet){
            $datas = Yii::$app->request->get('id');
            $integratResult = SupplierUpdateApply::integratApply($datas);
            SupplierLog::saveSupplierLog('supplier-update-apply/integrat',$integratResult['status'].':id-'.$datas.'message-'.$integratResult['message']);
            Yii::$app->getSession()->setFlash($integratResult['status'],$integratResult['message']);
            return $this->redirect(Yii::$app->request->referrer);
        }
    }


    /**
     * 整合不成功操作
     * @param $id
     * @return string|\yii\web\Response
     */
    public function actionIntegratno($ids){

        if (Yii::$app->request->isPost)
        {
            $idDatas = explode(',',$ids);
            try{
                if(is_array($idDatas)&&!empty($idDatas)){
                    foreach ($idDatas as $id){
                        $model = SupplierUpdateApply::find()->where(['id'=>$id])->one();
                        $form = Yii::$app->request->getBodyParam('SupplierUpdateApply');
                        if(empty($model)||$model->integrat_status !=1||$model->status !=2){
                            throw new HttpException(500,'数据异常');
                        }
                        if(!isset($form['fail_reason']) || empty($form['fail_reason'])){
                            throw new HttpException(500,'原因必填');
                        }
                        $userId = Yii::$app->authManager->getUserIdsByRole('供应链');
                        if(!in_array($model->create_user_id,$userId)){
                            throw new HttpException(500,'该申请不是供应链人员申请');
                        }
                        $model->integrat_status = 3;
                        $model->integrat_time = date('Y-m-d H:i:s');
                        $model->fail_reason   = $form['fail_reason'];
                        $model->integrat_user_name = Yii::$app->user->identity->username;
                        if($model->save() == false){
                            throw new HttpException(500,'操作失败！请联系管理员！');
                        }
                    }
                }
                $respone = ['status'=>'success','message'=>'操作成功！'];
            }catch(HttpException $e){
                $respone = ['status'=>'error','message'=>$e->getMessage()];
            }
            Yii::$app->getSession()->setFlash($respone['status'],$respone['message']);
            SupplierLog::saveSupplierLog('supplier-update-apply/integratno',$respone['status'].':'.$id.$respone['message']);
            return $this->redirect(Yii::$app->request->referrer);
        } else {
            return $this->renderAjax('integratno');
        }
    }

    //取消整合状态
    public function  actionCancelIntegrat(){
        if(Yii::$app->request->isPost){
            $ids = Yii::$app->request->getBodyParam('ids');
            if(is_array($ids)&&!empty($ids)){
                SupplierUpdateApply::updateAll(['integrat_status'=>1,'integrat_time'=>null,'integrat_user_name'=>null],['in','id',$ids]);
                SupplierLog::saveSupplierLog('supplier-update-apply/cancel-integrat','id:'.implode(',',$ids).'取消整合状态成功');
                Yii::$app->getSession()->setFlash('success','取消整合状态成功');
                return $this->redirect(Yii::$app->request->referrer);
            }
        }
    }

    //登记是否拿样
    public function actionSample(){
        if(Yii::$app->request->isAjax && Yii::$app->request->isGet){
            $applyId  = Yii::$app->request->getQueryParam('id');
            $type     = Yii::$app->request->getQueryParam('type');
            $ids = strpos($applyId,',') ? explode(',',$applyId) : $applyId;
            if(is_array($ids)&&!empty($ids)){
                foreach($ids as $id){
                    $response = SupplierUpdateApply::sample($id,$type);
                }
            }else{
                $response = SupplierUpdateApply::sample($ids,$type);
            }
            Yii::$app->getSession()->setFlash($response['status'],$response['message']);
            SupplierLog::saveSupplierLog('supplier-update-apply/sample',$response['status'].':'.$applyId.$response['message']);
            return $this->redirect(Yii::$app->request->referrer);
            //return $this->redirect(['supplier-integrat']);
        }
    }

    /**
     *  sku降本以采购数据为主表多对一sku降本
     */
    public function actionCost(){
        set_time_limit(0);
        ini_set('memory_limit', '1024M');   
        $searchModel = new CostPurchaseNum();
        $dataProvider = $searchModel->search2(Yii::$app->request->queryParams);

        //统计价格变化金额
        $query = $searchModel->search2(Yii::$app->request->queryParams,true);
        $data = $query->all();
        $total = 0;
        $is_show = 0;
        if($data && !empty(Yii::$app->request->queryParams)){
            $is_show = 1;
            foreach($data as $model){
                $oldPrice = !empty($model->apply->oldQuotes) ? $model->apply->oldQuotes->supplierprice : 0;
                $newPrice = !empty($model->apply->newQuotes) ? $model->apply->newQuotes->supplierprice : 0;
                $result   = (1000*$newPrice-1000*$oldPrice)/1000;
                $num      = $model->purchase_num;
                $total    += $result*$num;
            }
        }

        return $this->render('cost', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'total' => $total,
            'is_show' => $is_show
        ]);
    }

    /**
     * sku降本以申请数据为主表一对多sku降本
     */
    public function actionApplyCost(){
        $searchModel = new SupplierUpdateApplySearch();
        $dataProvider = $searchModel->search2(Yii::$app->request->queryParams);
        return $this->render('apply_cost', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    /**
     * 样品检验列表
     */
    public function actionSupplierSampleInspect(){
        $searchModel = new SampleInspectSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('inspect', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    //样品人员确定
    public function actionInspect(){
        if(Yii::$app->request->isAjax && Yii::$app->request->isGet){
            $response = SampleInspect::sendTake(Yii::$app->request->getQueryParams());
            SupplierLog::saveSupplierLog('supplier-update-apply/inspect',$response['status'].':'.$response['message']);
            echo json_encode($response);
        }
    }

    //质检合格
    public function actionQuality($id){
        $model = SampleInspect::find()->where(['id'=>$id])->one();
        if(Yii::$app->request->isPost){
            $response = SampleInspect::quality(Yii::$app->request->getBodyParam('SampleInspect'),'quality');
            Yii::$app->getSession()->setFlash($response['status'],$response['message']);
            SupplierLog::saveSupplierLog('supplier-update-apply/quality',$response['status'].':id-'.$id.$response['message']);
            return $this->redirect(Yii::$app->request->referrer);
        } else {
            return $this->renderAjax('qualityno', [
                'model' => $model,
                'type' => 'quality'
            ]);
        }
    }

    //质检不合格
    public function actionQualityno($id){
        $model = SampleInspect::find()->where(['id'=>$id])->one();
        if (Yii::$app->request->isPost)
        {
            $response = SampleInspect::quality(Yii::$app->request->getBodyParam('SampleInspect'),'qualityno');
            Yii::$app->getSession()->setFlash($response['status'],$response['message']);
            SupplierLog::saveSupplierLog('supplier-update-apply/qualityno',$response['status'].':id-'.$id.$response['message']);
            return $this->redirect(Yii::$app->request->referrer);
        } else {
            return $this->renderAjax('qualityno', [
                'model' => $model,
                'type' => 'qualityno'
            ]);
        }
    }


    //整合页面导出所有审核成功的数据
    public function actionExport(){
        $searchForm = Yii::$app->request->getQueryParams();
        $userIds = Yii::$app->authManager->getUserIdsByRole('供应链');
        //以写入追加的方式打开
        $query = SupplierUpdateApplySearch::find()
                ->andFilterWhere(['status'=>2])
                ->andFilterWhere(['<>','type',4])
                ->andFilterWhere(['in','create_user_id',$userIds]);
        if(isset($searchForm['applyname']) && !empty($searchForm['applyname'])){
            $query->andFilterWhere(['create_user_name'=>$searchForm['applyname']]);
        }
        $query->andFilterWhere(['between', 'create_time',$searchForm['start'],$searchForm['end']]);
        $model = $query->all();
        $table = [
            '审核人',
            '审核时间',
            '申请人',
            '整合状态',
            'sku创建时间',
            'sku状态',
            '产品线',
            'sku',
            '货品名称',
            '原单价',
            '原供货商',
            '现单价',
            '现供货商',
            '是否拿样',
            '质检结果',
            '整合备注',
            '申请时间'
        ];
        /* foreach($table as $k=>$v)
         {
             $table[$k]=mb_convert_encoding($v,'gb2312','utf-8');
         }*/
        $table_head = [];
        foreach($model as $k=>$v)
        {
            $table_head[$k][]=$v->update_user_name;
            $table_head[$k][]=$v->update_time;
            $table_head[$k][]=$v->create_user_name;
            $table_head[$k][]=SupplierServices::getIntegratStatus($v->integrat_status,2);
            $table_head[$k][]=$v->productDetail->create_time;
            $table_head[$k][]=SupplierGoodsServices::getProductStatus($v->productDetail->product_status);
            $table_head[$k][]=!empty($v->productDes)&&!empty($v->productDes->product_linelist_id) ? \app\services\BaseServices::getProductLine($v->productDes->product_linelist_id): '';
            $table_head[$k][]=$v->sku;
            $table_head[$k][]=!empty($v->productDetail) ? !empty($v->productDetail->desc) ? $v->productDetail->desc->title : '' : '';
            $table_head[$k][]=!empty($v->oldQuotes) ? $v->oldQuotes->supplierprice : '';
            $table_head[$k][]=!empty($v->oldSupplier) ? $v->oldSupplier->supplier_name : '';
            $table_head[$k][]=!empty($v->newQuotes) ? $v->newQuotes->supplierprice : '';
            $table_head[$k][]=!empty($v->newSupplier) ? $v->newSupplier->supplier_name : '';
            $table_head[$k][]=!empty($v->is_sample) ? htmlspecialchars(SupplierServices::getSampleStatusText($v->is_sample)) : '';
            $table_head[$k][]=!empty($v->qualityResult) ? !empty($v->qualityResult->qc_result) ? SupplierServices::getSampleResultStatusText($v->qualityResult->qc_result) : '' : '';
            $table_head[$k][]=$v->integrat_note;
            $table_head[$k][]=$v->create_time;



        }
        theCsv::export([
            'header' =>$table,
            'data' => $table_head,
        ]);

    }

    //修改备注
    public function actionNote(){
        $ids = Yii::$app->request->getQueryParam('id');
        if(Yii::$app->request->isPost){
            try{
                if(Yii::$app->request->isAjax){
                    $ids[] = Yii::$app->request->getBodyParam('id');
                    $note  = Yii::$app->request->getBodyParam('note');
                }else{
                    $id = Yii::$app->request->getBodyParam('SupplierUpdateApply');
                    $ids = explode(',',$id['id']);
                    $note = $id['note'];
                }
                foreach($ids as $value){
                    $apply = SupplierUpdateApply::find()->where(['id'=>$value])->one();
                    if(empty($apply)){
                        throw new HttpException(500,'整合数据错误!');
                    }
                    $apply->integrat_note = $note;
                    $apply->save(false);
                }
                $response = ['status'=>'success','message'=>'备注编辑成功！','note'=>$note];
            }catch(HttpException $e){
                $response = ['status'=>'error','message'=>$e->getMessage(),'note'=>''];
            }
            //Yii::$app->getSession()->setFlash($response['status'],$response['message']);
            SupplierLog::saveSupplierLog('supplier-update-apply/note',$response['status'].':id-'.implode(',',$ids).$response['message']);
            if(Yii::$app->request->isAjax){
                echo json_encode($response);
            }else{
                Yii::$app->getSession()->setFlash($response['status'],$response['message']);
                return $this->redirect(Yii::$app->request->referrer);
            }
        }else{
            return $this->renderAjax('note',['ids'=>$ids]);
        }
    }

    //样品检测追加采购单号
    public function actionInsertNumber($ids){
        $idsArray = explode(',',$ids);
        $datas =  SampleInspect::find()->where(['in','id',$idsArray])->all();
        if(Yii::$app->request->isPost){
            $form = Yii::$app->request->getBodyParam('SampleInspect');
            if(!isset($form['pur_number'])||empty($form['pur_number'])){
                Yii::$app->getSession()->setFlash('error','请填写采购单号再提交！');
                return $this->redirect(Yii::$app->request->referrer);
            }
            $errorSku=[];
            if($form['pur_number']== 'PO000000'){
                foreach ($datas as $v){
                    $v->updateAttributes(['pur_number'=>$form['pur_number']]);
                }
            }else{
                $purNumber = PurchaseOrder::find()->where(['pur_number'=>$form['pur_number']])->one();
                if(empty($purNumber)){
                    Yii::$app->getSession()->setFlash('error','采购单不存在');
                    return $this->redirect(Yii::$app->request->referrer);
                }
                $items = PurchaseOrderItems::find()->select('sku')->where(['pur_number'=>$form['pur_number']])->asArray()->all();
                foreach ($datas as $v){
                    if(!empty($v->pur_number)||!in_array($v->sku,array_column($items,'sku'))){
                        $errorSku[]=$v->sku;
                        continue;
                    }else{
                        $v->updateAttributes(['pur_number'=>$form['pur_number']]);
                    }
                }
            }
            Yii::$app->getSession()->setFlash('success','操作成功，其中'.implode(',',$errorSku).'数据有误写入失败');
            return $this->redirect(Yii::$app->request->referrer);
        }else{
            return $this->renderAjax('number',['data'=>$datas]);
        }
    }

    //样品检测更新采购单号
    public function actionUpdateNumber($ids){
        $idsArray = explode(',',$ids);
        $datas =  SampleInspect::find()->where(['in','id',$idsArray])->all();
        if(Yii::$app->request->isPost){
            $form = Yii::$app->request->getBodyParam('SampleInspect');
            if(!isset($form['pur_number'])||empty($form['pur_number'])){
                Yii::$app->getSession()->setFlash('error','请填写采购单号再提交！');
                return $this->redirect(Yii::$app->request->referrer);
            }
            $errorSku=[];
            if($form['pur_number']== 'PO000000'){
                foreach ($datas as $v){
                    $v->updateAttributes(['pur_number'=>$form['pur_number']]);
                }
            }else{
                $purNumber = PurchaseOrder::find()->where(['pur_number'=>$form['pur_number']])->one();
                if(empty($purNumber)){
                    Yii::$app->getSession()->setFlash('error','采购单不存在');
                    return $this->redirect(Yii::$app->request->referrer);
                }
                $items = PurchaseOrderItems::find()->select('sku')->where(['pur_number'=>$form['pur_number']])->asArray()->all();
                foreach ($datas as $v){
                    if(empty($v->pur_number)||!in_array($v->sku,array_column($items,'sku'))){
                        $errorSku[]=$v->sku;
                        continue;
                    }else{
                        $v->updateAttributes(['pur_number'=>$form['pur_number']]);
                    }
                }
            }
            Yii::$app->getSession()->setFlash('success','操作成功，其中'.implode(',',$errorSku).'数据有误更新失败');
            return $this->redirect(Yii::$app->request->referrer);
        }else{
            return $this->renderAjax('number',['data'=>$datas]);
        }
    }

    //整合数据统计
    public function actionIntegratStat(){
        $searchModel = new SupplierUpdateApplySearch();
        $params = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search3($params);

        return $this->render('integrat-stat', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'params' => $params,
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
        $id = Yii::$app->request->get('ids');
        $id = strpos($id,',')?explode(',',$id):$id;

        if (!empty($id)){
            $model = CostPurchaseNum::find()
                ->alias('t')
                ->select([
                    'apply_id'=>'t.apply_id',
                    'purchase_num'=>'ifnull(t.purchase_num,0)',
                    'create_user_name'=>'ifnull(apply.create_user_name,"")',
                    'sku'=>'t.sku',
                    'title'=>'ifnull(pd.title,"")',
                    'supplier_name'=>'ifnull(su.supplier_name,"")',
                    'update_time'=>'ifnull(apply.update_time,"")',
                    'cost_begin_time'=>'ifnull(apply.cost_begin_time,"")',
                    'date'=>'t.date',
                    'last_price'=>'ifnull(old.supplierprice,0)',
                    'price'=>'ifnull(new.supplierprice,0)'
                ])
                ->where(['apply.status' => 2])
                ->andWhere(['t.status' => 1])
                ->andWhere(['not in', 'apply.type', [4, 5]])
                ->leftJoin(SupplierUpdateApply::tableName() . ' apply', 't.apply_id=apply.id')
                ->leftJoin(SupplierQuotes::tableName() . ' old', 'old.id=apply.old_quotes_id')
                ->leftJoin(SupplierQuotes::tableName() . ' new', 'new.id=apply.new_quotes_id')
                ->leftJoin(ProductDescription::tableName().' pd','t.sku=pd.sku')
                ->leftJoin(Supplier::tableName().' su','apply.new_supplier_code=su.supplier_code')
                ->andWhere('left(t.date,7)>=left(apply.cost_begin_time,7)')
                ->andWhere('old.supplierprice <> new.supplierprice')
                ->andWhere(['<>', 't.purchase_num', 0])
                ->andWhere(['in', 't.id', $id])
                ->asArray()->all();
        }else {
            $searchData = \Yii::$app->session->get('CostPurchaseNumData');
            $searchModel = new CostPurchaseNum();
            $query = $searchModel->search2($searchData,true);
            $query->leftJoin(ProductDescription::tableName().' pd','t.sku=pd.sku');
            $query->leftJoin(Supplier::tableName().' su','apply.new_supplier_code=su.supplier_code');
            $query->select([
                'apply_id'=>'t.apply_id',
                'purchase_num'=>'ifnull(t.purchase_num,0)',
                'create_user_name'=>'ifnull(apply.create_user_name,"")',
                'sku'=>'t.sku',
                'title'=>'ifnull(pd.title,"")',
                'supplier_name'=>'ifnull(su.supplier_name,"")',
                'update_time'=>'ifnull(apply.update_time,"")',
                'cost_begin_time'=>'ifnull(apply.cost_begin_time,"")',
                'date'=>'t.date',
                'last_price'=>'ifnull(old.supplierprice,0)',
                'price'=>'ifnull(new.supplierprice,0)'
            ]);
            $model = $query->asArray()->all();
        }
        //当前月份 第一天和最后一天
        $searchData = \Yii::$app->session->get('CostPurchaseNumData');
        $month_current = $searchData['CostPurchaseNum']['date'];
        if (empty($month_current)) {
            Yii::$app->getSession()->setFlash('error','请选择统计时间并搜索');
            return $this->redirect(Yii::$app->request->referrer);
        }
        //表格头的输出  采购需求建立时间、财务审核时间、财务付款时间、销售备注
        $cell_value = ['供应链人员','SKU','品名','供应商','价格变化时间','首次计算时间','统计时间','原价','现价','价格变化幅度','降本比例','采购数量','价格变化金额','采购单号','采购时间','采购员'];
        $circleArray = array_chunk($model,1000);
        $exportArray=[];
        if(!empty($circleArray)){
            foreach ($circleArray as $value){
                foreach ( $value as $v )
                {
                    $oldPrice = !empty($v['last_price']) ? $v['last_price'] : 0;
                    $newPrice = !empty($v['price']) ? $v['price']: 0;
                    $jj_fd = $oldPrice-$newPrice;
                    // 降本比例=降价幅度/原价*100%;
                    $bili = $oldPrice==0 ? ($jj_fd*100).'%': round(($jj_fd/$oldPrice), 4) * 100 . '%';
                    $result   = (1000*$newPrice-1000*$oldPrice)/1000;
                    $num      = $v['purchase_num'];
                    $exportArray[] =[
                        $v['create_user_name'],
                        $v['sku'],
                        $v['title'],
                        $v['supplier_name'],
                        $v['update_time'],
                        $v['cost_begin_time'],
                        date('Y--m',strtotime($v['date'])),
                        $oldPrice,
                        $newPrice,
                        $result,
                        $bili,
                        $num,
                        $result*$num,
                        '',
                        '',
                        ''
                    ];
                    $dateArray = $this->getTimeArray($v['cost_begin_time'],30,[],date('Y-m',strtotime($month_current)));
                    $query = PurchaseOrderItems::find()
                        ->alias('t')
                        ->select(['ctq'=>'t.ctq','cancel_num'=>'ifnull(b.cancel_ctq,0)','cancel_num2'=>'ifnull(refund_qty,0)','pur_number'=>'a.pur_number','audit_time'=>'a.audit_time',
                            'buyer'=>'a.buyer','cancel_audit_time'=>'c.audit_time'])
                        ->leftJoin(PurchaseOrder::tableName().' a','a.pur_number=t.pur_number')
                        ->leftJoin(PurchaseOrderCancelSub::tableName().' b','t.pur_number=b.pur_number and t.sku=b.sku')
                        ->leftJoin(PurchaseOrderCancel::tableName().' c','c.id = b.cancel_id')
                        ->leftJoin(PurchaseOrderRefundQuantity::tableName().' d','t.sku=d.sku and t.pur_number=d.pur_number')
                        ->andWhere(['or',['c.audit_status'=>2],['c.audit_status'=>null]])
                        ->andWhere(['or',['and',['t.base_price'=>0],['<=','t.price',$newPrice]],['and',['<>','t.base_price',0],['<=','t.base_price',$newPrice]]])
                        ->andFilterWhere(['t.sku'=>$v['sku']])
                        ->andFilterWhere(['NOT IN','a.purchas_status',[1,2,4,10]])
                        ->andFilterWhere(['>=','a.audit_time',$dateArray[0]])
                        ->andFilterWhere(['<','a.audit_time',$dateArray[1]])
                        ->orderBy('a.audit_time DESC');
                        $pur_items = $query->asArray()->all();
                    if (!empty($pur_items)) {
                        foreach ($pur_items as $val) {
                            if(preg_match('/^ABD/',$val['pur_number'])&&!empty($val['cancel_audit_time'])&&strtotime($val['cancel_audit_time'])<=strtotime('2018-11-28 12:00:00')){
                                $cancel = 0;
                            }else {
                                $cancel =   $val['cancel_num2'] + $val['cancel_num'];
                            }
                            if(($val['ctq']-$cancel)<=0){
                                continue;
                            }
                            $exportArray[] =[
                                $v['create_user_name'],
                                $v['sku'],
                                $v['title'],
                                $v['supplier_name'],
                                $v['update_time'],
                                $v['cost_begin_time'],
                                date('Y--m',strtotime($v['date'])),
                                $oldPrice,
                                $newPrice,
                                $result,
                                $bili,
                                $val['ctq']-$cancel,
                                $result*$num,
                                $val['pur_number'],
                                $val['audit_time'],
                                $val['buyer']
                            ];
                        }
                    }
                    unset($v);
                }
                unset($value);
            }
        }
        theCsv::export([
            'header' =>$cell_value,
            'data' => $exportArray,
        ]);
    }

    /**
     * PHPExcel导出 -- sku降本（申请）
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function actionExportCsvApplyCost()
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');
        $id = Yii::$app->request->get('ids');
        $id = strpos($id,',')?explode(',',$id):$id;

        if (!empty($id)) {
            $model = SupplierUpdateApplySearch::find()
                ->alias('t')
                ->select('t.*')
                ->leftJoin(SupplierQuotes::tableName().' old','old.id=t.old_quotes_id')
                ->leftJoin(SupplierQuotes::tableName().' new','new.id=t.new_quotes_id')
                ->where(['t.status'=>2])
                ->andWhere(['not in','t.type',[4,5]])
                ->andWhere('old.supplierprice <> new.supplierprice')
                ->andWhere(['in','t.id',$id])
//                ->asArray()
                ->all();
        } else {
            $searchData = \Yii::$app->session->get('SupplierApplyCostData');
            $searchModel = new SupplierUpdateApplySearch();
            $query = $searchModel->search2($searchData,true);
            $model = $query->all();
        }

        $objectPHPExcel = new \PHPExcel();
        $objectPHPExcel->setActiveSheetIndex(0);
        $n = 0;
        //报表头的输出
        $objectPHPExcel->getActiveSheet()->mergeCells('A1:L1'); //合并单元格
//        $objectPHPExcel->getActiveSheet()->mergeCells('A1:A2');
        $objectPHPExcel->getActiveSheet()->setCellValue('A1','SKU降本');  //设置表标题
        $objectPHPExcel->setActiveSheetIndex(0)->getStyle('A1')->getFont()->setSize(24); //设置字体大小
        $objectPHPExcel->setActiveSheetIndex(0)->getStyle('A1')
            ->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        //表格头的输出  采购需求建立时间、财务审核时间、财务付款时间、销售备注
//        $cell_value = ['供应链人员','SKU','品名','供应商','价格变化时间','首次计算时间','统计时间','原价','现价','价格变化幅度','采购数量','价格变化金额'];
        $cell_value = ['供应链人员','SKU','品名','供应商','价格变化时间','首次计算时间','原价','现价','价格变化幅度','采购数量','价格变化金额'];
        foreach ($cell_value as $k => $v) {
            $objectPHPExcel->setActiveSheetIndex(0)->setCellValue(chr($k+65) . '3',$v);
        }
        //设置表头居中
        $objectPHPExcel->getActiveSheet()->getStyle('A3:L3')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //设置数据水平靠左和垂直居中
        $objectPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $objectPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        foreach ( $model as $v )
        {
                //明细的输出
                $objectPHPExcel->getActiveSheet()->setCellValue('A'.($n+4) ,$v->create_user_name);
                $objectPHPExcel->getActiveSheet()->setCellValue('B'.($n+4) ,$v->sku);

                $name =!empty($v->productDetail) ? !empty($v->productDetail->desc) ? $v->productDetail->desc->title : '' : '';
                $objectPHPExcel->getActiveSheet()->setCellValue('C'.($n+4) ,$name);

                $objectPHPExcel->getActiveSheet()->setCellValue('D'.($n+4) ,!empty($v->newSupplier) ? $v->newSupplier->supplier_name : '');
                $objectPHPExcel->getActiveSheet()->setCellValue('E'.($n+4) ,!empty($v->update_time) ? $v->update_time : '');
                $objectPHPExcel->getActiveSheet()->setCellValue('F'.($n+4) ,!empty($v->cost_begin_time) ? $v->cost_begin_time : '');
                $objectPHPExcel->getActiveSheet()->setCellValue('G'.($n+4) ,!empty($v->oldQuotes) ? $v->oldQuotes->supplierprice : '');
                $objectPHPExcel->getActiveSheet()->setCellValue('H'.($n+4) ,!empty($v->newQuotes) ? $v->newQuotes->supplierprice : '');

                $oldPrice = !empty($v->oldQuotes) ? $v->oldQuotes->supplierprice : 0;
                $newPrice = !empty($v->newQuotes) ? $v->newQuotes->supplierprice : 0;
                $result  = (1000*$newPrice-1000*$oldPrice)/1000;
                $objectPHPExcel->getActiveSheet()->setCellValue('I'.($n+4) ,$result);

                $html = '';
                if(!empty($v->skuCost)){
                    foreach ($v->skuCost as $data){
                        $html .= date('Y-m',strtotime($data->date)).'采购数量:'.$data->purchase_num;
                    }
                }
                $objectPHPExcel->getActiveSheet()->setCellValue('J'.($n+4) ,$html);

                $oldPrice = !empty($v->oldQuotes) ? $v->oldQuotes->supplierprice : 0;
                $newPrice = !empty($v->newQuotes) ? $v->newQuotes->supplierprice : 0;
                $result   = (1000*$newPrice-1000*$oldPrice)/1000;
                $html_price = '';
                if(!empty($v->skuCost)){
                    foreach ($v->skuCost as $data){
                        $html_price .= date('Y-m',strtotime($data->date)).'金额:'.$data->purchase_num*$result;
                    }
                }
                $objectPHPExcel->getActiveSheet()->setCellValue('K'.($n+4) ,$html_price);
                $n = $n +1;
        }

        for ($i = 65; $i<77; $i++) {
            $objectPHPExcel->getActiveSheet()->getColumnDimension(chr($i))->setWidth(15);
            $objectPHPExcel->getActiveSheet()->getStyle( chr($i) . "3")->getFont()->setBold(true);
        }
        $objectPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        $objectPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);

        //设置样式
        $objectPHPExcel->getActiveSheet()->getStyle('A1:L'.($n+4))->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        ob_end_clean();
        ob_start();
        header("Content-type:application/vnd.ms-excel;charset=UTF-8");
        header('Content-Type : application/vnd.ms-excel');
        header('Content-Disposition:attachment;filename="'.'SKU降本(申请)-'.date("Y年m月j日").'.xls"');
        $objWriter= \PHPExcel_IOFactory::createWriter($objectPHPExcel,'Excel5');
        $objWriter->save('php://output');
        die;
    }

    //根据开始时间和天数间隔计算采购数据计算时间,date统计时间;
    protected function getTimeArray($begin_time,$limit=30,$dataArray=[],$date=null){
        if($limit<0){
            return [];
        }
        if(!empty($date)){
            $dateBegin = date('Y-m-01 00:00:00',strtotime($date));
            $dateEnd = date('Y-m-01 00:00:00',strtotime("$date +1 month"));
        }
        $month_big = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
        //开始的月份
        $date_month_old = (int)date('m',strtotime($begin_time));
        //下个月的月份
        $year = $date_month_old==12 ? date('Y',strtotime($begin_time)) +1 : date('Y',strtotime($begin_time));
        $date_time_new = strtotime('1 '.$month_big[$date_month_old%12].' '.$year);

        //今天的时间戳
        $date_time_old = strtotime(date('d',strtotime($begin_time)).' '.$month_big[$date_month_old-1].' '.date('Y',strtotime($begin_time)));
        //距下月剩余时间
        //var_dump($limit);
        $time_new = ($date_time_new - $date_time_old)/24/60/60;
        $old_limit=$limit;
        $limit-=$time_new;
        if($limit>0){
            $dataArray[] =[0=>$begin_time,1=>date('Y-m-d H:i:s',$date_time_new)];
            if(isset($dateBegin)&&isset($dateEnd)&&strtotime($dateBegin)<=strtotime($begin_time)&&strtotime($dateEnd)>=$date_time_new){
                return [0=>$begin_time,1=>date('Y-m-d H:i:s',$date_time_new)];
            }else{
                return $this->getTimeArray(date('Y-m-d H:i:s',$date_time_new),$limit,$dataArray,$date);
            }
        }else{
            $dataArray[] =[0=>$begin_time,1=>date('Y-m-d 00:00:00',strtotime("$begin_time + $old_limit day"))];
            if(isset($dateBegin)&&isset($dateEnd)){
                if(strtotime($dateBegin)<=strtotime($begin_time)&&strtotime($dateEnd)>=$date_time_new){
                    return [0=>$begin_time,1=>date('Y-m-d 00:00:00',strtotime("$begin_time + $old_limit day"))];
                }else{
                    return [];
                }
            }else{
                return $dataArray;
            }
        }

    }

}
