<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontController extends AbstractController
{
    /**
     * @Route("/home", name="home")
     */
    public function home()
    {

        return $this->render("front/home");
    }

    /**
     * @Route("/addArticle", name="addArticle")
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function addArticle(Request $request, EntityManagerInterface $manager)
    {

        $article = new Article(); // Ici on instancie un nouvel objet Article vide que l'on va charger avec les données du formulaire.

        $form = $this->createForm(ArticleType::class, $article); // On fait une instance d'un objet Form qui va controller automatiquement la correspondance des champs de formulaire (contenus dans articleType) avec l'entity Article (contenu dans $article).

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

                $this->addFlash("success", "L'article à bien été ajouté");
                $this->redirectToRoute('addArticle');
            endif;

        endif;

        return $this->render('front/addArticle.html.twig', [

            "form" => $form->createView()
        ]);
    }
} // FIN DE LA CLASS
