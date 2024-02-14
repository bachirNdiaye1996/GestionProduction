<?php
    // Pour la supression d'une reception avec un get de idsupreception pour reception detail
    include "../connexion/conexiondb.php";


    if(isset($_GET['idsupreceptionDetail'])){
        $id = $_GET['idsupreceptionDetail'];
        $sql = "DELETE from `matiere` where idmatiere=$id";
        $db->query($sql);

        header('location: reception.php');
        exit;
    }


    // Pour la supression d'une reception avec un get de idsupreception 
    if(isset($_GET['idsupreception'])){
        $id = $_GET['idsupreception'];
        $sql = "UPDATE `reception` set `actif`=0 where idreception=$id";
        $db->query($sql);

        //Pour suprimer tous les details de cette reception
        $id = $_GET['idsupreception'];
        $sql = "DELETE from `matiere` where idreception=$id";
        $db->query($sql);

        header('location: reception.php');
        exit;
    }

    // Pour la supression d'une reception planifiée avec un get de idsupreceptionPlanifie 
    if(isset($_GET['idsupreceptionPlanifie'])){
        $id = $_GET['idsupreceptionPlanifie'];
        $sql = "UPDATE `receptionplanifiee` set `actif`=0 where idreception=$id";
        $db->query($sql);

        //Pour suprimer tous les details de cette reception
        $id = $_GET['idsupreceptionPlanifie'];
        $sql = "DELETE from `matiereplanifie` where idreception=$id";
        $db->query($sql);

        header('location: receptionPlanifie.php');
        exit;
    }

    // Pour la supression d'une reception planifiée detail avec un get de idsupreceptionPlanifie 
    if(isset($_GET['idsupreceptionPlanifieDetails'])){
        //Pour suprimer tous les details de cette reception
        $id = $_GET['idsupreceptionPlanifieDetails'];
        $sql = "DELETE from `matiereplanifie` where idmatiereplanifie=$id";
        $db->query($sql);

        header('location: detailsReceptionPlanifie.php');
        exit;
    }


?>