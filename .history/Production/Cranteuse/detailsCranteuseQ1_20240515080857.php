<?php   

session_start(); 

if(!$_SESSION){
    header("location: ../../404.php");
    return 0;
}

include "../../connexion/conexiondb.php";
//include "./mailTransfert.php";


//Variables
// Il faut savoir que metal3 est remplacer par niambour
$valideTransfert="";
$idmatierearrive="";
$idcranteuseq1 = $_GET['idcranteuseq1'];  // On recupére l'ID de la réception par get


//Pour send mail approuveTransfertRectifie
    if(isset($_POST['approuveTransfertRectifie'])){
        if(!empty($_POST['motifRectifier'])){
            $idtransfertDemandeRectifier=htmlspecialchars($_POST['idtransfertDemandeRectifier']);
            $idmatiereDemandeRectifier=htmlspecialchars($_POST['idmatiereDemandeRectifier']);
            $epaisseur=htmlspecialchars($_POST['epaisseur']);
            $nombrebobine=htmlspecialchars($_POST['nombrebobine']);
            $user=htmlspecialchars($_POST['user']);
            $idtransfert=htmlspecialchars($_POST['idtransfert']);
            $pointdepart=htmlspecialchars($_POST['pointdepart']);
            $poidspese=htmlspecialchars($_POST['poidspese']);
            $pointarrive=htmlspecialchars($_POST['pointarrive']);
            $motifRectifier=htmlspecialchars($_POST['motifRectifier']);
            //$idmatierearrive=htmlspecialchars($_POST['idmatierearrive']);

            //Rechercher le nombre de piéces sur le lieu d'arrive
                $sqlEpaisseur = "SELECT * FROM `matiere` where `lieutransfert`='$pointarrive' and `epaisseur`='$epaisseur' and `nbbobineactuel` != 0 and `nbbobineactuel`>=$nombrebobine LIMIT 1;";
                // On prépare la requête
                $queryEpaisseur = $db->prepare($sqlEpaisseur);

                // On exécute
                $queryEpaisseur->execute();

                // On récupère le nombre d'articles
                $resultMatiereArrive = $queryEpaisseur->fetch();
            //Fin Rechercher le nombre de piéces d'arrive

            if($resultMatiereArrive){
                $sql1 = "UPDATE `transfertdetails` set `actifapprouvtransfert`=1 where idtransfertdetail=$idtransfertDemandeRectifier";
                $db->query($sql1);
    
                //Pour enlever la couleur rouge
                $sql1 = "UPDATE `transfertdetails` set `acceptereceptionmodif`=1 where idtransfertdetail=$idtransfertDemandeRectifier";
                $db->query($sql1);

                $messageD = "
                <html>
                <head>
                <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
                    <title>Nouveau compte</title>
                </head>
                <body>
                    <div id='email-wrap' style='background: #3F5EFB; border-radius: 10px;'><br><br>
                        <p align='center' style='margin-top:20px;'>
                            <h2 align='center' style='color:white'>METAL * * * AFRIQUE</h2>
                            <p align='center' style='color:white'>$_SESSION[nomcomplet] vous demande l'autorisation de modifier ou supprimer un transfert de code de transfert : <strong>TRAN00-$idtransfert </strong></p>
                            <p align='center' style='color:white'>Avec comme motif : <strong>$motifRectifier</strong></p>
                            <p align='center'><a href='http://10.10.10.127:8082/GestionProduction' style='color:white'>Cliquez ici pour y acceder.</a></p>
                        </p>
                        <br><br>
                    </div>
                </body>
                </html>
                    ";

                // Utilisateur admin seul
                    $sql = "SELECT * FROM `utilisateur` where `actif`=1 and `niveau`='admin';";

                    // On prépare la requête
                    $query = $db->prepare($sql);

                    // On exécute
                    $query->execute();

                    // On récupère les valeurs dans un tableau associatif
                    $UserMails = $query->fetchAll();
                //** Fin select 
                foreach($UserMails as $user => $item){
                    envoie_mail("Gestion de production réctification",$item['email'],"Demande de réctification pour le transfert de code TRAN00-$idtransfert",$messageD);
                }

                
                header("location: detailTransfert.php?idtransfert=$idmatiereDemandeRectifier");
                exit;
            }else{
                $idmatierearrive="erreurIdmatierearrive";
            }

        }else{
            $valideTransfert="erreurInsertion";
        }
    }
