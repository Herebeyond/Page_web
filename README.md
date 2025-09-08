# Les Chroniques de la Faille - World-Building Web Application

## English Version

### Project Overview
**Les Chroniques de la Faille - Les mondes oubliés** is a comprehensive English fantasy world-building web application built with PHP/MySQL. It allows users to explore and administrators to manage species, races, characters, dimensions, and an interactive map system for a fictional universe.

### Key Features

#### 🌍 **Interactive World Exploration**
- **Species & Races Management**: Complete hierarchical system with species containing multiple races
- **Character System**: Characters organized by race and species with detailed profiles
- **Interactive Map**: Clickable world map with points of interest, multiple map layers (world, aerial, underground)
- **Dimensions System**: Visual representation of different planes of existence
- **Ideas Management**: Admin system for managing universe lore and world-building concepts

#### 🔐 **Advanced User Management**
- **Multi-level Authorization**: Public access, authenticated users, and admin-only pages
- **Role-based Access Control**: Different features available based on user permissions
- **Secure Authentication**: Session management with timeout and security measures

#### ⚡ **Modern Web Technology**
- **AJAX-Powered Interface**: Dynamic updates without page reloads
- **Modal Admin Interface**: Integrated management system for all entities
- **Responsive Design**: Mobile-friendly interface with adaptive layouts
- **Smart File Management**: Automatic image handling with unique naming
- **Blueprint Architecture**: Consistent page structure with reusable components

### Current Pages & Features

#### **Public Access Pages:**
- **Homepage**: Welcome page with universe introduction
- **Map_view**: Interactive exploration map for all users
- **Beings**: Species and races browser with expandable cards
- **Beings_display**: Detailed view of specific species/races (via links)
- **Characters**: Character browser organized by species and races
- **Character_display**: Detailed character listings (via links)
- **Dimensions**: Landing page for dimension-related content
  - **Dimension_affichage**: Visual representation of dimensional planes
  - **Dimension_list**: Detailed dimension descriptions and lore

#### **Authenticated User Pages:**
- **User_profil**: User profile management and settings

#### **Administrator-Only Pages:**
- **Admin**: User management system (block/unblock users)
- **Map_modif**: Advanced map editing with point management and type systems
- **places_manager**: Location and place management interface
- **Ideas**: Universe lore and world-building concept management
- **Character_add**: Legacy character management (being phased out)

#### **Integrated Admin Features:**
- **Species Management**: Add, edit, delete species with real-time statistics
- **Race Management**: Complete race CRUD with species association
- **Character Management**: Integrated into display pages with modal interface
- **Map Point Management**: Advanced editing tools with type categorization
- **File Upload System**: Secure image handling for all entities

### Technical Architecture

#### **Blueprint System**
Every page follows a strict 3-part structure:
```php
<?php
require_once "./blueprints/page_init.php";     // Auth, sessions, DB
require_once "./blueprints/gl_ap_start.php";   // HTML start, header
?>
<!-- Custom page content -->
<?php
require_once "./blueprints/gl_ap_end.php";     // Footer, scroll-to-top, closing tags
?>
```

#### **Database Structure**
- **Species Table**: Core species data with icons and descriptions
- **Races Table**: Races linked to species with detailed attributes (lifespan, homeworld)
- **Characters Table**: Individual characters linked to races
- **Interest_points Table**: Map locations with coordinates and types
- **Ideas Table**: Hierarchical lore management system
- **Users Table**: Authentication and role management

#### **Security Features**
- **Input Validation**: Comprehensive sanitization and validation functions
- **Path Traversal Prevention**: Secure file handling with path validation
- **XSS Protection**: Proper output escaping throughout the application
- **SQL Injection Prevention**: Prepared statements for all database queries
- **File Upload Security**: Type validation and secure naming

### Docker Environment

#### **Services:**
- **Web Container** (chroniques_web): PHP 8.2 + Apache with production optimizations
- **Database Container** (chroniques_db): MySQL with optimized configuration
- **phpMyAdmin** (chroniques_phpmyadmin): Database administration interface
- **Redis Cache** (chroniques_redis): Session and caching system

#### **Production Features:**
- **Multi-stage Dockerfile**: Optimized builds with security enhancements
- **Health Checks**: Automated service monitoring
- **Volume Management**: Persistent data storage
- **Network Security**: Isolated container networking
- **Environment Variables**: Secure configuration management

