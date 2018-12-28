<?php

namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use Yii;

/**
 * This is the model class for table "pur_supplier_product_line".
 *
 * @property integer $id
 * @property string $supplier_code
 * @property string $first_product_line
 * @property string $second_product_line
 * @property string $third_product_line
 */
class SupplierProductLine extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_supplier_product_line';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id','status','first_product_line', 'second_product_line', 'third_product_line'], 'integer'],
            [['first_product_line'],'required','message'=>'产品线不能为空','skipOnEmpty' => false, 'skipOnError' => false],
            [['supplier_code' ], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'supplier_code' => 'Supplier Code',
            'first_product_line' => 'First Product Line',
            'second_product_line' => 'Second Product Line',
            'third_product_line' => 'Third Product Line',
            'status'            => 'Status'
        ];
    }

    public function getFirstLine(){
        return $this->hasOne(ProductLine::className(),['product_line_id'=>'first_product_line']);
    }
    public function saveSupplierLine($data,$supplier,$bool=false){

        $info = [];

        if(isset($data['SupplierProductLine'])&&!empty($data['SupplierProductLine'])) {
            $supplierLine = self::find()->andFilterWhere(['supplier_code'=>$supplier['code']])->all();
            if(!empty($supplierLine)){
                if ($bool) {
                    $info['update'] = ['supplier_code'=>$supplier['code'],'status'=>2];
                } else {
                    self::updateAll(['status'=>2],['supplier_code'=>$supplier['code']]);
                }
            }
            if(is_array($data['SupplierProductLine']['first_product_line'])){
                foreach($data['SupplierProductLine']['first_product_line'] as $k=>$value){
                    if(!isset($data['SupplierProductLine']['first_product_line'][$k])||empty($data['SupplierProductLine']['first_product_line'][$k])){
                        continue;
                    }
                    $model = new self;
                    $model->supplier_code = $supplier['code'];
                    $model->first_product_line = $data['SupplierProductLine']['first_product_line'][$k];
                    $model->second_product_line = $data['SupplierProductLine']['second_product_line'][$k];
                    $model->third_product_line = $data['SupplierProductLine']['third_product_line'][$k];
                    if ($bool) {
                        $info['supplier_product_line'][$k] = $model->attributes;
                    } else {
                        $model->save(false);
                    }
                }
            }else{
                $model = new self;
                $model->supplier_code = $supplier['code'];
                $model->first_product_line = $data['SupplierProductLine']['first_product_line'];
                $model->second_product_line = $data['SupplierProductLine']['second_product_line'];
                $model->third_product_line = $data['SupplierProductLine']['third_product_line'];
                if ($bool) {
                    $info['supplier_product_line'][] = $model->attributes;
                } else {
                    $model->save(false);
                }
            }
        }
        if ($bool) {
            return $info;
        }
    }
}
