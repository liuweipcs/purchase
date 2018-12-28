<?php

namespace app\api\v1\models;

use app\models\PurchaseAbnormals;
use Yii;
use app\config\Vhelper;
/**
 * This is the model class for table "pur_purchase_abnomal".
 *
 * @property string $id
 * @property string $express_no
 * @property string $package_qty
 * @property string $send_addr
 * @property string $send_name
 * @property integer $status
 * @property integer $is_del
 * @property string $note
 * @property string $create_user
 * @property string $create_time
 * @property string $update_time
 */
class PurchaseAbnomal extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_purchase_abnomal';
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
                //如果仓库ID存在就更新
                $model= self::find()->where(['wms_id'=>$v['wms_id']])->one();

                if ($model)
                {
                    self::SaveOne($model,$v);
                    $data['success_list'][$k]['wms_id']                 = $model->attributes['wms_id'];
                    $data['failure_list'][]                             = '';
                } else {
                    //如果快递单号存在就更新
                    $models= self::find()->where(['express_no'=>$v['express_no']])->one();
                    if($models)
                    {
                        self::SaveOne($model,$v);
                        $data['success_list'][$k]['express_no']                 = $model->attributes['express_no'];
                        $data['failure_list'][]                             ='';
                    } else{
                        $model =new self;
                        self::SaveOne($model,$v);
                        $data['success_list'][$k]['wms_id']                 = $model->attributes['wms_id'];
                        $data['failure_list'][]                             = '';
                        $datas = [
                            'title'=>'快递单'.$v['express_no'].'出现了PO异常了,请及时处理',
                            'content'=>'快递单'.$v['express_no'].'出现了PO异常了,请及时处理',
                            'pur_number'=>$v['express_no'],
                            'type'=>'3',
                        ];
                        PurchaseAbnormals::Saves($datas);
                    }

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

        $model->express_no                      = $datass['express_no'];
        $model->package_qty                     = $datass['package_qty'];
        $model->send_addr                       = $datass['send_addr'];
        $model->send_name                       = $datass['send_name'];
        $model->status                          = $model->status==4?$model->status:3;
        $model->is_del                          = $datass['is_del'];
        $model->wms_id                          = $datass['wms_id'];
        $model->img                             = $datass['img'];
        $model->note                            = $datass['note'];
        $model->buyer                           = isset($datass['buyer'])?$datass['buyer']:'';
        $model->create_user                     = $datass['create_user'];
        $model->create_time                     = $datass['create_time'];
        $model->is_push                         = $model->is_push==1?$model->is_push:0;
        $model->update_time                     = !empty($datass['update_time'])?$datass['update_time']:date('Y-m-d H:i:s');
        $status =$model->save();
        return $status;
    }
}
