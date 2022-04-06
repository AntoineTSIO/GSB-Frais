<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use PDO;
use PDOException;

class VisiteurController extends AbstractController
{
    public function index(Request $request): Response
    {

        $session = $request->getSession();

        return $this->render('visiteur/index.html.twig', [
            'controller_name' => 'VisiteurController',
            'session' => $session,
        ]);
    }

    public function consulterFicheFrais(Request $request): Response
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
        
        $session = $request->getSession();
        $idVisiteur = $session->get('idVisiteur');

        $mois = $request->request->get('mois');
        $annee = $request->request->get('annee');
        $dateFiche = $mois . $annee;

        $requete = $connexion->query('SELECT * FROM FicheFrais where mois = ' . $dateFiche );
        $FicheFrais = $requete->fetchall();

        //recuperation du libelle de la fiche frais

        $requete = $connexion->query("SELECT libelle FROM Etat where id = '" . $FicheFrais[0]['idEtat'] . "'");
        $libelle = $requete->fetchall();

        $requete = $connexion->query("SELECT idVisiteur, mois, idFraisForfait, quantite, montant FROM LigneFraisForfait as L 
                                        INNER JOIN FraisForfait as F ON L.idFraisForfait = F.id where idVisiteur = '" . $idVisiteur . "' and mois = " . $dateFiche );
        $ligneFraisForfait = $requete->fetchall();

        $requete = $connexion->query("SELECT * FROM LigneFraisHorsForfait where idVisiteur = '" . $idVisiteur . "' and mois = " . $dateFiche );
        $ligneFraisHorsForfait = $requete->fetchall();

        return $this->render('visiteur/consulterFicheFrais.html.twig', [
            'ficheFrais' => $FicheFrais,
            'libelle' => $libelle,
            'ligneFraisForfait' => $ligneFraisForfait,
            'ligneFraisHorsForfait' => $ligneFraisHorsForfait
        ]);
    }

    public function renseignerFicheFrais(Request $request): Response
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
        
        $session = $request->getSession();
        $idVisiteur = $session->get('idVisiteur');

        $TypeFrais = $request->get('TypeFrais');

        $dateActuelle = date("y-m-d");
        $moisActuelle = date("m");
        $anneeActuelle = date('Y');
        $mois = $moisActuelle . $anneeActuelle;
        if($moisActuelle != 1){
            $moisPrecedent = $moisActuelle-1 . $anneeActuelle; 
        }else{
            $moisPrecedent = 12 . $anneeActuelle-1;
        }
        if(isset($TypeFrais)){
            
            $requete = $connexion->query("SELECT * FROM FicheFrais where mois ='" . $mois . "' and idVisiteur = '" . $idVisiteur . "'");
            $ficheFraisActuelle = $requete->fetch();
        
            if(empty($ficheFraisActuelle[0])){ //si aucune valeur n'est retourné, la fiche frais doit donc être créer et clot celle du mois précedent
               $requete = $connexion->query("INSERT INTO FicheFrais(`idVisiteur`,`mois`,`dateModif`) values('" . $idVisiteur . "','" . $mois . "', '" . $dateActuelle . "')");
               $requete = $connexion->query("UPDATE FicheFrais SET idEtat = 'CL', dateModif = '" . $dateActuelle . "' where idVisiteur = '" . $idVisiteur . "' and mois = " . $moisPrecedent );
            }
            if($TypeFrais == "ETP" or $TypeFrais == "KM" or $TypeFrais == "NUI" or $TypeFrais == "REP" ){
                $quantite = $request->get("quantite");
                $connexion->query("INSERT INTO LigneFraisForfait VALUES('" . $idVisiteur . "','" . $mois . "','" . $TypeFrais . "'," . $quantite . ")");
            }elseif($TypeFrais == "AUT"){
                $libelle = $request->get("libelle");
                $date = $request->get("date");
                $montant = $request->get("montant");
                $connexion->query("INSERT INTO LigneFraisHorsForfait(`idVisiteur`,`mois`,`libelle`,`date`,`montant`) VALUES('" . $idVisiteur . "','" . $mois . "','" . $libelle . "','" . $date . "'," . $montant . ")");
            }
        }

        // debut des lignes de modification des frais 

        // modification des frais forfait

        $ligneModifETP = $request->get("ligneModifETP");
        $ligneModifKM = $request->get('ligneModifKM');
        $ligneModifNUI = $request->get('ligneModifNUI');
        $ligneModifREP = $request->get('ligneModifREP');
    
        if(isset($ligneModifETP)){
            $requeteETP = $connexion->query("UPDATE LigneFraisForfait SET quantite = " . $ligneModifETP . " WHERE idFraisForfait = 'ETP' and mois = '" . $mois . "'");
        }

        if(isset($ligneModifKM)){
            $requeteKM = $connexion->query("UPDATE LigneFraisForfait SET quantite = " . $ligneModifKM . " WHERE idFraisForfait = 'KM' and mois = '" . $mois . "'");
        }

        if(isset($ligneModifNUI)){
            $requeteNUI = $connexion->query("UPDATE LigneFraisForfait SET quantite = " . $ligneModifNUI . " WHERE idFraisForfait = 'NUI' and mois = '" . $mois . "'");
        }

        if(isset($ligneModifREP)){
            $requeteREP = $connexion->query("UPDATE LigneFraisForfait SET quantite = " . $ligneModifREP . " WHERE idFraisForfait = 'REP' and mois = '" . $mois . "'");
        }

        // modification des frais hors forfait

        $numId = $request->get("numId");
        $modifMontantHorsForfait = $request->get("ligneModifMontantHorsForfait".$numId);
        $modifDateHorsForfait = $request->get("ligneModifDateHorsForfait");
        


        if(isset($modifMontantHorsForfait)){
            $requeteHorsForfait = $connexion->query("UPDATE LigneFraisHorsForfait SET montant = " . $modifMontantHorsForfait . ", date = '" . $modifDateHorsForfait . "' WHERE id = " . $numId );
        }

        // supprimer frais hors forfait

        $supp = $request->get("supp");

        if($supp == "ok"){
            $requeteSuppHorsForfait = $connexion->query("DELETE FROM LigneFraisHorsForfait WHERE id = " . $numId );
        }

        // fin des lignes de modification des frais

        // récupération des tableau frais forfait et hors forfait

        $requete = $connexion->query("SELECT idVisiteur, mois, idFraisForfait, quantite, montant FROM LigneFraisForfait as L 
                                        INNER JOIN FraisForfait as F ON L.idFraisForfait = F.id where idVisiteur = '" . $idVisiteur . "' and mois = " . $mois );
        $ligneFraisForfait = $requete->fetchall();

        $requete = $connexion->query("SELECT * FROM LigneFraisHorsForfait where idVisiteur = '" . $idVisiteur . "' and mois = " . $mois );
        $ligneFraisHorsForfait = $requete->fetchall();
        

        return $this->render('visiteur/renseignerFicheFrais.html.twig', [
            'ligneFraisForfait' => $ligneFraisForfait,
            'ligneFraisHorsForfait' => $ligneFraisHorsForfait
        ]);
    }
}