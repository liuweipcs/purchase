<?php
namespace app\api\v1\controllers;

/*
 * 数据统计api
 */
use app\api\v1\models\ArrivalRecord;
use app\api\v1\models\DataControlConfig;
use app\api\v1\models\FbaAvgDelieryTime;
use app\api\v1\models\FbaAvgDeliveryLog;
use app\api\v1\models\InlandAvgDelieryTime;
use app\api\v1\models\InlandAvgDeliveryLog;
use app\api\v1\models\Product;
use app\api\v1\models\PurchaseOrder;
use app\api\v1\models\PurchaseOrderItems;
use app\api\v1\models\SkuStatisticsInfo;
use yii\db\Exception;

class StatisticsController extends BaseController {
    /*
     * 统计sku在一定时间内的采购数量
     */
    public function actionStatPurchaseNum(){
        set_time_limit(0);
        $time_limit = DataControlConfig::find()->select('values')->where(['type'=>'down_time_limit'])->scalar();
        if(!$time_limit){
            $time_limit = 7;
        }
        $begin_time = date('Y-m-d 00:00:00',strtotime("- $time_limit day"));
        $end_time   = date('Y-m-d 23:59:59',time());
        $checkTime  = date('Y-m-d H:i:s',time());
        $product_info = Product::find()->select('sku')->asArray()->all();
        $skus = array_column($product_info,'sku');
        $insertData = [];
        $purchaseDatas = PurchaseOrderItems::find()
            ->alias('t')
            ->select(['ctq'=>'ifnull(t.ctq,0)','sku'=>'p.sku'])
            ->leftJoin(Product::tableName().' p','p.sku=t.sku')
            ->leftJoin(PurchaseOrder::tableName().' o','o.pur_number=t.pur_number')
            ->where(['not in','o.purchas_status',[1,2,4,10]])
            ->andWhere(['between','o.created_at',$begin_time,$end_time])
            ->asArray()->all();
        $purchaseNum = [];
        foreach ($purchaseDatas as $data){
            $purchaseNum[$data['sku']] = isset($purchaseNum[$data['sku']]) ? $purchaseNum[$data['sku']]+$data['ctq'] : $data['ctq'];
        }
        foreach ($skus as $key=>$sku){
            $insertData[$key][]= $sku;
            $insertData[$key][]= isset($purchaseNum[$sku]) ? $purchaseNum[$sku] : 0;
            $insertData[$key][]= $checkTime;
        }
        $tran = \Yii::$app->db->beginTransaction();
        try{
            \Yii::$app->db->createCommand()->delete(SkuStatisticsInfo::tableName())->execute();
            \Yii::$app->db->createCommand()->batchInsert(SkuStatisticsInfo::tableName(),['sku','purchase_num','create_time'],$insertData)->execute();
            $tran->commit();
        }catch (Exception $e){
            $tran->rollBack();
            exit($e->getMessage());
        }
        unset($insertData);
    }
    //计算国内仓FBA权均交期
    public function actionStatInlandAvgDelivery($type){
        //$type==1国内仓 $type==3 FBA
        if(empty($type)||!in_array($type,[1,3])){
            \Yii::$app->end('类型参数不符合要求');
        }
        $query = ArrivalRecord::find()
            ->alias('t')
            ->select('t.id,t.sku,t.purchase_order_no,t.check_time,o.audit_time,o.warehouse_code')
            ->leftJoin(PurchaseOrder::tableName().' o','o.pur_number=t.purchase_order_no')
            ->where(['>','t.check_time','2018-04-14 12:00:00'])
            ->andWhere(['is_check_inland'=>0]);
         if($type==1){
            $query->andWhere('t.purchase_order_no like "PO%"');
         }
         if($type==3){
             $query->andWhere('t.purchase_order_no like "FBA%"');
         }
         $datas=$query->orderBy('t.check_time ASC')
            ->limit(2000)
            ->asArray()->all();
        if(empty($datas)){
            \Yii::$app->end('没有需要计算的数据');
        }
        $ids = array_column($datas,'id');
        $avg_data=[];
        $tran = \Yii::$app->db->beginTransaction();
        try{
            \Yii::$app->db->createCommand()->update(ArrivalRecord::tableName(),['is_check_inland'=>1],['in','id',$ids])->execute();
            foreach ($datas as $data){
                if(empty($data['audit_time'])){
                    continue;
                }
                if($type==1){
                    $haveCheck = InlandAvgDeliveryLog::find()->where(['sku'=>$data['sku'],'pur_number'=>$data['purchase_order_no']])->exists();
                    if(!$haveCheck){
                        \Yii::$app->db->createCommand()->insert(InlandAvgDeliveryLog::tableName(),
                            [
                                'sku'=>$data['sku'],
                                'pur_number'=>$data['purchase_order_no'],
                                'audit_time'=>$data['audit_time'],
                                'instock_time'=>$data['check_time'],
                                'create_time'=>date('Y-m-d H:i:s'),
                                'arrival_time'=>strtotime($data['check_time'])-strtotime($data['audit_time']),
                                'warehouse_code'=>$data['warehouse_code'],
                                'is_calc'=>1])->execute();
                        $avg_data[] = $data['sku'];
                    }
                }
                if($type==3){
                    $haveCheck = FbaAvgDeliveryLog::find()->where(['sku'=>$data['sku'],'pur_number'=>$data['purchase_order_no']])->exists();
                    if(!$haveCheck){
                        \Yii::$app->db->createCommand()->insert(FbaAvgDeliveryLog::tableName(),
                            [
                                'sku'=>$data['sku'],
                                'pur_number'=>$data['purchase_order_no'],
                                'audit_time'=>$data['audit_time'],
                                'instock_time'=>$data['check_time'],
                                'create_time'=>date('Y-m-d H:i:s'),
                                'arrival_time'=>strtotime($data['check_time'])-strtotime($data['audit_time']),
                                'warehouse_code'=>$data['warehouse_code'],
                                'is_calc'=>1])->execute();
                        $avg_data[] = $data['sku'];
                    }
                }

            }
            unset($datas);
            $avg_data = array_unique($avg_data);
            if(empty($avg_data)){
                exit('没有符合要求的数据');
            }
            if($type==1){
                foreach ($avg_data as $value){
                    $exists = InlandAvgDelieryTime::find()->where(['sku'=>$value])->exists();
                    $avgTime = InlandAvgDeliveryLog::find()
                        ->select(['avg_delivery_time'=>'sum(UNIX_TIMESTAMP(instock_time)-UNIX_TIMESTAMP(audit_time))/count(id)'])
                        ->where(['sku'=>$value])
                        ->andWhere(['is_calc'=>1])
                        ->groupBy('sku')
                        ->scalar();
                    if($exists){
                        \Yii::$app->db->createCommand()->update(InlandAvgDelieryTime::tableName(),
                            [
                                'avg_delivery_time'=>$avgTime,
                                'update_time'=>date('Y-m-d H:i:s',time())
                            ],['sku'=>$value])->execute();
                    }else{
                        \Yii::$app->db->createCommand()->insert(InlandAvgDelieryTime::tableName(),
                            [
                                'sku'=>$value,
                                'avg_delivery_time'=>$avgTime,
                                'update_time'=>date('Y-m-d H:i:s',time())])->execute();
                    }
                }
            }
            if($type==3){
                foreach ($avg_data as $value){
                    $exists = FbaAvgDelieryTime::find()->where(['sku'=>$value])->exists();
                    $avgTime = FbaAvgDeliveryLog::find()
                        ->select(['avg_delivery_time'=>'sum(UNIX_TIMESTAMP(instock_time)-UNIX_TIMESTAMP(audit_time))/count(id)'])
                        ->where(['sku'=>$value])
                        ->andWhere(['is_calc'=>1])
                        ->groupBy('sku')
                        ->scalar();
                    if($exists){
                        \Yii::$app->db->createCommand()->update(FbaAvgDelieryTime::tableName(),
                            [
                                'avg_delivery_time'=>$avgTime,
                                'update_time'=>date('Y-m-d H:i:s',time())
                            ],['sku'=>$value])->execute();
                    }else{
                        \Yii::$app->db->createCommand()->insert(FbaAvgDelieryTime::tableName(),
                            [
                                'sku'=>$value,
                                'avg_delivery_time'=>$avgTime,
                                'update_time'=>date('Y-m-d H:i:s',time())])->execute();
                    }
                }
            }
            $tran->commit();
            \Yii::$app->end('运行完成');
        }catch (Exception $e){
            $tran->rollBack();
            \Yii::$app->end($e->getMessage());
        }
    }
}