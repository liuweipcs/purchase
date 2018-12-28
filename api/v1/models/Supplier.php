<?php

namespace app\api\v1\models;


use app\models\SupplierProductLine;
use app\services\CommonServices;
use app\services\SupplierServices;
use Yii;
use yii\behaviors\TimestampBehavior;
use app\config\Vhelper;

use app\api\v1\models\SupplierContactInformation;
/**
 * This is the model class for table "{{%supplier}}".
 *
 * @property integer $id
 * @property string $suppliercode
 * @property integer $buyer
 * @property integer $merchandiser
 * @property integer $maincategory
 * @property string $suppliername
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
 */
class Supplier extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%supplier}}';
    }


    /**
     * 关联支付方式
     * @return \yii\db\ActiveQuery
     */
    public function getPay()
    {

        return $this->hasMany(SupplierPaymentAccount::className(), ['supplier_id' => 'id']);
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
     * 通过code获取供应商名;
     * @param $code
     * @return false|null|string
     */
    public static function getSupplierName($code)
    {
        return self::find()->select(['supplier_name'])->where(['supplier_code'=>$code])->scalar();
    }

    /**
     *
     * @param mixed $datass
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function FindOnes($datass)
    {
        //Vhelper::dump($datass);
        foreach ($datass as $k=>$v)
        {
            $model= self::find()->where(['like','supplier_name',$v->provider_company])->one();


            if ($model)
            {
                self::SaveOne($model,$v);
                $data['success_list'][$k]['supplier_code']          = $v->provider_company;
                $data['failure_list'][]                             = '';
            } else {

                $model =new self;
                self::SaveOne($model,$v);
                $data['success_list'][$k]['supplier_code']          = $model->attributes['supplier_name'];
                $data['failure_list'][]                             = '';
            }
        }

        return $data;


    }
    /**
     * 新增数据
     * @param $model
     * @param $datass
     * @return mixed
     */
    public  static function SaveOne($model,$datass)
    {
        //Vhelper::dump($datass);
        $transaction=\Yii::$app->db->beginTransaction();
        try {
            $model->buyer                       = 1;
            $model->merchandiser                = 1;
            $model->main_category               = $datass->providercategory;
            $model->supplier_name               = $datass->provider_company;
            $model->supplier_level              = 1;
            $model->supplier_type               = $datass->provider_type;
            $model->supplier_settlement         = $datass->provider_settlement_type >=14 ? 1:$datass->provider_settlement_type;
            $model->payment_method              = 1;
            $model->cooperation_type            = 1;
            $model->payment_cycle               = 1;
            $model->transport_party             = 2;
            $model->product_handling            = 1;
            $model->commission_ratio            = 1;
            $model->purchase_amount             = 1;
            $model->create_id                   = 1;
            $model->update_id                   = 1;
            $model->status                      = 1;
            $model->esupplier_name              = $datass->provider_company;
            $model->contract_notice             = $datass->contact_note;
            $model->province                    = 6;
            $model->city                        = 77;
            $model->area                        = 709;
            $model->source                      = 1;
            $model->is_push                     = 1;
            $model->business_scope              = 1;
            $model->store_link                  = $datass->provider_website;
            $model->create_time                 = ($datass->create_time AND $datass->create_time != '0000-00-00 00:00:00')?strtotime($datass->create_time):0;
            $model->update_time                 = ($datass->modify_time AND $datass->modify_time != '0000-00-00 00:00:00')?strtotime($datass->modify_time):0;
            $model->supplier_address            = $datass->provider_detail_address;
            $model->business_scope              = $datass->provider_description;
            $model->supplier_code               = !empty($model->supplier_code)?$model->supplier_code:CommonServices::getNumber('QS');
            if($model->save(false))
            {
                // 供应商修改记录
                \app\models\SupplierLog::saveSupplierLog('supplier::SaveOne',json_decode($datass),false,$model->supplier_name,$model->supplier_code);

                $contanName = !empty($datass->contact_name)?$datass->contact_name:'无';
                $model_cont  = SupplierContactInformation::find(['contact_person'=>$contanName,'supplier_id'=>$model->attributes['id']])->one();
                if(empty($model_cont)){
                $model_cont  =new  SupplierContactInformation;
                }
                $model_cont->contact_person           =   !empty($datass->contact_name)?$datass->contact_name:'无';
                $model_cont->supplier_id              =   $model->attributes['id'];
                $model_cont->contact_number           =   !empty($datass->contact_phone)?$datass->contact_phone:'无';
                $model_cont->contact_fax              =   !empty($datass->contact_fax)?$datass->contact_fax:'无';
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
                if(!empty($datass->provider_productlinelistfirst)){
                    $exist = SupplierProductLine::find()
                        ->where(['supplier_code'=>$model->attributes['supplier_code'],'first_product_line'=>$datass->provider_productlinelistfirst,'status'=>1])
                        ->exists();
                    if(!$exist){
                        $supplierLine = new SupplierProductLine();
                        $supplierLine->first_product_line = $datass->provider_productlinelistfirst;
                        $supplierLine->supplier_code      = $model->attributes['supplier_code'];
                        $supplierLine->save();
                    }
                }
            }
            //SupplierContactInformation::SaveOne($datass['contact'],$model->attributes['id'],$model->attributes['supplier_code']);
             $transaction->commit();
            return $model->attributes['supplier_code'];

            //return $status;
        } catch (Exception $e) {
            $transaction->rollback();
            return false;
        }
    }

    /**
     * 保存通途供应商数据
     */
    public static function SaveTongTool($data)
    {
        $transaction=\Yii::$app->db->beginTransaction();
        try {

                foreach ($data as $k => $datass) {
                    $models = self::find()->where(['supplier_name' => $datass['corporationFullname']])->one();
                    if ($models) {

                    } else {
                        $model                      = new self;
                        $model->buyer               = 1;
                        $model->merchandiser        = 1;
                        $model->main_category       = 1;
                        $model->supplier_name       = $datass['corporationFullname'];
                        $model->supplier_level      = 1;
                        $model->supplier_type       = 3;
                        $model->supplier_settlement = !empty($datass['clearingForm']) ? SupplierServices::getPayMe($datass['clearingForm']) : '1';
                        $model->payment_method      = !empty($datass['paymentMode']) ? SupplierServices::getPaymentPlatforms($datass['paymentMode']) : '1';
                        $model->cooperation_type    = 1;
                        $model->payment_cycle       = 1;
                        $model->transport_party     = 1;
                        $model->product_handling    = 1;
                        $model->commission_ratio    = 1;
                        $model->purchase_amount     = 10;
                        $model->create_id           = 1;
                        $model->update_id           = 1;
                        $model->status              = 1;
                        $model->esupplier_name      = 'no';
                        $model->contract_notice     = $datass['description'];
                        //区域
                        $model->province = 6;
                        $model->city     = 77;
                        $model->area     = 709;
                        //来源于通途
                        $model->source           = 3;
                        $model->is_push          = 1;
                        $model->create_time      = time();
                        $model->update_time      = time();
                        $model->supplier_code    = CommonServices::getNumber('QS');
                        $model->supplier_address = $datass['detailAddress'];
                        if ($model->save(false)) {
//支付子表
                            $model_pay                          = new SupplierPaymentAccount();
                            $model_pay->supplier_id             = $model->attributes['id'];
                            $model_pay->payment_method          = 1;
                            $model_pay->payment_platform        = !empty($datass['paymentMode']) ? SupplierServices::getPaymentPlatforms($datass['paymentMode']) : '1';
                            $model_pay->account                 = !empty($datass['payeeAccount']) ? $datass['payeeAccount'] : '1';
                            $model_pay->account_name            = !empty($datass['accountName']) ? $datass['accountName'] : 'no';
                            $model_pay->status                  = 1;
                            $model_pay->payment_platform_branch = $datass['bank'];
                            $model_pay->supplier_code           = $model->attributes['supplier_code'];
                            $model_pay->save(false);

                            //联系方式子表
                            $model_content                          = new SupplierContactInformation();
                            $model_content->supplier_id             = $model->attributes['id'];
                            $model_content->contact_person          = !empty($datass['linkman']) ? $datass['linkman'] : 'no';
                            $model_content->contact_number          = !empty($datass['telephone']) ? $datass['telephone'] : 'no';
                            $model_content->contact_fax             = $datass['faxNumber'];
                            $model_content->chinese_contact_address = '';
                            $model_content->english_address         = '';
                            $model_content->contact_zip             = $datass['postalCode'];
                            $model_content->qq                      = $datass['qqNumber'];
                            $model_content->micro_letter            = '';
                            $model_content->want_want               = $datass['wwNumber'];
                            $model_content->skype                   = '';
                            $model_content->supplier_code           = $model->attributes['supplier_code'];
                            $model_content->save(false);
                        }


                    }
                }
                 return $transaction->commit();

        } catch (Exception $e) {
               $transaction->rollback();

        }
    }

}


