<?php

namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use Yii;
use yii\behaviors\TimestampBehavior;
/**
 * This is the model class for table "{{%supplier_quotes}}".
 *
 * @property integer $id
 * @property string $suppliercode
 * @property string $product_sku
 * @property string $product_number
 * @property double $supplierprice
 * @property integer $currency
 * @property integer $minimum_purchase_amount
 * @property integer $purchase_delivery
 * @property integer $purchasing_units
 * @property string $business_order_number
 * @property integer $number_operations
 * @property integer $default_buyer
 * @property integer $add_time
 * @property integer $default_vendor
 * @property integer $default_Merchandiser
 * @property integer $add_user
 * @property string $supplier_product_address
 * @property integer $category_id
 * @property integer $status
 */
class SupplierQuotes extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%supplier_quotes}}';
    }
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['add_time'],
                    \yii\db\ActiveRecord::EVENT_BEFORE_UPDATE => ['update_time'],
                ],
                // if you're using datetime instead of UNIX timestamp:
                'value' => time(),
            ],
        ];
    }
    public  $sku;
    public $default_vendor;
    public $file_execl;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['suppliercode','product_sku','supplierprice','currency','default_vendor'], 'required'],
            [['supplierprice'], 'number'],
            [[ 'minimum_purchase_amount', 'purchase_delivery', 'purchasing_units', 'number_operations', 'default_buyer', 'add_time',  'default_Merchandiser', 'add_user',], 'integer'],
            [['suppliercode', 'product_sku', 'product_number', 'business_order_number'], 'string', 'max' => 30],
            [['add_user','update_user'], 'default', 'value' => Yii::$app->user->id],
            [['supplier_product_address'], 'string', 'max' => 500],
            ['file_execl', 'file', 'extensions' => ['xls', 'csv']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'suppliercode' => Yii::t('app', '供应商编码'),
            'product_sku' => Yii::t('app', '产品sku'),
            'product_number' => Yii::t('app', '供应商品号'),
            'default_vendor' => Yii::t('app', '默认供应商'),
            'supplierprice' => Yii::t('app', '供应商单价'),
            'currency' => Yii::t('app', '币种'),
            'minimum_purchase_amount' => Yii::t('app', '最低采购量'),
            'purchase_delivery' => Yii::t('app', '采购交期'),
            'purchasing_units' => Yii::t('app', '采购单位'),
            'business_order_number' => Yii::t('app', '业务单号'),
            'number_operations' => Yii::t('app', '业务数量'),
            'default_buyer' => Yii::t('app', '默认采购员'),
            'add_time' => Yii::t('app', '添加时间'),
            'default_Merchandiser' => Yii::t('app', '默认跟单员'),
            'add_user' => Yii::t('app', '操作人'),
            'supplier_product_address' => Yii::t('app', '供应商产品地址'),
            'category_id' => Yii::t('app', '品类id'),
        ];
    }

    /**
     * 获取供应商信息
     */
    public function getSupplier(){
        return $this->hasOne(Supplier::className(),['supplier_code'=>'suppliercode']);
    }

    /**
     * 数据更新 多条与单条
     * @param $data
     * @return bool
     */
    public  function  saveSupplierOne($data)
    {

        $sku = strpos($data['SupplierQuotes']['id'],',')? explode(',',$data['SupplierQuotes']['id']):$data['SupplierQuotes']['id'];
        if (is_array($sku))
        {

            foreach ($sku as $v)
            {

                $status = $this->SupplierOne($v, $data);
            }
        } else {

                $status = $this->SupplierOne($sku, $data);

        }
        return $status;
    }
    /**
     * 更新报价
     * @param $data
     * @return bool
     */
    public  function  saveSupplierQuotes($data)
    {
        //20180810关闭采购单过审修改报价功能----王瑞
        return true;
        foreach ($data as $v)
        {
            if(!isset($v['suppliercode'])&&isset($v['supplier_code'])){
                $v['suppliercode'] = $v['supplier_code'];
            }
            $defaultSupplier = ProductProvider::find()->andFilterWhere(['sku'=>$v['sku'],'is_supplier'=>1])->one();
            //采购单供应商不是默认供应商则不添加数据
            if(!empty($defaultSupplier)&&$defaultSupplier->supplier_code != $v['suppliercode']){
                continue;
            }
            if(!empty($defaultSupplier)){
                $oldQuotes = self::find()->andFilterWhere(['id'=>$defaultSupplier->quotes_id])->one();
                if(!isset($v['link'])||empty($v['link'])){
                    $v['link'] = $oldQuotes->supplier_product_address ? $oldQuotes->supplier_product_address :'';
                }
                //采购价格和链接没有变化则不添加数据
                if(!empty($oldQuotes)&&$v['price']==$oldQuotes->supplierprice&&$v['link']==$oldQuotes->supplier_product_address){
                    continue;
                }
                $v['quoteId'] = $oldQuotes->id;
                $v['pur_ticketed_point'] = $oldQuotes->pur_ticketed_point;
            }else{
                $v['quoteId'] = '';
                if(!isset($v['link'])||empty($v['link'])){
                    $v['link'] = '';
                }
            }
            //新增一条待审的数据
            $applyId = SupplierUpdateApply::saveOrderApply($v);
            if($applyId){
                $applyIds[]= $applyId;
            }
        }
        if(!empty($applyIds)){
            SupplierLog::saveSupplierLog('orderSaveQuotes','采购单审核更新报价'.implode(',',$applyIds));
            //触发审核通过
            SupplierUpdateApply::checkApply($applyIds);
        }
        return true;
    }

    /**
     * ztt
     * 保存单条数据
     * @param $sku
     * @param $data
     * @return bool
     */
    protected  function  SupplierOne($sku, $data)
    {
            $model = new self;
            //更新中间表的默认供应商
            $provider = ProductProvider::SaveOne($data['SupplierQuotes']);
            $datas = [];
            if($provider == false)
            {
                $datas['sku']   = $sku;
                $datas['error'] = 0;
                return $datas;
            }
            //获取上一次的报价记录
            $Lasttime= $model->find()->where(['product_sku' =>$sku,'suppliercode'=>$data['SupplierQuotes']['suppliercode']])->orderBy('id desc')->one();
            //如果只填了供应商代码，那么就以上一次的报价记录copy一份
            $model->product_sku                = $sku;
            $model->suppliercode               = !empty($data['SupplierQuotes']['suppliercode']) ? $data['SupplierQuotes']['suppliercode']:(isset($Lasttime->suppliercode)?$Lasttime->suppliercode:'');
            $model->supplierprice              = !empty($data['SupplierQuotes']['supplierprice']) ?$data['SupplierQuotes']['supplierprice']:(isset($Lasttime->supplierprice)?$Lasttime->supplierprice:'');
            $model->currency                   = !empty($data['SupplierQuotes']['currency']) ? $data['SupplierQuotes']['currency'] : (isset($Lasttime->currency)?$Lasttime->currency:'');
            $model->purchase_delivery          = !empty($data['SupplierQuotes']['purchase_delivery']) ?$data['SupplierQuotes']['purchase_delivery'] :(isset($Lasttime->purchase_delivery)?$Lasttime->purchase_delivery:'');
            $model->minimum_purchase_amount    = !empty($data['SupplierQuotes']['minimum_purchase_amount']) ?$data['SupplierQuotes']['minimum_purchase_amount'] :(isset($Lasttime->minimum_purchase_amount)?$Lasttime->minimum_purchase_amount:'');
            $model->default_buyer              = !empty($data['SupplierQuotes']['default_buyer']) ?$data['SupplierQuotes']['default_buyer']:(isset( $Lasttime->default_buyer)? $Lasttime->default_buyer:'');
            $model->supplier_product_address   = !empty($data['SupplierQuotes']['supplier_product_address']) ?$data['SupplierQuotes']['supplier_product_address']:(isset($Lasttime->supplier_product_address)?$Lasttime->supplier_product_address:'');
            $model->purchasing_units           = !empty($data['SupplierQuotes']['purchasing_units']) ?$data['SupplierQuotes']['purchasing_units']: (isset($Lasttime->purchasing_units)?$Lasttime->purchasing_units:'');
            $model->add_user                   = Yii::$app->user->id;
            $model->default_Merchandiser       = isset( $Lasttime->default_Merchandiser)? $Lasttime->default_Merchandiser:'';
            $status = $model->save(false);
        return $status;
    }

    /**
     * 获取sku报价
     * @param $sku
     */
    public static function  getQuotes($sku,$supplier_code=null)
    {
        //获取最大报价
        $data['max']   = self::find()->where(['product_sku'=>$sku])->max('supplierprice');
        //获取最小报价
        $data['mix']   = self::find()->where(['product_sku'=>$sku])->min('supplierprice');
        //平均
        $data['avg']   = self::find()->where(['product_sku'=>$sku])->average('supplierprice');
        //总和
        $data['sum']   = self::find()->where(['product_sku'=>$sku])->sum('supplierprice');
        //次数
        $data['count'] = self::find()->where(['product_sku'=>$sku])->count();
        //获取实际报价
        if (!empty($supplier_code)) {
            $data['supplierprice'] = self::findOne(['product_sku' =>$sku,'suppliercode'=>$supplier_code])['supplierprice'];
        }
        return $data;
    }

    /**
     * 获取产品的链接
     * @param $code
     * @param $sku
     * @return false|null|string
     */
    public  static  function  getUrl($sku)
    {
        //2018-03-08 王瑞修改获取默认供应商的采购链接
        $defaultSupplier = ProductProvider::find()->andFilterWhere(['sku'=>$sku,'is_supplier'=>1])->one();
        if($defaultSupplier&&!empty($defaultSupplier->quotes)){
            return $defaultSupplier->quotes->supplier_product_address;
        }else{
            return 'http://www.1688.com';
        }
//        $url =self::find()->select('supplier_product_address')->where(['product_sku'=>$sku])->scalar();
//        if($url)
//        {
//            return $url;
//        } else{
//            return 'http://www.1688.com';
//        }
    }
    public static  function  getFiled($sku,$field='*')
    {
        $model=  self::find()->select($field)->where(['product_sku'=>$sku])->one();
        return $model;
    }

    /**
     * 通过ID获取价格
     * @param $id
     * @param string $field
     * @return array|null|\yii\db\ActiveRecord
     */
    public  static  function  getFileds($id,$field='*')
    {
        $model=  self::find()->select($field)->where(['id'=>$id])->one();
        return $model;
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
}
