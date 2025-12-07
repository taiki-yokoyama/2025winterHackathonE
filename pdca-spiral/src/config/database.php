<?php

class Database {
    private static ?PDO $connection = null;
    
    /**
     * Get database connection
     * 
     * @return PDO
     * @throws PDOException
     */
    public static function getConnection(): PDO {
        if (self::$connection === null) {
            try {
                $host = getenv('DB_HOST') ?: 'db';
                $dbname = getenv('DB_NAME') ?: 'pdca_spiral';
                $username = getenv('DB_USER') ?: 'root';
                $password = getenv('DB_PASSWORD') ?: 'root';
                
                $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
                
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];
                
                self::$connection = new PDO($dsn, $username, $password, $options);
            } catch (PDOException $e) {
                error_log("Database connection failed: " . $e->getMessage());
                throw new PDOException("データベース接続に失敗しました。");
            }
        }
        
        return self::$connection;
    }
    
    /**
     * Close database connection
     */
    public static function closeConnection(): void {
        self::$connection = null;
    }
    
    /**
     * Begin transaction
     */
    public static function beginTransaction(): bool {
        return self::getConnection()->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public static function commit(): bool {
        return self::getConnection()->commit();
    }
    
    /**
     * Rollback transaction
     */
    public static function rollback(): bool {
        return self::getConnection()->rollBack();
    }
}
