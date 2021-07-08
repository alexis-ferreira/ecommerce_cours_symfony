<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use DateTime;
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
     * @return Response
     */
    public function home(ArticleRepository $articleRepository) // On injecte en dépendance le repository d'Article pour pouvoir hériter des méthodes présente à l'interieur.
    {
        // Le repository est obligatoirement appelé pour les requêtes de SELECT

        $articles = $articleRepository->findAll();



        return $this->render("front/home.html.twig", [

            "articles" => $articles
        ]);
    }
} // FIN DE LA CLASS
