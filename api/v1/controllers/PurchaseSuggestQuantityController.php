<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/4
 * Time: 10:24
 */

namespace app\api\v1\controllers;
use app\config\Vhelper;
use app\models\ProductSourceStatus;
use app\models\PurchaseSuggestMrp;
use yii;
use linslin\yii2\curl;

use app\api\v1\models\PurchaseSuggestQuantity;
use app\models\PurchaseSuggestHistory;
use app\models\PurchaseSuggest;
use app\services\PurchaseSuggestQuantityServices;

class PurchaseSuggestQuantityController extends BaseController
{
    public static function actionGetSuggestQuantity()
    {
        $datas = Yii::$app->request->post();

        if(!empty($datas['stock'])){
            $get_info = json_decode($datas['stock'],1);
            $sku = [];
            foreach($get_info as $k=>$v){
                $model = new PurchaseSuggestQuantity();
                if (empty($v['sku'])) {
                    continue;
                }
                //采购需求中状态未使用的，就不用重新接受数据
                $res = PurchaseSuggestQuantity::find()
                    ->where(['=','sku',$v['sku']])
                    ->andWhere(['=','suggest_status','1'])
                    ->andWhere(['=','purchase_warehouse',$v['purchase_warehouse']])
                    ->andWhere(['=','purchase_type','5'])
                    ->one();
                if (!empty($res)) {
                    continue;
                }

                $model->sku = $v['sku'];
                $model->platform_number = !empty($v['platform_number']) ? $v['platform_number'] : ''; //平台号
                $model->purchase_quantity = $v['purchase_quantity'];  //采购数量
                $model->purchase_warehouse = $v['purchase_warehouse'];  //采购仓
                $model->sales_note = $v['sales_note'];  //销售备注
                $model->create_id = $v['create_user']; //创建人
                $model->create_time = $v['create_time'];  //创建时间
                $model->suggest_status = 1; //状态
                $model->purchase_type = 5; //采购类型 5 仓库推送的采购需求
                if($model->save()){
                    $sku[] = $v['sku'];
                }
            }
            $msg = ['sku'=>$sku,'code'=>'200','msg'=>'success'];
        }else{
            $msg = ['code'=>'500','msg'=>'无数据'];
        }
        return $msg;

    }
    /**
     * 统计采购建议中的未处理时间
     */
    public function actionUntreatedTime()
    {
        $warehouse_code = PurchaseSuggestQuantityServices::getSuggestWarehouseCode(true);
        $start_time = date('Y-m-d 00:00:00',time());
        $end_time = date('Y-m-d 23:59:59',time());
        $created_at = date('Y-m-d 00:00:00', time());
        $time = date('Y-m-d 00:00:00', time());
        $data = [];

        $sql = "SELECT `sku`,`warehouse_code`
        FROM `pur_purchase_suggest`
        WHERE ( `warehouse_code` IN ({$warehouse_code}) )
        AND ( `qty` > 0 )
        AND ( `sku` != 'XJFH0000' )
        AND ( `purchase_type` = 1 )
        AND ( `created_at` BETWEEN '{$start_time}' AND '{$end_time}' )
        AND ( `pur_purchase_suggest`.`state` IN (0, 1, 2) )
        GROUP BY `sku`, `warehouse_code`";

        $res = Yii::$app->db->createCommand($sql)->queryAll();
        // vd($sql, $res);

        foreach ($res as $key => $value) {
            $created_at = date('Y-m-d 00:00:00', time());

            //state 处理状态: 0 未处理，1 已生成PO， 2 已完成
            $hres = PurchaseSuggestHistory::find()->select('id,state,created_at')->where($value)->orderBy(['id'=>SORT_DESC])->asArray()->all();
            if (!empty($hres)) {
                $state_array = array_column($hres, 'state');
                $state_res = array_unique($state_array);

                if ( !in_array(2, $state_res) ) {
                    //如果全部未处理,取最后一个
                    $created_at = array_pop($hres)['created_at'];
                } else {
                    //否则取已处理的上一个
                    foreach ($hres as $hk => $hv) {
                        if ( ($hv['state']==2) ) {
                            //如果已经处理，则返回上一个未处理时间
                            $created_at = !empty($hres[$hk-1]['created_at']) ? $hres[$hk-1]['created_at'] : $created_at;
                            break;
                        }
                    }
                }
            }

            //插入未处理时间
            $untreated_time = ceil((strtotime($time) - strtotime($created_at)) / 86400) + 1;
            PurchaseSuggest::updateAll(['untreated_time' => $untreated_time], $value);
            // vd($time, $created_at, $untreated_time);
            //$data[] = $value;
        }

        //vd($data);
    }

    /**
     * 获取 采购建议列表 SKU 建议数量
     *
     * @method GET | POST
     * @param       int page_index 页码
     *              int limit 限制条数
     *              array sku_list 指定 SKU 列表
     */
    public function actionGetPurchaseSuggest(){
        $return_arr = ['code' => 'success','message' => ''];

        $params     = Yii::$app->request->post();
        if(empty($params)){
            $return_arr['code']     = 'error';
            $return_arr['message']  = '请设置查询参数';
            echo json_encode($return_arr);
            exit;
        }

        // 查询参数初始化
        $pageIndex  = isset($params['page_index'])?intval($params['page_index']):0;
        $limit      = isset($params['limit'])?intval($params['limit']):100;
        $sku_list   = isset($params['data'])?$params['data']:'';

        if(isset($params['data']) AND empty($params['data'])){
            $return_arr['code']     = 'error';
            $return_arr['message']  = 'data SKU参数不能为空';
            echo json_encode($return_arr);
            exit;
        }

        if($sku_list) $sku_list = json_decode($sku_list,true);
        if( (isset($params['limit']) AND $params['limit'] > 500 ) OR ($sku_list AND count($sku_list) > 500) ){
            $return_arr['code']     = 'error';
            $return_arr['message']  = '一次最多只能查询 500 个';
            echo json_encode($return_arr);
            exit;
        }

        $subQuery = (new yii\db\Query())->from(ProductSourceStatus::tableName())->select('sku')->where('sourcing_status=2');
        if($sku_list){// 根据 SKU 查询
            $purchase_suggest_sku_list = PurchaseSuggestMrp::find()
                ->select([" concat(UPPER(warehouse_code),'_',sku) AS ware_sku",'qty AS sku_qty'])
                ->andFilterWhere(['in','sku',$sku_list])
                ->andWhere(['warehouse_code' => 'SZ_AA'])
                ->andWhere(['not in','sku',$subQuery])
                ->andWhere(['>','created_at',date('Y-m-d')])
                ->asArray()
                ->all();
        }else{// 分页查询
            $purchase_suggest_sku_list = PurchaseSuggestMrp::find()
                ->select([" concat(UPPER(warehouse_code),'_',sku) AS ware_sku",'qty AS sku_qty'])
                ->andWhere(['warehouse_code' => 'SZ_AA'])
                ->andWhere(['not in','sku',$subQuery])
                ->andWhere(['>','created_at',date('Y-m-d')])
                ->offset($pageIndex * $limit)
                ->limit($limit)
                ->asArray()
                ->all();
        }

        if($purchase_suggest_sku_list){
            $purchase_suggest_sku_list = array_column($purchase_suggest_sku_list,'sku_qty','ware_sku');
            $return_arr['sku_list'] = $purchase_suggest_sku_list;
        }else{
            $return_arr['message'] = '未找到记录';
        }

        echo json_encode($return_arr);
        exit;
    }

}