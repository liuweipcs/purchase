<?php

namespace app\api\v1\controllers;

use app\api\v1\models\TransferOrderPage;
use app\config\Vhelper;
use app\models\LockWarehouseConfig;
use app\models\PurchaseSuggest;
use app\models\TransferOrderChangeLog;
use Yii;
use app\models\SkuSingleTacticMain;
use app\models\WarehouseMin;
use yii\db\Exception;
use linslin\yii2\curl;
use yii\helpers\Json;
use app\api\v1\models\ApiRequestLog;

class TransferOrderController extends BaseController
{

    /**
     * 查询 SKU 是否已经锁定
     * @return array|string
     */
    public function  actionQuerySkuLockStatus()
    {
        $data  = Yii::$app->request->post();
        if( !isset($data['sku_list']) OR !isset($data['warehouse_code'])){
            $param = ['status' => 'error','message' => '仓库编码或SKU缺失'];
            echo Json::encode($param);
            exit;
        }

        $warehouse_code = $data['warehouse_code'];
        $sku_list = $data['sku_list'];
        if(!is_array($sku_list)){
            $sku_list = explode(',',$sku_list);
        }
        $sku_list_tmp = [];
        if($sku_list){
            foreach ($sku_list as $sku){
                $lock_sku = LockWarehouseConfig::find()
                    ->where("is_lock=1 AND sku=:sku AND warehouse_code=:warehouse_code")
                    ->addParams([':sku' => $sku,':warehouse_code' => $warehouse_code])
                    ->one();
                if ($lock_sku) $sku_list_tmp[$sku] = 1;

            }
        }

        $param = ['status' => 'success','data' => $sku_list_tmp,'message' => ''];
        echo Json::encode($param);
        exit;
    }

    /**
     * 采购建议生成调拨单
     * DATE: 2018-08-15
     */
    public function actionCreateTransferOrder(){
        try {
            ini_set('memory_limit', '1096M');
            set_time_limit(3600);

            //获取所有有建议数量（建议数量大于0）的国内仓采购建议
            $sql = "SELECT sum(1) as total
                 FROM `pur_purchase_suggest` 
                 LEFT JOIN `pur_product` ON pur_purchase_suggest.sku=pur_product.sku 
                 WHERE (`pur_purchase_suggest`.`warehouse_code` IN ('SZ_AA', 'HW_XNC', 'ZDXNC')) 
                 AND (`pur_purchase_suggest`.`qty` > 0) AND (`pur_purchase_suggest`.`sku` != 'XJFH0000') 
                 AND (`pur_purchase_suggest`.`purchase_type`=1) AND (`pur_purchase_suggest`.`state`='0') 
                 AND (`pur_purchase_suggest`.`product_status` NOT IN ('0', '5', '6', '7', '100'))
                 AND (`pur_product`.`product_status`!='7') 
            ";

            $arr_count =  Yii::$app->db->createCommand($sql)->queryOne();
            $total = isset($arr_count['total'])?$arr_count['total']:0;

            //分页获取
            if($total>0){
                //每页数量
                $pageSize = 100;
                //总页数
                $total_page = ceil($total/$pageSize);

                //获取当前已执行的页数
                $self_page = TransferOrderPage::find()
                    ->select('page')
                    ->where(['status'=>1,'create_time'=>date('Y-m-d 00:00:00')])
                    ->orderBy('page desc')
                    ->scalar();
                $page = !empty($self_page)?$self_page+1:1;
                if($self_page <= $total_page){
                    $loop_max = $page + 20;
                    for($i=$page;$i<$loop_max;$i++){
                        echo "page:" . $i;
                        /*$url = "http://" . $_SERVER['HTTP_HOST'] . "/v1/transfer-order/create-transfer?page=" . $i;
                        self::runThread($url);*/
                        $url = "/v1/transfer-order/create-transfer?page=" . $i;
                        Vhelper::throwTheader($url);
                        echo "<br>";
                        echo "<br>";
                    }
                }else{
                    exit('DONE');
                }
            }
        }catch (Exception $e) {
            exit('发生了错误');
        }
    }

