<?php
namespace app\models;

use app\models\base\BaseModel;
use Yii;
/**
 * This is the model class for table "{{%purchase_cancel_quantity}}".
 *
 * @property string $id
 * @property string $pur_number
 * @property string $sku
 * @property integer $cly
 * @property string $create_time
 * @property integer $purchase_type
 *
 * @property PurchaseOrder $purNumber
 */
class PurchaseCancelQuantity extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_cancel_quantity}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pur_number', 'sku'], 'required'],
            [['cly', 'purchase_type'], 'integer'],
            [['create_time'], 'safe'],
            [['pur_number'], 'string', 'max' => 100],
            [['sku'], 'string', 'max' => 30],
            [['pur_number', 'sku'], 'unique', 'targetAttribute' => ['pur_number', 'sku'], 'message' => 'The combination of Pur Number and Sku has already been taken.'],
            [['pur_number'], 'exist', 'skipOnError' => true, 'targetClass' => PurchaseOrder::className(), 'targetAttribute' => ['pur_number' => 'pur_number']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pur_number' => 'Pur Number',
            'sku' => 'Sku',
            'cly' => 'Cly',
            'create_time' => 'Create Time',
            'purchase_type' => 'Purchase Type',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPurNumber()
    {
        return $this->hasOne(PurchaseOrder::className(), ['pur_number' => 'pur_number']);
    }
    /**
     * 获取取消数量
     */
    public static function getCly($pur_number,$sku)
    {
        if (!empty($pur_number) && !empty($sku)) {
            $model = self::findOne(['pur_number'=>$pur_number,'sku' => $sku]);
            if (!empty($model)) {
                return $model['cly'];
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }
    /**
     * 保存取消数量
     */
    public function saveCly($data,$purchase_type=1){
        if(!empty($data))
        {
            foreach($data as $v)
            {
                $model = self::find()->where(['pur_number'=>$v['pur_number'],'sku'=>$v['sku']])->one();
                if(!empty($model))
                {
                    $model->cly        = $v['cly'];
                    $model->create_time = date('Y-m-d H:i:s');
                    $model->purchase_type = $purchase_type;
                    $status = $model->save();
                } else {
                    $models             = new self;
                    $models->pur_number = $v['pur_number'];
                    $models->sku        = $v['sku'];
                    $models->cly        = $v['cly'];
                    $models->create_time = date('Y-m-d H:i:s');
                    $models->purchase_type = $purchase_type;
                    $status = $models->save(false);
                }
            }
            return $status;
        } else {
            return false;
        }
    }
}
