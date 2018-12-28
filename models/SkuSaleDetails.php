<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "{{%sku_sale_details}}".
 *
 * @property integer $id
 * @property integer $warehouse_id
 * @property string $warehouse_code
 * @property string $platform_code
 * @property string $sku
 * @property integer $sales
 * @property string $sale_date
 * @property string $statistics_date
 * @property integer $is_new
 */
class SkuSaleDetails extends BaseModel
{
	
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%sku_sale_details}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['warehouse_id', 'warehouse_code', 'sku', 'sales', 'sale_date'], 'required'],
            [['warehouse_id', 'sales', 'is_new'], 'integer'],
            [['sale_date', 'statistics_date'], 'safe'],
            [['warehouse_code'], 'string', 'max' => 50],
            [['platform_code'], 'string', 'max' => 30],
            [['sku'], 'string', 'max' => 125],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'warehouse_id' => '仓库ID',
            'warehouse_code' => '仓库CODE',
            'platform_code' => '平台CODE',
            'sku' => 'SKU编码',
            'sales' => '销售数量',
            'sale_date' => '销售日期',
            'statistics_date' => '统计日期',
            'is_new' => '是否新品',// 0-否 1-是
        ];
    }

    /**
     * 保存物流商
     * @param $data
     * @return array
     */
    public static function saveNewSkuSalesStatisticsData($data)
    {
        if (empty($data['sku'])){
            return ['status' => 0, 'msg' => 'sku为空'];
        }

        $where = [
            'platform_code' => $data['platform_code'],
            'warehouse_id'  => $data['warehouse_id'],
            'sku'           => $data['sku'],
            'sale_date'     => $data['sale_date']
        ];
        $model = self::find()->where($where)->one();
        if (empty($model)){
            $model = new self();
        }
        $model->warehouse_id = $data['warehouse_id'];
        $model->warehouse_code = $data['warehouse_code'];
        $model->platform_code = $data['platform_code'];
        $model->sku = $data['sku'];
        $model->sales = $data['sales'];
        $model->sale_date = $data['sale_date'];
        $model->statistics_date = $data['statistics_date'];
        $model->is_new = $data['is_new'];

        return $model->saveModel();
    }

    /**
     * 保存
     * @return array
     */
    protected function saveModel()
    {
        $return = $this->save();
        //保存失败
        if (!$return) {
            //有错误信息
            if ($this->hasErrors()) {
                $arr = $this->getErrors();
                $str = '';
                foreach ($arr as $k => $v) {
                    foreach ($v as $kk => $vv) {
                        $str .= $vv . '<br>';
                    }
                }
                return ['status' => 0, 'msg' => $str];
            }
            return ['status' => 0, 'msg' => '保存失败'];
        }

        return ['status' => 1, 'msg' => '保存成功', 'model' => $this];
    }

    /**
     * 删除日期销量那天的统计数据
     * @param string $salesDate
     * @return bool|int
     */
    public static function deleteAllDataBySalesDate($salesDate = '')
    {
        if (empty($salesDate)){
            //销量日期为空
            return false;
        }
        //删除当前销售日期所有数据
        $condition = "sale_date='{$salesDate}'";
        return self::deleteAll($condition);
    }
}
