<?php

namespace app\api\v1\controllers;
use app\api\v1\models\ExcepReturnInfo;
use app\api\v1\models\ExchangeGoods;
use app\api\v1\models\PurchaseAbnomal;
use app\api\v1\models\PurchaseQc;
use app\api\v1\models\PurchaseReceive;
use app\api\v1\models\ReturnGoods;
use app\api\v1\models\PurchaseWarehouseAbnormal;
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
class PurchaseExceptionController extends BaseController
{
    public $modelClass = 'app\api\v1\models\PurchaseAbnomal';

    /**
     * 接收到货异常入库
     * @return mixed
     */
    public function actionCreateAbnomal()
    {

        $datas  = Yii::$app->request->post()['deliveryAbnormal'];
        if(isset($datas) && !empty($datas))
        {
            $datas = Json::decode($datas);
            $m     = $this->modelClass;
            $data  = $m::FindOnes($datas);

            return $data;
        } else {

            return '没有任何的数据过来！';
        }
    }

    /**
     * 接收收货异常与qc异常入库
     * @return mixed
     */
    public function  actionCreateReceiveQc()
    {

        $datas  = Yii::$app->request->post()['deliveryAbnormal'];
        if(isset($datas) && !empty($datas))
        {
            $datas  = Json::decode($datas);
            $m      = new PurchaseReceive();
            $data   = $m::FindOnes($datas);

            return $data;
        } else {
            return '没有任何的数据过来！';
        }

    }

    /**
     * 推送采购到货异常处理结果至数据中心
     */
    public  function  actionPushAbnomal()
    {
        $curl = new curl\Curl();
        $limit = (int)Yii::$app->request->get('limit');
        if ($limit <= 0 || $limit > 1000)
            $limit = 1000;
        $query = PurchaseAbnomal::find()->select(['express_no','note','wms_id'])->where(['is_push' => 0,'status'=>4])->asarray()->limit($limit)->all();
        try {
            $url = Yii::$app->params['server_ip'] . '/index.php/purchases/purchaseAbnormalUpdateToMysql';
            $s = $curl->setPostParams([
                'deliveryAbnormal' => Json::encode($query),
                'token'    => Json::encode(Vhelper::stockAuth()),
            ])->post($url);

            //验证json
            $sb = Vhelper::is_json($s);
            if(!$sb)
            {
                echo '请检查json'."\r\n";
                exit($s);
            } else {
                $_result = Json::decode($s);
                if ($_result['success_list'] && !empty($_result['success_list']))
                {
                    if (!empty($_result['success_list']['wms_id']))
                    {
                        foreach ($_result['success_list']['wms_id'] as $v)
                        {

                            PurchaseAbnomal::updateAll(['is_push' => 1], 'wms_id = :wms_id', [':wms_id' =>$v]);


                        }
                    } else {
                        if(!empty($_result['success_list']['express_no']))
                        {
                            foreach ($_result['success_list']['express_no'] as $v) {

                                PurchaseAbnomal::updateAll(['is_push' => 1], 'express_no = :express_no', [':express_no' => $v]);


                            }
                        }
                    }


                } else {
                    Vhelper::dump($s);
                }
            }
        } catch (Exception $e) {
            exit('发生了错误');
        }
    }

    /**
     * 推送收货异常处理结果和qc异常处理结果至数据中心
     */
    public  function  actionPushReceiveQc()
    {
        $curl = new curl\Curl();
        $limit = (int)Yii::$app->request->get('limit');
        if ($limit <= 0 || $limit > 1000)
            $limit = 1000;
        $qc      = PurchaseQc::find()->select(['express_no','note_center','sku','pur_number','name','buyer','warehouse_code','bad_products_qty','created_at','creator','qc_id','type','is_receipt'])->where(['is_push' => 0,'qc_status'=>['3','2']])->asarray()->limit($limit)->all();
        $receive = PurchaseReceive::find()->select(['express_no','note_center','sku','pur_number','name','type','buyer','warehouse_code','created_at','creator','qc_id','is_receipt'])->where(['is_push' => 0,'receive_status'=>2])->asarray()->limit($limit)->all();
        $query   = yii\helpers\ArrayHelper::merge($qc,$receive);
        //$query   = $receive;
        try {
            $url = Yii::$app->params['server_ip'] . '/index.php/purchases/purchaseQualityControlUpdateToMysql';
            $s   = $curl->setPostParams([
                'deliveryAbnormal' => Json::encode($query),
                'token'    => Json::encode(Vhelper::stockAuth()),
            ])->post($url);
            //验证json
            $sb = Vhelper::is_json($s);
            if(!$sb)
            {
                echo '请检查json'."\r\n";
                exit($s);
            } else {
                    $_result = Json::decode($s);
                    if ($_result['success_list'] && !empty($_result['success_list'])) {
                        foreach ($_result['success_list'] as $v) {
                            if ($v['type'] == 1) {
                                PurchaseReceive::updateAll(['is_push' => 1], 'qc_id = :qc_id', [':qc_id' => $v['qc_id']]);
                            } else {
                                PurchaseQc::updateAll(['is_push' => 1], 'qc_id = :qc_id', [':qc_id' => $v['qc_id']]);
                            }

                        }

                    } else {
                        Vhelper::dump($s);
                    }
            }
        } catch (Exception $e) {

            exit('发生了错误');
        }
    }

