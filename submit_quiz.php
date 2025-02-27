<?php
session_start();
require_once 'includes/db.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Vérifier si les données du quiz ont été soumises
if (!isset($_POST['quiz_id']) || !isset($_POST['answers'])) {
    header('Location: index.php');
    exit();
}

$quiz_id = (int)$_POST['quiz_id'];
$user_id = $_SESSION['user_id'];
$answers = $_POST['answers'];

// Récupérer les questions du quiz avec tous les détails
$stmt = $conn->prepare("SELECT id, question, option_a, option_b, option_c, option_d, correct_answer FROM questions WHERE quiz_id = ? ORDER BY id");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculer le score et préparer les détails des réponses
$score = 0;
$total_questions = count($questions);
$answers_details = array();

foreach ($questions as $index => $question) {
    $user_answer = isset($answers[$index]) ? $answers[$index] : '';
    $is_correct = ($user_answer === $question['correct_answer']);
    if ($is_correct) {
        $score++;
    }
    
    $answers_details[] = array(
        'question_id' => $question['id'],
        'question_text' => $question['question'],
        'user_answer' => $user_answer,
        'correct_answer' => $question['correct_answer'],
        'is_correct' => $is_correct,
        'options' => array(
            'A' => $question['option_a'],
            'B' => $question['option_b'],
            'C' => $question['option_c'],
            'D' => $question['option_d']
        )
    );
}

// Enregistrer le résultat avec les détails des réponses
$answers_json = json_encode($answers_details);
$stmt = $conn->prepare("INSERT INTO results (user_id, quiz_id, score, total_questions, answers_detail, completed_at) VALUES (?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("iiiis", $user_id, $quiz_id, $score, $total_questions, $answers_json);
$stmt->execute();
$result_id = $conn->insert_id;

// Rediriger vers la page des résultats
header('Location: my_results.php?result_id=' . $result_id);
exit();
