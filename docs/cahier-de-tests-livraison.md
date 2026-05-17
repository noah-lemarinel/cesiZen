# Cahier de tests de livraison

## 1. Objet

Ce document formalise les scénarios de recette fonctionnelle de l’application CesiZen.
Il vise à couvrir l’ensemble des fonctionnalités livrées, avec un niveau de détail suffisant pour exécuter la validation manuellement ou la rapprocher des tests automatisés.

## 2. Périmètre couvert

Les scénarios ci-dessous couvrent :

- **Authentification** : inscription, connexion, déconnexion, compte désactivé ;
- **Comptes utilisateurs** : consultation, mise à jour des informations, changement de mot de passe, suppression du compte ;
- **Administration des utilisateurs** : recherche, création, activation, désactivation, suppression ;
- **Informations** : consultation des ressources et gestion des articles ;
- **Exercices de respiration** : consultation, création, modification, suppression, droits d’accès ;
- **Tracker d’émotions** : journal, ajout, suppression, rapport, administration des émotions.

## 3. Règles de rédaction

Chaque scénario contient :

- un identifiant unique ;
- l’objectif du test ;
- les prérequis ;
- les étapes de test ;
- le résultat attendu ;
- la priorité.

## 4. Environnement de test

### 4.1 Pré-requis techniques

- Application déployée sur l’environnement de recette ;
- Base de données initialisée ;
- Comptes disponibles :
  - `utilisateur_test@cesizen.local` / mot de passe de recette ;
  - `admin_test@cesizen.local` / mot de passe de recette.

### 4.2 Données de test minimales

- au moins un compte utilisateur standard actif ;
- au moins un compte administrateur actif ;
- au moins un compte désactivé ;
- au moins un article publié ;
- au moins un article non publié ;
- au moins un exercice de respiration par défaut ;
- au moins un exercice créé par un utilisateur ;
- au moins une émotion principale et une sous-émotion ;
- au moins une entrée d’émotion liée à un utilisateur de test.

## 5. Scénarios de test

### 5.1 Authentification

| ID | Scénario | Préconditions | Étapes de test | Résultat attendu | Priorité |
|---|---|---|---|---|---|
| AUTH-01 | Inscription d’un nouvel utilisateur | Aucun compte avec l’adresse utilisée | 1. Ouvrir `/register`. 2. Saisir nom, e-mail et mot de passe valide. 3. Valider. | Le compte est créé et l’utilisateur est connecté automatiquement. | Haute |
| AUTH-02 | Connexion d’un utilisateur existant | Compte actif existant | 1. Ouvrir `/login`. 2. Saisir e-mail et mot de passe corrects. 3. Valider. | La connexion réussit et l’utilisateur est redirigé vers l’accueil. | Haute |
| AUTH-03 | Refus d’une connexion avec compte désactivé | Compte existant avec `isActive = false` | 1. Tenter de se connecter avec le compte désactivé. | La connexion est refusée et l’utilisateur reste sur la page de connexion. | Haute |
| AUTH-04 | Redirection d’un utilisateur déjà connecté vers l’accueil | Utilisateur déjà authentifié | 1. Ouvrir `/login` ou `/register`. | L’utilisateur est redirigé vers l’accueil. | Moyenne |

### 5.2 Comptes utilisateurs

| ID | Scénario | Préconditions | Étapes de test | Résultat attendu | Priorité |
|---|---|---|---|---|---|
| CU-01 | Consulter la page de compte | Utilisateur connecté | 1. Ouvrir `/account`. | Les informations du compte sont visibles. | Haute |
| CU-02 | Accéder à la page de compte en tant qu’anonyme | Aucun utilisateur connecté | 1. Ouvrir `/account`. | Redirection vers `/login`. | Haute |
| CU-03 | Modifier le nom et l’e-mail du compte | Utilisateur connecté | 1. Ouvrir `/account/edit`. 2. Modifier les informations. 3. Valider. | Les informations sont enregistrées et la page compte reflète les changements. | Haute |
| CU-04 | Refuser un e-mail déjà utilisé | Deux comptes existants avec e-mails distincts | 1. Ouvrir l’édition de compte. 2. Saisir un e-mail déjà utilisé. 3. Valider. | La mise à jour est refusée et les données du compte courant ne changent pas. | Haute |
| CU-05 | Changer le mot de passe avec l’ancien mot de passe correct | Utilisateur connecté et mot de passe connu | 1. Ouvrir `/account/password`. 2. Renseigner l’ancien mot de passe. 3. Saisir un nouveau mot de passe. 4. Confirmer. | Le mot de passe est mis à jour. | Haute |
| CU-06 | Refuser un ancien mot de passe incorrect | Utilisateur connecté | 1. Ouvrir `/account/password`. 2. Saisir un ancien mot de passe erroné. 3. Valider. | Le mot de passe n’est pas modifié. | Haute |
| CU-07 | Refuser une confirmation de mot de passe différente | Utilisateur connecté | 1. Saisir un nouveau mot de passe. 2. Saisir une confirmation différente. 3. Valider. | Le mot de passe n’est pas modifié. | Haute |
| CU-08 | Supprimer son propre compte | Utilisateur connecté | 1. Ouvrir `/account`. 2. Déclencher la suppression. 3. Confirmer. | Le compte est supprimé et l’utilisateur est redirigé vers la déconnexion. | Haute |

