<?php
session_start();
require 'vendor/autoload.php';

// var_dump($_POST);
// if(!empty($_POST)){
// echo $_POST['useremail'];
// echo $_POST['phone'];
// }
// else
// {
// echo "post empty";
// }

$uploaddir = '/tmp/';
$uploadfile = $uploaddir . basename($_FILES['userfile']['name']);
print '<pre>';
if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
  echo "File is valid, and successfully uploaded.\n";
} else {
    echo "Possible file upload!\n";
}
echo 'Here is some more debugging info:';
print_r($_FILES);
print "</pre>";

#use Aws\S3\S3Client;
#$client = S3Client::factory();
$s3 = new Aws\S3\S3Client([
    'version' => 'latest',
    'region'  => 'us-east-1'
]);
$bucket = uniqid("mpuzma",false);
## AWS PHP SDK version 3 create bucket
$result = $s3->createBucket([
    'ACL' => 'public-read',
    'Bucket' => $bucket
]);
$s3->waitUntil('BucketExists', array( 'Bucket'=> $bucket));
#print_r($result);
$result = $s3->putObject([
'ACL' => 'public-read',
'Bucket' => $bucket,
'Key' =>  $uploadfile,
'ContentType' => $_FILES['userfile']['type'],
'Body' => fopen($uploadfile,'r+')
]);
$url = $result['ObjectURL'];
echo $url;
$rds = new Aws\Rds\RdsClient([
    'version' => 'latest',
    'region'  => 'us-east-1'
]);
$result = $rds->describeDBInstances(array(
    'DBInstanceIdentifier' => 'mp1'
  ));
$endpoint = $result['DBInstances'][0]['Endpoint']['Address'];
    echo "============\n". $endpoint . "================";
$link = mysqli_connect($endpoint,"UzmaFarheen","UzmaFarheen","Project") or die("Error " . mysqli_error($link));
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}
else {
echo "Success";
}
if (!($stmt = $link->prepare("INSERT INTO ITMO544 (uname,email,phoneforsms,raws3url,finisheds3url,jpegfilename,state) VALUES (?,?,?,?,?,?,?)"))) {
    echo "Prepare failed: (" . $link->errno . ") " . $link->error;
}
$uname="Uzma";
$email = $_POST['useremail'];
$phoneforsms = $_POST['phone'];
$raws3url = $url; 
$finisheds3url = "none";
$jpegfilename = basename($_FILES['userfile']['name']);
$state=0;
$stmt->bind_param("ssssssi",$uname,$email,$phoneforsms,$raws3url,$finisheds3url,$jpegfilename,$state);
if (!$stmt->execute()) {
    echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
}
printf("%d Row inserted.\n", $stmt->affected_rows);
$stmt->close();
$link->real_query("SELECT * FROM ITMO544");
$res = $link->use_result();
echo "Result set order...\n";
while ($row = $res->fetch_assoc()) {
    echo $row['id'] . " " . $row['email']. " " . $row['phoneforsms'];
}
$link->close();
header('Location:gallery.php');
die();
?> 

  
