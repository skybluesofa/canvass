<?php
namespace SkyBlueSofa\Canvass\Canvass\Map\Entity\Contract;

use SkyBlueSofa\Canvass\Support\Wordpress;

abstract class Entity
{
    protected $tableName;
    
    protected $id;
    protected $created;
    protected $updated;

    abstract protected function createDbSql();

    public function createDbTable()
    {
        Wordpress::dbDelta($this->createDbSql());
    }

    protected function tableName()
    {
        return Wordpress::db()->prefix . $this->tableName;
    }

    public function save()
    {
        if (empty($this->getId())) {
            $this->insert();
        } else {
            $this->update();
        }

        return $this;
    }

    protected function insert()
    {
        $this->created = $this->generateDate();
        $this->updated = $this->generateDate();

        return Wordpress::db()->insert(
            $this->tableName(),
            $this->getData()
        );
    }

    protected function update()
    {
        $this->updated = $this->generateDate();

        $data = $this->getData();
        unset($data['id']);

        return Wordpress::db()->update(
            $this->tableName(),
            $data,
            ['id' => $this->getId()]
        );
    }

    protected function getData()
    {
        $data = get_object_vars($this);
        
        unset($data['tableName']);
        
        return $data;
    }

    protected function generateDate()
    {
        return date("Y-m-d H:i:s");
    }

    public function getById($id)
    {
    }

    public function getAll()
    {
        return $this->getSqlResults("SELECT * from " . $this->tableName() . " ORDER BY id");
    }

    public function getSqlResults($sql)
    {
        return Wordpress::db()->get_results(
            $sql,
            ARRAY_A
        );
    }

    protected function collect($rows)
    {
        $collection = [];
        foreach ($rows as $row) {
            $collection[] = $this->createFromArray($row);
        }

        return $collection;
    }

    public function createFromArray($data)
    {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }
}
