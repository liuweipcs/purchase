<?php

namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use Yii;
use yii\rbac\DbManager;
use yii\web\HttpException;

/**
 * This is the model class for table "pur_sample_inspect".
 *
 * @property integer $id
 * @property string $sku
 * @property integer $apply_id
 * @property integer $product_num
 * @property string $supply_send_name
 * @property string $supply_chain_send_time
 * @property integer $supply_chain_send
 * @property string $quality_take_name
 * @property string $quality_control_take_time
 * @property integer $quality_control_take
 * @property string $quality_send_name
 * @property string $quality_control_send_time
 * @property integer $quality_control_send
 * @property string $supply_take_name
 * @property string $supply_chain_take_time
 * @property integer $supply_chain_take
 * @property string $confirm_time
 * @property integer $qc_result
 * @property string $reason
 * @property string $confirm_user_name
 */
class SampleInspect extends BaseModel
{
    public $apply_user;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_sample_inspect';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['apply_id', 'product_num', 'supply_chain_send', 'quality_control_take', 'quality_control_send', 'supply_chain_take', 'qc_result'], 'integer'],
            [['supply_chain_send_time', 'quality_control_take_time', 'quality_control_send_time', 'supply_chain_take_time', 'confirm_time','confirm_user_name'], 'safe'],
            [['reason'], 'string'],
            [['sku'], 'string', 'max' => 100],
            [['supply_send_name', 'quality_take_name', 'quality_send_name', 'supply_take_name'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sku' => 'Sku',
            'apply_id' => 'Apply ID',
            'product_num' => 'Product Num',
            'supply_send_name' => 'Supply Send Name',
            'supply_chain_send_time' => 'Supply Chain Send Time',
            'supply_chain_send' => 'Supply Chain Send',
            'quality_take_name' => 'Quality Take Name',
            'quality_control_take_time' => 'Quality Control Take Time',
            'quality_control_take' => 'Quality Control Take',
            'quality_send_name' => 'Quality Send Name',
            'quality_control_send_time' => 'Quality Control Send Time',
            'quality_control_send' => 'Quality Control Send',
            'supply_take_name' => 'Supply Take Name',
            'supply_chain_take_time' => 'Supply Chain Take Time',
            'supply_chain_take' => 'Supply Chain Take',
            'confirm_time' => 'Confirm Time',
            'qc_result' => 'Qc Result',
            'reason' => 'Reason',
            'confirm_user_name'=> 'Confirm User Name'
        ];
    }

    //获取产品关联
    public function getProduct(){
        return $this->hasOne(Product::className(),['sku'=>'sku']);
    }

    public function getApply(){
        return $this->hasOne(SupplierUpdateApply::className(),['id'=>'apply_id']);
    }

