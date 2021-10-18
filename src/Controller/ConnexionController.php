<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Entity;

class ConnexionController extends AbstractController
{
    public function index(): Response {
        return $this->render('connexion/index.html.twig', [
            'controller_name' => 'ConnexionController',
        ]);
    }

    public function connecter(Request $request): Response {
        $login = $request->request->get('login');
        $mdp = $request->request->get('mdp');
        
        $em = $this->getDoctrine()->getManager();
        $repositoryVisiteur = $em->getRepository(Entity\Visiteur::class);
        $visiteurs = $repositoryVisiteur->findAll();
        $repositoryComptable = $em->getRepository(Entity\Comptable::class);
        $comptables = $repositoryComptable->findAll();
        
        foreach($visiteurs as $visiteur){
            if($login == $visiteur->getLogin() and $mdp == $visiteur->getMdp()){
                $session = new Session();
                $session->clear();
                $session->start();
                $session->set('login', $visiteur->getLogin());
                $session->set('nom', $visiteur->getNom());
                $session->set('prenom', $visiteur->getPrenom());
                $session->set('idVisteur', $visiteur->getId());
                return $this->render('visiteur/index.html.twig');
            }
        }
        
        foreach($comptables as $comptable){
            if($login == $comptable->getLogin() and $mdp == $comptable->getMdp()){
                $session = new Session();
                $session->clear();
                $session->start();
                $session->set('login', $comptable->getLogin());
                $session->set('nom', $comptable->getNom());
                $session->set('prenom', $comptable->getPrenom());
                return $this->render('comptable/index.html.twig');
            }
        }
        return $this->render('connexion/index.html.twig');       
    }
}
?>