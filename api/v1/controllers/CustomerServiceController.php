<?php

namespace app\api\v1\controllers;

use app\api\v1\models\CustomerService;
use app\config\Vhelper;

/**
 * 售后
 * Created by PhpStorm.
 * User: wr
 * Date: 2018/02/06
 * Time: 10:55
 */
class CustomerServiceController extends BaseController
{

    /*
     * 获取售后系统sku关于供应商问题的售后单信息
     */
    public function actionGetServiceData(){
        //$date = '2018-04';
        $date = date('Y-m-d',strtotime('-1 day'));
        $url = 'http://kefu.yibainetwork.com/services/order/order/getafterproducts?date='.$date;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//这个是重点。
        curl_setopt($ch, CURLOPT_POST, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        if(Vhelper::is_json($output)){
            $data = json_decode($output);
            if(!empty($data)){
                $insertData = [];
                foreach ($data as $k=>$v){
                    $model = CustomerService::find()->where(['data_id'=>$v->id])->one();
                    if(empty($model)){
                        $insertData[$k][] = $v->id;
                        $insertData[$k][] = $v->reason_id;
                        $insertData[$k][] = $v->platform_code;
                        $insertData[$k][] = $v->sku;
                        $insertData[$k][] = $v->create_time;
                        $insertData[$k][] = $v->reason;
                        $insertData[$k][] = date('Y-m-d H:i:s',time());
                    }else{
                        $model->reason_id = $v->reason_id;
                        $model->platform_code = $v->platform_code;
                        $model->sku = $v->sku;
                        $model->data_create_time = $v->create_time;
                        $model->reason = $v->reason;
                        $model->update_time = date('Y-m-d H:i:s',time());
                        $model->save();
                    }
                }
                if(!empty($insertData)){
                    \Yii::$app->db->createCommand()->batchInsert(CustomerService::tableName(),
                        [
                            'data_id',
                            'reason_id',
                            'platform_code',
                            'sku',
                            'data_create_time',
                            'reason',
                            'create_time'
                        ],$insertData)->execute();
                }
            }
        }else{
            exit('error');
        }
    }

}
