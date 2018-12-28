<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "pur_supplier_kpi_caculte".
 *
 * @property integer $id
 * @property string $supplier_code
 * @property string $month
 * @property integer $settlement
 * @property integer $purchase_times
 * @property string $purchase_total
 * @property integer $sku_purchase_times
 * @property integer $sku_punctual_times
 * @property integer $sku_exception_times
 * @property integer $sku_overseas_exception
 * @property string $sku_up_total
 * @property string $sku_down_rate
 * @property string $sku_up_rate
 * @property string $cacul_date
 * @property string $sku_down_total
 * @property string $punctual_rate
 * @property string $excep_rate
 */
class SupplierKpiCaculte extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_supplier_kpi_caculte';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            //[['supplier_code'], 'required',],
            [['month', 'cacul_date'], 'safe'],
            [['settlement', 'purchase_times', 'sku_purchase_times', 'sku_punctual_times', 'sku_exception_times', 'sku_overseas_exception'], 'integer'],
            [['purchase_total', 'sku_up_total', 'sku_down_rate', 'sku_up_rate', 'sku_down_total','excep_rate','punctual_rate'], 'number'],
            [['supplier_code'], 'string', 'max' => 100],
            [['supplier_code','month'],'safe'],
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
            'month' => 'Month',
            'settlement' => 'Settlement',
            'purchase_times' => 'Purchase Times',
            'purchase_total' => 'Purchase Total',
            'sku_purchase_times' => 'Sku Purchase Times',
            'sku_punctual_times' => 'Sku Punctual Times',
            'sku_exception_times' => 'Sku Exception Times',
            'sku_overseas_exception' => 'Sku Overseas Exception',
            'sku_up_total' => 'Sku Up Total',
            'sku_down_rate' => 'Sku Down Rate',
            'sku_up_rate' => 'Sku Up Rate',
            'cacul_date' => 'Cacul Date',
            'sku_down_total' => 'Sku Down Total',
        ];
    }

    public function getSupplier(){
        return $this->hasOne(Supplier::className(),['supplier_code'=>'supplier_code']);
    }

    public function search($params)
    {
        $query = self::find();
        $query->alias('t');
        $pageSize = isset($params['per-page']) ? intval($params['per-page']) : 20;
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);

        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }
        $query->andFilterWhere(['month'=>$this->month ? date('Y-m-01',strtotime($this->month)) : date('Y-m-01',time())])
              ->andFilterWhere(['supplier_code'=>$this->supplier_code]);
        return $dataProvider;
    }
}
