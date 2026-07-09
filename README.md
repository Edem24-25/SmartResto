# SmartResto — Application de gestion de restaurant (PHP / MySQL)

Implémentation v1 basée sur le CDC & Backlog SmartResto.

## Contenu
- Site vitrine public (accueil, menu, contact)
- Authentification + gestion des rôles (Gérant, Serveur, Cuisinier, Caissier)
- Tableau de bord (KPIs : CA du jour, tickets, plats vendus, alertes stock)
- Plan de salle interactif (états : libre / occupée / réservée / à nettoyer)
- Prise de commande & modification avant envoi cuisine
- Écran cuisine (KDS) avec minuteur et changement de statut
- Facturation & encaissement (Espèces, Mobile Money MTN/Moov/Celtiis, Carte)
- Gestion du menu (catégories, plats, rupture)
- Gestion des stocks (ingrédients, seuil d'alerte, entrées fournisseur)
- Gestion du personnel (comptes + rôles)
- Rapports simples

## Stack
- PHP 8.1+ (procédural, PDO)
- MySQL 8 / MariaDB 10.4+
- HTML5 / CSS3 (custom) / JavaScript vanilla
- Aucun framework externe requis (Chart via CDN pour les graphes)

## Installation

1. Copier le dossier `smartresto/` dans `htdocs/` (XAMPP/WAMP/MAMP) ou `/var/www/html/`.
2. Créer la base de données :
   ```sql
   CREATE DATABASE smartresto CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. Importer `database.sql` :
   ```bash
   mysql -u root -p smartresto < database.sql
   ```
4. Configurer `config/db.php` (host, user, pass).
5. Ouvrir `http://localhost/smartresto/public/index.php`.

## Comptes de démonstration
| Rôle       | Email                    | Mot de passe |
|------------|--------------------------|--------------|
| Gérant     | admin@smartresto.bj      | admin123     |
| Serveur    | serveur@smartresto.bj    | serveur123   |
| Cuisinier  | cuisine@smartresto.bj    | cuisine123   |
| Caissier   | caisse@smartresto.bj     | caisse123    |

## Structure
```
smartresto/
├── config/       Configuration DB & app
├── includes/     Auth, layout, helpers
├── assets/       CSS / JS / images
├── public/       Pages publiques (vitrine, login)
├── app/          Pages fonctionnelles (dashboard, salle, cuisine…)
├── api/          Endpoints AJAX
└── database.sql  Schéma + données de démo
```
