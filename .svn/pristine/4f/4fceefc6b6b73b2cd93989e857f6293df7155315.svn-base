<?php

namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use app\services\PurchaseOrderServices;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\PurchaseOrder;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\data\Pagination;

/**
 * PurchaseOrderSearch represents the model behind the search form about `app\models\PurchaseOrder`.
 */
class PurchaseOrderSearch extends PurchaseOrder
{
    public $start_time;
    public $end_time;
    public $express_no;
    public $supplier_name;
    public $warn_status;
    public $settlement;
    public $compact_number;
    public $grade;
    public $audit_time_start;
    public $audit_time_end;

    public $product_is_new; //判断是否为新品
    public $is_check;//是否需要验货
    public $warehouse_code;
    public $purchase_type;
    public $warehouse_category_id;
    public $receive_goods;// 到货类型
    public $sku;// SKU查询
    public $supplier_special_flag;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['warn_status','settlement','grade','pur_number', 'warehouse_code', 'supplier_code', 'pur_type',
                'shipping_method', 'operation_type', 'creator', 'account_type', 'pay_type', 'currency_code','items.sku',
                'buyer','purchas_status','refund_status','create_type','audit_return','created_at','singletype','reference',
                'start_time','end_time','pur_type','is_arrival','complete_type','qc_abnormal_status','receiving_exception_status',
                'code','ss.supplier_type','sku_type','merchandiser','buyer','supplier_name','pay_status','ship.express_no',
                'page_size','sku','express_no','supplier_name','source','product_is_new','is_drawback',
                'arrival_status','is_check_goods','receive_goods','supplier_special_flag'], 'safe'],
        ];
    }

    public function attributes()
    {
        // 添加关联字段到可搜索属性集合
        return array_merge(parent::attributes(), ['items.sku','ss.supplier_type','ship.express_no','pro.product_is_new','receive_goods']);
    }
    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * 采购确认
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params, $noDataProvider = false)
    {
        $query = PurchaseOrder::find();
        $query->alias('t');

        // add conditions that should always apply here
        $pageSize = isset($params['per-page']) ? intval($params['per-page']) : 20;
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);

        $query->where(['in','t.purchas_status',['1']]);
        $buyer = PurchaseOrderServices::getPurchaseOrderBuyerByRole();
        if(is_array($buyer)){
            $query->andFilterWhere(['in','t.buyer',$buyer]);
        }
        $query->andFilterWhere(['in','t.purchase_type',['1']]);
//        $query->andFilterWhere(['not in','pur_purchase_order.purchase_type',['3']]);
        $query->orderBy('id desc');

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $puid= PurchaseUser::find()->select('pur_user_id')->where(['in','grade',[1,2,3]])->asArray()->all();
        $ids = ArrayHelper::getColumn($puid, 'pur_user_id');
        if(in_array(Yii::$app->user->id,$ids))
        {

        } else {
            $query->andWhere(['t.buyer'=>Yii::$app->user->identity->username]);
        }

        //单号
        $query->andFilterWhere(['=', 't.pur_number', trim($this->pur_number)]);
       // $query->andFilterWhere(['=', 'creator', Yii::$app->user->identity->username]);
        if ($this->getAttribute('items.sku'))
        {
            $query->joinWith('purchaseOrderItems AS items');
        }

        if($this->buyer){
            $query->andWhere(['t.buyer'=>$this->buyer]);
        }
        // grid filtering conditions
        $query->andFilterWhere([
            't.purchas_status'   => $this->purchas_status,
            't.create_type'      => $this->create_type,
            't.audit_return'     => $this->audit_return,
            't.warehouse_code'   => $this->warehouse_code,
            't.supplier_code' => $this->supplier_code,
            't.shipping_method'  => $this->shipping_method,
           // 'items.sku' => $this->getAttribute('items.sku'),
        ]);

        if($this->supplier_special_flag !== '' AND $this->supplier_special_flag !== NULL){
            $query->joinWith('supplier');
            $query->andWhere(['=', 'pur_supplier.supplier_special_flag', $this->supplier_special_flag]);
        }

        $query->andFilterWhere(['like', 't.supplier_name', $this->supplier_name])
            ->andFilterWhere(['like', 'items.sku', trim($this->getAttribute('items.sku'))]);
        if (!empty($this->start_time)) {
            $this->start_time = $this->start_time . ' 00:00:00';
            $this->end_time = $this->end_time . ' 23:59:59';
            $query->andFilterWhere(['between', 't.created_at', $this->start_time, $this->end_time]);
        } else {
            $query->andFilterWhere(['between', 't.created_at', date('Y-m-d H:i:s',strtotime("last month")), date('Y-m-d H:i:s',time())]);
        }
        \Yii::$app->session->set('PurchaseOrderConfirmSearchData', $params);
        if ($noDataProvider)
            return $query;
        return $dataProvider;
    }

    /**
     * 采购审核搜索
     * @param $params
     * @return ActiveDataProvider
     */
    public function search1($params)
    {
        $query = PurchaseOrder::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $query->where(['=', 'audit_return', '2']);
        $query->andFilterWhere(['in','purchase_type',['1']]);
//        $query->andFilterWhere(['!=','purchase_type','3']);
        $query->andFilterWhere(['in','purchas_status',['2']]);
        $buyer = PurchaseOrderServices::getPurchaseOrderBuyerByRole();
        if(is_array($buyer)) {
            $query->andWhere(['in', 'buyer', $buyer]);
        }
        //$query->andFilterWhere(['=','purchas_status','2']);

        /*$grade=PurchaseUser::findOne(['pur_user_id'=>Yii::$app->user->id]);

        if(!empty($grade->grade)){
            if($grade->grade==1){
                $query->andFilterWhere(['in','purchas_status',['2']]);
                $query->andFilterWhere(['=','review_status','0']);
            }

            if($grade->grade==2){
                $query->andFilterWhere(['in','purchas_status',['3']]);
                $query->andFilterWhere(['=','review_status','1']);
            }

            if($grade->grade==3){
                $query->andFilterWhere(['in','purchas_status',['3']]);
                $query->andFilterWhere(['=','review_status','2']);
            }
        }else{
            $query->andFilterWhere(['=','purchas_status','2']);
            //$query->andFilterWhere(['in','review_status',['1','2']]);
        }*/



        $query->orderBy('id desc');
        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        if ($this->getAttribute('items.sku'))
        {
            $query->joinWith('purchaseOrderItems AS items');
        }
        // grid filtering conditions
        $query->andFilterWhere([
            'buyer' => $this->buyer,
            'pur_purchase_order.supplier_code' => $this->supplier_code,
            //'items.sku' => $this->getAttribute('items.sku'),
        ]);
        $query->andFilterWhere(['=', 'pur_purchase_order.pur_number', trim($this->pur_number)])
            ->andFilterWhere(['like', 'items.sku', trim($this->getAttribute('items.sku'))])
            ->andFilterWhere(['like', 'supplier_name', $this->supplier_name])
            ->andFilterWhere(['=', 'pur_type', $this->pur_type])
            ->andFilterWhere(['account_type' => $this->account_type])
            ->andFilterWhere(['like', 'warehouse_code', $this->warehouse_code])
            ->andFilterWhere(['pay_type'=>$this->pay_type]);
        if($this->supplier_special_flag !== '' AND $this->supplier_special_flag !== NULL){
            $query->joinWith('supplier');
            $query->andWhere(['=', 'pur_supplier.supplier_special_flag', $this->supplier_special_flag]);
        }
        if (!empty($this->start_time)) {
            $this->start_time = $this->start_time . ' 00:00:00';
            $this->end_time = $this->end_time . ' 23:59:59';
            $query->andFilterWhere(['between', 'created_at', $this->start_time, $this->end_time]);
        } else {
            $query->andFilterWhere(['between', 'created_at', date('Y-m-d H:i:s',strtotime("last month")), date('Y-m-d H:i:s',time())]);
        }
        return $dataProvider;
    }

    /**
     * 采购复审搜索
     * @param $params
     * @return ActiveDataProvider
     */
    public function search2($params)
    {
        $query = PurchaseOrder::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $query->where(['in','purchas_status',['2']]);
        $query->andFilterWhere(['in','audit_return',['3']]);
        $query->andFilterWhere(['not in','purchase_type',['3']]);
        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        if ($params)
        {
            $query->joinWith('purchaseOrderItems AS items');
        }
        // grid filtering conditions


        //$query->andFilterWhere(['=','items.sku' , $this->getAttribute('items.sku')]);

        $query->andFilterWhere(['=', 'pur_purchase_order.pur_number', $this->pur_number])
            ->andFilterWhere(['like', 'items.sku', trim($this->getAttribute('items.sku'))]);


        //->andFilterWhere(['=', 'items.supplier_code', $this->supplier_code]);




        return $dataProvider;
    }
    /**
     * 采购单搜索
     * @param $params
     * @return ActiveDataProvider
     */
    public function search3($params, $noDataProvider = false)
    {
        $query = PurchaseOrder::find();

        // add conditions that should always apply here
        $pageSize = isset($params['per-page']) ? intval($params['per-page']) : 20;
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);
        //有采购采购类型搜索改为角色限制
        //$query->andFilterWhere(['in','pur_purchase_order.purchase_type',['1']]);
