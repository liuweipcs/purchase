<?php

namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use linslin\yii2\curl\Curl;
use Yii;
use yii\db\Exception;

/**
 * This is the model class for table "{{%product}}".
 *
 * @property string $id
 * @property string $sku
 * @property string $product_category_id
 * @property integer $product_status
 * @property string $uploadimgs
 * @property string $product_cn_link
 * @property string $product_en_link
 * @property string $create_id
 * @property string $create_time
 * @property string $product_cost
 * @property string $product_is_multi
 */
class Product extends BaseModel
{

    public $product_line;
    public $product_name;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sku'], 'unique'],
            [['product_category_id','sku'], 'required'],
            [['product_category_id', 'product_status'], 'integer'],
            [['create_time','supply_status','note','supplier_name','supplier_code','supplier_link','purchase_cost'], 'safe'],
            [['product_cost'], 'number'],
            [['sku'], 'string', 'max' => 32],
            [['uploadimgs'], 'string', 'max' => 2000],
            [['product_cn_link', 'product_en_link'], 'string', 'max' => 500],
            [['create_id'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'sku' => Yii::t('app', 'SKU'),
            'product_category_id' => Yii::t('app', '产品分类'),
            'product_status' => Yii::t('app', '产品状态'),
            'supply_status' => Yii::t('app', '货源状态'),
            'uploadimgs' => Yii::t('app', '图片'),
            'product_cn_link' => Yii::t('app', '产品中文链接'),
            'product_en_link' => Yii::t('app', '产品英文链接'),
            'create_id' => Yii::t('app', '开发人员'),
            'create_time' => Yii::t('app', '开发时间'),
            'product_cost' => Yii::t('app', '开发成本'),
            'note' => Yii::t('app', '备注'),
            'product_type' => Yii::t('app', '是否捆绑'),
            'supplier_code' => Yii::t('app', '供应商编码'),
            'supplier_name' => Yii::t('app', '供应商名'),
            'supplier_link' => Yii::t('app', '供应商连接'),
            'purchase_cost' => Yii::t('app', '采购成本'),
            'last_price' => Yii::t('app', '产品最后采购价格'),
            'product_linelist_id' => Yii::t('app', '产品线'),
            'product_is_multi' => Yii::t('app', '是否多属性'),
        ];
    }

    /**
     * 关联产品名
     * @return $this
     */
    public  function  getDesc()
    {
        return $this->hasOne(ProductDescription::className(), ['sku' => 'sku'])->where(['language_code'=>'Chinese']);
    }

    /**
     *供应商报价表
     * @return $this
     */
    public function getSuppquotes()
    {
        return $this->hasOne(SupplierQuotes::className(), ['product_sku' => 'sku']);
    }
    /**
     *关联产品类目
     * @return $this
     */
    public function getCat()
    {
        return $this->hasOne(ProductCategory::className(), ['id' =>'product_category_id'])->where(['category_status'=>1]);
    }

    /**
     *库存综合查询表
     * @return $this
     */
    public function getStock()
    {
        return $this->hasOne(Stock::className(), ['sku' => 'sku']);
    }

    /**
     *库存综合查询表
     * @return $this
     */
    public function getSkusales()
    {
        return $this->hasOne(SkuSalesStatistics::className(), ['sku' => 'sku']);
    }

    /**
     * 产品中间表关联
     * @return \yii\db\ActiveQuery
     */
    public function getProductItems()
    {
        return $this->hasMany(ProductProvider::className(), ['sku' => 'sku']);
    }

    /**
     *
     * @return $this
     */
    public function getItems()
    {
        return $this->hasMany(SupplierQuotes::className(), ['suppliercode' => 'supplier_code'])
            ->via('productItems');
    }
    /**
     * @desc 通过中间表获取产品对应的供应商信息
     * @author Jimmy
     * @date 2017-04-06 14:25:11
     */
    public function getSupplier(){
        return $this->hasMany(Supplier::className(), ['supplier_code' => 'supplier_code'])
            ->viaTable('pur_product_supplier', ['sku' => 'sku']);
    }
    /**
     * @desc 通过中间表获取产品对应的供应商信息
     * @author Jimmy
     * @date 2017-04-06 14:25:11
     */
    public function getOneSupplier(){
        return $this->hasOne(Supplier::className(), ['supplier_code' => 'supplier_code'])
            ->viaTable('pur_product_supplier', ['sku' => 'sku']);
    }
    /**
     * @desc 通过中间表获取默认供应商报价
     */
    public function getSupplierQuote(){
        return $this->hasOne(SupplierQuotes::className(), ['id'=>'quotes_id'])->where(['status'=>1])
                ->via('defaultSupplier');
    }

    /**
     * @desc 获取默认供货商信息
     * @return $this
     */
    public function getDefaultSupplierDetail(){
        return $this->hasOne(Supplier::className(), ['supplier_code' => 'supplier_code'])
            ->via('defaultSupplier');
    }
    /**
     * @desc 获取默认供应商
     * @return $this
     */
    public function getDefaultSupplier(){
        return $this->hasOne(ProductProvider::className(), ['sku' => 'sku'])->where(['is_supplier'=>1]);
    }

