<?php
namespace app\models;
use app\config\Vhelper;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
class PurchaseOrderPaySearch extends PurchaseOrderPay
{
    public $pur_type;
    public $pur_status;
    public $start_time;
    public $end_time;
    public $order_account;
/*  public $p_start_time;
    public $p_end_time;*/

    public function rules()
    {
        return [
            [['id', 'pur_number', 'requisition_number', 'supplier_code',
                'pur_type', 'pur_status','pay_name', 'notice', 'application_time',
                'review_time', 'processing_time','pay_status','start_time',
                'end_time','payer','applicant','approver','pay_type',
                'settlement_method','payment_cycle','payer_time',
                'source', 'pay_ratio', 'js_ratio', 'real_pay_price', 'images','pay_category'
                ], 'safe'],
            [['pay_price'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * 付款管理
     */
    public function search($params, $purchase_type = null)
    {
        $query=PurchaseOrderPay::find();
        $dataProvider=new ActiveDataProvider([
            'query'=>$query,
        ]);
        $query->orderBy('id desc');
        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }

        //区分请款单类型
        if($purchase_type == 'FBA') {
            $query->andWhere(['like', 'pur_number', 'FBA']);
        }

        if($purchase_type == 'PO') {
            $query->andWhere(['like', 'pur_number', 'PO']);
        }

        if($purchase_type == 'ABD') {
            $query->andWhere(['like', 'pur_number', 'ABD']);
        }
        $query->andWhere(['pur_purchase_order_pay.source' => $this->source]);
        $query->andFilterWhere([
            'id' => $this->id,
            'pay_status'        => $this->pay_status,
            'settlement_method' => $this->settlement_method,
            'pay_price'         => $this->pay_price,
            'pay_type'          => $this->pay_type,
            'applicant'         => $this->applicant,
            'auditor'           => $this->auditor,
            'approver'          => $this->approver,
            'review_time'       => $this->review_time,
            'processing_time'   => $this->processing_time,
        ]);

        $query->andFilterWhere(['like', 'pur_number', trim($this->pur_number)])
              ->andFilterWhere(['like', 'requisition_number', trim($this->requisition_number)])
              ->andFilterWhere(['like', 'supplier_code', $this->supplier_code])
              ->andFilterWhere(['like', 'pay_name', $this->pay_name]);

        if($this->application_time) {
            $times = explode(' - ', $this->application_time);
            $query->andFilterWhere(['>', 'application_time', $times[0]]);
            $query->andFilterWhere(['<', 'application_time', $times[1]]);
            $this->start_time = $times[0];
            $this->end_time = $times[1];
        }
        return $dataProvider;
    }

    public function formName()
    {
        return '';
    }
    /**
     * 付款通知
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search1($params)
    {
        $query = PurchaseOrderPay::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $query->orderBy('id desc');
        $this->load($params);

        $query->joinWith(['purchaseOrder AS items', 'purchaseOrderAccount']);
        //$query->leftJoin('pur_purchase_order',['pur_number'=>'pur_number']);
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        if (!empty($this->pay_status)) {
            $query->where(['in','pur_purchase_order_pay.pay_status',$this->pay_status]);
        } else {
            $query->where(['in','pur_purchase_order_pay.pay_status',['2']]);
        }
        $query->andFilterWhere([
            'pur_purchase_order_pay.pay_status'        => $this->pay_status,
            'pur_purchase_order_pay.settlement_method' => $this->settlement_method,
            'pur_purchase_order_pay.applicant'         => $this->applicant,
            'pur_purchase_order_pay.pay_type'          => $this->pay_type,
            'pur_purchase_order_pay.auditor'           => $this->auditor,
            'pur_purchase_order_pay.approver'          => $this->approver,
            'pur_purchase_order_pay.payment_cycle'     => $this->payment_cycle,
        ]);
        if(!empty($this->pur_type)&&$this->pur_type != 'all'){
            $query->andFilterWhere(['items.purchase_type'=>$this->pur_type]);
        }

        $query->andFilterWhere(['like', 'pur_purchase_order_pay.pur_number', trim($this->pur_number)])
              ->andFilterWhere(['like', 'pur_purchase_order_pay.requisition_number', trim($this->requisition_number)])
              ->andFilterWhere(['like', 'pur_purchase_order_pay.supplier_code', trim($this->supplier_code)])
              ->andFilterWhere(['between', 'pur_purchase_order_pay.application_time', $this->start_time, $this->end_time]);
        return $dataProvider;
    }



    /**
     * 出纳付款
     * 只显示待财务付款的数据
     */
    public function search2($params)
    {

        $query = PurchaseOrderPay::find();

        $pageSize = isset($params['per-page']) ? intval($params['per-page']) : 20;
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);


        $query->orderBy('id desc');
        $this->load($params);



        $query->joinWith(['purchaseOrder AS items', 'purchaseOrderAccount']);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andWhere(['pur_purchase_order_pay.source' => $this->source]);


        $query->andFilterWhere([

            'pur_purchase_order_pay.pay_status'        => $this->pay_status,
            'pur_purchase_order_pay.settlement_method' => $this->settlement_method,
            'pur_purchase_order_pay.applicant'         => $this->applicant,
            'pur_purchase_order_pay.auditor'           => $this->auditor,
            'pur_purchase_order_pay.approver'          => $this->approver,
            'pur_purchase_order_pay.payer'             => $this->payer,
           // 'pur_purchase_order_pay.pay_type'          => $this->pay_type,

        ]);

        if(!empty($this->pay_type)&&$this->pay_type != 'all'){
            $query->andFilterWhere(['pur_purchase_order_pay.pay_type'=>$this->pay_type]);
        }

        if(!empty($this->pur_type) && $this->pur_type != 'all') {
            $query->andFilterWhere(['items.purchase_type'=>$this->pur_type]);
        }

        if(!empty($this->pur_status)){
            $query->andFilterWhere(['items.purchas_status'=>$this->pur_status]);
        }



        if(isset($params['chuna']) && $params['chuna'] !== '') {
            $ids = \app\models\AlibabaZzh::getPayableIds($params['chuna']);
            $query->andFilterWhere(['in', 'pur_purchase_order_pay.applicant', $ids]);
            $this->chuna = $params['chuna'];
        }

        $query//->andFilterWhere(['like', 'pur_purchase_order_pay.pur_number', trim($this->pur_number)])
            ->andFilterWhere(['like', 'pur_purchase_order_pay.requisition_number', trim($this->requisition_number)])
            ->andFilterWhere(['like', 'pur_purchase_order_pay.supplier_code', trim($this->supplier_code)])
            ->andFilterWhere(['between', 'pur_purchase_order_pay.application_time', !empty($params['PurchaseOrderPaySearch']['start_time'])?$params['PurchaseOrderPaySearch']['start_time']:date('Y-m-d H:i:s',strtotime("-3 month")), !empty($params['PurchaseOrderPaySearch']['end_time'])?$params['PurchaseOrderPaySearch']['end_time']:date('Y-m-d H:i:s',time())]);

        //批量查找采购单号
        if(strpos(trim($this->pur_number),' ')){
            $pur_number = preg_replace("/\s+/",',',trim($this->pur_number));
            $pur_number = explode(',',$pur_number);
            if(count($pur_number)>0){
                $query->andFilterWhere(['in', 'pur_purchase_order_pay.pur_number', $pur_number]);
            }else{
                $query->andFilterWhere(['like', 'pur_purchase_order_pay.pur_number', trim($this->pur_number)]);
            }
        }else{
            $query->andFilterWhere(['like', 'pur_purchase_order_pay.pur_number', trim($this->pur_number)]);
        }

        //账号搜索
        if(!empty($params['order_account'])){
            $query->leftJoin('pur_purchase_order_pay_type','pur_purchase_order_pay.pur_number=pur_purchase_order_pay_type.pur_number')
                ->andFilterWhere(['pur_purchase_order_pay_type.purchase_acccount'=>$params['order_account']]);
        }



        return $dataProvider;
    }


    /*
     * 请款单列表数据（区分合同与网采）
     */
    public function search3($params, $platform = null)
    {
        $query = PurchaseOrderPay::find();

        $pageSize = isset($params['per-page']) ? intval($params['per-page']) : 20;
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);

        $query->andWhere(['pur_purchase_order_pay.source' => $this->source]);
        $query->orderBy('id desc');
        $this->attributes = $params;
        if(!$this->validate()) {
            return $dataProvider;
        }
        if($platform == 'FBA') {
            $query->andWhere(['like', 'pur_purchase_order_pay.pur_number', 'FBA']);
        }
        if($platform == 'PO') {
            $query->andWhere(['like', 'pur_purchase_order_pay.pur_number', 'PO']);
        }
        if($platform == 'ABD') {
            $query->andWhere(['like', 'pur_purchase_order_pay.pur_number', 'ABD']);
        }


        $query->andWhere(['not in', 'pur_purchase_order_pay.pay_status', [0]]);

        $query->andFilterWhere(['pur_purchase_order_pay.pay_status' => $this->pay_status]);
        $query->andFilterWhere([
            'id' => $this->id,
            'settlement_method' => $this->settlement_method,
            'pay_price'         => $this->pay_price,
            //'pay_type'          => $this->pay_type,
            'applicant'         => $this->applicant,
            'auditor'           => $this->auditor,
            'approver'          => $this->approver,
            'review_time'       => $this->review_time,
            'processing_time'   => $this->processing_time,
            'pay_category' => $this->pay_category
        ]);
        if($this->source == 2) {
            $query->joinWith(['purchaseOrder AS items', 'purchaseOrderAccount']);
            if (!empty($this->pur_type) && $this->pur_type != 'all') {
                $query->andFilterWhere(['items.purchase_type' => $this->pur_type]);
            }
        }

        if(!empty($this->pay_type)&&$this->pay_type != 'all'){
            $query->andFilterWhere(['pur_purchase_order_pay.pay_type'=>$this->pay_type]);
        }
        $query//->andFilterWhere(['like', 'pur_number', trim($this->pur_number)])
            ->andFilterWhere(['like', 'pur_purchase_order_pay.requisition_number', trim($this->requisition_number)])
            ->andFilterWhere(['like', 'pur_purchase_order_pay.supplier_code', $this->supplier_code])
            ->andFilterWhere(['like', 'pur_purchase_order_pay.pay_name', $this->pay_name]);

        //批量查找采购单号
        if(strpos(trim($this->pur_number),' ')){
            $pur_number = preg_replace("/\s+/",',',trim($this->pur_number));
            $pur_number = explode(',',$pur_number);
            if(count($pur_number)>0){
                $query->andFilterWhere(['in', 'pur_purchase_order_pay.pur_number', $pur_number]);
            }else{
                $query->andFilterWhere(['like', 'pur_purchase_order_pay.pur_number', trim($this->pur_number)]);
            }
        }else{
            $query->andFilterWhere(['like', 'pur_purchase_order_pay.pur_number', trim($this->pur_number)]);
        }

        if($this->application_time) {
            $times = explode(' - ', $this->application_time);
            $query->andFilterWhere(['>', 'application_time', $times[0]]);
            $query->andFilterWhere(['<', 'application_time', $times[1]]);
            $this->start_time = $times[0];
            $this->end_time = $times[1];
        }
        if($this->payer_time) {
            $times = explode(' - ', $this->payer_time);
            $query->andFilterWhere(['>', 'payer_time', $times[0]]);
            $query->andFilterWhere(['<', 'payer_time', $times[1]]);
            $this->p_start_time = $times[0];
            $this->p_end_time = $times[1];
        }
        //账号搜索
        if(!empty($params['order_account'])){
            $query->andFilterWhere(['pur_purchase_order_account.account'=>$params['order_account']]);
        }

        return $dataProvider;
    }











}
