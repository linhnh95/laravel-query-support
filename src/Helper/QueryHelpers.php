<?php

namespace Linhnh95\LaravelQuerySupport\Helper;

use Illuminate\Database\Eloquent\Builder;

class QueryHelpers
{
    /**
     * @var Builder|\Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * @var
     */
    protected $query;

    /**
     * @param Builder $builder
     */
    public function setQuery(Builder $builder)
    {
        $this->query = $builder;
        $this->model = $builder->getModel();
    }

    /**
     * @return Builder
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param array $params
     * @param array $conditions
     *
     * @return $this|Builder
     */
    public function createWhere(array $params, array $conditions)
    {
        // Can't Create Where SQL If Not Isset $params
        if (empty($params)) {
            return $this;
        }

        // Can't Create Where SQL If Not Isset $conditions
        if (empty($conditions)) {
            return $this;
        }

        foreach ($params as $key => $value) {

            // Pass If Not Isset Conditions With Keys
            if (!isset($conditions[$key])) {
                continue;
            }

            // Create Where SQL
            $this->where($value, $conditions[$key], $this->getQuery());
        }
        return $this->getQuery();
    }

    /**
     * @param $value
     * @param array $condition
     * @param null $query
     *
     * @return Builder|mixed
     */
    private function where($value, array $condition, $query = null)
    {
        if (isset($condition['group_relation'])) {
            $this->whereGroup($value, $condition['groups'], $query);
        } else {
            return $this->buildWhere($value, $condition, $query);
        }
    }

    /**
     * @param $value
     * @param array $condition
     * @param $query
     */
    private function whereGroup($value, array $condition, $query)
    {
        $conditionGroup = $this->pluckConditionWithRelation($condition);
        if (!empty($conditionGroup)) {
            foreach ($conditionGroup as $relation => $cond) {
                $query->whereHas($relation, function ($query) use ($value, $cond, $relation) {
                    $this->buildWhereByGroup($query, $value, $cond, $relation);
                });
            }
        } else {
            $this->buildWhereByGroup($query, $value, $condition);
        }
    }

    /**
     * @param $query
     * @param $value
     * @param $condition
     * @param $relation
     *
     * @return mixed
     */
    private function buildWhereByGroup($query, $value, $condition, $relation = '')
    {
        $query->where(function ($q) use ($value, $condition, $relation) {
            foreach ($condition as $cond) {
                if ($relation !== '') {
                    $cond['relation'] = str_replace($relation, '', $cond['relation']);
                    $cond['relation'] = trim($cond['relation'], '.');
                }
                $this->where($value, $cond, $q);
            }
        });
        return $query;
    }

    /**
     * @param array $condition
     * @return array
     */
    private function pluckConditionWithRelation(array $condition)
    {
        $result = [];
        if (!empty($condition)) {
            foreach ($condition as $key => $value) {
                if (isset($value['group_relation'])) {
                    continue;
                }
                if ($value['relation'] !== '') {
                    $result[$value['relation']][$key] = $value;
                }
            }
        }
        return $result;
    }

    /**
     * @param $value
     * @param array $condition
     * @param null $query
     *
     * @return Builder|mixed
     */
    private function buildWhere($value, array $condition, $query = null)
    {
        if (isset($condition['key_field']) && $condition['key_field'] !== '') {
            $value = $value[$condition['key_field']] ?? $value;
        }
        if (is_array($value)) {
            return $this->whereSpecialCondition($value, $condition, $query);
        } else {
            if ($condition['relation'] !== '') {
                return $query->whereHas($condition['relation'], function ($query) use ($condition, $value) {
                    return $query->where($condition['table'] . '.' . $condition['field'], $value);
                });
            } else {
                return $query->where($condition['table'] . '.' . $condition['field'], $value);
            }
        }
    }

    /**
     * @param $value
     * @param array $condition
     * @param null $query
     *
     * @return Builder|mixed
     */
    private function whereSpecialCondition($value, array $condition, $query = null)
    {
        if ($condition['relation'] !== '') {
            return $query->whereHas($condition['relation'], function ($query) use ($value, $condition) {
                return $this->buildWhereSpecial($value, $condition, $query);
            });
        } else {
            return $this->buildWhereSpecial($value, $condition, $query);
        }
    }

    /**
     * @param $value
     * @param array $condition
     * @param null $query
     *
     * @return mixed
     */
    private function buildWhereSpecial($value, array $condition, $query = null)
    {
        $key = key($value);
        if (is_numeric($key)) {
            return $query->whereIn($condition['table'] . '.' . $condition['field'], $value);
        } else {
            return $this->whereSpecial($value, $condition, $query);
        }
    }

    /**
     * @param $value
     * @param $condition
     * @param $query
     *
     * @return mixed
     */
    private function whereSpecial($value, $condition, $query)
    {
        foreach ($value as $cond => $val) {
            switch ($cond) {
                case 'exist':
                    return $query->whereHas($val);
                    break;
                case 'not_exist':
                    return $query->whereDoesntHave($val);
                    break;
                case 'in':
                    return $query->whereIn($condition['table'] . '.' . $condition['field'], $val);
                    break;
                case 'or':
                    return $query->orWhere($condition['table'] . '.' . $condition['field'], $val);
                    break;
                case 'orLike':
                    return $query->orWhere($condition['table'] . '.' . $condition['field'], 'like', $val);
                    break;
                case 'orIn':
                    return $query->orWhereIn($condition['table'] . '.' . $condition['field'], $val);
                    break;
                default:
                    return $query->where($condition['table'] . '.' . $condition['field'], $cond, $val);
                    break;
            }
        }
    }
}
