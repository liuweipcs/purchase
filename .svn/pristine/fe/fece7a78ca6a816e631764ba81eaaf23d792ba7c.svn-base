<?php

namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use Yii;

/**
 * This is the model class for table "{{%purchase_history}}".
 *
 * @property integer $id
 * @property string $pur_number
 * @property string $warehouse
 * @property string $buyer
 * @property string $purchase_time
 * @property string $cargo_location
 * @property string $sku
 * @property string $sku_alias
 * @property string $product_name
 * @property string $specification
 * @property string $purchase_link
 * @property string $features
 * @property string $currency
 * @property string $purchase_price
 * @property string $latest_offer
 * @property integer $purchase_quantity
 * @property integer $actual_arrival_quantity
 * @property integer $actual_storage_quantity
 * @property string $actually_pay_money
 * @property integer $freight
 * @property string $supplier_code
 * @property string $supplier_name
 * @property string $settlement_method
 * @property string $contact_person
 * @property string $contact_number
 * @property string $fax_number
 * @property string $qq
 * @property string $ali_want
 * @property string $email
 * @property string $country
 * @property string $province_state
 * @property string $city
 * @property string $address
 * @property string $remarks
 * @property string $expected_arrival_date
 * @property string $payment_status
 * @property string $purchasing_status
 * @property integer $create_id
 * @property string $create_time
 */
class PurchaseHistory extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_history}}';
    }

    public $file_execl;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['file_execl'], 'file', 'skipOnEmpty' => false, 'extensions' => 'csv'],
            [['purchase_time', 'expected_arrival_date', 'create_time'], 'safe'],
            [['purchase_price', 'latest_offer', 'actually_pay_money'], 'number'],
            [['purchase_quantity', 'actual_arrival_quantity', 'actual_storage_quantity', 'freight', 'create_id'], 'integer'],
            [['pur_number', 'warehouse', 'sku', 'supplier_code', 'contact_person', 'contact_number', 'fax_number', 'qq', 'ali_want', 'email'], 'string', 'max' => 30],
            [['buyer', 'cargo_location', 'currency', 'settlement_method', 'country', 'province_state', 'city', 'payment_status', 'purchasing_status'], 'string', 'max' => 20],
            [['sku_alias', 'product_name', 'purchase_link', 'features', 'supplier_name','external_water'], 'string', 'max' => 100],
            [['specification'], 'string', 'max' => 50],
            [['address'], 'string', 'max' => 200],
            [['remarks'], 'string', 'max' => 500],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'pur_number' => Yii::t('app', '采购单号'),
            'warehouse' => Yii::t('app', '采购仓库'),
            'buyer' => Yii::t('app', '采购员'),
            'purchase_time' => Yii::t('app', '采购日期'),
            'cargo_location' => Yii::t('app', '货位'),
            'sku' => Yii::t('app', 'SKU'),
            'sku_alias' => Yii::t('app', 'SKU别名'),
            'product_name' => Yii::t('app', '货品名称'),
            'specification' => Yii::t('app', '规格'),
            'purchase_link' => Yii::t('app', '采购链接'),
            'features' => Yii::t('app', '产品特点'),
            'currency' => Yii::t('app', '采购币种'),
            'purchase_price' => Yii::t('app', '采购单价'),
            'latest_offer' => Yii::t('app', '最新报价'),
            'purchase_quantity' => Yii::t('app', '采购数量'),
            'actual_arrival_quantity' => Yii::t('app', '实际到货数量'),
            'actual_storage_quantity' => Yii::t('app', '实际入库数量'),
            'actually_pay_money' => Yii::t('app', '实际应付货款'),
            'freight' => Yii::t('app', '运费'),
            'supplier_code' => Yii::t('app', '供应商代码'),
            'supplier_name' => Yii::t('app', '供应商名称'),
            'settlement_method' => Yii::t('app', '结算方式'),
            'contact_person' => Yii::t('app', '联系人'),
            'contact_number' => Yii::t('app', '联系电话'),
            'fax_number' => Yii::t('app', '传真号'),
            'qq' => Yii::t('app', 'Qq'),
            'ali_want' => Yii::t('app', '阿里旺旺'),
            'email' => Yii::t('app', 'Email'),
            'country' => Yii::t('app', '国家'),
            'province_state' => Yii::t('app', '省/州'),
            'city' => Yii::t('app', '城市'),
            'address' => Yii::t('app', '详细地址'),
            'remarks' => Yii::t('app', '备注'),
            'expected_arrival_date' => Yii::t('app', '预计到达日期'),
            'payment_status' => Yii::t('app', '付款状态'),
            'purchasing_status' => Yii::t('app', '采购状态'),
            'create_id' => Yii::t('app', '导入人'),
            'create_time' => Yii::t('app', '导入时间'),
        ];
    }

    /**
     * 通过采购sku获取采购链接
     * @param $sku
     * @return false|null|string
     */
    public  static  function  getPurchaseLink($sku)
    {
        $purchase_link=self::find()->select('purchase_link,features')->where(['sku'=>$sku])->one();
        if(!empty($purchase_link['purchase_link']))
        {
            return $purchase_link['purchase_link'];
        } elseif(!empty($purchase_link['features'])) {
            return $purchase_link['features'];
        } else{
            return 'https://www.1688.com';
        }

    }
    public function upload()
    {


            $uploadpath = 'Uploads/' . date('Ymd') . '/';  //上传路径
            // 图片保存在本地的路径：images/Uploads/当天日期/文件名，默认放置在basic/web/下
            $dir = '/images/' . $uploadpath;
            //生成唯一uuid用来保存到服务器上图片名称
            $pickey = Vhelper::genuuid();
            $filename = $pickey . '.' . $this->file_execl->getExtension();
            //如果文件夹不存在，则新建文件夹
            $filepath= Vhelper::fileExists(Yii::getAlias('@app') . '/web' . $dir);
            $file = $filepath.$filename;

            if ($this->file_execl->saveAs($file))
            {

                return $file;
            } else{
                return false;
            }

    }
    public static  function  getField($sku,$field='*')
    {
        return self::find()->select($field)->where(['sku'=>$sku])->orderBy('purchase_time desc')->scalar();
    }


    /**
     * 上次采购单价
     * @return string
     */
    public static function getLastPrice($sku){
        $model1=PurchaseOrderItems::find()
            ->alias('b')
            ->select('submit_time,price')
            ->leftJoin('pur_purchase_order as a','a.pur_number = b.pur_number')
            ->where(['NOT IN','a.purchas_status',[1,2,4,10]])
            ->andFilterWhere(['b.sku'=>$sku])
            ->orderBy('submit_time desc')
            ->asArray()
            ->one();

        $model2=PurchaseHistory::find()
            ->select('purchase_time,purchase_price')
            ->where(['sku'=>$sku])
            ->orderBy('purchase_time desc')
            ->asArray()
            ->one();

        $last_price = '';
        //新系统采购信息存在  通途采购信息不存在

        if($model1 && empty($model2)){
            $last_price = $model1['price'];
        }elseif ($model2 && empty($model1)){
            $last_price = $model2['purchase_price'];
        }elseif ($model1 && $model2){
            $time1 = $model1['submit_time'];
            $time2 = $model2['purchase_time'];
            if($time1 > $time2){
                $last_price = $model1['price'];
            }else{
                $last_price = $model2['purchase_price'];
            }
        }

        return $last_price;
    }
}
