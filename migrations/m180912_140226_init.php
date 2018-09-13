<?php

use yii\db\Migration;

/**
 * Class m180912_140226_init
 */
class m180912_140226_init extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%teachers}}', [
            'id'         => $this->primaryKey(),
            'first_name' => $this->string()->comment('Имя'),
            'last_name'  => $this->string()->comment('Фамилия'),
        ], $tableOptions);
        $this->generateTeachers(Yii::$app->params['generator']['teachers']);
        $this->createIndex('first_name', '{{%teachers}}', ['first_name']);
        $this->createIndex('last_name', '{{%teachers}}', ['last_name']);
        $this->createIndex('first_name_last_name', '{{%teachers}}', ['first_name', 'last_name']);

        $this->createTable('{{%groups}}', [
            'id'         => $this->primaryKey(),
            'teacher_id' => $this->integer()->unsigned()->notNull()->comment('Идентификатор учителя из `teachers`'),
            'title'      => $this->string()->comment('Название'),
        ], $tableOptions);
        $this->generateGroups(Yii::$app->params['generator']['groups'], Yii::$app->params['generator']['teachers']);
        $this->createIndex('teacher_id', '{{%groups}}', ['teacher_id']);

        $this->createTable('{{%lessons}}', [
            'id'       => $this->primaryKey(),
            'group_id' => $this->integer()->unsigned()->notNull()->comment('Идентификатор группы из `groups`'),
            'title'    => $this->string()->comment('Название'),
            'date'     => $this->date()->notNull()->comment('Дата'),
            'time'     => $this->time()->null()->comment('Время'),
        ], $tableOptions);
        $this->generateLessons(Yii::$app->params['generator']['lessons'], Yii::$app->params['generator']['groups']);
        $this->createIndex('group_id', '{{%lessons}}', ['group_id']);
        $this->createIndex('date_time', '{{%lessons}}', ['date', 'time']);
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->dropTable('{{%lessons}}');
        $this->dropTable('{{%groups}}');
        $this->dropTable('{{%teachers}}');
    }

    /**
     * Генерация случайных данных учителей в количестве $count и добавление их в базу данных
     *
     * @param int количество генерируемых записей $count
     */
    private function generateTeachers(int $count = 100)
    {
        $rows = [];
        
        $firstNames = [
            'Артём', 'Александр', 'Максим', 'Данил', 'Дмитрий', 'Иван', 'Кирилл', 'Никита', 'Михаил', 'Егор', 'Матвей',
            'Андрей', 'Илья', 'Алексей', 'Роман', 'Сергей', 'Владислав', 'Ярослав', 'Тимофей', 'Денис', 'Владимир',
            'Павел', 'Глеб', 'Константин', 'Богдан', 'Евгений', 'Николай', 'Степан', 'Захар', 'Тимур', 'Марк', 'Семён',
            'Фёдор', 'Георгий', 'Лев', 'Антон', 'Вадим', 'Игорь', 'Руслан', 'Вячеслав', 'Григорий', 'Макар', 'Артур',
            'Виктор', 'Станислав', 'Савелий', 'Олег', 'Давид', 'Леонид', 'Пётр', 'Юрий', 'Виталий', 'Мирон', 'Василий',
            'Всеволод', 'Елисей', 'Назар', 'Родион', 'Марат', 'Платон', 'Герман', 'Игнат', 'Святослав', 'Анатолий',
            'Тихон', 'Валерий', 'Мирослав', 'Ростислав', 'Борис', 'Филипп', 'Демьян', 'Клим', 'Гордей', 'Валентин',
            'Геннадий', 'Серафим', 'Савва', 'Аркадий',
        ];

        $lastNames = [
            'Иванов', 'Смирнов', 'Кузнецов', 'Попов', 'Васильев', 'Петров', 'Соколов', 'Михайлов', 'Новиков', 'Федоров', 
            'Морозов', 'Волков', 'Алексеев', 'Лебедев', 'Семенов', 'Егоров', 'Павлов', 'Козлов', 'Степанов', 'Николаев', 
            'Орлов', 'Андреев', 'Макаров', 'Никитин', 'Захаров', 'Зайцев', 'Соловьев', 'Борисов', 'Яковлев', 'Григорьев', 
            'Романов', 'Воробьев', 'Сергеев', 'Кузьмин', 'Фролов', 'Александров', 'Дмитриев', 'Королев', 'Гусев', 
            'Киселев', 'Ильин', 'Максимов', 'Поляков', 'Сорокин', 'Виноградов', 'Ковалев', 'Белов', 'Медведев', 
            'Антонов', 'Тарасов', 'Жуков', 'Баранов', 'Филиппов', 'Комаров', 'Давыдов', 'Беляев', 'Герасимов', 
            'Богданов', 'Осипов', 'Сидоров', 'Матвеев',
        ];

        $countFirstNames = count($firstNames);
        $countLastNames  = count($lastNames);

        for ($i = 1; $i <= $count; $i++) {
            $rows[] = [
                $i,
                $firstNames[mt_rand(0, $countFirstNames - 1)],
                $lastNames[mt_rand(0, $countLastNames - 1)],
            ];

            if (count($rows) == 5000) {
                $this->batchInsert('{{%teachers}}', ['id', 'first_name', 'last_name'], $rows);
                $rows = [];
            }
        }

        if (count($rows) > 0) {
            $this->batchInsert('{{%teachers}}', ['id', 'first_name', 'last_name'], $rows);
        }
    }

    /**
     * Генерация случайных данных групп в количестве $count и добавление их в базу данных
     *
     * @param int количество генерируемых записей  $count
     * @param int количество учителей в базе $teachersCount
     */
    private function generateGroups(int $count = 1000, $teachersCount = 100)
    {
        $rows = [];

        for ($i = 1; $i <= $count; $i++) {
            $rows[] = [
                $i,
                mt_rand(1, $teachersCount),
                'Группа #' . $i,
            ];

            if (count($rows) == 5000) {
                $this->batchInsert('{{%groups}}', ['id', 'teacher_id', 'title'], $rows);
                $rows = [];
            }
        }

        if (count($rows) > 0) {
            $this->batchInsert('{{%groups}}', ['id', 'teacher_id', 'title'], $rows);
        }
    }

    /**
     * Генерация случайных данных уроков в количестве $count и добавление их в базу данных
     *
     * @param int количество генерируемых записей  $count
     * @param int количество групп в базе $groupsCount
     */
    private function generateLessons(int $count = 2000, $groupsCount = 1000)
    {
        $titles = [
            'Русский язык', 'Литература', 'Английский язык', 'Математика', 'Информатика', 'История',
        ];

        $rows = [];

        $titlesCount = count($titles);
        for ($i = 1; $i <= $count; $i++) {

            // Время может быть не задано, в 4 из 5 случаев задаем случайное
            $randomUnixtime = mt_rand(time(), time() + 2592000); //2592000 на 30 дней вперед
            $randomDate = date('Y-m-d H:i:0', $randomUnixtime);
            $randomTime = null;

            if (mt_rand(0, 5) > 1) {
                $randomTime = date('H:i:0', $randomUnixtime);
            }

            $rows[] = [
                $i,
                mt_rand(1, $groupsCount),
                $titles[mt_rand(0, $titlesCount - 1)],
                $randomDate,
                $randomTime,
            ];

            if (count($rows) == 5000) {
                $this->batchInsert('{{%lessons}}', ['id', 'group_id', 'title', 'date', 'time'], $rows);
                $rows = [];
            }
        }

        if (count($rows) > 0) {
            $this->batchInsert('{{%lessons}}', ['id', 'group_id', 'title', 'date', 'time'], $rows);
        }
    }
}
