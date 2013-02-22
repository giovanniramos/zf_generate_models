<?php
/************************************************************************************
 * 
 * ZF1 - MODELS GENERATOR (https://github.com/giovanniramos/zf_generate_models)
 * 
 * Copyright (c) 2013 Giovanni Ramos (https://github.com/giovanniramos)
 * 
 * Licensed under the MIT License
 * 
 ***********************************************************************************/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Zend Framework - Models Generator</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<link href="assets/css/bootstrap.min.css" media="screen" rel="stylesheet" type="text/css" />
<link href="assets/css/bootstrap-responsive.min.css" media="screen" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.1/themes/base/jquery-ui.css" />
<script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
<script src="http://code.jquery.com/ui/1.10.1/jquery-ui.min.js"></script>
<script type="text/javascript" src="assets/js/bootstrap.min.js"></script>
<script type="text/javascript" src="assets/js/jquery-scroll-pane.js"></script>
<script type="text/javascript">$().ready(function(){ $('form').submit(function() { $(this).find('.btn.active').each(function(){ $(this).closest('form').prepend($('<input type="hidden">').prop('name', this.name).val(this.value)); }); }); });</script>
<style type="text/css">
.btn-group { margin: 3px; }
.notice { display: block; color: red; clear: both; }
.scroll-pane { overflow: auto; width: 90%; float: left; }
.scroll-content { width: auto; float: left; }
.scroll-content-item { width: 100px; height: 100px; float: left; margin: 10px; font-size: 3em; line-height: 96px; text-align: center; }
.scroll-bar-wrap { clear: left; padding: 0 4px 0 2px; margin: 0 -1px -1px -1px; }
.scroll-bar-wrap .ui-slider { background: none; border:0; height: 1.55em; margin: 0 auto;  }
.scroll-bar-wrap .ui-handle-helper-parent { position: relative; width: 100%; height: 100%; margin: 0 auto; }
.scroll-bar-wrap .ui-slider-handle { top: .2em; height: 1em; cursor: pointer; }
.scroll-bar-wrap .ui-slider-handle .ui-icon { position: relative; top: 50%; margin: -8px auto 0; }
</style>
</head>
<body>
<div class="navbar navbar-static-top">
<div class="navbar-inner"><a class="brand" href="index">ZF1 - MODELS GENERATOR</a></div>
</div>
<div class="container" style="margin-top: 30px;">
<?php
// Enable automatic backup of models
$generate_backup = false;

// Defining the paths of application
define('ROOT', '../../');
define('DIR_APP', ROOT . 'application/');
define('DIR_MODELS', DIR_APP . 'models/');
define('DIR_DBTABLE', DIR_MODELS . 'DbTable/');

// Adding the library to the include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(ROOT . 'library'),
    get_include_path(),
)));
require_once('Zend/Config/Ini.php');

// Setting up access to the database
$config = new Zend_Config_Ini(DIR_APP . 'configs/application.ini', 'development');
$params = $config->resources->db->params->toArray();

$db_name = $params['dbname'];
$db_hostname = $params['host'];
$db_username = $params['username'];
$db_password = $params['password'];

// Connecting to the database
$db_link = @mysql_connect($db_hostname, $db_username, $db_password);


// Stores the current step
$step = isset($_POST['step']) ? $_POST['step'] : 1;


