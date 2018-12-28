<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "pur_supplier_deliver_adress".
 *
 * @property integer $id
 * @property string $supplier_code
 * @property string $province
 * @property string $city
 * @property string $area
 * @property string $adress
 * @property integer $is_visible
 * @property integer $is_check
 * @property string $change_reason
 * @property string $pur_number
 * @property string $order_number
 * @property string $create_time
 * @property integer $items_id
 */
class SupplierDeliverAdress extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_supplier_deliver_adress';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['is_visible', 'is_check', 'items_id'], 'integer'],
            [['create_time'], 'safe'],
            [['supplier_code', 'province', 'city', 'area', 'order_number'], 'string', 'max' => 100],
            [['adress', 'change_reason'], 'string', 'max' => 255],
            [['pur_number'], 'string', 'max' => 50],
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
            'province' => 'Province',
            'city' => 'City',
            'area' => 'Area',
            'adress' => 'Adress',
            'is_visible' => 'Is Visible',
            'is_check' => 'Is Check',
            'change_reason' => 'Change Reason',
            'pur_number' => 'Pur Number',
            'order_number' => 'Order Number',
            'create_time' => 'Create Time',
            'items_id' => 'Items ID',
        ];
    }

    public function getSupplier(){
        return $this->hasOne(Supplier::className(),['supplier_code'=>'supplier_code']);
    }

    public function search($params,$noDataProvider=false){
        $query = self::find();

        // add conditions that should always apply here
        $pageSize = isset($params['per-page']) ? intval($params['per-page']) : 20;
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);
        $query->where(['is_visible'=>1]);
        $query->where(['is_check'=>1]);
        $query->groupBy('supplier_code');
        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }
        \Yii::$app->session->set('SupplierSearch', $params);
        if ($noDataProvider)
            return $query;
        return $dataProvider;
    }
}
