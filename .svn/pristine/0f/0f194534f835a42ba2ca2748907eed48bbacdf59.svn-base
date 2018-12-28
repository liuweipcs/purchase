<?php

namespace app\controllers;

use app\config\Vhelper;
use app\models\AdminLog;
use Yii;
use yii\web\Controller;
use yii\helpers\Url;
use BCGColor;
use BCGDrawing;
use BCGcode128;
/**
 *                             _ooOoo_
 *                            o8888888o
 *                            88" . "88
 *                            (| -_- |)
 *                            O\  =  /O
 *                         ____/`---'\____
 *                       .'  \\|     |//  `.
 *                      /  \\|||  :  |||//  \
 *                     /  _||||| -:- |||||-  \
 *                     |   | \\\  -  /// |   |
 *                     | \_|  ''\---/''  |   |
 *                     \  .-\__  `-`  ___/-. /
 *                   ___`. .'  /--.--\  `. . __
 *                ."" '<  `.___\_<|>_/___.'  >'"".
 *               | | :  `- \`.;`\ _ /`;.`/ - ` : | |
 *               \  \ `-.   \_ __\ /__ _/   .-` /  /
 *          ======`-.____`-.___\_____/___.-`____.-'======
 *                             `=---='
 *          ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
 *                     佛祖保佑        永无BUG
 * User: ztt
 * Date: 2017/3/23 0023
 * Time: 18:41
*/
class BaseController extends Controller
{

//    public function init(){
//        /* 判断是否登录 */
//        if (\Yii::$app->user->getIsGuest()) {
//            $this->redirect(Url::toRoute(['/site/login']));
//            Yii::$app->end();
//        }
//
//    }
    /**
     * ---------------------------------------
     * 在执行所有动作之前，先执行这个方法。用于权限验证
     * @param \yii\base\Action $action
     * @return bool true-继续执行/false-终止执行
     * ---------------------------------------
     */
    public function beforeAction($action)
    {

        AdminLog::addLog($action);
        //parent::beforeAction($action);
        /* 超级管理员允许访问任何页面 */
        if(Yii::$app->params['admin'] == Yii::$app->user->id){
            return true;
        }
        if (!parent::beforeAction($action)) {
            return false;
        } else {
            return true;
        }
//        $controller = Yii::$app->controller->id;
//        $action = Yii::$app->controller->action->id;
//        $permissionName = '/'.$controller.'/'.$action;
//        if(!\Yii::$app->user->can($permissionName) && Yii::$app->getErrorHandler()->exception === null){
//            throw new \yii\web\ForbiddenHttpException('对不起，您现在还没获此操作的权限');
//        } else {
//            return true;
//        }

    }


    /**
     * 通过采购单号得出条形码
     * @param $codes
     * @return mixed
     */
    public function  BarCode($codes)
    {
        $colorFront = new BCGColor(0, 0, 0);
        $colorBack = new BCGColor(255, 255, 255);

        // Barcode Part
        $code = new BCGcode128();
        $code->setScale(2);
        $code->setColor($colorFront, $colorBack);
        $code->parse($codes);

        
        // Drawing Part
        $drawing = new BCGDrawing('', $colorBack);
        $drawing->setBarcode($code);
        $drawing->draw();

        header('Content-Type: image/png');

        $drawing->finish(BCGDrawing::IMG_FORMAT_PNG);

    }
    
    /**
     * @desc 转换数组格式
     * 转换前：
     * 'BasicTactics' => 
    array (size=5)
      'type' => 
        array (size=2)
          0 => string 'wave_up' (length=7)
          1 => string 'wave_down' (length=9)
      'days_3' => 
        array (size=2)
          0 => string '1' (length=1)
          1 => string '1' (length=1)
      'days_7' => 
        array (size=2)
          0 => string '1' (length=1)
          1 => string '1' (length=1)
      'days_14' => 
        array (size=2)
          0 => string '11' (length=2)
          1 => string '1' (length=1)
      'days_30' => 
        array (size=2)
          0 => string '1' (length=1)
          1 => string '1' (length=1)
     * 
     * 转换后
     * 'BasicTactics' => 
    array (size=2)
      0 => 
        array (size=5)
          'type' => string 'wave_up' (length=7)
          'days_3' => string '1' (length=1)
          'days_7' => string '1' (length=1)
          'days_14' => string '1' (length=1)
          'days_30' => string '1' (length=1)
      1 => 
        array (size=5)
          'type' => string '1' (length=7)
          'days_3' => string '1' (length=1)
          'days_7' => string '1' (length=1)
          'days_14' => string '1' (length=1)
          'days_30' => string '1' (length=1)
     * 
     *@author Jimmy
     *@date 2017-04-01 15:53:11
     */
    public function changeData($arr=[])
    {
        $data=[];//拼装后的数组
        foreach ($arr as $key=>$val){
            foreach ($val as $k=>$v){
                $data[$k][$key]=$v;
            }
        }
        return $data;
    }





}