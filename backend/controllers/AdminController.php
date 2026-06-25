<?php
// backend/controllers/AdminController.php

class AdminController {
    private $userModel;
    private $reportModel;

    public function __construct() {
        $this->userModel = new User();
        $this->reportModel = new ReportModel();
    }

    /**
     * Valide que l'utilisateur connecté est administrateur
     */
    private function checkAdmin() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(["error" => "Non autorisé. Veuillez vous connecter."]);
            exit();
        }

        $userId = $_SESSION['user_id'];
        $user = $this->userModel->getUserById($userId);
        
        // On récupère aussi la colonne is_admin qui n'est pas forcément retournée dans getUserById standard.
        // Faisons une vérification rapide en base
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT is_admin FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $userId]);
        $row = $stmt->fetch();

        if (!$row || $row['is_admin'] != 1) {
            http_response_code(403);
            echo json_encode(["error" => "Accès refusé. Réservé aux administrateurs."]);
            exit();
        }
    }

    /**
     * Récupère les statistiques de la plateforme
     */
    public function getStats() {
        $this->checkAdmin();

        try {
            $db = Database::getInstance();
            
            // Total utilisateurs
            $totalUsers = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
            
            // Premium utilisateurs
            $premiumUsers = $db->query("SELECT COUNT(*) FROM users WHERE is_premium = 1")->fetchColumn();
            
            // Nombre de signalements en attente
            $pendingReports = $db->query("SELECT COUNT(*) FROM reports WHERE status = 'pending'")->fetchColumn();

            // Revenu estimé (9.99€ par mois par utilisateur premium)
            $estimatedRevenue = $premiumUsers * 9.99;

            http_response_code(200);
            echo json_encode([
                "stats" => [
                    "total_users" => $totalUsers,
                    "premium_users" => $premiumUsers,
                    "pending_reports" => $pendingReports,
                    "revenue" => number_format($estimatedRevenue, 2, '.', '')
                ]
            ]);
        } catch (Exception $e) {
            Logger::log("Erreur statistiques admin : " . $e->getMessage(), "ERROR");
            http_response_code(500);
            echo json_encode(["error" => "Erreur serveur."]);
        }
    }

    /**
     * Récupère la liste complète des utilisateurs
     */
    public function getUsersList() {
        $this->checkAdmin();

        try {
            $users = $this->userModel->getAllUsers();
            http_response_code(200);
            echo json_encode(["users" => $users]);
        } catch (Exception $e) {
            Logger::log("Erreur chargement utilisateurs admin : " . $e->getMessage(), "ERROR");
            http_response_code(500);
            echo json_encode(["error" => "Erreur serveur."]);
        }
    }

    /**
     * Modifie le profil d'un utilisateur (Bannissement/Débannissement, Premium, Suppression)
     */
    public function updateUserInfo() {
        $this->checkAdmin();

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (empty($data['user_id']) || empty($data['action_type'])) {
            http_response_code(400);
            echo json_encode(["error" => "Données obligatoires manquantes."]);
            return;
        }

        $targetUserId = intval($data['user_id']);
        $actionType = $data['action_type']; // 'ban', 'unban', 'toggle_premium', 'delete'

        try {
            if ($actionType === 'delete') {
                if ($this->userModel->deleteUser($targetUserId)) {
                    Logger::log("Admin a supprimé l'utilisateur ID $targetUserId", "INFO");
                    http_response_code(200);
                    echo json_encode(["message" => "Utilisateur supprimé avec succès."]);
                } else {
                    http_response_code(500);
                    echo json_encode(["error" => "Impossible de supprimer l'utilisateur."]);
                }
                return;
            }

            if ($actionType === 'ban') {
                if ($this->userModel->updateStatus($targetUserId, 0)) {
                    Logger::log("Admin a banni l'utilisateur ID $targetUserId", "WARN");
                    http_response_code(200);
                    echo json_encode(["message" => "Compte utilisateur désactivé."]);
                } else {
                    http_response_code(500);
                    echo json_encode(["error" => "Impossible de bannir l'utilisateur."]);
                }
                return;
            }

            if ($actionType === 'unban') {
                if ($this->userModel->updateStatus($targetUserId, 1)) {
                    Logger::log("Admin a réactivé l'utilisateur ID $targetUserId", "INFO");
                    http_response_code(200);
                    echo json_encode(["message" => "Compte utilisateur réactivé."]);
                } else {
                    http_response_code(500);
                    echo json_encode(["error" => "Impossible d'activer l'utilisateur."]);
                }
                return;
            }

            if ($actionType === 'toggle_premium') {
                $db = Database::getInstance();
                $stmt = $db->prepare("SELECT is_premium FROM users WHERE id = :id LIMIT 1");
                $stmt->execute([':id' => $targetUserId]);
                $curr = $stmt->fetch();
                if ($curr) {
                    $newPremium = $curr['is_premium'] == 1 ? 0 : 1;
                    if ($this->userModel->updatePremium($targetUserId, $newPremium)) {
                        Logger::log("Admin a modifié le statut premium de l'utilisateur ID $targetUserId en " . ($newPremium ? "Premium" : "Standard"), "INFO");
                        http_response_code(200);
                        echo json_encode(["message" => "Statut premium mis à jour.", "is_premium" => $newPremium]);
                    } else {
                        http_response_code(500);
                        echo json_encode(["error" => "Impossible de modifier le statut premium."]);
                    }
                } else {
                    http_response_code(404);
                    echo json_encode(["error" => "Utilisateur introuvable."]);
                }
                return;
            }

            http_response_code(400);
            echo json_encode(["error" => "Action non supportée."]);
        } catch (Exception $e) {
            Logger::log("Erreur modification utilisateur admin : " . $e->getMessage(), "ERROR");
            http_response_code(500);
            echo json_encode(["error" => "Erreur interne serveur."]);
        }
    }

    /**
     * Récupère la liste complète des signalements
     */
    public function getReportsList() {
        $this->checkAdmin();

        try {
            $reports = $this->reportModel->getAllReports();
            http_response_code(200);
            echo json_encode(["reports" => $reports]);
        } catch (Exception $e) {
            Logger::log("Erreur chargement signalements admin : " . $e->getMessage(), "ERROR");
            http_response_code(500);
            echo json_encode(["error" => "Erreur serveur."]);
        }
    }

    /**
     * Traite un signalement (Bannissement de l'utilisateur signalé, archivage du signalement, etc.)
     */
    public function updateReportInfo() {
        $this->checkAdmin();

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (empty($data['report_id']) || empty($data['action_type'])) {
            http_response_code(400);
            echo json_encode(["error" => "Données obligatoires manquantes."]);
            return;
        }

        $reportId = intval($data['report_id']);
        $actionType = $data['action_type']; // 'resolve', 'dismiss', 'ban_reported'

        try {
            if ($actionType === 'dismiss') {
                if ($this->reportModel->updateReportStatus($reportId, 'dismissed')) {
                    Logger::log("Admin a rejeté le signalement ID $reportId", "INFO");
                    http_response_code(200);
                    echo json_encode(["message" => "Signalement classé sans suite."]);
                } else {
                    http_response_code(500);
                    echo json_encode(["error" => "Impossible de classer le signalement."]);
                }
                return;
            }

            if ($actionType === 'resolve') {
                if ($this->reportModel->updateReportStatus($reportId, 'resolved')) {
                    Logger::log("Admin a résolu le signalement ID $reportId", "INFO");
                    http_response_code(200);
                    echo json_encode(["message" => "Signalement résolu avec succès."]);
                } else {
                    http_response_code(500);
                    echo json_encode(["error" => "Impossible de résoudre le signalement."]);
                }
                return;
            }

            if ($actionType === 'ban_reported') {
                $db = Database::getInstance();
                // Retrouver l'ID de l'utilisateur signalé
                $stmt = $db->prepare("SELECT reported_id FROM reports WHERE id = :id LIMIT 1");
                $stmt->execute([':id' => $reportId]);
                $row = $stmt->fetch();
                if ($row) {
                    $reportedUserId = $row['reported_id'];
                    $this->userModel->updateStatus($reportedUserId, 0); // Bannissement
                    $this->reportModel->updateReportStatus($reportId, 'resolved'); // Résolution
                    Logger::log("Admin a banni l'utilisateur signalé ID $reportedUserId via le signalement ID $reportId", "WARN");
                    http_response_code(200);
                    echo json_encode(["message" => "Utilisateur signalé banni et signalement résolu."]);
                } else {
                    http_response_code(404);
                    echo json_encode(["error" => "Signalement introuvable."]);
                }
                return;
            }

            http_response_code(400);
            echo json_encode(["error" => "Action non supportée."]);
        } catch (Exception $e) {
            Logger::log("Erreur modification signalement admin : " . $e->getMessage(), "ERROR");
            http_response_code(500);
            echo json_encode(["error" => "Erreur interne serveur."]);
        }
    }
}
