<?php
require 'conexao.php';
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");  // Permitir qualquer origem (modifique para produção)
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Permitir requisições CORS, se necessário
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Função para enviar respostas padronizadas
function sendResponse($success, $message, $data = null) {
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit;
}

// Verifica o método da requisição
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
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
} elseif ($method === 'POST') {
    // Obtém os dados da requisição
    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['action'])) {
        $action = $input['action'];

        // Criação de médicos
        if ($action === 'create_medicos') {
            $email = mysqli_real_escape_string($conexao, trim($input['email']));
            $data_nascimento = mysqli_real_escape_string($conexao, trim($input['data_nascimento']));
            $senha = isset($input['senha']) ? password_hash(trim($input['senha']), PASSWORD_DEFAULT) : '';

            $sql = "INSERT INTO medicos (email, data_nascimento, senha) VALUES ('$nome', '$email', '$data_nascimento', '$senha')";
            if (mysqli_query($conexao, $sql)) {
                sendResponse(true, 'Médico criado com sucesso', ['id' => mysqli_insert_id($conexao)]);
            } else {
                sendResponse(false, 'Erro ao criar o médico');
            }
        }

        // Atualização de médicos
        elseif ($action === 'update_medicos') {
            $medicos_id = mysqli_real_escape_string($conexao, $input['medicos_id']);
            $email = mysqli_real_escape_string($conexao, trim($input['email']));
            $data_nascimento = mysqli_real_escape_string($conexao, trim($input['data_nascimento']));
            $senha = trim($input['senha']);

            $sql = "UPDATE medicos SET email = '$email', data_nascimento = '$data_nascimento'";
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
        elseif ($action === 'delete_medicos') {
            $medicos_id = mysqli_real_escape_string($conexao, $input['medicos_id']);
            $sql = "DELETE FROM medicos WHERE id = '$medicos_id'";

            if (mysqli_query($conexao, $sql) && mysqli_affected_rows($conexao) > 0) {
                sendResponse(true, 'Médico deletado com sucesso');
            } else {
                sendResponse(false, 'Erro ao deletar o médico ou registro não encontrado');
            }
        } else {
            sendResponse(false, 'Ação não reconhecida');
        }
    } else {
        sendResponse(false, 'Nenhuma ação foi especificada');
    }
} else {
    sendResponse(false, 'Método HTTP não suportado');
}
