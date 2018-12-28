<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\HttpException;
use yii\data\ActiveDataProvider;
use app\config\Vhelper;
use app\services\PurchaseOrderServices;

/**
 * This is the model class for table "pur_supplier_update_apply".
 *
 * @property integer $id
 * @property string $sku
 * @property integer $old_quotes_id
 * @property integer $new_quotes_id
 * @property string $new_supplier_code
 * @property integer $new_product_num
 * @property integer $create_user_id
 * @property string $create_user_name
 * @property integer $status
 * @property string $create_time
 * @property string $update_time
 * @property string $update_user_name
 * @property integer $type
 * @property integer $is_sample
 * @property integer $old_product_num
 * @property string $old_supplier_code
 * @property string $refuse_reason
 * @property string $integrat_user_name
 * @property string $integrat_time
 * @property string $fail_reason
 * @property string $integrat_status
 * @property string $old_purchase_link
 * @property string $new_purchase_link
 * @property string $integrat_note
 *
 */
class SupplierUpdateApply extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_supplier_update_apply';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['old_quotes_id', 'new_quotes_id', 'new_product_num', 'create_user_id', 'status', 'type', 'is_sample', 'old_product_num'], 'integer'],
            [['create_time', 'update_time','integrat_note'], 'safe'],
            [['sku'], 'string', 'max' => 255],
            [['new_supplier_code', 'create_user_name', 'update_user_name', 'old_supplier_code'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sku' => 'Sku',
            'old_quotes_id' => 'Old Quotes ID',
            'new_quotes_id' => 'New Quotes ID',
            'new_supplier_code' => 'New Supplier Code',
            'new_product_num' => 'New Product Num',
            'create_user_id' => 'Create User ID',
            'create_user_name' => 'Create User Name',
            'status' => 'Status',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'update_user_name' => 'Update User Name',
            'type' => 'Type',
            'is_sample' => 'Is Sample',
            'old_product_num' => 'Old Product Num',
            'old_supplier_code' => 'Old Supplier Code',
            'refuse_reason'     =>'Refuse Reason',
            'integrat_user_name' => 'Integrat User Name',
            'integrat_time'      => 'Integrat Time',
            'fail_reason'        => 'Fail Reason',
            'integrat_status'    => Yii::t('app','整合状态'),
            'old_purchase_link'  => 'Old Purchase Link',
            'new_purchase_link'  => 'New Purchase Link',
            'integrat_note'      => 'Integrat Note',
        ];
    }


    /**
     * 获取sku关联产品信息
     * @return \yii\db\ActiveQuery
     */
    public function getProductDetail(){
        return $this->hasOne(Product::className(),['sku'=>'sku']);
    }

    /**
     * 获取sku原有报价信息
     * @return \yii\db\ActiveQuery
     */
    public function getOldQuotes(){
        return $this->hasOne(SupplierQuotes::className(),['id'=>'old_quotes_id']);
    }

    /**
     * 获取sku新报价信息
     * @return \yii\db\ActiveQuery
     */
    public function getNewQuotes(){
        return $this->hasOne(SupplierQuotes::className(),['id'=>'new_quotes_id']);
    }

    /*
     * 获取sku原供货商信息
     */
    public function getOldSupplier(){
        return $this->hasOne(Supplier::className(),['supplier_code'=>'old_supplier_code']);
    }

    //获取降本采购数量
    public function getSkuCost(){
        return CostPurchaseNum::find()->alias('t')->leftJoin(self::tableName().' sua','t.apply_id=sua.id')
            ->where(['sua.id'=>$this->id])
            //->andWhere(['t.sku'=>1])
            ->andWhere('t.purchase_num !=0')
            ->andWhere('left(t.date,7)>=left(sua.cost_begin_time,7)')
            ->all();
    }

    /**
     * 获取sku新供货商信息
     * @return \yii\db\ActiveQuery
     */
    public function getNewSupplier(){
        return $this->hasOne(Supplier::className(),['supplier_code'=>'new_supplier_code']);
    }

    public function getQualityResult(){
        return $this->hasOne(SampleInspect::className(),['apply_id'=>'id']);
    }

    public function getProductDes(){
        return $this->hasOne(Product::className(),['sku'=>'sku']);
    }
    /**
     * 提交供应商修改申请
     * @param $datas  = array([0] => Array
    (
    [quoteId] => 0
    [sku] => SJ00001-01
    [suppliercode] => A1685034725
    [price] =>10
    )
     *   )
     * @return array
     * @throws \yii\db\Exception
     */
    public static function saveApply($datas,$type='update',$remark=''){
        $tran = Yii::$app->db->beginTransaction();
        try{
            $count = '';
            foreach($datas as $data){
                $updateExist = self::find()->andFilterWhere(['sku'=>$data['sku'],'status'=>1,'new_supplier_code'=>$data['suppliercode']])->one();
                if($updateExist){
                    $count .= $data['sku'].',';
                    continue;
                }
                if($type=='update'){
                    $exist = self::find()->andFilterWhere(['sku'=>$data['sku'],'status'=>1])->andFilterWhere(['NOT IN','type',[5,6]])->one();
                    if($exist){
                        $count .= $data['sku'].',';
                        continue;
                    }
                    $standbySupplier = ProductProvider::find()->select('supplier_code')->andFilterWhere(['sku'=>$data['sku'],'is_supplier'=>2])->all();
                    $standbySupplierList = ArrayHelper::getValue($standbySupplier,'supplier_code');
                    if(!empty($standbySupplierList)&&in_array($data['suppliercode'],$standbySupplierList)){
                        $count .= $data['sku'].',';
                        continue;
                    }
                }
                $defaultSupplier = ProductProvider::find()->andFilterWhere(['sku'=>$data['sku'],'is_supplier'=>1])->one();
                if(!empty($defaultSupplier)&&$defaultSupplier->quotes_id !=$data['quoteId']){
                    throw new HttpException(500,'提交信息已有变更！请刷新页面后重新提交');
                }
                if($type=='add'&&!empty($defaultSupplier)){
                    if($data['suppliercode'] == $defaultSupplier->supplier_code){
                        throw new HttpException(500,'备用供应商不能与默认供应商一致');
                    }
                }
                $oldQuotes = SupplierQuotes::find()->andFilterWhere(['id'=>$data['quoteId']])->one();
                if (isset($data['pur_ticketed_point']) && empty($data['pur_ticketed_point'])) {
                    unset($data['pur_ticketed_point']);
                }
                if(in_array('',$data)){
                    throw new HttpException(500,'请确认报价,供应商,采购链接无信息缺失！');
                }
                if(!empty($oldQuotes)&&$oldQuotes->product_sku !=$data['sku']){
                    throw new HttpException(500,'新报价与旧报价SKU不一致！');
                }
                
                $newQuote = self::saveQuotes($oldQuotes,$data);
                if( $newQuote == false){
                    throw new HttpException(500,'报价新增失败，请联系管理员！');
                }

                $model = new self;
                $model->sku                 =   $data['sku'];
                $model->old_quotes_id       =   $data['quoteId'];
                $model->old_supplier_code   =   !empty($oldQuotes) ? $oldQuotes->suppliercode : '';
                $model->old_product_num     =   !empty($oldQuotes) ? self::getProduct($oldQuotes->suppliercode) : 0;
                $model->new_quotes_id       =   $newQuote->attributes['id'];
                $model->new_product_num     =   self::getProduct($newQuote->suppliercode);
                $model->new_supplier_code   =   $data['suppliercode'];
                $model->create_user_id      =   Yii::$app->user->id;
                $model->create_user_name    =   Yii::$app->user->identity->username;
                $model->status              =   1;
                $model->create_time         =   date('Y-m-d H:i:s',time());
                $model->type                =   self::getUpdateType($oldQuotes,$newQuote,$type);
                $model->is_sample           =   1;
                $model->old_purchase_link   =   !empty($oldQuotes) ? $oldQuotes->supplier_product_address : '';
                $model->new_purchase_link   =   $data['link'];
                $model->remark              =   $remark;
                if($model->save() == false){
                    throw new HttpException(500,'申请失败！请联系管理员！');
                }
            }
            $tran->commit();
            return array('status'=>'success','message'=>'申请提交成功!','count'=>$count);
        }catch(HttpException $e){
            $tran->rollBack();
            return ['status'=>'error','message'=>$e->getMessage(),'count'=>$count];
        }
    }

    //通过采购单数据新增待审申请不拿样的数据
    public static function saveOrderApply($data){
        $tran = Yii::$app->db->beginTransaction();
        try{
            $updateExist = self::find()->andFilterWhere(['sku'=>$data['sku'],'status'=>1,'new_supplier_code'=>$data['suppliercode']])->one();
            if($updateExist){
                throw new HttpException(500,'已有待审数据');
            }
            $exist = self::find()->andFilterWhere(['sku'=>$data['sku'],'status'=>1])->andFilterWhere(['NOT IN','type',[5,6]])->one();
            if($exist){
                throw new HttpException(500,'已有待审数据');
            }
            $standbySupplier = ProductProvider::find()->select('supplier_code')->andFilterWhere(['sku'=>$data['sku'],'is_supplier'=>2])->all();
            $standbySupplierList = ArrayHelper::getValue($standbySupplier,'supplier_code');
            if(!empty($standbySupplierList)&&in_array($data['suppliercode'],$standbySupplierList)){
                throw new HttpException(500,'已是备用供应商');
            }
            $defaultSupplier = ProductProvider::find()->andFilterWhere(['sku'=>$data['sku'],'is_supplier'=>1])->one();
            if(!empty($defaultSupplier)&&$defaultSupplier->quotes_id !=$data['quoteId']){
                throw new HttpException(500,'提交信息已有变更！请刷新页面后重新提交');
            }
            $oldQuotes = SupplierQuotes::find()->andFilterWhere(['id'=>$data['quoteId']])->one();
//            if(in_array('',$data)){
//                throw new HttpException(500,'请确认报价,供应商,采购链接无信息缺失！');
//            }
            if(!empty($oldQuotes)&&$oldQuotes->product_sku !=$data['sku']){
                throw new HttpException(500,'新报价与旧报价SKU不一致！');
            }
            $newQuote = self::saveQuotes($oldQuotes,$data);
            if( $newQuote == false){
                throw new HttpException(500,'报价新增失败，请联系管理员！');
            }
            $model = new self;
            $model->sku                 =   $data['sku'];
            $model->old_quotes_id       =   $data['quoteId'];
            $model->old_supplier_code   =   !empty($oldQuotes) ? $oldQuotes->suppliercode : '';
            $model->old_product_num     =   !empty($oldQuotes) ? self::getProduct($oldQuotes->suppliercode) : 0;
            $model->new_quotes_id       =   $newQuote->attributes['id'];
            $model->new_product_num     =   self::getProduct($newQuote->suppliercode);
            $model->new_supplier_code   =   $data['suppliercode'];
            $model->create_user_id      =   User::findByUsername($data['buyer']) ? User::findByUsername($data['buyer'])->id : Yii::$app->user->id;
            $model->create_user_name    =   $data['buyer'];
            $model->status              =   1;
            $model->create_time         =   date('Y-m-d H:i:s',time());
            $model->type                =   6;
            $model->is_sample           =   2;
            $model->old_purchase_link   =   !empty($oldQuotes) ? $oldQuotes->supplier_product_address : '';
            $model->new_purchase_link   =   $data['link'];
            if($model->save() == false){
                throw new HttpException(500,'记录添加失败！请联系管理员！');
            }

            //表修改日志-新增
            $change_content = "insert:新增id值为{$model->id}的记录";
            $change_data = [
                'table_name' => 'pur_supplier_update_apply', //变动的表名称
                'change_type' => '1', //变动类型(1insert，2update，3delete)
                'change_content' => $change_content, //变更内容
            ];
            TablesChangeLog::addLog($change_data);
            $tran->commit();
            return $model->attributes['id'];
        }catch(HttpException $e){
            $tran->rollBack();
            return false;
        }
    }

    /**
     * 获取申请类型
     * @param $oldQuotes
     * @param $newQuote
     * @return int
     * @throws HttpException
     */
    public static function getUpdateType($oldQuotes,$newQuote,$type){
        if($type=='add'){
            //添加备用供应商
            return 5;
        }
        $suppliercodeCompare = !empty($oldQuotes) && ($oldQuotes->suppliercode == $newQuote->suppliercode) ? true : false;
        $priceCompare        = !empty($oldQuotes) && $oldQuotes->supplierprice == $newQuote->supplierprice
            && $oldQuotes->pur_ticketed_point == $newQuote->pur_ticketed_point ? true : false;
        $linkCompare         = !empty($oldQuotes) && ($oldQuotes->supplier_product_address == $newQuote->supplier_product_address) ? true : false;
        $pointCompare        = !empty($oldQuotes) && ($oldQuotes->pur_ticketed_point == $newQuote->pur_ticketed_point) ? true : false;
        if($suppliercodeCompare && $priceCompare && $linkCompare && $pointCompare){
            throw new HttpException(500,'当前没有修改任何数据！');
        }
        if(!$suppliercodeCompare&&!$priceCompare){
            //供应商和报价都修改
            return 3;
        }
        if(!$suppliercodeCompare&&$priceCompare){
            //供应商修改
            return 1;
        }
        if($suppliercodeCompare&&!$priceCompare){
            //单价修改
            return 2;
        }
        if($suppliercodeCompare&&$priceCompare&&!$linkCompare){
            //只有链接修改
            return 4;
        }
    }

    /**
     * 获取供应商状态不为0和7的sku数量即审核不通过和停售的
     * @param $supplier_code
     * @return int
     */
    public static function getProduct($supplier_code){
        //$datas  = new
        $datas = ProductProvider::find()->andFilterWhere(['supplier_code'=>$supplier_code,'is_supplier'=>1])->groupBy('sku')->joinWith('trueProduct');
        $query = new Query();
        $count = $query->from(['c'=>$datas])->select('count(*)')->scalar();
        return $count;
    }


    /**
     * 新增不显示的供应商报价
     * @param $newQuotes
     * Array
    (
    [quoteId] => 0
    [sku] => SJ00001-01
    [suppliercode] => A1685034725
    [price] =>10
    )
     * @return SupplierQuotes|bool
     */
    public static function saveQuotes($oldQuotes,$newQuotes){
        $model = new SupplierQuotes();
        $model->suppliercode                = $newQuotes['suppliercode'];
        $model->product_sku                 = $newQuotes['sku'];
        $model->supplierprice               = $newQuotes['price'];
        $model->pur_ticketed_point          = isset($newQuotes['pur_ticketed_point']) ? round($newQuotes['pur_ticketed_point'],2) : 0;
        $model->currency                    = !empty($oldQuotes) ? $oldQuotes->currency : 'RMB';
        $model->default_buyer               = !empty($oldQuotes) ? $oldQuotes->default_buyer : 1;
        $model->default_Merchandiser        = !empty($oldQuotes) ? $oldQuotes->default_Merchandiser : 1;
        $model->supplier_product_address    = $newQuotes['link'];
        $model->add_time                    = time();
        $model->add_user                    = Yii::$app->user->id;
        $model->status                      = 2;
        $model->default_vendor              = 1;
        
        if($model->save() == false){
            return false;
        }

        //表修改日志-新增
        $change_content = "insert:新增id值为{$model->id}的记录";
        $change_data = [
            'table_name' => 'pur_supplier_quotes', //变动的表名称
            'change_type' => '1', //变动类型(1insert，2update，3delete)
            'change_content' => $change_content, //变更内容
        ];
        TablesChangeLog::addLog($change_data);
        return $model;
    }

    /**
     * 申请审核通过
     * @param $applyIds
     */
    public static function checkApply($applyIds){
        $tran = Yii::$app->db->beginTransaction();
        try{
            foreach($applyIds as $id){
                $apply = self::find()->where(['id'=>$id])->one();
                if(empty($apply) || $apply->status != 1){
                    throw new  HttpException(500,'审核数据异常,请认真勾选复选框');
                }
                if(empty($apply->is_sample) || $apply->is_sample ==1){
                    throw new HttpException(500,'请等待是否拿样确认！');
                }
                if($apply->is_sample ==3 && $apply->qualityResult->qc_result == 1){
                    throw new HttpException(500,'请等待品控返回质检结果！');
                }
                if($apply->is_sample ==3 && $apply->qualityResult->qc_result == 3){
                    throw new HttpException(500,'样品不合格不能审核通过！');
                }
                $apply->status = 2;
                $apply->update_time = date('Y-m-d H:i:s',time());
                $apply->update_user_name = Yii::$app->user->identity->username;

                //表修改日志-更新
                $change_content = TablesChangeLog::updateCompare($apply->attributes, $apply->oldAttributes);
                $change_data = [
                    'table_name' => 'pur_supplier_update_apply', //变动的表名称
                    'change_type' => '2', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);

                if($apply->save() == false){
                    throw new HttpException(500,'审核状态更新失败');
                }
                self::updateQuotes($apply);
                self::saveDefaultSupplier($apply);
                
                //海外仓待询价需求变更供应商
                if ($apply->old_supplier_code != $apply->new_supplier_code) {
                    $demand_numbers = PlatformSummary::find()->where(['sku'=>$apply->sku,'demand_status'=>1,'purchase_type'=>2,'level_audit_status'=>1])
                        ->andWhere("agree_time > '2018-08-29 10:00:00'")->select('demand_number')->column();
                    if ($demand_numbers) {
                        PlatformSummary::updateAll(['supplier_code'=>$apply->new_supplier_code], ['in','demand_number',$demand_numbers]);

                        //修改采购单中的供应商
                        $pur_numbers = PurchaseDemand::find()->where(['in','demand_number',$demand_numbers])->select('pur_number')->column();
                        $supplierModel = Supplier::find()->where(['supplier_code'=>$apply->new_supplier_code])->select('supplier_name')->one();
                        $supplier_name = $supplierModel->supplier_name;
                        PurchaseOrder::updateAll(['supplier_code'=>$apply->new_supplier_code,'supplier_name'=>$supplier_name], ['in', 'pur_number', $pur_numbers]);
                        
                        $message = "变更供应商";
                        $message .= "<br>原供应商:".PurchaseOrderServices::getSupplierName($apply->old_supplier_code);
                        $message .= "<br>新供应商:".PurchaseOrderServices::getSupplierName($apply->new_supplier_code);
                        PurchaseOrderServices::writelog($demand_numbers, $message);
                    }
                }
            }
            $tran->commit();
            $result = array('status'=>'success','message'=>'审核通过成功！');
        }catch(HttpException $e){
            $tran->rollBack();
            $result = array('status'=>'error','message'=>$e->getMessage());
        }
        return $result;
    }

    /**
     * 更新报价状态
     * @param $apply
     */
    public static function updateQuotes($apply){
        $oldQuotes = SupplierQuotes::find()->where(['id'=>$apply->old_quotes_id])->one();
        $newQuotes = SupplierQuotes::find()->where(['id'=>$apply->new_quotes_id])->one();
        if(!empty($oldQuotes)){
            if($oldQuotes->status !=1){
                throw new HttpException(500,'原报价信息异常！请联系管理员！');
            }
            $oldQuotes->default_vendor = 1;
            $oldQuotes->status         = $apply->type == 5 ? 1 :2;
            if($oldQuotes->save() == false){
                throw new HttpException(500,'修改原报价信息失败！');
            }

            //表修改日志-更新
            $change_content = TablesChangeLog::updateCompare($oldQuotes->attributes, $oldQuotes->oldAttributes);
            $change_data = [
                'table_name' => 'pur_supplier_quotes', //变动的表名称
                'change_type' => '2', //变动类型(1insert，2update，3delete)
                'change_content' => $change_content, //变更内容
            ];
            TablesChangeLog::addLog($change_data);
        }
        if(empty($newQuotes)||$newQuotes->status!=2){
            throw new HttpException(500,'新报价信息异常！请联系管理员！');
        }
        $newQuotes->default_vendor = 1;
        $newQuotes->status = 1;
        $productInfo = Product::find()->select('tax_rate')->andFilterWhere(['sku'=>$newQuotes->product_sku])->one();
        $newQuotes->is_back_tax = Vhelper::getProductIsBackTax($productInfo->tax_rate, $newQuotes->pur_ticketed_point);
        
        //表修改日志-更新
        $change_content = TablesChangeLog::updateCompare($newQuotes->attributes, $newQuotes->oldAttributes);
        $change_data = [
            'table_name' => 'pur_supplier_quotes', //变动的表名称
            'change_type' => '2', //变动类型(1insert，2update，3delete)
            'change_content' => $change_content, //变更内容
        ];
        TablesChangeLog::addLog($change_data);
        if($newQuotes->save() == false){
            throw new HttpException(500,'修改新报价信息失败');
        }

        //-----------------------------即时更新到 采购需求、采购建议、ERP系统上去 Start-----------------------------------
        // 即时更新到 采购需求、采购建议、ERP系统上去
        $update_data = $push_data = [];
        $push_data['product_sku']       = $newQuotes->product_sku;
        if(empty($oldQuotes)||$newQuotes->suppliercode != $oldQuotes->suppliercode){
            // 修改采购建议的 供应商、
            $supplier = Supplier::findOne(['supplier_code' => $newQuotes->suppliercode]);
            $update_data['supplier_code'] = $newQuotes->suppliercode;
            $update_data['supplier_name'] = $supplier->supplier_name;

            $push_data['supplier_code']     = $newQuotes->suppliercode;

            // 子查询：查询需求是否已经生成采购单
            $subQuery = (new Query())
                ->select('d.demand_number')
                ->from('pur_purchase_demand as d')
                ->leftJoin('pur_purchase_order as o','d.pur_number = o.pur_number')
                ->where("d.demand_number=pur_platform_summary.demand_number")
                ->andwhere(['in','o.purchas_status',['3','5','6','7','8','9','99']]);

            // 查询需要修改供应商的需求
            $model_summary_list = PlatformSummary::find()
                ->where(['sku' => $newQuotes->product_sku])
                ->andWhere(['or',
                    ['and',['>=','create_time','2018-08-29 10:00:00'],['<=','demand_status',6]],
                    ['and',['>=','create_time','2018-08-29 10:00:00'],['>=','demand_status',14]],
                    ["not exists",$subQuery]
                ])
            ->all();

            if($model_summary_list){
                // 更新 未生成采购单的 采购需求数据的供应商
                foreach($model_summary_list as $model_value){
                    $model_value->supplier_code = $newQuotes->suppliercode;
                    $res =  $model_value->save();
                }
            }

            // 更新采购建议中的供应商（只更新当天的采购建议）
            PurchaseSuggestMrp::updateAll(['supplier_code' => $supplier->supplier_code,'supplier_name' => $supplier->supplier_name],
                                          "sku='{$newQuotes->product_sku}' AND created_at>='".date('Y-m-d 00:00:00')."'");
        }
        if(empty($oldQuotes)||$newQuotes->supplierprice != $oldQuotes->supplierprice){
            $update_data['price']      = $newQuotes->supplierprice;

            $push_data['product_cost'] = $newQuotes->supplierprice;

            // 修改 国内仓、待确认状态下采购单（采购计划单）
            $purchaseList = PurchaseOrder::find()
                ->select('p_o_i.id')
                ->from(PurchaseOrder::tableName().' AS p_o')
                ->innerJoin(PurchaseOrderItems::tableName() .' AS p_o_i',"p_o_i.pur_number=p_o.pur_number")
                ->where(['p_o.purchase_type' => 1])
                ->andWhere(['p_o.purchas_status' => 1])
                ->andWhere(['p_o_i.sku' => $newQuotes->product_sku])
                ->asArray()
                ->column();
            $push_data_order = ['price' => $newQuotes->supplierprice,'base_price' => $newQuotes->supplierprice];
            if($purchaseList){ PurchaseOrderItems::updateAll($push_data_order,['in','id', $purchaseList]);}
        }
        if($update_data){// 改变当天 未生成采购单的 采购建议
            $date = date('Y-m-d');
            PurchaseSuggestMrp::updateAll($update_data,"is_purchase='Y' AND state=0 AND sku='{$newQuotes->product_sku}' AND LEFT(created_at,10)='$date'");
        }
        \app\api\v1\models\Product::pushProductInfo($push_data);// 即时推送到ERP系统 @author Jolon @date 2018-10-15 17:25
        //-----------------------------即时更新到 采购需求、采购建议、ERP系统上去 End  -----------------------------------

        if ((empty($oldQuotes) && $newQuotes->pur_ticketed_point) || (!empty($oldQuotes) && $newQuotes->pur_ticketed_point != $oldQuotes->pur_ticketed_point)) {
            ProductTicketedPointLog::insertLog($newQuotes->product_sku, $newQuotes->pur_ticketed_point, $newQuotes->is_back_tax);
        }
    }

    /**
     * 更新默认供应商
     * @param $apply
     * @throws HttpException
     */
    public static function saveDefaultSupplier($apply){
        if($apply->type != 5){
            //表修改日志-更新
            $change_data = [
                'table_name' => 'pur_product_supplier', //变动的表名称
                'change_type' => '2', //变动类型(1insert，2update，3delete)
                'change_content' => "update:sku:{$apply->sku},type:=>1", //变更内容
            ];
            TablesChangeLog::addLog($change_data);

            ProductProvider::updateAll(['is_supplier'=>0],'sku = :sku AND is_supplier = :type',[':sku'=>$apply->sku,':type'=>1]);
        }
        $model  = ProductProvider::find()->where(['sku'=>$apply->sku,'supplier_code'=>$apply->new_supplier_code])->one();
        $flag = true;
        if(empty($model)){
            $flag = false;
            $model = new ProductProvider();
        }
        $model->sku           = $apply->sku;
        $model->supplier_code = $apply->new_supplier_code;
        $model->is_supplier   = $apply->type == 5 ? 2 :1;
        $model->is_exemption  = 1;
        $model->is_push       = 0;
        $model->is_push_to_erp= 0; 
        $model->quotes_id     = $apply->new_quotes_id;
        if ($flag) {
            //表修改日志-更新
            $change_content = TablesChangeLog::updateCompare($model->attributes, $model->oldAttributes);
            $change_data = [
                'table_name' => 'pur_product_supplier', //变动的表名称
                'change_type' => '2', //变动类型(1insert，2update，3delete)
                'change_content' => $change_content, //变更内容
            ];
            TablesChangeLog::addLog($change_data);
        }
        if($model->save() == false){
            throw new HttpException(500,'更新默认供应商失败！');
        }

        if (!$flag) {
            //表修改日志-新增
            $change_content = "insert:新增id值为{$model->id}的记录";
            $change_data = [
                'table_name' => 'pur_product_supplier', //变动的表名称
                'change_type' => '1', //变动类型(1insert，2update，3delete)
                'change_content' => $change_content, //变更内容
            ];
            TablesChangeLog::addLog($change_data);
        }
    }

    public static function integratApply($datas){
        try{
            if(empty($datas)){
                throw new HttpException(500,'数据有误！');
            }
            $ids = explode(',',$datas);
            foreach($ids as $id){
                $applyData = self::find()->where(['id'=>$id])->one();
                if(empty($applyData)||$applyData->status !=2 || $applyData->integrat_status !=1){
                    throw new HttpException(500,'申请数据有误！');
                }
                $userId = Yii::$app->authManager->getUserIdsByRole('供应链');
                if(!in_array($applyData->create_user_id,$userId)){
                    continue;
                }
                $applyData->integrat_status = 2;
                $applyData->integrat_time   = date('Y-m-d H:i:s',time());
                $applyData->integrat_user_name = Yii::$app->user->identity->username;
                if($applyData->save() == false){
                    throw new HttpException(500,'整合错误！');
                }
            }
            $result = ['status'=>'success','message'=>'整合操作成功'];
        }catch(HttpException $e){
            $result = ['status'=>'error','message'=>$e->getMessage()];
        }
        return $result;
    }

    public static function sample($id,$type){
        $tran = Yii::$app->db->beginTransaction();
        try{
            if(empty($id) || empty($type)){
                throw new HttpException(500,'缺少必要数据！请联系管理员！');
            }
            $apply = SupplierUpdateApply::find()->where(['id'=>$id])->one();
            if(empty($apply) || $apply->is_sample != 1){
                throw new HttpException(500,'当前申请数据异常！');
            }
            if($apply->create_user_id != Yii::$app->user->id){
                throw new HttpException(500,'当前申请不属于当前登录用户');
            }
            switch($type){
                case 'sample':
                    $apply->is_sample = 3;
                    $model = new SampleInspect();
                    $model->apply_id        = $id;
                    $model->sku             = $apply->sku;
                    $model->product_num     = 1;
                    if($model->save() == false){
                        throw new HttpException(500,'样品检测写入失败！');
                    }
                    break;
                case 'sampleno':
                    $apply->is_sample = 2;
                    break;
                default:
                    $apply->is_sample = 1;
            }
            if($apply->save() == false){
                throw new HttpException(500,'拿样操作失败！');
            }
            $tran->commit();
            $sampleResult = ['status'=>'success','message'=>'操作成功'];
        }catch(HttpException $e){
            $tran->rollBack();
            $sampleResult = ['status'=>'error','message'=>$e->getMessage()];
        }
        return$sampleResult;
    }

    /**
     * 根据applyIds 获取pur_purchase_order的pur_number
     * @param $applyIds
     * @return sting
     */
    public static function getPurNumber($applyIds){
        try{
            foreach ($applyIds as $id) {
                $purnumberCount = PurchaseOrderItems::find()
                              ->from(PurchaseOrderItems::tableName().' as i')
                              ->leftJoin('pur_supplier_update_apply as a',' a.sku=i.sku')
                              ->leftJoin('pur_purchase_order as o',' i.pur_number=o.pur_number AND o.purchase_type=1')
                              ->where(['a.id'=>$id, 'o.purchas_status' =>2])->count('o.pur_number');
                if($purnumberCount) {
                    $skuData = self::findone($id);
                    if($skuData){
                        $ng_sku = $skuData->sku;
                    }
                    throw new  HttpException(500,$ng_sku.'还有待审核的采购计划单,计划单审核完成后才能更新单价');
                }
            }

            $purnumberCount = array('status'=>'success','message'=>'审核通过成功！');
        }catch(HttpException $e){
            $purnumberCount = array('status'=>'error','message'=>$e->getMessage());
        }

        return $purnumberCount;
    }

}
