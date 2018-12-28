<?php

namespace app\api\v1\models;

use app\config\Vhelper;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "pur_hwc_avg_delivery_time".
 *
 * @property integer $id
 * @property string $sku
 * @property integer $avg_delivery_time
 * @property string $cacul_date
 * @property integer $status
 * @property integer $delivery_total
 * @property integer $purchase_time
 */
class HwcAvgDeliveryTime extends \yii\db\ActiveRecord
{
    public $supplier_code;
    public $file_execl;
    public $product_status;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_hwc_avg_delivery_time';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'avg_delivery_time', 'status', 'delivery_total', 'purchase_time'], 'integer'],
            [['cacul_date','supplier_code','sku','product_status'], 'safe'],
            [['sku'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sku' => 'Sku',
            'avg_delivery_time' => 'Avg Delivery Time',
            'cacul_date' => 'Cacul Date',
            'status' => 'Status',
            'delivery_total' => 'Delivery Total',
            'purchase_time' => 'Purchase Time',
        ];
    }

    public function getSupplierQuote(){
        return $this->hasOne(SupplierQuotes::className(), ['id'=>'quotes_id'])->where(['status'=>1])
            ->via('defaultSupplier');
    }

    /**
     * @desc 获取默认供货商信息
     * @return $this
     */
    public function getDefaultSupplierDetail(){
        return $this->hasOne(Supplier::className(), ['supplier_code' => 'supplier_code'])
            ->via('defaultSupplier');
    }
    /**
     * @desc 获取默认供应商
     * @return $this
     */
    public function getDefaultSupplier(){
        return $this->hasOne(ProductProvider::className(), ['sku' => 'sku'])->where(['is_supplier'=>1]);
    }

    public function getProduct(){
        return $this->hasOne(Product::className(),['sku'=>'sku']);
    }
    public function search($params)
    {
        $query = self::find();
        $query->alias('t');
        $query->select('t.sku,(t.delivery_total/t.purchase_time) as avg_delivery_time');
        // add conditions that should always apply here
        $pageSize = isset($params['per-page']) ? intval($params['per-page']) : 20;
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);

        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }
        $query->andFilterWhere(['like','t.sku',$this->sku]);
        if($this->supplier_code){
            $query->joinWith('defaultSupplier');
            $query->andFilterWhere(['supplier_code'=>$this->supplier_code]);
        }
        if($this->product_status){
            $query->joinWith('product');
            $query->andFilterWhere(['pur_product.product_status'=>$this->product_status]);
        }
        $query->orderBy('avg_delivery_time DESC');
//        echo  $query->createCommand()->getRawSql();
//        exit();
        return $dataProvider;
    }

    public function upload()
    {


        $uploadpath = 'Uploads/' . date('Ymd') . '/';  //上传路径
        // 图片保存在本地的路径：images/Uploads/当天日期/文件名，默认放置在basic/web/下
        $dir = '/images/' . $uploadpath;
        //生成唯一uuid用来保存到服务器上图片名称
        $pickey = Vhelper::genuuid();
        $filename = $pickey . '.' . $this->file_execl->getExtension();
        //如果文件夹不存在，则新建文件夹
        $filepath= Vhelper::fileExists(Yii::getAlias('@app') . '/web' . $dir);
        $file = $filepath.$filename;

        if ($this->file_execl->saveAs($file))
        {

            return $file;
        } else{
            return false;
        }

    }


}
