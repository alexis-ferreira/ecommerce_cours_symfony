<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Categorie;
use App\Form\ArticleType;
use App\Form\CategorieType;
use App\Repository\ArticleRepository;
use App\Repository\CategorieRepository;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackController extends AbstractController
{

    /**
     * @Route("/addArticle", name="addArticle")
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function addArticle(Request $request, EntityManagerInterface $manager)
    {

        $article = new Article(); // Ici on instancie un nouvel objet Article vide que l'on va charger avec les données du formulaire.

        $form = $this->createForm(ArticleType::class, $article, array('ajout' => true)); // On fait une instance d'un objet Form qui va controller automatiquement la correspondance des champs de formulaire (contenus dans articleType) avec l'entity Article (contenu dans $article).

        $form->handleRequest($request); // La méthode handlerequest de Form nous permet de préparer la requête.

        if ($form->isSubmitted() && $form->isValid()): // Si le formulaire a été soumis et qu'il est valide (boolean de correspondance généré dans le createForm).

            $article->setCreateAt(new DateTime("now"));
            $photo = $form->get("photo")->getData(); // On récupère l'input type File photo de notre formulaire. Grace a getData on obtient $_FILE dans son intégralité.

            if ($photo):

                $nomPhoto = date('YmdHis') . uniqid() . $photo->getClientOriginalName(); // Ici on modifie le nom de notre photo avec uniqid(); fonction de php générant une clé de hashage de 10 caractères  aléatoires concaténé avec son nom et la date (heure, minute, seconde), pour s'assurer que la photo soit unique en BDD.

                $photo->move(
                    $this->getParameter('upload_directory'), $nomPhoto); // Equivalent de move_uploaded_file(), attendant 2 paramètres, la direction de l'upload (Défini dans config/service.yaml) dans le parameters. Puis le nom du fichier à insérer.

                $article->setPhoto($nomPhoto);

                $manager->persist($article); // Le manager Symfony fait le lien entre l'entity et la database via l'ORM (Object Relationnal Mapping) Doctrine. Grâce à la méthode persist(), il conserve en mémoire la requête préparé.

                $manager->flush(); // La méthode flush execute les requêtes en mémoire.

                $this->addFlash("success", "L'article à bien été ajouté au catalogue");
                return $this->redirectToRoute('listeArticle');
            endif;

        endif;

        return $this->render('back/addArticle.html.twig', [

            "form" => $form->createView()
        ]);
    }

    /**
     * @Route("/listeArticle", name="listeArticle")
     * @param ArticleRepository $articleRepository
     * @return Response
     */
    public function listeArticle(ArticleRepository $articleRepository)
    {

        $articles = $articleRepository->findAll();

        return $this->render("back/listeArticle.html.twig", [

            "articles" => $articles
        ]);
    }

    /**
     * @Route("/modifArticle/{id}", name="modifArticle")
     * @param Article $article
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function modifArticle(Article $article, Request $request, EntityManagerInterface $manager)
    {
        // Lorsqu'un id est transité dans l'url et qu'une entity est injectée en dépendance, Symfony instancie automatiquement l'objet entité, et le rempli avec ses données en BDD. Pas besoin d'utiliser la méthode find($id) du repository

        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()):

            $article->setCreateAt(new DateTime("now"));
            $photo = $form->get("photoModif")->getData();

            if ($photo):

                $nomPhoto = date('YmdHis') . uniqid() . $photo->getClientOriginalName();

                $photo->move(
                    $this->getParameter('upload_directory'), $nomPhoto);

                unlink($this->getParameter('upload_directory') . "/" . $article->getPhoto());

                $article->setPhoto($nomPhoto);
            endif;

            $manager->persist($article);
            $manager->flush();

            $this->addFlash("success", "L'article à bien été modifié");
            return $this->redirectToRoute('listeArticle');

        endif;

        return $this->render("back/modifArticle.html.twig", [

            "form" => $form->createView(),
            "article" => $article
        ]);
    }

    /**
     * @Route("/deleteArticle/{id}", name="deleteArticle")
     * @param Article $article
     * @param EntityManagerInterface $manager
     * @return RedirectResponse
     */
    public function deleteArticle(Article $article, EntityManagerInterface $manager)
    {

        $manager->remove($article);
        $manager->flush();

        $this->addFlash("success", "L'article à bien été supprimé !");

        return $this->redirectToRoute('listeArticle');
    }

    /**
     * @Route("/ajoutCategorie", name="ajoutCategorie")
     * @Route("/modifCategorie/{id}", name="modifCategorie")
     * @param Categorie $categorie
     * @param EntityManagerInterface $manager
     * @param Request $request
     * @return Response
     */
    public function categorie(Categorie $categorie=null, EntityManagerInterface $manager, Request $request)
    {

        if (!$categorie):
            $categorie = new Categorie();
        endif;

        $form = $this->createForm(CategorieType::class, $categorie);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()):

            $manager->persist($categorie);
            $manager->flush();

            $this->addFlash("success", "La catégorie à bien été créer");

            return $this->redirectToRoute("listeCategorie");

        endif;

        return $this->render("back/categorie.html.twig", [

            "form" => $form->createView()
        ]);
    }

    /**
     * @Route("/listeCategorie", name="listeCategorie")
     * @param CategorieRepository $categorieRepository
     * @return Response
     */
    public function listeCategorie(CategorieRepository $categorieRepository)
    {

        $categories = $categorieRepository->findAll();

        return $this->render("back/listeCategorie.html.twig", [

            "categories" => $categories
        ]);
    }

    /**
     * @Route("/deleteCategorie/{id}", name="deleteCategorie")
     * @param EntityManagerInterface $manager
     * @param Categorie $categorie
     * @return RedirectResponse
     */
    public function deleteCategorie(EntityManagerInterface $manager, Categorie $categorie)
    {

        $manager->remove($categorie);
        $manager->flush();

        $this->addFlash("success", "La catégorie à bien été supprimé !");

        return $this->redirectToRoute('listeCategorie');
    }
} // END OF CLASS