//        $query->andFilterWhere(['in','pur_purchase_order.purchase_type',['1','2','4']]);

        /*$user = ['张涛涛','admin'];
        $user_name=Yii::$app->user->identity->username;

        $pur_user=PurchaseUser::findOne(['pur_user_id'=>Yii::$app->user->id]);

        if(!in_array($user_name,$user) && !empty($pur_user) && $pur_user->grade != 3){
               if($pur_user->type==0){
                   $query->andFilterWhere(['=', 'pur_purchase_order.purchase_type', 1]);
               }elseif($pur_user->type==1){
                   $query->andFilterWhere(['=', 'pur_purchase_order.purchase_type', 2]);
               }else{
                   $query->andFilterWhere(['=', 'buyer', Yii::$app->user->identity->username]);
               }
        }*/
        /*$puid= PurchaseUser::find()->select('pur_user_id')->where(['in','grade',[1,2,3]])->asArray()->all();
        $ids = ArrayHelper::getColumn($puid, 'pur_user_id');
        if(in_array(Yii::$app->user->id,$ids))
        {

        } else {
            $query->andWhere(['in', 'buyer',Yii::$app->user->identity->username]);
        }*/

        $this->load($params);
        //$query->where(['in','purchas_status',['3','5','6','7','8','9','10']]);
        $query->andFilterWhere(['in','pur_purchase_order.purchase_type',['1']]);
        if (in_array('FBA采购经理组',array_keys(Yii::$app->authManager->getRolesByUser(Yii::$app->user->id)))) {

        } else if(!in_array('供应链',array_keys(Yii::$app->authManager->getRolesByUser(Yii::$app->user->id)))){
            $buyer = PurchaseOrderServices::getPurchaseOrderBuyerByRole();
            if(is_array($buyer)){
                $query->andWhere(['in','buyer',$buyer]);
            }
        }
        $query->orderBy('submit_time desc');
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        if ($this->getAttribute('items.sku'))
        {
            $query->innerJoinWith('purchaseOrderItems AS items');
        }

        if($this->getAttribute('ship.express_no'))
        {
            $query->innerJoinWith('orderShip AS ship');
        }
        if ($this->purchas_status==99)
        {
            $query->andwhere(['in','purchas_status',['7','8']]);

        } else {
            $query->andFilterWhere(['=', 'purchas_status', $this->purchas_status]);
        }
        //批量查找采购单号
        if(strpos($this->pur_number,',') || strpos($this->pur_number,'，')){
            $this->pur_number = preg_replace("/\s/","",$this->pur_number);
            $pur_number = explode(',',str_replace('，',',',trim($this->pur_number)));
            if(count($pur_number)>0){
                $query->andFilterWhere(['in', 'pur_purchase_order.pur_number', $pur_number]);
            }
        }else{
            $query->andFilterWhere(['=', 'pur_purchase_order.pur_number', trim($this->pur_number)]);
        }


        //$query->andFilterWhere(['=','items.sku' , $this->getAttribute('items.sku')]);

        // grid filtering conditions
        $query->andFilterWhere([
            'pur_purchase_order.buyer' => Vhelper::chunkBuyerByNumeric($this->buyer),
            'is_arrival' => $this->is_arrival,
            'receiving_exception_status' => $this->receiving_exception_status,
            'qc_abnormal_status' => $this->qc_abnormal_status,
            'audit_return' => $this->audit_return,
            'pay_status' => $this->pay_status,
            'create_type' => $this->create_type,
            'account_type' => $this->account_type,
            'shipping_method' => $this->shipping_method,
            'pur_purchase_order.supplier_code' => $this->supplier_code,
            'pur_purchase_order.merchandiser' => $this->merchandiser,
            'refund_status'   => $this->refund_status,
            //'items.sku' => $this->getAttribute('items.sku'),
            'ship.express_no' => trim($this->getAttribute('ship.express_no')),


        ]);

        $query->andFilterWhere(['=', 'warehouse_code', $this->warehouse_code])
            //           ->andFilterWhere(['like', 'items.sku', trim($this->getAttribute('items.sku'))])
