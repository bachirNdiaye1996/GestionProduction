<?php

    session_start(); 

    if(!$_SESSION['niveau']){
        header('Location: ../../../../404.php');
    }
    
    // Pour la supression d'une reception avec un get de idsupreception
    include "../../connexion/conexiondb.php";


    if(isset($_GET['idsuptransfert'])){
        $id = $_GET['idsuptransfert'];
        $idtransfertsup = $_GET['idtransfertsup'];
        $sql = "UPDATE `transfertdetails` set `actif`=0 where idtransfertdetail=$idtransfertsup";
        $db->query($sql);

        header("location: detailTransfert.php?idtransfert=$id");
        exit;
    }

    if(isset($_GET['idtransfertreel'])){
        $id = $_GET['idtransfertreel'];
        $sql = "UPDATE `transfert` set `actif`=0 where idtransfert=$id";
        $db->query($sql);

        //Pour suprimer tous les details de ce transfert
        $sql = "DELETE from `transfertdetails` where idtransfert=$id";
        $db->query($sql);
        
        header("location: transfert.php");
        exit;
    }

?>