//Fin send mail approuveTransfertRectifie



    //** Debut  Validerectification 
    $mess2="";
    if(isset($_POST['Validerectification'])){
        //print_r($_POST);
        if(!empty($_POST['motifRectifier'])){  
            $id=htmlspecialchars($_POST['idreception']);
            $user=htmlspecialchars($_POST['user']);
            //$motifRectifier=htmlspecialchars($_POST['motifRectifier']);
            $req ="UPDATE reception SET actifapprouvreception=1, `status` = 'En cours de rectification', acceptereception=1, user=? WHERE idreception=$id;"; 
            //$db->query($req); 
            $reqtitre = $db->prepare($req);
            $reqtitre->execute(array($user));


            $reqMatiere ="UPDATE matiere SET actifapprouvreception=0 WHERE idmatiere = ?;"; 
            //$db->query($req); 
            $reqtitreMatiere = $db->prepare($reqMatiere);
            $reqtitreMatiere->execute(array($id));

            //$messageD=$_SESSION['nomcomplet'].' vient de faire une livraison de piéces pour la DA00'.$_POST['idda'].' Veillez verifier svp! '.'<a href="http://localhost/GestionDemandePiece">Acceder ici.</a>';
            /*$messageD = "
            <html>
            <head>
            <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
                <title>Nouveau compte</title>
            </head>
            <body>
                <div id='email-wrap' style='background: #33ECFF;color: #FFF; border-radius: 10px;'>
                    <p align='center'>
                    <img src='https://bootstrapemail.com/img/icons/logo.png' alt='' width=72 height=72>
                
                    <h3 align='center'>METAL AFRIQUE EMAIL</h3>
                
                    <p align='center'>$_SESSION[nomcomplet] vient de rejeter la commande de piéces dans la DA00$_POST[idda] pour les motifs suivants :</p>
                    <p align='center' style='color:red'>$_POST[motifrejet]</p>
                    <p align='center'><a href='http://localhost/GestionDemandePiece'>Cliquez ici pour y acceder.</a></p>
                    </p>
                    <br>
                </div>
            </body>
            </html>
                ";
            foreach($articlMails as $article){
                if(($article['niveau'] == 'kemc') || ($article['niveau'] == 'admin')){
                    envoie_mail($article['nomcomplet'],$article['email'],'Rejeter commande',$messageD);
                }
            }*/
            
            /*if(isset($_GET['id'])){
                $id = $_GET['id'];
                header("location:acueilAdmin1.php?id=$id");
                exit;
            }*/

            //Pour éffacer les valeurs enregistrées dans le stockage
                
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
                        $req ="UPDATE epaisseur SET `$epaisseur` = `$epaisseur` - ? where `id`=1;";  //Metal 1
                        //$db->query($req); 
                        $reqtitre = $db->prepare($req);
                        $reqtitre->execute(array($nbbobine));
                        //Fin inserer le nombre de bobine par epaisseur
                    }elseif(($lieutransfert == "Niambour")){
                        //Debut inserer le nombre de bobine par epaisseur
                        $req ="UPDATE epaisseur SET `$epaisseur` = `$epaisseur` - ? where `id`=3;";  // Metal 3 dit Niambour
                        //$db->query($req); 
                        $reqtitre = $db->prepare($req);
                        $reqtitre->execute(array($nbbobine));
                        //Fin inserer le nombre de bobine par epaisseur
                    }elseif(($lieutransfert == "Metal Mbao")){
                        //Debut inserer le nombre de bobine par epaisseur
                        $req ="UPDATE epaisseur SET `$epaisseur` = `$epaisseur` - ? where `id`=3;";  // Metal 3 dit Niambour
                        //$db->query($req); 
                        $reqtitre = $db->prepare($req);
                        $reqtitre->execute(array($nbbobine));
                        //Fin inserer le nombre de bobine par epaisseur
                    }
                }
            //Fin pour éffacer les valeurs enregistrées 

            header("location: detailsReception.php?idreception=$_GET[idreception]");
            exit;
        }else{
        $mess2 = "error";
        }    
    }
