<?php
namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use app\services\BaseServices;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\log\EmailTarget;
use app\api\v1\models\PurchaseAbnomal;

/**
 * PurchaseOrderSearch represents the model behind the search form about `app\models\PurchaseOrder`.
 */
class PlatformSummarySearch extends PlatformSummary
{
    public  $amount_1;
    public  $amount_2;
    public  $left_time;
    public  $supplier_special_flag;
    public  $default_supplier_code;
    public  $default_supplier_name;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['dimension','sku','sales','order_buyer','order_status','default_buyer','product_line','is_drawback','demand_number','pur_number','group_id','product_category',
                'is_purchase','create_time','start_time','end_time','platform_number','purchase_warehouse','supplierQuotes.suppliercode','level_audit_status',
                'product_name','start_time','end_time','supplier_code','supplier_name','amount_1','amount_2','order.purchas_status','is_back_tax',
                'pay_status','refund_status','date_eta_is_timeout','avg_eta_is_timeout','weight_sku', 'init_level_audit_status','supplier_special_flag','xiaoshou_zhanghao'], 'safe'],
        ];
    }

    public function attributes()
    {
        // 添加关联字段到可搜索属性集合
        return array_merge(parent::attributes(), ['supplierQuotes.suppliercode','order.purchas_status','refund_status','pay_status','date_eta_is_timeout','avg_eta_is_timeout','weight_sku']);
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
     * 获取 海外仓需求的平台类型列表
     * @param string $platform   指定平台
     * @param bool $is_list true.返回整个数组,false.返回字符串
     * @return array|mixed|string
     */
    public static function overseasPlatformList($platform,$is_list = false){
        $platformlist = [
            'SHOPEE'=>'SHOPEE',
            'LAZADA'=>'LAZADA',
            'CDISCOUNT'=>'CDISCOUNT',
            'WISH'=>'WISH',
            'EB'=>'EB',
            'AMAZON'=>'AMAZON',
            'ALI'=>'ALI',
            'SMT'=>'SMT',
            'WalMart'=>'WalMart',
            'Public'=>'Public',
            'aiifun' => 'aiifun',
        ];

        if($is_list){
            return $platformlist;
        }else{
            return isset($platformlist[$platform])?$platformlist[$platform]:'';
        }
    }

    /**
     * FBA销售采购汇总
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params,$nodataProvider=false)
    {
        $pageSize = isset($params['per-page']) ? intval($params['per-page']) : 50;
        //$fields                     = ['*','sum(purchase_quantity) as total_purchase_quantity'];
        $query = PlatformSummary::find();
        $query->alias('t');
        // add conditions that should always apply here
        $query->andwhere(['in','t.purchase_type',['3']]);

        $query->andwhere(['t.init_level_audit_status'=>0]);

        $query->orderBy('t.agree_time asc');
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);

        $this->load($params);


        //$query->select('t.*,pur_product_supplier.supplier_code');
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andWhere(['or',['<','t.agree_time',date('Y-m-d 00:00:00)')],['is_push_priority'=>1]]);
        if($params) {
            $query->andFilterWhere(['like', 't.sku', trim($this->sku)]);
            $query->andFilterWhere([
                't.demand_number'    => trim($this->demand_number),
                //'t.product_category' => $this->product_category,
                //'supplier_code'    => $this->supplier_code,
                //'t.is_purchase'      => !empty($this->is_purchase) && $this->is_purchase!='all' ? $this->is_purchase : 1,
                't.group_id'      => !empty($this->group_id) && $this->group_id!='all' ? $this->group_id : null,
            ]);
            if (empty($this->level_audit_status)) {
                $query->andFilterWhere(['t.level_audit_status'    => 1]);
            } elseif(!empty($this->level_audit_status)&&$this->level_audit_status!='all'){
                $query->andFilterWhere(['t.level_audit_status'=>$this->level_audit_status]);
            }else{
            }
            if (empty($this->is_purchase)) {
                $query->andFilterWhere(['t.is_purchase'    => 1]);
            } elseif(!empty($this->is_purchase)&&$this->is_purchase!='all'){
                $query->andFilterWhere(['t.is_purchase'=>$this->is_purchase]);
            }else{

            }
            if(!empty($this->is_back_tax)&&$this->is_back_tax!='all'){
                $query->andFilterWhere(['t.is_back_tax'=>$this->is_back_tax]);
            }
            $query->andFilterWhere(['t.xiaoshou_zhanghao'=>$this->xiaoshou_zhanghao]);
            //如果筛选的时间，大于今天零点，就只要今天之前的
//            if (strtotime($this->start_time) >= strtotime(date('Y-m-d 00:00:00'))) {
//                $query->andWhere(['between', 't.agree_time', date('Y-m-d 00:00:00',time()-86400), date('Y-m-d 23:59:59',time()-86400)]);
//            } else if (strtotime($this->end_time) > strtotime(date('Y-m-d 00:00:00'))) {
//                $query->andFilterWhere(['between', 't.agree_time', $this->start_time, date('Y-m-d 23:59:59',time()-86400)]);
//            } else {
//                $query->andFilterWhere(['between', 't.agree_time', $this->start_time, $this->end_time]);
//            }
            $query->andFilterWhere(['between', 't.agree_time', $this->start_time, $this->end_time]);
            if(!empty($this->supplier_code) OR ($this->supplier_special_flag !== '' AND $this->supplier_special_flag !== NULL)){
                $query->joinWith('defaultSupplier');
                $query->andFilterWhere(['pur_product_supplier.supplier_code'=>$this->supplier_code]);
                if($this->supplier_special_flag !== ''){
                    $query->leftJoin(Supplier::tableName(),'pur_supplier.supplier_code=pur_product_supplier.supplier_code');
                    $query->andWhere(['=', 'pur_supplier.supplier_special_flag', $this->supplier_special_flag]);
                }
            }
            if(!empty($this->product_line)||!empty($this->default_buyer)){
                $query->joinWith('defaultSupplierLine');
            }
            if(!empty($this->product_line)){
                $query->andFilterWhere(['pur_supplier_product_line.first_product_line'=>$this->product_line]);
            }
            if($this->amount_1 <= $this->amount_2)
            {
                $query->andFilterWhere(['>', 'purchase_quantity', $this->amount_1]);
                $query->andFilterWhere(['<=', 'purchase_quantity', $this->amount_2]);
            }

            //产品线默认采购员搜索
            if(!empty($this->default_buyer)){
                $bind = PurchaseCategoryBind::find()->andFilterWhere(['buyer_name'=>$this->default_buyer])->asArray()->all();
                $productLine = array_column($bind,'category_id');
                $query->andFilterWhere(['in','pur_supplier_product_line.first_product_line',$productLine]);
            }
        } else{
            $query->andWhere(['in','t.is_purchase',['1']]);
            $query->andWhere(['in','t.level_audit_status',['1']]);
            $query->andWhere(['between', 't.agree_time', date('Y-m-d H:i:s',strtotime("-6 month")), date('Y-m-d 23:59:59',time()-86400)]);
        }
        if($nodataProvider){
            return $query;
        }
        Yii::$app->session->set('FBA_SUMMARY_SEARCH',$params);
//        Vhelper::dump($query->createCommand()->getRawSql());
        return $dataProvider;
    }
    /**
     * 添加产品
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search1($params)
    {
        //$fields                     = ['*','sum(purchase_quantity) as total_purchase_quantity'];
        $fields                     = ['*'];
        $query = PlatformSummary::find()->select($fields);
        $Dy    = DynamicTable::getId();
        // add conditions that should always apply here
        $query->where(['in','pur_platform_summary.level_audit_status',['1']]);
        $query->andwhere(['in','pur_platform_summary.is_purchase',['1']]);
        $query->andwhere(['in','pur_platform_summary.purchase_type',['2']]);
        $query->andwhere(['not in','pur_platform_summary.id',$Dy]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        $this->load($params);
        //$query->groupBy(['sku','purchase_warehouse']);
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        if($params) {
            if ($this->getAttribute('supplierQuotes.suppliercode')) {
                $query->joinWith('historyB');
            }
            //$query->groupBy('history.sku');
            // grid filtering conditions
            $query->andFilterWhere([
                'pur_platform_summary.sku'                => $this->sku,
                'pur_platform_summary.purchase_warehouse' => $this->purchase_warehouse,
                'pur_platform_summary.platform_number'    => $this->platform_number,
                'pur_platform_summary.demand_number'      => $this->demand_number,

            ]);
            $query->andFilterWhere(['like','pur_platform_summary.product_name',$this->product_name]);
            $query->andFilterWhere(['like','pur_purchase_history.supplier_name',$this->getAttribute('supplierQuotes.suppliercode')]);
        } else{
            $query->andwhere(['in','pur_platform_summary.is_purchase',['1']]);
        }
        return $dataProvider;
    }

    /**
     * 海外仓销售采购汇总
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search2($params)
    {
        //$fields                     = ['*','sum(purchase_quantity) as total_purchase_quantity'];
       // $fields                     = ['*'];
        $query = PlatformSummary::find();

        // add conditions that should always apply here
        $query->andwhere(['in','pur_platform_summary.purchase_type',['2']]);
        $pageSize = isset($params['per-page']) ? intval($params['per-page']) : 20;
        $query->orderBy('pur_platform_summary.id desc');
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);

        if(in_array('产品开发组',array_keys(Yii::$app->authManager->getRolesByUser(Yii::$app->user->id)))) {

            $query->leftJoin('pur_product','pur_product.sku=pur_platform_summary.sku');
            $query->andFilterWhere(['pur_product.product_is_new'=>1]);
        }
        $this->load($params);
        //$query->groupBy(['sku','purchase_warehouse']);
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        if ($this->getAttribute('order.purchas_status'))
        {
            $query->innerJoinWith('purOrder AS order');
        }
        if ($this->getAttribute('order.purchas_status') === '1') { //未生成采购单
//            $query->andFilterWhere(['in','order.purchas_status',['1','2','4','10']]);
            $query->andFilterWhere(['not in','order.purchas_status',['4','10','3','5','6','7','8','9','99']]);
        } else if ($this->getAttribute('order.purchas_status') === '2') {
            $query->andFilterWhere(['in','order.purchas_status',['3','5','6','7','8','9','99']]);
        }

        // grid filtering conditions
        if($params) {
            $query->andFilterWhere([

                'pur_platform_summary.demand_number'    => trim($this->demand_number),
                'pur_platform_summary.product_category' => $this->product_category,
                'pur_platform_summary.platform_number' => $this->platform_number,
                'pur_platform_summary.purchase_warehouse' => $this->purchase_warehouse,
                'pur_platform_summary.level_audit_status' => $this->level_audit_status,
                //'supplier_code' => $this->supplier_code,
                'pur_platform_summary.is_purchase'      => !empty($this->is_purchase)?$this->is_purchase:[1,2],
                //'level_audit_status'      => !empty($this->level_audit_status)?$this->is_purchase:3,
            ]);

            if($this->supplier_code){
                $query->joinWith('defaultSupplier');
                $query->andFilterWhere(['pur_product_supplier.supplier_code'=>$this->supplier_code]);
            }


            if($this->is_purchase)
            {

            } else{
                $query->andWhere(['not in','pur_platform_summary.level_audit_status',['3']]);
            }

            $query->andFilterWhere(['like','pur_platform_summary.sku',trim($this->sku)]);
            $query->andFilterWhere(['between', 'pur_platform_summary.create_time', $this->start_time,$this->end_time]);

        } else {
            $query->andWhere(['not in','pur_platform_summary.level_audit_status',['3']]);
            $query->andwhere(['in','pur_platform_summary.is_purchase',['1']]);
        }

//        Vhelper::dump($query->createCommand()->getRawSql());
        return $dataProvider;
    }
    /**
     * FBA销售汇总
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search3($params)
    {
        //$fields                     = ['*','sum(purchase_quantity) as total_purchase_quantity'];
        //$fields                     = ['*'];
        $pageSize = isset($params['per-page']) ? intval($params['per-page']) : 20;
        $query = PlatformSummary::find();

        // add conditions that should always apply here
        $query->alias('t');
        //$query->where(['in','t.level_audit_status',['0','4','2','1']]);
        $query->andwhere(['in','t.purchase_type',['3']]);
        $query->orderBy('t.id desc');
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);

        $this->load($params);
        //$query->groupBy(['sku','purchase_warehouse']);
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

//        $userGroup = SupervisorGroupBind::find()->where(['supervisor_id'=>Yii::$app->user->id])->all();
//        $groupId   = [];
//        if(!empty($userGroup)){
//            foreach($userGroup  as $value){
//                $groupId[] = $value->group_id;
//            }
//        }

        $groupId = BaseServices::getGroupByUserName(2);
        // grid filtering conditions
        if($params) {
            $query->andFilterWhere(['like', 't.sku', trim($this->sku)]);
            $query->andFilterWhere([
                't.demand_number'    => trim($this->demand_number),
                //'product_category' => $this->product_category,
                't.is_purchase'      => !empty($this->is_purchase) && $this->is_purchase != 'all' ? $this->is_purchase : null,
                't.is_back_tax'      => !empty($this->is_back_tax) && $this->is_back_tax != 'all' ? $this->is_back_tax : null,
                //'t.level_audit_status'=> !empty($this->level_audit_status) ? $this->level_audit_status : 0,
            ]);
            if($this->level_audit_status != 'all'){
                $query->andFilterWhere([
                    't.level_audit_status'=> !empty($this->level_audit_status) ? $this->level_audit_status : 0,
                ]);
            }
            if(!empty($this->group_id)){
                if($this->group_id == 'all'&&!empty($groupId)){
                    $query->andWhere(['in','t.group_id',$groupId]);
                }else{
                    $query->andFilterWhere(['t.group_id' => !empty($this->group_id) && $this->group_id != 'all' ? $this->group_id : null]);
                }
            }else{
                if(!empty($groupId)){
                    $query->andWhere(['in','t.group_id',$groupId]);
                }
            }
            $query->andFilterWhere(['between', 't.create_time', $this->start_time, $this->end_time]);
            if(!empty($this->product_line)){
                $query->joinWith('product');
                $query->andFilterWhere(['in','pur_product.product_linelist_id',BaseServices::getProductLineChild($this->product_line)]);
            }
        } else {
            if(!empty($groupId)){
                $query->andWhere(['in','t.group_id',$groupId]);
            }
            $query->andFilterWhere(['t.level_audit_status'=>0]);

        }

        if(Yii::$app->user->identity->username == '刘楚雯'){
            $query->andFilterWhere(['t.init_level_audit_status'=>1]);
        } else {
            $query->andFilterWhere(['t.init_level_audit_status'=>$this->init_level_audit_status]);
        }

        $query->andFilterWhere(['t.xiaoshou_zhanghao'=>$this->xiaoshou_zhanghao]);

        //判断当前登录账户是否为销售经理   销售经理只能查看销售提交了的需求
        $role = Yii::$app->authManager->getRolesByUser(Yii::$app->user->identity->id);

        $is_visible = 0;
        if ($role && is_array($role)) {
            foreach ($role as $key => $value) {
                if(preg_match('/FBA销售经理组/',$key)){
                    $is_visible = 1;
                }
            }
            if($is_visible){
                $query->andFilterWhere(['t.sale_audit_status'=>1]);
            }
        }
       // Vhelper::dump($query->createCommand()->getRawSql());

        return $dataProvider;
    }

    /**
     * 销售需求跟踪查询
     */
    public function search4($params,$id=null,$noDataProvider=false)
    {
        $pageSize = isset($params['per-page']) ? intval($params['per-page']) : 20;
        $query = PlatformSummary::find();
        $query->alias('t');
        $query->andwhere(['in','t.purchase_type',['3']]);
        $query->andFilterWhere(['<>','t.level_audit_status',3]);
        $query->orderBy('t.id desc');
        $query->joinWith('purOrder');
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

        $groupId = BaseServices::getGroupByUserName(2);
        if($params) {
            $query->andFilterWhere(['between', 't.create_time', $this->start_time,$this->end_time]);
            $query->andFilterWhere([
                't.sales'                               => trim($this->sales),
                't.sku'                                 => trim($this->sku),
                't.purchase_warehouse'                  => $this->purchase_warehouse,
                't.is_purchase'                         => !empty($this->is_purchase)&& $this->is_purchase != 'all' ? $this->is_purchase : null,
                't.is_back_tax'                         => !empty($this->is_back_tax)&& $this->is_back_tax != 'all' ? $this->is_back_tax : null,
                't.level_audit_status'                  => $this->level_audit_status,
                'pur_purchase_order.buyer'              => $this->order_buyer,
                'pur_purchase_order.pur_number'         => trim($this->pur_number),
                'pur_purchase_order.supplier_code'      => $this->supplier_code,
                'pur_purchase_order.purchas_status'     => $this->order_status,
                'pur_purchase_order.is_drawback'        => !empty($this->is_drawback) && $this->is_drawback != 'all' ? $this->is_drawback : null,
                'pur_purchase_order.pay_status'         => $this->pay_status,
            ]);
/*            if ($this->order_status=='8') {
                $query->leftJoin("pur_purchase_order_items as i","pur_purchase_demand.pur_number=i.pur_number ")
                      ->where("i.rqy is null");

            }*/

            if(isset($this->refund_status) AND $this->refund_status){// 退款状态
                if($this->refund_status == 1 OR $this->refund_status == 2){
                    $query->andFilterWhere(['pur_purchase_order.refund_status'=>$this->refund_status]);
                }else{
                    $subQuery_receipt = (new Query())->select("pur_number")
                        ->from('pur_purchase_order_receipt')
                        ->where("pay_status='0'")
                        ->groupBy('pur_number');
                    $query->andWhere(['in','pur_purchase_order.pur_number',$subQuery_receipt]);
                }
            }

            if($this->date_eta_is_timeout OR $this->avg_eta_is_timeout){// 此种查询条件 必须有对应的采购单
                $query->andWhere("pur_purchase_order.pur_number !='' AND pur_purchase_order.pur_number IS NOT NULL");
            }
            if($this->date_eta_is_timeout OR $this->avg_eta_is_timeout){// 此种查询条件 对应的采购单必须审核
                $query->andWhere("pur_purchase_order.audit_time !='' AND pur_purchase_order.audit_time IS NOT NULL");
            }

            if($this->date_eta_is_timeout){// 预计到货是否超时
                if($this->date_eta_is_timeout == 2){// 预计到货是否超时(否)  查询未超时
                    $subQuery2 = (new Query())->select('sku')
                        ->from("pur_fab_purchase_order_trace AS cache_d")
                        ->where("cache_d.sku=t.sku")
                        ->andWhere("cache_d.pur_number=pur_purchase_order.pur_number")
                        ->andWhere('data_type=1');
                    $query->andWhere(['exists',$subQuery2]);
                }else{
                    $subQuery2 = (new Query())->select('sku')
                        ->from("pur_fab_purchase_order_trace AS cache_d")
                        ->where("cache_d.sku=t.sku")
                        ->andWhere("cache_d.pur_number=pur_purchase_order.pur_number")
                        ->andWhere('data_type=1');
                    $query->andWhere(['not exists',$subQuery2]);
                }
            }
            if($this->avg_eta_is_timeout){// 权均交期是否超时
                if($this->avg_eta_is_timeout == 2){// 否
                    $subQuery3 = (new Query())->select('sku')
                        ->from("pur_fab_purchase_order_trace AS cache_d")
                        ->where("cache_d.sku=t.sku")
                        ->andWhere("cache_d.pur_number=pur_purchase_order.pur_number")
                        ->andWhere('data_type=2');
                    $query->andWhere(['exists',$subQuery3]);
                }else{
                    $subQuery3 = (new Query())->select('sku')
                        ->from("pur_fab_purchase_order_trace AS cache_d")
                        ->where("cache_d.sku=t.sku")
                        ->andWhere("cache_d.pur_number=pur_purchase_order.pur_number")
                        ->andWhere('data_type=2');
                    $query->andWhere(['not exists',$subQuery3]);
                }
            }
            if($this->weight_sku){// 是否是 加重SKU
                $subQuery_sku_1 = (new Query())->select("p_p.sku")
                    ->from("pur_product AS p_p")
                    ->where("p_p.is_boutique=1")
                    ->orWhere("p_p.is_repackage=1")
                    ->orWhere("p_p.is_weightdot=1");

                $subQuery4 = (new Query())->select('sku')
                    ->from("pur_fab_purchase_order_trace AS cache_d")
                    ->where("cache_d.sku=t.sku")
                    ->andWhere('data_type=3');

                if($this->weight_sku == 1){// 重、包、精
                    $query->andWhere(['OR',['not exists',$subQuery4],['in','t.sku',$subQuery_sku_1]]);
                }else{
                    $query->andWhere(['AND',['exists',$subQuery4],['not in','t.sku',$subQuery_sku_1]]);
                }
            }

            if(!empty($this->group_id)){
                if($this->group_id != 'all'){
                    $query->andWhere(['in','t.group_id',$this->group_id]);
                }
            }else{
                if(!empty($groupId)){
                    $query->andWhere(['in','t.group_id',$groupId]);
                }
            }
            if($this->amount_1 <= $this->amount_2)
            {
                $query->andFilterWhere(['>', 'purchase_quantity', $this->amount_1]);
                $query->andFilterWhere(['<=', 'purchase_quantity', $this->amount_2]);
            }
        } else {
            if(!empty($groupId)){
                $query->andWhere(['in','group_id',$groupId]);
            }
        }

        if ($id) {
            $query->andWhere(['in','t.id',$id]);
        }

        $query->andFilterWhere(['t.xiaoshou_zhanghao'=>$this->xiaoshou_zhanghao]);
        $query->andFilterWhere(['t.demand_number'=>$this->demand_number]);
        // grid filtering conditions
        Yii::$app->session->set('FBA-plat_summary_detail',$params);
        if($noDataProvider){
            return $query;
        }

        return $dataProvider;
    }

    //销售需求图像统计搜索
    public function search5($type='sales',$group_id=1,$start,$end)
    {

        $group_id = explode(',',$group_id);
        $platQuery = PlatformSummary::find()->alias('a')->select('a.sku as sku,a.sales,a.group_id as group_id,b.pur_number as pur_number,a.purchase_quantity as purchase_quantity')->leftJoin('pur_purchase_demand as b','{{a}}.demand_number={{b}}.demand_number')->where(['a.is_purchase'=>2,'a.purchase_type'=>3])->andFilterWhere(['between','{{a}}.create_time',$start, $end]);
        $query = new Query();
        $query->from(['c'=>$platQuery])->leftJoin('pur_purchase_order_items as d','{{c}}.sku={{d}}.sku AND {{c}}.pur_number = {{d}}.pur_number') ->leftJoin('pur_purchase_order as e','{{c}}.pur_number={{e.pur_number}}')->andWhere(['NOT', ['d.id' => null]])->andFilterWhere(['NOT IN','{{e}}.purchas_status',[1,4,10]]);
        $query->select('c.sales,c.group_id,sum(c.purchase_quantity * d.price) as total,sum(case when (cast(c.purchase_quantity as signed)-cast(d.rqy as signed))>0 then (cast(c.purchase_quantity as signed)-cast(d.rqy as signed))*d.price else 0 end) as left_arrive,sum(c.purchase_quantity) as pur_num,sum(case when (cast(c.purchase_quantity as signed)-cast(d.rqy as signed))>0 then (cast(c.purchase_quantity as signed)-cast(d.rqy as signed)) else 0 end) as left_num');
        $query->andFilterWhere(['in','{{c}}.group_id',$group_id]);
        //获取以销售为维度的采购金额
        if($type=='sales'){
            $query->groupBy('{{c}}.sales');
        }else{
            //获取以销售分组为维度的采购金额
            $query->groupBy('{{c}}.group_id');
        }
        return $query->all();
    }

    //销售需求列表统计搜索
    public function search6($param)
    {
        $pageSize = isset($param['per-page']) ? intval($param['per-page']) : 50;
        //表别名
        //a:pur_platform_summary,b:pur_purchase_demand,c/f:$platQuery查询结果集,d/g:pur_purchase_order_items,e/h:pur_purchase_order
        //case when () then() mysql条件判断
        //cast mysql 转换数据类型便于计算
        $this->load($param);
        $start   = !empty($this->start_time) ? $this->start_time : '2017-06-01 00:00:00';
        $end     = !empty($this->end_time)   ? $this->end_time   : date('Y-m-d H:i:s',time());
        $platQuery = PlatformSummary::find()->alias('a')->select('a.sku as sku,a.sales,a.group_id as group_id,b.pur_number as pur_number,a.purchase_quantity as purchase_quantity')->leftJoin('pur_purchase_demand as b','{{a}}.demand_number={{b}}.demand_number')->where(['a.is_purchase'=>2,'a.purchase_type'=>3])->andFilterWhere(['between','{{a}}.create_time',$start, $end]);
        $query = new Query();
        //子查询以销售名称维度分组
        $query->from(['c'=>$platQuery])->leftJoin('pur_purchase_order_items as d','{{c}}.sku={{d}}.sku AND {{c}}.pur_number = {{d}}.pur_number') ->leftJoin('pur_purchase_order as e','{{c}}.pur_number={{e.pur_number}}')->andWhere(['NOT', ['d.id' => null]])->andFilterWhere(['NOT IN','{{e}}.purchas_status',[1,4,10]]);
        $query->select('c.sales,c.group_id,sum(c.purchase_quantity * d.price) as total,sum(case when (cast(c.purchase_quantity as signed)-cast(d.rqy as signed))>0 then (cast(c.purchase_quantity as signed)-cast(d.rqy as signed))*d.price else 0 end) as left_arrive,sum(c.purchase_quantity) as pur_num,sum(case when (cast(c.purchase_quantity as signed)-cast(d.rqy as signed))>0 then (cast(c.purchase_quantity as signed)-cast(d.rqy as signed)) else 0 end) as left_num');
        $query->groupBy('{{c}}.sales');
        $query->orderBy('{{c}}.group_id ASC,{{c}}.sales DESC');
        if(!empty($this->dimension)&&$this->dimension==2){
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => $pageSize,
                ],
            ]);
            return $dataProvider;
        }
        $unionQuery = new Query();
        //子查询以组别维度分组
        $unionQuery->from(['f'=>$platQuery])->leftJoin('pur_purchase_order_items as g','{{f}}.sku={{g}}.sku AND {{f}}.pur_number = {{g}}.pur_number') ->leftJoin('pur_purchase_order as h','{{f}}.pur_number={{h.pur_number}}')->andWhere(['NOT', ['g.id' => null]])->andFilterWhere(['NOT IN','{{h}}.purchas_status',[1,4,10]]);
        $unionQuery->select(' "{{总计}}" as sales,f.group_id,sum(f.purchase_quantity * g.price) as total,sum(case when (cast(f.purchase_quantity as signed)-cast(g.rqy as signed))>0 then (cast(f.purchase_quantity as signed)-cast(g.rqy as signed))*g.price else 0 end) as left_arrive,sum(f.purchase_quantity) as pur_num,sum(case when (cast(f.purchase_quantity as signed)-cast(g.rqy as signed))>0 then (cast(f.purchase_quantity as signed)-cast(g.rqy as signed)) else 0 end) as left_num');
        $unionQuery->groupBy('{{f}}.group_id');
        $unionQuery->orderBy('{{f}}.group_id ASC');
        if(!empty($this->group_id)){
            $query->andFilterWhere(['in','{{c}}.group_id',$this->group_id]);
            $unionQuery->andFilterWhere(['in','{{f}}.group_id',$this->group_id]);
        }
        if(!empty($this->dimension)&&$this->dimension==3){
            $dataProvider = new ActiveDataProvider([
                'query' => $unionQuery,
                'pagination' => [
                    'pageSize' => $pageSize,
                ],
            ]);
            return $dataProvider;
        }
        //联合两个子查询
        $query->union($unionQuery);
        $queryResult = new Query();
        //新建子查询对联合查询结果进行排序
        $queryResult->from(['x'=>$query]);
        $queryResult->orderBy('x.group_id ASC,x.total ASC');
        $dataProvider = new ActiveDataProvider([
            'query' => $queryResult,
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);
        return $dataProvider;
    }
    /**
     * 国内添加产品
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search7($params)
    {
        //$fields                     = ['*','sum(purchase_quantity) as total_purchase_quantity'];
        $fields                     = ['*'];
        $query = PlatformSummary::find()->select($fields);
        $Dy    = DynamicTable::getId();
        // add conditions that should always apply here
        //同意、未采购、国内
        $query->where(['in','pur_platform_summary.level_audit_status',['1']]);
        $query->andwhere(['in','pur_platform_summary.is_purchase',['1']]);
        $query->andwhere(['in','pur_platform_summary.purchase_type',['1']]);
        $query->andwhere(['not in','pur_platform_summary.id',$Dy]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        $this->load($params);
        //$query->groupBy(['sku','purchase_warehouse']);
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        if($params) {
            if ($this->getAttribute('supplierQuotes.suppliercode')) {
                $query->joinWith('historyB');
            }
            //$query->groupBy('history.sku');
            // grid filtering conditions
            $query->andFilterWhere([
                'pur_platform_summary.sku'                => $this->sku,
                'pur_platform_summary.purchase_warehouse' => $this->purchase_warehouse,
                'pur_platform_summary.platform_number'    => $this->platform_number,
                'pur_platform_summary.demand_number'      => $this->demand_number,

            ]);
            $query->andFilterWhere(['like','pur_platform_summary.product_name',$this->product_name]);
            $query->andFilterWhere(['like','pur_purchase_history.supplier_name',$this->getAttribute('supplierQuotes.suppliercode')]);
        } else{
            $query->andwhere(['in','pur_platform_summary.is_purchase',['1']]);
        }
        return $dataProvider;
    }
    /**
     * 国内仓销售采购汇总
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search8($params)
    {
        //$fields                     = ['*','sum(purchase_quantity) as total_purchase_quantity'];
        $fields                     = ['*'];
        $query = PlatformSummary::find()->select($fields);

        // add conditions that should always apply here

        $query->andwhere(['in','purchase_type',['1']]);
        $query->orderBy('id desc');
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 100,
            ],
        ]);

        $this->load($params);
        //$query->groupBy(['sku','purchase_warehouse']);
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        if($params) {
            $query->andFilterWhere([

                'demand_number'    => trim($this->demand_number),
                'product_category' => $this->product_category,
                'platform_number' => $this->platform_number,
                'purchase_warehouse' => $this->purchase_warehouse,
                'level_audit_status' => $this->level_audit_status,
                'is_purchase'      => !empty($this->is_purchase)?$this->is_purchase:[1,2],
                //'level_audit_status'      => !empty($this->level_audit_status)?$this->is_purchase:3,
            ]);
            if($this->is_purchase)
            {

            } else{
                $query->andWhere(['not in','level_audit_status',['3']]);
            }

            $query->andFilterWhere(['like','sku',trim($this->sku)]);
            $query->andFilterWhere(['between', 'create_time', $this->start_time,$this->end_time]);

        } else {
            $query->andWhere(['not in','level_audit_status',['3']]);
            $query->andwhere(['in','is_purchase',['1']]);
        }
//        Vhelper::dump($query->createCommand()->getRawSql());
        return $dataProvider;
    }

    //销售需求列表统计搜索
    public function search9($param)
    {
    	$pageSize = isset($param['per-page']) ? intval($param['per-page']) : 20;
    	//表别名
    	//a:pur_platform_summary,b:pur_purchase_demand,c/f:$platQuery查询结果集,d/g:pur_purchase_order_items,e/h:pur_purchase_order
    	//case when () then() mysql条件判断
    	//cast mysql 转换数据类型便于计算
    	$this->load($param);
    	$start   = !empty($this->start_time) ? $this->start_time : date('Y-m-d',strtotime('-1 month'));
    	$end     = !empty($this->end_time)   ? $this->end_time   : date('Y-m-d H:i:s',time());
    	$platQuery = PlatformSummary::find()->alias('a')->select('a.demand_number as demand_number,a.sku,a.buyer as buyer,b.pur_number as pur_number,a.level_audit_status as status,d.date_eta,d.total_price,d.purchas_status,f.instock_date,a.line_buyer')
    	->leftJoin('pur_purchase_demand as b','{{a}}.demand_number={{b}}.demand_number')
    	->leftJoin('pur_purchase_order as d','{{b}}.pur_number={{d}}.pur_number')
    	->leftJoin('pur_warehouse_results as f','{{f}}.pur_number={{b}}.pur_number and {{a}}.sku = {{f}}.sku')
    	->where(['a.purchase_type'=>3])->andFilterWhere(['between','{{a}}.agree_time',$start, $end])->asArray()->all();
    	$pur=array_column($platQuery,'pur_number');
    	$pur=array_filter($pur);
//         $receive=PurchaseReceive::find()->select('pur_number,sku,id')->andFilterWhere(['in','pur_number',$pur])->asArray()->all();
//         $qc=PurchaseQc::find()->select('pur_number,sku,id')->andFilterWhere(['in','pur_number',$pur])->asArray()->all();
    	$qc=PurchaseWarehouseAbnormal::find()->select('UCASE(TRIM(purchase_order_no)) as purchase_order_no,sku,id')->andFilterWhere(['in','UCASE(TRIM(purchase_order_no))',$pur])->asArray()->all();
//   	$receive=PurchaseReceive::find()->select('pur_number,sku,id')->andFilterWhere(['in','pur_number',$pur])->asArray()->all();


    	$total_pur=PurchaseOrderItems::find()->select('pur_number,sum(ctq*price) as total')->andFilterWhere(['in','pur_number',$pur])->groupBy('pur_number')->asArray()->all();
        $total=array_column($total_pur,'total','pur_number');

//        $rece=array_column($receive,'id','pur_number');
        $qc=array_column($qc,'id','purchase_order_no');
            $data=array();
    	foreach($platQuery as$key=> $val){
    		$data[$val['line_buyer']]['demand']=isset($data[$val['line_buyer']]['demand'])?$data[$val['line_buyer']]['demand']:0;
    		$data[$val['line_buyer']]['final']=isset($data[$val['line_buyer']]['final'])?$data[$val['line_buyer']]['final']:'';
    		$data[$val['line_buyer']]['turned']=isset($data[$val['line_buyer']]['turned'])?$data[$val['line_buyer']]['turned']:'';
    		$data[$val['line_buyer']]['wait']=isset($data[$val['line_buyer']]['wait'])?$data[$val['line_buyer']]['wait']:'';
    		$data[$val['line_buyer']]['order']=isset($data[$val['line_buyer']]['order'])?$data[$val['line_buyer']]['order']:'';
    		$data[$val['line_buyer']]['total_price']=isset($data[$val['line_buyer']]['total_price'])?$data[$val['line_buyer']]['total_price']:'';
    		$data[$val['line_buyer']]['demand'] +=1;

    		if(!empty($val['pur_number'])&& !in_array($val['purchas_status'], array(1,4,10))){
    			$data[$val['line_buyer']]['final']+=1;
    			$data[$val['line_buyer']]['order'][$val['pur_number']]=$val['demand_number'];
    			$data[$val['line_buyer']]['total_price'][$val['pur_number']]=isset($total[$val['pur_number']])?$total[$val['pur_number']]:'';
//    			$data[$val['line_buyer']]['total_price']+=isset($total[$val['pur_number']])?$total[$val['pur_number']]:0;
    		}else{
    			$data[$val['line_buyer']]['turned']+=1;
    		}
    		if(empty($val['pur_number'])||$val['purchas_status']==1){
    			$data[$val['line_buyer']]['wait']+=1;
    		}
    		$time=strtotime($val['instock_date'])-strtotime($val['date_eta']);
    		if (!empty($val['instock_date'])&&$time<=3600*24*3 && $time>=-3600*24*3){
    			$data[$val['line_buyer']]['reached'][$val['pur_number']]=$val['demand_number'];
    		}
    		if(isset($qc[$val['pur_number']])){
    			$data[$val['line_buyer']]['normal'][$val['pur_number']]=$val['demand_number'];
    		}
    	}
    	return $data;
//     	Vhelper::dump($platQuery);


    }

    public function search10($params=[]){
        $query = self::find();
        $query->leftJoin(ProductProvider::tableName().' as p_su ','p_su.sku=pur_platform_summary.sku AND p_su.is_supplier=1')
              ->leftJoin(Supplier::tableName().' as su','su.supplier_code=p_su.supplier_code');
        $query->select('pur_platform_summary.*,su.supplier_code as default_supplier_code,su.supplier_name as default_supplier_name');

        if($params) {
            $this->load($params);

            if ($this->getAttribute('order.purchas_status'))
            {
                $query->innerJoinWith('purOrder AS order');
            }
            if ($this->getAttribute('order.purchas_status') === '1') { //未生成采购单
                $query->andFilterWhere(['not in','order.purchas_status',['4','10','3','5','6','7','8','9','99']]);
            } else if ($this->getAttribute('order.purchas_status') === '2') {
                $query->andFilterWhere(['in','order.purchas_status',['3','5','6','7','8','9','99']]);
            }

            $query->andFilterWhere([
                'pur_platform_summary.demand_number'    => trim($this->demand_number),
                'pur_platform_summary.product_category' => $this->product_category,
                'pur_platform_summary.platform_number' => $this->platform_number,
                'pur_platform_summary.purchase_warehouse' => $this->purchase_warehouse,
                'pur_platform_summary.level_audit_status' => 6,
                'pur_platform_summary.purchase_type' => 2,
                'pur_platform_summary.is_purchase'      => !empty($this->is_purchase)?$this->is_purchase:[1,2],
            ]);

            if($this->supplier_code){
                $query->andFilterWhere(['p_su.supplier_code'=>$this->supplier_code]);
            }

            $query->andFilterWhere(['like','pur_platform_summary.sku',trim($this->sku)]);
            $query->andFilterWhere(['between', 'pur_platform_summary.create_time', $this->start_time,$this->end_time]);
            $query->orderBy('pur_platform_summary.agree_time desc');
        } else {
            $query->andFilterWhere(['level_audit_status'=>6,'purchase_type'=>2])->orderBy('agree_time desc');
        }

        $datas = $query->all();
        $result = [];
        foreach ($datas as $value){
            if(!empty($value->default_supplier_code)){
                $result[$value->default_supplier_code][$value->purchase_warehouse][$value->transport_style][]=$value;
            }
        }

        return $result;
    }

    //金额拦截数据
    public function searchAmountIntercept($params=[]){
        $query = self::find();
        $query->leftJoin(ProductProvider::tableName().' as p_su ','p_su.sku=pur_platform_summary.sku AND p_su.is_supplier=1')
              ->leftJoin(Supplier::tableName().' as su','su.supplier_code=p_su.supplier_code');
        $query->select('pur_platform_summary.*,su.supplier_code as default_supplier_code,su.supplier_name as default_supplier_name');

        if($params) {
            $this->load($params);

            if ($this->getAttribute('order.purchas_status'))
            {
                $query->innerJoinWith('purOrder AS order');
            }
            if ($this->getAttribute('order.purchas_status') === '1') { //未生成采购单
                $query->andFilterWhere(['not in','order.purchas_status',['4','10','3','5','6','7','8','9','99']]);
            } else if ($this->getAttribute('order.purchas_status') === '2') {
                $query->andFilterWhere(['in','order.purchas_status',['3','5','6','7','8','9','99']]);
            }

            $query->andFilterWhere([
                'pur_platform_summary.demand_number'    => trim($this->demand_number),
                'pur_platform_summary.product_category' => $this->product_category,
                'pur_platform_summary.platform_number' => $this->platform_number,
                'pur_platform_summary.purchase_warehouse' => $this->purchase_warehouse,
                'pur_platform_summary.level_audit_status' => 6,
                'pur_platform_summary.purchase_type' => 2,
                'pur_platform_summary.is_purchase'      => !empty($this->is_purchase)?$this->is_purchase:[1,2],
            ]);
            $query->andWhere("audit_note like '%金额%'");

            if($this->supplier_code){
                $query->andFilterWhere(['p_su.supplier_code'=>$this->supplier_code]);
            }

            $query->andFilterWhere(['like','pur_platform_summary.sku',trim($this->sku)]);
            $query->andFilterWhere(['between', 'pur_platform_summary.create_time', $this->start_time,$this->end_time]);
            $query->orderBy('pur_platform_summary.agree_time desc');
        } else {
            $query->andFilterWhere(['level_audit_status'=>6,'purchase_type'=>2])
                ->andWhere("audit_note like '%金额%'")
                ->orderBy('agree_time desc');
        }

        $datas = $query->all();
        $result = [];
        foreach ($datas as $value){
            if(!empty($value->default_supplier_code)){
                $result[$value->default_supplier_code][$value->purchase_warehouse][$value->transport_style][]=$value;
            }else{
                $result['默认供应商为空'][$value->purchase_warehouse][]=$value;
            }
        }
        return $result;
    }

    //7天3小时拦截数据
    public function search7days3hours($params=[]){
        $query = self::find();
        $query->leftJoin(ProductProvider::tableName().' as p_su ','p_su.sku=pur_platform_summary.sku AND p_su.is_supplier=1')
            ->leftJoin(Supplier::tableName().' as su','su.supplier_code=p_su.supplier_code');
        $query->select('pur_platform_summary.*,su.supplier_code as default_supplier_code,su.supplier_name as default_supplier_name');

        if($params) {
            $this->load($params);

            if ($this->getAttribute('order.purchas_status'))
            {
                $query->innerJoinWith('purOrder AS order');
            }
            if ($this->getAttribute('order.purchas_status') === '1') { //未生成采购单
                $query->andFilterWhere(['not in','order.purchas_status',['4','10','3','5','6','7','8','9','99']]);
            } else if ($this->getAttribute('order.purchas_status') === '2') {
                $query->andFilterWhere(['in','order.purchas_status',['3','5','6','7','8','9','99']]);
            }

            $query->andFilterWhere([
                'pur_platform_summary.demand_number'    => trim($this->demand_number),
                'pur_platform_summary.product_category' => $this->product_category,
                'pur_platform_summary.platform_number' => $this->platform_number,
                'pur_platform_summary.purchase_warehouse' => $this->purchase_warehouse,
                'pur_platform_summary.level_audit_status' => 6,
                'pur_platform_summary.purchase_type' => 2,
                'pur_platform_summary.is_purchase'      => !empty($this->is_purchase)?$this->is_purchase:[1,2],
            ]);
            $query->andWhere("audit_note like '%小时少于%'");

            if($this->supplier_code){
                $query->andFilterWhere(['p_su.supplier_code'=>$this->supplier_code]);
            }

            $query->andFilterWhere(['like','pur_platform_summary.sku',trim($this->sku)]);
            $query->andFilterWhere(['between', 'pur_platform_summary.create_time', $this->start_time,$this->end_time]);
            $query->orderBy('pur_platform_summary.agree_time desc');
        } else {
            $query->andFilterWhere(['level_audit_status'=>6,'purchase_type'=>2])
                ->andWhere("audit_note like '%小时少于%'")
                ->orderBy('agree_time desc');
        }

        $datas = $query->all();
        $result = [];
        $limitDay = date('Y-m-d H:i:s',time()-7*24*60*60);
        $limitTime = date('Y-m-d H:i:s',time()-3*60*60);
        foreach ($datas as $value){
            if(!empty($value->default_supplier_code)){
                //根据供应商获取最后创建时间
                $create_time = self::find()->select('create_time')
                    ->where(['supplier_code'=>$value->default_supplier_code])
                    ->andFilterWhere(['level_audit_status'=>6,'purchase_type'=>2])
                    ->orderBy('create_time asc')
                    ->scalar();
                $left_time = 0;
                if($create_time){
                    //三小时之内则时间为0
                    if((time()-strtotime($create_time)>=0 && time()-strtotime($create_time)<=10800) || (time()-strtotime($create_time)>=604800)){
                        $left_time = 0;
                    }else{
                        //其余时间统计剩余时间
                        $time_7 = strtotime($create_time)+604800;
                        $now_left_time = $time_7-time();
                        if($now_left_time <= 604800){
                            $left_time = $now_left_time;
                        }else{
                            $left_time = 0;
                        }
                    }
                }else{
                    $left_time = 0;
                }
                $value['left_time'] = $left_time;
                $result[$value->default_supplier_code][]=$value;
            }
        }

        return self::my_array_multisort($result,'left_time');
    }

    //信息不全拦截数据
    public function searchIncompleteInfo($params=[]){
        $query = self::find();
        $query->leftJoin(ProductProvider::tableName().' as p_su ','p_su.sku=pur_platform_summary.sku AND p_su.is_supplier=1')
            ->leftJoin(Supplier::tableName().' as su','su.supplier_code=p_su.supplier_code');
        $query->select('pur_platform_summary.*,su.supplier_code as default_supplier_code,su.supplier_name as default_supplier_name');

        if($params) {
            $this->load($params);

            if ($this->getAttribute('order.purchas_status'))
            {
                $query->innerJoinWith('purOrder AS order');
            }
            if ($this->getAttribute('order.purchas_status') === '1') { //未生成采购单
                $query->andFilterWhere(['not in','order.purchas_status',['4','10','3','5','6','7','8','9','99']]);
            } else if ($this->getAttribute('order.purchas_status') === '2') {
                $query->andFilterWhere(['in','order.purchas_status',['3','5','6','7','8','9','99']]);
            }

            $query->andFilterWhere([
                'pur_platform_summary.demand_number'    => trim($this->demand_number),
                'pur_platform_summary.product_category' => $this->product_category,
                'pur_platform_summary.platform_number' => $this->platform_number,
                'pur_platform_summary.purchase_warehouse' => $this->purchase_warehouse,
                'pur_platform_summary.level_audit_status' => 6,
                'pur_platform_summary.purchase_type' => 2,
                'pur_platform_summary.is_purchase'      => !empty($this->is_purchase)?$this->is_purchase:[1,2],
            ]);
            $query->andWhere("audit_note like '%信息不全%'");

            if($this->supplier_code){
                $query->andFilterWhere(['p_su.supplier_code'=>$this->supplier_code]);
            }

            $query->andFilterWhere(['like','pur_platform_summary.sku',trim($this->sku)]);
            $query->andFilterWhere(['between', 'pur_platform_summary.create_time', $this->start_time,$this->end_time]);
            $query->orderBy('pur_platform_summary.agree_time desc');
        } else {
            $query->andFilterWhere(['level_audit_status'=>6,'purchase_type'=>2])
                ->andWhere("audit_note like '%信息不全%'")
                ->orderBy('update_time desc');
        }

        $datas = $query->all();
        $result = [];
        foreach ($datas as $value){
            if(!empty($value->default_supplier_code)){
                $result[$value->default_supplier_code][$value->purchase_warehouse][]=$value;
            }else{
                $result['默认供应商为空'][$value->purchase_warehouse][]=$value;
            }
        }
        return $result;
    }

    //7天3小时拦截数据根据时间排序
    public static function my_array_multisort($array,$sort_name){
        $key_arrays=[];
        foreach($array as $key=>$val){
            $first_key = key($val);
            //获取第一个
            $left_time=$val[0][$sort_name];
            $key_arrays[]=$left_time;
        }
        array_multisort($key_arrays,SORT_ASC,SORT_NUMERIC,$array);

        return $array;
    }

    //获取天数
    /**
     * 将秒数转换成天时分秒的字符串格式
     * @param $second 秒数
     * @return string
     */
    public static function sec2string($second)
    {
        if(!$second){
            return '0';
        }
        $day = floor($second / (3600 * 24));
        $second = $second % (3600 * 24);
        $hour = floor($second / 3600);
        $second = $second % 3600;
        $minute = floor($second / 60);
        $second = $second % 60;
        $day = $day ? $day . '天' : '';
        $hour = $hour ? $hour . '时' : ($day && ($hour || $minute || $second) ? '0时' : '');
        $minute = $minute ? $minute . '分' : ($hour && $second ? '0分' : '');
        $second = $second ? $second . '秒' : '';
        return $day . $hour . $minute . $second;
    }


    /**
     * 获得 FBA 的 SKU 的权均交货时间
     * @return array|null|\yii\db\ActiveRecord
     */
    public function getFbaAvgArrival(){
        return $this->hasOne(FbaAvgDelieryTime::className(),['sku'=>'sku'])->one();
    }


}
