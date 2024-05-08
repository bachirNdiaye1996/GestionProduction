<?php

    session_start(); 

    if(!$_SESSION['niveau']){
        header('Location: ../../../404.php');
    }

    // Pour la supression d'une reception avec un get de idsupreception pour reception detail
    include "../connexion/conexiondb.php";


    if(isset($_GET['idsupreceptionDetail'])){
        $id = $_GET['idsupreceptionDetail'];
        $sql = "DELETE from `matiere` where idmatiere=$id";
        $db->query($sql);

        $epaisseur = $_GET['epaisseur'];
        $nbbobine = $_GET['nbbobine'];
        $lieutransfert = $_GET['lieutransfert'];
        if(($lieutransfert == "Metal1")){ // Vérifie le type de transfert
            //Debut inserer le nombre de bobine par epaisseur
            $req ="UPDATE epaisseur SET `$epaisseur` = `$epaisseur` + ? where `id`=1;";  //Metal 1
            //$db->query($req); 
            $reqtitre = $db->prepare($req);
            $reqtitre->execute(array($nbbobine));
            //Fin inserer le nombre de bobine par epaisseur
        }elseif(($lieutransfert == "Niambour")){
            //Debut inserer le nombre de bobine par epaisseur
            $req ="UPDATE epaisseur SET `$epaisseur` = `$epaisseur` + ? where `id`=3;";  // Metal 3 dit Niambour
            //$db->query($req); 
            $reqtitre = $db->prepare($req);
            $reqtitre->execute(array($nbbobine));
            //Fin inserer le nombre de bobine par epaisseur
        }
        elseif(($lieutransfert == "Metal Mbao")){
            //Debut inserer le nombre de bobine par epaisseur
            $req ="UPDATE epaisseur SET `$epaisseur` = `$epaisseur` + ? where `id`=6;";  // Metal Mbao dit Niambour
            //$db->query($req); 
            $reqtitre = $db->prepare($req);
            $reqtitre->execute(array($nbbobine));
            //Fin inserer le nombre de bobine par epaisseur
        }

        header("location: detailsReception.php?idreception=$_GET[idreceptionDemandeAccepter]");
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

    // Pour changer le status de la reception 
    if(isset($_GET['idStatusReception'])){
        //Pour suprimer tous les details de cette reception
        $id = $_GET['idStatusReception'];
        $sql = "UPDATE `reception` set `status`='Terminée' where idreception=$id";
        $db->query($sql);

        header("location: detailsReception.php?idreception=$id");
        exit;
    }


?>