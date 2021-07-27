<?php
// s3_storage Plugin edit.php Hooks File
// This file adds hook functions to the ../pages/edit.php file.


function HookS3_storageEditEdit_previews_recreate_extra($ref)
    {
    debug('EDIT Recreate Previews for Ref ID: ' . $ref);
    }


function HookS3_storageEditEdit_filesize_extra($ref)
    {
    $result = get_resource_data($ref);
    $file_size = formatfilesize($result['file_size']);
    unset($result);

    return $file_size;
    }
