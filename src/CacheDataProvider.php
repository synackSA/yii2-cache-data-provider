<?php

namespace synacksa\cachedataprovider;

use yii\db\ActiveQueryInterface;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\db\Connection;
use yii\db\QueryInterface;

class CacheDataProvider extends \yii\data\ActiveDataProvider
{
    public $cache=false;

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
    public function prepareModels(){

        if(!$this->cache){
            return parent::prepareModels();
        }

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

        $db = $this->db;
        if($db === null){
            $modelClass = $this->query->modelClass;
            $db = $modelClass::getDb();
        }

        return $db->cache(function($db) use ($query){
            return $query->all($db);
        });
    }

    /**
     * @inheritdoc
     */
    protected function prepareTotalCount()
    {
        if(!$this->cache){
            return parent::prepareTotalCount();
        }
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
        });
    }
    
}
