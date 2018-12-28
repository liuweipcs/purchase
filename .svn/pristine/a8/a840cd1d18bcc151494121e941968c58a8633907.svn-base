<?php

namespace app\api\v1\controllers;


use app\models\LargeWarehouse;
use yii;
use app\config\Vhelper;
use linslin\yii2\curl;
use yii\helpers\Json;

/**
 * 采购到货异常与收货异常与采购收货异常审核
 * Created by PhpStorm.
 * User: ztt
 * Date: 2017/3/23 0023
 * Time: 18:41
 */
class LargeWarehouseController extends BaseController
{
    //仓库驳回数据
    public function actionAddLargeWarehouse(){
        $result = ['status'=>0 , 'msg'=>'', 'data'=>[]];
        try {
            $data = Yii::$app->request->post('warehouse');

            if(empty($data)){
                $result['msg'] = '数据不存在，请检查';
                die(Json::encode($result));
            }

            //验证json
            $is_json = Vhelper::is_json($data);
            if(!$is_json)
            {
                $result['msg'] = '数据格式不正确，请检查';
                die(Json::encode($result));
            }


            //清空大仓表
            $data = Json::decode($data);
            $success_ids = [];
            foreach($data as $k => $v) {
                $is_exists = LargeWarehouse::find()->where(['category_id' => $v['category_id']])->one();
                if($is_exists){
                    $is_exists->name = $v['name'];
                    $is_exists->description = $v['description'];
                    $is_exists->create_by = $v['create_by'];
                    $is_exists->create_time = $v['create_time'];
                    $is_exists->modify_by = $v['modify_by'];
                    $is_exists->modify_time = $v['modify_time'];
                    $is_exists->is_delete = $v['is_delete'];
                    $is_exists->is_push = $v['is_push'];
                    $res = $is_exists->save();
                    if($res){
                        $success_ids[] = $v['category_id'];
                    }
                }else{
                    $model = new LargeWarehouse();
                    $model->category_id = $v['category_id'];
                    $model->name = $v['name'];
                    $model->description = $v['description'];
                    $model->create_by = $v['create_by'];
                    $model->create_time = $v['create_time'];
                    $model->modify_by = $v['modify_by'];
                    $model->modify_time = $v['modify_time'];
                    $model->is_delete = $v['is_delete'];
                    $model->is_push = $v['is_push'];
                    $res = $model->save();
                    if($res){
                        $success_ids[] = $v['category_id'];
                    }
                }
            }

            $result['status'] = 1;
            $result['data'] = $success_ids;
            die(Json::encode($result));
        } catch(\Exception $e) {
            die(Json::encode($e->getMessage()));
        }

    }

}
