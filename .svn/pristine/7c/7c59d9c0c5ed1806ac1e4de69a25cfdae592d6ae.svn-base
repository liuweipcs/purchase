<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "{{%purchase_ticket_open}}".
 *
 * @property integer $id
 * @property string $pur_number
 * @property string $sku
 * @property string $open_time
 * @property string $ticket_name
 * @property string $issuing_office
 * @property string $total_par
 * @property integer $tickets_number
 * @property string $invoice_code
 * @property string $note
 * @property integer $status
 * @property string $create_user
 * @property string $create_time
 */
class PurchaseTicketOpen extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_ticket_open}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['key_id','open_time', 'create_time'], 'safe'],

            ['audit_time', 'default', 'value' => null],  #说明:CDefaultValueValidator 的别名, 为特性指派了一个默认值. 
            [['total_par'], 'number'],
            [['tickets_number', 'status'], 'integer'],
            [['note'], 'string'],
            [['pur_number', 'ticket_name', 'issuing_office','open_number'], 'string', 'max' => 100],
            [['sku', 'create_user', 'audit_user'], 'string', 'max' => 50],
            [['invoice_code'], 'string', 'max' => 255],
            [['pur_number', 'sku','key_id'], 'unique', 'targetAttribute' => ['pur_number', 'sku','key_id'], 'message' => 'The combination of Pur Number and Sku has already been taken.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pur_number' => 'Pur Number',
            'sku' => 'Sku',
            'open_time' => 'Open Time',
            'ticket_name' => 'Ticket Name',
            'issuing_office' => 'Issuing Office',
            'total_par' => 'Total Par',
            'tickets_number' => 'Tickets Number',
            'invoice_code' => 'Invoice Code',
            'note' => 'Note',
            'status' => 'Status',
            'create_user' => 'Create User',
            'create_time' => 'Create Time',
        ];
    }
    /**
     * 关联订单详情表
     * @return \yii\db\ActiveQuery
     */
    public function getPurchaseOrderItems()
    {
        return $this->hasOne(PurchaseOrderItems::className(),['pur_number'=>'pur_number','sku'=>'sku']);
    }

    /**
     * 获取开票详情信息
     * @param string    $sku            SKU
     * @param string    $pur_number     采购单号
     * @param bool      $total          是否是查询总数 true.查询总数
     * @return array|false|null|string|\yii\db\ActiveRecord[]
     */
    public static function getOpenInfo($sku=null,$pur_number=null,$total=false)
    {
        if (!empty($sku)) {
            $where['sku'] = $sku;
        }
        if (!empty($pur_number)) {
            $where['pur_number'] = $pur_number;
        }
        if($total){
            $res = self::find()->select('sum(tickets_number) as tickets_number')->where($where)->orderBy('id DESC')->asArray()->scalar();
        }else{
            $res = self::find()->where($where)->orderBy('id DESC')->asArray()->all();
        }
        return $res;
    }

    /**
     * 获取 开票详情 状态（有多个报关单 只会返回一个最终状态）
     * @param string    $sku            SKU
     * @param string    $pur_number     采购单号
     * @return int
     */
    public static function getOpenStatus($sku = null,$pur_number = null)
    {
        if (!empty($sku)) {
            $where['sku'] = $sku;
        }
        if (!empty($pur_number)) {
            $where['pur_number'] = $pur_number;
        }
        $resList = self::find()->select('status')->where($where)->asArray()->all();
        $count   = DeclareCustoms::find()->where($where)->count();
        $resList = array_column($resList,'status');

        if(count($resList) == 0 OR $count == 0 ) return 0;

        if( (count($resList) != $count) OR in_array(0,$resList) OR in_array(1,$resList)){
            $status = 0;
        }else{
            $status = 2;
        }
        return $status;
    }

    /**
     * 更新数据
     */
    public static function updateOne($data)
    {
        $model = PurchaseTicketOpen::findOne(['pur_number'=> $data['pur_number'], 'sku'=> $data['sku']]);
        if (empty($model)) {
            $model = new self;
        }

        $model->pur_number = $data['pur_number'];
        $model->sku = $data['sku'];
        $model->open_time = $data['open_time'];
        $model->ticket_name = $data['ticket_name'];
        $model->issuing_office = $data['issuing_office'];
        $model->total_par = $data['total_par'];
        $model->tickets_number = $data['tickets_number'];
        $model->invoice_code = $data['invoice_code'];
        $model->status = $data['status'];
        if ($model->status === '0' || $model->status ==1) {
            $model->create_user = Yii::$app->user->identity->username;
            $model->create_time = date('Y-m-d H:i:s', time());
        } else {
            $model->create_user = Yii::$app->user->identity->username;
            $model->create_time = date('Y-m-d H:i:s', time());
        }
        $model->audit_time = null;

        $status = $model->save();
        return $status;
    }
}
