<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "pur_supplier_check_sku".
 *
 * @property integer $id
 * @property integer $check_id
 * @property integer $type
 * @property string $sku
 * @property string $complaint_rate
 * @property string $complaint_point
 * @property integer $check_num
 * @property string $check_rate
 * @property string $check_standard
 * @property integer $bad_goods
 * @property integer $status
 */
class SupplierCheckSku extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_supplier_check_sku';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['check_id', 'type', 'sku', 'check_rate'], 'required'],
            [['check_id', 'type', 'check_num', 'bad_goods', 'status'], 'integer'],
            [['complaint_rate'], 'number'],
            [['sku','check_rate'], 'string', 'max' => 100],
            [['complaint_point', 'check_standard'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'check_id' => 'Check ID',
            'type' => 'Type',
            'sku' => 'Sku',
            'complaint_rate' => 'Complaint Rate',
            'complaint_point' => 'Complaint Point',
            'check_num' => 'Check Num',
            'check_rate' => 'Check Rate',
            'check_standard' => 'Check Standard',
            'bad_goods' => 'Bad Goods',
            'status' => 'Status',
        ];
    }

    public function getProduct(){
        return self::hasOne(Product::className(),['sku'=>'sku']);
    }
    public function getProductDesc(){
        return self::hasOne(ProductDescription::className(),['sku'=>'sku']);
    }
    public static function saveData($data,$check_id){
        $model = new self();
        $model->check_id = $check_id;
        $model->type = $data['type'];
        $model->sku = $data['sku'];
        $model->complaint_rate = $data['complaint_rate'];
        $model->purchase_num = $data['purchase_num'];
        $model->complaint_point = $data['complaint_point'];
        $model->check_num = $data['check_num'];
        $model->check_rate = $data['check_rate'];
        $model->check_standard = $data['check_standard'];
        $model->status = 0;
        return $model->save();
    }
}