//            ->andFilterWhere(['=', 'purchas_status', $this->purchas_status])
            ->andFilterWhere(['like', 'pur_purchase_order.supplier_name', $this->supplier_name])
            ->andFilterWhere(['=', 'pur_type', $this->pur_type]);
        if($this->supplier_special_flag !== '' AND $this->supplier_special_flag !== NULL){
            $query->joinWith('supplier');
            $query->andWhere(['=', 'pur_supplier.supplier_special_flag', $this->supplier_special_flag]);
        }
        //批量查找sku
        if(strpos(trim($this->getAttribute('items.sku')),',') || strpos(trim($this->getAttribute('items.sku')),'，')){
            $sku = trim($this->getAttribute('items.sku'));
            $sku = preg_replace("/\s/","",$sku);
            $sku = explode(',',str_replace('，',',',$sku));
            if(count($sku)>0){
                $query->andFilterWhere(['in', 'items.sku', $sku]);
            }
        }else{
            $query->andFilterWhere(['items.sku'=>trim($this->getAttribute('items.sku'))]);
        }

        if (!empty($this->start_time)) {
            $this->start_time = $this->start_time . ' 00:00:00';
            $this->end_time = $this->end_time . ' 23:59:59';
            $query->andFilterWhere(['between', 'audit_time', $this->start_time, $this->end_time]);
        } else {
            $query->andFilterWhere(['between', 'audit_time', date('Y-m-d H:i:s',strtotime("-6 month")), date('Y-m-d H:i:s',time())]);
        }
        \Yii::$app->session->set('PurchaseOrderSearchData', $params);
        if ($noDataProvider)
            return $query;
        return $dataProvider;
    }
    /**
     * 采购单异常搜索
     * @param $params
     * @return ActiveDataProvider
     */
    public function search4($params)
    {
        $query = PurchaseOrder::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);
        $query->where(['and',['in','refund_status',['4','6','7']],['not in','purchase_type',['1']]]);
        $this->load($params);
        $query->orderBy('created_at desc');
        if (!$this->validate()) {
            return $dataProvider;
        }
        if ($this->getAttribute('items.sku'))
        {
            $query->innerJoinWith('purchaseOrderItems AS items');

        }
        $query->andFilterWhere(['=', 'pur_purchase_order.pur_number', trim($this->pur_number)])
            ->andFilterWhere(['like', 'items.sku', trim($this->getAttribute('items.sku'))]);

        $query->andFilterWhere([
            'pur_purchase_order.buyer' => trim($this->buyer),

        ]);

        return $dataProvider;
    }
    /**
     * 采购单fba搜索
     * @param $params
     * @return ActiveDataProvider
     */
    public function search5($params, $noDataProvider=false)
    {
        $pageSize = isset($params['per-page']) ? intval($params['per-page']) : 50;
        $query = PurchaseOrder::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);

        // '1' => '待确认', '2' => '采购已确认', '4' => '撤销'
        $query->where(['not in', 'purchas_status', [4]]);
        $query->andWhere(['pur_purchase_order.source'=>$params['source']]);

        $query->andFilterWhere(['in','pur_purchase_order.purchase_type',['3']]);
        $this->load($params);
        $query->joinWith(['purchaseOrderAccount']);
        if ($params['source'] == 2) {
            $query->orderBy('submit_time asc');
        } else {
            $query->orderBy('id desc');
        }


        if (!$this->validate()) {
            return $dataProvider;
        }
        if ($this->getAttribute('items.sku'))
        {
            $query->innerJoinWith('purchaseOrderItems AS items');

        }
        if($this->getAttribute('ship.express_no'))
        {
            $query->innerJoinWith('orderShip AS ship');
        }
        if(!empty($this->purchas_status)){
            $query->andFilterWhere(['in','purchas_status',$this->purchas_status]);
        }
        if(!empty($this->is_drawback)){
            $query->andFilterWhere(['is_drawback' => $this->is_drawback]);
        }
        $query->andFilterWhere(['=', 'pur_purchase_order.pur_number', trim($this->pur_number)]);

        $query->andFilterWhere([
            'pur_purchase_order.buyer' => Vhelper::chunkBuyerByNumeric($this->buyer),
            'is_arrival' => $this->is_arrival,
            'receiving_exception_status' => $this->receiving_exception_status,
            'qc_abnormal_status' => $this->qc_abnormal_status,
            'audit_return' => $this->audit_return,
            'pay_status' => $this->pay_status,
            'create_type' => $this->create_type,
            'account_type' => $this->account_type,
            'shipping_method' => $this->shipping_method,
            'pur_purchase_order.merchandiser' => $this->merchandiser,
            'refund_status'   => $this->refund_status,
            'is_check_goods'   => $this->is_check_goods,
            'items.sku' => trim($this->getAttribute('items.sku')),
            'ship.express_no' => trim($this->getAttribute('ship.express_no')),


        ]);

        if( $this->receive_goods)// 到货类型
        {
            $receive_goods = $this->receive_goods;
            $now_time = date('Y-m-d H:i:s');
            $now_time_plus_3_day = date('Y-m-d H:i:s',strtotime("+3 days"));
            if($receive_goods == 'r_format'){//正常回货
                $query->andFilterWhere(['>=','pur_purchase_order.date_eta',$now_time_plus_3_day]);
            }elseif($receive_goods == 'r_due'){// 即将到期
                $query->andFilterWhere(['between', 'pur_purchase_order.date_eta', $now_time, $now_time_plus_3_day]);
            }else{// 已超期
                $query->andFilterWhere(['<=','pur_purchase_order.date_eta',$now_time]);
            }
        }

        $query->andFilterWhere(['=', 'warehouse_code', $this->warehouse_code])
            ->andFilterWhere(['like', 'pur_purchase_order.supplier_name', trim($this->supplier_name)])
            ->andFilterWhere(['=', 'pur_type', $this->pur_type]);

        if($this->supplier_special_flag !== '' AND $this->supplier_special_flag !== NULL){
            $query->joinWith('supplier');
            $query->andWhere(['=', 'pur_supplier.supplier_special_flag', $this->supplier_special_flag]);
        }

        $query->andFilterWhere(['between', 'created_at', $this->start_time, $this->end_time]);

        Yii::$app->session->set('FBA-purchase_order-net',$params);
        if($noDataProvider){
            return $query;
        }

        return $dataProvider;
    }
    /**
     * FBA-采购单-合同搜索
     */
    // 海外仓-合同-订单列表页-数据搜索
    public function fbaCompactSearch($params, $noDataProvider=false)
    {
        $query = PurchaseOrder::find();

        $query->where(['pur_purchase_order.source' => 1,'pur_purchase_order.purchase_type' => 3]);
        $query->andWhere(['in', 'purchas_status', ['3', '5', '6', '7', '8', '9', '10']]);
        $pageSize = isset($params['per-page']) ? intval($params['per-page']) : 20;
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);
        $this->load($params);

        if(isset($params['compact_number']) && !empty($params['compact_number'])) {
            $pos = PurchaseCompact::getPurNumbers($params['compact_number']);
            $query->andWhere(['in', 'pur_number', $pos]);
            $this->compact_number = $params['compact_number'];
        }

        $query->orderBy('submit_time desc');
        if (!$this->validate()) {
            return $dataProvider;
        }
        if ($this->getAttribute('items.sku')){
            $query->innerJoinWith('purchaseOrderItems AS items');
        }

        if($this->getAttribute('ship.express_no')){
            $query->innerJoinWith('orderShip AS ship');
        }
        if (is_array($this->purchas_status) && in_array(99,$this->purchas_status) ){
            $query->andwhere(['in','purchas_status',['7','8']]);
        }
        //批量查找采购单号
        if(strpos(trim($this->pur_number),' ')){
            $pur_number = preg_replace("/\s+/",',',trim($this->pur_number));
            $pur_number = explode(',',$pur_number);
            if(count($pur_number)>0){
                $query->andFilterWhere(['in', 'pur_purchase_order.pur_number', $pur_number]);
            }else{
                $query->andFilterWhere(['=', 'pur_purchase_order.pur_number', trim($this->pur_number)]);
            }
        }else{
            $query->andFilterWhere(['=', 'pur_purchase_order.pur_number', trim($this->pur_number)]);
        }

        $query->andFilterWhere([
            'pur_purchase_order.buyer' => $this->buyer,
            'is_arrival' => $this->is_arrival,
            'receiving_exception_status' => $this->receiving_exception_status,
            'qc_abnormal_status' => $this->qc_abnormal_status,
            'audit_return' => $this->audit_return,
            'create_type' => $this->create_type,
            'account_type' => $this->account_type,
            'shipping_method' => $this->shipping_method,
            'supplier_code' => $this->supplier_code,
            'pur_purchase_order.merchandiser' => $this->merchandiser,
            'refund_status'   => $this->refund_status,
            'ship.express_no' => trim($this->getAttribute('ship.express_no')),
        ]);

        $query->andFilterWhere(['=', 'warehouse_code', $this->warehouse_code])
            ->andFilterWhere(['in', 'purchas_status', $this->purchas_status])
            ->andFilterWhere(['in', 'pay_status', $this->pay_status])
            ->andFilterWhere(['=', 'arrival_status', $this->arrival_status])
            ->andFilterWhere(['like', 'pur_purchase_order.supplier_name', $this->supplier_name])
            ->andFilterWhere(['=', 'pur_type', $this->pur_type]);

        if($this->supplier_special_flag !== '' AND $this->supplier_special_flag !== NULL){
            $query->joinWith('supplier');
            $query->andWhere(['=', 'pur_supplier.supplier_special_flag', $this->supplier_special_flag]);
        }

        //批量查找sku
        if(strpos(trim($this->getAttribute('items.sku')),' ')){
            $sku = preg_replace("/\s+/",',',trim($this->getAttribute('items.sku')));
            $sku = explode(',',$sku);
            if(count($sku)>0){
                $query->andFilterWhere(['in', 'items.sku', $sku]);
            }else{
                $query->andFilterWhere(['like', 'items.sku', trim($this->getAttribute('items.sku'))]);
            }
        }else{
            $query->andFilterWhere(['like', 'items.sku', trim($this->getAttribute('items.sku'))]);
        }

        $query->andFilterWhere(['between', 'audit_time', $this->start_time, $this->end_time]);
        if (isset($params['shipfees_audit_status']))
            $query->andFilterWhere(['=', 'shipfees_audit_status', (int)$params['shipfees_audit_status']]);

        Yii::$app->session->set('FBA-purchase_order-compact',$params);
        if($noDataProvider){
            return $query;
        }
        
        return $dataProvider;
    }

    //获取供应商为广东，且金额大于20000的采购单id
    public static function getCheckOrder($start_time, $end_time){
        $query = PurchaseOrder::find()->joinWith(['purchaseOrderAccount'])
            ->leftJoin('pur_supplier','pur_supplier.supplier_code=pur_purchase_order.supplier_code')
            ->leftJoin('pur_provincial','pur_provincial.id=pur_supplier.province')
            ->where(['not in', 'purchas_status', [4]])
            ->andFilterWhere(['in','pur_purchase_order.purchase_type',['3']])
            ->andWhere(['pur_provincial.id'=>20]);
        //获取金额大于20000的采购单
        $orders  = $query->andFilterWhere(['between', 'created_at', $start_time, $end_time])->all();
        $ids = [];
        if($orders){
            foreach ($orders as $order){
                $price = PurchaseOrderItems::getCountPrice($order->pur_number);
                //大于20000
                if($price > 20000){
                    $ids[] = $order->id;
                }
            }
        }
        return $ids;
    }

    /**海外-采购计划单
     * @param $params
     * @param bool $noDataProvider
     * @return ActiveDataProvider|\yii\db\ActiveQuery
     */
    public function search6($params, $noDataProvider = false)
    {
        $query = PurchaseOrder::find();
        // add conditions that should always apply here
        $pageSize = isset($params['per-page']) ? intval($params['per-page']) : 20;
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);

        $query->where(['in','purchas_status',['1']]);

        $query->andFilterWhere(['in','pur_purchase_order.purchase_type',['2']]);

        //获取用户级别
        $grade = PurchaseUser::getUserGrade(Yii::$app->user->identity->id);
        if (in_array('采购组-海外',array_keys(Yii::$app->authManager->getRolesByUser(Yii::$app->user->id))) && ($grade=='' || $grade=='( 一般组员 )')&& empty($this->buyer)) {
            $query->andWhere(['in','buyer',Yii::$app->user->identity->username]);
        }

        $query->orderBy('id desc');

        $this->load($params);
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $puid= PurchaseUser::find()->select('pur_user_id')->where(['in','grade',[1,2,3]])->asArray()->all();
        $ids = ArrayHelper::getColumn($puid, 'pur_user_id');
        if(in_array(Yii::$app->user->id,$ids))
        {

        } else {
           // $query->andWhere(['in', 'buyer',Yii::$app->user->identity->username]);
        }

        /*//用户是否是超级管理员组
        $s =Yii::$app->authManager->getRolesByUser(Yii::$app->user->identity->id);
        $key = array_key_exists('超级管理员组',$s);

        //获取用户级别
        $grade = PurchaseUser::getUserGrade(Yii::$app->user->identity->id);

        //如果用户是超级管理员组 或  是经理级别的
        if ($key || ($grade == '( 经理 )')) {
            $query->andFilterWhere(['not in','pur_purchase_order.purchase_type',['3']]);
        } else {
            $type = PurchaseUser::getType(Yii::$app->user->id);
            if ($type !== false) {
                $query->andFilterWhere(['in','pur_purchase_order.purchase_type',$type+1]);
            }
        }*/

        //单号
        $query->andFilterWhere(['=', 'pur_purchase_order.pur_number', trim($this->pur_number)]);
        // $query->andFilterWhere(['=', 'creator', Yii::$app->user->identity->username]);
        if ($this->getAttribute('items.sku'))
        {
            $query->joinWith('purchaseOrderItems AS items');
        }

        //是否未新品
         if (!empty($this->product_is_new))
        {
            $query->leftJoin('pur_purchase_order_items','pur_purchase_order.pur_number = pur_purchase_order_items.pur_number')->leftjoin('pur_product','pur_product.sku=pur_purchase_order_items.sku');
            $query->andFilterWhere(['pur_product.product_is_new'=>$this->product_is_new]);

        }
        
        // grid filtering conditions
        $query->andFilterWhere([
            'buyer'            => $this->buyer,
            'purchas_status'   => $this->purchas_status,
            'create_type'      => $this->create_type,
            'audit_return'     => $this->audit_return,
            'warehouse_code'   => $this->warehouse_code,
            'supplier_code' => $this->supplier_code,
            'shipping_method'  => $this->shipping_method,
            //'product_is_new'  => $this->getAttribute('pro.product_is_new'),
            //'items.sku' => $this->getAttribute('items.sku'),
        ]);

        $query->andFilterWhere(['like', 'supplier_name', $this->supplier_name])
            ->andFilterWhere(['like', 'items.sku', trim($this->getAttribute('items.sku'))])
            ->andFilterWhere(['between', 'created_at', $this->start_time, $this->end_time]);
        \Yii::$app->session->set('PurchaseOrderConfirmSearchData', $params);
        if ($noDataProvider)
            return $query;
        return $dataProvider;
    }

    /**海外仓-采购审核
     * @param $params
     * @return ActiveDataProvider
     */
    public function search7($params)
    {
        $query = PurchaseOrder::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pagesize' => 20,
                'pageSizeParam' => false
            ]
        ]);

        $query->where(['=', 'audit_return', '2']);

        $query->where(['source' => 2]);


        $query->andFilterWhere(['in','purchase_type',['2']]);
