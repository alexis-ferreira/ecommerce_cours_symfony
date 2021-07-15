<?php

namespace App\Controller;

use App\Entity\Achat;
use App\Entity\Article;
use App\Entity\Categorie;
use App\Entity\Commande;
use App\Entity\Matiere;
use App\Form\ArticleType;
use App\Form\CategorieType;
use App\Form\MatiereType;
use App\Repository\ArticleRepository;
use App\Repository\CategorieRepository;
use App\Repository\CommandeRepository;
use App\Repository\MatiereRepository;
use App\Repository\UserRepository;
use App\Service\Panier\PanierService;
use Doctrine\ORM\EntityManagerInterface;
use Swift_Image;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints\Uuid;
use function Sodium\add;

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
     * @return RedirectResponse
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
     * @param Request $request
     * @return RedirectResponse
     */
    public function sendMail(Request $request)
    {

        $transporter = (new Swift_SmtpTransport("smtp.gmail.com", 465, 'ssl'))
            ->setUsername('767paris4@gmail.com')
            ->setPassword('Session767Paris4');

        $mailer = new Swift_Mailer($transporter);

        $mess = $request->request->get("message");
        $name = $request->request->get("name");
        $surname = $request->request->get("surname");
        $subject = $request->request->get("need");
        $from = $request->request->get("email");

        $message = (new Swift_Message("$subject"))
            ->setFrom($from)
            ->setTo("767paris4@gmail.com");

        $cid = $message->embed(Swift_Image::fromPath("upload/logo.png"));
        $message->setBody(
            $this->renderView("mail/mail_template.html.twig", [

                "mess" => $mess,
                "name" => $name,
                "surname" => $surname,
                "subject" => $subject,
                "from" => $from,
                "logo" => $cid,
                "objectif" => "Accéder au site",
                "lien" => "http://127.0.0.1:8000/"
            ]),
            "text/html"
        );
        $mailer->send($message);

        $this->addFlash('success', "Votre email a bien été envoyé");

        return $this->redirectToRoute("home");
    }

    /**
     * @Route("/mailForm", name="mailForm")
     */
    public function mailForm()
    {
        return $this->render("mail/mail_form.html.twig");
    }

    /**
     * @Route("/mailTemplate", name="mailTemplate")
     */
    public function mailTemplate()
    {
        return $this->render("mail/mail_template.html.twig");
    }

    /**
     * @Route("/forgotPassword", name="forgotPassword")
     * @param Request $request
     * @param UserRepository $repository
     * @return Response
     */
    public function forgotPassword(Request $request, UserRepository $repository, EntityManagerInterface $manager)
    {
        if ($_POST):

            $email = $request->get("email");

            $user = $repository->findOneBy(["email" => $email]);

            if ($user):
                $transporter = (new Swift_SmtpTransport("smtp.gmail.com", 465, 'ssl'))
                    ->setUsername('767paris4@gmail.com')
                    ->setPassword('Session767Paris4');

                $mailer = new Swift_Mailer($transporter);

                $mess = "Vous avez fait une demande de réinitialisation de mot de passe, veuillez cliquer sur le lien ci dessous.";
                $name = "";
                $surname = "";
                $subject = "Reinitialisation de votre mot de passe";
                $from = "767paris4@gmail.com";

                $message = (new Swift_Message("$subject"))
                    ->setFrom($from)
                    ->setTo($email);

                $mail = urlencode($email);
                $token = uniqid();

                $user->setReset($token);

                $manager->persist($user);
                $manager->flush();

                $mail = $user->getId();


                $cid = $message->embed(Swift_Image::fromPath("upload/logo.png"));
                $message->setBody(
                    $this->renderView("mail/mail_template.html.twig", [

                        "mess" => $mess,
                        "name" => $name,
                        "surname" => $surname,
                        "subject" => $subject,
                        "from" => $from,
                        "logo" => $cid,
                        "objectif" => "Réinitialiser",
                        "lien" => 'http://127.0.0.1:8000/resetToken/' . $mail . '/' . $token
                    ]),
                    "text/html"
                );
                $mailer->send($message);

                $this->addFlash("success", "Un lien de réinitialisation vous a été envoyer à votre adresse mail");
            else:

                $this->addFlash("error", "Aucun utilisateur ne correspond à cette adresse mail.");
                $this->redirectToRoute("forgotPassword");

            endif;

        endif;

        return $this->render("security/forgotPassword.html.twig");
    }

    /**
     * @Route("/resetToken/{email}/{token}", name="resetToken")
     * @param $email
     * @param $token
     * @param UserRepository $repository
     * @return Response
     */
    public function resetToken($email, $token, UserRepository $repository)
    {
        $mail = urldecode($email);

        $user = $repository->findOneBy(["id" => $mail, "reset" => $token]);

        if ($user):

            return $this->render("security/resetPassword.html.twig", [
                "id" => $user->getId()
            ]);

        else:

            $this->addFlash("error", "Une erreur s'est produite, veuillez refaire une demande de réinitialisation");
            return $this->redirectToRoute("login");
        endif;
    }

    /**
     * @Route("/resetPassword", name="resetPassword")
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param UserPasswordEncoderInterface $encoder
     * @param UserRepository $repository
     * @return Response
     */
    public function resetPassword(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder, UserRepository $repository)
    {

        if ($_POST):

            $password = $request->request->get("password");
            $confirmPassword = $request->request->get("confirm_password");
            $id = $request->request->get("id");

            if ($password == $confirmPassword):

                $user = $repository->find($id);

                $hash = $encoder->encodePassword($user, $password);

                $user->setPassword($hash);
                $user->setReset(null);

                $manager->persist($user);
                $manager->flush();

                $this->addFlash("success", "Votre mot de passe à bien été modifié");

                return $this->render("security/login.html.twig", [
                    "id" => $user->getId()
                ]);

            else:

                $this->addFlash("error", "Les mots de passe ne correspondent pas");

                return $this->render("security/forgotPassword.html.twig");

            endif;
        endif;

        return $this->render("security/resetPassword.html.twig");
    }

    /**
     * @Route("/addMatiere", name="addMatiere")
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function addMatiere(Request $request, EntityManagerInterface $manager)
    {

        $matiere = new Matiere();

        $form = $this->createForm(MatiereType::class, $matiere);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()):

            $manager->persist($matiere);
            $manager->flush();

            $this->addFlash("success", "La matière à bien été ajouté");

        else:

            $this->addFlash("error", "Un problème est survenue lors de l'ajout");
        endif;

        return $this->render("back/addMatiere.html.twig", [
            "form" => $form->createView()
        ]);
    }

    /**
     * @Route("/listeMatiere", name="listeMatiere")
     * @param MatiereRepository $matiereRepository
     * @return Response
     */
    public function listeMatiere(MatiereRepository $matiereRepository)
    {

        $matieres = $matiereRepository->findAll();

        return $this->render("back/listeMatiere.html.twig", [

            "matieres" => $matieres
        ]);
    }

    /**
     * @Route("/modifMatiere/{id}", name="modifMatiere")
     * @param Matiere $matiere
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function modifMatiere(Matiere $matiere, Request $request, EntityManagerInterface $manager)
    {

        $form = $this->createForm(MatiereType::class, $matiere);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()):

            $manager->persist($matiere);
            $manager->flush();

            $this->addFlash('success', 'La matière a bien été modifié');

            return $this->redirectToRoute("listeMatiere");
        endif;

        return $this->render('back/modifMatiere.html.twig', [

            'form' => $form->createView(),
            'matieres' => $matiere
        ]);
    }

    /**
     * @Route("/deleteMatiere/{id}", name="deleteMatiere")
     * @param EntityManagerInterface $manager
     * @param Matiere $matiere
     * @return Response
     */
    public function deleteMatiere(EntityManagerInterface $manager, Matiere $matiere)
    {

        $manager->remove($matiere);
        $manager->flush();
        $this->addFlash('success', 'La matière a bien été supprimée');

        return $this->redirectToRoute("listeMatiere");
    }

}
