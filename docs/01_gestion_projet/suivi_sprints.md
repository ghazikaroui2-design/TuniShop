# Suivi des sprints — TuniShop Marketplace

| Champ | Valeur |
|---|---|
| Version | 1.1 — 21/11/2026 |
| Méthode | Scrum — 5 sprints de 2 semaines |
| État actuel | **Sprints 1 à 4 livrés par anticipation — Sprint 5 (S9–S10) en cours** |
| Outil de suivi | Ce document + tableau Kanban (GitHub Projects / Trello / Notion au choix) |
| Anomalies | [liste_anomalies.md](liste_anomalies.md) — 12 entrées, 0 bloquante ouverte |

---

## 1. Équipe et rôles (hypothèse : 4 membres)

| Rôle | Membre | Responsabilités | Disponibilité |
|---|---|---|---|
| Chef de projet / Product Owner | *à compléter* | CDC, backlog, planning, rituels, documentation, soutenance | S1 → S10 |
| UX/UI Designer + Intégrateur front | *à compléter* | Personas, flows, Figma, HTML/CSS/JS responsive, accessibilité | S2 → S6 |
| Développeur back-end / POO / E-commerce | *à compléter* | UML, MVC, CRUD, panier, commande, paiement, factures | S5 → S9 |
| Architecte BDD / Sécurité / SEO | *à compléter* | Modélisation, SQL, triggers, auth, RBAC, audit, SEO | S3 → S9 |

> ⚠️ **A-01 (21/11)** : les 4 noms restent à renseigner. DEC-007 fixe l'identité Git à **Ghazi KAROUI** (chef de projet présumé). Voir [liste_anomalies.md](liste_anomalies.md).

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

| Sprint | Période | Objectif (Sprint Goal) | Points | US | Statut réel au 21/11 |
|---|---|---|---|---|---|
| **Sprint 1** | 16/11 → 29/11 | Cadrer le projet et valider la structure UX | 27 | US-001 → US-010 | ✅ Terminé (25/27 — US-005 en attente A-01) |
| **Sprint 2** | 30/11 → 13/12 | Produire le design haute fidélité et la base SQL | 40 | US-011 → US-022 | ✅ Livré par anticipation (BDD 25 tests, UX 6 docs) |
| **Sprint 3** | 14/12 → 27/12 | Avoir une application MVC + CRUD et un site responsive complet | 41 | US-023 → US-034 | ✅ Livré par anticipation (MVC 24 tests + frontend 12 tests) |
| **Sprint 4** | 28/12 → 10/01 | Réussir le parcours d'achat de bout en bout et sécuriser | 45 | US-035 → US-047 | 🟡 Presque : E-commerce 15 + sécurité 18 verts ; US-047 SEO partielle (A-05) |
| **Sprint 5** | 11/01 → 24/01 | Auditer, corriger, documenter et déployer la démo | 30 | US-048 → US-058 | 🔵 En cours par anticipation (SEO, anomalies, soutenance, Git) |
| **TOTAL** | | | **183** | **58 US** | **~151 pts validés / 183** |

### Détail du Sprint 1 (terminé)

| US | Tâche | Pts | Responsable | État |
|---|---|---|---|---|
| US-001 | Rédiger le cahier des charges | 5 | Chef de projet | ✅ Terminé (v1.0) |
| US-002 | Construire le backlog produit | 3 | Chef de projet | ✅ Terminé (v1.0) |
| US-003 | Élaborer le planning Gantt | 3 | Chef de projet | ✅ Terminé (v1.0) |
| US-004 | Initialiser le dépôt et les conventions Git | 2 | Back-end | ✅ Terminé (Git 2.56.1, commit `5a6caeb`) |
| US-005 | Répartir les rôles et planifier les rituels | 2 | Chef de projet | 🟡 Noms à compléter (A-01) |
| US-006 | Créer les 3 personas (+ 1 secondaire) | 2 | UX/UI | ✅ Terminé |
| US-007 | Dessiner les user flows (7 flux Mermaid) | 2 | UX/UI | ✅ Terminé |
| US-008 | Wireframes front-office | 3 | UX/UI | ✅ Terminé (HTML + ASCII) |
| US-009 | Wireframes back-office | 3 | UX/UI | ✅ Terminé (HTML + ASCII) |
| US-010 | Dossier de recette / validation 1 | 2 | Chef de projet | ✅ Terminé (checklist dans ce document) |

