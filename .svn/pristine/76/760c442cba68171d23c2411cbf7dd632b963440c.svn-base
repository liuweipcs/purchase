<?php

namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\PurchaseOrderReceipt;

/**
 * PurchaseOrderReceiptSearch represents the model behind the search form about `app\models\PurchaseOrderReceipt`.
 */
class PurchaseOrderReceiptSearch extends PurchaseOrderReceipt
{
    public $supplier_special_flag;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pur_number', 'requisition_number', 'supplier_code', 'pay_name', 'notice', 'application_time', 'review_time', 'processing_time','pay_status','start_time','end_time','pay_type','applicant','payer','supplier_special_flag'], 'safe'],
            [['pay_price'], 'number'],
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
        $query = PurchaseOrderReceipt::find();
        //Vhelper::dump($params);
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        if(!empty($params['PurchaseOrderReceiptSearch']['buyer'])){
            $query->leftJoin('pur_purchase_order','pur_purchase_order.pur_number=pur_purchase_order_receipt.pur_number')
                  ->where(['pur_purchase_order.buyer' => $params['PurchaseOrderReceiptSearch']['buyer']]);
        }

        $query->orderBy('pur_purchase_order_receipt.id desc');
        // grid filtering conditions
        $query->andFilterWhere([
            'pur_purchase_order_receipt.pay_status'        => $this->pay_status,
            'pur_purchase_order_receipt.pay_type'        => $this->pay_type,
            'pur_purchase_order_receipt.settlement_method' => $this->settlement_method,
            'pur_purchase_order_receipt.applicant'         => $this->applicant,
            'pur_purchase_order_receipt.payer'         => $this->payer,
            'pur_purchase_order_receipt.supplier_code' => $this->supplier_code,
        ]);

        $query->andFilterWhere(['like', 'pur_purchase_order_receipt.requisition_number', trim($this->requisition_number)])
            ->andFilterWhere(['like', 'pur_purchase_order_receipt.supplier_code', $this->supplier_code]);
        if($this->supplier_special_flag !== '' AND $this->supplier_special_flag !== NULL){
            $query->joinWith('supplier');
            $query->andWhere(['=', 'pur_supplier.supplier_special_flag', $this->supplier_special_flag]);
        }
        //批量查找采购单号
        if(strpos(trim($this->pur_number),' ')){
            $pur_number = preg_replace("/\s+/",',',trim($this->pur_number));
            $pur_number = explode(',',$pur_number);
            if(count($pur_number)>0){
                $query->andFilterWhere(['in', 'pur_purchase_order_receipt.pur_number', $pur_number]);
            }else{
                $query->andFilterWhere(['like', 'pur_purchase_order_receipt.pur_number', trim($this->pur_number)]);
            }
        }else{
            $query->andFilterWhere(['like', 'pur_purchase_order_receipt.pur_number', trim($this->pur_number)]);
        }

        if (!empty($this->start_time)) {
            $this->start_time = $this->start_time . ' 00:00:00';
            $this->end_time = $this->end_time . ' 23:59:59';
            $query->andFilterWhere(['between', 'pur_purchase_order_receipt.application_time', $this->start_time, $this->end_time]);
        } else {
            $query->andFilterWhere(['between', 'pur_purchase_order_receipt.application_time', date('Y-m-d H:i:s',strtotime("last month")), date('Y-m-d H:i:s',time())]);
        }
        return $dataProvider;
    }
}
