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

        $requete = $connexion->query("SELECT * FROM LigneFraisForfait where idVisiteur = '" . $idVisiteur . "'" );
        $ligneFraisForfait = $requete->fetchall();

        $requete = $connexion->query("SELECT * FROM LigneFraisHorsForfait where idVisiteur = '" . $idVisiteur . "'" );
        $ligneFraisHorsForfait = $requete->fetchall();

        return $this->render('visiteur/renseignerFicheFrais.html.twig', [
            'ligneFraisForfait' => $ligneFraisForfait,
            'ligneFraisHorsForfait' => $ligneFraisHorsForfait
        ]);
    }
}