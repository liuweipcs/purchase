<?php
namespace app\models;

use app\models\base\BaseModel;
use Yii;
class PurchaseWarehouseAbnormal extends BaseModel
{

    public $start_time;
    public $end_time;

    public static $abnormal_type = [
        1 => '查找入库单',
        2 => '入库有次品',
        3 => '质检不合格'
    ];

    public static $handler_type = [
        1 => '退货',
        4 => '正常入库',
        2 => '次品退货',
        3 => '次品转正',
        5 => '不做处理',
        6 => '优品入库',
        7 => '整批退货',
        8 => '二次包装',
        9 => '正常入库',
        10 => '不做处理',
        15 => '处理中',
    ];

    public static function tableName()
    {
        return 'pur_purchase_warehouse_abnormal';
    }

    public function rules()
    {
        return [
            [[
                'sku', 'num', 'position', 'defective_type',
                'defective_id', 'purchase_order_no', 'abnormal_type',
                'express_code', 'abnormal_depict', 'img_path_data',
                'can_handle_type_data', 'pull_time',
                'buyer',
                'is_handler',
                'handler_type',
                'handler_person',
                'handler_time',
                'handler_describe' ,
                'is_push_to_warehouse',
                'start_time',
                'end_time',
                'return_province',
                'return_city',
                'return_address',
                'return_linkman',
                'return_phone',
                'warehouse_handler_result',
                'is_reading',
            ], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'sku' => 'SKU',
            'num' => '产品数量',
            'position' => '异常货位',
            'defective_type' => '次品类型',
            'buyer' => '采购员',
            'defective_id' => '异常单号',
            'purchase_order_no' => '采购单号',
            'abnormal_type' => '异常类型',
            'express_code' => '快递单号',
            'abnormal_depict' => '异常原因',
            'img_path_data' => '图片地址',
            'can_handle_type_data' => '可处理类型',
            'pull_time' => '拉取时间',
            'is_handler' =>'是否处理',
            'handler_type' => '处理类型',
            'handler_person' =>'采购员',
            'handler_time' => '处理时间',
            'handler_describe' => '处理描述',
            'is_push_to_warehouse' => '是否推送至仓库',
            'warehouse_handler_result' => '仓库处理结果',
            'is_reading' => '是否读取消息',
            'return_province' => '退货省份',
            'return_city' => '退货城市',
            'return_address' => '退货详细地址',
            'return_linkman' => '联系人',
            'return_phone' => '联系电话',

        ];
    }

    /**
     * 关联订单表
     * @return \yii\db\ActiveQuery
     */
    public function getPurchaseOrder()
    {
        return $this->hasOne(PurchaseOrder::className(), ['pur_number' => 'purchase_order_no']);
    }

    public function formName()
    {
        return '';
    }

    public static function updateRow($data)
    {
        $model = self::find()->where(['defective_id' => $data['defective_id']])->one();
        if(!$model) {
            return false;
        }
        $model->is_handler           = $data['is_handler']; // 是否处理
        $model->handler_type         = $data['handler_type'];   // 处理类型
        $model->handler_person       = Yii::$app->user->identity->username;    // 处理人
        $model->purchase_order_no    = $data['purchase_order_no']; // 采购单号，由采购员填写
        $model->handler_time         = date('Y-m-d H:i:s', time());      // 采购处理时间
        $model->handler_describe     = $data['handler_describe'];  // 采购处理描述
        $model->return_province = $data['return_province'];
        $model->return_city     = $data['return_city'];
        $model->return_address  = $data['return_address'];
        $model->return_linkman = $data['return_linkman'];
        $model->return_phone   = $data['return_phone'];
        $model->is_push_to_warehouse = 0; // 未推送至仓库系统

        //表修改日志-更新
        $change_content = TablesChangeLog::updateCompare($model->attributes, $model->oldAttributes);
        $change_data = [
            'table_name' => 'pur_purchase_warehouse_abnormal', //变动的表名称
            'change_type' => '2', //变动类型(1insert，2update，3delete)
            'change_content' => $change_content, //变更内容
        ];
        TablesChangeLog::addLog($change_data);
        $result = $model->save();
        if($result) {
            return true;
        } else {
            return false;
        }
    }










}
