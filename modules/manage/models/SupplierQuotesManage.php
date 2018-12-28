<?php

namespace app\modules\manage\models;

use app\models\Product;
use app\models\ProductProvider;
use app\models\Supplier;
use app\models\SupplierQuotes;
use Yii;
use yii\db\Exception;

/**
 * This is the model class for table "pur_supplier_quotes_manage".
 *
 * @property integer $id
 * @property integer $type
 * @property integer $is_in_stock
 * @property string $delivery_time
 * @property string $reason
 * @property string $create_time
 * @property string $create_supplier_code
 * @property string $create_user_ip
 * @property string $update_time
 * @property string $update_supplier_code
 * @property string $update_user_ip
 * @property integer $status
 * @property string $sku
 * @property integer $is_sample
 * @property integer $check_result
 * @property string $check_reason
 * @property integer $sample_result
 * @property string $sample_reason
 * @property string $check_time
 * @property integer $check_user_id
 * @property string $check_user_ip
 */
class SupplierQuotesManage extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_supplier_quotes_manage';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'is_in_stock', 'status','is_sample','check_result','sample_result','check_user_id'], 'integer'],
            [['delivery_time'], 'number'],
            [['reason'], 'string'],
            [['create_time', 'update_time','check_time'], 'safe'],
            [['create_supplier_code', 'create_user_ip', 'update_supplier_code', 'update_user_ip','check_user_ip'], 'string', 'max' => 100],
            [['sku'], 'string', 'max' => 150],
            [['sample_reason','check_reason'], 'string', 'max' => 500],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'is_in_stock' => 'Is In Stock',
            'delivery_time' => 'Delivery Time',
            'reason' => 'Reason',
            'create_time' => 'Create Time',
            'create_supplier_code' => 'Create Supplier Code',
            'create_user_ip' => 'Create User Ip',
            'update_time' => 'Update Time',
            'update_supplier_code' => 'Update Supplier Code',
            'update_user_ip' => 'Update User Ip',
            'status' => 'Status',
            'sku' => 'Sku',
            'is_sample' => 'Is Sample' ,
            'check_result'=> 'Check Result',
            'check_reason' => 'Check Reason',
            'sample_result' => 'Sample Result',
            'sample_reason' =>'Sample Reason',
        ];
    }

    public function getProduct(){
        return $this->hasOne(Product::className(),['sku'=>'sku']);
    }

    public function getQuotesItems(){
        return $this->hasMany(SupplierQuotesManageItems::className(),['supplier_quotes_id'=>'id'])->where(['pur_supplier_quotes_manage_items.status'=>1]);
    }

    public function getManageSupplier(){
        return $this->hasOne(Supplier::className(),['supplier_code'=>'create_supplier_code']);
    }

    //提交报价审核操作
    public static function saveCheckResult($datas){
        try{
            if(empty($datas['id'])||empty($datas['check_result'])){
                throw new Exception('关键数据为空');
            }
            if($datas['check_result']==2&&empty($datas['check_reason'])){
                throw new Exception('审核不通过必须填写原因');
            }
            $model = self::find()->where(['id'=>$datas['id']])->one();
            if(empty($model)){
                throw new Exception('报价数据不存在');
            }
            if($model->status !=0){
                throw new Exception('当前数据已经开始审核');
            }
            $model->is_sample = $datas['is_sample'];
            $model->check_result = $datas['check_result'];
            $model->check_reason = $datas['check_reason'];
            $model->check_time = date('Y-m-d H:i:s',time());
            $model->check_user_id = Yii::$app->user->id;
            $model->check_user_ip = Yii::$app->request->userIP;
            $model ->status =self::getApplyStatus($datas['check_result'],$datas['is_sample'],0,0);
            if($model->save()==false){
                throw new Exception('审核数据保存失败');
            }
            $response = ['status'=>'success','message'=>'审核成功'];
        }catch (Exception $e){
            $response = ['status'=>'error','message'=>$e->getMessage()];
        }
        return $response;
    }

    //拿样确认操作
    public static function sampleCommit($id){
        try{
            $quotesDatas = self::find()->where(['id'=>$id])->one();
            if(empty($quotesDatas)){
                throw new Exception('当前报价审核信息不存在！');
            }
            if($quotesDatas->status !=1){
                throw new Exception('当前报价状态无法进行拿样确认');
            }
            if($quotesDatas->is_sample !=1){
                throw new Exception('不拿样报价无法确认拿样');
            }
            $quotesDatas->status = self::getApplyStatus($quotesDatas->check_result,$quotesDatas->is_sample,0,$quotesDatas->status);
            if($quotesDatas->save()==false){
                throw new Exception('状态变更失败');
            }
            $response = ['status'=>'success','message'=>'拿样确认成功'];
        }catch (Exception $e){
            $response = ['status'=>'error','message'=>$e->getMessage()];
        }
        return $response;
    }

    //样品结果确认
    public static function saveSampleResult($datas){
        try{
            if(empty($datas['id'])||empty($datas['sample_result'])){
                throw new Exception('关键数据为空');
            }
            $model = self::find()->where(['id'=>$datas['id']])->one();
            if(empty($model)||$model->status!=2){
                throw new Exception('数据异常');
            }
            $model->sample_result = $datas['sample_result'];
            $model->sample_reason = isset($datas['sample_reason']) ? $datas['sample_reason'] : '';
            $model->status        = self::getApplyStatus($model->check_result,$model->is_sample,$datas['sample_result'],$model->status);
            if($model->save()==false){
                throw new Exception('状态变更失败');
            }
            $response = ['status'=>'success','message'=>'提交样品检验结果成功！'];
        }catch (Exception $e){
            $response = ['status'=>'error','message'=>$e->getMessage()];
        }
        return $response;
    }

    //获取报价申请更新状态
    public static function getApplyStatus($check_result,$is_sample,$sample_result,$now_status){
        //0待审核1,待拿样2,样品检测中,3,已取消，4完成，5审核失败，6样品检测失败
        $status=0;
        switch ($now_status){
            case 0 :
                switch ($check_result){
                    case 1:
                        switch ($is_sample){
                            case 1:
                                $status = 1;
                                break;
                            case 2:
                                $status = 4;
                                break;
                        }
                        break;
                    case 2:
                        $status=5;
                        break;
                }
                break;
            case 1 :
                $status = 2;
                break;
            case 2 :
                switch ($sample_result){
                    case 1 :
                        $status = 4;
                        break;
                    case 2 :
                        $status = 6;
                        break;
                }
                break;
            default :
                $status=$now_status;
        }
        return $status;
    }

    public static function getCompareDatas($skus,$supplier_code){
        $skuQuotesDatas = [];
        foreach ($skus as $sku){
            foreach ($supplier_code as $code){
                $skuQuotesDatas[$sku][$code]=self::find()->where(['sku'=>$sku,'create_supplier_code'=>$code])->orderBy('create_time DESC')->one();
            }
        }
        return $skuQuotesDatas;
    }
}
