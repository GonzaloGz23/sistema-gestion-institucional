<?php

class IdEncoder {
    // ⚠️ Cambia este salt por algo único de tu proyecto
    private const SALT = 'x7kP9mQ2vL8nR5wE3tY6uI1oA4sD7fG0hJ9kL2mN5pQ8rT1uV4wX7zB0cE3fH6jK9mP2sU5vY8aC1dF4gI7lO0qR3tW6yZ9bE2hK5nQ8sV1xA4cF7iL0oR3uX6zA9dG2jM5pS8vB1eH4kN7qT0wY3bF6iO9rU2xE5hL8mP1sV4yC7fJ0qN3tW6zA9cG2kP5sX8uB1eI4mQ7vY0cF3hL6oR9tW2zA5dH8kN1qU4xB7eJ0mP3sV6yC9fL2iO5rT8wA1dG4hK7nQ0sU3vY6bE9cF2jM5pX8zA1eI4lO7qT0wV3yC6fH9kN2rU5sB8dG1jP4mQ7vY0cL3iO6tW9zA2eH5kN8qU1xB4fJ7mP0sV3yC6gI9lR2tW5zA8dE1hK4nQ7uX0bF3jM6pS9vA2cH5iL8oR1tU4wY7zB0eG3fJ6kN9mP2qS5vX8aC1dH4iL7oR0tU3wY6zB9eF2gJ5kN8mP1qS4vX7aC0dH3iL6oR9tU2wY5zB8e';
    
    public static function encode($id) {
        if (empty($id) || !is_numeric($id)) {
            return null;
        }
        
        // Crear checksum con el salt
        $checksum = substr(md5($id . self::SALT), 0, 8);
        
        // Combinar ID + checksum
        $combined = $id . '|' . $checksum;
        
        // Codificar en base64 y hacer URL-safe
        $encoded = base64_encode($combined);
        $encoded = str_replace(['+', '/', '='], ['-', '_', ''], $encoded);
        
        return $encoded;
    }
    
    /**
     * Decodifica un string a ID numérico
     * Valida que no haya sido manipulado
     */
    public static function decode($encoded) {
        if (empty($encoded)) {
            return null;
        }
        
        try {
            // Revertir URL-safe
            $encoded = str_replace(['-', '_'], ['+', '/'], $encoded);
            
            // Decodificar base64
            $decoded = base64_decode($encoded, true);
            
            if ($decoded === false) {
                return null;
            }
            
            // Separar ID y checksum
            $parts = explode('|', $decoded);
            
            if (count($parts) !== 2) {
                return null;
            }
            
            list($id, $checksum) = $parts;
            
            // Validar que el ID sea numérico
            if (!is_numeric($id)) {
                return null;
            }
            
            // Verificar checksum (detecta manipulación)
            $expectedChecksum = substr(md5($id . self::SALT), 0, 8);
            
            if ($checksum !== $expectedChecksum) {
                return null; // URL manipulada
            }
            
            return (int)$id;
            
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Codifica múltiples IDs
     */
    public static function encodeMultiple(array $ids) {
        $encoded = [];
        foreach ($ids as $key => $id) {
            $encoded[$key] = self::encode($id);
        }
        return $encoded;
    }
    
    /**
     * Decodifica múltiples IDs
     */
    public static function decodeMultiple(array $encoded) {
        $decoded = [];
        foreach ($encoded as $key => $value) {
            $decoded[$key] = self::decode($value);
        }
        return $decoded;
    }
}
?>