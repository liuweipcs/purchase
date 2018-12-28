<?php

namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;


/**
 * PurchaseOrderSearch represents the model behind the search form about `app\models\PurchaseOrder`.
 */
class SupplierUpdateApplySearch extends SupplierUpdateApply
{
    public $apply_end_time;
    public $apply_start_time;
    public $check_end_time;
    public $check_start_time;
    public $group;
    public $check_status;
    public $tendency;
    public $note;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_time','tendency','check_status','group','is_sample','status','update_time','type','integrat_status','create_user_name','sku','new_supplier_code','old_supplier_code','apply_end_time','apply_start_time','check_end_time','check_start_time'], 'safe'],
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
     * 申请列表查询
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $pageSize = isset($params['per-page']) ? intval($params['per-page']) : 20;
        $query = self::find();
        $query->alias('t');
        $query->select('t.*,product.note');
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,//要多少写多少吧
            ],
        ]);
        $query->andFilterWhere(['t.status'=>1]);
        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }
        $query->orderBy('t.id DESC');
        // grid filtering conditions
        $query->andFilterWhere([
            't.new_supplier_code' => $this->new_supplier_code,
            't.old_supplier_code' =>$this->old_supplier_code,
            't.type'              =>$this->type,
            't.create_user_name'  =>$this->create_user_name
        ]);
        if(!empty($this->check_status)){
            $query->leftJoin('pur_sample_inspect as sample','sample.apply_id=t.id');
            $query->andFilterWhere(['sample.qc_result'=>$this->check_status]);
        }
        if(!empty($this->is_sample)&&$this->is_sample != 'all'){
            $query->andFilterWhere(['t.is_sample'=>$this->is_sample]);
        }
        $query->andFilterWhere(['like', 't.sku', $this->sku])
              ->andFilterWhere(['between', 't.create_time', $this->apply_start_time, $this->apply_end_time]);

        $query->leftJoin('pur_product as product','product.sku=t.sku');

        return $dataProvider;
    }

    /**
     * 通过申请查询
     */

    public function search1($params)
    {
        $pageSize = isset($params['per-page']) ? intval($params['per-page']) : 20;
        $query = self::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,//要多少写多少吧
            ],
        ]);
        $query->orderBy('id DESC');


        //$query->andFilterWhere(['in','create_user_name',['王曼','王范彬','邱心','李新','何怡','龙菁','胡不为','符圮珍','刘小玲','汪秀']]);
        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }
        $userIds = Yii::$app->authManager->getUserIdsByRole('供应链');
        if((empty($this->group)&&$this->group!=='0')|| $this->group == 1){
            $query->andFilterWhere(['in','create_user_id',$userIds]);
        }elseif($this->group==2){
            $query->andFilterWhere(['NOT in','create_user_id',$userIds]);
        }
        if(empty($this->type)&&$this->type != '0'){
            $query->andFilterWhere(['NOT in','type',[4,5]]);
        }
        if(!empty($this->type)){
            $query->andFilterWhere(['type'=>$this->type]);
        }
        // grid filtering conditions
        $query->andFilterWhere([
            'new_supplier_code' => $this->new_supplier_code,
            'old_supplier_code' =>$this->old_supplier_code,
            'create_user_name'  =>$this->create_user_name,
            'status'            =>!empty($this->status)&&$this->status != 'all' ? $this->status : [2,3],
            'integrat_status'   =>!empty($this->integrat_status)&&$this->integrat_status != 'all' ? $this->integrat_status : [1,2,3],
        ]);
        //$query->orderBy('id DESC');
        $query->andFilterWhere(['like', 'sku', trim($this->sku)])
              ->andFilterWhere(['between', 'create_time', $this->apply_start_time, $this->apply_end_time])
              ->andFilterWhere(['between', 'update_time', $this->check_start_time, $this->check_end_time]);
        return $dataProvider;
    }

    //sku降本
    public function search2($params, $noDataProvider = false)
    {
        $pageSize = isset($params['per-page']) ? intval($params['per-page']) : 20;
        $query = self::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,//要多少写多少吧
            ],
        ]);
        $this->load($params);
        $query->alias('t');
        $query->andFilterWhere(['t.status'=>2]);
        $query->andFilterWhere(['not in','t.type',[4,5]]);
        $query->joinWith('oldQuotes as old');
        $query->joinWith('newQuotes as new');
        $query->andWhere('old.supplierprice <> new.supplierprice');
        if(!empty($this->tendency)&&$this->tendency==1){
            $query->andWhere('old.supplierprice > new.supplierprice');
        }
        if(!empty($this->tendency)&&$this->tendency==2){
            $query->andWhere('old.supplierprice < new.supplierprice');
        }
        if(!empty($this->group)&&$this->group==1){
            $userIds = Yii::$app->authManager->getUserIdsByRole('供应链');
            $query->andFilterWhere(['in','t.create_user_id',$userIds]);
        }
        if(!empty($this->group)&&$this->group==2){
            $FBAIds = Yii::$app->authManager->getUserIdsByRole('FBA采购组');
            $FBAMIds = Yii::$app->authManager->getUserIdsByRole('FBA采购经理组');
            $userIds = array_merge($FBAIds,$FBAMIds);
            $query->andFilterWhere(['in','t.create_user_id',$userIds]);
        }
        if(!empty($this->group)&&$this->group==3){
            $GNIds = Yii::$app->authManager->getUserIdsByRole('采购组-国内');
            $GNMIds = Yii::$app->authManager->getUserIdsByRole('采购经理组');
            $userIds = array_merge($GNIds,$GNMIds);
            $query->andFilterWhere(['in','t.create_user_id',$userIds]);
        }
        if(!empty($this->group)&&$this->group==4){
            $userIds = Yii::$app->authManager->getUserIdsByRole('采购组-海外');
            $query->andFilterWhere(['in','t.create_user_id',$userIds]);
        }
        if (!$this->validate()) {
            return $dataProvider;
        }
        // grid filtering conditions
        $query->andFilterWhere([
            't.new_supplier_code' => $this->new_supplier_code,
            't.old_supplier_code' =>$this->old_supplier_code,
        ]);
        $query->andFilterWhere(['like', 't.sku', trim($this->sku)])
            ->andFilterWhere(['like', 't.create_user_name', trim($this->create_user_name)])
              ->andFilterWhere(['between', 't.update_time', $this->check_start_time, $this->check_end_time]);

        \Yii::$app->session->set('SupplierApplyCostData', $params);
        if ($noDataProvider)
            return $query;

