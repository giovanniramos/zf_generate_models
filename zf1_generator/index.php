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

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Zend Framework - Models Generator</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<link href="assets/css/bootstrap.min.css" media="screen" rel="stylesheet" type="text/css" />
<link href="assets/css/bootstrap-responsive.min.css" media="screen" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="assets/css/jquery-ui.css" />
<script src="assets/js/jquery-1.8.0.min.js"></script>
<script src="assets/js/jquery-ui.min.js"></script>
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
<div class="navbar-inner"><a class="brand" href="">ZF1 - MODELS GENERATOR</a></div>
</div>
<div class="container" style="margin-top: 30px;">
<?php
// Defining the paths of application
define('ROOT', '../../');
define('DIR_APP', ROOT . 'application/');
define('DIR_MODELS', DIR_APP . 'models/');
define('DIR_DBTABLE', DIR_MODELS . 'DbTable/');

// Define path to application directory
define('APPLICATION_PATH', realpath(dirname(__FILE__) . '../../../application'));

// Adding the library to the include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(ROOT . 'library'),
    get_include_path(),
)));
require_once('Zend/Config/Ini.php');

// Setting up access to the database in the "development" environment
$config = new Zend_Config_Ini(DIR_APP . 'configs/application.ini', 'development');

// Get resources database
$adapter = $config->resources->db->adapter;
$params = $config->resources->db->params->toArray();

$db_hostname = $params['host'];
$db_username = $params['username'];
$db_password = $params['password'];
$db_database = $params['dbname'];

// Generate class
require_once('Generator.class.php');

// Connecting to the database
Generator::connect($adapter, $db_hostname, $db_username, $db_password, $db_database);


// Stores the current step
$step = isset($_POST['step']) ? $_POST['step'] : 1;


//***************************************************
// STEP 1
//***************************************************
if ($step == 1):
    // Selecting all databases
    $databases = Generator::getDatabases();
    ?>
    
    <form class="form-horizontal" method="post">
        <input type="hidden" name="step" value="2" />
        <fildset>
            <legend>Choose a database to continue</legend>
            
            <?php
            if (!$databases): 
                ?>
                <div class="alert">
                    <strong>Notice!</strong> No database found.
                </div>
                <?php
            endif;
            ?>
            
            <div class="control-group">
                <label class="control-label" for="inputServername"><strong>Server name</strong></label>
                <div class="controls">
                    <input type="text" id="inputServername" placeholder="Server undefined" value="<?php echo $db_hostname; ?>" readonly="readonly" />
                </div>
            </div>
            
            <div class="control-group">
                <label class="control-label"><strong>Databases available</strong></label>
                <div class="controls">
                    <div class="scroll-pane ui-widget ui-widget-header ui-corner-all" data-toggle="buttons-checkbox">
                        <div class="scroll-content">
                            <div class="btn-group" data-toggle="buttons-radio">
                                <?php
                                if ($databases):
                                    foreach ($databases as $database):
                                        echo '<button type="button" class="btn" name="database" value="' . $database . '">' . $database . '</button>';
                                    endforeach;
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


//***************************************************
// STEP 2
//***************************************************
if ($step == 2):
    // Selecting all tables
    $database = isset($_POST['database']) ? $_POST['database'] : null;
    $tables = Generator::getTablesFromDatabase($database);
    ?>

    <form class="form-horizontal" method="post">
        <input type="hidden" name="step" value="3" />
        <input type="hidden" name="database" value="<?php echo $database; ?>" />
        <fildset>
            <legend>Choose the tables to continue</legend>
            
            <?php
            if (!$tables): 
                ?>
                <div class="alert">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <strong>Notice!</strong> Go back and choose a database.
                </div>
                <?php
            endif;
            ?>
            
            <div class="control-group">
                <label class="control-label" for="inputDatabase"><strong>Database</strong></label>
                <div class="controls">
                    <input type="text" id="inputDatabase" placeholder="None" value="<?php echo $database; ?>" readonly="readonly" />
                </div>
            </div>
            
            <div class="control-group">
                <label class="control-label"><strong>Tables available</strong></label>
                <div class="controls">
                    <div class="scroll-pane ui-widget ui-widget-header ui-corner-all" data-toggle="buttons-checkbox">
                        <div class="scroll-content">
                            <div class="btn-group">
                            <?php
                            if ($tables):
                                foreach ($tables as $table):
                                    $table_name = implode(array_map('ucwords', preg_split('~_~', $table)));
                                    $has_file = file_exists(DIR_MODELS . $table_name . '.php') ? 'red' : 'black';

                                    echo '<button type="button" class="btn" name="tables[]" value="' . $table . '" style="color: ' . $has_file . '">' . $table . '</button>';
                                endforeach;
                            else:
                                echo '&nbsp;';
                            endif;
                            ?>
                            </div>
                        </div>
                        <div class="scroll-bar-wrap ui-widget-content ui-corner-bottom">
                            <div class="scroll-bar"></div>
                        </div>
                    </div>
                    
                    <small class="notice">Tables in red have been previously created</small>
                    
                    <label class="checkbox inline">
                        <input type="checkbox" id="inputBackup" name="backup" value="1" checked /> Enable automatic backup of models
                    </label>
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

// Enable automatic backup of models
$backup = isset($_POST['backup']) ? (boolean) $_POST['backup'] : null;
$database = isset($_POST['database']) ? $_POST['database'] : null;
$tables = isset($_POST['tables']) ? (array) $_POST['tables'] : null;
Generator::setBackup($backup);
Generator::generate($database, $tables);

echo '<br /><input type="button" value="FINISH" class="btn btn-primary" onclick="javascript:history.go(-2);" /><br /><br /><br /><br />';

?>
</div>
</body>
</html>