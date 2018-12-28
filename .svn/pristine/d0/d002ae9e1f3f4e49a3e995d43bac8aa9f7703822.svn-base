<?php

namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use Yii;

/**
 * This is the model class for table "{{%dynamic_table}}".
 *
 * @property integer $id
 * @property string $demand_number
 * @property integer $user_id
 */
class DynamicTable extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dynamic_table}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id','demand_number_id'], 'integer'],

        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', '需求单号'),
            'demand_number_id' => Yii::t('app', 'Demand Number'),
            'user_id' => Yii::t('app', 'User ID'),
        ];
    }
    public  static  function  Add($data)
    {

        $model                = new  self;
        $model->user_id       = $data['user_id'];
        $model->demand_number_id = $data['demand_number_id'];
        $status =$model->save();
        return $status;

    }
    public  static  function  Check($data)
    {
        $model = self::find()->where(['demand_number_id'=>$data['demand_number_id']])->one();

        if($model)
        {
            return true;
        }else {
            return false;
        }
    }
    public static  function  Deletes($data)
    {
        $rs=self::Check($data);
        if(!$rs)
        {
            return true;
        }
        $status = self::deleteAll(['user_id'=>$data['user_id']]);
        if($status)
        {
            return true;
        } else{
            return false;
        }

    }
    public static  function  Deletess($data)
    {

        $status = self::deleteAll(['user_id'=>$data['user_id']]);
        if($status)
        {
            return true;
        } else{
            return false;
        }

    }
    public  static  function  getId()
    {
        $model = self::find()->select('demand_number_id')->asArray()->all();
        $arr =[];
        foreach($model as $v)
        {
            $arr[]=$v['demand_number_id'];
        }
        return $arr;

    }
}
