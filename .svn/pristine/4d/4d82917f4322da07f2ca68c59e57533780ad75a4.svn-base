<?php

namespace app\api\v1\models;

use Yii;

/**
 * This is the model class for table "pur_stock".
 *
 * @property string $id
 * @property string $sku
 * @property string $warehouse_code
 * @property string $on_way_stock
 * @property string $available_stock
 * @property string $stock
 * @property integer $left_stock
 * @property string $created_at
 * @property string $update_at
 */
class SupplierProposalTemplate extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_proposal_template}}';
    }
	
	
	
	 public static function SaveProposalTemplate($data)
    {
        try {
			foreach ($data->data->array as $k => $datass) {
				$models = self::find()->where(['purchasetemplateid' => $datass->purchaseTemplateId])->one();
				if ($models) {
					
					   $models->warehousename= htmlspecialchars($datass->warehouseName,ENT_QUOTES);//productCode
					   $models->warehouseidkeys= $datass->warehouseIdKeys;
					   $models->warehousetype= $datass->warehouseType;
					   $models->purchasetemplatename= htmlspecialchars($datass->purchaseTemplateName,ENT_QUOTES);
					   $models->suggestiontype = $datass->suggestionType;
					   $models->frequencyofpurchase = $datass->frequencyOfPurchase;
					   $models->formuladaily      = htmlspecialchars($datass->formulaDaily,ENT_QUOTES);
					   // $models->purchasetemplateid    = $datass->purchaseTemplateId;
					   $models->update();

				} else {
				   $model                      = new self;
				   $model->warehousename= htmlspecialchars($datass->warehouseName,ENT_QUOTES);//productCode
				   $model->warehouseidkeys= $datass->warehouseIdKeys;
				   $model->warehousetype= $datass->warehouseType;
				   $model->purchasetemplatename= htmlspecialchars($datass->purchaseTemplateName,ENT_QUOTES);
				   $model->suggestiontype = $datass->suggestionType;
				   $model->frequencyofpurchase = $datass->frequencyOfPurchase;
				   $model->formuladaily      = htmlspecialchars($datass->formulaDaily,ENT_QUOTES);
				   $model->purchasetemplateid    = $datass->purchaseTemplateId;
				   $model->save();


				}
			}

        } catch (Exception $e) {
			echo $e->getMessage();

        }
    }


}
