<?php
/** s3_storage Plugin Simple Storage Service (S3) Functions File
* This file contains the required functions for the s3_storage plugin.
* 
* Once the AWS PHP v3.158.6 SDK is loaded via the class autoloader file, the function $s3_storage_setup defines the
* provider endpoint URL and SDK shared configuration parameters for setting up S3 and AWS CloudWatch clients in other
* functions, and then a new SDK class is defined.  The remainder of the s3_storage_functions.php file contains the S3
* storage specific functions.
*
* Future versions of the SDK (https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/getting-started_installation.
* html#installing-by-using-the-zip-file) can be used by saving the specific SDK to a new ../plugins/s3_storage/lib
* folder, deleting the older SDK folder, and changing Line 13 below to the new folder name.  Should a new SDK use a
* new S3 and /or CloudWatch API version, that can be changed in the 'Set the AWS PHP S3 and CloudWatch API version'
* section of ../plugins/config/config.php.
*/

// Load the AWS PHP SDK Version 3 class autoloader and ResourceSpace ../include/db.php file.
require dirname(__FILE__) . '/../lib/aws_php_sdk_3.173.22/aws-autoloader.php';
use Aws\S3\S3Client;
use Aws\S3\S3ClientInterface;
use Aws\ResultInterface;
use Aws\CloudWatch\CloudWatchClient;
use Aws\Exception\AwsException;
use Aws\S3\Exception\S3Exception;
use Aws\S3\ObjectUploader;
use Aws\S3\ObjectCopier;

include_once dirname(__FILE__) . '/../../../include/db.php';


/**
* Function to check that the required ResourceSpace S3 storage parameters are set fromm the plugin configuration page.
* Parameters $s3_storage_enable, $s3_storage_provider, $s3_storage_key, $s3_storage_secret, $s3_storage_region,
* $s3_storage_bucket, $s3_storage_class, and $s3_storage_stats must be set.
*
* @return boolean   TRUE on success; otherwise, FALSE.
*/
function s3_check_parameters()
    {
    global $storagedir, $s3_storage_enable, $s3_storage_provider, $s3_storage_key, $s3_storage_secret, $s3_storage_region, $s3_storage_bucket, $s3_storage_class, $s3_storage_stats;

    if(isset($storagedir) && isset($s3_storage_enable) && $s3_storage_enable && isset($s3_storage_provider) && isset($s3_storage_key) && isset($s3_storage_secret) && isset($s3_storage_region) && isset($s3_storage_bucket) && isset($s3_storage_class) && isset($s3_storage_stats))
        {
        debug('S3_CHECK_PARAMETERS Result: ' . boolean_convert(true, 'ok'));
        return true;
        }

    debug('ERROR S3_CHECK_PARAMETERS Result: ' . boolean_convert(false, 'ok'));
    return false;
    }


/**
* Function to setup the AWS PHP SDK and the S3 storage environment.  Call before setting up a SDK client.
*
* @return array     Configuration parameters AWS SDK class for setting up SDK clients; otherwise, FALSE.
*/
function s3_storage_setup()
    {
    global $s3_storage_provider, $s3_storage_endpoint, $s3_storage_region, $s3_storage_key, $s3_storage_secret, $s3_storage_stats, $s3_api_version, $cw_api_version, $s3_storage_setup_debug, $s3_storage_path_endpoint;

    try
        {
        // Define the AWS S3 endpoint URL.
        if($s3_storage_provider == 'AWS')
            {
            $s3_storage_endpoint = 'https://s3.' . $s3_storage_region . '.amazonaws.com';
            }

        // Set the AWS PHP SDK shared client configuration.
        if($s3_storage_path_endpoint != null && $s3_storage_provider != 'AWS')
            {
            $s3_storage_path_endpoint = boolean_convert($s3_storage_path_endpoint, 'true');
            $sdk_shared_config = [
                'region' => $s3_storage_region,
                'credentials' => [
                    'key' => $s3_storage_key,
                    'secret' => $s3_storage_secret
                    ],
                'stats' => $s3_storage_stats,
                'S3' => [
                    'version' => $s3_api_version,
                    'endpoint' => $s3_storage_endpoint,
                    'use_path_style_endpoint' => $s3_storage_path_endpoint
                    ],
                'CloudWatch' => [
                    'version' => $cw_api_version
                    ]
                ];
            }
        else
            {
            $sdk_shared_config = [
                'region' => $s3_storage_region,
                'credentials' => [
                    'key' => $s3_storage_key,
                    'secret' => $s3_storage_secret
                    ],
                'stats' => $s3_storage_stats,
                'S3' => [
                    'version' => $s3_api_version,
                    'endpoint' => $s3_storage_endpoint,
                    ],
                'CloudWatch' => [
                    'version' => $cw_api_version
                    ]
                ];
            }

        // Create a configuration parameters AWS SDK class to share across multiple clients.
        $aws_sdk = new Aws\Sdk($sdk_shared_config);
        if($s3_storage_setup_debug)
            {
            debug('S3_STORAGE_SETUP Result: ' . print_r($aws_sdk, true));
            }

        return $aws_sdk;
        }
    catch(S3Exception $e)
        {
        debug('ERROR S3_STORAGE_SETUP: ' . $e->getMessage());
        }
    catch(AwsException $e)
        {
        debug('ERROR S3_STORAGE_SETUP: RequestID ' . $e->getAwsRequestID() . ', Type ' . $e->getAwsErrorType() . ', Code ' . $e->getAwsErrorCode());
        }
    catch(Exception $e)
        {
        debug('ERROR S3_STORAGE_SETUP: ' . $e->getMessage());
        }

    return false;
    }


