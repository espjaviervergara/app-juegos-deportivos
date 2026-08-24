<?php
namespace App\Repositories;

use App\Core\Database;
use PDO;

abstract class BaseRepository
{
    protected PDO $pdo;
    protected string $table;

    public function __construct()
    {
        $this->pdo = Database::pdo();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function all(int $page=1,int $limit=20, array $filters=[]): array
    {
        $offset = ($page-1)*$limit;
        $where = ''; $params=[];
        if ($filters) {
            $clauses=[];
            foreach ($filters as $k=>$v){ $clauses[]="$k=?"; $params[]=$v; }
            $where=' WHERE '.implode(' AND ', $clauses);
        }
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} $where ORDER BY id DESC LIMIT $limit OFFSET $offset");
        $stmt->execute($params);
        $data = $stmt->fetchAll();
        $cnt = $this->pdo->prepare("SELECT COUNT(*) FROM {$this->table} $where");
        $cnt->execute($params);
        $total = (int)$cnt->fetchColumn();
        return [$data,$total];
    }

    public function create(array $data): int
    {
        $cols = implode(',', array_keys($data));
        $ph = implode(',', array_fill(0, count($data), '?'));
        $stmt = $this->pdo->prepare("INSERT INTO {$this->table} ($cols) VALUES ($ph)");
        $stmt->execute(array_values($data));
        return (int)$this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sets = implode(',', array_map(fn($k)=>"$k=?", array_keys($data)));
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET $sets WHERE id=?");
        return $stmt->execute([...array_values($data), $id]);
    }

    public function delete(int $id): bool
    {
        return $this->pdo->prepare("DELETE FROM {$this->table} WHERE id=?")->execute([$id]);
    }

    public function paginate(int $page,int $limit): array
    {
        $limit = min(max(1,$limit), 100);
        $page = max(1,$page);
        return $this->all($page,$limit);
    }
}
