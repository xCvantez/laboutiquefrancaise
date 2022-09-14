<?php

namespace App\Controller;

use DateTime;
use App\Classe\Cart;
use App\Entity\Order;
use App\Form\OrderType;
use App\Entity\OrderDetails;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/commande', name: 'app_order')]
    public function index(Cart $cart, Request $request): Response
    {

        // Si l'utilisateur n'a pas d'adresses ALORS
        if (!$this->getUser()->getAddresses()->getValues()) {
            // On le redirige vers la page d'ajout d'adresse
            return $this->redirectToRoute('app_address_add');
        }

        $form = $this->createForm(OrderType::class, null, [
            'user' => $this->getUser()
        ]);

        $form->handleRequest($request);


        return $this->render('order/index.html.twig', [
            'form' => $form->createView(),
            'cart' => $cart->getFull()
        
        ]);
    }

    #[Route('/commande/recapitulatif', name: 'order_recap', methods: "POST")]
    public function add(Cart $cart, Request $request): Response
    {

        // Si l'utilisateur n'a pas d'adresses ALORS
        if (!$this->getUser()->getAddresses()->getValues()) {
            // On le redirige vers la page d'ajout d'adresse
            return $this->redirectToRoute('app_address_add');
        }

        $form = $this->createForm(OrderType::class, null, [
            'user' => $this->getUser()
        ]);

        // Ecoute la requête
        $form->handleRequest($request);

        // SI le formulaire est soumis ET le formulaire est valide ALORS
        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrer ma commande Order()
            //Enregistrer mes produit OrderDetails()
            $order = new Order();
            //Création de l'objet DateTime
            $date = new DateTime();

            $carriers = $form->get('carriers')->getData();

            $delivery = $form->get('addresses')->getData();

            $delivery_content = $delivery->getFirstname().' '.$delivery->getLastname();
            $delivery_content .= '<br>'.$delivery->getPhone();

            if ($delivery->getCompany()) {
                $delivery_content .= '<br>'.$delivery->getCompany();
            }

            $delivery_content .= '<br>'.$delivery->getAddress();
            $delivery_content .= '<br>'.$delivery->getPostal().' '.$delivery->getCity();
            $delivery_content .= '<br>'.$delivery->getCountry();



            $order->setUser($this->getUser());
            //définit la date
            $order->setCreatedAt($date);
            //définit le transporteur
            $order->setCarrierName($carriers->getName());
            //Définir le prix de livraison
            $order->setCarrierPrice($carriers->getPrice());
            //définit le contenu de la livraison
            $order->setDelivery($delivery_content);
            //Définit si l'article est payé
            $order->setIsPaid(0);
           


            //Fige la DATA
            $this->entityManager->persist($order);

             //Pour chaque produit que j'ai dans mon panier
            foreach ($cart->getFull() as $product) {
                $orderDetails = new OrderDetails();

                $orderDetails->SetMyOrder($order);
                $orderDetails->setProduct($product['product']->getName());
                $orderDetails->setQuantity($product['quantity']);
                $orderDetails->setPrice($product['product']->getPrice());
                $orderDetails->setTotal($product['product']->getPrice() * $product['quantity']);
                $this->entityManager->persist($orderDetails);

                // dd($product);
            }
            // $this->entityManager->flush();
            return $this->render('order/add.html.twig', [
                'cart' => $cart->getFull(),
                'carrier' => $carriers,
                'delivery' => $delivery_content,
    
            ]);
         }
             return $this->redirectToRoute('R');
    }
    
}
