<?php
namespace app\controllers;

use app\models\BankConfig;
use Yii;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/9
 * Time: 17:47
 */
class BankConfigController extends BaseController{

    public function actionIndex(){
        $searchModel = new BankConfig();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionGetBankConfig($keywords){
        $out =  ['type'=>'success','code'=>0,'content' =>['bankName'=>'']];
        if (!is_null($keywords)) {
            $query = BankConfig::find()
                ->select('bank_name AS text,id')
                ->andFilterWhere(['like', 'bank_name',trim($keywords)])
                ->andWhere(['status'=>1]);
            $query->distinct()
                ->limit(10);
            $data = $query->asArray()->all();
        }
        if(!empty($data)){
            foreach ($data as $key=>$value){
                $out['content'][$key]=$value;
            }
        }
        echo json_encode($out);
        \Yii::$app->end();
    }

    public function actionChangeStatus($id){
        $model = BankConfig::findOne($id);
        $model->status = $model->status==1 ? 0 : 1;
        if ($model->save()==false){
            Yii::$app->session->setFlash('warning',implode(',',$model->getFirstErrors()));
        }else{
            Yii::$app->session->setFlash('success','状态更新成功');
        }
        return $this->redirect(Yii::$app->request->referrer);
    }
}