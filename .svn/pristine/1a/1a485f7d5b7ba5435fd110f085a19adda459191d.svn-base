<?php

namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use Yii;
use yii\base\Exception;
use yii\web\HttpException;

/**
 * This is the model class for table "pur_supplier_buyer".
 *
 * @property integer $id
 * @property string $supplier_code
 * @property integer $type
 * @property string $buyer
 * @property integer $status
 * @property string $supplier_name
 */
class SupplierBuyer extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_supplier_buyer';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'type', 'status'], 'integer'],
            [['type'],'required','message'=>'部门不能为空'],
            [['supplier_code', 'buyer'], 'string', 'max' => 255],
            [['supplier_name'], 'string', 'max' => 500],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'supplier_code' => 'Supplier Code',
            'type' => 'Type',
            'buyer' => 'Buyer',
            'status' => 'Status',
            'supplier_name' => 'Supplier Name',
        ];
    }

    /**
     * 保存采购员
     * @param $data
     * @param $supplier
     * @throws HttpException
     */
    public function saveSupplierBuyer($data,$supplier,$bool=false)
    {
        if ($bool) {

        } else {
            SupplierBuyer::updateAll(['status'=>2],['supplier_code'=>$supplier['code']]);
        }
        if(isset($data['SupplierBuyer'])&&!empty($data['SupplierBuyer']))
        {
            if(isset($data['SupplierBuyer']['type'])&&!empty($data['SupplierBuyer']['type']))
            {
                foreach($data['SupplierBuyer']['type'] as $k=>$value)
                {
                    $model = SupplierBuyer::find()->andFilterWhere(['supplier_code'=>$supplier['code'],'type'=>$value])->one();
                    if(empty($model))
                    {
                        $model = new  self;
                        $model->type  = $value;
                        $model->buyer = $data['SupplierBuyer']['buyer'][$value-1];
                        $model->supplier_code = $supplier['code'];
                        $model->supplier_name = $supplier['name'];
                        $model->status        = 1;
                        if ($bool) {
                            $old_supplier_buyer_info[$k] = $model->oldAttributes;
                            $new_supplier_buyer_info[$k] = $model->attributes;
                        } else {
                            $model->save(false);
                        }
                    } else {
                        $model->supplier_code = $supplier['code'];
                        $model->supplier_name = $supplier['name'];
                        $model->buyer = $data['SupplierBuyer']['buyer'][$value-1];
                        $model->status        = 1;
                        if ($bool) {
                            $old_supplier_buyer_info[$k] = $model->oldAttributes;
                            $new_supplier_buyer_info[$k] = $model->attributes;
                        } else {
                            $model->save(false);
                        }
                    }
                }

                if ($bool) {
                    return ['old'=>$old_supplier_buyer_info,'new'=>$new_supplier_buyer_info];
                }
            }else{
                throw new HttpException('500','部门不能为空');
            }
        }
    }

    /**
     * 获取国内仓采购员的名字
     * @param $code
     * @param int $type
     * @return false|null|string
     */
    public static function getBuyer($code,$type=1)
    {
        return self::find()->select('buyer')->where(['supplier_code'=>$code,'type'=>$type,'status'=>1])->scalar();
    }
}
