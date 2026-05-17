# Procédure de validation et de recette

## 1. Objet

Cette procédure décrit la méthode de validation de la livraison CesiZen.
Elle permet de vérifier que les fonctionnalités livrées correspondent au périmètre attendu avant la signature du procès-verbal de recette.

## 2. Périmètre

La validation porte sur les fonctionnalités suivantes :

- **Comptes utilisateurs**
- **Informations**
- **Tracker d’émotions**

## 3. Rôles

| Rôle | Responsabilités |
|---|---|
| Product owner / représentant métier | Valide le besoin, arbitre les anomalies, signe le PV de recette. |
| Recetteur fonctionnel | Exécute les scénarios, consigne les résultats, remonte les anomalies. |
| Équipe technique | Corrige les anomalies, prépare les livraisons de reprise si nécessaire. |

## 4. Préparation de la validation

Avant de lancer la recette :

1. Vérifier que la version à tester est bien déployée sur l’environnement de recette.
2. Contrôler l’accès à l’application et à la base de données de recette.
3. Préparer les comptes de test : utilisateur standard et administrateur.
4. Vérifier la présence des données minimales : articles publiés, article non publié, émotions, entrées d’émotions.
5. S’assurer que le cahier de tests de livraison est disponible au format exploitable.

## 5. Déroulé de validation

### 5.1 Exécution des tests

1. Exécuter les scénarios du cahier de tests dans l’ordre de priorité recommandé.
2. Pour chaque scénario, noter :
   - date et heure ;
   - nom du testeur ;
   - résultat obtenu ;
   - statut : conforme / non conforme / non testé.
3. Capturer les preuves utiles si nécessaire : captures d’écran, exports, logs applicatifs.

### 5.2 Gestion des anomalies

En cas de non-conformité :

1. Identifier précisément le scénario concerné.
2. Décrire l’écart observé et la reproduction du défaut.
3. Évaluer l’impact métier : mineur, majeur ou bloquant.
4. Décider du traitement : correction immédiate, report, ou acceptation avec réserve.
5. Rejouer le test après correction si une reprise est réalisée.

### 5.3 Clôture de recette

La validation est clôturée lorsque :

- les scénarios prioritaires ont été exécutés ;
- les anomalies bloquantes ont été corrigées ou contournées ;
- une décision formelle est prise sur la conformité de la livraison ;
- le PV de recette est signé.

## 6. Critères d’acceptation

La livraison peut être validée si :

- les parcours d’authentification et de gestion de compte fonctionnent correctement ;
- les pages de ressources sont accessibles conformément aux droits d’accès ;
- le tracker d’émotions permet bien la saisie, la consultation et la suppression des données selon le profil ;
- les contrôles d’accès et de sécurité attendus sont en place ;
- les anomalies restantes sont non bloquantes et explicitement acceptées.

## 7. Plan de validation recommandé

| Étape | Description | Résultat attendu |
|---|---|---|
| 1 | Contrôle de l’environnement | Version déployée et accessible |
| 2 | Validation du module Comptes utilisateurs | Tous les scénarios prioritaires sont conformes |
| 3 | Validation du module Informations | Consultation publique et administration conformes |
| 4 | Validation du module Tracker d’émotions | Parcours utilisateur et admin conformes |
| 5 | Revue des anomalies | Liste consolidée et décision prise |
| 6 | Signature du PV de recette | Livraison acceptée ou acceptée avec réserve |

## 8. Livrables de validation

À l’issue de la recette, conserver :

- le cahier de tests renseigné ;
- la liste des anomalies ;
- les preuves de validation ;
- le procès-verbal de recette signé.

## 9. Procédure de reprise en cas de refus

Si la recette est refusée :

1. Formaliser les réserves ou les motifs de refus.
2. Planifier la correction des anomalies.
3. Déployer une nouvelle version si nécessaire.
4. Reprendre la validation sur les scénarios impactés.
5. Émettre un nouveau PV de recette à l’issue de la reprise.

