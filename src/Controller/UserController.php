<?php

namespace App\Controller;

use App\Service\SpreadsheetService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Service\Attribute\Required;

#[Route(path: '/api/user')]
class UserController extends AbstractController
{
    private SpreadsheetService $spreadsheetService;

    public function __construct(
        private readonly UserService $userService
    ) { }

    #[Required]
    public function setSpreadsheetService(SpreadsheetService $spreadsheetService): void{
        $this->spreadsheetService = $spreadsheetService;
    }

    #[Route(path: '/{groupId}', name: 'user_index', methods: [Request::METHOD_GET])]
    public function index(int $groupId): JsonResponse
    {
        $lstUser = $this->userService->getUserByGroupId($groupId);

        if(is_null($lstUser)) {
            return $this->json(
                [
                    'message' => 'group not found'
                ],
                Response::HTTP_NOT_FOUND
            );
        }
        return $this->json($lstUser);
    }

    #[Route(path: '/', name: 'user_update', methods: [Request::METHOD_PATCH])]
    public function update(Request $request): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/UserController.php',
        ]);
    }

    #[Route(path: '/import', name: 'user_import', methods: [Request::METHOD_POST])]
    public function import(Request $request): JsonResponse
    {
        $file = $request->files->get('file');
        $lstUsers = $this->spreadsheetService->extractUser($file);
        $lstUsers = $this->userService->saveMany($lstUsers);

        return $this->json($lstUsers);
    }

    #[Route(path: '/{groupId}/export', name: 'user_export', methods: [Request::METHOD_GET])]
    public function export(int $groupId): BinaryFileResponse
    {
        $lstUser = $this->userService->getUserByGroupId($groupId) ?? [];
        $file = new File($this->spreadsheetService->writeExcel($lstUser->toArray()));

        return $this->file($file);
    }
}