/**
* Function to get the current S3 API description.
*
* @return array         SDK getApi() results; otherwise, FALSE.
*/
function s3_get_api()
    {
    global $lang;

    try
        {
        // Get the S3 configuration parameters and create a new S3 API client.
        $aws_sdk = s3_storage_setup();
        $s3Client = $aws_sdk->createS3();
        debug('S3_GET_API: S3 Client Setup Ok');

        // Use the client getApi to get the API description.
        $s3_result = $s3Client->getApi();
        $result['service_name'] = $s3_result->getServiceName();
        $result['service_fullname'] = $s3_result['metadata']['serviceFullName'];
        $result['serviceID'] = $s3_result->getServiceId();
        $result['api_version_date'] = $s3_result->getApiVersion();
        $result['api_version'] = $s3_result['metadata']['apiVersion'];
        $result['status'] = $lang['status-ok'];
        unset($s3Client);
        debug('S3_GET_API Results 2: ' . print_r($result, true));

        return $result;
        }
    catch(S3Exception $e)
        {
        debug('ERROR S3_GET_API: ' . $e->getMessage());
        }
    catch(AwsException $e)
        {
        debug('ERROR S3_GET_API: RequestID ' . $e->getAwsRequestID() . ', Type ' . $e->getAwsErrorType() . ', Code ' . $e->getAwsErrorCode());
        }
    catch(Exception $e)
        {
        debug('ERROR S3_GET_API: ' . $e->getMessage());
        }

    return false;
    }


/**
* Function to get the current S3 storage region.
*
* @return array         SDK getRegion() results; otherwise, FALSE.
*/
function s3_get_region()
    {
    global $lang;

    try
        {
        // Get the S3 configuration parameters and create a new S3 API client.
        $aws_sdk = s3_storage_setup();
        $s3Client = $aws_sdk->createS3();
        debug('S3_GET_REGION: S3 Client Setup Ok');

        // Use the client getRegion to get the region currently connected to.
        $result = $s3Client->getRegion();
        debug('S3_GET_REGION Results: ' . print_r($result, true));
        unset($s3Client);

        return $result;
        }
    catch(S3Exception $e)
        {
        debug('ERROR S3_GET_REGION: ' . $e->getMessage());
        }
    catch(AwsException $e)
        {
        debug('ERROR S3_GET_REGION: RequestID ' . $e->getAwsRequestID() . ', Type ' . $e->getAwsErrorType() . ', Code ' . $e->getAwsErrorCode());
        }
    catch(Exception $e)
        {
        debug('ERROR S3_GET_REGION: ' . $e->getMessage());
        }

    return false;
    }


/**
* Function to get the S3 endpoint that the SDK is currently connected to.
*
* @return array         SDK getEndpoint() results; otherwise, FALSE.
*/
function s3_get_endpoint()
    {
    global $lang;

    try
        {
        // Get the S3 configuration parameters and create a new S3 API client.
        $aws_sdk = s3_storage_setup();
        $s3Client = $aws_sdk->createS3();
        debug('S3_GET_ENDPOINT: S3 Client Setup Ok');

        // Use the client getEndpoint to get the endpoint currently connected to.
        $result = $s3Client->getEndpoint();
        $result1['scheme'] = $result->getScheme();
        $result1['endpoint'] = $result->getHost();
        unset($s3Client);
        debug('S3_GET_ENDPOINT Result: ' . print_r($result, true));

        if($result != '' && !stristr($result1['endpoint'], '..'))
            {
            $result1['status'] = true;
            }
        else
            {
            $result1['status'] = false;
            }

        return $result1;
        }
    catch(S3Exception $e)
        {
        debug('ERROR S3_GET_ENDPOINT: ' . $e->getMessage());
        }
    catch(AwsException $e)
        {
        debug('ERROR S3_GET_ENDPOINT: RequestID ' . $e->getAwsRequestID() . ', Type ' . $e->getAwsErrorType() . ', Code ' . $e->getAwsErrorCode());
        }
    catch(Exception $e)
        {
        debug('ERROR S3_GET_ENDPOINT: ' . $e->getMessage());
        }

    $result1['scheme'] = $lang['status-fail'];
    $result1['endpoint'] = $lang['status-fail'];
    $result1['status'] = $lang['status-fail'];
    return $result1;
    }


/**
* Function to define the available S3 storage classes and convert the S3 storage class code to a name.
*
* @param string  $s3_storage_class  S3 storage class code.
* @param boolean $format            Enable special display text format?
*
* @return array                     ('code' => 'value', 'name' => 'value', 'status' => value)
*/
function s3_storage_class($s3_storage_class, $format = false)
    {
    global $lang, $s3_storage_provider;
    $result['status'] = $lang['status-ok'];

    // Determine the storage class if different than AWS S3 usage (add other providers as needed).
    switch ($s3_storage_provider)
        {
        case 'AWS':
            $storage_class = $s3_storage_class;
            break;
        case 'DigitalOcean':
            $storage_class = 'STANDARD';
            break;
        default:
            $storage_class = 'OTHER';
        }

    // Determine the S3 storage class code and name.
    switch ($storage_class)
        {
        case 'STANDARD':
            $result['code'] = 'STANDARD';
            $result['name'] = 'Standard Storage';
            break;
        case 'INTELLIGENT_TIERING':
            $result['code'] = 'INTELLIGENT_TIERING';
            $result['name'] = 'Intelligent Tiering';
            break;
        case 'STANDARD_IA':
            $result['code'] = 'STANDARD_IA';
            $result['name'] = 'Standard, Infrequent Access Storage';
            break;
        case 'ONEZONE_IA':
            $result['code'] = 'ONEZONE_IA';
            $result['name'] = 'One Zone, Infrequent Access Storage';
            break;
        case 'REDUCED_REDUNDANCY':
            $result['code'] = 'REDUCED_REDUNDANCY';
            $result['name'] = 'Reduced Redundancy Storage';
            break;
        case 'OUTPOSTS':
            $result['code'] = 'OUTPOSTS';
            $result['name'] = 'Outposts';
            break;
        case 'OTHER':
            $result['code'] = 'default';
            $result['name'] = 'No Storage Type Used';
        default:
            $result['code'] = 'AllStorageTypes';
            $result['name'] = 'All Storage Types';
            $result['status'] = $lang['status-fail'];
        }

    if($format)
        {
        $result['name'] = ' (' . $result['name'] . ')';
        }

    return $result;
    }


