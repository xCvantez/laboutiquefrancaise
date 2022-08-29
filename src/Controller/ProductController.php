<?php

namespace App\Controller;


use App\Classe\Search;
use App\Entity\Product;
use App\Form\SearchType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager) // Injection d'indépendance
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/nos-produits', name: 'products')]

    public function index(): Response {
        $products = $this->entityManager->getRepository(Product::class)->findAll(); // je récupère mes données à l'aide du repository
    // dd($products);

    $search = new Search();

    $form = $this->createForm(SearchType::class, $search);

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'form' => $form->createView()

        ]);
    }
// On passe le slug en paramètre dans l'URL
    #[Route('/produit/{slug}', name: 'product')]

    public function show($slug): Response {
        $product = $this->entityManager->getRepository(Product::class)->findOneBySlug($slug); // je récupère mes données à l'aide du repository et je recherche un produit à la place de son slug

        if(!$product){
            return $this->redirectToRoute('products');
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,

        ]);
    }

}