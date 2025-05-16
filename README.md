# But

* Ce site à pour but de créer des races, des espèces et des personnages d'un univers fictif
* Pour le moment seul les personnes avec un droit administrateur ont la possibilité de les créer
   * A terme, les utilisateurs pourront faire des suggestions de race, espèce et personnage ou des modifications ou d'ajouts à des pages existantes dont ils ne sont pas les créateursqui pourront plus tard etre validé par les authorité compétentes ou par la communauté, à déterminer plus tard
   * Egalement, il sera possible d'ajouter, modifier et supprimer des dimensions/lieux etc...
   * Le texte sur la gauche est le lore de cet univers
 

# Pages

* Navigation : Liste déroulante des pages ci-dessous. En cliquant dessus, redirige vers la liste de toutes les pages accessible par l'utilisateur dans l'ordre alphabétique :
   * Beings : Permet de sélectionner une espèce ou un race à visualiser
   * Characters : Permet de visualiser quel race/espèce possède au moins un personnage de cette race/espèce puis de la sélectionner pour la visualiser
   * Dimensions : Liste des différentes pages listé sous la page Dimensions
      * Dimensions_affichage : Affiche l'image de la représentation des différentes dimensions/plans d'existance de cet univers (non modifiable, peut-être plus tards)
      * Dimension_list : Affiche les dimensions une par une avec leur descriptions. (en cours de travaux)
   * Map : Affiche la carte du monde de la dimensions "Les Mondes Oubliés"
   * Species : Affiche la liste des différentes races, sans leurs races associés, avec une image afin de mieu se représenter ce à quoi elles ressemblent. En les sélectionnant cela ammène sur la page de l'espèce avec sa description et toutes ses races et leurs descriptions
 

* Species : Liste déroulante des diférentes espèces avec la possibilité de voir leurs races associés lorsque l'ont passe la souris dessus. Redirige vers la page Species lister plus haut. (liste à modifier ou supprimer plus tards)

* Admin : Liste déroulante de toutes les pages uniquement accessible aux admins :
   * Admin : Permet de bloquer ou débloquer un utilisateur (permettra à terme de modifier les roles et autres aspects des utilisateurs, comme un ban temporaire)
   * Character_add : Permet de modifier, ajouter, supprimer un personnage.
   * Specie_add : Permet de modifier, ajouter, supprimer une espèce
   * Race_add : Permet de modifier, ajouter, supprimer une race


# Utilisation

* En se connectant sur le site, si le compte n'est pas administrateur, il est uniquement possible d'observer les pages et les pages administrateurs sont invisible et bloquées si l'url est utilisé pour y accéder.
* En se connectant en tant qu'administrateur, les pages admins sont débloqués et visibles.
* Actions possibles :
   * Dans les différentes pages de descriptions, un boutton "edit" est affiché en haut à gauche de chaque espèce/race/personnage. En cliquant dessus, redirige vers la page admin correspondante avec toute les infos nécessaires préremplies.
   * Dans les différentes pages admin de races/espèces/personnages, il est possible au choix de sélectionner une race déjà existante pour la modifier ou la supprimer, ou bien remplir un nom pour en créer une nouvelle (il est pour le moment impossible de modifier un nom déjà existant)
      * Il est impossible de créer une race/espèce/personnage avec un nom déjà prit par un autre comme lui.
   * Il est possible de remplir les différents champs pour ajouter ou modifier les informations correspondantes de ces derniers.
      * Attention, les champs non-remplis ne seront pas modifié, il n'y a donc pas besoin de rentrer toutes les informations lors d'une modification, cependant si il y a un espace, un caractère ou plus à l'intérieur, l'information précédente sera écrasé par cette nouvelle donnée.
   * Il est possible d'ajouter une image à chacun d'entre eux dans la rubrique "[character/race/specie] icon" en choisissant un fichier présent sur votre ordinateur
      * Le fichier sera alors téléchargé dans un dossier prévu sur le serveur, le fichier aura alors comme nom le nom original du fichier avec un id unique ajouté
      * De préférance, ajouter des images au format carré pour la mise en forme
   * Dans chacune de ces pages il y a un boutton "Fetch Info" qui, une fois cliqué, affichera les informations de la race/espèce/personnage sélectionné plus haut
      * Cela ne marche pas si rien n'est sélectionné, par exemple lors d'une création au lieu d'une modification
   * Il est possible de supprimer la sélection en cliquant sur le boutton "Delete [race/specie/character]"
   * Pour enregistrer les modifications, cliquer sur le boutton "Submit"
   * Les pages des races et personnages ont une catégorie "correspondence" qui permet de dire à quel espèce et race ils correspondent respectivement
   * si l'utilisateur est connecté, un engrenage s'affichera à coté de son pseudonyme. cliquer dessus redirigera vers la page des paramètre utilisateur.
