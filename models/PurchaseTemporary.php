<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
use app\config\Vhelper;
/**
 * This is the model class for table "{{%purchase_temporary}}".
 *
 * @property integer $id
 * @property integer $product_id
 * @property integer $create_id
 */
class PurchaseTemporary extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_temporary}}';
    }
    public $file_execl;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['file_execl'], 'file', 'skipOnEmpty' => false, 'extensions' => 'csv'],
            [['product_id', 'create_id','sku','purchase_quantity'], 'required'],
            [['product_id', 'create_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'product_id' => Yii::t('app', '产品ID'),
            'create_id' => Yii::t('app', '创建人ID'),
        ];
    }

    public function getDefaultQuotes(){
        return $this->hasOne(SupplierQuotes::className(),['id'=>'quotes_id'])->via('defaultSupplier');
    }

    public function getDefaultSupplier(){
        return $this->hasOne(ProductProvider::className(),['sku'=>'sku'])->where(['is_supplier'=>1]);
    }
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
