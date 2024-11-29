<?php
require 'vendor/autoload.php'; // Autoload do Composer para o Dotenv
Dotenv\Dotenv::createImmutable(__DIR__)->load();

header('Content-Type: application/json');

// Permitir requisições CORS, se necessário
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Classe para gerenciar médicos
class MedicosAPI {
    private $conexao;

    // Construtor: configura a conexão com o banco de dados
    public function __construct($baseUrl = "https://web-production-2a8d.up.railway.app/") {
        $dbHost = $_ENV['DB_HOST'];
        $dbName = $_ENV['DB_NAME'];
        $dbUser = $_ENV['DB_USER'];
        $dbPass = $_ENV['DB_PASS'];
        $dbPort = $_ENV['DB_PORT'];

        // Configura a URL base
        $this->baseUrl = $baseUrl;

        // Conexão com o banco de dados
        $this->conexao = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName, $dbPort);

        if (!$this->conexao) {
            die(json_encode(['success' => false, 'message' => 'Erro na conexão com o banco de dados: ' . mysqli_connect_error()]));
        }
    }

    // Função para enviar respostas padronizadas
    private function sendResponse($success, $message, $data = null) {
        echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
        exit;
    }

    // Listar médicos
    public function listMedicos() {
        $sql = "SELECT * FROM medicos";
        $result = mysqli_query($this->conexao, $sql);

        if ($result) {
            $medicos = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $medicos[] = $row;
            }
            $this->sendResponse(true, 'Lista de médicos obtida com sucesso', $medicos);
        } else {
            $this->sendResponse(false, 'Erro ao obter a lista de médicos');
        }
    }

    // Criar médico
    public function createMedico($data) {
        $email = mysqli_real_escape_string($this->conexao, trim($data['email']));
        $data_nascimento = mysqli_real_escape_string($this->conexao, trim($data['data_nascimento']));
        $senha = isset($data['senha']) ? password_hash(trim($data['senha']), PASSWORD_DEFAULT) : '';

        $sql = "INSERT INTO medicos (email, data_nascimento, senha) VALUES ('$email', '$data_nascimento', '$senha')";
        if (mysqli_query($this->conexao, $sql)) {
            $this->sendResponse(true, 'Médico criado com sucesso', ['id' => mysqli_insert_id($this->conexao)]);
        } else {
            $this->sendResponse(false, 'Erro ao criar o médico');
        }
    }

    // Atualizar médico
    public function updateMedico($data) {
        $medicos_id = mysqli_real_escape_string($this->conexao, $data['medicos_id']);
        
        $email = mysqli_real_escape_string($this->conexao, trim($data['email']));
        $data_nascimento = mysqli_real_escape_string($this->conexao, trim($data['data_nascimento']));
        $senha = trim($data['senha']);

        $sql = "UPDATE medicos SET email = '$email', data_nascimento = '$data_nascimento'";
        if (!empty($senha)) {
            $hashedSenha = password_hash($senha, PASSWORD_DEFAULT);
            $sql .= ", senha='$hashedSenha'";
        }
        $sql .= " WHERE id = '$medicos_id'";

        if (mysqli_query($this->conexao, $sql) && mysqli_affected_rows($this->conexao) > 0) {
            $this->sendResponse(true, 'Médico atualizado com sucesso');
        } else {
            $this->sendResponse(false, 'Nenhuma alteração realizada ou erro ao atualizar');
        }
    }

    // Excluir médico
    public function deleteMedico($data) {
        $medicos_id = mysqli_real_escape_string($this->conexao, $data['medicos_id']);
        $sql = "DELETE FROM medicos WHERE id = '$medicos_id'";

        if (mysqli_query($this->conexao, $sql) && mysqli_affected_rows($this->conexao) > 0) {
            $this->sendResponse(true, 'Médico deletado com sucesso');
        } else {
            $this->sendResponse(false, 'Erro ao deletar o médico ou registro não encontrado');
        }
    }
}

// Inicializa a API
$api = new MedicosAPI(); // Não precisa passar baseUrl, pois já está configurado no construtor

// Lida com as requisições
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

if ($method === 'GET') {
    $api->listMedicos();
} elseif ($method === 'POST' && isset($data['action'])) {
    switch ($data['action']) {
        case 'create_medicos':
            $api->createMedico($data);
            break;
        case 'update_medicos':
            $api->updateMedico($data);
            break;
        case 'delete_medicos':
            $api->deleteMedico($data);
            break;
        default:
            $api->sendResponse(false, 'Ação não reconhecida');
    }
} else {
    $api->sendResponse(false, 'Método HTTP não suportado');
}
