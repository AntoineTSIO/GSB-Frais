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
        
        $mois = $request->request->get('mois');
        $annee = $request->request->get('annee');
        $dateFiche = $mois . $annee;

        $requete = $connexion->query('SELECT * FROM FicheFrais where mois = ' . $dateFiche );
        $FicheFrais = $requete->fetchall();

        return $this->render('visiteur/consulterFicheFrais.html.twig', [
            'ficheFrais' => $FicheFrais
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

        $moisActuelle = date("m");
        
        if(isset($TypeFrais)){    
            
            $requete = $connexion->query("SELECT * FROM FicheFrais where mois ='" . $moisActuelle . "'");
            $ficheFraisActuelle = $requete->fetch();
            if(empty($ficheFraisActuelle)){
                $requete = $connexion->query("INSERT INTO FicheFrais(`idVisiteur`,`mois`) values('" . $idVisiteur . "','" . $moisActuelle . "')");
            }
            if($TypeFrais == "ETP" or $TypeFrais == "KM" or $TypeFrais == "NUI" or $TypeFrais == "REP" ){
                $quantite = $request->get("quantite");
                $connexion->query("INSERT INTO LigneFraisForfait VALUES('" . $idVisiteur . "','" . $moisActuelle . "','" . $TypeFrais . "'," . $quantite . ")");
            }elseif($TypeFrais == "AUT"){
                $libelle = $request->get("libelle");
                $date = $request->get("date");
                $montant = $request->get("montant");
                $connexion->query("INSERT INTO LigneFraisHorsForfait(`idVisiteur`,`mois`,`libelle`,`date`,`montant`) VALUES('" . $idVisiteur . "','" . $moisActuelle . "','" . $libelle . "','" . $date . "'," . $montant . ")");
            }
        }

        //$ligneFixeETP = $request->get('ligneFixeETP');
        //$ligneFixeKM = $request->get('ligneFixeKM');
        //$ligneFixeNUI = $request->get('ligneFixeNUI');
        //$ligneFixeREP = $request->get('ligneFixeREP');

        //if($ligneFixeETP != null or $ligneFixeKM != null or $ligneFixeNUI != null or $ligneFixeREP != null){
        //    $requeteETP = $connexion->query("UPDATE LigneFraisForfait SET quantite = " . $ligneFixeETP . " WHERE idFraisForfait = ETP and mois = '" . $moisActuelle . "'");
        //}

        $requete = $connexion->query("SELECT idVisiteur, mois, idFraisForfait, quantite, montant FROM LigneFraisForfait as L 
                                        INNER JOIN FraisForfait as F ON L.idFraisForfait = F.id where idVisiteur = '" . $idVisiteur . "'" );
        $ligneFraisForfait = $requete->fetchall();

        $requete = $connexion->query("SELECT * FROM LigneFraisHorsForfait where idVisiteur = '" . $idVisiteur . "'" );
        $ligneFraisHorsForfait = $requete->fetchall();

        return $this->render('visiteur/renseignerFicheFrais.html.twig', [
            'ligneFraisForfait' => $ligneFraisForfait,
            'ligneFraisHorsForfait' => $ligneFraisHorsForfait,
           // 'test' => $ligneFixeETP
        ]);
    }
}