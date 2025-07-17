<?php

namespace App\Repositories;

use App\Repositories\Interfaces\ClockInRepositoryInterface;
use App\Dtos\ClockIn\CreateClockInDto;
use App\Dtos\ClockIn\ListFilterDto;
use App\Models\ClockIn;
use App\Exceptions\DuplicateClockInException;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class ClockInRepository implements ClockInRepositoryInterface
{
    private const PER_PAGE = 15;
    private const DEFAULT_PAGE = 1;

    public function __construct(private ClockIn $model) {}

    public function findAll(ListFilterDto $dto): LengthAwarePaginator
    {
        $queryBase = $this->buildBaseQuery($dto);
        $binds = $this->buildBinds($dto);

        $countQuery = "SELECT COUNT(*) as total FROM (" . $queryBase . ") as sub";
        $total = DB::selectOne($countQuery, $binds)->total;

        $perPage = self::PER_PAGE;
        $currentPage = request()->get('page', self::DEFAULT_PAGE);
        $offset = ($currentPage - 1) * $perPage;

        $queryPaginated = $queryBase . " LIMIT $perPage OFFSET $offset";
        $results = DB::select($queryPaginated, $binds);

        $results = array_map(fn($item) => (array) $item, $results);

        return new LengthAwarePaginator(
            $results,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );
    }

    private function buildBaseQuery(ListFilterDto $dto): string
    {
        $query = "SELECT 
                    ci.id,
                    employee.name,
                    employee.job_title,
                    TIMESTAMPDIFF(YEAR, employee.birth_date, CURDATE()) AS age,
                    manager.name AS manager_name,
                    DATE_FORMAT(ci.registered_at, '%d/%m/%Y %H:%i:%s') AS registered_at
                FROM clock_ins ci
                INNER JOIN users employee ON employee.id = ci.user_id
                INNER JOIN users manager ON manager.id = employee.created_by
                WHERE 1=1";

        if ($dto->startDate) {
            $query .= " AND ci.registered_at >= ?";
        }
        if ($dto->endDate) {
            $query .= " AND ci.registered_at <= ?";
        }
        $query .= " ORDER BY ci.registered_at DESC";
        return $query;
    }

    private function buildBinds(ListFilterDto $dto): array
    {
        $binds = [];
        if ($dto->startDate) {
            $binds[] = $dto->startDate->format('Y-m-d H:i:s');
        }
        if ($dto->endDate) {
            $binds[] = $dto->endDate->copy()->endOfDay()->format('Y-m-d H:i:s');
        }
        return $binds;
    }

    public function create(CreateClockInDto $dto): ClockIn
    {
        $this->validateDuplication($dto);
        return $this->model->create($dto->toArray());
    }

    private function validateDuplication(CreateClockInDto $dto): void
    {
        $existingClockIn = $this->model
            ->where('user_id', $dto->user_id)
            ->whereBetween('registered_at', [
                $dto->registered_at->copy()->startOfSecond(),
                $dto->registered_at->copy()->endOfSecond(),
            ])
            ->first();

        if ($existingClockIn) {
            throw new DuplicateClockInException(userId: $dto->user_id);
        }
    }
} 