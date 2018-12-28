<?php

namespace app\api\v1\models;

use app\config\Vhelper;
use Yii;
use app\models\ProductTicketedPointLog;
use linslin\yii2\curl;
use yii\db\Query;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%supplier_product}}".
 *
 * @property integer $id
 * @property string $sku
 * @property integer $product_category_id
 * @property integer $product_brand_id
 * @property double $product_cost
 * @property double $last_price
 * @property integer $last_provider_id
 * @property integer $provider_type
 * @property string $currency
 * @property integer $product_status
 * @property integer $product_type
 * @property double $product_weight
 * @property double $product_length
 * @property double $product_width
 * @property double $product_height
 * @property string $product_combine_code
 * @property integer $product_combine_num
 * @property string $product_bind_code
 * @property integer $product_line_id
 * @property string $keywords
 * @property string $measure_info
 * @property integer $product_is_attach
 * @property integer $product_is_bak
 * @property integer $product_bak_type
 * @property integer $product_prearrival_days
 * @property integer $product_bak_days
 * @property integer $original_material_type_id
 * @property string $product_pack_code
 * @property string $product_package_code
 * @property integer $product_package_max_nums
 * @property integer $product_is_storage
 * @property integer $product_original_package
 * @property integer $product_is_new
 * @property integer $product_is_multi
 * @property integer $provider_level_id
 * @property integer $create_user_id
 * @property integer $modify_user_id
 * @property string $create_time
 * @property string $modify_time
 * @property string $drop_shipping
 * @property string $drop_shipping_sku
 * @property string $product_cn_link
 * @property string $product_en_link
 * @property integer $sku_mark
 * @property integer $product_to_way_package
 * @property string $stock_reason
 * @property string $product_label_proces
 * @property double $pack_product_length
 * @property double $pack_product_width
 * @property double $pack_product_height
 * @property double $gross_product_weight
 * @property integer $is_to_mid
 * @property string $to_mid_time
 * @property integer $state_type
 * @property string $checked_time
 * @property string $uploadimgs
 * @property string $label
 * @property string $buycomp_note
 * @property string $quality_note
 * @property integer $hot_rank
 * @property integer $min_purchase
 * @property integer $inquirer_id
 * @property integer $purchase_id
 * @property string $aliases_name
 * @property integer $instructions
 * @property string $quality_standard
 * @property integer $quality_lable
 * @property string $quality_remark
 * @property string $image_remark
 * @property integer $buy_sample_type
 * @property string $reference_price
 * @property string $picking_name
 * @property string $picking_ename
 * @property string $customs_code
 * @property string $declare_ename
 * @property string $declare_cname
 * @property string $declare_price
 * @property double $tariff
 * @property double $tax_rate
 * @property string $onlie_remark
 * @property integer $source
 * @property integer $is_push
 */
