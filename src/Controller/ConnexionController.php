<?php

namespace App\Controller;

use App\Entity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ConnexionController extends AbstractController
{

    private $em ;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function index(): Response {
        return $this->render('connexion/index.html.twig', [
            'controller_name' => 'ConnexionController',
        ]);
    }

    public function connecter(Request $request): Response {
        $login = $request->request->get('login');
        $mdp = $request->request->get('mdp');
        
        
        $repositoryVisiteur = $this->em->getRepository(Entity\Visiteur::class);
        $visiteurs = $repositoryVisiteur->findAll();
        $repositoryComptable = $this->em->getRepository(Entity\Comptable::class);
        $comptables = $repositoryComptable->findAll();
        
        foreach($visiteurs as $visiteur){
            if($login == $visiteur->getLogin() and $mdp == $visiteur->getMdp()){
                $session = new Session();
                $session->clear();
                $session->start();
                $session->set('login', $visiteur->getLogin());
                $session->set('nom', $visiteur->getNom());
                $session->set('prenom', $visiteur->getPrenom());
                $session->set('idVisiteur', $visiteur->getId());
                return $this->redirect('/visiteur');
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
                return $this->redirect('/comptable');
            }
        }
        return $this->redirect('/accueil');    
    }

    public function deconnexion(Request $request): Response
    {
        $session = $request->getSession();
        $session -> clear();

        return $this->redirect('/accueil');
    }
}
?>