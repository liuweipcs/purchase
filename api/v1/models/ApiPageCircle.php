<?php

namespace app\api\v1\models;

use Yii;

/**
 * This is the model class for table "pur_api_page_circle".
 *
 * @property integer $id
 * @property integer $page
 * @property string $create_time
 * @property string $type
 */
class ApiPageCircle extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_api_page_circle';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['page', 'create_time', 'type'], 'required'],
            [['page'], 'integer'],
            [['create_time'], 'safe'],
            [['type'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'page' => 'Page',
            'create_time' => 'Create Time',
            'type' => 'Type',
        ];
    }

    public static function insertNewPage($page,$type){
        $model = new self();
        $model ->type =$type;
        $model ->page =$page;
        $model ->create_time =date('Y-m-d H:i:s',time());
        $model->save(false);
    }
}
