<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    public function findByPhone(string $phone): ?User;
    public function findById(int $id): ?User;
    public function allPaginated(array $filters = []): LengthAwarePaginator;
    public function create(array $data): User;
    public function update(User $user, array $data): User;
    public function ban(User $user): void;
    public function unban(User $user): void;
}
