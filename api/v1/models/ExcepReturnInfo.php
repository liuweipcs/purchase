<?php

namespace app\api\v1\models;

use Yii;
use yii\base\Exception;

/**
 * This is the model class for table "pur_excep_return_info".
 *
 * @property integer $id
 * @property string $express_no
 * @property string $pur_number
 * @property string $excep_number
 * @property string $create_time
 * @property string $update_time
 * @property string $return_user
 * @property string $return_time
 * @property string $return_status
 * @property integer $data_id
 */
class ExcepReturnInfo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_excep_return_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_time', 'return_time','update_time'], 'safe'],
            [['data_id'], 'integer'],
            [['express_no', 'pur_number', 'excep_number'], 'string', 'max' => 100],
            [['return_user'], 'string', 'max' => 150],
            [['return_status'], 'string', 'max' => 255],
            [['data_id'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'express_no' => 'Express No',
            'pur_number' => 'Pur Number',
            'excep_number' => 'Excep Number',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'return_user' => 'Return User',
            'return_time' => 'Return Time',
            'return_status' => 'Return Status',
            'data_id' => 'Data ID',
        ];
    }

    public static function saveData($data){
        try{
            $failList=[];
            $successList=[];
            foreach ($data as $v){
                $model =  self::find()->where(['data_id'=>$v->data_id])->one();
                if(empty($model)){
                    $model = new self();
                }
                $model->express_no = $v->express_no;
                $model->excep_number = $v->excep_number;
                $model->pur_number = $v->pur_number;
                $model->create_time = $model->isNewRecord ? date('Y-m-d H:i:s',time()) : $model->create_time;
                if(!$model->isNewRecord){
                    $model->update_time = date('Y-m-d H:i:s',time());
                }
                $model->return_user = $v->return_user;
                $model->return_time = $v->return_time;
                $model->return_status = $v->return_status;
                $model->data_id = $v->data_id;
                if($model->save()==false){
                    $failList[]=$v->data_id;
                }else{
                    $successList[]=$v->data_id;
                }
            }
        }catch (Exception $e){
            $successList =[];
        }
        echo json_encode(['successList'=>$successList,'failList'=>$failList]);
        Yii::$app->end();
    }
}
