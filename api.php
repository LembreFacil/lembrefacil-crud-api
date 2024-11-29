<?php
require 'conexao.php';
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");  // Permitir qualquer origem (modifique para produção)
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Função para enviar respostas padronizadas
function sendResponse($success, $message, $data = null) {
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit;
}

// Verifica o método da requisição
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Listagem de médicos
    $sql = "SELECT * FROM medicos";
    $result = mysqli_query($conexao, $sql);

    if ($result) {
        $medicos = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $medicos[] = $row;
        }
        sendResponse(true, 'Lista de médicos obtida com sucesso', $medicos);
    } else {
        sendResponse(false, 'Erro ao obter a lista de médicos');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Criação de médicos
    if (isset($_POST['create_medicos'])) {
        $nome = mysqli_real_escape_string($conexao, trim($_POST['nome']));
        $email = mysqli_real_escape_string($conexao, trim($_POST['email']));
        $data_nascimento = mysqli_real_escape_string($conexao, trim($_POST['data_nascimento']));
        $senha = isset($_POST['senha']) ? password_hash(trim($_POST['senha']), PASSWORD_DEFAULT) : '';

        $sql = "INSERT INTO medicos (nome, email, data_nascimento, senha) VALUES ('$nome', '$email', '$data_nascimento', '$senha')";
        if (mysqli_query($conexao, $sql)) {
            sendResponse(true, 'Médico criado com sucesso', ['id' => mysqli_insert_id($conexao)]);
        } else {
            sendResponse(false, 'Erro ao criar o médico');
        }
    }

    // Atualização de médicos
    if (isset($_POST['update_medicos'])) {
        $medicos_id = mysqli_real_escape_string($conexao, $_POST['medicos_id']);
        $nome = mysqli_real_escape_string($conexao, trim($_POST['nome']));
        $email = mysqli_real_escape_string($conexao, trim($_POST['email']));
        $data_nascimento = mysqli_real_escape_string($conexao, trim($_POST['data_nascimento']));
        $senha = trim($_POST['senha']);

        $sql = "UPDATE medicos SET nome = '$nome', email = '$email', data_nascimento = '$data_nascimento'";
        if (!empty($senha)) {
            $hashedSenha = password_hash($senha, PASSWORD_DEFAULT);
            $sql .= ", senha='$hashedSenha'";
        }
        $sql .= " WHERE id = '$medicos_id'";

        if (mysqli_query($conexao, $sql) && mysqli_affected_rows($conexao) > 0) {
            sendResponse(true, 'Médico atualizado com sucesso');
        } else {
            sendResponse(false, 'Nenhuma alteração realizada ou erro ao atualizar');
        }
    }

    // Exclusão de médicos
    if (isset($_POST['delete_medicos'])) {
        $medicos_id = mysqli_real_escape_string($conexao, $_POST['delete_medicos']);
        $sql = "DELETE FROM medicos WHERE id = '$medicos_id'";

        if (mysqli_query($conexao, $sql) && mysqli_affected_rows($conexao) > 0) {
            sendResponse(true, 'Médico deletado com sucesso');
        } else {
            sendResponse(false, 'Erro ao deletar o médico ou registro não encontrado');
        }
    }
}

// Resposta padrão caso nenhuma ação seja executada
sendResponse(false, 'Nenhuma ação foi executada');