/**
* Function to get the owner of a specific S3 bucket.
*
* @param string  $s3_storage_bucket  S3 bucket name.
*
* @return array                      ('name' => value, 'id' => value, 'status' => value); otherwise, FALSE.
*/
function s3_bucket_owner($s3_storage_bucket)
    {
    global $lang;

    $s3_result['name'] = '';
    $s3_result['id'] = '';
    $s3_result['status'] = $lang['status-fail'];

    try
        {
        // Get the S3 configuration parameters and create a new S3 API client.
        $aws_sdk = s3_storage_setup();
        $s3Client = $aws_sdk->createS3();
        debug('S3_BUCKET_OWNER: S3 Client Setup Ok');

        // Use the client getBucketAcl to get the owner of the specified S3 bucket.
        $result = $s3Client->getBucketAcl([
           'Bucket' => $s3_storage_bucket,
        ]);

        unset($s3Client);
        $s3_result['name'] = $result['Owner']['DisplayName'];
        $s3_result['id'] = $result['Owner']['ID'];
        $s3_result['status'] = $lang['status-ok'];
        return $s3_result;
        }
    catch(S3Exception $e)
        {
        debug('ERROR S3_BUCKET_OWNER: ' . $e->getMessage());
        }
    catch(AwsException $e)
        {
        debug('ERROR S3_BUCKET_OWNER: RequestID ' . $e->getAwsRequestID() . ', Type ' . $e->getAwsErrorType() . ', Code ' . $e->getAwsErrorCode());
        }
    catch(Exception $e)
        {
        debug('ERROR S3_BUCKET_OWNER: ' . $e->getMessage());
        }

    return false;
    }


/**
* Function to check if a specific S3 bucket exists.
*
* @param string $s3_storage_bucket S3 bucket name.
*
* @return array                    SDK headBucket() results or FALSE.
*/
function s3_bucket_head($s3_storage_bucket)
    {
    global $lang;

    try
        {
        // Get the S3 configuration parameters and create a new S3 API client.
        $aws_sdk = s3_storage_setup();
        $s3Client = $aws_sdk->createS3();
        debug('S3_BUCKET_HEAD: S3 Client Setup Ok');

        // Use the client headBucket to determine if the specified S3 bucket exists.
        $result = $s3Client->headBucket([
            'Bucket' => $s3_storage_bucket
            ]);
        unset($s3Client);
        debug('S3_BUCKET_HEAD Result: ' . print_r($result, true));

        return $result;
        }
    catch(S3Exception $e)
        {
        debug('ERROR S3_BUCKET_HEAD: ' . $e->getMessage());
        }
    catch(AwsException $e)
        {
        debug('ERROR S3_BUCKET_HEAD: RequestID ' . $e->getAwsRequestID() . ', Type ' . $e->getAwsErrorType() . ', Code ' . $e->getAwsErrorCode());
        }
    catch(Exception $e)
        {
        debug('ERROR S3_BUCKET_HEAD: ' . $e->getMessage());
        }

    return false;
    }


/**
* Function to get a the location (region) of a specific S3 bucket.
*
* @param string $s3_storage_bucket  S3 bucket name.
*
* @return array                     SDK getBucketLocation() results; otherwise, FALSE.
*/
function s3_bucket_location($s3_storage_bucket)
    {
    global $lang;

    try
        {
        // Get the S3 configuration parameters and create a new S3 API client.
        $aws_sdk = s3_storage_setup();
        $s3Client = $aws_sdk->createS3();
        debug('S3_BUCKET_LOCATION: S3 Client Setup Ok');

        // Use the client getBucketLocation to determine the location of the specified S3 bucket.
        $result = $s3Client->getBucketLocation([
            'Bucket' => $s3_storage_bucket
            ]);
        debug('S3_BUCKET_LOCATION Results: ' . print_r($result, true));
        unset($s3Client);

        if(isset($result['LocationConstraint']))
            {
            $result['status'] = true;
            }
        else
            {
            $result['LocationConstraint'] = '';
            $result['status'] = false;
            }
        }
    catch(S3Exception $e)
        {
        $result['status'] = false;
        debug('ERROR S3_BUCKET_LOCATION: ' . $e->getMessage());
        }
    catch(AwsException $e)
        {
        $result['status'] = false;
        debug('ERROR S3_BUCKET_LOCATION: RequestID ' . $e->getAwsRequestID() . ', Type ' . $e->getAwsErrorType() . ', Code ' . $e->getAwsErrorCode());
        }
    catch(Exception $e)
        {
        $result['status'] = false;
        debug('ERROR S3_BUCKET_LOCATION: ' . $e->getMessage());
        }

    return $result;
    }


