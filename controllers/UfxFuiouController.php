<?php
namespace app\controllers;
use app\models\BankCityInfo;
use app\models\BankInfo;
use app\models\PurchaseOrderPayUfxfuiou;
use app\models\UfxFuiou;
use app\models\xml;
use linslin\yii2\curl\Curl;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/3
 * Time: 20:57
 */
class UfxFuiouController extends BaseController{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['get-city','get-branch-bank-city','get-transfer-result','get-branch-bank-city','branch-bank','check-bank'],
                'rules' => [
                    [
                        // 允许 guest用户访问
                        'allow' => true,
                        'actions' => ['get-city','get-branch-bank-city','get-transfer-result','get-branch-bank-city','branch-bank','check-bank'],
                        'roles' => ['?'],
                    ],
                    [
                        // 允许 登录 用户访问
                        'allow' => true,
                        'actions' => ['get-city','get-branch-bank-city','get-transfer-result','get-branch-bank-city','branch-bank','check-bank'],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }


    public function actionGetBranchBank($masterBankCode){
        echo Html::tag('option','请选择', ['value'=>'']) ;
        $branchBank = BankInfo::find()->select('bank_union_code,branch_bank_name')->andFilterWhere(['bank_code'=>$masterBankCode])
                    ->asArray()->all();
        $branchBank = ArrayHelper::map($branchBank,'bank_union_code','branch_bank_name');
        if(!empty($branchBank)){
            foreach($branchBank as $value=>$name)
            {
                echo Html::tag('option',Html::encode($name),array('value'=>$value));
            }
        }
    }

    public function actionGetTransferResult(){
        $transferData = PurchaseOrderPayUfxfuiou::find()
            ->select('requisition_number,pur_tran_num')
            ->where(['status'=>1])
            ->andWhere(['or',['<','check_time',date('Y-m-d H:i:s',time()-2*60*60)],['check_time'=>null]])
            ->orderBy('check_time ASC')
            ->limit(10)
            ->asArray()->all();
        UfxFuiou::getTransferResult($transferData);
    }

    public function actionGetBranchBankCity($unionBankCode){
        $cityCode = BankInfo::find()->select('city_code')->where(['bank_union_code'=>$unionBankCode])->scalar();
        if(!$cityCode){
            echo json_encode(['provNo'=>'','provName'=>'','cityNo'=>'','cityName'=>'']);
            \Yii::$app->end();
        }
        $bankInfo = BankCityInfo::find()->where(['city_code'=>$cityCode])->one();
        if(empty($bankInfo)){
            echo json_encode(['provNo'=>'','provName'=>'','cityNo'=>'','cityName'=>'']);
            \Yii::$app->end();
        }
        echo json_encode(['provNo'=>$bankInfo->prov_code,'provName'=>$bankInfo->prov_name,'cityNo'=>$bankInfo->city_code,'cityName'=>$bankInfo->city_name]);
        \Yii::$app->end();
    }

    public function actionGetCity($prov,$city=null){
        echo Html::tag('option','请选择', ['value'=>'']) ;
        $cityCodeArray = BankCityInfo::find()->select('city_code,city_name')->where(['prov_code'=>$prov])->asArray()->all();
        $cityInfo= ArrayHelper::map($cityCodeArray,'city_code','city_name');
        if(!empty($cityInfo)&&!empty($cityInfo)){
            foreach($cityInfo as $value=>$name)
            {
                echo Html::tag('option',Html::encode($name),array('value'=>$value,'selected'=>$city==$value?true :false));
            }
        }
    }

    public function actionBranchBank($keywords = null){
        $out =  ['type'=>'success','code'=>0,'content' =>['bankName'=>'']];
        if (!is_null($keywords)) {

            $query = BankInfo::find()
                ->select('branch_bank_name AS text,bank_union_code')
                ->andFilterWhere(['like', 'branch_bank_name', $keywords]);
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

    public function actionCheckBank($masterBankCode,$branchBankName,$provCode,$cityCode){
        $bankExist = BankInfo::find()->where(['bank_code'=>$masterBankCode,'city_code'=>$cityCode,'branch_bank_name'=>$branchBankName])->exists();
        $cityExist = BankCityInfo::find()->where(['prov_code'=>$provCode,'city_code'=>$cityCode])->exists();
        if(!$bankExist||!$cityExist){
            echo json_encode(['status'=>'error','message'=>'主行、支行、区域信息不存在，请确认信息填写正确！']);
            \Yii::$app->end();
        }
        echo json_encode(['status'=>'success','message'=>'银行验证通过']);
        \Yii::$app->end();
    }
}