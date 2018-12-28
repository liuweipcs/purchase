<?php
namespace app\models;

use app\models\base\BaseModel;

use yii\caching\DbDependency;
use yii\caching\Dependency;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/23
 * Time: 16:07
 */
class FbaStatisticSearch extends BaseModel
{

    public function rules()
    {
        return [
        ];
    }

    public function attributeLabels()
    {
        return [
        ];
    }

    //初始化页面
    public static function search($beginTime,$endTime){
        $beginTime = date('Y-m-d 00:00:00',strtotime($beginTime));
        $endTime = date('Y-m-d 23:59:59',strtotime($endTime));
        $orderDays = PurchaseOrder::find()
            ->select(['day'=>'DATE_FORMAT(submit_time,"%Y-%m-%d")','count'=>'count(id)'])
            ->where(['between','submit_time',$beginTime,$endTime])
            ->andWhere(['in','purchas_status',[2,3,5,6,7,8,9]])
            ->andWhere(['purchase_type'=>3])
            ->groupBy('day')
            ->orderBy('day ASC')
            ->asArray()->all();
        $orderDays = ArrayHelper::map($orderDays,'day','count');
        $demandDays = PlatformSummary::find()->select(['day'=>'DATE_FORMAT(agree_time,"%Y-%m-%d")','count'=>'count(id)'])
                        ->where(['level_audit_status'=>1,'purchase_type'=>3])
                        ->andWhere(['between','agree_time',$beginTime,$endTime])
                        ->groupBy('day')
                        ->orderBy('day ASC')
                        ->asArray()->all();
        $demandDays = ArrayHelper::map($demandDays,'day','count');
        $begintime = strtotime($beginTime);$endtime = strtotime($endTime);
        for ($start = $begintime; $start <= $endtime; $start += 24 * 3600) {
            $demandDatas[]= isset($demandDays[date('Y-m-d',$start)]) ? $demandDays[date('Y-m-d',$start)] : 0;
            $orderDatas[]= isset($orderDays[date('Y-m-d',$start)]) ? $orderDays[date('Y-m-d',$start)] : 0;
            $dateDatas[] = date('Y-m-d',$start);
        }
        return json_encode(['date'=>$dateDatas,'demand'=>$demandDatas,'order'=>$orderDatas]);
    }

    //每日需求情况
    public static function searchDemand($time){
        $productFirstLine = ProductLine::find()->select('product_line_id,linelist_cn_name')->where(['linelist_parent_id'=>0])->asArray()->all();
        if(\Yii::$app->cache->get('product_line_array')&&\Yii::$app->cache->get('product_line_data')){
            $productLineData = \Yii::$app->cache->get('product_line_data');
            $productLineChildren = \Yii::$app->cache->get('product_line_array');
        }else{
            $productFirstLineArray = ArrayHelper::map($productFirstLine,'product_line_id','linelist_cn_name');
            foreach ($productFirstLineArray as $key=>$value){
                $productLineData[] = $value;
                $productLineChildren[$key] = self::getLineChildren($key);
            }
            $dependency  = new DbDependency(['sql'=>'select max(id) from pur_product_line']);
            \Yii::$app->cache->set('product_line_array',$productLineChildren,0,$dependency);
            \Yii::$app->cache->set('product_line_data',$productLineData,0,$dependency);
        }
        foreach ($productLineChildren as $k=>$v){
            $count = PlatformSummary::find()
                    ->select(['count'=>'count(t.id)'])
                    ->alias('t')
                    ->leftJoin(Product::tableName().' p','p.sku=t.sku')
                    ->where(['in','p.product_linelist_id',$v])
                    ->andWhere(['between','t.agree_time',date('Y-m-d 00:00:00',strtotime($time)),date('Y-m-d 23:59:59',strtotime($time))])
                    ->andWhere(['level_audit_status'=>1,'purchase_type'=>3])->scalar();
            $productCount[] = $count? $count:0;
        }
        return json_encode(['Xdata'=>$productLineData,'Ydata'=>$productCount]);
    }

    //获取产品线情况
    public static function getLineChildren($parentId){
        $items[] =$parentId ;
        $chilProductLine = ProductLine::find()->select('product_line_id')->where(['linelist_parent_id'=>$parentId])->column();
        if(empty($chilProductLine)){
            return $items;
        }else{
            $items=array_merge($items,$chilProductLine);
            foreach ($chilProductLine as $productLine){
                $items =array_merge(self::getLineChildren($productLine),$items);
            }
        }
        return array_unique($items);
    }

    //每日采购单情况
    public static  function searchOrder($time){
        $userArray = self::getPurchaseUserData('FBA');
        $userNames = array_values($userArray);
        $orderDatas = PurchaseOrder::find()->select(['buyer'=>'buyer','count'=>'count(id)'])
            ->where(['in','buyer',$userNames])
            ->andWhere(['between','submit_time',date('Y-m-d 00:00:00',strtotime($time)),date('Y-m-d 23:59:59',strtotime($time))])
            ->andWhere(['in','purchas_status',[2,3,5,6,7,8,9]])
            ->groupBy('buyer')
            ->asArray()->all();
        $orderArray = ArrayHelper::map($orderDatas,'buyer','count');
        $orderDataResult = [];
        foreach ($userNames as $key=>$userName){
            $orderDataResult[$key] = isset($orderArray[$userName]) ? $orderArray[$userName]  : 0;
        }
        return json_encode(['Xdata'=>$userNames,'Ydata'=>$orderDataResult]);
    }

    public static function getPurchaseUserData($type){
        if($type=='FBA'){
            $fbaPurchaseUser = \Yii::$app->authManager->getUserIdsByRole('FBA采购组');
            $fbaPurchaseManageUser = \Yii::$app->authManager->getUserIdsByRole('FBA采购经理组');
        }
        $userIds = array_unique(array_merge($fbaPurchaseUser,$fbaPurchaseManageUser));
        $userDatas = User::find()->select('id,username')->where(['in','id',$userIds])->asArray()->all();
        $userDataArray = ArrayHelper::map($userDatas,'id','username');
        return $userDataArray;
    }
}