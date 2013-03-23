<?php

// abstract class

abstract class App_Model_Abstract
{
    private $_properties;

    public function __construct(array $options = null)
    {
        if (is_array($options)) {
            $this->populate($options);
        }
    }

    public function __set($name, $value)
    {
        $name = preg_replace('~_~', '', $name);
        $method = 'set' . $name;

        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Invalid property (' . $name . ')');
        }

        $this->$method($value);
    }

    public function __get($name)
    {
        $name = preg_replace('~_~', '', $name);
        $method = 'get' . $name;

        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Invalid property (' . $name . ')');
        }

        return $this->$method();
    }

    public function populate($options)
    {
        $methods = array_diff(get_class_methods($this), get_class_methods(get_parent_class($this)));

        foreach ($options as $key => $value) {
            $method = preg_replace('~(\b|_)([[:alnum:]])~e', 'mb_strtoupper("$2")', $key);

            $setMethod = 'set' . $method;
            if (in_array($setMethod, $methods)) {
                $this->$setMethod($value);
                $this->_properties[] = strtolower($method);
            }
        }

        return $this;
    }

    public function toArray()
    {
        $class = new ReflectionClass($this);
        $properties = $class->getProperties(ReflectionProperty::IS_PROTECTED);

        $data = array();

        if (count($properties) > 0) {
            foreach ($properties as $property) {
                if ($class->name <> $property->class)
                    continue;

                $_property = mb_strtolower(substr($property->name, 1));
                if (in_array(preg_replace('~_~', '', $_property), $this->_properties)) {
                    if ($property->isProtected()) {
                        $property->setAccessible(TRUE);
                        $data[$_property] = $property->getValue($this);
                    }
                }
            }
        }

        return $data;
    }

}