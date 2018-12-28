<?php

namespace app\models;

use app\models\base\BaseModel;


use app\services\CommonServices;
use Yii;
use yii\behaviors\TimestampBehavior;
use app\config\Vhelper;
use app\models\SupplierPaymentAccount;
use app\models\SupplierImages;
use app\models\SupplierContactInformation;
/**
 * This is the model class for table "{{%supplier}}".
 *
 * @property integer $id
 * @property string $suppliercode
 * @property integer $buyer
 * @property integer $merchandiser
 * @property integer $maincategory
 * @property string $supplier_name
 * @property integer $supplierlevel
 * @property integer $suppliertype
 * @property integer $suppliersettlement
 * @property integer $paymentmethod
 * @property integer $cooperationtype
 * @property integer $paymentcycle
 * @property integer $transportparty
 * @property integer $producthandling
 * @property double $commissionratio
 * @property double $purchaseamount
 * @property integer $create_time
 * @property integer $update_time
 * @property integer $create_id
 * @property integer $update_id
 * @property integer $status
 * @property integer $first_cooperation_time
 */
class Supplier extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%supplier}}';
    }
    public $payment_platform_branch;
    public $image_url;
    public $file_execl;
    public $sex;
    public $first_line;
    public $second_line;
    public $third_line;
    public $default_buyer;
    public $page_size;
    public $order_type;
    public $is_check;
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['create_time'],
                    \yii\db\ActiveRecord::EVENT_BEFORE_UPDATE => ['update_time'],
                ],
                // if you're using datetime instead of UNIX timestamp:
                // 'value' => new Expression('NOW()'),
            ],
        ];
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['supplier_name'],'unique','on'=>['create']],
            [['supplier_name', 'supplier_code','business_scope','store_link','supplier_address','supplier_type','supplier_level','payment_method','invoice', 'credit_code'], 'required'],
            [['main_category', 'supplier_level', 'supplier_type', 'supplier_settlement', 'payment_method', 'cooperation_type', 'payment_cycle', 'transport_party', 'product_handling', 'create_time', 'update_time', 'create_id', 'update_id', 'status','is_taxpayer'], 'integer'],
            [['commission_ratio', 'purchase_amount'], 'number'],
            [['create_id','merchandiser'], 'default','value' => Yii::$app->user->id],
            [['supplier_code', 'supplier_name','esupplier_name'], 'string', 'max' => 100],
            [['supplier_address'], 'string', 'max' => 200],
            [['contract_notice'], 'string', 'max' => 500],
            [['taxrate'], 'string', 'max' => 10],
            [['first_cooperation_time','is_push_to_k3cloud'],'safe'],
            //默认值
            [['supplier_level','main_category','supplier_type','supplier_settlement','payment_method','transport_party','product_handling','commission_ratio','purchase_amount','cooperation_type'], 'default','value' => 1],
            [['payment_cycle'], 'default','value' => 3],
            [['province'], 'default','value' =>6],
            [['city'], 'default','value' => 77],
            [['area'], 'default','value' => 709],

        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                         => Yii::t('app', 'ID'),
            'supplier_code'              => Yii::t('app', '供应商代码'),
            'buyer'                      => Yii::t('app', '采购员'),
            'merchandiser'               => Yii::t('app', '跟单员'),
            'main_category'              => Yii::t('app', '主营品类'),
            'supplier_name'              => Yii::t('app', '供应商中文名'),
            'esupplier_name'             => Yii::t('app', '供应商英文名'),
            'supplier_level'             => Yii::t('app', '供应商等级'),
            'supplier_type'              => Yii::t('app', '供应商类型'),
            'supplier_settlement'        => Yii::t('app', '供应商结算方式'),
            'payment_method'             => Yii::t('app', '支付方式'),
            'cooperation_type'           => Yii::t('app', '合作类型'),
            'payment_cycle'              => Yii::t('app', '支付周期类型'),
            'transport_party'            => Yii::t('app', '运输承担方'),
            'product_handling'           => Yii::t('app', '不良品处理'),
            'commission_ratio'           => Yii::t('app', '供应商佣金比例'),
            'purchase_amount'            => Yii::t('app', '合同采购金额'),
            'create_time'                => Yii::t('app', '创建时间'),
            'update_time'                => Yii::t('app', '修改时间'),
            'create_id'                  => Yii::t('app', '创建人ID'),
            'update_id'                  => Yii::t('app', '修改人ID'),
            'contract_notice'            => Yii::t('app', '合同注意事项'),
            'province'                   => Yii::t('app', '所在省'),
            'city'                       => Yii::t('app', '所在市'),
            'area'                       => Yii::t('app', '所在区'),
            'supplier_address'           => Yii::t('app', '详细地址'),
            'is_taxpayer'                => Yii::t('app', '是否为一般纳税人'),
            'taxrate'                    => Yii::t('app', '税率'),
            'status'                     => Yii::t('app', '状态'),
            'use_type'                     => Yii::t('app', '部门'),
            'use_type_m'                     => Yii::t('app', '部门多选'),
            'business_scope'              => Yii::t('app', '经营范围'),
            'supplier_status'            => Yii::t('app', '供应商审核'),
            'financial_status'            => Yii::t('app', '财务审核'),
            'freight_pay_type'            => Yii::t('app', '运费付费方式'),
            'store_link'                 => Yii::t('app', '店铺链接'),
            'first_cooperation_time'     => Yii::t('app','首次合作时间'),
            'invoice'                    => Yii::t('app','开票'),
            'account_type'            	 => Yii::t('app', '账户类型'),
            'credit_code'                => Yii::t('app', '统一社会信用代码'),
            'supplier_special_flag'      => Yii::t('app', '跨境宝'),
        ];
    }

    /**
     * 关联支付方式
     * @return \yii\db\ActiveQuery
     */
    public function getPay()
    {

        return $this->hasMany(SupplierPaymentAccount::className(), ['supplier_id' => 'id'])->where(['pur_supplier_payment_account.status'=>1]);
    }
    /**
     * 关联联系方式
     * @return \yii\db\ActiveQuery
     */
    public function getContact()
    {

        return $this->hasMany(SupplierContactInformation::className(), ['supplier_id' => 'id']);
    }
    /**
     * 关联附图
     * @return \yii\db\ActiveQuery
     */
    public function getImg()
    {

        return $this->hasMany(SupplierImages::className(), ['supplier_id' => 'id']);
    }
    /**
     * 关联附图
     */
    public function getImgOne()
    {
        return $this->hasOne(SupplierImages::className(), ['supplier_id' => 'id'])->where(['image_status'=>1]);
    }

    /**
     * 关联采购员及部门
     * @return \yii\db\ActiveQuery
     */
    public function getBuyerList()
    {

        return $this->hasMany(SupplierBuyer::className(), ['supplier_code' => 'supplier_code'])->where(['pur_supplier_buyer.status'=>1]);
    }

    /**
     * 关联产品线
     * @return \yii\db\ActiveQuery
     */
    public function getLine()
    {

        return $this->hasMany(SupplierProductLine::className(), ['supplier_code' => 'supplier_code'])->where(['pur_supplier_product_line.status'=>1]);
    }

    public function getSupplierLine(){
        return $this->hasOne(SupplierProductLine::className(),['supplier_code'=>'supplier_code'])->where(['pur_supplier_product_line.status'=>1]);
    }
    /**
     * 关联产品信息
     */
    public function getProduct(){
        return $this->hasMany(Product::className(),['sku'=>'sku'])->via('sku');
    }
    /**
     * 关联SKU
     */
    public function getSku()
    {
        return $this->hasMany(ProductProvider::className(),['supplier_code'=>'supplier_code'])->where(['is_supplier'=>1])->leftJoin('pur_product','pur_product.sku=pur_product_supplier.sku')->andWhere(['NOT in','pur_product.product_status',[0,7]])->andFilterWhere(['<>','pur_product.product_is_multi',2])->andFilterWhere(['pur_product.product_type'=>1]);
    }
    /**
     * 关联供应商更新审核日志
     */
    public function getSupplierUpdateLog()
    {
        return $this->hasMany(SupplierUpdateLog::className(),['supplier_code'=>'supplier_code']);
    }
    /**
     * 保存数据
     * @param $data
     */
    public  function saveSupplier($data,$userId=null,$status=1,$source=2)
    {
        $exist = self::find()->where(['supplier_name'=>isset($data['Supplier']['supplier_name']) ? trim($data['Supplier']['supplier_name']) :null])->exists();
        if($exist){
            $supplierName = isset($data['Supplier']['supplier_name']) ? trim($data['Supplier']['supplier_name']) :'';
            return $supplierName.'采购系统已经存在';
        }
        $model = new self();
        $model->scenario = 'create';
        $model->load($data);
        $model->create_id = $userId ? $userId : Yii::$app->user->id;
        $model->status = $status;
        $model->supplier_name = isset($data['Supplier']['supplier_name']) ? trim($data['Supplier']['supplier_name']) :null;
        $model->source = $source;
        $model->validate();
        $model->supplier_code = CommonServices::getNumber('QS');
        if ($model->save(false))
        {
            $data['id']   = $model->attributes['id'];
            $data['code'] = $model->attributes['supplier_code'];
            $data['name'] = $model->attributes['supplier_name'];
            return $data;
        }
        return serialize($model->getErrors()) ;
    }

    /**
     * 数据更新
     * @param $data
     * @return bool
     */
    public  function  saveSupplierOne($data)
    {
        $model = new self;
        $id = strpos($data['Supplier']['id'],',') ? explode(',',$data['Supplier']['id']):$data['Supplier']['id'];
        if (is_array($id))
        {
            foreach ($id as $v)
            {

                    $model = $model->findOne($v);
                    $model->main_category        = $data['Supplier']['main_category'];
                    $model->supplier_level       = $data['Supplier']['supplier_level'];
                    $model->cooperation_type     = $data['Supplier']['cooperation_type'];
                    $model->supplier_type        = $data['Supplier']['supplier_type'];
                    $model->buyer                = $data['Supplier']['buyer'];
                    //$model->merchandiser         = $data['Supplier']['merchandiser'];
                    $model->supplier_settlement  = $data['Supplier']['supplier_settlement'];
                    //$model->payment_cycle        = $data['Supplier']['payment_cycle'];
                    $model->purchase_amount      = $data['Supplier']['purchase_amount'];
                    $model->commission_ratio     = $data['Supplier']['commission_ratio'];
                    $model->contract_notice      = $data['Supplier']['contract_notice'];

                    $model->update_id            = Yii::$app->user->id;
                    $status = $model->save(false);

                    // 供应商修改记录
                    \app\models\SupplierLog::saveSupplierLog('supplier::saveSupplierOne',$model->supplier_settlement,false,$model->supplier_name,$model->supplier_code);

            }
        } else {
                    $model = $model->findOne($id);
                    $model->main_category        = $data['Supplier']['main_category'];
                    $model->supplier_level       = $data['Supplier']['supplier_level'];
                    $model->cooperation_type     = $data['Supplier']['cooperation_type'];
                    $model->supplier_type        = $data['Supplier']['supplier_type'];
                    $model->buyer                = $data['Supplier']['buyer'];
                    //$model->merchandiser         = $data['Supplier']['merchandiser'];
                    $model->supplier_settlement  = $data['Supplier']['supplier_settlement'];
                    //$model->payment_cycle        = $data['Supplier']['payment_cycle'];
                    $model->purchase_amount      = $data['Supplier']['purchase_amount'];
                    $model->commission_ratio     = $data['Supplier']['commission_ratio'];
                    $model->contract_notice      = $data['Supplier']['contract_notice'];

                    $model->update_id            = Yii::$app->user->id;
                    $status = $model->save(false);

                    // 供应商修改记录
                    \app\models\SupplierLog::saveSupplierLog('supplier::saveSupplierOne',$model->supplier_settlement,false,$model->supplier_name,$model->supplier_code);

        }
        return $status;
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

    //保存erp供应商
    public function erpSave($model,$datass){
        $model->buyer                       = 1;
        $model->merchandiser                = 1;
        $model->main_category               = $datass->providercategory;
        $model->supplier_name               = $datass->provider_company;
        $model->supplier_level              = 1;
        $model->supplier_type               = $datass->provider_type==3 ? 8 : 7;
        $model->supplier_settlement         = $datass->provider_settlement_type ? self::getPayType($datass->provider_settlement_type) : 1;
        $model->payment_method              = 2;
        $model->cooperation_type            = 1;
        $model->payment_cycle               = 1;
        $model->transport_party             = 2;
        $model->product_handling            = 1;
        $model->commission_ratio            = 1;
        $model->purchase_amount             = 1;
        $model->create_id                   = Yii::$app->user->id;
        $model->update_id                   = 1;
        $model->status                      = 1;
        $model->esupplier_name              = $datass->provider_company;
        $model->contract_notice             = $datass->contact_note;
        $model->province                    = self::getArea($datass->provider_region_id,1);
        $model->city                        = self::getArea($datass->provider_region_id,2);;
        $model->area                        = self::getArea($datass->provider_region_id,3);;
        $model->source                      = 1;
        $model->is_push                     = 1;
        $model->business_scope              = 1;
        $model->store_link                  = $datass->provider_website;
        $model->create_time                 = strtotime($datass->create_time);
        $model->update_time                 = strtotime($datass->modify_time);
        $model->supplier_address            = $datass->provider_detail_address;
        $model->business_scope              = $datass->provider_description;
        $model->supplier_code               = !empty($model->supplier_code)?$model->supplier_code:CommonServices::getNumber('QS');
        if($model->save(false))
        {
            // 供应商修改记录
            \app\models\SupplierLog::saveSupplierLog('supplier::erpSave',json_decode($datass),false,$model->supplier_name,$model->supplier_code);

            $contanName = !empty($datass->contact_name)?$datass->contact_name:'张三风';
            $model_cont  = SupplierContactInformation::find(['contact_person'=>$contanName,'supplier_id'=>$model->attributes['id']])->one();
            if(empty($model_cont)){
                $model_cont  =new  SupplierContactInformation;
            }
            $model_cont->contact_person           =   !empty($datass->contact_name)?$datass->contact_name:'张三风';
            $model_cont->supplier_id              =   $model->attributes['id'];
            $model_cont->contact_number           =   !empty($datass->contact_phone)?$datass->contact_phone:'13242082971';
            $model_cont->contact_fax              =   !empty($datass->contact_fax)?$datass->contact_fax:'13242082971';
            $model_cont->chinese_contact_address  =   isset($datass->provider_detail_address)?$datass->provider_detail_address:'';
            $model_cont->english_address          =   isset($datass->english_address)?$datass->english_address:'';
            $model_cont->contact_zip              =   isset($datass->contact_zip)?$datass->contact_zip:'';
            $model_cont->qq                       =   isset($datass->contact_qq)?$datass->contact_qq:'';
            $model_cont->micro_letter             =   isset($datass->micro_letter)?$datass->micro_letter:'';
            $model_cont->want_want                =   isset($datass->contact_ali_wang)?$datass->contact_ali_wang:'';
            $model_cont->skype                    =   isset($datass->skype)?$datass->skype:'';
            $model_cont->sex                      =   isset($datass->contact_sex)?$datass->contact_sex : 1;
            $model_cont->supplier_code            =   $model->attributes['supplier_code'];
            $model_cont->save(false);
            //添加国内仓默认采购员王开伟
            $suplierBuyer = new SupplierBuyer();
            $suplierBuyer->supplier_code = $model->attributes['supplier_code'];
            $suplierBuyer->type          = 1;
            $suplierBuyer->buyer         = '王开伟';
            $suplierBuyer->status        = 1;
            $suplierBuyer->supplier_name = $model->attributes['supplier_name'];
            $suplierBuyer->save(false);
            //添加产品线
            if(isset($datass->provider_productlinelistfirst)&&!empty($datass->provider_productlinelistfirst)){
                $supplierProduct = new SupplierProductLine();
                $supplierProduct->first_product_line = $datass->provider_productlinelistfirst;
                $supplierProduct->status             = 1;
                $supplierProduct->supplier_code      = $model->attributes['supplier_code'];
                $supplierProduct->save(false);
            }
        }
        return $model->attributes['supplier_code'];
    }

    //获取erp结算方式
    protected static function getPayType($type){
        switch ($type){
            case 1 :
                return 2;
                break;
            case 99 :
                return 1;
                break;
            case 7 :
                return 7;
                break;
            case 14 :
                return 8;
                break;
            case 30 :
                return 9;
                break;
            default :
                return 1;
                break;
        }
    }

    //获取erp供应商区域
    protected static function getArea($id,$type){
        $region = Region::find()->where(['id'=>$id])->one();
        if(empty($region)){
            return "";
        }
        if($region->region_type == $type){
            return $region->id;
        }
        if($region->region_type >$type){
            return $region->pid;
        }
        return '';
    }
    /**
     * 获取供应商编码
     */
    public static function getSupplierCode($supplier_name)
    {
        return self::find()->select('supplier_code')->where(['supplier_name'=>$supplier_name, 'status'=>1])->scalar();
    }

    /**
     * 获取供应商是否是 跨境宝供应商
     * @param bool $is_html_label  true.返回 HTML 标签,false.返回数值
     * @param null $supplier_code  供应商代码
     * @param null $supplier_name  供应商名称
     * @return mixed|string
     */
    public static function flagCrossBorder($is_html_label = true,$supplier_code = null,$supplier_name = null){
        if(empty($supplier_code) AND empty($supplier_name)) return $is_html_label?'':0;
        if($supplier_name){
            $su_key         = 'CrossBorder_'.$supplier_name;
        }else{
            $su_key         = 'CrossBorder_'.$supplier_code;
        }
        //Yii::$app->cache->delete($su_key);
        //$su_flag_value  = Yii::$app->cache->get($su_key);
        $su_flag_value = false;
        if($su_flag_value === false){
            $supplier = self::find()
                ->select('supplier_code,supplier_name,supplier_special_flag')
                ->andFilterWhere(['supplier_code' => $supplier_code,'supplier_name' => $supplier_name])
                ->one();

            /*if($supplier){
                Yii::$app->cache->set($su_key, $supplier->supplier_special_flag, 60*60);
            }else{
                Yii::$app->cache->set($su_key, 0, 60*60);
            }
            $su_flag_value = Yii::$app->cache->get($su_key); */

            $su_flag_value = isset($supplier->supplier_special_flag)?intval($supplier->supplier_special_flag):0;
        }

        if($is_html_label){
            return $su_flag_value ? " <span class='su_cross_border' style='display:inline-block'>跨境</span>" : '';
        }else{
            return $su_flag_value;
        }
    }
}