    /**
     *
     * @param $url
     * @param string $hostname
     * @param int $port
     */
    public static function runThread($url,$hostname='',$port=80) {
        if(!$hostname){
            $hostname=$_SERVER['HTTP_HOST'];
        }
        $fp=fsockopen($hostname,$port,$errno,$errstr,30);
        if (!$fp)
        {
            echo "$errstr ($errno)<br />\n";
            return;
        }
        stream_set_blocking($fp, false);
        fputs($fp,"POST ".$url."\r\n");

        fclose($fp);
    }

    /**
     *
     * @param $limit
     * @param $offset
     */
    public function actionCreateTransfer(){
        ini_set('memory_limit', '1096M');
        set_time_limit(3600);
        //当前分页
        $page = $_REQUEST['page'];

        //页码
        $pageSize = 100;
        $offset = ($page-1)*$pageSize;

        //判断当前页是否已经执行成功
        $is_execute = TransferOrderPage::find()
            ->where(['page'=>$page,'create_time'=>date('Y-m-d'),'status'=>1])
            ->one();

        if(!$is_execute) {
            //获取符合条件的采购建议
            $sql = "SELECT pur_purchase_suggest.sku, 
                 `pur_purchase_suggest`.`warehouse_code`,
                 `pur_purchase_suggest`.`qty`,
                 `pur_purchase_suggest`.`sales_avg`, 
                 `pur_purchase_suggest`.`id` 
                 FROM `pur_purchase_suggest` LEFT JOIN `pur_product` ON pur_purchase_suggest.sku=pur_product.sku 
                 WHERE (`pur_purchase_suggest`.`warehouse_code` IN ('SZ_AA', 'HW_XNC', 'ZDXNC')) 
                 AND (`pur_purchase_suggest`.`qty` > 0) AND (`pur_purchase_suggest`.`sku` != 'XJFH0000') 
                 AND (`pur_purchase_suggest`.`purchase_type`=1) AND (`pur_purchase_suggest`.`state`='0') 
                 AND (`pur_purchase_suggest`.`product_status` NOT IN ('0', '5', '6', '7', '100')) 
                 AND (`pur_product`.`product_status`!='7')
                 GROUP BY `pur_purchase_suggest`.`sku`, `pur_purchase_suggest`.`warehouse_code` limit {$offset},{$pageSize}
            ";

            try {
                $suggests = Yii::$app->db->createCommand($sql)->queryAll();
                //循环采购建议进行建议数量调整
                if ($suggests) {
                    //记录执行页数
                    $dateTime   = date('Y-m-d H:i:s');
                    $order_page = TransferOrderPage::find()
                        ->where(['page' => $page, 'create_time' => date('Y-m-d 00:00:00')])
                        ->one();
                    if (empty($order_page)) {
                        $TransferOrderPageModel               = new TransferOrderPage();
                        $TransferOrderPageModel->page         = $page;
                        $TransferOrderPageModel->status       = TransferOrderPage::TASK_STATUS_FAILED;
                        $TransferOrderPageModel->execute_time = $dateTime;
                        $TransferOrderPageModel->create_time  = date('Y-m-d');
                        $TransferOrderPageModel->save();
                    }

                    //只调FBA_SZ_AA、SZ_AA、HW_XNC、执御虚拟仓
                    $able_warehouse = ['SZ_AA' => 'SZ_AA', 'ZDXNC' => 'ZDXNC', 'HW_XNC' => 'HW_XNC', 'FBA_SZ_AA' => 'FBA_SZ_AA'];

                    foreach ($suggests as $key => $suggest) {
                        //排除当前采购建议仓库 调拨其他仓库可调拨库存
                        unset($able_warehouse[$suggest['warehouse_code']]);

                        //通过数据中心接口获取 sku可用库存
                        $stock = self::actionGetStock($suggest['sku'], array_values($able_warehouse));

                        if ((!empty($stock['total_count']) && $stock['total_count'] > 0) && !empty($stock['data'])) {
                            //需调拨的数量  根据该数量判断是否调拨完成   调拨完成不需要调拨下一个仓库的库存
                            $need_stock_num = $suggest['qty'];

                            foreach ($stock['data'] as $v) {
                                //排除可调拨数量为0的仓库
                                if ($v['available_stock'] == 0) {
                                    continue;
                                }

                                //判断当前sku是否被锁定，锁定的sku不能调拨
                                $lock_sku = LockWarehouseConfig::find()
                                    ->where(['is_lock' => 1, 'sku' => $v['sku'], 'warehouse_code' => $v['warehouse_code']])
                                    ->one();
                                if ($lock_sku) {
                                    continue;
                                }

                                //不可调拨的仓库排除
                                if (!in_array(strtoupper($v['warehouse_code']), $able_warehouse)) {
                                    continue;
                                }

                                //获取安全调拨天数
                                $days_safe_transfer = self::actionGetDaysSafeTransfer($suggest['sku'], $suggest['warehouse_code']);

                                //sku可用库存 - (日均销量 * 安全调拨天数)   可调拨数量大于0就调拨
                                $allowinvent = $v['available_stock'] - intval($suggest['sales_avg'] * $days_safe_transfer);

                                if ($allowinvent > 0) {
                                    //判断可调拨数量  调拨数量最大为采购建议数量（采购建议数量>调拨数量  取调拨数量;采购建议数量<调拨数量  取采购建议数量 ）
                                    $qty = 0;
                                    if ($suggest['qty'] >= $allowinvent) {
                                        $qty = $allowinvent;
                                    } elseif ($suggest['qty'] < $allowinvent) {
                                        $qty = $suggest['qty'];
                                    }

                                    //通过接口在erp记录调拨记录 获取调拨记录id
                                    $arr_record = self::actionAddStockErpRecord($suggest['sku'], $qty, $suggest['id'], $v['warehouse_code'], $suggest['warehouse_code']);

                                    //记录erp调拨记录成功
                                    if ($arr_record['status'] == 1) {
                                        //调用仓库接口创建调拨单
                                        $res = self::actionGenerateOrder($arr_record['message'], $suggest['sku'], $qty, $v['warehouse_code'], $suggest['warehouse_code']);
                                        if ($res && isset($res['data']['success_list']) && $res['data']['success_list']) {
                                            //标记erp调拨记录状态:成功
                                            self::actionUpdateWarehouseMovement($arr_record['message'], $suggest['sku'], 1);

                                            //根据已调拨数量 判断采购建议需调拨数量都调拨完成   没调拨完成  调拨下一个仓库的
                                            $need_stock_num -= $qty;

                                            //调拨成功  更改采购建议数量
                                            $purchase_model = PurchaseSuggest::find()->where(['id' => $suggest['id']])->one();

                                            // 表修改日志-更新
                                            $change_data = [
                                                'suggest_id' => $suggest['id'], //采购建议id
                                                'sku' => $suggest['sku'], // 产品SKU
                                                'qty' => $purchase_model->qty, // 原始建议数据量
                                                'changed_qty' => $need_stock_num, // 变更后的建议数据量
                                            ];
                                            TransferOrderChangeLog::addLog($change_data);

                                            $purchase_model->qty = $need_stock_num;
                                            $purchase_model->save(false);


                                            //采购建议的建议数量大于0 继续调拨
                                            if ($need_stock_num > 0) {
                                                //调拨下一个可调拨仓库
                                                continue;
                                            } else {
                                                //轮循下一个采购建议
                                                continue 2;
                                            }
                                        } else {
                                            //标记该页失败
                                            $order_page = TransferOrderPage::find()
                                                ->where(['page' => $page, 'create_time' => date('Y-m-d')])
                                                ->one();
                                            if ($order_page) {
                                                $order_page->status = TransferOrderPage::TASK_STATUS_FAILED;
                                                $order_page->save();
                                            }
                                            //标记erp调拨记录状态:失败
                                            self::actionUpdateWarehouseMovement($arr_record['message'], $suggest['sku'], 2);
                                        }
                                    }
                                }
                            }
                        }
                        //页数执行到最后一个，标记成功
                        if ($key == 99) {
                            $orderModel         = TransferOrderPage::find()
                                ->where(['page' => $page, 'create_time' => date('Y-m-d')])
                                ->one();
                            $orderModel->status = TransferOrderPage::TASK_STATUS_SUCCESS;
                            $orderModel->save();
                        }
                    }
                }
            } catch (\Exception $e) {
                print_r($e->getMessage());
            }
        }
    }


