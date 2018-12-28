<?php

namespace app\api\v1\controllers;
use yii;
use yii\helpers\Json;

/**
 * sku采购数量
 * Created by PhpStorm.
 * User: ztt
 * Date: 2018/6/1 0023
 * Time: 18:41
 */
class SkuPurchaseQuantityController extends BaseController
{

    public function actionGetSkuQuantity()
    {

        //$data = ['status'=>0 , 'msg'=>'', 'data'=>[]];
        $connection = Yii::$app->db;

        ////获取数据
        $arrList = [];
        $set_month = '';
        for($m=1 ; $m<date('m'); $m++){
            $month = Yii::$app->request->get('month');
            if($month) {
                if(!preg_match('/\d{4}-\d{1,2}-\d{1,2}/',$month)){
                    return Yii::$app->response->data = [
                        'status' => 0,
                        'msg' => '月份格式不正确,格式为YYYY-MM-DD',
                    ];
                }
                $arr = explode('-',$month);
                $set_month = $arr[1];
                //获取输入月份第一天
                $start = date("Y-{$arr[1]}-01 00:00:00");
                //获取输入月份
                $end = date("Y-{$arr[1]}-{$arr[2]} 23:59:59");
            }else{
                //每个月第一天
                $start = date("Y-{$m}-01 00:00:00");

                //每个月最后一天
                $BeginDate = date("Y-{$m}-01", strtotime(date("Y-m-d")));
                $end = date('Y-m-d 23:59:59', strtotime("$BeginDate +1 month -1 day"));
            }
            //获取输入月份的sku采购数量数据
            $sql = "
                    select pur_purchase_suggest.warehouse_name,pur_purchase_suggest.sku,pur_purchase_suggest.created_at as date,
                    COUNT(*) as amount_total,pur_purchase_suggest.warehouse_code,pur_purchase_suggest.name
                    from pur_purchase_order,pur_purchase_order_pay_type,pur_purchase_demand,pur_purchase_suggest
                    where pur_purchase_order.pur_number = pur_purchase_order_pay_type.pur_number
                    and pur_purchase_demand.pur_number = pur_purchase_order.pur_number 
                    and pur_purchase_demand.demand_number = pur_purchase_suggest.demand_number 
                    AND (`pur_purchase_suggest`.`created_at` BETWEEN '{$start}' AND '{$end}')
                    group by pur_purchase_suggest.name,pur_purchase_suggest.warehouse_name,pur_purchase_suggest.sku HAVING count(*)>0
                    order by `pur_purchase_suggest`.`created_at` asc;
                ";
            $arrQuantity = $connection->createCommand($sql)->queryAll();

            //如果有数据则对数据进行重组
            $list = [];
            if(count($arrQuantity) > 0){
                foreach ($arrQuantity as $key=>$val){
                    $warehouse_name = $val['warehouse_name'];
                    unset($val['warehouse_name']);
                    $list[] = $val;
                }

            }
        }

        Yii::$app->response->format = yii\web\Response::FORMAT_JSON;
        if(empty($list)){
            /*$data['msg'] = '未获取到数据';
            return \yii\helpers\Json::encode($data);*/
            return Yii::$app->response->data = [
                'status' => 0,
                'msg' => '未获取到数据',
            ];
        }
        //成功返回
       /* $data['status'] = 1;
        $data['data'] = $arrList;
        return \yii\helpers\Json::encode($data);*/
        return Yii::$app->response->data = [
            'success' => 1,
            'msg' => '',
            'success_list' => $list
        ];
    }

    //获取中文月份
    public static function getMonth($number){
        if(!in_array($number,['01','02','03','04','05','06','07','08','09','10','11','12',])){
            return '';
        }
        switch ($number)
        {
            case "01":return "一月";
            case "02":return "二月";
            case "03":return "三月";
            case "04":return "四月";
            case "05":return "五月";
            case "06":return "六月";
            case "07":return "七月";
            case "08":return "八月";
            case "09":return "九月";
            case "10":return "十月";
            case "11":return "十一月";
            case "12":return "十二月";
        }
    }
}
