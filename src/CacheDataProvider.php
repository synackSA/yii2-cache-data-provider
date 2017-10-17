<?php

namespace synacksa\cachedataprovider;

use Yii;
use yii\db\QueryInterface;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

class CacheDataProvider extends \yii\data\ActiveDataProvider
{
    /**
     * @var array The caching length and dependency object
     */
    public $cache = [
        'length' => null,
        'dependency' => null,
    ];

    /**
     * @return int The length the cache should be valid for
     */
    public function getCacheLength()
    {
        return ArrayHelper::getValue($this->cache, 'length');
    }

    /**
     * Created the dependency if it is formatted as an array
     * Otherwise it will return the value
     * @return mixed the dependency
     */
    public function getCacheDependency()
    {
        $dependency = ArrayHelper::getValue($this->cache, 'dependency');

        if (is_array($dependency)) {
            return Yii::createObject($dependency);
        }

        return $dependency;
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }

    /**
     * @inheritdoc
     */
    protected function prepareModels()
    {
        if (!$this->query instanceof QueryInterface) {
            throw new InvalidConfigException('The "query" property must be an instance of a class that implements the QueryInterface e.g. yii\db\Query or its subclasses.');
        }

        $query = clone $this->query;
        if (($pagination = $this->getPagination()) !== false) {
            $pagination->totalCount = $this->getTotalCount();
            $query->limit($pagination->getLimit())->offset($pagination->getOffset());
        }

        if (($sort = $this->getSort()) !== false) {
            $query->addOrderBy($sort->getOrders());
        }

        return \Yii::$app->db->cache(function ($db) use ($query) {
            return $query->all($db);
        }, $this->cacheLength, $this->cacheDependency);
    }

    /**
     * @inheritdoc
     */
    protected function prepareTotalCount()
    {
        if (!$this->query instanceof QueryInterface) {
            throw new InvalidConfigException('The "query" property must be an instance of a class that implements the QueryInterface e.g. yii\db\Query or its subclasses.');
        }
        $query = clone $this->query;

        $db = $this->db;
        if($db === null){
            $modelClass = $this->query->modelClass;
            $db = $modelClass::getDb();
        }

        return $db->cache(function($db) use($query){
            return (int) $query->limit(-1)->offset(-1)->orderBy([])->count('*', $db);
        }, $this->cacheLength, $this->cacheDependency);
    }
}

?>
