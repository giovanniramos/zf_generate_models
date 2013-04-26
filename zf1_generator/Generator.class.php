<?php

/* * **********************************************************************************
 * 
 * ZEND FRAMEWORK - MODELS GENERATOR (https://github.com/giovanniramos/zf_generate_models)
 * 
 * Copyright (c) 2013 Giovanni Ramos (https://github.com/giovanniramos)
 * 
 * Licensed under the MIT License
 * 
 * ********************************************************************************* */

class Generator
{
    private static $dbh;
    private static $adapter;
    private static $hostname;
    private static $username;
    private static $password;
    private static $database;
    private static $backup = true;

    public static function connect($adapter, $hostname, $username, $password, $database)
    {
        self::setAdapter($adapter);
        self::setParameters($hostname, $username, $password, $database);

        try {
            switch (self::getAdapter()) {
                case 'MYSQL':
                case 'PDO_MYSQL':
                    self::$dbh = new PDO('mysql:host=' . $hostname . ';dbname=' . $database, $username, $password);
                    break;
                case 'PGSQL':
                case 'PDO_PGSQL':
                    self::$dbh = new PDO('pgsql:host=' . $hostname . ';dbname=' . $database, $username, $password);
                    break;
                case 'SQLITE':
                case 'PDO_SQLITE':
                    self::$dbh = new PDO('sqlite:' . $database);
                    break;
                default: exit('Adapter not implemented!');
            }

            self::$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            $msg = mb_convert_encoding($e->getMessage(), 'utf8');
            exit($msg);
        }

        return self::$dbh;
    }

    public static function reconnect($database)
    {
        self::connect(self::getAdapter(), self::$hostname, self::$username, self::$password, $database);
    }

    public static function setBackup($backup)
    {
        self::$backup = $backup;
    }

    public static function setAdapter($adapter)
    {
        self::$adapter = $adapter;
    }

    public static function getAdapter()
    {
        return self::$adapter;
    }

    public static function setParameters($hostname, $username, $password, $database)
    {
        self::$hostname = $hostname;
        self::$username = $username;
        self::$password = $password;
        self::$database = basename($database);
    }

    public static function getDatabase()
    {
        return self::$database;
    }

    public static function getDatabases()
    {
        $dbs = self::list_dbs();

        $databases = null;
        foreach ($dbs as $db)
            $databases[] = basename(end($db));

        return $databases;
    }

    public static function getTablesFromDatabase($database)
    {
        $tbs = self::list_tbs($database);

        $tables = null;
        foreach ($tbs as $tb)
            $tables[] = end($tb);

        return $tables;
    }

    public static function list_dbs()
    {
        switch (self::getAdapter()):
            case 'MYSQL':
            case 'PDO_MYSQL':
                $dbs = self::query("SHOW DATABASES WHERE `Database` NOT IN ('information_schema', 'performance_schema', 'phpmyadmin', 'mysql', 'webauth');", false);
                break;
            case 'PGSQL':
            case 'PDO_PGSQL':
                $dbs = self::query("SELECT datname FROM pg_database JOIN pg_authid ON pg_database.datdba = pg_authid.oid AND datname NOT LIKE 'template%';", false);
                break;
            case 'SQLITE':
            case 'PDO_SQLITE':
                $dbs = self::query("PRAGMA database_list;", false);
                break;
        endswitch;

        return $dbs;
    }

    public static function list_tbs($database)
    {
        if (is_null($database))
            return;

        switch (self::getAdapter()):
            case 'MYSQL':
            case 'PDO_MYSQL':
                $tbs = self::query("SHOW TABLES FROM " . $database . ";", false);
                break;
            case 'PGSQL':
            case 'PDO_PGSQL':
                self::reconnect($database);
                $tbs = self::query("SELECT table_name FROM information_schema.tables WHERE table_catalog = '" . $database . "' AND table_schema NOT IN ('pg_catalog', 'information_schema') AND table_type = 'BASE TABLE';", false);
                break;
            case 'SQLITE':
            case 'PDO_SQLITE':
                $tbs = self::query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
                break;
        endswitch;

        return $tbs;
    }

