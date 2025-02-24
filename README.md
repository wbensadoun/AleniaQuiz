# Alenia Quiz Application

Une application web de quiz moderne et intuitive développée avec PHP et MySQL.

## Fonctionnalités

- Création de quiz via upload de fichiers CSV
- Interface administrateur sécurisée
- Système de suivi des résultats
- Design moderne et responsive
- Gestion des utilisateurs

## Prérequis

- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Serveur web (Apache recommandé)
- Extension PHP MySQL

## Installation

1. Clonez le dépôt :
```bash
git clone [URL_DU_REPO]
```

2. Configurez votre base de données MySQL

3. Importez la structure de la base de données :
```sql
-- La structure sera fournie dans database.sql
```

4. Configurez vos paramètres de connexion dans config.php

## Structure du Projet

```
QuizzApp/
├── css/                  # Styles CSS
├── includes/            # Fichiers inclus (header, footer)
├── secure_admin/        # Interface administrateur
├── uploads/            # Dossier pour les uploads
└── README.md           # Documentation
```

## Utilisation

1. Accédez à l'interface administrateur via `/secure_admin`
2. Uploadez vos fichiers CSV de questions
3. Les quiz sont automatiquement créés et disponibles

## Sécurité

- Authentification requise pour l'administration
- Validation des entrées utilisateur
- Protection contre les injections SQL
- Sessions sécurisées

## Contribution

Les contributions sont les bienvenues ! N'hésitez pas à :
1. Fork le projet
2. Créer une branche pour votre fonctionnalité
3. Commiter vos changements
4. Pusher vers la branche
5. Ouvrir une Pull Request

## Licence

[MIT License](LICENSE)
