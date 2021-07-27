<?php
/**
* s3_plugin Tool to Check Which Resources do not have an Original File in S3 Storage
* Note: The script does not care about previews, since these can be recreated.
*/

$start = time();
$start_text = nicedate(date("Y-m-d h:i:s"), true, false);

// Check if run on the command line, if not, exit.
global $lang, $s3_storage_bucket;
if('cli' != PHP_SAPI)
    {
    header('HTTP/1.1 401 Unauthorized');
    exit($lang['cli_error']);
    }

include __DIR__ . '../../../../include/db.php';
include_once dirname(__FILE__) . '/../../include/s3_storage_functions.php';

// Setup script logging to a text file named by the date and time the script is run.
$s3_temp_dir = s3_create_temp();
$out_file = $s3_temp_dir . 'FindDeletedFiles_' . str_replace(array('/', ':', '@'), '-', str_replace(' ', '', $start_text)) . '.txt';
$text_file = fopen($out_file, 'w+');

// Introductory script output text.
output("\nRESOURCESPACE ORIGINAL FILE SIMPLE STORAGE SERVICE (S3) STORAGE CHECK\n");
output('Start on ' . $start_text . "\n");
output('Text output saved to ' . $out_file . "\n");
output('S3 Bucket Name: ' . $s3_storage_bucket . "\n");

ob_end_clean();
restore_error_handler();

// Run the PHP garbage collector to free memory.
output('Running PHP Garbage Collector to Free Memory...');
$gc = gc_collect_cycles();
output('Collected Cycles: ' . $gc . "\n\n");

$resources = sql_query("SELECT ref, file_extension FROM resource WHERE ref > 0 AND archive != {$resource_deletion_state}");
output('Resources in the Database Without Corresponding Original Files in S3 Storage');

// Loop through the resources in the database.
foreach($resources as $resource)
    {
    // Get the resource original filestore path and S3 object path.
    $file_path = get_resource_path($resource['ref'], true, '', false, $resource['file_extension']);
    $s3_object = s3_object_path($file_path);

    // Check if the file(object) exists in S3 storage.
    $s3_result = s3_object_head($s3_object);
    if($s3_result)
        {
        continue;
        }

    output('ID: ' . $resource['ref'] . ', ' . $s3_object . "\n");
    }

$end = time();
$t_unit = " minutes.\n\n";
$ltime = ($end - $start) / 60;
if($ltime > 60)
    {
    $ltime = $ltime / 60;
    $t_unit = " hours.\n\n";
    }
output("\nScript ended on " . nicedate(date("Y-m-d h:i:s")) . ' in ' . number_format($ltime, 1, '.', '') . $t_unit);
fclose($text_file);


// Function to add "output" text to a script run text output file.
function output($text)
    {
    global $out_file;
    echo($text);
    ob_flush();
    file_put_contents($out_file, $text, FILE_APPEND);
    }
