<?php

namespace App\Repositories;

use App\Interfaces\EmployeeRepositoryInterface;
use PDO;
use PDOException;
use App\Exceptions\DatabaseException;

class EmployeeRepository implements EmployeeRepositoryInterface
{
    private const TABLE = 'employees';

    public function __construct(private PDO $conn) {}

    public function getAll(int $page = 1, int $limit = 10): array
    {
        try {
            $offset = ($page - 1) * $limit;
            
            // Get total count
            $totalStmt = $this->conn->query("SELECT COUNT(*) FROM " . self::TABLE . " WHERE deleted_at IS NULL");
            $total = (int) $totalStmt->fetchColumn();
            
            // Get paginated data
            $query = "SELECT * FROM " . self::TABLE . " 
                     WHERE deleted_at IS NULL 
                     ORDER BY created_at DESC 
                     LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return [
                'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
                'total' => $total,
                'page' => $page,
                'last_page' => ceil($total / $limit)
            ];
        } catch (PDOException $e) {
            throw new DatabaseException('Failed to fetch employees: ' . $e->getMessage());
        }
    }

    public function getById(int $id): ?array
    {
        try {
            $query = "SELECT * FROM " . self::TABLE . " 
                     WHERE id = :id AND deleted_at IS NULL";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            throw new DatabaseException('Failed to fetch employee: ' . $e->getMessage());
        }
    }

    public function create(array $data): int
    {
        try {
            $this->conn->beginTransaction();

            $query = "INSERT INTO " . self::TABLE . " 
                    (name, email, position, department, start_date, salary, status, third_party_info) 
                    VALUES (:name, :email, :position, :department, :start_date, :salary, :status, :third_party_info)";
            
            $stmt = $this->conn->prepare($query);
            
            $third_party_info = isset($data['third_party_info']) ? json_encode($data['third_party_info']) : null;
            
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':position', $data['position']);
            $stmt->bindParam(':department', $data['department']);
            $stmt->bindParam(':start_date', $data['start_date']);
            $stmt->bindParam(':salary', $data['salary']);
            $stmt->bindParam(':status', $data['status']);
            $stmt->bindParam(':third_party_info', $third_party_info);
            
            $stmt->execute();
            $id = (int) $this->conn->lastInsertId();
            
            $this->conn->commit();
            return $id;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            throw new DatabaseException('Failed to create employee: ' . $e->getMessage());
        }
    }

    public function update(int $id, array $data): bool
    {
        try {
            $this->conn->beginTransaction();

            $query = "UPDATE " . self::TABLE . " SET ";
            $params = [];
            
            // Build dynamic update query
            foreach ($data as $key => $value) {
                if ($key === 'third_party_info') {
                    $value = json_encode($value);
                }
                $params[$key] = $value;
                $query .= "$key = :$key, ";
            }
            
            $query = rtrim($query, ', ');
            $query .= " WHERE id = :id AND deleted_at IS NULL";
            $params['id'] = $id;
            
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => &$value) {
                $stmt->bindParam(":$key", $value);
            }
            
            $result = $stmt->execute();
            $this->conn->commit();
            
            return $result && $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            throw new DatabaseException('Failed to update employee: ' . $e->getMessage());
        }
    }

    public function delete(int $id): bool
    {
        try {
            // Soft delete
            $query = "UPDATE " . self::TABLE . " 
                     SET deleted_at = CURRENT_TIMESTAMP 
                     WHERE id = :id AND deleted_at IS NULL";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute() && $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new DatabaseException('Failed to delete employee: ' . $e->getMessage());
        }
    }

    public function search(string $query, array $filters = []): array
    {
        try {
            $sql = "SELECT * FROM " . self::TABLE . " WHERE deleted_at IS NULL";
            $params = [];
            
            // Add search condition
            if ($query) {
                $sql .= " AND (name LIKE :query OR email LIKE :query OR position LIKE :query)";
                $params[':query'] = "%$query%";
            }
            
            // Add filters
            foreach ($filters as $key => $value) {
                $sql .= " AND $key = :$key";
                $params[":$key"] = $value;
            }
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new DatabaseException('Failed to search employees: ' . $e->getMessage());
        }
    }

    public function getTotalCount(): int
    {
        try {
            $query = "SELECT COUNT(*) FROM " . self::TABLE . " 
                     WHERE status = 'active' AND deleted_at IS NULL";
            return (int) $this->conn->query($query)->fetchColumn();
        } catch (PDOException $e) {
            throw new DatabaseException('Failed to get total count: ' . $e->getMessage());
        }
    }

    public function getNewHiresCount(): int
    {
        try {
            $query = "SELECT COUNT(*) FROM " . self::TABLE . "
                     WHERE start_date >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
                     AND status = 'active' AND deleted_at IS NULL";
            return (int) $this->conn->query($query)->fetchColumn();
        } catch (PDOException $e) {
            throw new DatabaseException('Failed to get new hires count: ' . $e->getMessage());
        }
    }

    public function getResignedCount(): int
    {
        try {
            $query = "SELECT COUNT(*) FROM " . self::TABLE . "
                     WHERE status = 'resigned' 
                     AND updated_at >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
                     AND deleted_at IS NULL";
            return (int) $this->conn->query($query)->fetchColumn();
        } catch (PDOException $e) {
            throw new DatabaseException('Failed to get resigned count: ' . $e->getMessage());
        }
    }

    public function updateThirdPartyInfo(int $id, array $data): bool
    {
        try {
            $query = "UPDATE " . self::TABLE . " 
                     SET third_party_info = :third_party_info 
                     WHERE id = :id AND deleted_at IS NULL";
            
            $stmt = $this->conn->prepare($query);
            $third_party_info = json_encode($data);
            
            $stmt->bindParam(':third_party_info', $third_party_info);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute() && $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new DatabaseException('Failed to update third party info: ' . $e->getMessage());
        }
    }
} 