//** Fin debut  Validerectification 

//** Debut select de la production (erreur)
    $sql = "SELECT * FROM `cranteuseq1arret` where `actif`=1 and `idcranteuseq1`=$idcranteuseq1 ORDER BY `idcranteuseq1arret` DESC;";

    // On prépare la requête
    $query = $db->prepare($sql);

    // On exécute
    $query->execute();

    // On récupère les valeurs dans un tableau associatif
    $productioncrantq1 = $query->fetchAll();
//** Fin select de la production 

//** Debut select de la production (consommation)
    $sql = "SELECT * FROM `cranteuseq1consommation` where `actif`=1 and `idcranteuseq1`=$idcranteuseq1 ORDER BY `idcranteuseq1consommation` DESC;";

    // On prépare la requête
    $query = $db->prepare($sql);

    // On exécute
    $query->execute();

    // On récupère les valeurs dans un tableau associatif
    $consommation = $query->fetchAll();
//** Fin select de la production 

//** Debut select de la production
    $sql = "SELECT * FROM `cranteuseq1` where `actif`=1 ORDER BY `idcranteuseq1` DESC;";

    // On prépare la requête
    $query = $db->prepare($sql);

    // On exécute
    $query->execute();

    // On récupère les valeurs dans un tableau associatif
    $Cranteuseq1 = $query->fetch();