#### **Automated Backup System**
- **Smart Backups**: Only creates backup when database changes
- **OneDrive Integration**: Automatic sync to `C:\Users\baill\OneDrive\Documents\Docker saves`
- **30-minute Intervals**: Continuous protection while Docker runs
- **Readable Format**: .txt files with human-readable SQL dumps
- **Automatic Cleanup**: Maintains 20 most recent backups

### Development Workflow

#### **Adding New Features:**
1. Update `pages/scriptes/authorisation.php` for page access control
2. Follow blueprint architecture for consistent structure
3. Use shared functions from `pages/scriptes/functions.php`
4. Implement responsive design with existing CSS classes
5. Document changes in `.github/modifications.txt`

#### **File Organization:**
```
Web/
├── .github/              # Project documentation and instructions
├── images/               # All image assets (auto-organized)
│   ├── maps/            # Map images and overlays
│   ├── places/          # Location-specific images
│   └── small_icon/      # UI icons and thumbnails
├── pages/
│   ├── blueprints/      # Shared page components
│   ├── scriptes/        # Backend logic and APIs
│   └── *.php           # Individual page files
├── login/               # Authentication system
├── style/               # CSS stylesheets
├── texte/               # Large text content storage
└── tests/               # Development testing files
```

### Access Information

#### **Local Development URLs:**
- **Main Application**: `http://localhost/test/Web/pages/Homepage.php`
- **Database Admin**: `http://localhost:8080` (phpMyAdmin)
- **Database**: `localhost:3306`

#### **Docker Commands:**
```bash
# Start all services
docker-compose up -d

# View logs
docker-compose logs

# Database backup
.\backup-once.bat  # Single backup
.\start-backup.bat # Continuous backup (30-minute intervals)

# Stop services
docker-compose down
```

---

## Version Française

### Aperçu du Projet
**Les Chroniques de la Faille - Les mondes oubliés** est une application web complète de création d'univers fantastique anglais construite avec PHP/MySQL. Elle permet aux utilisateurs d'explorer et aux administrateurs de gérer les espèces, races, personnages, dimensions et un système de carte interactive pour un univers fictif.

### Fonctionnalités Principales

#### 🌍 **Exploration Interactive du Monde**
- **Gestion des Espèces et Races**: Système hiérarchique complet avec espèces contenant plusieurs races
- **Système de Personnages**: Personnages organisés par race et espèce avec profils détaillés
- **Carte Interactive**: Carte du monde cliquable avec points d'intérêt, couches multiples (monde, aérien, souterrain)
- **Système des Dimensions**: Représentation visuelle des différents plans d'existence
- **Gestion des Idées**: Système admin pour gérer la lore et les concepts de world-building

#### 🔐 **Gestion Avancée des Utilisateurs**
- **Autorisation Multi-niveaux**: Accès public, utilisateurs authentifiés et pages admin uniquement
- **Contrôle d'Accès basé sur les Rôles**: Fonctionnalités différentes selon les permissions
- **Authentification Sécurisée**: Gestion des sessions avec timeout et mesures de sécurité

#### ⚡ **Technologie Web Moderne**
- **Interface AJAX**: Mises à jour dynamiques sans rechargement de page
- **Interface Admin Modale**: Système de gestion intégré pour toutes les entités
- **Design Responsive**: Interface mobile avec layouts adaptatifs
- **Gestion Intelligente des Fichiers**: Traitement automatique des images avec nommage unique
- **Architecture Blueprint**: Structure de page cohérente avec composants réutilisables

### Pages et Fonctionnalités Actuelles

#### **Pages d'Accès Public:**
- **Homepage**: Page d'accueil avec introduction à l'univers
- **Map_view**: Carte d'exploration interactive pour tous les utilisateurs
- **Beings**: Navigateur d'espèces et races avec cartes extensibles
- **Beings_display**: Vue détaillée d'espèces/races spécifiques (via liens)
- **Characters**: Navigateur de personnages organisé par espèces et races
- **Character_display**: Listes détaillées de personnages (via liens)
- **Dimensions**: Page d'accueil pour le contenu lié aux dimensions
  - **Dimension_affichage**: Représentation visuelle des plans dimensionnels
  - **Dimension_list**: Descriptions détaillées et lore des dimensions

#### **Pages Utilisateurs Authentifiés:**
- **User_profil**: Gestion du profil utilisateur et paramètres

#### **Pages Administrateur Uniquement:**
- **Admin**: Système de gestion des utilisateurs (bloquer/débloquer)
- **Map_modif**: Édition avancée de carte avec gestion des points et systèmes de types
- **places_manager**: Interface de gestion des lieux et endroits
- **Ideas**: Gestion de la lore de l'univers et concepts de world-building
- **Character_add**: Gestion legacy des personnages (en cours de suppression)