class Product extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product}}';
    }

    /**
     * 保存数据与更新
     * @param $datass
     * @return mixed
     * @throws \yii\db\Exception
     */
    public static function FindOnes($datass)
    {
        //Vhelper::dump($datass);
        if(!empty($datass))
        {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                foreach ($datass as $v) {

                    ProductTaxRate::SaveOne($v);

                    //检测sku是否存在
                    $model = self::find()->where(['sku' => $v['sku']])->one();
                    $rate_flag = false;//是否需要修改 is_back_tax:是否可退税
                    
                    if ($model) {
                        $base_tax_rate = $model->tax_rate;
                        self::SaveOne($model, $v);
                        if (!empty($v['description'])) {
                            ProductDescription::SaveOne($v['description']);
                        }
                        if (isset($v['tax_rate']) && $base_tax_rate != $v['tax_rate']) {
                            $rate_flag = true;
                        }
                        $data['success_list'][] = $model->attributes['sku'];
                        $data['failure_list'][] = '';
                    } else {
                        $model = new self;
                        self::SaveOne($model, $v);
                        if (!empty($v['description'])) {
                            ProductDescription::SaveOne($v['description']);
                        }
                        if (isset($v['tax_rate'])) {
                            $rate_flag = true;
                        }
                        $data['success_list'][] = $model->attributes['sku'];
                        $data['failure_list'][] = '';
                    }
                    if ($rate_flag) {
                        $supplier_model = ProductProvider::find()->where(['sku'=>$v['sku'],'is_supplier'=>1])->one();
                        if (!empty($supplier_model)) {
                            $quote_model = SupplierQuotes::find()->select('id,pur_ticketed_point,is_back_tax')->where(['id'=>$supplier_model->quotes_id])->one();
                            if ($quote_model && $quote_model->pur_ticketed_point > 0) {
                                $is_back_tax = Vhelper::getProductIsBackTax($v['tax_rate'], $quote_model->pur_ticketed_point);
                                if ($is_back_tax != $quote_model->is_back_tax) {
                                    $quote_model->is_back_tax = $is_back_tax;
                                    $quote_model->save(false);
                                    ProductTicketedPointLog::insertLog($v['sku'], $quote_model->pur_ticketed_point, $is_back_tax);
                                }
                            }
                        }
                    }
                }
                $transaction->commit();
                return $data;

            } catch (Exception $e) {
                $transaction->rollBack();
            }
        }
    }

    /**
     * 保存一条数据
     * @param $model
     * @param $v
     * @return mixed
     */
    public static function SaveOne($model,$v)
    {
        //$model->id                    = $v['id'];
        $model->sku                   = $v['sku'];
        $model->product_linelist_id   = isset($v['product_linelist_id'])?$v['product_linelist_id']:'';
        //$model->last_price            = $v['last_price']; 自己更新平均采购成本，不接受erp数据
        $model->product_category_id   = $v['product_category_id'];
        $model->product_status        = $v['product_status'];
        $model->uploadimgs            = $v['uploadimgs'];
        $model->create_time           = $v['create_time'];
        $model->product_cost          = $v['product_cost'];
        $model->create_id             = $v['create_user_id'];
        $model->product_cn_link       = $v['product_en_link'];
        $model->product_en_link       = $v['product_en_link'];
        $model->product_type          = $v['product_type'];
        $model->product_package_code  = $v['product_package_code'];
        $model->purchase_packaging    = $v['product_to_way_package'];
        $model->product_is_multi      = isset($v['product_is_multi'])?$v['product_is_multi']:'0';
        $model->tax_rate              = isset($v['tax_rate']) ? $v['tax_rate'] : 0.00;
        $model->declare_cname         = isset($v['declare_cname']) ? $v['declare_cname'] : '';
        $model->declare_unit          = isset($v['declare_unit']) ? $v['declare_unit'] : '';
        $model->product_model_out     = isset($v['product_model_out']) ? $v['product_model_out'] : '';
        $model->export_cname          = isset($v['export_cname']) ? $v['export_cname'] : '';
        $model->is_inspection         = isset($v['is_inspection']) ? $v['is_inspection'] : 0;
        $model->is_boutique           = isset($v['is_boutique']) ? $v['is_boutique'] : 0;// 同步自ERP是否为精品


        self::skuWeightMarkUpdate(['sku' => $model->sku,'is_boutique' => $model->is_boutique]);// 更新是否是新品标记

        return $model->save();
    }


    /**
     * 即时 同步修改的数据到 ERP
     * @param $data
     * @return bool
     */
    public static function pushProductInfo($data){
        if(empty($data) OR (!isset($data['product_sku']) AND !isset($data['supplier_code']))){// 验证数据
            return false;
        }

        $curl   = new curl\Curl();
        $url    = Yii::$app->params['ERP_URL'].'/products/productgoods/syncproductinfo/';

        if(isset($data['product_sku'])){
            $product_supplier_info = Product::find()->alias('t')
                ->select('b.id,t.sku,b.supplier_code,c.supplierprice,d.supplier_name,d.invoice')
                ->leftJoin(ProductProvider::tableName() . ' b', 't.sku=b.sku')
                ->leftJoin(SupplierQuotes::tableName() . ' c', 'b.quotes_id=c.id')
                ->leftJoin(Supplier::tableName() . ' d', 'b.supplier_code=d.supplier_code')
                ->where(['b.is_supplier' => 1])
                ->andWhere(['t.sku' => $data['product_sku']])
                ->asArray()
                ->one();

            $data['supplierprice']  = isset($product_supplier_info['supplierprice'])?$product_supplier_info['supplierprice']:'';
            $data['invoice']        = isset($product_supplier_info['invoice'])?$product_supplier_info['invoice']:'';
        }
        if(!empty($data['supplier_code'])){
            $data['supplier_name'] = Supplier::find()->select('supplier_name')->where(['supplier_code' => $data['supplier_code']])->scalar();
        }

        $s = $curl->setPostParams([
            'product_data' => $data
        ])->post($url);

        $s = json_decode($s);
        if(isset($s->status) AND $s->status == 'success'){
            return true;
        }else{
            return isset($s->message)?$s->message:false;
        }
    }


    /**
     * 更新 SKU 加重标记  是否是新品
     */
    public static function skuIsNewMarkUpdate(){
        // 标记为 非新品
        $connection     = Yii::$app->db;
        $sql_update     = " UPDATE pur_cache_product_mark_data 
                         SET is_new=0,is_sync=0
                         WHERE is_new=1 
                         AND sku IN(SELECT sku FROM pur_purchase_order_items GROUP BY sku )";

        $command        = $connection->createCommand($sql_update);
        $res            = $command->execute();


        // 标记为 新品
        $sql_update     = " UPDATE pur_cache_product_mark_data 
                         SET is_new=1,is_sync=0
                         WHERE is_new=0
                         AND sku NOT IN(SELECT sku FROM pur_purchase_order_items GROUP BY sku );";

        $command        = $connection->createCommand($sql_update);
        $res            = $command->execute();

        return intval($res);
    }

    /**
     * 更新 SKU 加重标记  是否是重点SKU
     */
    public static function skuIsWeightDot(){
        $connection     = Yii::$app->db;

        // 重点SKU
        $sql_insert     = "UPDATE pur_cache_product_mark_data SET is_weightdot=0,is_sync=0
                            WHERE is_weightdot=1 AND sku NOT IN(SELECT sku FROM pur_product WHERE is_weightdot=1)";
        $command        = $connection->createCommand($sql_insert);
        $res            = $command->execute();

        $sql_insert     = "UPDATE pur_cache_product_mark_data SET is_weightdot=1,is_sync=0
                            WHERE is_weightdot=0 AND sku IN(SELECT sku FROM pur_product WHERE is_weightdot=1)";
        $command        = $connection->createCommand($sql_insert);
        $res            = $command->execute();

        return intval($res);
    }

    /**
     * 更新或新增SKU 加重[重包精新]标记 记录
     * @param $data
     * @return bool
     */
    public static function skuWeightMarkUpdate($data){
        if(!isset($data['sku'])) return false;
        $sku     = trim($data['sku']);

        // 查询是否有记录
        $have_old = (new Query())
            ->select('*')
            ->from("pur_cache_product_mark_data")
            ->where(['sku' => $sku])
            ->one();

        $update_data = [];

        if($have_old){// 更新
            $have_id = $have_old['id'];

            // 判断是否有变更
            if(isset($data['is_weightdot']) AND $data['is_weightdot'] != $have_old['is_weightdot']){
                $update_data['is_weightdot']    = intval($data['is_weightdot']);
            }
            if(isset($data['is_boutique'])  AND $data['is_boutique'] != $have_old['is_boutique']){
                $update_data['is_boutique']     = intval($data['is_boutique']);
            }
            if(isset($data['is_repackage']) AND $data['is_repackage'] != $have_old['is_repackage']){
                $update_data['is_repackage']    = intval($data['is_repackage']);
            }
            if(isset($data['is_new'])       AND $data['is_new'] != $have_old['is_new']){
                $update_data['is_new']          = intval($data['is_new']);
            }
            if(empty($update_data)) return false;

            $update_data['is_sync']     = 0;
            $update_data['update_time'] = date('Y-m-d H:i:s');

            $res = Yii::$app->db->createCommand()->update('pur_cache_product_mark_data', $update_data, "id=$have_id")->execute();
        }else{// 插入

            if(isset($data['is_weightdot'])){ $update_data['is_weightdot']  = intval($data['is_weightdot']);}
            if(isset($data['is_boutique'])) { $update_data['is_boutique']   = intval($data['is_boutique']);}
            if(isset($data['is_repackage'])){ $update_data['is_repackage']  = intval($data['is_repackage']);}
            if(isset($data['is_new']))      { $update_data['is_new']        = intval($data['is_new']);}

            if(empty($update_data)) return false;

            $update_data['sku']         = $sku;
            $update_data['is_sync']     = 0;
            $update_data['update_time'] = date('Y-m-d H:i:s');

            $res = Yii::$app->db->createCommand()->insert('pur_cache_product_mark_data', $update_data)->execute();
        }

        return $res?true:false;
    }

}
