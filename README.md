# ResourceSpace-S3-Storage-Plugin
A ResourceSpace v9.6 (https://resourcespace.com) plugin to enable S3 object-based storage for original resource files.

Place in the ../plugins folder and enable via the Admin/System/Manage Plugins page.

Object-based storage often has a lower cost than traditional block-based file storage. While the cloud-based Simple Storage Service (S3) was originally developed by Amazon Web Services (AWS), it is also provided by numerous other cloud-based vendors and local appliances.  The plugin incorporates support for S3 storage using the AWS SDK for PHP Version 3 (https://aws.amazon.com/sdk-for-php/, Apache License v2.0) to enable S3 API support. Since AWS developed the S3 object-based storage API, the AWS SDK was used for maximum compatibility. Other provider APIs could be integrated; however, many of these reuse the AWS API code. ResourceSpace (RS) can connect to and use S3 storage from AWS, DigitalOcean, and other S3-compliant storage providers or appliances. As this is an open-source project, the additional RS code has been made
provider agnostic, not favoring one provider over another.  When enabled, ResourceSpace will store the original resource and alternative files in a specified S3 bucket, with the derived preview images stored locally on your server in the same ../filestore location as before. For more information on using object-based S3 storage, see https://en.wikipedia.org/wiki/Object_storage and https://en.wikipedia.org/wiki/Amazon_S3.

Users of your ResourceSpace system have no access to S3 storage or the files (objects) stored there. ResourceSpace natively handles all of the connections and file management using AWS APIs for enhanced security and transfers between the local server and the S3 provider are encrypted by default.

Storage of original resource files is in a specified S3 bucket (a container for file objects).  Preview and other resized images are stored in the existing ../filestore/ location as before.  Object-based storage systems do not use the concept of a folder or directory structure, as each file stored in them is referred to as an object.  However, the object name can contain the desired path structure, preserving the original folder structure used elsewhere. For this plugin, original resource files stored in S3 are named by their unique ResourceSpace pathname after the ../filestore folder name to preserve the filestore structure for easy conversion from one storage system to another and to help reduce vendor and/or system lock in.  There is no limit to the number of files or the total file size stored in an AWS S3 bucket (other providers
may be different); however, individual files are limited to a maximum size of 5 TB each. Individual files are not able to be uploaded in ResourceSpace greater than 5 TB when AWS S3 storage is used.

Additional S3 storage providers and/or S3 storage classes can be added in the configuration arrays in the code. You will also need to update the s3_storage_setup() and s3_get_endpoint() functions in ../plugins/s3_storage/include/s3_storage_functions.php with any custom S3 vendor settings at a minimum.

Before uploading any resources (files), ensure that fields below are correctly configured and use the S3 Installation Check at Admin, System, S3 Installation Check. If any lines indicate FAIL, as a start, check that your server date and time are correct. If your server time is different than your S3 provider over than about 15 minutes, a provider error will be generated. Use your S3 provider bucket management system to verify the correct uploads are occuring for a few test resource uploads. 

Original Resource File S3 Storage Setup

1. Verify your PHP web server meets ResourceSpace and AWS (for the PHP SDK) requirements that include PHP v7.0 or later, the SimpleXML PHP extension, and cURL v7.16.2 or later. If your web server does not meet these requirements, you will not be able to use S3 storage in ResourceSpace, so stop here. It is highly recommended to use the PHP OPCache extension for performance.

2. You will need to setup an account with your S3 provider to create an use a S3 bucket.  

3. Setup a new S3 bucket with your provider.  Bucket names are globally unique within a S3 system, so several tries may be needed to determine a unique name.  It is usually best to pick a region the bucket will be located in physically closest to your ResourceSpace server to reduce latency.  Use the default bucket settings and permissions allowing access to the bucket only by your account and no public access.

4. Create an security Access Key pair. Once an Access Key pair (access key ID and secret access key) is created, the secret key is not retreivable afterwards, so make sure to save the access key pair file in a secure location. Otherwise, you will need to create a new Access Key pair. Do NOT share this key pair with anyone or else they will have full access to the S3 bucket and all of its contents (objects).

5. Decide on a S3 Storage Class. For AWS and unsure, use INTELLIGENT_TIERING, which works well for most ResourceSpace use cases.

6. Set the following ResourceSpace configuration parameters in the ../include/config.php file:
  $storagedir = ‘’; // Full path to the ../filestore folder.
  $purge_temp_folder_age = x; // Time in days to clear the tmp folder.
  $exiftool_write = true; // Enable ExifTool metadata writing?
  $exiftool_write_metadata = true; // Force Exiftool metadata writing?
  $custompermshowfile = false;

7. Verify S3 storage connectivity and settings by using the S3 Installation Check at Admin, System, S3 Installation Check (http://localhost/resourcespace/pages/admin/admin_system_s3.php). Resolve any lines reporting FAIL before uploading new resources or converting an existing filestore; otherwise, you may lose files.

8. If issues arise, check the ResourceSpace Installation Check (Admin, System, Installation Check; ../pages/check.php) and S3 Installation Check (Admin, System, S3 Installation Check; ../pages/admin/admin_system_s3.php) pages for any errors, and enable the ResourceSpace debug log in ../include/config.php by adding $debug_log = true; and set the $debug_log_location parameter. Check for irregularities, including with S3 operations that have S3 in the name.
