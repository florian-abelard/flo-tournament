<?php

declare(strict_types = 1);

namespace Flo\Tournoi\Persistence\Tournament\Repositories;

use Flo\Tournoi\Domain\Core\ValueObjects\Uuid;
use Flo\Tournoi\Domain\Tournament\Collections\TournamentCollection;
use Flo\Tournoi\Domain\Tournament\Entities\Tournament;
use Flo\Tournoi\Domain\Tournament\TournamentRepository;
use Flo\Tournoi\Domain\Tournament\ValueObjects\TournamentStatus;
use Flo\Tournoi\Persistence\Core\Repositories\Mysql as MysqlCore;

class Mysql extends MysqlCore implements TournamentRepository
{
    private const
        TABLE = 'tournament';

    public function persist(Tournament $tournament): void
    {
        $table = self::TABLE;
        $dto = $tournament->toDTO();

        $sql = <<<SQL
            INSERT INTO $table (uuid, name, status)
            VALUES (:uuid, :name, :status)
SQL;

        $statement = $this->getDatabaseConnection()->prepare($sql);
        $statement->bindValue(':uuid', $dto->uuid());
        $statement->bindValue(':name', $dto->name());
        $statement->bindValue(':status', $dto->status());
        $statement->execute();
    }

    public function findById(Uuid $uuid): ?Tournament
    {
        $table = self::TABLE;

        $sql = <<<SQL
            SELECT * FROM $table
            WHERE uuid = :uuid
SQL;

        $statement = $this->getDatabaseConnection()->prepare($sql);
        $statement->bindValue(':uuid', $uuid->value());
        $statement->execute();

        if ($statement->rowCount() === 0)
        {
            return null;
        }

        $result = $statement->fetch();
        $tournament = $this->buildDomainObject($result);

        return $tournament;
    }

    public function findAll(): TournamentCollection
    {
        $sql = 'SELECT * FROM ' . self::TABLE;

        $statement = $this->getDatabaseConnection()->prepare($sql);
        $statement->execute();

        $tournaments = new TournamentCollection;
        foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $result)
        {
            $tournaments->add($this->buildDomainObject($result));
        }

        return $tournaments;
    }

    public function updateStatus(Uuid $uuid, TournamentStatus $status): void
    {
        $table = self::TABLE;

        $sql = <<<SQL
            UPDATE $table
            SET status = :status
            WHERE uuid = :uuid
SQL;

        $statement = $this->getDatabaseConnection()->prepare($sql);
        $statement->bindValue(':status', $status->value());
        $statement->bindValue(':uuid', $uuid->value());
        $statement->execute();
    }

    public function remove(Uuid $uuid): void
    {
        $table = self::TABLE;

        $sql = <<<SQL
            DELETE FROM $table
            WHERE uuid = :uuid
SQL;

        $statement = $this->getDatabaseConnection()->prepare($sql);
        $statement->bindValue(':uuid', $uuid->value());
        $statement->execute();
    }

    public function buildDomainObject(array $result): Tournament
    {
        return new Tournament(
            new Uuid($result['uuid']),
            $result['name'],
            new TournamentStatus($result['status'])
        );
    }
}
