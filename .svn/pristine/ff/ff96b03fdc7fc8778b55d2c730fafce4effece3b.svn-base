<?php

namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use Yii;

/**
 * This is the model class for table "{{%cancel_quantity_log}}".
 *
 * @property integer $id
 * @property string $buyer
 * @property string $create_time
 * @property string $pur_number
 * @property string $sku
 * @property integer $cly
 * @property string $note
 */
class CancelQuantityLog extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%cancel_quantity_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_time'], 'safe'],
            [['pur_number', 'sku'], 'required'],
            [['cly'], 'integer'],
            [['buyer'], 'string', 'max' => 20],
            [['pur_number'], 'string', 'max' => 100],
            [['sku'], 'string', 'max' => 30],
            [['note'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'buyer' => 'Buyer',
            'create_time' => 'Create Time',
            'pur_number' => 'Pur Number',
            'sku' => 'Sku',
            'cly' => 'Cly',
            'note' => 'Note',
        ];
    }
    public static function setLog($s)
    {
        if (!empty($s[0])) {
            $data = $s[0];
            foreach ($data as $v) {
                $model              = new self();
                $model->pur_number  = $v['pur_number'];
                $model->sku  = $v['sku'];
                $model->note        = $s['note'];
                $model->cly        = $v['cly'];
                $model->buyer = Yii::$app->user->identity['username'];
                $model->create_time = date('Y-m-d H:i:s');
                $status = $model->save();
            }
        }

    }
}
