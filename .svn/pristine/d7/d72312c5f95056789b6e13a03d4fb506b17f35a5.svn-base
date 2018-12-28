<?php

namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use Yii;


/**
 * This is the model class for table "{{%supplier_images}}".
 *
 * @property integer $id
 * @property integer $supplier_id
 * @property string $image_url
 */
class SupplierImages extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%supplier_images}}';
    }
    public $img_id;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [

            [['img_id', 'supplier_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'img_id' => Yii::t('app', 'ID'),
            'supplier_id' => Yii::t('app', '供应商ID'),
            'image_url' => Yii::t('app', '附属图片'),
        ];
    }
    /**
     * 保存数据
     * @param $data
     */
    public  function saveSupplierImg($data,$supplier_id)
    {
        $model = new self();
        $model->validate();

        if (!empty($data['SupplierImages']))
        {
            $data['SupplierImages']['supplier_id'] = $supplier_id['id'];
                $status = self::saveSupplierImageBcc($data['SupplierImages']);
                if ($status) {
                    return $data;
                } else {
                    return false;
            }


            // foreach ($data['SupplierImages'] as $c => $v)
            // {
            //     if (!empty($v))
            //     {
            //         foreach ($v as $d => $k) {

            //             Yii::$app->db->createCommand()->batchInsert(SupplierImages::tableName(), ['supplier_id', 'image_url'], [
            //                 [$supplier_id['id'], $k],
            //             ])->execute();

            //         }
            //     }

            // }
        }

    }
    /**
     * 保存数据：新增和修改
     * $is_insert：是否新增
     */
    public static function saveSupplierImageBcc($data, $is_insert=true)
    {
        $where = ['supplier_id'=>$data['supplier_id'], 'image_status'=>1];
        $findImageModel = self::find()->where($where)->orderBy('image_id desc')->one();// 找到最近的一个记录
        if (!empty($findImageModel)) {
            foreach ($data as $k => $v) {// 旧的值赋给当前为空的字段
                if (empty($v)) {
                    $data[$k] = $findImageModel->$k;
                }
            }
            $findImageModel->image_status = 2;// 旧的记录变为历史
            $status = $findImageModel->save();
            if ($status) {
                $is_insert = true;
            } else {
                return false;
            }

            self::updateAll(['image_status' => 2],$where);// 所有的 记录都 进入历史记录
        }

        if ($is_insert) {
            $insertFields = $insertInfo = [];
            foreach ($data as $k=>$v) {
                $insertFields[] = $k;
                $insertInfo[] = is_array($v)?implode(';',$v):$v;// 数组转成字符串
            }
            return Yii::$app->db->createCommand()->batchInsert(SupplierImages::tableName(), $insertFields, [$insertInfo])->execute();
        }
    }
}