### 5.3 Administration des utilisateurs

| ID | Scénario | Préconditions | Étapes de test | Résultat attendu | Priorité |
|---|---|---|---|---|---|
| ADM-U-01 | Accéder à l’administration des utilisateurs en tant qu’anonyme | Aucun utilisateur connecté | 1. Ouvrir `/admin/users`. | Accès refusé. | Haute |
| ADM-U-02 | Accéder à l’administration des utilisateurs en tant qu’utilisateur standard | Utilisateur connecté non administrateur | 1. Ouvrir `/admin/users`. | Accès refusé. | Haute |
| ADM-U-03 | Rechercher un utilisateur | Administrateur connecté | 1. Ouvrir `/admin/users?q=...`. | La liste est filtrée selon le terme recherché. | Moyenne |
| ADM-U-04 | Créer un utilisateur en tant qu’administrateur | Administrateur connecté | 1. Ouvrir `/admin/users/create`. 2. Renseigner les champs. 3. Valider. | L’utilisateur est créé avec le rôle attendu. | Haute |
| ADM-U-05 | Désactiver un utilisateur | Administrateur connecté | 1. Ouvrir la liste des utilisateurs. 2. Choisir un compte actif. 3. Désactiver. | Le compte passe à l’état inactif. | Haute |
| ADM-U-06 | Réactiver un utilisateur | Administrateur connecté | 1. Choisir un compte inactif. 2. Activer le compte. | Le compte repasse à l’état actif. | Haute |
| ADM-U-07 | Supprimer un utilisateur | Administrateur connecté | 1. Choisir un compte tiers. 2. Supprimer avec token valide. | Le compte est supprimé. | Haute |
| ADM-U-08 | Empêcher l’auto-désactivation | Administrateur connecté | 1. Tenter de désactiver son propre compte. | L’action est refusée et le compte reste actif. | Haute |
| ADM-U-09 | Empêcher l’auto-suppression | Administrateur connecté | 1. Tenter de supprimer son propre compte. | L’action est refusée et le compte reste présent. | Haute |

### 5.4 Informations

| ID | Scénario | Préconditions | Étapes de test | Résultat attendu | Priorité |
|---|---|---|---|---|---|
| INF-01 | Consulter la liste des ressources | Aucune | 1. Ouvrir `/ressources`. | Les articles publiés sont visibles. | Haute |
| INF-02 | Consulter un article publié | Au moins un article publié existe | 1. Ouvrir l’article depuis la liste. | Le contenu de l’article est affiché. | Haute |
| INF-03 | Masquer un article non publié à un visiteur | Au moins un article non publié existe | 1. Ouvrir l’URL de l’article sans être administrateur. | Une erreur 404 est retournée. | Haute |
| INF-04 | Consulter un article non publié en tant qu’administrateur | Administrateur connecté et article non publié existant | 1. Ouvrir l’URL de l’article. | L’article est accessible. | Moyenne |
| INF-05 | Créer un article | Administrateur connecté | 1. Ouvrir `/ressources/blog/new`. 2. Renseigner titre, contenu et état publié. 3. Valider. | L’article est créé et apparaît dans la liste si publié. | Haute |
| INF-06 | Interdire la création d’article à un utilisateur standard | Utilisateur connecté non administrateur | 1. Ouvrir `/ressources/blog/new`. | Accès refusé. | Haute |
| INF-07 | Modifier un article | Administrateur connecté | 1. Ouvrir l’article. 2. Cliquer sur modifier. 3. Mettre à jour le contenu. 4. Valider. | Les modifications sont enregistrées. | Haute |
| INF-08 | Supprimer un article | Administrateur connecté | 1. Ouvrir l’article. 2. Soumettre la suppression avec token CSRF valide. | L’article est supprimé. | Haute |

### 5.5 Exercices de respiration

