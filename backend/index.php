<?php
// Enregistrement d'un gestionnaire d'exceptions global
set_exception_handler(function ($exception) {
    // Log l'erreur fatale avec le message, le fichier et la ligne du crash
    Logger::log("Erreur non capturée : " . $exception->getMessage() . " dans " . $exception->getFile() . " à la ligne " . $exception->getLine(), "ERROR");
    
    http_response_code(500);
    echo json_encode(["error" => "Une erreur interne du serveur est survenue."]);
    exit();
});

// 1. Gestion des en-têtes CORS (indispensable pour que ton JavaScript puisse interroger l'API)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Si c'est une requête de vérification OPTIONS (CORS), on arrête là
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 2. Inclusion automatique des classes (Autoload basique)
spl_autoload_register(function ($className) {
    if (file_exists(__DIR__ . '/config/' . $className . '.php')) {
        require_once __DIR__ . '/config/' . $className . '.php';
    } elseif (file_exists(__DIR__ . '/controllers/' . $className . '.php')) {
        require_once __DIR__ . '/controllers/' . $className . '.php';
    } elseif (file_exists(__DIR__ . '/models/' . $className . '.php')) {
        require_once __DIR__ . '/models/' . $className . '.php';
    }
});

// 3. Récupération et nettoyage de la route
// Exemple d'URL attendue : backend/index.php?action=users
$action = isset($_GET['action']) ? $_GET['action'] : ''; //  En majuscules !
$method = $_SERVER['REQUEST_METHOD'];

// 4. Système de Routage (Aiguillage)
switch ($action) {


    case 'register':
        if ($method === 'POST') {
            $userController = new UserController();
            $userController->register();
        } else {
            http_response_code(405);
            echo json_encode(["error" => "Méthode non autorisée"]);
        }
        break;

    case 'login':
        if ($method === 'POST') {
            $userController = new UserController();
            $userController->login();
        } else {
            http_response_code(405);
            echo json_encode(["error" => "Méthode non autorisée"]);
        }
        break;

    case 'next-profile':
        if ($method === 'GET') {
            $matchController = new MatchController();
            $matchController->getNextProfile();
        } else {
            http_response_code(405);
            echo json_encode(["error" => "Méthode non autorisée"]);
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(["error" => "Route non trouvée"]);
        break;
}