    /**
     * 推送采购换货至数据中心
     */
    public  function  actionPushReplacement()
    {
        $curl = new curl\Curl();
        $limit = (int)Yii::$app->request->get('limit');
        if ($limit <= 0 || $limit > 1000)
            $limit = 1000;
        $query = ExchangeGoods::find()->where(['is_push' => 0,'state'=>1])->asarray()->limit($limit)->all();
        try {
            $url = Yii::$app->params['server_ip'] . '/index.php/purchases/purchaseProductExchangeToMysql';
            $s = $curl->setPostParams([
                'productExchange' => Json::encode($query),
                'token'    => Json::encode(Vhelper::stockAuth()),
            ])->post($url);
            $_result = Json::decode($s);
            if ($_result['success_list'] && !empty($_result['success_list'])) {
                foreach ($_result['success_list'] as $v) {
                    ExchangeGoods::updateAll(['is_push' => 1],['exchange_number' => $v['exchange_number']]);
                }

            } else {
                Vhelper::dump($s);
            }
        } catch (Exception $e) {

            exit('发生了错误');
        }
    }

    /**
     * 接受仓库返回的换货数据
     */
    public function  actionExReturn()
    {
        $datas  = Yii::$app->request->post()['replacement'];
        if(isset($datas) && !empty($datas))
        {
            $datas  = Json::decode($datas);
            $m      = new ExchangeGoods();
            $data   = $m::FindOnes($datas);

            return $data;
        } else {
            return '没有任何的数据过来！';
        }
    }

    /**
     * 推送采购退货处理结果至数据中心
     */
    public  function  actionPushReturnGoods()
    {
        $curl = new curl\Curl();
        $limit = (int)Yii::$app->request->get('limit');
        if ($limit <= 0 || $limit > 1000)
            $limit = 1000;
        $query = ReturnGoods::find()->where(['is_push' => 0,'state'=>1])->asarray()->limit($limit)->all();
        try {
            $url = Yii::$app->params['server_ip'] . '/index.php/purchases/purchaseProductReturnToMysql';
            $s = $curl->setPostParams([
                'productReturn' => Json::encode($query),
                'token'    => Json::encode(Vhelper::stockAuth()),
            ])->post($url);
            $_result = Json::decode($s);

            if ($_result['success_list'] && !empty($_result['success_list']))
            {
                foreach ($_result['success_list'] as $v)
                {
                    ReturnGoods::updateAll(['is_push' => 1],['return_number' => $v['return_number']]);
                }

            } else {
                Vhelper::dump($s);
            }
        } catch (Exception $e) {

            exit('发生了错误');
        }
    }

    /**
     * 接受仓库返回的退货数据
     */
    public function actionReGoods()
    {
        $datas  = Yii::$app->request->post()['returngoods'];
        if(isset($datas) && !empty($datas))
        {
            $datas  = Json::decode($datas);
            $m      = new ReturnGoods();
            $data   = $m::FindOnes($datas);

            return $data;
        } else {
            return '没有任何的数据过来！';
        }
    }

    // 接收仓库异常信息
    public function actionPullWarehouseExp()
    {
        $data = Yii::$app->request->post('quality_abnormal_data');
        if(isset($data) && !empty($data))
        {
            $datass = Json::decode($data);
            $res = PurchaseWarehouseAbnormal::FindOnes($datass);
            return $res;
        } else {
            return ['text' => '没有任何的数据过来！'];
        }
    }

