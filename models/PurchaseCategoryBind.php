<?php

namespace app\models;

use app\models\base\BaseModel;

use app\services\BaseServices;
use Yii;

/**
 * This is the model class for table "{{%purchase_category_bind}}".
 *
 * @property integer $id
 * @property integer $category_id
 * @property integer $buyer
 * @property string $cate_name
 * @property string $buyer_name
 * @property string $bind_time
 * @property string $bind_name
 * @property integer $purchase_type
 */
class PurchaseCategoryBind extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_category_bind}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category_id', 'buyer', 'purchase_type'], 'integer'],
            [['bind_time'], 'safe'],
            [['cate_name', 'buyer_name', 'bind_name'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'category_id' => Yii::t('app', '品类ID'),
            'buyer' => Yii::t('app', '采购ID'),
            'cate_name' => Yii::t('app', '品类名'),
            'buyer_name' => Yii::t('app', '采购员'),
            'bind_time' => Yii::t('app', '绑定时间'),
            'bind_name' => Yii::t('app', '绑定人'),
            'purchase_type' => Yii::t('app', '1国内2海外3FBA'),
        ];
    }
    public static function getBuyer($id)
    {
        return self::find()->select('buyer_name')->where(['category_id'=>$id])->scalar();
    }

    //根据sku获取采购员
    public static function getBuyerBySku($sku){
        $Product = Product::find()->where(['sku'=>$sku])->one();
        $buyer = '';
        if($Product){
            $pur_product_line = ProductLine::find()->where(['product_line_id'=>$Product->product_linelist_id])->one();

            if($pur_product_line && $pur_product_line->linelist_parent_id != 0) {
                $parent_product_line = ProductLine::find()->where(['product_line_id' => $pur_product_line->linelist_parent_id])->one();
                if ($parent_product_line && $parent_product_line->linelist_parent_id != 0) {
                    $buyer = PurchaseCategoryBind::find()->select('buyer_name')
                        ->where(['category_id' => $parent_product_line->linelist_parent_id])
                        ->scalar();
                }else{
                    $buyer = PurchaseCategoryBind::find()->select('buyer_name')
                        ->where(['category_id' => $parent_product_line->product_line_id])
                        ->scalar();
                }
            }elseif($pur_product_line && $pur_product_line->linelist_parent_id == 0){
                $buyer = PurchaseCategoryBind::find()->select('buyer_name')
                    ->where(['category_id' => $pur_product_line->product_line_id])
                    ->scalar();
            }
        }

        return !empty($buyer)?$buyer:'';
    }
}
