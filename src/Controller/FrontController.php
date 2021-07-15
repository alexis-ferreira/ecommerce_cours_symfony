<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use App\Repository\CategorieRepository;
use App\Service\Panier\PanierService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontController extends AbstractController
{

    /**
     * @Route("/", name="home")
     * @param ArticleRepository $articleRepository
     * @param CategorieRepository $categorieRepository
     * @return Response
     */
    public function home(ArticleRepository $articleRepository, CategorieRepository $categorieRepository, Request $request) //on injecte en dépendance le repository d'article pour pouvoir hériter des méthodes présentes dedans
    {
        // le repository est obligatoirement appelé pour les requetes de SELECT

        $categories = $categorieRepository->findAll();

        if ($_POST):

            $cat = $request->request->get("categorie");
            $prix = $request->request->get("prixmax");

            if ($cat == "all" && $prix == 1500):

                $articles = $articleRepository->findByPrix($prix);

            elseif ($cat !== "all" && $prix == 1500):

                $articles = $articleRepository->findBy(["categorie" => $cat]);

            elseif ($cat !== "all" && $prix !== 1500):

                $articles = $articleRepository->findByPrixCategorie($prix, $cat);

            else:

                $articles = $articleRepository->findAll();

            endif;

            return $this->render("front/home.html.twig", [
                "articles" => $articles,
                "categories" => $categories
            ]);

        endif;

        $articles = $articleRepository->findAll();
        $categories = $categorieRepository->findAll();


        return $this->render('front/home.html.twig', [

            'articles' => $articles,
            'categories' => $categories
        ]);

    }

    /**
     * @Route ("/panier", name="panier")
     * @param PanierService $panierService
     * @return Response
     */
    public function panier(PanierService $panierService)
    {
        $panier = $panierService->getFullPanier();
        $total = $panierService->getTotal();

        return $this->render("front/panier.html.twig", [
            'panier' => $panier,
            'total' => $total

        ]);
    }

    /**
     * @Route("/search", name="search")
     * @param Request $request
     * @param ArticleRepository $repository
     * @param CategorieRepository $categorieRepository
     * @return Response
     */
    public function search(Request $request, ArticleRepository $repository, CategorieRepository $categorieRepository)
    {

        $search = $request->query->get("search");

        $articles = $repository->search($search);

        $categories = $categorieRepository->findAll();

        return $this->render("front/home.html.twig", [

            "articles" => $articles,
            "categories" => $categories
        ]);
    }


}
