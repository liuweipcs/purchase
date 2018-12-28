<?php
namespace app\controllers;
use app\models\FbaStatisticSearch;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/23
 * Time: 15:28
 */

class FbaStatisticsController extends BaseController{


    public function actionIndex(){
        return $this->render('index');
    }



    public function actionGetData(){
        $beginTime = \Yii::$app->request->getBodyParam('begin_time',date('Y-m-d 00:00:00',strtotime("last month")));
        $endTime = \Yii::$app->request->getBodyParam('end_time',date('Y-m-d 00:00:00',time()));
        $datas = FbaStatisticSearch::search($beginTime,$endTime);
        echo $datas;
        \Yii::$app->end();
    }

    public function actionGetDemand(){
        $time = \Yii::$app->request->getBodyParam('time',date('Y-m-d 00:00:00',time()));
        $datas = FbaStatisticSearch::searchDemand($time);
        echo $datas;
        \Yii::$app->end();
    }

    public function actionGetOrder(){
        $time = \Yii::$app->request->getBodyParam('time',date('Y-m-d 00:00:00'),time());
        $datas = FbaStatisticSearch::searchOrder($time);
        echo $datas;
        \Yii::$app->end();
    }
}