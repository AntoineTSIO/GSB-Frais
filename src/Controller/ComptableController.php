<?php

namespace App\Controller;

use App\Entity;
use App\Entity\Visiteur;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use PDO;


class ComptableController extends AbstractController
{
    public function index(): Response
    {
        return $this->render('comptable/index.html.twig', [
            'controller_name' => 'ComptableController',
        ]);
    }

    public function validerFicheFrais(): Response
    {
        return $this->render('comptable/validerFicheFrais.html.twig', [
            'controller_name' => 'ComptableController',
        ]);
    }

    public function validerUneFiche(Request $request):Response
    {
        try{
            $dbName = 'gsbFrais';
            $host = 'localhost';
            $utilisateur = 'gsb';
            $motDePasse = 'azerty';
            $port = '3306';
            $dns = 'mysql:host='.$host.';dbname='.$dbName.';port='.$port;
            $connexion = new PDO( $dns, $utilisateur, $motDePasse);
        } catch (Exception $e) {
            echo "connection impossible : " . $e;
            die();
        }


        //Liste visiteurs
        $requeteVisiteurs = $connexion->query('SELECT * FROM Visiteur');
        $listeVisiteurs = $requeteVisiteurs -> fetchAll();



        //Liste fiche frais
        $idVisiteur = $request->get('idVisiteur');
        $requeteFicheFrais = $connexion->query("SELECT * FROM FicheFrais WHERE idVisiteur = '".$idVisiteur."'");
        $listeFicheFrais = $requeteFicheFrais -> fetchAll();



        //Liste frais forfait et frais hors forfait
        $mois = $request->get('mois');
        $requeteFraisForfait = $connexion->query("SELECT * FROM LigneFraisForfait AS LFF INNER JOIN FraisForfait AS FF ON FF.id = LFF.idFraisForfait WHERE LFF.idVisiteur ='".$idVisiteur."' AND LFF.mois ='".$mois."'");
        $requeteFraisHorsForfait = $connexion->query("SELECT * FROM LigneFraisHorsForfait WHERE idVisiteur ='".$idVisiteur."' AND mois ='".$mois."'");
        $listeFraisForfait = $requeteFraisForfait->fetchAll();
        $listeFraisHorsForfait = $requeteFraisHorsForfait->fetchAll();



        //Récupération des frais validés
        $fraisForfait = $request->get('fraisForfait');
        $fraisHorsForfait = $request->get('fraisHorsForfait');



        //Invalidation d'un frais hors forfait
        $id_FraisHorsForfait_invalide = $request->get('id_FraisHorsForfait_invalide');
        $libelle_FraisHorsForfait_invalide = $request->get('libelle_FraisHorsForfait_invalide');
        $libelle_FraisHorsForfait_invalide = "REFUSE : " . $libelle_FraisHorsForfait_invalide;
        $mois_FraisHorsForfait_invalide = $request->get('mois_FraisHorsForfait_invalide');
        $verif_FraisHorsForfait_invalide = $request->get('verif_FraisHorsForfait_invalide');
        $idVisiteur_FraisHorsForfait_invalide = $request->get('idVisiteur_FraisHorsForfait_invalide');
        $motifRefus_FraisHorsForfait_invalide = $request->get('motif_refus_FraisHorsForfait_invalide');

        $anneeActuelle = date('Y');
        $dateActuelle = date("y-m-d");
        if($mois_FraisHorsForfait_invalide == 12){
            $moisModifFraisHorsForfait = '01' . $anneeActuelle+1;
        }else if($mois_FraisHorsForfait_invalide+1 < 10){
            $moisModifFraisHorsForfait = "0".$mois_FraisHorsForfait_invalide+1 . $anneeActuelle;
        }else{
            $moisModifFraisHorsForfait = $mois_FraisHorsForfait_invalide+1 . $anneeActuelle;
        }

        if(isset($verif_FraisHorsForfait_invalide)){
            $requeteVerificationFicheFrais = $connexion->query("SELECT * FROM FicheFrais where mois='".$moisModifFraisHorsForfait."' AND idVisiteur = '".$idVisiteur_FraisHorsForfait_invalide."'");
            $verificationFicheFrais = $requeteVerificationFicheFrais->fetch();
            if(empty($verificationFicheFrais[0])){ //Si aucune valeur n'est retournée, une nouvelle fiche de frais doit être créée pour le mois suivant
                $requeteCreationFicheFrais = "INSERT INTO FicheFrais(`idVisiteur`,`mois`,`dateModif`) VALUES('" . $idVisiteur_FraisHorsForfait_invalide . "','" . $moisModifFraisHorsForfait . "', '" . $dateActuelle . "')";
                $resCreationFicheFrais = $connexion->prepare($requeteCreationFicheFrais);
                $resCreationFicheFrais->execute();
            }
        }

        $requeteModifFraisHorsForfait = $connexion->query("UPDATE LigneFraisHorsForfait SET libelle ='".$libelle_FraisHorsForfait_invalide."', mois ='".$moisModifFraisHorsForfait."', motif_refus ='".$motifRefus_FraisHorsForfait_invalide."' WHERE id ='".$id_FraisHorsForfait_invalide."'");
        $requeteModifFraisHorsForfait->execute();
        $requeteModifDateModif = $connexion->query("UPDATE FicheFrais SET dateModif ='".$dateActuelle."'where mois='".$moisModifFraisHorsForfait."' AND idVisiteur = '".$idVisiteur_FraisHorsForfait_invalide."'");
        $requeteModifDateModif->execute();


        //Report d'un frais hors forfait
        $id_FraisHorsForfait_report = $request->get('id_FraisHorsForfait_report');
        $libelle_FraisHorsForfait_report = $request->get('libelle_FraisHorsForfait_report');
        $mois_FraisHorsForfait_report = $request->get('mois_FraisHorsForfait_report');
        $verif_FraisHorsForfait_report = $request->get('verif_FraisHorsForfait_report');
        $idVisiteur_FraisHorsForfait_report = $request->get('idVisiteur_FraisHorsForfait_report');

        $anneeActuelle = date('Y');
        $dateActuelle = date("y-m-d");
        if($mois_FraisHorsForfait_report == 12){
            $moisReportFraisHorsForfait = '01' . $anneeActuelle+1;
        }else if($mois_FraisHorsForfait_report+1 < 10){
            $moisReportFraisHorsForfait = "0".$mois_FraisHorsForfait_report+1 . $anneeActuelle;
        }else{
            $moisReportFraisHorsForfait = $mois_FraisHorsForfait_report+1 . $anneeActuelle;
        }

        if(isset($verif_FraisHorsForfait_report)){
            $requeteVerificationFicheFrais = $connexion->query("SELECT * FROM FicheFrais where mois='".$moisReportFraisHorsForfait."' AND idVisiteur = '".$idVisiteur_FraisHorsForfait_report."'");
            $verificationFicheFrais = $requeteVerificationFicheFrais->fetch();
            if(empty($verificationFicheFrais[0])){ //Si aucune valeur n'est retournée, une nouvelle fiche de frais doit être créée pour le mois suivant
                $requeteCreationFicheFrais = "INSERT INTO FicheFrais(`idVisiteur`,`mois`,`dateModif`) VALUES('" . $idVisiteur_FraisHorsForfait_report . "','" . $moisReportFraisHorsForfait . "', '" . $dateActuelle . "')";
                $resCreationFicheFrais = $connexion->prepare($requeteCreationFicheFrais);
                $resCreationFicheFrais->execute();
            }
        }

        $requeteReportFraisHorsForfait = $connexion->query("UPDATE LigneFraisHorsForfait SET libelle ='".$libelle_FraisHorsForfait_report."', mois ='".$moisReportFraisHorsForfait."' WHERE id ='".$id_FraisHorsForfait_report."'");
        $requeteReportFraisHorsForfait->execute();
        $requeteReportDateModif = $connexion->query("UPDATE FicheFrais SET dateModif ='".$dateActuelle."'where mois='".$moisReportFraisHorsForfait."' AND idVisiteur = '".$idVisiteur_FraisHorsForfait_report."'");
        $requeteReportDateModif->execute();


        //Validation de la fiche de frais
        $dateActuelleValidation = date("y-m-d");
        $form_idVisiteurValide = $request->get('form_idVisiteurValide');
        $form_moisValide = $request->get('form_moisValide');
        $form_fraisForfaitValide = (double)$request->get('form_fraisForfaitValide');
        $form_fraisHorsForfaitValide = (double)$request->get('form_fraisHorsForfaitValide');
        $montantValide = $form_fraisForfaitValide + $form_fraisHorsForfaitValide;
        $requeteValidationFicheFrais = $connexion->query("UPDATE FicheFrais SET idEtat = 'VA' , montantValide = '".$montantValide."', dateModif = '".$dateActuelleValidation."'WHERE idVisiteur ='".$form_idVisiteurValide."' AND mois = '".$form_moisValide."'");
        $requeteValidationFicheFrais->execute();




        return $this->render('comptable/validerFicheFrais.html.twig', [
            'listeVisiteurs' => $listeVisiteurs,
            'idVisiteur' => $idVisiteur,
            'listeFicheFrais' => $listeFicheFrais,
            'listeFraisForfait' => $listeFraisForfait,
            'listeFraisHorsForfait' => $listeFraisHorsForfait,
            'fraisForfaitValidés' => $fraisForfait,
            'fraisHorsForfaitValidés' => $fraisHorsForfait
        ]);
    }



