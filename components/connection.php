<?php 
$conn  = mysqli_connect("localhost","root","Barrientos90","nmsc");

if(!$conn){
    die("Connection failed: " . mysqli_connect_error());
}