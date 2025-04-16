<?php

namespace App\Services;

use App\Interfaces\EmployeeRepositoryInterface;
use App\Exceptions\ValidationException;
use App\Exceptions\NotFoundException;
use Respect\Validation\Validator as v;
use Predis\Client as Redis;
use Monolog\Logger;

class EmployeeService
{
    private const CACHE_TTL = 3600; // 1 hour
    private const CACHE_KEY_PREFIX = 'employee:';

    public function __construct(
        private EmployeeRepositoryInterface $repository,
        private Redis $redis,
        private Logger $logger
    ) {}

    public function getAllEmployees(int $page = 1, int $limit = 10): array
    {
        $cacheKey = self::CACHE_KEY_PREFIX . "all:{$page}:{$limit}";
        
        // Try to get from cache
        if ($cached = $this->redis->get($cacheKey)) {
            return json_decode($cached, true);
        }

        // Get from repository
        $result = $this->repository->getAll($page, $limit);
        
        // Cache the result
        $this->redis->setex($cacheKey, self::CACHE_TTL, json_encode($result));
        
        return $result;
    }

    public function createEmployee(array $data): int
    {
        $this->validateEmployeeData($data);
        
        try {
            $employeeId = $this->repository->create($data);
            
            // Clear relevant caches
            $this->clearEmployeeCache();
            
            $this->logger->info('Employee created successfully', [
                'id' => $employeeId,
                'email' => $data['email']
            ]);
            
            return $employeeId;
        } catch (\Exception $e) {
            $this->logger->error('Failed to create employee', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    public function updateEmployee(int $id, array $data): bool
    {
        if (!$this->repository->getById($id)) {
            throw new NotFoundException('Employee not found');
        }

        $this->validateEmployeeData($data, true);
        
        try {
            $result = $this->repository->update($id, $data);
            
            // Clear relevant caches
            $this->clearEmployeeCache($id);
            
            $this->logger->info('Employee updated successfully', [
                'id' => $id,
                'email' => $data['email'] ?? null
            ]);
            
            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Failed to update employee', [
                'error' => $e->getMessage(),
                'id' => $id,
                'data' => $data
            ]);
            throw $e;
        }
    }

    public function deleteEmployee(int $id): bool
    {
        if (!$this->repository->getById($id)) {
            throw new NotFoundException('Employee not found');
        }

        try {
            $result = $this->repository->delete($id);
            
            // Clear relevant caches
            $this->clearEmployeeCache($id);
            
            $this->logger->info('Employee deleted successfully', ['id' => $id]);
            
            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Failed to delete employee', [
                'error' => $e->getMessage(),
                'id' => $id
            ]);
            throw $e;
        }
    }

    private function validateEmployeeData(array $data, bool $isUpdate = false): void
    {
        $rules = [
            'name' => v::stringType()->notEmpty()->length(2, 255),
            'email' => v::email()->notEmpty(),
            'position' => v::stringType()->notEmpty()->length(2, 100),
            'department' => v::stringType()->notEmpty()->length(2, 100),
            'start_date' => v::date('Y-m-d'),
            'salary' => v::numericVal()->positive(),
            'status' => v::in(['active', 'inactive', 'resigned'])
        ];

        $errors = [];
        foreach ($rules as $field => $rule) {
            if (!$isUpdate || isset($data[$field])) {
                try {
                    $rule->assert($data[$field] ?? null);
                } catch (\Exception $e) {
                    $errors[$field] = $e->getMessage();
                }
            }
        }

        if (!empty($errors)) {
            throw new ValidationException('Validation failed', $errors);
        }
    }

    private function clearEmployeeCache(?int $id = null): void
    {
        if ($id) {
            $this->redis->del(self::CACHE_KEY_PREFIX . $id);
        }
        
        // Clear list caches
        $keys = $this->redis->keys(self::CACHE_KEY_PREFIX . 'all:*');
        if (!empty($keys)) {
            $this->redis->del($keys);
        }
    }
} 