#### **Fonctionnalités Admin Intégrées:**
- **Gestion des Espèces**: Ajout, édition, suppression avec statistiques temps réel
- **Gestion des Races**: CRUD complet des races avec association aux espèces
- **Gestion des Personnages**: Intégrée dans les pages d'affichage avec interface modale
- **Gestion des Points de Carte**: Outils d'édition avancés avec catégorisation par type
- **Système d'Upload de Fichiers**: Traitement sécurisé d'images pour toutes les entités

### Architecture Technique

#### **Système Blueprint**
Chaque page suit une structure stricte en 3 parties :
```php
<?php
require_once "./blueprints/page_init.php";     // Auth, sessions, DB
require_once "./blueprints/gl_ap_start.php";   // HTML début, en-tête
?>
<!-- Contenu personnalisé de la page -->
<?php
require_once "./blueprints/gl_ap_end.php";     // Pied de page, retour en haut, balises fermantes
?>
```

#### **Structure de Base de Données**
- **Table Species**: Données principales des espèces avec icônes et descriptions
- **Table Races**: Races liées aux espèces avec attributs détaillés (durée de vie, monde natal)
- **Table Characters**: Personnages individuels liés aux races
- **Table Interest_points**: Lieux sur la carte avec coordonnées et types
- **Table Ideas**: Système de gestion hiérarchique de la lore
- **Table Users**: Authentification et gestion des rôles

#### **Fonctionnalités de Sécurité**
- **Validation des Entrées**: Fonctions complètes de sanitisation et validation
- **Prévention du Path Traversal**: Traitement sécurisé des fichiers avec validation des chemins
- **Protection XSS**: Échappement approprié de sortie dans toute l'application
- **Prévention Injection SQL**: Instructions préparées pour toutes les requêtes
- **Sécurité Upload de Fichiers**: Validation de type et nommage sécurisé

### Environnement Docker

#### **Services:**
- **Conteneur Web** (chroniques_web): PHP 8.2 + Apache avec optimisations de production
- **Conteneur Base de Données** (chroniques_db): MySQL avec configuration optimisée
- **phpMyAdmin** (chroniques_phpmyadmin): Interface d'administration de base de données
- **Cache Redis** (chroniques_redis): Système de session et cache

#### **Fonctionnalités de Production:**
- **Dockerfile Multi-étapes**: Builds optimisés avec améliorations de sécurité
- **Contrôles de Santé**: Surveillance automatisée des services
- **Gestion des Volumes**: Stockage de données persistant
- **Sécurité Réseau**: Réseau de conteneurs isolé
- **Variables d'Environnement**: Gestion de configuration sécurisée

#### **Système de Sauvegarde Automatisé**
- **Sauvegardes Intelligentes**: Ne crée une sauvegarde que si la base de données change
- **Intégration OneDrive**: Synchronisation automatique vers `C:\Users\baill\OneDrive\Documents\Docker saves`
- **Intervalles de 30 minutes**: Protection continue pendant que Docker fonctionne
- **Format Lisible**: Fichiers .txt avec dumps SQL lisibles par humain
- **Nettoyage Automatique**: Maintient les 20 sauvegardes les plus récentes

### Organisation des Fichiers

```
Web/
├── .github/              # Documentation du projet et instructions
├── images/               # Tous les assets d'images (auto-organisés)
│   ├── maps/            # Images de cartes et superpositions
│   ├── places/          # Images spécifiques aux lieux
│   └── small_icon/      # Icônes UI et miniatures
├── pages/
│   ├── blueprints/      # Composants de page partagés
│   ├── scriptes/        # Logique backend et APIs
│   └── *.php           # Fichiers de pages individuelles
├── login/               # Système d'authentification
├── style/               # Feuilles de style CSS
├── texte/               # Stockage de contenu textuel volumineux
└── tests/               # Fichiers de test de développement
```

### Informations d'Accès

#### **URLs de Développement Local:**
- **Application Principale**: `http://localhost/test/Web/pages/Homepage.php`
- **Admin Base de Données**: `http://localhost:8080` (phpMyAdmin)
- **Base de Données**: `localhost:3306`

#### **Commandes Docker:**
```bash
# Démarrer tous les services
docker-compose up -d

# Voir les logs
docker-compose logs

# Sauvegarde de base de données
.\backup-once.bat  # Sauvegarde unique
.\start-backup.bat # Sauvegarde continue (intervalles de 30 minutes)

# Arrêter les services
docker-compose down
```
