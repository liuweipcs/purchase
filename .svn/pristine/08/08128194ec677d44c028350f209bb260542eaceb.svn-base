<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "wl_menu".
 *
 * @property integer $id
 * @property string $name
 * @property integer $parent
 * @property string $route
 * @property integer $order
 * @property string $data
 *
 * @property WlMenu $parent0
 * @property WlMenu[] $wlMenus
 */
class WlMenu extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'wl_menu';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['parent', 'order'], 'integer'],
            [['data'], 'string'],
            [['name'], 'string', 'max' => 128],
            [['route'], 'string', 'max' => 256],
            [['parent'], 'exist', 'skipOnError' => true, 'targetClass' => WlMenu::className(), 'targetAttribute' => ['parent' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'parent' => Yii::t('app', 'Parent'),
            'route' => Yii::t('app', 'Route'),
            'order' => Yii::t('app', 'Order'),
            'data' => Yii::t('app', 'Data'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent0()
    {
        return $this->hasOne(WlMenu::className(), ['id' => 'parent']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWlMenus()
    {
        return $this->hasMany(WlMenu::className(), ['parent' => 'id']);
    }
}
