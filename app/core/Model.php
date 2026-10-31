<?php
/**
 * ============================================================
 *  Model.php — Base model
 *  Every model (User, Subject, Question...) extends this and
 *  automatically gets the database connection as $this->db
 * ============================================================
 */

class Model
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }
}