<?php
namespace app\api\v1\controllers;

use linslin\yii2\curl;
use app\models\Product;
use app\models\ProductSupplierChange;
use app\models\ProductSupplierChangeSearch;
use Yii;
use yii\filters\VerbFilter;
use app\models\ChangeLog;
use app\services\BaseServices;
use app\models\SkuSalesStatistics;



use app\api\v1\models\ArrivalRecord;
use app\api\v1\models\CostPurchaseNum;
use app\api\v1\models\ProductProvider;
use app\api\v1\models\PurchaseOrder;
use app\api\v1\models\PurchaseOrderItems;
use app\api\v1\models\Supplier;
use app\api\v1\models\SupplierKpiCaculte;
use app\api\v1\models\SupplierProposalResult;
use app\api\v1\models\SupplierProposalTemplate;
use app\api\v1\models\SupplierQuotes;
use app\api\v1\models\WarehouseResults;
use app\models\PurchaseCancelQuantity;
use app\models\PurchaseOrderCancel;
use app\models\PurchaseOrderCancelSub;
use app\models\PurchaseOrderRefundQuantity;
use app\models\SupplierProductLine;
use app\models\SupplierSettlementLog;
use app\models\SupplierUpdateApply;
use app\services\SupplierGoodsServices;
use app\config\Vhelper;
use yii\helpers\Json;
use app\models\TablesChangeLog;
use yii\helpers\ArrayHelper;
use app\models\SupplierCheck;
use app\models\SupplierCheckSearch;
use app\models\SupplierCheckNote;

/**
 * API：sku屏蔽申请列表 控制器
 * Class ProductRepackageController
 * @package app\controllers
 */
class ProductSupplierChangeController extends BaseController
{

    public static function getPath(){
        $filePath       = Yii::$app->basePath.'/web/files/';
        return $filePath;
    }

    /**
     * 推送 SKU屏蔽列表数据到ERP
     * @return mixed
     */
    public function actionPushToErp()
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        $start_time = time();

        ProductSupplierChangeSearch::autoCompleteInfo();// 自动完成缺失信息

        $query = ProductSupplierChange::find()
            ->where(['push_erp' => 0])
            ->andWhere(['!=','status',70])
            ->andWhere(['>','status',10]);
        $count = clone $query;
        $count = $count->count();

        echo '<pre/>';
        if($count > 0){
            $curl   = new curl\Curl();
            $url    = Yii::$app->params['ERP_URL'] . '/services/products/product/getskuscreen';
            $list   = $query->limit(300)->all();

            $push_list_data = [];
            foreach($list as $list_value){
                $productInfo    = Product::findOne(['sku' => $list_value->sku]);

                $push_data                              = [];
                $push_data['id']                        = $list_value->id;
                $push_data['sku']                       = $list_value->sku;
                $push_data['title']                     = isset($productInfo->desc)?$productInfo->desc->title:'';
                $push_data['create_id']                 = $list_value->productInfo->create_id;
                $push_data['current_supplier_code']     = $list_value->old_supplier_code;
                $push_data['current_supplier_name']     = BaseServices::getSupplierName($list_value->old_supplier_code);
                $push_data['current_price']             = $list_value->old_price;

                $push_data['days_sales_30'] = SkuSalesStatistics::find()
                                ->select('sum(days_sales_30) as days_sales_30')
                                ->andFilterWhere(['sku' => $list_value->sku])
                                ->scalar();
                $push_data['apply_user']    = $list_value->apply_user;
                $push_data['apply_time']    = $list_value->apply_time;
                $push_data['apply_remark']  = $list_value->apply_remark;
                $push_data['deadline']      = $list_value->deadline;

                $push_list_data[] = $push_data;
            }
//            print_r($push_list_data);exit;

            try {
                // 执行推送数据
                $s = $curl->setPostParams([
                      'block_list' => Json::encode($push_list_data),
                      'token'    => Json::encode(Vhelper::stockAuth())
                  ])->post($url);

                //file_put_contents(self::getPath().'ProductSupplierChange_001.txt',$s);

                //验证json
                $sb = Vhelper::is_json($s);
                if(!$sb)
                {
                    echo '请检查json'."\r\n";
                    Vhelper::dump($s);
                } else {
                    $_result = Json::decode($s);
                    if (isset($_result['ids']) && !empty($_result['ids'])) {
                        foreach ($_result['ids'] as $value_id) {
                            if($value_id){
                                $model_change            = ProductSupplierChange::findOne(['id' => $value_id]);
                                $model_change->push_erp  = 1;// 推送结果
                                $res = $model_change->save();
                                if($res){
                                    $content = '推送记录成功';
                                    $data = [
                                        'oper_id'       => $model_change->id,
                                        'oper_type'     => 'ProductSupplierChange',
                                        'content'       => $content,
                                        'is_show'       => 2,
                                    ];
                                    ChangeLog::addLog($data);
                                }
                            }
                        }

                        $push_count = count($push_list_data);
                        $success    = count($_result['ids']);
                        echo "总共[$count]个，本次推送[$push_count]个，推送成功[$success]个数 ";
                    } else {
                        Vhelper::dump($s);
                    }
                }
            } catch (\Exception $e) {
                exit('发生了错误');
            }
            echo '推送结束，耗时: '.(time()-$start_time).' 秒';
        }else{
            exit('没有需要推送的数据');
        }

