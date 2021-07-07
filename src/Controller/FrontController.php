<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontController extends AbstractController
{

    /**
     * @Route("/addArticle", name="addArticle")
     * @param Request $request
     * @param EntityManagerInterface $manager
     */
    public function addArticle(Request $request, EntityManagerInterface $manager)
    {

        $article = new Article();

        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()):


        endif;

        return $this->render('front/addArticle.html.twig', [

            "form" => $form->createView()
        ]);
    }
} // FIN DE LA CLASS
