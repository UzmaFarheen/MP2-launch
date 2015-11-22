<?php
echo "Hello World1";
session_start();
var_dump($_POST);
if(!empty($_POST)){
echo $_POST['useremail'];
echo $_POST['phone'];
echo $_POST['firstname'];
$_SESSION['firstname']=$_POST['firstname'];
$_SESSION['phone']=$_POST['phone'];
$_SESSION['useremail']=$_POST['useremail'];
}
else
{
echo "post empty";
}
$uploaddir = '/tmp/';
$uploadfile = $uploaddir . basename($_FILES['userfile']['name']);
print '<pre>';
if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
  echo "File is valid, and was successfully uploaded.\n";
}
else {
    echo "Possible file upload attack!\n";
}
echo 'Here is some more debugging info:';
print_r($_FILES);
print "</pre>";
require 'vendor/autoload.php';
$s3 = new Aws\S3\S3Client([
    'version' => 'latest',
    'region'  => 'us-east-1'
]);
#print_r($s3);
$bucket = uniqid("mpuzma",false);
#$result = $s3->createBucket(array(
#    'Bucket' => $bucket
#));
#
## AWS PHP SDK version 3 create bucket
$result = $s3->createBucket([
    'ACL' => 'public-read',
    'Bucket' => $bucket
]);
#print_r($result);
$result = $s3->putObject([
    'ACL' => 'public-read',
    'Bucket' => $bucket,
   'Key' => $uploadfile,
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
$link = mysqli_connect($endpoint,"UzmaFarheen","UzmaFarheen","Project");
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}
else {
echo "Success";
}
#create sns client
$result = new Aws\Sns\SnsClient([
    'version' => 'latest',
    'region'  => 'us-east-1'
]);
#print_r($result);
//echo "sns Topic";
$result = $sns->listTopics(array(
));
foreach ($result['Topics'] as $key => $value){
if(preg_match("/snspicture/", $result['Topics'][$key]['TopicArn'])){
$topicARN =$result['Topics'][$key]['TopicArn'];
}
}
$uname=$_POST['username'];
$email = $_POST['useremail'];
$phoneforsms = $_POST['phone'];
$raws3url = $url; 
$finisheds3url = "none";
$jpegfilename = basename($_FILES['userfile']['name']);
$state=0;
$res = $link->query("SELECT * FROM ITMO544 where email='$email'");
if($res->num_rows>0){
if (!($stmt = $link->prepare("INSERT INTO ITMO544 (uname,email,phoneforsms,raws3url,finisheds3url,jpegfilename,state) VALUES (?,?,?,?,?,?,?)"))) {
    echo "Prepare failed: (" . $link->errno . ") " . $link->error;
}
$stmt->bind_param("ssssssi",$uname,$email,$phoneforsms,$raws3url,$finisheds3url,$jpegfilename,$state);
if (!$stmt->execute()) {
    echo "Execute failed: (" . $stmt->errno0 . ") " . $stmt->error;
}
printf("%d Row inserted.\n", $stmt->affected_rows);
$stmt->close();
$pub = $result->publish(array(
    'TopicArn' => $topicARN,
    // Message is required
    'Subject' => 'Test',
    'Message' => 'msg',
    
    
));
$link->real_query("SELECT * FROM ITMO544");
$res = $link->use_result();
echo "Result set order...\n";
while ($row = $res->fetch_assoc()) {
    echo $row['id'] . " " . $row['email']. " " . $row['phoneforsms'];
}
$link->close();
$url	= "gallery1.php";
   header('Location: ' . $url, true);
   die();
}
else 
{
$url	= "tmp.php";
   header('Location: ' . $url, true);
   die();
}
?> 
