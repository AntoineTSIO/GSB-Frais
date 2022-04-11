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

        $requeteModifFraisHorsForfait = $connexion->query("UPDATE LigneFraisHorsForfait SET libelle ='".$libelle_FraisHorsForfait_invalide."', mois ='".$moisModifFraisHorsForfait."' WHERE id ='".$id_FraisHorsForfait_invalide."'");
        $requeteModifFraisHorsForfait->execute();
        $requeteModifDateModif = $connexion->query("UPDATE FicheFrais SET dateModif ='".$dateActuelle."'");
        $requeteModifDateModif->execute();



        //Invalidation d'un frais hors forfait
        $form_idVisiteurValide = $request->get('form_idVisiteurValide');
        $form_moisValide = $request->get('form_moisValide');
        $form_fraisForfaitValide = (double)$request->get('form_fraisForfaitValide');
        $form_fraisHorsForfaitValide = (double)$request->get('form_fraisHorsForfaitValide');
        $montantValide = $form_fraisForfaitValide + $form_fraisHorsForfaitValide;
        $requeteValidationFicheFrais = $connexion->query("UPDATE FicheFrais SET idEtat = 'VA' , montantValide = '".$montantValide."'WHERE idVisiteur ='".$form_idVisiteurValide."' AND mois = '".$form_moisValide."'");
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
}