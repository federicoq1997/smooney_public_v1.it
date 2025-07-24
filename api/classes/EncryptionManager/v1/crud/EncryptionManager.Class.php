<?php
class EncryptionManager {
	private static $masterKey;

	// Metodo per inizializzare l'ambiente
	public function __construct() {
		$config = self::loadConfig(__DIR__ . '/../../assets/config.php');
		self::$masterKey = $config['masterKey'];
	}

	// Metodo privato per caricare il file di configurazione
	private static function loadConfig($fileName) {
			if (file_exists($fileName)) {
					return include $fileName; // Include il file di configurazione e lo restituisce
			} else {
					throw new Exception("File di configurazione non trovato");
			}
	}

	// Genera una User Key univoca per un utente (256-bit)
	public static function generateUserKey(): string {
			return bin2hex(random_bytes(32));
	}

	// Crittografa la User Key usando la Master Key e restituisce il risultato
	public static function encryptUserKey(string $userKey): string {
			$masterKey = self::$masterKey;
			$iv = random_bytes(16); // Vettore di inizializzazione per AES
			$encryptedKey = openssl_encrypt($userKey, 'aes-256-cbc', $masterKey, OPENSSL_RAW_DATA, $iv);
			return base64_encode($iv . $encryptedKey); // Restituisce IV concatenato alla chiave crittografata
	}

	// Decrittografa la User Key usando la Master Key
	public static function decryptUserKey(string $encryptedUserKey): string {
			$masterKey = self::$masterKey;
			$data = base64_decode($encryptedUserKey);
			$iv = substr($data, 0, 16);
			$encryptedKey = substr($data, 16);
			return openssl_decrypt($encryptedKey, 'aes-256-cbc', $masterKey, OPENSSL_RAW_DATA, $iv);
	}

	// Crittografa i dati utente utilizzando la User Key
	public static function encryptData(string $plaintext, string $userKey): string {
			$iv = random_bytes(16); // Vettore di inizializzazione per AES
			$ciphertext = openssl_encrypt($plaintext, 'aes-256-cbc', $userKey, OPENSSL_RAW_DATA, $iv);
			return base64_encode($iv . $ciphertext); // Restituisce IV concatenato ai dati crittografati
	}

	// Decrittografa i dati utente utilizzando la User Key
	public static function decryptData(string $encryptedData, string $userKey): string {
		try {
			$data = base64_decode($encryptedData);
			$iv = substr($data, 0, 16);
			if (strlen($iv) !== 16) {
				throw new Exception("L'IV deve essere lungo esattamente 16 byte");
			}
			$ciphertext = substr($data, 16);
			$decrypted = openssl_decrypt($ciphertext, 'aes-256-cbc', $userKey, OPENSSL_RAW_DATA, $iv);
			if ($decrypted === false) {
				throw new Exception("Errore nella decrittazione dei dati");
			}
			return $decrypted;
		}catch (Exception $e) {
			return $encryptedData;
		}
	}
}
/**
 * Crittografare i Dati Sensibili di un Utente:
 * 
 * $userKey = EncryptionManager::decryptUserKey($encryptedUserKey); // Recupera e decrittografa la User Key
 * $plaintext = "Dati sensibili dell'utente";
 * $encryptedData = EncryptionManager::encryptData($plaintext, $userKey);
 */
/**
 * Decrittografare i Dati Sensibili di un Utente:
 * 
 * $userKey = EncryptionManager::decryptUserKey($encryptedUserKey); // Recupera e decrittografa la User Key
 * $decryptedData = EncryptionManager::decryptData($encryptedData, $userKey);
 * echo "Dati decrittografati: " . $decryptedData;
 */