# Personas — TuniShop Marketplace

| Champ | Valeur |
|---|---|
| Version | 1.0 — 20/11/2026 |
| Source | Cahier des charges pédagogique §5.2 + atelier UX S2 |
| Objectif | Aligner les décisions de design sur les besoins réels des 3 acteurs principaux |

> Les personas sont des **fictions fondées sur la réalité de Sidi Bou Saïd**. Ils servent à justifier les choix d'interface devant le jury : chaque écran doit répondre à un besoin identifié ci-dessous.

---

## Persona 1 — Aymen, l'acheteur curieux (primaire)

| | |
|---|---|
| **Photo** | *[à insérer : homme 30 ans, tenue décontractée, smartphone en main]* |
| **Citation** | « Je veux rapporter un vrai objet fait ici, mais je n'ai pas envie de courir les boutiques les yeux fermés. » |

### Profil
| Attribut | Détail |
|---|---|
| Âge | 32 ans |
| Localisation | Tunis (15 km de Sidi Bou Saïd) |
| Situation | Jeune actif, visite le village le week-end |
| Équipement | Smartphone Android (principal), PC au bureau |
| Connaissance digitale | Confortable (achats en ligne occasionnels) |
| Budget moyen | 50–150 DT par achat artisanal |

### Objectifs
- Découvrir des objets authentiques avec une histoire et un visage derrière ;
- Comparer prix, disponibilité et avis avant de se déplacer ou de commander ;
- Recevoir chez lui sans frais excessifs ;
- Avoir une preuve d'achat (facture) pour un cadeau.

### Frustrations
- Ne pas savoir si l'objet est encore disponible avant de se déplacer ;
- Photos de mauvaise qualité ou absentes sur les pages Facebook des artisans ;
- Peur de payer en ligne sur un site inconnu ;
- Ne pas pouvoir suivre sa commande après le paiement.

### Tâches clés dans TuniShop
1. Parcourir le catalogue par catégorie (céramique, tapis, parfumerie…).
2. Rechercher « vase bleu » et filtrer par prix < 60 DT.
3. Lire la fiche produit : photos, prix TTC, stock, nom de l'atelier, avis.
4. Ajouter au panier, voir le total avec TVA et livraison.
5. Payer avec la carte de test sandbox, télécharger la facture, suivre le statut.
6. Laisser un avis après réception.

### Besoins UX déduits
| Besoin | Traduction design |
|---|---|
| Confiance | Photos grandes, nom et description de l'atelier, avis visibles, mention « Paiement simulé — aucun débit » |
| Rapidité | Recherche visible en haut, filtres persistants, panier accessible en 1 clic |
| Clarté des prix | Prix TTC affiché partout, récapitulatif détaillé (HT / TVA / livraison) |
| Mobile-first | Parcours d'achat complet sur 375 px, boutons tactiles ≥ 44 px |
| Réassurance | Statut de commande visible dans l'espace client, facture PDF téléchargeable |

---

## Persona 2 — Fatma, l'artisane locale (primaire)

| | |
|---|---|
| **Photo** | *[à insérer : femme 45 ans, atelier de céramique, bleu et blanc]* |
| **Citation** | « Je sais faire de beaux pots, mais je ne sais pas vendre sur Internet. Aidez-moi à rester simple. » |

### Profil
| Attribut | Détail |
|---|---|
| Âge | 45 ans |
| Localisation | Sidi Bou Saïd (atelier familial depuis 20 ans) |
| Métier | Céramiste — vaisselle, vases, carreaux décoratifs |
| Équipement | Smartphone (WhatsApp, Facebook), PC partagé à l'atelier |
| Connaissance digitale | Faible à moyenne (Facebook oui, e-commerce non) |
| Temps disponible | 30 min/jour pour gérer la vitrine |

### Objectifs
- Montrer ses produits sans apprendre un outil compliqué ;
- Être payée et savoir combien elle a vendu ce mois-ci ;
- Ne pas se tromper sur les quantités en stock ;
- Traiter ses commandes sans paperasse.

### Frustrations
- Peur de « casser » quelque chose en cliquant ;
- Termes techniques (SKU, TVA, triggers) incompréhensibles ;
- Ne pas recevoir les commandes en temps réel ;
- Photos prises avec le téléphone, mal cadrées.

### Tâches clés dans TuniShop
1. Créer un compte vendeur et attendre la validation de l'administrateur.
2. Renseigner sa boutique : nom, description courte, quartier, téléphone.
3. Ajouter un produit : photo, titre, prix, catégorie, quantité en stock.
4. Voir les commandes reçues et changer le statut (« En préparation » → « Expédiée »).
5. Consulter son tableau de bord : ventes du mois, stock faible.

### Besoins UX déduits
| Besoin | Traduction design |
|---|---|
| Simplicité | Formulaires courts, libellés clairs (« Nom de votre atelier » au lieu de « Raison sociale »), aide contextuelle |
| Guidance | Barre d'étapes, messages de confirmation explicites, état « En attente de validation » bien visible |
| Feedback | Toast « Produit enregistré », badge « Stock faible » en orange |
| Accessibilité | Police lisible (16 px minimum), contrastes forts, boutons larges |
| Mobile | Interface vendeur utilisable depuis le smartphone de l'atelier |

