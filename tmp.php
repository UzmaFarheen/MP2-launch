?php
#echo "Hello";
session_start();
$useremail=$_SESSION['useremail'];
$username=$_SESSION['firstname'];
$phone=$_SESSION['phone'];
#echo $useremail;
require 'vendor/autoload.php';
$rds = new Aws\Rds\RdsClient([
    'version' => 'latest',
    'region'  => 'us-east-1'
]);
$resultrds = $rds->describeDBInstances(array(
    'DBInstanceIdentifier' => 'mp1'
   
));
$endpoint = $resultrds['DBInstances'][0]['Endpoint']['Address'];
 #   echo "============\n". $endpoint . "================";
$link = mysqli_connect($endpoint,"UzmaFarheen","UzmaFarheen","Project");
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}
else {
echo "Success";
}
# create sns client
$sns = new Aws\Sns\SnsClient([
    'version' => 'latest',
    'region'  => 'us-east-1'
]);
$result1 = $result->listTopics(array(
    
));
#print_r($result1);
//to retrieve Topic ARN of ImageTopicSK
foreach ($result1['Topics'] as $key => $value){
if(preg_match("/snspicture/", $result1['Topics'][$key]['TopicArn'])){
$topicARN =$result['Topics'][$key]['TopicArn'];
}
}
$result = $sns->subscribe(array(
    // TopicArn is required
    'TopicArn' => $topicARN,
    // Protocol is required
    'Protocol' => 'email',
    'Endpoint' => $useremail,
));

if (!($stmt = $link->prepare("INSERT INTO ITMO544 (uname,email,phoneforsms) VALUES (?,?,?)"))) {
    echo "Prepare failed: (" . $link->errno . ") " . $link->error;
}
$stmt->bind_param("sss",$username,$useremail,$phone);
if (!$stmt->execute()) {
    echo "Execute failed: (" . $stmt->errno0 . ") " . $stmt->error;
}
# printf("%d Row inserted.\n", $stmt->affected_rows);
$stmt->close();
echo "To subscribe your e-mail please click on <a href='index.php'/>index!</a>";
