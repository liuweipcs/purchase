<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/20
 * Time: 19:40
 */

namespace app\controllers;

use app\config\Vhelper;
use Yii;
use app\services\BaseServices;
use app\services\CommonServices;
use yii\base\ErrorException;
use yii\db\Connection;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

use yii\web\Controller;
use yii\db\Migration;
use yii\db\Schema;
use app\models\OverseasCheckPriv;
use app\models\TablesChangeLog;

class OverseasCheckPrivController extends BaseController
{
    /**
     * 首页
     */
    public function actionIndex()
    {
        $datalist = OverseasCheckPriv::find()->all();
        if (Yii::$app->request->isPost) {
            $prices = Yii::$app->request->post('prices');
            foreach ($datalist as $v) {
                $price = isset($prices[$v->id]) ? max(0,intval($prices[$v->id])) : 0;
                $v->update_time = date('Y-m-d H:i:s');
                if ($v->price != $price) {
                    $log_message = "id[{$v->id}],price:{$v->price}=>{$price}";
                    $logData = ['table_name'=>OverseasCheckPriv::tableName(),'change_type'=>2,'change_content'=>$log_message];
                    $v->price = $price;
                    $v->save();
                    TablesChangeLog::addLog($logData);
                }
            }
        
            Yii::$app->getSession()->setFlash('success',"修改成功！",true);
            return $this->redirect(['index']);
        }
        return $this->render('index', ['data'=>$datalist]);
    }    
}