<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRepository implements UserRepositoryInterface
{
    public function findByPhone(string $phone): ?User
    {
        return User::where('phone', User::normalizePhone($phone))->first();
    }

    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function allPaginated(array $filters = []): LengthAwarePaginator
    {
        $query = User::withCount('stationUpdates')->orderByDesc('created_at');

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $search = $filters['search'];
                // If search is numeric, normalize it for exact match
                if (is_numeric(preg_replace('/\D/', '', $search))) {
                     $normSearch = User::normalizePhone($search);
                     $q->where('phone', 'like', "%{$normSearch}%");
                } else {
                     $q->where('phone', 'like', "%{$search}%");
                }
                $q->orWhere('name', 'like', "%{$search}%");
            });
        }
        if (isset($filters['is_banned'])) {
            $query->where('is_banned', $filters['is_banned']);
        }

        return $query->paginate($filters['per_page'] ?? 20);
    }

    public function create(array $data): User
    {
        if (isset($data['phone'])) {
            $data['phone'] = User::normalizePhone($data['phone']);
        }
        return User::create($data);
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);
        return $user->fresh();
    }

    public function ban(User $user): void
    {
        $user->update(['is_banned' => true]);
        $user->tokens()->delete();
    }

    public function unban(User $user): void
    {
        $user->update(['is_banned' => false]);
    }
}
