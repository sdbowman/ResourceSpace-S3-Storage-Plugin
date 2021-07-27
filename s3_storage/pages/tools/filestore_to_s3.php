<?php
// Tool to Convert an Existing Filestore with Original/Alternative Files to Simple Storage Service (S3) Object-Based Storage

// SCRIPT USER NOTES
// If original/alternative files fail to verify they are in the S3 bucket, not enough time since upload may cause eventual consistency to not have been met, and is a part of object-based storage systems.  Suggest rerunning the script to verify again.

include_once dirname(__FILE__) . '/../../include/s3_storage_functions.php';

$start = time();
$start_text = nicedate(date("Y-m-d h:i:s"), true, false);

// Check if run on the command line, if not, exit.
global $lang;
if('cli' != php_sapi_name())
    {
    header('HTTP/1.1 401 Unauthorized');
    exit($lang['cli_error']);
    }

global $storagedir, $s3_storage_enable, $s3_storage_provider, $s3_storage_region, $s3_storage_bucket, $s3_storage_class, $originals_separate_storage;

// Setup script logging to a text file named by the date and time the script is run.
$s3_temp_dir = s3_create_temp();
$out_file = $s3_temp_dir . 'FilestoreToS3_' . str_replace(array('/', ':', '@'), '-', str_replace(' ', '', $start_text)) . '.txt';
$text_file = fopen($out_file, 'w+');

