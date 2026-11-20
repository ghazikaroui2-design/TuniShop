# Suivi des sprints — TuniShop Marketplace

| Champ | Valeur |
|---|---|
| Version | 1.0 — 20/11/2026 |
| Méthode | Scrum — 5 sprints de 2 semaines |
| État actuel | **Sprint 1 (S1–S2) en cours — jour 5/10** |
| Outil de suivi | Ce document + tableau Kanban (GitHub Projects / Trello / Notion au choix) |

---

## 1. Équipe et rôles (hypothèse : 4 membres)

| Rôle | Membre | Responsabilités | Disponibilité |
|---|---|---|---|
| Chef de projet / Product Owner | *à compléter* | CDC, backlog, planning, rituels, documentation, soutenance | S1 → S10 |
| UX/UI Designer + Intégrateur front | *à compléter* | Personas, flows, Figma, HTML/CSS/JS responsive, accessibilité | S2 → S6 |
| Développeur back-end / POO / E-commerce | *à compléter* | UML, MVC, CRUD, panier, commande, paiement, factures | S5 → S9 |
| Architecte BDD / Sécurité / SEO | *à compléter* | Modélisation, SQL, triggers, auth, RBAC, audit, SEO | S3 → S9 |

> Si l'équipe compte 5 personnes : isoler le rôle « E-commerce » du back-end et le rôle « Sécurité/SEO » de la BDD pour absorber la charge du sprint 4.

---

## 2. Rituels Agile

| Rituel | Fréquence | Durée | Participants | Objectif |
|---|---|---|---|---|
| **Daily stand-up** | Chaque matin (ou 3×/semaine) | 15 min | Équipe | Qu'ai-je fait ? Que vais-je faire ? Quels blockers ? |
| **Planning de sprint** | 1ʳᵉ journée du sprint | 2 h | Équipe + PO | Choisir les US de la section « Sprint Backlog » et valider la capacité |
| **Revue de sprint** | Dernier jour de chaque sprint | 2 h | Équipe + formateurs | Démonstration des incréments, retours, validation du jalon |
| **Rétrospective** | Après la revue | 1 h | Équipe | « À garder / À améliorer / Actions correctives » |
| **Point jalon** | S2, S5, S8, S10 | ½ journée | Équipe + formateurs | Validation officielle (voir §5) |

---

## 3. Vue d'ensemble des sprints

| Sprint | Période | Objectif (Sprint Goal) | Points | US | Statut |
|---|---|---|---|---|---|
| **Sprint 1** | 16/11 → 29/11 | Cadrer le projet et valider la structure UX | 27 | US-001 → US-010 | 🔵 En cours (J5/10) |
| **Sprint 2** | 30/11 → 13/12 | Produire le design haute fidélité et la base SQL | 40 | US-011 → US-022 | ⚪ À venir |
| **Sprint 3** | 14/12 → 27/12 | Avoir une application MVC + CRUD et un site responsive complet | 41 | US-023 → US-034 | ⚪ À venir |
| **Sprint 4** | 28/12 → 10/01 | Réussir le parcours d'achat de bout en bout et sécuriser | 45 | US-035 → US-047 | ⚪ À venir |
| **Sprint 5** | 11/01 → 24/01 | Auditer, corriger, documenter et déployer la démo | 30 | US-048 → US-058 | ⚪ À venir |
| **TOTAL** | | | **183** | **58 US** | |

### Détail du Sprint 1 (en cours)

