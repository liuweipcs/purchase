<?php

namespace app\modules\manage\models;

use Yii;

/**
 * This is the model class for table "pur_product_line".
 *
 * @property integer $id
 * @property integer $product_line_id
 * @property integer $linelist_parent_id
 * @property string $linelist_cn_name
 * @property integer $linelist_level
 * @property string $create_time
 * @property integer $lft
 * @property integer $rgt
 */
class ProductLine extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_product_line';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_line_id', 'linelist_cn_name'], 'required'],
            [['product_line_id', 'linelist_parent_id', 'linelist_level', 'lft', 'rgt'], 'integer'],
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
            'id' => 'ID',
            'product_line_id' => 'Product Line ID',
            'linelist_parent_id' => 'Linelist Parent ID',
            'linelist_cn_name' => 'Linelist Cn Name',
            'linelist_level' => 'Linelist Level',
            'create_time' => 'Create Time',
            'lft' => 'Lft',
            'rgt' => 'Rgt',
        ];
    }

    public static function getProductLinefamily($productLine){
        $items[] =$productLine ;
        $chilProductLine = ProductLine::find()->select('product_line_id')->where(['linelist_parent_id'=>$productLine])->column();
        if(empty($chilProductLine)){
            return $items;
        }else{
            $items=array_merge($items,$chilProductLine);
            foreach ($chilProductLine as $productLine){
                $items =array_merge(self::getProductLinefamily($productLine),$items);
            }
        }
        return array_unique($items);
    }
}
