<?php

namespace app\models;

use app\models\base\BaseModel;

use app\api\v1\models\InlandAvgDeliveryLog;
use Yii;
use yii\db\Query;

/**
 * This is the model class for table "{{%sku_sales_statistics_total_mrp}}".
 *
 * @property integer $id
 * @property string $sku
 * @property string $warehouse_code
 * @property integer $warehouse_id
 * @property string $platform_code
 * @property string $days_sales_3
 * @property string $days_sales_7
 * @property string $days_sales_15
 * @property string $days_sales_30
 * @property string $days_sales_60
 * @property string $days_sales_90
 * @property string $statistics_date
 * @property string $create_time
 * @property string $update_time
 * @property integer $is_suggest
 * @property integer $is_sum
 * @property integer $is_new
 * @property double $arrival_time_sd
 * @property double $avg_sales
 * @property double $sales_sd
 * @property integer $is_success
 * @property double $sort_segment
 * @property integer $stock_quantity
 */
class SkuSalesStatisticsTotalMrp extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%sku_sales_statistics_total_mrp}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sku', 'warehouse_code'], 'required'],
            [['warehouse_id', 'days_sales_3', 'days_sales_7', 'days_sales_15', 'days_sales_30', 'days_sales_60', 'days_sales_90', 'is_suggest', 'is_sum', 'is_new', 'is_success'], 'integer'],
            [['statistics_date', 'create_time', 'update_time'], 'safe'],
            [['arrival_time_sd', 'avg_sales', 'sales_sd', 'sort_segment'], 'number'],
            [['sku'], 'string', 'max' => 200],
            [['warehouse_code'], 'string', 'max' => 100],
            [['platform_code'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sku' => '产品SKU',
            'warehouse_code' => '仓库编码',
            'warehouse_id' => '仓库id',
            'platform_code' => '平台code',
            'days_sales_3' => 'SKU3天销量',
            'days_sales_7' => 'SKU7天销量',
            'days_sales_15' => 'SKU15天销量',
            'days_sales_30' => 'SKU30天销量',
            'days_sales_60' => 'SKU60天销量',
            'days_sales_90' => 'SKU90天销量',
            'statistics_date' => '统计时间',
            'create_time' => '创建时间',
            'update_time' => '更新时间',
            'is_suggest' => '是否生成采购建议(0没有1有)',
            'is_sum' => '数据是否相加(0未加1已加）',
            'is_new' => '是否新品 0不是1是',
            'arrival_time_sd' => '交期标准差',
            'avg_sales' => '日均销量',
            'sales_sd' => '销量标准差',
            'is_success' => '1',
            'sort_segment' => '总销量占比（排序分段）',
        ];
    }
    /**
     * @desc 和仓库表建立联系
     * 第一个参数为要关联的字表模型类名称，
     * 第二个参数指定 关联的条件
     * @return 关联关系条件
     * @author Jimmy
     * @date 2017-04-05 11:42:11
     */
    public function getWarehouse(){
        return $this->hasOne(Warehouse::className(), ['warehouse_code' => 'warehouse_code']);
    }
    /**
     * @desc 和仓库补货策略最小备货表建立联系
     * 第一个参数为要关联的字表模型类名称，
     * 第二个参数指定 关联的条件
     * @return 关联关系条件
     * @author Jimmy
     * @date 2017-04-05 13:56:11
     */
    public function getWarehouseMin(){
        return $this->hasOne(WarehouseMin::className(), ['warehouse_code' => 'warehouse_code']);
    }
    /**
     * @desc 和仓库补货策略-采购系数表建立联系
     * 第一个参数为要关联的字表模型类名称，
     * 第二个参数指定 关联的条件
     * @return 关联关系条件
     * @author Jimmy
     * @date 2017-04-05 14:21:11
     */
    public function getWarehousePurchaseTactics(){
        return $this->hasMany(WarehousePurchaseTactics::className(), ['warehouse_code' => 'warehouse_code']);
    }
    /**
     * @desc 和库补货策略-客单量系数表建立联系
     * 第一个参数为要关联的字表模型类名称，
     * 第二个参数指定 关联的条件
     * @return 关联关系条件
     * @author Jimmy
     * @date 2017-04-05 15:22:11
     */
    public function getWarehouseQtyTactics(){
        return $this->hasMany(WarehouseQtyTactics::className(), ['warehouse_code' => 'warehouse_code']);
    }
    /**
     * @desc 和仓库补货策略-销售系数表建立联系
     * 第一个参数为要关联的字表模型类名称，
     * 第二个参数指定 关联的条件
     * @return 关联关系条件
     * @author Jimmy
     * @date 2017-04-05 15:23:11
     */
    public function getWarehouseSalesTactics(){
        return $this->hasMany(WarehouseSalesTactics::className(), ['warehouse_code' => 'warehouse_code']);
    }
    /**
     * @desc 和加权系数-基础数据表建立联系
     * 第一个参数为要关联的字表模型类名称，
     * 第二个参数指定 关联的条件
     * @return 关联关系条件
     * @author Jimmy
     * @date 2017-04-05 15:37:11
     */
    public function getBasicTactics(){
        return $this->hasMany(BasicTactics::className(), ['warehouse_code' => 'warehouse_code']);
    }
    /**
     * @desc 和仓库补货策略最小备货表建立联系
     * 第一个参数为要关联的字表模型类名称，
     * 第二个参数指定 关联的条件
     * @return 关联关系条件
     * @author Jimmy
     * @date 2017-04-11 10:25:11
     */
    public function getProduct(){
        return $this->hasOne(Product::className(), ['sku' => 'sku']);
    }
    /**
     * @desc 根据SKU到描述表里面获取产品名称。
     * 第一个参数为要关联的字表模型类名称，
     * 第二个参数指定 关联的条件
     * @return 关联关系条件
     * @author Jimmy
     * @date 2017-04-11 10:25:11
     */
    public function getProductChName(){
        return $this->hasOne(ProductDescription::className(), ['sku' => 'sku']);
    }
    /**
     * @desc 根据SKU到描述表里面获取产品名称。
     * 第一个参数为要关联的字表模型类名称，
     * 第二个参数指定 关联的条件
     * @return 关联关系条件
     * @author Jimmy
     * @date 2017-04-11 10:25:11
     */
    public function getProductDefSupplier(){
        return $this->hasOne(ProductSupplier::className(), ['sku' => 'sku']);
    }
    /**
     *库存综合查询表
     * @return $this
     */
    public function getStock()
    {
        return $this->hasOne(Stock::className(), ['sku' => 'sku']);
    }
    /**
     * @desc 通过sku和仓库获取销量统计。
     * @return $sku
     * @author ZTT
     * @date 2017-04-11 10:25:11
     */
    public static function  getStatistics($sku)
    {
        return self::find()->where(['sku'=>$sku])->one();
    }

    /**
     * 创建逻辑
     * @param unknown $sku
     * @param unknown $warehouse_code
     * @param unknown $skuMrpInfo 销量汇总表数据
     */
    public function createLogic($sku=null, $warehouse_code=null,$skuMrpInfo)
    {
        if (empty($sku) || empty($warehouse_code))  return ['is_success'=>false, 'data'=>'运行备货逻辑时，sku和仓库编码不能为空'];
        //sku备货逻辑:tactics_type：2sku补货策略，status：1启用
        $tacticsData = PurchaseTactics::find()
            ->alias('pt')
            ->leftJoin(PurchaseTacticsWarehouse::tableName().' ptw','ptw.tactics_id=pt.id')
            ->where(['sku'=>$sku,'tactics_type'=>2,'status'=>1])
            ->andWhere(['OR', "warehouse_code='$warehouse_code'", 'warehouse_code IS NULL'])
            ->one();

        $flag = false; //默认：跑仓库备货逻辑
        if (!empty($tacticsData)) {
            //填了开始和结束时间
            if (!empty($tacticsData->end_time) && !empty($tacticsData->start_time)) {
                $end_time = !empty($tacticsData->end_time)?strtotime($tacticsData->end_time):0;
                $start_time = !empty($tacticsData->end_time)?strtotime($tacticsData->start_time):0;
                //在时间范围内
                if ($start_time<=time() && time()<=$end_time) {
                    $flag = true;
                } else {
                    $flag = false;
                }

            } else {
                $flag = true;
            }
        } else {
            $flag = false;
        }

        //如果有数据 且 在备货时间段内
        if (!empty($tacticsData) && $flag== true) {
            //sku补货策略
            $tactics_type = 2;
            $res = $this->skuMrp($sku, $warehouse_code,$skuMrpInfo,$tacticsData);
        } else {
            //仓库补货策略
            $tactics_type = 1;
            $res = $this->warehouseMrp($sku, $warehouse_code,$skuMrpInfo);
        }
        return $res;
    }
    /**
     * sku-创建逻辑
     * @param unknown $sku
     * @param unknown $warehouse_code
     * @param unknown $skuMrpInfo 销量汇总表数据
     */
    public function skuMrp($sku, $warehouse_code,$skuMrpInfo,$tacticsData){
        $is_stock_owes = ($skuMrpInfo['is_stock_owes'] == 1)?true:false;// 是否是  缺货列表导入的数据
        $type_arr = [1,2,3];
        $tactics_id = $tacticsData['id']; //补货策略ID
        $arrivalTimeStandardDeviation = $skuMrpInfo['arrival_time_sd']?:0; //交期标准差-王瑞-ok
        
        $L =  PurchaseTacticsSuggest::getPurchaseTacticsInfo($warehouse_code,'lead_time_value_range',['tactics_id'=>$tactics_id]); //提前期-系统设置-ok
        if ($L['is_success']===true) {
            $L =  $L['data'];
        } else {
            return ['is_success'=>false, 'data'=>'获取提前期失败'];
        }
        $L = self::getAvgArrivalTime($sku,$warehouse_code,$L);
        if ($L['is_success']===true) {
            $L =  $L['avg_arrival_time'];
        } else {
            $L = 10;
        }
        $QL =  PurchaseTacticsSuggest::getPurchaseTacticsInfo($warehouse_code,'weight_avg_period_value_range',['tactics_id'=>$tactics_id]); ; //权均交期取值范围-系统设置-ok
        if ($QL['is_success']===true) {
            $QL =  $QL['data'];
        } else {
            $QL = 0;
        }

        $percent = $skuMrpInfo['sort_segment']; //销量排序分段值-任志才
        if ($percent == null AND empty($is_stock_owes)) return ['is_success'=>false, 'data'=>'无销量排序分段值'];
        $Qd = $skuMrpInfo['sales_sd']?:0;//销量标准差-占伟龙 $this->calculateSDValueBySku($sku,$warehouse_code)
        $V = $skuMrpInfo['avg_sales']?:0;//日均销量-占伟龙 $this->calculateDailySalesBySku($sku,$warehouse_code)

        //合仓-在途在库缺货合仓
        $he_stock_warehouse = DataControlConfig::getMrpWarehouseHeStock();
        //主仓
        $main_warehouse = DataControlConfig::getMrpWarehouseMain();
        //如果当前仓库属于主仓，则
        if (!empty($main_warehouse[0]) && !empty($he_stock_warehouse) && $warehouse_code==trim($main_warehouse[0])) {
            $he_stock_warehouse = array_unique(array_merge($main_warehouse, $he_stock_warehouse));
            $stockInfo = Stock::find()->select('SUM(on_way_stock) as on_way_stock, SUM(available_stock) as available_stock')->where(['sku'=>$sku])->andWhere(['in', 'warehouse_code', $he_stock_warehouse])->asArray()->one();
            $stockOwe = StockOwes::find()->where(['sku'=>$sku])->andWhere(['in', 'warehouse_code', $he_stock_warehouse])->sum('left_stock');
        } else {
            $stockInfo = Stock::find()->select('on_way_stock, available_stock')->where(['sku'=>$sku,'warehouse_code'=>$warehouse_code])->asArray()->one();
            $stockOwe = StockOwes::find()->select('left_stock')->where(['sku'=>$sku,'warehouse_code'=>$warehouse_code])->scalar();
        }
        if (empty($stockInfo)){
            $Q0 = 0;
            $Q1 = 0;
        }else{
            $Q0 = isset($stockInfo['on_way_stock']) ? $stockInfo['on_way_stock']:0; //在途库存-拉取
            $Q1 = isset($stockInfo['available_stock']) ? $stockInfo['available_stock'] :0; //在库库存
        }
        $Q2 = $stockOwe ? $stockOwe : 0; //欠货量

        $typeInfo =  PurchaseTacticsSuggest::getPurchaseTacticsInfo($warehouse_code,'type',['tactics_id'=>$tactics_id]); ; //补货策略类型-系统设置-ok
        if ($typeInfo['is_success']===true && !empty($typeInfo['data'][0]['type'])) {
            $type =  $typeInfo['data'][0]['type'];
        } else {
            return ['is_success'=>false, 'data'=>'获取补货策略类型失败'];
        }

        $data = [
            'arrivalTimeStandardDeviation'=>$arrivalTimeStandardDeviation, //交期标准差-王瑞-ok
            'L' => $L, ////提前期-系统设置-ok
            'V' => $V,//日均销量-占伟龙
            'Qd' => $Qd,//销量标准差-占伟龙
            'QL' => $QL,//权均交期取值范围-系统设置-ok
            'Q0' => $Q0,//在途库存-拉取
            'Q1' => $Q1,//在库库存
            'Q2' => $Q2,//欠货量
            'id' => $typeInfo['data'][0]['id'],//id
            'tactics_id'=>$tactics_id
        ];

        //1.定期备货，2.定量备货，3.最大最小备货
        if ($type == 1) {
            //定期备货
            $res = self::getRegularStock($sku,$warehouse_code,$data);
        } elseif ($type == 2) {
            //定量
            $res = self::getRationStock($sku,$warehouse_code,$data);
        }elseif ($type == 3) {
            //最大最小值
            $res = self::getMaxMinStock($sku,$warehouse_code,$data);
        }
        $res = isset($res) ? $res : ['is_success'=>false, 'data'=>'备货逻辑运行失败'];

        if ($res['is_success'] === true) {
            $resData = $res['data'];
            $suggestion_logic = ''; //建议逻辑数据
            $suggestData['排序分段(sort_segment)'] = isset($percent)?$percent:'';
            $suggestData['交期标准差(arrivalTimeStandardDeviation)'] = isset($resData['arrivalTimeStandardDeviation'])?$resData['arrivalTimeStandardDeviation']:'';
            $suggestData['提前期平均值(L)'] = isset($resData['L'])?$resData['L']:'';
            $suggestData['日均销量(V)'] = isset($resData['V'])?$resData['V']:'';
            $suggestData['销量标准差(Qd)'] = isset($resData['Qd'])?$resData['Qd']:'';
            $suggestData['权均交期取值范围(QL)'] = isset($resData['QL'])?$resData['QL']:'';
            $suggestData['在途库存(Q0)'] = isset($resData['Q0'])?$resData['Q0']:'';
            $suggestData['在库库存(Q1)'] = isset($resData['Q1'])?$resData['Q1']:'';
            $suggestData['欠货量(Q2)'] = isset($resData['Q2'])?$resData['Q2']:'';
            $suggestData['备货周期或备货天数(T)'] = isset($resData['T'])?$resData['T']:'';
            $suggestData['服务系数(Z)'] = isset($resData['Z'])?$resData['Z']:'';
            $suggestData['安全库存(SS)'] = isset($resData['SS'])?$resData['SS']:'';
            $suggestData['周转库存(TS)'] = isset($resData['TS'])?$resData['TS']:'';
            $suggestData['备货量(Q)'] = isset($resData['Q'])?$resData['Q']:'';
            $suggestData['服务系数(Z)'] = isset($resData['Z'])?$resData['Z']:'';
            $suggestData['最大天数(MaxDay)'] = isset($resData['MaxDay'])?$resData['MaxDay']:'';
            $suggestData['最小天数(MinDay)'] = isset($resData['MinDay'])?$resData['MinDay']:'';
            $suggestData['最大值天数的安全库存(MaxDayStock)'] = isset($resData['MaxDayStock'])?$resData['MaxDayStock']:'';
            $suggestData['最小值天数的安全库存(MixDayStock)'] = isset($resData['MixDayStock'])?$resData['MixDayStock']:'';
            $suggestData['增加量的天数(incrDays)'] = isset($resData['incrDays'])?$resData['incrDays']:'';
            foreach ($suggestData as $k => $v) $suggestion_logic .= "$k=$v,";

            //返回到建议的数据
            $returnData = [
                'qty'=>$resData['Q']<=0 ? ($is_stock_owes?$resData['Q2']:0)  :$resData['Q'], //备货数量
                'suggestion_logic'=>$suggestion_logic, //备货数据
                'on_way_stock' => $resData['Q0'], //在途数量
                'available_stock' => $resData['Q1'], //可用数量
                'left_stock' => $resData['Q2'], //欠货数量
                'sales_avg' => $resData['V'], //平均销量
                'stock_logic_type' => $resData['stock_logic_type'], //备货逻辑
                'resData'=>$resData
            ];
            return ['is_success'=>1, 'data' => $returnData];
        } else {
            return $res;
        }
    }
    /**
     * 仓库-创建逻辑
     * @param unknown $sku
     * @param unknown $warehouse_code
     * @param unknown $skuMrpInfo 销量汇总表数据
     */
    public function warehouseMrp($sku, $warehouse_code,$skuMrpInfo){
        $is_stock_owes = ($skuMrpInfo['is_stock_owes'] == 1)?true:false;// 是否是  缺货列表导入的数据
        $type_arr = [1,2,3];
        foreach ($type_arr as $type) {
            $percent_start =  PurchaseTacticsSuggest::getPurchaseTacticsInfo($warehouse_code,'percent_start',['type'=>$type]); //获取销量占比-占比开始值-系统设置-ok
            if ($percent_start['is_success']===true) {
                $percentStart[$type] =  $percent_start['data'];
            } else {
                return ['is_success'=>false, 'data'=>'获取销量占比失败'];
            }
        }
        $arrivalTimeStandardDeviation = $skuMrpInfo['arrival_time_sd']?:0; //交期标准差-王瑞-ok self::getArrivalTimeStandardDeviation($sku,$warehouse_code)
        $L =  PurchaseTacticsSuggest::getPurchaseTacticsInfo($warehouse_code,'lead_time_value_range'); //提前期-系统设置-ok
        if ($L['is_success']===true) {
            $L =  $L['data'];
        } else {
            return ['is_success'=>false, 'data'=>'获取提前期失败'];
        }
        $L = self::getAvgArrivalTime($sku,$warehouse_code,$L);
        if ($L['is_success']===true) {
            $L =  $L['avg_arrival_time'];
        } else {
            $L = 10;
        }

        $QL =  PurchaseTacticsSuggest::getPurchaseTacticsInfo($warehouse_code,'weight_avg_period_value_range'); ; //权均交期取值范围-系统设置-ok
        if ($QL['is_success']===true) {
            $QL =  $QL['data'];
        } else {
            $QL = 0;
        }

        $percent = $skuMrpInfo['sort_segment']; //销量排序分段值-任志才
        if ($percent == null AND empty($is_stock_owes)) return ['is_success'=>false, 'data'=>'无销量排序分段值'];
        $Qd = $skuMrpInfo['sales_sd']?:0;//销量标准差-占伟龙 $this->calculateSDValueBySku($sku,$warehouse_code)
        $V = $skuMrpInfo['avg_sales']?:0;//日均销量-占伟龙 $this->calculateDailySalesBySku($sku,$warehouse_code)

        //合仓-在途在库缺货合仓
        $he_stock_warehouse = DataControlConfig::getMrpWarehouseHeStock();
        //主仓
        $main_warehouse = DataControlConfig::getMrpWarehouseMain();
        //如果当前仓库属于主仓，则
        if (!empty($main_warehouse[0]) && !empty($he_stock_warehouse) && $warehouse_code==trim($main_warehouse[0])) {
            $he_stock_warehouse = array_unique(array_merge($main_warehouse, $he_stock_warehouse));
            $stockInfo = Stock::find()->select('SUM(on_way_stock) as on_way_stock, SUM(available_stock) as available_stock')->where(['sku'=>$sku])->andWhere(['in', 'warehouse_code', $he_stock_warehouse])->asArray()->one();
            $stockOwe = StockOwes::find()->where(['sku'=>$sku])->andWhere(['in', 'warehouse_code', $he_stock_warehouse])->sum('left_stock');
        } else {
            $stockInfo = Stock::find()->select('on_way_stock, available_stock')->where(['sku'=>$sku,'warehouse_code'=>$warehouse_code])->asArray()->one();
            $stockOwe = StockOwes::find()->select('left_stock')->where(['sku'=>$sku,'warehouse_code'=>$warehouse_code])->scalar();
        }
        if (empty($stockInfo)){
            $Q0 = 0;
            $Q1 = 0;
        }else{
            $Q0 = isset($stockInfo['on_way_stock']) ? $stockInfo['on_way_stock']:0; //在途库存-拉取
            $Q1 = isset($stockInfo['available_stock']) ? $stockInfo['available_stock'] :0; //在库库存
        }

        $Q2 = $stockOwe ? $stockOwe : 0; //欠货量

        $data = [
            'arrivalTimeStandardDeviation'=>$arrivalTimeStandardDeviation, //交期标准差-王瑞-ok
            'L' => $L, ////提前期-系统设置-ok
            'V' => $V,//日均销量-占伟龙
            'Qd' => $Qd,//销量标准差-占伟龙
            'QL' => $QL,//权均交期取值范围-系统设置-ok
            'Q0' => $Q0,//在途库存-拉取
            'Q1' => $Q1,//在库库存
            'Q2' => $Q2,//欠货量
        ];
        foreach ($percentStart as $type => $pv) {
            foreach ($pv as $k=>$v) {
                $data['id'] = $v['id'];
                if ($v['percent_start']<$percent && $percent<=$v['percent_end']) {
                    if ($type === 1) {
                        //定期备货
                        $res = self::getRegularStock($sku,$warehouse_code,$data);
                    } elseif ($type ===2 ) {
                        //定量
                        $res = self::getRationStock($sku,$warehouse_code,$data);
                    } elseif ($type === 3) {
                        //最大最小值
                        $res = self::getMaxMinStock($sku,$warehouse_code,$data);
                    }
                    break;
                }
                if($is_stock_owes){// is_stock_owes 没有销量，强制根据最大最小备货逻辑进行备货
                    $res = self::getMaxMinStock($sku,$warehouse_code,$data);
                    break;
                }
            }
        }

        $res = isset($res) ? $res : ['is_success'=>false, 'data'=>'备货逻辑运行失败'];

        if ($res['is_success'] === true) {
            $resData = $res['data'];
            $suggestion_logic = ''; //建议逻辑数据
            $suggestData['排序分段(sort_segment)'] = isset($percent)?$percent:'';
            $suggestData['交期标准差(arrivalTimeStandardDeviation)'] = isset($resData['arrivalTimeStandardDeviation'])?$resData['arrivalTimeStandardDeviation']:'';
            $suggestData['提前期平均值(L)'] = isset($resData['L'])?$resData['L']:'';
            $suggestData['日均销量(V)'] = isset($resData['V'])?$resData['V']:'';
            $suggestData['销量标准差(Qd)'] = isset($resData['Qd'])?$resData['Qd']:'';
            $suggestData['权均交期取值范围(QL)'] = isset($resData['QL'])?$resData['QL']:'';
            $suggestData['在途库存(Q0)'] = isset($resData['Q0'])?$resData['Q0']:'';
            $suggestData['在库库存(Q1)'] = isset($resData['Q1'])?$resData['Q1']:'';
            $suggestData['欠货量(Q2)'] = isset($resData['Q2'])?$resData['Q2']:'';
            $suggestData['备货周期或备货天数(T)'] = isset($resData['T'])?$resData['T']:'';
            $suggestData['服务系数(Z)'] = isset($resData['Z'])?$resData['Z']:'';
            $suggestData['安全库存(SS)'] = isset($resData['SS'])?$resData['SS']:'';
            $suggestData['周转库存(TS)'] = isset($resData['TS'])?$resData['TS']:'';
            $suggestData['备货量(Q)'] = isset($resData['Q'])?$resData['Q']:'';
            $suggestData['服务系数(Z)'] = isset($resData['Z'])?$resData['Z']:'';
            $suggestData['最大天数(MaxDay)'] = isset($resData['MaxDay'])?$resData['MaxDay']:'';
            $suggestData['最小天数(MinDay)'] = isset($resData['MinDay'])?$resData['MinDay']:'';
            $suggestData['最大值天数的安全库存(MaxDayStock)'] = isset($resData['MaxDayStock'])?$resData['MaxDayStock']:'';
            $suggestData['最小值天数的安全库存(MixDayStock)'] = isset($resData['MixDayStock'])?$resData['MixDayStock']:'';
            $suggestData['增加量的天数(incrDays)'] = isset($resData['incrDays'])?$resData['incrDays']:'';
            foreach ($suggestData as $k => $v) $suggestion_logic .= "$k=$v,";
            //返回到建议的数据
            $returnData = [
                'qty'=>$resData['Q']<=0 ? ($is_stock_owes?$resData['Q2']:0) :$resData['Q'], //备货数量(is_stock_owes取欠货数量)
                'suggestion_logic'=>$suggestion_logic, //备货数据
                'on_way_stock' => $resData['Q0'], //在途数量
                'available_stock' => $resData['Q1'], //可用数量
                'left_stock' => $resData['Q2'], //欠货数量
                'sales_avg' => $resData['V'], //平均销量
                'stock_logic_type' => $resData['stock_logic_type'], //备货逻辑
                'resData'=>$resData
            ];
            return ['is_success'=>1, 'data' => $returnData];
        } else {
            return $res;
        }
    }

    /**
     * 定期备货：设置销量占比 、备货周期、服务系数，根据设置的备货周期进行备货
     * @param $sku
     * @param $warehouse_code
     * @param $data
     */
    public static function getRegularStock($sku,$warehouse_code,$data)
    {
        $type = 1; //`type` int(4) DEFAULT NULL COMMENT '采购建议逻辑类型（1.定期备货，2.定量备货，3.最大最小备货）',
        $arrivalTimeStandardDeviation = $data['arrivalTimeStandardDeviation'];
        $L = $data['L'];
        $V = $data['V'];
        $Qd = $data['Qd'];
        $Q0 = $data['Q0'];
        $Q1 = $data['Q1'];
        $Q2 = $data['Q2'];
        $where = ['type'=> $type,'id'=>$data['id']];
        if (!empty($data['tactics_id'])) $where['tactics_id'] = $data['tactics_id'];

        $T =  PurchaseTacticsSuggest::getPurchaseTacticsInfo($warehouse_code,'stockup_days',$where); ; //备货周期,获取备货天数-系统设置：不同的策略（定期、定量），备货周期,获取备货天数不同-ok
        if ($T['is_success']===true) {
            $T = $T['data'][0]['stockup_days'];
        } else {
            return ['is_success'=>false, 'data'=>'获取备货周期或备货天数失败'];
        }
        $Z =  PurchaseTacticsSuggest::getPurchaseTacticsInfo($warehouse_code,'service_coefficient',$where); //服务系数-系统设置：不同的策略（定期、定量），服务系数不同-ok
        if ($Z['is_success']===true) {
            $Z = $serviceFactor = $Z['data'][0]['service_coefficient'];
        } else {
            return ['is_success'=>false, 'data'=>'获取备货周期或备货天数失败'];
        }

        $safeStock = self::getSafeStock($sku, $warehouse_code, $serviceFactor,$Qd, $arrivalTimeStandardDeviation,$L); //安全库存-王瑞-ok
        if ($safeStock['is_success'] === true) {
            $safeStock = $safeStock['safe_stock'];
        } else {
            return ['is_success'=>false, 'data'=>'安全库存计算失败'];
        }

        $incrDays =  PurchaseTacticsSuggest::getPurchaseTacticsInfo($warehouse_code,'incr_days',$where); //增加量的天数-系统设置：不同的策略（定期、定量），服务系数不同-ok
        if ($incrDays['is_success']===true && !empty($incrDays['data'][0]['incr_days'])) {
            $incrDays = $incrDays['data'][0]['incr_days'];
        } else {
            return ['is_success'=>false, 'data'=>'获取增加量的天数失败'];
        }

        $Ts = ($T+$L) * $V;//周转库存=（备货周期+提前期）*日均销量
        $Ss = $safeStock;//安全库存

        //定期备货数量：Q=TS+SS-Q0-Q1+Q2+增加量的天数*日均销量
        if (($Q1+$Q0-$Q2)<=0) {
            // 当可用库存(在库库存)<=0时，每天执行一次采购建议备货逻辑
            $Q = ceil($Ts+$Ss-$Q0-$Q1+$Q2 + $incrDays*$V); //备货量
        } else {
            // 当可用库存>0时，根据设置的备货周期进行备货（如：备货周期T设置的是三天，则每三天执行一次备货逻辑）
            $suggestHistoryMrp = PurchaseSuggestHistoryMrp::find()->select('created_at,qty')->where(['sku'=>$sku, 'warehouse_code'=>$warehouse_code])->orderBy('id DESC')->asArray()->one();
            if (empty($suggestHistoryMrp)) {
                $Q = ceil($Ts+$Ss-$Q0-$Q1+$Q2 + $incrDays*$V); //备货量
            } elseif (!empty($suggestHistoryMrp['created_at'])) {
                //采购单的创建日期+备货周期的日期
                $createAddT = strtotime( date('Y-m-d', strtotime($suggestHistoryMrp['created_at'])+$T*86400) );
                $today = strtotime( date('Y-m-d', time()) ); //今天

                //获取采购数量
                $order_ctq = $cancel_ctq = 0;
                $audit_purchase_order = PurchaseOrder::AUDIT_PURCHASE_ORDER; //审核通过的采购单
                $start_time = date('Y-m-d 00:00:00', strtotime($suggestHistoryMrp['created_at']));
                $end_time = date('Y-m-d 23:59:59', strtotime($suggestHistoryMrp['created_at']));

                $itemsInfo = PurchaseOrderItems::find()
                    ->select('SUM(ctq) as ctq, group_concat(po.pur_number separator " ") as pur_number')
                    ->alias('poi')
                    ->leftJoin(PurchaseOrder::tableName().' po','po.pur_number=poi.pur_number')
                    ->where(['sku'=>$sku])
                    ->andWhere(['purchase_type'=>1])
                    ->andWhere(['in', 'purchas_status', $audit_purchase_order])
                    ->andWhere(['between', 'created_at', $start_time,$end_time])
                    ->asArray()->one();

                if(!empty($itemsInfo['pur_number'])) {
                    $item_numbers = explode(' ', $itemsInfo['pur_number']);
                    $cancelInfo = PurchaseOrderRefundQuantity::find()
                        ->select('SUM(refund_qty) as cancel_ctq')
                        ->where(['sku'=>$sku,'refund_status'=>1])
                        ->andWhere(['in', 'pur_number', $item_numbers])
                        ->asArray()
                        ->one();
                    $cancel_ctq = !empty($cancelInfo['cancel_ctq'])?$cancelInfo['cancel_ctq']:0;
                    $order_ctq = !empty($itemsInfo['ctq'])?$itemsInfo['ctq']:0;
                    unset($cancelInfo);
                }
                unset($itemsInfo);

                $suggest_qty = !empty($suggestHistoryMrp['qty'])?$suggestHistoryMrp['qty']:0;
                $cg_ctq = $order_ctq-$cancel_ctq; //实际采购
                unset($suggestHistoryMrp);

                if ( ($createAddT == $today) || ($cg_ctq<$suggest_qty) ) {
                    # 如果 在备货周期 或 当前采购的数量，小于采购建议的数量，则备货
                    $Q = ceil($Ts+$Ss-$Q0-$Q1+$Q2 + $incrDays*$V); //备货量
                } else {
                    # 如果当天采购的数量，大于等于采购建议的数量，则不备货
                    return ['is_success'=>false, 'data'=>'没在备货周期时间，不备货'];
                }
            } else {
                return ['is_success'=>false, 'data'=>'对应的采购建议没有创建时间'];
            }
        }

        $data['T'] = $T; //备货周期,获取备货天数-系统设置：不同的策略（定期、定量），备货周期,获取备货天数不同-ok
        $data['Z'] = $Z; //服务系数-系统设置：不同的策略（定期、定量），服务系数不同-ok
        $data['safeStock'] = $safeStock; //安全库存-王瑞-ok
        $data['TS'] = $Ts;//周转库存=（备货周期+提前期）*日均销量
        $data['SS'] = $Ss;//安全库存
        $data['Q'] = $Q; //备货量
        $data['incrDays'] = $incrDays; //增加量的天数
        $data['stock_logic_type'] = $type; //备货逻辑

        return ['is_success'=> true, 'data'=>$data];
    }

    /**
     * 定量备货：设置销量占比、备货天数、服务系数
     * @param $sku
     * @param $warehouse_code
     * @param $data
     */
    public static function getRationStock($sku,$warehouse_code,$data)
    {
        $type = 2;
        $arrivalTimeStandardDeviation = $data['arrivalTimeStandardDeviation'];
        $L = $data['L'];
        $V = $data['V'];
        $Qd = $data['Qd'];
        $QL = $data['QL'];
        $Q0 = $data['Q0'];
        $Q1 = $data['Q1'];
        $Q2 = $data['Q2'];
        $where = ['type'=> $type,'id'=>$data['id']];
        if (!empty($data['tactics_id'])) $where['tactics_id'] = $data['tactics_id'];

        $Z = PurchaseTacticsSuggest::getPurchaseTacticsInfo($warehouse_code,'service_coefficient', $where); //服务系数-系统设置：不同的策略（定期、定量），服务系数不同-ok
        if ($Z['is_success']===true) {
            $Z = $serviceFactor = $Z['data'][0]['service_coefficient'];
        } else {
            return ['is_success'=>false, 'data'=>'获取服务系数失败'];
        }

        $stockDays =  PurchaseTacticsSuggest::getPurchaseTacticsInfo($warehouse_code,'stockup_days', $where); ; //备货周期,获取备货天数-系统设置：不同的策略（定期、定量），备货周期,获取备货天数不同-ok
        if ($stockDays['is_success']===true) {
            $stockDays = $stockDays['data'][0]['stockup_days'];
        } else {
            return ['is_success'=>false, 'data'=>'获取备货周期或备货天数失败'];
        }

        $safeStock = self::getSafeStock($sku, $warehouse_code, $serviceFactor,$Qd, $arrivalTimeStandardDeviation,$L); //安全库存-王瑞-ok
        if ($safeStock['is_success'] === true) {
            $safeStock = $safeStock['safe_stock'];
        } else {
            return ['is_success'=>false, 'data'=>'安全库存计算失败'];
        }

        $incrDays =  PurchaseTacticsSuggest::getPurchaseTacticsInfo($warehouse_code,'incr_days', $where); //增加量的天数-系统设置：不同的策略（定期、定量），服务系数不同-ok
        if ($incrDays['is_success']===true && !empty($incrDays['data'][0]['incr_days'])) {
            $incrDays = $incrDays['data'][0]['incr_days'];
        } else {
            return ['is_success'=>false, 'data'=>'获取增加量的天数失败'];
        }

        $qt1 = $L*$V + $safeStock + $Q2-$Q0;
        //当Q1≤L*V+{Z√（Qd）2 *L+(Ql)2*（Qd）2}+Q2-Q0+增加量的天数*日均销量时
        if ($Q1<=$qt1 + $incrDays*$V) {
            //订货量Q=备货天数*日均销量 + $qt1 -$Q1
            $Q = ceil($stockDays*$V + $qt1-$Q1);
        } else {
            //当Q1>L*V+Z√（Qd）2 *L+(Ql)2*（Qd）2+Q2-Q0时，则不需进行备货
            $Q = 0;
        }

        $data['Z']= $Z;//服务系数-系统设置：不同的策略（定期、定量），服务系数不同-ok
        $data['T'] = $stockDays; //备货周期,获取备货天数-系统设置：不同的策略（定期、定量），备货周期,获取备货天数不同-ok
        $data['Q'] = $Q; //备货量
        $data['SS'] = $safeStock; //安全库存
        $data['incrDays'] = $incrDays; //增加量的天数
        $data['stock_logic_type'] = $type; //备货逻辑

        return ['is_success'=> true, 'data'=>$data];
    }

    /**
     * 最大最小备货：设置销量占比、最大值、最小值
     * @param $sku
     * @param $warehouse_code
     * @param $data
     */
    public static function getMaxMinStock($sku,$warehouse_code,$data){
        $type = 3;
        $V = $data['V'];
        $Q1 = $data['Q1'];
        $Q2 = $data['Q2'];
        $Q0 = $data['Q0'];//在途库存-拉取

        $where = ['type'=> $type,'id'=>$data['id']];
        if (!empty($data['tactics_id'])) $where['tactics_id'] = $data['tactics_id'];

        $MaxDay =  PurchaseTacticsSuggest::getPurchaseTacticsInfo($warehouse_code,'maximum',$where); ; //最大天数-系统设置-ok
        if ($MaxDay['is_success']===true) {
            $MaxDay =  $MaxDay['data'][0]['maximum'];
        } else {
            return ['is_success'=>false, 'data'=>'获取最大天数失败'];
        }

        $MinDay =  PurchaseTacticsSuggest::getPurchaseTacticsInfo($warehouse_code,'minimum',$where); ; //最小天数-系统设置-ok
        if ($MinDay['is_success']===true) {
            $MinDay =  $MinDay['data'][0]['minimum'];
        } else {
            return ['is_success'=>false, 'data'=>'获取最小天数失败'];
        }

        $MixDayStock = $MinDay*$V; //最小值天数的安全库存
        if ( $Q1 < $MixDayStock) {
            //当在库库存(Q1)下降到配置的最小值天数的安全库存(最大值*日均销量)时，备货量为：最大值天数的安全库存(最大值*日均销量)+Q2
            $MaxDayStock = $MaxDay*$V; //最大值天数的安全库存
            $Q = ceil($MaxDayStock + $Q2 - $Q0);
        } else {
            $Q = 0;
        }

        $data['MaxDay'] = $MaxDay; //最大天数-系统设置-ok
        $data['MinDay'] = $MinDay; //最小天数-系统设置-ok
        $data['MaxDayStock'] = isset($MaxDayStock)?$MaxDayStock:''; //最大值天数的安全库存
        $data['MixDayStock'] = isset($MixDayStock)?$MixDayStock:''; //最小值天数的安全库存
        $data['Q'] = $Q; //备货量
        $data['Q2'] = $Q2; //欠货货量
        $data['stock_logic_type'] = $type; //备货逻辑
        return ['is_success'=> true, 'data'=>$data];
    }

    /**
     * @desc 通过sku,仓库获取sku新品备货数量
     * @param $sku sku
     * @param null $price sku当前采购单价，默认为空，不传方法会查询一次
     * @param null $priceLimit 单价限制值，默认为空，不传方法会查询一次
     * @param null $stockLimit 库存持有量，默认为空，不传会查询
     * @param null $leftStock 欠货，默认为空不传会查询
     * @param null $isNew 是否新品，
     * @return array ['is_success'=>bool,'suggest_num'=>int,'message'=>string,'price'=>int,'left_stock'=>int]
     * is_new 是否新品备货 suggest_num 新品备货数量 message 提示信息 price sku单价 left_stock 缺货（订单数量）
     */
    public static function getNewSkuSuggestNum($sku,$warehouseCode,$price=null,$priceLimit=null,$stockLimit=null,$leftStock=null,$isNew=null){
        $suggestNum=0;
        if(!isset($sku)||!isset($warehouseCode)||empty($sku)||empty($warehouseCode)){
            //sku为空或者没传sku，不走新品备货,仓库为空或没传仓库，不走新品备货
            return ['is_success'=>false,'suggest_num'=>$suggestNum,'message'=>'仓库或sku为空，无法获取新品备货数量','price'=>0,'left_stock'=>0];
        }
        if(empty($isNew)){
            $isNew=SkuSaleDetails::find()->select('is_new')->where(['sku'=>$sku,'warehouse_code'=>$warehouseCode,'sale_date'=>date('Y-m-d',time()-86400)])->scalar();
        }
        //没有是否新品数据或者不是新品，不走新品备货
        if(!$isNew||$isNew==0){
            return ['is_success'=>false,'suggest_num'=>$suggestNum,'message'=>'sku不是新品，无法获取新品备货数量','price'=>0,'left_stock'=>0];
        }
        if(empty($priceLimit)||empty($stockLimit)){
            //根据仓库查询单价限制值及库存持有量
            $tactics_id_info = PurchaseTactics::getStatisticsId($sku, $warehouseCode);
            if ($tactics_id_info['is_success'] == false) return $tactics_id_info;
            $configInfo = $tactics_id_info['configInfo'];

            if(empty($configInfo)){
                return ['is_success'=>false,'suggest_num'=>$suggestNum,'message'=>'缺少配置值,新品备货数量获取失败','price'=>0,'left_stock'=>0];
            }
            $priceLimit = $configInfo['single_price'];
            $stockLimit = $configInfo['inventory_holdings'];
        }
        if(empty($price)){
            $price= ProductProvider::find()
                ->alias('t')
                ->select('sq.supplierprice')
                ->leftJoin(SupplierQuotes::tableName().' sq','t.quotes_id=sq.id')
                ->where(['t.sku'=>$sku,'t.is_supplier'=>1])->scalar();
            if(!$price){
                //没有维护采购单价，不走新品备货
                return ['is_success'=>false,'suggest_num'=>$suggestNum,'message'=>'sku没有维护单价，无法获取新品备货数量','price'=>0,'left_stock'=>0];
            }
        }
        if(empty($leftStock)){
            $leftStock = self::getLeftStockForCombine($sku,$warehouseCode);
        }
        if($price<=$priceLimit){
            //单价小于等于单价限制值，备货数量为订单数量加库存持有量
            $suggestNum = $leftStock+$stockLimit;
        }else{
            //单价大于单价限制值，备货数量为订单数量
            $suggestNum = $leftStock;
        }
        return ['is_success'=>true,'suggest_num'=>$suggestNum,'message'=>'新品备货数量获取成功','price'=>$price,'left_stock'=>$leftStock];
    }

    /**
     * @desc 根据sku，仓库获取到货时间标准差
     * @param $sku sku
     * @param $warehouseCode 仓库
     * @param null $calculateNum 标准差取值范围
     * @param null $avgArrivalTime 取值范围内的平均到货时间
     * @param null $arrivalTimeData 取值范围内的每次到货时间
     * @return array
     * return ['is_success'=>false,'arrival_time_standard_deviation'=>0,'message'=>string,weight_avg_period_value_range=>int,avg_arrival_time=>int];
     * is_success 是否计算成功 ， arrival_time_standard_deviation 到货时间标准差 message 提示信息 weight_avg_period_value_range 交期标准差取值范围   avg_arrival_time 范围内交期平均值
     */
    public static function getArrivalTimeStandardDeviation($sku,$warehouseCode,$calculateNum=null,$avgArrivalTime=null,$arrivalTimeData=null){
        if(empty($sku)||empty($warehouseCode)){
            return ['is_success'=>false,'arrival_time_standard_deviation'=>0,'message'=>'缺少sku或仓库，交期标准差计算失败','weight_avg_period_value_range'=>0,'avg_arrival_time'=>0];
        }
        if(empty($calculateNum)){
            //查询获取配置值
            $tactics_id_info = PurchaseTactics::getStatisticsId($sku, $warehouseCode);
            if ($tactics_id_info['is_success'] == false) return $tactics_id_info;
            $configInfo = $tactics_id_info['configInfo'];

            if(empty($configInfo)){
                return ['is_success'=>false,'arrival_time_standard_deviation'=>0,'message'=>'无法获取交期标准差配置值,交期标准差计算失败','weight_avg_period_value_range'=>0,'avg_arrival_time'=>0];
            }
            $calculateNum=$configInfo['weight_avg_period_value_range'];
        }
        if(empty($avgArrivalTime)||empty($arrivalTimeData)){
            $avgData = self::getAvgArrivalTime($sku,$warehouseCode,$calculateNum,'second');
            if(isset($avgData['is_success'])&&$avgData['is_success']==true){
                if(isset($avgData['avg_arrival_time'])&&isset($avgData['arrival_time_data'])){
                    $avgArrivalTime = $avgData['avg_arrival_time'];
                    $arrivalTimeData = $avgData['arrival_time_data'];
                }else{
                    return ['is_success'=>false,'arrival_time_standard_deviation'=>0,'message'=>'无法获取交期平均值，交期标准差计算失败','weight_avg_period_value_range'=>0,'avg_arrival_time'=>0];
                }
            }else{
                return ['is_success'=>false,'arrival_time_standard_deviation'=>0,'message'=>isset($avgData['message']) ? $avgData['message'] : '平均值计算失败，无法计算交期标准差','weight_avg_period_value_range'=>0,'avg_arrival_time'=>0];
            }
        }
        if(empty($arrivalTimeData)||count($arrivalTimeData)==1){
            //没有到货数据标准差默认返回为0
            return ['is_success'=>true,'arrival_time_standard_deviation'=>0,'message'=>'交期数据为空或只有一个，标准差计算成功','weight_avg_period_value_range'=>$calculateNum,'avg_arrival_time'=>0];
        }
        $circleLimit = count($arrivalTimeData);
        $totalSum=0;
        for ($i=0;$i<$circleLimit;$i++){
            $arrivalTime = isset($arrivalTimeData[$i]) ? $arrivalTimeData[$i]:0;
            $totalSum += pow(($arrivalTime-$avgArrivalTime)/86400,2);
        }
        $arrivalTimeStandardDeviation = round(sqrt($totalSum/($circleLimit-1)),2);
        return ['is_success'=>true,'arrival_time_standard_deviation'=>$arrivalTimeStandardDeviation,'message'=>'标准差计算成功','weight_avg_period_value_range'=>$calculateNum,'avg_arrival_time'=>$avgArrivalTime];
    }

    /**
     * @desc 根据sku仓库获取周转库存
     * @param  $sku sku
     * @param  $warehouseCode 仓库
     * @param  $prepareTime 备货周期
     * @param  $avgSales 平均销量
     * @param null $advanceTime 提前期
     *
     * @return array ['is_success'=>bool,'cycle_stock'=>float,'message'=>string];
     * is_success 是否计算成功 cycle_stock 周转库存（进1） message=>提示信息
     */
    public static function getCycleStock($sku,$warehouseCode,$prepareTime,$avgSales,$advanceTime=null){
        if(empty($sku)||empty($warehouseCode)){
            return ['is_success'=>false,'cycle_stock'=>0,'message'=>'sku或仓库为空,周转库存计算失败'];
        }
        if(empty($prepareTime)){
            return ['is_success'=>false,'cycle_stock'=>0,'message'=>'备货周期为空,周转库存计算失败'];
        }
        if(empty($avgSales)){
            return ['is_success'=>false,'cycle_stock'=>0,'message'=>'平均销量为空,周转库存计算失败'];
        }
        if(empty($advanceTime)){
            //没传提前期，查询获取提前期
            $tactics_id_info = PurchaseTactics::getStatisticsId($sku, $warehouseCode);
            if ($tactics_id_info['is_success'] == false) return $tactics_id_info;
            $configInfo = $tactics_id_info['configInfo'];
            $calculateNum = $configInfo['lead_time_value_range'];

            if(!$calculateNum){
                return ['is_success'=>false,'cycle_stock'=>0,'message'=>'提前期配置值获取失败，周转库存计算失败'];
            }
            $arrivalData= self::getAvgArrivalTime($sku,$warehouseCode,$calculateNum);
            if(isset($arrivalData['is_success'])&&$arrivalData['is_success']==true){
                $advanceTime = isset($arrivalData['avg_arrival_time']) ? $arrivalData['avg_arrival_time'] : 10;
            }else{
                return ['is_success'=>false,'cycle_stock'=>0,'message'=>'提前期交期平均值计算失败，周转库存计算失败'];
            }
        }
        return ['is_success'=>true,'cycle_stock'=>ceil(($prepareTime+$advanceTime)*$avgSales),'message'=>'周转库存计算成功'];
    }

    /**
     * @desc 根据sku仓库获取安全库存
     * @param $sku
     * @param $warehouseCode
     * @param $salesStandardDeviation 销量标准差
     * @param $serviceFactor 服务系数
     * @param null $arrivalTimeStandardDeviation 交期标准差
     * @param null $advanceTime 提前期
     * @return array ['is_success'=>bool,'safe_stock'=>float,'message'=>string];
     * is_success 是否成功计算 safe_stock 安全库存(进一) message 提示信息
     */
    public static function getSafeStock($sku,$warehouseCode,$serviceFactor,$salesStandardDeviation,$arrivalTimeStandardDeviation=null,$advanceTime=null){
        if(empty($sku)||empty($warehouseCode)){
            return ['is_success'=>false,'safe_stock'=>0,'message'=>'缺少sku或仓库，安全库存计算失败'];
        }
        if(empty($salesStandardDeviation)){
            return ['is_success'=>false,'safe_stock'=>0,'message'=>'缺少销量标准差值，安全库存计算失败'];
        }
        if(empty($serviceFactor)){
            return ['is_success'=>false,'safe_stock'=>0,'message'=>'服务系数为空，安全库存计算失败'];
        }
        if(empty($arrivalTimeStandardDeviation)||empty($advanceTime)){
            //获取tactics_id
            $tactics_id_info = PurchaseTactics::getStatisticsId($sku, $warehouseCode);
            if ($tactics_id_info['is_success'] == false) return $tactics_id_info;
            $configInfo = $tactics_id_info['configInfo'];

            if(empty($configInfo)){
                return ['is_success'=>false,'safe_stock'=>0,'message'=>'交期标准差和提前期配置值为空，安全库存计算失败'];
            }
            $standardDeviationCalculateNum = $configInfo['weight_avg_period_value_range'];
            $advanceCalculateNum = $configInfo['lead_time_value_range'];
        }

        if(empty($arrivalTimeStandardDeviation)){
            //查询获取到货时间标准差
            $arrivalTimeData = self::getArrivalTimeStandardDeviation($sku,$warehouseCode,$standardDeviationCalculateNum);
            if(isset($arrivalTimeData['is_success'])&&$arrivalTimeData['is_success']==true){
                $arrivalTimeStandardDeviation = isset($arrivalTimeData['arrival_time_standard_deviation']) ? $arrivalTimeData['arrival_time_standard_deviation'] : 0;
            }else{
                return ['is_success'=>false,'safe_stock'=>0,'message'=>'到货时间标准差计算失败，安全库存计算失败'];
            }
        }
        if(empty($advanceTime)){
            $avgArrivalTimeData = self::getAvgArrivalTime($sku,$warehouseCode,$advanceCalculateNum);
            if(isset($avgArrivalTimeData['is_success'])&&$avgArrivalTimeData['is_success']==true){
                $advanceTime = isset($avgArrivalTimeData['avg_arrival_time']) ? $avgArrivalTimeData['avg_arrival_time'] : 10;
            }else{
                return ['is_success'=>false,'safe_stock'=>0,'message'=>'提前期平均值计算失败，安全库存计算失败'];
            }
        }
        //安全库存公式  服务系数*平方根（销量标准差的平方*（提前期加到货时间标准差的平方））
        $safeStock = $serviceFactor*sqrt(pow($salesStandardDeviation,2)*($advanceTime+pow($arrivalTimeStandardDeviation,2)));
        return ['is_success'=>true,'safe_stock'=>$safeStock,'message'=>'安全库存计算成功'];
    }

    /**
     * @desc 通过sku,仓库获取sku一点次数内的平均到货时间
     * @param $sku sku
     * @param $warehouseCode 仓库编码
     * @param null $calculateNum 计算时间段（默认提前期配置值）
     * @param day $format 平均时间返回单位，默认天（day），可选秒(second)
     * @param null $arrivalTimeData 计算数据内容(单位秒)
     * @return array ['is_success'=>bool,'avg_arrival_time'=>float,'arrival_time_data'=>array]
     * is_success 是否成功 avg_arrival_time 平均到货时间(单位：天)  arrival_time_data 到货数据
     */
    public static function getAvgArrivalTime($sku,$warehouseCode,$calculateNum=null,$format='day',$arrivalTimeData=null){
        if(empty($sku)||empty($warehouseCode)){
            return ['is_success'=>false,'avg_arrival_time'=>0,'arrival_time_data'=>[],'message'=>'缺少sku或仓库，交期平均值计算失败'];
        }
        if(empty($calculateNum)){
            //查询获取配置值默认是计算提前期配置值
            $tactics_id_info = PurchaseTactics::getStatisticsId($sku, $warehouseCode);
            if ($tactics_id_info['is_success'] == false) return $tactics_id_info;
            $configInfo = $tactics_id_info['configInfo'];
            $calculateNum = $configInfo['lead_time_value_range'];

            if(!$calculateNum){
                return ['is_success'=>false,'avg_arrival_time'=>0,'arrival_time_data'=>[],'message'=>'提前期配置值为空，提前期平均值计算失败'];
            }
        }
        if(empty($arrivalTimeData)){
            if(in_array($warehouseCode,['FBA_SZ_AA'])){
                $arrivalTimeData = FbaAvgDeliveryLog::find()
                    ->select('arrival_time')
                    ->where(['sku'=>$sku,'warehouse_code'=>$warehouseCode])
                    ->orderBy('instock_time DESC')->limit($calculateNum)->column();
            }
            $warehouse_type_domestic = DataControlConfig::getMrpWarehouseStockList();
            if(in_array($warehouseCode,$warehouse_type_domestic)){
                $arrivalTimeData = InlandAvgDeliveryLog::find()
                    ->select('arrival_time')
                    ->where(['sku'=>$sku,'warehouse_code'=>$warehouseCode])
                    ->orderBy('instock_time DESC')->limit($calculateNum)->column();
            }
        }
        $count = 0;
        $arrivalTotalTime = 0;
        if(empty($arrivalTimeData)){
            //没有到货数据平均值默认返回为10
            return ['is_success'=>true,'avg_arrival_time'=>10,'arrival_time_data'=>[],'message'=>'交期数据为空'];
        }
        foreach ($arrivalTimeData as $arrival){
            $arrivalTotalTime+=$arrival;
            $count++;
        }
        if($format=='day'){
            $avgArrivalTime = round(($arrivalTotalTime/($count*86400)),2);
        }elseif($format=='second'){
            $avgArrivalTime = round(($arrivalTotalTime/($count)),2);
        }else{
            return ['is_success'=>false,'avg_arrival_time'=>0,'arrival_time_data'=>[],'message'=>'平均交期格式不符合要求'];
        }
        return ['is_success'=>true,'avg_arrival_time'=>$avgArrivalTime,'arrival_time_data'=>$arrivalTimeData,'message'=>'平均交期计算成功'];
    }


    /**
     * @desc 计算指定销售天数的 日均销量
     * @author Jolon
     * @param array|int $sales_list     销售天数内的销量列表或总销量
     * @return array
     */
    public function calculateDailySales($sales_list){
        $days = count($sales_list);

        // 验证参数是否满足要求
        if( $days <= 1 ){
            return ['is_success' => false,'message' => "计算销量均值错误[销售天数必须大于 1]"];
        }

        if(is_int($sales_list)){
            $sales_total = $sales_list;
        }else{
            $sales_total = array_sum($sales_list);
        }

        // 求得日均销量
        $daily_sale = $sales_total / $days;
        $daily_sale = sprintf("%.3f", $daily_sale);

        return ['is_success' => true,'data' => [ 'average' => $daily_sale ],'message' => ""];

    }

    /**
     * @desc （根据占比计算）计算 指定SKU 日均销量
     * @author Jolon
     * @param string $sku             SKU
     * @param string $warehouse_code  仓库代码
     * @param string $platform_code   平台代码
     * @return array
     */
    public function calculateDailySalesBySku($sku,$warehouse_code,$platform_code = null){
        // 验证参数是否满足要求
        if(empty($sku) OR empty($warehouse_code)){
            return ['is_success' => false,'message' => '计算日均销量[SKU或仓库缺失]'];
        }

        //获取仓库 所属备货策略 信息
        $tactics_id_info = PurchaseTactics::getStatisticsId($sku, $warehouse_code);
        if ($tactics_id_info['is_success'] == false) return $tactics_id_info;
        $tactics_id = $tactics_id_info['configInfo']['tactics_id'];

        // 获取 对应的备货策略 信息
        $model_tactics = PurchaseTactics::findOne(['id' => $tactics_id]);
        if(empty($model_tactics) OR $model_tactics->status != 1){
            return ['is_success' => false,'message' => "备货策略[$model_tactics->tactics_name]非启用状态"];
        }

        // 获取 备货策略 的销售平均值占比
        $day_sales_percent_list = PurchaseTacticsDailySales::find()
            ->where(
                ['=','tactics_id',$tactics_id]
            )->createCommand()->queryAll();

        if(empty($day_sales_percent_list)){
            return ['is_success' => false,'message' => "备货策略[$model_tactics->tactics_name]未设置销售平均值占比"];
        }

        $sale_average = 0;// 根据设置的日均销量的比值 计算日均销量
        foreach($day_sales_percent_list as $list_value){
            $day_value          = $list_value['day_value'];// 销量平均值天数
            $day_sales          = $list_value['day_sales'];// 销量平均值比值
            if(empty($day_value) OR $day_value <= 0){
                return ['is_success' => false,'message' => "销量平均值天数数据异常"];
            }

            // $sale_qty_list      = $this->getSaleQtyByDays($sku,$day_value,$warehouse_code,$platform_code);// 获取 销量平均值天数 内 每天的销量
            // if(empty($sale_qty_list)){
            //     return ['is_success' => false,'message' => "未获取到每天的销量"];
            // }
            // $sale_qty_average   = $this->calculateDailySales($sale_qty_list);// 获取 销量平均值天数 的 日均销量

            // if($sale_qty_average['is_success'] AND isset($sale_qty_average['data']['average'])){
            //     $sale_qty_average = $sale_qty_average['data']['average'];
            // }else{// 获取数据异常
            //     return ['is_success' => false,'message' => $sale_qty_average['message']];
            // }

            if ($day_value == 3) {
                $days_sales = self::find()->select('days_sales_3')->where(['sku'=>$sku, 'warehouse_code'=>$warehouse_code])->scalar();
            } elseif ($day_value == 7) {
                $days_sales = self::find()->select('days_sales_7')->where(['sku'=>$sku, 'warehouse_code'=>$warehouse_code])->scalar();
            } elseif ($day_value == 15) {
                $days_sales = self::find()->select('days_sales_15')->where(['sku'=>$sku, 'warehouse_code'=>$warehouse_code])->scalar();
            } elseif ($day_value == 30) {
                $days_sales = self::find()->select('days_sales_30')->where(['sku'=>$sku, 'warehouse_code'=>$warehouse_code])->scalar();
            } elseif ($day_value == 60) {
                $days_sales = self::find()->select('days_sales_60')->where(['sku'=>$sku, 'warehouse_code'=>$warehouse_code])->scalar();
            } elseif ($day_value == 90) {
                $days_sales = self::find()->select('days_sales_90')->where(['sku'=>$sku, 'warehouse_code'=>$warehouse_code])->scalar();
            }
            $sale_qty_average = $days_sales/$day_value; //日均销量
            $sale_average       += $day_sales * $sale_qty_average;// 占比 * 日均销量
        }

        //$sale_average = sprintf("%.3f", $sale_average);

        return ['is_success' => true,'data' => [ 'average' => $sale_average ],'message' => ""];

    }

    /**
     * @desc 计算 标准差
     * @author Jolon
     * @param float     $average   数组的平均值
     * @param array     $data_list 数值数组
     * @return array
     */
    public function calculateSDValue($average,$data_list){
        $days = count($data_list);// 数值数组 个数

        if($days <= 1 ){
            return ['is_success' => false,'message' => "计算标准差错误[数组元素列表必须大于 1]"];
        }else{
            $total = 0;
            foreach ($data_list as $lv){
                $total += pow(($lv - $average), 2);// 计算 平方值
            }

            // 标准差
            $value_SD = sqrt( $total/($days - 1 ) );// 计算 平方根值
            $value_SD = sprintf("%.3f", $value_SD);

            return ['is_success' => true,'data' => [ 'value_SD' => $value_SD ],'message' => ""];
        }
    }

    /**
     * @desc 计算 指定SKU 销量标准差
     * @author Jolon
     * @param string    $sku                SKU
     * @param string    $warehouse_code     仓库代码
     * @param string    $platform_code      平台代码
     * @return array
     */
    public function calculateSDValueBySku($sku,$warehouse_code,$platform_code = null){
        if(empty($sku) OR empty($warehouse_code)){
            return ['is_success' => false,'message' => "计算销量标准差[SKU或仓库缺失]"];
        }

        //获取仓库 所属备货策略 信息
        $tactics_id_info = PurchaseTactics::getStatisticsId($sku, $warehouse_code);
        if ($tactics_id_info['is_success'] == false) return $tactics_id_info;
        $tactics_id = $tactics_id_info['configInfo']['tactics_id'];

        // 获取 销量标准差取值范围
        $model_tactics = PurchaseTactics::find()->where(['=','id',$tactics_id])->one();
        if(empty($model_tactics) OR empty($model_tactics->sales_sd_value_range)){
            return ['is_success' => false,'message' => "仓库[$warehouse_code]的备货策略未设置或备货策略的销量标准差取值范围为设置"];
        }

        $sale_average   = $this->getSaleAverage($sku,$model_tactics->sales_sd_value_range,$warehouse_code,$platform_code);// 30天销量平均值
        if($sale_average['is_success'] AND isset($sale_average['data']['average'])){
            $sale_average = $sale_average['data']['average'];
        }else{// 获取数据异常
            return ['is_success' => false,'message' => $sale_average['message']];
        }

        // 获取 销量标准差取值范围 指定天数内 每天的销量
        $sale_qty_list      = $this->getSaleQtyByDays($sku,$model_tactics->sales_sd_value_range,$warehouse_code,$platform_code);
        $sale_value_SD      = $this->calculateSDValue($sale_average,$sale_qty_list);// 计算 销量标准差
        if($sale_value_SD['is_success'] AND isset($sale_value_SD['data']['value_SD'])){
            $sale_value_SD      = $sale_value_SD['data']['value_SD'];
            $sale_value_SD      = sprintf("%.3f", $sale_value_SD);
        }else{// 数据异常
            return ['is_success' => false,'message' => $sale_value_SD['message']];
        }

        return ['is_success' => true,'data' => [ 'sale_value_SD' => $sale_value_SD ],'message' => ""];

    }

    /**
     * @desc  计算指定SKU 的日均销量
     * @param string        $sku                SKU
     * @param int           $days               销售天数
     * @param string        $warehouse_code     仓库代码
     * @param string        $platform_code      平台代码
     * @return array
     */
    public function getSaleAverage($sku,$days,$warehouse_code,$platform_code = null){
        $sale_qty_list = $this->getSaleQtyByDays($sku,$days,$warehouse_code,$platform_code);

        // 计算销量平均值
        $average_value = $this->calculateDailySales($sale_qty_list);

        if($average_value['is_success'] AND isset($average_value['data']['average'])){
            $average      = $average_value['data']['average'];
        }else{// 数据异常
            return ['is_success' => false,'message' => $average_value['message']];
        }

        return ['is_success' => true,'data' => [ 'average' => $average ],'message' => ""];

    }

    /**
     * @desc 获得  指定SKU  指定天数范围内 每天的销量
     * @author Jolon
     * @param string    $sku            SKU
     * @param int       $days           销售天数
     * @param string    $warehouse_code 仓库代码
     * @param string    $platform_code  平台代码
     * @return array
     */
    public function getSaleQtyByDays($sku,$days,$warehouse_code,$platform_code = null){
        if(empty($days) OR $days <= 1) $days = 30;// 异常参数设置为30天

        // 定义销量列表
        $sale_days = [];
        // 获取排除日期
        $deleteDateArray = self::getDeleteData();

        $i          = 1;
        $count_day  = 1;
        while (true){
            $saleDate = date('Y-m-d',strtotime(" -$i days"));
            $i ++;
            if(in_array($saleDate,$deleteDateArray)){
                continue;
            }
            $sale_days[] = $saleDate;
            if($count_day == $days){
                break;
            }
            $count_day ++;
        }

        // 获取指定销售日期的销量
        $sale_qty_date_list = [];
        foreach($sale_days as $key => $date){
            $now_qty = $this->getOneSaleQtyByDay($sku,$date,$warehouse_code,$platform_code);

            $sale_qty_date_list[$date.'_'.$key] = $now_qty;
        }

        return $sale_qty_date_list;

    }

    /**
     * @desc 获取指定一天的销量
     * @author Jolon
     * @param string    $sku            SKU
     * @param int       $date           销售天数
     * @param string    $warehouse_code 仓库代码
     * @param string    $platform_code  平台代码
     * @return int
     */
    public function getOneSaleQtyByDay($sku,$date,$warehouse_code,$platform_code = null){
        //当前仓库
        $warehouse_code_arr = explode(',',$warehouse_code);
        //主仓
        $mrp_warehouse_main = DataControlConfig::getMrpWarehouseMain();
        //合仓
        $mrp_warehouse_he = DataControlConfig::getMrpWarehouseHe();
        if (!empty($mrp_warehouse_main) && !empty($mrp_warehouse_he)) {
            //如果当前仓库属于主仓，则拉取该仓库和合仓的的销量
            if (in_array($warehouse_code, $mrp_warehouse_main)) {
                $warehouse_code_arr = array_unique(array_merge($warehouse_code_arr, $mrp_warehouse_he));
            }
        }

        // 计算 SKU、日期、仓库、平台 下的总数量
        // 自动过滤掉 平台代码（）
        $total_sales = (new Query())->select("sum(sales) as total_sales")
            ->from("{{%sku_sale_details}}")
            ->andWhere(['sku' => $sku])
            ->andWhere(['in', 'warehouse_code', $warehouse_code_arr])
            ->andWhere(['sale_date'=> $date])
            ->andFilterWhere(['=','platform_code',$platform_code])
            ->scalar();

        if( $total_sales !== NULL){
            return $total_sales;
        }else{
            return 0;
        }

    }

    /**
     * 获取排除 日期
     * @return array
     */
    public static function getDeleteData(){
        $deleteDate = DataControlConfig::find()->select('values')->where(['type'=>'mrp_delete_date'])->scalar();
        if($deleteDate){
            $deleteDateArray = explode(',',$deleteDate);
        }else{
            $deleteDateArray = ['2018-11-10','2018-11-11','2018-11-12'];
        }

        return $deleteDateArray;
    }

    /**
     * @desc根据传入时间段计算汇总销量
     * @param $date 计算开始日期
     * @param $page 计算页码
     * @param $limit 计算每页数量
     * @param int $rangeMax 时间范围最大值
     * @param array $rangeArray 时间段对应字段
     * @return bool
     */
    public  static function countSkuSalesByFormat($date,$page,$limit,$rangeMax=90,$rangeArray=[],$warehouse_code=null){
        // $beginDate = self::getCountDateRange($date,$rangeMax);
        $deleteDateArray=self::getDeleteData();

        # 三十天前的2018-11-10和2018-11-11和2018-11-12的销量不统计，统计时间往后延三天
        $beginDate = self::getCountDateRange($date,30,$deleteDateArray);
        //获取时间段内的一段数据
        $salesDatas = SkuSaleDetails::find()
            ->where(['>=','sale_date',$beginDate])
            ->andWhere(['not in','sale_date',$deleteDateArray])
            ->andWhere(['<','sale_date',$date])
            ->andWhere(['in', 'warehouse_code', $warehouse_code])
            ->offset($page*$limit)
            ->limit($limit)
            ->asArray()->all();
        if(empty($salesDatas)){
            return false;
        }

        //主仓
        $mrp_warehouse_main = DataControlConfig::getMrpWarehouseMain();
        //合仓
        $mrp_warehouse_he = DataControlConfig::getMrpWarehouseHe();
        // 根据仓库列表运行多仓库MRP
        $warehouse_code_list = DataControlConfig::getMrpWarehouseList();
        foreach ($salesDatas as $sales){
            $range = self::getRange($sales,$date,$rangeArray,$deleteDateArray);
            self::saveSalesData($sales,$range, $warehouse_code_list, $mrp_warehouse_main, $mrp_warehouse_he);
        }
        unset($salesDatas);
        return true;
    }

    /**
     * 按销售日期以仓库、sku分组统计sku销量（去掉新品）
     * @return array
     * @throws \yii\db\Exception
     */
    public static function dataListGroupByWarehouseIdSku()
    {
        $tableName = self::tableName();
        $querySql = " SELECT warehouse_code,sku,days_sales_30 FROM {$tableName} ".
            " WHERE days_sales_30>0 and is_new != 1 ".
            " GROUP BY warehouse_code,sku ".
            " ORDER BY days_sales_30 desc ";
        return Yii::$app->db->createCommand($querySql)->queryAll();
    }

    /**
     * @desc 根据销量获取改数据所属时间段
     * @param $sales 销量数据
     * @param $date 开始时间
     * @param $rangeArray 时间段范围
     * @return array 返回所属时间段
     */
    public static function getRange($sales,$date,$rangeArray,$deleteDateArray=[]){
        $insertRange=[];
        foreach ($rangeArray as $key=>$value){
            $dateRangeBegin = self::getCountDateRange($date,$key,$deleteDateArray);
            if( strtotime($dateRangeBegin)<=strtotime($sales['sale_date'])&&strtotime($sales['sale_date'])<strtotime($date)){
                $insertRange[]=$value;
            }
        }
        return $insertRange;
    }

    /**
     * @desc 根据销量所属时间段范围对汇总数据进行新增或者自增
     * @param $sales
     * @param $range
     * @throws \yii\db\Exception
     */
    public static function saveSalesData($sales,$range,$warehouse_code_list, $mrp_warehouse_main, $mrp_warehouse_he){
        if(empty($range) || $sales['sales']<=0){
            return;
        }
        //如果此仓库 在仓库列表中且不是主仓 时，则需要跑出需求汇总
        if (in_array($sales['warehouse_code'], $warehouse_code_list) && (empty($mrp_warehouse_main) || !in_array($sales['warehouse_code'], $mrp_warehouse_main)) ) {
            $exist = self::find()->where(['sku'=>$sales['sku'],'warehouse_code'=>$sales['warehouse_code']])->exists();
            if($exist){
                foreach ($range as $value){
                    $counterArray[$value] = $sales['sales'];
                }
                self::updateAllCounters($counterArray,['sku'=>$sales['sku'],'warehouse_code'=>$sales['warehouse_code']]);
            }else{
                foreach ($range as $value){
                    $insertArray[$value]=$sales['sales'];
                }
                $insertArray['sku']=$sales['sku'];
                $insertArray['warehouse_code']=$sales['warehouse_code'];
                $insertArray['statistics_date']=date('Y-m-d H:i:s',time());
                Yii::$app->db->createCommand()->insert(self::tableName(),$insertArray)->execute();
            }
        }

        if (!empty($mrp_warehouse_main[0])) {
            //如果这个仓库属于合仓或是主仓，那么就将此仓的数据合并到主仓
            if ( (!empty($mrp_warehouse_he) && in_array($sales['warehouse_code'], $mrp_warehouse_he)) || in_array($sales['warehouse_code'], $mrp_warehouse_main) ) {
                $sales['warehouse_code'] = trim($mrp_warehouse_main[0]);
                $exist = self::find()->where(['sku'=>$sales['sku'],'warehouse_code'=>$sales['warehouse_code']])->exists();
                if($exist){
                    foreach ($range as $value){
                        $counterArray[$value] = $sales['sales'];
                    }
                    self::updateAllCounters($counterArray,['sku'=>$sales['sku'],'warehouse_code'=>$sales['warehouse_code']]);
                }else{
                    foreach ($range as $value){
                        $insertArray[$value]=$sales['sales'];
                    }
                    $insertArray['sku']=$sales['sku'];
                    $insertArray['warehouse_code']=$sales['warehouse_code'];
                    $insertArray['statistics_date']=date('Y-m-d H:i:s',time());
                    Yii::$app->db->createCommand()->insert(self::tableName(),$insertArray)->execute();
                }
            }
        }
    }

    /**
     * @desc 根据开始时间和范围值获取范围值的开始时间
     * @param $date 计算日期
     * @param int $format 时间段
     * @param array $deleteDate 排除日期
     * @return false|string
     */
    public static function getCountDateRange($date,$format=3,$deleteDate=[]){
        $i=1;
        $count_day = 1;
        while (true){
            $saleDate = date('Y-m-d',strtotime("$date -$i days"));
            $i++;
            if(in_array($saleDate,$deleteDate)){
                continue;
            }
            if($count_day==$format){
                return $saleDate;
            }
            $count_day++;
        }
    }


    /**
     * 更新排序分段值
     * @param $data
     * @param $sortSegment
     * @return bool
     */
    public static function updateSortSegment($data, $sortSegment)
    {
        $where = [
            'sku'=>$data['sku'],
            'warehouse_code'=>$data['warehouse_code']
        ];
        $model = self::find()->where($where)->one();
        if (empty($model)){
            return false;
        }

        //更新
        $params=['sort_segment'=>$sortSegment];
        return $model->updateAttributes($params);
    }

    /**
     * 所有仓库总销量数据
     */
    public static function allWarehouseTotalSalesData()
    {
        $tableName = self::tableName();
        $querySql = " SELECT warehouse_code,sum(days_sales_30) as total_sales FROM {$tableName} ".
            " WHERE days_sales_30>0 and is_new != 1 ".
            " GROUP BY warehouse_code ";
        return Yii::$app->db->createCommand($querySql)->queryAll();
    }

    /**
     * 缺货SKU列表数据 加入到 SKU MRP中
     * @param  array $range
     * @return bool|int
     */
    public static function stockOwesInsertMrp($range){
        $date = date('Y-m-d');
        $warehouse_type_domestic = DataControlConfig::getMrpWarehouseStockList();
        $mrp_warehouse_main = DataControlConfig::getMrpWarehouseMain(); //主仓
        $mrp_warehouse_he_stock = DataControlConfig::getMrpWarehouseHeStock(); //合仓-在途在库

        // 循环仓库，统计缺货列表 SKU，生成 MRP 记录
        if($warehouse_type_domestic){
            foreach($warehouse_type_domestic as $warehouse_code){
                if (!empty($mrp_warehouse_main[0]) && !empty($mrp_warehouse_he_stock) && in_array($warehouse_code,$mrp_warehouse_he_stock)) {
                    # 如果仓库属于合仓-在途在库，则将仓库变成主仓
                    $mrp_warehouse_code = trim($mrp_warehouse_main[0]);
                } else {
                    $mrp_warehouse_code = $warehouse_code;
                }

                // 查询当前仓库已经生成 MRP 的SKU
                $subQuery = (new Query())
                    ->select('sku')
                    ->from(SkuSalesStatisticsTotalMrp::tableName())
                    ->where(['=','LEFT(statistics_date,10)',$date])
                    ->andWhere(['warehouse_code' => $mrp_warehouse_code]);

                // 缺货列表的 SKU 未生成 MRP 记录的 SKU
                $stockOwesList = StockOwes::find()
                    ->where(['>','left_stock',0])
                    ->andWhere(['not in','sku',$subQuery])
                    ->andWhere(['warehouse_code' => $warehouse_code])
                    ->asArray()
                    ->all();
                unset($subQuery);

                // 生成 MRP 记录
                foreach($stockOwesList as $stockOwes){
                    foreach ($range as $value){
                        $insertArray[$value] = 0;
                    }

                    //如果是合仓stock的数据且 存在主仓配置，则仓库变为主仓
                    if ( in_array($stockOwes['warehouse_code'], $mrp_warehouse_he_stock) && !empty($mrp_warehouse_main[0]) ) {
                        $stockOwes['warehouse_code']= trim($mrp_warehouse_main[0]);
                    }
                    $insertArray['sku']             = $stockOwes['sku'];
                    $insertArray['warehouse_code']  = $stockOwes['warehouse_code'];
                    $insertArray['statistics_date'] = date('Y-m-d H:i:s',time());
                    $insertArray['is_stock_owes']   = 1;// 标记缺货列表导入的数据
                    try{
                        Yii::$app->db->createCommand()->insert(self::tableName(),$insertArray)->execute();
                    }catch(Exception $e){// 不处理的异常

                    }
                    unset($insertArray);
                }
                unset($stockOwesList);
            }
        }

        return true;
    }

    /**
     * 获取 SKU 的缺货数量【合仓逻辑】
     * @param      $sku
     * @param      $warehouseCode
     * @param null $main_warehouse
     * @param null $he_stock_warehouse
     * @return int
     */
    public static function getLeftStockForCombine($sku,$warehouseCode,$main_warehouse = null,$he_stock_warehouse = null){
        if(empty($main_warehouse))
            $main_warehouse = DataControlConfig::getMrpWarehouseMain();//主仓
        if(empty($he_stock_warehouse))
            $he_stock_warehouse = DataControlConfig::getMrpWarehouseHeStock();//合仓-在途在库缺货合仓

        //如果当前仓库属于主仓，则
        if (!empty($main_warehouse[0]) && !empty($he_stock_warehouse) && $warehouseCode==trim($main_warehouse[0])) {
            $he_stock_warehouse = array_unique(array_merge($main_warehouse, $he_stock_warehouse));
            $leftStock = StockOwes::find()->where(['sku'=>$sku])->andWhere(['in', 'warehouse_code', $he_stock_warehouse])->sum('left_stock');
        } else {
            $leftStock = StockOwes::find()->select('left_stock')->where(['sku'=>$sku,'warehouse_code'=>$warehouseCode])->scalar();
        }

        return intval($leftStock);
    }


}
