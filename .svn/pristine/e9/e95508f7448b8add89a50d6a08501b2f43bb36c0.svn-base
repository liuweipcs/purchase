<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Exception;

/**
 * This is the model class for table "pur_supplier_settlement_log".
 *
 * @property integer $id
 * @property string $supplier_code
 * @property integer $old_settlement
 * @property integer $new_settlement
 * @property string $create_user_name
 * @property integer $create_user_id
 * @property integer $means_upload
 * @property integer $is_exec
 * @property string $pay_time
 * @property string $update_time
 * @property string $create_time
 */
class SupplierSettlementLog extends BaseModel
{
    public $group;
    public $update_start_time;
    public $update_end_time;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_supplier_settlement_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['!old_settlement', '!new_settlement', '!create_user_id', 'means_upload', 'is_exec'], 'integer'],
            [['supplier_code', 'create_user_name', 'pay_time','note'], 'string', 'max' => 255],
            [['supplier_code','group','update_end_time','update_start_time'],'safe']
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
            'old_settlement' => 'Old Settlement',
            'new_settlement' => 'New Settlement',
            'create_user_name' => 'Create User Name',
            'create_user_id' => 'Create User ID',
            'means_upload' => 'Means Upload',
            'is_exec' => 'Is Exec',
            'pay_time' => 'Pay Time',
            'update_time' => 'Update Time',
            'note' => 'Note',
        ];
    }

    public function getSupplier(){
        return $this->hasOne(Supplier::className(),['supplier_code'=>'supplier_code']);
    }

    public static function updateLog($formData){
        $tran = Yii::$app->db->beginTransaction();
        try{
            foreach ($formData as $k=>$data){

                $logModel = self::find()->where(['id'=>$k])->one();
                if(empty($logModel)){
                    throw new Exception('提交数据有误');
                }
                if($logModel->load($data,'')&&$logModel->validate()){
                    $logModel->update_user_name = Yii::$app->user->identity->username;
                    $logModel->update_user_id = Yii::$app->user->id;
                    $logModel->update_time = date('Y-m-d H:i:s',time());
                    if($logModel->save()==false){
                        throw new Exception('更新失败');
                    }
                }
            }
            $response = ['status'=>'success','message'=>'更新成功'];
            $tran->commit();
        }catch (Exception $e){
            $response = ['status'=>'error','message'=>$e->getMessage()];
            $tran->rollBack();
        }
        return $response;
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
        $query->orderBy('create_time ASC');
        if(!empty($this->group)&&$this->group==1){
            $userIds = Yii::$app->authManager->getUserIdsByRole('供应链');
            $query->andFilterWhere(['in','create_user_id',$userIds]);
        }
        if(!empty($this->group)&&$this->group==2){
            $FBAIds = Yii::$app->authManager->getUserIdsByRole('FBA采购组');
            $FBAMIds = Yii::$app->authManager->getUserIdsByRole('FBA采购经理组');
            $userIds = array_merge($FBAIds,$FBAMIds);
            $query->andFilterWhere(['in','create_user_id',$userIds]);
        }
        if(!empty($this->group)&&$this->group==3){
            $GNIds = Yii::$app->authManager->getUserIdsByRole('采购组-国内');
            $GNMIds = Yii::$app->authManager->getUserIdsByRole('采购经理组');
            $userIds = array_merge($GNIds,$GNMIds);
            $query->andFilterWhere(['in','create_user_id',$userIds]);
        }
        $query->andFilterWhere(['between','create_time',$this->update_start_time,$this->update_end_time]);
        $query->andFilterWhere(['supplier_code'=>$this->supplier_code]);
        return $dataProvider;
    }
}
