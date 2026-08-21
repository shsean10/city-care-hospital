<?php

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "hospital_management"
);

if(!$conn){
    die("Connection Failed: " . mysqli_connect_error());
}

?>