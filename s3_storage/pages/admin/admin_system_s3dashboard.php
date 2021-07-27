<?php
// Simple Storage Service (S3) Object-Based Storage Dashboard
// This file creates the Admin, System, S3 Dashboard page.

include '../../../../include/db.php';
include '../../../../include/authenticate.php';
if(!checkperm('a'))
    {
    exit($lang['error-permissiondenied']);
    }
include_once '../../include/s3_storage_functions.php';
include '../../../../include/header.php';

global $lang, $s3_storage_enable, $s3_storage_bucket, $s3_storage_provider, $s3_storage_class;

// Page breadcrumbs.
$links_trail = array(
    array(
        'title' => $lang['systemsetup'],
        'href'  => $baseurl_short . 'pages/admin/admin_home.php'
    ),
    array(
        'title' => $lang['s3_dashboard'] 
    )
);
renderBreadcrumbs($links_trail);

// Show page title and introductory text.
?>
<div class="BasicsBox">
<h1><?php echo $lang['s3_dashboard_title'];?></h1>
<p><?php echo $lang['s3_dashboard_introtext'];?></p>
<p><b><?php echo $lang['s3_dashboard_parameters'];?></b></p>

<table class="InfoTable"><?php

// S3 PROVIDER AND BUCKET PARAMETERS section.
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

// Get the S3 API description.
$result = s3_get_api();
?><tr><td><?php echo $lang['s3_api'];?></td><td><?php echo $result['service_fullname'] . ' (' . $result['api_version'] . ')'; ?></td><td><b><?php echo boolean_convert($result['status'], 'ok');?></b></td></tr><?php

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
?><tr><td><?php echo $lang['s3_provider'];?></td><td><?php echo $result;?></td><td><b><?php echo boolean_convert($status, 'ok');?></b></td></tr><?php

// S3 storage endpoint check.
$result = s3_get_endpoint();
?><tr><td><?php echo $lang['s3_endpoint'];?></td><td><?php echo $result['scheme'] . '://' . $result['endpoint'];?></td><td><b><?php echo boolean_convert($result['status'], 'ok');?></b></td></tr><?php

// S3 bucket accessibility check.
$result = s3_bucket_head($s3_storage_bucket);
$result = boolean_convert($result, 'ok');
?><tr><td><?php echo $lang['s3_bucket_access'];?></td><td><?php echo $s3_storage_bucket;?></td><td><b><?php echo $result;?></b></td></tr><?php

// Get S3 bucket location.
$result = s3_bucket_location($s3_storage_bucket);
?><tr><td><?php echo $lang['s3_region'];?></td><td><?php echo $result['LocationConstraint'];?></td><td><b><?php echo boolean_convert($result['status'], 'ok');?></b></td></tr><?php

// Get S3 bucket owner.
$result = s3_bucket_owner($s3_storage_bucket);
?><tr><td><?php echo $lang['s3_owner'];?></td><td><?php echo $result['name'];?></td><td><b><?php echo $result['status'];?></b></td></tr><?php
?><tr><td><?php echo $lang['s3_id'];?></td><td><?php echo $result['id'];?></td><td><b><?php echo $result['status'];?></b></td></tr><?php

// Get S3 bucket storage class.
if(isset($s3_storage_class))
    {
    $result = s3_storage_class($s3_storage_class);
    }
else
    {
    $result['name'] = $lang['status-fail'];
    }
?><tr><td><?php echo $lang['s3_storage_class'];?></td><td><?php echo $result['name'];?></td><td><b><?php echo $result['status'];?></b></td></tr>
</table><?php

