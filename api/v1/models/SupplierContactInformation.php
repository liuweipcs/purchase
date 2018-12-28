<?php

namespace app\api\v1\models;

use app\config\Vhelper;
use Yii;
use yii\helpers\VarDumper;

/**
 * This is the model class for table "{{%supplier_contact_information}}".
 *
 * @property integer $id
 * @property integer $supplier_id
 * @property string $contact_person
 * @property string $contact_number
 * @property string $contact_fax
 * @property string $chinese_contact_address
 * @property string $english_address
 * @property string $contact_zip
 * @property string $qq
 * @property string $micro_letter
 * @property string $want_want
 * @property string $skype
 */
class SupplierContactInformation extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%supplier_contact_information}}';
    }

    public static function SaveOne($data,$id,$k)
    {
        $model_cont                         = new self;
        foreach ($data as $v)
        {
            $model_cont->contact_person           =   !empty($v['contact_person'])?$v['contact_person']:'张三风';
            $model_cont->supplier_id              =   $id;
            $model_cont->contact_number           =   !empty($v['contact_number'])?$v['contact_number']:'13242082971';
            $model_cont->contact_fax              =   !empty($v['contact_fax'])?$v['contact_fax']:'13242082971';
            $model_cont->chinese_contact_address  =   $v['chinese_contact_address'];
            $model_cont->english_address          =   $v['english_address'];
            $model_cont->contact_zip              =   $v['contact_zip'];
            $model_cont->qq                       =   $v['qq'];
            $model_cont->micro_letter             =   $v['micro_letter'];
            $model_cont->want_want                =   $v['want_want'];
            $model_cont->skype                    =   $v['skype'];
            $model_cont->supplier_code            =   $k;
            $satas= $model_cont->save(false);
        }
        return $satas;
    }

}

