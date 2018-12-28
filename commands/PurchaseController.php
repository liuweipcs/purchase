<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\api\v1\models\FreightUpdate;
use app\api\v1\models\PurchaseOrder;
use app\api\v1\models\PurchaseOrderItems;
use app\api\v1\models\SkuSalesStatistics;
use app\api\v1\models\Stock;
use app\config\Vhelper;
use app\models\Product;
use app\models\PurchaseAvg;
use app\models\PurchaseOrderItemsCostCalculate;
use app\models\PurchaseOrderOrders;
use app\models\PurchaseOrderPayType;
use app\models\PurchaseOrderShip;
use app\models\SampleInspect;
use app\models\SkuAvgLog;
use app\models\SkuSalesStatisticsTotal;
use app\models\SupplierNum;
use app\services\BaseServices;
use yii\console\Controller;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class PurchaseController extends Controller
{
    /**
     * 计算所有产品到货成本表
     */
    public  function actionConst()
    {
        date_default_timezone_set('Asia/Shanghai');
        $beginTime = strtotime(date('Y-m-d 00:00:00'));
        $endTime   = strtotime(date('Y-m-d 23:59:59'));
        $num = SupplierNum::find()->andWhere(['type'=>25])->orderBy('id DESC')->andWhere(['between','time',$beginTime,$endTime])->one();
        if($num){
            $pager = $num->num;
        }else{
            $pager = 0;
        }
        \Yii::$app->db->createCommand()->insert(SupplierNum::tableName(),[
            'num'=>$pager+1,
            'type'=>25,
            'time'=>time()
        ])->execute();
        $limit  = 2000;
        $query  = PurchaseOrderItems::find()
            ->alias('t')
            ->leftJoin(PurchaseOrder::tableName().' a','a.pur_number = t.pur_number')
            ->andWhere(['in','a.warehouse_code',['SZ_AA', 'ZDXNC', 'HW_XNC','FBA_SZ_AA']])
            ->andWhere(['not',['t.cty' => null]])
            ->orderBy('t.id ASC')
            ->offset($pager*$limit)
            ->limit($limit);
        $purchase = $query->all();
        if(empty($purchase)){
            exit();
        }
        foreach($purchase as $c)
        {
            $warehoue_code_s = ['SZ_AA', 'ZDXNC', 'HW_XNC','FBA_SZ_AA'];
            $warehoue_code   = PurchaseOrder::find()->select('warehouse_code')->where(['pur_number' => $c['pur_number']])->scalar();
            if (in_array($warehoue_code, $warehoue_code_s))
            {
                $re = PurchaseOrderItemsCostCalculate::find()->where(['check_id' => $c['id']])->one();
                if ($re) {
                    $re->is_check       = 1;
                    $re->num            = $c->cty;
                    $re->add_num        = $c->cty-$re->num >0 ? $c->cty-$re->num : 0;
                    $re->check_date     = date('Y-m-d');
                    $re->warehouse_code = PurchaseOrder::find()->select('warehouse_code')->where(['pur_number' => $c['pur_number']])->scalar();
                    $re->save();
                    echo $c['id'] . '--------------------update success' . "\r\n";
                } else {
                    $mode                 = new PurchaseOrderItemsCostCalculate();
                    $mode->check_id       = $c['id'];
                    $mode->is_check       = 1;
                    $mode->num            = $c->cty;
                    $mode->add_num        = $c->cty;
                    $mode->check_date     = date('Y-m-d');
                    $mode->warehouse_code = PurchaseOrder::find()->select('warehouse_code')->where(['pur_number' => $c['pur_number']])->scalar();
                    $mode->save(false);
                    echo $c['id'] . '--------------------insert success' . "\r\n";
                }
            } else {
                continue;
            }
        }
    }

    /**
     *
     */
    public  function actionConst1()
    {
        date_default_timezone_set('Asia/Shanghai');
        $num   =
        $dates = date('Y-m-d');
        $re    = PurchaseOrderItemsCostCalculate::find()->where(['check_date'=>$dates,'is_check'=>1,'is_end'=>0,'is_jisuan'=>0])->orderBy('check_id ASC')->asArray()->all();

        if($re)
        {
            foreach($re as $v)
            {
                $res =PurchaseOrderItems::find()->where(['id'=>$v['check_id']])->andWhere(['not', ['cty' => null]])->one();

                if($res)
                {
                    if ($res->cty >= $res->ctq || in_array($res->purNumber->purchas_status,[6,9,4,10])) {
                        $status = $this->getAvg($res,$re['add_num']);
                        if ($status) {
                            PurchaseOrderItemsCostCalculate::updateAll(['is_end' => 1, 'jisuan_update_time' => date('Y-m-d H:i:s')], ['id' => $v['id']]);
                            echo $v['id'] . '--------------------更新成功' . "\r\n";
                        } else{
                            continue;
                        }
                    } else {
                        //计算平均成本
                        $status = $this->getAvg($res,$re['add_num']);
                        if ($status) {
                            PurchaseOrderItemsCostCalculate::updateAll(['is_jisuan' => 1, 'jisuan_update_time' => date('Y-m-d H:i:s')], ['id' => $v['id']]);
                            echo $v['id'] . '--------------------更新成功' . "\r\n";
                        } else{
                            continue;
                        }
                    }
                } else {
                    continue;
                }

            }
        }
    }

    //新计算方法
    public function actionCalculteAvg(){
        date_default_timezone_set('Asia/Shanghai');
        $limit = 1000;
        $datas  = PurchaseOrderItems::find()
            ->alias('t')
            ->leftJoin(PurchaseOrder::tableName().' a','t.pur_number=a.pur_number')
            ->andWhere(['NOT IN','a.warehouse_code',['de-yida']])//排除样品仓的单
            ->andWhere(['in','a.purchas_status',[4,5,6,8,9,10]])
            ->andWhere(['t.is_end'=>0])
            ->andWhere(['or',['<','t.check_date',date('Y-m-d 00:00:00')],['t.check_date'=>null]])
            ->orderBy('t.id ASC')
            ->limit($limit)
            ->all();
        if(empty($datas)){
            echo 'success';
            exit();
        }
        foreach ($datas as $data){
            PurchaseOrderItems::updateAll(['check_date'=>date('Y-m-d H:i:s')],['id'=>$data->id]);
            if($data->is_end == 1){
                continue;
            }
            //海外仓计算数量由到货数量计算
            if(stripos($data->pur_number,'ABD')!==false){
                $num = !empty($data->rqy)&&$data->rqy>$data->ctq ? $data->ctq : $data->rqy;
                $add_num = $num - $data->avg_num > 0 ? $num - $data->avg_num : 0;
            }else{
                //其他采购单计算数量由入库数量计算
                $add_num = $data->cty - $data->avg_num > 0 ? $data->cty - $data->avg_num : 0;
            }
            //查询采购单是否在样品检验数据
            $exist = SampleInspect::find()->where(['pur_number'=>$data->pur_number])->exists();
            //计算数量大于零且采购单不是作废货撤销则计算平均成本
            if($add_num>0&&!in_array($data->purNumber->purchas_status,[4,10])&&!$exist){
                $this->getAvg($data,$add_num);
            }
            //入库入了减少且已经完成计算数量大于等于采购确认数量货采购单状态是作废，撤销，全部到货，部分到货不等待剩余则标记结束计算
            if(($add_num<=0&&$data->avg_num>=$data->ctq)||in_array($data->purNumber->purchas_status,[4,10,6,9])||$exist){
                PurchaseOrderItems::updateAll(['is_end'=>1],['id'=>$data->id]);
            }
        }
        echo 'success end';
    }

    /**
     * 计算平均成本
     * @param $data
     */
    public  function getAvg($data,$add_num)
    {
        //获取最后的价格
        $Product         = Product::find()->where(['sku' => $data->sku])->asArray()->one();
        if(empty($Product)){
            return false;
        }
        $available_stock = Stock::find()->select('available_stock')->where(['sku' => $data->sku])->sum('available_stock');
        //产品最后价格(产品表)
        $datas['last_price'] = !empty($Product['last_price']) ? $Product['last_price'] : $data->price;
//        $datas['cty'] = $data['cty'];
        //当前购买价格 base_price 不含税价  price 改版之前是不含税价 之后是含税价
        $datas['price'] = $data->base_price ==0 ? $data->price : $data->base_price;
        $datas['total_ctq'] = PurchaseOrderItems::find()->select('ctq')->where(['pur_number'=>$data->pur_number])->sum('ctq');
        //比例ceil(sprintf( "%.3f ",1/3)*100)/100小数点后三位进一
        $ratio = $add_num/$datas['total_ctq'];
        //运费
        $freight                = PurchaseOrderShip::find()->select('freight')->where(['pur_number' => $data->pur_number])->scalar();
        $newfreight                = PurchaseOrderPayType::find()->select('freight')->where(['pur_number' => $data->pur_number])->scalar();
        $freight=!empty($freight)?$freight:($newfreight ? $newfreight : 0);
        if(empty($available_stock) && empty($datas['last_price']) && empty($datas['cty']) && empty($datas['price']) && empty($ratio) && empty($freight) && empty($data->cty))
        {
            return;
        }

        //每个产品运费
        $oldFreight = $freight/$datas['total_ctq'];
        //每个产品带运费的成本
        //$oldCost = $datas['price']+$oldFreight;
        //计算平均采购成本带运费
        //$re = $Product['is_first'] == 1 ? $this->getCalculateResult($available_stock,$oldCost,$add_num,$datas['price'],$ratio,$freight):$this->getCalculateResult($available_stock,$datas['last_price'],$add_num,$datas['price'],$ratio,$freight);
        //计算平均采购成本不带运费
        $re1 = $Product['is_first'] == 1 ? $this->getCalculateResult($available_stock,$datas['price'],$add_num,$datas['price'],$ratio,0):$this->getCalculateResult($available_stock,$Product['avg_purchase_price'],$add_num,$datas['price'],$ratio,0);
        //计算平均运费成本
        //海外仓运费修正数据
        $freightExist = FreightUpdate::find()->where(['pur_number'=>$data->pur_number])->one();
        //如果查到修正数据则用修正数据代替查询数据
        if($freightExist){
            $ratio=1;
            $freight = $add_num*$freightExist->avg_freight;
            $oldFreight = $freightExist->avg_freight;
        }
        $re2 = $Product['is_first'] == 1 ? $this->getCalculateResult($available_stock, $oldFreight, $add_num, 0, $ratio, $freight) : $this->getCalculateResult($available_stock, $Product['avg_freight'], $add_num, 0, $ratio, $freight);
        //平均成本表
        $exist = PurchaseAvg::find()->where(['sku'=>$data->sku])->orderBy('id DESC')->one();
        if(!empty($exist)&& $exist->avg_freight ==$re2 && $exist->avg_purchase_price==$re1){

        }else{
            $avg                 = new PurchaseAvg();
            $avg->warehouse_code = $data->purNumber->warehouse_code;
            $avg->sku            = $data->sku;
            $avg->avg_price      = (100*$re1+100*$re2)/100;//小数点后三位进1
            $avg->create_time    = date('Y-m-d H:i:s', time());
            $avg->avg_freight    = $re2;
            $avg->avg_purchase_price    = $re1;
            $status              = $avg->save();
        }
        \Yii::$app->db->createCommand()->insert(SkuAvgLog::tableName(),[
            'sku'=> $data->sku,
            'freight'=> $freight,
            'old_last_price'=> $Product['is_first'] == 1 ? $datas['price']: $datas['last_price'],
            'new_last_price'=> (100*$re1+100*$re2)/100,
            'create_date'=> date('Y-m-d H:i:s',time()),
            'purchase_price'=> $datas['price'],
            'pur_number'=> $data->pur_number,
            'available_stock'=> $available_stock,
            'old_avg_freight'=> $Product['is_first'] ==1 ? ceil(sprintf('%4f',$oldFreight)*1000)/1000 : $Product['avg_freight'],
            'new_avg_freight'=> $re2,
            'old_purchase_price'=> $Product['is_first'] ==1 ?$datas['price'] : $Product['avg_purchase_price'],
            'new_purchase_price'=> $re1,
            'is_first'=> $Product['is_first'],
            'ratio'=> $add_num.'/'.$datas['total_ctq'],
            'cty'=>$add_num
        ])->execute();
        PurchaseOrderItems::updateAllCounters(['avg_num'=>$add_num],['id'=>$data->id]);
        Product::updateAll(['last_price'=>(100*$re1+100*$re2)/100,'avg_freight'=>$re2,'avg_purchase_price'=>$re1,'is_first'=>0],['sku'=>$data->sku]);
        echo 'Calculte success'.PHP_EOL;
        //}
        return true;



    }

    protected function getCalculateResult($available_stock,$oldCost,$cty,$newCost,$ratio,$freight){
        if($available_stock+$cty==0){
            return 0;
        }
        $result = (($available_stock * $oldCost) + ($cty*$newCost) + ($ratio)*$freight)/ ($available_stock+$cty);
        return ceil(sprintf('%3f',$result)*100)/100;
    }
    /**
     * 计算采购平均成本
     * （可用库存*最后价格+上架数量*购买价格+(上架数量/总购买数量)*运费）/可用库存+上架数量
     */
    public  function  actionCAvg()
    {

        //查询采购单是东莞仓并且是全到货,部分到货的
        $purchase   = PurchaseOrder::find()->where(['warehouse_code'=>'SZ_AA','purchas_status'=>[6,8]])->joinWith(['purchaseOrderItems'])->asArray()->limit(1)->all();

        foreach($purchase as $v)
        {
            foreach($v['purchaseOrderItems'] as $c)
            {
                if(!empty($c['cty'])) {


                    $data = [];
                    //获取最后的价格
                    $Product         = Product::find()->where(['sku' => $c['sku']])->asArray()->one();
                    $available_stock = Stock::find()->select('available_stock')->where(['sku' => $c['sku']])->asArray()->scalar();
                    //可用数量(库存表)
                    $data['available_stock'] = $available_stock;
                    //产品最后价格(产品表)
                    $data['last_price'] = !empty($Product['last_price']) ? $Product['last_price'] : $c['price'];
                    //仓库编码
                    $data['warehouse_code'] = $v['warehouse_code'];
                    //上架数量
                    $data['cty'] = $c['cty'];
                    //当前购买价格
                    $data['price'] = $c['price'];
                    //总购买数量(总确认数量)
                    $data['total_cty'] = PurchaseOrderItems::find()->where(['pur_number' => $v['pur_number']])->sum('ctq');
                    //运费
                    $freight                = PurchaseOrderShip::find()->select('freight')->where(['pur_number' => $v['pur_number']])->scalar();
                    $data['shipping_costs'] = !empty($freight) ? $freight : 1;
                    $data['sku']            = $c['sku'];
                    $status = $this->getAvgs($data);
                    if ($status) {
                        echo $c['sku'] . '--------------------计算成功' . "\r\n";
                    } else {
                        echo $c['sku'] . '--------------------计算失败' . "\r\n";
                    }
                }else{
                    continue;
                }
            }

        }


    }
    protected function getAvgs($data)
    {
        $re =  (
                ($data['available_stock'] * $data['last_price']) +
                ($data['cty']*$data['price']) +
                ($data['cty']/$data['total_cty'])*$data['shipping_costs']
            )/
            ($data['available_stock']+$data['cty']);

        $avg                 = new PurchaseAvg();
        $avg->warehouse_code = $data['warehouse_code'];
        $avg->sku            = $data['sku'];
        $avg->avg_price      = $re;
        $avg->create_time    = date('Y-m-d H:i:s', time());
        $status              = $avg->save();


        return $status;
    }

    /**
     * 把平台的sku销量写入销量总表
     */
    public function  actionCalculate()
    {
        set_time_limit(0);
        SkuSalesStatisticsTotal::deleteAll();
        $model = SkuSalesStatistics::find()->select('sku,warehouse_code')->distinct()->where(['statistics_date'=>date('Y-m-d',time())])->asArray()->all();
        if($model)
        {
            foreach ($model as $v)
            {
                //如果不是捆绑销售就进去
                $s=Product::find()->where(['sku'=>$v['sku'],'product_type'=>1,'product_is_multi'=>[0,1]])->scalar();
                $war =['FBA-WW','SZ_AA','ZDXNC','HW_XNC'];
                //$war =['SZ_AA','ZDXNC','HW_XNC'];
                if (!empty($v['sku']) && $s && in_array($v['warehouse_code'],$war))
                {

                    $models                 = new SkuSalesStatisticsTotal();
                    $models->sku            = $v['sku'];
                    $models->warehouse_code = $v['warehouse_code'];
                    $status =$models->save(false);
                    if($status)
                    {
                        echo $v['sku'] . '--------------------插入成功' . "\r\n";
                    } else {
                        echo $v['sku'] . '--------------------失败成功' . "\r\n";
                    }

                } else {

                    continue;
                }

            }
        } else {
            Vhelper::ToMail('没有今天的销量','少年,没有今天的东莞仓销量');
        }
    }
}
