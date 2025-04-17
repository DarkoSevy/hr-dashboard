<?php
require_once 'Database.php';

class Task {
    private $conn;
    private $table_name = "tasks";

    public $id;
    public $title;
    public $description;
    public $status;
    public $priority;
    public $due_date;
    public $assignee_id;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                (title, description, status, priority, due_date, assignee_id)
                VALUES
                (:title, :description, :status, :priority, :due_date, :assignee_id)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":priority", $this->priority);
        $stmt->bindParam(":due_date", $this->due_date);
        $stmt->bindParam(":assignee_id", $this->assignee_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function read() {
        $query = "SELECT t.*, e.name as assignee_name 
                 FROM " . $this->table_name . " t
                 LEFT JOIN employees e ON t.assignee_id = e.id
                 ORDER BY t.due_date ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readByStatus($status) {
        $query = "SELECT t.*, e.name as assignee_name 
                 FROM " . $this->table_name . " t
                 LEFT JOIN employees e ON t.assignee_id = e.id
                 WHERE t.status = :status
                 ORDER BY t.due_date ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->execute();
        return $stmt;
    }

    public function readByAssignee($assignee_id) {
        $query = "SELECT t.*, e.name as assignee_name 
                 FROM " . $this->table_name . " t
                 LEFT JOIN employees e ON t.assignee_id = e.id
                 WHERE t.assignee_id = :assignee_id
                 ORDER BY t.due_date ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":assignee_id", $assignee_id);
        $stmt->execute();
        return $stmt;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET
                    title = :title,
                    description = :description,
                    status = :status,
                    priority = :priority,
                    due_date = :due_date,
                    assignee_id = :assignee_id
                WHERE
                    id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":priority", $this->priority);
        $stmt->bindParam(":due_date", $this->due_date);
        $stmt->bindParam(":assignee_id", $this->assignee_id);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getTaskStats() {
        $query = "SELECT 
                    COUNT(*) as total_tasks,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_tasks,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_tasks,
                    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_tasks,
                    SUM(CASE WHEN priority = 'high' THEN 1 ELSE 0 END) as high_priority_tasks,
                    SUM(CASE WHEN priority = 'medium' THEN 1 ELSE 0 END) as medium_priority_tasks,
                    SUM(CASE WHEN priority = 'low' THEN 1 ELSE 0 END) as low_priority_tasks
                 FROM " . $this->table_name;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getTotalCount() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function getCompletedCount() {
        $query = "SELECT COUNT(*) as completed FROM " . $this->table_name . " WHERE status = 'completed'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['completed'];
    }

    public function getPendingCount() {
        $query = "SELECT COUNT(*) as pending FROM " . $this->table_name . " WHERE status = 'pending'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['pending'];
    }

    public function getOverdueCount() {
        $query = "SELECT COUNT(*) as overdue FROM " . $this->table_name . " 
                 WHERE status != 'completed' AND due_date < CURDATE()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['overdue'];
    }

    public function getStats() {
        return [
            'total' => $this->getTotalCount(),
            'completed' => $this->getCompletedCount(),
            'pending' => $this->getPendingCount(),
            'overdue' => $this->getOverdueCount()
        ];
    }
}
?> 