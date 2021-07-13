<?php

namespace App\Controller;

use App\Entity\Achat;
use App\Entity\Article;
use App\Entity\Categorie;
use App\Entity\Commande;
use App\Form\ArticleType;
use App\Form\CategorieType;
use App\Repository\ArticleRepository;
use App\Repository\CategorieRepository;
use App\Repository\CommandeRepository;
use App\Service\Panier\PanierService;
use Doctrine\ORM\EntityManagerInterface;
use Swift_Mailer;
use Swift_SmtpTransport;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class BackController extends AbstractController
{


    /**
     *
     * @Route("/addArticle", name="addArticle")
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return RedirectResponse|Response
     */
    public function addArticle(Request $request, EntityManagerInterface $manager)
    {

        $article = new Article(); // ici on instancie un nouvel objet Article vide que l'on va charger avec les données du formulaire

        $form = $this->createForm(ArticleType::class, $article, array('ajout' => true));  // on instancie un objet Form qui va va controller automatiquement la corrrespondance des champs de formulaire (contenus dans articlType) avec l'entité Article (contenu dans $article)

        $form->handleRequest($request);// la méthode handlerequest de Form nous permet de préparer la requête et remplir notre objet article instancié

        if ($form->isSubmitted() && $form->isValid()): // si le formulaire a été soumis et qu'il est valide (booléan de correspondance généré dans le createForm)
            $article->setCreateAt(new \DateTime('now'));

            $photo = $form->get('photo')->getData();   //on récupere l'input type File photo de notre formulaire grace à getData on obtient $_FILE dans son intégralité

            if ($photo):

                $nomPhoto = date('YmdHis') . uniqid() . $photo->getClientOriginalName(); //ici on modifie le nom de notre photo avec uniqid(), fonction de php générant une clé de hashage de 10 caractère aléatoires concaténé avec son nom et la date avec heure,minute et seconde pour s'assuré de l'unicité de la photo en bdd
                //et en upload

                $photo->move(
                    $this->getParameter('upload_directory'),
                    $nomPhoto
                ); // equivalent de move_uploaded_file() en symfony attendant 2 paramètres, la direction de l'upload (défini dans config/service.yaml dans les parameters et le nom du fichier à inserer)

                $article->setPhoto($nomPhoto);

                $manager->persist($article); // le manager de symfony fait le lien entre l'entité et la BDD via l'ORM (Object Relationnal MApping) Doctrine.Grace à la méthode persist() il conserve en mémoire la requete préparé.
                $manager->flush();  //ici la méthode flush execute les requête en mémoire

                $this->addFlash('success', 'L\'article a bien été ajouté');
                return $this->redirectToRoute('listeArticle');
            endif;


        endif;

        return $this->render('back/addArticle.html.twig', [
            'form' => $form->createView()

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


        return $this->render('back/listeArticle.html.twig', [
            'articles' => $articles
        ]);
    }

    /**
     *
     * @Route("/modifArticle/{id}", name="modifArticle")
     * @param Article $article
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return RedirectResponse|Response
     */
    public function modifArticle(Article $article, Request $request, EntityManagerInterface $manager)
    {
        // lorsqu'un id est transité dans l'url et qu'une entité est injectée en dépendance, symfony instancie automatique l'objet entité et le rempli avec ses données en BDD. Pas besoin d'utiliser la méthode find($id) du repository

        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()):

            $photo = $form->get('photoModif')->getData();

            if ($photo):

                $nomPhoto = date('YmdHis') . uniqid() . $photo->getClientOriginalName();

                $photo->move(
                    $this->getParameter('upload_directory'),
                    $nomPhoto
                );

                unlink($this->getParameter('upload_directory') . '/' . $article->getPhoto());

                $article->setPhoto($nomPhoto);


            endif;
            $manager->persist($article);
            $manager->flush();
            $this->addFlash('success', 'L\'article a bien été modifié');
            return $this->redirectToRoute('listeArticle');


        endif;

        return $this->render('back/modifArticle.html.twig', [

            'form' => $form->createView(),
            'article' => $article
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
        $this->addFlash('success', 'L\'article a bien été supprimé');

        return $this->redirectToRoute('listeArticle');
    }


    /**
     * @Route("/ajoutCategorie", name="ajoutCategorie")
     * @Route("/modifCategorie/{id}", name="modifCategorie")
     * @param Categorie|null $categorie
     * @param EntityManagerInterface $manager
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function categorie(Categorie $categorie = null, EntityManagerInterface $manager, Request $request)
    {

        if (!$categorie):
            $categorie = new Categorie();
        endif;


        $form = $this->createForm(CategorieType::class, $categorie);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()):

            $manager->persist($categorie);
            $manager->flush();
            $this->addFlash('success', 'La catégorie a bien été créée');

            return $this->redirectToRoute('listeCategorie');

        endif;

        return $this->render("back/categorie.html.twig", [

            'form' => $form->createView()
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
            'categories' => $categories

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
        $this->addFlash('success', 'La catégorie a bien été supprimée');
        return $this->redirectToRoute('listeCategorie');


    }


    /**
     * @Route("/addPanier/{id}", name="addPanier")
     * @param PanierService $panierService
     * @param $id
     * @return RedirectResponse
     */
    public function addPanier(PanierService $panierService, $id)
    {

        $panierService->add($id);
        $panier = $panierService->getFullPanier();
        $total = $panierService->getTotal();

        return $this->redirectToRoute('panier', [
            'panier' => $panier,
            'total' => $total

        ]);

    }


    /**
     * @Route("/removePanier/{id}", name="removePanier")
     * @param $id
     * @param PanierService $panierService
     * @return RedirectResponse
     */
    public function removePanier($id, PanierService $panierService)
    {

        $panierService->remove($id);
        $panier = $panierService->getFullPanier();
        $total = $panierService->getTotal();

        return $this->redirectToRoute('panier', [
            'panier' => $panier,
            'total' => $total

        ]);

    }

    /**
     * @Route("/deleteArticlePanier/{id}", name="deleteArticlePanier")
     * @param $id
     * @param PanierService $panierService
     * @return RedirectResponse
     */
    public function deleteArticlePanier($id, PanierService $panierService)
    {

        $panierService->delete($id);
        $panier = $panierService->getFullPanier();
        $total = $panierService->getTotal();

        return $this->redirectToRoute('panier', [
            'panier' => $panier,
            'total' => $total

        ]);

    }

    /**
     * @Route("/deletePanier", name="deletePanier")
     */
    public function deletePanier(PanierService $panierService)
    {

        $panierService->deleteAll();

        return $this->redirectToRoute("home");
    }

    /**
     * @Route("/commande", name="commande")
     * @param PanierService $panierService
     * @param SessionInterface $session
     * @param EntityManagerInterface $manager
     */
    public function commande(PanierService $panierService, SessionInterface $session, EntityManagerInterface $manager)
    {

        $panier = $panierService->getFullPanier();

        $commande = new Commande();
        $commande->setMontantTotal($panierService->getTotal());
        $commande->setUser($this->getUser());
        $commande->setStatut(0);
        $commande->setDate(new \DateTime());

        foreach ($panier as $item):

            $article = $item['article'];
            $achat = new Achat();
            $achat->setArticle($article);
            $achat->setQuantite($item['quantite']);
            $achat->setCommande($commande);

            $manager->persist($achat);
        endforeach;

        $manager->persist($commande);

        $manager->flush();

        $panierService->deleteAll();

        $this->addFlash("success", "Votre commande a été prise en compte.");

        return $this->redirectToRoute("listeCommande");

    }

    /**
     * @Route("/listeCommande", name="listeCommande")
     * @param CommandeRepository $commandeRepository
     * @return Response
     */
    public function listeCommande(CommandeRepository $commandeRepository)
    {

        $commandes = $commandeRepository->findBy(['user' => $this->getUser()]);

        return $this->render("front/listeCommande.html.twig", [

            "commandes" => $commandes
        ]);
    }

    /**
     * @Route("/gestionCommande", name="gestionCommande")
     * @param CommandeRepository $commandeRepository
     * @return Response
     */
    public function gestionCommande(CommandeRepository $commandeRepository)
    {

        $commandes = $commandeRepository->findAll([]);

        return $this->render("back/gestionCommande.html.twig", [

            "commandes" => $commandes
        ]);
    }

    /**
     * @Route("/statut/{id}/{param}", name="statut")
     * @param CommandeRepository $commandeRepository
     * @param EntityManagerInterface $manager
     * @param $id
     * @param $param
     * @return RedirectResponse
     */

    public function statut(CommandeRepository $commandeRepository, EntityManagerInterface $manager, $id, $param)
    {

        $commande = $commandeRepository->find($id);
        $commande->setStatut($param);

        $manager->persist($commande);
        $manager->flush();

        return $this->redirectToRoute("gestionCommande");
    }

    /**
     * @Route("/sendMail", name="sendMail")
     */
    public function sendMail()
    {

        $transporter = (new Swift_SmtpTransport("smtp.gmail.com", 465, 'ssl'))
            ->setUsername('767paris4@gmail.com')
            ->setPassword('Session767Paris4');

        $mailer = new Swift_Mailer($transporter);

        return $this->render("");
    }


}
