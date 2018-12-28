<?php

namespace app\models;

use app\models\base\BaseModel;

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
class SupplierContactInformation extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%supplier_contact_information}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['supplier_id', 'contact_person', 'contact_number'], 'required'],
            [['contact_id', 'supplier_id'], 'integer'],
            [['contact_person', 'english_address'], 'string', 'max' => 30],
            //['contact_number', 'match', 'pattern' => '/^1[34578]\d{9}$/'],
            [['contact_fax', 'contact_zip', 'qq', 'micro_letter', 'skype'], 'string', 'max' => 20],
            [['contact_number','want_want','chinese_contact_address', 'corporate'], 'string', 'max' => 100],
            [['email'], 'string', 'max' => 255],
            ['email', 'email'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'contact_id'              => Yii::t('app', 'ID'),
            'supplier_id'             => Yii::t('app', '供应商ID'),
            'contact_person'          => Yii::t('app', '联系人'),
            'contact_number'          => Yii::t('app', '联系电话'),
            'contact_fax'             => Yii::t('app', 'Fax'),
            'chinese_contact_address' => Yii::t('app', '中文联系地址'),
            'english_address'         => Yii::t('app', '英文联系地址'),
            'contact_zip'             => Yii::t('app', '联系邮编'),
            'qq'                      => Yii::t('app', 'QQ'),
            'micro_letter'            => Yii::t('app', '微信'),
            'want_want'               => Yii::t('app', '旺旺'),
            'skype'                   => Yii::t('app', 'Skype'),
            'sex'                   => Yii::t('app', '性别'),
        ];
    }

    /**
     * 保存数据
     * @param $data
     */
    public function saveSupplierContact($data, $supplier_id,$bool=false)
    {
        $model = new self();
        $model->validate();
        if (!empty($data['SupplierContactInformation'])) {
            $supplier_contact_information_info = [];

            foreach ($data['SupplierContactInformation'] as $c => $v)
            {
                $sb =[];
                foreach ($v as $d => $k) {
                    $sb[] = [
                        'supplier_id'             => $supplier_id['id'],
                        'supplier_code'           => $supplier_id['code'],
                        'contact_person'          => $data['SupplierContactInformation']['contact_person'][$d],
                        'corporate'          => $data['SupplierContactInformation']['corporate'][$d],
                        'contact_number'          => $data['SupplierContactInformation']['contact_number'][$d],
//                        'contact_fax'             => $data['SupplierContactInformation']['contact_fax'][$d],
                        'chinese_contact_address' => $data['SupplierContactInformation']['chinese_contact_address'][$d],
      //                  'english_address'         => $data['SupplierContactInformation']['english_address'][$d],
//                        'contact_zip'             => $data['SupplierContactInformation']['contact_zip'][$d],
                        'qq'                      => $data['SupplierContactInformation']['qq'][$d],
                        'micro_letter'            => $data['SupplierContactInformation']['micro_letter'][$d],
                        'email'               => $data['SupplierContactInformation']['email'][$d],
                        'want_want'               => $data['SupplierContactInformation']['want_want'][$d],
//                        'skype'                   => $data['SupplierContactInformation']['skype'][$d],
//                        'sex'                   => $data['SupplierContactInformation']['sex'][$d],

                    ];
                }
            }
            foreach ($sb as $sk => $v)
            {
                if ($bool) {
                    $supplier_contact_information_info[$sk] = $v;
                } else {
                    Yii::$app->db->createCommand()->insert(SupplierContactInformation::tableName(),$v)->execute();
                }
            }

            if ($bool) {
                return $supplier_contact_information_info;
            }

        }

    }

    /**
     * 通过供应商ID获取一条地址
     * @param $id
     * @return false|null|string
     */
    public  static function  getAddrss($id)
    {
        $addr= self::find()->select('chinese_contact_address')->where(['supplier_id'=>$id])->orderBy('contact_id desc')->scalar();
        return $addr;
    }
}

