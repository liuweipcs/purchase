<?php

namespace app\services;
use app\config\Vhelper;
use app\models\Product;
use app\models\ProductCategory;
use app\models\WarehouseMin;
use app\models\WarehousePurchaseTactics;
use app\models\PrefixNumber;
use Yii;
use yii\web\HttpException;

/**
 * @desc 公共方法文件，该初的方法适用于M,V,C任何模块
 * @author Jimmy
 * @date 2017-04-10 18:34:11
 */
class CommonServices {

    /**
     * @desc 根据传入的数值，返回对应的label
     * @param array $data key=>value 的键值对数组
     * @param string $val 传入的value
     * @return string $key 返回对应的key
     * @author Jimmy
     * @date 2017-04-10 18:42:11
     */
    function getOptionsLabel($data = [], $val = '') {
        foreach ($data as $key => $value) {
            if ($val == $value) {
                return $key;
            } else {
                return;
            }
        }
    }
    /**
     * @desc 获取默认供应商信息
     * @param string $sku 需要查询的SKU数据信息
     * @return array $res 查询的数据信息
     * @author Jimmy
     * @date 2017-04-11 14:05:11
     */
    public static function getDefSupplier($sku=''){
        $model=new Product();
        $map['pur_product.sku']=$sku;
        $map['is_supplier']=1;
        $res=$model->find()->joinWith('oneSupplier')->where($map)->asArray()->one();
        return $res;
    }
    /**
     * @desc 获取产品的分类数据
     * @param string $id 产品分类ID
     * @author Jimmy
     * @date 2017-04-11 14:46:11
     */
    public static function getCategory($id=''){
        $model=new ProductCategory();
        $map['id']=$id;
        $res=$model->find()->where($map)->asArray()->one();
        return $res;
    }
    /**
     * @desc 获取对应的补货厕策略
     * @param string $warehouse_code 仓库编码
     * @param string $pattern 补货模式
     * @author Jimmy
     * @date 2017-04-11 15:11;11
     */
    public static function getTactics($warehouse_code,$pattern){
        $str='';
        if($pattern=='min'){
            $model=new WarehouseMin();
            $map['warehouse_code']=$warehouse_code;
            $res=$model->find()->where($map)->asArray()->one();
            if($res){
                $str.='安全库存天数:'."(<font color='red'>{$res['days_safe']}</font>)</br>";
                $str.='最小补货天数:'."(<font color='red'>{$res['days_min']}</font>)</br>";
                $str.='安全调拨天数:'."(<font color='red'>{$res['days_safe_transfer']}</font>)</br>";
            }else{
                $str='无';
            }
            return $str;
        } elseif ($pattern=='def') {
            $model=new WarehousePurchaseTactics();
            $map['warehouse_code']=$warehouse_code;
            $res=$model->find()->where($map)->asArray()->all();
            if($res){
                foreach ($res as $val){
                    $str.=Yii::$app->params['type'][$val['type']].":生产(<font color='red'>{$val['days_product']}</font>),物流(<font color='red'>{$val['days_logistics']}</font>),安全库存(<font color='red'>{$val['days_safe_stock']}</font>),采购频率(<font color='red'>{$val['days_frequency_purchase']}</font>)</br>";
                }
                return $str;
            }
        }
    }
    /**
     *                             _ooOoo_
     *                            o8888888o
     *                            88" . "88
     *                            (| -_- |)
     *                            O\  =  /O
     *                         ____/`---'\____
     *                       .'  \\|     |//  `.
     *                      /  \\|||  :  |||//  \
     *                     /  _||||| -:- |||||-  \
     *                     |   | \\\  -  /// |   |
     *                     | \_|  ''\---/''  |   |
     *                     \  .-\__  `-`  ___/-. /
     *                   ___`. .'  /--.--\  `. . __
     *                ."" '<  `.___\_<|>_/___.'  >'"".
     *               | | :  `- \`.;`\ _ /`;.`/ - ` : | |
     *               \  \ `-.   \_ __\ /__ _/   .-` /  /
     *          ======`-.____`-.___\_____/___.-`____.-'======
     *                             `=---='
     *          ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
     *                     高山仰止,景行行止.虽不能至,心向往之。
     * User: ztt
     * Date: 2017/9/15 0015
     * Description: CommonServices.php      
    */
    public static function getNumber($prefix)
    {

        $map['prefix'] = $prefix;
        $number        = '';
        $res           = PrefixNumber::find()->where($map)->one();
        if($res)
        {
            //插入一条数据
            $res->prefix = $prefix;
            $res->number += 1;
            $res->date = date('Y-m-d');
            $res->note = '采购订单';
            $res->save();
            //拼接单据号
            $number = $prefix . str_pad($res->number, 6, "0", STR_PAD_LEFT);
            if(!Yii::$app->cache->add($number,date('Y-m-d H:i:s',time()),600)){
                $number=self::getNumber($prefix);
            }
        } else {
            //初始化
            $model         = new PrefixNumber();
            $model->prefix = $prefix;
            $model->number = 1;
            $model->date   = date('Y-m-d');
            $model->note   = '采购订单';
            $model->save();
            //拼接单据号
            $number        = $prefix . str_pad($model->number, 6, "0", STR_PAD_LEFT);
            if(!Yii::$app->cache->add($number,date('Y-m-d H:i:s',time()),600)){
                $number=self::getNumber($prefix);
            }
        }

        return $number;
    }
    
    /**
     * 获取修改的内容
     * @param object $model
     * @param array $data
     */
    public static function getUpdateData($model, $data)
    {
        $update_data = [];
        foreach ($data as $field=>$v) {
            if ($model->isAttributeChanged($field, false)) {
                if (is_array($v)) {
                    $name = $v[0];
                    $from = isset($v[1][$model->getOldAttribute($field)]) ? $v[1][$model->getOldAttribute($field)] : $model->getOldAttribute($field);
                    $to = isset($v[1][$model->$field]) ? $v[1][$model->$field] : $model->$field;
                } else {
                    $name = $v;
                    $from = $model->getOldAttribute($field);
                    $to = $model->$field;
                }
                $update_data[] = [$field, $name, $from, $to];
            }
        }
        
        return $update_data;
    }
}
