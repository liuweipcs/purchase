<?php

namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%bulletin_board}}".
 *
 * @property integer $id
 * @property string $title
 * @property string $content
 * @property integer $create_id
 * @property string $create_time
 * @property string $update_time
 */
class BulletinBoard extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%bulletin_board}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title','content'], 'required'],
            [['id','bulletin_board_type'], 'integer'],
            [['content','create_id'], 'string'],
            [['create_time', 'update_time'], 'safe'],
            [['title'], 'string', 'max' => 100],
            [['create_id'], 'default', 'value' => Yii::$app->user->identity->username],
            [['create_time'], 'default', 'value' => date('Y-m-d H:i:s',time())],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'title' => Yii::t('app', 'Title'),
            'content' => Yii::t('app', '内容'),
            'create_id' => Yii::t('app', '发布人'),
            'create_time' => Yii::t('app', '发布时间'),
            'update_time' => Yii::t('app', '修改时间'),
        ];
    }
    /**
     * 公告状态
     * @param null $type
     * @return array
     */
    public  static  function  bulletinBoardType($type=null)
    {
        $types = [
            '0' =>'普通',
            '1' =>'头条',
            '2' =>'置顶',

        ];
        return isset($type) ?  $types[$type]:$types;
    }

    /**
     * 获取最新公告头条
     * @return array|bool|null|\yii\db\ActiveRecord
     */
    public static function getBulletinBoardHeadlines()
    {
        $top = self::find()->where(['bulletin_board_type'=>1])->orderBy('id desc')->one();
        if (!empty($top)) {
            return $top;
        } else {
            return false;
        }
    }
}
