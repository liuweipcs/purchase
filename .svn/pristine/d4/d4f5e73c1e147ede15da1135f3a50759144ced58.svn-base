<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "{{%supervisor_group_bind}}".
 *
 * @property string $id
 * @property integer $supervisor_id
 * @property string $supervisor_name
 * @property string $group_id
 * @property integer $creator_id
 * @property string $creator_name
 * @property string $editor_name
 * @property integer $create_time
 * @property integer $edit_time
 */
class SupervisorGroupBind extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%supervisor_group_bind}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['supervisor_id', 'supervisor_name', 'group_id', 'creator_id', 'creator_name', 'create_time'], 'required'],
            [['supervisor_id', 'creator_id', 'create_time', 'edit_time'], 'integer'],
            [['supervisor_name','group_id','creator_name', 'editor_name'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'supervisor_id' => Yii::t('app', 'Supervisor ID'),
            'supervisor_name' => Yii::t('app', '销售名称'),
            'group_id' => Yii::t('app', '分组'),
            'creator_id' => Yii::t('app', 'Creator ID'),
            'creator_name' => Yii::t('app', '创建人'),
            'editor_name' => Yii::t('app', '修改人'),
            'create_time' => Yii::t('app', '创建时间'),
            'edit_time' => Yii::t('app', '修改时间'),
        ];
    }
    /**
     * 获取分组权限
     * $group = 38组：可查看采购建议/历史采购建议/采购单 ，此3个界面的单价，金额，供应商信息  需要对该组别的人做隐藏
     */
    public static function getGroupPermissions($group)
    {
        $userGroup = self::find()->where(['supervisor_name'=>Yii::$app->user->identity->username])->all();
        if(!empty($userGroup)){
            foreach($userGroup  as $value){
                $groupId[] = $value->group_id;
            }
        }
        if (!empty($groupId) && in_array($group,$groupId)) {
            $bool = true;
        } else {
            $bool = false;
        }
        return $bool;
    }
}
