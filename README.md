# EcoGardenAPI

## Description

EcoGardenAPI : L’API qui connecte votre jardin à la météo et aux saisons
Une API REST moderne pour intégrer facilement :
✅ Des conseils de jardinage mensuels, adaptés à chaque région de France,
✅ Des données météorologiques locales en temps réel,
✅ Un système d’authentification JWT pour une gestion sécurisée des comptes.
Pour les utilisateurs :

Un accès personnalisé à des conseils pratiques et à la météo de leur ville,
Une expérience simplifiée pour planifier leurs cultures via des routes.

Pour les administrateurs :

Des outils de gestion avancés pour mettre à jour les conseils et superviser les utilisateurs,
Une architecture flexible pour étendre les fonctionnalités.

Idéal pour :
🌱 Les applications mobiles de jardinage,
🌦️ Les sites web dédiés à l’écologie et à l’agriculture urbaine,
🔧 Les projets open-source ou commerciaux en lien avec la nature.


## Installation du projet

1. **Cloner le projet** :
   bash
   git clone https://github.com/L-deathnotavailable/EcoGardenApi.git
1. Modifier le fichier .env et renseigner vos informations de connexion à la base de données
2. Créer la base de données avec `php bin/console doctrine:database:create`
3. Appliquer les migrations avec `php bin/console doctirne:migrations:migrate`
4. Insérer les fixtures avec `php bin/console doctrine:fixtures:load`
5. Lancer le serveur
6. Pour vous connecter utiliser les données suivantes :
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

## Fonctionnalités 

1. ✅ Accéder aux conseils de jardinage mensuels pour des recommandations adaptées à chaque saison.
2. ✅ Consulter les conseils de jardinage actuels directement depuis l'API, en temps réel.
3. ✅ Obtenir des informations météorologiques pour une ville donnée, afin d’optimiser la planification des activités de jardinage.
4. ✅ Intégrer la météo locale pour la ville de l’utilisateur, et affiner les conseils en fonction des conditions climatiques.
5. ✅ Créer et gérer des comptes utilisateurs, incluant des informations essentielles comme la ville de résidence.
6. ✅ Authentification sécurisée via un système de tokens JWT, pour un accès protégé aux fonctionnalités.
7. ✅ Ajouter, modifier et supprimer des conseils de jardinage, afin de les adapter aux besoins des utilisateurs (réservé aux administrateurs).
8. ✅ Gérer les utilisateurs, y compris la mise à jour de leurs informations ou leur suppression (réservé aux administrateurs).
9. ✅ Gestion robuste des erreurs et réponses adaptées, pour garantir une utilisation fluide, sécurisée et sans interruption.

## Technologies utilisées
1. PHP 8.3
2. Symfony 6.4
3. MySQL
4. API Rest (Création)
5. API Publique : OpenWeatherMap (Utilisation)
6. Token JWT
7. Nelmio

✨ Projet réalisé dans le cadre du parcours OpenClassrooms.



