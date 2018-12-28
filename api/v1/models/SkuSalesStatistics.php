<?php

namespace app\api\v1\models;

use app\config\Vhelper;
use Yii;
use yii\behaviors\TimestampBehavior;
/**
 * This is the model class for table "pur_sku_sales_statistics".
 *
 * @property integer $id
 * @property string $sku
 * @property string $warehouse_code
 * @property string $days_sales_3
 * @property string $days_sales_7
 * @property string $days_sales_15
 * @property string $days_sales_30
 * @property string $days_sales_60
 * @property string $days_sales_90
 * @property string $statistics_date
 */
class SkuSalesStatistics extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_sku_sales_statistics';
    }
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['create_time'],
                    \yii\db\ActiveRecord::EVENT_BEFORE_UPDATE => ['update_time'],
                ],
                // if you're using datetime instead of UNIX timestamp:
                'value' => date('Y-m-d H:i:s',time()),
            ],
        ];
    }
    /**
     *
     * @param mixed $datass
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function FindOnes($datass)
    {

        $stauts = [];
        foreach ($datass as $k=>$v)
        {
            if(!empty($v['sku']))
            {


               /* $model= self::find()->where(['warehouse_code'=>$v['warehouse_code'],'sku'=>$v['sku'],'platform_code'=>$v['platform_code']])->one();

                if ($model)
                 {
                     $stauts[] =self::SaveOne($model,$v);
                     $data['success_list'][$k]['warehouse_code']         = $model->attributes['warehouse_code'];
                     $data['success_list'][$k]['sku']                    = $model->attributes['sku'];
                     $data['failure_list'][]                             = '';
                 } else {*/
                    $model = new self;
                    $stauts[] =self::SaveOne($model, $v);
                    $data['success_list'][$k]['warehouse_code'] = $model->attributes['warehouse_code'];
                    $data['success_list'][$k]['sku']            = $model->attributes['sku'];
                    $data['failure_list'][]                     = '';
               // }
            } else{
                continue;
            }
        }

        return $stauts;


    }
    
    /**
     * 新增数据
     * @param $model
     * @param $datass
     * @return mixed
     */
    public  static function batchInsertData($datas)
    {
        $batchInsertData = [];
        foreach ($datas as $data)
        {
            $daysSales15                      = !empty($data['15days_sales'])?$data['15days_sales']:0;
            $daysSales3                       = !empty($data['3days_sales'])?$data['3days_sales']:0;
            $daysSales30                      = !empty($data['30days_sales'])?$data['30days_sales']:0;
            $daysSales60                      = !empty($data['60days_sales'])?$data['60days_sales']:0;
            $daysSales7                       = !empty($data['7days_sales'])?$data['7days_sales']:0;
            $daysSales90                      = !empty($data['90days_sales'])?$data['90days_sales']:0;
            $sku                              = !empty($data['sku'])?$data['sku']:'';
            $statisticsDate                   = !empty($data['statistics_date'])?$data['statistics_date']:'';
            $warehouseCode                    = !empty($data['warehouse_code'])?$data['warehouse_code']:'';
            $warehouseId                      = !empty($data['warehouse_id'])?$data['warehouse_id']:'';
            $platformCode                     = !empty($data['platform_code'])?$data['platform_code']:'';
            $isSuggest                        = 0;
            
            $batchInsertData[] = [$daysSales15, $daysSales3, $daysSales30, $daysSales60, $daysSales7, 
                $daysSales90, $sku, $statisticsDate, $warehouseCode, $warehouseId, $platformCode, $isSuggest];
        }
        $fileds = ['days_sales_15', 'days_sales_3', 'days_sales_30','days_sales_60', 'days_sales_7', 
            'days_sales_90', 'sku', 'statistics_date', 'warehouse_code', 'warehouse_id', 
            'platform_code', 'is_suggest'];
        /**
         * @var $dbCommand \yii\db\Command
         */
        $dbCommand = self::find()->createCommand()
            ->batchInsert(self::tableName(), $fileds, $batchInsertData);
        $sql = $dbCommand->getRawSql();
        //忽略唯一键错误
        $sql = str_replace('INSERT INTO', 'INSERT IGNORE INTO', $sql);
        $dbCommand->setSql($sql);
        return $dbCommand->execute();
    }    
    
    /**
     * 新增数据
     * @param $model
     * @param $datass
     * @return mixed
     */
    public  static function SaveOne($model,$datass)
    {

        $model->days_sales_15                      = !empty($datass['15days_sales'])?$datass['15days_sales']:0;
        $model->days_sales_3                       = !empty($datass['3days_sales'])?$datass['3days_sales']:0;
        $model->days_sales_30                      = !empty($datass['30days_sales'])?$datass['30days_sales']:0;
        $model->days_sales_60                      = !empty($datass['60days_sales'])?$datass['60days_sales']:0;
        $model->days_sales_7                       = !empty($datass['7days_sales'])?$datass['7days_sales']:0;
        $model->days_sales_90                      = !empty($datass['90days_sales'])?$datass['90days_sales']:0;
        $model->sku                                = !empty($datass['sku'])?$datass['sku']:'';
        $model->statistics_date                    = !empty($datass['statistics_date'])?$datass['statistics_date']:'';
        $model->warehouse_code                     = !empty($datass['warehouse_code'])?$datass['warehouse_code']:'';
        $model->warehouse_id                       = !empty($datass['warehouse_id'])?$datass['warehouse_id']:'';
        $model->platform_code                      = !empty($datass['platform_code'])?$datass['platform_code']:'';
        $model->is_suggest                         = 0;
        $status =$model->save();

        return $status;
    }
    
}
