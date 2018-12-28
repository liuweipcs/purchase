<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "pur_overseas_demand_pass_rule".
 *
 * @property integer $id
 * @property integer $transport
 * @property string $warehouse_code
 * @property string $create_time
 * @property string $create_user_name
 * @property integer $status
 */
class OverseasDemandPassRule extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_overseas_demand_pass_rule';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['transport', 'status'], 'integer'],
            [['create_time'], 'safe'],
            [['warehouse_code', 'create_user_name'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'transport' => 'Transport',
            'warehouse_code' => 'Warehouse Code',
            'create_time' => 'Create Time',
            'create_user_name' => 'Create User Name',
            'status' => 'Status',
        ];
    }

    public static function saveRules($datas){
        $tran = Yii::$app->db->beginTransaction();
        try{
            if(!is_array($datas)){
                throw new Exception('提交数据无法添加！');
            }
            $insertData = [];
            $formData = [];
            foreach ($datas['transport'] as $k=>$data){
                $formData=array_column($datas,$k);
                $formData[]=Yii::$app->user->identity->username;
                $formData[]=date('Y-m-d H:i:s',time());
                $formData[]=1;
                $insertData[]=$formData;
            }
            self::updateAll(['status'=>0],['status'=>1]);
            Yii::$app->db->createCommand()->batchInsert(self::tableName(),[
                'transport',
                'warehouse_code',
                'create_user_name',
                'create_time',
                'status'
            ],$insertData)->execute();
            $tran->commit();
            $response = ['status'=>'success','message'=>'规则编辑成功'];
        }catch (Exception $e){
            $tran->rollBack();
            $response = ['status'=>'error','message'=>'规则编辑失败'];
        }
        return $response;
    }
}