| ID | Scénario | Préconditions | Étapes de test | Résultat attendu | Priorité |
|---|---|---|---|---|---|
| EXT-01 | Consulter la liste des exercices | Aucune | 1. Ouvrir `/exercises`. | Les exercices publics sont visibles. | Haute |
| EXT-02 | Consulter un exercice de respiration | Exercice existant | 1. Ouvrir la fiche de l’exercice. | Les paramètres et le timer sont visibles. | Haute |
| EXT-03 | Créer un exercice personnel | Utilisateur connecté standard | 1. Ouvrir `/exercises/create`. 2. Saisir le nom, la description et les durées. 3. Valider. | L’exercice est créé et rattaché à l’utilisateur. | Haute |
| EXT-04 | Laisser un administrateur créer un exercice par défaut | Administrateur connecté | 1. Créer un exercice depuis `/exercises/create`. | L’exercice est créé sans propriétaire. | Moyenne |
| EXT-05 | Modifier son propre exercice | Utilisateur connecté propriétaire de l’exercice | 1. Ouvrir l’édition de l’exercice. 2. Modifier les valeurs. 3. Valider. | Les modifications sont enregistrées. | Haute |
| EXT-06 | Empêcher la modification d’un exercice d’un autre utilisateur | Utilisateur connecté non propriétaire | 1. Ouvrir l’édition de l’exercice d’un autre compte. | Accès refusé. | Haute |
| EXT-07 | Supprimer son propre exercice | Utilisateur connecté propriétaire | 1. Ouvrir la fiche exercice. 2. Soumettre la suppression. | L’exercice est supprimé. | Haute |
| EXT-08 | Empêcher la suppression d’un exercice d’un autre utilisateur | Utilisateur connecté non propriétaire | 1. Tenter de supprimer l’exercice d’un autre compte. | Accès refusé. | Haute |

### 5.6 Tracker d’émotions

| ID | Scénario | Préconditions | Étapes de test | Résultat attendu | Priorité |
|---|---|---|---|---|---|
| EMO-01 | Rediriger un utilisateur standard vers son journal | Utilisateur connecté standard | 1. Ouvrir `/emotion/tracker`. | Redirection vers `/emotion/tracker/journal`. | Haute |
| EMO-02 | Accéder à la gestion des émotions en tant qu’administrateur | Administrateur connecté | 1. Ouvrir `/emotion/tracker`. | La palette d’émotions administrable est affichée. | Haute |
| EMO-03 | Ajouter une émotion au journal | Utilisateur connecté | 1. Ouvrir `/emotion/tracker/add`. 2. Choisir une sous-émotion. 3. Saisir des notes. 4. Valider. | L’entrée est enregistrée dans le journal. | Haute |
| EMO-04 | Consulter son journal d’émotions | Utilisateur connecté avec au moins une entrée | 1. Ouvrir `/emotion/tracker/journal`. | Seules les entrées du compte connecté sont visibles. | Haute |
| EMO-05 | Supprimer sa propre entrée d’émotion | Utilisateur connecté avec une entrée existante | 1. Ouvrir le journal. 2. Supprimer une entrée. | L’entrée disparaît du journal. | Haute |
| EMO-06 | Empêcher la suppression d’une entrée appartenant à un autre utilisateur | Deux utilisateurs avec des entrées distinctes | 1. Tenter de supprimer l’entrée d’un autre compte. | L’accès est refusé. | Haute |
| EMO-07 | Consulter un rapport d’émotions sur une période donnée | Utilisateur connecté avec plusieurs entrées | 1. Ouvrir `/emotion/tracker/report`. 2. Choisir une période. | Les compteurs et les répartitions correspondent à la période choisie. | Moyenne |
| EMO-08 | Créer une émotion principale en tant qu’administrateur | Administrateur connecté | 1. Ouvrir `/emotion/tracker/create`. 2. Renseigner les informations. 3. Valider. | L’émotion est créée. | Haute |
| EMO-09 | Supprimer une émotion sans sous-émotion | Administrateur connecté | 1. Choisir une émotion sans enfant. 2. Supprimer. | L’émotion est supprimée. | Moyenne |
| EMO-10 | Refuser la suppression d’une émotion avec sous-émotions | Administrateur connecté | 1. Choisir une émotion ayant des sous-émotions. 2. Tenter la suppression. | La suppression est refusée. | Moyenne |

## 6. Critères de validation

La livraison est validée si :

- tous les scénarios de priorité **Haute** sont conformes ;
- les scénarios de priorité **Moyenne** ne présentent pas de blocage majeur ;
- aucun écart critique n’est constaté sur l’authentification, les accès et les données persistées ;
- les droits d’accès administrateur/utilisateur sont respectés.

## 7. Suivi des anomalies

Pour chaque anomalie, renseigner :

- l’identifiant du scénario ;
- la description du dysfonctionnement ;
- l’impact métier ;
- la gravité ;
- l’état de correction ;
- la date de résolution ;
- le statut final : accepté / corrigé / reporté.

## 8. Synthèse de couverture

| Domaine | Nombre de scénarios | Couverture |
|---|---:|---|
| Authentification | 4 | Inscription, connexion, compte désactivé, redirection |
| Comptes utilisateurs | 8 | Consultation, modification, mot de passe, suppression |
| Administration des utilisateurs | 9 | Recherche, création, activation, désactivation, suppression |
| Informations | 8 | Consultation publique et administration des articles |
| Exercices de respiration | 8 | Consultation, création, modification, suppression |
| Tracker d’émotions | 10 | Journal, ajout, suppression, rapport, administration |

