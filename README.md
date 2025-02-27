# Alenia Quiz - Plateforme de Quiz en Ligne

## Description
Alenia Quiz est une plateforme web interactive permettant aux professeurs de créer et gérer des quiz, et aux élèves de les passer. Le système inclut un tableau de bord pour suivre les progrès et une interface d'administration.

## Fonctionnalités

### Pour les Élèves
- Passage de quiz avec timer
- Tableau de bord personnalisé
- Historique des résultats
- Visualisation des corrections

### Pour les Professeurs
- Création de quiz via CSV
- Gestion des quiz existants
- Suivi des résultats des élèves
- Statistiques détaillées

### Pour les Administrateurs
- Gestion des utilisateurs
- Attribution des rôles
- Supervision globale

## Installation

1. Cloner le repository :
```bash
git clone https://github.com/wbensadoun/AleniaQuiz.git
```

2. Configurer la base de données :
- Créer une base de données MySQL nommée 'quizzapp'
- Importer le fichier update_database.php via le navigateur :
```
http://localhost/AleniaQuiz/update_database.php
```

3. Comptes de test :
- Admin : admin@quiz.com / admin123
- Professeur : professeur@quiz.com / prof123
- Élève : eleve1@quiz.com / eleve123

## Structure des fichiers

```
AleniaQuiz/
├── css/
│   └── style.css
├── includes/
│   └── header.php
├── database/
│   └── *.sql
├── templates/
│   └── quiz_template.csv
├── *.php
└── README.md
```

## Sécurité
- Mots de passe hashés avec password_hash()
- Protection contre les injections SQL
- Vérification des rôles et permissions
- Sessions sécurisées

## Technologies utilisées
- PHP 7.4+
- MySQL 5.7+
- HTML5/CSS3
- JavaScript

## Contribution
1. Fork le projet
2. Créer une branche (`git checkout -b feature/AmazingFeature`)
3. Commit les changements (`git commit -m 'Add some AmazingFeature'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrir une Pull Request

## License
Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.