//***************************************************
// STEP 1
//***************************************************
if ($step == 1):
    // Selecting all databases
    $list_databases = mysql_list_dbs($db_link);
    ?>
    
    <form class="form-horizontal" method="post">
        <input type="hidden" name="step" value="2" />
        <input type="hidden" name="adapter" value="<?php echo $db_name; ?>" />
        <fildset>
            <legend>Choose a database to continue</legend>
            
            <?php
            if (!$list_databases): 
                ?>
                <div class="alert">
                    <strong>Notice!</strong> No database found.
                </div>
                <?php
            endif;
            ?>
            
            <div class="control-group">
                <label class="control-label" for="inputServername">Server name</label>
                <div class="controls">
                    <input type="text" id="inputServername" placeholder="Server undefined" value="<?php echo $db_hostname; ?>" readonly="readonly" />
                </div>
            </div>
            
            <div class="control-group">
                <label class="control-label">Databases available</label>
                <div class="controls">
                    <div class="scroll-pane ui-widget ui-widget-header ui-corner-all" data-toggle="buttons-checkbox">
                        <div class="scroll-content">
                            <div class="btn-group" data-toggle="buttons-radio">
                                <?php
                                if ($list_databases):
                                    while ($_ = mysql_fetch_array($list_databases)):
                                        $database = $_[Database];
                                        if (in_array($_[0], array('information_schema', 'mysql', 'performance_schema', 'phpmyadmin', 'webauth', 'test')))
                                            continue;
                                        else
                                            echo '<button type="button" class="btn" name="database" value="' . $database . '">' . $database . '</button>';
                                    endwhile;
                                endif;
                                ?>
                            </div>
                        </div>
                        <div class="scroll-bar-wrap ui-widget-content ui-corner-bottom">
                            <div class="scroll-bar"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <br />
            <input type="submit" value="NEXT" class="btn btn-primary" />
        </fildset>
    </form>

    <?php
    exit();
endif;


$db_adapter = (string) $_POST['adapter'];
$db_database = (string) $_POST['database'];

$__zend['adapter'] = strtolower(preg_replace('~[\/]{1,}$~', '', $db_adapter));
$__zend['adapterUpper'] = ucfirst($__zend['adapter']);
$__zend['schema'] = strtolower($db_database);
$__zend['base'] = preg_replace('~_~', '', $__zend['schema']);
$__zend['baseUpper'] = implode(array_map('ucwords', preg_split('~_~', $__zend['schema'])));


//***************************************************
// STEP 2
//***************************************************
if ($step == 2):
    // Selecting all tables
    $list_tables = mysql_query("SHOW TABLES FROM {$db_database}");
    ?>

    <form class="form-horizontal" method="post">
        <input type="hidden" name="step" value="3" />
        <input type="hidden" name="adapter" value="<?php echo $db_adapter; ?>" />
        <input type="hidden" name="database" value="<?php echo $db_database; ?>" />
        <fildset>
            <legend>Choose the tables to continue</legend>
            
            <?php
            if (!$list_tables): 
                ?>
                <div class="alert">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <strong>Notice!</strong> Go back and choose a database.
                </div>
                <?php
            endif;
            ?>
            
            <div class="control-group">
                <label class="control-label" for="inputDatabase">Database</label>
                <div class="controls">
                    <input type="text" id="inputDatabase" placeholder="None" value="<?php echo $db_database; ?>" readonly="readonly" />
                </div>
            </div>
            
            <div class="control-group">
                <label class="control-label">Tables available</label>
                <div class="controls">
                    <div class="scroll-pane ui-widget ui-widget-header ui-corner-all" data-toggle="buttons-checkbox">
                        <div class="scroll-content">
                            <div class="btn-group">
                            <?php
                            if ($list_tables):
                                while ($_ = @mysql_fetch_row($list_tables)):
                                    $table = $_[0];
                                    $table_name = implode(array_map('ucwords', preg_split('~_~', $table)));
                                    $has_file = file_exists(DIR_MODELS . $table_name . '.php') ? 'red' : 'black';

                                    echo '<button type="button" class="btn" name="tables[]" value="' . $table . '" style="color: ' . $has_file . '">' . $table . '</button>';
                                endwhile;
                            endif;
                            ?>
                            </div>
                        </div>
                        <div class="scroll-bar-wrap ui-widget-content ui-corner-bottom">
                            <div class="scroll-bar"></div>
                        </div>
                    </div>
                    
                    <small class="notice">Tables in red have been previously created</small>
                </div>
            </div>
            
            <br />
            <input type="button" value="BACK" class="btn btn-primary" onclick="javascript:history.back()" />
            <input type="submit" value="NEXT" class="btn btn-primary" />
        </fildset>
    </form>

    <?php
    exit();
