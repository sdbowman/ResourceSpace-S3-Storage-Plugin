<?php
// s3_storage Plugin All Files Hooks File
// This file adds hook functions to ../include/.. PHP files.

global $baseurl;
include_once (__DIR__) . '/../include/s3_storage_functions.php';


/**
* Hook to save the original resource as an alternative file in S3 storage.
*
* @param array $paths   ('origpath' => Original resource filestore filepath, 'newaltpath' => New alternative filestore
*                        filepath)
*
* @return
*/
function HookS3_storageAllSave_original_alternative_extra($paths)
    {
    global $s3_storage_class;

    // Create S3 path and check if the S3 object exists.
    debug('SAVE_ORIGINAL_ALTERNATIVE_EXTRA S3 Input: ' . print_r($paths, true));
    $object_from = s3_object_path($paths['origpath']);
    $s3_result = s3_object_exists($object_from);

    // If the S3 object exists, create the alternative file S3 path, and move the object.
    if($s3_result)
        {
        $object_to = s3_object_path($paths['newaltpath']);
        $result = s3_object_copy($object_from, $object_to, $s3_storage_class);
        }
    // ERROR: The S3 object does not exist or an other error.
    else
        {
        debug('ERROR SAVE_ORIGINAL_ALTERNATIVE_EXTRA S3: ' . $object_from);
        return false;
        }
    }


/**
* Hook to replace an existing original resource file in S3 storage.
*
* @param array $resource   Results from function get_resource_data.
*
* @return
*/
function HookS3_storageAllReplace_resource_file_extra($resource)
    {
    debug('REPLACE_RESOURCE_FILE_EXTRA S3 Ref: ' . $resource['ref']);
    $fs_path = get_resource_path($resource['ref'], true, '', false, $resource['file_extension']);
    $s3_object = s3_object_path($fs_path);
    s3_object_upload($fs_path, $s3_object);
    s3_file_placeholder($fs_path);
    }


/**
* Hook to delete the original file in S3 storage.
*
* @param array $resource   Results from function get_resource_data.
*
* @return
*/
function HookS3_storageAllDelete_resource_extra($resource)
    {
debug('DELETE_RESOURCE_EXTRA S3 $resource: ' . print_r($resource, true));
    $path = get_resource_path($resource['ref'], true, '', false, $resource['file_extension']);
    $s3_object = s3_object_path($path);
    $s3_result = s3_object_exists($s3_object);
    if($s3_result)
        {
        s3_object_delete($s3_object);
        }
    }


/**
* Hook to delete the original file in S3 storage.
*
* @param string $dirpath   Path to local filestore file to delete in S3 storage.
*
* @return
*/
function HookS3_storageAllDelete_resource_path_extra($dirpath)
    {
    if(is_dir($dirpath))
        {
        $s3_object = s3_object_path($dirpath);
        debug('DELETE_RESOURCE_PATH_EXTRA S3 Object Path:' . $dirpath);

        $s3_object_list = s3_object_list($s3_object);
        debug('DELETE_RESOURCE_PATH_EXTRA S3 List: ' . print_r($s3_object_list, true));
        if(isset($s3_object_list['Contents']))
            {
            foreach($s3_object_list['Contents'] as $object)
                {
                $s3_result = s3_object_exists($object['Key']);
                if($s3_result)
                    {
                    $result = s3_object_delete($object['Key']);
                    debug('DELETE_RESOURCE_PATH_EXTRA S3 Delete: ' . boolean_convert($result, 'ok') . ', ' . $object['Key']);
                    }
                }
            }
        else
            {
            debug('DELETE_RESOURCE_PATH_EXTRA S3: No objects found to delete.');
            }
        }
    else
        {
        debug('ERROR DELETE_RESOURCE_PATH_EXTRA S3: Invalid folder path.');
        return false;
        }
    }


/**
* Hook to delete an original alternative file in S3 storage.
*
* @param string $path   Path to local filestore file to delete in S3 storage.
*
* @return
*/
function HookS3_storageAllDelete_alternative_file_extra($path)
    {
    debug('DELETE_ALTERNATIVE_FILE_EXTRA S3 Input Path: ' . $path);
    $s3_object = s3_object_path($path);
    debug('DELETE_ALTERNATIVE_FILE_EXTRA S3 Object Path: ' . $s3_object);

    $s3_result = s3_object_exists($s3_object);
    if($s3_result)
        {
        s3_object_delete($s3_object);
        }
    }


/**
* Hook to delete the JPG original alternative file in S3 storage.
*
* @param string $path   Path to local filestore file to delete in S3 storage.
*
* @return
*/
function HookS3_storageAllDelete_alternative_jpg_extra($path)
    {
    $s3_object = s3_object_path($path);
    $s3_result = s3_object_exists($s3_object);
    if($s3_result)
        {
        debug('DELETE_ALTERNATIVE_JPG_EXTRA S3 Object Path: ' . $s3_object);
        s3_object_delete($s3_object);
        }
    }


