<?php

namespace App\Form;

use App\Entity\Visiteur;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Response;

class VisiteurType extends AbstractType
{
    private $em ;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em ;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', ChoiceType::class ,[
                'choices' => $this->selectionVisiteurs()
            ])
            ->add('valider', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Visiteur::class,
        ]);
    }

    public function selectionVisiteurs()
    {
        $repositoryVisiteur = $this->em->getRepository(Visiteur::class);
        $visiteurs = $repositoryVisiteur->findAll();
        $tableauVisiteurs = [] ;
        foreach ($visiteurs as $visiteur) {
            array_push($tableauVisiteurs , $visiteur->getNom()." ".$visiteur->getPrenom());
        }
        $tableauVisiteurs = array_flip($tableauVisiteurs);
        return $tableauVisiteurs ;
    }
}