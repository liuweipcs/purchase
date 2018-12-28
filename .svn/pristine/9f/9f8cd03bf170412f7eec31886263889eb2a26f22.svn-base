<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "pur_company_holder_list".
 *
 * @property integer $id
 * @property string $toco
 * @property string $amount
 * @property string $data_id
 * @property string $logo
 * @property integer $type
 * @property string $name
 * @property string $credit_code
 * @property integer $status
 * @property string $update_time
 */
class CompanyHolderList extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_company_holder_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'status'], 'integer'],
            [['credit_code', 'update_time'], 'required'],
            [['update_time'], 'safe'],
            [['toco', 'amount', 'data_id'],'string','max'=>50],
            [['logo', 'name', 'credit_code'], 'string', 'max' => 255],
        ];
    }

    public function getHolderCapital(){
        return $this->hasMany(CompanyHolderCapital::className(),['holder_id'=>'id'])->andWhere(['pur_company_holder_capital.status'=>1]);
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'toco' => 'Toco',
            'amount' => 'Amount',
            'data_id' => 'Data ID',
            'logo' => 'Logo',
            'type' => 'Type',
            'name' => 'Name',
            'credit_code' => 'Credit Code',
            'status' => 'Status',
            'update_time' => 'Update Time',
        ];
    }

    public static function saveHolderList($datas,$credit_code){
        if(!empty($datas)) {
            $newId = array_column($datas, 'id');
            $existId = self::find()->select('data_id')->where(['credit_code' => $credit_code,'status'=>1])->column();
            $deleteId = array_diff($existId, $newId);
            if(!empty($deleteId)){
                Yii::$app->db->createCommand()->update(self::tableName(),
                    [
                        'status'=>0,
                        'update_time'=>date('Y-m-d H:i:s',time())
                    ],['credit_code'=>$credit_code,'status'=>1,'data_id'=>$deleteId])->execute();
            }
            foreach ($datas as $value){
                $model = self::find()->where(['credit_code'=>$credit_code,'data_id'=>$value['id']])->one();
                if(empty($model)){
                    $model = new self();
                }
                $model->toco = isset($value['toco']) ? $value['toco'] : '';
                $model->amount = isset($value['amount']) ? $value['amount'] : '';
                $model->data_id = isset($value['id']) ? $value['id'] : '';
                $model->logo = isset($value['logo']) ? $value['logo'] : '';
                $model->type = isset($value['type']) ? $value['type'] : '';
                $model->name = isset($value['name']) ? $value['name'] : '';
                $model->credit_code = $credit_code;
                $model->status = 1;
                $model->update_time = date('Y-m-d H:i:s',time());
                if($model->save(false)){
                    CompanyHolderCapital::saveData($model->attributes['id'],$value['capital']);
                }
            }
        }
    }
}
