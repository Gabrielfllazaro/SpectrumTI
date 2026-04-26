<?php
ob_start();
session_start();

// 🔥 SIMULA LOGIN (REMOVER DEPOIS)
$_SESSION['user_id'] = 1;
$_SESSION['nivel'] = 0;

// require_once 'check_session.php'; // ❌ desativa por enquanto

ob_clean();

header('Content-Type: application/json; charset=utf-8');

// Conexão
$host = "localhost";
$user = "root"; // 🔥 padrão XAMPP
$password = "";
$database = "spectrum";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); 

try {

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'Sessão inválida']);
        exit;
    }

    $conn = new mysqli($host, $user, $password, $database);
    $conn->set_charset("utf8mb4");

    $action = $_GET['action'] ?? '';
    $user_id = $_SESSION['user_id'];

    switch ($action) {

        // 🔥 DASHBOARD COMPLETO
        case 'dashboard':

            // 🔵 EM ANDAMENTO
            $sql = "
                SELECT t.*, i.nome as nome_tag
                FROM trilha t
                JOIN progresso_trilha p ON p.id_trilha = t.id_trilha
                LEFT JOIN tag_interesse i ON t.id_tag_interesse = i.id_interesse
                WHERE p.id_user = ?
                AND p.status IN (0,1)
            ";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $em_andamento = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            // 🟡 RECOMENDADOS
            $sql = "
                SELECT t.*, i.nome as nome_tag
                FROM trilha t
                LEFT JOIN tag_interesse i ON t.id_tag_interesse = i.id_interesse
                WHERE t.id_trilha NOT IN (
                    SELECT id_trilha 
                    FROM progresso_trilha 
                    WHERE id_user = ?
                )
            ";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $recomendados = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            // 🟣 INTERESSES
            $sql = "
                SELECT t.*, i.nome as nome_tag
                FROM trilha t
                JOIN user_interesse ui ON ui.id_interesse = t.id_tag_interesse
                LEFT JOIN tag_interesse i ON t.id_tag_interesse = i.id_interesse
                WHERE ui.id_user = ?
            ";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $interesses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            echo json_encode([
                'success' => true,
                'em_andamento' => $em_andamento,
                'recomendados' => $recomendados,
                'interesses' => $interesses
            ]);
        break;

        case 'detalhe':

    $id = $_GET['id'] ?? 0;

    // 🔹 BUSCA TRILHA
    $stmt = $conn->prepare("
        SELECT t.*, i.nome as nome_tag
        FROM trilha t
        LEFT JOIN tag_interesse i ON t.id_tag_interesse = i.id_interesse
        WHERE t.id_trilha = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $trilha = $stmt->get_result()->fetch_assoc();

    // 🔹 BUSCA AULAS (se existir tabela aula)
    try {
        $stmt = $conn->prepare("
            SELECT * FROM aula WHERE id_trilha = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $aulas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } catch (Exception $e) {
        // 🔥 fallback caso não exista tabela aula
        $aulas = [];
    }

    echo json_encode([
        'success' => true,
        'trilha' => $trilha,
        'aulas' => $aulas
    ]);

break;

        // 🔵 LISTA NORMAL
        case 'list':

            $sql = "
                SELECT t.*, i.nome as nome_tag
                FROM trilha t
                LEFT JOIN tag_interesse i ON t.id_tag_interesse = i.id_interesse
            ";
            $result = $conn->query($sql);

            echo json_encode([
                'success' => true,
                'dados' => $result->fetch_all(MYSQLI_ASSOC)
            ]);
        break;

        // 🏷 TAGS
        case 'list_tags':
            $result = $conn->query("SELECT id_interesse as id, nome FROM tag_interesse");
            echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        break;

        // 💾 SALVAR
        case 'save':

            $id = $_POST['id_trilha'] ?? '';
            $nome = $_POST['nome'] ?? '';
            $desc = $_POST['descricao'] ?? '';
            $img = $_POST['img'] ?? '';
            $tag = $_POST['id_tag_interesse'] ?? null;

            if (empty($id)) {
                $stmt = $conn->prepare("
                    INSERT INTO trilha 
                    (nome, descricao, img, id_tag_interesse, status, id_usuario, data_criacao) 
                    VALUES (?, ?, ?, ?, 1, ?, NOW())
                ");
                $stmt->bind_param("sssii", $nome, $desc, $img, $tag, $user_id);
            } else {
                $stmt = $conn->prepare("
                    UPDATE trilha 
                    SET nome=?, descricao=?, img=?, id_tag_interesse=? 
                    WHERE id_trilha=?
                ");
                $stmt->bind_param("sssii", $nome, $desc, $img, $tag, $id);
            }

            $stmt->execute();
            echo json_encode(['success' => true]);
        break;

        // ❌ DELETE
        case 'delete':
            $id = $_GET['id'] ?? 0;
            $stmt = $conn->prepare("DELETE FROM trilha WHERE id_trilha = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            echo json_encode(['success' => true]);
        break;
    }

    $conn->close();

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

ob_end_flush();