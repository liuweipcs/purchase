<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/29
 * Time: 11:05
 */

namespace app\controllers;

use app\config\Vhelper;
use app\models\Product;
use app\models\PurchaseHistory;
use app\models\PurchaseNote;
use app\models\PurchaseSuggestNote;
use app\models\SupplierQuotes;
use app\services\BaseServices;
use Yii;
use app\models\PurchaseSuggestHistoryMrp;
use app\models\PurchaseSuggestHistoryMrpSearch;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\PurchaseOrder;
use app\models\PurchaseOrderItems;
use app\services\CommonServices;
use app\models\User;
use app\models\PurchaseLog;
use app\models\Stock;
use app\models\PurchaseUser;
use yii\helpers\ArrayHelper;
use app\services\PurchaseOrderServices;
use yii\helpers\BaseArrayHelper;
use app\models\PurchaseSuggestQuantity;
use app\services\SupplierGoodsServices;
use app\models\ProductSourceStatus;

/**
 * PurchaseSuggestHistoryController implements the CRUD actions for PurchaseSuggestHistory model.
 * @desc 历史采购建议
 */
class PurchaseSuggestHistoryMrpController extends BaseController
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
     * Lists all PurchaseSuggestHistory models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PurchaseSuggestHistoryMrpSearch();
        $map=Yii::$app->request->queryParams;
        Yii::$app->session->set('get_parameters', $map);

        $dataProvider = $searchModel->search($map);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    /**
     * Finds the PurchaseSuggestHistory model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return PurchaseSuggestHistory the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PurchaseSuggestHistory::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    /**
     * @desc 根据选中的采购建议生成相应的采购单，对选中的数据进行验证：同一供应商，同一仓库的才能生成同一个采购单
     * @author Jimmy
     * @date 2017-04-17 14:33:11
     */
    public function actionCreatePurchase()
    {

        if(Yii::$app->request->isAjax||Yii::$app->request->isGet){
            $res=[];
            $this->checkPurchaseData($res);
            $model=new User();
            $users=$model->find()->all();

            $data=Yii::$app->request->get();
//            var_dump($data);
//            exit;


            return $this->renderAjax('create-purchase', ['data' => $res,'users'=>$users]);
        } elseif(Yii::$app->request->isPost) {
//            echo '1';
//            exit;
            $data=Yii::$app->request->post();
            $this->actionSavePurchase($data);
        }
    }

    /**
     * @desc 根据选中的采购建议生成相应的采购单，对选中的数据进行验证：同一供应商，同一仓库的才能生成同一个采购单
     * @author Jimmy
     * @date 2017-04-07 09:42:11
     */
    protected function actionSavePurchase($res)
    {
        //拼凑主表数据
        $model_order=new PurchaseOrder();
        $model_order->load($res);
        $model_order->pur_number      = CommonServices::getNumber('PO');
        $model_order->operation_type  ='2';
        $model_order->created_at      = date('Y-m-d H:i:s');
        $model_order->creator         = Yii::$app->user->identity->username;
        $model_order->buyer           = Yii::$app->user->identity->username;
        $model_order->merchandiser    = $res['PurchaseOrder']['merchandiser'];
        $model_order->purchas_status  = 1;//待确认
        $model_order->create_type     = 1;//创建类型
        $model_order->is_expedited    = $res['PurchaseOrder']['is_expedited'];//加急
        $transactions=Yii::$app->db->beginTransaction();
        //加入备注
        $PurchaseNote=[
            'pur_number'=>$model_order->pur_number,
            'note'      =>$res['PurchaseNote']['note'],
        ];
        $model_note = new PurchaseNote();
        $model_note->saveNote($PurchaseNote);
        //加入采购单日志
        $data=[
            'pur_number'=>$model_order->pur_number,
            'note'      =>'生成采购计划单',
        ];
        PurchaseLog::addLog($data);
        //插入主表数据

        if(false==$model_order->save())
        {
            $errors=$model_order->getFirstErrors();
            $str="</br>";
            foreach ($errors as $error)
            {
                $str.=$error."</br>";
            }
            Yii::$app->getSession()->setFlash('error','操作失败了,请联系管理员:'.$str,true);
            $transactions->rollBack();
            return $this->redirect(['index']);
        }
        foreach ($res['PurchaseOrder']['items'] as $val)
        {
            $sb= PurchaseOrderItems::findOne(['pur_number'=>$model_order->pur_number,'sku'=>$val['sku']]);
            if($sb)
            {
                $sb->qty         += $val['qty'];
                $sb->save(false);
            } else {
                $model_items = new PurchaseOrderItems();
                $model_items->load(['PurchaseOrderItems' => $val]);
                $model_items->pur_number       = $model_order->pur_number;
                $model_items->items_totalprice = $val['qty'] * $val['price'];
                $model_items->product_img      = Product::find()->select('uploadimgs')->where(['sku' => $val['sku']])->scalar();
                //插入条目数据
                if (false == $model_items->save(false)) {
                    $errors = $model_items->getFirstErrors();
                    $str    = "</br>";
                    foreach ($errors as $error) {
                        $str .= $error . "</br>";
                    }
                    Yii::$app->getSession()->setFlash('error', '操作失败了,请联系管理员:' . $str, true);
                    $transactions->rollBack();
                    return $this->redirect(['index']);
                }
                //回写采购建议，标识已经生产采购单
                $model_suggest           = new PurchaseSuggestHistoryMrp();
                $where['sku']            = $val['sku'];
                $where['warehouse_code'] = $model_order->warehouse_code;
                //$where['is_purchase']    = 'Y';

                $id = $model_suggest->find()->where($where)->one();
                $id->load(['PurchaseSuggestHistoryMrp' => ['is_purchase' => 'N','state'=>1,'demand_number' =>$model_order->pur_number]]);

                //更新在途库存 暂时关闭
                $mods = PurchaseOrderItems::getSKUc($model_order->pur_number);
                Stock::saveStock($mods, $model_order->warehouse_code);
                if ($id->save(false) == false) {
                    $errors = $model_suggest->getFirstErrors();
                    $str    = "</br>";
                    foreach ($errors as $error) {
                        $str .= $error . "</br>";
                    }
                    Yii::$app->getSession()->setFlash('error', '操作失败,请联系管理员:' . $str, true);
                    $transactions->rollBack();
                    return $this->redirect(['index']);
                }
            }
        }
        $transactions->commit();
        Yii::$app->getSession()->setFlash('success',"恭喜，操作成功！生成的采购单为: {$model_order->pur_number}",true);
        if(Yii::$app->request->post('flag'))
        {
            return $this->redirect(['purchase-suggest-supplier/index']);
        }else{
            return $this->redirect(['purchase-order-confirm/index']);
        }

    }
    /**
     * @desc 对提交过来的数据信息进行相关核查
     * @return array $data 选中的需要备货的数据信息
     * @author  Jimmy
     * @date 2017-04-07 11:49:11
     */
    protected function checkPurchaseData(&$data)
    {
        $ids=Yii::$app->request->get('ids');
        if(empty($ids)){
            Yii::$app->getSession()->setFlash('error', '是的,缺少参数是不会让你通过的！',true);
            return $this->redirect(['index']);
        }
        $model=new PurchaseSuggestHistoryMrp();
        $map['id']=explode(',',$ids);
        $num=$model->find()->groupBy(['supplier_code','warehouse_code'])->where($map)->count();
        if($num>1)
        {
            Yii::$app->getSession()->setFlash('error','是的,同一供应商，同一仓库的才能生成同一个采购单!',true);
            return $this->redirect(['index']);
        }
        $data=$model->find()->where($map)->asArray()->all();
        if(count($data)==0)
        {
            return $this->redirect(['index']);
        }
    }
    /**
     * @desc Ajax 修改采购建议数量
     * @author Jimmy
     * @date 2017-04-13 13:50:11
     */
    public function actionUpdateQty()
    {
        $id= Yii::$app->request->post('id');
        $qty= Yii::$app->request->post('qty');
        $model=new PurchaseSuggestHistoryMrp();
        $res=$model->updateAll(['qty'=>$qty],['id'=>$id]);
        return $res;
    }

    /** 图片查看
     * @param $img
     * @param $sku
     */
    public function actionImg()
    {
        $Img = Yii::$app->request->get();
        if($Img['sku'])
        {
            $img = Vhelper::toSkuImgBig($Img['sku'],'');
            return $this->renderAjax('view',['img'=>$img]);
        } else{
            return 'sku传值有问题';
        }

    }

    /**
     * 半年内下单的供应商产品地址
     * @return string
     */
    public function actionPaddress(){
        $sku=Yii::$app->request->get('sku');

        $etime=date('Y-m-d H:i:s',time());
        $stime=date('Y-m-d H:i:s',time()-86400*30*6);

        if(!empty($sku)){
            $model=SupplierQuotes::find()
                ->select('supplier_product_address')
                ->where(['product_sku'=>$sku])
                ->andFilterWhere(['between','add_time',$stime,$etime])
                ->asArray()->all();

            $model2=PurchaseHistory::find()
                ->select('features')
                ->where(['sku'=>$sku])
                ->andFilterWhere(['between','purchase_time',$stime,$etime])
                ->asArray()->all();
        }

        return $this->renderAjax('paddress',[
            'model'=>$model,
            'model2'=>$model2,
        ]);
    }

    /**
     * 历史采购信息
     * @return string
     */
    public function actionHistorPurchaseInfo(){
        $sku=Yii::$app->request->get('sku');
        $pur_items=PurchaseOrderItems::find()
            ->alias('b')
            ->leftJoin('pur_purchase_order as a','a.pur_number = b.pur_number')
            ->where(['NOT IN','a.purchas_status',[1,2,3,4,10]])
            ->andFilterWhere(['b.sku'=>$sku])
            ->orderBy('submit_time desc')
            ->all();

        $model2=PurchaseHistory::find()->where(['sku'=>$sku])->orderBy('purchase_time desc')->all();

        return $this->renderAjax('histor-purchase-info',[
            'model'=>$pur_items,
            'model2'=>$model2,
        ]);
    }


    public function actionFbaHistorPurchaseInfo(){
        $sku=Yii::$app->request->get('sku');
        $pur_items=PurchaseOrderItems::find()
            ->alias('b')
            ->leftJoin('pur_purchase_order as a','a.pur_number = b.pur_number')
            ->where(['NOT IN','a.purchas_status',[1,2,3,4,10]])
            ->andFilterWhere(['b.sku'=>$sku])
            ->orderBy('submit_time desc')
            ->all();

        $model2=PurchaseHistory::find()->where(['sku'=>$sku])->orderBy('purchase_time desc')->all();

        return $this->render('histor-purchase-info',[
            'model'=>$pur_items,
            'model2'=>$model2,
        ]);
    }
    /**
     * PHPExcel导出
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function actionExport()
    {
        set_time_limit(0);
        ini_set('memory_limit','1024M');
        $query = PurchaseSuggestHistoryMrp::find();
        $query->orderBy('left_stock asc');
        $query->andWhere(['in','warehouse_code',['DG','SZ_AA','xnc','ZDXNC','CDxuni','ZMXNC_WM','ZMXNC_EB']]);
        $query->andWhere(['>','qty',0]);
        $query->andWhere(['in','purchase_type',1]);

        //是否是超级管理员
        $puid=PurchaseUser::find()->select('pur_user_id')->where(['in','grade',[1,2,3]])->asArray()->all();
        $ids = ArrayHelper::getColumn($puid, 'pur_user_id');
        if(in_array(Yii::$app->user->id,$ids))
        {

        } else {
            $query->andWhere(['in', 'buyer_id',Yii::$app->user->id]);
        }

        $get_parameter = Yii::$app->session->get('get_parameters');
         // Vhelper::dump($get_parameter);
        if (empty($get_parameter['PurchaseSuggestHistoryMrpSearch'])) {
            $daterangepicker_start = date('Y-m-d 00:00:00', time()-86400);
            $daterangepicker_end   = date('Y-m-d 23:59:59', time()-86400);
            $query->andFilterWhere(['between', 'created_at', $daterangepicker_start, $daterangepicker_end]);
            $query->andFilterWhere(['=','product_status',4]);
        } else {
            //以写入追加的方式打开
            //时间
            if (!empty($get_parameter['PurchaseSuggestHistoryMrpSearch']['end_time'])) {
                $start_time = $get_parameter['PurchaseSuggestHistoryMrpSearch']['start_time'] . ' 00:00:00';
                $end_time = $get_parameter['PurchaseSuggestHistoryMrpSearch']['end_time'] . ' 23:59:59';
            } else {
                $start_time = date('Y-m-d 00:00:00', time()-86400);
                $end_time = date('Y-m-d 23:59:59', time()-86400);
            }

            $query->andFilterWhere(['between', 'created_at', $start_time, $end_time]);

            //sku
            if (isset($get_parameter['PurchaseSuggestHistoryMrpSearch']['sku']) && trim($get_parameter['PurchaseSuggestHistoryMrpSearch']['sku']) != '') {
                $query->andWhere(['in', 'sku',trim($get_parameter['PurchaseSuggestHistoryMrpSearch']['sku'])]);
            }
            //处理状态
            if (isset($get_parameter['PurchaseSuggestHistoryMrpSearch']['state']) && !empty($get_parameter['PurchaseSuggestHistoryMrpSearch']['state'])) {
                $query->andWhere(['in', 'state',$get_parameter['PurchaseSuggestHistoryMrpSearch']['state']]);
            }
            //供应商
            if (isset($get_parameter['PurchaseSuggestHistoryMrpSearch']['supplier_code']) && !empty($get_parameter['PurchaseSuggestHistoryMrpSearch']['supplier_code'])) {
                $query->andWhere(['in', 'supplier_code',$get_parameter['PurchaseSuggestHistoryMrpSearch']['supplier_code']]);
            }
            //是否欠货
             if(isset($get_parameter['PurchaseSuggestHistoryMrpSearch']['left']) && $get_parameter['PurchaseSuggestHistoryMrpSearch']['left'] == 1)
             {
                 $query->andFilterWhere(['<','left_stock',0]);
             } elseif(isset($get_parameter['PurchaseSuggestHistoryMrpSearch']['left']) && $get_parameter['PurchaseSuggestHistoryMrpSearch']['left'] == 2){
                 $query->andFilterWhere(['>=','left_stock',0]);
             }
            //产品状态
            if(isset($get_parameter['PurchaseSuggestHistoryMrpSearch']['product_status']) && !empty($get_parameter['PurchaseSuggestHistoryMrpSearch']['product_status']))
            {
                $query->andFilterWhere(['in','product_status',$get_parameter['PurchaseSuggestHistoryMrpSearch']['product_status']]);
            }
            //采购员
            if (isset($get_parameter['PurchaseSuggestHistoryMrpSearch']['buyer_id']) && !empty($get_parameter['PurchaseSuggestHistoryMrpSearch']['buyer_id']) ) {
                if (is_numeric($get_parameter['PurchaseSuggestHistoryMrpSearch']['buyer_id'])) {
                    $query->andFilterWhere(['in','buyer_id',$get_parameter['PurchaseSuggestHistoryMrpSearch']['buyer_id']]);
                } else {
                    $group=[
                        'g1'=>'1',
                        'g2'=>'2',
                        'g3'=>'3',
                        'g4'=>'4',
                        'g5'=>'5',
                        
                    ];
                    $gid=$group[$get_parameter['PurchaseSuggestHistoryMrpSearch']['buyer_id']];
                    $puid=PurchaseUser::find()->select('pur_user_id')->where(['group_id'=>$gid])->asArray()->all();
                    $query->andFilterWhere(['in', 'buyer_id', array_values(BaseArrayHelper::map($puid,'pur_user_id','pur_user_id'))]);
                }
            }
        }
         // $model = $query->createCommand()->getRawSql(); Vhelper::dump($model);

        $model = $query->asArray()->all();
//        Vhelper::dump(count($model));

        if (empty($model)) {
            Yii::$app->getSession()->setFlash('error','没有你所需要的数据!!',true);
            return $this->redirect(['index']);
        }

        $objectPHPExcel = new \PHPExcel();
        $objectPHPExcel->setActiveSheetIndex(0);

        $n = 0;
        //报表头的输出
        $objectPHPExcel->getActiveSheet()->mergeCells('A1:P1'); //合并单元格
        // $objectPHPExcel->getActiveSheet()->mergeCells('A1:A2');
        $objectPHPExcel->getActiveSheet()->setCellValue('A1','采购历史建议表');  //设置表标题
        $objectPHPExcel->setActiveSheetIndex(0)->getStyle('A1')->getFont()->setSize(24); //设置字体大小
        $objectPHPExcel->setActiveSheetIndex(0)->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objectPHPExcel->getActiveSheet()->getStyle('P')->getAlignment()->setWrapText(true);
        //表格头的输出  采购需求建立时间、财务审核时间、财务付款时间、销售备注
        $cell_value = ['序号', '采购员', 'SKU', '产品状态', '货源状态', '产品名称', '仓库名称', '单价', '日均销量', '可用库存', '在途库存', '欠货', '采购数量', '需求生成时间', '处理状态', '未处理原因'];
        foreach ($cell_value as $k => $v) {
            $objectPHPExcel->setActiveSheetIndex(0)->setCellValue(chr($k+65) . '3',$v);
        }

        //设置表头居中
        $objectPHPExcel->getActiveSheet()->getStyle('A3:P3')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //设置数据水平靠左和垂直居中
        $objectPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $objectPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        // Vhelper::dump($model);
        foreach ($model as $v )
        {
            //明细的输出
            $objectPHPExcel->getActiveSheet()->setCellValue('A' . ($n + 4), $n + 1);
            $objectPHPExcel->getActiveSheet()->setCellValue('B' . ($n + 4), $v['buyer']);
            $objectPHPExcel->getActiveSheet()->setCellValue('C' . ($n + 4), $v['sku']);
            $objectPHPExcel->getActiveSheet()->setCellValue('D' . ($n + 4), isset($v['product_status'])?SupplierGoodsServices::getProductStatus($v['product_status']):"未知");

            $sourceStatus = SupplierGoodsServices::getProductSourceStatus();
            $res = ProductSourceStatus::find()
                   ->select('sourcing_status')
                   ->where(['pur_product_source_status.status'=>1,'sku'=>$v['sku']])
                   ->asArray()
                   ->scalar();
     
            $source_status = !empty($res)&& isset($sourceStatus[$res]) ? $sourceStatus[$res] :'正常';
            
            $objectPHPExcel->getActiveSheet()->setCellValue('E' . ($n + 4), $source_status);
            $objectPHPExcel->getActiveSheet()->setCellValue('F' . ($n + 4), $v['name']);
            $objectPHPExcel->getActiveSheet()->setCellValue('G' . ($n + 4), $v['warehouse_name']);

            $pur_price=\app\models\PurchaseOrderItems::findOne(['sku'=>$v['sku']])['price'];
            $pro_price=\app\models\Product::findOne(['sku'=>$v['sku']])['product_cost'];
            if(!empty($pur_price)){//优先显示最近一次采购价
                $price=$pur_price;
            }elseif(!empty($pro_price)){
                $price=$pro_price;
            }else{
                $price=$v['price'];
            }

            $objectPHPExcel->getActiveSheet()->setCellValue('H' . ($n + 4), $price);
            $objectPHPExcel->getActiveSheet()->setCellValue('I' . ($n + 4), $v['sales_avg']);
            $objectPHPExcel->getActiveSheet()->setCellValue('J' . ($n + 4), $v['available_stock']);
            $objectPHPExcel->getActiveSheet()->setCellValue('K' . ($n + 4), $v['on_way_stock']);

            $objectPHPExcel->getActiveSheet()->setCellValue('L' . ($n + 4),  $v['available_stock']+$v['on_way_stock']+$v['left_stock']);
            $objectPHPExcel->getActiveSheet()->setCellValue('M' . ($n + 4), $v['qty']);
            $objectPHPExcel->getActiveSheet()->setCellValue('N' . ($n + 4), $v['created_at']);
            $objectPHPExcel->getActiveSheet()->setCellValue('O' . ($n + 4), isset($v['state'])?(PurchaseOrderServices::getProcesStatus()[$v['state']]):'');

            $undisposedReason = PurchaseSuggestNote::find()
                ->select("creator,suggest_note,create_time")
                ->where(['sku'=>$v['sku'], 'warehouse_code'=>$v['warehouse_code']])
                ->andWhere(['status'=>1])
                ->asArray()
                ->one();

            $objectPHPExcel->getActiveSheet()->setCellValue('P' . ($n + 4),$undisposedReason['suggest_note']."\r\n".$undisposedReason['create_time']."\r\n".$undisposedReason['creator'] );

            $n = $n + 1;
        }

        for ($i = 65; $i<81; $i++) {
            $objectPHPExcel->getActiveSheet()->getColumnDimension(chr($i))->setWidth(15);
            $objectPHPExcel->getActiveSheet()->getStyle( chr($i) . "3")->getFont()->setBold(true);
        }
        $objectPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
        $objectPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(20);

        //设置样式
        $objectPHPExcel->getActiveSheet()->getStyle('B2:H'.($n+4))->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        // $objectPHPExcel->getActiveSheet()->getColumnDimension('P')->setAutoSize(true); //设置列与数据的宽相等
        ob_end_clean();
        ob_start();
        header("Content-type:application/vnd.ms-excel;charset=UTF-8");
        header('Content-Type : application/vnd.ms-excel');
        header('Content-Disposition:attachment;filename="'.'采购单计划表-'.date("Y年m月j日").'.xls"');
        $objWriter= \PHPExcel_IOFactory::createWriter($objectPHPExcel,'Excel5');
        $objWriter->save('php://output');
        die;
    }
    /**
     * 点击采购数量，查看采购数量的组成
     * @return string
     */
    public function actionQtyView()
    {
        $sku = Yii::$app->request->get('sku');
        $qty = Yii::$app->request->get('qty');
        $model= PurchaseSuggestQuantity::find()->where(['sku'=> $sku])->all();
        $model_suggest= PurchaseSuggestHistoryMrp::find()->where(['sku'=> $sku])->one();
        return $this->renderAjax('qty-view', [
            'model' => $model,
            'model_suggest' => $model_suggest,
            'qty' => $qty,
        ]);
    }

    public function actionGetHistoryNote(){
        $sku = Yii::$app->request->getQueryParam('sku');
        $warehouse_code = Yii::$app->request->getQueryParam('warehouse_code');
        $suggest_create_time = Yii::$app->request->getQueryParam('create_time');
        $noteDatas = PurchaseSuggestNote::find()
            ->select('sku,warehouse_code,suggest_note,create_time,update_time,creator,update_user_name')
            ->where(['sku'=>$sku,'warehouse_code'=>$warehouse_code])
            ->andWhere(['or',
                ['and',['<=','create_time',$suggest_create_time],
                ['or',
                ['>=','update_time',$suggest_create_time],['update_time'=>null]]],
                ['and',['>=','create_time',$suggest_create_time],['<=','create_time',date('Y-m-d 23:59:59',strtotime($suggest_create_time))]]])
            ->asArray()->all();
        if(empty($noteDatas)){
            Yii::$app->end('<div style="text-align: center">没有历史备注信息</div>');
        }
        return $this->renderAjax('history-note',['noteDatas'=>$noteDatas]);
    }
}
