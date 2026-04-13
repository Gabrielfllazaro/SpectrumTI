<?php
session_start();

$host = "localhost";
$user = "gabrielkafferDS";
$password = "gabrielkafferDS123@";
$database = "spectrum";

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

function redirect_with_message($redirect, $status, $message) {
    $separator = parse_url($redirect, PHP_URL_QUERY) ? '&' : '?';
    header("Location: {$redirect}{$separator}{$status}=" . urlencode($message));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $redirect = trim($_POST['redirect'] ?? '../html/login.html');
    $redirect = filter_var($redirect, FILTER_SANITIZE_URL);

    if (empty($email) || empty($senha)) {
        redirect_with_message($redirect, 'error', 'E-mail e senha são obrigatórios.');
    }

    $stmt = $conn->prepare('SELECT id, nome, senha FROM usuarios WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        redirect_with_message($redirect, 'error', 'Credenciais inválidas.');
    }

    $user = $result->fetch_assoc();
    if (!password_verify($senha, $user['senha'])) {
        redirect_with_message($redirect, 'error', 'Credenciais inválidas.');
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_nome'] = $user['nome'];
    $_SESSION['user_email'] = $email;

    redirect_with_message($redirect, 'success', 'Login realizado com sucesso.');
}

$conn->close();
