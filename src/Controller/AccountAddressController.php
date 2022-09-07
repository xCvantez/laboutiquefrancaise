<?php

namespace App\Controller;

use App\Entity\Address;
use App\Form\AddressType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AccountAddressController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }

    #[Route('/compte/adresses', name: 'app_account_address')]
    public function index(): Response
    {
        // dd($this->getuser());
        return $this->render('account/address.html.twig');
    }

    #[Route('/compte/ajouter-une-adresse', name: 'app_address_add')]
    public function add(Request $request): Response
    {
        //$address est l'objet de L'antité Address
        //Instance de classe = Objet
        $address = new Address;
        // Je passe en paramètres à ma fonction createForm() Le type du formulaire et L'objet
        $form = $this->createForm(AddressType::class, $address);
        // Ecoute la Requête
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $address->setUser($this->getUser());
            //Fige la data
            $this->entityManager->persist($address);

            //Exécute 
            $this->entityManager->flush();

            return $this->redirectToRoute('app_account_address');
            // dd($address);
        }
        



        return $this->render('account/address_add.html.twig',[
            'form' => $form->createView()
        ]);
    }
}
