<?php

    session_start(); 

    if(!$_SESSION['niveau']){
        header('Location: ../../../404.php');
    }

    include "../../connexion/conexiondb.php";
    include "./mailReception.php";

    // Pour insertion une nouvelle reception
        if(isset($_GET['creerReception'])){

            $user=htmlspecialchars($_GET['user']);

            $insertUser=$db->prepare("INSERT INTO `receptionmachine` (`idreceptionmachine`, `datecreation`, `user`, `actif`) 
                VALUES (NULL, current_timestamp(), ?, '1');");
            $insertUser->execute(array($user));

            header("location: reception.php");
            exit;
        }
    //Fin insertion une nouvelle reception
?>