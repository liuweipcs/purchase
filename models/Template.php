<?php

namespace app\models;

use app\models\base\BaseModel;
use Yii;
use app\config\Vhelper;

class Template extends BaseModel
{

    public $statusCss = [
        '0' => '<label class="label label-default">禁用</label>',
        '1' => '<label class="label label-info">启用</label>'
    ];

    public static function tableName()
    {
        return '{{%template}}';
    }

    public function rules()
    {
        return [
            [[
              'name',
              'type',
              'platform',
              'status',
              'style_code',
            ], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => '名称',
            'type' => '类型',
            'platform' => '平台',
            'status' => '状态',
            'style_code' => '样式编码'
        ];
    }

    public static function getTplTypeName($type)
    {
        $types = [
            'DDHT' => '采购订单合同',
            'DZHT' => '采购对账合同',
            'FKSQS' => '付款申请书',
            'GXHT' => '购销合同',
        ];
        return isset($types[$type]) ? $types[$type] : '';
    }

}
