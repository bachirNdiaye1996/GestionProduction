<?php
    
    session_start(); 
    
    if(!$_SESSION){
        header("location: ../../404.php");
        return 0;
    }

    include "../../connexion/conexiondb.php";


    //Variables
    // Il faut savoir que metal3 est remplacer par niambour
    $valideExportation="";
    $ProblemeNbBobineDepart="";
    $Iddepart=null;                       // Variable qui nous permet d'optenir l'Id de la matiere de départ
    $idcranteuseq1 = $_GET['idcranteuseq1'];  // On recupére l'ID exportation par get
    $quart = $_GET['quart'];


    //Insertion des réceptions
    if(isset($_POST['CreerExportation'])){

        $idexportation=htmlspecialchars($_POST['idexportation']);
        $saisisseur=htmlspecialchars( $_POST['saisisseur']);
        $dateexportation=htmlspecialchars( $_POST['dateexportation']);
        $transporteur=htmlspecialchars( $_POST['transporteur']);
        $commentaire=htmlspecialchars($_POST['commentaire']);

        if(!empty($_POST['saisisseur'])){
        
            $sql = "UPDATE `exportation` SET `dateexportation` = '$dateexportation', `transporteur` = '$transporteur', `commentaire` = '$commentaire', `saisisseur` = '$saisisseur' WHERE `idexportation` = ?;";
            //$result = $db->query($sql);
            $sth = $db->prepare($sql);    
            $sth->execute(array($idexportation));


            for ($i = 0; $i < count($_POST['epaisseur']); $i++){
                if(!empty($_POST['epaisseur'][$i])){
                    //var_dump($_POST);
                    $epaisseur=htmlspecialchars( $_POST['epaisseur'][$i]);
                    //$largeur=htmlspecialchars($_POST['largeur']);
                    $etatbobine=htmlspecialchars($_POST['etatbobine'][$i]);
                    //$user=htmlspecialchars($_POST['user'][$i]);
                    $poidsdeclare=htmlspecialchars($_POST['poidsdeclare'][$i]);
                    $poidspese=htmlspecialchars($_POST['poidspese'][$i]);
                    $pointdepart=htmlspecialchars($_POST['pointdepart'][$i]);
                    $pointarrive=htmlspecialchars($_POST['pointarrive'][$i]);
                    $nbbobine=htmlspecialchars($_POST['nbbobine'][$i]);

                    //** On vérifie si le nombre est exact
                        $idepais = 0;
                        /*
                            1 -> Metal1
                            3 -> Niambour
                            4 -> Cranteuse
                            5 -> Tréfilage
                            6 -> Metal Mbao
                        */

                        //Rechercher le nombre de piéces sur le lieu de depart
                            $sqlEpaisseur = "SELECT * FROM `matiere` where `lieutransfert`='$pointdepart' and `epaisseur`='$epaisseur' and `nbbobineactuel` != 0 and `nbbobineactuel`>=$nbbobine LIMIT 1;";
                            // On prépare la requête
                            $queryEpaisseur = $db->prepare($sqlEpaisseur);

                            // On exécute
                            $queryEpaisseur->execute();

                            // On récupère le nombre d'articles
                            $resultEpaisseur = $queryEpaisseur->fetch();

                            if($resultEpaisseur){
                                $Iddepart = $resultEpaisseur['idmatiere'];
                            }
                        //Fin Rechercher le nombre de piéces 

                        if($resultEpaisseur){
                            // Enlever le nombre de bobine dans le lieu de depart
                                $req ="UPDATE matiere SET `nbbobineactuel` = `nbbobineactuel` - ? where `idmatiere`='$resultEpaisseur[idmatiere]';";
                                $reqtitre = $db->prepare($req);
                                $reqtitre->execute(array($nbbobine));
                            // Fin enlever le nombre de bobine dans le lieu de depart
                            
                            // Récuperer le dernier id de la matiére
                                $sqlEpaisseur = "SELECT MAX( idmatiere )  AS idMax FROM `matiere`";
                                // On prépare la requête
                                $queryEpaisseur = $db->prepare($sqlEpaisseur);
                                $queryEpaisseur->execute();
                                $resultEpaisseur = $queryEpaisseur->fetch();
                                $idMax = (int) $resultEpaisseur['idMax'];
                            // Fin

                            //Depart epaisseur
                                $sqlEpaisseur = "SELECT `$epaisseur` AS epaisseurVeriDepart FROM `epaisseur` where `lieu`='$pointdepart';";
                                // On prépare la requête
                                $queryEpaisseur = $db->prepare($sqlEpaisseur);

                                // On exécute
                                $queryEpaisseur->execute();

                                // On récupère le nombre d'articles
                                $resultEpaisseur = $queryEpaisseur->fetch();

                                $nbEpaisseurDepart = (int) $resultEpaisseur['epaisseurVeriDepart'];
                            //Fin Depart epaisseur

                            //if(($nbEpaisseurDepart < $nbbobine) || ($nbbobine == 0)){
                            //    $valideTransfert="erreurEpaisseur";
                                /*echo"<script language=\"javascript\">";
                                    echo"alert('bonjour')";
                                    echo"return false";
                                echo"</script>";*/
                            //}else{
                                //if(($pointdepart == "Cranteuse vers Metal1")){ // Vérifie le type de transfert
                                    //Debut inserer le nombre de bobine par epaisseur
                                $req ="UPDATE epaisseur SET `$epaisseur` = `$epaisseur` - ? where `lieu`='$pointdepart';";
                                //$db->query($req); 
                                $reqtitre = $db->prepare($req);
                                $reqtitre->execute(array($nbbobine));

                                $insertUser=$db->prepare("INSERT INTO `exportationdetails` (`idexportationdetail`, `poidspese`, `dateajout`, `user`, `nbbobine`, `actif`, `pointdepart`, `idexportation`, `epaisseur`, `etatbobine`, `poidsdeclare`, `commentaire`, `pointarrive`,`idmatieredepart`,`idmatierearrive`)
                                VALUES (NULL, ?, current_timestamp(), NULL, ?, '1', ?, ?, ?, ?, ?, ?, ?, ?, ?);");
                                $insertUser->execute(array($poidspese,$nbbobine,$pointdepart,$idexportation,$epaisseur,$etatbobine,$poidsdeclare,$commentaire,$pointarrive,$Iddepart,$idMax));
                            //}
                        }else{
                            $ProblemeNbBobineDepart="erreurProblemeNbDepart";
                        }

                        //** Fin verification


                    /*if($nbbobine == 0){
                        $valideTransfert="erreurEpaisseur";
                    }else{
                        $insertUser=$db->prepare("INSERT INTO `transfertdetails` (`idtransfertdetail`, `poidspese`, `dateajout`, `user`, `nbbobine`, `actif`, `pointdepart`, `idtransfert`, `epaisseur`, `etatbobine`, `poidsdeclare`, `commentaire`, `pointarrive`)
                        VALUES (NULL, ?, current_timestamp(), NULL, ?, '1', ?, ?, ?, ?, ?, ?, ?);");
                        $insertUser->execute(array($poidspese,$nbbobine,$pointdepart,$idtransfert,$epaisseur,$etatbobine,$poidsdeclare,$commentaire,$pointarrive));

                        //Inserer dans la reception pour que pont bascule puisse modifier
                        $insertUser=$db->prepare("INSERT INTO `matiere` (`idmatiere`, `epaisseur`, `poidsdeclare`, `dateajout`, `nbbobine`, `idreception`) 
                        VALUES (NULL, ?, ?, current_timestamp(), ?, ?);");
                        $insertUser->execute(array($epaisseur,$poidsdeclare,$nbbobine,$idreception));
                    }*/

                    // Pour éliminer les post une fois ajouté

                }else{
                    //$valideTransfert="erreurInsertion";
                }
            }

            // Ajouter le nombre d'epaisseur 
                /*if(isset($_GET['idreception'])){
                    $id = $_GET['idreception'];
                    
                    //** Debut select des receptions
                        $sql = "SELECT * FROM `matiere` where `actif`=1 and idreception=$id;";
            
                        // On prépare la requête
                        $query = $db->prepare($sql);
            
                        // On exécute
                        $query->execute();
            
                        // On récupère les valeurs dans un tableau associatif
                        $Reception = $query->fetchAll();
                    //** Fin select des receptions
            
                        
                    foreach ($Reception as $key => $value) {
                        $epaisseur = $value['epaisseur'];
                        $nbbobine = $value['nbbobine'];
                        $lieutransfert = $value['lieutransfert'];
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
                    }
            
                    header('location: reception.php');
                    exit;
                }
            // Fin ajouter le nombre d'epaisseur */

            if($ProblemeNbBobineDepart != "erreurProblemeNbDepart"){
                header("location: detailExportation.php?idexportation=$idexportation");
                exit;
            }
        }else{
            //$valideTransfert="erreurInsertion";
        }

    }

    // Pour insertion details reception
        /*if(isset($_POST['CreerReception'])){
            if(!empty($_POST['referenceusine']) && !empty($_POST['epaisseur'])  && !empty($_POST['lieutransfert'])  && !empty($_POST['poidsdeclare'])){
                $referenceusine=htmlspecialchars($_POST['referenceusine']);
                $epaisseur=htmlspecialchars($_POST['epaisseur']);
                //$largeur=htmlspecialchars($_POST['largeur']);
                $lieutransfert=htmlspecialchars($_POST['lieutransfert']);
                $commentaire=htmlspecialchars($_POST['commentaire']);
                $user=htmlspecialchars($_POST['user']);
                $poidsdeclare=htmlspecialchars($_POST['poidsdeclare']);
                //$poidspese=htmlspecialchars($_POST['poidspese']);
                //$idLot=htmlspecialchars($_POST['idLot']);
                $idbobine=htmlspecialchars($_POST['idbobine']);
                //$idreception=htmlspecialchars($_POST['idreception']);
                $nbbobine=htmlspecialchars($_POST['nbbobine']);

                if($nbbobine == 0){
                    $valideTransfert="erreurEpaisseur";
                }else{
                    $insertUser=$db->prepare("INSERT INTO `matiere` (`idmatiere`, `referenceusine`, `epaisseur`, `largeur`, `poidsdeclare`, `laminage`, `poidspese`, `produitfini`,
                    `travaille`, `idlot`, `annee`, `numprod`, `dateajout`, `idbobine`,`user`,`nbbobine`,`commentaire`,`lieutransfert`, `idreception`) VALUES (NULL, ?, ?, '', ?, '', '', '', '', '', '', '', current_timestamp(), '1',?,?,?,?,?);");
                $insertUser->execute(array($referenceusine,$epaisseur,$poidsdeclare,$user,$nbbobine,$commentaire,$lieutransfert,$idreception));

                header("location: detailsReception.php?idreception=$idreception");
                exit;
                }
                
            }else{
                $valideTransfert="erreurInsertion";
            }
        }*/
    //Fin insertion details reception

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <link rel="shortcut icon" href="../../image/iconOnglet.png" />
    <title>METAL AFRIQUE</title>

    <!-- Custom fonts for this template -->
    <link href="../../indexPage/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Sweet Alert -->
    <link href="../../libs/sweetalert2/sweetalert2.min.css" rel="stylesheet" type="text/css"/>
    <script src="../../libs/sweetalert2/sweetalert2.min.js"></script>
    <script src="../../libs/sweetalert2/jquery-1.12.4.js"></script>

    <!-- Custom styles for this template -->
    <link href="../../indexPage/css/sb-admin-2.min.css" rel="stylesheet">

    <!-- Custom styles for this page -->
    <link href="../../indexPage/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">

    <script>
        $(document).ready(() => { 
            // Removing Row on click to Remove button
            $('#dynamicadd').on('click', '.removeConsommations', function () {
                //console.log("TestRemove");
                $(this).parent('td.text-center').parent('tr.rowClass').remove(); 
            });

            // Removing Row on click to Remove button
            $('#dynamicadd').on('click', '.removeErreurs', function () {
                //console.log("TestRemove");
                $(this).parent('td.text-center').parent('tr.rowClass').remove(); 
            });
        })
    </script>

    <script type="text/javascript"> 
        $(document).ready(function(){  
            var i = 1; 
            $('#addErreurs').click(function(){           
            //alert('ok');           
            i++;           
            $('#dynamicaddErreurs').append(`
            <tr id="row'+i+'" class="rowClass">
                <td style="background-color:#CFFEDA ;">
                    <div class="col-md-10">
                        <div class="mb-1 text-start">
                            <input class="form-control" id="validationDefault04" type="time" name="nbbobine[]" required>
                        </div>
                    </div>
                </td>
                <td style="background-color:#CFFEDA ;">
                    <div class="col-md-10">
                        <div class="mb-1 text-start">
                            <input class="form-control designa" type="time" name="poidspese[]" value="">
                        </div>
                    </div>
                </td>
                <td style="background-color:#CFFEDA ;">
                    <div class="col-md-10">
                        <div class="mb-1 text-start">
                            <textarea class="form-control" id="exampleFormControlTextarea1" rows="2"></textarea>
                        </div>
                    </div>
                </td>
                <td style="background-color:#CFFEDA ;" class="text-center"> 
                    <button class="btn btn-danger remove"
                        type="button">Enlever
                    </button> 
                </td>
            </tr>`
            );});

            $('#addConsommations').click(function(){           
            //alert('ok');           
            i++;           
            $('#dynamicaddConsommations').append(`
            <tr id="row'+i+'" class="rowClass">
                <td style="background-color:#CFFEDA ;">
                    <div class="col-md-10">
                        <div class="mb-1 text-start">
                            <input class="form-control designa" type="number" step="0.01" name="poidspese[]" id="example" value="">
                        </div>
                    </div>
                </td>
                <td style="background-color:#CFFEDA ;">
                    <div class="col-md-10">
                        <div class="mb-1 text-start">
                            <input class="form-control designa" type="number" step="0.01" name="poidspese[]" id="example" value="">
                        </div>
                    </div>
                </td>
                <td style="background-color:#CFFEDA ;">
                    <div class="col-md-10">
                        <div class="mb-1 text-start">
                            <input class="form-control designa" type="number" step="0.01" name="poidspese[]" id="example" value="">
                        </div>
                    </div>
                </td>
                <td style="background-color:#CFFEDA ;">
                    <div class="col-md-10">
                        <div class="mb-1 text-start">
                            <input class="form-control designa" type="number" step="0.01" name="poidspese[]" id="example" value="">
                        </div>
                    </div>
                </td>
                <td style="background-color:#CFFEDA ;" class="text-center"> 
                    <button class="btn btn-danger remove"
                        type="button">Enlever
                    </button> 
                </td>
            </tr>`
            );});
            $(document).on('click','.remove_row',function(){ 
            var row_id = $(this).attr("id");          
            $('#row'+row_id+'').remove();});});      
    </script> 

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">


        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>



                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid" style="margin-bottom: 200px;">
                    <!-- Content Row -->
                    <div class="row">
                        <!-- Modale pour ajouter reception -->
                        <div class="col-lg-12 ">
                            <div class="modal-dialog-centered">
                                <div class="modal-content col-lg-12">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="myExtraLargeModalLabel">Ajouter une fiche de production cranteuse</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="#" method="POST" enctype="multipart/form-data" class="row g-3">
                                            <div class="row">
                                                <?php if($valideExportation != "erreurEpaisseur" && $ProblemeNbBobineDepart != "erreurProblemeNbDepart"){ // Lorsqu'il y a pas de erreur ?> 
                                                    <div class="col-md-2 mr-2 mt-3 mb-5">
                                                        <div class="mb-1 text-start">
                                                            <label class="form-label fw-bold" for="nom">Nom complet du saisisseur</label>
                                                            <input class="form-control" id="validationDefault01" type="text" name="saisisseur" value="" placeholder="Mettez le nom complet du saisisseur" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2 ml-5 mt-3">
                                                        <div class="mb-1 text-start">
                                                            <label class="form-label fw-bold" for="nom">Transporteur</label>
                                                            <input class="form-control" id="validationDefault02" type="text" name="transporteur" value="" placeholder="Mettez le nom complet du transporteur" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2 mt-3 ml-5">
                                                        <div class="mb-1 text-start">
                                                            <label class="form-label fw-bold" for="nom">Date d'exportation</label>
                                                            <input class="form-control" id="validationDefault03" type="date" name="dateexportation" value="" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="mb-5 float-right mr-5">
                                                            <label class="form-label fw-bold" for="commentaire" >Observations (fin) </label>
                                                            <textarea class="form-control" name="commentaire" rows="4" cols="100"  placeholder="Commentaire en quelques mots ( pas obligatoire... )"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6 ">
                                                        <h5 class="modal-title mb-3 text-center font-weight-bold text-primary" id="myExtraLargeModalLabel">Gestion des erreurs</h5>
                                                        <table class="table table-bordered" id="" width="100%" cellspacing="0">
                                                            <thead>
                                                                <tr>       
                                                                    <th>Début arrets</th>
                                                                    <th>Fin arrets</th>
                                                                    <th>Raisons</th>
                                                                    <th>Supprimer ligne</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="dynamicaddErreurs">
                                                                <?php
                                                                    //$i=0;
                                                                    //for ($i = 0; $i <= $NombreLigne; $i++){
                                                                        //$i++;
                                                                        //if($article['status'] == 'termine'){
                                                                ?>
                                                                    <tr class="rowClass">
                                                                        <td style="background-color:#CFFEDA ;">
                                                                            <div class="col-md-10">
                                                                                <div class="mb-1 text-start">
                                                                                    <input class="form-control" id="validationDefault04" type="time" name="nbbobine[]" required>
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td style="background-color:#CFFEDA ;">
                                                                            <div class="col-md-10">
                                                                                <div class="mb-1 text-start">
                                                                                    <input class="form-control designa" type="time" name="poidspese[]" value="">
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td style="background-color:#CFFEDA ;">
                                                                            <div class="col-md-10">
                                                                                <div class="mb-1 text-start">
                                                                                    <textarea class="form-control" id="exampleFormControlTextarea1" rows="2"></textarea>
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td style="background-color:#CFFEDA ;" class="text-center"> 
                                                                            <button class="btn btn-danger removeErreurs"
                                                                                type="button">Enlever
                                                                            </button> 
                                                                        </td>
                                                                    </tr>
                                                                <?php
                                                                // }
                                                                ?>
                                                            </tbody>
                                                        </table>

                                                        <div class="col-md-4  d-flex gap-2">
                                                            <div class="mb-5 text-start d-flex gap-2 pt-4">
                                                                <input class="btn btn-success  w-lg bouton mr-3" name="ChangerNombreLigne" id="addErreurs" type="button" value="Ajouter une ligne">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6 ">
                                                        <h5 class="modal-title mb-3 text-center font-weight-bold text-primary" id="myExtraLargeModalLabel">Consommations</h5>
                                                        <table class="table table-bordered" id="" width="100%" cellspacing="0">
                                                            <thead>
                                                                <tr>       
                                                                    <th>Diametre</th>
                                                                    <th>N° fin</th>
                                                                    <th>Poids</th>
                                                                    <th>Déchets</th>
                                                                    <th>Supprimer ligne</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="dynamicaddConsommations">
                                                                <?php
                                                                    //$i=0;
                                                                    //for ($i = 0; $i <= $NombreLigne; $i++){
                                                                        //$i++;
                                                                        //if($article['status'] == 'termine'){
                                                                ?>
                                                                    <tr class="rowClass">
                                                                        <td style="background-color:#CFFEDA ;">
                                                                            <div class="col-md-10">
                                                                                <div class="mb-1 text-start">
                                                                                    <input class="form-control designa" type="number" step="0.01" name="poidspese[]" id="example" value="">
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td style="background-color:#CFFEDA ;">
                                                                            <div class="col-md-10">
                                                                                <div class="mb-1 text-start">
                                                                                    <input class="form-control designa" type="number" step="0.01" name="poidspese[]" id="example" value="">
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td style="background-color:#CFFEDA ;">
                                                                            <div class="col-md-10">
                                                                                <div class="mb-1 text-start">
                                                                                    <input class="form-control designa" type="number" step="0.01" name="poidspese[]" id="example" value="">
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td style="background-color:#CFFEDA ;">
                                                                            <div class="col-md-10">
                                                                                <div class="mb-1 text-start">
                                                                                    <input class="form-control designa" type="number" step="0.01" name="poidspese[]" id="example" value="">
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td style="background-color:#CFFEDA ;" class="text-center"> 
                                                                            <button class="btn btn-danger removeConsommations"
                                                                                type="button">Enlever
                                                                            </button> 
                                                                        </td>
                                                                    </tr>
                                                                <?php
                                                                // }
                                                                ?>
                                                            </tbody>
                                                        </table>

                                                        <div class="col-md-4  d-flex gap-2">
                                                            <div class="mb-5 text-start d-flex gap-2 pt-4">
                                                                <input class="btn btn-success  w-lg bouton mr-3" name="ChangerNombreLigne" id="addConsommations" type="button" value="Ajouter une ligne">
                                                            </div>
                                                        </div>
                                                    </div>

                                                <?php }else{  // Lorsqu'il y a une erreur ?>
                                                    <div class="col-md-2 mr-2 mt-3 mb-5">
                                                        <div class="mb-1 text-start">
                                                            <label class="form-label fw-bold" for="nom">Nom complet du saisisseur</label>
                                                            <input class="form-control" id="validationDefault01" type="text" name="saisisseur" value="" placeholder="Mettez le nom complet du saisisseur" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2 ml-5 mt-3">
                                                        <div class="mb-1 text-start">
                                                            <label class="form-label fw-bold" for="nom">Transporteur</label>
                                                            <input class="form-control" id="validationDefault02" type="text" name="transporteur" value="" placeholder="Mettez le nom complet du transporteur" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2 mt-3 ml-5">
                                                        <div class="mb-1 text-start">
                                                            <label class="form-label fw-bold" for="nom">Date d'exportation</label>
                                                            <input class="form-control" id="validationDefault03" type="date" name="dateexportation" value="" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6 ">
                                                        <h5 class="modal-title mb-3 text-center font-weight-bold text-primary" id="myExtraLargeModalLabel">Gestion des erreurs</h5>
                                                        <table class="table table-bordered" id="" width="100%" cellspacing="0">
                                                            <thead>
                                                                <tr>       
                                                                    <th>Début arrets</th>
                                                                    <th>Fin arrets</th>
                                                                    <th>Raisons</th>
                                                                    <th>Supprimer ligne</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="dynamicaddErreurs">
                                                                <?php
                                                                    //$i=0;
                                                                    //for ($i = 0; $i <= $NombreLigne; $i++){
                                                                        //$i++;
                                                                        //if($article['status'] == 'termine'){
                                                                ?>
                                                                    <tr class="rowClass">
                                                                        <td style="background-color:#CFFEDA ;">
                                                                            <div class="col-md-10">
                                                                                <div class="mb-1 text-start">
                                                                                    <input class="form-control" id="validationDefault04" type="time" name="nbbobine[]" required>
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td style="background-color:#CFFEDA ;">
                                                                            <div class="col-md-10">
                                                                                <div class="mb-1 text-start">
                                                                                    <input class="form-control designa" type="time" name="poidspese[]" value="">
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td style="background-color:#CFFEDA ;">
                                                                            <div class="col-md-10">
                                                                                <div class="mb-1 text-start">
                                                                                    <textarea class="form-control" id="exampleFormControlTextarea1" rows="2"></textarea>
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td style="background-color:#CFFEDA ;" class="text-center"> 
                                                                            <button class="btn btn-danger removeErreurs"
                                                                                type="button">Enlever
                                                                            </button> 
                                                                        </td>
                                                                    </tr>
                                                                <?php
                                                                // }
                                                                ?>
                                                            </tbody>
                                                        </table>

                                                        <div class="col-md-4  d-flex gap-2">
                                                            <div class="mb-5 text-start d-flex gap-2 pt-4">
                                                                <input class="btn btn-success  w-lg bouton mr-3" name="ChangerNombreLigne" id="addErreurs" type="button" value="Ajouter une ligne">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6 ">
                                                        <h5 class="modal-title mb-3 text-center font-weight-bold text-primary" id="myExtraLargeModalLabel">Consommations</h5>
                                                        <table class="table table-bordered" id="" width="100%" cellspacing="0">
                                                            <thead>
                                                                <tr>       
                                                                    <th>Diametre</th>
                                                                    <th>N° fin</th>
                                                                    <th>Poids</th>
                                                                    <th>Déchets</th>
                                                                    <th>Supprimer ligne</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="dynamicaddConsommations">
                                                                <?php
                                                                    //$i=0;
                                                                    //for ($i = 0; $i <= $NombreLigne; $i++){
                                                                        //$i++;
                                                                        //if($article['status'] == 'termine'){
                                                                ?>
                                                                    <tr class="rowClass">
                                                                        <td style="background-color:#CFFEDA ;">
                                                                            <div class="col-md-10">
                                                                                <div class="mb-1 text-start">
                                                                                    <input class="form-control designa" type="number" step="0.01" name="poidspese[]" id="example" value="">
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td style="background-color:#CFFEDA ;">
                                                                            <div class="col-md-10">
                                                                                <div class="mb-1 text-start">
                                                                                    <input class="form-control designa" type="number" step="0.01" name="poidspese[]" id="example" value="">
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td style="background-color:#CFFEDA ;">
                                                                            <div class="col-md-10">
                                                                                <div class="mb-1 text-start">
                                                                                    <input class="form-control designa" type="number" step="0.01" name="poidspese[]" id="example" value="">
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td style="background-color:#CFFEDA ;">
                                                                            <div class="col-md-10">
                                                                                <div class="mb-1 text-start">
                                                                                    <input class="form-control designa" type="number" step="0.01" name="poidspese[]" id="example" value="">
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td style="background-color:#CFFEDA ;" class="text-center"> 
                                                                            <button class="btn btn-danger removeConsommations"
                                                                                type="button">Enlever
                                                                            </button> 
                                                                        </td>
                                                                    </tr>
                                                                <?php
                                                                // }
                                                                ?>
                                                            </tbody>
                                                        </table>

                                                        <div class="col-md-4  d-flex gap-2">
                                                            <div class="mb-5 text-start d-flex gap-2 pt-4">
                                                                <input class="btn btn-success  w-lg bouton mr-3" name="ChangerNombreLigne" id="addConsommations" type="button" value="Ajouter une ligne">
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php } ?>

                                                <div class="col-md-6 invisible">
                                                    <div class="mb-1 text-start">
                                                        <label class="form-label fw-bold" for="user" ></label>
                                                        <input class="form-control " type="text" value="<?php
                                                            echo $_SESSION['nomcomplet']; 
                                                        ?>" name="user" id="example-date-input2">
                                                    </div>
                                                </div>
                                                <div class="col-md-6 invisible">
                                                    <div class="mb-1 text-start">
                                                        <input class="form-control " type="text" value="<?php
                                                            echo $_GET['idcranteuseq1']; 
                                                        ?>" name="idcranteuseq1" id="example-date-input2">
                                                    </div>
                                                </div>
                                                <div class="col-md-6 invisible">
                                                    <div class="mb-1 text-start">
                                                        <input class="form-control " type="text" value="<?php
                                                            echo $_GET['quart']; 
                                                        ?>" name="quart" id="example-date-input2">
                                                    </div>
                                                </div>
                                                <?php if($valideExportation != "erreurEpaisseur" && $ProblemeNbBobineDepart != "erreurProblemeNbDepart"){ // Lorsqu'il y a pas de erreur ?> 
                                                    <div class="col-md-8">
                                                        <div class="mb-1 text-start">
                                                            <label class="form-label fw-bold" for="commentaire" >Commentaire</label>
                                                            <textarea class="form-control" name="commentaire" rows="4" cols="50"  placeholder="Commentaire en quelques mots ( pas obligatoire... )"><?= $row['commentaire'] ?></textarea>
                                                        </div>
                                                    </div> 
                                                <?php }else{  // Lorsqu'il y a une erreur ?>
                                                    <div class="col-md-8">
                                                        <div class="mb-1 text-start">
                                                            <label class="form-label fw-bold" for="commentaire" >Observations (fin) </label>
                                                            <textarea class="form-control" name="commentaire" rows="4" cols="50"  placeholder="Commentaire en quelques mots ( pas obligatoire... )"><?= $_POST['commentaire'] ?></textarea>
                                                        </div>
                                                    </div> 
                                                <?php } ?>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12 text-end">
                                                    <div class="col-md-8 align-items-center col-md-12 text-end"> 
                                                        <?php if($valideExportation == "erreurInsertion"){ ?> 
                                                            <script>    
                                                                Swal.fire({
                                                                    text: 'Veiller remplir tous les champs svp!',
                                                                    icon: 'error',
                                                                    timer: 2500,
                                                                    showConfirmButton: false,
                                                                },
                                                                function(){ 
                                                                    location.reload();
                                                                });
                                                            </script> 
                                                        <?php } ?>
                                                        <?php if($ProblemeNbBobineDepart == "erreurProblemeNbDepart"){ ?> 
                                                            <script>    
                                                                Swal.fire({
                                                                    text: 'Veiller revoir votre stockage (nombre de bobine, epaisseur ou poids déclaré) svp!',
                                                                    icon: 'error',
                                                                    timer: 5500,
                                                                    showConfirmButton: false,
                                                                },
                                                                function(){ 
                                                                    location.reload();
                                                                });
                                                            </script> 
                                                        <?php } ?>
                                                        <?php if($valideExportation == "ValideInsertion"){?> 
                                                            <script>    
                                                                Swal.fire({
                                                                    text: 'Exportation enregistrée avec succès merci!',
                                                                    icon: 'success',
                                                                    timer: 3000,
                                                                    showConfirmButton: false,
                                                                    });
                                                            </script> 
                                                        <?php } ?>
                                                        <div class="d-flex gap-2 pt-4">                           
                                                            <a href="detailExportation.php?idexportation=<?= $_GET['idexportation'] ?>"><input class="btn btn-danger  w-lg bouton mr-3" name=""  value="Annuler"></a>
                                                            <input class="btn btn-success  w-lg bouton mr-3" name="CreerExportation" type="submit" value="ENREGISTRER">
                                                        </div>
                                                        <hr/>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>  
                                    </div>
                                </div><!-- /.modal-content -->
                            </div><!-- /.modal-dialog -->
                        </div><!-- /.modal -->
                    </div>
                </div>
                <!-- /.container-fluid -->
            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white" style="position: absolute; width: 100%; bottom: 0;">
                <div class="text-center text-dark p-3" style="background-color: rgba(0, 0, 0, 0.1);">
                    METAL AFRIQUE © <script>document.write(new Date().getFullYear())</script> Copyright:
                    <a class="text-dark" href="https://metalafrique.com//">METALAFRIQUE.COM BY @BACHIR</a>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>


    <!-- Bootstrap core JavaScript-->
    <script src="../../indexPage/vendor/jquery/jquery.min.js"></script>
    <script src="../../indexPage/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="../../indexPage/vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="../../indexPage/js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="../../indexPage/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../../indexPage/vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="../../indexPage/js/demo/datatables-demo.js"></script>

</body>

</html>