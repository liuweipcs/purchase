<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "{{%develop_track}}".
 *
 * @property string $id
 * @property string $sku
 * @property string $kf_create_time
 * @property string $kf_user
 * @property integer $kf_audit_status
 * @property string $kf_audit_time
 * @property string $kf_audit_user
 * @property integer $kf_zhijian_status
 * @property string $kf_zhijian_time
 * @property string $kf_zhijian_user
 * @property string $cg_xiaoshou_time
 * @property string $cg_xiaoshou_user
 * @property integer $cg_xiaoshou_audit_status
 * @property string $cg_xiaoshou_audit_time
 * @property string $cg_xiaoshou_audit_user
 * @property string $cg_suggest_time
 * @property string $cg_suggest_user
 * @property integer $cg_audit_status
 * @property string $cg_audit_time
 * @property string $cg_audit_user
 * @property string $cg_shenqing_pay_time
 * @property string $cg_shenqing_pay_user
 * @property integer $cg_caiwu_pay_status
 * @property string $cg_caiwu_pay_time
 * @property string $cg_caiwu_pay_user
 * @property integer $wms_daohuo_status
 * @property string $wms_daohuo_time
 * @property string $wms_daohuo_user
 * @property string $wms_zhijian_time
 * @property string $wms_zhijian_user
 * @property string $wms_ruku_time
 * @property string $wms_ruku_user
 * @property string $wms_fahuo_time
 * @property string $wms_fahuo_user
 * @property string $wms_beihuo_time
 * @property string $wms_beihuo_user
 * @property string $wms_audit_time
 * @property string $wms_audit_user
 * @property string $wms_jianhuo_time
 * @property string $wms_jianhuo_user
 * @property string $wms_yanhuo_time
 * @property string $wms_shangjia_time
 * @property integer $jiedian_status
 */
class DevelopTrack extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%develop_track}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['kf_create_time', 'kf_audit_time', 'kf_zhijian_time', 'cg_xiaoshou_time', 'cg_xiaoshou_audit_time', 'cg_suggest_time', 'cg_audit_time', 'cg_shenqing_pay_time', 'cg_caiwu_pay_time', 'wms_daohuo_time', 'wms_zhijian_time', 'wms_ruku_time', 'wms_fahuo_time', 'wms_beihuo_time', 'wms_audit_time', 'wms_jianhuo_time', 'wms_yanhuo_time', 'wms_shangjia_time'], 'safe'],
            [['kf_audit_status', 'kf_zhijian_status', 'cg_xiaoshou_audit_status', 'cg_audit_status', 'cg_caiwu_pay_status', 'wms_daohuo_status', 'jiedian_status'], 'integer'],
            [['sku'], 'string', 'max' => 100],
            [['kf_user', 'kf_audit_user', 'kf_zhijian_user', 'cg_xiaoshou_user', 'cg_xiaoshou_audit_user', 'cg_suggest_user', 'cg_audit_user', 'cg_shenqing_pay_user', 'cg_caiwu_pay_user', 'wms_daohuo_user', 'wms_zhijian_user', 'wms_ruku_user', 'wms_fahuo_user', 'wms_beihuo_user', 'wms_audit_user', 'wms_jianhuo_user'], 'string', 'max' => 255],
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
            'kf_create_time' => 'Kf Create Time',
            'kf_user' => 'Kf User',
            'kf_audit_status' => 'Kf Audit Status',
            'kf_audit_time' => 'Kf Audit Time',
            'kf_audit_user' => 'Kf Audit User',
            'kf_zhijian_status' => 'Kf Zhijian Status',
            'kf_zhijian_time' => 'Kf Zhijian Time',
            'kf_zhijian_user' => 'Kf Zhijian User',
            'cg_xiaoshou_time' => 'Cg Xiaoshou Time',
            'cg_xiaoshou_user' => 'Cg Xiaoshou User',
            'cg_xiaoshou_audit_status' => 'Cg Xiaoshou Audit Status',
            'cg_xiaoshou_audit_time' => 'Cg Xiaoshou Audit Time',
            'cg_xiaoshou_audit_user' => 'Cg Xiaoshou Audit User',
            'cg_suggest_time' => 'Cg Suggest Time',
            'cg_suggest_user' => 'Cg Suggest User',
            'cg_audit_status' => 'Cg Audit Status',
            'cg_audit_time' => 'Cg Audit Time',
            'cg_audit_user' => 'Cg Audit User',
            'cg_shenqing_pay_time' => 'Cg Shenqing Pay Time',
            'cg_shenqing_pay_user' => 'Cg Shenqing Pay User',
            'cg_caiwu_pay_status' => 'Cg Caiwu Pay Status',
            'cg_caiwu_pay_time' => 'Cg Caiwu Pay Time',
            'cg_caiwu_pay_user' => 'Cg Caiwu Pay User',
            'wms_daohuo_status' => 'Wms Daohuo Status',
            'wms_daohuo_time' => 'Wms Daohuo Time',
            'wms_daohuo_user' => 'Wms Daohuo User',
            'wms_zhijian_time' => 'Wms Zhijian Time',
            'wms_zhijian_user' => 'Wms Zhijian User',
            'wms_ruku_time' => 'Wms Ruku Time',
            'wms_ruku_user' => 'Wms Ruku User',
            'wms_fahuo_time' => 'Wms Fahuo Time',
            'wms_fahuo_user' => 'Wms Fahuo User',
            'wms_beihuo_time' => 'Wms Beihuo Time',
            'wms_beihuo_user' => 'Wms Beihuo User',
            'wms_audit_time' => 'Wms Audit Time',
            'wms_audit_user' => 'Wms Audit User',
            'wms_jianhuo_time' => 'Wms Jianhuo Time',
            'wms_jianhuo_user' => 'Wms Jianhuo User',
            'wms_yanhuo_time' => 'Wms Yanhuo Time',
            'wms_shangjia_time' => 'Wms Shangjia Time',
            'jiedian_status' => 'Jiedian Status',
        ];
    }
}
