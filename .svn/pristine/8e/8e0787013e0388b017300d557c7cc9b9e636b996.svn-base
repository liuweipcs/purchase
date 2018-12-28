<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "{{%sku_outofstock_statisitics}}".
 *
 * @property integer $id
 * @property integer $warehouse_id
 * @property string $sku
 * @property string $platform
 * @property integer $lack_quantity
 * @property string $statistics_date
 * @property string $warehouse_code
 * @property string $earlest_outofstock_date
 */
class SkuOutofstockStatisitics extends BaseModel
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%sku_outofstock_statisitics}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['warehouse_id', 'lack_quantity'], 'integer'],
            [['warehouse_code'], 'string', 'max' => 100],
            [['platform'], 'string', 'max' => 50],
            [['sku'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'warehouse_id' => Yii::t('app', '仓库ID'),
            'sku' => Yii::t('app', 'sku编号'),
            'platform' => Yii::t('app', '平台'),
            'lack_quantity' => Yii::t('app', '缺货数量'),
            'statistics_date' => Yii::t('app', '统计时间'),
            'warehouse_code' => Yii::t('app', '仓库CODE'),
            'earlest_outofstock_date' => Yii::t('app', '最早缺货时间'),
        ];
    }

    /**
     * 获取缺货平台的名称
     * @param $code
     * @param int $type
     * @return false|null|string
     */
    public static function  getPlatform($sku='')
    {
        //只查询易佰东莞仓库
        return self::find()->where(['sku'=>$sku,'warehouse_code'=>'SZ_AA'])->all();
    }
}
