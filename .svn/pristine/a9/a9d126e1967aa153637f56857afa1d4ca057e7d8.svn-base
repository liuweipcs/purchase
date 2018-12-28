<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "{{%declare_customs}}".
 *
 * @property string $id
 * @property string $pur_number
 * @property string $sku
 * @property string $key_id
 * @property string $order_id
 * @property string $is_clear
 * @property string $custom_number
 * @property string $clear_time
 * @property integer $amounts
 * @property string $price
 * @property string $declare_name
 * @property string $declare_unit
 * @property string $create_time
 */
class DeclareCustoms extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%declare_customs}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pur_number', 'sku'], 'required'],
            [['clear_time', 'create_time'], 'safe'],
            [['amounts'], 'integer'],
            [['price'], 'number'],
            [['pur_number', 'key_id', 'order_id', 'custom_number'], 'string', 'max' => 100],
            [['sku'], 'string', 'max' => 30],
            [['is_clear'], 'string', 'max' => 255],
            [['declare_name', 'declare_unit'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pur_number' => 'Pur Number',
            'sku' => 'Sku',
            'key_id' => 'Key ID',
            'order_id' => 'Order ID',
            'is_clear' => 'Is Clear',
            'custom_number' => 'Custom Number',
            'clear_time' => 'Clear Time',
            'amounts' => 'Amounts',
            'price' => 'Price',
            'declare_name' => 'Declare Name',
            'declare_unit' => 'Declare Unit',
            'create_time' => 'Create Time',
        ];
    }

    /**
     * 获取信息再前台展示
     * @param $pur_number
     * @param $sku
     * @param $key_id
     * @return string
     */
    public static function getHtml($pur_number, $sku,$key_id)
    {
        $html = '';

        $data = self::find()->where(['pur_number'=>$pur_number, 'sku' => $sku,'key_id' => $key_id])->asArray()->all();
        if (empty($data)) return $html;
        foreach ($data as $key => $value) {
            $html .= "报关单号：{$value['custom_number']}<br />";
            $html .= "报关时间：{$value['clear_time']}<br />";
            $html .= "报关品名：{$value['declare_name']}<br />";
            $html .= "报关单位：{$value['declare_unit']}<br />";
            $html .= "报关数量：{$value['amounts']}<br />";
            $html .= "规格型号：{$value['declare']}";
        }
        return $html;  
    } 
}
