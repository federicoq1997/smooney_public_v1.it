<?php

class EnvironmentType {
    // Variabile statica per contenere la configurazione
    private static $config;
    private static $configDatabase;

    // Metodo per inizializzare l'ambiente
    public static function init() {
        self::$config = self::loadConfig(__DIR__ . '/config.php');
        self::$configDatabase = self::$config['database'];
        unset(self::$config['database']);
    }

    // Metodo privato per caricare il file di configurazione
    private static function loadConfig($fileName) {
        if (file_exists($fileName)) {
            return include $fileName; // Include il file di configurazione e lo restituisce
        } else {
            throw new Exception("File di configurazione non trovato");
        }
    }

    // Metodo per ottenere i valori di configurazione
    public static function getConfig() {
        if (self::$config) return self::$config;
        return null;
    }

    public static function getConfigDatabase(){
        if (self::$configDatabase) return self::$configDatabase;
        return null;
    }
    
}

?>