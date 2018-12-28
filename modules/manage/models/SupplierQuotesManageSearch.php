<?php
namespace app\modules\manage\models;

use app\models\Product;
use app\models\ProductDescription;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Created by PhpStorm.
 * User: Wr
 * Date: 2018/6/13
 * Time: 15:56
 */
class SupplierQuotesManageSearch extends SupplierQuotesManage {
    public $linelist_cn_name;
    public $product_line;
    public $uploadimgs;
    public $product_name;
    public function rules()
    {
        return[
        [['create_supplier_code','sku','status','product_name','product_line'],'safe']
        ];
    }

    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }
    public function search($params,$dataCondition=false){
        $query = self::find();
        $query->select('t.sku,pl.linelist_cn_name,t.status,t.check_time,t.id,t.create_supplier_code,t.type,p.uploadimgs,t.create_time,t.reason');
        // add conditions that should always apply here
        $query->alias('t');
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        $query->leftJoin(Product::tableName().' p','p.sku=t.sku');
        $query->leftJoin(ProductLine::tableName().' pl','pl.product_line_id=p.product_linelist_id');
        // $query->where(['sku'=>'JM00042']);
        $this->load($params);
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        if($this->product_name){
            $query->leftJoin(ProductDescription::tableName().' pd','pd.sku=t.sku');
            $query->andWhere(['language_code'=>'Chinese']);
            $query->andFilterWhere(['like','pd.title',$this->product_name]);
        }
        if($this->product_line){
            $product_line_list = ProductLine::getProductLinefamily($this->product_line);
            $query->andWhere(['in','p.product_linelist_id',$product_line_list]);
        }
        $query->andFilterWhere(['t.create_supplier_code'=>$this->create_supplier_code]);
        $query->andFilterWhere(['like','t.sku',trim($this->sku)]);
        $query->andFilterWhere(['t.status'=>$this->status]);
        $query->orderBy('t.id DESC');
        \Yii::$app->getSession()->set('quotes-manage-search',$params);
        if($dataCondition){
            return $query;
        }
        return $dataProvider;
    }
}