//        $query->andFilterWhere(['!=','purchase_type','3']);
        $query->andFilterWhere(['in','purchas_status',['2']]);

        $query->orderBy('id desc');


        $this->attributes = $params;


        if (!$this->validate()) {
            return $dataProvider;
        }
        if ($this->getAttribute('items.sku'))
        {
            $query->joinWith('purchaseOrderItems AS items');
        }
        $query->andFilterWhere([
            'buyer' => $this->buyer,
            'supplier_code' => $this->supplier_code,
            //'items.sku' => $this->getAttribute('items.sku'),
        ]);



        $query->andFilterWhere(['=', 'pur_purchase_order.pur_number', trim($this->pur_number)])
            ->andFilterWhere(['like', 'items.sku', trim($this->getAttribute('items.sku'))])
            ->andFilterWhere(['=', 'pur_type', $this->pur_type])
            ->andFilterWhere(['like', 'warehouse_code', $this->warehouse_code])
            ->andFilterWhere(['between', 'created_at', $this->start_time, $this->end_time]);
        return $dataProvider;
    }

    // 海外仓-网络采购单
    public function search8($params, $noDataProvider = false)
    {
        $query = PurchaseOrder::find()->where(['pur_purchase_order.source' => 2,'pur_purchase_order.purchase_type' => 2]);
        $query->andWhere(['in', 'pur_purchase_order.purchas_status', ['3', '5', '6', '7', '8', '9', '10']]);
        $query->andWhere(['=', 'pur_purchase_order.is_new',2]);

        $pageSize = isset($params['per-page']) ? intval($params['per-page']) : 10;
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);

        $this->load($params);
        if(isset($params['compact_number']) && !empty($params['compact_number'])) {//合同号  合同列表新增删除功能
            $pos = PurchaseCompact::getPurNumbers($params['compact_number']);
            $query->andWhere(['in', 'pur_purchase_order.pur_number', $pos]);
            $this->compact_number = $params['compact_number'];
        }

        if(empty($this->buyer)){
            $userIds = Yii::$app->authManager->getUserIdsByRole('采购组-海外');
            $user = User::find()->select('username')->andFilterWhere(['id'=>$userIds])->asArray()->all();
            $query->andFilterWhere(['in','pur_purchase_order.buyer',array_column($user,'username')]);
        }

        $query->orderBy('submit_time desc');
        if (!$this->validate()) {
            return $dataProvider;
        }
        if ($this->getAttribute('items.sku')){
            $query->innerJoinWith('purchaseOrderItems AS items');
        }

        if($this->getAttribute('ship.express_no')){
            $query->innerJoinWith('orderShip AS ship');
        }
        if (is_array($this->purchas_status) && in_array(99,$this->purchas_status) ){
            $query->andwhere(['in','pur_purchase_order.purchas_status',['7','8']]);
        }
        //批量查找采购单号
        if(strpos(trim($this->pur_number),' ')){
            $pur_number = preg_replace("/\s+/",',',trim($this->pur_number));
            $pur_number = explode(',',$pur_number);
            if(count($pur_number)>0){
                $query->andFilterWhere(['in', 'pur_purchase_order.pur_number', $pur_number]);
            }else{
                $query->andFilterWhere(['=', 'pur_purchase_order.pur_number', trim($this->pur_number)]);
            }
        }else{
            $query->andFilterWhere(['=', 'pur_purchase_order.pur_number', trim($this->pur_number)]);
        }
        $query->andFilterWhere([
            'pur_purchase_order.buyer' => Vhelper::chunkBuyerByNumeric($this->buyer),
            'is_arrival' => $this->is_arrival,
            'receiving_exception_status' => $this->receiving_exception_status,
            'qc_abnormal_status' => $this->qc_abnormal_status,
            'audit_return' => $this->audit_return,
            'create_type' => $this->create_type,
            'account_type' => $this->account_type,
            'shipping_method' => $this->shipping_method,
            'pur_purchase_order.supplier_code' => $this->supplier_code,
            'pur_purchase_order.merchandiser' => $this->merchandiser,
            'refund_status'   => $this->refund_status,
            'ship.express_no' => trim($this->getAttribute('ship.express_no')),
        ]);

        $query->andFilterWhere(['=', 'pur_purchase_order.warehouse_code', $this->warehouse_code])
            ->andFilterWhere(['in', 'pur_purchase_order.purchas_status', $this->purchas_status])
            ->andFilterWhere(['in', 'pur_purchase_order.pay_status', $this->pay_status])
            ->andFilterWhere(['=', 'arrival_status', $this->arrival_status])
            ->andFilterWhere(['like', 'pur_purchase_order.supplier_name', $this->supplier_name])
            ->andFilterWhere(['=', 'pur_purchase_order.pur_type', $this->pur_type]);

        //批量查找sku
        if(strpos(trim($this->getAttribute('items.sku')),' ')){
            $sku = preg_replace("/\s+/",',',trim($this->getAttribute('items.sku')));
            $sku = explode(',',$sku);
            if(count($sku)>0){
                $query->andFilterWhere(['in', 'items.sku', $sku]);
            }else{
                $query->andFilterWhere(['like', 'items.sku', trim($this->getAttribute('items.sku'))]);
            }
        }else{
            $query->andFilterWhere(['like', 'items.sku', trim($this->getAttribute('items.sku'))]);
        }

        $query->leftJoin('pur_purchase_demand demand','demand.pur_number=pur_purchase_order.pur_number');
        $query->leftJoin('pur_platform_summary summary','summary.demand_number=demand.demand_number');
        $query->andFilterWhere(['and',"summary.agree_time<'2018-08-29 10:00:00' OR summary.agree_time IS NULL"]);
        $query->andFilterWhere(['between', 'pur_purchase_order.audit_time', $this->start_time, $this->end_time]);

        if (isset($params['shipfees_audit_status']))
            $query->andFilterWhere(['=', 'shipfees_audit_status', (int)$params['shipfees_audit_status']]);
        \Yii::$app->session->set('PurchaseOrderSearchData', $params);
        if ($noDataProvider) return $query;
        return $dataProvider;
    }







    /**国内-跟踪到货
     * @param $params
     * @param bool $noDataProvider
     * @return ActiveDataProvider|\yii\db\ActiveQuery
     */
    public function search9($params, $noDataProvider = false)
    {
        $query = PurchaseOrderItems::find();
        $query->select(['pur_purchase_order_items.id','pur_purchase_order_items.pur_number','pur_purchase_order_items.sku','product_img','name','ctq','cty']);
        //关联订单主表,物流表
        $query->joinWith(['purNumber']);
//        $query->joinWith(['purNumber','orderShip']);

        $pageSize = isset($params['per-page']) ? intval($params['per-page']) : 20;
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);
        $query->where(['in','pur_purchase_order.purchas_status',['5','7','8']]);
        $query->andFilterWhere(['in','pur_purchase_order.purchase_type',['1']]);
        //((ifnull(ctq, 0) - ifnull(rqy, 0)) != 0)
        $query->andFilterWhere(['!=',"ifnull(ctq, 0) - ifnull(cty, 0)",0]);

        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }
        if($this->grade){
            $arr = ['g1'=>1,'g2'=>2,'g3'=>3,'g4'=>4];
            if(in_array($this->grade,['g1','g2','g3','g4'])){
                $query->leftJoin(PurchaseUser::tableName(),'pur_purchase_user.pur_user_name=pur_purchase_order.buyer');
                $query->andFilterWhere(['pur_purchase_user.group_id'=>$arr[$this->grade]]);
            }else{
                $query->leftJoin(User::tableName(),'pur_user.username=pur_purchase_order.buyer');
                $query->andFilterWhere(['pur_user.id'=>$this->grade]);
            }
        }
        if($this->warn_status){
            $query->joinWith('warnStatus');
            $query->andFilterWhere(['pur_purchase_warning_status.warn_status'=>$this->warn_status]);
        }
        /*if ($this->getAttribute('items.sku'))
        {
            $query->innerJoinWith('purchaseOrderItems AS items');
        }*/

        if($this->settlement){
            $query->andFilterWhere(['pur_purchase_order.account_type'=>$this->settlement]);
        }

        $query->andFilterWhere([
            'pur_purchase_order.buyer' => trim($this->buyer),
//            'pur_purchase_order.supplier_code' => trim($this->supplier_code),
//            'pur_purchase_order_ship.express_no' => trim($this->express_no),
            //'items.sku' => trim($this->getAttribute('items.sku')),
//            'warehouse_code' => $this->warehouse_code,
        ]);
        $query->andFilterWhere([
//            'pur_purchase_order.supplier_code' => trim($this->supplier_code),
//            'pur_purchase_order_ship.express_no' => trim($this->express_no),
            //'items.sku' => trim($this->getAttribute('items.sku')),
//            'warehouse_code' => $this->warehouse_code,
            'pur_purchase_order_items.pur_number' => trim($this->pur_number),
        ]);
