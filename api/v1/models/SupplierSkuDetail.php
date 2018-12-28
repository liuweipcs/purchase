<?php

namespace app\api\v1\models;

use app\models\SkuSalesStatisticsTotal;
use app\models\SupplierBuyer;
use Yii;
use yii\behaviors\TimestampBehavior;
use app\config\Vhelper;
/**
 * This is the model class for table "pur_stock".
 *
 * @property string $id
 * @property string $sku
 * @property string $warehouse_code
 * @property string $on_way_stock
 * @property string $available_stock
 * @property string $stock
 * @property integer $left_stock
 * @property string $created_at
 * @property string $update_at
 */
class SupplierSkuDetail extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_supplier_detail';
    }
//     public function behaviors()
//     {
//         return [
//             [
//                 'class' => TimestampBehavior::className(),
// //                 'attributes' => [
// //                     \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
//                     //\yii\db\ActiveRecord::EVENT_BEFORE_UPDATE => ['update_at'],
// //                 ],
//                 // if you're using datetime instead of UNIX timestamp:
//                 'value' => date('Y-m-d H:i:s',time()),
//             ],
//         ];
//     }
    /**
     *
     * @param mixed $datass
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function FindOnes($datass)
    {

        foreach ($datass as $k=>$v)
        {
            $model= self::find()->where(['warehouse_code'=>$v['warehouse_code'],'sku'=>$v['sku']])->one();

            if ($model)
            {
                self::SaveOne($model,$v);
                $data['success_list'][$k]['warehouse_code']       = $model->attributes['warehouse_code'];
                $data['success_list'][$k]['sku']                  = $model->attributes['sku'];
                $data['failure_list'][]                            = '';
            } else {
                $model =new self;
                self::SaveOne($model,$v);
                $data['success_list'][$k]['warehouse_code']        = $model->attributes['warehouse_code'];
                $data['success_list'][$k]['sku']                   = $model->attributes['sku'];
                $data['failure_list'][]                             = '';
            }
        }

        return $data;


    }

    public function getSkuSales(){
    	return $this->hasOne(SkuSalesStatistics::className(), ['sku' => 'sku']);
    
    }
    public function getSupplierbuyer(){
        return $this->hasMany(SupplierBuyer::className(), ['supplier_code' => 'supplier_code']);
    }
}
