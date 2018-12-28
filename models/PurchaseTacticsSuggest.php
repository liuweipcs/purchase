<?php
namespace app\models;

use app\models\base\BaseModel;

/**
 * This is the model class for table "pur_purchase_tactics_pur_suggest".
 */
class PurchaseTacticsSuggest extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_tactics_pur_suggest}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tactics_id'],'required'],
            [['type'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tactics_id' => '备货策略ID',
            'type' => '采购建议逻辑类型',
            'percent_start' => '占比起始值',
            'percent_end' => '占比结束值',
            'stockup_days' => '备货周期',
            'service_coefficient' => '服务系数',
            'maximum' => '最大值[天数]',
            'minimum' => '最小值[天数]',
        ];
    }

    /**
     * 配置补货策略-相关信息
     * @param $warehouse_code 仓库
     * @param $name 获取的字段
     * @param int $type 采购建议逻辑类型（1.定期备货，2.定量备货，3.最大最小备货）
     * @return mixed
     */
    public static function getPurchaseTacticsInfo($warehouse_code, $name, $where=[])
    {
        $_suggest_data_warehouse_code = [];
        $type = $where['type'] = isset($where['type'])?$where['type']:1;

        if (isset($where['tactics_id'])) {
            //sku-配置
            $warehouseWhere['tactics_id'] = $where['tactics_id'];
            $warehouseWhere['warehouse_code'] = $warehouse_code;
            $warehouseWhere['tactics_type'] = 2;
            $warehouseWhere['status'] = 1;
        } else {
            $warehouseWhere['warehouse_code'] = $warehouse_code;
            $warehouseWhere['status'] = 1; //启用
            $warehouseWhere['tactics_type'] = [0, 1]; //0默认，1MRP逻辑补货策略

        }

        $tacticsInfo = PurchaseTactics::find()
            ->alias('pt')
            ->leftJoin(PurchaseTacticsWarehouse::tableName().' ptw','ptw.tactics_id=pt.id')
            ->where($warehouseWhere)
            ->one();

        if (empty($tacticsInfo)) return ['is_success'=>false,'data'=>'无配置补货策略-仓库列表信息'];
        foreach ($tacticsInfo as $tk => $tv) {
            $_suggest_data_warehouse_code[$warehouse_code][$type][$tk] = $tv;
        }
        if (isset($where['tactics_id'])) {
            //sku-配置
            $tacticsSuggestWhere['tactics_id'] = $where['tactics_id'];
        } else {
            $tacticsSuggestWhere['tactics_id'] = $tacticsInfo['id'];
            $tacticsSuggestWhere['type'] = $type;
            foreach ($where as $wk=>$wv) {
                $tacticsSuggestWhere[$wk] = $wv;
            }
        }
        //通过货策略ID获取到：配置补货策略-采购建议逻辑
        $suggestInfo = self::find()->where($tacticsSuggestWhere)->asArray()->all();
        if (empty($suggestInfo)) return ['is_success'=>false,'data'=>'无配置补货策略-采购建议逻辑信息'];
        foreach ($suggestInfo as $sk => $sv) {
            foreach ($sv as $ssk=>$ssv) {
                if ($ssk=='id') continue;
                $_suggest_data_warehouse_code[$warehouse_code][$type][$ssk][$sk] = $sv;
            }
        }
        return ['is_success'=>true,'data'=>$_suggest_data_warehouse_code[$warehouse_code][$type][$name]];

        if (isset($where['tactics_id'])) {
            //sku-配置
            $warehouseWhere['tactics_id'] = $where['tactics_id'];
            $warehouseWhere['warehouse_code'] = $warehouse_code;
        } else {
            $warehouseWhere['warehouse_code'] = $warehouse_code;
        }
        //通过仓库获取到补：货策略ID
        $purchaseTacticsWarehouseInfo = PurchaseTacticsWarehouse::find()->where($warehouseWhere)->asArray()->all();
        if (empty($purchaseTacticsWarehouseInfo)) return ['is_success'=>false,'data'=>'无配置补货策略-适用仓库列表信息'];

        if (isset($where['tactics_id'])) {
            //sku-配置
            $tacticsWhere['id'] = $where['tactics_id'];
        } else {
            $tactics_id = array_column($purchaseTacticsWarehouseInfo,'tactics_id');
            $tacticsWhere['id'] = $tactics_id;
            $tacticsWhere['status'] = 1; //启用
            $tacticsWhere['tactics_type'] = [0, 1]; //0默认，1MRP逻辑补货策略
        }
        $tacticsInfo = PurchaseTactics::find()->where($tacticsWhere)->asArray()->one();
        if (empty($tacticsInfo)) return ['is_success'=>false,'data'=>'无配置补货策略信息'];
        foreach ($tacticsInfo as $tk => $tv) {
            $_suggest_data_warehouse_code[$warehouse_code][$type][$tk] = $tv;
        }
        if (isset($where['tactics_id'])) {
            //sku-配置
            $tacticsSuggestWhere['tactics_id'] = $where['tactics_id'];
        } else {
            $tacticsSuggestWhere['tactics_id'] = $tacticsInfo['id'];
            $tacticsSuggestWhere['type'] = $type;
            foreach ($where as $wk=>$wv) {
                $tacticsSuggestWhere[$wk] = $wv;
            }
        }
        //通过货策略ID获取到：配置补货策略-采购建议逻辑
        $suggestInfo = self::find()->where($tacticsSuggestWhere)->asArray()->all();
        if (empty($suggestInfo)) return ['is_success'=>false,'data'=>'无配置补货策略-采购建议逻辑信息'];
        foreach ($suggestInfo as $sk => $sv) {
            foreach ($sv as $ssk=>$ssv) {
                if (isset($_suggest_data_warehouse_code[$warehouse_code][$type][$ssk])) continue;
                $_suggest_data_warehouse_code[$warehouse_code][$type][$ssk][$sk] = $sv;
            }
        }
        return ['is_success'=>true,'data'=>$_suggest_data_warehouse_code[$warehouse_code][$type][$name]];
    }

    public static function getSkuQuoteValue($sku, $name)
    {
        static $_demand_data_sku = [];
        $sku = strtoupper($sku);
        if (isset($_demand_data_sku[$sku])) {
            return $_demand_data_sku[$sku][$name];
        }
        $quoteid = ProductProvider::find()->where(['sku'=>$sku,'is_supplier'=>1])->select('quotes_id')->scalar();
        $product_quote = SupplierQuotes::find()->where(['id'=>$quoteid])->one();
        $_demand_data_sku[$sku]['pur_ticketed_point'] = $product_quote->pur_ticketed_point;
        $_demand_data_sku[$sku]['is_back_tax'] = $product_quote->is_back_tax;
        $_demand_data_sku[$sku]['base_price'] = $product_quote->supplierprice;
        if ($product_quote->is_back_tax == 1) {
            $pur_ticketed_point = is_null($product_quote->pur_ticketed_point)?0:$product_quote->pur_ticketed_point;// 税点为NULL时其值设置为0
            $_demand_data_sku[$sku]['price'] = round($product_quote->supplierprice + $product_quote->supplierprice*$pur_ticketed_point/100, 4);
        } else {
            $_demand_data_sku[$sku]['price'] = $product_quote->supplierprice;
        }
        return $_demand_data_sku[$sku][$name];
    }
}
