<?php

namespace app\api\v1\controllers;
use app\api\v1\models\ProductProvider;
use app\api\v1\models\Stock;
use app\api\v1\models\Supplier;
use app\models\SkuTests;
use app\models\StockOwes;
use app\models\SkuOutofstockStatisitics;
use app\models\Warehouse;
use yii;
use app\config\Vhelper;
use linslin\yii2\curl;
use yii\helpers\Json;
use app\models\SupplierNum;
/**
 * 库存
 * Created by PhpStorm.
 * User: ztt
 * Date: 2017/3/23 0023
 * Time: 18:41
 */
class StockController extends BaseController
{
    public $modelClass = 'app\api\v1\models\Stock';

    public function actionCreateStock()
    {

        
        $datas  = Yii::$app->request->post()['purchaseStock'];
        if(isset($datas) && !empty($datas))
        {
            $datas  = Json::decode($datas);
            $m      = $this->modelClass;
            $data   = $m::FindOnes($datas);
            return $data;
        } else {
            return '没有任何的数据过来！';
        }

    }
    /**
     * 更新所有欠货库存为零
     */
    protected  function  UpdateAllStock()
    {
        Stock::updateAll(['left_stock'=>0],['warehouse_code'=>['SZ_AA','ZMXNC_EB','ZMXNC_WM','CDxuni','ZDXNC','xnc','HW_XNC']]);
        StockOwes::deleteAll();
    }
    /**
     * 获取欠货的 页码类型12
     */
    public  function  actionGetLess($ids=1, $renew = 1)
    {
        \yii::$app->response->format = 'raw';
        if ($renew == 1)
        {
            SupplierNum::deleteAll(['type' => 12]);
        }
        $start =  strtotime(date('Y-m-d 00:00:00',time()));
        $end   =  strtotime(date('Y-m-d H:i:s'));
        $is   = SupplierNum::find()->select('num')->where(['type'=>12])->andWhere(['between','time',$start,$end])->orderBy('id desc')->scalar();
        
        if(!empty($is))
        {
            $id = $is;
        } else{
            $this->UpdateAllStock();
            $id = $ids;
        }
        $url = Yii::$app->params['ERP_URL'].'/services/api/order/index/method/getSkuOutofstockStatistics?page='.$id;

        $curl  = new curl\Curl();
        $s       = $curl->post($url);
        //验证json
        $sb = Vhelper::is_json($s);
        if(!$sb)
        {
            echo '请检查json'."\r\n";
            exit($s);
        } else {
            $_result = Json::decode($s);
           /* foreach($_result['datas'] as $v)
            {

                $sku      = new SkuTests;
                $sku->sku = $v['sku'];
                $sku->t1  = $v['warehouse_code'];
                $sku->t2  = $v['lack_quantity'];
                $sku->t3  = $v['statistics_date'];
                $sku->save(false);
            }*/
            foreach($_result['datas'] as $v)
            {

                $stockowes = new StockOwes();
                $stockowes->sku = trim($v['sku']);
                $stockowes->left_stock = trim($v['lack_quantity']);
                $stockowes->warehouse_code= trim($v['warehouse_code']);
                $stockowes->statistics_date= trim($v['statistics_date']);
                $stockowes->earlest_outofstock_date= trim($v['earlest_outofstock_date']);
                $stockowes->status=0;
                $stockowes->save(false);
            }
            $status  = Stock::SaveOness($_result['datas']);
            if(in_array(1,$status))
            {
                $id       = $_result['page']+1;
                $mod      = new SupplierNum();
                $mod->num = $id;
                $mod->type = 12;
                $mod->time = time();
                $mod->save(false);
                $this->actionGetLess(null, 0);

           } else{
                $mod      = new SupplierNum();
                $mod->num = $_result['page'];
                $mod->type = 12;
                $mod->time = time();
                $mod->save(false);
                exit('数据拉取完成');
            }
        }
    }

