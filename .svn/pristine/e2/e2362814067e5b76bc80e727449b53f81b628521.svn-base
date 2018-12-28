<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/10
 * Time: 19:13
 */

namespace app\api\v1\models;


use app\config\Vhelper;

class ProductTaxRate extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%product_tax_rate}}';
    }
    /**
     *保存erp传过来的税率
     */
    public static function saveOne($v)
    {
        $models = self::find()->where(['sku'=>trim($v['sku'])])->one();
        //如果存在
        if (!empty($models)) {
            $models->tax_rate = isset($v['tax_rate'])?$v['tax_rate']:false; //出口退税税率
            $models->quality_random = isset($v['quality_random'])?$v['quality_random']:''; //
            $models->quality_level = isset($v['quality_level'])?$v['quality_level']:''; //
            $models->update_time = date('Y-m-d H:i:s',time());  //修改时间
            return $models->save(false);
        } else if (!empty($v['sku'])) {
            $modelb = new self;
            $modelb->sku = trim($v['sku']);
            $modelb->tax_rate = isset($v['tax_rate'])?$v['tax_rate']:false; //出口退税税率
            $modelb->quality_random = isset($v['quality_random'])?$v['quality_random']:''; //
            $modelb->quality_level = isset($v['quality_level'])?$v['quality_level']:''; //
            $modelb->update_time = date('Y-m-d H:i:s',time());  //修改时间
            $modelb->create_time = date('Y-m-d H:i:s',time()); //开发时间
            return $modelb->save(false);
        } else {
            return false;
        }
    }
}