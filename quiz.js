// Classe pour gérer les logs
class QuizLogger {
    constructor(enableDebug = true) {
        this.enableDebug = enableDebug;
        this.logs = [];
        this.initTime = new Date();
        
        // Style pour la console
        console.log('%cQuiz Debug Mode', 'color: #4CAF50; font-size: 20px; font-weight: bold;');
        this.log('Logger initialized');
    }

    log(message, data = null) {
        const timestamp = new Date();
        const timeDiff = (timestamp - this.initTime) / 1000;
        
        console.log(
            `%c[${timeDiff.toFixed(3)}s] %c${message}`,
            'color: #888',
            'color: #4CAF50; font-weight: bold'
        );
        
        if (data) {
            console.log('%cData:', 'color: #888', data);
        }
    }

    error(message, data = null) {
        console.error(
            `%c[ERROR] %c${message}`,
            'color: #ff4444; font-weight: bold',
            'color: #ff4444'
        );
        
        if (data) {
            console.error('Error data:', data);
        }
    }

    warn(message, data = null) {
        console.warn(
            `%c[WARNING] %c${message}`,
            'color: #ffaa00; font-weight: bold',
            'color: #ffaa00'
        );
        
        if (data) {
            console.warn('Warning data:', data);
        }
    }
}

// Création de l'instance du logger
const quizLogger = new QuizLogger(true);

// Fonction pour analyser la structure des questions
function analyzeQuestions() {
    const questions = document.querySelectorAll('.question');
    quizLogger.log(`Analyzing ${questions.length} questions`);
    
    questions.forEach((questionElement, index) => {
        const questionData = {
            index: index + 1,
            text: questionElement.querySelector('h3')?.textContent || 'NO TEXT',
            options: Array.from(questionElement.querySelectorAll('input[type="radio"]')).map(input => input.value),
            hasError: questionElement.querySelector('.error') !== null
        };
        
        if (questionData.hasError) {
            quizLogger.error(`Question ${index + 1} has errors`, questionData);
        } else if (questionData.options.length === 0) {
            quizLogger.warn(`Question ${index + 1} has no options`, questionData);
        } else {
            quizLogger.log(`Question ${index + 1} structure:`, questionData);
        }
    });
}

// Écoute des événements du quiz
document.addEventListener('DOMContentLoaded', () => {
    quizLogger.log('Quiz initialized');
    
    // Analyse de la structure des questions
    analyzeQuestions();
    
    // Écoute des réponses
    document.querySelectorAll('input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', (e) => {
            const questionIndex = e.target.getAttribute('data-question-index');
            const answer = e.target.value;
            quizLogger.log('Answer selected', {
                questionIndex: parseInt(questionIndex) + 1,
                answer
            });
        });
    });
    
    // Écoute du formulaire
    const form = document.getElementById('quiz-form');
    if (form) {
        form.addEventListener('submit', (e) => {
            quizLogger.log('Form submitted', {
                numberOfQuestions: form.querySelectorAll('.question').length,
                numberOfAnswered: form.querySelectorAll('input[type="radio"]:checked').length
            });
        });
    } else {
        quizLogger.error('Quiz form not found');
    }
});

// Fonction utilitaire pour logger les erreurs non capturées
window.onerror = function(msg, url, lineNo, columnNo, error) {
    quizLogger.error('Uncaught Error', {
        message: msg,
        url,
        lineNo,
        columnNo,
        error
    });
    return false;
};

// Ajouter un style visuel pour les logs dans la console
console.log('%cOuvrez la console pour voir les logs en temps réel', 'color: #888; font-size: 14px;');
