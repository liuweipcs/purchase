<?php

namespace app\models;

use app\models\base\BaseModel;

use app\services\BaseServices;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;


/**
 * WlClientRecieveInvoicesSearch represents the model behind the search form about `app\models\WlClientRecieveInvoices`.
 */
class SupplierGoodsSearch extends Product
{
    public $sourcing_status;
    public $supplier_special_flag;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [

            [['product_status','sourcing_status','product_is_multi','product_name','product_category_id','product_line','sku','quotes.suppliercode','quotes.default_buyer','supplier_special_flag'],'safe']
        ];
    }

    public function attributes()
    {
        // 添加关联字段到可搜索属性集合
        return array_merge(parent::attributes(), ['quotes.suppliercode','sku','quotes.default_buyer']);
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
        $pageSize = isset($params['per-page']) ? intval($params['per-page']) : 20;
        $query = Product::find();
    
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
               'pageSize' => $pageSize,//要多少写多少吧
            ],
        ]);
       // $query->With(['desc' => function($query) { $query->from(['desc' => ProductDescription::tableName()]); }]);
        //供应商商品管理只显示非捆绑的非主SKU
        $query->andFilterWhere(['pur_product.product_type'=>1]);
        $query->andFilterWhere(['in','pur_product.product_is_multi',[0,1]]);
        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        // grid filtering conditions
       $query->andFilterWhere([
            'pur_product.product_status' => $this->product_status,
            'pur_product.product_category_id' => $this->product_category_id,
        ]);
        //产品状态不为审核不通过和停售
        if(empty($this->product_status)){
            $query->andFilterWhere(['not in','pur_product.product_status',['0','7']]);
        }
        if(!empty($this->product_name)){
            $query->joinWith('desc');
            $query->andFilterWhere(['like','pur_product_description.title',trim($this->product_name)]);
        }
        if(!empty($this->sourcing_status)){
            $query->leftJoin(ProductSourceStatus::tableName().' pst','pst.sku=pur_product.sku');
            if($this->sourcing_status==1){
                $query->andWhere(['or',['pst.sourcing_status'=>$this->sourcing_status,'pst.status'=>1],['pst.sourcing_status'=>null]]);
            }else{
                $query->andFilterWhere(['pst.sourcing_status'=>$this->sourcing_status,'pst.status'=>1]);
            }
        }
        //供货商查询
        if($this->getAttribute('quotes.suppliercode') OR ($this->supplier_special_flag !== '' AND $this->supplier_special_flag !== NULL))
        {
            $query->joinWith('defaultSupplier');
            if($this->getAttribute('quotes.suppliercode')){
                $query->andFilterWhere(['pur_product_supplier.supplier_code'=>$this->getAttribute('quotes.suppliercode')]);
            }
            if($this->supplier_special_flag !== ''){
                $query->leftJoin(Supplier::tableName(),'pur_supplier.supplier_code=pur_product_supplier.supplier_code');
                $query->andWhere(['=', 'pur_supplier.supplier_special_flag', $this->supplier_special_flag]);
            }
        }
        //产品线搜索
        if(!empty($this->product_line)){
            $query->andFilterWhere(['in','pur_product.product_linelist_id',BaseServices::getProductLineChild($this->product_line)]);
        }
        //$query->andFilterWhere(['=', 'quotes.suppliercode', $this->getAttribute('quotes.suppliercode')]);
        $query->andFilterWhere(['like', 'pur_product.sku', trim($this->sku)]);
        

        return $dataProvider;
    }

}
