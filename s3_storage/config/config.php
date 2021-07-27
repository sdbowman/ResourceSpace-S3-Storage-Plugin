<?php
// s3_storage Plugin Configuration File
// This file contains the initial plugin variable definitions.

include_once __DIR__ . '/../include/s3_storage_functions.php';

/* See the plugin setup page (../plugins/s3_storage/pages/setup.php) to add additional storage providers and/or S3
* storage classes.
*/

// Set needed ResourceSpace parameters.
$exiftool_write = true;
$exiftool_write_metadata = true;
$force_exiftool_write_metadata = true;

// Set disabled ResourceSpace functionality, due to features known to not currently work with the s3_storage plugin.
//$collection_download = false;

// Set the AWS PHP S3 and CloudWatch API version.
$s3_api_version = '2006-03-01';
$cw_api_version = '2010-08-01';

// Set the default main S3 storage parameters.
$s3_storage_enable = true;
$s3_storage_temp_purge = 1;
$s3_storage_mpupload_gc = true;
$s3_storage_resource_types = array();

$s3_storage_enable_stats = true;
$s3_storage_setup_debug = false;

// Set the default S3 storage provider parameters.
$s3_storage_provider_text = '<br/>While this plugin uses the AWS S3 API SDK, any S3 provider that is compatible with the AWS S3 API should work. If not using AWS, you will need to set the provider S3 endpoint URL at a minimum. It is usually best to pick a region the bucket will be located in physically closest to your ResourceSpace server to reduce latency.<br/>
<br/>';
$s3_storage_provider = 'AWS';
$s3_storage_endpoint = '';
$s3_storage_path_endpoint = null;
$s3_storage_region = '';

// Set the default S3 security parameters.
$s3_storage_security_option_text = '<br/>To disable web display of the security key/secret pair, change the $s3_storage_security_option = true in the plugin ../plugin/s3_storage/config/config.php file and set the key/secret pair there.  For greater security, you may want to disable the pair display after plugin configuration is complete.  Do NOT share this key pair with anyone or else they will have full access to the S3 bucket and all of its contents (objects).<br/>
<br/>';
$s3_storage_security_option_on_text = '<br/>Web display and entry of the security key/secret pair has been disabled.<br/>';
$s3_storage_security_option = false; # To not show key/secret pair on setup page, change to TRUE and enter pair below.
$s3_storage_key = '';
$s3_storage_secret = '';

// Set the default S3 bucket parameters.
$s3_storage_bucket_text = "<br/>Bucket names and storage classes have provider-specific schema definitions. See your S3 storage provider documentation for more information. Use the default bucket settings and permissions allowing access to the bucket only by your account and no public access. If using AWS, the 'Intelligent Tiering' storage class works best for most use cases. More information is available at <a href=\"https://docs.aws.amazon.com/AmazonS3/latest/dev/storage-class-intro.html\">https://docs.aws.amazon.com/AmazonS3/latest/dev/storage-class-intro.html</a>.<br/>
<br/>";
$s3_storage_bucket = '';
$s3_storage_class = 'INTELLIGENT_TIERING';

// Collection download settings.
$s3_storage_original_default = true;
$s3_storage_csv_default = true;
$s3_storage_collection_prefix = '';

// Set other parameters.
$s3_storage_log_height = 10; # Height of the Upload Log in number of rows.

// Setup page end HTML text.
$s3_storage_endtext = '<br/>Once the S3 storage plugin configuration is complete and saved below, make sure to verify S3 storage connectivity and settings by using the <b>S3 Installation Check</b> at <i>Admin, System, S3 Installation Check</i>. Resolve any lines reporting FAIL before uploaading new resources or converting an existing filestore; otherwise, you may lose files. You can also use the <b>AWS PHP SDK Configuration Check</b> at ../plugins/s3_storage/lib/aws_php_sdk_3.173.22/compatibility-test.php for additional information.<br/>
<br/>
The <b>S3 Storage Dashboard</b> at <i>Admin, System, S3 Storage Dashboard</i> can be used to help manage S3 storage.  The S3 Provider and Bucket Parameters section shows the S3 connection parameters and their status.  If AWS S3 storage is used, the AWS CloudWatch S3 Metrics section shows the total bucket file size, number of stored objects in the current bucket, and other information (metrics) on the bucket and its use.  CloudWatch metrics are posted once each day; as a result, data may not be accurate at the date and time of the page load.  A ? value indicates that specific metric is not currently available.<br/>
<br/>
New files may now be uploaded to ResourceSpace and users allowed to access the system.  There is a slight delay when creating new resources or downloading existing resources, as the original files are stored remotely in S3 storage. Most of the time, this delay is minimal and unless very large files are used, most users will not notice the difference.<br/>
<br/>
If issues arise, check the ResourceSpace <b>Installation Check</b> at <i>Admin, System, Installation Check</i> and the <b>S3 Installation Check</b> pages for any errors, and enable the ResourceSpace debug log in your ../include/config.php by adding $debug_log = true; and set the $debug_log_location parameter.  Check for irregularities, including with S3 operations that have S3 in the name.<br/>
<br/>';
