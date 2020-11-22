<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Class MCatalog
 * @package app\models
 * @property integer $ID
 * @property string $TITLE
 * @property string $DEPTH
 * @property string $PARENTID
 */
class Catalogs extends ActiveRecord
{
    /**
     * Возвращает массив с каталогами
     * @return array|ActiveRecord[]
     */
    public static function GetAllCatalogsArray () {
        return self::find()->asArray()->all();
    }

    /**
     * Добаввляет каталог
     * @param $data
     */
    public static function AddCatalog($data)
    {
        $department = new self();

        $department->TITLE = $data->TITLE;
        $department->DEPTH = $data->DEPTH;
        $department->PARENTID = $data->PARENTID;

        $department->save();
    }

    /**
     * Возвращает массив подкаталогов по ID каталога
     * @param $id
     * @return array|ActiveRecord[]
     */
    private static function GetChildrenIdByParentId($id) {
        return self::find()->select("ID")->where("PARENTID = $id")->asArray()->all();
    }

    /**
     * Удаляет указанный каталог вместе с подкаталогами
     * @param $id
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public static function DeleteCatalog($id)
    {
        $department = self::find()->where("ID = $id")->one();
        $children = self::GetChildrenIdByParentId($id);
        if (count($children) != 0)
        {
            foreach ($children as $child) {
                self::DeleteCatalog($child['ID']);
            }
        }
        $department->delete();

    }

    /**
     * Редактирует выбранный каталог
     * @param $data
     */
    public static function EditCatalog($data)
    {
        $department = self::find()->where("ID = $data->ID")->one();

        $department->TITLE = $data->TITLE;
        $department->DEPTH = $data->DEPTH;
        $department->PARENTID = $data->PARENTID;

        $department->save();
    }

}