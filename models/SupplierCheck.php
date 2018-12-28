<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
use yii\web\HttpException;

/**
 * This is the model class for table "pur_supplier_check".
 *
 * @property integer $id
 * @property string $supplier_code
 * @property string $contact_person
 * @property string $phone_number
 * @property string $contact_address
 * @property integer $check_times
 * @property string $apply_user_name
 * @property integer $apply_user_id
 * @property string $check_time
 * @property string $check_message
 * @property integer $check_reason
 * @property string $check_result
 * @property string $check_danger
 * @property string $create_time
 * @property string $supplier_name
 * @property string $pur_number
 */
class SupplierCheck extends BaseModel
{
    public $check_user;
    public $type;
    public $apply_start_time;
    public $apply_end_time;
    public $check_start_time;
    public $check_end_time;
    public $sku;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_supplier_check';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['check_times', 'apply_user_id','status','check_type','judgment_results'], 'integer'],
            [['check_time', 'create_time'], 'safe'],
            [['check_message', 'check_result', 'check_danger','check_code','check_reason','evaluate','improvement_measure'], 'string'],
            [['supplier_code'], 'string', 'max' => 50],
            [['supplier_code','supplier_name','contact_person', 'phone_number', 'contact_address', 'apply_user_name'], 'string', 'max' => 255],
            [['pur_number'],'string','max'=>500],
        ];
    }

    public function getSupplier(){
        return $this->hasOne(Supplier::className(),['supplier_code'=>'supplier_code']);
    }

    public function getCheckUser(){
        return $this->hasMany(SupplierCheckUser::className(),['check_id'=>'id'])->where(['pur_supplier_check_user.status'=>1])->asArray();
    }

    public function getReport(){
        return $this->hasOne(SupplierCheckUpload::className(),['check_id'=>'id'])->where(['status'=>1,'type'=>1]);
    }

    public function getGoodsReport(){
        return $this->hasOne(SupplierCheckUpload::className(),['check_id'=>'id'])->where(['status'=>1,'type'=>3]);
    }

    public function getCheckPur(){
        return $this->hasMany(SupplierCheckSku::className(),['check_id'=>'id'])->orderBy('id ASC')->where(['pur_supplier_check_sku.status'=>0]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'supplier_code' => 'Supplier Code',
            'contact_person' => 'Contact Person',
            'phone_number' => 'Phone Number',
            'contact_address' => 'Contact Address',
            'check_times' => 'Check Times',
            'apply_user_name' => 'Apply User Name',
            'apply_user_id' => 'Apply User ID',
            'check_time' => 'Check Time',
            'check_message' => 'Check Message',
            'check_reason' => 'Check Reason',
            'check_result' => 'Check Result',
            'check_danger' => 'Check Danger',
            'create_time' => 'Create Time',
            'supplier_name' => 'Supplier Name',
            'pur_number' => 'Pur Number',
        ];
    }

    public function getCheckType(){
        $array = [
            1=>'验厂',
            2=>'验货'
        ];
        return $array[$this->check_type];
    }
    public function saveApply($model,$params,$applyStatus=null){
        $tran = Yii::$app->db->beginTransaction();
        try {
            if($model->isNewRecord){
                if ((!isset($params['supplier_code']) || empty($params['supplier_code']))&&empty($params['supplier_name'])) {
                    throw new HttpException(500, '供应商不能为空');
                }
            }
            $model->supplier_code = $model->isNewRecord &&isset($params['supplier_code']) ? $params['supplier_code'] : $model->supplier_code;
            $model->supplier_name = $model->isNewRecord &&isset($params['supplier_name']) ? trim($params['supplier_name']) : $model->supplier_name;
            $model->check_code = $model->isNewRecord ? date('YmdHis').rand(10,99) : $model->check_code;
            $model->contact_person = isset($params['contact_person']) ? $params['contact_person'] : '';
            $model->contact_address = isset($params['contact_address']) ? $params['contact_address'] : '';
            $model->check_type = isset($params['check_type']) ? $params['check_type'] : '';
            $model->phone_number = isset($params['phone_number']) ? $params['phone_number'] : '';
            $model->check_times = $model->isNewRecord&&isset($params['check_times']) ? $params['check_times'] : $model->check_times;
            $model->check_reason = isset($params['check_reason']) ?$params['check_reason']: '';
            $model->is_urgent   = isset($params['is_urgent']) ? $params['is_urgent'] :0;
            $model->create_time = $model->isNewRecord ? date('Y-m-d H:i:s', time()) : $model->create_time;
            $model->apply_user_name = $model->isNewRecord ? Yii::$app->user->identity->username : $model->apply_user_name;
            $model->apply_user_id = $model->isNewRecord ? Yii::$app->user->id : $model->apply_user_id;
            $model->pur_number = isset($params['pur_number']) ? $params['pur_number'] : '';
            $model->group = isset($params['group']) ? $params['group'] : 0;
            $model->order_type = isset($params['order_type']) ? $params['order_type'] : 1;
            $model->expect_time = isset($params['expect_time']) ? $params['expect_time'] : $model->expect_time;
            $model->status = !empty($applyStatus) ? $applyStatus : (($model->isNewRecord ||empty($model->status)) ? 1 :$model->status);
            if ($model->save() == false) {
                throw new HttpException(500, '申请失败');
            }

            if(isset($params['items'])){
                SupplierCheckSku::updateAll(['status'=>1],['check_id'=>$model->attributes['id']]);
                if(isset($params['check_type'])&&$params['check_type']==2){
                if(!empty($params['items'])){
                    foreach ($params['items'] as $v){
                        if(!isset($v['sku'])||empty($v['sku'])){
                            continue;
                        }
                        $saveStatus = SupplierCheckSku::saveData($v,$model->attributes['id']);
                        if(!$saveStatus){
                            throw new HttpException(500,'sku信息插入失败');
                        }
                    }
                }
                }
            }
            $response = ['status'=>'success','message'=>'操作成功'];
            $tran->commit();
        }catch (HttpException $e){
            $response = ['status'=>'error','message'=>$e->getMessage()];
            $tran->rollBack();
        }
        return $response;
    }
    /**
     * 以采购员为维度的验厂验货数量-
     */
    public static function getSupplierCheck($check_user_name, $start_time, $end_time)
    {
        return self::find()
            ->joinWith('checkUser')
            ->where(['pur_supplier_check_user.check_user_name' =>$check_user_name])
            ->andWhere(['pur_supplier_check.status' =>3])
            ->andWhere(['pur_supplier_check.check_type' =>1])
            ->andWhere(['between','pur_supplier_check.check_time',$start_time,$end_time])
            ->count();
    }

    /*
     * params 根据sku获取去除固定前缀后缀得到采购数量和sku数组
     */
    public static function getExportSkus($array){
        $result = [];
        foreach ($array as $sku=>$value){
            $fatherSku  = SkuBindInfo::find()->select('father_sku')->where(['child_sku'=>$sku])->scalar();
            if(!$fatherSku){
                $fatherSku = $sku;
            }
            $result[$fatherSku]['sku'][]=$sku;
            $result[$fatherSku]['purchase_num'] = isset($result[$fatherSku]['purchase_num']) ?  $result[$fatherSku]['purchase_num'] +  $value :  $value;
        }
        return $result;
    }

    /*
     * 根据采购单类型和采购数量，
     * params $type 采购单类型 $purchaseNum sku采购数量
     * return array ['quality_random'=>质检标准,'quality_level'=>质检等级,'sampleNum'=>抽检数量,'ac'=>合格数量,'re'=>不合格标准]
     */
    public static function getSkuAql($type,$purchaseNum){
        $sampleCodeArray = [
            '1'=>['quality_random'=>'S-4','quality_level'=>1.5],
            '2'=>['quality_random'=>'Ⅱ','quality_level'=>2.5],
            '3'=>['quality_random'=>'Ⅱ','quality_level'=>1.5],
        ];

        $quality_random = $sampleCodeArray[$type]['quality_random'];
        $quality_level = $sampleCodeArray[$type]['quality_level'];
        $sampleCode = SampleRule::find()->select('sample_code')
            ->where('min_num <= :num and max_num>= :num and type=:type and status=0 and quality_random = :quality_random',
                [':num'=>$purchaseNum,':type'=>$type,':quality_random'=>$quality_random])->scalar();
        $sampleCode = $sampleCode ? $sampleCode : '';
        $sampleNum = SampleCode::find()->select('sample_num')->where(['sample_code'=>$sampleCode,'type'=>$type,'aql'=>$quality_level])->scalar();
        $ac = SampleCode::find()->select('ac_num')->where(['sample_code'=>$sampleCode,'type'=>$type,'aql'=>$quality_level])->scalar();
        $ac = $ac ? $ac : 0;
        $re = SampleCode::find()->select('re_num')->where(['sample_code'=>$sampleCode,'type'=>$type,'aql'=>$quality_level])->scalar();
        $re = $re ? $re : 0;
        return ['quality_random'=>$quality_random,'quality_level'=>$quality_level,'sampleNum'=>$sampleNum,'ac'=>$ac,'re'=>$re];

    }
    public static function copyCheckData($lastModel,$price,$reason){
        $model= new self();
        $tran = Yii::$app->db->beginTransaction();
        try {
            if($model->isNewRecord){
                if ((!isset($lastModel->supplier_code) || empty($lastModel->supplier_code))&&empty($lastModel->supplier_name)) {
                    throw new HttpException(500, '供应商不能为空');
                }
            }
            $model->supplier_code = $lastModel->supplier_code;
            $model->supplier_name = $lastModel->supplier_name;
            $model->check_code = $lastModel->check_code;
            $model->contact_person = !empty($lastModel->contact_person)? $lastModel->contact_person : '';
            $model->contact_address = !empty($lastModel->contact_address)? $lastModel->contact_address : '';
            $model->check_type = !empty($lastModel->check_type)? $lastModel->check_type : '';
            $model->phone_number = !empty($lastModel->phone_number)? $lastModel->phone_number : '';
            $model->check_times = !empty($lastModel->check_times)? $lastModel->check_times : 1;
            $model->is_urgent   = !empty($lastModel->is_urgent)? $lastModel->is_urgent : 0;
            $model->create_time = !empty($lastModel->create_time) ? $lastModel->create_time : date('Y-m-d H:i:s',time());
            $model->apply_user_name = !empty($lastModel->apply_user_name) ? $lastModel->apply_user_name : Yii::$app->user->identity->username;
            $model->apply_user_id = !empty($lastModel->apply_user_id) ? $lastModel->apply_user_id : Yii::$app->user->id;
            $model->pur_number = !empty($lastModel->pur_number) ? $lastModel->pur_number : '';
            $model->group = !empty($lastModel->group) ? $lastModel->group : 0;
            $model->order_type = !empty($lastModel->order_type) ? $lastModel->order_type : 1;
            $model->times      = !empty($lastModel->times) ? $lastModel->times+1 : 1;
            $model->is_check_again      = 1;
            $model->review_reason = $reason;
            $model->check_price   = $price;
            $model->status  =6;
            if ($model->save() == false) {
                throw new HttpException(500, '复制申请失败'.implode(',',$model->getFirstErrors()));
            }
            if(!empty($lastModel->checkPur)){
                SupplierCheckSku::updateAll(['status'=>1],['check_id'=>$model->attributes['id']]);
                if(!empty($model->check_type)&&$model->check_type==2){
                    if(!empty($lastModel->checkPur)){
                        foreach ($lastModel->checkPur as $v){
                            if(empty($v->sku)){
                                continue;
                            }
                            $saveStatus = SupplierCheckSku::saveData($v->attributes,$model->attributes['id']);
                            if(!$saveStatus){
                                throw new HttpException(500,'新申请sku信息插入失败');
                            }
                        }
                    }
                }
            }
            $response = ['status'=>'success','message'=>'操作成功'];
            $tran->commit();
        }catch (HttpException $e){
            $response = ['status'=>'error','message'=>$e->getMessage()];
            $tran->rollBack();
        }
        return $response;
    }
}
