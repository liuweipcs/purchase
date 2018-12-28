<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for model "ProductSupplierChange".
 */
class ProductSupplierChangeSearch extends ProductSupplierChange
{
    public $supplier_code;
    public $other_apply_reason;
    public $apply_time_start;
    public $apply_time_end;
    public $erp_oper_time_start;
    public $erp_oper_time_end;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sku','supplier_code','status','create_id','apply_user','apply_time','apply_time_start','apply_time_end','erp_oper_time','erp_oper_time_start','erp_oper_time_end'], 'safe'],
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

    public static function auditStatusList(){
        return [0 => '待审核',1 => '审核通过',2 => '审核不通过'];
    }


    /**
     * 列表查询
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = ProductSupplierChange::find();
        $query->alias('t');
        $query->leftJoin(Product::tableName().' as product','product.sku=t.sku');

        $query->orderBy('t.id desc');
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => isset($params['per-page']) ? intval($params['per-page']) : 20,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }
        $query->andFilterWhere(['=','t.apply_user',$this->apply_user]);
        $query->andFilterWhere(['=','product.create_id',$this->create_id]);
        $query->andFilterWhere(['=','t.sku',$this->sku]);
        $query->andFilterWhere(['=','t.old_supplier_code',$this->supplier_code]);

        if($this->apply_time){
            $apply_time_rang        = explode(' - ',$this->apply_time);
            $this->apply_time_start = $apply_time_rang[0].' 00:00:00';
            $this->apply_time_end   = $apply_time_rang[1].' 23:59:59';

            $query->andWhere(['between','t.apply_time',$this->apply_time_start,$this->apply_time_end]);
        }
        if($this->erp_oper_time){
            $erp_time_rang              = explode(' - ',$this->erp_oper_time);
            $this->erp_oper_time_start  = $erp_time_rang[0].' 00:00:00';
            $this->erp_oper_time_end    = $erp_time_rang[1].' 23:59:59';
            
            $query->andWhere(['between','t.erp_oper_time',$this->erp_oper_time_start,$this->erp_oper_time_end]);
        }
        if(in_array($this->status,['70_P','70_D','70_M','70_S','70_PB'])){//组合状态查询
            $status_flag = explode('_',$this->status);
            $query->andFilterWhere(['=','t.status',$status_flag[0]])
                  ->andFilterWhere(['=','t.status_flag',$status_flag[1]]);
        }else{
            $query->andFilterWhere(['=','t.status',$this->status]);
        }

        \Yii::$app->session->set('ProductSupplierChangeSearch', $params);
        return $dataProvider;
    }

    /**
     * 添加 SKU屏蔽记录
     * @param $addInfo
     * @return array
     */
    public static function addChangeInfo($addInfo){
        $result = array('status' => 'error','message' => '');

        if(!isset($addInfo['sku']) OR empty($addInfo['sku'])){
            $result['status']   = 'none';
            $result['message']  = 'sku 信息不存在';
            return $result;
        }

        $sku = trim($addInfo['sku']);

        $old_model  = self::find()
            ->where(['sku' => $sku])
            ->andWhere(['not in','status',[60,70]])
            ->one();
        if($old_model){
            $result['status']  = 'exists';
            $result['message'] = '此SKU存在未完结状态的申请';
            return $result;
        }

        $productModel = Product::findOne(['sku' => $sku]);
        if(empty($productModel)){
            $result['status']   = 'none';
            $result['message']  = 'sku 信息不存在';
        }else{
            $model                      = new self();
            $model->sku                 = $sku;
            $model->apply_user          = Yii::$app->user->identity->username;
            $model->apply_time          = date('Y-m-d H:i:s');
            $model->apply_remark        = isset($addInfo['apply_remark'])?$addInfo['apply_remark']:'';
            $model->old_supplier_code   = isset($productModel->defaultSupplier)?$productModel->defaultSupplier->supplier_code:'';// 原供应商编码
            $model->old_supplier_name   = isset($productModel->defaultSupplierDetail)?$productModel->defaultSupplierDetail->supplier_name:'';// 原供应商名称
            $model->old_price           = isset($productModel->supplierQuote)?$productModel->supplierQuote->supplierprice:'';
            $model->status              = 1;

            $model->save(false);
            $result['status'] = 'success';
        }

        return $result;
    }


    /**
     * 替换日志信息里面 字段名称为中文名称
     * @param $content
     * @return mixed
     */
    public function changeShowName($content){
        $attributeLabels = $this->attributeLabels();

        foreach($attributeLabels as $label => $name){
            $content = str_replace($label,$name,$content);
        }

        $content = str_replace(':','：',$content);

        return $content;
    }


    /**
     * 针对 ERP通过结果的数据，操作时间为空的自动填充 日志里的时间
     */
    public static function autoCompleteInfo(){
        $wait_list = self::find()
            ->where("(erp_oper_user != '' and erp_oper_user IS NOT NULL) OR (erp_result != '' and erp_result IS NOT NULL)")
            ->andWhere("erp_oper_time = '' OR erp_oper_time IS NULL")
            ->all();

        if($wait_list){
            foreach($wait_list as $v_list){
                $operate_time = ChangeLog::find()
                    ->select('operate_time')
                    ->where(['oper_id' => $v_list->id,'oper_type' => 'ProductSupplierChange'])
                    ->andWhere("content LIKE '%接收 ERP 开发审核结果成功%'")
                    ->orderBy(' id desc')
                    ->scalar();

                if($operate_time){
                    $v_list->erp_oper_time = $operate_time;
                    $v_list->save();
                }
            }
        }
    }

}
