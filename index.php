<!DOCTYPE html>
<html>
<head>
    <title>Quiz App - Accueil</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .hero-container {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            width: 90%;
            margin: 20px auto;
        }

        .hero-title {
            font-size: 3em;
            color: #2196F3;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .hero-subtitle {
            font-size: 1.5em;
            color: #666;
            margin-bottom: 40px;
        }

        .features {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            margin: 40px 0;
            gap: 20px;
        }

        .feature {
            flex: 1;
            min-width: 200px;
            padding: 20px;
            text-align: center;
            background: #f8f9fa;
            border-radius: 10px;
            margin: 10px;
        }

        .feature-icon {
            font-size: 2em;
            color: #2196F3;
            margin-bottom: 15px;
        }

        .feature-title {
            font-size: 1.2em;
            color: #333;
            margin-bottom: 10px;
        }

        .feature-text {
            color: #666;
            font-size: 0.9em;
        }

        .cta-buttons {
            margin-top: 40px;
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        .cta-button {
            padding: 15px 30px;
            border-radius: 25px;
            font-size: 1.1em;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .primary-button {
            background: #2196F3;
            color: white;
        }

        .secondary-button {
            background: #4CAF50;
            color: white;
        }

        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
    <div class="hero-container">
        <h1 class="hero-title">Quiz App</h1>
        <p class="hero-subtitle">Testez vos connaissances de manière interactive et amusante</p>

        <div class="features">
            <div class="feature">
                <div class="feature-icon">📚</div>
                <h3 class="feature-title">Apprentissage</h3>
                <p class="feature-text">Des quiz variés pour tester et améliorer vos connaissances</p>
            </div>
            <div class="feature">
                <div class="feature-icon">📊</div>
                <h3 class="feature-title">Suivi</h3>
                <p class="feature-text">Suivez votre progression et vos résultats</p>
            </div>
            <div class="feature">
                <div class="feature-icon">🏆</div>
                <h3 class="feature-title">Réussite</h3>
                <p class="feature-text">Relevez des défis et atteignez vos objectifs</p>
            </div>
        </div>

        <div class="cta-buttons">
            <a href="login.php" class="cta-button primary-button">Se connecter</a>
            <a href="register.php" class="cta-button secondary-button">S'inscrire</a>
        </div>
    </div>
</body>
</html>