/**
* Function to list the available S3 buckets to the current owner and connection.
*
* @return array  SDK listBucket() results; otherwise, FALSE.
*/
function s3_bucket_list()
    {
    try
        {
        // Get the S3 configuration parameters and create a new S3 API client.
        $aws_sdk = s3_storage_setup();
        $s3Client = $aws_sdk->createS3();
        debug('S3_BUCKET_LIST: S3 Client Setup Ok');

        // Use the client listBuckets to list the buckets owned by the owner.
        $result = $s3Client->listBuckets([]);
        unset($s3Client);
        debug('S3_BUCKET_LIST Result: ' . print_r($result, true));

        return $result;
        }
    catch(S3Exception $e)
        {
        debug('ERROR S3_BUCKET_LIST: ' . $e->getMessage());
        }
    catch(AwsException $e)
        {
        debug('ERROR S3_BUCKET_LIST: RequestID ' . $e->getAwsRequestID() . ', Type ' . $e->getAwsErrorType() . ', Code ' . $e->getAwsErrorCode());
        }
    catch(Exception $e)
        {
        debug('ERROR S3_BUCKET_LIST: ' . $e->getMessage());
        }

    return false;
    }


/**
* Function to create an S3 object path from a normal filestore path.
*
* @param string  $path            A normal filestore path to a resource.
* @param boolean $trailing_slash  Add a trailing slash to the S3 object path?
*
* @return string                  S3 object path; otherweise, FALSE.
*/
function s3_object_path($path, $trailing_slash = false)
    {
    global $storagedir;

    try
        {
        // Add trailing slash to path if needed.
        $slash = '';
        if($trailing_slash)
            {
            $slash = DIRECTORY_SEPARATOR;
            }

        // Strip the $storagedir and leading slash from path to match S3 bucket path.
        $s3_path = ltrim(str_replace($storagedir, '', $path), DIRECTORY_SEPARATOR) . $slash;
        debug('S3 OBJECT PATH: ' . $s3_path);

        return $s3_path;
        }
    catch(Exception $e)
        {
        debug('ERROR S3 OBJECT PATH: ' . $e->getMessage());
        }

    return false;
    }


/**
* Function to check if an object exists in a specified S3 bucket.
*
* @param string $s3_object  S3 object path.
*
* @return boolean           TRUE if the object exists in the S3 bucket; otherwise, FALSE.
*/
function s3_object_exists($s3_object)
    {
    global $lang, $s3_storage_bucket;

    try
        {
        // Get the S3 configuration parameters and create a new S3 API client.
        $aws_sdk = s3_storage_setup();
        $s3Client = $aws_sdk->createS3();
        debug('S3_OBJECT_EXISTS: S3 Client Setup Ok');

        // Use the client doesObjectExist to check if an object exists in the specified S3 bucket.
        $result = $s3Client->doesObjectExist($s3_storage_bucket, $s3_object);
        unset($s3Client);
        if($result)
            {
            debug('S3_OBJECT_EXISTS: Yes, ' . $s3_object);
            return true;
            }
        else
            {
            debug('WARNING S3_OBJECT_EXISTS: No, ' . $s3_object);
            }
        }
    catch(S3Exception $e)
        {
        debug('ERROR S3_OBJECT_EXISTS: ' . $e->getMessage());
        }
    catch(AwsException $e)
        {
        debug('ERROR S3_OBJECT_EXISTS: RequestID ' . $e->getAwsRequestID() . ', Type ' . $e->getAwsErrorType() . ', Code ' . $e->getAwsErrorCode());
        }
    catch(Exception $e)
        {
        debug('ERROR S3_OBJECT_EXISTS: ' . $e->getMessage());
        }

    return false;
    }


/**
* Function to get the metadata on a S3 object (S3 metadata, not the ResourceSpace metadata).
*
* @param string $s3_object  S3 object path.
*
* @return array             SDK headObject() results; otherwise, FALSE.
*/
function s3_object_head($s3_object)
    {
    try
        {
        global $lang, $s3_storage_bucket;

        // Get the S3 configuration parameters and create a new S3 API client.
        $aws_sdk = s3_storage_setup();
        $s3Client = $aws_sdk->createS3();
        debug('S3_OBJECT_HEAD: S3 Client Setup Ok');

        // Use the client headObject to get metadata on the specified S3 bucket.
        $result = $s3Client->headObject([
            'Bucket' => $s3_storage_bucket,
            'Key' => $s3_object
            ]);
        unset($s3Client);
        debug('S3_OBJECT_HEAD Result: ' . print_r($result, true));
        debug('S3_OBJECT_HEAD Result 2: ' . json_encode($result->toArray(), JSON_PRETTY_PRINT));
        debug('S3_OBJECT_HEAD Result 3: ' . $result['ContentLength']);

        return $result;
        }
    catch(S3Exception $e)
        {
        debug('ERROR S3_OBJECT_HEAD: ' . $e->getMessage());
        }
    catch(AwsException $e)
        {
        debug('ERROR S3_OBJECT_HEAD: RequestID ' . $e->getAwsRequestID() . ', Type ' . $e->getAwsErrorType() . ', Code ' . $e->getAwsErrorCode());
        }
    catch(Exception $e)
        {
        debug('ERROR S3_OBJECT_HEAD: ' . $e->getMessage());
        }

    return false;
    }


/**
* Function to list specific objects in a S3 bucket using a prefix.
*
* @param string $s3_object_prefix  S3 object prefix to search on.
*
* @return array                    SDK listObjectsV2() results; otherwise, FALSE.
*/
function s3_object_list($s3_object_prefix)
    {
    global $lang, $s3_storage_bucket;

    try
        {
        // Get the S3 configuration parameters and create a new S3 API client.
        $aws_sdk = s3_storage_setup();
        $s3Client = $aws_sdk->createS3();
        debug('S3_OBJECT_LIST: S3 Client Setup Ok');

        // Use the client listObjectsV2 to list objects matching the prefix in the specified S3 bucket.
        $result = $s3Client->listObjectsV2([
            'Bucket' => $s3_storage_bucket,
            'Prefix' => $s3_object_prefix
            ]);
        unset($s3Client);
        debug('S3_OBJECT_LIST Results: ' . print_r($result, true));

        return $result;
        }
    catch(S3Exception $e)
        {
        debug('ERROR S3_OBJECT_LIST: ' . $e->getMessage());
        }
    catch(AwsException $e)
        {
        debug('ERROR S3_OBJECT_LIST: RequestID ' . $e->getAwsRequestID() . ', Type ' . $e->getAwsErrorType() . ', Code ' . $e->getAwsErrorCode());
        }
    catch(Exception $e)
        {
        debug('ERROR S3_OBJECT_LIST: ' . $e->getMessage());
        }

    return false;
    }


