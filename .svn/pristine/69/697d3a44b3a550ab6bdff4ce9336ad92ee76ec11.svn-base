<?php

namespace app\api\v1\models;

use app\config\Vhelper;
use Yii;
/**
 * This is the model class for table "pur_warehouse".
 *
 * @property string $id
 * @property string $warehouse_name
 * @property integer $warehouse_type
 * @property integer $is_custody
 * @property string $warehouse_code
 * @property string $country
 * @property string $state
 * @property string $city
 * @property string $address
 * @property string $telephone
 * @property string $fax
 * @property string $zip_code
 * @property string $remark
 * @property integer $use_status
 * @property integer $create_user_id
 * @property integer $modify_user_id
 * @property string $create_time
 * @property string $modify_time
 * @property string $pattern
 */
class Warehouse extends \yii\db\ActiveRecord
{


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_warehouse';
    }

    /**
     *
     * @param mixed $datass
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function FindOnes($datass)
    {
        foreach ($datass as $v)
        {
            $model= self::find()->where(['warehouse_code'=>$v['warehouse_code']])->one();

            if ($model)
            {
                 self::SaveOne($model,$v);
                $data['success_list'][]         = $model->attributes['warehouse_code'];
                $data['failure_list'][]         = '';
            } else {
                $model =new self;
                 self::SaveOne($model,$v);
                $data['success_list'][]         = $model->attributes['warehouse_code'];
                $data['failure_list'][]         = '';
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
        $model->warehouse_name          = $datass['warehouse_name'];
        $model->warehouse_type          = $datass['warehouse_type'];
        $model->is_custody              = $datass['is_custody'];
        $model->warehouse_code          = $datass['warehouse_code'];
        $model->country                 = $datass['country'];
        $model->state                   = $datass['state'];
        $model->city                    = $datass['city'];
        $model->address                 = $datass['address'];
        $model->telephone               = $datass['telephone'];
        $model->fax                     = $datass['fax'];
        $model->zip_code                = $datass['zip_code'];
        $model->remark                  = $datass['remark'];
        $model->use_status              = $datass['use_status'];
        $model->create_user_id          = $datass['create_user'];
        $model->modify_user_id          = $datass['modify_user'];
        $model->create_time             = $datass['create_time'];
        $model->modify_time             = $datass['modify_time'];
        $status =$model->save();

        return $status;
    }


    
}
