<?php

namespace App\DataFixtures;

use App\Entity\Advice;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }
    public function load(ObjectManager $manager): void
    {
    $conseils = [
        1  => "Conseil de janvier : Planifiez votre année de jardinage en notant les rotations de cultures, les associations de plantes et les dates de semis.",
        2  => "Conseil de février : Bouturez les plantes d’intérieur pour les multiplier avant le printemps.",
        3  => "Conseil de mars : Divisez les vivaces pour rajeunir vos massifs et obtenir de nouveaux plants.",
        4  => "Conseil d'avril : Plantez des aromatiques (basilic, persil, ciboulette) en pots pour les avoir à portée de main.",
        5  => "Conseil de mai : Installez des tuteurs pour les plantes grimpantes (tomates, haricots, pois).",
        6  => "Conseil de juin : Récoltez les herbes aromatiques (menthe, thym, romarin) pour les faire sécher et les conserver.",
        7  => "Conseil de juillet : Organisez des visites ou des échanges de plants avec des voisins jardiniers.",
        8  => "Conseil d'août : Semez des engrais verts (moutarde, trèfle) pour enrichir le sol après les récoltes.",
        9  => "Conseil de septembre : Plantez des fleurs bisannuelles (pensées, myosotis) pour des couleurs précoces au printemps.",
        10 => "Conseil d'octobre : Fabriquez du compost avec les déchets verts du jardin et de la cuisine.",
        11 => "Conseil de novembre : Rentrez les plantes gélives (géraniums, agrumes) et protégez-les du froid.",
        12 => "Conseil de décembre : Nettoyez et affûtez vos outils de jardin pour qu’ils soient prêts pour l’année suivante."
    ];

    foreach ($conseils as $mois => $texte) {
        $advice = new Advice();
        $advice->setAdviceText($texte);
        $advice->setMonth($mois);

        $manager->persist($advice);
    }
      $usersData = [
            [
                'email' => 'admin@mail.com',
                'password' => 'root123',
                'roles' => ['ROLE_ADMIN'],
                'postCode' => 19000
            ],
            [
                'email' => 'user@mail.com',
                'password' => 'user/123',
                'roles' => ['ROLE_USER'],
                'postCode' => 63000
            ],
        ];

        foreach ($usersData as $userData) {
            $user = new User();
            $user->setEmail($userData['email']);
            $user->setRoles($userData['roles']);
            $user->setPostCode($userData['postCode']);

            // Hachage du mot de passe
            $hashedPassword = $this->passwordHasher->hashPassword($user, $userData['password']);
            $user->setPassword($hashedPassword);

            $manager->persist($user);
        }

        $manager->flush();
    }
}