* Si vous souhaitez ajouter ou supprimer une page, il est impératif de supprimer son nom dans le script "authorisation.php" qui apparaitra dans les array $authorisation et $type


# Arborescence
* Web - racine du git, en plus des dossier il y a le fichier de connection à la BDD et un .gitignore
   * BDD (dossier vide, à supprimer)
   * images - là où sont téléchargées et stockés toutes les images utilisées sur le site
      * map - images spécifiques à la page "Map.php"
      * small_icon - petites images utilisées pour les icons
      * small_img - images de petites tailles utilisés pour les utilitaires
      * unused - images qui ne servent pas mais je je souhaite garder temporairement pour une potentielle utilisation futur
   * login - les pages permettant de se connecter, s'enregistrer, se déconnecter et la connection à la base de données
   * pages - là où se trouve toutes les pages du site 
      * blueprints - stockage des morceaux de codes utilisés sur pratiquement toutes les pages du site, évitant ainsi une redondance
      * scripts - les scripts étant parfois appelés, également là où se trouve le fichier functions.php
   * style - là où se trouve mes pages de style .css
   * texte - là où sont stockés les textes de grande tailles visuellement, mais tous de même légé en poids.



# Sous Docker
## Docker-compose.yml
* Voici le Docker-compose.yml que j'utilise :

services:
  web:
    build: .    # Utiliser le Dockerfile dans le répertoire courant
    ports:
      - "80:80"
    depends_on:
      - db
    volumes:
      - ./html:/var/www/html  # Map the html directory to /var/www/html in the container
      - ./php.ini:/usr/local/etc/php/php.ini # Montez le fichier php.ini personnalisé

  db:
    image: mysql:latest
    environment:
      MYSQL_ROOT_PASSWORD: root_password
      MYSQL_DATABASE: univers
    volumes:
      - ./mysql_data:/var/lib/mysql
    ports:
      - "3306:3306"  # Expose le port MySQL sur ton hôte

  phpmyadmin:
    image: phpmyadmin/phpmyadmin
    ports:
      - "8080:80"
    depends_on:
      - db
    environment:
      PMA_HOST: db

## Dockerfile

### Utiliser l'image PHP avec Apache
FROM php:8.2-apache

### #Installer les extensions nécessaires
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    libfreetype6-dev \
    && docker-php-ext-install pdo pdo_mysql zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install gd

### Copier le contenu du répertoire html dans le répertoire /var/www/html du conteneur
COPY ./html /var/www/html

### Activer les modules Apache nécessaires (si nécessaire)
RUN a2enmod rewrite

### Copier le fichier cacert.pem dans le conteneur
COPY ./cacert.pem /etc/ssl/certs/cacert.pem

### Configurer PHP pour utiliser le fichier cacert.pem
RUN echo "curl.cainfo = /etc/ssl/certs/cacert.pem" >> /usr/local/etc/php/conf.d/curl-ca.ini
RUN echo "openssl.cafile = /etc/ssl/certs/cacert.pem" >> /usr/local/etc/php/conf.d/openssl-ca.ini

### Copier le fichier composer.json et installer les dépendances
COPY composer.json /var/www/html/composer.json

### Définir le répertoire de travail
WORKDIR /var/www/html

### Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

### Installer les dépendances PHP
RUN composer install

### Commande par défaut
CMD ["apache2-foreground"]


## php.ini (optionel)
Permet de limiter la taille des images téléchargées. a changer au besoin mais il faudra aussi changer les commentaires dans les pages en "_add.php".

upload_max_filesize = 5M
post_max_size = 10M


## Précisions
* L'arborescence entre mon docker est la racine de mon git est comme tel:

* Docker - container
   * .gitignore
   * docker-compose.yml
   * Dockerfile
   * php.ini
   * html
      * test
         * Web - racine du git

* De cete manière, le lien pour accéder au site est http://localhost/test/Web/pages/Homepage.php
