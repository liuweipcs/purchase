<?php

namespace app\controllers;

/**
 *                             _ooOoo_
 *                            o8888888o
 *                            88" . "88
 *                            (| -_- |)
 *                            O\  =  /O
 *                         ____/`---'\____
 *                       .'  \\|     |//  `.
 *                      /  \\|||  :  |||//  \
 *                     /  _||||| -:- |||||-  \
 *                     |   | \\\  -  /// |   |
 *                     | \_|  ''\---/''  |   |
 *                     \  .-\__  `-`  ___/-. /
 *                   ___`. .'  /--.--\  `. . __
 *                ."" '<  `.___\_<|>_/___.'  >'"".
 *               | | :  `- \`.;`\ _ /`;.`/ - ` : | |
 *               \  \ `-.   \_ __\ /__ _/   .-` /  /
 *          ======`-.____`-.___\_____/___.-`____.-'======
 *                             `=---='
 *          ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
 *                     高山仰止,景行行止.虽不能至,心向往之。
 * User: ztt
 * Date: 2017/11/10 0023
 * Description: OverseasPurchaseDemandController.php      
*/
use app\api\v1\models\PurchaseOrderPay;
use app\config\Vhelper;
use app\models\BoxSkuQty;
use app\models\DynamicTable;
use app\models\PlatformSummarySearch;
use app\models\Product;
use app\models\PurchaseDemand;
use app\models\PurchaseDemandBak;
use app\models\PurchaseDemandCopy;
use app\models\PurchaseHistory;
use app\models\PurchaseLog;
use app\models\PurchaseNote;
use app\models\PurchaseOrder;
use app\models\PurchaseOrderItems;
use app\models\PurchaseOrderTaxes;
use app\models\PurchaseSuggest;
use app\models\PurchaseTemporary;
use app\models\SkuSalesStatistics;
use app\models\Stock;
use app\models\Supplier;
use app\models\SupplierBuyer;
use app\models\SupplierQuotes;
use app\models\User;
use app\models\Warehouse;
use app\services\BaseServices;
use app\services\CommonServices;
use Yii;
use yii\helpers\FileHelper;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\PlatformSummary;
use app\models\PurchaseOrderShip;
use m35\thecsv\theCsv;
use app\services\PlatformSummaryServices;
use app\services\PurchaseOrderServices;
use app\services\SupplierServices;
use app\services\SupplierGoodsServices;

