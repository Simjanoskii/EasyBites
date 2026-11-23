<?php

declare(strict_types=1);

abstract class EntityService
{
    private BaseDAO $dao;

    protected function __construct(BaseDAO $dao)
    {
        $this->dao = $dao;
    }

    public function findAll(): array
    {
        return array_map([$this, 'present'], $this->dao->findAll());
    }

    public function findById(int $id): array
    {
        $this->assertValidId($id);
        $record = $this->dao->findById($id);
        if ($record === null) {
            throw new OutOfBoundsException('The requested record was not found.');
        }

        return $this->present($record);
    }

    public function create(array $data): int
    {
        return $this->dao->create($this->validate($data, true));
    }

    public function update(int $id, array $data): void
    {
        $this->assertValidId($id);
        if (!$this->dao->update($id, $this->validate($data, false))) {
            throw new OutOfBoundsException('The requested record was not found.');
        }
    }

    public function delete(int $id): void
    {
        $this->assertValidId($id);
        if (!$this->dao->delete($id)) {
            throw new OutOfBoundsException('The requested record was not found.');
        }
    }

    protected function present(array $record): array
    {
        return $record;
    }

    protected function assertValidId(int $id): void
    {
        if ($id < 1) {
            throw new InvalidArgumentException('Record ID must be a positive integer.');
        }
    }

    abstract protected function validate(array $data, bool $creating): array;
}