/**
* Hook to delete the MP3 original alternative file in S3 storage.
*
* @param string $path   Path to local filestore file to delete in S3 storage.
*
* @return
*/
function HookS3_storageAllDelete_alternative_mp3_extra($path)
    {
    $s3_object = s3_object_path($path);
    $s3_result = s3_object_exists($s3_object);
    if($s3_result)
        {
        debug('DELETE_ALTERNATIVE_MP3_EXTRA S3 Object Path: ' . $s3_object);
        s3_object_delete($s3_object);
        }
    }


/**
* Hook to delete the original alternative files in S3 storage.
*
* @param string $path   Path to local filestore file to delete in S3 storage.
*
* @return
*/
function HookS3_storageAllDelete_alternative_file_loop($path)
    {
    $s3_object = s3_object_path($path);
    $s3_result = s3_object_exists($s3_object);
    if($s3_result)
        {
        debug('DELETE_ALTERNATIVE_FILE_LOOP S3 Object Path: ' . $s3_object);
        s3_object_delete($s3_object);
        }
    }


/**
* Hook to download an object in S3 storage for the createTempFile function.
*
* @param string $path       Path to local filestore file to download from S3 storage.
* @param string $tmpfile    Path to temp file.
*
* @return
*/
function HookS3_storageAllCreatetempfile_copy($path, $tmpfile)
    {
    global $ref;

    // Check the filestore file for its size; if not a zero-byte original placeholder file, return with no action.
    debug('HOOK CREATETEMPFILE_COPY From: ' . $path);
    debug('HOOK CREATETEMPFILE_COPY To: ' . $tmpfile);
    if(filesize_unlimited($path) <= 1)
        {
        // Check if the resource is a resource type not using S3 storage.
        s3_check_resource_type($ref);

        // Get the file S3 object path and check if it exists in S3 storage.
        $s3_object = s3_object_path($path);
        $s3_result = s3_object_exists($s3_object);

        // If the S3 object exists, download the object to the $tmpfile path and return the path.
        if($s3_result)
            {
            s3_object_download($s3_object, $tmpfile);
            return $tmpfile;
            }
        }

    // Filestore file not a placeholder file or the S3 object does not exist in S3 storage.
    return false;
    }


/**
* Hook to download original file from S3 storage to the filestore to recreate previews.
*
* @param string $ref   Resource ID.
*
* @return
*/
function HookS3_storageAllCreate_previews_extra($ref)
    {
    // Get the resource data and path to the filestore original placeholder file.
    debug('HOOK CREATE_PREVIEWS_EXTRA $ref: ' . $ref);
    $fs_resource = get_resource_data($ref);
    $fs_path = get_resource_path($ref, true, '', false, $fs_resource['file_extension']);

    // Check the placeholder file is a file, its size, then delete.  If filesize > 1, then not a placeholder file.
    if(is_file($fs_path) && filesize_unlimited($fs_path) > 1)
        {
        debug('HOOK CREATE_PREVIEWS_EXTRA: Skipping, not a filestore resource placeholder file.');
        return true;
        }
    // File is a placeholder file, so delete the file in preparation for downloading the original from S3 storage.
    elseif(is_file($fs_path) && filesize_unlimited($fs_path) <= 1)
        {
        $result = unlink($fs_path);
        debug('HOOK CREATE_PREVIEWS_EXTRA Delete Placeholder File: ' . boolean_convert($result, 'ok'));
        }
    else
        {
        debug('HOOK CREATE_PREVIEWS_EXTRA: Filestore file does not exist.');
        }

    // Get the S3 path and download the original file from S3 storage.
    $s3_object = s3_object_path($fs_path);
    $s3_result = s3_object_exists($s3_object);
    if($s3_result)
        {
        $s3_file = s3_object_download($s3_object, $fs_path);
        }
    else
        {
        debug('ERROR HOOK CREATE_PREVIEWS_EXTRA Recreate: ' . print_r($s3_result, true));
        return false;
        }

    debug('CREATE_PREVIEWS_EXTRA S3 Result: ' . $fs_path);
    return $fs_path;
    }


/**
* Hook to delete the filestore original file downloaded from S3 storage for preview re-creation and create a new
* placeholder file.
*
* @param string $fs_path   Path to local filestore file.
*
* @return
*/
function HookS3_storageAllAfterpreviewcreation($fs_path)
    {
    // Delete the filestore original file (not the zero-byte placeholder file) if it exists.
    if(is_file($fs_path) && filestore_unlimited($fs_path) > 1)
        {
        $result = unlink($fs_path);
        debug('AFTERPREVIEWCREATION Delete Original File Result: ' . boolean_convert($result, 'ok'));
        }

    // Create a new filestore original zero-byte placeholder file.
    s3_file_placeholder($fs_path);
    }


