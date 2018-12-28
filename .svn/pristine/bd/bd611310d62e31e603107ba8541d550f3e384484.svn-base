<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "product_repackage".
 */
class ProductRepackage extends BaseModel
{
    public $file_execl;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product_repackage}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sku'], 'required'],
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
            'pur_number' => 'Pur Number',
            'audit_time' => 'Audit Time',
            'instock_time' => 'Instock Time',
            'create_time' => 'Create Time',
            'is_calc' => 'Is Calc',
        ];
    }


    /**
     * 获取产品信息
     * @return \yii\db\ActiveQuery
     */
    public function getProductInfo(){

        return $this->hasOne(Product::className(),['sku' => 'sku']);

    }

    /**
     * 获取默认供应商
     * @return $this
     */
    public function getDefaultSupplier(){
        return $this->hasOne(ProductProvider::className(), ['sku' => 'sku'])->where(['is_supplier'=>1]);

    }


    /**
     * 验证并保存 SKU 二次包装信息
     * @param string $sku
     * @param int $sku_type   采购类型(1国内2海外3FBA)
     * @return array
     *      array(
     *          'status' => 'success|exists|none'  // 保存成功|已存在|SKU不存在
     *          'message' => 'sku 二次包装记录已存在'
     *      )
     */
    public static function addSkuInfo($sku,$sku_type){
        $sku = trim($sku);
        $now_date = date('Y-m-d H:i:s');

        $result = array('status' => '','message' => '');

        $old_model  = self::findOne(['sku' => $sku,'sku_type' => $sku_type]);
        if($old_model){// 已经存在
            if($old_model->status == 2){// 2.已经删除
                $old_model->status          = 1;
                $old_model->add_user        = Yii::$app->user->id;
                $old_model->add_time        = $now_date;
                $old_model->audit_status    = 0;// 待审核
                $old_model->audit_user      = '';
                $old_model->audit_time      = '0000-00-00 00:00:00';

                $old_model->save();
                $result['status'] = 'success';
            }else{
                $result['status'] = 'exists';
                $result['message'] = 'sku 二次包装记录已存在';
            }
        }else{
            $productModel = Product::findOne(['sku' => $sku]);
            if(empty($productModel)){
                $result['status'] = 'none';
                $result['message'] = 'sku 信息不存在';
            }else{
                $model                  = new self();
                $model->sku             = $sku;
                $model->sku_type        = $sku_type;
                $model->audit_status    = 0;
                $model->add_user        = Yii::$app->user->id;
                $model->add_time        = $now_date;

                $model->save(false);
                $result['status'] = 'success';
            }

        }
        return $result;
    }

}
