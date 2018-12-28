<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "pur_sku_statistics_log".
 *
 * @property string $id
 * @property string $po_number
 * @property string $sku
 * @property string $warehouse_code
 * @property string $note
 * @property string $created_at
 * @property string $creator
 * @property string $status
 */
class SkuStatisticsLog extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_sku_statistics_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['po_number', 'sku', 'warehouse_code', 'created_at', 'creator'], 'required'],
            [['created_at'], 'safe'],
            [['status'], 'string'],
            [['po_number', 'warehouse_code', 'creator'], 'string', 'max' => 20],
            [['sku'], 'string', 'max' => 30],
            [['note'], 'string', 'max' => 500],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'po_number' => '批次号',
            'sku' => 'Sku',
            'warehouse_code' => '仓库编码',
            'note' => '备注',
            'created_at' => '时间',
            'creator' => '用户',
            'status' => '状态',
        ];
    }
}
