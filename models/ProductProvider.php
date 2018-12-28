<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
use app\config\Vhelper;
use yii\data\ActiveDataProvider;
/**
 * This is the model class for table "{{%product_provider}}".
 *
 * @property integer $id
 * @property string $sku
 * @property string $supplier_code
 * @property string $is_supplier
 * @property string $is_exemption
 * @property string $is_push
 * @property string $quotes_id
 */
class ProductProvider extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product_supplier}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sku', 'supplier_code'], 'required'],
            [['sku', 'supplier_code'], 'string', 'max' => 20],
            [['sku', 'supplier_code'], 'unique', 'targetAttribute' => ['sku', 'supplier_code'], 'message' => 'The combination of Sku and Supplier Code has already been taken.'],
            [['sku'],'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'sku' => Yii::t('app', 'Sku'),
            'supplier_code' => Yii::t('app', 'Supplier Code'),
            'is_supplier' => Yii::t('app', 'is_supplier'),
            'is_exemption' => Yii::t('app', 'is_exemption'),
            'is_push' => Yii::t('app', 'is_push'),
            'quotes_id' => Yii::t('app', 'quotes_id'),
        ];
    }

    /**
     * 关联供应商报价表
     * @return \yii\db\ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(SupplierQuotes::className(), ['suppliercode' => 'supplier_code']);

    }

    /**
     * 关联产品状态不为0和7的产品
     * @return $this
     */
    public function getProduct(){
        return $this->hasOne(Product::className(),['sku'=>'sku'])->where(['NOT IN','product_status',[0,7]]);
    }

    /*
     * 关联产品状态不为0和7,不为捆绑的子产品
     */
    public function getTrueProduct(){
        return $this->hasOne(Product::className(),['sku'=>'sku'])->where(['NOT IN','product_status',[0,7]])->andFilterWhere(['<>','product_is_multi',2])->andFilterWhere(['product_type'=>1]);
    }

    public function getQuotes(){
        return $this->hasOne(SupplierQuotes::className(),['id'=>'quotes_id']);
    }

    /**
     * 插入一条数据到中间表
     * @param $data
     */
    public static function  SaveOne($data)
    {


        $model     = self::findOne(['sku'=>$data['product_sku'],'is_supplier'=>1]);

        //存在默认供应商，就把他修改为零，并且插入一条新的记录
        if ($model)
        {
            $model->is_supplier = 0;
            $model->save();
            $models = self::findOne(['sku'=>$data['product_sku'],'supplier_code'=>$data['suppliercode']]);
            if($models)
            {
                $models->is_supplier = 1;
                $status = $models->save(false);
            } else{
                $modeld                = new self();
                $modeld->sku           = $data['product_sku'];
                $modeld->supplier_code = $data['suppliercode'];
                $modeld->is_exemption  = 0;
                $modeld->is_supplier   = $data['default_vendor'];
                $status = $modeld->save(false);
            }

        } else{
            $models = self::findOne(['sku'=>$data['product_sku'],'supplier_code'=>$data['suppliercode']]);
            if($models)
            {
                $models->is_supplier = 1;
                $status = $models->save(false);
            } else{
                $modeld                = new self();
                $modeld->sku           = $data['product_sku'];
                $modeld->supplier_code = $data['suppliercode'];
                $modeld->is_exemption  = 0;
                $modeld->is_supplier   = $data['default_vendor'];
                $status = $modeld->save(false);
            }

        }
        return $status;



    }

    public function search($params)
    {
        $query = self::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        $query->andFilterWhere(['is_supplier'=>2]);
        $query->andFilterWhere([
            'sku' => isset($params['sku']) ? $params['sku'] :'',
        ]);
        return $dataProvider;
    }
}
