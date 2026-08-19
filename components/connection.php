<?php 
$conn  = mysqli_connect("localhost","root","","nmsc");

if(!$conn){
    die("Connection failed: " . mysqli_connect_error());
}
