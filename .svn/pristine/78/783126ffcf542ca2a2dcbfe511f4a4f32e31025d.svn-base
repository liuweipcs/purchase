<?php

namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use Yii;
use yii\helpers\Html;
/**
 * This is the model class for table "{{%purchase_order_items}}".
 *
 * @property integer $id
 * @property string $pur_number
 * @property string $sku
 * @property string $name
 * @property integer $qty
 * @property string $price
 * @property integer $ctq
 * @property integer $rqy
 * @property integer $cty
 * @property integer $sales_status
 *
 * @property PurchaseOrder $purNumber
 */
class PurchaseOrderItemsV2 extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%domestic_purchase_order_items}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pur_number', 'sku', 'name', 'qty'], 'required'],
            [['qty', 'ctq','e_ctq', 'rqy', 'cty', 'sales_status'], 'integer'],
            [['price','e_price'], 'number'],
            [['purchase_link'], 'safe'],
            [['pur_number'], 'string', 'max' => 20],
            [['sku'], 'string', 'max' => 30],
            [['name'], 'string', 'max' => 300],
            [['pur_number', 'sku'], 'unique', 'targetAttribute' => ['pur_number', 'sku'], 'message' => 'The combination of Pur Number and Sku has already been taken.'],
            [['pur_number'], 'exist', 'skipOnError' => true, 'targetClass' => PurchaseOrdersV2::className(), 'targetAttribute' => ['pur_number' => 'pur_number']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'pur_number' => Yii::t('app', 'Pur Number'),
            'product_link' => Yii::t('app', '产品链接'),
            'sku' => Yii::t('app', 'Sku'),
            'name' => Yii::t('app', 'Name'),
            'qty' => Yii::t('app', 'Qty'),
            'price' => Yii::t('app', 'Price'),
            'ctq' => Yii::t('app', 'Ctq'),
            'e_price' => Yii::t('app', 'Price'),
            'e_ctq' => Yii::t('app', 'Ctq'),
            'rqy' => Yii::t('app', 'Rqy'),
            'cty' => Yii::t('app', 'Cty'),
            'sales_status' => Yii::t('app', 'Sales Status'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPurNumber()
    {
        return $this->hasOne(PurchaseOrdersV2::className(), ['pur_number' => 'pur_number']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPurNumbers()
    {
        return $this->hasOne(PurchaseOrdersV2::className(), ['pur_number' => 'pur_number'])->where(['purchas_status'=>1]);
    }

    /**
     * 根据采购单号获取sku数
     * @param $purnumber
     */
    public static function  getSKU($purnumber)
    {
        return self::find()->where(['pur_number'=>$purnumber])->count();
    }


    public function getProduct(){
        return $this->hasOne(Product::className(),['sku'=>'sku']);
    }

    /**
     * 根据采购单号获取统计总金额
     * @param $purnumber
     * @return mixed
     */
    public static function getCountPrice($purnumber)
    {
        return self::find()->where(['pur_number'=>$purnumber])->sum('items_totalprice');
    }
    /**
     * 根据采购单号获取sku
     * @param $purnumber
     * @return mixed
     */
    public static function getSkus($purnumber,$type=1,$code='SZ_AA',$limit=5)
    {
        $model = self::find()->select('sku,items_totalprice,price,ctq')->where(['pur_number'=>$purnumber])->limit($limit)->all();
        $s='';
       foreach($model as $v)
       {
           if($type==1)
           {
               $s.=$v->sku.' : '.$v->items_totalprice.' RMB'."<Br/>";
               $s.='包装方式：'.Product::getSkuCode($v->sku)."<Br/>";
               $sb= SupplierQuotes::getUrl($v->sku);
               $s.="采购链接：<a href='$sb' title='' target='_blank'><i class='fa fa-fw fa-internet-explorer'></i></a><Br/>";
           } else{
               $s.=$v->sku.'&nbsp;&nbsp;RMB'.$v->price.'&nbsp;数量:'.$v->ctq.
               Html::a('',['product/viewskusales'], ['class' => "glyphicon glyphicon-signal b",'data'=>$v->sku , 'title' => '销量统计','data-toggle' => 'modal', 'data-target' => '#create-modal',]).'&nbsp;'.
               Html::a('',['#'], ['class' => "fa fa-fw fa-th data-updates",'data'=>$v->sku , 'title' => '历史报价','data-toggle' => 'modal', 'data-target' => '#create-modal',])."<Br/>";
           }
       }
        return $s;

    }

    /**
     * 根据采购单号获取sku
     * @param $purnumber
     * @return false|null|string
     */
    public static function  getSKUc($purnumber)
    {
        return self::find()->select('sku,ctq')->where(['pur_number'=>$purnumber])->asArray()->all();
    }


    /**
     * 获取sku在一段时间内的采购数量，
     * @param $sku  商品sku
     * @param $beginTime 开始时间
     * @param null $endTime 结束时间，未传入默认为当月结束时间
     * @param null $supplierCode 供应商,未传入不加入限制条件
     */
    public static function getSkuPurchaseNum($sku,$beginTime,$endTime=null){
        $endTime = !empty($endTime) ? $endTime : date('Y-m-t 23:59:59',strtotime($beginTime));
        $data = self::getPurchaseData($sku,$beginTime,$endTime);
        if(empty($data)){
            if(date('m') ==12){
                $newBeginTime = date('Y-01-01 00:00:00',strtotime("$beginTime+1year"));
                $newEndTime   = date('Y-01-t',strtotime("$beginTime+1year"));
            }else{
                $newBeginTime = date('Y-m-01 00:00:00',strtotime("$beginTime+1month"));
                $newEndTime   = date('Y-m-t 23:59:59',strtotime("$beginTime+1month"));
            }
            $data = self::getPurchaseData($sku,$newBeginTime,$newEndTime);
        }
        return !empty($data) ? $data->ctq : 0;
    }

    public static function getPurchaseData($sku,$begin,$end){
        $data = self::find()->alias('t')
            ->select('sum(t.ctq) as ctq')
            ->leftJoin('pur_purchase_order AS a',' a.pur_number=t.pur_number')
            ->andFilterWhere(['t.sku'=>$sku])
            ->andFilterWhere(['between', 'a.created_at',$begin,$end])
            ->andFilterWhere(['a.purchas_status'=>6])
            ->groupBy('t.sku')->one();
        return $data;
    }

    public function getSkuPurchaseLink(){
        return !empty($this->product) ? !empty($this->product->supplierQuote) ? !empty($this->product->supplierQuote->supplier_product_address) ? $this->product->supplierQuote->supplier_product_address : 'https://www.1688.com' : 'https://www.1688.com' : 'https://www.1688.com';
    }
}