    public function getUpdateApply(){
        return $this->hasOne(SupplierUpdateApply::className(),['sku'=>'sku'])->where(['status'=>1])->andFilterWhere(['<>','type',5]);
    }
    /**
     * @param $sku
     * @return false|null|string
     */
    public static function getSkuStatus($sku)
    {
        return self::find()->select('product_status')->where(['sku'=>$sku])->scalar();
    }
    /**
     * @param $sku
     * @return false|null|string
     */
    public static function getSkuCode($sku)
    {
        return self::find()->select('purchase_packaging')->where(['sku'=>$sku])->scalar();
    }

    public function getSourceStatus(){
        return $this->hasOne(ProductSourceStatus::className(),['sku'=>'sku'])->where(['pur_product_source_status.status'=>1]);
    }

    /**
     * 获取供货商商品管理的单价
     */
    public static function getProductPrice($sku)
    {
        $info = self::find()
            ->joinWith('supplierQuote')
            ->where(['pur_product.sku'=>$sku])
            ->asArray()
            ->one();
        if (!empty($info['supplierQuote']['supplierprice'])) {
            return $info['supplierQuote']['supplierprice'];
        } else {
            return 0;
        }
    }

    /**
     * @param $datas 货源状态编辑数据，二维[[sku,source_status]]
     * @return array
     * @throws Exception
     */
    public static function changeSourceStatus($datas){
        $tran = Yii::$app->db->beginTransaction();
        try{
            if(!is_array($datas)||empty($datas)){
                throw new Exception('数据不符合更新要求');
            }
            foreach ($datas as $key=>$data){
                $insertData[$key][]=$data['sku'];
                $insertData[$key][]=$data['source_status'];
                $insertData[$key][]=Yii::$app->user->identity->username;
                $insertData[$key][]=date('Y-m-d H:i:s',time());
            }
            $updateSkus = array_column($datas,'sku');
            ProductSourceStatus::updateAll(['status'=>0,'update_user_name'=>Yii::$app->user->identity->username,'update_time'=>date('Y-m-d H:i:s',time())],
                ['and',['status'=>1],['in','sku',$updateSkus]]);
            Yii::$app->db->createCommand()->batchInsert(ProductSourceStatus::tableName(),['sku','sourcing_status','create_user_name','create_time'],$insertData)->execute();
            $tran ->commit();
            $response = ['status'=>'success','message'=>'货源状态编辑成功'];
        }catch (Exception $e){
            $response = ['status'=>'error','message'=>'货源状态编辑失败'];
            $tran->rollBack();
        }
        return $response;
    }


    /**
     * 货源状态编辑数据，二维[[sku,source_status]]
     * @param $datas
     * @return bool
     */
    public static function changeSourceStatusBySku($datas){
        if(empty($datas) || !is_array($datas)){
            return false;// 数据缺失
        }
        $user_name = isset(Yii::$app->user->identity->username)?Yii::$app->user->identity->username:'admin';// 默认用户

        $tran = Yii::$app->db->beginTransaction();
        try{
            foreach($datas as $key => $data){
                $insertData[$key][] = $data['sku'];
                $insertData[$key][] = $data['source_status'];
                $insertData[$key][] = $user_name;
                $insertData[$key][] = date('Y-m-d H:i:s', time());
            }
            if(!isset($insertData) || empty($insertData)){
                return false;// 数据缺失
            }

            $updateSkus = array_column($datas,'sku');
            ProductSourceStatus::updateAll(['status'=>0,
                                            'update_user_name'=>$user_name,
                                            'update_time'=>date('Y-m-d H:i:s',time())],
                                           ['and',['status'=>1],['in','sku',$updateSkus]]
            );
            Yii::$app->db->createCommand()->batchInsert(ProductSourceStatus::tableName(),
                                                        ['sku','sourcing_status','create_user_name','create_time'],
                                                        $insertData)
                ->execute();
            $tran ->commit();
            return true;
        }catch (Exception $e){
            return false;
        }
    }



    public static function productStockSalesDatas($sku,$warehouse_code,$platform_code){
        if(empty($sku)&&empty($warehouse_code)){
            return ['salesDatas'=>[],'stock'=>[]];
        }
        $salesDatas = SkuSalesStatistics::find()->andFilterWhere(['sku'=>$sku])
            ->andFilterWhere(['warehouse_code'=>$warehouse_code])
            ->andFilterWhere(['platform_code'=>$platform_code])
            ->asArray()->all();
        $url = Yii::$app->params['server_ip'].'/index.php/stock/queryLocalStock';
        $s = json_encode([['sku'=>$sku,'warehouse_code'=>$warehouse_code]]);
        $curl = new Curl();
        $data = $curl->setPostParams(['query_stock'=>$s])
                ->setOption(CURLOPT_CONNECTTIMEOUT,10)
                ->setOption(CURLOPT_TIMEOUT,10)
                ->post($url);
        if($data&&Vhelper::is_json($data)){
            $response = json_decode($data);
            if(property_exists($response,'success_list')){
                $stockDatas['data']= $response->success_list;
                $stockDatas['status'] = 'success';
            }else{
                $stockDatas['status'] = 'error';
                $stockDatas['data'] = [];
                $stockDatas['message']='获取实时库存失败';
            }
        }else{
            $stockDatas['status'] = 'error';
            $stockDatas['data'] = [];
            $stockDatas['message']='获取实时库存失败';
        }
        return ['salesDatas'=>$salesDatas,'stock'=>$stockDatas];

    }
}
