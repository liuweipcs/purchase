<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Exception;

/**
 * This is the model class for table "pur_amazon_outofstock_order".
 *
 * @property integer $id
 * @property string $amazon_order_id
 * @property string $demand_number
 * @property string $sku
 * @property integer $purchase_num
 * @property integer $outofstock_num
 * @property string $create_time
 * @property string $update_time
 * @property integer $status
 * @property string $note
 * @property string $is_show
 */
class AmazonOutofstockOrder extends BaseModel
{
    public $buyer;
    public $supplier_code;
    public $addnote;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_amazon_outofstock_order';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['purchase_num', 'outofstock_num', 'status'], 'integer'],
            [['create_time', 'update_time','buyer','pay_time','supplier_code'], 'safe'],
            [['note'], 'string'],
            [['amazon_order_id', 'demand_number'], 'string', 'max' => 100],
            [['sku'], 'string', 'max' => 150],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'amazon_order_id' => '亚马逊订单号',
            'demand_number' => '需求单号',
            'sku' => 'Sku',
            'purchase_num' => '订单数量',
            'outofstock_num' => '缺货数量',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'status' => '处理状态',
            'note' => 'Note',
            'pay_time'=>'支付时间'
        ];
    }

    public function getProduct(){
        return $this->hasOne(Product::className(),['sku'=>'sku']);
    }

    public function getDefaultSupplier(){
        return $this->hasOne(ProductProvider::className(),['sku'=>'sku'])->where(['is_supplier'=>1]);
    }

    public function getDefaultSupplierLine(){
        return $this->hasOne(SupplierProductLine::className(),['supplier_code'=>'supplier_code'])->via('defaultSupplier')->where(['pur_supplier_product_line.status'=>1]);
    }

    public function search($params, $noDataProvider = false)
    {
        $query = self::find();

        // add conditions that should always apply here
        $pageSize = isset($params['per-page']) ? intval($params['per-page']) : 50;

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);
        $query->alias('t');
        //导出excel根据id获取数据
        if(isset($params['AmazonOutofstockOrder']['id']) && count($params['AmazonOutofstockOrder']['id'])>0){
            $query->andWhere(['in','t.id',$params['AmazonOutofstockOrder']['id']]);
        }else{
            if(isset($params['AmazonOutofstockOrder']['status']) && $params['AmazonOutofstockOrder']['status']==1){
                $query->where(['t.status'=>1]);
            }else{
                $query->where(['t.status'=>0]);
            }
        }
        $query->andWhere(['or',['>','t.create_time',date('Y-m-d 00:00:00',time())],['>','t.update_time',date('Y-m-d 00:00:00',time())]]);
        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        $query->leftJoin(Product::tableName(),'pur_product.sku=t.sku');
        $query->andWhere(['pur_product.product_status'=>7]);
        $query->andWhere(['is_show'=>1]);
        $query->andWhere(['>','pay_time','2018-06-30 23:59:59']);
        $query->andFilterWhere(['t.sku'=>trim($this->sku)])
            ->andFilterWhere(['t.amazon_order_id'=>trim($this->amazon_order_id)]);
        if(!empty($this->supplier_code)){
            $query->joinWith('defaultSupplier');
            $query->andFilterWhere(['pur_product_supplier.supplier_code'=>$this->supplier_code]);
        }
        if(!empty($this->buyer)){
            $query->joinWith('defaultSupplierLine');
            $bind = PurchaseCategoryBind::find()->andFilterWhere(['buyer_name'=>$this->buyer])->asArray()->all();
            $productLine = array_column($bind,'category_id');
            $query->andFilterWhere(['in','pur_supplier_product_line.first_product_line',$productLine]);
        }
        if ($noDataProvider)
            return $query;

        return $dataProvider;
    }

    public static function getCreateOrderData($ids){
        try{
        if(!is_array($ids)){
            $ids = explode(',',$ids);
        }
        $datas = self::find()->where(['in','id',$ids])->asArray()->all();
        $skus = array_column($datas,'sku');
        $defaultSupplierCode = ProductProvider::find()->select('supplier_code')->where(['in','sku',$skus])->andWhere(['is_supplier'=>1])->column();
        if(count(array_unique($defaultSupplierCode))>1||empty($defaultSupplierCode)){
            throw new Exception('同一供应商才能生成采购单');
        }
        $supplier_code = $defaultSupplierCode[0];
        if(empty($supplier_code)){
            throw new Exception('默认供应商为空不能创建采购单');
        }
//        $checkSkus = self::find()
//            ->select(['sku','outofstock_num'=>'ifnull(outofstock_num,0)'])
//            ->where(['in','sku',$skus])
//            ->andWhere(['is_show'=>1])
//            ->andWhere(['>','pay_time','2018-06-30 23:59:59'])
//            ->andWhere(['or',['>','create_time',date('Y-m-d 00:00:00')]
//                ,['>','update_time',date('Y-m-d 00:00:00')]])->asArray()->all();
//        if(count($checkSkus)!=count($ids)){
//            throw new Exception('请选择当前sku今天所有的缺货数据');
//        }
        $outofstockSku = self::find()
            ->select(['sku','outofstock_num'=>'ifnull(outofstock_num,0)'])
            ->where(['in','sku',$skus])
            ->andWhere(['>','pay_time','2018-06-30 23:59:59'])
            ->andWhere(['or',['>','create_time',date('Y-m-d 00:00:00')]
                ,['>','update_time',date('Y-m-d 00:00:00')]])->asArray()->all();
        $outofstockSkuNum=[];
        foreach ($outofstockSku as $value){
            $outofstockSkuNum[$value['sku']] = isset($outofstockSkuNum[$value['sku']]) ? $outofstockSkuNum[$value['sku']] + $value['outofstock_num'] : $value['outofstock_num'];
        }

        $skuOtherPlatformNum = StockOwes::find()->select('sku,left_stock')->where(['in','sku',$skus])->andWhere(['warehouse_code'=>'SZ_AA'])->andWhere(['>','statistics_date',date('Y-m-d 00:00:00',time())])
                                ->asArray()->all();
        $platOutstock=[];
        foreach ($skuOtherPlatformNum as $platnum){
            $platOutstock[$platnum['sku']] = isset($platOutstock[$platnum['sku']]) ? $platOutstock[$platnum['sku']] + $platnum['left_stock'] : $platnum['left_stock'];
        }
        $stockData = Stock::find()->select(['sku','on_way_stock'=>'ifnull(on_way_stock,0)','available_stock'=>'ifnull(available_stock,0)'])
            ->where(['in','sku',$skus])->andWhere(['in','warehouse_code',['FBA_SZ_AA','SZ_AA']])->asArray()->all();
        $skuStockNum = [];
        foreach ($stockData as $v){
            $skuStockNum[$v['sku']] = isset($skuStockNum[$v['sku']]) ? $skuStockNum[$v['sku']] + $v['on_way_stock'] + $v['available_stock'] : $v['on_way_stock'] + $v['available_stock'];
        }
        $purchaseNum = [];
        foreach ($outofstockSkuNum  as $sku=>$outofstock){
            $suggestNum = isset($platOutstock[$sku]) ? $platOutstock[$sku] :$outofstock;
            if(isset($skuStockNum[$sku])&&$suggestNum>$skuStockNum[$sku]){
                $purchaseNum[$sku]['suggest_num'] = $suggestNum-$skuStockNum[$sku];
                $purchaseNum[$sku]['alloutofstock'] = isset($platOutstock[$sku]) ? $platOutstock[$sku] :0;
                $purchaseNum[$sku]['outofstock'] = $outofstock;
                $purchaseNum[$sku]['have_num'] = $skuStockNum[$sku];
            }
            if(!isset($skuStockNum[$sku])){
                $purchaseNum[$sku]['suggest_num'] = $suggestNum;
                $purchaseNum[$sku]['alloutofstock'] = isset($platOutstock[$sku]) ? $platOutstock[$sku] :0;
                $purchaseNum[$sku]['outofstock'] = $outofstock;
                $purchaseNum[$sku]['have_num'] = 0;
            }
        }
        if(empty($purchaseNum)){
            self::updateAll(['is_show'=>0],['in','id',$ids]);
            throw new Exception('当前选中sku已有在途,无推荐购买数量,sku今日已隐藏');
        }
            $response = ['status'=>'success','purchaseItems'=>$purchaseNum,'supplier_code'=>$supplier_code];
        }catch (Exception $e){
            $response = ['status'=>'error','message'=>$e->getMessage()];
        }
         return $response;
    }
}