class PurchaseDemandController extends BaseController
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
                    'delete' => ['GET'],
                ],
            ],
        ];
    }

    /**
     * Lists all PurchaseAbnomal models.
     * @return mixed
     */
    public function actionIndex()
    {

        $searchModel  = new PlatformSummarySearch();
        $dataProvider = $searchModel->search8(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
    }

    /**
     * Displays a single PurchaseAbnomal model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * 创建需求
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new PlatformSummary();

        if (Yii::$app->request->isPost) {
            $model->demand_number =CommonServices::getNumber('RD');
            $model->purchase_type =1;
            $model->load(Yii::$app->request->post()) && $model->save();

            Yii::$app->getSession()->setFlash('success',"恭喜你添加成功！",true);
            return $this->redirect(['index']);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            $model->level_audit_status=0;
            $model->audit_note='';
            $model->save();
            return $this->redirect(['index']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * @param $id
     * @return \yii\web\Response
     */
    public function actionDelete($id)
    {
        $model = PlatformSummary::findOne($id);
        $model->level_audit_status =5;
        Yii::$app->getSession()->setFlash('success',"恭喜您操作成功！",true);
        $model->save(false);
        //$this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * @param $id
     * @return static
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = PlatformSummary::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    /**
     * 采购驳回
     */
    public function actionPurchaseDisagree($id)
    {
        $model = $this->findModel($id);
        if(Yii::$app->request->isPost)
        {
            $model->level_audit_status = 4;
            $model->buyer              = Yii::$app->user->identity->username;
            $model->is_purchase        = 1;
            $model->purchase_note      = Yii::$app->request->post()['PlatformSummary']['purchase_note'];
            $model->purchase_time      = date('Y-m-d H:i:s', time());
            $model->save();
            Yii::$app->getSession()->setFlash('success',"恭喜您操作成功！",true);
            return $this->redirect(['index','page'=>Yii::$app->request->post()['PlatformSummary']['page']]);

        } else{
            $page=Yii::$app->request->get('page');
            return $this->renderAjax('pnote',['model' =>$model,'page'=>$page]);
        }
    }
    /**
     * 审核-批量同意
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public  function actionAgree()
    {
        $ids=Yii::$app->request->post('ids');
        $id=Yii::$app->request->get('id');
        if(!empty($id) || !empty($ids)){
            if(!empty($ids)){//批量
                foreach($ids as $v){
                    $model = $this->findModel($v);
                    $arr = ['0','3'];
                    if(in_array($model->level_audit_status,$arr)){
                        $model->level_audit_status=1;
                    }
                    $model->agree_user=Yii::$app->user->identity->username;
                    $model->agree_time=date('Y-m-d H:i:s',time());
                    $model->save(false);
                }

                Yii::$app->getSession()->setFlash('success',"恭喜您,批量操作成功！",true);
            }else{
                $model = $this->findModel($id);
                $arr = ['0','3'];
                if(in_array($model->level_audit_status,$arr)){
                    $model->level_audit_status=1;
                }
                $model->agree_user=Yii::$app->user->identity->username;
                $model->agree_time=date('Y-m-d H:i:s',time());
                $model->save(false);
                Yii::$app->getSession()->setFlash('success',"恭喜您操作成功！",true);
            }
        }else{
            Yii::$app->getSession()->setFlash('error',"没有数据ID,操作失败！",true);
        }

        $page = isset($_REQUEST['page'])?$_REQUEST['page']:1;

        return $this->redirect(['index','page'=>$page]);
    }

    /**
     * 清除产品
     * @return \yii\web\Response
     */
    public function actionEliminate()
    {
        $data =[
          'user_id'=>Yii::$app->user->id,
        ];
        $rc =DynamicTable::Deletess($data);
        if(!$rc)
        {
            Yii::$app->getSession()->setFlash('error',"清除失败");
        }
        PurchaseTemporary::deleteAll(['create_id'=>Yii::$app->user->id]);
        Yii::$app->getSession()->setFlash('success',"清除成功");

        return $this->redirect(['purchase-demand/create-purchase-order']);

        //return $this->redirect(['addproduct']);
    }
    /**
     * 审核-批量驳回
     */
    public  function actionDisagree()
    {
        $post=Yii::$app->request->post('PlatformSummary');

        if(!empty($post)){
            $ids=explode(',',$post['id']);
            if(count($ids)>1){//批量
                foreach($ids as $v){
                    $post_model = $this->findModel($v);
                    if($post_model->level_audit_status==0){
                        $post_model->level_audit_status=2;
                    }
                    $post_model->agree_user=Yii::$app->user->identity->username;
                    $post_model->audit_note=$post['audit_note'];
                    $post_model->agree_time=date('Y-m-d H:i:s',time());
                    $post_model->save();
                }

                Yii::$app->getSession()->setFlash('success',"恭喜您,批量操作成功！",true);

                return $this->redirect(['index','page'=>$post['page']]);
            }else{
                $model = $this->findModel($post['id']);
                if($model->level_audit_status==0){
                    $model->level_audit_status=2;
                }
                $model->agree_user=Yii::$app->user->identity->username;
                $model->audit_note=$post['audit_note'];
                $model->agree_time=date('Y-m-d H:i:s',time());
                $model->save();
                Yii::$app->getSession()->setFlash('success',"恭喜您操作成功！",true);

                return $this->redirect(['index','page'=>$post['page']]);
            }
        }else{
            $id=$_REQUEST['id'];
            $page  = $_REQUEST['page'];
            if($id){
                $model=new PlatformSummary();
                if(is_array($id)){
                    $id=implode(',',$id);
                }
                return $this->renderAjax('note',['id' =>$id,'model'=>$model,'page'=>$page]);
            }
        }
        return false;

    }
    /**
     * 模板查看
     */
    public function actionTemplate()
    {

        $filename = Yii::$app->request->hostInfo . "/images/purchase.csv";//模板放的位置
        $file_name = "purchase.csv";
        $contents = file_get_contents($filename);
        // $file_size = filesize($filename);
        header("Content-type: application/octet-stream;charset=utf-8");
        header("Accept-Ranges: bytes");
        //header("Accept-Length: $file_size");
        header("Content-Disposition: attachment; filename=".$file_name);
        exit($contents);


    }

    /**
     * 撤销需求
     * @return void|\yii\web\Response
     */
    public function  actionRevokeDemand()
    {
        $ids    = Yii::$app->request->get('ids');

        if (!$ids) return;
        $map['id']=strpos($ids,',') ? explode(',',$ids):$ids;
        $map['level_audit_status'] = ['0','4','2']; //待同意，采购驳回，驳回
        $map['is_purchase']        = 1; //已采购
        $orders=PlatformSummary::find()->select('id,level_audit_status')->where($map)->all();

        if(!empty($orders))
        {
            foreach ($orders as $v)
            {
                $v->level_audit_status=3; //撤销
                $result =$v->save(false);
            }
            if($result)
            {
                Yii::$app->getSession()->setFlash('success','恭喜你,撤销确认成功',true);
            }
        } else {
            Yii::$app->getSession()->setFlash('error','对不起少年,已经同意或者已是采购的不能再撤销了！');
        }
        return $this->redirect(['index']);

    }

    /**
     * 提交-创建采购单计划
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionCreatePurchaseOrder()
    {
        $ordermodel   = new PurchaseOrder();
        $purchasenote = new PurchaseNote();

        if(!empty($_POST['PurchaseOrder']))
        {
            $data = [
                'user_id' => Yii::$app->user->id,
            ];
            DynamicTable::Deletess($data);
            $purdesc=$_POST['PurchaseOrder'];
            //生成采购订单主表、详情表数据
            $orderdata['purdesc']=$purdesc;

            $orderdata['purdesc']['supplier_code']     = preg_match("/[\x7f-\xff]/", $purdesc['supplier_code'])?SupplierQuotes::getFiled($purdesc['items'][0]['sku'],'suppliercode')->suppliercode:$purdesc['supplier_code'];
            //Vhelper::dump($orderdata);
            $transaction=\Yii::$app->db->beginTransaction();
            try {
                $pur_number   = $ordermodel::Savepurdata($orderdata);
                //加入备注
                $PurchaseNote =[
                    'pur_number'=>$pur_number,
                    'note'      =>$_POST['PurchaseNote']['note'],
                ];
                $purchasenote->saveNote($PurchaseNote);
                $demand_array =[];
                foreach($purdesc['items'] as $k=>$v)
                {
                    $demand_array [] = $v['demand_number'];

                }
                PurchaseDemand::saveOne($pur_number,$purdesc['items']);
                $ordermodel::OrderItems($pur_number,$purdesc['items'],2);
                PlatformSummary::Updates($demand_array);
                $transaction->commit();

                Yii::$app->getSession()->setFlash('success', '恭喜你,手动创建采购单成功', true);
                return $this->redirect(['index']);
            }catch (Exception $e) {
                $transaction->rollBack();
                Yii::$app->getSession()->setFlash('error','数据异常！保存失败,请联系管理员');
                return $this->redirect(['index']);
            }
        }
        $temporay= PurchaseTemporary::find()->where(['create_id'=>Yii::$app->user->id])->all();
        return $this->render('addproduct', [

            'purchasenote' =>$purchasenote,
            'ordermodel'=>$ordermodel,
            'temporay'=>$temporay,
        ]);

       /* $ids                       = Yii::$app->request->get('ids');
        $map['id']                 = strpos($ids, ',') ? explode(',', $ids) : $ids;
        $map['level_audit_status'] = 1;
        $map['is_purchase']        = 1;
        $map['purchase_type']      = 2;
        $orders                    = PlatformSummary::find()->where($map)->asArray()->all();

        if(empty($orders))
        {
            Yii::$app->getSession()->setFlash('error','对不起少年,没有经过同意或者已是采购过了！');
            return $this->redirect(['index']);
        } else {
            $pur         = [];
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                foreach ($orders as $k => $v) {
                    $orderdata['purdesc']['warehouse_code']    = $v['purchase_warehouse'];
                    $orderdata['purdesc']['is_transit']        = $v['is_transit'] == 1 ? 0 : 1;
                    $orderdata['purdesc']['transit_warehouse'] = $v['transit_warehouse'];
                    $orderdata['purdesc']['supplier_code']     = BaseServices::getSupplierCode(PurchaseHistory::getField($v['sku'], 'supplier_name'), 'supplier_code');
                    $orderdata['purdesc']['is_expedited']      = 1;
                    $orderdata['purdesc']['purchase_type']     = $v['purchase_type'];
                    $pur['items'][$k]['sku']                   = $v['sku'];
                    $pur['items'][$k]['title']                 = $v['product_name'];
                    $pur['items'][$k]['purchase_quantity']     = PlatformSummary::getSku($v['sku'], $v['purchase_warehouse'], $v['transit_warehouse'], '*', 3);
                    $pur_number                                = PurchaseOrder::Savepurdata($orderdata);
                    //需求单号和采购单号关联
                    $dats = [
                        'pur_number'    => $pur_number,
                        'demand_number' => $v['demand_number'],
                        'create_id'     => $v['create_id'],
                        'create_time'   => $v['create_time'],
                    ];
                    PurchaseDemand::saveOne($dats);
                    PurchaseOrder::OrderItems($pur_number, $pur['items'], 2);
                    $transaction->commit();
                    Yii::$app->getSession()->setFlash('success', '恭喜你,手动创建采购单成功,请到采购管理下面的采购计划去查看吧！', true);
                    return $this->redirect(['index']);
                }
            } catch (Exception $e) {
                $transaction->rollBack();
            }
        }*/


    }
    /**
     * 导入cvs
     * @return string|\yii\web\Response
     * @throws \yii\db\Exception
     */
    public function actionImportProduct()
    {

        $model = new PurchaseTemporary();
        if (Yii::$app->request->isPost)
        {
            $model->file_execl = UploadedFile::getInstance($model, 'file_execl');

            $data              = $model->upload();

            if(empty($data))
            {
                Yii::$app->getSession()->setFlash('error',"文件上传失败",true);
                return $this->redirect(['index']);
            }
            $file        = fopen($data, 'r');
            $line_number = 0;
            while ($datas = fgetcsv($file))
            {
                if ($line_number == 0)
                { //跳过表头
                    $line_number++;
                    continue;
                }
                $num = count($datas);
                for ($c = 0; $c < $num; $c++)
                {

                    $Name[$line_number][] = mb_convert_encoding(trim($datas[$c]),'utf-8','gbk');

                }
                $Name[$line_number][] = Yii::$app->user->identity->id;
                $line_number++;
            }
            $statu = Yii::$app->db->createCommand()->batchInsert(PurchaseTemporary::tableName(),['sku','purchase_quantity', 'purchase_price','title','create_id'], $Name)->execute();
            fclose($file);
            if ($statu)
            {
                Yii::$app->getSession()->setFlash('success',"恭喜你，导入成功！",true);
                return $this->redirect(['addproduct']);
            } else {
                Yii::$app->getSession()->setFlash('error','恭喜你，导入失败了！请联系管理员',true);
                return $this->redirect(['addproduct']);
            }

        } else {
            return $this->renderAjax('addfile', ['model' => $model]);
        }
    }
    /**
     * 添加产品
     * @return string
     */
    public function actionProductIndex()
    {
        $searchModel  = new PlatformSummarySearch();
        $dataProvider = $searchModel->search7(Yii::$app->request->queryParams);
        return $this->renderAjax('_orderform',[
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 确认添加
     * @return string
     */
    public function actionAddTemporary()
    {
        $id = Yii::$app->request->post()['id'];
        $id = strpos($id,',')?explode(',',$id):$id;

        if (!is_string($id))
        {
            foreach($id as $v)
            {
                if(!empty($v))
                {
                    $data =[
                        'user_id'=>  Yii::$app->user->id,
                        'demand_number_id'=>$v,
                    ];
                    $rc = DynamicTable::Check($data);
                    if($rc)
                    {
                        return json_encode(['code'=>0,'msg'=>'其他采购人员正在操作此选中的部分需求单,添加失败']);
                    }
                    $rs = DynamicTable::Add($data);
                    if(!$rs)
                    {
                        return json_encode(['code'=>0,'msg'=>'采购需求单动态表部分添加失败,请刷新页面']);
                    }
                    $model            = new PurchaseTemporary;
                    $model->product_id       = $v;
                    $model->sku             = PlatformSummary::find()->select('sku')->where(['id'=>$v])->scalar();
                    $model->create_id = Yii::$app->user->id;
                    $status = $model->save(false);

                }

            }
        } else {
            $data =[
                'user_id'=>  Yii::$app->user->id,
                'demand_number_id'=>$id,
            ];
            $rc = DynamicTable::Check($data);
            if($rc)
            {
                return json_encode(['code'=>0,'msg'=>'其他采购人员正在操作此采购需求单,你现在无法操作']);
            }
            $rs = DynamicTable::Add($data);
            if(!$rs)
            {
                return json_encode(['code'=>0,'msg'=>'采购需求单动态表添加失败！']);
            }
            $model                   = new PurchaseTemporary;
            $model->product_id       = $id;
            $model->sku              = PlatformSummary::find()->select('sku')->where(['id'=>$id])->scalar();
            $model->create_id        = Yii::$app->user->id;
            $status                  = $model->save(false);
        }

        if($status){
            return json_encode(['code'=>1,'msg'=>'恭喜你,产品添加成功']);
        }else{
            return json_encode(['code'=>0,'msg'=>'哦喔！产品添加失败了']);
        }
    }

    /**
     * 采购需求导入
     * @return string|\yii\web\Response
     */
    public function actionPurchaseSumImport(){
        $model = new PlatformSummary();
        if (Yii::$app->request->isPost && $_FILES)
        {
            $extension=pathinfo($_FILES['PlatformSummary']['name']['file_execl'], PATHINFO_EXTENSION);

            $filessize=$_FILES['PlatformSummary']['size']['file_execl']/1024/1024;
            $filessize=round($filessize,2);

            if($filessize>10)
            {
                Yii::$app->getSession()->setFlash('warning',"文件大小不能超过 10M，当前大小： $filessize M",true);
                return $this->redirect(['index']);
            }


            if($extension!='csv')
            {
                Yii::$app->getSession()->setFlash('warning',"格式不正确,只接受 .csv 格式的文件",true);
                return $this->redirect(['index']);
            }
            $name= 'PlatformSummary[file_execl]';
            $data = Vhelper::upload($name);

            if(empty($data))
            {
                Yii::$app->getSession()->setFlash('error',"文件上传失败",true);
                return $this->redirect(['index']);
            }
            $file        = fopen($data, 'r');
            $line_number = 0;
            $s=0;
            while ($datas = fgetcsv($file)) {
                if ($line_number == 0) { //跳过表头
                    $line_number++;
                    continue;
                }

                $sku=Product::find()->where(['sku'=>$datas[0]])->asArray()->one()['sku'];
                if(!$sku){
                    $s++;
                    continue;
                }

                $num = count($datas);
                for ($c = 0; $c < $num; $c++) {
                    $Name[$line_number][] = mb_convert_encoding(trim($datas[$c]),'utf-8','gbk');
                }

                if(!empty($Name[$line_number][1]) && !is_numeric($Name[$line_number][1])){
                    $Name[$line_number][1] =strtoupper($Name[$line_number][1]);
                }

                if(!empty($Name[$line_number][3]) && !is_numeric($Name[$line_number][3])){
                    $purchase_warehouse = Warehouse::find()->select('id,warehouse_code,warehouse_name')->where(['use_status'=>1,'warehouse_name'=>$Name[$line_number][3]])->asArray()->one()['warehouse_code'];
                    if (empty($purchase_warehouse)) {
                        Yii::$app->getSession()->setFlash('error',$Name[$line_number][3] . ' -- 采购仓有误或为停运状态',true);
                        return $this->redirect(['index']);
                    }
                    $Name[$line_number][3] = $purchase_warehouse;
                }


                $pmodel = Product::find()->joinWith(['desc','cat'])->where(['pur_product.sku'=>$Name[$line_number][0]])->asArray()->one();

                $Name[$line_number][] = $pmodel['product_category_id'];
                $Name[$line_number][] = !empty($pmodel['desc']['title']) ? $pmodel['desc']['title'] : '';
                $Name[$line_number][] = 1; //采购类型
                $Name[$line_number][] = CommonServices::getNumber('RD');
                $Name[$line_number][] = Yii::$app->user->identity->username;
                $Name[$line_number][] = date('Y-m-d H:i:s',time());
                $line_number++;
            }

            //数据一次性入库
            $transaction=\Yii::$app->db->beginTransaction();
            try{
                $statu= Yii::$app->db->createCommand()->batchInsert(PlatformSummary::tableName(), ['sku', 'platform_number', 'purchase_quantity', 'purchase_warehouse','sales_note','product_category','product_name', 'purchase_type', 'demand_number', 'create_id', 'create_time',], $Name)->execute();

                $transaction->commit();
            }catch (Exception $e){
                $transaction->rollBack();
            }

            fclose($file);

            $dir=Yii::getAlias('@app') .'/web/files/' . date('Ymd');
            if (file_exists($dir)){
                FileHelper::removeDirectory($dir);
            }
            if ($statu) {
                if($s>0){
                    $f_sku ="SKU错误：$s 条";
                } else {
                    $f_sku ="";
                }

                Yii::$app->getSession()->setFlash('success',"恭喜你，导入成功！$f_sku",true);
                return $this->redirect(['index']);
            } else {
                Yii::$app->getSession()->setFlash('error','恭喜你，导入失败了！请联系管理员',true);
                return $this->redirect(['index']);
            }

        } else {
            return $this->renderAjax('purchase-sum-import', ['model' => $model]);
        }
    }

    /**
     * 根据需求生成采购建议
     */
    public function  actionPurchase()
    {
        $model = PlatformSummary::find()->joinWith('supplierQuotes')->where(['level_audit_status'=>[1],'is_purchase'=>1,'purchase_type'=>2])->asArray()->orderBy('id asc')->limit(500)->all();

        if($model)
        {

            foreach ($model as $v) {

                if (!empty($v['supplierQuotes']) && !empty($v['product_name'])) {

                    $suggest                      = new PurchaseSuggest();
                    $suggest->warehouse_code      = $v['purchase_warehouse'];
                    $suggest->warehouse_name      = BaseServices::getWarehouseCode($v['purchase_warehouse']) ? BaseServices::getWarehouseCode($v['purchase_warehouse']) : $v['purchase_warehouse'];
                    $suggest->sku                 = $v['sku'];
                    $suggest->name                = $v['product_name'];
                    $suppliercode =SupplierQuotes::getFileds($v['supplierQuotes']['quotes_id'],'suppliercode')->suppliercode;
                    if($suppliercode=='aaa' || empty($suppliercode))
                    {
                        continue;
                    }
                    $suggest->supplier_code       = $suppliercode;
                    $buyer=Supplier::find()->select('buyer')->where(['supplier_code'=>$suppliercode])->scalar();
                    $suggest->supplier_name       = BaseServices::getSupplierName($suppliercode);
                    $suggest->buyer               = !empty($buyer)?:BaseServices::getEveryOne($buyer)?:'admin';
                    $suggest->buyer_id            = !empty($buyer)?$buyer:'1';
                    $suggest->replenish_type      = 3;
                    $suggest->qty                 = $v['purchase_quantity'];
                    $suggest->price               = SupplierQuotes::getFileds($v['supplierQuotes']['quotes_id'],'supplierprice')->supplierprice;
                    $suggest->currency            = 'RMB';
                    $suggest->payment_method      = '1';
                    $suggest->is_purchase         = 'Y';
                    $suggest->supplier_settlement = '1';
                    $suggest->ship_method         = '2';
                    $suggest->safe_delivery       = '100';
                    $suggest->created_at          = $v['create_time'];
                    $suggest->creator             = $v['create_id'];
                    $suggest->product_category_id = $v['product_category'];
                    $suggest->category_cn_name    = BaseServices::getCategory($v['product_category']);
                    $suggest->type                = 'last_down';
                    $suggest->transit_code        = $v['transit_warehouse'];
                    $suggest->demand_number       = $v['demand_number'];
                    $suggest->purchase_type       = 4;
                    //如果保存成功的话
                    if($suggest->save(false))
                    {
                        $p              = PlatformSummary::find()->where(['demand_number' =>$v['demand_number']])->one();
                        if($p)
                        {
                            $p->is_purchase = 2;
                            $p->save(false);
                        }

                    } else{
                        continue;
                    }


                } else {
                    continue;
                }

            }

        }

    }

    public function  actionTest()
    {


        //以写入追加的方式打开
        /*$model = PurchaseSuggest::find()->asArray()->where(['not in','warehouse_code','SZ_AA'])->andWhere(['purchase_type'=>2])->limit(100)->all();
        //Vhelper::dump($model);
        $table = [
            'SKU',
            '总库存',
            '可用库存',
            '在途库存',
            '欠货库存',
            '平均销量',
            '7天销量',
            '30天销量',
            '90天销量',
            '欠货数量',
            '采购数量',
            'sku安全库存天数',
            '仓库安全库存天数',
            '7天加权系数',
            '30天加权系数',
            '90天加权系数',
            '所属仓库',
            '波动类型',
        ];
        $table_head = [];
        foreach($model as $k=>$v)
        {
                $table_head[$k][]=$v['sku'];
                $left =$v['left_stock']>0?0:abs($v['left_stock']);
                $table_head[$k][]=($v['available_stock'])-$left;
                $table_head[$k][]=$v['available_stock'];
                $table_head[$k][]=$v['on_way_stock'];
                $table_head[$k][]=$v['left_stock'];
                $table_head[$k][]=$v['sales_avg'];
                $table_head[$k][]=$v['days_sales_7'];
                $table_head[$k][]=$v['days_sales_30'];
                $table_head[$k][]=$v['days_sales_90'];
                $table_head[$k][]=$v['left_stock'];
                $table_head[$k][]=$v['qty'];
                $table_head[$k][]= '0';
                $table_head[$k][]= $v['safe_delivery'];
                $table_head[$k][]= $v['weighted_7'];
                $table_head[$k][]= $v['weighted_30'];
                $table_head[$k][]= $v['weighted_90'];
                $table_head[$k][]= $v['warehouse_name'];
                $table_head[$k][]= $v['type'];

        }
        //Vhelper::dump($table_head);
        theCsv::export([
            'header' =>$table,
            'data' => $table_head,
        ]);*/
        set_time_limit(0);
        $data ='./files/buyer.csv';
        $file        = fopen($data, 'r');

        $line_number = 0;
        while ($datas = fgetcsv($file)) {
            if ($line_number == 0) { //跳过表头
                $line_number++;
                continue;
            }
            $num = count($datas);
            for ($c = 0; $c < $num; $c++) {

                $Name[$line_number][] = mb_convert_encoding(trim($datas[$c]),'utf-8','gbk');

            }
            $line_number++;
        }

        //Vhelper::dump($Name);

        foreach($Name as $v)
        {
                $supplier_code = BaseServices::getSupplierCode($v['0'],'supplier_code');
                $sup           = Supplier::find()->where(['supplier_code'=>$supplier_code])->one();
                if($sup)
                {
                     /*$user = User::find()->select('id')->where(['username'=>$v['1']])->scalar();
                        $sup->buyer        =!empty($user)?$user:123;
                        $sup->merchandiser =!empty($user)?$user:123;
                        $statu= $sup->save(false);*/
                    $supplierBuyer = SupplierBuyer::find()->andFilterWhere(['supplier_code'=>$supplier_code,'type'=>1])->one();
                    if(empty($supplierBuyer)){
                        $supplierBuyer = new SupplierBuyer();
                    }
                    $supplierBuyer->type = 1;
                    $supplierBuyer->buyer= isset($v['1'])&&!empty($v['1']) ? $v['1'] : '王开伟';
                    $supplierBuyer->supplier_code = $supplier_code;
                    $supplierBuyer->supplier_name = $sup->supplier_name;
                    $supplierBuyer->status  =1;
                    $supplierBuyer->save(false);
                } else{

                continue;
                }


        }
        fclose($file);
        exit('导入成功');
        /*if ($statu) {

        } else {
            exit('导入失败');
        }*/
       /* $pur =PurchaseOrder::find()->limit(10)->asArray()->orderBy('id desc')->all();
        $pay =PurchaseOrderPay::find()->limit(10)->asArray()->all();
        Vhelper::dump($pur);*/

    }
}
