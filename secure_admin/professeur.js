document.addEventListener('DOMContentLoaded', function () {
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('file-input');
    const submitBtn = document.getElementById('submit-btn');
    const statusMessage = document.getElementById('status-message');

    let selectedFile = null;

    // Gestion du glisser-déposer
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.style.backgroundColor = '#e0e0e0';
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.style.backgroundColor = '#f9f9f9';
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.style.backgroundColor = '#f9f9f9';

        if (e.dataTransfer.files.length > 0) {
            selectedFile = e.dataTransfer.files[0];
            validateFile(selectedFile);
        }
    });

    // Gestion du bouton de sélection de fichier
    document.getElementById('file-select-btn').addEventListener('click', () => {
        document.getElementById('file-input').click();
    });

    // Gestion de la sélection de fichier
    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            selectedFile = e.target.files[0];
            validateFile(selectedFile);
        }
    });

    // Validation du fichier
    function validateFile(file) {
        if (file && file.name.endsWith('.csv')) {
            statusMessage.textContent = `Fichier sélectionné : ${file.name}`;
            submitBtn.disabled = false;
        } else {
            statusMessage.textContent = 'Veuillez sélectionner un fichier CSV valide.';
            submitBtn.disabled = true;
        }
    }

    // Soumission du fichier
    submitBtn.addEventListener('click', () => {
        if (selectedFile) {
            const formData = new FormData();
            formData.append('csvFile', selectedFile);

            fetch('upload.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    statusMessage.textContent = 'Fichier CSV chargé avec succès !';
                    window.location.href = '../quiz.php'; // Rediriger vers le quiz
                } else {
                    statusMessage.textContent = 'Erreur lors du chargement du fichier.';
                }
            })
            .catch(error => {
                statusMessage.textContent = 'Erreur lors de la communication avec le serveur.';
                console.error(error);
            });
        }
    });
});
