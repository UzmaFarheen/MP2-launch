<?php
require 'vendor/autoload.php';
$rds = new Aws\Rds\RdsClient([
 'version' => 'latest',
 'region'  => 'us-east-1'
]);

$s3 = new Aws\S3\S3Client([
 'version' => 'latest',
 'region'  => 'us-east-1'
]);

$result = $rds->describeDBInstances(array(
 'DBInstanceIdentifier' => 'mp1'
));
$endpoint = $result['DBInstances'][0]['Endpoint']['Address'];
 echo "============\n". $endpoint . "================\n";
 $link = new mysqli($endpoint,"UzmaFarheen","UzmaFarheen","Project",3306) or die("Error " . mysqli_error($link)); 
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
 exit();
}
$link->query("CREATE TABLE ITMO544 
(
ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
uname VARCHAR(20),
email VARCHAR(20),
phoneforsms VARCHAR(20),
raws3url VARCHAR(256),
finisheds3url VARCHAR(256),
filename VARCHAR(256),
state tinyint(3) CHECK(state IN(0,1,2)),
datetime timestamp
)");

shell_exec("chmod 700 setup.php");
?>
