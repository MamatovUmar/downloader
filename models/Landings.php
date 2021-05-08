<?php

namespace app\models;

use Yii;
use yii\helpers\Json;

/**
 * This is the model class for table "landings".
 *
 * @property int $id
 * @property int|null $status
 * @property int|null $date
 * @property int|null $is_paid
 * @property string|null $file_name
 * @property string|null $url
 * @property string|null $error
 */
class Landings extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'landings';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['status', 'date', 'is_paid'], 'integer'],
            ['date', 'default', 'value' => time()],
            [['file_name', 'url'], 'string', 'max' => 255],
            [['file_name', 'url'], 'required'],
            ['error', 'safe']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'status' => 'Status',
            'date' => 'Date',
            'is_paid' => 'Is Paid',
            'file_name' => 'File Name',
            'url' => 'Url',
        ];
    }

    public function newLanding(Parser $landing)
    {
        $this->url = $landing->url;
        $this->file_name = $landing->project_name;
        $this->save();
    }

    public function parseError($exception)
    {
        $this->error = Json::encode($exception);
        $this->save();
    }
}
