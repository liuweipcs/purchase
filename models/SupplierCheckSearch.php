<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * WlClientRecieveInvoicesSearch represents the model behind the search form about `app\models\WlClientRecieveInvoices`.
 */
class SupplierCheckSearch extends SupplierCheck
{
    public $start_time;
    public $end_time;
    public $confirm_start_time;
    public $confirm_end_time;
    public $expect_start_time; //期望时间
    public $expect_end_time;
    public $report_start_time; //报告时间
    public $report_end_time;
    public $check_user;
    public $confirm_time;
    public $expect_time;
    public $report_time;
    public $create_time;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[  'confirm_time',
                'create_time',
                'expect_time',
                'report_time',
                'supplier_code',
                'is_urgent',
                'times',
                'sku',
                'group',
                'judgment_results','check_user','check_code','apply_user_name','start_time','pur_number','end_time','check_type','supplier_name','status','apply_start_time','apply_end_time','confirm_start_time','confirm_end_time', 'report_start_time', 'report_end_time','expect_start_time', 'expect_end_time'],'safe']
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
    public function search($params,$nodata=false)
    {
        //var_dump($params);exit();
        $pageSize = isset($params['per-page']) ? intval($params['per-page']) : 20;
        $query = SupplierCheck::find();
        $query->alias('t');
        if(isset($params['sort'])){
        }else{
            $query->orderBy('t.id DESC');
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,//要多少写多少吧
            ],
        ]);
        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }

        if(empty($this->status)){
            $query->andFilterWhere(['<>','t.status',4]);
        }
        

        $query->andFilterWhere([
            't.supplier_code'=>$this->supplier_code,
            't.check_type'=>$this->check_type,
            't.status'       =>$this->status==0 ? null :$this->status,
            't.supplier_name'       =>$this->supplier_name,
            't.check_code'       =>$this->check_code,
            't.times'=>$this->times,
            't.is_urgent'=>$this->is_urgent,
            't.apply_user_name'       =>$this->apply_user_name,
            't.judgment_results'       =>$this->judgment_results,
            't.group'       =>$this->group,
        ]);
        if($this->check_user){
            $query->joinWith('checkUser');
            $query->andFilterWhere(['pur_supplier_check_user.check_user_name'=>$this->check_user]);
        }
        if($this->sku){
            $query->joinWith('checkPur');
            $query->andFilterWhere(['pur_supplier_check_sku.sku'=>$this->sku]);
        }
        $query->andFilterWhere(['like','t.pur_number',$this->pur_number]);
        if(!empty($params['SupplierCheckSearch']['create_time'])){
            $createTimeArray = explode(' ~ ',$params['SupplierCheckSearch']['create_time']);
            $this->start_time = date('Y-m-d 00:00:00',strtotime(trim($createTimeArray[0])));
            $this->end_time = date('Y-m-d 23:59:59',strtotime(trim($createTimeArray[1])));
            $query->andFilterWhere(['between','t.create_time',$this->start_time,$this->end_time]);
        }
        if(!empty($params['SupplierCheckSearch']['confirm_time'])){
            $confirmTimeArray = explode(' ~ ',$params['SupplierCheckSearch']['confirm_time']);
            $this->confirm_start_time = date('Y-m-d 00:00:00',strtotime(trim($confirmTimeArray[0])));
            $this->confirm_end_time = date('Y-m-d 23:59:59',strtotime(trim($confirmTimeArray[1])));
            $query->andFilterWhere(['between','t.confirm_time',$this->confirm_start_time,$this->confirm_end_time]);
        }
        //期望时间
        if (!empty($params['SupplierCheckSearch']['expect_time'])) {
            $expectTimeArray = explode(' ~ ',$params['SupplierCheckSearch']['expect_time']);
            $expect_start_time = date('Y-m-d 00:00:00', strtotime(trim($expectTimeArray[0])));
            $expect_end_time = date('Y-m-d 23:59:59', strtotime(trim($expectTimeArray[1])));
            $query->andFilterWhere(['between','t.expect_time',$expect_start_time,$expect_end_time]);
        }
        //报告时间
        if (!empty($params['SupplierCheckSearch']['report_time'])) {
            $reportTimeArray = explode(' ~ ',$params['SupplierCheckSearch']['report_time']);
            $report_start_time = date('Y-m-d 00:00:00', strtotime(trim($reportTimeArray[0])));
            $report_end_time = date('Y-m-d 23:59:59', strtotime(trim($reportTimeArray[1])));
            $query->andFilterWhere(['between','t.report_time',$report_start_time,$report_end_time]);
        }
        Yii::$app->session->set('supplier_check_search_params',$params);
        if($nodata){
            return $query;
        }
        // vd($query->createCommand()->getRawSql());
        return $dataProvider;
    }
}