    // 定时拉取仓库异常处理结果
    public function actionGetWarehouseResult()
    {
        $ids = PurchaseWarehouseAbnormal::find()
            ->select('defective_id')
            ->where(['in', 'is_handler', [1, 2]])
            ->andWhere(['in', 'is_push_to_warehouse', [0, 1]])
            ->limit(50)
            ->asArray()
            ->column();
        if(empty($ids)) {
            exit('没有数据了');
        }
        $curl = new curl\Curl();
        try {
            $url = 'http://wms.yibainetwork.com/Api/Purchase/QualityAbnormal/getDefectiveData';
            $s = $curl->setPostParams([
                'defective_id_list' => Json::encode($ids),
                'token' => Json::encode(Vhelper::stockAuth()),
            ])->post($url);
            $_result = Json::decode($s);
            if(isset($_result['success']) && !empty($_result['success'])) {
                $models = PurchaseWarehouseAbnormal::find()->where(['in', 'defective_id', $_result['success']])->all();
                foreach($models as $model) {
                    $model->is_handler = 1;
                    $model->is_push_to_warehouse = 2;
                    $model->warehouse_handler_result = '仓库已处理';
                    $a = $model->save(false);
                    var_dump($a);
                }
            } else {
                Vhelper::dump($s);
            }
        } catch (\Exception $e) {
            Vhelper::dump($e->getMessage());
        }
    }

    //拉取异常图片
    public function actionGetWarehouseImages(){
        $ids = PurchaseWarehouseAbnormal::find()
            ->select('defective_id')
            ->where('isnull(img_path_data)')
            ->orWhere(['img_path_data'=>''])
            ->limit(50)
            ->asArray()
            ->column();
        $curl = new curl\Curl();
        $url = 'http://wms.yibainetwork.com/Api/Purchase/QualityAbnormal/getDefectiveImg';
        $s = $curl->setPostParams([
            'defective_id' => Json::encode($ids),
            'token' => Json::encode(Vhelper::stockAuth()),
        ])->post($url);
        $_result = Json::decode($s);
        if(isset($_result['status_list']) && !empty($_result['status_list'])) {
            foreach ($_result['status_list'] as $key=>$value){
                PurchaseWarehouseAbnormal::updateAll(['img_path_data'=>json_encode($value)],
                    ['defective_id'=>$key,'img_path_data'=>'']);
            }
        }
    }

    //仓库驳回数据
    public function actionReject(){
        $result = ['status'=>0 , 'msg'=>'', 'data'=>[], 'fail'=>[]];
        try {
            //接收驳回的异常单
            $data = Yii::$app->request->post();

            if(empty($data)){
                $result['msg'] = '数据不存在，请检查';
                die(Json::encode($result));
            }

            $defective_ids = [];
            $fail_ids = [];
            foreach($data as $k => $v) {
                $model = PurchaseWarehouseAbnormal::find()->where(['defective_id' => $v['defective_id']])->one();
                if(!empty($model)) {
                    $model->is_handler = 0; // 是否处理 因为是驳回的数据  需要采购重新处理
                    $model->is_push_to_warehouse = 0; // 处理推送至仓库
                    $model->warehouse_handler_result = !empty($v['reburt_reson'])?"驳回:".$v['reburt_reson']:'驳回';

                    //记录标记成功的异常单号
                    $res = $model->save(false);
                    if($res && !in_array($v['defective_id'],$defective_ids)){
                        $defective_ids[] = $v['defective_id'];
                    }else{
                        if(!in_array($v['defective_id'],$fail_ids)){
                            $fail_ids[] = $v['defective_id'];
                        }
                    }
                }else{
                    if(!in_array($v['defective_id'],$fail_ids)){
                        $fail_ids[] = $v['defective_id'];
                    }
                }
            }

            $result['status'] = 1;
            $result['data'] = $defective_ids;
            $result['fail'] = $fail_ids;
            die(Json::encode($result));
        } catch(\Exception $e) {
            /*echo '错误信息：';
            Vhelper::dump($e->getMessage());*/
            die(Json::encode($e->getMessage()));
        }

    }

    /**
     * 获取仓库退货信息
     */
    public function actionGetExcepReturnInfo(){
        $data = Yii::$app->request->getBodyParam('data');
        $infoSave = json_encode(['successList'=>[],'failList'=>[]]);
        if (!empty($data)){
            $infoSave = ExcepReturnInfo::saveData(json_decode($data));
        }
        echo $infoSave;
        Yii::$app->end();
    }
}
