<?php

namespace app\api\v1\models;

use Yii;

/**
 * This is the model class for table "{{%product_line}}".
 *
 * @property integer $id
 * @property integer $product_line_id
 * @property integer $linelist_parent_id
 * @property string $linelist_cn_name
 * @property integer $linelist_level
 * @property string $create_time
 */
class ProductLine extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product_line}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_line_id', 'linelist_cn_name'], 'required'],
            [['product_line_id', 'linelist_parent_id', 'linelist_level'], 'integer'],
            [['create_time'], 'safe'],
            [['linelist_cn_name'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'product_line_id' => Yii::t('app', 'erp产品线ID'),
            'linelist_parent_id' => Yii::t('app', '产品线父级ID'),
            'linelist_cn_name' => Yii::t('app', '产品线名'),
            'linelist_level' => Yii::t('app', '产品线级别'),
            'create_time' => Yii::t('app', '接受创建时间'),
        ];
    }

    /**
     * 保存数据
     * @param $data
     */
    public static function Saves($data)
    {
        foreach ($data as $v)
        {
            $model=self::find()->where(['product_line_id'=>$v['id']])->one();
            if($model)
            {
                $model->linelist_parent_id = $v['linelist_parent_id'];
                $model->linelist_cn_name   = $v['linelist_cn_name'];
                $model->linelist_level     = $v['linelist_level'];
                $model->create_time        = date('Y-m-d H:i:s',time());
                $status= $model->save();
            } else{
                $models                     = new  self;
                $models->product_line_id    = $v['id'];
                $models->linelist_parent_id = $v['linelist_parent_id'];
                $models->linelist_cn_name   = $v['linelist_cn_name'];
                $models->linelist_level     = $v['linelist_level'];
                $models->create_time        = date('Y-m-d H:i:s',time());
                $status= $models->save();
            }

        }
        return $status;
    }
}
