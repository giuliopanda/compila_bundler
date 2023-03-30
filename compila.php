<?php
/** 
 * 
 * type "build":{"files":", "dest": "dest", "action": "fn"}
 * 
 * complia is a free and open source form bundler written in php.
 * Thanks to complia you can create a single html file, 
 * css, js from multiple php files (and more). 
 * You don't have to learn any new script language, 
 * no complicated configuration, you'll just prepare
 * a simple json to define the output structure.
 * 
 * eg: 
 * {
 *     "build": [
 *         {"files": "assets/*.css", "dest": "compila/etoile.css", "fn": "fn_build"},
 *         { "files": "assets/*.js", "dest": "compila/etoile.js" , "fn": "fn_build"},
 *         {"files": "home.html", "dest": "compila/index.html", "fn": "fn_build"}
 *     ], 
 *     "copy": [ {"files": "copy_origin/*", "dest": "assets/", "fn": "fn_copy"}]
 * }
 * 
 * * How to use.
 * Download the project
 * and save it to your project folder.
 * by writing the command from the terminal
 * * php complia.php -copy element_name
 * an element will be copied from copy_origin to assets by replacing {var} with element_name
 * writing the command from terminal
 * # php complia.php -build
 * will be compliad the files inside assets and home.html inside the complia folder
 * 
 * * configuring the json
 * 
 * In the file you can write either the name of the file to be compliad or use 
 * the Special characters to indicate a list of files to complia.
 *
 * * - Matches zero or more characters.
 * ?  - Matches exactly one character (any character).
 * [...] - Matches one character from a group of characters. 
 * If the first character is !, matches any character not in the group.
 * \ - Escapes the following character.
 *
 * * * watch
 * you can pass the -w or -watch parameter to monitor the files and recomplia them if modified.
 * 
 * * Include multiple files inside the original file.
 * Inside the files to be compliad you can use.
 * the "e()" function that allows you to include a file 
 * inside the file being compliad. 
 * In this way, not only is any 
 * php code, but if you use watch, the file is 
 * monitored and the compilation re-executed if modified.
 *
 * 
 * -copy param
 * Copy the files indicated in the json to the target folder by replacing {var} with param.
 * e.g.: php complia.php -copy folder_name
 * the json configuration
 * "copy_path" : {"origin": "copy_origin/*", "dest": "assets/"}
 * * ex:
 * php compare.php -copy folder_name.
 * 
 * Create new functions.
 * you can create new functions like copy or build.
 * to do this you have to create a php file called complia_extend.php to extend the new function
 * example minify
 * in the json you can add.
 * "minify" : {"files": "complia/*", "dest": "complia/", "fn": "my_minify_function"},
 * you can see inside complia copy function for how it should be structured
 */

/**
 * @var array $compila_errors contiene gli errori che si verificano durante la compilazione
 */
$compila_errors = [];
/**
 * @var array $list_of_files contiene la lista dei file da compilare
 */
$list_of_files = [];
/**
 * @var bool $global_watch
 */
$global_watch = false;

/**
 * --------------
 * - CONTROLLER -
 * --------------
 */ 

 if (is_file("compila_extend.php")) {
    require "compila_extend.php";
 }
array_shift($argv); // params passed
// help
if (count($argv) == 1 && ($argv[0] == "-h" || $argv[0] == "-help")) {
    help_and_die();
}

foreach ($argv as $key => $value) {
    if ($value == "-w" || $value == "-watch") {
        unset($argv[$key]);
        $argv = array_values($argv);
        $global_watch = true;
    }
}

compila();

if ( $global_watch ) {
    watch_files();
} else {
die;
}



/**
 * -------------
 * - FUNCTIONS -
 * -------------
 */

function compila() {
    global $argv, $json, $global_watch, $list_of_files;
    echo "\r\033[K Buinding";
    if (is_file("compila.json")) {
        $json = json_decode(file_get_contents("compila.json"), true);
        // verifica se json_decode ha dato errore
        if (json_last_error() != JSON_ERROR_NONE) {
            echo "Errore nel file compila.json ".json_last_error_msg().PHP_EOL;
            die();
        }
    } 
    if (count($argv) >= 1 && array_key_exists($argv[0], $json)) {
        $argv2 = array_slice($argv, 1);
        foreach ($json[$argv[0]] as $row) {
            echo ".";
            if (is_string ($row)) {
                echo "Error: param is not an array ". PHP_EOL;
                die;
            }
            $files = glob($row['files']);
            $dest = $row['dest'];
            // aggiungo il file alla lista dei file da controllare
            if ($global_watch) {
                foreach ($files as $file) {
                    $list_of_files[] = $file;
                }
            }
            // chiamo la funzione passata come parametro nel json con chiave fn
            $fn = $row['fn'];
            // se ci sono altri parametri li passo alla funzione
            if (count($argv2) > 0) {
                $fn($files, $dest, $argv2);
            } else {
                $fn($files, $dest);
            }
        }
        echo "\r\033[K Done";
        if (!$global_watch) {
            echo PHP_EOL;
        }
    } else {
        help_and_die();
    }
}

