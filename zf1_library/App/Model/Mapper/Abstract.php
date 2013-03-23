<?php

// abstract class

abstract class App_Model_Mapper_Abstract
{
    private $_dbTable;
    private $_dbModel;
    private $_model;

    public function __call($methodName, $args)
    {
        $className = get_class($this);

        try {
            if (method_exists($this->getModel(), $methodName)) {
                return call_user_func_array(array($this->getModel(), $methodName), $args);
            } else if (method_exists($this->getDbTable(), $methodName)) {
                return call_user_func_array(array($this->getDbTable(), $methodName), $args);
            } else {
                throw new Zend_Exception('The method ' . $methodName . '() was not implemented in <i>' . $className . '</i>');
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function setDbTable($dbTable)
    {
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }

        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Zend_Exception('Invalid table data gateway provided');
        }

        $this->_dbTable = $dbTable;
    }

    public function getDbTable()
    {
        if (null === $this->_dbTable) {
            $this->setDbTable('Application_Model_DbTable_' . $this->getModel());
        }

        return $this->_dbTable;
    }

    private function setDbModel($dbModel)
    {
        if (is_string($dbModel)) {
            $dbModel = new $dbModel();
        }

        $this->_dbModel = $dbModel;
    }

    private function getDbModel()
    {
        if (null === $this->_dbModel) {
            $this->setDbModel('Application_Model_' . $this->getModel());
        }

        return $this->_dbModel;
    }

    public function setModel($model)
    {
        $this->_model = $model;
    }

    public function getModel()
    {
        if (null === $this->_model) {
            $this->setModel('Abstract');
        }

        return $this->_model;
    }

    public function getDbPrimary()
    {
        $primaryKey = $this->getDbTable()->info('primary');

        return $primaryKey[1];
    }

    public function save($model)
    {
        $data = $model->toArray();

        if (0 === ($id = $model->getId())) {
            $this->insert($data);
        } else {
            $this->update($data, $id);
        }
    }

    public function insert($data)
    {
        $this->getDbTable()->insert($data);
    }

    public function update($data, $id)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto($this->getDbPrimary() . ' = ?', $id);

        $this->getDbTable()->update($data, $where);
    }

    public function delete($id)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto($this->getDbPrimary() . ' = ?', $id);

        $this->getDbTable()->delete($where);
    }

    public function find($id)
    {
        $result = $this->getDbTable()->find($id);
        if (count($result)) {
            return $this->getDbModel()->populate($result->current())->toArray();
        } else {
            throw new Zend_Controller_Action_Exception('This page does not exist', 404);
        }
    }

    public function fetchAll($select)
    {
        return $this->getDbTable()->fetchAll($select);
    }

}