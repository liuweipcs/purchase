<?php
namespace app\api\v1\controllers;
use app\api\v1\models\PurchaseOrderPay;
use app\config\Vhelper;
use app\models\UfxFuiou;
use app\models\UfxfuiouPayDetail;
use app\models\UfxfuiouRequestLog;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/21
 * Time: 18:33
 */
class UfxFuiouController extends BaseController{

    public function actionAcceptResult(){
        $reponseStr = \Yii::$app->request->getBodyParam('reqStr','');
        $model = new UfxfuiouRequestLog();
        $model->create_time = date('Y-m-d H:i:s',time());
        $model->create_user_name = '富友银行卡转账回调';
        $model->request_response = !empty($reponseStr) ? $reponseStr :'无回调数据';
        $model->post_params = '接受回调数据';
        $model->type        = 2;
        if(empty($reponseStr)) {
            $model->pur_tran_no = '无交易流水号';
            $model->save();
            exit('请求参数不能为空');
        }
        $ufxiou = new UfxFuiou();
        if($ufxiou->checkResponse($reponseStr)){
            $responseBody = UfxFuiou::getXmlStrByTag($reponseStr,'body',0);
            $responseBodyArray = $ufxiou->xml_unserialize($responseBody);
            //通知类型04：转账到银行卡，划款结果通知；08失败
            $notifyType  = isset($responseBodyArray['notifyType']) ? $responseBodyArray['notifyType'] :'04';
            $model->pur_tran_no = isset($responseBodyArray['notify'.$notifyType]['eicSsn'])&&!empty($responseBodyArray['notify'.$notifyType]['eicSsn']) ? $responseBodyArray['notify'.$notifyType]['eicSsn'] :'无交易流水号';
            $model->save();
            PurchaseOrderPay::savePayResult($responseBodyArray);
        }else{
            $model->pur_tran_no = '回调数据没有通过验证';
            $model->save();
            exit('数据验证失败');
        }
    }

    public function actionGetPayBack(){
        \Yii::$app->response->format = 'raw';
        $datas = UfxfuiouPayDetail::find()->where(['pay_status'=>'5005','is_get_back'=>0])->asArray()->all();
        if(empty($datas)){
            exit('没有需要获取的电子回单');
        }
        $fail=[];
        $success=[];
        foreach ($datas as $data){
            if(empty($data['ufxfuiou_tran_num'])){
                continue;
            }
            $response = UfxFuiou::getPayBack($data['ufxfuiou_tran_num']);
            if($response['status']==false){
                $fail[]=$data['ufxfuiou_tran_num'];
                Vhelper::ToMail($data['ufxfuiou_tran_num'].':'.$response['message'],'付款回单抓取失败');
                continue;
            }
            $success[] = $data['ufxfuiou_tran_num'];
        }
        \Yii::$app->end(json_encode(['success'=>$success,'fail'=>$fail]));
    }
}