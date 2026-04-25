<?php

$conn = new mysqli("localhost","root","","attendance_system");

if($conn->connect_error){
die("Connection Failed");
}

?>