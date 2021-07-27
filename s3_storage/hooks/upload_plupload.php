<?php
// s3_storage Plugin upload_plupload.php Hooks File
// This file adds hook functions to the ../pages/upload_plupload.php file.

include_once '../plugins/s3_storage/include/s3_storage_functions.php';


/**
* Hook to upload an original alternative file to a S3 bucket, delete the filestore original alternative file, and create
* a filestore placeholder alternative file.
*
* @param string $path   Path to local filestore file to upload to S3 storage.
*
* @return
*/
function HookS3_storageUpload_pluploadUpload_alternative_extra($path)
    {
    $s3_object = s3_object_path($path);
    s3_object_upload($path, $s3_object);
    s3_file_placeholder($path);
    }


/**
* Hook to upload an original file to a S3 bucket, delete the filestore original file, and create a filestore placeholder
* file.
*
* @param string $path   Path to local filestore file to upload to S3 storage.
*
* @return
*/
function HookS3_storageUpload_pluploadUpload_original_extra($ref)
    {
    $result = sql_query("SELECT file_extension FROM resource WHERE ref = '$ref'");
    $fs_path = get_resource_path($ref, true, '', false, $result[0]['file_extension']);
    $s3_object = s3_object_path($fs_path);
    s3_object_upload($fs_path, $s3_object);
    s3_file_placeholder($fs_path);
    }


/**
* Hook to replace the Upload Log.
*
* @return
*/
function HookS3_storageUpload_pluploadUpload_log()
    {
    global $lang, $s3_storage_log_height;

    ?>
    <div class="BasicsBox">
        <h2 class="CollapsibleSectionHead" id="UploadLogSectionHead" onClick="UICenterScrollBottom();"><?php echo $lang['plupload_log'];?></h2>
        <div class="CollapsibleSection" id="UploadLogSection">
            <textarea id="upload_log" rows=<?php echo $s3_storage_log_height;?> cols=100 style="width: 100%; border: solid 1px;"><?php echo $lang['plupload_log_intro'] . nicedate(date('Y-m-d H:i'), true) . ')';?></textarea>
        </div>
    </div> <?php
    }



function HookS3_storageUpload_pluploadReplace_upload_log_text()
    { ?>
    jQuery("#upload_log").append("\r\n" + "Uploaded: " + file.name + " - " + uploadresponse.message + " as Resource ID " + uploadresponse.id);
    if(resource_keys === processed_resource_keys)
        {
        resource_keys = [];
        }
    resource_keys.push(uploadresponse.id.replace( /^\D+/g, '')); <?php
    }
