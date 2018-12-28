<?php
namespace app\api\v1\controllers;

use app\api\v1\models\ApiPageCircle;
use app\api\v1\models\Product;
use app\api\v1\models\SkuOutofstockStatisitics;
use app\api\v1\models\Stock;
use app\api\v1\models\PlatformSummary;
use app\config\Vhelper;
use app\models\AmazonOutofstockOrder;
use app\models\SupplierNum;
use Yii;
use yii\db\Exception;
use yii\web\BadRequestHttpException;


class FbaOutstockController extends BaseController {
    public function actionGetPurchaseSuggest(){
//        if(time()>strtotime(date('Y-m-d 09:00:00',time()))){
//            exit();
//        }
        $page = ApiPageCircle::find()->select('page')->where(['type'=>'AMAZON_OUT_STOCK'])->andWhere(['>=','create_time',date('Y-m-d 00:00:00')])->orderBy('id DESC')->scalar();
        if(!$page){
            $page=0;
        }
        $pageSize=100;
        $url = Yii::$app->params['ERP_URL'].'/services/api/order/index/method/getOutofstockOrders?platformCode=AMAZON&page='.$page.'&pageSize='.$pageSize;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//这个是重点。
        $output = curl_exec($ch);
        curl_close($ch);
        $outofStockDatas =[];
        if(Vhelper::is_json($output)){
            $datas = json_decode($output);
            $i=0;
            if(property_exists($datas,'ack')&&$datas->ack){
               if(!empty($datas->datas)){
                   if(count($datas->datas)==$pageSize){
                       Yii::$app->db->createCommand()->insert(ApiPageCircle::tableName(),['type'=>'AMAZON_OUT_STOCK','page'=>$page+1,'create_time'=>date('Y-m-d H:i:s',time())])->execute();
                   }
                   foreach ($datas->datas as $data){
                       if(in_array($data->warehouse_id,[1,316])){
                           foreach ($data->items as $item){
                               if(property_exists($item,'qs')&&$item->qs!=0){
                                   if(empty($item->sku)||empty($item->qs)||empty($data->order_id)){
                                       continue;
                                   }
                                   $outofStockDatas[$i]['sku'] = $item->sku;
                                   $outofStockDatas[$i]['amazon_order_id'] = $data->order_id;
                                   $outofStockDatas[$i]['purchase_num'] = $item->quantity;
                                   $outofStockDatas[$i]['outofstock_num'] = $item->qs;
                                   $outofStockDatas[$i]['pay_time'] = $data->paytime;
                                   $i++;
                               }
                           }
                       }
                   }
               }else{
                   exit('已经没有缺货数据');
               }
            }
        }
        if(!empty($outofStockDatas)){
            foreach ($outofStockDatas as $outofStockOrder){
                $model =  AmazonOutofstockOrder::find()->where(['amazon_order_id'=>$outofStockOrder['amazon_order_id'],'sku'=>$outofStockOrder['sku']])->one();
                if(empty($model)){
                    $model = new AmazonOutofstockOrder();
                }
                $model->sku = $outofStockOrder['sku'];
                $model->amazon_order_id = $outofStockOrder['amazon_order_id'];
                $model->purchase_num = $outofStockOrder['purchase_num'];
                $model->outofstock_num = $outofStockOrder['outofstock_num'];
                $model->pay_time = $outofStockOrder['pay_time'];
                if($model->isNewRecord){
                    $model->create_time = date('Y-m-d H:i:s',time());
                }else{
                    $model->update_time = date('Y-m-d H:i:s',time());
                }
                $model->is_show = 1;
                if($model->save()==false){
                    throw new BadRequestHttpException('缺货数据写入失败');
                }
            }
        }
    }
}