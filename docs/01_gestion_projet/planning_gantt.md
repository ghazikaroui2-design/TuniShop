# Planning Gantt — TuniShop Marketplace

| Champ | Valeur |
|---|---|
| Version | 1.0 — 20/11/2026 |
| Période | 16/11/2026 → 24/01/2027 (10 semaines de production) |
| Jury final | 25/01/2027 |
| Méthode | Agile Scrum — 5 sprints de 2 semaines |
| Vue graphique | **[Ouvrir le diagramme de Gantt HTML](gantt.html)** |

---

## 1. Semaines calendaires

| Semaine | Dates | Sprint | Focus principal |
|---|---|---|---|
| S1 | 16/11 → 22/11 | Sprint 1 | Cadrage, CDC, backlog, Gantt |
| S2 | 23/11 → 29/11 | Sprint 1 | Personas, user flows, wireframes — **Validation 1** |
| S3 | 30/11 → 06/12 | Sprint 2 | UI kit, prototype Figma, dictionnaire/MCD/MLD |
| S4 | 07/12 → 13/12 | Sprint 2 | Script SQL, jeu d'essai, pages publiques |
| S5 | 14/12 → 20/12 | Sprint 3 | Triggers/procédures, socle MVC, espaces — **Validation 2** |
| S6 | 21/12 → 27/12 | Sprint 3 | CRUD, recherche avancée, accessibilité |
| S7 | 28/12 → 03/01 | Sprint 4 | Panier, checkout, paiement, authentification |
| S8 | 04/01 → 10/01 | Sprint 4 | Back-office, sécurité, SEO — **Préjury** |
| S9 | 11/01 → 17/01 | Sprint 5 | Audit, tests, refactoring, marketing |
| S10 | 18/01 → 24/01 | Sprint 5 | Déploiement, documentation — **Validation 3** |
| Jury | 25/01 | — | Soutenance et démonstration en direct |

---

## 2. Diagramme de Gantt (vue tableau)

Légende : █■ = période d'activité · ◆ = jalon.

| Lot de travail | Responsable | S1 | S2 | S3 | S4 | S5 | S6 | S7 | S8 | S9 | S10 | J |
|---|---|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|
| **1. Gestion de projet** | Chef de projet | █ | █ | █ | █ | █ | █ | █ | █ | █ | █ | |
| 1.1 CDC, backlog, Gantt | Chef de projet | █ | █ | | | | | | | | | |
| 1.2 Rituel Agile & suivi Sprint | Chef de projet | █ | █ | █ | █ | █ | █ | █ | █ | █ | █ | |
| 1.3 Revues & dossier synthèse | Chef de projet | | █ | | | █ | | | █ | █ | █ | |
| 1.4 Manuel utilisateur | Chef de projet | | | | | | | | | █ | █ | |
| **2. Design UX/UI** | UX/UI Designer | | █ | █ | █ | | | | | | | |
| 2.1 Personas & user flows | UX/UI Designer | | █ | | | | | | | | | |
| 2.2 Wireframes basse fidélité | UX/UI Designer | | █ | | | | | | | | | |
| 2.3 Identité graphique & UI kit | UX/UI Designer | | | █ | | | | | | | | |
| 2.4 Prototype Figma Desktop/Mobile | UX/UI Designer | | | █ | █ | | | | | | | |
| **3. Front-end responsive** | Intégrateur web | | | | █ | █ | █ | | | | | |
| 3.1 Accueil, catalogue, fiche produit | Intégrateur web | | | | █ | | | | | | | |
| 3.2 Espaces client/vendeur/admin | Intégrateur web | | | | | █ | | | | | | |
| 3.3 Tunnel panier/commande (vues) | Intégrateur web | | | | | █ | █ | | | | | |
| 3.4 Accessibilité & finitions | Intégrateur web | | | | | | █ | | | █ | | |
| **4. Base de données** | Architecte BDD | | | █ | █ | █ | | | | █ | | |
| 4.1 Dictionnaire, MCD, MLD | Architecte BDD | | | █ | | | | | | | | |
| 4.2 Script SQL & jeu d'essai | Architecte BDD | | | | █ | | | | | | | |
| 4.3 Vues, procédures, triggers | Architecte BDD | | | | | █ | | | | | | |
| 4.4 Indexation & contrôle intégrité | Architecte BDD | | | | | | | | | █ | | |
| **5. Back-end POO / MVC** | Développeur back | | | | | █ | █ | | | █ | | |
| 5.1 UML (classes, séquence) | Développeur back | | | | | █ | | | | | | |
| 5.2 Socle MVC & autoload | Développeur back | | | | | █ | | | | | | |
| 5.3 CRUD utilisateurs/boutiques/produits/avis | Développeur back | | | | | | █ | | | | | |
| 5.4 Refactoring & patterns | Développeur back | | | | | | | | | █ | | |
| **6. E-commerce** | Spécialiste e-commerce | | | | | █ | | █ | █ | | | |
| 6.1 Panier & calcul des frais | Spécialiste e-commerce | | | | | | | █ | | | | |
| 6.2 Checkout & confirmation | Spécialiste e-commerce | | | | | | | █ | | | | |
| 6.3 Paiement sandbox | Spécialiste e-commerce | | | | | | | █ | █ | | | |
| 6.4 Factures PDF & back-office | Spécialiste e-commerce | | | | | | | | █ | | | |
| **7. Sécurité** | Responsable sécurité | | | | | | | █ | █ | █ | | |
| 7.1 Authentification & RBAC | Responsable sécurité | | | | | | | █ | █ | | | |
| 7.2 CSRF / XSS / SQLi | Responsable sécurité | | | | | | | | █ | | | |
| 7.3 Audit & fiches correctives | Responsable sécurité | | | | | | | | | █ | | |
| **8. SEO & Marketing** | Responsable marketing | | | | | | | | █ | █ | | |
| 8.1 SEO on-page & technique | Responsable marketing | | | | | | | | █ | | | |
| 8.2 Plan de lancement & KPI | Responsable marketing | | | | | | | | | █ | | |
| **9. Déploiement & soutenance** | Équipe | | | | | | | | | | █ | █ |
| 9.1 Démo locale & sauvegarde | Équipe | | | | | | | | | | █ | |
| 9.2 Dossier final & soutenance | Équipe | | | | | | | | | | █ | █ |
| **JALONS** | | | ◆ | | | ◆ | | | ◆ | | ◆ | ◆ |