    /**
     * 获取安全调拨天数
     * @param $sku
     * @param $worehouse_codes仓库编码
     * @return false|int|null|string
     */
    public static function actionGetDaysSafeTransfer($sku,$worehouse_code){
        //1、先根据SKU补货天数
        $now =  date('Y-m-d H:i:s');
        $days_safe_transfer = SkuSingleTacticMain::find()
            ->alias('main')
            ->select('content.days_safe_transfer')
            ->leftJoin('pur_sku_single_tactic_main_content content','main.id=content.single_tactic_main_id')
            ->where(['main.warehouse'=>$worehouse_code,'main.status'=>1])
            ->andFilterWhere(['like', 'main.sku', trim($sku)])
            ->andFilterWhere(['<=', 'main.date_start',$now])
            ->andFilterWhere(['>=', 'main.date_end',$now])
            ->scalar();

        if(!$days_safe_transfer){
            //2、SKU补货策略没查询到根据仓库查询安全调拨天数
            $days_safe_transfer = WarehouseMin::find()
                ->select('days_safe_transfer')
                ->where(['warehouse_code'=>$worehouse_code])
                ->scalar();
        }

        return !empty($days_safe_transfer) ? $days_safe_transfer : 0;
    }

    /**
     * erp记录调拨记录
     * @param $sku
     * @param $qty调拨数量
     * @param $src_warehouse_code原仓
     * @param $dtz_warehouse_code目的仓
     */
    public static function actionAddStockErpRecord($sku,$qty,$id,$src_warehouse_code,$dtz_warehouse_code){
        //组装参数
        $param=[];
        $param['title'] = $sku;
        $param['source_id']= $id;
        $param['detail'][]=[
            'sku' => $sku,
            'qty' => $qty,
            'src_warehouse_code' => $src_warehouse_code,
            'dtz_warehouse_code' => $dtz_warehouse_code
        ];

        //调用接口获取数据
        //$url = 'http://comerp.com/services/warehouse/warehouse/addwarehousemovement';
        $url = Yii::$app->params['ERP_URL'] . '/services/warehouse/warehouse/addwarehousemovement';//正式环境

        $curl  = new curl\Curl();
        $data = $curl->setPostParams([
            'data'=>Json::encode($param)
        ])->post($url);

        //记录请求日志
        $status='no-status';
        if($data){
            $status='success';
        }else{
            $status='error';
        }
        Yii::$app->db->createCommand()->insert(ApiRequestLog::tableName(),['post_content'=>Json::encode($param),
            'response_content'=>serialize($data),'create_time'=>date('Y-m-d H:i:s',time()),'api_url'=>'services/warehouse/warehouse/addwarehousemovement','status'=>$status])->execute();

        return Json::decode($data,true);
    }


