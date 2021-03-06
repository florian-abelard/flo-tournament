<?php

declare(strict_types = 1);

namespace Flo\Tournoi\Tests\Domain\Stage\Entities;

use Flo\Tournoi\Domain\Core\ValueObjects\Uuid;
use Flo\Tournoi\Domain\Match\Entities\Match;
use Flo\Tournoi\Domain\Group\Entities\Group;
use Flo\Tournoi\Domain\Player\Entities\Player;
use Flo\Tournoi\Persistence\Group\DataTransferObjects as DTO;
use PHPUnit\Framework\TestCase;

class GroupTest extends TestCase
{
    private
        $uuid,
        $stageUuid,
        $type,
        $numberOfPlaces;

    public function setUp()
    {
        $this->uuid = new Uuid();
        $this->stageUuid = new Uuid();
        $this->label = 'Poule A';
        $this->numberOfPlaces = 4;
    }

    public function testCreateEntity()
    {
        $group = $this->createGroup();

        $this->assertEquals($this->uuid, $group->uuid());
        $this->assertEquals($this->stageUuid, $group->stageUuid());
        $this->assertEquals($this->label, $group->label());
        $this->assertEquals($this->numberOfPlaces, $group->numberOfPlaces());
    }

    public function testAddPlayerInEntity()
    {
        $group = $this->createGroup();

        $this->assertCount(0, $group->players());

        $player = $this->createMock(Player::class);
        $group->addPlayer($player);

        $this->assertCount(1, $group->players());
        $this->assertEquals($player, $group->players()->last());
    }

    public function testAddMatchInEntity()
    {
        $group = $this->createGroup();

        $this->assertCount(0, $group->matches());

        $match = $this->createMock(Match::class);
        $group->addMatch($match);

        $this->assertCount(1, $group->matches());
        $this->assertEquals($match, $group->matches()->last());
    }

    public function testToDTO()
    {
        $group = $this->createGroup();

        $dto = $group->toDTO();

        $this->assertInstanceOf(DTO\Group::class, $dto);
        $this->assertSame($group->uuid()->value(), $dto->uuid());
        $this->assertSame($group->stageUuid()->value(), $dto->stageUuid());
    }

    public function createGroup(): Group
    {
        $group = new Group($this->uuid, $this->stageUuid);
        $group->setLabel($this->label);
        $group->setNumberOfPlaces($this->numberOfPlaces);

        return $group;
    }
}
