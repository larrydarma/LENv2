<?php
//incluir libreria para conexion de BD
include("conexion.php");
//Recibir datos del frontend
$usu=$_POST['username'];
$contra=$_POST['password'];

//validar
if(empty($usu)||empty($contra)){
header("Location:login.php");
exit();
}
//verificar usuario valido
$q="SELECT * FROM usuario where Nombre='$usu' and Contraseña='$contra' and Estado='activo'";
$r=mysqli_query($con,$q);
  if(mysqli_num_rows($r)>0){
  session_start(); 
 
  $_SESSION['Nombre']=$usu;
  $_SESSION['Contraseña']=$contra;
    header("Location:inteligencia.php");
  } else{
    header("Location:login.php");
  }

?>