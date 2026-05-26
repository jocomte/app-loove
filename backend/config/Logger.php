<?php


class Logger {
    private static $logFile = __DIR__ . '/../logs/app.log';

    /**
     * Enregistre un message dans le fichier de log
     * @param string $message Le texte à enregistrer
     * @param string $level Le niveau d'importance (INFO, WARN, ERROR, SECURITY)
     */
    public static function log($message, $level = 'INFO') {
        // Crée le dossier logs s'il n'existe pas encore
        $logDir = dirname(self::$logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        $date = date('Y-m-d H:i:s');
        
        // Structure de la ligne : [DATE] [NIVEAU] Message + Retour à la ligne
        $logLine = "[$date] [$level] $message" . PHP_EOL;

        // Écrit à la fin du fichier sans effacer le contenu existant
        file_put_contents(self::$logFile, $logLine, FILE_APPEND);
    }
}