| US | Tâche | Pts | Responsable | État |
|---|---|---|---|---|
| US-001 | Rédiger le cahier des charges | 5 | Chef de projet | ✅ Terminé (v1.0) |
| US-002 | Construire le backlog produit | 3 | Chef de projet | ✅ Terminé (v1.0) |
| US-003 | Élaborer le planning Gantt | 3 | Chef de projet | ✅ Terminé (v1.0) |
| US-004 | Initialiser le dépôt et les conventions Git | 2 | Back-end | ✅ Terminé (Git 2.56.1, commit `5a6caeb`) |
| US-005 | Répartir les rôles et planifier les rituels | 2 | Chef de projet | 🟡 Noms à compléter |
| US-006 | Créer les 3 personas (+ 1 secondaire) | 2 | UX/UI | ✅ Terminé |
| US-007 | Dessiner les user flows (7 flux Mermaid) | 2 | UX/UI | ✅ Terminé |
| US-008 | Wireframes front-office | 3 | UX/UI | ✅ Terminé (HTML + ASCII) |
| US-009 | Wireframes back-office | 3 | UX/UI | ✅ Terminé (HTML + ASCII) |
| US-010 | Dossier de recette / validation 1 | 2 | Chef de projet | ✅ Terminé (checklist dans ce document) |

**Points validés : 25 / 27 · 2 points en attente (noms des membres dans US-005) · Jours restants : 5**

> Le prototype cliquable livré (12 écrans, 3 supports) couvre les US-006 à US-009 et anticipe une partie de l'US-012/US-013 (prototype Figma) prévue en Sprint 2.

---

## 4. Burndown prévisionnel

| Fin de semaine | Restant réel (prévu) | Restant idéal | Écart |
|---|---:|---:|---:|
| Départ (16/11) | 183 | 183 | 0 |
| S1 (22/11) | 168 | 165 | +3 |
| S2 (29/11) | 156 | 146 | +10 |
| S3 (06/12) | 137 | 128 | +9 |
| S4 (13/12) | 116 | 109 | +7 |
| S5 (20/12) | 95 | 91 | +4 |
| S6 (27/12) | 75 | 73 | +2 |
| S7 (03/01) | 52 | 55 | −3 |
| S8 (10/01) | 30 | 37 | −7 |
| S9 (17/01) | 12 | 18 | −6 |
| S10 (24/01) | 0 | 0 | 0 |

> Projection : la charge est concentrée sur les sprints 2 à 4 (121 points sur 183). Un retard de plus d'une semaine acquise en S3–S4 doit déclencher le basculement des US Should/Could en réserve (voir backlog §8).
>
> **Écart constaté au 20/11 :** le Sprint 1 est bouclé avec 3 jours d'avance (23 pts validés, 4 pts en attente externe). La courbe réelle est donc en avance d'environ 9 points sur la courbe idéale en S1 ; le tableau ci-dessus reste la projection initiale de référence.

**Vélocité moyenne visée : 36,6 points / sprint (18,3 points / semaine).**

---

## 5. Suivi des jalons

### 🟡 Validation 1 — 29/11/2026 (en préparation)

- [x] Cahier des charges fonctionnel et technique
- [x] Backlog produit (58 US)
- [x] Planning Gantt (tableau + graphique)
- [x] Wireframes front-office (US-008)
- [x] Wireframes back-office (US-009)
- [x] Personas & user flows (US-006, US-007) — prototype cliquable livré en plus
- [x] Dossier de recette (US-010)
- [x] Installation Git + initialisation du dépôt (US-004 — commit `5a6caeb`)
- [ ] Noms des 4 membres dans le planning (US-005)
- [x] Installation de Composer 2.11.2 + extensions PHP `zip`/`gd` (US-004)

### ⚪ Validation 2 — 20/12/2026

- [ ] Pages responsives intégrées (accueil, catalogue, fiche, espaces, tunnel)
- [ ] Prototype Figma haute fidélité Desktop + Mobile
- [ ] Base SQL : tables, contraintes, index, triggers, procédures, jeu d'essai
- [ ] Socle MVC + diagrammes UML

### ⚪ Préjury — 10/01/2027

- [ ] Démo parcours client complet (recherche → paiement → facture)
- [ ] Démo back-office (validation vendeur, stocks, commandes, dashboard)
- [ ] Liste des anomalies détectées avec priorité

### ⚪ Validation 3 — 24/01/2027

- [ ] Dépôt Git complet (branches fusionnées, tags)
- [ ] 7 dossiers numériques conformes
- [ ] Rapport d'audit de sécurité + plan SEO + support de soutenance

---

## 6. Registre des risques

