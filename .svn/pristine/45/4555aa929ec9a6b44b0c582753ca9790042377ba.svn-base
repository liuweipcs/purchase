<?php

namespace app\controllers;

use Yii;
use yii\models;  
use app\models\LogisticsImport;
use app\models\LogisticsImportSearch;
use app\models\User;
use app\controllers\BaseController;
use yii\filters\VerbFilter;
use m35\thecsv\theCsv;
use app\config\Vhelper;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\Json;
use linslin\yii2\curl;
use yii\web\UploadedFile;
use yii\web\ConflictHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
/**
 * Created by PhpStorm.
 * User: zhangchu
 * Date: 2018/5/14 
 */
class LogisticsImportController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all LogisticsImport models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new LogisticsImportSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single LogisticsImport model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * 加急物流包裹号批量导入
     * @return string|\yii\web\Response
     */
    public function actionBatchImport()
    {
        set_time_limit(0);//程序执行时间无限制
        $model = new LogisticsImport();
        if (Yii::$app->request->isPost)
        {
            $model->file_execl = UploadedFile::getInstance($model, 'file_execl');

            if($model->file_execl->getExtension()!='csv')
            {
                Yii::$app->getSession()->setFlash('error',"格式不正确",true);
                return $this->redirect(['index']);
            }
            $data = $model->upload();

            if(empty($data))
            {
                Yii::$app->getSession()->setFlash('error',"文件上传失败",true);
                return $this->redirect(['index']);
            }
            $file = fopen($data, 'r');
            $line_number = 0;
            while ($datas = fgetcsv($file)) {
                if ($line_number == 0) { //跳过表头
                    $line_number++;
                    continue;
                }
                $num = count($datas);
                for ($c = 0; $c < $num; $c++) {

                    $Name[$line_number][] = mb_convert_encoding(trim($datas[$c]),'utf-8','gbk');

                }
                $line_number++;
            }
            $tran = Yii::$app->db->beginTransaction();
            try{
                $repeatingData = []; //上传重复的物流单号
                foreach($Name as $k=>$value){
                    if(count($value)!=2){
                        throw new HttpException(500,'信息缺失!');
                    }
                    $user_id = Yii::$app->user->id;
                    $user_name = Yii::$app->user->identity->username;;
                    if(empty($value[0])){
                        throw new HttpException(500,'请填写物流单号!');
                    }
                    if(strpos($value[0],'+')){
                        throw new HttpException(500,'物流单号：'.$value[0].'有误，请填写采购单号!');
                    }
                    if(empty($value[1])){
                        throw new HttpException(500,'请填写正确的物流单号!');
                    }

                    $logisticsInfo = LogisticsImport::find()->andFilterWhere(['logistics_num'=>$value[0],'is_deleted'=>'1'])->asArray()->one();
                    if($logisticsInfo){
                        //throw new HttpException(500,'物流单号：'.$value[0].'已上传，请勿再次上传!');
                        $repeatingData[] = $logisticsInfo['logistics_num'];
                        continue;
                    }
                    $LogisticsImport = new LogisticsImport();
                    $LogisticsImport->logistics_num = $value[0];
                    $LogisticsImport->purchase_order_num = $value[1];
                    $LogisticsImport->create_id = $user_id;
                    $LogisticsImport->create_name = $user_name;
                    $LogisticsImport->create_time = date('Y-m-d H:i:s',time());
                    $LogisticsImport->update_time = date('Y-m-d H:i:s',time());
                    if($LogisticsImport->save(false) == false){
                        throw new HttpException(500,'批量导入加急单号失败！');
                    }
                }
                if($repeatingData){
                    throw new HttpException(500,'物流单号：'.rtrim(implode(',',$repeatingData),',').'已上传，请勿再次上传!');
                }
                $response = ['status'=>'success','message'=>'加急单号导入成功！'];
                $tran->commit();
            }catch(HttpException $e){
                $response = ['status'=>'error','message'=>$e->getMessage()];
                $tran->commit();
            }
            fclose($file);
            Yii::$app->getSession()->setFlash($response['status'],$response['message'],true);
            return $this->redirect(['index']);
        } else {
            return $this->renderAjax('batch-import', ['model' => $model]);
        }

    }

    /**
     * 计划任务-推送加急物流单号至仓库系统
     */
    public function actionPushLogisticsInfo(){
        $data = LogisticsImport::find()
            ->select('id,logistics_num,purchase_order_num')
            ->where(['push_status' => 0, 'is_deleted' => 1])
            ->orderBy(['create_time'=>SORT_ASC])
            ->limit(20)
            ->asArray()
            ->all();
        if(empty($data)) {
            return '已经没有数据了';
        }
        $url = "http://wms.yibainetwork.com/Api/Purchase/Purchase/setDeliveryPurchase";
        $curl = new \linslin\yii2\curl\Curl();
        $response = $curl->setPostParams([
            'logistics_num_info' => json_encode($data),
            'token'    => json_encode(Vhelper::stockAuth()),
        ])->post($url);
/*         $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1); */
/*         curl_setopt($curl, CURLOPT_POSTFIELDS, ['logistics_num_info' => json_encode($data)]);//传送信息
        $response = curl_exec($curl); */
        if($response === FALSE ){
            echo "CURL Error:".curl_error($curl);
        }
        //curl_close($curl);
        $res = json_decode($response,1);
        if(is_array($res) && !empty($res)) {
          
                $successed_ids = [];
                foreach($res['success_list'] as $k=>$v) {
                        $successed_ids[] = $k;
                }

                $fail_data = [];
                foreach($res['fail_list'] as $k=>$v) {
                    if($v['status'] == 'fail') {
                        $fail_data[$k]['id'] = $k;
                        $fail_data[$k]['msg'] = $v['msg'];
                    }
                }

            if(!empty($successed_ids) || !empty($fail_data)) {
                $successed_ids = Vhelper::getSqlArrayString($successed_ids);
                $successed_res = LogisticsImport::updateAll(['push_status' => 1,'push_res'=>'推送成功'],"id in ({$successed_ids})");

                foreach ($fail_data as $key => $value) {
                    $model = LogisticsImport::findOne($value['id']);
                    $model -> push_status = 3;
                    $model -> push_res = $value['msg'];
                    $model -> save(false);
                }

                Vhelper::dump('推送成功的条数：'.$successed_res.'-----推送失败的条数：'.count($fail_data));
                
            } else {
                Vhelper::dump($res);
            }
        } else {
            Vhelper::dump($res);
        }

    }

    /**
     * 手动推送-推送加急物流单号至仓库系统
     */
    public function actionPushLogisticsData(){
        if (Yii::$app->request->isAjax)
        {
            $id = Yii::$app->request->get('id');
            $repeat_ids = LogisticsImport::find()
                    ->select('logistics_num')
                    ->where(['push_status' => 1, 'is_deleted' => 1])
                    ->andWhere(['in','id',$id])
                    ->asArray()
                    ->all();

            if($repeat_ids) {
                $logistics_res = [];
                foreach ($repeat_ids as $key => $value) {

                    $logistics_res[] = $value['logistics_num'];  
                }
                return implode('---',$logistics_res).'您已提交，请勿重复提交！';
            }        
            $data = LogisticsImport::find()
                ->select('id,logistics_num,purchase_order_num')
                ->where(['push_status' => 0, 'is_deleted' => 1])
                ->andWhere(['in','id',$id])
                ->orderBy(['create_time'=>SORT_ASC])
                ->asArray()
                ->all();
            if(empty($data)) {
                return '已经没有数据了';
            }

            $curl = new curl\Curl();
            $url = "http://wms.yibainetwork.com/Api/Purchase/Purchase/setDeliveryPurchase";
            $token = Json::encode(Vhelper::stockAuth());
            //var_dump($token);exit;
            $s = $curl->setPostParams([
                'logistics_num_info' => Json::encode($data),
                'token'    => $token
            ])->post($url);
            /*$curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, ['logistics_num_info' => json_encode($data)]);//传送信息
            $response = curl_exec($curl);curl_close($curl);*/
            if($s === FALSE ){
                echo "CURL Error:".curl_error($curl);
            }
            $res = json_decode($s,1);

            //print_r($res);exit;
            if(is_array($res) && !empty($res)) {
              
                    $successed_ids = [];
                    foreach($res['success_list'] as $k=>$v) {
                            $successed_ids[] = $k;
                    }

                    $fail_data = [];
                    $fail_ids = [];
                    foreach($res['fail_list'] as $k=>$v) {
                        if($v['status'] == 'fail') {
                            $fail_ids[] = $k;
                            $fail_data[$k]['id'] = $k;
                            $fail_data[$k]['msg'] = $v['msg'];
                        }
                    }

                if((!empty($successed_ids)) || (!empty($fail_data))) {
                    $successed_res = LogisticsImport::updateAll(['push_status' => 1,'push_res'=>'推送成功'], ['in', 'id', $successed_ids]);

                    $fail_res = LogisticsImport::updateAll(['push_status' => 3,'push_res'=>'推送失败'], ['in', 'id', $fail_ids]);
                    foreach ($fail_data as $key => $value) {
                        $model = LogisticsImport::findOne($value['id']);
                        $model -> push_status = 3;
                        $model -> push_res = $value['msg'];
                        $model -> save(false);
                    }

                    Vhelper::dump('推送成功的条数：'.$successed_res.'-----推送失败的条数：'.$fail_res);
                    
                } else {
                    Vhelper::dump($res);
                }
            } else {
                Vhelper::dump($res);
            }
        }    
    }

    /**
     * Deletes an existing LogisticsImport model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->is_deleted = '0'; 
        if ($model->save(false)) {
            return $this->redirect(['index']);
        } else {
            Yii::$app->getSession()->setFlash($checkResult['500'],$checkResult['未删除成功，请联系管理员！']);
            return $this->redirect(Yii::$app->request->referrer);
        }
    }

    /**
     * Finds the LogisticsImport model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return LogisticsImport the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = LogisticsImport::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
