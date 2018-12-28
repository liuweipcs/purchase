<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "{{%operat_log}}".
 *
 * @property integer $id
 * @property integer $type
 * @property string $username
 * @property string $content
 * @property string $create_date
 * @property integer $uid
 * @property string $ip
 * @property integer $pid
 * @property string $module
 */
class OperatLog extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%operat_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'uid', 'pid'], 'integer'],
            [['content'], 'string'],
            [['create_date'], 'safe'],
            [['username', 'module','pur_number'], 'string', 'max' => 100],
            [['ip'], 'string', 'max' => 15],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'type' => Yii::t('app', '操作类型'),
            'username' => Yii::t('app', '操作人'),
            'content' => Yii::t('app', '操作内容'),
            'create_date' => Yii::t('app', '操作时间'),
            'uid' => Yii::t('app', 'Uid'),
            'ip' => Yii::t('app', '操作Ip'),
            'pid' => Yii::t('app', 'Pid'),
            'module' => Yii::t('app', '操作模块'),
            'pur_number' => Yii::t('app', 'PO号'),
        ];
    }

    public function getPurchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItems::className(), ['pur_number' => 'pur_number']);
    }

    public function getPurchaseOrderPayType()
    {
        return $this->hasOne(PurchaseOrderPayType::className(),['pur_number'=>'pur_number']);
    }

    public function getPurchaseOrder()
    {
        return $this->hasOne(PurchaseOrder::className(),['pur_number'=>'pur_number']);
    }




    public static function getType(){
        $operat_type=DataControlConfig::findOne(['type'=>'operat_type'])->values;

        if(!empty($operat_type)){
            $operat_type=explode(',',$operat_type);
            foreach($operat_type as $k=>$v){
                $data[$k+1]=$v;
            }
            $data[0]='其他';
            return $data;
        }

        return ['1'=>'添加','2'=>'编辑','3'=>'删除','0'=>'其他'];

    }

    /**
     * 数组处理成字符串
     * @param $date
     * @return string
     */
    public static function subLogstr($data){
        $strs='';
        if(!empty($data->attributes)){
            $data=array_values((array)$data->attributes);
        }else{
            $data=array_values((array)$data);
        }

        if(!empty($data)){
            foreach($data as $k=>$v){
                if(empty($v)){
                    continue;
                }else{
                    if($k+1==count($data)){
                        $strs.=$v;
                    }else{
                        $strs.=$v.', ';
                    }
                }
            }
        }

        return rtrim($strs,', ');

    }

    // 添加一条操作日志
    public static function AddLog($data)
    {
        $model = new self();
        $model->type        = $data['type'];
        $model->username    = Yii::$app->user->identity->username;
        $model->create_date = date('Y-m-d H:i:s', time());
        $model->uid         = Yii::$app->user->identity->id;
        $model->ip          = Yii::$app->request->userIP;
        $model->module      = $data['module'];
        $model->pur_number  = $data['pur_number'];
        $model->content     = $data['content'];
        $model->pid         = $data['pid'];
        $res = $model->save(false);
        return $res;
    }


}
