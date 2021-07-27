<?php
// s3_storage Plugin download.php Hooks File
// This file adds hook functions to the ../pages/download.php page.

include_once '../plugins/s3_storage/include/s3_storage_functions.php';

/**
* Hook to download an original resource from S3 storage.
*
* @param string $path   Path to local filestore file to save the downloaded S3 object(file).
*
* @return               TRUE on success or FALSE.
*/
function HookS3_storageDownloadDownload_resource_extra($path)
    {
    global $s3_storage_enable;

    // Download the file from S3 storage.
    if($s3_storage_enable)
        {
        try
            {
            // Determine the local S3 object tmpfile name.
            $fs_tmpfile = s3_file_tempname($path);
            $result['fs_tmpfile'] = $fs_tmpfile;

            // Determine the object path, check if it exists, and download the original file from a S3 bucket.
            $s3_object = s3_object_path($path, false);
            $s3_result = s3_object_exists($s3_object);
            if($s3_result)
                {
                s3_object_download($s3_object, $fs_tmpfile);
                $result['status'] = true;
                }
            else
                {
                debug('ERROR DOWNLOAD_RESOURCE_EXTRA: S3 object Does Not Exist');
                $result['status'] = false;
                }
            }
        catch(Exception $e)
            {
            debug('ERROR DOWNLOAD_RESOURCE_EXTRA S3: ' . $e->getMessage());
            $result['status'] = false;
            }
        }

    debug('DOWNLOAD_RESOURCE_EXTRA Result: ' . print_r($result, true));
    return $result;
    }


/**
* Hook to modify the download path to the temp file downloaded from S3 storage.
*
* @param array $download_extra  Output from the earlier download_resource_extra hook.
*
* @return
*/
function HookS3_storageDownloadModifydownloadpath2($download_extra)
    {
    debug('MODIFYDOWNLOADPATH Result: ' . print_r($download_extra, true));
    return $download_extra['fs_tmpfile'];
    }


/**
* Hook to prevent stripping metadata on download.
*
* @return
*/
function HookS3_storageDownloadDownload_write_metadata()
    {
    }


/**
* Hook to delete the download temp file created from downloading a file from S3 storage.
*
* @param array $download_extra  Output from the earlier download_resource_extra hook.
*
* @return
*/
function HookS3_storageDownloadBeforedownloadresourceexit($download_extra)
    {
    global $s3_storage_enable;

    if($s3_storage_enable && file_exists($download_extra['fs_tmpfile']))
        {
        $s3_result = unlink($download_extra['fs_tmpfile']);
        debug('BEFOREDOWNLOADRESOURCEEXIT Delete S3 Temp File: ' . boolean_convert($s3_result, 'ok'));
        }
    }