**Points validés : 25 / 27 · 2 points en attente (noms des membres dans US-005 / A-01) · Sprint bouclé avec 8 jours d'avance.**

> Le prototype cliquable livré (12 écrans, 3 supports) couvre les US-006 à US-009 et anticipe une partie de l'US-012/US-013 (prototype Figma) prévue en Sprint 2.

### Écart constaté au 21/11/2026 — situation réelle

| Module | Livrables | Preuve de validation | Commit |
|---|---|---|---|
| Gestion de projet + UX/UI | 5 + 6 documents, prototype 12 écrans | PDV jalon 1 | `5a6caeb`, `4f8af7f` |
| Base de données | 20 tables, 6 vues, 7 fonctions, 13 procédures, 11 triggers | **25 SQL PASS** | `4f8af7f` |
| POO / MVC / UML | Routeur 26 routes, 8 modèles, 10 services, 4 diagrammes | **24 PHP PASS** | `aa2d59f` |
| E-commerce | Panier, checkout 3 étapes, sandbox, factures PDF | **15 PHP PASS** | `7328ef5` |
| Sécurité | Argon2id, throttle, CSRF, RBAC, CSP, audit | **18 PHP PASS** + `composer audit` propre | `58c51ba` |
| Frontend | Design system, 12 écrans, JS externe, WCAG AA | **12 PHP PASS** | `7d74622` |
| SEO / soutenance / anomalies | 8ᵉ et 9ᵉ dossiers `docs/`, registre des anomalies | Plan SEO + support livrés | en cours (21/11) |

> **Total validé : 94 tests PASS / 0 FAIL** (24 + 15 + 18 + 12 + 25). PHP lint : 64 fichiers, 0 erreur. L'équipe a donc livré en 6 semaines l'équivalent des sprints 1 à 4, soit environ **9 semaines de travail prévues**.

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
> **Écart constaté au 21/11 :** le Sprint 1 est bouclé avec 8 jours d'avance et les modules des sprints 2, 3 et 4 sont déjà livrés et validés par 94 tests. Le point réel se situe vers **~151 points validés / 183**, soit environ **34 points restants** contre 168 prévus à la date du 22/11. L'avance est donc d'environ **134 points sur le burndown de référence**. Le tableau ci-dessus est conservé comme projection initiale ; le paragraphe « Écart constaté au 21/11 » du §3 fait foi pour l'avancement réel.
>
> ⚠️ Cette avance ne doit pas masquer les 2 points encore ouverts (A-01 noms, A-02 remote Git) ni l'US-047 partielle (A-05 SEO) : ils sont suivis dans [liste_anomalies.md](liste_anomalies.md).

**Vélocité moyenne visée : 36,6 points / sprint (18,3 points / semaine).**

---

## 5. Suivi des jalons

### 🟡 Validation 1 — 29/11/2026 (préparée — 1 case ouverte)

- [x] Cahier des charges fonctionnel et technique
- [x] Backlog produit (58 US)
- [x] Planning Gantt (tableau + graphique)
- [x] Wireframes front-office (US-008)
- [x] Wireframes back-office (US-009)
- [x] Personas & user flows (US-006, US-007) — prototype cliquable livré en plus
- [x] Dossier de recette (US-010)
- [x] Installation Git + initialisation du dépôt (US-004 — commit `5a6caeb`)
- [ ] Noms des 4 membres dans le planning (US-005 — **A-01**, en attente du chef de projet)
- [x] Installation de Composer 2.11.2 + extensions PHP `zip`/`gd` (US-004)

