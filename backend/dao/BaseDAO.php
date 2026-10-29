<?php

declare(strict_types=1);

abstract class BaseDAO
{
    protected PDO $pdo;

    private string $table;
    private string $primaryKey;
    private array $writableColumns;

    protected function __construct(PDO $pdo, string $table, string $primaryKey, array $writableColumns)
    {
        $this->assertIdentifier($table);
        $this->assertIdentifier($primaryKey);
        foreach ($writableColumns as $column) {
            $this->assertIdentifier($column);
        }

        $this->pdo = $pdo;
        $this->table = $table;
        $this->primaryKey = $primaryKey;
        $this->writableColumns = array_values($writableColumns);
    }

    public function create(array $data): int
    {
        $data = $this->validateWritableData($data);
        if ($data === []) {
            throw new InvalidArgumentException('At least one field is required to create a record.');
        }

        $columns = array_keys($data);
        $quotedColumns = array_map([$this, 'quoteIdentifier'], $columns);
        $placeholders = array_map(static function (string $column): string {
            return ':' . $column;
        }, $columns);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->quoteIdentifier($this->table),
            implode(', ', $quotedColumns),
            implode(', ', $placeholders)
        );
        $statement = $this->pdo->prepare($sql);
        $statement->execute($data);

        $id = filter_var($this->pdo->lastInsertId(), FILTER_VALIDATE_INT);
        if ($id === false || $id < 1) {
            throw new UnexpectedValueException('The database did not return a valid inserted record ID.');
        }

        return $id;
    }

    public function findById(int $id): ?array
    {
        $this->assertPositiveId($id);
        $sql = sprintf(
            'SELECT * FROM %s WHERE %s = :record_id',
            $this->quoteIdentifier($this->table),
            $this->quoteIdentifier($this->primaryKey)
        );
        $statement = $this->pdo->prepare($sql);
        $statement->execute(['record_id' => $id]);
        $record = $statement->fetch();

        return $record === false ? null : $record;
    }

    public function findAll(): array
    {
        $sql = sprintf(
            'SELECT * FROM %s ORDER BY %s ASC',
            $this->quoteIdentifier($this->table),
            $this->quoteIdentifier($this->primaryKey)
        );

        $statement = $this->pdo->prepare($sql);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function update(int $id, array $data): bool
    {
        $this->assertPositiveId($id);
        $data = $this->validateWritableData($data);
        if ($data === []) {
            throw new InvalidArgumentException('At least one field is required to update a record.');
        }

        $assignments = [];
        $parameters = ['record_id' => $id];
        foreach ($data as $column => $value) {
            $placeholder = 'set_' . $column;
            $assignments[] = $this->quoteIdentifier($column) . ' = :' . $placeholder;
            $parameters[$placeholder] = $value;
        }

        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s = :record_id',
            $this->quoteIdentifier($this->table),
            implode(', ', $assignments),
            $this->quoteIdentifier($this->primaryKey)
        );
        $statement = $this->pdo->prepare($sql);
        $statement->execute($parameters);

        if ($statement->rowCount() > 0) {
            return true;
        }

        return $this->findById($id) !== null;
    }

    public function delete(int $id): bool
    {
        $this->assertPositiveId($id);
        $sql = sprintf(
            'DELETE FROM %s WHERE %s = :record_id',
            $this->quoteIdentifier($this->table),
            $this->quoteIdentifier($this->primaryKey)
        );
        $statement = $this->pdo->prepare($sql);
        $statement->execute(['record_id' => $id]);

        return $statement->rowCount() > 0;
    }

    private function validateWritableData(array $data): array
    {
        foreach ($data as $column => $value) {
            if (!is_string($column) || !in_array($column, $this->writableColumns, true)) {
                throw new InvalidArgumentException('The data contains a field that cannot be written.');
            }
        }

        return $data;
    }

    private function assertPositiveId(int $id): void
    {
        if ($id < 1) {
            throw new InvalidArgumentException('Record ID must be a positive integer.');
        }
    }

    private function assertIdentifier(string $identifier): void
    {
        if (preg_match('/\A[A-Za-z_][A-Za-z0-9_]*\z/', $identifier) !== 1) {
            throw new InvalidArgumentException('Invalid SQL identifier in DAO configuration.');
        }
    }

    private function quoteIdentifier(string $identifier): string
    {
        $this->assertIdentifier($identifier);

        return '`' . $identifier . '`';
    }
}
