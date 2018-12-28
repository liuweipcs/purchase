<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "pur_prefix_number".
 *
 * @property string $id
 * @property string $prefix
 * @property string $number
 * @property string $note
 * @property string $date
 */
class PrefixNumber extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_prefix_number';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['prefix', 'date'], 'required'],
            [['number'], 'integer'],
            [['date'], 'safe'],
            [['prefix'], 'string', 'max' => 10],
            [['note'], 'string', 'max' => 300],
            [['prefix', 'date'], 'unique', 'targetAttribute' => ['prefix', 'date'], 'message' => 'The combination of Prefix and Date has already been taken.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'prefix' => 'Prefix',
            'number' => 'Number',
            'note' => 'Note',
            'date' => 'Date',
        ];
    }
}
