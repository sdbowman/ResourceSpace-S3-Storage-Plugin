<?php
// s3_storage Plugin collection_download.php Hooks File
// This file adds hook functions to the ../pages/collection_download.php page.

include_once '../plugins/s3_storage/include/s3_storage_functions.php';


/**
* Hook to add introductory text to the ../pages/collection_download.php page.
*
* @param
*
* @return
*/
function HookS3_storageCollection_downloadCollectiondownloadintro()
    {
    global $lang, $totalsize;

    // Add the collection_download page intro text.
    ?><p><?php echo $lang['s3_storage_cdownload_introtext'];?></p><?php
    }


/**
* Hook to modify the download file formats on the ../pages/collection_download.php page.
*
* @param
*
* @return
*/
function HookS3_storageCollection_downloadCollectiondownloadfileformats()
    {
    global $lang, $collection_download_tar_option;
    ?>
    <option value="off"><?php echo $lang['s3_storage_cdownload_format'];?></option>
    <option value="on" <?php if($collection_download_tar_option) { echo "selected"; } ?> ><?php echo $lang['collection_download_use_tar'];?></option><?php

    return true;
    }


/**
* Hook to hide the download file format help text box on the ../pages/collection_download.php page.
*
* @param
*
* @return
*/
function HookS3_storageCollection_downloadCollectiondownloadformhelp()
    {
    return true;
    }


/**
* Hook to use the file size value in existing resource table for file.
*
* @param string $path  Path to local filestore file to upload to S3 storage.
*
* @return integer      File size.
*/
function HookS3_storageCollection_downloadDownload_totalsize($ref)
    {
    $ref_data = get_resource_data($ref);
    debug('HOOK COLLECTION_DOWNLOAD_FILESIZE: ' . $ref_data['file_size']);

    return $ref_data['file_size'];
    }

/**
* Hook to prevent copying the file from the filestore rather than renaming.
*
* @return
*/
function HookS3_storageCollection_downloadCollection_download_copy()
    {
    debug('HOOK COLLECTION_DOWNLOAD_COPY');
    }


/**
* Hook to replace the collection download size options.
*
* @return
*/
function HookS3_storageCollection_downloadReplacesizeoptions()
    {
    global $lang, $count_data_only_types, $result_count, $collection, $maxaccess, $available_sizes, $submitted;

    if($count_data_only_types !== $result_count)
        { ?>
        <div class="Question">
            <label for="downloadsize"><?php echo $lang['s3_storage_cdownload_size'];?></label>
        <div class="tickset"><?php

        $maxaccess = collection_max_access($collection);
        $sizes = get_all_image_sizes(false, $maxaccess >= 1);
        $available_sizes = array_reverse($available_sizes, true);

        // Analyze the available sizes and present options.
        ?><select name="size" class="stdwidth" id="downloadsize"<?php if(!empty($submitted)) echo ' disabled="disabled"' ?>><?php
        }

    // Display size options.
    if(array_key_exists('original', $available_sizes))
        {
        display_size_option('original', $lang['original'], true);
        }

    foreach($available_sizes as $key => $value)
        {
        foreach($sizes as $size)
            {
            if($size['id'] == $key)
                {
                display_size_option($key, $size['name'], true);
                break;
                }
            }
        }
    ?></select>

    <div class="clearerleft"></div></div>
    <div class="clearerleft"></div></div><?php

    return true;
    }


/**
* Hook to check the Use Original if Selected Size is Unavailable by default.
*
* @return
*/
function HookS3_storageCollection_downloadReplaceuseoriginal()
    {
    global $lang, $count_data_only_types, $result_count, $s3_storage_original_default;

    debug('HOOK COLLECTION_DOWNLOAD replaceuseoriginal');
    if($count_data_only_types !== $result_count)
        { ?>
        <div class="Question">
            <label for="use_original"><?php echo $lang['s3_storage_cdownload_original'];?> <br/><?php
            display_size_option('original', $lang['original'], false);
            if($s3_storage_original_default)
                {
                ?></label><input type=checkbox id="use_original" name="use_original" value="yes" checked><?php
                }
            else
                {
                ?></label><input type=checkbox id="use_original" name="use_original" value="yes"><?php
                }
        ?>
        <div class="clearerleft"> </div></div><?php
        }

    return true;
    }


/**
* Hook to replace the collection download text file option.
*
* @return
*/
function HookS3_storageCollection_downloadcollectiondownloadtextfile()
    {
    global $lang, $zipped_collection_textfile_default_no;
    ?>
    <div class="Question">
        <label for="text"><?php echo $lang['s3_storage_cdownload_textfile'];?></label><?php
            if($zipped_collection_textfile_default_no)
                { ?>
                <input type="checkbox" id="text" name="text" value="true"><?php
                }
            else
                { ?>
                <input type="checkbox" id="text" name="text" value="true" checked><?php
                } ?>
        <div class="clearerleft"></div>
    </div><?php

    return true;
    }


/**
* Hook to include CSV metadata by default.
*
* @return
*/
function HookS3_storageCollection_downloadReplacecsvfile()
    {
    global $lang, $s3_storage_csv_default;

    ?>
    <!-- Add CSV file with the metadata of all the resources found in this colleciton -->
    <div class="Question">
        <label for="include_csv_file"><?php echo $lang['s3_storage_cdowbload_csvfile'];?></label><?php
        if($s3_storage_csv_default)
            { ?>
            <input type="checkbox" id="include_csv_file" name="include_csv_file" value="yes" checked><?php
            }
        else
            { ?>
            <input type="checkbox" id="include_csv_file" name="include_csv_file" value="yes"><?php
            }
        ?>
        <div class="clearerleft"></div>
    </div><?php

    return true;
    }


/**
* Hook to include CSV metadata by default.
*
* @return
*/
function HookS3_storageCollection_downloadReplacecollectiontextfile($collectiondata)
    {
    global $lang, $text, $baseurl;

    $text = $lang['s3_storage_cdownload_header'] . ' ' . strtoupper(i18n_get_collection_name($collectiondata) . ' ' . $lang['archive'] . ' ' . $lang['file']) . "\r\n";
    $text .= $lang['downloaded'] . ' ' . $lang['on_date'] . ' ' . nicedate(date("Y-m-d H:i:s"), true, true) . "\r\n";
    $text .= $lang['from'] . ' ' . $baseurl . "\r\n";

    if($collectiondata['keywords'] != '')
        {
        $text .= $lang['fieldtitle-keywords'] . ': ' . $collectiondata['keywords'] . "\r\n";
        $text .= wordwrap($lang['fieldtitle-keywords'] . ': ' . $collectiondata['keywords'] . "\r\n", 65);
        }

    if($collectiondata['description'] != '')
        {
        $text .= wordwrap($lang['description'] . ': ' . $collectiondata['description'] . "\r\n", 65);
        }

    $text .= "\r\n";
    $text .= $lang['s3_storage_cdownload_contents'] . "\r\n\r\n";

    return $text;
    }