    public static function sendTake($param){
        try{
            if(!isset($param['id'])||empty($param['id'])||!isset($param['type'])||empty($param['type'])){
                throw new HttpException(500,'请求缺少必要参数请联系管理员！');
            }
            $inspect = self::find()->where(['id'=>$param['id']])->one();
            if(empty($inspect)){
                throw new HttpException(500,'检测数据异常！请联系管理员！');
            }
            $role = Yii::$app->authManager->getRolesByUser(Yii::$app->user->id);
            switch($param['type']){
//                case 'supplySend':
//                    if(!isset($role['品控'])&&!isset($role['超级管理员组'])){
//                        throw new HttpException(500,'该按钮由品控角色操作');
//                    }
//                    if(self::getStatus($inspect,'supplySend')){
//                        throw new HttpException(500,'请等待前一步操作完成！');
//                    }
//                    $inspect->supply_chain_send = 2;
//                    $inspect->supply_send_name  = Yii::$app->user->identity->username;
//                    $inspect->supply_chain_send_time = date('Y-m-d H:i:s',time());
//                    break;
                case 'qualityTake':
                    /*if(!isset($role['供应链'])&&!isset($role['超级管理员组'])){
                        throw new HttpException(500,'该按钮由供应链角色操作');
                    }*/
                    if(!isset($role['品控'])&&!isset($role['超级管理员组'])){
                        throw new HttpException(500,'该按钮由品控角色操作');
                    }
//                    if(self::getStatus($inspect,'qualityTake')){
//                        throw new HttpException(500,'请等待前一步操作完成！');
//                    }
                    if(strtotime($inspect->apply->create_time)>strtotime('2018-04-13 12:00:00') &&empty($inspect->pur_number)){
                        throw new HttpException(500,'2018-04-13 12:00:00 之后的申请必须添加采购单号才能收货');
                    }
                    $inspect->quality_control_take = 2;
                    $inspect->quality_take_name  = Yii::$app->user->identity->username;
                    $inspect->quality_control_take_time = date('Y-m-d H:i:s',time());
                    break;
                case 'qualitySend':
//                    if(!isset($role['供应链'])&&!isset($role['超级管理员组'])){
//                        throw new HttpException(500,'该按钮由供应链角色操作');
//                    }
                    if(!isset($role['品控'])&&!isset($role['超级管理员组'])){
                        throw new HttpException(500,'该按钮由品控角色操作');
                    }
                    if(self::getStatus($inspect,'qualitySend')){
                        throw new HttpException(500,'请等待前一步操作完成！');
                    }
                    if($inspect->qc_result!=2){
                        throw new HttpException(500,'质检合格产品才能入库');
                    }
                    if(strtotime($inspect->apply->create_time)>strtotime('2018-04-13 12:00:00') &&empty($inspect->pur_number)){
                        throw new HttpException(500,'2018-04-13 12:00:00 之后的申请必须添加采购单号才能入库');
                    }
                    $inspect->quality_control_send = 2;
                    $inspect->quality_send_name  = Yii::$app->user->identity->username;
                    $inspect->quality_control_send_time = date('Y-m-d H:i:s',time());
                    break;
//                case 'supplyTake':
//                    if(!isset($role['品控'])&&!isset($role['超级管理员组'])){
//                        throw new HttpException(500,'该按钮由品控角色操作');
//                    }
//                    if(self::getStatus($inspect,'supplyTake')){
//                        throw new HttpException(500,'请等待前一步操作完成！');
//                    }
//                    $inspect->supply_chain_take = 2;
//                    $inspect->supply_take_name  = Yii::$app->user->identity->username;
//                    $inspect->supply_chain_take_time = date('Y-m-d H:i:s',time());
//                    break;
                default:
                    throw new HttpException(500,'参数异常！');
            }
            if($inspect->save() == false){
                throw new HttpException(500,'操作失败！');
            }
            $result = ['status'=>'success','message'=>'操作成功!','time'=>date('Y-m-d H:i:s',time()),'user'=>Yii::$app->user->identity->username];
        }catch(HttpException $e){
            $result = ['status'=>'error','message'=>$e->getMessage(),'time'=>date('Y-m-d H:i:s',time()),'user'=>Yii::$app->user->identity->username];
        }
        return $result;
    }

    //获取当前样品流程步骤
    public static function getStatus($inspect,$type){
//        $num1 = in_array($type,['supplySend']) ? 1 : 2;
//        $num2 = in_array($type,['supplySend','qualityTake']) ? 1 : 2;
//        $num3 = in_array($type,['supplySend','qualityTake','qualitySend']) ? 1 : 2;
//        return ($inspect->supply_chain_send != $num1 || $inspect->quality_control_take != $num2 || $inspect->quality_control_send != $num3 || $inspect->supply_chain_take != 1) ? true : false;
         return $inspect->quality_control_take == 2 ? false : true;
    }

    //提交质检结果
    public static function quality($param,$type){
        try{
            if(!isset($param['id']) || empty($param['id'])){
                throw new HttpException(500,'缺失必要参数！');
            }
            $inspect = SampleInspect::find()->where(['id'=>$param['id']])->one();
            if(empty($inspect) || $inspect->qc_result !=1){
                throw new HttpException(500,'数据错误！');
            }
            if($inspect->quality_control_take !=2){
                throw new HttpException(500,'请收货后再返回质检结果');
            }
            $role = Yii::$app->authManager->getRolesByUser(Yii::$app->user->id);
            if(!isset($role['品控'])&&!isset($role['超级管理员组'])){
                throw new HttpException(500,'该按钮由品控角色操作');
            }
            if($type=='quality'){
                $inspect->reason            = $param['reason'];
                $inspect->qc_result         = 2;
                $inspect->confirm_user_name = Yii::$app->user->identity->username;
                $inspect->confirm_time      = date('Y-m-d H:i:s',time());
                if($inspect->save() == false){
                    throw new HttpException(500,'更新质检结果失败！');
                }
            }
            if($type=='qualityno'){
                if(!isset($param['reason']) || empty($param['reason'])){
                    throw new HttpException(500,'原因不能为空！');
                }
                $inspect->reason            = $param['reason'];
                $inspect->qc_result         = 3;
                $inspect->confirm_user_name = Yii::$app->user->identity->username;
                $inspect->confirm_time      = date('Y-m-d H:i:s',time());
                if($inspect->save() == false){
                    throw new HttpException(500,'更新质检结果失败！');
                }
            }
            $result = ['status'=>'success','message'=>'操作成功'];
        }catch(HttpException $e){
            $result = ['status'=>'error','message'=>$e->getMessage()];
        }
        return $result;
    }
}
