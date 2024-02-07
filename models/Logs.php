<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "logs".
 *
 * @property string $hoat
 * @property string $date
 * @property string $url
 * @property string $useragent
 * @property string $os
 * @property string $archi
 * @property string $browser
 */
class Logs extends \yii\db\ActiveRecord
{
    public $date_end;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'logs';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['host', 'date', 'url', 'useragent'], 'required'],
            [['date'], 'safe'],
            [['host', 'url', 'useragent', 'os', 'archi', 'browser'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'host' => 'IP адрес',
            'date' => 'Дата',
            'url' => 'URL',
            'useragent' => 'Useragent',
            'os' => 'ОС',
            'archi' => 'Архитектура',
            'browser' => 'Браузер',
        ];
    }
}