---

## 3. Jalons et livrables associés

| Jalon | Date | Livrables attendus | Responsable |
|---|---|---|---|
| ◆ **Validation 1** | 29/11/2026 | Cahier des charges, Gantt, backlog, personas, user flows, wireframes | Chef de projet / UX |
| ◆ **Validation 2** | 20/12/2026 | Interfaces responsives, prototype Figma, base SQL (tables + triggers + procédures + jeu d'essai), socle MVC, UML | UX / Front / BDD / Back |
| ◆ **Préjury** | 10/01/2027 | Démo parcours client + back-office, liste des anomalies | Équipe entière |
| ◆ **Validation 3** | 24/01/2027 | Dépôt Git, 7 dossiers numériques, scripts SQL, rapport d'audit, plan SEO | Chef de projet |
| ◆ **Jury final** | 25/01/2027 | Soutenance + démonstration en direct | Équipe entière |

---

## 4. Chemin critique et dépendances

```
CDC (S1-S2)
   ├─→ Wireframes (S2) ─→ UI kit + Prototype (S3-S4) ─→ Intégration responsive (S4-S6)
   └─→ MCD/MLD (S3) ─→ Script SQL (S4) ─→ Triggers/Procédures (S5)
                                            ↓
                        Socle MVC + UML (S5) ─→ CRUD (S6) ─→ Panier/Checkout (S7)
                                                                ↓
                                        Paiement + Sécurité (S7-S8) ─→ Dashboard/Factures/SEO (S8)
                                                                ↓
                                        Audit + Tests (S9) ─→ Déploiement + Soutenance (S10)
```

**Chemin critique** : `CDC → MCD/MLD → Script SQL → Socle MVC → CRUD → Panier/Checkout → Paiement/Sécurité → Audit → Déploiement → Jury`.

Tout retard sur la modélisation de la base (S3–S4) se répercute directement sur le back-end et donc sur le préjury (S8). C'est le point de vigilance n° 1.

### Dépendances inter-matières

| Dépendance | De → Vers |
|---|---|
| CDC & backlog → | UX/UI, BDD, POO |
| Wireframes & prototype Figma → | Intégration front-end |
| MCD/MLD & script SQL → | Socle MVC, CRUD, triggers |
| Socle MVC → | Modules e-commerce, sécurité |
| E-commerce + Sécurité → | Dashboard admin, rapport d'audit |
| Intégration pages → | SEO on-page, sitemap |
| Tous les livrables → | Dossier final & soutenance |

---

## 5. Charge par rôle (équipe de 4 personnes — hypothèse S1)

| Rôle | Membre | Charge principale | Sprints |
|---|---|---|---|
| Chef de projet | à désigner | CDC, backlog, Gantt, rituels, documentation, manuel, soutenance | S1 → S10 |
| UX/UI + Front-end | à désigner | Personas, flows, wireframes, Figma, intégration responsive, accessibilité | S2 → S6 |
| Back-end / POO / E-commerce | à désigner | UML, MVC, CRUD, panier, commande, paiement, factures | S5 → S9 |
| BDD / Sécurité / SEO | à désigner | Modélisation, SQL, triggers, auth, RBAC, audit, SEO | S3 → S9 |

> En cas d'équipe de 5 personnes : séparer « E-commerce » et « Sécurité/SEO » pour réduire la charge du sprint 4 (45 points).

---

## 6. Suivi d'avancement

Voir [suivi_sprints.md](suivi_sprints.md) pour :
- les objectifs de sprint et les points réalisés/estimés ;
- le burndown prévisionnel ;
- les comptes rendus de revue ;
- le registre des risques et des décisions.
