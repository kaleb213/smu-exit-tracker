<?php
/**
 * ============================================================
 *  User.php — User model
 *  Authentication lookups + student account creation.
 *  All queries use prepared statements (via Database).
 * ============================================================
 */

class User extends Model
{
    // Find one user by their Student/Staff ID number
    public function findByIdNumber($idNumber)
    {
        return $this->db->fetch(
            'SELECT * FROM users WHERE id_number = ? LIMIT 1',
            [$idNumber]
        );
    }

    // Check whether an ID number is already registered (PDF: reject duplicates)
    public function idNumberExists($idNumber)
    {
        return $this->db->fetch(
            'SELECT id FROM users WHERE id_number = ? LIMIT 1',
            [$idNumber]
        ) !== false;
    }

    // Create a new student account (public self-registration)
    // $passwordHash must come from password_hash() — never store plain text
    public function createStudent($fullName, $idNumber, $passwordHash)
    {
        $this->db->query(
            'INSERT INTO users (full_name, id_number, password_hash, role, created_at)
             VALUES (?, ?, ?, ?, NOW())',
            [$fullName, $idNumber, $passwordHash, ROLE_STUDENT]
        );
        return $this->db->lastInsertId();
    }
}