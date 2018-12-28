<?php

namespace app\models;

use app\models\base\BaseModel;

use app\services\BaseServices;
use Yii;

/**
 * This is the model class for table "{{%purchase_suggest_note}}".
 *
 * @property integer $id
 * @property string $sku
 * @property string $warehouse_code
 * @property string $creator
 * @property string $suggest_note
 * @property integer $note_type
 * @property string $create_time
 * @property integer $purchase_type
 */
class PurchaseSuggestNote extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_suggest_note}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sku', 'warehouse_code', 'creator', 'suggest_note'], 'required'],
            [['note_type', 'purchase_type'], 'integer'],
            [['create_time'], 'safe'],
            [['sku'], 'string', 'max' => 200],
            [['warehouse_code'], 'string', 'max' => 30],
            [['creator'], 'string', 'max' => 20],
            [['suggest_note'], 'string', 'max' => 500],
            [['group_id'], 'string', 'max' => 11],
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
            'warehouse_code' => 'Warehouse Code',
            'creator' => 'Creator',
            'suggest_note' => 'Suggest Note',
            'note_type' => 'Note Type',
            'create_time' => 'Create Time',
            'purchase_type' => 'Purchase Type',
            'group_id' => 'Group Id',
        ];
    }

    /**修改和新增采购未处理备注
     * @param $data
     * @param null $purchase_type
     * @return bool|int
     */
    public static function updateSuggestNote($data,$purchase_type=null)
    {
        $data['creator'] = Yii::$app->user->identity->username;
        $data['note_type'] = 0;
        $data['create_time'] = date('Y-m-d H:i:s',time());
        if (!empty($purchase_type)) {
            $data['purchase_type'] = $purchase_type;
        }

//        $exists = PurchaseSuggestNote::find()->where(['sku'=>$data['sku']])->andWhere(['warehouse_code'=>$data['warehouse_code']])->exists();
//
//        $str = BaseServices::getStrData($data);

//        if ($exists) {
//            $model=new PurchaseSuggestNote();
//
//            //表修改日志-更新
//            $change_data = [
//                'table_name' => 'pur_purchase_suggest_note', //变动的表名称
//                'change_type' => '2', //变动类型(1insert，2update，3delete)
//                'change_content' => "update:sku:{$data['sku']},warehouse_code{$data['warehouse_code']},{$str}", //变更内容
//            ];
//            TablesChangeLog::addLog($change_data);
//            $status = $model->updateAll($data,['sku'=>$data['sku'],'warehouse_code'=>$data['warehouse_code']]);
//        } else {
        PurchaseSuggestNote::updateAll(['status'=>0,'update_time'=>date('Y-m-d H:i:s',time()),
            'update_user_name'=>Yii::$app->user->identity->username],
            ['sku'=>$data['sku'],'warehouse_code'=>$data['warehouse_code'],'status'=>1]);
            $model= new PurchaseSuggestNote();
            $model->setAttributes($data);
            $status = $model->save();

            //表修改日志-新增
            $change_content = "insert:新增id值为{$model->id}的记录";
            $change_data = [
                'table_name' => 'pur_purchase_suggest_note', //变动的表名称
                'change_type' => '1', //变动类型(1insert，2update，3delete)
                'change_content' => $change_content, //变更内容
            ];
            TablesChangeLog::addLog($change_data);
    //    }
        if($status){
            return json_encode(['status'=>'success','message'=>'备注修改成功']);
        }else{
            return json_encode(['status'=>'error','message'=>'备注编辑失败']);
        }
    }


    /**修改和新增FBA备注
     * @param $data
     * @param null $purchase_type
     * @return bool|int
     */
    public static function updateFbaSuggestNote($data,$purchase_type=null)
    {
        $data['creator'] = Yii::$app->user->identity->username;
        $data['note_type'] = 0;
        $data['create_time'] = date('Y-m-d H:i:s',time());
        if (!empty($purchase_type)) {
            $data['purchase_type'] = $purchase_type;
        }

        $exists = PurchaseSuggestNote::find()
            ->where(['sku'=>$data['sku']])
            ->andWhere(['warehouse_code'=>$data['warehouse_code']])
            ->andWhere(['group_id'=>$data['group_id']])
            ->exists();

        $str = BaseServices::getStrData($data);

        if ($exists) {
            $model=new PurchaseSuggestNote();

            //表修改日志-更新
            $change_data = [
                'table_name' => 'pur_purchase_suggest_note', //变动的表名称
                'change_type' => '2', //变动类型(1insert，2update，3delete)
                'change_content' => "update:sku:{$data['sku']},warehouse_code{$data['warehouse_code']},{$str}", //变更内容
            ];
            TablesChangeLog::addLog($change_data);
            $status = $model->updateAll($data,['sku'=>$data['sku'],'warehouse_code'=>$data['warehouse_code'],'group_id'=>$data['group_id']]);
        } else {
            $model= new PurchaseSuggestNote();
            $model->setAttributes($data);
            $status = $model->save();

            //表修改日志-新增
            $change_content = "insert:新增id值为{$model->id}的记录";
            $change_data = [
                'table_name' => 'pur_purchase_suggest_note', //变动的表名称
                'change_type' => '1', //变动类型(1insert，2update，3delete)
                'change_content' => $change_content, //变更内容
            ];
            TablesChangeLog::addLog($change_data);
        }
        return $status;
    }
}