    public static function fetch_fields($database, $table)
    {
        switch (self::getAdapter()):
            case 'MYSQL':
            case 'PDO_MYSQL':
                $fields = self::query("SHOW COLUMNS FROM " . $database . "." . $table . ";", false);
                $columns = array('Field', 'Type', 'Key', 'Null');
                break;
            case 'PGSQL':
            case 'PDO_PGSQL':
                $fields = self::query("SELECT DISTINCT cs.column_name, cs.data_type, cs.ordinal_position, cs.is_nullable, kc.ordinal_position as pkey FROM information_schema.columns cs left join information_schema.key_column_usage kc on cs.column_name = kc.column_name WHERE cs.table_name = '" . $table . "' ORDER BY ordinal_position;", false);
                $columns = array('column_name', 'data_type', 'pkey', 'is_nullable');
                break;
            case 'SQLITE':
            case 'PDO_SQLITE':
                $fields = self::query("PRAGMA table_info('" . $table . "');", false);
                $columns = array('name', 'type', 'pk', 'notnull');
                break;
        endswitch;

        foreach ($fields as $field):
            $_name = $field[$columns[0]];
            $_type = $field[$columns[1]];
            $_pkey = $field[$columns[2]];
            $_null = $field[$columns[3]];

            if (preg_match('~^((int)(\([0-9]{1,2}\))?([\s].*)?|integer)$~i', $_type))
                $_tpzf = 'int';
            elseif (preg_match('~(date|datetime)~i', $_type))
                $_tpzf = 'date';
            elseif (preg_match('~(yes|0)~i', $_null))
                $_tpzf = 'null';
            else
                $_tpzf = 'string';

            $_fields[] = array(
                '_name' => $_name,
                '_type' => $_type,
                '_tpzf' => $_tpzf,
                '_pkey' => $_pkey,
                '_null' => $_null
            );
        endforeach;

        return $_fields;
    }

    public static function query($query, $both = true)
    {
        $query = self::$dbh->prepare($query);
        $query->execute();
        $rows = ($both) ? $query->fetchAll() : $query->fetchAll(PDO::FETCH_ASSOC);

        return $rows;
    }

    public static function generate($database, $tables)
    {
        // Generate model layers
        echo '<fildset><legend>Generate models</legend>';
        if (!$tables)
            exit('<div class="alert"><h4>No results</h4> <a href="javascript:history.back();" />Click here and try again</a></div>');
        foreach ($tables as $table)
            self::gen_models($database, $table);
        echo '</fildset>';
    }

