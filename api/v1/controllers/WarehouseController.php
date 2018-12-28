<?php

namespace app\api\v1\controllers;
use app\api\v1\models\ArrivalRecord;
use app\api\v1\models\Product;
use app\api\v1\models\ProductDescription;
use app\api\v1\models\SkuSalesStatistics;
use app\api\v1\models\Stock;
use app\api\v1\models\Warehouse;
use app\api\v1\models\WarehouseOwedGoods;
use app\models\BoxSkuQty;
use yii;
use app\config\Vhelper;
use linslin\yii2\curl;
use yii\helpers\Json;


/**
 * 仓库
 * Created by PhpStorm.
 * User: ztt
 * Date: 2017/3/23 0023
 * Time: 18:41
 */
class WarehouseController extends BaseController
{
    public $modelClass = 'app\api\v1\models\Warehouse';

    /**
     * 接收数据中心过来的仓库
     * @return array|null|yii\db\ActiveRecord
     */
    public function actionCreateWarehouse()
    {
        $datas  = Yii::$app->request->post()['warehouseInfo'];
        if(isset($datas) && !empty($datas))
        {
            $datas  = Json::decode($datas);
            $data   = Warehouse::FindOnes($datas);

            return $data;
        } else{
            return '没有任何的数据过来！';
        }


    }

    /**
     * 接收数据中心过来sku销量统计
     * @return array|null|yii\db\ActiveRecord
     */
    public function  actionCreateSalesStatistics()
    {
        $datas  = Yii::$app->request->post()['salesStatistics'];
        if(isset($datas) && !empty($datas))
        {
            $datas  = Json::decode($datas);
            $data   = SkuSalesStatistics::FindOnes($datas);
            return $data;
        } else {
            return '没有任何的数据过来！';
        }

    }

    /**
     * 接收仓库欠货
     * @return array|null|string|yii\db\ActiveRecord
     */
    public function  actionCreateOwedGoods()
    {
        $datas  = Yii::$app->request->post()['OwedGoods'];
        if(isset($datas) && !empty($datas))
        {
            $datas  = Json::decode($datas);
            $data   = WarehouseOwedGoods::FindOnes($datas);
            return $data;
        } else {
            return '没有任何的数据过来！';
        }
    }

    //接收并保存采购到货记录
    public function actionGetArriva(){
        $post=Yii::$app->request->post('deliveryData');
        if(!empty($post)){
            $qc_id = [];
            foreach($post as $k=>$v){
                $model = ArrivalRecord::find()->where(['qc_id'=>$v['qc_id']])->one();
                if(empty($model)){
                    $model = new ArrivalRecord();
                }
                $model->purchase_order_no = $v['purchase_order_no'];
                $model->sku = $v['sku'];
                if(empty($v['name'])){
                    $v['name'] = ProductDescription::find()->select('title')->where(['sku'=>$v['sku'],'language_code'=>'Chinese'])->scalar();
                }
                $model->name = $v['name'];
                $model->delivery_qty = $v['delivery_qty'];
                $model->delivery_time = empty($v['delivery_time']) ? $v['middle_create_time'] : $v['delivery_time'];
                $model->delivery_user = empty($v['delivery_user']) ? '系统操作' : $v['delivery_user'];
                $model->cdate = time();
                $model->express_no = $v['express_no'];
                $model->check_type = $v['check_type'];
                $model->bad_products_qty = $v['bad_products_qty'];
                $model->check_time = $v['check_time'];
                $model->check_user = $v['check_user'];
                $model->qc_id = $v['qc_id'];
                $model->note = $v['note'];
                if($model->save()){
                    $qc_id[] = $v['qc_id'];
                }
            }
            exit(json_encode(['qc_id'=>$qc_id,'code'=>'200','msg'=>'success']));
        }else{
            exit(json_encode(['code'=>'500','msg'=>'null']));
        }
    }

    //接收并保存一个SKU一箱的数量
    public function actionGetBoxskuqty(){
        $post=Yii::$app->request->post('delivery_data');
        $data=json_decode($post,true);

        if(!empty($data)){
            $box_id=[];
            foreach($data as $k=>$v){
                $findone=BoxSkuQty::findOne(['sku'=>$v['sku']]);
                $model=$findone ? $findone : new BoxSkuQty();
                //$model->setAttributes($v);
                $model->sku=$v['sku'];
                $model->box_amount=$v['box_amount'];
                $model->create_time=$v['create_time'];
                $model->create_user_ip=$v['create_user_ip'];
                $model->create_user_id=$v['create_user_id'];
                $re=$model->save(false);

                if($re){
                    $box_id[]=$v['box_id'];
                }else{
                    $box_id[]=$v['box_id'];
                }
            }

            if($re){
                exit(json_encode(['box_id'=>$box_id,'code'=>'200','msg'=>'success']));
            }else{
                exit(json_encode(['box_id'=>$box_id,'code'=>'300','msg'=>'failure']));
            }
        }else{
            exit(json_encode(['code'=>'500','msg'=>'null']));
        }
    }

}
