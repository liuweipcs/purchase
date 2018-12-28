<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
use app\config\Vhelper;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
/**
 * This is the model class for table "{{%logistics_import}}".
 *
 * @property integer $id
 * @property string $logistics_num
 * @property string $purchase_order_num
 * @property integer $create_id
 * @property string $create_name
 * @property string $create_time
 * @property string $update_time
 * @property integer $push_status
 * @property integer $push_res
 * @property integer $is_deleted
 */
class LogisticsImport extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%logistics_import}}';
    }

    public $file_execl;

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['create_time'],
                    \yii\db\ActiveRecord::EVENT_BEFORE_UPDATE => ['update_time'],
                ],
                // if you're using datetime instead of UNIX timestamp:
                'value' => date('Y-m-d H:i:s',time()),
            ],

        ];
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['logistics_num','create_id'], 'required'],
            [['id'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['name'], 'string', 'max' => 20],
            [['logistics_num'], 'string', 'max' => 30],
            [['purchase_order_num'], 'string', 'max' => 30],
            [['create_name'], 'string', 'max' => 30],
            [['push_res'], 'string', 'max' => 20],
            [['push_status'], 'default', 'value' => 0],
            [['is_deleted'], 'default', 'value' => 1],
            ['file_execl', 'file', 'extensions' => ['csv']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'logistics_num' => Yii::t('app', '物流单号'),
            'purchase_order_num' => Yii::t('app', '采购单号'),
            'create_id' => Yii::t('app', '创建人id'),
            'create_name' => Yii::t('app', '创建人名称'),
            'create_time' => Yii::t('app', '创建时间'),
            'update_time' => Yii::t('app', '更新时间'),         
            'push_status' => Yii::t('app', '推送状态'),
            'push_res' => Yii::t('app', '推送结果'),
            'is_deleted' => Yii::t('app', '操作'),
        ];
    }

    /**
     * 上传文件
     */
      public function upload()
    {


        $uploadpath = 'Uploads/' . date('Ymd') . '/';  //上传路径
        // 图片保存在本地的路径：images/Uploads/当天日期/文件名，默认放置在basic/web/下
        $dir = '/images/' . $uploadpath;
        //生成唯一uuid用来保存到服务器上图片名称
        $pickey = Vhelper::genuuid();
        $filename = $pickey . '.' . $this->file_execl->getExtension();
        //如果文件夹不存在，则新建文件夹
        $filepath= Vhelper::fileExists(Yii::getAlias('@app') . '/web' . $dir);
        $file = $filepath.$filename;

        if ($this->file_execl->saveAs($file))
        {

            return $file;
        } else{
            return false;
        }

    }
}