---

## Persona 3 — Karim, l'administrateur de la plateforme (primaire)

| | |
|---|---|
| **Photo** | *[à insérer : homme 28 ans, responsable digital, écrans multiples]* |
| **Citation** | « Je dois voir en 10 secondes si la marketplace tourne bien et qui a besoin d'aide. » |

### Profil
| Attribut | Détail |
|---|---|
| Âge | 28 ans |
| Localisation | Tunis |
| Rôle | Animateur du projet / modérateur de la marketplace |
| Équipement | PC 27" (desktop), parfois tablette |
| Connaissance digitale | Élevée (Excel, CRM, SQL de base) |
| Fréquence | 2 à 3 fois par jour |

### Objectifs
- Valider rapidement les nouveaux vendeurs sans laisser proliférer les faux comptes ;
- Surveiller les ventes, les commandes bloquées et les ruptures de stock ;
- Modérer les avis et garantir la qualité du catalogue ;
- Produire un bilan pour le jury / la commune.

### Frustrations
- Données éparpillées (Facebook, Excel, appels téléphoniques) ;
- Ne pas savoir quelle commande est en retard ;
- Absence de vue globale sur le CA et les vendeurs actifs.

### Tâches clés dans TuniShop
1. Se connecter à l'espace administrateur.
2. Valider/refuser une demande de vendeur avec motif.
3. Consulter le dashboard : CA, commandes, vendeurs actifs, stock faible.
4. Filtrer les commandes par statut et relancer une commande « En attente ».
5. Publier ou masquer un avis, gérer les catégories et les utilisateurs.
6. Télécharger une facture pour contrôle.

### Besoins UX déduits
| Besoin | Traduction design |
|---|---|
| Vue d'ensemble | Dashboard avec 4 KPI + graphique + activité récente |
| Efficacité | Tableau filtrable, actions en ligne, badges de statut colorés |
| Traçabilité | Horodatage, journal des actions, motif de refus obligatoire |
| Sécurité | RBAC strict, confirmation avant suppression, session courte |

---

## Persona secondaire — Le touriste de passage (occasionnel)

| | |
|---|---|
| **Citation** | « J'ai 20 minutes, je veux un joli souvenir et je repars. » |
| **Besoins** | Accueil immédiat, catégories visuelles, achat rapide, retrait à l'atelier |
| **Impact design** | Bouton « Découvrir le catalogue » dès le hero, catégories en icônes, option « Retrait gratuit à Sidi Bou Saïd » (US-055) |

---

## Matrice de synthèse

| Besoin | Aymen (client) | Fatma (vendeuse) | Karim (admin) |
|---|:---:|:---:|:---:|
| Chemin d'achat court | ★★★ | ★ | ★★ |
| Photos & contenu de qualité | ★★★ | ★★ | ★★ |
| Gestion des stocks | — | ★★★ | ★★★ |
| Suivi des commandes | ★★★ | ★★★ | ★★★ |
| Tableau de bord | ★ | ★★ | ★★★ |
| Simplicité / faible charge cognitive | ★★ | ★★★ | ★★ |
| Sécurité & confiance | ★★★ | ★★ | ★★★ |
| Mobile | ★★★ | ★★★ | ★ |

---

## Scénarios d'usage (user scenarios)

### Scénario A — « Le cadeau du week-end »
Aymen est sur son téléphone, à Tunis. Il cherche un cadeau pour sa mère. Il ouvre TuniShop, tape « vase » dans la recherche, filtre à moins de 60 DT, lit la fiche d'un vase de l'**Atelier Bleu de Sidi Bou**, voit qu'il reste 7 pièces et que le vendeur est validé. Il ajoute au panier, paie avec la carte sandbox, reçoit `CMD-2026-0001`, télécharge sa facture et laisse un avis 5 étoiles après réception.

### Scénario B — « Le premier produit de Fatma »
Fatma crée son compte vendeur. Un bandeau indique « Votre compte est en attente de validation ». Karim valide le compte. Fatma reçoit un message « Votre boutique est en ligne ». Elle ajoute son premier produit avec une photo, un prix de 48 DT et un stock de 8. Le produit apparaît dans le catalogue. Une commande arrive : elle la passe en « En préparation » puis « Expédiée ». Le stock passe automatiquement à 7.

### Scénario C — « Le point de contrôle de Karim »
Karim ouvre le dashboard : 3 demandes de vendeurs en attente, 1 commande bloquée en « En attente », 6 produits en stock faible. Il valide 2 vendeurs, refuse le 3ᵉ avec motif, relance la commande et prévient Fatma. Il publie un avis modéré. Il exporte le bilan du mois.

---

## Validation UX

- [x] 3 personas primaires + 1 secondaire rédigés
- [x] Objectifs, frustrations, tâches et besoins UX explicités
- [x] Scénarios d'usage reliés aux user stories du backlog (US-035, US-037, US-041, US-046…)
- [ ] Photos de personas à insérer (Figma)
- [ ] Validation des personas par les formateurs (jalon 1 — 29/11)