    /**
     * erp记录调拨记录
     * @param $sku
     * @param $qty调拨数量
     * @param $src_warehouse_code原仓
     * @param $dtz_warehouse_code目的仓
     */
    public static function actionUpdateWarehouseMovement($mv_id,$sku,$status){
        //组装参数
        $param=[];
        /*$param['mv_detail_id'][] = $mv_id;
        $param['status'][] = $status;*/
        $param[] = [
            'mv_detail_id' => $mv_id,
            'status' => $status,
        ];

        //调用接口获取数据
        //$url = 'http://comerp.com/services/warehouse/warehouse/updatewarehousemovement';
        $url = Yii::$app->params['ERP_URL'] . '/services/warehouse/warehouse/updatewarehousemovement';//正式环境

        $curl  = new curl\Curl();
        $data = $curl->setPostParams([
            'data'=>Json::encode($param)
        ])->post($url);

        //记录请求日志
        $status='no-status';
        if($data){
            $status='success';
        }else{
            $status='error';
        }
        Yii::$app->db->createCommand()->insert(ApiRequestLog::tableName(),['post_content'=>Json::encode($param),
            'response_content'=>serialize($data),'create_time'=>date('Y-m-d H:i:s',time()),'api_url'=>'services/warehouse/warehouse/updatewarehousemovement','status'=>$status])->execute();

        return Json::decode($data,true);
    }


