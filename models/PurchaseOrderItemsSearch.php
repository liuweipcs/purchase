<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\PurchaseOrder;
use app\models\PurchaseOrderItems;
use app\models\DeclareCustoms;
use app\models\PurchaseTicketOpen;
use yii\db\Query;

/**
 * PurchaseOrderItemsSearch represents the model behind the search form about `app\models\PurchaseOrderItems`.
 */
class PurchaseOrderItemsSearch extends PurchaseOrderItems
{
    public $declare_name;
    public $buyer;
    public $custom_number;
    public $invoice_code;
    public $start_open_time;
    public $end_open_time;
    public $open_time;
    public $key_id;
    public $ticket_name;
    public $issuing_office;
    public $total_par;
    public $tickets_number;
    public $note;
    public $status;
    public $item_id;
    public $supplier_code;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pur_number', 'sku', 'name', 'open.status', 'order.purchase_type','supplier_code','open_time','invoice_code','custom_number','declare_name'], 'safe'],
        ];
    }
    public function attributes()
    {
        // 添加关联字段到可搜索属性集合
        return array_merge(parent::attributes(), ['open.status', 'order.purchase_type']);
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
     * 含税采购跟踪
     */
    public function search($params)
    {
        $query = PurchaseOrderItemsSearch::find();
        $query->alias('poi');
        $query->leftJoin(PurchaseOrder::tableName().' as order','order.pur_number=poi.pur_number');
        $query->where(['order.purchase_type'=>3, 'order.is_drawback' => 2, 'order.warehouse_code'=> 'TS']);
        $query->andWhere(['not in', 'order.purchas_status', [4, 10]]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $open_status = $this->getAttribute('open.status');

        if ($open_status == 1) {// 未完成
            $query->andWhere([
                'or',
                "(SELECT COUNT(1) FROM pur_purchase_ticket_open open WHERE (open.status IN (0,1)) AND (open.pur_number=poi.pur_number AND open.sku=poi.sku))>0",
                "(SELECT COUNT(1) FROM pur_purchase_ticket_open open WHERE (open.pur_number=poi.pur_number AND open.sku=poi.sku))=0"
            ]);
        } elseif ($open_status == 2) {// 已完成
            // pur_purchase_ticket_open.status 全部已审核，pur_purchase_ticket_open个数=pur_declare_customs个数
            $query->andWhere("(SELECT COUNT(1) FROM pur_purchase_ticket_open open WHERE (open.status IN (2, 3)) AND (open.pur_number=poi.pur_number AND open.sku=poi.sku))>0");
            $query->andWhere("(SELECT COUNT(1) FROM pur_purchase_ticket_open open WHERE (open.status IN (0,1)) AND (open.pur_number=poi.pur_number AND open.sku=poi.sku))=0");
            $query->andWhere("(SELECT COUNT(1) FROM pur_purchase_ticket_open open WHERE (open.pur_number=poi.pur_number AND open.sku=poi.sku))="
                            ."(SELECT COUNT(1) FROM pur_declare_customs dec_cus WHERE (dec_cus.pur_number=poi.pur_number AND dec_cus.sku=poi.sku))");
        }

        // grid filtering conditions
        $query->andFilterWhere(['like', 'poi.pur_number', trim($this->pur_number)])
            // ->andFilterWhere(['like', 'poi.name', $this->name])
            ->andFilterWhere(['like', 'poi.sku', trim($this->sku)]);

        //供应商
        if(!empty($params['PurchaseOrderItemsSearch']['supplier_code'])){
            $query->andWhere(['order.supplier_code'=>$params['PurchaseOrderItemsSearch']['supplier_code']]);
        }
        //开票日期
        if(!empty($params['PurchaseOrderItemsSearch']['open_time'])){
            $query->leftJoin(PurchaseTicketOpen::tableName().' as open',"open.pur_number=poi.pur_number AND open.sku=poi.sku");
            $arr_open_time = explode('~',$params['PurchaseOrderItemsSearch']['open_time']);
            $open_time_start = date('Y-m-d 00:00:00', strtotime(trim($arr_open_time[0])));
            $open_time_end = date('Y-m-d 23:59:59', strtotime(trim($arr_open_time[1])));
            $query->andWhere(['between', 'open.open_time', $open_time_start, $open_time_end]);
        }
        //采购员
        if(!empty($params['PurchaseOrderItemsSearch']['buyer'])){
            $query->andWhere(['order.buyer'=>$params['PurchaseOrderItemsSearch']['buyer']]);
        }
        //报关品名
        if(!empty($params['PurchaseOrderItemsSearch']['declare_name']) || !empty($params['PurchaseOrderItemsSearch']['custom_number'])){
            $query->leftJoin('pur_declare_customs customs', 'customs.pur_number=poi.pur_number and customs.sku=poi.sku');
        }
        if(!empty($params['PurchaseOrderItemsSearch']['declare_name'])){
            $query->andWhere(['customs.declare_name'=>$params['PurchaseOrderItemsSearch']['declare_name']]);
        }
        //报关单号
        if(!empty($params['PurchaseOrderItemsSearch']['custom_number'])){
            $query->andWhere(['customs.custom_number'=>$params['PurchaseOrderItemsSearch']['custom_number']]);
        }

        // vd($query->createCommand()->getRawSql());
        return $dataProvider;
    }
    /**
     * 报关&开票
     */
    public function search2($params)
    {
        $query = PurchaseOrderItemsSearch::find();
        $query->select('poi.id as item_id,poi.pur_number,poi.sku,poi.name,poi.product_img,poi.price,declare.key_id,open.id as id,open.open_time,open.ticket_name,open.issuing_office,open.total_par,open.tickets_number,open.invoice_code,open.note,open.status');
        $query->alias('poi');
        $query->leftJoin(PurchaseOrder::tableName().' as order','order.pur_number=poi.pur_number');
        $query->leftJoin(DeclareCustoms::tableName().' as declare',"declare.pur_number=poi.pur_number AND declare.sku=poi.sku");
        $query->leftJoin(PurchaseTicketOpen::tableName().' as open',"open.pur_number=poi.pur_number AND open.sku=poi.sku AND open.key_id=declare.key_id");
        $query->where(['order.purchase_type'=>3, 'order.is_drawback' => 2, 'order.warehouse_code'=> 'TS']);
        $query->andWhere(['not in', 'order.purchas_status', [4, 10]]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        if(trim($this->getAttribute('open.status')) === "0"){
            $query->andFilterWhere(['or',['open.status' => trim($this->getAttribute('open.status'))],'open.status IS NULL']);
        }else{
            $query->andFilterWhere(['open.status' => trim($this->getAttribute('open.status'))]);
        }

        $query->andFilterWhere(['like', 'poi.pur_number', trim($this->pur_number)])
            // ->andFilterWhere(['like', 'poi.name', $this->name])
            ->andFilterWhere(['like', 'poi.sku', trim($this->sku)]);

        //供应商
        if(!empty($params['PurchaseOrderItemsSearch']['supplier_code'])){
            $query->andWhere(['order.supplier_code'=>$params['PurchaseOrderItemsSearch']['supplier_code']]);
        }
        //开票日期
        if(!empty($params['PurchaseOrderItemsSearch']['open_time'])){
            $arr_open_time = explode('~',$params['PurchaseOrderItemsSearch']['open_time']);
            $open_time_start = date('Y-m-d 00:00:00', strtotime(trim($arr_open_time[0])));
            $open_time_end = date('Y-m-d 23:59:59', strtotime(trim($arr_open_time[1])));
            $query->andWhere(['between', 'open.open_time', $open_time_start, $open_time_end]);
        }
        //采购员
        if(!empty($params['PurchaseOrderItemsSearch']['buyer'])){
            $query->andWhere(['order.buyer'=>$params['PurchaseOrderItemsSearch']['buyer']]);
        }
        //发票编码
        if(!empty($params['PurchaseOrderItemsSearch']['invoice_code'])){
            $query->andWhere(['open.invoice_code'=>$params['PurchaseOrderItemsSearch']['invoice_code']]);
        }
        //报关品名
        if(!empty($params['PurchaseOrderItemsSearch']['declare_name']) || !empty($params['PurchaseOrderItemsSearch']['custom_number'])){
            $query->leftJoin('pur_declare_customs customs', 'customs.pur_number=poi.pur_number and customs.sku=poi.sku');
        }
        if(!empty($params['PurchaseOrderItemsSearch']['declare_name'])){
            $query->andWhere(['customs.declare_name'=>$params['PurchaseOrderItemsSearch']['declare_name']]);
        }
        //报关单号
        if(!empty($params['PurchaseOrderItemsSearch']['custom_number'])){
            $query->andWhere(['customs.custom_number'=>$params['PurchaseOrderItemsSearch']['custom_number']]);
        }

        return $dataProvider;
    }
}