/**
* Function to upload a local file to a specified S3 bucket.
*
* @param string  $fs_path           Local resource filestore path.
* @param string  $s3_object         S3 object path.
* @param string  $s3_storage_class  S3 storage class code.
*
* @return array                     SDK ObjectUploader() or MultipartUploader results; otherwise, FALSE.
*/
function s3_object_upload($fs_path, $s3_object)
    {
    global $lang, $s3_storage_bucket, $s3_storage_class, $s3_storage_mpupload_gc;

    try
        {
        // Get the S3 configuration parameters and create a new S3 API client.
        $aws_sdk = s3_storage_setup();
        $s3Client = $aws_sdk->createS3();
        debug('S3_OBJECT_UPLOAD: S3 Client Setup Ok');

        // Using stream instead of file path, set options, and create an ObjectUploader.
        $source = fopen($fs_path, 'rb');
        $options = array('params' => array(
            'StorageClass' => $s3_storage_class
            ));
        $uploader = new ObjectUploader($s3Client, $s3_storage_bucket, $s3_object, $source, $acl = 'private', $options);
        debug('S3_OBJECT_UPLOAD: ObjectUploader Setup Ok');

        do
            {
            // Use the API upload.
            try {
                $result = $uploader->upload();
                if ($result['@metadata']['statusCode'] == '200')
                    {
                    debug('S3_OBJECT_UPLOAD: Ok, ' . $result['ObjectURL']);
                    }
                else
                    {
                    debug('ERROR S3_OBJECT_UPLOAD: ' . print_r($result, true));
                    }
                }
            // Otherwise, use the API MultipartUpload for larger files.
            catch (MultipartUploadException $e)
                {
                rewind($source);
                $uploader = new MultipartUploader($s3Client, $source, [
                    'state' => $e->getState(),
                    'before_upload' => function(\Aws\Command $command)
                        {
                        // Run the PHP garbage collector before a S3 multipart upload to free memory for large uploads.
                        if($s3_storage_mpupload_gc)
                            {
                            $gc = gc_collect_cycles();
                            debug('S3_OBJECT_UPLOAD PHP Garbage Collector Cycles: ' . $gc);
                            }
                        }
                    ]);
                if ($uploader['@metadata']['statusCode'] == '200')
                    {
                    debug('S3_OBJECT_UPLOAD: Ok, ' . $uploader['ObjectURL']);
                    }
                else
                    {
                    debug('ERROR S3_OBJECT_UPLOAD MP: ' . print_r($uploader, true));
                    }
                }
            }
        while (!isset($result));

        unset($s3Client);
        return $result;
        }
    catch(S3Exception $e)
        {
        debug('ERROR S3_OBJECT_UPLOAD: ' . $e->getMessage());
        }
    catch(AwsException $e)
        {
        debug('ERROR S3_OBJECT_UPLOAD: RequestID ' . $e->getAwsRequestID() . ', Type ' . $e->getAwsErrorType() . ', Code ' . $e->getAwsErrorCode());
        }
    catch(Exception $e)
        {
        debug('ERROR S3_OBJECT_UPLOAD: ' . $e->getMessage());
        }

    return false;
    }


/**
* Function to download an object from a S3 bucket to a local file.
*
* @param string $s3_object  S3 object path.
* @param string $fs_file    Local filestore path.
*
* @return array             SDK getObject() results; otherwise, FALSE.
*/
function s3_object_download($s3_object, $fs_file)
    {
    global $lang, $s3_storage_bucket;

    try
        {
        // Get the S3 configuration parameters and create a new S3 API client.
        $aws_sdk = s3_storage_setup();
        $s3Client = $aws_sdk->createS3();
        debug('S3_OBJECT_DOWNLOAD: S3 Client Setup Ok');

        // Use the client getObject to download an object from a specified S3 bucket.
        $result = $s3Client->getObject([
            'Bucket' => $s3_storage_bucket,
            'Key' => $s3_object,
            'SaveAs' => $fs_file
            ]);
        unset($s3Client);
        debug('S3_OBJECT_DOWNLOAD From: ' . $s3_object . ', Results: ' . print_r($result, true));

        return $result;
        }
    catch(S3Exception $e)
        {
        debug('ERROR S3_OBJECT_DOWNLOAD: ' . $e->getMessage());
        }
    catch(AwsException $e)
        {
        debug('ERROR S3_OBJECT_DOWNLOAD: RequestID ' . $e->getAwsRequestID() . ', Type ' . $e->getAwsErrorType() . ', Code ' . $e->getAwsErrorCode());
        }
    catch(Exception $e)
        {
        debug('ERROR S3_OBJECT_DOWNLOAD: ' . $e->getMessage());
        }

    return false;
    }


