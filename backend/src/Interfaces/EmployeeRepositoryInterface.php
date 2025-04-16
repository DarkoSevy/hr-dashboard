<?php

namespace App\Interfaces;

interface EmployeeRepositoryInterface
{
    public function getAll(int $page = 1, int $limit = 10): array;
    public function getById(int $id): ?array;
    public function create(array $data): int;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function search(string $query, array $filters = []): array;
    public function getTotalCount(): int;
    public function getNewHiresCount(): int;
    public function getResignedCount(): int;
    public function updateThirdPartyInfo(int $id, array $data): bool;
} 