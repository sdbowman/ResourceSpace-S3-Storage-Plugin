<?php
// s3_storage Plugin delete.php Hooks File
// This file adds hook functions to the ../pages/delete.php page.

include_once '../plugins/s3_storage/include/s3_storage_functions.php';


/**
* Hook to display a list of files before actually deleting them.
*
* @param integer $ref Resource ref ID.
*
* @return
*/
function HookS3_storageDeleteDelete_extra($ref)
    {
    global $s3_storage_enable, $show_files_delete, $lang;

    if($s3_storage_enable || $show_files_delete)
        {
        // Determine the resource file directory path.
        $ref_path = pathinfo(get_resource_path($ref, true, '', false));
        $resource_path = $ref_path['dirname'] . "\n";
        $resource_path = substr_replace($resource_path, '', -1);

        ?> <br>
        <h2><?php echo $lang['deletefilecheck']?></h2>
        <p><?php echo $lang['deletefilechecktext']?></p>
        <?php

        // List the files stored in the normal filestore.
        $scandir = scandir($resource_path);
        echo $lang['filestore'] . ' (' . $resource_path . ')';
        ?> <br> <hr> &nbsp;&nbsp;&nbsp; <?php
        echo $resource_path;
        ?> <br> <?php
        foreach($scandir as $sdir)
            {
            if($sdir != '.' && $sdir != '..')
                {
                ?> &nbsp;&nbsp;&nbsp; <?php
                echo $sdir;
                ?> <br> <?php
                }
            }
        ?> <br> <?php

        // List the files in S3 storage.
        if($s3_storage_enable)
            {
            $resource_path = s3_object_path($resource_path, true);
            echo $lang['s3_storage'] . ' (' . $resource_path . ')';
            ?> <br> <hr> <?php
            // Get a list of the files in S3 storage matching the filestore subfolder.
            $list_result = s3_object_list($resource_path);
            if($list_result && $list_result['Contents'] != '')
                {
                foreach($list_result['Contents'] as $s3_file)
                    {
                    ?> &nbsp;&nbsp;&nbsp; <?php
                    if($s3_file['StorageClass'] == '')
                        {
                        $s3_file['StorageClass'] = 'STANDARD';
                        }

                    $s3_strclass = s3_storage_class($s3_file['StorageClass']);
                    $s3_line = $s3_file['Key'] . ' (' . formatfilesize($s3_file['Size']) . ') ' . $lang['in'] . ' ' . strtolower($lang['s3_storage_class']) . $s3_strclass['name'];
                    echo $s3_line;
// FUTURE: Need to add error check for no S3 files if filestore files.
                    ?> <br> <?php
                    }
                }
            }
        } ?> <br> <?php
    }
