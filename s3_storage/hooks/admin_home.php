<?php
// s3_storage admin_home.php Hooks File
// This file adds hook functions to the ../pages/admin/admin_home.php page.

function HookS3_storageAdmin_homeCustomadminfunction()
    {
    global $s3_storage_enable, $baseurl, $lang;

    if($s3_storage_enable)
        { ?>
        <li><i aria-hidden="true" class="fa fa-fw fa-download"></i>&nbsp;<a href="<?php echo $baseurl;?>/plugins/s3_storage/pages/admin/admin_system_s3.php" onClick="return CentralSpaceLoad(this, true);"><?php echo $lang['s3_check'];?></a></li>
        <li><i aria-hidden="true" class="fa fa-fw fa-download"></i>&nbsp;<a href="<?php echo $baseurl;?>/plugins/s3_storage/pages/admin/admin_system_s3dashboard.php" onClick="return CentralSpaceLoad(this, true);"><?php echo $lang['s3_dashboard'];?></a></li><?php
        }
    }