    /**
     * 获取sku可用库存
     * DATE: 2018-08-15
     * @param $skus
     * @param $worehouse_codes
     * @param int $page_size
     * @param int $current_page
     * @return mixed
     */
    public static function actionGetStock($skus,$worehouse_codes=[],$page_size=100,$current_page=1)
    {
        //组装参数
        $param = [];
        $param['skus'][] = $skus;
        $param['worehouse_codes'] = $worehouse_codes;
        $param['page_size'] = $page_size;
        $param['current_page'] = $current_page;


        //调用接口获取数据
        //$url = 'http://dc.yibainetwork.com/index.php/stock/getStockPage';
        $url = Yii::$app->params['server_ip'] . '/index.php/stock/getStockPage';//正式环境

        $curl  = new curl\Curl();
        $data = $curl->setPostParams([
            'search_json' => Json::encode($param),
        ])->post($url);

        //记录请求日志
        $status='no-status';
        if($data){
            $status='success';
        }else{
            $status='error';
        }
        Yii::$app->db->createCommand()->insert(ApiRequestLog::tableName(),['post_content'=>Json::encode($param),
            'response_content'=>serialize($data),'create_time'=>date('Y-m-d H:i:s',time()),'api_url'=>'stock/getStockPage','status'=>$status])->execute();

        return Json::decode($data,true);
    }

    /**
     * 采购接口生成调拨单
     * @param $message_id调拨记录id
     * @param $sku
     * @param $qty调拨数量
     * @param $src_warehouse_code原仓
     * @param $dtz_warehouse_code目的仓
     * @param $src_platform_code
     * @param $dtz_platform_code
     */
    public static function  actionGenerateOrder($message_id,$sku,$qty,$src_warehouse_code,$dtz_warehouse_code){
        //组装参数
        $param = [];
        $param['message_id'] = $message_id;
        $param['sku'] = $sku;
        $param['qty'] = $qty;
        $param['src_warehouse_code'] = $src_warehouse_code;
        $param['dtz_warehouse_code'] = $dtz_warehouse_code;
        $param['src_platform_code'] = '';
        $param['dtz_platform_code'] = '';

        //调用接口获取数据
        //$url = 'http://www.stock.com/Api/Order/Order/xncStockAllot';
        $url = Yii::$app->params['wms_domain'] . '/Api/Order/Order/xncStockAllot';//正式环境

        $curl  = new curl\Curl();
        $token = Json::encode(Vhelper::stockAuth());
        $data = $curl->setPostParams([
            'token' => $token,
            'stock_info'=>Json::encode([$param])
        ])->post($url);

        //记录请求日志
        $status='no-status';
        $data = Json::decode($data,true);
        if($data && $data['error']!=-1){
            $status='success';
        }else{
            $status='error';
        }
        Yii::$app->db->createCommand()->insert(ApiRequestLog::tableName(),['post_content'=>Json::encode($param),
            'response_content'=>serialize($data),'create_time'=>date('Y-m-d H:i:s',time()),'api_url'=>'Api/Order/Order/xncStockAllot','status'=>$status])->execute();

        return $data;
    }
}
