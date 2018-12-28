<?php

namespace app\controllers;

use app\config\Vhelper;
use app\models\ProductDescription;
use app\models\ProductDescriptionCopy;
use app\models\ProductImgDownload;
use app\models\PurchaseCategoryBind;
use app\models\SkuSalesStatisticsSearch;
use app\services\BaseServices;
use Yii;
use app\models\Product;
use app\models\ProductSearch;
use app\models\PurchaseCategoryBindSearch;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\ProductProvider;
use app\api\v1\models\Supplier;
use app\models\SupplierQuotes;

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
 * Date: 2017/9/30 0030
 * Description: ProductController.php      
*/
class ProductController extends BaseController
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
     * Lists all Product models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ProductSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    /**
     * 产品分类与采购员绑定关系
     * Lists all Product models.
     * @return mixed
     */
    public function actionIndexCate()
    {
        $searchModel = new PurchaseCategoryBindSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index_cate', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Updates an existing OverseasWarehouseGoodsTaxRebate model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate1($id)
    {
        $model = PurchaseCategoryBind::find()->where(['id'=>$id])->one();

        if(!empty(Yii::$app->request->post())){
            $model->cate_name  = BaseServices::getCategory(Yii::$app->request->post()['PurchaseCategoryBind']['category_id']);
            $model->buyer_name = BaseServices::getEveryOne(Yii::$app->request->post()['PurchaseCategoryBind']['buyer']);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index-cate']);
        } else {
            return $this->render('create-purchase-bind', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing OverseasWarehouseGoodsTaxRebate model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete1($id)
    {
        PurchaseCategoryBind::find()->where(['id'=>$id])->one()->delete();

        return $this->redirect(['product/index-cate']);
    }

    /**
     * Displays a single BulletinBoard model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }
    /**
     * Updates an existing BulletinBoard model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Product();
        $model_desc = new ProductDescription();


        if (Yii::$app->request->isPost)
        {
            $transaction=\Yii::$app->db->beginTransaction();
            try {
                $model->sku                 = Yii::$app->request->post()['Product']['sku'];
                $model->product_category_id = Yii::$app->request->post()['Product']['product_category_id'];
                $model->product_status      = Yii::$app->request->post()['Product']['product_status'];
                $model->product_cost          = Yii::$app->request->post()['Product']['product_cost'];
                $model->product_cn_link     = Yii::$app->request->post()['Product']['product_cn_link'];
                $model->product_en_link     = Yii::$app->request->post()['Product']['product_en_link'];
                $model->create_id           = Yii::$app->request->post()['Product']['create_id'];
                $model->create_time         = date('Y-m-d H:i:s');
                $model->product_type        = Yii::$app->request->post()['Product']['product_type'];
                $model->product_linelist_id = Yii::$app->request->post()['Product']['product_linelist_id'];
                $model->last_price          = Yii::$app->request->post()['Product']['last_price'];
                $model->product_is_multi    = Yii::$app->request->post()['Product']['product_is_multi'];
                $model->note                = Yii::$app->request->post()['Product']['note'];
                $status                     = $model->save(false);

               if($status)
               {
                   $model_desc->sku            = Yii::$app->request->post()['Product']['sku'];
                   $model_desc->title          = Yii::$app->request->post()['ProductDescription']['title'];
                   $model_desc->language_code  = 'Chinese';
                   $model_desc->create_user_id = Yii::$app->user->id;
                   $model_desc->create_time    = date('Y-m-d H:i:s');
                   if($model_desc->save(false))
                   {
                       Yii::$app->getSession()->setFlash('success','添加成功',true);
                   }

               } else{

                   Yii::$app->getSession()->setFlash('error','添加失败',true);
               }
                $transaction->commit();
                return $this->redirect(['index']);
            } catch (HttpException $e) {
               $transaction->rollBack();
                Yii::$app->getSession()->setFlash('error',$e->getMessage(),true);
                return $this->redirect(Yii::$app->request->referrer);

            }
        } else {
            return $this->render('create', [
                'model' => $model,
                'model_desc' => $model_desc,
            ]);
        }
    }
    /**
     * Updates an existing BulletinBoard model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id,$sku)
    {
        $model = $this->findModel($id);
        $model_desc = ProductDescription::find()->where(['sku'=>$sku])->one();
        if (Yii::$app->request->isPost)
        {
            $transaction=\Yii::$app->db->beginTransaction();
            try {
                $model->sku                 = Yii::$app->request->post()['Product']['sku'];
                $model->product_category_id = Yii::$app->request->post()['Product']['product_category_id'];
                $model->product_status      = Yii::$app->request->post()['Product']['product_status'];
                $model->product_cost          = Yii::$app->request->post()['Product']['product_cost'];
                $model->product_cn_link     = Yii::$app->request->post()['Product']['product_cn_link'];
                $model->product_en_link     = Yii::$app->request->post()['Product']['product_en_link'];
                $model->create_id           = Yii::$app->request->post()['Product']['create_id'];
                $model->create_time         = date('Y-m-d H:i:s');
                $model->product_type        = Yii::$app->request->post()['Product']['product_type'];
                $model->product_linelist_id = Yii::$app->request->post()['Product']['product_linelist_id'];
                $model->last_price          = Yii::$app->request->post()['Product']['last_price'];
                $model->product_is_multi    = Yii::$app->request->post()['Product']['product_is_multi'];
                $model->note                = Yii::$app->request->post()['Product']['note'];
                $status                     = $model->save(false);

                if($status)
                {
                    $model_desc->sku            = Yii::$app->request->post()['Product']['sku'];
                    $model_desc->title          = Yii::$app->request->post()['ProductDescription']['title'];
                    $model_desc->language_code  = 'Chinese';
                    $model_desc->create_user_id = Yii::$app->user->id;
                    $model_desc->create_time    = date('Y-m-d H:i:s');
                    if($model_desc->save(false))
                    {
                        Yii::$app->getSession()->setFlash('success','更新成功',true);
                    }

                } else{

                    Yii::$app->getSession()->setFlash('error','更新失败',true);
                }
                $transaction->commit();
                return $this->redirect(['index']);
            } catch (HttpException $e) {
                $transaction->rollBack();
                Yii::$app->getSession()->setFlash('error',$e->getMessage(),true);
                return $this->redirect(Yii::$app->request->referrer);

            }
        } else {
            return $this->render('update', [
                'model' => $model,
                'model_desc' =>$model_desc

            ]);
        }
    }

    /**
     * Finds the Product model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Product the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Product::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * 查看销量统计
     * @return string
     */
    public function actionViewskusales()
    {
        $map=[];
        $map['sku']=Yii::$app->request->get('sku');
        $map['warehouse_code']=Yii::$app->request->get('warehouse_code');
        //$map['warehouse_code']=Yii::$app->request->get('warehouse_code');
        $searchModel = new SkuSalesStatisticsSearch();
        $dataProvider = $searchModel->search2(['SkuSalesStatisticsSearch'=>$map]);
        return $this->renderAjax('viewskusales', ['searchModel' => $searchModel,'dataProvider'=>$dataProvider]);
    }

    /**
     * 根据产品sku获取产品类别和产品名
     */
    public  function  actionGetName()
    {
        $sku = Yii::$app->request->get('sku');
        if($sku)
        {
            $model = Product::find()->joinWith(['desc','cat'])->where(['pur_product.sku'=>trim($sku)])->asArray()->one();
            $data['cid'] = $model['product_category_id'];
            $data['name'] = !empty($model['cat']['category_cn_name']) ? $model['cat']['category_cn_name'] : '没有这个类别哦';
            $data['title'] = !empty($model['desc']['title']) ? $model['desc']['title'] : '没有这个产品名';
            if (Yii::$app->request->get('tax')) {
                $data['is_back_tax'] = 0;
                $supplier_model = ProductProvider::find()->where(['sku'=>$sku,'is_supplier'=>1])->one();
                if ($supplier_model) {
                    $data['is_back_tax'] = SupplierQuotes::find()->select('is_back_tax')->where(['id'=>$supplier_model->quotes_id])->scalar();
                    $data['payment_method'] = Supplier::find()->select('payment_method')->where(['supplier_code'=>$supplier_model->supplier_code])->scalar();
                }
            }
            echo Json::encode($data);
        }
    }

    /**
     * 分类与采购员的绑定关系
     */
    public function actionCreatePurchaseBind()
    {
        $model = new PurchaseCategoryBind();

        if (Yii::$app->request->isPost)
        {
            $model->cate_name  = BaseServices::getCategory(Yii::$app->request->post()['PurchaseCategoryBind']['category_id']);
            $model->buyer_name = BaseServices::getEveryOne(Yii::$app->request->post()['PurchaseCategoryBind']['buyer']);
            $model->bind_time = date('Y-m-d H:i:s');
            $model->bind_name = Yii::$app->user->identity->username;
            $model->load(Yii::$app->request->post()) && $model->save();
            Yii::$app->getSession()->setFlash('success','恭喜你！添加成功');
            return $this->redirect(['create-purchase-bind']);
        } else {
            return $this->render('create-purchase-bind', [
                'model' => $model,
            ]);
        }
    }

    public  function  actionDesc()
    {
        ini_set('memory_limit','5000M');
        $model = ProductDescription::find()->where(['is_p'=>0])->orderBy('create_time desc')->asArray()->limit(2000)->all();
        foreach($model as $k=>$s)
        {
            $mode = ProductDescriptionCopy::find()->where(['sku'=>$s['sku']])->one();
            if($mode)
            {
                $mdo = ProductDescription::findOne($s['id']);
                if($mdo)
                {
                    $mdo->is_p=1;
                    $mdo->save(false);
                }
                continue;

            } else{
                $model                 = new  ProductDescriptionCopy();
                $model->sku            = $s['sku'];
                $model->language_code  = $s['language_code'];
                $model->title          = $s['title'];
                $model->create_user_id = $s['create_user_id'];
                $model->create_time    = $s['create_time'];
                $model->save(false);
                $mdo = ProductDescription::findOne($s['id']);
                if($mdo)
                {
                    $mdo->is_p=1;
                    $mdo->save(false);
                }
            }


        }
    }

    public function actionReload(){
        if(Yii::$app->request->isGet){
            $sku = Yii::$app->request->getQueryParam('sku');
            $model = ProductImgDownload::find()->andFilterWhere(['sku'=>$sku,'status'=>1])->one();
            if(!empty($model)){
                $model->status = 2;
                $model->save(false);
            }
            if(Yii::$app->cache->get($sku.'_cache1')){
                Yii::$app->cache->delete($sku.'_cache1');
            }
            if(Yii::$app->cache->get($sku.'_cache2')){
                Yii::$app->cache->delete($sku.'_cache2');
            }
            if(Yii::$app->cache->get($sku.'_imagecache')){
                Yii::$app->cache->delete($sku.'_imagecache');
            }
            Yii::$app->getSession()->setFlash('success','产品图片下载状态已变更');
            return $this->redirect(Yii::$app->request->referrer);
        }
    }

    /*
     * 获取sku库存销量
     */
    public function actionGetStockSales(){
        $sku = Yii::$app->request->getQueryParam('sku','');
        $warehouse_code = Yii::$app->request->getQueryParam('warehouse_code','');
        $platform_code = Yii::$app->request->getQueryParam('platform_code','');
        $data = Product::productStockSalesDatas($sku,$warehouse_code,$platform_code);
        if(Yii::$app->request->isAjax){
            return $this->renderAjax('stock-sales',['datas'=>$data]);
        }else{
            return $this->render('stock-sales',['datas'=>$data]);

        }
    }


    /**
     * 导入 重点SKU标记 数据【各平台旺季重点产品20180816(1).刘楚雯(1)】
     * 此方法一次有效
     */
    public function actionPeakSeasonSkuImport(){
        header('Content-type:text/html;charset=utf-8');
        set_time_limit(0);
        ini_set('memory_limit','512M');

        $filePath = Yii::$app->basePath.'/web/files/product-repackage/AllPlatformPeakSeason20180816(1).LiuChuWen.xlsx';

        $PHPReader      = new \PHPExcel_Reader_Excel2007();
        $PHPReader      = $PHPReader->load($filePath);
        $currentSheet   = $PHPReader->getSheet(0);
        $sheetData      = $currentSheet->toArray(null,true,true,true);

        $time           = date('Y-m-d H:i:s');
        $success        = 0;
        $error_list     = [];
        if($sheetData){
            foreach($sheetData as $value){

                $sku = trim($value['A']);
                if(empty($sku)) continue;
                $model = Product::findOne(['sku' => $sku]);

                if(empty($model)){
                    continue;
                }else{
                    $model->is_weightdot = 1;
                    $res = $model->save(false);
                    if($res){
                        $success ++;
                    }else{
                        $error_list[] = $sku;
                    }
                }
            }
            
            echo '<pre>';
            echo 'Product 更新成功：'.($success).'<br/><br/>';

            print_r($error_list);

            $connection     = Yii::$app->db;

            // 重点SKU
            $sql_insert     = "UPDATE pur_cache_product_mark_data SET is_weightdot=0,is_sync=0,update_time='$time'
                            WHERE is_weightdot=1 AND sku NOT IN(SELECT sku FROM pur_product WHERE is_weightdot=1)";
            $command        = $connection->createCommand($sql_insert);
            $res            = $command->execute();

            $sql_insert     = "UPDATE pur_cache_product_mark_data SET is_weightdot=1,is_sync=0,update_time='$time'
                            WHERE is_weightdot=0 AND sku IN(SELECT sku FROM pur_product WHERE is_weightdot=1)";
            $command        = $connection->createCommand($sql_insert);
            $res            = $command->execute();

            echo '<br/><br/>Product_Mark 更新成功：'.($res);
            echo '<br/><br/>Success';
            exit;
        }

    }
}
