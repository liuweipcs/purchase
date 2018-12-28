<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "pur_product_img_download".
 *
 * @property integer $id
 * @property string $sku
 * @property string $image_url
 * @property integer $status
 */
class ProductImgDownload extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_product_img_download';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sku', 'image_url'], 'required'],
            [['status'], 'integer'],
            [['sku'], 'string', 'max' => 50],
            [['image_url'], 'string', 'max' => 150],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sku' => 'Sku',
            'image_url' => 'Image Url',
            'status' => 'Status',
        ];
    }
}
