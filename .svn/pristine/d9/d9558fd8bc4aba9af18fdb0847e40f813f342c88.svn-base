<?php

namespace app\api\v1\models;

use app\services\CommonServices;
use Yii;

/**
 * This is the model class for table "{{%platform_summary}}".
 *
 * @property integer $id
 * @property string $sku
 * @property string $platform_number
 * @property string $product_name
 * @property integer $purchase_quantity
 * @property string $purchase_warehouse
 * @property string $transit_warehouse
 * @property integer $is_transit
 * @property string $create_id
 * @property string $create_time
 */
class PlatformSummary extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%platform_summary}}';
    }


    public function getDemand()
    {
        return $this->hasOne(PurchaseDemand::className(), ['demand_number' => 'demand_number']);
    }

    public static function saveSuggestSummary($datas){
        $success = [];
        $AFN_types = [1,57,15272,16167,16169];// 物流方式为空运
        foreach ($datas as $v){
            $model = self::find()->where(['suggest_id'=>$v->id])->one();
            $name = ProductDescription::find()->select('title')->where(['sku'=>$v->sku,'language_code'=>'Chinese'])->scalar();
            if(!$name){
                continue;
            }
            $defaultSupplier = ProductProvider::find()->select('supplier_code')->where(['sku'=>$v->sku,'is_supplier'=>1])->scalar();
            if(!$defaultSupplier){
                continue;
            }
            if(!$model){
                $model = new self();
                $model->sku = $v->sku;
                $model->platform_number = $v->platform_number;
                $model->product_name  = $name ? $name : '';
                $model->purchase_quantity = $v->purchase_quantity;
                $model->purchase_warehouse = $v->purchase_warehouse;
                $model->transit_warehouse = in_array($v->transport_style,$AFN_types) ? 'AFN' : 'shzz';
                $model->is_transit = 2;
                $model->create_id = 'admin';
                $model->sales_note = 'erp采购建议';
                $model->create_time = date('Y-m-d H:i:s',time());
                $model->purchase_type = 2;
                $model->transit_number = $v->purchase_quantity;
                $category = Product::find()->select('product_category_id')->where(['sku'=>$v->sku])->scalar();
                $model->product_category = $category ? $category : '';
                $model->transport_style = self::getTransportType($v->transport_style);
                $model->demand_number = CommonServices::getNumber('RD');
                $model->level_audit_status=0;
                $model->source = 2;
                $model->suggest_id = $v->id;
                $model->ship_code = $v->ship_code ;
                $model->company   = $v->company;
                $model->country   = $v->country;
                $model->push_to_erp = 0;
                if($model->save()){
                    $success[]=$v->id;
                }
            }elseif(in_array($model->level_audit_status,[0,6])){
                $model->sku = $v->sku;
                $model->platform_number = $v->platform_number;
                $model->product_name  = $name ? $name : '';
                $model->purchase_quantity = $v->purchase_quantity;
                $model->purchase_warehouse = $v->purchase_warehouse;
                $model->transit_warehouse = in_array($v->transport_style,$AFN_types) ? 'AFN' : 'shzz';
                $model->is_transit = 2;
                $model->create_id = 'admin';
                $model->sales_note = 'erp采购建议';
                $model->create_time = date('Y-m-d H:i:s',time());
                $model->purchase_type = 2;
                $model->transit_number = $v->purchase_quantity;
                $category = Product::find()->select('product_category_id')->where(['sku'=>$v->sku])->scalar();
                $model->level_audit_status=0;
                $model->product_category = $category ? $category : '';
                $model->transport_style = self::getTransportType($v->transport_style);
                $model->source = 2;
                $model->suggest_id = $v->id;
                $model->ship_code = $v->ship_code ;
                $model->company   = $v->company;
                $model->country   = $v->country;
                $model->push_to_erp = 0;
                if($model->save()){
                    $success[]=$v->id;
                }
            }else{}
        }
        return $success;
    }

    public static function getTransportType($transport_style){
        if(empty($transport_style)){
            return 56;
        }
        switch ($transport_style){
            case 1 :
                return 57;
                break;
            case 2 :
                return 56;
                break;
            case 3 :
                return 15275;
                break;
            default :
                return $transport_style;
        }
    }

    /**
     * 采购节点：获取销售信息
     */
    public static function getXiaoshouInfo($sku)
    {

        //销售建单日期，建单人，销售审核状态，审核时间，审核人
        $xiaoshou_info = PlatformSummary::find()
            ->select(['sku','demand_number','create_time cg_xiaoshou_time','create_id cg_xiaoshou_user','level_audit_status cg_xiaoshou_audit_status','purchase_time cg_xiaoshou_audit_time','buyer cg_xiaoshou_audit_user'])
            ->where(['sku'=>$sku,'purchase_type'=>2])
            ->asArray()->all();
        return $xiaoshou_info;
    }

}