    public function suivreUneFiche(Request $request):Response
    {
        try{
            $dbName = 'gsbFrais';
            $host = 'localhost';
            $utilisateur = 'gsb';
            $motDePasse = 'azerty';
            $port = '3306';
            $dns = 'mysql:host='.$host.';dbname='.$dbName.';port='.$port;
            $connexion = new PDO( $dns, $utilisateur, $motDePasse);
        } catch (Exception $e) {
            echo "connection impossible : " . $e;
            die();
        }




        //Détail frais forfait et frais hors forfait
        $moisDetailFraisValides = $request->get('mois');
        $idDetailVisiteurFraisValides = $request->get('idVisiteur');
        $idEtat = $request->get('idEtat');
        $requeteFraisForfait = $connexion->query("SELECT * FROM LigneFraisForfait AS LFF INNER JOIN FraisForfait AS FF ON FF.id = LFF.idFraisForfait WHERE LFF.idVisiteur ='".$idDetailVisiteurFraisValides."' AND LFF.mois ='".$moisDetailFraisValides."'");
        $requeteFraisHorsForfait = $connexion->query("SELECT * FROM LigneFraisHorsForfait WHERE idVisiteur ='".$idDetailVisiteurFraisValides."' AND mois ='".$moisDetailFraisValides."'");
        $listeFraisForfait = $requeteFraisForfait->fetchAll();
        $listeFraisHorsForfait = $requeteFraisHorsForfait->fetchAll();


        //Mise en paiement de la fiche de frais
        $dateActuelleMP = date("y-m-d");
        $mp_idVisiteur = $request->get('mp_idVisiteur');
        $mp_mois = $request->get('mp_mois');
        $requeteMiseEnPaiementFicheFrais = $connexion->query("UPDATE FicheFrais SET idEtat = 'MP', dateModif = '".$dateActuelleMP."' WHERE idVisiteur ='".$mp_idVisiteur."' AND mois = '".$mp_mois."'");
        $requeteMiseEnPaiementFicheFrais->execute();

        //Remboursement de la fiche de frais
        $dateActuelleRB = date("y-m-d");
        $rb_idVisiteur = $request->get('rb_idVisiteur');
        $rb_mois = $request->get('rb_mois');
        $varRemboursement = $request->get('rb_varRemboursement');
        $requeteRemboursementFicheFrais = $connexion->query("UPDATE FicheFrais SET idEtat = 'RB', dateModif = '".$dateActuelleRB."' WHERE idVisiteur ='".$rb_idVisiteur."' AND mois = '".$rb_mois."'");
        $requeteRemboursementFicheFrais->execute();


        //Annuler la Mise en paiement de la fiche de frais
        $dateActuelleVA = date("y-m-d");
        $va_idVisiteur = $request->get('va_idVisiteur');
        $va_mois = $request->get('va_mois');
        $requeteAnnulerMiseEnPaiementFicheFrais = $connexion->query("UPDATE FicheFrais SET idEtat = 'VA', dateModif = '".$dateActuelleVA."' WHERE idVisiteur ='".$va_idVisiteur."' AND mois = '".$va_mois."'");
        $requeteAnnulerMiseEnPaiementFicheFrais->execute();


        //Liste fiches de frais validées
        $requeteFichesFraisValidees = $connexion->query("SELECT * FROM FicheFrais INNER JOIN Etat ON FicheFrais.idEtat = Etat.id WHERE FicheFrais.idEtat = 'VA' or FicheFrais.idEtat ='MP'");
        $listeFichesFraisValidees = $requeteFichesFraisValidees -> fetchAll();

        return $this->render('comptable/suivrePaiementFicheFrais.html.twig', [
            'listeFichesFraisValidees' => $listeFichesFraisValidees,
            'idEtat' => $idEtat,
            'listeFraisForfait' => $listeFraisForfait,
            'listeFraisHorsForfait' => $listeFraisHorsForfait,
            'varRemboursement' => $varRemboursement
        ]);
    }
}