//        Vhelper::dump($query->createCommand()->getRawSql());
        return $dataProvider;
        return $dataProvider;
    }

    //整合统计
    public function search3($params){
       // $pageSize = isset($params['per-page']) ? intval($params['per-page']) : 20;

        $query = new Query();
        $this->load($params);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,//要多少写多少吧
            ],
        ]);
        $this->check_start_time = $this->check_start_time?$this->check_start_time :date('Y-m-d 00:00:00');
        $this->check_end_time = $this->check_end_time?$this->check_end_time :date('Y-m-d 23:59:59');
        $userids = Yii::$app->authManager->getUserIdsByRole('供应链');
        $userQuery = User::find()->select('id,username')->where(['in','id',$userids]);
        $supplierQuery = self::find()
            ->select('create_user_name,create_user_id,old_supplier_code')
            ->andWhere(['status'=>2])
            ->andWhere(['integrat_status'=>2])
            ->andWhere(['in','create_user_id',$userids])
            ->groupBy('create_user_id,old_supplier_code')
            ->andFilterWhere(['between','update_time',$this->check_start_time,$this->check_end_time]);

        $downQuery = self::find()
            ->select('t.create_user_id,t.create_user_name,count(t.id) as down_num')
            ->alias('t')
            ->andWhere(['t.status'=>2])
            ->andWhere(['in','t.type',[2,3,6]])
            ->andWhere(['in','t.create_user_id',$userids])
            ->leftJoin(SupplierQuotes::tableName().' oq','t.old_quotes_id=oq.id')
            ->leftJoin(SupplierQuotes::tableName().' nq','t.new_quotes_id=nq.id')
            ->andWhere('nq.supplierprice<oq.supplierprice')
            ->andFilterWhere(['between','t.update_time',$this->check_start_time,$this->check_end_time])
            ->groupBy('t.create_user_id');

        $settlementQuery = SupplierSettlementLog::find()
            ->select('create_user_id,count(supplier_code) as settlement_num')
            ->andFilterWhere(['between','create_time',$this->check_start_time,$this->check_end_time])
            ->groupBy('create_user_id');
        $query->from(['u'=>$userQuery])->select('u.id,u.username,count(t.old_supplier_code) as integrat_num,d.down_num,s.settlement_num')->groupBy('u.id');
        $query->leftJoin(['t'=>$supplierQuery],'u.id=t.create_user_id');
        $query->leftJoin(['d'=>$downQuery],'u.id=d.create_user_id');
        $query->leftJoin(['s'=>$settlementQuery],'u.id=s.create_user_id');
        return $dataProvider;
    }
}

