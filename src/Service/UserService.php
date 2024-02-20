<?php

namespace App\Service;

use App\Repository\GroupRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;

readonly class UserService
{
    public function __construct(
        private readonly GroupRepository $groupRepository,
        private readonly UserRepository $userRepository
    ) { }

    public function getUserByGroupId(int $groupId): ?Collection {
        $group = $this->groupRepository->find($groupId);

        if(is_null($group)) {
            return null;
        }

        return $group->getUsers();
    }

    public function saveMany(array $lstUsers): array {
        return $this->userRepository->saveAll($lstUsers);
    }
}