endif;


//***************************************************
// STEP 3
//***************************************************

// Selecting the database
$db_selected = mysql_select_db($db_database, $db_link);
if (!$db_selected)
    echo(mysql_error());

// Generate model layers
$tables = (array) $_POST['tables'];
echo '<fildset><legend>Generate models</legend>';
if (!$tables):
    exit('<div class="alert"><h4>No results</h4> <a href="javascript:history.back();" />Click here and try again</a></div>');
endif;
foreach ($tables as $table)
    genModels($table);
echo '</fildset>';

function genModels($table = null)
{
    global $__zend, $content_model_controll, $content_model_mapper, $content_model_dbtable;
    
    // Checks if the following directories exist
    gen_dir(DIR_APP);     # DIR: \application
    gen_dir(DIR_MODELS);  # DIR: \application\models
    gen_dir(DIR_DBTABLE); # DIR: \application\models\DbTable
    
    $__zend_tableName = $table;
    $__zend_tableNameUpper = implode(array_map('ucwords', preg_split('~_~', $__zend_tableName)));
    
    // Selecting the table
    $db_result = mysql_query("SELECT * FROM " . $__zend_tableName);
    if (!$db_result):
        exit('<div class="alert alert-error"><h4>Query failed!</h4> <a href="javascript:location.reload(true);" />Click here and try again</a></div>');
    endif;
    
    // Total records found
    $db_num_fields = mysql_num_fields($db_result);
    
    $list = new ArrayIterator;
    $process = null;
    for ($i = 0; $i < $db_num_fields; $i++):
        $meta = mysql_fetch_field($db_result, $i);
        if (!$meta)
            exit("No information available.<br />");
        if ($i == 0)
            $process[] = "<strong>Table Information</strong>: {$__zend_tableName}<br />";
        
        $process[] = "- <strong>{$meta->name}</strong>:<i>{$meta->type}</i> ";
        if ($meta->primary_key)
            $__zend_table_pk[] = $meta->name;
        if ($meta->primary_key)
            $process[] = "<strong style='color:green;'>[PK]</strong>"; # Primary key
        if ($meta->multiple_key)
            $process[] = "<strong style='color:blue;'>[FK]</strong>";  # Foreign key
        if ($meta->not_null)
            $process[] = "<strong style='color:red;'>[NN]</strong>";   # Not-null
        
        $list[$i]['name'] = $meta->name;
        $list[$i]['type'] = $meta->type == 'date' || $meta->type == 'datetime' ? 'date' : ($meta->numeric ? ($meta->not_null ? 'int' : 'null') : 'string');

        $process[] = ($i == $db_num_fields - 1) ? "<br /><br />" : "<br />";
    endfor;
    
    $listVars = null;
    $listVOs = null;
    $listToArray = null;
    for ($j = 0; $j < $db_num_fields; $j++):
        $listVars.= gen_var($list[$j]);
        $listVOs.= gen_vo($list[$j]);
        $listToArray.= gen_toArray($list[$j], ($j == $db_num_fields - 1));
    endfor;

    if (sizeof($__zend_table_pk) == 0):
        $__zend_table_pk = "null";
    elseif (sizeof($__zend_table_pk) == 1):
        $__zend_table_pk = "'" . implode($__zend_table_pk) . "'";
    else:
        $listPKs = null;

        if (sizeof($__zend_table_pk))
            foreach ($__zend_table_pk as $var)
                $listPKs.= ", '$var'";

        $listPKs = preg_replace('~, ~', '', $listPKs, 1);

        $__zend_table_pk = "array({$listPKs})";
    endif;

    
    //***************************************************
    //  CREATE CONTROLL
    //***************************************************
    $content_model_controll = <<<PARTE1
<?php

// application/models/{$__zend_tableNameUpper}.php

class Application_Model_{$__zend_tableNameUpper} extends System_Db_Model_Abstract
{
    {$listVars}{$listVOs}
    public function toArray()
    {
        return array({$listToArray}
        );
    }

}
PARTE1;

    //***************************************************
    //  CREATE MAPPER
    //***************************************************
    $content_model_mapper = <<<PARTE2
<?php

// application/models/{$__zend_tableNameUpper}Mapper.php

class Application_Model_{$__zend_tableNameUpper}Mapper extends System_Db_Mapper_Abstract
{
    
}
PARTE2;

    //***************************************************
    //  CREATE DBTABLE
    //***************************************************
    $content_model_dbtable = <<<PARTE3
<?php

// application/models/DbTable/{$__zend_tableNameUpper}.php

class Application_Model_DbTable_{$__zend_tableNameUpper} extends Zend_Db_Table_Abstract
{
    /**
     * Name of database
     * @var string
     */
    protected \$_schema = '{$__zend['schema']}';

    /**
     * Table Name
     * @var string
     */
    protected \$_name = '{$__zend_tableName}';

}
PARTE3;
    
    // Displays tables, data types and models created
    echo '<pre>';
    foreach ($process as $ps) echo $ps;
    
    gen_file(DIR_MODELS, $__zend_tableNameUpper, $content_model_controll); # \models\File.php
    gen_file(DIR_MODELS, $__zend_tableNameUpper . 'Mapper', $content_model_mapper);   # \models\FileMapper.php
    gen_file(DIR_DBTABLE, $__zend_tableNameUpper, $content_model_dbtable); # \models\DbTable\File.php
    echo '</pre>';
}

