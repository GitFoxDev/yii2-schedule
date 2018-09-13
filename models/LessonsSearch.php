<?php

namespace app\models;

use \app\data\LessonsDataProvider;

/**
 * Class LessonsSearch
 *
 * @package app\models
 */
class LessonsSearch extends Lessons
{
    /**
     * @var string
     */
    public $teacher_name;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            //[['id'], 'integer'],
            [['teacher_name', 'title'], 'string', 'max' => 64],
            [['teacher_name', 'title'], 'trim'],
            [['teacher_name', 'title'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return Lessons::scenarios();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(),
            [
                'teacher_name' => 'Учитель',
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function search($params)
    {
        $query = Lessons::find();
        $addFilters = true;

        $dataProvider = new LessonsDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'date' => SORT_ASC,
                    'time' => SORT_ASC,
                ],
            ],
        ]);

        $query->joinWith(['group' => function(GroupsQuery $q) {
            $q->joinWith('teacher', true, 'INNER JOIN');
        }], true, 'INNER JOIN');

        if (!($this->load($params) && $this->validate())) {
            $addFilters = false;
        }

        if ($addFilters) {
            if (!empty($this->teacher_name)) {
                $names = explode(' ', $this->teacher_name);
                if (count($names) >= 2) {
                    $query->andFilterWhere(['like', 'teachers.first_name', $names[0]]);
                    $query->andFilterWhere(['like', 'teachers.last_name', $names[1]]);
                } else {
                    $query->andFilterWhere(['like', 'teachers.first_name', $names[0]])
                          ->orFilterWhere(['like', 'teachers.last_name', $names[0]]);
                }
            }

            $query->andFilterWhere(['like', 'lessons.title', $this->title]);
        }

        $filterDate = new \DateTime();
        if ($filterDate->format('w') != 0) {
            $filterDate->modify('next Sunday');
        }
        $query->andWhere(['<=', 'lessons.date', $filterDate->format('Y-m-d')]);
        $query->orderBy(['date' => SORT_ASC]);

        return $dataProvider;
    }
}
