<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Exception;

/**
 * This is the model class for table "pur_supplier_settlement".
 *
 * @property integer $id
 * @property string $supplier_settlement_name
 * @property integer $supplier_settlement_code
 * @property integer $settlement_status
 */
class SupplierSettlement extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_supplier_settlement';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['supplier_settlement_code', 'settlement_status'], 'integer'],
            [['supplier_settlement_code'], 'unique'],
            [['supplier_settlement_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'supplier_settlement_name' => 'Supplier Settlement Name',
            'supplier_settlement_code' => 'Supplier Settlement Code',
            'settlement_status' => 'Settlement Status',
        ];
    }

    public function search($params){
        $query = self::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,//要多少写多少吧
            ],
        ]);
        $this->load($params);
        $query->orderBy('settlement_status ASC');
        return $dataProvider;
    }

    public static function saveSettlement($params,$type){
        try{
            if(!isset($params['supplier_settlement_code'])||empty($params['supplier_settlement_code'])){
                throw new Exception('缺少结算方式编码参数');
            }
            if(!isset($params['supplier_settlement_name'])||empty($params['supplier_settlement_name'])){
                throw new Exception('缺少结算方式名称参数');
            }
            $model = self::find()->andWhere(['supplier_settlement_code'=>$params['supplier_settlement_code']])->one();
            if($type=='create'&&!empty($model)){
                throw new Exception('结算方式编码已经存在');
            }
            if($type=='create'){
                $model = new self();
                $model->supplier_settlement_code = $params['supplier_settlement_code'];
                $model->settlement_status        = 1;
            }
            $model->supplier_settlement_name = $params['supplier_settlement_name'];
            if($model->save() == false){
                throw new Exception($model->isNewRecord ? '添加失败！' :'编辑失败！');
            }
            $response = ['status'=>'success','message'=>$model->isNewRecord ? '添加成功！' :'编辑成功！'];
        }catch (Exception $e){
            $response = ['status'=>'error','message'=>$e->getMessage()];
        }
        return $response;
    }

    public static function changeStatus($id){
        try{
            if(empty($id)){
                throw new Exception('缺少参数!');
            }
            $model = self::find()->andWhere(['id'=>$id])->one();
            if(empty($model)){
                throw new Exception('数据异常！');
            }
            $model->settlement_status = $model->settlement_status == 1 ? 2 : 1;
            if($model->save()==false){
                throw new Exception('更新失败!');
            }
            $response = ['status'=>'success','message'=>'更新成功'];
        }catch (Exception $e){
            $response = ['status'=>'error','message'=>$e->getMessage()];
        }
        return $response;
    }
}
