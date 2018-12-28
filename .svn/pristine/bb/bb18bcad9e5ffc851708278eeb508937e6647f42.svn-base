<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "product_supplier_change".
 */
class ProductSupplierChange extends BaseModel
{
    const DEAD_LINE = 172800;// 自动屏蔽期限（秒）

    public $create_id;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product_supplier_change}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sku'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                    => 'ID',
            'sku'                   => '产品SKU',
            'product_linelist_id'   => '产品线ID',
            'apply_user'            => '申请人',
            'apply_time'            => '申请时间',
            'apply_remark'          => '申请备注',
            'old_supplier_code'     => '原供应商',
            'old_supplier_name'     => '原供应商名称',
            'old_price'             => '原单价',
            'new_supplier_code'     => '现供应商',
            'new_supplier_name'     => '现供应商名称',
            'new_price'             => '现单价',
            'status_flag'           => '状态标记',
            'status'                => '状态',
            'erp_oper_user'         => 'erp操作人',
            'erp_oper_time'         => 'erp操作时间',
            'erp_remark'            => 'erp操作备注',
            'erp_result'            => 'erp操作结果',
            'supply_chain_user'     => '供应链审核人',
            'supply_chain_time'     => '供应链审核时间',
            'affirm_user'           => '确认人',
            'affirm_time'           => '确认时间',
            'is_delete'             => '删除状态',
            'create_id'             => '开发员'
        ];
    }

    /**
     * 返回指定 状态值对应的 名称
     * @param  string     $status
     * @return array|bool|string
     * @desc 设置则返回状态值对应的 名称或false,未设置则返回 状态列表
     */
    public static function getStatusList($status = null){
        $status_list = [
            '1'  => '待采购经理审核',
            '20' => '待开发审核',
            '30' => '开发审核通过',
            '50' => '待采购确认',
            '60' => '已变更',
            '70' => '已结束',
            '70_PM' => '已结束(采购经理驳回)',
            '70_P'  => '已结束(采购驳回)',
            '70_D'  => '已结束(开发驳回)',
            '70_M'  => '已结束(同意停售)',
            '70_S'  => '已结束(系统屏蔽)',
            '70_PB' => '已结束(同意屏蔽)',
        ];

        // 不准查询状态值为空的 Name
        if($status !== null AND empty($status)) return false;

        if($status){// 只返回 对应状态值的 Name
            return isset($status_list[$status])?$status_list[$status]:false;
        }

        return $status_list;
    }


    /**
     * 返回指定 状态值对应的 名称
     * @param  string     $status
     * @return array|bool|string
     * @desc 设置则返回状态值对应的 名称或false,未设置则返回 状态列表
     */
    public static function getStatusFlagList($status = null){
        $status_list = [
            'PM' => '采购经理驳回',
            'P' => '采购驳回',
            'D' => '开发驳回',
            'M' => '同意停售',
            'S' => '系统屏蔽',
            'PB' => '同意屏蔽',
        ];

        // 不准查询状态值为空的 Name
        if($status !== null AND empty($status)) return false;

        if($status){// 只返回 对应状态值的 Name
            return isset($status_list[$status])?$status_list[$status]:false;
        }

        return $status_list;
    }

    /**
     * 返回指定 申请原因 名称
     * @param  string     $key
     * @return array|bool|string
     * @desc 设置则返回申请原因值对应的 名称或false,未设置则返回 申请原因列表
     */
    public static function getApplyReasonList($key = null){
        $reason_list = [
            '10'    => '缺货',
            '20'    => '断货',
            '30'    => '停产',
            '40'    => '需要起订量',
            '100'   => '其他'
        ];

        // 不准查询值为空的 Name
        if($key !== null AND empty($key)) return false;

        if($key){
            return isset($reason_list[$key])?$reason_list[$key]:false;
        }

        return $reason_list;
    }


    /**
     * 获取 日志列表
     * @param $key_id
     * @param $is_show
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getLogList($key_id,$is_show = 1){
        $all = ChangeLog::find()
            ->where(['oper_id' => $key_id,'oper_type' => 'ProductSupplierChange'])
            ->andFilterWhere(['is_show' => $is_show])
            ->asArray()
            ->all();
        return $all;
    }

    /**
     * 获取产品信息
     * @return \yii\db\ActiveQuery
     */
    public function getProductInfo(){
        return $this->hasOne(Product::className(),['sku' => 'sku']);
    }

    /**
     * 获取默认供应商
     * @return mixed
     */
    public function getDefaultSupplier(){
        return $this->hasOne(ProductProvider::className(), ['sku' => 'sku'])->where(['is_supplier'=>1]);

    }


}
