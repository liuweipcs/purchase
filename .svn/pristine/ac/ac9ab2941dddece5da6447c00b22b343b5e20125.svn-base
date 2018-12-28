<?php

namespace app\api\v1\models;

use app\config\Vhelper;
use app\services\BaseServices;
use Yii;
use yii\behaviors\TimestampBehavior;
/**
 * This is the model class for table "{{%supplier_quotes}}".
 *
 * @property integer $id
 * @property string  $suppliercode
 * @property string  $product_sku
 * @property string  $product_number
 * @property double  $supplierprice
 * @property integer $currency
 * @property integer $minimum_purchase_amount
 * @property integer $purchase_delivery
 * @property integer $purchasing_units
 * @property string  $business_order_number
 * @property integer $number_operations
 * @property integer $default_buyer
 * @property integer $add_time
 * @property integer $default_vendor
 * @property integer $default_Merchandiser
 * @property integer $add_user
 * @property string  $supplier_product_address
 * @property integer $category_id
 * @property integer $status
 */
class SupplierQuotes extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%supplier_quotes}}';
    }

    /**
     * 保存通途供应商数据
     */
    public static function SaveTongTool($data)
    {


            foreach ($data as $k => $datass)
            {
               $model = self::find()->where(['product_sku'=>$datass['goodsSku'],'suppliercode'=>BaseServices::getSupplierCode($datass['supplierName'],'supplier_code')])->one();

                if($model)
                {
                    continue;
                } else {
                    $model                           = new  self;
                    $model->suppliercode             = BaseServices::getSupplierCode($datass['supplierName'],'supplier_code');
                    $model->product_sku              = $datass['goodsSku'];
                    $model->supplierprice            = $datass['price'];
                    $model->currency                 = $datass['currency'] == 'CNY' ? 'RMB' : $datass['currency'];
                    $model->add_time                 = time();
                    $model->default_buyer            = 1;
                    $model->default_Merchandiser     = 1;
                    $model->add_user                 = 1;
                    $model->supplier_product_address = !empty($datass['purchaseLink'])?$datass['purchaseLink']:'http://www.1688.com';
                    $model->save();

                }


            }

    }
    /**
     * 保存通途供应商数据- 来源于产品接口
     */
    public static function SaveTongTools($data)
    {


        foreach ($data as $k => $datass)
        {
            $product = new  TongProduct();
            $product->goods_ave_cost = $datass['goodsAveCost'];
            $product->category_name = $datass['categoryName'];
            $product->supplier_name = $datass['supplierName'];
            $product->goods_cur_cost = $datass['goodsCurCost'];
            $product->product_code = $datass['productCode'];
            $product->goods_weight = $datass['goodsWeight'];
            $product->product_name = $datass['productName'];
            $product->save(false);
            if(!empty($datass['supplierName']))
            {
                $model = self::find()->where(['product_sku' => $datass['productCode'], 'suppliercode' => BaseServices::getSupplierCode($datass['supplierName'], 'supplier_code')])->one();


                if ($model) {
                   /* $models                = new ProductProvider();
                    $models->sku           = $datass['productCode'];
                    $models->supplier_code = BaseServices::getSupplierCode($datass['supplierName'], 'supplier_code');
                    $models->is_supplier   = 1;
                    $models->is_exemption  = 1;
                    $models->is_push       = 0;
                    $models->save();*/
                    continue;
                } else {
                    $models = ProductProvider::find()->where(['sku'=>$datass['productCode'],'is_supplier'=>1])->one();
                    if($models){
                        continue;
                    }
                    $model                           = new  self;
                    $model->suppliercode             = BaseServices::getSupplierCode($datass['supplierName'], 'supplier_code');
                    $model->product_sku              = $datass['productCode'];
                    $model->supplierprice            = $datass['goodsCurCost'];
                    $model->currency                 = 'RMB';
                    $model->add_time                 = time();
                    $model->default_buyer            = 1;
                    $model->default_Merchandiser     = 1;
                    $model->add_user                 = 1;
                    $model->supplier_product_address = isset($datass['purchaseLink']) ? $datass['purchaseLink'] : 'https://www.1688.com';
                    $model->save();

                    $models                = new ProductProvider();
                    $models->sku           = $datass['productCode'];
                    $models->supplier_code = BaseServices::getSupplierCode($datass['supplierName'], 'supplier_code');
                    $models->is_supplier   = 1;
                    $models->is_exemption  = 1;
                    $models->is_push       = 0;
                    $models->quotes_id     = $model->attributes['id'];
                    $models->save();

                }
            } else{
                continue;
            }


        }

    }

}
