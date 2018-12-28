<?php
namespace app\controllers;

use app\config\Vhelper;
use app\models\ProductImgDownload;
use app\models\StockOwesSearch;
use app\models\Warehouse;
use app\models\SkuOutofstockStatisitics;
use app\services\SupplierGoodsServices;
use m35\thecsv\theCsv;
use Yii;
use yii\web\Controller;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/14
 * Time: 17:22
 */

class LackGoodsController extends Controller{
    public function actionIndex(){
        $searchModel = new StockOwesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        Yii::$app->session->set('lack-goods-search-excep',Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionExcep(){
        $searchModel = new StockOwesSearch();
        $dataProvider = $searchModel->search1(Yii::$app->request->queryParams);
        Yii::$app->session->set('lack-goods-search',Yii::$app->request->queryParams);
        return $this->render('excep', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionExport(){
        set_time_limit(0);
        ini_set('memory_limit', '1024M');
        $model = new StockOwesSearch();
        $query = $model->search1(Yii::$app->session->get('lack-goods-search'),false);
        $datas = $query->all();
        $table = ['产品名称','SKU','货源状态','产品状态','在途库存','可用库存','缺货数量','开发员','采购员'];
        $table_head = [];
        foreach ($datas as $c=>$v)
        {
            $table_head[$c][]=isset($v->productDesc->title) ? $v->productDesc->title : '';
            $table_head[$c][]=$v->sku;
            $productStatusModel=\app\models\ProductSourceStatus::find()->select('sourcing_status')->where(['sku'=>$v->sku,'status'=>1])->one();
            $sourceStatus='未知';
            if(!empty($productStatusModel)){
                switch ($productStatusModel['sourcing_status']){
                    case 1:
                        $sourceStatus='正常';
                        break;
                    case 2:
                        $sourceStatus='停产';
                        break;
                    case 3:
                        $sourceStatus='断货';
                        break;
                    default:
                        $sourceStatus='未知状态';
                        break;
                }
            }
            $table_head[$c][]=$sourceStatus;
            $table_head[$c][]=isset($v->product)? SupplierGoodsServices::getProductStatus($v->product->product_status) : '';
            $table_head[$c][]=isset($v->szStock->on_way_stock) ? $v->szStock->on_way_stock :0;
            $table_head[$c][]=isset($v->szStock->available_stock) ? $v->szStock->available_stock :0;
            $table_head[$c][]=$v->left_stock;
            $table_head[$c][]=isset($v->product->create_id) ? $v->product->create_id : '';
            $table_head[$c][]=isset($v->buyer->buyer ) ? $v->buyer->buyer :'';
        }
        theCsv::export([
            'header' =>$table,
            'data' => $table_head,
        ]);
    }

    public function actionExcepExport(){
        set_time_limit(0);
        ini_set('memory_limit', '1024M');
        $model = new StockOwesSearch();
        $query = $model->search(Yii::$app->session->get('lack-goods-search-excep'),false);
        $datas = $query->all();
        $table = ['SKU','品名','产品状态','仓库','缺货数量','缺货开始时间','缺货时间'];
        $table_head = [];
        foreach ($datas as $c=>$v)
        {
            $table_head[$c][]=$v->sku;
            $table_head[$c][]=!empty($v->productDesc) ? $v->productDesc->title : '';
            $table_head[$c][]=!empty($v->product)? SupplierGoodsServices::getProductStatus($v->product->product_status) : '';
            $table_head[$c][]=Warehouse::find()->select('warehouse_name')->where(['warehouse_code'=>$v->warehouse_code])->scalar();
            $table_head[$c][]=$v->left_stock;
            $table_head[$c][]=$v->earlest_outofstock_date;
            $table_head[$c][]=round(((time()-strtotime($v->earlest_outofstock_date))/(60*60)),2).'H';;
        }
        theCsv::export([
            'header' =>$table,
            'data' => $table_head,
        ]);
    }

    public function actionPlatform($sku , $warehouse=''){
        $platformArr = SkuOutofstockStatisitics::getPlatform($sku);
        //没获取到平台数据
        if(empty($platformArr)){
            return '';
        }
        //有平台数据
        $html = ' : ';
        foreach ($platformArr as $key=>$platform){
            if(strtoupper(trim($platform->platform)) == 'BORROW') continue;// 不展示 BORROW 平台的数据
            $html .= ' '.$platform->platform.'('.$platform->lack_quantity.')、';
        }
        return rtrim($html,'、');
    }
}