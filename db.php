<?php
$conn = mysqli_connect("localhost","root","","green_bites");
if(!$conn){
    die("Database Connection Failed: " . mysqli_connect_error());
}
?>