// Introductory script output text.
output("\nRESOURCESPACE FILESTORE TO OBJECT-BASED SIMPLE STORAGE SERVICE (S3) CONVERTER v1.0: ORIGINAL/ALT FILES ONLY\n");
output('Start on ' . $start_text . "\n");
output('Text output saved to ' . $out_file . "\n\n");
output("This script converts an existing ResourceSpace filestore to use object-based Simple Storage Service (S3) for\n original and original alternative files only.  Files in the S3 bucket are in the same \"folder\" structure as\n in the filestore.  The preview images are left in the filestore for high performance.  You must set up an\n empty S3 bucket and set the \$s3_enable, \$storagedir, \$s3_key, \$s3_secret, \$s3_provider, \$s3_storage_class,\n \$s3_bucket, \$s3_region, and \$s3_stats parameters in the ../include/config.php file and the server date and\n time are correct before starting.  If the script is rerun, it will skip over already processed files as\n needed.  While this script is running, no users should use the system.  It is highly recommended to run a\n full system backup before running this script.  You may also want to run ../pages/tools/prune_filestore.php\n and database_prune.php to cleanup the filestore and database prior to running this script.\n
 Step 1: Configures filestore to S3 bucket storage conversion parameters.
 Step 2: Uploads original resource and alternative files to a specified S3 bucket.
 Step 3: Verifies original file in S3, deletes the filestore original file, and creates a placeholder file.\n\n\n");

// Start Step 1, set up the conversion parameters.
output("STEP 1 OF 3: SETTING UP FILESTORE TO S3 BUCKET CONVERSION PARAMETERS-----------\n");
output('Filestore Location: ' . $storagedir . "\n");
output('Separated Filestore: ' . boolean_convert($originals_separate_storage, 'yes') . "\n");

// Get the range of resource IDs to check and set up the counting variables.
$ref_max = get_max_resource_ref();
$ref_range = get_resource_ref_range(0, $ref_max);
$ref_number = count($ref_range);
$count = 1;
$s3_count = 0;
$s3_error_count = 0;
$upfile_missing_count = 0;
output($ref_number . " resources found in the ResourceSpace filestore to convert to S3 bucket storage.\n");

// Check the S3 storage parameters.
$result = s3_check_parameters();
if(!$result)
    {
    output("ERROR: Must set \$storagedir, \$s3_storage_enable = true, \$s3_storage_provider, \$s3_storage_key, \$s3_storage_secret, \$s3_storage_class, \$s3_storage_bucket, \$s3_storage_region, and \$s3_storage_stats, exiting.\n");
    exit();
    }

output('S3 Storage Provider: ' . $s3_storage_provider . "\n");
output('S3 Bucket Name: ' . $s3_storage_bucket . "\n");
output('S3 Storage Class: ' . $s3_storage_class . "\n");
output('S3 Region: ' . $s3_storage_region . "\n");
output("The required S3 storage parameters have been set in the s3_storage plugin configuration.\n");

// Check that the specified S3 bucket exists; otherwise, exit.
output("Checking that the S3 bucket '" . $s3_storage_bucket . "' exists...");
$result = s3_bucket_head($s3_storage_bucket);
output(boolean_convert($result, 'ok') . "\n");

if(!$result)
    {
    $result = s3_bucket_list();
    output("Available buckets:\n" . print_r($result, true) . "\n");
    exit();
    }

// Run the PHP garbage collector to free memory.
output('Running PHP Garbage Collector to Free Memory...');
$gc = gc_collect_cycles();
output('Collected Cycles: ' . $gc . "\n\n");

// Start Step 2, upload the filestore original files to S3.
output("STEP 2 OF 3: UPLOADING FILESTORE ORIGINAL FILES TO A S3 BUCKET----------\n");
$file_size = '';
$upload_size = 0;

// Loop through the resource IDs and upload original and alternative files to S3; files uploaded to same path structure in ../resourcespace/filestore/.. to a specified S3 bucket.
foreach($ref_range as $ref)
    {
    // Check for resource alternative files for a specific resource.
    $alt_files = get_alternative_files($ref);
    $alt_files_num = count($alt_files);

    output('PROCESSING RESOURCE ' . $count . ' OF ' . $ref_number . ' WITH RESOURCESPACE ID ' . $ref . ' AND ' . $alt_files_num . " ALTERNATIVE FILES\n");

    // Build array of the resource original file and alternative files to upload to a S3 bucket.
    $ref_original[0]['ref'] = 0;
    $ref_files = array_merge($ref_original + $alt_files);

    // Loop through resource original files and upload to S3.
    foreach($ref_files as $file)
        {
        // Setup AWS SDK S3 putObject parameters.
        if($file['ref'] == 0)
            {
            $s3filepath = get_resource_path($ref, true, '', false);
            $file_output = 'Uploading original file (';
            $file_info = 'Original file: ';
            }
        else
            {
            $s3filepath = get_resource_path($ref, true, '', false, $file['file_extension'], true, 1, false, '', $file['ref']);
            $file_output = 'Uploading alternative file (';
            $file_info = 'Original alternative file #' . $file['ref'] . ': ';
            }

        output($file_info . $s3filepath . "\n");
        if(!is_file($s3filepath))
            {
            output("  ERROR: Skipping resource, unable to find original file in the filestore.\n");
            ++$s3_error_count;
            continue;
            }

        $s3strippath = s3_object_path($s3filepath, false);
        $original_file = filesize($s3filepath);

        // Check if file needs to be uploaded.
        if($original_file != 0)
            {
            $file_size = str_replace("&nbsp;", ' ', formatfilesize($original_file));
            output($file_output . $file_size . ') to a S3 bucket...');
            }
        elseif($original_file == 0)
            {
            output("Skipping file, already uploaded to the S3 bucket.\n");
            continue;
            }

        // Upload a file to a S3 bucket.
        $result = s3_object_upload($s3filepath, $s3strippath, $s3_storage_class);
        if($result)
            {
            $upload_size += $original_file;
            ++$s3_count;
            output("Done\n");
            }
        else
            {
            ++$s3_error_count;
            }
        }

    ++$count;
    output ("\n");
    }

// Start Step 3, verify files exist in the specified S3 bucket, and if so, delete the filestore copies.
output("\nSTEP 3 OF 3: VERIFYING ORIGINAL FILES IN THE S3 BUCKET AND DELETING FILESTORE COPY----------\n");
$count = 1;
$s3_verify_error = 0;
$delete_error = 0;
$placeholder_error = 0;

// Loop through the resource IDs and verify/delete original files.
foreach($ref_range as $ref)
    {
    output('VERIFYING RESOURCE ' . $count . ' OF ' . $ref_number . ' AS RESOURCESPACE ID ' . $ref . "\n");

    // Check for resource alternative files.
    $alt_files = get_alternative_files($ref);
    $alt_files_num = count($alt_files);

    // Build array of resource original file and original alternative files in a S3 bucket.
    $ref_original[0]['ref'] = 0;
    $ref_files = array_merge($ref_original + $alt_files);

    // Loop through resource original files.
    foreach($ref_files as $file)
        {
        // Setup AWS SDK S3 doesObjectExist parameters.
        if($file['ref'] == 0)
            {
            $s3filepath = get_resource_path($ref, true, '', false);
            $file_output = 'Verifying original file is in the S3 bucket...';
            $file_info = 'Original file: ';
            }
        else
            {
            $s3filepath = get_resource_path($ref, true, '', false, $file['file_extension'], true, 1, false, '', $file['ref']);
            $file_output = 'Verifying alternative file is in the S3 bucket...';
            $file_info = 'Original alternative file: ';
            }

        output($file_info . $s3filepath . "\n");
        if(!is_file($s3filepath))
            {
            output("  ERROR: Unable to find original file in the filestore.\n");
            }
        output($file_output);
        $s3strippath = s3_object_path($s3filepath, false);

        // Check if file exists in the specified S3 bucket before deleting.
        try
            {
            $s3result = s3_object_head($s3strippath);
            output(boolean_convert($s3result, 'ok'));
            output("\n");

            // Check filestore file size for zero byte placeholder file.
            if(is_file($s3filepath))
                {
                $placeholder = filesize($s3filepath);
                }
            elseif ($s3result) // Create placeholder file if missing, if original is in S3 bucket.
                {
                output('Filestore placeholder file missing, creating file...');
                $placeholder_file = fopen($s3filepath, 'w');
                fclose($placeholder_file);
                $placeholder = filesize($s3filepath);
                output("Done\n");
                }
            else
                {
                output("  ERROR: Unable to find the original placeholder file.\n");
                ++$placeholder_error;
                continue;
                }

            // Delete filestore file if in a S3 bucket.
            if($s3result && $placeholder != 0)
                {
                output('Deleting original file in filestore...');
                $file_delete = unlink($s3filepath);

                if(!$file_delete)
                    {
                    ++$delete_error;
                    }

                output(boolean_convert($file_delete, 'ok'));
                output("\n");

                // Adding filestore placeholder file.
                if($placeholder != 0)
                    {
                    output('Creating filestore placeholder file...');
                    $placeholder_file = fopen($s3filepath, 'w');
                    fclose($placeholder_file);
                    output(boolean_convert($placeholder_file, 'ok'));
                    output("\n");
                    }
                }
            elseif($placeholder == 0)
                {
                output("Skipping file, already processed.\n");
                }
            else // File not in a S3 bucket, do not delete.
                {
                ++$s3_verify_error;
                output("  ERROR: File does not exist in the S3 bucket, not deleting original.\n");
                }
            }
        catch(Aws\S3\Exception\S3Exception $e) // Error catching.
            {
            output('ERROR: Object Head: ' . $e->getMessage());
            }
        }

    ++$count;
    output ("\n");
    }

// Summarize script results and terminate.
output("\nRESOURCESPACE FILESTORE TO S3 BUCKET CONVERSION SUMMARY----------\n");
if ($upload_size == 0)
    {
    $upload_size = '';
    }
else
    {
    $upload_size = str_replace("&nbsp;", ' ', '(' . formatfilesize($upload_size) . ') ');
    }

output($count - 1 . ' files processed with ' . $s3_count . ' files ' . $upload_size . "uploaded to the S3 '" . $s3_bucket . "' bucket.\n");
//output($upfile_missing_count . " original files missing from the filestore during the upload process.\n");
output($s3_error_count . " original files failed to upload to the S3 '" . $s3_storage_bucket . "' bucket.\n");
if($s3_error_count > 0)
    {
    output("  ERROR: Run ../pages/tools/prune_filestore.php and database_prune.php and rerun this script to cleanup.\n");
    }

output($s3_verify_error . " original files failed to verify in the  S3 '" . $s3_storage_bucket . "' bucket.\n");

output($placeholder_error . " placeholder files missing from the filestore during the verify process.\n");
if($placeholder_error > 0)
    {
    output("  ERROR: Run ../pages/tools/prune_filestore.php and database_prune.php and rerun this script to cleanup.\n");
    }

output($delete_error . ' original files failed to delete from the ResourceSpace filestore: ' . $storagedir . "\n");

$end = time();
$t_unit = " minutes.\n\n";
$ltime = ($end - $start) / 60;
if($ltime > 60)
    {
    $ltime = $ltime / 60;
    $t_unit = " hours.\n\n";
    }
output('Script ended on ' . nicedate(date("Y-m-d h:i:s")) . ' in ' . number_format($ltime, 1, '.', '') . $t_unit);

// Close the text log file and unset large variables to free memory.
fclose($text_file);
unset($ref_range, $ref_files);


// Function to add "output" text to a script run text output file.
function output($text)
    {
    global $out_file;
    echo($text);
    ob_flush();
    file_put_contents($out_file, $text, FILE_APPEND);
    }