//** Fin select de la production




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

    <link href="../../StyleLoad.css" rel="stylesheet">

    <script>
        // Pour le loading
            //const loader = document.querySelector('.loader');

            document.addEventListener('DOMContentLoaded', () => {
                //console.log(loader);
                //document.getElementById('loader').className = "fondu-out";
                document.getElementById("loader").remove();
                //loader.classList.add('fondu-out');

            })
        //Pour le loading
    </script>

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Contient la nav bar gauche -->
            <?php include "./navGaucheCranteuse.php" ?>
        <!-- End  -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                 <!-- Topbar -->
                 <nav class="navbar navbar-expand navbar-light bg-white topbar static-top shadow">
                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    <!-- Topbar Search -->
                    <form
                        class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                        <div class="input-group">
                            <input type="text" class="form-control bg-light border-0 small" placeholder="Search for..."
                                aria-label="Search" aria-describedby="basic-addon2">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button">
                                    <i class="fas fa-search fa-sm"></i>
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">

                        <!-- Nav Item - Search Dropdown (Visible Only XS) -->
                        <li class="nav-item dropdown no-arrow d-sm-none">
                            <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-search fa-fw"></i>
                            </a>
                            <!-- Dropdown - Messages -->
                            <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in"
                                aria-labelledby="searchDropdown">
                                <form class="form-inline mr-auto w-100 navbar-search">
                                    <div class="input-group">
                                        <input type="text" class="form-control bg-light border-0 small"
                                            placeholder="Search for..." aria-label="Search"
                                            aria-describedby="basic-addon2">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="button">
                                                <i class="fas fa-search fa-sm"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </li>

                        <!-- Nav Item - Alerts -->
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-bell fa-fw"></i>
                                <!-- Counter - Alerts -->
                                <span class="badge badge-danger badge-counter">3+</span>
                            </a>
                            <!-- Dropdown - Alerts -->
                            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="alertsDropdown">
                                <h6 class="dropdown-header">
                                    Alerts Center
                                </h6>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-primary">
                                            <i class="fas fa-file-alt text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500">December 12, 2019</div>
                                        <span class="font-weight-bold">A new monthly report is ready to download!</span>
                                    </div>
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-success">
                                            <i class="fas fa-donate text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500">December 7, 2019</div>
                                        $290.29 has been deposited into your account!
                                    </div>
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-warning">
                                            <i class="fas fa-exclamation-triangle text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500">December 2, 2019</div>
                                        Spending Alert: We've noticed unusually high spending for your account.
                                    </div>
                                </a>
                                <a class="dropdown-item text-center small text-gray-500" href="#">Show All Alerts</a>
                            </div>
                        </li>

                        <!-- Nav Item - Messages -->
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-envelope fa-fw"></i>
                                <!-- Counter - Messages -->
                                <span class="badge badge-danger badge-counter">7</span>
                            </a>
                            <!-- Dropdown - Messages -->
                            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="messagesDropdown">
                                <h6 class="dropdown-header">
                                    Message Center
                                </h6>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="dropdown-list-image mr-3">
                                        <img class="rounded-circle" src="../../indexPage/img/undraw_profile_1.svg"
                                            alt="...">
                                        <div class="status-indicator bg-success"></div>
                                    </div>
                                    <div class="font-weight-bold">
                                        <div class="text-truncate">Hi there! I am wondering if you can help me with a
                                            problem I've been having.</div>
                                        <div class="small text-gray-500">Emily Fowler · 58m</div>
                                    </div>
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="dropdown-list-image mr-3">
                                        <img class="rounded-circle" src="../../indexPage/img/undraw_profile_2.svg"
                                            alt="...">
                                        <div class="status-indicator"></div>
                                    </div>
                                    <div>
                                        <div class="text-truncate">I have the photos that you ordered last month, how
                                            would you like them sent to you?</div>
                                        <div class="small text-gray-500">Jae Chun · 1d</div>
                                    </div>
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="dropdown-list-image mr-3">
                                        <img class="rounded-circle" src="../../indexPage/img/undraw_profile_3.svg"
                                            alt="...">
                                        <div class="status-indicator bg-warning"></div>
                                    </div>
                                    <div>
                                        <div class="text-truncate">Last month's report looks great, I am very happy with
                                            the progress so far, keep up the good work!</div>
                                        <div class="small text-gray-500">Morgan Alvarez · 2d</div>
                                    </div>
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="dropdown-list-image mr-3">
                                        <img class="rounded-circle" src="https://source.unsplash.com/Mv9hjnEUHR4/60x60"
                                            alt="...">
                                        <div class="status-indicator bg-success"></div>
                                    </div>
                                    <div>
                                        <div class="text-truncate">Am I a good boy? The reason I ask is because someone
                                            told me that people say this to all dogs, even if they aren't good...</div>
                                        <div class="small text-gray-500">Chicken the Dog · 2w</div>
                                    </div>
                                </a>
                                <a class="dropdown-item text-center small text-gray-500" href="#">Read More Messages</a>
                            </div>
                        </li>

                        <div class="topbar-divider d-none d-sm-block"></div>

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo $_SESSION['nomcomplet']; ?></span>
                                <img class="img-profile rounded-circle"
                                    src="../../indexPage/img/undraw_profile.svg">
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a href="javascript:void(0);" data-toggle="modal" data-target="#InformationProfile" title="Voir votre profile" class="dropdown-item">
                                <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Profile
                                </a>
                                <?php if($_SESSION['niveau']=='admin'){ ?>
                                    <a class="dropdown-item" href="../../indexPage/Utilisateur/ParametreUtilisateur.php?idUser=<?php echo $_SESSION['id']; ?>">
                                        <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                                        Changer paramétres
                                    </a>
                                <?php } ?>
                                <a class="dropdown-item" href="../../indexPage/Utilisateur/utilisateur.php">
                                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Gestion des utilisateurs
                                </a>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Activités
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="../../index.php">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Déconnexion
                                </a>
                            </div>
                        </li>

                    </ul>
                    <!-- Pour le status !--> 
                    <div class="modal fade " id="InformationProfile" tabindex="-1" aria-labelledby="fileModalLabel" aria-hidden="true" >
                        <div class="modal-dialog modal-xl modal-dialog-centered" style="width=750px">
                            <div class="modal-content">
                                <div class="card">
                                    <div class="card-header bg-primary">
                                    </div>
                                    <div class="card-body">
                                        <img src="../../image/avatar.jpg" class="img-fluid mx-auto d-block text-center" alt="" style="border-radius: 45%; margin-top:-3%; opacity: 0.9;" width="150">
                                        <h5 class="card-title mt-2 mb-5 text-center"><?php echo $_SESSION['nomcomplet']; ?></h5>
                                        <p class="card-text"><h6 class="d-inline mr-3">Email :</h6><?php echo $_SESSION['email']; ?></p>
                                        <p class="card-text"><h6 class="d-inline mr-3">Section :</h6> <?php echo $_SESSION['section']; ?></p>
                                        <p class="card-text"><h6 class="d-inline mr-3">Matricule :</h6> <?php echo $_SESSION['matricule']; ?></p>
                                        <p class="card-text"><h6 class="d-inline mr-3">Nom d'utilisateur :</h6> <?php echo $_SESSION['username']; ?></p>
                                        <p class="card-text"><h6 class="d-inline mr-3">Numéro téléphone :</h6> <?php echo $_SESSION['numTelephone']; ?></p>
                                        <div class="col text-center">
                                            <a href="" class="btn btn-primary text-center">Retour</a>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-primary text-muted text-center">
                                        <h5 style="color:white">METAL *** AFRIQUE</h5>
                                    </div>
                                </div>
                            </div>    
                        </div>
                    </div>

                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->

                <div class="container-fluid">
                    <!-- Page Heading -->
                    <div class="row">
                        <div class="col-xl-3 col-md-6 mb-1">
                            <img src="../../image/cranteuse.jpg" class="img-fluid" alt="" style="border-radius: 50%; margin:20px; opacity: 0.7;" width="200">
                        </div>
                    </div>
                    <!-- DataTales Example -->
                       <!-- Fade In Utility -->
                       <?php 
                            if($productioncrantq1){
                        ?>
                            <div class="col-lg-12">
                                <div class="card position-relative">
                                    <div class="card-header py-3 bg-primary">
                                        <p class="m-0 font-weight-bold text-center h2 text-uppercase mt-2 mb-5" style="color: white;">FICHE DE PRODUCTION <?php echo "Cranteuse 1" ; ?></p>
                                        <div class="mb-4">
                                            <p class="m-0 mb-3 h5 d-inline" style="color: white;  padding-right: 15%;"><span class="mr-4">Date : </span> <?php echo "24/04/2024" ; ?></p>
                                            <p class="m-0 mb-3 h5 d-inline" style="color: white; padding-right: 15%;"><span class="mr-4">Début quart : </span> <?php echo "08:00" ; ?></p>
                                            <p class="m-0 mb-3 h5 d-inline" style="color: white; padding-right: 15%;"><span class="mr-4">Fin quart : </span> <?php echo "20:00" ; ?></p>
                                            <p class="m-0 mb-3 h5 d-inline" style="color: white;"><span class="mr-4">Contrôleurs : </span> <?php echo "Modou Ndiaye ; Papi Diop" ; ?></p>
                                        </div>
                                        <p class="m-0 mb-3 h5 mt-3 ml-3 d-inline" style="color: white; padding-right: 30%;"><span class="mr-4">Compteur Horaire Début : </span> <?php echo "7859966633" ; ?></p>
                                        <p class="m-0 mb-3 h5 d-inline" style="color: white;"><span class="mr-4">Observations (Début) : </span> <?php echo "Test observations début" ; ?></p>
                                        <p class="m-0 mt-3  h5 ml-3" style="color: white;"><span class="mr-5">Compteur Horaire Fin : </span> <?php echo "  7859966777" ; ?></p>
                                    </div>
                                    <div class="row m-1 mb-3 col-lg-12">
                                        <div class="table-responsive col-lg-5">
                                            <!-- Page Loader -->
                                                <div id="loader">
                                                    <span class="lettre">M</span>
                                                    <span class="lettre">E</span>
                                                    <span class="lettre">T</span>
                                                    <span class="lettre">A</span>
                                                    <span class="lettre">L</span>
                                                    <span class="lettre">*</span>
                                                    <span class="lettre">*</span>
                                                    <span class="lettre">*</span>
                                                    <span class="lettre">A</span>
                                                    <span class="lettre">F</span>
                                                    <span class="lettre">R</span>
                                                    <span class="lettre">I</span>
                                                    <span class="lettre">Q</span>
                                                    <span class="lettre">U</span>
                                                    <span class="lettre">E</span>
                                                </div>
                                            <!-- Page Loader -->
                                            <div class="">
                                                <p class="m-0 font-weight-bold text-center h4 text-uppercase mt-5 mb-5" style="color: blue;">Géstion des erreurs</p>
                                            </div>
                                            <table class="table table-bordered m-0" width="100%" cellspacing="0">
                                                <thead>
                                                    <tr>       
                                                        <th>Début arrets</th>                                                                                
                                                        <th>Fin arrets</th>
                                                        <th>Raisons</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                        $i=0;
                                                        $TotalArretH=0;
                                                        $TotalArretM=0;
                                                        foreach($productioncrantq1 as $prodcrantq1){
                                                            
                                                            /*$heures = explode(":", $prodcrantq1['finarret']);
                                                            $heureFinHeure = $heures[0]; 
                                                            $heureFinMin = $heures[1]; 

                                                            $heures = explode(":", $prodcrantq1['debutarret']);
                                                            $heureDebutHeure = $heures[0]; 
                                                            $heureDebutMin = $heures[1]; */

                                                            //$TotalArretH +=  ($heureFinHeure - $heureDebutHeure); 
                                                            if(((strtotime($prodcrantq1['finarret']) - strtotime($prodcrantq1['debutarret'])) > 0)){
                                                                $TotalArretH +=  (strtotime($prodcrantq1['finarret']) - strtotime($prodcrantq1['debutarret']));
                                                            }else{
                                                                $TotalArretH +=  (-strtotime($prodcrantq1['debutarret']) + strtotime($prodcrantq1['finarret']));
                                                            }

                                                            $i++;
                                                            //if($article['status'] == 'termine'){
                                                    ?>
                                                        <tr>
                                                            <td style="background-color:#4e73df ; color:white;"><?= $prodcrantq1['debutarret'] ?></td>
                                                            <td style="background-color:#4e73df ; color:white;"><?= $prodcrantq1['finarret'] ?></td>
                                                            <td style="background-color:#4e73df ; color:white;"><?= $prodcrantq1['raison'] ?></td>
                                                        </tr>
                                                    <!-- Pour le sweetAlert approuveTransfert !--> 
                                                    <?php
                                                        }
                                                    ?> 
                                                </tbody>
                                            </table>
                                            <!-- Bouton et pagnination--> 
                                            <?php 
                                                //if($_SESSION['niveau']=='kemc'){
                                            ?>
                                            <?php
                                                //}
                                            ?>
                                        </div>
                                    <!-- Tableau d'en bas -->

                                        <div class="table-responsive col-lg-7">
                                            <div class="">
                                                <p class="m-0 font-weight-bold text-center h4 text-uppercase mt-5 mb-5" style="color: blue;">Consommations</p>
                                            </div>
                                            <table class="table table-bordered m-0" width="100%" cellspacing="0">
                                                <thead>
                                                    <tr>       
                                                        <th>Diamétre</th>                                                                                
                                                        <th>Numéro fin</th>
                                                        <th>poids</th>
                                                        <th>dechet</th>                                                                                
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                        $i=0;
                                                        foreach($consommation as $consom){
                                                            $i++;
                                                            //if($article['status'] == 'termine'){
                                                    ?>
                                                        <tr>
                                                            <td style="background-color:#4e73df ; color:white;"><?= $consom['diametre'] ?></td>
                                                            <td style="background-color:#4e73df ; color:white;"><?= $consom['numerofin'] ?></td>
                                                            <td style="background-color:#4e73df ; color:white;"><?= $consom['poids'] ?></td>
                                                            <td style="background-color:#4e73df ; color:white;"><?= $consom['dechet'] ?></td>
                                                        </tr>
                                                    <!-- Pour le sweetAlert approuveTransfert !--> 
                                                    <?php
                                                        }
                                                    ?> 
                                                </tbody>
                                            </table>
                                            <!-- Bouton et pagnination--> 
                                            <?php 
                                                //if($_SESSION['niveau']=='kemc'){
                                            ?>
                                            <?php
                                                //}
                                            ?>
                                        </div>
                                    <!-- Tableau d'en bas -->
                                </div>
                                    <div class="card-footer py-3 mt-5 bg-primary">
                                        <div class="mb-4 mt-3">
                                            <p class="m-0 mb-3 h5 d-inline" style="color: white;  padding-right: 40%;"><span class="mr-4"><span class="mr-4">Total arret :</span> <?php  $TotalArretHReel = explode(":",date("H:i",$TotalArretH) ); echo ($TotalArretHReel[0]-1)." H ".$TotalArretHReel[1]." min"; ?></p>
                                            <p class="m-0 mb-3 h5 d-inline" style="color: white; padding-right: 10%;" ><span class="mr-4">Vitesse : </span> <?php echo "5" ; ?></p>
                                            <p class="m-0 mb-3 h5 d-inline" style="color: white;"><span class="mr-4">Poids estimatif travaillé et non noté : </span> <?php echo "10" ; ?></p>
                                        </div>
                                        <p class="m-0 mb-3 h5 mt-3 ml-3 d-inline" style="color: white; padding-right: 52%;"></p>
                                        <p class="m-0 mb-3 h5 d-inline" style="color: white;"><span class="mr-4">Observations (Fin) : </span> <?php echo "Test observations fin" ; ?></p>
                                        <p class="m-0 mt-3  h5 ml-3 mb-3" style="color: white;"><span class="mr-5">Temps de fonction : </span> <?php echo "  7859966777" ; ?></p>
                                    </div>
                            </div>
                        <?php
                            }else{
                        ?>
                            <div class="col-lg-12 mt-5">
                                <div class="card position-relative">
                                    <div class="card-header py-3">
                                        <h3 style="color:red;" class="ml-5 font-weight-bold">Pas encore de fiche enregistrée pour ce quart !</h3>
                                    </div>
                                </div>
                            </div>  
                        <?php
                            } 
                        ?>
                        <div class="col-md-8 align-items-center mt-5">
                            <div class="d-flex gap-2 pt-4">
                                <?php
                                    if(!$productioncrantq1){
                                ?>
                                    <a href="ajoutFormulaireQuart1.php?idcranteuseq1=<?= $_GET['idcranteuseq1']?>&quart=<?= $_GET['quart']?>" class="btn btn-success w-lg bouton"><i class="fa fa-plus me-1"></i> Ajouter fiche</a>
                                <?php
                                    } 
                                ?>
                                    <a href="quart1.php" class="btn btn-danger w-lg bouton ml-3"><i class="fa fa-angle-double-left mr-2"></i>Retour</a>
                            </div>
                        </div>
                </div> 
            </div><!-- div Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="text-center text-dark p-3" style="background-color: rgba(0, 0, 0, 0.1);">
                    METAL AFRIQUE © <script>document.write(new Date().getFullYear())</script> Copyright:
                    <a class="text-dark" href="https://metalafrique.com//">METALAFRIQUE.COM BY @BACHIR</a>
                </div>
            </footer>
            <!-- End of Footer -->

        </div><!-- End of Content Wrapper -->

    </div><!-- End of Content Wrapper -->

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