/**
* Function to delete an object in a S3 bucket.
*
* @param string $s3_object  S3 object path.
*
* @return boolean           TRUE on success; otherwise, FALSE.
*/
function s3_object_delete($s3_object)
    {
    global $lang, $s3_storage_bucket;

    try
        {
        // Get the S3 configuration parameters and create a new S3 API client.
        $aws_sdk = s3_storage_setup();
        $s3Client = $aws_sdk->createS3();
        debug('S3_OBJECT_DELETE: S3 Client Setup Ok');

        // Use the client deleteObject to delete an object from the specified S3 bucket.
        $result = $s3Client->deleteObject([
            'Bucket' => $s3_storage_bucket,
            'Key' => $s3_object
            ]);
        unset($s3Client);

        $status_code = html_status_code($result['@metadata']['statusCode'], true);
        debug('S3_OBJECT_DELETE From: ' . $s3_object . ' with Status Code: ' . $status_code['text']);
        if($status_code['success'])
            {
            return true;
            }
        }
    catch(S3Exception $e)
        {
        debug('ERROR S3_OBJECT_DELETE: ' . $e->getMessage());
        }
    catch(AwsException $e)
        {
        debug('ERROR S3_OBJECT_DELETE: RequestID ' . $e->getAwsRequestID() . ', Type ' . $e->getAwsErrorType() . ', Code ' . $e->getAwsErrorCode());
        }
    catch(Exception $e)
        {
        debug('ERROR S3_OBJECT_DELETE: ' . $e->getMessage());
        }

    return false;
    }


/**
* Function to copy an existing S3 object within a S3 bucket; used mainly to rename objects.
*
* @param string $object_from      S3 object path to copy from.
* @param string $object_to        S3 object path to copy to.
* @param string $s3_storage_class S3 storage class code.
*
* @return array                   SDK copyObject() results; otherwise, FALSE.
*/
function s3_object_copy($object_from, $object_to, $s3_storage_class)
    {
    global $lang, $s3_storage_bucket;
    debug('S3_OBJECT_COPY Input: From ' . $object_from . ' To ' . $object_to . ' in ' . $s3_storage_class . ' storage class');

    try
        {
        // Get the S3 configuration parameters and create a new S3 API client.
        $aws_sdk = s3_storage_setup();
        $s3Client = $aws_sdk->createS3();
        debug('S3_OBJECT_COPY: S3 Client Setup Ok');

        // Get the existing object size and storage class.
        $result = s3_object_head($sdk, $object_from);
        $size = (int)$result['ContentLength'];
        $size = (int)$result->search('ContentLength');
        if($size = '' || !$size)
            {
            $size = 0;
            }

        $from_strclass = ' (' . $result['StorageClass'] . ')';
        $to_strclass = s3_storage_class($s3_storage_class, true);
        debug('S3_OBJECT_COPY From: ' . $object_from . $from_strclass . ' To: ' . $object_to . $to_strclass . ', Filesize: ' . formatfilesize($size));

        // If filesize <5GB, use the client copyObject.
        if($size > 5000000000 || $size == '')
            {
            $result = $s3Client->copyObject([
                'Bucket' => $s3_storage_bucket,
                'CopySource' => $object_from,
                'Key' => $object_to,
                'StorageClass' => $s3_storage_class
                ]);
            debug('S3_OBJECT_COPY From: ' . $object_from . ' To: ' . $object_to . ', Results: ' . print_r($result, true));
            unset($s3Client);

            return $result;
            }
        // If the filesize >5GB, use the API MultipartCopy.
        elseif($size >=5000000000)
            {
            $copier = new MultipartCopy($s3Client, $object_from, [
                'bucket' => $s3_storage_bucket,
                'key' => $object_to,
            ]);

            try
                {
                $result = $copier->copy();
                debug('S3_OBJECT_COPY Multipart Complete: ' . $result['ObjectURL']);
                unset($s3Client);

                return $result;
                }
            catch (MultipartUploadException $e)
                {
                debug('ERROR S3_OBJECT_COPY Multipart: ' . $e->getMessage());
                }
            }
        }
    catch(S3Exception $e)
        {
        debug('ERROR S3_OBJECT_COPY: ' . $e->getMessage());
        }
    catch(AwsException $e)
        {
        debug('ERROR S3_OBJECT_COPY: RequestID ' . $e->getAwsRequestID() . ', Type ' . $e->getAwsErrorType() . ', Code ' . $e->getAwsErrorCode());
        }
    catch(Exception $e)
        {
        debug('ERROR S3_OBJECT_COPY: ' . $e->getMessage());
        }

    return false;
    }


/**
* Function to delete a filestore original file and create a zero-byte placeholder file in its place.
*
* @param string $fs_path  Original resource filestore path.
*
* @return array           ('delete' => boolean, 'placeholder' => boolean); otherwise, FALSE.
*/
function s3_file_placeholder($fs_path)
    {
    if(is_file($fs_path))
        {
        // Delete the filestore original file, as it is now in S3 storage.
        $result['delete'] = unlink($fs_path);
        debug('S3 FILE PLACEHOLDER Original Delete: ' . boolean_convert($result['delete'], 'ok') . ', ' . $fs_path);

        // Create a zero-byte placeholder file in the filestore with the same path and name as the original file.
        $ph_path = fopen($fs_path, 'wb');
        $result['placeholder'] = fclose($ph_path);
        debug('S3 FILE PLACEHOLDER Original Placeholder: ' . boolean_convert($result['placeholder'], 'ok'));
        }
    else // Error, filestore original file is missing.
        {
        debug('ERROR S3 FILE PLACEHOLDER: Original Missing');
        return false;
        }

    return $result;
    }


/**
* Function to determine a local filestore temp filepath for a given filepath using a unique, random filename.
*
* @param string $path    Resource filestore temp filepath.
* @param string $uniqid  Unique ID.
*
* @return string         Local filestore temp filepath.
*/
function s3_file_tempname($path, $uniqid = '')
    {
    debug('S3_FILE_TEMPNAME Input Path: ' . $path);
    $file_path_info = pathinfo($path);
    $filename = md5(mt_rand()) . "_{$file_path_info['basename']}";

    $tmp_dir = get_temp_dir(false, $uniqid);
    $s3_tmpfile = $tmp_dir . DIRECTORY_SEPARATOR . $filename;
    debug('S3_FILE_TEMPNAME Local Temp Path: ' . $s3_tmpfile);

    return $s3_tmpfile;
    }


