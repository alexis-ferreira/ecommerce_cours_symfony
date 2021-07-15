<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Categorie;
use App\Repository\CategorieRepository;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);

        for ($i = 1; $i < 51; $i++):

            $article = new Article();

            $id = rand(1, 3);

            $article->setNom("Article $i");
            $article->setPhoto("https://zupimages.net/up/21/28/rn7v.jpg");
            $article->setPrix(rand(100, 1500));
            $article->setCreateAt(new DateTime());

            $manager->persist($article);

        endfor;

        $manager->flush();
    }
}
