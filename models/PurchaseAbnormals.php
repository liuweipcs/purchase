<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "{{%purchase_abnormals}}".
 *
 * @property integer $id
 * @property string $content
 * @property integer $status
 * @property string $title
 * @property string $create_time
 */
class PurchaseAbnormals extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_abnormals}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status','type'], 'integer'],
            [['create_time'], 'safe'],
            [['content'], 'string', 'max' => 500],
            [['title','pur_number'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'content' => Yii::t('app', '内容'),
            'status' => Yii::t('app', '状态'),
            'title' => Yii::t('app', '标题'),
            'pur_number' => Yii::t('app', '采购单号'),
            'create_time' => Yii::t('app', '创建时间'),
        ];
    }

    /**
     * 保存一条数据
     * @param $data
     */
    public static function Saves($data)
    {
        //如果存在就什么也不做了,没有就添加啊
        $models = self::find()->where(['pur_number'=>$data['pur_number']])->one();
        if ($models)
        {
            return true;
        } else{
            $model              = new  self;
            $model->title       = $data['title'];
            $model->content     = $data['content'];
            $model->type        = $data['type'];
            $model->pur_number  = $data['pur_number'];
            $model->status      = 1;
            $model->create_time = date('Y-m-d H:i:s', time());
            $model->save();
        }

    }

    /**
     * 根据采购单号修改一条数据
     * @param $pur_number
     */
    public  static  function UpdateOne($pur_number)
    {
        $model = self::find()->where(['pur_number'=>$pur_number])->one();
        if($model)
        {
            $model->status=2;
            $model->save();
        }

    }
}
