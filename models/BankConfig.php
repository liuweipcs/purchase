<?php

namespace app\models;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "pur_bank_config".
 *
 * @property integer $id
 * @property string $bank_name
 * @property string $create_user_name
 * @property string $create_time
 * @property integer $status
 * @property string $update_user_name
 * @property string $update_time
 */
class BankConfig extends \app\models\base\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_bank_config';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['bank_name', 'create_user_name'], 'required'],
            [['create_time', 'update_time'], 'safe'],
            [['status'], 'integer'],
            [['bank_name'], 'string', 'max' => 255],
            [['create_user_name', 'update_user_name'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'bank_name' => 'Bank Name',
            'create_user_name' => 'Create User Name',
            'create_time' => 'Create Time',
            'status' => 'Status',
            'update_user_name' => 'Update User Name',
            'update_time' => 'Update Time',
        ];
    }

    public function search($params){
        $query = self::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }
        $query->andFilterWhere([
           'id'=>$this->id,
           'bank_name'=>$this->bank_name,
           'status'=>$this->status
        ]);
        return $dataProvider;
    }

    public static function getMasterBankId($bank_name){
        $bank_config = self::find()->where(['bank_name'=>trim($bank_name)])->one();
        if(empty($bank_config)){
            $model=new self();
            $model->bank_name = trim($bank_name);
            $model->create_user_name = Yii::$app->user->identity->username;
            $model->create_time = date('Y-m-d H:i:s',time());
            $model->status =1 ;
            if($model->save()){
                return $model->attributes['id'];
            }
        }
        if($bank_config->status ==0){
            $bank_config->status =1;
            $bank_config->update_user_name = Yii::$app->user->identity->username;
            $bank_config->update_time = date('Y-m-d H:i:s',time());
            $bank_config->save(false);
        }
        return $bank_config->id;
    }
}
