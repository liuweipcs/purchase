<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
use yii\web\HttpException;
use yii\web\UploadedFile;

/**
 * This is the model class for table "pur_supplier_check_upload".
 *
 * @property integer $id
 * @property integer $check_id
 * @property integer $type
 * @property string $url
 * @property integer $status
 * @property string $creat_time
 * @property string $create_user_name
 * @property string $update_time
 * @property string $update_user_name
 */
class SupplierCheckUpload extends BaseModel
{
    public $inspection_report;
    public $product_img;
    public $abnormal_img;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_supplier_check_upload';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['check_id'], 'required'],
            [['check_id', 'type', 'status'], 'integer'],
            [['creat_time', 'update_time', 'update_user_name'], 'safe'],
            [['url', 'create_user_name','file_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'check_id' => 'Check ID',
            'type' => 'Type',
            'url' => 'Url',
            'status' => 'Status',
            'creat_time' => 'Creat Time',
            'create_user_name' => 'Create User Name',
            'update_time' => 'Update Time',
            'update_user_name' => 'Update User Name',
        ];
    }

    public static function saveFile($filePath,$file_name,$type,$check_id){
        $savemodel = new SupplierCheckUpload();
        $savemodel->url  = Yii::getAlias('@app') . '/web'.$filePath;
        $savemodel->type = $type;
        $savemodel->check_id = $check_id;
        $savemodel->status = 1;
        $savemodel->creat_time = date('Y-m-d H:i:s',time());
        $savemodel->create_user_name = Yii::$app->user->identity->username;
        $savemodel->file_name  = $file_name;
        $savemodel->save(false);
    }
}
