<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $userPasswordHasherInterface;

    public function __construct (UserPasswordHasherInterface $userPasswordHasherInterface) 
    {
        $this->userPasswordHasherInterface = $userPasswordHasherInterface;
    }
    
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $users = [];
        for ($i=0; $i < 20; $i++) { 
            $user = new User();
            $user->setUsername(!$i ? "Maximilien" : $faker->userName())
                ->setEmail(!$i ? "bey.maximilien@gmail.com" : $faker->email())
                ->setRoles([$i < 5 ? "ROLE_ADMIN" : "ROLE_USER"])
                ->setPassword($this->userPasswordHasherInterface->hashPassword(
                    $user,
                    "password"
                ));
            $manager->persist($user);
            $manager->flush();
            array_push($users, $user);
        }
        $categories = [];
        foreach (["Plat", "Apéritif", "Dessert", "Recette", "Cocktail"] as $value) {
            $category = new Category();
            $category->setLabel($value);
            $manager->persist($category);
            $manager->flush();
            array_push($categories, $category);
        }
        $images = [
            "https://recettes100faim.fr/wp-content/uploads/2022/05/recettes100faim-bruschetta-pesto-tomate-jb-cru-mozza-1920x1080.jpg",
            "https://recettes100faim.fr/wp-content/uploads/2020/04/recettes100faim-gratin-aubergine-tomate-fromage.jpg",
            "https://img3.wallspic.com/crops/6/9/9/5/6/165996/165996-crepe-tarte_a_la_fraise-acheter-palatschinke-gateau-1920x1080.jpg",
            "https://img1.wallspic.com/crops/4/1/8/5/6/165814/165814-plat-crepe-breakfast-dessert-desserts_ceto-1920x1080.jpg",
            "https://img1.wallspic.com/crops/8/3/9/3/6/163938/163938-robe-fourche-aliment-vaisselle-la_vaisselle-1920x1080.jpg",
            "https://img3.wallspic.com/crops/2/9/5/5/5/155592/155592-la_cuisine_japonaise-sushis-makizushi-restaurant-cuisine-1920x1080.jpg",
            "https://img2.wallspic.com/crops/9/4/4/5/5/155449/155449-festival_de_champignons-conifere_a_cone-champignon-petit_penny-aliment-1920x1080.jpg",
            "https://img2.wallspic.com/crops/2/6/5/5/5/155562/155562-gateau_au_chocolat-boulangerie-tiramisu-gateau-gateau_danniversaire-1920x1080.jpg",
            "https://img2.wallspic.com/crops/0/0/8/6/5/156800/156800-pizza-salami-cuisine-restaurant-aliment-1920x1080.jpg",
            "https://img2.wallspic.com/crops/2/6/8/3/6/163862/163862-joyeux_anniversaire_voeux_danniversaire-partie-anniversaire_de_mariage-gateau-cadeau-1920x1080.jpg",
            "https://img2.wallspic.com/crops/4/4/8/3/6/163844/163844-cocktail_garnir-aliment-vaisselle-liquid-fruits-1920x1080.jpg",
            "https://img3.wallspic.com/crops/6/0/6/6/5/156606/156606-plat-porridge-cuisine_vegetarienne-breakfast-bouillie_davoine-1920x1080.jpg",
            "https://img3.wallspic.com/crops/9/1/6/6/5/156619/156619-jus-smoothie-pasteque-fruits-cantaloup-1920x1080.jpg",
            "https://img3.wallspic.com/crops/8/2/4/5/5/155428/155428-les_aliments_naturels-cuisine_vegetarienne-salade_davocats-salade-fruits-1920x1080.jpg",
            "https://img3.wallspic.com/crops/8/0/9/2/6/162908/162908-aperitif-pointe-canap-dessert-brunch-1920x1080.jpg",
            "https://img1.wallspic.com/crops/2/0/6/1/6/161602/161602-al_dente-salade_cesar-legume_feuille-dejeuner-salade-1920x1080.jpg",
            "https://img3.wallspic.com/crops/7/9/5/5/5/155597/155597-gateau-tarte-cerise-gateau_danniversaire-dessert-1920x1080.jpg",
            "https://img1.wallspic.com/crops/6/1/3/8/4/148316/148316-royaume-plat-la_cuisine_indienne-plats-produit-1920x1080.jpg",
            "https://img3.wallspic.com/crops/4/6/8/2/6/162864/162864-cupcake-creme-douceur-aliment-dessert-1920x1080.jpg",
            "https://img3.wallspic.com/crops/1/7/7/8/2/128771/128771-douceur-aliment-graphisme-chocolat-confiserie-1920x1080.jpg"
        ];
        for ($i=0; $i < 20; $i++) { 
            $post = new Post();
            $post->setTitle("Post $i");
            $post->setContent($faker->realTextBetween(200, 500));
            $post->setImage($images[$i]);
            $post->setAuthor($users[$faker->numberBetween(0, 4)]);
            for ($j=0; $j < 3; $j++) { 
                $post->addCategory($categories[$faker->numberBetween(0, 4)]);
            }
            // for ($j=0; $j < 15; $j++) { 
            //     $comment = new Comment();
            //     $comment->setContent($faker->realText());
            //     $comment->setPost($post);
            //     $comment->setAuthor($users[$faker->numberBetween(0, 19)]);
            //     $manager->persist($comment);
            //     $manager->flush();
            //     $post->addComment($comment);
            // }
            $manager->persist($post);
            $manager->flush();
        }
    }
}
