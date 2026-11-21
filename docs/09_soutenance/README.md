# TuniShop — Dossier de soutenance (dossier 9)

| Champ | Valeur |
|---|---|
| Champ | Jury final — 25/01/2027 |
| Durée recommandée | 15 min de présentation + 10 min de démonstration + 5 min de questions |
| Public | Formateurs BTS DI 2-1, département Infographie & Multimédia |
| Prérequis | Préjury validé (10/01), liste des anomalies à jour |
| Références | [Cahier des charges](../01_gestion_projet/cahier_des_charges.md) · [Suivi des sprints](../01_gestion_projet/suivi_sprints.md) · [Anomalies](../01_gestion_projet/liste_anomalies.md) |

---

## 1. Fichiers livrés

| Document | Contenu |
|---|---|
| [support_soutenance.md](support_soutenance.md) | Déroulé du diaporama (13 slides), messages clés, démonstration scriptée, checklist matériel |
| [questions_jury.md](questions_jury.md) | 25 questions fréquentes avec réponses techniques argumentées |

> Ce dossier correspond au dossier `09_soutenance/` annoncé dans l'arborescence du README. Avec `08_seo_marketing/`, il porte à **9 le nombre de dossiers `docs/`**, comme exigé par la checklist de remise finale (`git_workflow.md` §8).

## 2. Chiffres clés à retenir

| Indicateur | Valeur |
|---|---|
| Durée du projet | 16/11/2026 → 25/01/2027 (5 sprints de 2 semaines) |
| User stories | 58 · 183 points · MoSCoW |
| Écrans du prototype intégrés | 12 (Desktop / Tablette / Mobile) |
| Routes MVC | 26, dont 3 ajoutées lors du frontend |
| Base de données | 20 tables InnoDB · 6 vues · 7 fonctions · 13 procédures · 11 triggers |
| Tests automatisés | **131 PASS / 0 FAIL** (24 MVC + 15 e-commerce + 18 sécurité + 12 frontend + 12 SEO + 25 SQL) |
| Matières couvertes | 8 (gestion, UX/UI, responsive, BDD, POO/UML, e-commerce, sécurité, SEO) |

## 3. Règles d'or de la présentation

1. **Démontrer avant d'expliquer** : montrer le parcours client, puis revenir sur l'architecture.
2. **Une phrase = une idée** : éviter la lecture des slides.
3. **Toujours relier au cahier des charges** : citez FR-xx / RG-xx / US-xxx à chaque affirmation.
4. **Être honnête sur les limites** : les anomalies ouvertes sont listées et assumées (report V1.1), voir [liste_anomalies.md](../01_gestion_projet/liste_anomalies.md).
5. **Avoir un plan B** : si la démo en direct échoue, les captures et les sorties de tests sont dans les docs.