### ✅ Validation 2 — 20/12/2026 (anticipée au 21/11)

- [x] Pages responsives intégrées (accueil, catalogue, fiche, espaces, tunnel) — 12 écrans, 3 breakpoints
- [x] Prototype Figma haute fidélité Desktop + Mobile — plan et prototype cliquable livrés (binaire `.fig` non versionné, A-08)
- [x] Base SQL : tables, contraintes, index, triggers, procédures, jeu d'essai — 25 tests PASS
- [x] Socle MVC + diagrammes UML — 24 tests PASS, 4 diagrammes Mermaid

### ✅ Préjury — 10/01/2027 (anticipée au 21/11)

- [x] Démo parcours client complet (recherche → fiche → panier → paiement → facture) — script slide 7
- [x] Démo back-office (validation vendeur, stocks, commandes, dashboard) — script slide 8
- [x] Liste des anomalies détectées avec priorité — [liste_anomalies.md](liste_anomalies.md), 12 entrées

### 🟡 Validation 3 — 24/01/2027 (en préparation)

- [ ] Dépôt Git complet (branches fusionnées, tags) — **A-02** : aucun remote configuré
- [x] 7 dossiers numériques conformes — **9 dossiers** livrés (`01` à `09`, dont SEO et soutenance)
- [x] Rapport d'audit de sécurité + plan SEO + support de soutenance — `checklist_securite.md`, `plan_seo.md`, `support_soutenance.md`

---

## 6. Registre des risques

| ID | Risque | Prob. | Impact | Responsable | Mitigation | État |
|---|---|---|---|---|---|---|
| R-01 | Retard sur la validation 2 (S5) | Moyenne | Fort | Chef de projet | Périmètre gelé à S2 — **livrée par anticipation au 21/11** (A-08 mineur ouvert) | 🟢 Fermé |
| R-02 | Périmètre trop large | Forte | Fort | PO | MoSCoW, réserve V1.1 | 🟢 Ouvert |
| R-03 | Composer absent (Git OK depuis S1) | Moyenne | Moyen | Back-end | Résolu : Composer 2.11.2 installé + extensions `zip`/`gd` activées | 🟢 Fermé |
| R-04 | Conflits de merge | Moyenne | Moyen | Back-end | Branches feature + PR | 🟢 Ouvert |
| R-05 | Trigger de stock défaillant | Moyenne | Fort | BDD | Tests SQL + transaction | 🟢 Ouvert |
| R-06 | Jeu de données irréaliste | Moyenne | Faible | BDD | Produits/artisans inspirés de Sidi Bou Saïd | 🟢 Ouvert |
| R-07 | Incompréhension sandbox | Faible | Moyen | E-commerce | Mentions explicites dans l'UI | 🟢 Ouvert |
| R-08 | Indisponibilité d'un membre | Faible | Fort | Chef de projet | Documentation partagée, binômes sur les modules clés | 🟢 Ouvert |
| R-09 | SEO technique incomplet (US-047) | Forte | Moyen | SEO | Plan P0 livré, 6 critères/12 déjà conformes — A-05 | 🟠 Ouvert |
| R-10 | Aucun remote Git (push impossible) | Forte | Fort | Back-end | URL du dépôt à fournir puis `git remote add origin` — A-02 | 🟠 Ouvert |

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
| DEC-008 | 21/11/2026 | Design system unique + **un seul fichier JS externe** chargé en `defer` | Conformité CSP `script-src 'self'` (aucun script inline) et maintenance facilitée |
| DEC-009 | 21/11/2026 | Une seule vue `dashboard/index.php` pour les 4 écrans de back-office | Évite 4 copies divergentes ; paramétrage par le contrôleur |
| DEC-010 | 21/11/2026 | 9 dossiers `docs/` (8 matières + soutenance) | Conformité à la checklist de remise finale du `git_workflow.md` §8 |

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
