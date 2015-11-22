<?php
session_start();
require 'vendor/autoload.php';
#include CSS Style Sheet
   echo "<link rel='stylesheet' type='text/css' href='fotorama.css' />";
#include Java Script
echo "<script type='text/javascript' src='fotorama.js'></script>";

# Creating a client for the s3 bucket
use Aws\Rds\RdsClient;
$client = new Aws\Rds\RdsClient([
 'version' => 'latest',
 'region'  => 'us-east-1'
]);
$result = $client->describeDBInstances(array(
    'DBInstanceIdentifier' => 'mp1',
));
$endpoint = $result['DBInstances'][0]['Endpoint']['Address'];
echo "============\n". $endpoint . "================";
# Connecting to the database
$link = mysqli_connect($endpoint,"UzmaFarheen","UzmaFarheen","Project") or die("Error " . mysqli_error($link));
/* Checking the database connection */
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}
$link->real_query("SELECT * FROM ITMO544");
$res = $link->use_result();
?>
