<?php

namespace App\Controller;

use App\Entity;
use App\Entity\Visiteur;
use App\Form\VisiteurType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


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
        $visiteurInstance = new Visiteur();
        $formVisiteur = $this->createForm(VisiteurType::class, $visiteurInstance);
        $formVisiteur->handleRequest($request);

        if($formVisiteur->isSubmitted()&& $formVisiteur->isValid()){
            $data = $formVisiteur->getData();
            return $this->render('comptable/validerFicheFrais.html.twig',
                array('formulaire' => $formVisiteur->createView()));
        }
        return $this->render('comptable/validerFicheFrais.html.twig',
            array('formulaire' => $formVisiteur->createView()));
    }
}