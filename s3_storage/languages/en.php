<?php
// s3_storage Plugin English Language File
// This file contains the English language $lang variables.

// Plugin setup page text settings.
$lang['s3_storage_configuration'] = 'Simple Storage Service (S3) Storage Configuration';
$lang['s3_storage_introtext'] = "Object-based storage often has a lower cost than traditional block-based file storage. While the cloud-based Simple Storage Service (S3) was originally developed by Amazon Web Services (AWS), it is also provided by numerous other cloud-based vendors and local appliances.  When enabled, ResourceSpace will store the original resource and alternative files in a specified S3 bucket, with the derived preview images stored locally on your server in the same ../filestore location as before. For more information on using object-based S3 storage, see <a href=\"https://en.wikipedia.org/wiki/Object_storage\">https://en.wikipedia.org/wiki/Object_storage</a> and <a href=\"https://en.wikipedia.org/wiki/Amazon_S3\">https://en.wikipedia.org/wiki/Amazon_S3</a>.<br/>
<br/>
Storage of original resource files is in a specified S3 bucket (a container for file objects).  Preview and other resized images are stored in the existing ../filestore/ location as before.  Object-based storage systems do not use the concept of a folder or directory structure, as each file stored in them is referred to as an object.  However, the object name can contain the desired path structure, preserving the original folder structure used elsewhere. For this plugin, original resource files stored in S3 are named by their unique ResourceSpace pathname after the ../filestore folder name to preserve the filestore structure for easy conversion from one storage system to another and to help reduce vendor and/or system lock in.<br/>
<br/>
Additional S3 storage providers and/or S3 storage classes can be added in the configuration arrays in the code for this page. You will also need to update the s3_storage_setup() and s3_get_endpoint() functions in ../plugins/s3_storage/include/s3_storage_functions.php with any custom S3 vendor settings at a minimum.<br/>
<br/>
Before uploading any resources (files), ensure that fields below are correctly configured and use the <b>S3 Installation Check</b> at <i>Admin, System, S3 Installation Check</i>. If any lines indicate FAIL, as a start, check that your server date and time are correct. If your server time is different than your S3 provider over than about 15 minutes, a provider error will be generated. Use your S3 provider bucket management system to verify the correct uploads are occuring for a few test resource uploads. For AWS, the <b>AWS S3 Management Console</b> (<a target=\"_blank\" href=\"https://s3.console.aws.amazon.com/s3\">https://s3.console.aws.amazon.com/s3</a>) should be used and the <b>AWS Billing & Cost Management Dashboard</b> (<a target=\"_blank\" href=\"https://console.aws.amazon.com/billing\">https://console.aws.amazon.com/billing</a>) can be used to understand S3 storage and usage costs.";

$lang['s3_storage_rs_parameters_header'] = 'Required ResourceSpace Configuration Parameters';
$lang['s3_storage_storagedir'] = 'Full absolute path to the ../filestore folder:';
$lang['s3_storage_purge_temp_age'] = 'Time to clear the ../filestore/tmp folder (days):';

$lang['s3_storage_intro_header'] = 'Main S3 Storage Settings';
$lang['s3_storage_enable'] = 'Enable object-based S3 storage?';
$lang['s3_storage_temp_purge'] = 'Time to clear the S3 temp folder (days):';
$lang['s3_storage_mpupload_gc'] = 'Run the PHP Garbage Collector before attempting Multipart Uploads?';
$lang['s3_storage_resource_types'] = 'Resource types not using S3 storage (for special use cases and will require the url_download plugin or other customization):';

$lang['s3_storage_collection_download'] = 'Collection Download Settings';
$lang['s3_storage_cdownload_enable'] = 'Enable downloads of entire collections?';
$lang['s3_storage_cdownload_name'] = 'Use the collection name in the downloaded archive filename?';
$lang['s3_storage_original_default'] = 'Default: Download resource original if size is unavailable?';
$lang['s3_storage_csv_default'] = 'Default: Include CSV metadata file?';
$lang['s3_storage_collection_prefix'] = 'Collection download archive filename prefix:';
$lang['s3_storage_cdownload_header'] = 'RESOURCE METADATA FOR COLLECTION';
$lang['s3_storage_cdownload_contents'] = '---ARCHIVE FILE CONTENTS---';
$lang['s3_storage_cdownload_tarsize'] = 'Force TAR archives for total sizes above this value in MB (0 to disable):';
$lang['forthispackage'] = 'for this archive file';
$lang['s3_storage_with'] = 'with';
$lang['s3_storage_cdownload_introtext'] = "Set the options below to download the entire collection as a ZIP or TAR archive. For large collections, the TAR format is highly recommended. The original download size is the full-resolution file and the screen download size is a preview quality image file. Checking the include text file box will include a text file listing each file in the download archive and its available metadata.  Checking the include metadata CSV file box will include a CSV file with each row representing an individual file in the download archive and its available metadata.<br/>
<br/>
The open-source 7-zip software (<a href=\"http://www.7-zip.org\"target=\"_blank\">http://www.7-zip.org</a>) can be used to open and uncompress ZIP and TAR archives on most operating systems.";
$lang['downloadzip'] = 'Download a Collection as a Single-File Archive';
$lang['s3_storage_cdownload_format'] = 'ZIP Archive';
$lang['s3_storage_cdownload_size'] = 'Resource download available sizes:';
$lang['s3_storage_cdownload_original'] = 'Use the resource original if the selected size is not available?';
$lang['s3_storage_cdownload_textfile'] = 'Include a text file with resource metadata?';
$lang['s3_storage_cdowbload_csvfile'] = 'Include a CSV file with resource metadata?';
$lang['s3_storage_cdownload_totalsize'] = 'Collection download estimated totalsize:';

$lang['s3_storage_log_header'] = 'S3 Storage Debug Logging Settings';
$lang['s3_storage_enable_stats'] = 'Enable adding transfer stastistics to S3 errors and results when $debug_log = true?';
$lang['s3_storage_setup_debug'] = "Add function 's3_storage_setup' results to the Debug Log (allows suppressing extra data in the log)?";

$lang['s3_storage_provider_header'] = 'S3 Storage Provider Settings';
$lang['s3_storage_provider'] = 'S3 storage provider name:';
$lang['s3_storage_endpoint'] = 'S3 storage provider endpoint URL (only needed if not using AWS S3):';
$lang['s3_storage_path_endpoint'] = 'Use a path-style endpoint? (only needed if not using AWS S3)';
$lang['s3_storage_region'] = 'S3 storage provider region code:';

$lang['s3_storage_security_header'] = 'S3 Storage Security Settings';
$lang['s3_storage_key'] = 'Security access key:';
$lang['s3_storage_secret'] = 'Security secret key:';

$lang['s3_storage_bucket_header'] = 'S3 Storage Bucket Settings';
$lang['s3_storage_bucket'] = 'S3 bucket name:';
$lang['s3_storage_class'] = 'S3 storage class:';

$lang['s3_storage_end_header'] = 'Final Configuration Information';

// S3 providers.
$lang['s3_storage_aws'] = 'Amazon Web Services (AWS)';
$lang['s3_storage_digitalocean'] = 'DigitalOcean';
$lang['s3_storage_other'] = 'Other';

// S3 regions.
$lang['aws_us-east-1'] = 'AWS US East 1 (Northern Virginia)';
$lang['aws_us-east-2'] = 'AWS US East 2 (Ohio)';
$lang['aws_us-west-1'] = 'AWS US West 1 (Northern California)';
$lang['aws_us-west-2'] = 'AWS US West 2 (Oregon)';
$lang['aws_ca-central-1'] = 'AWS Canada (Central)';
$lang['aws_eu-central-1'] = 'AWS Europe (Frankfurt)';
$lang['aws_eu-north-1'] = 'AWS Europe (Stockholm)';
$lang['aws_eu-west-1'] = 'AWS Europe (Ireland)';
$lang['aws_eu-west-2'] = 'AWS Europe (London)';
$lang['aws_eu-west-3'] = 'AWS Europe (Paris)';
$lang['aws_eu-south-1'] = 'AWS Europe (Milan)';
$lang['aws_af-south-1'] = 'AWS Africa (Cape Town)';
$lang['aws_ap-east-1'] = 'AWS Asia Pacific (Hong Kong)';
$lang['aws_ap-south-1'] = 'AWS Asia Pacific (Mumbai)';
$lang['aws_ap-northeast-2'] = 'AWS Asia Pacific (Seoul)';
$lang['aws_ap-northeast-3'] = 'AWS Asia Pacific (Osaka-Local)';
$lang['aws_ap-southeast-1'] = 'AWS Asia Pacific (Singapore)';
$lang['aws_ap-southeast-2'] = 'AWS Asia Pacific (Sydney)';
$lang['aws_me-south-1'] = 'AWS Middle East (Bahrain)';
$lang['aws_sa-east-1'] = 'AWS South America (Sao Paulo)';
$lang['do_SFO1'] = 'DigitalOcean San Francisco (SFO1)';
$lang['do_LON1'] = 'DigitalOcean London (LON1)';

// S3 storage classes.
$lang['s3_storage_class_standard'] = 'Standard';
$lang['s3_storage_class_intelligent_tiering'] = 'Intelligent Tiering';
$lang['s3_storage_class_standard_ia'] = 'Standard IA';
$lang['s3_storage_class_onezone_ia'] = 'One Zone IA';
$lang['s3_storage_class_reduced_redundancy'] = 'Reduced Redundancy';
$lang['s3_storage_class_outposts'] = 'Outposts';
$lang['s3_storage_class_other'] = 'Other';

// ResourceSpace Installation Check text settings.
$lang['s3_storage'] = 'Simple Storage Service (S3) Based Original Filestore';
$lang['s3_endpoint_region'] = 'Endpoint region: ';
$lang['s3_bucket'] = 'Bucket: ';

// S3 Installation Check text settings.
$lang['s3_check'] = 'S3 Installation Check';
$lang['s3_check_title'] = 'Simple Storage Service (S3) Based Storage Installation Check';
$lang['s3_check_introtext'] = 'Simple Storage Service (S3) object-based storage often has a lower cost than traditional block-based file storage and is provided by numerous cloud-based vendors.  When enabled, ResourceSpace will store the original resource files in S3 with the derived preview images stored locally on your server.
</br><br/>
Before uploading any files, ensure that all fields below do not indicate FAIL and that your ResourceSpace ../include/config.php parameters are set correctly.  If S3 indicates FAIL, as a start, check that your server time is correct. Use your S3 provider bucket management system to verify the correct uploads are occuring for a few test resource uploads.  For AWS, the AWS S3 Management Console (<a target="_blank" href="https://s3.console.aws.amazon.com/s3">https://s3.console.aws.amazon.com/s3</a>) should be used and the AWS Billing & Cost Management Dashboard (<a target="_blank" href="https://console.aws.amazon.com/billing">https://console.aws.amazon.com/billing</a>) can be used to understand the S3 storage and usage costs.';

$lang['aws_php_sdk'] = 'AWS PHP SDK S3 Status (SDK used to access AWS S3 and S3-compatible services):';
$lang['php_extension'] = 'PHP extension';
$lang['opcache_memory'] = 'Memory Consumption: ';
$lang['xdebug_extension'] = 'Xdebug';
$lang['s3_text'] = 'Enable and use S3 object-based original file filestore?';
$lang['filestore_type2'] = 'Using an original file separated filestore';
$lang['installed'] = 'installed';
$lang['s3_storage_directory']  = 'Is the local storage directory set ';
$lang['purge_temp_folder_age'] = 'Purge temp folder age';
$lang['rs_parameters_check'] = 'ResourceSpace parameters check:';
$lang['exiftool_write'] = "\$exiftool_write: ";
$lang['exiftool_write_option'] = "\$exiftool_write_option: ";
$lang['force_exiftool_write_metadata'] = "\$force_exiftool_write_metadata: ";
$lang['custompermshowfile'] = "\$custompermshowfile: ";
$lang['s3_provider'] = 'S3 Storage provider: ';
$lang['s3_keypair'] = 'Is the security key pair (key / secret) set?';
$lang['s3_endpoint'] = 'S3 Provider endpoint URL:';
$lang['s3_bucket_access'] = 'Is the S3 bucket accessible?';
$lang['s3_region'] = 'S3 Bucket located in region: ';
$lang['s3_owner'] = 'S3 Bucket owner: ';
$lang['s3_id'] = 'S3 Bucket owner ID: ';
$lang['s3_stats'] = 'Enable adding transfer stastistics to S3 errors and results when $debug_log = true?';

// S3 Dashboard text settings.
$lang['s3_dashboard'] = 'S3 Storage Dashboard';
$lang['s3_dashboard_title'] = 'Simple Storage Service Dashboard';
$lang['s3_dashboard_introtext'] = 'Simple Storage Service (S3) object-based storage often has a lower cost than traditional block-based file storage and is provided by numerous cloud-based vendors.  When enabled, ResourceSpace will store the original resource files in S3 with the derived preview images stored locally on your server.';

$lang['s3_dashboard_parameters'] = 'S3 Provider and Bucket Parameters';
$lang['s3_api'] = 'S3 API: ';

$lang['cw_title'] = 'Amazon Web Services (AWS) CloudWatch S3 Metrics';
$lang['s3_bucket_size'] = 'Total Bucket Size (Last day / Last 30 day average): ';
$lang['s3_object_number'] = 'Total Number of Objects in the Bucket (Last day / Last 30 day average): ';
$lang['s3_uploaded_size'] = 'Files Uploaded Total Size (Last day / Last 30 days): ';
$lang['s3_downloaded_size'] = 'Files Download Total Size (Last day / Last 30 days): ';
$lang['s3_first_request'] = 'Average Latency of the First HTTP Request (Last day / Last 30 day average): ';
$lang['s3_total_request'] = 'Average Latency of the Total HTTP Request (Last day / Last 30 day average): ';
$lang['s3_all_requests'] = 'Total Number of HTTP Requests (Last day / Last 30 days): ';
$lang['s3_head_requests'] = 'Total Number of HTTP HEAD Requests (Last day / Last 30 days): ';
$lang['s3_list_requests'] = 'Total Number of HTTP LIST Requests (Last day / Last 30 days): ';
$lang['s3_get_requests'] = 'Total Number of HTTP GET Requests (Last day / Last 30 days): ';
$lang['s3_put_requests'] = 'Total Number of HTTP PUT Requests (Last day / Last 30 days): ';
$lang['s3_delete_requests'] = 'Total Number of HTTP DELETE Requests (Last day / Last 30 days): ';
$lang['s3_4xx_errors'] = 'Total Number of HTTP 4xx Errors (Last day / Last 30 days): ';
$lang['s3_5xx_errors'] = 'Total Number of HTTP 5xx Errors (Last day / Last 30 days): ';
$lang['cw_notes'] = 'CloudWatch metrics are posted once each day; as a result, data may not be accurate at the date and time of the page load, a ? value indicates that specific metric is not currently available';

// S3 download text settings.
$lang['s3_download_text'] = 'Downloading file from long-term storage, please stand by.';

// S3 upload text settings.
$lang['s3_upload'] = ' to S3 storage';
$lang['plupload_log'] = 'Upload log';
$lang['plupload_log_intro'] = 'Upload Summary (server time: ';
$lang['s3_upload_log_text'] = 'Uploading original file to S3 storage.';
$lang['created2'] = 'Created Resource ID';

// S3 delete text settings.
$lang['deletefilecheck'] = 'Filestore delete file check';
$lang['deletefilecheck_s3'] = 'Filestore and S3 delete file check';
$lang['deletefilechecktext'] = 'Checking the server filestore for remaining folders and files.';
$lang['deletefilechecktext_s3'] = 'Checking the server filestore and S3 bucket for remaining folders and files.';
$lang['resource_delete'] = 'Error: Resource deletion is disabled.';

// S3 script tool text settings.
$lang['cli_error'] = 'Must use the command line interface to run this script tool.';
$lang['permission_denied'] = 'Permission denied.';

// Other text settings.
$lang['milliseconds'] = ' ms';
$lang['filestore'] = 'Filestore';
$lang['purge_temp_folder_age'] = 'Purge temp folder age';
$lang['exiftool_write_option'] = 'Require ExifTool metadata writing on resource download ($exiftool_write_option = true)? ';
$lang['input_error'] = 'Input error';
$lang['in'] = 'in';