echo '<br /><input type="button" value="FINISH" class="btn btn-primary" onclick="javascript:history.go(-2);" /><br /><br /><br /><br />';



//***************************************************
//  Useful functions
//***************************************************

function gen_vo($var)
{
    if ($var['name'] == 'id')
        return;

    $name = preg_split('~_~', $var['name']);
    $nameFirst = implode(array_map('ucwords', $name));
    $nameLower = strtolower($var['name']);
    $type = $var['type'];

    $set =
    ($type == 'date') ?
    "
    public function set{$nameFirst}(\$value)
    {
        \$this->_{$nameLower} = \$value;
        return \$this;
    }
    " :
    (($type == 'null') ?
    "
    public function set{$nameFirst}(\$value)
    {
        \$this->_{$nameLower} = (NULL !== \$value) ? (int) \$value : NULL;
        return \$this;
    }
    " :
    "
    public function set{$nameFirst}(\$value)
    {
        \$this->_{$nameLower} = ({$type}) \$value;
        return \$this;
    }
    ");

    $get =
    "
    public function get{$nameFirst}()
    {
        return \$this->_{$nameLower};
    }
    ";

    return $set . $get;
}

function gen_var($var)
{
    if ($var['name'] == 'id')
        return;

    $name = strtolower($var['name']);
    return "protected \$_{$name};\n\t";
}

function gen_toArray($var, $lastComma)
{
    $name = preg_split('~_~', $var['name']);
    $nameFirst = implode(array_map('ucwords', $name));
    $nameLower = strtolower($var['name']);

    $lastComma = $lastComma ? null : ",";
    return "\n\t\t\t'{$nameLower}' => \$this->get{$nameFirst}(){$lastComma}";
}

function gen_dir($dir = null)
{
    if (is_null($dir))
        exit("You have not set a directory name.");

    if (!file_exists($dir))
        if (!@mkdir($dir, 0777))
            exit("Unable to create the directory:<br />{$dir}");
        else
            echo "Directory created:<br />{$dir}";
}

function gen_file($path = null, $file = null, $content = null)
{
    global $generate_backup;

    if (is_null($path) || is_null($file))
        exit("You have not set a filename or directory.");

    $file = $file . ".php";
    if ($generate_backup && file_exists($path . $file)):
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
?>
</div>
</body>
<html>