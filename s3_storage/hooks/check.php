<?php
// s3_storage Plugin check.php Hooks File
// This file adds hook functions to the ../pages/check.php page.

include_once '../plugins/s3_storage/include/s3_storage_functions.php';


/**
* Hook to add a brief check of S3 storage (S3 bucket connectivity, bucket location, and ACL information).
*
* @return
*/
function HookS3_storageCheckAdd_filestore_install_check()
    {
    global $s3_storage_enable, $lang, $s3_storage_provider, $s3_storage_bucket, $s3_storage_class;

    // Check S3 object-based original filestore connectivity.
    if($s3_storage_enable)
        {
        global $s3_storage_bucket, $s3_storage_class;

        // Check if the S3 bucket is accessible.
        $result = s3_bucket_head($s3_storage_bucket);
        $s3_bucket = $s3_storage_bucket;

        // Determine the S3 bucket location and ACL information.
        if($result)
            {
            $result2a = s3_get_region();
            $result2 = s3_bucket_location($s3_storage_bucket);
            $result3 = s3_bucket_owner($s3_storage_bucket);
            $result4 = s3_storage_class($s3_storage_class);
            }
        else
            {
            $result2a = $lang['status-fail'];
            $result2['LocationConstraint'] = $lang['status-fail'];
            $result3['name'] = $lang['status-fail'];
            $result3['id'] = $lang['status-fail'];
            $result4['name'] = $lang['status-fail'];
            }

        if($s3_storage_bucket == '')
            {
            $s3_bucket = $lang['status-fail'];
            }

        if(!$result || !$result2a || !$result2 || !$result3 || !$result4)
            {
            $result = false;
            }
// add after ['s3_region'] . $result2['LocationConstraint']
        ?><tr><td><?php echo $lang['s3_storage'];?></td><td><?php echo $lang['s3_provider'] . $s3_storage_provider;?><br/><?php echo $lang['s3_endpoint_region'] . $result2a;?><br/><?php echo $lang['s3_bucket'] . $s3_bucket;?><br/><?php echo $lang['s3_region'] ;?><br/><?php echo $lang['s3_storage_class'] . $result4['name'];?><br/><?php echo $lang['s3_owner'] . $result3['name'];?><br/><?php echo $lang['s3_id'] . $result3['id'];?></td><td><b><?php echo boolean_convert($result, 'ok');?></b></td></tr><?php
        }
    }