/**
* Function to create a local temp S3 folder for S3 processing and the S3 script tool logs.
*
* @params $subfolder    Optional: subfolder name.
*
* @return string        Local filestore S3 temp folder path; otherwise, FALSE.
*/
function s3_create_temp($subfolder = '')
    {
    // Define an OS-independent S3 temporary folder in the ../filestore/tmp folder.
    if($subfolder == '')
        {
        $s3_temp_dir = get_temp_dir(false) . DIRECTORY_SEPARATOR . 's3_temp';
        }
    else
        {
        $s3_temp_dir = get_temp_dir(false) . DIRECTORY_SEPARATOR . 's3_temp' . DIRECTORY_SEPARATOR . $subfolder;
        }

    // Check if the local S3 file temporary folder exists, if not, create it.
    if(!is_dir($s3_temp_dir))
        {
        $result = mkdir($s3_temp_dir, 0777, true);
        $result1 = chmod($s3_temp_dir, 0777);
        debug('S3_CREATE_TEMP: Created at ' . $s3_temp_dir . ' (' . boolean_convert($result, 'ok') . '/' . boolean_convert($result1, 'ok') . ')');
        if($result && $result1)
            {
            $s3_temp = $s3_temp_dir . DIRECTORY_SEPARATOR;
            }
        else
            {
            debug('ERROR S3_CREATE_TEMP: Could not create ' . $s3_temp_dir);
            return false;
            }
        }
    else
        {
        $s3_temp = $s3_temp_dir . DIRECTORY_SEPARATOR;
        debug('S3_CREATE_TEMP: ' . $s3_temp);
        }

    return $s3_temp;
    }


/**
* Function to cleanup a folder by purging files, not subfolders, based on file age or the last access in minutes.
*
* @param string $folder    Folder to cleanup.
* @param number $min_age   Minimum age in minutes.
* @param string $time_type Options: change or access
* @param number $max_age   Maximum age in minutes.
*
* @return boolean          TRUE on success; otherwise, FALSE.
*/
function s3_folder_cleanup($folder = '', $min_age = 5, $time_type = 'change', $max_age = '')
    {
    global $s3_temp_purge;

    // Get the folder path to cleanup.
    if($folder = '')
        {
        $tmp_dir = get_temp_dir(false);
        $flag = '/tmp ';
        }
    else
        {
        $tmp_dir = $folder;
        $flag = $folder . ' ';
        }

    // Set the max file age to use.
    if($max_age = '' || !$max_age || !isset($max_age))
        {
        $age = $s3_temp_purge * 86400;
        }
    else
        {
        $age = $max_age;
        $flag = 'S3 Temp ';
        }

    // Set the minimum file age to protect other operations.
    if($age < $min_age * 60)
        {
        $age = $min_age * 60;
        }

    // Check the specified folder exists.
    if(is_dir($tmp_dir))
        {
        // Loop through the specified folder.
        $time_now = time();
        foreach(new FilesystemIterator($tmp_dir) as $file)
            {
            // If item is a sub-folder, skip and continue loop.
            if(is_dir($file))
                {
                continue;
                }

            // Using file change age.
            if($time_type = 'change')
                {
                $time1 = $time_now - $file->getCTime();
                }
            // Using file access age.
            elseif($time_type = 'access')
                {
                $time1 = $time_now - $file->getATime();
                }

            // Delete the file if the file age is greater than the maximum age parameter.
            if(is_file($file) && $age == 0 || $time1 >= $age)
                {
                $result = unlink($file->getRealPath());
                debug('S3_FOLDER_CLEANUP Result: ' . $flag . boolean_convert($result, 'ok'));
                }
            }

        return true;
        }

    debug("ERROR S3_FOLDER_CLEANUP: {$tmp_dir} folder does not exist.");
    return false;
    }


/**
* Function to check if a file exists in a local filestore temp folder.
*
* @param string $subfolder    Filestore temp subfolder path.
* @param string $search_file  Filename to search for.
*
* @return boolean             TRUE on success; otherwise, FALSE.
*/
function tmp_file_search($subfolder, $search_file)
    {
    $tmp_dir = get_temp_dir(false) . DIRECTORY_SEPARATOR . $subfolder;

    foreach(new FilesystemIterator($tmp_dir) as $file)
        {
        $filename = pathinfo($file, PATHINFO_FILENAME);

        if(is_dir($file))
            {
            continue;
            }
        if(is_file($file) && strpos($filename, $search_file) !== false)
            {
            $result = true;
            return $result;
            }
        }

    return false;
    }


