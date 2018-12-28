<?php

namespace app\api\v1\models;

use Yii;

/**
 * This is the model class for table "pur_overseas_demand_rule".
 *
 * @property integer $id
 * @property string $min_money
 * @property string $min_operator
 * @property string $max_money
 * @property string $max_operator
 * @property string $float_money
 * @property integer $status
 * @property integer $create_user_id
 * @property string $create_time
 * @property integer $update_user_id
 * @property string $update_time
 * @property string $max_money_limit
 */
class OverseasDemandRule extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_overseas_demand_rule';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['min_money', 'min_operator', 'create_user_id', 'create_time'], 'required'],
            [['min_money', 'max_money', 'float_money', 'max_money_limit'], 'number'],
            [['status', 'create_user_id', 'update_user_id'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['min_operator', 'max_operator'], 'string', 'max' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'min_money' => 'Min Money',
            'min_operator' => 'Min Operator',
            'max_money' => 'Max Money',
            'max_operator' => 'Max Operator',
            'float_money' => 'Float Money',
            'status' => 'Status',
            'create_user_id' => 'Create User ID',
            'create_time' => 'Create Time',
            'update_user_id' => 'Update User ID',
            'update_time' => 'Update Time',
            'max_money_limit' => 'Max Money Limit',
        ];
    }

    public static function verifyInterceptRule($sku,$warehouseCode,$type=1,$data_id=null){
        $skuInfo = ProductProvider::find()
            ->select('t.supplier_code,q.supplierprice')
            ->alias('t')
            ->where(['t.sku'=>$sku])
            ->andWhere(['t.is_supplier'=>1])
            ->leftJoin(SupplierQuotes::tableName().' q','t.quotes_id=q.id')
            ->asArray()
            ->one();
        if(empty($skuInfo['supplier_code'])||empty($skuInfo['supplierprice'])){
            return ['status'=>'error','message'=>"产品报价信息不全无法匹配拦截规则",'level'=>6];
        }
        $supplierSku = ProductProvider::find()->select('sku')->where(['supplier_code'=>$skuInfo['supplier_code'],'is_supplier'=>1])->asArray()->all();
        $supplierSkus = array_column($supplierSku,'sku');
        $dateVer = self::verifyInterceptDateRule($supplierSkus,$data_id);
        if($dateVer['status']=='error'){
            return $dateVer;
        }
        if($type==2){
            return ['status'=>'success'];
        }
        $submitVer = self::verifyAgreeRule($data_id,$supplierSkus,$warehouseCode);
        if($submitVer['status']=='error'){
            return $submitVer;
        }
        $rules = self::find()->where(['status'=>1])->all();
        if(empty($rules)){
            return ['status'=>'success','message'=>"没有匹配到任何拦截规则"];
        }
        $purchaseData = self::getVerPurchasePrice($supplierSkus,$warehouseCode);
        $purchasePrice = $purchaseData['price'];
        foreach ($rules as $key=>$rule){
            if(empty($rule->max_money)||empty($rule->max_operator)){
                $result = "return $purchasePrice $rule->min_operator $rule->min_money;";
            }else{
                $result = "return $purchasePrice $rule->min_operator $rule->min_money&&(empty($rule->max_money) || $purchasePrice $rule->max_operator $rule->max_money);";
            }

            if(eval($result)){
                $matchRule = $rule;
                $ruleIndex = $key+1;
                break;
            }
        }
        if(isset($matchRule)){
            if(($matchRule->max_money-$matchRule->float_money)<=$purchasePrice){
                if(empty($matchRule->max_money)||$purchasePrice<=$matchRule->max_money){
                    $updateSku = self::updateSummary($supplierSkus,$warehouseCode);
                    return ['status'=>'success','message'=>'通过拦截规则','updateSku'=>$updateSku];
                }
            }
            if(($matchRule->max_money-$matchRule->float_money)>$purchasePrice){
                $min = $matchRule->max_money-$matchRule->float_money;
                if(isset($purchaseData['demand_number']) &&!empty($purchaseData['demand_number'])){
                    foreach ($purchaseData['demand_number'] as $demand_number){
                        if($demand_number!==$demandNumber){
                            Yii::$app->db->createCommand()->update(PlatformSummary::tableName(),['level_audit_status'=>6,'audit_note'=>"不满足第{$ruleIndex}拦截规则最低采购金额:$min"],['demand_number'=>$demand_number])->execute();
                        }
                    }
                }
                return ['status'=>'error','message'=>"不满足第{$ruleIndex}拦截规则最低采购金额:$min",'level'=>6];
            }
        }
        $updateSku = self::updateSummary($supplierSkus,$warehouseCode);
        return ['status'=>'success','message'=>"没有匹配到任何拦截规则",'updateSku'=>$updateSku];
    }


    public static function updateSummary($supplierSkus,$warehouseCode){
        $datas = PlatformSummary::find()
            ->where(['purchase_warehouse'=>$warehouseCode])
            ->andWhere(['in','sku',$supplierSkus])
            ->andWhere(['in','level_audit_status',[6,7]])
            ->andWhere(['in','transport_style',[2,3]])
            ->andWhere(['purchase_type'=>2])
            ->all();
        $updateArray = [];
        foreach ($datas as $data){
            $updateArray[]=$data->sku;
            $data->updateAttributes(['level_audit_status'=>1,'agree_time'=>date('Y-m-d H:i:s',time())]);
        }
        return $updateArray;
    }
    public static function verifyAgreeRule($demandNumber,$supplierSkus,$warehouseCode){
        $onSubmitSummary = PlatformSummary::find()
            ->where(['in','sku',$supplierSkus])
            ->andWhere(['level_audit_status'=>0])
            ->andWhere(['<>','demand_number',$demandNumber])
            ->andWhere(['purchase_warehouse'=>$warehouseCode])
            ->andWhere(['in','transport_style',[2,3]])
            ->andWhere(['purchase_type'=>2])
            ->exists();
        if($onSubmitSummary){
            return ['status'=>'error','message'=>'还有其他未同意需求','level'=>7];
        }
        return ['status'=>'success'];
    }

    //时间验证
    public static function verifyInterceptDateRule($supplierSkus,$demandNumber){
        $dataConfig = DataControlConfig::find()->select('values')->where(['type'=>'overseas_rule'])->scalar();
        $timeconfig = explode(',',$dataConfig);
        if(empty($timeconfig)){
            $timeconfig = [3,7];
        }
        $create_time = PlatformSummary::find()->select('create_time')->where(['demand_number'=>$demandNumber])->scalar();
        if(!$create_time){
            return ['status'=>'error','message'=>'数据异常','level'=>6];
        }
        $limitTime = $timeconfig[0]*60*60;
        $limitDay  = $timeconfig[1]*24*60*60;
        $pExist = PlatformSummary::find()
            ->where(['between','create_time',date('Y-m-d H:i:s',strtotime($create_time)-$limitDay),date('Y-m-d H:i:s',strtotime($create_time)-$limitTime)])
            ->andWhere(['in','sku',$supplierSkus])
            ->andWhere(['purchase_type'=>2])
            ->andWhere(['not in','level_audit_status',[3,4,5]])
            ->exists();
        if($pExist){
            return ['status'=>'error','message'=>"离该供应商上次创建需求大于".$timeconfig[0]."小时少于".$timeconfig[1].'天','level'=>6];
        }
        return ['status'=>'success'];
    }

    public static function getVerPurchasePrice($supplierSkus,$warehouseCode){
        $datas = PlatformSummary::find()
            ->alias('t')
            ->select('t.demand_number,t.purchase_quantity,q.supplierprice')
            ->leftJoin(ProductProvider::tableName().' a','t.sku=a.sku')
            ->leftJoin(SupplierQuotes::tableName().' q','a.quotes_id=q.id')
            ->where(['in','t.sku',$supplierSkus])
            ->andWhere(['a.is_supplier'=>1])
            ->andWhere(['t.purchase_warehouse'=>$warehouseCode])
            ->andWhere(['in','t.level_audit_status',[0,6,7]])
            ->andWhere(['in','t.transport_style',[2,3]])
            ->asArray()
            ->all();
        $price = 0;
        $demand_number = [];
        foreach ($datas as $data){
            $demand_number[] = $data['demand_number'];
            $price += $data['purchase_quantity']*$data['supplierprice'];
        }
        return ['price'=>$price,'demand_number'=>$demand_number];
    }
}
