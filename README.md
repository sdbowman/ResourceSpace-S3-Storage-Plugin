# ResourceSpace-S3-Storage-Plugin
A ResourceSpace v9.6 plugin to enable S3 object-based storage for original resource files.

Place in the ../plugins folder and enable via the Admin/System/Manage Plugins page.

Object-based storage often has a lower cost than traditional block-based file storage. While the cloud-based Simple Storage Service (S3) was originally developed by Amazon Web Services (AWS), it is also provided by numerous other cloud-based vendors and local appliances.  When enabled, ResourceSpace will store the original resource and alternative files in a specified S3 bucket, with the derived preview images stored locally on your server in the same ../filestore location as before. For more information on using object-based S3 storage, see https://en.wikipedia.org/wiki/Object_storage and https://en.wikipedia.org/wiki/Amazon_S3.

Storage of original resource files is in a specified S3 bucket (a container for file objects).  Preview and other resized images are stored in the existing ../filestore/ location as before.  Object-based storage systems do not use the concept of a folder or directory structure, as each file stored in them is referred to as an object.  However, the object name can contain the desired path structure, preserving the original folder structure used elsewhere. For this plugin, original resource files stored in S3 are named by their unique ResourceSpace pathname after the ../filestore folder name to preserve the filestore structure for easy conversion from one storage system to another and to help reduce vendor and/or system lock in.

Additional S3 storage providers and/or S3 storage classes can be added in the configuration arrays in the code. You will also need to update the s3_storage_setup() and s3_get_endpoint() functions in ../plugins/s3_storage/include/s3_storage_functions.php with any custom S3 vendor settings at a minimum.

Before uploading any resources (files), ensure that fields below are correctly configured and use the S3 Installation Check at Admin, System, S3 Installation Check. If any lines indicate FAIL, as a start, check that your server date and time are correct. If your server time is different than your S3 provider over than about 15 minutes, a provider error will be generated. Use your S3 provider bucket management system to verify the correct uploads are occuring for a few test resource uploads. 
