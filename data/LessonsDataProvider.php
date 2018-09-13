<?php

namespace app\data;

use yii\data\ActiveDataProvider;
use yii\base\InvalidConfigException;
use yii\db\QueryInterface;

class LessonsDataProvider extends ActiveDataProvider
{
    /**
     * {@inheritdoc}
     */
    protected function prepareModels()
    {
        if (!$this->query instanceof QueryInterface) {
            throw new InvalidConfigException('The "query" property must be an instance of a class that implements the QueryInterface e.g. yii\db\Query or its subclasses.');
        }

        // Создаем 2 запроса, один с данными, другой без данных
        $dataQuery = clone $this->query;
        $nullQuery = clone $dataQuery;

        $useUnion = true;
        $dataQuery->andWhere(['IS NOT', 'lessons.time', null]);
        $nullQuery->andWhere(['lessons.time' => null]);

        $dataCount = $dataQuery->count('*');

        if (($pagination = $this->getPagination()) !== false) {
            $pagination->totalCount = $this->getTotalCount();
            if ($pagination->totalCount === 0) {
                return [];
            }

            $nullQueryPage   = 0;
            $nullQueryOffset = 0;

            // Вычисляем сколько будет заполненных записей на последней странице, чтобы добавить корректное количество пустых записей
            $totalPages = (int)floor($dataCount / $pagination->getPageSize()); // Количество полностью заполненных страниц
            $dataRowsLastPage = $dataCount - ($pagination->getPageSize() * $totalPages); // Количество заполненных записей на последней странице
            $nullRowsFirstPage = $pagination->getPageSize() - $dataRowsLastPage; // Количество пустых записей на первой странице

            // Номер последней страницы с не пустыми записями
            if ($dataRowsLastPage != 0) {
                $dataLastPage = $totalPages + 1;
            } else {
                $dataLastPage = $totalPages;
            }

            if ($pagination->getPage() + 1 == $dataLastPage) {
                // Сейчас на последней странице с заполненными записями, необходимо добавить корректное кол-во пустых записей

                if ($dataRowsLastPage == 0) {
                    // На странице нет заполненных данных, выводим все пустые
                    $nullQuery->limit($pagination->getLimit())->offset($nullQueryOffset);
                } elseif ($dataRowsLastPage < $pagination->getPageSize()) {
                    // На странице есть заполненные данных, дополняем их пустыми до лимита
                    $nullQuery->limit($nullRowsFirstPage)->offset($nullQueryOffset);
                } else {
                    // Страница содержит только заполненные данные до лимита. Пустые не добавляем.
                    $useUnion = false;
                }

            } elseif ($pagination->getPage() + 1 > $dataLastPage) {
                // Страницы с заполненными записями кончились, движемся по страницам с полностью пустыми записями

                $nullQueryPage   = $pagination->getPage() - $dataLastPage;
                $nullQueryOffset = $nullQueryPage * $pagination->getLimit() + $nullRowsFirstPage; // Вычисляем отступ для запроса

                $nullQuery->limit($pagination->getLimit())->offset($nullQueryOffset);
            } else {
                // Сейчас на страницах, которые состоят только из заполненных записей, добавлять не можем
                $useUnion = false;
            }

            $dataQuery->limit($pagination->getLimit())->offset($pagination->getOffset());
        }

        // Сортировка заполненных записей
        if (($sort = $this->getSort()) !== false) {
            $dataQuery->addOrderBy($sort->getOrders());
        }

        // Склеивание данных, если это возможно
        if ($useUnion) {
            $dataQuery->union($nullQuery);
        }

        return $dataQuery->all($this->db);
    }
}
