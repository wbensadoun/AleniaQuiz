// Démarrer le timer pour la question actuelle
function startTimer(duration) {
    const timerElement = document.getElementById('time');
    const quizForm = document.getElementById('quiz-form');
    let timeLeft = duration;

    const timerInterval = setInterval(() => {
        timeLeft--;
        timerElement.textContent = timeLeft;

        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            quizForm.submit(); // Soumettre le formulaire automatiquement
        }
    }, 1000);
}

// Initialisation du timer
document.addEventListener('DOMContentLoaded', () => {
    const questionTimer = parseInt(document.getElementById('time').textContent);
    startTimer(questionTimer);
});
