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
    private static $adapter = 'mysql';
    private static $link;
    private static $databases;
    private static $tables;
    private static $backup = true;

    public static function adapter($adapter)
    {
        self::$adapter = $adapter;
    }

    public static function connect($hostname, $username, $password)
    {
        switch (self::$adapter) {
            case 'mysql':
            case 'PDO_MYSQL':
                self::$link = @mysql_connect($hostname, $username, $password);
                break;
            default: exit('No connection!');
        }

        self::setDatabases();
    }

    public static function setBackup($backup)
    {
        self::$backup = $backup;
    }

    public static function setDatabases($database)
    {
        if (!$database):
            switch (self::$adapter):
                case 'mysql':
                case 'PDO_MYSQL':
                    $dbs = self::list_dbs();

                    $database = null;
                    while ($row = self::fetch_row($dbs)):
                        if (in_array($row[0], array('information_schema', 'performance_schema', 'phpmyadmin', 'mysql', 'webauth')))
                            continue;
                        else
                            $database[] = $row[0];
                    endwhile;
                    self::$databases = $database;
                    break;
            endswitch;
        else:
            switch (self::$adapter):
                case 'mysql':
                case 'PDO_MYSQL':
                    $dbs = self::query('SHOW TABLES FROM ' . $database);

                    $tables = null;
                    while ($row = self::fetch_row($dbs)):
                        $tables[] = $row[0];
                    endwhile;
                    self::$tables = $tables;
                    break;
            endswitch;
        endif;
    }

    public static function getDatabases()
    {
        return self::$databases;
    }

    public static function getTablesFromDatabase($database)
    {
        Generator::setDatabases($database);

        return self::$tables;
    }

    public static function query($query)
    {
        switch (self::$adapter):
            case 'mysql':
            case 'PDO_MYSQL':
                $result = mysql_query($query, self::$link);
                break;
        endswitch;

        return $result;
    }

    public static function select_db($database)
    {
        switch (self::$adapter):
            case 'mysql':
            case 'PDO_MYSQL':
                mysql_select_db($database, self::$link) or mysql_error();
                break;
        endswitch;
    }

    public static function list_dbs()
    {
        switch (self::$adapter):
            case 'mysql':
            case 'PDO_MYSQL':
                $dbs = mysql_list_dbs(self::$link);
                break;
        endswitch;

        return $dbs;
    }

    public static function num_fields($result)
    {
        switch (self::$adapter):
            case 'mysql':
            case 'PDO_MYSQL':
                $num_fields = mysql_num_fields($result);
                break;
        endswitch;

        return $num_fields;
    }

    public static function fetch_field($result, $field_offset = 0)
    {
        switch (self::$adapter):
            case 'mysql':
            case 'PDO_MYSQL':
                $field = mysql_fetch_field($result, $field_offset);
                break;
        endswitch;

        return $field;
    }

    public static function fetch_row($result)
    {
        switch (self::$adapter):
            case 'mysql':
            case 'PDO_MYSQL':
                $row = mysql_fetch_row($result);
                break;
        endswitch;

        return $row;
    }

    public static function generate($database, $tables)
    {
        self::select_db($database);

        // Generate model layers
        echo '<fildset><legend>Generate models</legend>';
        if (!$tables):
            exit('<div class="alert"><h4>No results</h4> <a href="javascript:history.back();" />Click here and try again</a></div>');
        endif;
        foreach ($tables as $table)
            self::gen_models($table);
        echo '</fildset>';
    }

    public static function gen_models($table = null)
    {
        // Checks if the following directories exist
        // DIR: \application
        self::gen_dir(DIR_APP);
        // DIR: \application\models
        self::gen_dir(DIR_MODELS);
        // DIR: \application\models\DbTable
        self::gen_dir(DIR_DBTABLE);

        $result = self::query('SELECT * FROM ' . $table);
        if (!$result):
            exit('<div class="alert alert-error"><h4>Query failed!</h4> <a href="javascript:location.reload(true);" />Click here and try again</a></div>');
        endif;

        $num_fields = self::num_fields($result);

        $list = new ArrayIterator;
        $process = null;
        for ($i = 0; $i < $num_fields; $i++):
            $meta = self::fetch_field($result, $i);
            if (!$meta)
                exit("No information available.<br />");
            if ($i == 0)
                $process[] = "<strong>Table Information</strong>: {$table}<br />";

            $process[] = "- <strong>{$meta->name}</strong>:<i>{$meta->type}</i> ";
            if ($meta->primary_key)
                $table_pk[] = $meta->name;
            if ($meta->primary_key)
                $process[] = "<strong style='color:green;'>[PK]</strong>"; // Primary key
            if ($meta->multiple_key)
                $process[] = "<strong style='color:blue;'>[FK]</strong>"; // Foreign key
            if ($meta->not_null)
                $process[] = "<strong style='color:red;'>[NN]</strong>"; // Not-null

            $list[$i]['name'] = $meta->name;
            $list[$i]['type'] = $meta->type == 'date' || $meta->type == 'datetime' ? 'date' : ($meta->numeric ? ($meta->not_null ? 'int' : 'null') : 'string');

            $process[] = ($i == $num_fields - 1) ? "<br /><br />" : "<br />";
        endfor;

        $listVars = null;
        $listVOs = null;
        for ($j = 0; $j < $num_fields; $j++):
            $listVars.= self::gen_var($list[$j]);
            $listVOs.= self::gen_vo($list[$j]);
        endfor;

        if (sizeof($table_pk) == 0):
            $table_pk = "null";
        elseif (sizeof($table_pk) == 1):
            $table_pk = "'" . implode($table_pk) . "'";
        else:
            $listPKs = null;

            if (sizeof($table_pk))
                foreach ($table_pk as $var)
                    $listPKs.= ", '$var'";

            $listPKs = preg_replace('~, ~', '', $listPKs, 1);

            $table_pk = "array({$listPKs})";
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
{$listVars}{$listVOs}
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
        foreach ($process as $ps):
            echo $ps;
        endforeach;

        // \models\File.php
        self::gen_file(DIR_MODELS, $tableNameUcwords, $content_model);
        // \models\FileMapper.php
        self::gen_file(DIR_MODELS, $tableNameUcwords . 'Mapper', $content_mapper);
        // \models\DbTable\File.php
        self::gen_file(DIR_DBTABLE, $tableNameUcwords, $content_dbtable);
        echo '</pre>';
    }

    private function gen_var($var)
    {
        $name = strtolower($var['name']);
        return "    protected \$_{$name};\n";
    }

    private function gen_vo($var)
    {
        $name = preg_split('~_~', $var['name']);
        $nameFirst = implode(array_map('ucwords', $name));
        $nameLower = strtolower($var['name']);
        $type = $var['type'];
        $types = ($type == 'date') ? '$value;' : (($type == 'null') ? '(NULL !== $value) ? (int) $value : NULL;' : sprintf('(%s) $value;', $type));

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

    private function gen_dir($dir = null)
    {
        if (is_null($dir))
            exit("You have not set a directory name.");

        if (!file_exists($dir))
            if (!@mkdir($dir, 0777))
                exit("Unable to create the directory:<br />{$dir}");
            else
                echo "Directory created:<br />{$dir}";
    }

    private function gen_file($path = null, $file = null, $content = null)
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