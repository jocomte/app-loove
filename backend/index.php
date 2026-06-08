<?php
// backend/index.php
session_start(); // Toujours en ligne 2 !
// Enregistrement d'un gestionnaire d'exceptions global
set_exception_handler(function ($exception) {
    // Log l'erreur fatale avec le message, le fichier et la ligne du crash
    Logger::log("Erreur non capturée : " . $exception->getMessage() . " dans " . $exception->getFile() . " à la ligne " . $exception->getLine(), "ERROR");
    
    http_response_code(500);
    echo json_encode(["error" => "Une erreur interne du serveur est survenue."]);
    exit();
});



// Configure les en-têtes CORS pour autoriser les cookies de session
$allowedOrigins = [
    'http://localhost',
    'http://127.0.0.1',
    'http://10.45.31.100'
];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    header("Access-Control-Allow-Origin: http://10.45.31.100");
}
header("Access-Control-Allow-Credentials: true"); // 🚀 INDISPENSABLE pour les sessions !
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// Si c'est une requête de pré-vérification (OPTIONS), on s'arrête là
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
    case 'get-profile':
            if ($method === 'GET') {
                $userController = new UserController();
                $userController->getProfile();
            } else {
                http_response_code(405);
                echo json_encode(["error" => "Méthode non autorisée"]);
            }
            break;

        case 'update-profile':
            if ($method === 'POST') { // Utilisation de POST simulé pour simplifier le Vanilla PHP sans parser de PUT
                $userController = new UserController();
                $userController->editProfile();
            } else {
                http_response_code(405);
                echo json_encode(["error" => "Méthode non autorisée"]);
            }
            break;
        case 'update-location':
            if ($method === 'POST') {
                $userController = new UserController();
                $userController->updateLocation();
            } else {
                http_response_code(405);
                echo json_encode(["error" => "Méthode non autorisée"]);
            }
            break;
    case 'interaction':
        if ($method === 'POST') {
            $matchController = new MatchController();
            $matchController->handleInteraction();
        } else {
            http_response_code(405);
            echo json_encode(["error" => "Méthode non autorisée"]);
        }
        break;
    case 'upload-photo':
        if ($method === 'POST') {
            $photoController = new PhotoController();
            $photoController->uploadPhoto();
        } else {
            http_response_code(405);
            echo json_encode(["error" => "Méthode non autorisée"]);
        }
        break;
    case 'user-photos':
        if ($method === 'GET') {
            $photoController = new PhotoController();
            $photoController->listPhotos();
        } else {
            http_response_code(405);
            echo json_encode(["error" => "Méthode non autorisée"]);
        }
        break;
    case 'send-message':
        if ($method === 'POST') {
            $messageController = new MessageController();
            $messageController->sendMessage();
        } else {
            http_response_code(405);
            echo json_encode(["error" => "Méthode non autorisée"]);
        }
        break;
    case 'inbox':
        if ($method === 'GET') {
            $messageController = new MessageController();
            $messageController->getInbox();
        } else {
            http_response_code(405);
            echo json_encode(["error" => "Méthode non autorisée"]);
        }
        break;
    case 'conversation':
        if ($method === 'GET') {
            $messageController = new MessageController();
            $messageController->getConversation();
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