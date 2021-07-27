<?php
// s3_storage Plugin Setup File
// This file creates the plugin configuration page.

global $lang, $baseurl;

include '../../../include/db.php';
include '../../../include/authenticate.php';
if(!checkperm('a'))
    {
    exit($lang['error-permissiondenied']);
    }

// Specify the name of this plugin and the heading and introductory text to display for the page.
$plugin_name = 's3_storage';
$plugin_page_heading = $lang['s3_storage_configuration'];
$plugin_page_introtext = $lang['s3_storage_introtext'];

if(!in_array($plugin_name, $plugins))
    {
    plugin_activate_for_setup($plugin_name);
    }

// Define an array of S3 storage providers to choose from.
$s3_storage_providers = array(
    'AWS' => $lang['s3_storage_aws'],
    'DigitalOcean' => $lang['s3_storage_digitalocean'],
    'Other' => $lang['s3_storage_other']
);

// Define an array of available S3 regions to choose from.
$s3_regions = array(
    'us-east-1' => $lang['aws_us-east-1'],
    'us-east-2' => $lang['aws_us-east-2'],
    'us-west-1' => $lang['aws_us-west-1'],
    'us-west-2' => $lang['aws_us-west-2'],
    'ca-central-1' => $lang['aws_ca-central-1'],
    'eu-central-1' => $lang['aws_eu-central-1'],
    'eu-north-1' => $lang['aws_eu-north-1'],
    'eu-west-1' => $lang['aws_eu-west-1'],
    'eu-west-2' => $lang['aws_eu-west-2'],
    'eu-west-3' => $lang['aws_eu-west-3'],
    'eu-south-1' => $lang['aws_eu-south-1'],
    'af-south-1' => $lang['aws_af-south-1'],
    'ap-east-1' => $lang['aws_ap-east-1'],
    'ap-south-1' => $lang['aws_ap-south-1'],
    'ap-northeast-2' => $lang['aws_ap-northeast-2'],
    'ap-northeast-3' => $lang['aws_ap-northeast-3'],
    'ap-southeast-1' => $lang['aws_ap-southeast-1'],
    'ap-southeast-2' => $lang['aws_ap-southeast-2'],
    'me-south-1' => $lang['aws_me-south-1'],
    'sa-east-1' => $lang['aws_sa-east-1'],
    'sfo1' => $lang['do_SFO1'],
    'lon1' => $lang['do_LON1']
);

// Define an array of available S3 storage classes to choose from.
$s3_storage_classes = array(
    'STANDARD' => $lang['s3_storage_class_standard'],
    'INTELLIGENT_TIERING' => $lang['s3_storage_class_intelligent_tiering'],
    'STANDARD_IA' => $lang['s3_storage_class_standard_ia'],
    'ONEZONE_IA' => $lang['s3_storage_class_onezone_ia'],
    'REDUCED_REDUNDANCY' => $lang['s3_storage_class_reduced_redundancy'],
    'OUTPOSTS' => $lang['s3_storage_class_outposts'],
    'OTHER' => $lang['s3_storage_class_other']
);

// Build the $page_def array of descriptions of each configuration variable the plugin uses.
$page_def[] = config_add_section_header($lang['s3_storage_rs_parameters_header']);
$page_def[] = config_add_text_input('storagedir', $lang['s3_storage_storagedir']);
$page_def[] = config_add_text_input('purge_temp_folder_age', $lang['s3_storage_purge_temp_age'], '', 150);

$page_def[] = config_add_section_header($lang['s3_storage_intro_header']);
$page_def[] = config_add_boolean_select('s3_storage_enable', $lang['s3_storage_enable'], '', 150);
$page_def[] = config_add_text_input('s3_storage_temp_purge', $lang['s3_storage_temp_purge'], '', 150);
$page_def[] = config_add_boolean_select('s3_storage_mpupload_gc', $lang['s3_storage_mpupload_gc'], '', 150);
$page_def[] = config_add_multi_rtype_select('s3_storage_resource_types', $lang['s3_storage_resource_types']);

$page_def[] = config_add_section_header($lang['s3_storage_log_header']);
$page_def[] = config_add_boolean_select('s3_storage_enable_stats', $lang['s3_storage_enable_stats'], '', 150);
$page_def[] = config_add_boolean_select('s3_storage_setup_debug', $lang['s3_storage_setup_debug'], '', 150);

$page_def[] = config_add_section_header($lang['s3_storage_provider_header']);
$page_def[] = config_add_html($s3_storage_provider_text);
$page_def[] = config_add_single_select('s3_storage_provider', $lang['s3_storage_provider'], $s3_storage_providers, true, 420, '', true);
$page_def[] = config_add_text_input('s3_storage_endpoint', $lang['s3_storage_endpoint']);
$page_def[] = config_add_boolean_select('s3_storage_path_endpoint', $lang['s3_storage_path_endpoint'], '', 150);
$page_def[] = config_add_single_select('s3_storage_region', $lang['s3_storage_region'], $s3_regions, true, 420, '', true);

$page_def[] = config_add_section_header($lang['s3_storage_security_header']);
if(!$s3_storage_security_option)
    {
    $page_def[] = config_add_html($s3_storage_security_option_text);
    $page_def[] = config_add_text_input('s3_storage_key', $lang['s3_storage_key']);
    $page_def[] = config_add_text_input('s3_storage_secret', $lang['s3_storage_secret']);
    }
else
    {
    $page_def[] = config_add_html($s3_storage_security_option_on_text);
    }

$page_def[] = config_add_section_header($lang['s3_storage_bucket_header']);
$page_def[] = config_add_html($s3_storage_bucket_text);
$page_def[] = config_add_text_input('s3_storage_bucket', $lang['s3_storage_bucket']);
$page_def[] = config_add_single_select('s3_storage_class', $lang['s3_storage_class'], $s3_storage_classes, true, 420, '', true);

$page_def[] = config_add_section_header($lang['s3_storage_collection_download']);
$page_def[] = config_add_boolean_select('collection_download', $lang['s3_storage_cdownload_enable'], '', 150);
$page_def[] = config_add_boolean_select('use_collection_name_in_zip_name', $lang['s3_storage_cdownload_name'], '', 150);
$page_def[] = config_add_boolean_select('s3_storage_original_default', $lang['s3_storage_original_default'], '', 150);
$page_def[] = config_add_boolean_select('s3_storage_csv_default', $lang['s3_storage_csv_default'], '', 150);
$page_def[] = config_add_text_input('s3_storage_collection_prefix', $lang['s3_storage_collection_prefix'], '', 150);
$page_def[] = config_add_text_input('collection_download_tar_size', $lang['s3_storage_cdownload_tarsize'], '', 150);

$page_def[] = config_add_section_header($lang['s3_storage_end_header']);
$page_def[] = config_add_html($s3_storage_endtext);

// Do the page generation ritual, do not change this section.
$upload_status = config_gen_setup_post($page_def, $plugin_name);
include '../../../include/header.php';
config_gen_setup_html($page_def, $plugin_name, $upload_status, $plugin_page_heading, $plugin_page_introtext);
include '../../../include/footer.php';