//            'pur_purchase_order.supplier_name' => trim($this->supplier_name),

        $query->andFilterWhere(['like', 'pur_purchase_order_items.sku', trim($this->getAttribute('items.sku'))])
            ->andFilterWhere(['like', 'supplier_name', trim($this->supplier_name)]);
//        ->andFilterWhere(['like', 'pur_purchase_order.supplier_name', $this->supplier_name]);

        if (!empty($this->start_time)) {
            $this->start_time = $this->start_time . ' 00:00:00';
            $this->end_time = $this->end_time . ' 23:59:59';
            $query->andFilterWhere(['between', 'audit_time', $this->start_time, $this->end_time]);
        } else {
            $query->andFilterWhere(['between', 'audit_time', date('Y-m-d H:i:s',strtotime("-6 month")), date('Y-m-d H:i:s',time())]);
        }
        \Yii::$app->session->set('purchaseOrderFollowSearch', $params);
        if ($noDataProvider)
            return $query;
        return $dataProvider;
    }

    // 海外仓-合同-订单列表页-数据搜索
    public function search10($params,$noDataProvider = false)
    {
        $query = PurchaseOrder::find();

        $query->where(['pur_purchase_order.source' => 1,'pur_purchase_order.purchase_type' => 2]);
        $query->andWhere(['in', 'purchas_status', ['3', '5', '6', '7', '8', '9', '10']]);
        $query->andWhere(['=', 'pur_purchase_order.is_new',2]);

        $pageSize = isset($params['per-page']) ? intval($params['per-page']) : 10;

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);

        $this->load($params);
        if(isset($params['compact_number']) && !empty($params['compact_number'])) {
            $pos = PurchaseCompact::getPurNumbers($params['compact_number']);
            $query->andWhere(['in', 'pur_purchase_order.pur_number', $pos]);
            $this->compact_number = $params['compact_number'];
        }

        if(empty($this->buyer)){
            $userIds = Yii::$app->authManager->getUserIdsByRole('采购组-海外');
            $user = User::find()->select('username')->andFilterWhere(['id'=>$userIds])->asArray()->all();
            $query->andFilterWhere(['in','pur_purchase_order.buyer',array_column($user,'username')]);
        }

        $query->orderBy('submit_time desc');
        if (!$this->validate()) {
            return $dataProvider;
        }
        if ($this->getAttribute('items.sku')){
            $query->innerJoinWith('purchaseOrderItems AS items');
        }

        if($this->getAttribute('ship.express_no')){
            $query->innerJoinWith('orderShip AS ship');
        }
        if (is_array($this->purchas_status) && in_array(99,$this->purchas_status) ){
            $query->andwhere(['in','purchas_status',['7','8']]);
        }
        //批量查找采购单号
        if(strpos(trim($this->pur_number),' ')){
            $pur_number = preg_replace("/\s+/",',',trim($this->pur_number));
            $pur_number = explode(',',$pur_number);
            if(count($pur_number)>0){
                $query->andFilterWhere(['in', 'pur_purchase_order.pur_number', $pur_number]);
            }else{
                $query->andFilterWhere(['=', 'pur_purchase_order.pur_number', trim($this->pur_number)]);
            }
        }else{
            $query->andFilterWhere(['=', 'pur_purchase_order.pur_number', trim($this->pur_number)]);
        }

        $query->andFilterWhere([
            'pur_purchase_order.buyer' => Vhelper::chunkBuyerByNumeric($this->buyer),
            'is_arrival' => $this->is_arrival,
            'receiving_exception_status' => $this->receiving_exception_status,
            'qc_abnormal_status' => $this->qc_abnormal_status,
            'audit_return' => $this->audit_return,
            'create_type' => $this->create_type,
            'account_type' => $this->account_type,
            'shipping_method' => $this->shipping_method,
            'pur_purchase_order.supplier_code' => $this->supplier_code,
            'pur_purchase_order.merchandiser' => $this->merchandiser,
            'refund_status'   => $this->refund_status,
            'ship.express_no' => trim($this->getAttribute('ship.express_no')),
        ]);

        $query->andFilterWhere(['=', 'warehouse_code', $this->warehouse_code])
            ->andFilterWhere(['in', 'purchas_status', $this->purchas_status])
            ->andFilterWhere(['in', 'pur_purchase_order.pay_status', $this->pay_status])
            ->andFilterWhere(['=', 'arrival_status', $this->arrival_status])
            ->andFilterWhere(['like', 'pur_purchase_order.supplier_name', $this->supplier_name])
            ->andFilterWhere(['=', 'pur_type', $this->pur_type]);

        //批量查找sku
        if(strpos(trim($this->getAttribute('items.sku')),' ')){
            $sku = preg_replace("/\s+/",',',trim($this->getAttribute('items.sku')));
            $sku = explode(',',$sku);
            if(count($sku)>0){
                $query->andFilterWhere(['in', 'items.sku', $sku]);
            }else{
                $query->andFilterWhere(['like', 'items.sku', trim($this->getAttribute('items.sku'))]);
            }
        }else{
            $query->andFilterWhere(['like', 'items.sku', trim($this->getAttribute('items.sku'))]);
        }

        $query->leftJoin('pur_purchase_demand demand','demand.pur_number=pur_purchase_order.pur_number');
        $query->leftJoin('pur_platform_summary summary','summary.demand_number=demand.demand_number');
        $query->andFilterWhere(['and',"summary.agree_time<'2018-08-29 10:00:00' OR summary.agree_time IS NULL"]);

        $query->andFilterWhere(['between', 'pur_purchase_order.audit_time', $this->start_time, $this->end_time]);
        if (isset($params['shipfees_audit_status']))
            $query->andFilterWhere(['=', 'shipfees_audit_status', (int)$params['shipfees_audit_status']]);

        \Yii::$app->session->set('PurchaseOrderSearchSource', $params);
        if ($noDataProvider) return $query;
        return $dataProvider;
    }

    // 国内仓-采购订单首页-搜索
    public function search11($param)
    {
        $model = new PurchaseCompact();
        $query = $model::find()
            ->select('compact_number')
            ->where(['source' => 1])
            ->andWhere(['>', 'compact_status', 2]);

        $model->attributes = $param;
        $this->attributes = $param;

        if(isset($param['pur_number']) && trim($param['pur_number']) !== '') {
            $res = PurchaseCompactItems::find()->where(['pur_number' => trim($param['pur_number'])])->one();
            if(!empty($res)) {
                $model->compact_number = $res->compact_number;
                $this->pur_number = trim($param['pur_number']);
            }
        }
        if($model->compact_number) {
            $query->andWhere(['compact_number' => $model->compact_number]);
            $this->compact_number = $model->compact_number;
        }
        $query->andFilterWhere(['like', 'supplier_code', $model->supplier_code]);
        if($this->buyer) {
            $query->andWhere(['create_person_name' => $this->buyer]);
        }
        $count = $query->count();
        $pagination = new Pagination([
            'totalCount' => $count,
            'pageSize' => 10,
            'pageSizeParam' => false,
        ]);

        $query->orderBy('id desc');

        $compacts = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();
        $list = [];
        foreach($compacts as $m) {
            $pos = PurchaseCompact::getPurNumbers($m['compact_number']);
            $list[$m['compact_number']] = PurchaseOrder::find()->where(['in', 'pur_number', $pos])->all();
        }
        return [
            'list' => $list,
            'pagination' => $pagination
        ];
    }

    // 海外仓-合同审核-搜索
    public function search12($param)
    {
        $model = new PurchaseCompact();
        $query = $model::find()
            ->select('compact_number')
            ->where(['source' => 2, 'compact_status' => 2]);

        $model->attributes = $param;
        $this->attributes = $param;

        if(isset($param['pur_number']) && trim($param['pur_number']) !== '') {
            $res = PurchaseCompactItems::find()->where(['pur_number' => trim($param['pur_number'])])->one();
            if(!empty($res)) {
                $model->compact_number = $res->compact_number;
                $this->pur_number = trim($param['pur_number']);
            }
        }
        if($model->compact_number) {
            $query->andWhere(['compact_number' => $model->compact_number]);
            $this->compact_number = $model->compact_number;
        }
        $query->andFilterWhere(['like', 'supplier_code', $model->supplier_code]);
        if($this->buyer) {
            $query->andWhere(['create_person_name' => $this->buyer]);
        }
        $count = $query->count();
        $pagination = new Pagination([
            'totalCount' => $count,
            'pageSize' => 10,
            'pageSizeParam' => false,
        ]);

        $query->orderBy('id desc');

        $compacts = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();
        $list = [];
        foreach($compacts as $m) {
            $pos = PurchaseCompact::getPurNumbers($m['compact_number']);
            $list[$m['compact_number']] = PurchaseOrder::find()->where(['in', 'pur_number', $pos])->all();
        }
        return [
            'list' => $list,
            'pagination' => $pagination
        ];
    }


    // 国内仓-采购审核-搜索
    public function search13($param)
    {
        $model = new PurchaseCompact();
        $query = $model::find()
            ->select('compact_number')
            ->where(['source' => 1, 'compact_status' => 2]);

        $model->attributes = $param;
        $this->attributes = $param;

        if(isset($param['pur_number']) && trim($param['pur_number']) !== '') {
            $res = PurchaseCompactItems::find()->where(['pur_number' => trim($param['pur_number'])])->one();
            if(!empty($res)) {
                $model->compact_number = $res->compact_number;
                $this->pur_number = trim($param['pur_number']);
            }
        }
        if($model->compact_number) {
            $query->andWhere(['compact_number' => $model->compact_number]);
            $this->compact_number = $model->compact_number;
        }
        $query->andFilterWhere(['like', 'supplier_code', $model->supplier_code]);
        if($this->buyer) {
            $query->andWhere(['create_person_name' => $this->buyer]);
        }
        if($this->supplier_special_flag !== '' AND $this->supplier_special_flag !== NULL){
            if($this->supplier_special_flag == '1'){
                $query->andWhere("supplier_code IN (SELECT supplier_code FROM pur_supplier WHERE supplier_special_flag='1' )");
            }else{
                $query->andWhere("supplier_code NOT IN (SELECT supplier_code FROM pur_supplier WHERE supplier_special_flag='1' )");
            }
        }
        $count = $query->count();
        $pagination = new Pagination([
            'totalCount' => $count,
            'pageSize' => 10,
            'pageSizeParam' => false,
        ]);

        $query->orderBy('id desc');

        $compacts = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();
        $list = [];
        foreach($compacts as $m) {
            $pos = PurchaseCompact::getPurNumbers($m['compact_number']);
            $list[$m['compact_number']] = PurchaseOrder::find()->where(['in', 'pur_number', $pos])->all();
        }
        return [
            'list' => $list,
            'pagination' => $pagination
        ];
    }

    // 国内仓-采购单-搜索
    public function search15($params)
    {
        $query = PurchaseOrder::find();

        $query->where(['source' => 1]);

        // add conditions that should always apply here
        $pageSize = isset($params['per-page']) ? intval($params['per-page']) : 20;
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);

        $this->load($params);


        if(isset($params['compact_number'])) {
            $pos = PurchaseCompact::getPurNumbers($params['compact_number']);
            $query->andWhere(['in', 'pur_number', $pos]);
            $this->compact_number = $params['compact_number'];
        }



        //$query->where(['in','purchas_status',['3','5','6','7','8','9','10']]);
        $query->andFilterWhere(['in','pur_purchase_order.purchase_type',['1']]);
        if (in_array('FBA采购经理组',array_keys(Yii::$app->authManager->getRolesByUser(Yii::$app->user->id)))) {

        } else if(!in_array('供应链',array_keys(Yii::$app->authManager->getRolesByUser(Yii::$app->user->id)))){
            $buyer = PurchaseOrderServices::getPurchaseOrderBuyerByRole();
            if(is_array($buyer)){
                $query->andWhere(['in','buyer',$buyer]);
            }
        }
        $query->orderBy('submit_time desc');
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        if ($this->getAttribute('items.sku'))
        {
            $query->innerJoinWith('purchaseOrderItems AS items');
        }

        if($this->getAttribute('ship.express_no'))
        {
            $query->innerJoinWith('orderShip AS ship');
        }
        if ($this->purchas_status==99)
        {
            $query->andwhere(['in','purchas_status',['7','8']]);

        } else {
            $query->andFilterWhere(['=', 'purchas_status', $this->purchas_status]);
        }
        //批量查找采购单号
        if(strpos($this->pur_number,',') || strpos($this->pur_number,'，')){
            $this->pur_number = preg_replace("/\s/","",$this->pur_number);
            $pur_number = explode(',',str_replace('，',',',$this->pur_number));
            if(count($pur_number)>0){
                $query->andFilterWhere(['in', 'pur_purchase_order.pur_number', $pur_number]);
            }
        }else{
            $query->andFilterWhere(['=', 'pur_purchase_order.pur_number', trim($this->pur_number)]);
        }

        //$query->andFilterWhere(['=','items.sku' , $this->getAttribute('items.sku')]);

        // grid filtering conditions
        $query->andFilterWhere([
            'pur_purchase_order.buyer' =>  Vhelper::chunkBuyerByNumeric($this->buyer),
            'is_arrival' => $this->is_arrival,
            'receiving_exception_status' => $this->receiving_exception_status,
            'qc_abnormal_status' => $this->qc_abnormal_status,
            'audit_return' => $this->audit_return,
            'pay_status' => $this->pay_status,
            'create_type' => $this->create_type,
            'account_type' => $this->account_type,
            'shipping_method' => $this->shipping_method,
            'pur_purchase_order.supplier_code' => $this->supplier_code,
            'pur_purchase_order.merchandiser' => $this->merchandiser,
            'refund_status'   => $this->refund_status,
            //'items.sku' => $this->getAttribute('items.sku'),
            'ship.express_no' => trim($this->getAttribute('ship.express_no')),


        ]);
        $query->andFilterWhere(['=', 'warehouse_code', $this->warehouse_code])
 //           ->andFilterWhere(['like', 'items.sku', trim($this->getAttribute('items.sku'))])
