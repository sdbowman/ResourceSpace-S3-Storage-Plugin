<?php
// Simple Storage Service (S3) Object-Based Storage Installation Check
// This file creates the Admin, System, S3 Installation Check page to check the installation and operation of the s3_storage plugin.

include '../../../../include/db.php';
include '../../../../include/authenticate.php';
if(!checkperm('a'))
    {
    exit($lang['error-permissiondenied']);
    }
include_once '../../include/s3_storage_functions.php';
include '../../../../include/header.php';

global $lang, $s3_storage_enable, $originals_separate_storage, $storagedir, $s3_storage_bucket, $s3_storage_provider, $s3_storage_stats;

// Page breadcrumbs.
$links_trail = array(
    array(
        'title' => $lang['systemsetup'],
        'href'  => $baseurl_short . 'pages/admin/admin_home.php'
    ),
    array(
        'title' => $lang['s3_check'] 
    )
);
renderBreadcrumbs($links_trail);

?>
<div class="BasicsBox">
<h1><?php echo $lang['s3_check_title'];?></h1>
<p><?php echo $lang['s3_check_introtext'];?></p>

<table class="InfoTable"><?php
    // S3 storage check.
    if(isset($s3_storage_enable))
        {
        $result = boolean_convert($s3_storage_enable, 'yes');
        }
    else
        {
        $result = $lang['error'];
        }
    ?><tr><td colspan="2"><?php echo $lang['s3_text'];?></td><td><b><?php echo $result;?></b></td></tr><?php

    // AWS PHP SDK check.
    if($s3_storage_enable)
        {
        $result = s3_get_api();
        }
    else
        {
        $result = $lang['status-fail'];
        }
    ?><tr><td colspan="2"><?php echo $lang['aws_php_sdk'];?></td><td><b><?php echo $result['status'];?></b></td></tr><?php

    // PHP cURL extension check.
    $extension = 'curl';
    $curl_version = curl_version();
    if(extension_loaded($extension))
        {
        $result = $lang['status-ok'];
        }
    else
        {
        $result = $lang['status-fail'];
        }
    ?><tr><td>PHP-<?php echo $extension . ' ' . $lang['php_extension'] . ' ' . $lang['installed'] . '?';?></td><td><?php echo 'v' . $curl_version['version'] . ', ' . $curl_version['ssl_version'];?></td><td><b><?php echo $result;?></b></td></tr><?php

    // PHP OPCache extension check.
    $extension = 'Zend OPcache';
    if(extension_loaded($extension))
        {
        $result = opcache_get_configuration();
        $result2 = formatfilesize($result['directives']['opcache.memory_consumption']);
        $result1 = $result['version']['version'] . ', ' . $lang['opcache_memory'] . $result2;
        $result3 = boolean_convert($result['directives']['opcache.enable'], 'true');
        }
    else
        {
        $result = '';
        $result1 = '';
        $result3 = boolean_convert(false, 'true');
        }
    ?><tr><td>PHP-<?php echo $extension . ' ' . $lang['php_extension'] . ' ' . $lang['installed'] . '?';?></td><td><?php echo 'v' . $result1;?></td><td><b><?php echo $result3;?></b></td></tr><?php

    // PHP Xdebug extension check.
    if(!extension_loaded('xdebug'))
        {
        $result = $lang['status-ok'];
        }
    else
        {
        $result = $lang['status-fail'];
        }
    ?><tr><td>PHP-<?php echo $lang['xdebug_extension'] . ' ' . $lang['php_extension'] . ' ' . $lang['installed'] . '?';?></td><td><?php echo boolean_convert(extension_loaded('xdebug'), 'true');?></td><td><b><?php echo boolean_convert($result, 'ok');?></b></td></tr><?php

    // Separated filestore check.
    $result = boolean_convert($originals_separate_storage, 'yes');
    ?><tr><td><?php echo $lang['filestore_type2'] . " (\$originals_separate_storage = true)?";?></td><td><?php echo $result;?></td><td><b><?php echo $lang['status-ok'];?></b></td></tr><?php

    // Storage directory (filestore) check.
    if(isset($storagedir) && !stristr($storagedir, 'include'))
        {
        $storagedir_text = $storagedir;
        $result = $lang['status-ok'];
        }
    else
        {
        $storagedir_text = $lang['status-fail'];
        $result = $lang['status-fail'];
        }
    ?><tr><td><?php echo $lang['s3_storage_directory'] . " (\$storagedir)?";?></td><td><?php echo $storagedir_text;?></td><td><b><?php echo $result;?></b></td></tr><?php

    // Purge filestore tmp folder age check.
    $result = isset($purge_temp_folder_age);
    if($result)
        {
        $result = ($result == true) ? $lang['status-ok'] : $lang['status-fail'];
        $result1 = ($purge_temp_folder_age == 1) ? $lang['expire_day'] : $lang['expire_days'];
        }
    else
        {
        $result = $lang['status-fail'];
        $result1 = $lang['error'];
        $purge_temp_folder_age = '';
        }
    ?><tr><td><?php echo $lang['purge_temp_folder_age'] . " (\$purge_temp_folder_age):"; ?></td><td><?php echo $purge_temp_folder_age . ' ' . $result1;?></td><td><b><?php echo $result;?></b></td></tr><?php

    // ResourceSpace configuration parameters check.
    if($exiftool_write && $exiftool_write_option && $force_exiftool_write_metadata && !$custompermshowfile)
        {
        $result = $lang['status-ok'];
        }
    else
        {
        $result = $lang['status-fail'];
        }

    $result2 = boolean_convert($exiftool_write, 'true') . parameter_check($exiftool_write, true, true);
    $result3 = boolean_convert($exiftool_write_option, 'true') . parameter_check($exiftool_write_option, true, true);
    $result4 = boolean_convert($force_exiftool_write_metadata, 'true') . parameter_check($force_exiftool_write_metadata, true, true);
    $result5 = boolean_convert($custompermshowfile, 'true') . parameter_check($custompermshowfile, false, true);
    ?><tr><td><?php echo $lang['rs_parameters_check'];?></td><td><?php echo $lang['exiftool_write'] . $result2;?><br/><?php echo $lang['exiftool_write_option'] . $result3;?><br/><?php echo $lang['force_exiftool_write_metadata'] . $result4 ?><br/><?php echo $lang['custompermshowfile'] . $result5;?></td><td><b><?php echo $result;?></b></td></tr><?php

    // S3 storage provider check.
    if(isset($s3_storage_provider))
        {
        $result = $s3_storage_provider;
        $status = true;
        }
    else
        {
        $result = $lang['error'];
        $status = false;
        }
    ?><tr><td><?php echo $lang['s3_provider'];?></td><td><?php echo $result; ?></td><td><b><?php echo boolean_convert($status, 'ok');?></b></td></tr><?php

    // Key pair set check?
    if($s3_storage_key != '')
        {
        $result = boolean_convert(true, 'yes');
        }
    else
        {
        $result = boolean_convert(false, 'yes');
        }

    if($s3_storage_secret != '')
        {
        $result1 = boolean_convert(true, 'yes');
        }
    else
        {
        $result1 = boolean_convert(false, 'yes');
        }

    if($s3_storage_key != '' && $s3_storage_secret != '')
        {
        $result2 = true;
        }
    else
        {
        $result2 = false;
        }
    ?><tr><td><?php echo $lang['s3_keypair'];?></td><td><?php echo $result . ' / ' . $result1;?></td><td><b><?php echo boolean_convert($result2, 'ok');?></b></td></tr><?php

    // S3 storage endpoint check.
    $result = s3_get_endpoint();
    if(!$result)
        {
        $result['endpoint'] = '';
        }
    ?><tr><td><?php echo $lang['s3_endpoint'];?></td><td><?php echo $result['scheme'] . '://' . $result['endpoint'];?></td><td><b><?php echo boolean_convert($result['status'], 'ok');?></b></td></tr><?php

    // S3 bucket accessibility check.
    $result = s3_bucket_head($s3_storage_bucket);
    $result = boolean_convert($result, 'ok');
    ?><tr><td><?php echo $lang['s3_bucket_access']; ?></td><td><?php echo $s3_storage_bucket; ?></td><td><b><?php echo $result?></b></td></tr><?php

    // Get S3 bucket region location.
    $result = s3_bucket_location($s3_storage_bucket);
    if($result['status'] == '')
        {
        $result['LocationConstraint'] = '';
        $result['status'] = false;
        }
    ?><tr><td><?php echo $lang['s3_region'];?></td><td><?php echo $result['LocationConstraint'];?></td><td><b><?php echo boolean_convert($result['status'], 'ok');?></b></td></tr><?php

    // Get S3 bucket owner.
    $result = s3_bucket_owner($s3_storage_bucket);
    if($result == false)
        {
        $result['status'] = $lang['status-fail'];
        $result['name'] = '';
        $result['id'] = '';
        }

    ?><tr><td><?php echo $lang['s3_owner']; ?></td><td><?php echo $result['name']; ?></td><td><b><?php echo $result['status']; ?></b></td></tr><?php
    ?><tr><td><?php echo $lang['s3_id']; ?></td><td><?php echo $result['id'];?></td><td><b><?php echo $result['status'];?></b></td></tr><?php

    // Get S3 bucket storage class.
    if(isset($s3_storage_class))
        {
        $result = s3_storage_class($s3_storage_class);
        }
    else
        {
        $result['name'] = $lang['status-fail'];
        }
    ?><tr><td><?php echo $lang['s3_storage_class']; ?></td><td><?php echo $result['name'];?></td><td><b><?php echo $result['status'];?></b></td></tr><?php

    // Get the S3 transfer statistics parameter.
    if(isset($s3_storage_stats))
        {
        $result = boolean_convert($s3_storage_stats, 'yes');
        }
    else
        {
        $result = $lang['no'];
        }
    ?><tr><td colspan="2"><?php echo $lang['s3_stats'];?></td><td><b><?php echo $result;?></b></td></tr>
</table>
</div><?php

include '../../../../include/footer.php';


function parameter_check($parameter, $value, $add_para)
    {
    global $lang;

    if($parameter == $value)
        {
        $result = $lang['status-ok'];
        }
    else
        {
        $result = $lang['status-fail'];
        }

    if($add_para)
        {
        $result = ' (' . $result . ')';
        }

    return $result;
    }
