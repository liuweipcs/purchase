<?php

namespace app\api\v1\models;

use app\models\SkuSalesStatisticsTotal;
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
class Stock extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_stock';
    }
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                    //\yii\db\ActiveRecord::EVENT_BEFORE_UPDATE => ['update_at'],
                ],
                // if you're using datetime instead of UNIX timestamp:
                'value' => date('Y-m-d H:i:s',time()),
            ],
        ];
    }
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
    /**
     * 新增数据
     * @param $model
     * @param $datass
     * @return mixed
     */
    public  static function SaveOne($model,$datass)
    {

        $model->sku                      = $datass['sku'];
        #$model->stock                    = $datass['stock'];
        $model->stock                    = 0;
        $model->on_way_stock             = $datass['on_way_stock'];
        #$model->available_stock          = $datass['available_stock'];
        $model->available_stock          = $datass['stock'];
        $model->warehouse_code           = $datass['warehouse_code'];
        $model->update_at                = date('Y-m-d H:i:s',time());
        //$model->left_stock               = $datass['left_stock'];
        $model->is_suggest               = 0;

        $status =$model->save();

        return $status;
    }

    /**
     * 用于通途
     * @param mixed $datass
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function FindOness($datass)
    {

        foreach ($datass as $k=>$v)
        {
            $model= self::find()->where(['sku'=>$v['goodsSku']])->one();

            if ($model)
            {
                self::SaveOnes($model,$v);

            } else {
                $model =new self;
                self::SaveOnes($model,$v);

            }
        }




    }
    /**
     * 用于通途
     * @param $model
     * @param $datass
     * @return mixed
     */
    public  static function SaveOnes($model,$datass)
    {

        $model->sku                      = $datass['goodsSku'];
        //可用+待发
        $model->stock                    = $datass['availableStockQuantity'] + $datass['waitingShipmentStockQuantity'];
        //在途
        $model->on_way_stock             = $datass['intransitStockQuantity'];
        //可用
        $model->available_stock          = !empty($datass['availableStockQuantity'])?$datass['availableStockQuantity']:0;
        //仓库编码
        $model->warehouse_code           = 'SZ_AA';
        //欠货
        $model->left_stock               = $datass['defectsStockQuantity'];
        $model->is_suggest               = 0;

        $status =$model->save();

        return $status;
    }

    /**
     * 更新欠货库存
     * @param $data
     * @return array
     */
    public static  function  SaveOness($data)
    {
        $arr =[];
        if(is_array($data))
        {
            foreach($data as $v)
            {

                $model= self::find()->where(['warehouse_code'=>trim($v['warehouse_code']),'sku'=>trim($v['sku'])])->one();
                if($model)
                {
                    $total   = $model->available_stock + $model->on_way_stock;
                    $left_stock = $total-$v['lack_quantity'];
                    if($left_stock >0)
                    {
                        $model->left_stock = 0;
                        $arr[]=$model->save(false);
                    } else {
                        $model->left_stock = $left_stock;
                        $arr[]=$model->save(false);
                    }

                } else {
                    //追加到库存
                    $models                 = new self;
                    $models->sku            = trim($v['sku']);
                    $models->warehouse_code = trim($v['warehouse_code']);
                    $models->left_stock     = -($v['lack_quantity']);
                    $models->update_at      = date('Y-m-d H:i:s');
                    $models->save(false);

                    //continue;
                }
            }
        } else{
            exit('不是数组');
        }
        return $arr;
    }

}