        exit;
    }


    /**
     * 接收 EPR推送过来的 SKU屏蔽列表 操作结果
     */
    public function actionReceiveBlockResult(){
        $data       = Yii::$app->request->post();
        //file_put_contents(self::getPath().'ProductSupplierChange_002.txt',$data);

        $block_list = isset($data['screen'])?$data['screen']:'';
        $block_list = json_decode($block_list,true);

        $result_arr = [// ERP状态列表
            1 => '待处理',
            2 => '替换供应商',
            3 => '新供应商审核失败',
            4 => '新供应商审核通过',
            5 => '同意停售',
            6 => '已驳回',
            7 => '系统屏蔽',
            8 => '同意屏蔽',
        ];

        $status_flag_arr = [
            '5' => 'M',// 人工屏蔽（同意停售）
            '6' => 'D',// 开发驳回
            '7' => 'S',// 系统屏蔽
            '8' => 'PB',// 同意屏蔽
        ];

        $success_list = [];
        if($block_list){
            foreach($block_list as $list_value){
                if(!isset($list_value['id']) OR !isset($list_value['processing_status'])) continue;// 必须具有的值

                $id                     = $list_value['id'];
                $sku                    = $list_value['sku'];

                $model                  = ProductSupplierChange::findOne(['id' => $id]);
                if(empty($model)) continue;

                // ERP 操作结果
                // 开发审核四种可选状态：开发驳回、人工屏蔽、系统屏蔽、替换供应商
                $block_result           = isset($list_value['processing_status'])?trim($list_value['processing_status']):'';
                $model->erp_result      = isset($result_arr[$block_result])?$result_arr[$block_result]:$block_result;// 操作结果
                $model->erp_oper_time   = (isset($list_value['modify_time']) and $list_value['modify_time'])?trim($list_value['modify_time']):date('Y-m-d H:i:s');// 操作时间（默认为当前时间）

                (isset($list_value['modify_user'])   AND $list_value['modify_user'])   AND $model->erp_oper_user = trim($list_value['modify_user']);
                (isset($list_value['refuse_reason']) AND $list_value['refuse_reason']) AND $model->erp_remark    = trim($list_value['refuse_reason']);

                // 替换供应商的信息
                $new_supplier_name      = isset($list_value['provider_new'])?$list_value['provider_new']:'';
                $new_price              = isset($list_value['price_new'])?$list_value['price_new']:'';

                // 开发审核三种可选状态：开发驳回、人工屏蔽、系统屏蔽、替换供应商
                if(in_array($block_result,['5','6','7','8'])){// 开发驳回，系统屏蔽，人工屏蔽
                    $model->status      = 70;// 70.已结束
                    $model->status_flag = isset($status_flag_arr[$block_result])?$status_flag_arr[$block_result]:'';

                }elseif($block_result == '2'){// 替换供应商
                    $model->status      = 30;// 30.开发审核通过
                    if($new_supplier_name){// 设置替换供应商，状态变为 待采购确认
                        $model->new_supplier_name   = $new_supplier_name;
                        $model->new_supplier_code   = Supplier::find()->select('supplier_code')->where(['supplier_name' => $new_supplier_name])->scalar();
                        $model->new_price           = $new_price;
                        $model->status              = 50;// 50.待采购确认
                        $model->status_flag         = '';
                    }
                }else{
                    continue;
                }

                $update_content = TablesChangeLog::updateCompare($model->attributes, $model->oldAttributes);// 比较差异 在保存前有效哦
                $res = $model->save();

                $sourceStatus = '';
                // 货源状态更新 判断
                if($block_result == '5'){
                    $sourceStatus = '2';// 同意停售的  货源状态为停产
                }elseif($block_result == '8'){
                    $sourceStatus = '3';// 同意屏蔽的  货源状态为断货
                }elseif($block_result == '7' AND !empty($model->apply_remark)){
                    // 系统屏蔽的根据 申请备注来判断（备注为 停产就是停产   备注为断货是断货）
                    if($model->apply_remark == '停产'){
                        $sourceStatus = '2';
                    }elseif($model->apply_remark == '断货'){
                        $sourceStatus = '3';
                    }
                }

                $data = [
                    'oper_id'       => $id,
                    'oper_type'     => 'ProductSupplierChange',
                    'is_show'       => 1,
                ];
                // 保存结果
                if($res){
                    if($sourceStatus){// 更新货源状态
                        $res_up = Product::changeSourceStatusBySku([['sku'=>$sku,'source_status'=>$sourceStatus]]);
                        if($res_up){
                            $data['content'] = "更新货源状态成功[{$sourceStatus}]";
                        }else{
                            $data['content'] = "更新货源状态失败[{$sourceStatus}]";
                        }
                        ChangeLog::addLog($data);
                    }
                    $success_list[] = $id;// 返回成功结果
                    $content        = '接收 ERP 开发审核结果成功';
                }else{
                    $update_content = '';
                    $content        = '接收 ERP 开发审核结果失败';
                    if(empty($model)){ $content .= '没有找到记录';}
                }
                $data['content']     = $content;
                $data['update_data'] = $update_content;
                ChangeLog::addLog($data);
            }

            $param = ['status' => 'success','message' => '','success_list' => $success_list];
            echo Json::encode($param);
            exit;
        }else{

            $param = ['status' => 'error','message' => 'screen 数据缺失'];
            echo Json::encode($param);
            exit;
        }
    }


    /**
     * 推送 SKU屏蔽列表数据审核结果到ERP
     */
    public function actionPushSupplierCheckResult(){
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        $start_time = time();

        // 查询需要同步 供应商审核结果的 最新一条记录
        $query = ChangeLog::find()
            ->where(['oper_type' => 'ProductSupplierChange'])
            ->andWhere(['content' => 'SUPPLIER_CHECK_RESULT']);
        $count = clone $query;
        $count = $count->count();

        if($count > 0){
            $curl   = new curl\Curl();
            $url    = Yii::$app->params['ERP_URL'] . '/services/products/product/getprovidercheckinfo';
            $list   = $query->limit(300)->all();

            $push_list_data = [];
            foreach($list as $list_value){
                $model = ProductSupplierChange::findOne(['id' => $list_value->oper_id]);

                if($model){
                    $push_data                              = [];
                    $push_data['id']                        = $model->id;
                    $push_data['sku']                       = $model->sku;

                    $update_data = $list_value->update_data;// 审核结果信息
                    if( strtoupper($update_data) == 'AGREE'){// 同意替换供应商
                        $push_data['affirm_status']         = 'AGREE';
                        $push_data['new_supplier_code']     = $model->new_supplier_code;
                        $push_data['new_supplier_name']     = $model->new_supplier_name;
                    }else{
                        $push_data['affirm_status']         = 'DISAGREE';
                        $push_data['affirm_user']           = $model->affirm_user;
                        $push_data['reject_reason']         = str_ireplace('DISAGREE:','',$update_data);// 驳回原因
                    }

                    $push_list_data[$model->id] = $push_data;
                }else{
                    ChangeLog::deleteAll(['oper_id' => $list_value->oper_id,'oper_type' => 'ProductSupplierChange']);//删除记录
                }
            }
//            print_r($push_list_data);exit;

            try {
                // 执行推送数据
                $s = $curl->setPostParams(['block_list' => Json::encode($push_list_data),'token' => Json::encode(Vhelper::stockAuth())])->post($url);
                //file_put_contents(self::getPath().'ProductSupplierChange_003.txt',$s);

                //验证json
                $sb = Vhelper::is_json($s);
                if(!$sb)
                {
                    echo '请检查json'."\r\n";
                    Vhelper::dump($s);
                } else {
                    $_result = Json::decode($s);
                    if (isset($_result['ids']) && !empty($_result['ids'])) {
                        foreach ($_result['ids'] as $value_id) {
                            if($value_id){
                                $content = '推送记录成功';
                                $data  = [
                                    'oper_id'       => $value_id,
                                    'oper_type'     => 'ProductSupplierChange',
                                    'content'       => $content,
                                    'update_data'   => isset($push_list_data[$value_id])?$push_list_data[$value_id]:'',
                                    'is_show'       => 2,
                                ];
                                if(ChangeLog::addLog($data)){
                                    ChangeLog::deleteAll(['oper_id' => $value_id,'oper_type' => 'ProductSupplierChange','content' => 'SUPPLIER_CHECK_RESULT']);
                                }
                            }
                        }

                        $push_count = count($push_list_data);
                        $success    = count($_result['ids']);
                        echo "总共[$count]个,本次推送[$push_count]个，推送成功[$success]个数 ";
                    } else {
                        Vhelper::dump($s);
                    }
                }
            } catch (\Exception $e) {
                exit('发生了错误');
            }
            echo '推送结束，耗时: '.(time()-$start_time).' 秒';
        }else{
            exit('没有需要推送的数据');
        }

        exit;
    }




}