    /**
     * 拉取通途库存
     */
    public function actionGetStock()
    {
        set_time_limit(50000);
        $is= SupplierNum::find()->select('num')->orderBy('id desc')->scalar();

        if(!empty($is))
        {
            $id = $is;
        } else{
            $id = 1;
        }
        //for ($i=$id;$i<=140;$i++) {

        $curl  = new curl\Curl();
        $datas = [
            'token' => 'b24fe215-7a7b-4e83-85be-e917d59eef18',
            'data'  => [
                'merchantId' => '003498',
                'pageNo'     => $id,
                'warehouseName' =>'易佰东莞仓库',
            ],
        ];
        try {
            $url     = Yii::$app->params['tongtool'] . '/process/resume/openapi/tongtool/stocksQuery';
            $s       = $curl->setPostParams([
                'q' => Json::encode($datas),
            ])->post($url);

            $_result = Json::decode($s);
            if(!is_array($_result['data']['array']))
            {
                $mod      = new SupplierNum();
                $mod->num = $id;
                $mod->save(false);
                exit('不是数组');
            } else {
                $m      = $this->modelClass;
                $m::FindOness($_result['data']['array']);
                $mod      = new SupplierNum();
                $mod->num = $id+1;
                $mod->save(false);
            }

        } catch (Exception $e) {

            exit('发生了错误');
        }
        //}

    }


    /**
     * 更新所有欠货库存为零
     */
    protected  function  UpdateAllStockStatisitics()
    {
       // Stock::updateAll(['left_stock'=>0],['warehouse_code'=>['SZ_AA','ZMXNC_EB','ZMXNC_WM','CDxuni','ZDXNC','xnc','HW_XNC']]);
        SkuOutofstockStatisitics::deleteAll();
        SupplierNum::deleteAll(['type'=>'13']);
    }

    /**
     * 获取欠货的 页码类型12 获取平台
     */
    public  function  actionGetLessPlatform($ids=1)
    {
        set_time_limit(200);
        /*$start =  strtotime(date('Y-m-d 00:00:00',time()));
        $end   =  strtotime(date('Y-m-d H:i:s'));*/
        $is   = SupplierNum::find()->select(['num','time'])->where(['type'=>13])->orderBy('id desc')->one();

        if(!empty($is))
        {
            if((time() - $is->time) > 1800){
                $this->UpdateAllStockStatisitics();
                $id = $ids;
            }else{
                $id = $is->num;
            }
        } else{
            //$this->UpdateAllStockStatisitics();
            $id = $ids;
        }


        $url = Yii::$app->params['ERP_URL'].'/services/api/order/index/method/getSkuOutofstockStatistics?type=2&page='.$id;

        $curl  = new curl\Curl();
        $s       = $curl->post($url);
        //验证json
        $sb = Vhelper::is_json($s);
        if(!$sb)
        {
            echo '请检查json'."\r\n";
            exit($s);
        } else {
            $_result = Json::decode($s);
            if(!empty($_result['datas'])) {
                foreach ($_result['datas'] as $v) {
                    $stockStatisitics = new SkuOutofstockStatisitics();
                    $arrWarehouse = Warehouse::getWarehouseByCode($v['warehouse_code']);
                    if (empty($arrWarehouse)) {
                        continue;
                    }
                    $stockStatisitics->warehouse_id = trim($arrWarehouse['id']);
                    $stockStatisitics->sku = trim($v['sku']);
                    $stockStatisitics->platform = trim($v['platform_code']);
                    $stockStatisitics->lack_quantity = trim($v['lack_quantity']);
                    $stockStatisitics->statistics_date = trim($v['statistics_date']);
                    $stockStatisitics->warehouse_code = trim($v['warehouse_code']);
                    $stockStatisitics->earlest_outofstock_date = trim($v['earlest_outofstock_date']);
                    $stockStatisitics->save(false);
                }
            }

            /*$status  = Stock::SaveOness($_result['datas']);*/

            /*if(in_array(1,$status))*/
            if(!empty($_result['datas']))
            {
                $id       = $_result['page']+1;
                $mod      = new SupplierNum();
                $mod->num = $id;
                $mod->type = 13;
                $mod->time = time();
                $mod->save(false);
                $this->actionGetLessPlatform();

            } else{
                $mod      = new SupplierNum();
                $mod->num = $_result['page'];
                $mod->type = 13;
                $mod->time = time();
                $mod->save(false);
                exit('数据拉取完成');
            }
        }
    }
}
