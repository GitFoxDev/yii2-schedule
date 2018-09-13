<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "lessons".
 *
 * @property int $id
 * @property int $group_id Идентификатор группы из `groups`
 * @property string $title Название
 * @property string $date Название
 */
class Lessons extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'lessons';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'       => 'ID',
            'group_id' => 'Идентификатор группы из `groups`',
            'title'    => 'Название',
            'date'     => 'Дата',
            'time'     => 'Время',
        ];
    }

    /**
     * {@inheritdoc}
     * @return LessonsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new LessonsQuery(get_called_class());
    }

    /**
     * Связь с моделью групп один к одному
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGroup()
    {
        return $this->hasOne(Groups::class, ['id' => 'group_id']);
    }
}