/**
* Hook to change the collection download metadata CSV filename.
*
* @return string  Filename
*/
function HookS3_storageAllCollectiondownloadcsvfilename()
    {
    return 'collection_resource_metadata.csv';
    }


/**
* Hook to change the download collection archive filename.
*
* @param
*
* @return string    Download filename.
*/
function HookS3_storageAllChangecollectiondownloadname($collection, $size, $suffix)
    {
    global $lang, $use_collection_name_in_zip_name, $collectiondata, $s3_storage_collection_prefix;

    # Use collection name if configured.
    if($use_collection_name_in_zip_name)
        {
        $filename = trim($s3_storage_collection_prefix . '-' . safe_file_name(i18n_get_collection_name($collectiondata)) . $suffix, '-');
        }
    # Default: Do not include the collection name in the filename.
    else
        {
        $filename = trim($s3_storage_collection_prefix . '-' . $collection . $suffix, '-');
        }

    return trim($filename);
    }


/**
* Hook to change the download collection text file contents.
*
* @param
*
* @return string    Download filename.
*/
function HookS3_storageAllReplacecollectiontext($text, $sizetext, $filename, $ref, $fields, $fields_count, $commentdata)
    {
    global $lang;

    // Create the resource title line.
    $text .= $filename . ' (' . ($sizetext == '' ? '' : trim($sizetext, '-')) . ') ' . $lang['s3_storage_with'] . ' ' . $lang['resourceid'] . ': ' . $ref . "\r\n";

    // Create the resource metadata lines.
    for($i = 0; $i < $fields_count; $i++)
        {
        $value = $fields[$i]['value'];
        $title = str_replace('Keywords - ', '', $fields[$i]['title']);
        if((trim($value) != '') && (trim($value) != ','))
            {
            $text .= wordwrap('  ' . $title . ': ' . i18n_get_translated($value) . "\r\n", 65);
            }
        }

    if(trim($commentdata['comment']) != '')
        {
        $text .= wordwrap($lang['comment'] . ': ' . $commentdata['comment'] . "\r\n", 65);
        }

    if(trim($commentdata['rating']) != '')
        {
        $text .= wordwrap($lang['rating'] . ': ' . $commentdata['rating'] . "\r\n", 65);
        }
    $text .= "\r\n-----------------------------------------------------------------\r\n";

    return $text;
    }


function HookS3_storageAllZippedcollectiontextfile($text)
    {
    global $lang, $zipped_collection_textfile, $includetext, $sizetext, $use_zip_extension, $p, $available_sizes, $subbed_original_resources, $result, $usertempdir, $collection_download_tar, $size, $used_resources, $id, $path;

    if($zipped_collection_textfile == true && $includetext == 'true')
        {
        $qty_sizes = isset($available_sizes[$size]) ? count($available_sizes[$size]) : 0;
        $qty_total = count($result);
        $text .= $lang['status-note'] . ': ' . $qty_sizes . ' ' . $lang['of'] . ' ' . $qty_total . ' ';
        switch($qty_total)
            {
            case 0:
                $text .= $lang['resource-0'] . ' ';
                break;
            case 1:
                $text .= $lang['resource-1'] . ' ';
                break;
            default:
                $text .= $lang['resource-2'] . ' ';
                break;
            }

        switch($qty_sizes)
            {
            case 0:
                $text .= $lang['were_available-0'] . ' ';
                break;
            case 1:
                $text .= $lang['were_available-1'] . ' ';
                break;
            default:
                $text .= $lang['were_available-2'] . ' ';
                break ;
            }

        $text .= $lang['forthispackage'] . ".\r\n\r\n";

        foreach($result as $resource)
            {
            if(in_array($resource['ref'], $subbed_original_resources))
                {
                $text .= $lang['didnotinclude'] . ': ' . $resource['ref'];
                $text .= ' (' . $lang['substituted_original'] . ')';
                $text .= "\r\n";
                }
            elseif(!in_array($resource['ref'], $used_resources))
                {
                $text .= $lang['didnotinclude'] . ': ' . $resource['ref'];
                $text .= "\r\n";
                }
            }

        $textfilename = 'collection_resource_metadata.txt';
        $textfile = get_temp_dir(false, $id) . DIRECTORY_SEPARATOR . $textfilename;
        $fh = fopen($textfile, 'w') or die('cannot open file');
        fwrite($fh, $text);
        fclose($fh);
        if($collection_download_tar)
            {
            debug('COLLECTION_DOWNLOAD Adding Symlink: ' . $p . ' - ' . $usertempdir . DIRECTORY_SEPARATOR . $textfilename);
            @symlink($textfile, $usertempdir . DIRECTORY_SEPARATOR . $textfilename);
            }
        elseif($use_zip_extension)
            {
            $zip->addFile($textfile, $textfilename);
            }
        else
            {
            $path .= $textfile . "\r\n";
            }

        $deletion_array[] = $textfile;
        }

    return true;
    }
