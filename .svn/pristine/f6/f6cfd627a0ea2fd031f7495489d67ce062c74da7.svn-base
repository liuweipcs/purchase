<?php

namespace app\api\v1\controllers;
use Yii;
use yii\helpers\Json;
use app\api\v1\models\UebExpressReceipt;

class UebExpressReceiptController extends BaseController
{

    public function actionPullExpressReceipt()
    {
        $data = Yii::$app->request->post('express_receipt_data');
        if(isset($data) && !empty($data))
        {
            $datass = Json::decode($data);
            $res = UebExpressReceipt::FindOnes($datass);
            return $res;
        } else {
            return ['text' => '没有任何的数据过来！'];
        }
    }



}