//            ->andFilterWhere(['=', 'purchas_status', $this->purchas_status])
            ->andFilterWhere(['like', 'pur_purchase_order.supplier_name', $this->supplier_name])
            ->andFilterWhere(['=', 'pur_type', $this->pur_type]);
        if($this->supplier_special_flag !== '' AND $this->supplier_special_flag !== NULL){
            $query->joinWith('supplier');
            $query->andWhere(['=', 'pur_supplier.supplier_special_flag', $this->supplier_special_flag]);
        }
        //批量查找sku
        if(strpos(trim($this->getAttribute('items.sku')),',') || strpos(trim($this->getAttribute('items.sku')),'，')){
            $sku = trim($this->getAttribute('items.sku'));
            $sku = preg_replace("/\s/","",$sku);
            $sku = explode(',',str_replace('，',',',$sku));
            if(count($sku)>0){
                $query->andFilterWhere(['in', 'items.sku', $sku]);
            }
        }else{
            $query->andFilterWhere(['like', 'items.sku', trim($this->getAttribute('items.sku'))]);
        }

        if (!empty($this->start_time)) {
            $this->start_time = $this->start_time . ' 00:00:00';
            $this->end_time = $this->end_time . ' 23:59:59';
            $query->andFilterWhere(['between', 'audit_time', $this->start_time, $this->end_time]);
        } else {
            $query->andFilterWhere(['between', 'audit_time', date('Y-m-d H:i:s',strtotime("-6 month")), date('Y-m-d H:i:s',time())]);
        }
        \Yii::$app->session->set('PurchaseOrderSearchData', $params);

        return $dataProvider;
    }



    /**海外仓-采购单数据统计
     * @param $params
     * @param bool $noDataProvider
     * @return ActiveDataProvider|\yii\db\ActiveQuery
     */
    public function searchOrderRate($params, $noDataProvider = false)
    {
        $query = User::find();
        $pageSize = isset($params['per-page']) ? intval($params['per-page']) : 20;
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);

        $this->load($params);

        if(empty($this->buyer)){
            $userIds = Yii::$app->authManager->getUserIdsByRole('采购组-海外');
            $query->andFilterWhere(['id'=>$userIds]);
        }else{
            $query->where(['id'=>"{$this->buyer}"]);
        }
        $query->andWhere('status!=0')->andWhere(['not in', 'username', ['谭竣','史家松','王范彬','张建蓉']]);
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        \Yii::$app->session->set('PurchaseOrderSearchData', $params);
        if ($noDataProvider)
            return $query;
        return $query;
    }

    //根据登录用户权限获取采购员
    public static function getBuyer($id=null,$name=null){
        $userIds = Yii::$app->authManager->getUserIdsByRole('采购组-海外');
        $User   = User::find()->andFilterWhere(['id'=>$userIds])->select('id,username,alias_name');
        if (!empty($id))
        {
            $User->andWhere(['id' => $id]);
            $result = $User->asArray()->one();
            return $result['username'];
        } else{
            $user= $User->asArray()->all();
            $result = empty($name)?ArrayHelper::map($user,'id','username'):ArrayHelper::map($user,'id','username');
            return $result;
        }
    }

    public function search14($params, $noDataProvider = false)
    {
        $query = PurchaseOrderItems::find();
        $query->select(['pur_purchase_order_items.id','pur_purchase_order_items.pur_number','pur_purchase_order_items.sku','product_img','name','ctq','cty']);
        //关联订单主表,物流表
        $query->joinWith(['purNumber']);

        $pageSize = isset($params['per-page']) ? intval($params['per-page']) : 20;
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);
        $query->where(['in','pur_purchase_order.purchas_status',['5','7','8']]);
        $query->andFilterWhere(['in','pur_purchase_order.purchase_type',['2']]);
        $query->andFilterWhere(['!=',"ifnull(ctq, 0) - ifnull(cty, 0)",0]);

        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }
        if($this->warn_status){
            $query->joinWith('warnStatus');
            $query->andFilterWhere(['pur_purchase_warning_status.warn_status'=>$this->warn_status]);
        }

        if($this->settlement){
            $query->andFilterWhere(['pur_purchase_order.account_type'=>$this->settlement]);
        }

        $query->andFilterWhere([
            'pur_purchase_order.buyer' => trim($this->buyer),
        ]);
        $query->andFilterWhere([
            'pur_purchase_order_items.pur_number' => trim($this->pur_number),
        ]);
        $query->andFilterWhere(['like', 'pur_purchase_order_items.sku', trim($this->getAttribute('items.sku'))])
            ->andFilterWhere(['like', 'supplier_name', trim($this->supplier_name)]);

        if (!empty($this->start_time)) {
            $this->start_time = $this->start_time . ' 00:00:00';
            $this->end_time = $this->end_time . ' 23:59:59';
            $query->andFilterWhere(['between', 'audit_time', $this->start_time, $this->end_time]);
        } else {
            $query->andFilterWhere(['between', 'audit_time', date('Y-m-d H:i:s',strtotime("-6 month")), date('Y-m-d H:i:s',time())]);
        }
        \Yii::$app->session->set('purchaseOrderOverFollowSearch', $params);
        if ($noDataProvider)
            return $query;
        return $dataProvider;
    }


    /**采购订单统计
     * @param $params
     * @param bool $noDataProvider
     * @return ActiveDataProvider|\yii\db\ActiveQuery
     */
    public function searchOrderStatistics($params, $noDataProvider = false)
    {
        $query = PurchaseOrderItems::find();
        $query->alias('items');

        $query->leftJoin('pur_purchase_order order','order.pur_number=items.pur_number');

        $query->leftJoin('pur_product product','product.sku=items.sku');

        $query->select(
            '
            items.sku,
            items.name,
            items.price,
            items.cty,
            order.purchase_type,
            order.supplier_name,
            order.is_drawback,
            order.account_type,
            product.product_category_id
           '
        );

        $pageSize = isset($params['per-page']) ? intval($params['per-page']) : 20;
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);


        $this->load($params);

        //用户类型有搜索条件根据搜索条件搜索
        if(isset($params['PurchaseOrderSearch']['purchase_type'])) {
            $query->where(['order.purchase_type'=>$params['PurchaseOrderSearch']['purchase_type']]);
        }else{
            //没有 默认查询国内仓
            $query->where(['order.purchase_type'=>1]);
        }

        //审核时间有搜索条件根据搜索条件搜索
        if (!empty($this->start_time)) {
            $query->andFilterWhere(['between', 'order.audit_time', $this->start_time, $this->end_time]);
        } else {
            $query->andFilterWhere(['between', 'order.audit_time', date('Y-m-d H:i:s',strtotime("-0 year -6 month -0 day")), date('Y-m-d H:i:s',time())]);
        }

        $query->andFilterWhere(['in','order.purchas_status',['3', '5', '6', '7', '8', '9', '10']]);

        if ($this->sku)
        {
            $searchSku = $this->sku;
            if(strpos($searchSku,' ')){// 多SKU 精确查询
                $searchSku = explode(' ',$searchSku);
                $query->andFilterWhere(['in','items.sku',$searchSku]);
            }else{// 单SKU模糊查询
                $query->andFilterWhere(['like','items.sku', "$searchSku"]);
            }
        }
        if($this->supplier_name){// 供应商名称或代码查询
            $query->andWhere(['or', "order.supplier_name='$this->supplier_name' ", "order.supplier_code='$this->supplier_name' "]);
        }

        //$query->orderBy('order.id desc');

        //$query->groupBy('order.pur_number');

        $query->groupBy('items.sku');

        if (!$this->validate()) {
            return $dataProvider;
        }

        $dataProvider->setSort([
            'attributes' => [
                'sku' => [
                    'desc' => ['items.sku' => SORT_DESC],
                    'asc' => ['items.sku' => SORT_ASC],
                    'label' => 'taxes'
                ],
                'supplier_name' => [
                    'desc' => ['order.supplier_name' => SORT_DESC],
                    'asc' => ['order.supplier_name' => SORT_ASC],
                    'label' => 'taxes'
                ],
            ]
        ]);

        \Yii::$app->session->set('PurchaseOrderSearchData', $params);
        if ($noDataProvider)
            return $query;

        return $dataProvider;
    }
}
