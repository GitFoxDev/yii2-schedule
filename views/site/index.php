<?php

use yii\grid\GridView;
use yii\data\ActiveDataProvider;

/* @var $this \yii\web\View */
/* @var $dataProvider ActiveDataProvider */
/* @var $searchModel \app\models\LessonsSearch */

$this->title = Yii::$app->name;
?>

<h3>Поиск</h3>
<?= $this->render('_search', ['model' => $searchModel]) ?>
<hr>
<h3>Уроки</h3>
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'format' => 'text',
            'label'  => 'Учитель',
            'value'  => function($data) {
                return $data->group->teacher->first_name . ' ' . $data->group->teacher->last_name;
            }
        ],
        [
            'attribute' => 'group.title',
            'label'     => 'Группа',
        ],
        [
            'attribute' => 'title',
            'label'     => 'Урок',
            'enableSorting' => false,
        ],
        [
            'attribute' => 'date',
            'format'    => ['date', 'php:d M'],
            'enableSorting' => false,
        ],
        [
            'attribute' => 'time',
            'format'    => ['date', 'php:H:i'],
        ],
    ],
]); ?>