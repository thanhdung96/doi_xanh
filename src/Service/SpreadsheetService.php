<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\GroupRepository;
use App\Trait\TimestampTrait;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Row;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Contracts\Service\Attribute\Required;

readonly class SpreadsheetService
{
    private const REQUIRED_GROUP_ID = 3;

    use TimestampTrait;

    private LoggerInterface $logger;

    private string $exportDir;
    private string $projectDir;

    private GroupRepository $groupRepository;

    public function __construct(string $exportDir, string $projectDir, LoggerInterface $logger)
    {
        $this->exportDir = $exportDir;
        $this->projectDir = $projectDir;
        $this->logger = $logger;
    }

    #[Required]
    public function setGroupRepository(GroupRepository $groupRepository): void{
        $this->groupRepository = $groupRepository;
    }

    /**
     * @return User[]
     */
    public function extractUser(File $file): array
    {
        $file = $file->move($this->projectDir. '/var/temp', 'temp.xlsx');
        $lstUsers = [];
        $requiredGroup = $this->groupRepository->findOneBy([
            'id' => self::REQUIRED_GROUP_ID,
            'active' => true
        ]);

        $activeWorkSheet = IOFactory::load($file->getRealPath())->getSheetByName('Worksheet');
        $rows = $activeWorkSheet->toArray();

        foreach ($rows as $index => $row) {
            if($row[1] !== self::REQUIRED_GROUP_ID) {
                continue;
            }

            $user = new User();
            $user->setGroupUser($requiredGroup);
            $user->setFirstName($row[2]);
            $user->setLastName($row[3]);
            $user->setEmail($row[4]);
            $user->setPhone($row[5]);
            $lstUsers[] = $user;
        }


        return $lstUsers;
    }

    /**
     * @param User[] $lstUsers
     * @return string $fileName
     */
    public function writeExcel(array $lstUsers): string
    {
        $spreadSheet = new Spreadsheet();
        $activeWorkSheet = $this->setHeader($spreadSheet->getActiveSheet());

        foreach ($lstUsers as $index => $user) {
            $currentRow = $index + 2;

            $activeWorkSheet->setCellValue('A' . $currentRow, $user->getId());
            $activeWorkSheet->setCellValue('B' . $currentRow, $user->getGroupUser()->getId());
            $activeWorkSheet->setCellValue('C' . $currentRow, $user->getFirstName());
            $activeWorkSheet->setCellValue('D' . $currentRow, $user->getLastName());
            $activeWorkSheet->setCellValue('E' . $currentRow, $user->getEmail());
            $activeWorkSheet->setCellValue('F' . $currentRow, $user->getPhone());
            $activeWorkSheet->setCellValue('G' . $currentRow, $user->getCreatedAt());
            $activeWorkSheet->setCellValue('H' . $currentRow, $user->getUpdatedAt());
        }

        $fileName = $this->projectDir . $this->exportDir . $this->getTimestamp() . '.xlsx';
        $xlsx = new XlsxWriter($spreadSheet);
        $xlsx->save($fileName);
        return $fileName;
    }

    private function setHeader(Worksheet $worksheet): Worksheet
    {
        $worksheet->setCellValue('A1', 'ID');
        $worksheet->setCellValue('B1', 'Group ID');
        $worksheet->setCellValue('C1', 'First Name');
        $worksheet->setCellValue('D1', 'Last Name');
        $worksheet->setCellValue('E1', 'Email');
        $worksheet->setCellValue('F1', 'Phone');
        $worksheet->setCellValue('G1', 'Created Date');
        $worksheet->setCellValue('H1', 'Updated Date');

        return $worksheet;
    }
}