    public static function gen_models($database, $table)
    {
        // Checks if the following directories exist
        // DIR: \application
        self::gen_dir(DIR_APP);
        // DIR: \application\models
        self::gen_dir(DIR_MODELS);
        // DIR: \application\models\DbTable
        self::gen_dir(DIR_DBTABLE);

        $fields = self::fetch_fields($database, $table);

        $num_fields = count($fields);
        if (!$num_fields)
            exit('<div class="alert alert-error"><h4>No information available!</h4> <a href="javascript:location.reload(true);" />Click here and try again</a></div>');

        $_list = new ArrayIterator;
        $_listVars = $_listVOs = $primary_keys = $multiple_keys = null;

        $process[] = "<strong>Table Information</strong>: {$table}<br />";
        foreach ($fields as $k => $val):
            $_pk = $_fk = $_nn = null;
            $name = $val['_name'];
            $type = mb_strtolower($val['_type']);

            if (preg_match('~(pri|1)~i', $val['_pkey'])):
                $primary_keys[] = $name;
                $_pk = "<strong style='color:green;'>[PK]</strong>"; // Primary key
            endif;

            if (preg_match('~(mul)~i', $val['_pkey'])):
                $multiple_keys[] = $name;
                $_fk = "<strong style='color:blue;'>[FK]</strong>"; // Foreign key
            endif;

            if (preg_match('~(no|1)~i', $val['_null'])):
                $_nn = "<strong style='color:red;'>[NN]</strong>"; // Not-null
            endif;

            $process[] = ('- <strong>' . $name . '</strong>:<i>' . $type . '</i> ' . $_pk . $_fk . $_nn . '<br />');

            $_listVars.= self::gen_var($val);
            $_listVOs.= self::gen_vo($val);
        endforeach;

        if (sizeof($primary_keys) == 0):
            $primary_keys = "null";
        elseif (sizeof($primary_keys) == 1):
            $primary_keys = "'" . implode($primary_keys) . "'";
        else:
            $_listPKs = null;
            if (sizeof($primary_keys))
                foreach ($primary_keys as $var)
                    $_listPKs.= ", '$var'";
            $_listPKs = preg_replace('~, ~', '', $_listPKs, 1);

            $primary_keys = "array({$_listPKs})";
        endif;

        $tableNameUcwords = implode(array_map('ucwords', preg_split('~_~', $table)));

        //***************************************************
        //  CREATE MODEL
        //***************************************************
        $content_model = <<<MODEL
<?php

// application/models/{$tableNameUcwords}.php

class Application_Model_{$tableNameUcwords} extends App_Model_Abstract
{
{$_listVars}{$_listVOs}
}
MODEL;

        //***************************************************
        //  CREATE MAPPER
        //***************************************************
        $content_mapper = <<<MAPPER
<?php

// application/models/{$tableNameUcwords}Mapper.php

class Application_Model_{$tableNameUcwords}Mapper extends App_Model_Mapper_Abstract
{

    public function __construct()
    {
        parent::setModel("{$tableNameUcwords}");
    }

}
MAPPER;

        //***************************************************
        //  CREATE DBTABLE
        //***************************************************
        $content_dbtable = <<<DBTABLE
<?php

// application/models/DbTable/{$tableNameUcwords}.php

class Application_Model_DbTable_{$tableNameUcwords} extends Zend_Db_Table_Abstract
{

    /**
     * Table Name
     * @var string
     */
    protected \$_name = '{$table}';

}
DBTABLE;

        // Displays tables, data types and models created
        echo '<pre>';
        foreach ($process as $ps)
            echo $ps;
        echo '<br />';

        // FILE: \models\File.php
        self::gen_file(DIR_MODELS, $tableNameUcwords, $content_model);
        // FILE: \models\FileMapper.php
        self::gen_file(DIR_MODELS, $tableNameUcwords . 'Mapper', $content_mapper);
        // FILE: \models\DbTable\File.php
        self::gen_file(DIR_DBTABLE, $tableNameUcwords, $content_dbtable);
        echo '</pre>';
    }

    private static function gen_var($var)
    {
        $name = strtolower($var['_name']);
        return "    protected \$_{$name};\n";
    }

    private static function gen_vo($var)
    {
        $name = preg_split('~_~', $var['_name']);
        $nameFirst = implode(array_map('ucwords', $name));
        $nameLower = strtolower($var['_name']);

        $type = $var['_tpzf'];
        $types = $type == 'date' ? '$value;' : ($type == 'null' ? '(NULL !== $value) ? (string) $value : NULL;' : sprintf('(%s) $value;', $type));

        $setMethod = <<<SETTER

    public function set{$nameFirst}(\$value)
    {
        \$this->_{$nameLower} = {$types}
        return \$this;
    }

SETTER;

        $getMethod = <<<GETTER

    public function get{$nameFirst}()
    {
        return \$this->_{$nameLower};
    }

GETTER;

        return $setMethod . $getMethod;
    }

    private static function gen_dir($dir = null)
    {
        if (is_null($dir))
            exit("You have not set a directory name.");

        if (!file_exists($dir))
            if (!@mkdir($dir, 0777))
                exit("Unable to create the directory:<br />{$dir}");
            else
                echo "Directory created:<br />{$dir}";
    }

    private static function gen_file($path = null, $file = null, $content = null)
    {
        if (is_null($path) || is_null($file))
            exit("You have not set a filename or directory.");

        $file = $file . ".php";
        if (self::$backup && file_exists($path . $file)):
            $new_file = $file . ".bkp-" . date("YmdHis", time());
            rename($path . $file, $path . $new_file);
            $backup = " => <b>{$new_file}</b> [backup]";
        endif;

        $fopen = fopen($path . $file, 'wb');
        if (!$fopen):
            exit("Could not open the file for writing.<br />");
        else:
            fwrite($fopen, $content);
            echo ("File generated: {$path}<b>{$file}</b>{$backup}<br />");
        endif;

        fclose($fopen);
    }

}