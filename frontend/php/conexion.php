<?php
$con=mysqli_connect("localhost","root","","lenfind");
if(!$con){
    die("Error de conexion".mysqli_connect_error());
}  
mysqli_set_charset($con, "utf8");
  ?>