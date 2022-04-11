<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use PDO;

class RedirectionController extends AbstractController{

    public function index(): Response
    {
        return $this->render('redirection/index.html.twig', [
            'controller_name' => 'RedirectionController',
        ]);
    }

    public function erreurValidationFicheFrais():Response
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
        $requeteVisiteurs = $connexion->query('SELECT * FROM Visiteur');
        $listeVisiteurs = $requeteVisiteurs -> fetchAll();

        $numVisiteur = null;
        $listeFicheFrais = null;

        return $this->redirect('http://127.0.0.1:8000/comptable/valider', [
                'listeVisiteurs' => $listeVisiteurs,
                'idVisiteur' => $numVisiteur,
                'listeFicheFrais' => $listeFicheFrais
            ]);
    }

}