/**
* Function to get the current AWS S3 CloudWatch metric statistics; only valid when using AWS S3 storage.
*
* @param string  $namespace   CloudWatch namespace.
* @param string  $metricname  CloudWatch metric name.
* @param string  $dimensions  CloudWatch dimensions, storage type and bucket name key-value pairs.
* @param double  $start_time  Metric start time, use PHP strtotime.
* @param double  $end_time    Metric end time, use PHP strtotime.
* @param integer $period      Period of the metric in seconds.
* @param array   $statistics  Stastistic name: average, etc.
* @param string  $unit        CloudWatch metric unit.
*
* @return array               SDK getMetricStatistics() results; otherwise, FALSE.
*/
function s3_metric_statistics($namespace, $metric_name, $dimensions, $start_time, $end_time, $period, $statistics, $unit)
    {
    global $s3_storage_provider;

    if($s3_storage_provider == 'AWS')
        {
        try {
            // Get the S3 configuration parameters and create a new S3 CloudWatch client.
            $aws_sdk = s3_storage_setup();
            $cwClient = $aws_sdk->createCloudWatch();
            debug('S3_METRIC_STATISTICS: CloudWatch Client Setup Ok');

            // Get the specified metric statistics.
            $result = $cwClient->getMetricStatistics([
                'Namespace' => $namespace,
                'MetricName' => $metric_name,
                'Dimensions' => $dimensions,
                'StartTime' => $start_time,
                'EndTime' => $end_time,
                'Period' => $period,
                'Statistics' => $statistics,
                'Unit' => $unit
            ]);
            debug('S3_METRIC_STATISTICS ' . $metric_name . ' Result: ' . print_r($result, true));

            if($result['@metadata']['statusCode'] != 200)
                {
                debug('WARNING S3_METRIC_STATISTICS: No datapoints found ' . $metric_name);
                $result['error'] = 'No datapoints found for ' . $metric_name;
                $result['status'] = false;
                return $result;
                }

            return $result;
            }
        catch(AwsException $e)
            {
            debug('ERROR S3_METRIC_STATISTICS: ' . $e->getAwsErrorMessage());
            }
        catch(Exception $e)
            {
            debug('ERROR S3_METRIC_STATISTICS: ' . $e->getMessage());
            }
        }

    debug('ERROR S3_METRIC_STATISTICS: Only valid for AWS.');
    return false;
    }


/**
* Function to create the input dimension parameters for the AWS CloudWatch metric statistics.
*
* @param string $name1   Input dimension 1.
* @param string $value1  Input value 1.
* @param string $name2   Input dimension 2.
* @param string $value2  Input value 2.
*
* @return array          Array of key-value dimension pairs.
*/
function cw_dimension($name1, $value1, $name2, $value2)
    {
    global $s3_bucket;

    $dimensions = [
        [
        'Name' => $name1,
        'Value'=> $value1
        ],
        [
        'Name' => $name2,
        'Value' => $value2
        ]
    ];

    return $dimensions;
    }


/**
* Function to determine the total number and size of all files in a folder, not including subfolders.
*
* @param string  $folder  Filestore folder.
* @param boolean $string  Use human-readble text output?
*
* @return array           ('size' => value, 'number' => value)
*/
function folder_file_size($folder, $string = false)
    {
    // Set counters to zero and check if the specified folder is actually a folder.
    $result['size'] = 0;
    $result['number'] = 0;
    if(!is_dir($folder))
        {
        return false;
        }

    // Create an array of filenames within the specified folder, if none found, return zero.
    $files = scandir($folder);
    if(!$files)
        {
        if($string)
            {
            $result['size'] = formatfilesize($result['size']);
            }

        return $result;
        }
    $files = array_diff($files, array('.', '..'));

    // Loop through the folder files and cumulatively add up the file sizes.
    foreach ($files as $file)
        {
        if(is_file($folder . DIRECTORY_SEPARATOR . $file))
            {
            $result['size'] += filesize_unlimited($folder . DIRECTORY_SEPARATOR . $file);
            ++$result['number'];
            }
        }

    // If requested, convert the size in bytes, and return the folder info.
    unset($files);
    if($string)
        {
        $result['size'] = formatfilesize($result['size']);
        }

    debug('FOLDER_FILE_SIZE Result: ' . print_r($result, true));
    return $result;
    }


/** Function to convert HTML status codes.
*
* @param 
* 
* @return array
*/
function s3_check_resource_type($ref)
    {
    global $s3_storage_resource_types;

    // Get the resource type for the specified resource.
    $s3_res_type = sql_query("SELECT resource_type FROM resource WHERE ref='$ref'");
debug('RESOURCE_TYPES: ' . print_r($s3_res_type, true));
    
    }


/** Function to convert HTML status codes.
*
* @param integer   $code        HTML code.
* @param boolean   $show_text   Show text name for status code?
*
* @return array                 ('text' => value, 'error' => boolean, 'success' => boolean)
*/
function html_status_code($code, $show_text = false)
    {
    if(!is_numeric($code))
        {
        return false;
        }
    $code = (int)$code;
    $result['success'] = '';

    if($code >= 100 && $code <= 199)
        {
        $result['text'] = 'Informational Response';
        $result['error'] = false;
        }
    elseif($code >= 200 && $code <= 299)
        {
        $result['text'] = 'Success';
        $result['error'] = false;
        $result['success'] = true;
        }
    elseif($code >= 300 && $code <= 399)
        {
        $result['text'] = 'Redirection';
        $result['error'] = false;
        }
    elseif($code >= 400 && $code <= 499)
        {
        $result['text'] = 'Client Error';
        $result['error'] = true;
        }
    elseif($code >= 500 && $code <= 599)
        {
        $result['text'] = 'Server Error';
        $result['error']= true;
        }
    else
        {
        return false;
        }

    if($show_text)
        {
        $result['text'] = $code . ' (' . $result['text'] . ')';
        }

    return $result;
    }


/**
* Function to convert a boolean 'true-false' value to 'Ok-Fail' or 'Yes-No' text.
*
* @param boolean  $input  Boolean variable to convert.
* @param string   $type   Result formatting option: 'ok', 'yes', or 'true'
*
* @return string
*/
function boolean_convert($input, $type)
    {
    global $lang;

    if($input && $type == 'ok')
        {
        $result = $lang['status-ok'];
        }
    elseif(!$input && $type == 'ok')
        {
        $result = $lang['status-fail'];
        }
    elseif($input && $type == 'yes')
        {
        $result = $lang['yes'];
        }
    elseif(!$input && $type == 'yes')
        {
        $result = $lang['no'];
        }
    elseif($input && $type == 'true')
        {
        $result = $lang['false-true'][1];
        }
    elseif(!$input && $type == 'true')
        {
        $result = $lang['false-true'][0];
        }
    else // Insufficient input data.
        {
        $result = $lang['input_error'];
        }

    return $result;
    }
