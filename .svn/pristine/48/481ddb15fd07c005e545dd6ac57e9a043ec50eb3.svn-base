<?php

namespace app\api\v1\controllers;
use yii;
use app\config\Vhelper;
use linslin\yii2\curl;
use yii\helpers\Json;
use yii\data\Pagination;
use app\api\v1\models\DeclareCustoms;
/**
 * 报关数量
 */
class DeclareCustomsController extends BaseController
{
    /**
     * 获取报关信息
     * http://caigou.yibainetwork.com/v1/declare-customs/get-customs-details
     */
    public function actionGetCustomsDetails()
    {
        $purFba = $_REQUEST['purFba'];
        if(isset($purFba) && !empty($purFba))
        {
            $data = Json::decode($purFba);
            $res = DeclareCustoms::FindOnes($data);
            return $res;
        } else {
            return ['text' => '没有任何的数据过来！'];
        }
    }
}
