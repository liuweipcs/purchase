<?php

namespace app\api\v1\models;

use Yii;
use yii\data\ActiveDataProvider;
/**
 * This is the model class for table "pur_inland_avg_deliery_time".
 *
 * @property integer $id
 * @property string $sku
 * @property integer $avg_delivery_time
 * @property string $update_time
 */
class InlandAvgDelieryTime extends \yii\db\ActiveRecord
{
    public $supplier_code;
    public $product_status;
    public $supplier_name;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_inland_avg_deliery_time';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sku', 'update_time'], 'required'],
            [['avg_delivery_time'], 'integer'],
            [['update_time'], 'safe'],
            [['sku'], 'string', 'max' => 150],
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
            'avg_delivery_time' => 'Avg Delivery Time',
            'update_time' => 'Update Time',
        ];
    }

    public function search($params,$noDataProvider=false){
        $query = self::find();
        $query->select(['sku'=>'t.sku',
            'avg_delivery_time'=>'t.avg_delivery_time',
            'supplier_name'=>'s.supplier_name',
            'product_status'=>'p.product_status'
        ]);
        $query->alias('t');
        $pageSize = isset($params['per-page']) ? intval($params['per-page']) : 20;
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);
        $this->load($params);
        $query->leftJoin(Product::tableName().' p','t.sku=p.sku');
        $query->leftJoin(ProductProvider::tableName().' pp','pp.sku=t.sku');
        $query->leftJoin(Supplier::tableName().' s','pp.supplier_code=s.supplier_code');
        $query->where(['pp.is_supplier'=>1]);
        $query->andFilterWhere(['p.product_status'=>$this->product_status]);
        $query->andFilterWhere(['pp.supplier_code'=>$this->supplier_code]);
        $query->andFilterWhere(['t.sku'=>$this->sku]);
        \Yii::$app->session->set('Inland_avg_deliver_time_search', $params);
        if ($noDataProvider)
            return $query;
        return $dataProvider;
    }
}
