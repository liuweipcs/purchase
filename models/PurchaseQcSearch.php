<?php

namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\PurchaseQc;
use yii\helpers\ArrayHelper;

/**
 * PurchaseQcSearch represents the model behind the search form about `app\models\PurchaseQc`.
 */
class PurchaseQcSearch extends PurchaseQc
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'qty', 'delivery_qty', 'presented_qty', 'check_qty', 'good_products_qty', 'bad_products_qty', 'check_type'], 'integer'],
            [['express_no', 'pur_number', 'warehouse_code', 'supplier_code', 'supplier_name', 'sku', 'name', 'buyer', 'handle_type', 'note', 'created_at', 'creator', 'time_handle', 'handler', 'time_audit', 'auditor', 'note_audit','qc_status'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $fields=['*','qty as total_qty','sum(delivery_qty) as total_delivery_qty','sum(presented_qty) as total_presented_qty','sum(check_qty) as total_check_qty','sum(good_products_qty) as total_good_products_qty','sum(bad_products_qty) as total_bad_products_qty',];
        $query = PurchaseQc::find()->orderBy('id desc')->groupBy(['pur_number','express_no'])->select($fields);
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $puid= PurchaseUser::find()->select('pur_user_id')->where(['in','grade',[1,2,3]])->asArray()->all();
        $ids = ArrayHelper::getColumn($puid, 'pur_user_id');
        if(in_array(Yii::$app->user->id,$ids))
        {

        } else {
            $query->andWhere(['in', 'buyer',Yii::$app->user->identity->username]);
        }
        $query->orderBy('created_at desc');
        $this->load($params);
//Vhelper::dump($params);
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'qty' => $this->qty,
            'delivery_qty' => $this->delivery_qty,
            'presented_qty' => $this->presented_qty,
            'check_qty' => $this->check_qty,
            'good_products_qty' => $this->good_products_qty,
            'bad_products_qty' => $this->bad_products_qty,
            'check_type' => $this->check_type,
            'created_at' => $this->created_at,
            'time_handle' => $this->time_handle,
            'time_audit' => $this->time_audit,
            'qc_status' => $this->qc_status,
        ]);

        $query->andFilterWhere(['=', 'express_no', trim($this->express_no)])
            ->andFilterWhere(['like', 'pur_number', trim($this->pur_number)])
            ->andFilterWhere(['like', 'warehouse_code', trim($this->warehouse_code)])
            ->andFilterWhere(['like', 'supplier_code', trim($this->supplier_code)])
            ->andFilterWhere(['like', 'supplier_name', $this->supplier_name])
            ->andFilterWhere(['like', 'sku', $this->sku])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'buyer', trim($this->buyer)])
            ->andFilterWhere(['like', 'handle_type', $this->handle_type])
            ->andFilterWhere(['like', 'note', $this->note])
            ->andFilterWhere(['like', 'creator', $this->creator])
            ->andFilterWhere(['like', 'handler', $this->handler])
            ->andFilterWhere(['like', 'auditor', $this->auditor])
            ->andFilterWhere(['like', 'note_audit', $this->note_audit]);

        return $dataProvider;
    }

    /**
     * qc异常审核
     * @param $params
     * @return ActiveDataProvider
     */
    public function search1($params)
    {
        $fields=['*','qty as total_qty','sum(delivery_qty) as total_delivery_qty','sum(presented_qty) as total_presented_qty','sum(check_qty) as total_check_qty','sum(good_products_qty) as total_good_products_qty','sum(bad_products_qty) as total_bad_products_qty','sum(refund_amount) as total_refund_amount'];
        $query = PurchaseQc::find()->orderBy('id desc')->groupBy(['pur_number','express_no'])->select($fields);
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $query->where(['in','qc_status',[3]]);
        $this->load($params);
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'qty' => $this->qty,
            'delivery_qty' => $this->delivery_qty,
            'presented_qty' => $this->presented_qty,
            'check_qty' => $this->check_qty,
            'good_products_qty' => $this->good_products_qty,
            'bad_products_qty' => $this->bad_products_qty,
            'check_type' => $this->check_type,
            'created_at' => $this->created_at,
            'time_handle' => $this->time_handle,
            'time_audit' => $this->time_audit,
            'qc_status' => $this->qc_status,
        ]);

        $query->andFilterWhere(['like', 'express_no', trim($this->express_no)])
            ->andFilterWhere(['like', 'pur_number', trim($this->pur_number)])
            ->andFilterWhere(['like', 'warehouse_code', trim($this->warehouse_code)])
            ->andFilterWhere(['like', 'supplier_code', trim($this->supplier_code)])
            ->andFilterWhere(['like', 'supplier_name', $this->supplier_name])
            ->andFilterWhere(['like', 'sku', $this->sku])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'buyer', $this->buyer])
            ->andFilterWhere(['like', 'handle_type', $this->handle_type])
            ->andFilterWhere(['like', 'creator', $this->creator])
            ->andFilterWhere(['like', 'handler', $this->handler])
            ->andFilterWhere(['like', 'auditor', $this->auditor])
            ->andFilterWhere(['like', 'note_audit', $this->note_audit]);

        return $dataProvider;
    }
}