function watch_files() {
    global $list_of_files;
    $last_modified_times = array();

    // Inizializza l'array dei timestamp delle ultime modifiche
    foreach ($list_of_files as $file) {
        $last_modified_times[$file] = filemtime($file);
    }
    $tot_point = 0;
    while (true) {
        clearstatcache();
        // Verifica se i file sono stati modificati
        foreach ($list_of_files as $file) {
            $current_modified_time = filemtime($file);
            if ($current_modified_time != $last_modified_times[$file]) {
                compila();
                $last_modified_times[$file] = $current_modified_time;
                break;
            }
        }
        echo ".";
        $tot_point++;
        if ($tot_point == 4) {
            echo "\r\033[K";
            $tot_point = 0;
        }
        sleep(3); 
    }
}


/**
 * Stampa il primo set di commenti se non ci sono argomenti passati o se è -h o -help
 */
function help_and_die() {
    $tokens =  token_get_all(file_get_contents(__FILE__));
    echo PHP_EOL." ------------ HELP ------------".PHP_EOL;
    echo " ------------------------------".PHP_EOL;
    foreach ($tokens as $token) {
        if (is_array($token)) {
            if ( token_name($token[0]) == 'T_DOC_COMMENT') {
                $token[1] = str_replace('\*','§-§',$token[1]);
                $token[1] = str_replace(['/*','*/','*'], '', $token[1]);
                echo str_replace('§-§','*', $token[1]). PHP_EOL;
                break;
            }
        }
    }
    echo " ------------------------------".PHP_EOL;
    die;
}

/**
 * Fa il require dei file php/js/html/css 
 * Aggiunge il file alla lista dei file da compilare così vengono monitorati se si usa il watch
 */
function e($string) {
    global $compila_errors, $list_of_files, $global_watch;
    $info = pathinfo($string);
    ob_start();
    if (is_file($string)) {
        if ($global_watch) {
            $list_of_files[] = $string;
        }
        require $string;
        echo PHP_EOL;
    } else {
        $compila_errors[] = "File not found: ".$string;
        if ($info['extension'] == "html" || $info['extension'] == "htm") {
            echo "<!-- File not found: ".$string." -->".PHP_EOL;
        } else if ($info['extension'] == "css") {
            echo "/* File not found: ".$string." */".PHP_EOL;
        } else {
            echo "/** File not found: ".$string." */".PHP_EOL;
        }
    }
    $ris = ob_get_clean();
    echo $ris;
}


/**
 * 
 * @param string $string
 * @return string
 */
function fn_copy($files, $dest, $args = []) {
    if (count($args) == 0) {
        echo "No arguments passed!". PHP_EOL;
        die;
    }
    $var = reset($args);
    foreach ($files as $file) {
        $info = pathinfo($file);
        if (is_file($file)) {
            $new_file_name = str_replace("{var}", $var, $info['basename']);
            $file_contennt = file_get_contents($file);
            $file_contennt = str_replace("{var}", $var, $file_contennt);
            file_put_contents($dest . "/" . $new_file_name, $file_contennt);
        }
    }
    
}


 /**
  * compila il files
  * @param array $file array di file da compilare
  */
  function fn_build($files, $file_dest) {
    global $compila_errors;
    $file_exec = 0;
    ob_start();
    foreach ($files as $file) {
        if (!is_file($file)) {
            $compila_errors[] = "File not found: ".$file;
        } else {
            $file_exec ++;
            require $file;
            echo PHP_EOL;
        }
    }
    $contents = ob_get_clean();
    if ($file_exec > 0) {
        if ($file_dest == '') {
            $info = pathinfo($file);
            if (isset($info['filename']) && isset($info['extension'])) {
                $file_dest = $info['filename']."_".time().".".$info['extension'];
            } else {
                $file_dest = $file."_".time();
            }
        }
        file_put_contents($file_dest, $contents); 
    } else {
        $compila_errors[] = "No file to complia ";
    }
    if (count($compila_errors) > 0) {
        foreach ($compila_errors as $error) {
            echo $error.PHP_EOL;
        }
    }
    $compila_errors = [];
 }