| ID | Risque | Prob. | Impact | Responsable | Mitigation | État |
|---|---|---|---|---|---|---|
| R-01 | Retard sur la validation 2 (S5) | Moyenne | Fort | Chef de projet | Suivi hebdomadaire, périmètre gelé à S2 | 🟢 Ouvert |
| R-02 | Périmètre trop large | Forte | Fort | PO | MoSCoW, réserve V1.1 | 🟢 Ouvert |
| R-03 | Composer absent (Git OK depuis S1) | Moyenne | Moyen | Back-end | Résolu : Composer 2.11.2 installé + extensions `zip`/`gd` activées | 🟢 Fermé |
| R-04 | Conflits de merge | Moyenne | Moyen | Back-end | Branches feature + PR | 🟢 Ouvert |
| R-05 | Trigger de stock défaillant | Moyenne | Fort | BDD | Tests SQL + transaction | 🟢 Ouvert |
| R-06 | Jeu de données irréaliste | Moyenne | Faible | BDD | Produits/artisans inspirés de Sidi Bou Saïd | 🟢 Ouvert |
| R-07 | Incompréhension sandbox | Faible | Moyen | E-commerce | Mentions explicites dans l'UI | 🟢 Ouvert |
| R-08 | Indisponibilité d'un membre | Faible | Fort | Chef de projet | Documentation partagée, binômes sur les modules clés | 🟢 Ouvert |

---

## 7. Registre des décisions

| ID | Date | Décision | Justification |
|---|---|---|---|
| DEC-001 | 20/11/2026 | Stack **PHP 8.3 + MySQL 8 + MVC maison** | Cohérent avec XAMPP/MySQL disponibles et avec le programme BTS DI |
| DEC-002 | 20/11/2026 | 5 sprints de 2 semaines | Aligné sur les revues S2/S5/S8 du cahier pédagogique |
| DEC-003 | 20/11/2026 | 4 rôles principaux | Équipe supposée de 4 étudiants (à confirmer) |
| DEC-004 | 20/11/2026 | Périmètre Must gelé à S2 | Éviter la dérive fonctionnelle |
| DEC-005 | 20/11/2026 | Paiement **simulé uniquement** | Exigence du cahier des charges, aucun débit réel |
| DEC-006 | 20/11/2026 | TVA 19 %, livraison 8 DT offerte dès 100 DT | Règles métier RG-02 et RG-03 |
| DEC-007 | 20/11/2026 | Dépôt Git initialisé sur `main` + `develop` (commit `5a6caeb`), identité Ghazi KAROUI | Traçabilité des livrables et travail collaboratif dès S1 |

---

## 8. Modèles de comptes rendus

### Compte rendu de daily (exemple)

```markdown
## Daily — 20/11/2026
**Présents :** …
**Hier :**
- Chef de projet : rédaction CDC v1.0
- UX : recherche de références visuelles
**Aujourd'hui :**
- Chef de projet : finaliser backlog + Gantt
- UX : rédiger les personas
**Blockers :**
- Git et Composer non installés → installation prévue avant S3
```

### Compte rendu de revue de sprint

```markdown
## Revue du Sprint N — date
**Objectif du sprint :** …
**Points prévus / réalisés :** … / …
**Démonstration :** …
**Retours du PO / des formateurs :** …
**Anomalies détectées :** …
**Actions correctives (propriétaire + échéance) :** …
```

### Rétrospective

```markdown
## Rétro Sprint N
✅ À garder : …
⚠️ À améliorer : …
🔧 Actions : 1) … (responsable, date) 2) … (responsable, date)
```

---

## 9. Indicateurs de suivi

| Indicateur | Formule | Cible |
|---|---|---|
| Vélocité | Points validés / sprint | ≥ 30 pts |
| Taux de respect des jalons | Jalons validés à temps / jalons | 4 / 4 |
| Taux de défauts en recette | Anomalies bloquantes / US livrées | < 10 % |
| Couverture des critères d'acceptation | AC validés / AC prévus | 100 % à la validation 3 |
| Dette technique | Points refactoring restants | 0 à S10 |