// AMAZON WEB SERVICES (AWS) CloudWatch S3 METRICS section.
if($s3_storage_provider == 'AWS')
    { ?>
    <table class="InfoTable">
    <p></br><b><?php echo $lang['cw_title']; ?></b></p><?php

    // Set CloudWatch parameters.
    $cw_namespace = 'AWS/S3';
    $cw_30day_starttime = strtotime('-30 days');
    $cw_endtime = strtotime('now');

    // Get the S3 bucket size.
    $storage_class = s3_storage_class($s3_storage_class, false);
    $dimensions = cw_dimension('StorageType', $storage_class['code'], 'BucketName', $s3_storage_bucket);
    $result = s3_metric_statistics($cw_namespace, 'BucketSizeBytes', $dimensions, $cw_30day_starttime, $cw_endtime, 86400, array('Average'), 'Bytes');
    $result_size = '?';
    if(!empty($result['Datapoints'][0]['Average']))
        {
        $result_size = formatfilesize($result['Datapoints'][0]['Average']);
        }

    $timestamp = '';
    $bucket_size_cumulative = 0;
    $counter = 0;
    if(!empty($result['Datapoints']))
        {
        foreach($result['Datapoints'] as $datapoint)
            {
            $bucket_size_cumulative += $datapoint['Average'];
            ++$counter;

            if($timestamp == '' || $datapoint['Timestamp'] > $timestamp)
                {
                $timestamp = $datapoint['Timestamp'];
                $bucket_size = $datapoint['Average'];
                }
            }
        $result = formatfilesize($bucket_size_cumulative / $counter);
        }
    else
        {
        $result = '?';
        }

    ?><tr><td colspan="2"><?php echo $lang['s3_bucket_size'];?></td><td><b><?php echo $result_size . ' / ' . $result;?></b></td></tr><?php

    // Get the number of S3 objects in a bucket.
    $dimensions = cw_dimension('StorageType', 'AllStorageTypes', 'BucketName', $s3_storage_bucket);
    $result = s3_metric_statistics($cw_namespace, 'NumberOfObjects', $dimensions, $cw_30day_starttime, $cw_endtime, 86400, array('Average'), 'Count');

    $timestamp = '';
    $object_number_cumulative = 0;
    $counter = 0;
    if(!empty($result['Datapoints']))
        {
        foreach($result['Datapoints'] as $datapoint)
            {
            $object_number_cumulative += $datapoint['Average'];
            ++$counter;

            if($timestamp == '' || $datapoint['Timestamp'] > $timestamp)
                {
                $timestamp = $datapoint['Timestamp'];
                $object_number = $datapoint['Average'];
                }
            }
        $result = $object_number;
        $result1 = round($object_number_cumulative / $counter, 0);
        }
    else
        {
        $result = '?';
        $result1 = '?';
        }

    ?><tr><td colspan="2"><?php echo $lang['s3_object_number'];?></td><td><b><?php echo $result . ' / ' . $result1;?></b></td></tr><?php

    // Get the files uploaded size.
    $dimensions = cw_dimension('BucketName', $s3_storage_bucket, 'FilterId', 'EntireBucket');
    $result = s3_metric_statistics($cw_namespace, 'BytesUploaded', $dimensions, $cw_30day_starttime, $cw_endtime, 86400, array('Average'), 'Bytes');

    $timestamp = '';
    $upload_size_cumulative = 0;
    if(!empty($result['Datapoints']))
        {
        foreach($result['Datapoints'] as $datapoint)
            {
            $upload_size_cumulative += $datapoint['Average'];

            if($timestamp == '' || $datapoint['Timestamp'] > $timestamp)
                {
                $timestamp = $datapoint['Timestamp'];
                $upload_size = $datapoint['Average'];
                }
            }
        $result = formatfilesize($upload_size);
        $result1 = formatfilesize($upload_size_cumulative);
        }
    else
        {
        $result = '?';
        $result1 = '?';
        }

    ?><tr><td colspan="2"><?php echo $lang['s3_uploaded_size'];?></td><td><b><?php echo $result . ' / ' . $result1;?></b></td></tr><?php

    // Get the files downloaded size.
    $dimensions = cw_dimension('BucketName', $s3_storage_bucket, 'FilterId', 'EntireBucket');
    $result = s3_metric_statistics($cw_namespace, 'BytesDownloaded', $dimensions, $cw_30day_starttime, $cw_endtime, 86400, array('Average'), 'Bytes');

    $timestamp = '';
    $download_size_cumulative = 0;
    if(!empty($result['Datapoints']))
        {
        foreach($result['Datapoints'] as $datapoint)
            {
            $download_size_cumulative += $datapoint['Average'];

            if($timestamp == '' || $datapoint['Timestamp'] > $timestamp)
                {
                $timestamp = $datapoint['Timestamp'];
                $download_size = $datapoint['Average'];
                }
            }
        $result = formatfilesize($download_size);
        $result1 = formatfilesize($download_size_cumulative);
        }
    else
        {
        $result = '?';
        $result1 = '?';
        }

    ?><tr><td colspan="2"><?php echo $lang['s3_downloaded_size']; ?></td><td><b><?php echo $result . ' / ' . $result1;?></b></td></tr><?php

    // Get the request latencies.
    $dimensions = cw_dimension('BucketName', $s3_storage_bucket, 'FilterId', 'EntireBucket');
    $result = s3_metric_statistics($cw_namespace, 'FirstByteLatency', $dimensions, $cw_30day_starttime, $cw_endtime, 86400, array('Average'), 'Milliseconds');

    $timestamp = '';
    $first_request_cumulative = 0;
    $counter = 0;
    if(!empty($result['Datapoints']))
        {
        foreach($result['Datapoints'] as $datapoint)
            {
            $first_request_cumulative += $datapoint['Average'];
            ++$counter;

            if($timestamp == '' || $datapoint['Timestamp'] > $timestamp)
                {
                $timestamp = $datapoint['Timestamp'];
                $first_request = $datapoint['Average'];
                }
            }
        $result = round($first_request, 1) . $lang['milliseconds'];
        $result1 = round($first_request_cumulative / $counter, 1) . $lang['milliseconds'];
        }
    else
        {
        $result = '?';
        $result1 = '?';
        }

    ?><tr><td colspan="2"><?php echo $lang['s3_first_request'];?></td><td><b><?php echo $result . ' / ' . $result1;?></b></td></tr><?php

    $result = s3_metric_statistics($cw_namespace, 'TotalRequestLatency', $dimensions, $cw_30day_starttime, $cw_endtime, 86400, array('Average'), 'Milliseconds');

    $timestamp = '';
    $total_request_cumulative = 0;
    $counter = 0;
    if(!empty($result['Datapoints']))
        {
        foreach($result['Datapoints'] as $datapoint)
            {
            $total_request_cumulative += $datapoint['Average'];
            ++$counter;

            if($timestamp == '' || $datapoint['Timestamp'] > $timestamp)
                {
                $timestamp = $datapoint['Timestamp'];
                $total_request = $datapoint['Average'];
                }
            }
        $result = round($total_request, 1) . $lang['milliseconds'];
        $result1 = round($total_request_cumulative / $counter, 1) . $lang['milliseconds'];
        }
    else
        {
        $result = '?';
        $result1 = '?';
        }

    ?><tr><td colspan="2"><?php echo $lang['s3_total_request'];?></td><td><b><?php echo $result . ' / ' . $result1;?></b></td></tr><?php

    // Get the total number of HTTP requests.
    $dimensions = cw_dimension('BucketName', $s3_storage_bucket, 'FilterId', 'EntireBucket');
    $result = s3_metric_statistics($cw_namespace, 'AllRequests', $dimensions, $cw_30day_starttime, $cw_endtime, 86400, array('Average'), 'Count');

    $timestamp = '';
    $all_requests_cumulative = 0;
    if(!empty($result['Datapoints']))
        {
        foreach($result['Datapoints'] as $datapoint)
            {
            $all_requests_cumulative += $datapoint['Average'];

            if($timestamp == '' || $datapoint['Timestamp'] > $timestamp)
                {
                $timestamp = $datapoint['Timestamp'];
                $all_requests = $datapoint['Average'];
                }
            }
        $result = $all_requests;
        $result1 = $all_requests_cumulative;
        }
    else
        {
        $result = '?';
        $result1 = '?';
        }

    ?><tr><td colspan="2"><?php echo $lang['s3_all_requests'];?></td><td><b><?php echo $result . ' / ' . $result1;?></b></td></tr><?php

    // Get the total number of HTTP HEAD requests.
    $dimensions = cw_dimension('BucketName', $s3_storage_bucket, 'FilterId', 'EntireBucket');
    $result = s3_metric_statistics($cw_namespace, 'HeadRequests', $dimensions, $cw_30day_starttime, $cw_endtime, 86400, array('Average'), 'Count');

    $timestamp = '';
    $head_requests_cumulative = 0;
    if(!empty($result['Datapoints']))
        {
        foreach($result['Datapoints'] as $datapoint)
            {
            $head_requests_cumulative += $datapoint['Average'];

            if($timestamp == '' || $datapoint['Timestamp'] > $timestamp)
                {
                $timestamp = $datapoint['Timestamp'];
                $head_requests = $datapoint['Average'];
                }
            }
        $result = $head_requests;
        $result1 = $head_requests_cumulative;
        }
    else
        {
        $result = '?';
        $result1 = '?';
        }

    ?><tr><td colspan="2"><?php echo $lang['s3_head_requests'];?></td><td><b><?php echo $result . ' / ' . $result1;?></b></td></tr><?php

    // Get the total number of HTTP LIST requests.
    $dimensions = cw_dimension('BucketName', $s3_storage_bucket, 'FilterId', 'EntireBucket');
    $result = s3_metric_statistics($cw_namespace, 'ListRequests', $dimensions, $cw_30day_starttime, $cw_endtime, 86400, array('Average'), 'Count');

    $timestamp = '';
    $list_requests_cumulative = 0;
    if(!empty($result['Datapoints']))
        {
        foreach($result['Datapoints'] as $datapoint)
            {
            $list_requests_cumulative += $datapoint['Average'];

            if($timestamp == '' || $datapoint['Timestamp'] > $timestamp)
                {
                $timestamp = $datapoint['Timestamp'];
                $list_requests = $datapoint['Average'];
                }
            }
        $result = $list_requests;
        $result1 = $list_requests_cumulative;
        }
    else
        {
        $result = '?';
        $result1 = '?';
        }

    ?><tr><td colspan="2"><?php echo $lang['s3_list_requests'];?></td><td><b><?php echo $result . ' / ' . $result1;?></b></td></tr><?php

    // Get the total number of HTTP GET requests.
    $dimensions = cw_dimension('BucketName', $s3_storage_bucket, 'FilterId', 'EntireBucket');
    $result = s3_metric_statistics($cw_namespace, 'GetRequests', $dimensions, $cw_30day_starttime, $cw_endtime, 86400, array('Average'), 'Count');

    $timestamp = '';
    $get_requests_cumulative = 0;
    if(!empty($result['Datapoints']))
        {
        foreach($result['Datapoints'] as $datapoint)
            {
            $get_requests_cumulative += $datapoint['Average'];

            if($timestamp == '' || $datapoint['Timestamp'] > $timestamp)
                {
                $timestamp = $datapoint['Timestamp'];
                $get_requests = $datapoint['Average'];
                }
            }
        $result = $get_requests;
        $result1 = $get_requests_cumulative;
        }
    else
        {
        $result = '?';
        $result1 = '?';
        }

    ?><tr><td colspan="2"><?php echo $lang['s3_get_requests'];?></td><td><b><?php echo $result . ' / ' . $result1;?></b></td></tr><?php

    // Get the total number of HTTP PUT requests.
    $dimensions = cw_dimension('BucketName', $s3_storage_bucket, 'FilterId', 'EntireBucket');
    $result = s3_metric_statistics($cw_namespace, 'PutRequests', $dimensions, $cw_30day_starttime, $cw_endtime, 86400, array('Average'), 'Count');

    $timestamp = '';
    $put_requests_cumulative = 0;
    if(!empty($result['Datapoints']))
        {
        foreach($result['Datapoints'] as $datapoint)
            {
            $put_requests_cumulative += $datapoint['Average'];

            if($timestamp == '' || $datapoint['Timestamp'] > $timestamp)
                {
                $timestamp = $datapoint['Timestamp'];
                $put_requests = $datapoint['Average'];
                }
            }
        $result = $put_requests;
        $result1 = $put_requests_cumulative;
        }
    else
        {
        $result = '?';
        $result1 = '?';
        }

    ?><tr><td colspan="2"><?php echo $lang['s3_put_requests'];?></td><td><b><?php echo $result . ' / ' . $result1;?></b></td></tr><?php

    // Get the total number of HTTP DELETE requests.
    $dimensions = cw_dimension('BucketName', $s3_storage_bucket, 'FilterId', 'EntireBucket');
    $result = s3_metric_statistics($cw_namespace, 'DeleteRequests', $dimensions, $cw_30day_starttime, $cw_endtime, 86400, array('Average'), 'Count');

    $timestamp = '';
    $delete_requests_cumulative = 0;
    if(!empty($result['Datapoints']))
        {
        foreach($result['Datapoints'] as $datapoint)
            {
            $delete_requests_cumulative += $datapoint['Average'];

            if($timestamp == '' || $datapoint['Timestamp'] > $timestamp)
                {
                $timestamp = $datapoint['Timestamp'];
                $delete_requests = $datapoint['Average'];
                }
            }
        $result = $delete_requests;
        $result1 = $delete_requests_cumulative;
        }
    else
        {
        $result = '?';
        $result1 = '?';
        }

    ?><tr><td colspan="2"><?php echo $lang['s3_delete_requests'];?></td><td><b><?php echo $result . ' / ' . $result1;?></b></td></tr><?php

    // Get the number of HTTP 4xx errors.
    $dimensions = cw_dimension('BucketName', $s3_storage_bucket, 'FilterId', 'EntireBucket');
    $result = s3_metric_statistics($cw_namespace, '4xxErrors', $dimensions, $cw_30day_starttime, $cw_endtime, 86400, array('Average'), 'Count');

    $timestamp = '';
    $http_4xx_errors_cumulative = 0;
    if(!empty($result['Datapoints']))
        {
        foreach($result['Datapoints'] as $datapoint)
            {
            $http_4xx_errors_cumulative += $datapoint['Average'];

            if($timestamp == '' || $datapoint['Timestamp'] > $timestamp)
                {
                $timestamp = $datapoint['Timestamp'];
                $http_4xx_errors = $datapoint['Average'];
                }
            }
        $result = round($http_4xx_errors, 1);
        $result1 = round($http_4xx_errors_cumulative, 1);
        }
    else
        {
        $result = '?';
        $result1 = '?';
        }

    ?><tr><td colspan="2"><?php echo $lang['s3_4xx_errors'];?></td><td><b><?php echo $result . ' / ' . $result1;?></b></td></tr><?php

    // Get the number of HTTP 5xx errors.
    $dimensions = cw_dimension('BucketName', $s3_storage_bucket, 'FilterId', 'EntireBucket');
    $result = s3_metric_statistics($cw_namespace, '5xxErrors', $dimensions, $cw_30day_starttime, $cw_endtime, 86400, array('Average'), 'Count');

    $timestamp = '';
    $http_5xx_errors_cumulative = 0;
    if(!empty($result['Datapoints']))
        {
        foreach($result['Datapoints'] as $datapoint)
            {
            $http_5xx_errors_cumulative += $datapoint['Average'];

            if($timestamp == '' || $datapoint['Timestamp'] > $timestamp)
                {
                $timestamp = $datapoint['Timestamp'];
                $http_5xx_errors = $datapoint['Average'];
                }
            }
        $result = round($http_5xx_errors, 1);
        $result1 = round($http_5xx_errors_cumulative, 1);
        }
    else
        {
        $result = '?';
        $result1 = '?';
        }

    ?><tr><td colspan="2"><?php echo $lang['s3_5xx_errors'];?></td><td><b><?php echo $result . ' / ' . $result1;?></b></td></tr>
    </table>

    <?php // CloudWatch metrics table bottom text. ?>
    <p><?php echo $lang['cw_notes'] . ' (' . nicedate(date('Y-m-d H:i:s'), true) . ').'; ?></p><?php
    }
?>
</div><?php

include '../../../../include/footer.php';
