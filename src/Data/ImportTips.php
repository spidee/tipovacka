<?php
namespace App\Data;


use App\Entity\Tip;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\{Finder, SplFileInfo};
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\VarDumper\VarDumper;

class ImportTips
{
    const IMPORT_DIRECTORY = '/files';
    const IMPORT_FILE_SCHEMA = '*.xlsx';

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;
    /**
     * @var ParameterBagInterface
     */
    protected $parameterBag;
    /**
     * @var UserManagerInterface
     */
    protected $userManager;

    /**
     * DataManager constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager, ParameterBagInterface $parameterBag, UserManagerInterface $userManager)
    {
        $this->entityManager = $entityManager;
        $this->parameterBag = $parameterBag;
        $this->userManager = $userManager;
    }

    public function procesImport(): void
    {
        $em = $this->entityManager;
        $teamRepository = $em->getRepository('App:Team');
        $matchRepository = $em->getRepository('App:Match');
        $allUsers = $this->userManager->findUsers();
        $allTeams = $teamRepository->findAll();
        $allTMatches = $matchRepository->findAll();

        ini_set('max_execution_time', 3600);
        //ini_set('memory_limit', '1024M');
        set_time_limit(0);

        $rootDirectory = $this->parameterBag->get('kernel.project_dir');
        $importDirectory = $rootDirectory . self::IMPORT_DIRECTORY;

        $filesystem = new Filesystem();
        $finder = new Finder();

        $finder->name(self::IMPORT_FILE_SCHEMA)
            ->files()
            ->in($importDirectory);

        $processedCount = 0;
        /**
         * @var SplFileInfo $file
         */
        foreach ($finder as $file)
        {
            $importUser = $this->getImportUser($file->getFilename(), $allUsers);
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealpath());
            if (!$spreadsheet->getSheetCount()) {
                throw new \Exception(sprintf('No sheets in file "%s"!', $file->getFilename()));
            }


            $sheet = $spreadsheet->getSheet(0);

            $highestRow = $sheet->getHighestRow();
            $tips = [];

            foreach ($sheet->getRowIterator(1, $highestRow) as $row) {
                $matchData = $this->initTipStorage();

                foreach ($row->getCellIterator() as $cell) {
                    $cellValue = $cell->getValue();

                    if (!is_null($cellValue)) {
                        if (is_string($cellValue) && $team = $teamRepository->findTeamByCzechName($cell->getValue(), $allTeams)) {
                            if (is_null($matchData['homeTeam'])) {
                                $matchData['homeTeam'] = $team;
                            }
                            else if (is_null($matchData['awayTeam'])) {
                                $matchData['awayTeam'] = $team;
                            }
                        }

                        if (is_numeric($cellValue)) {
                            if (is_null($matchData['homeGoals'])) {
                                $matchData['homeGoals'] = intval($cellValue);
                            }
                            else if (is_null($matchData['awayGoals'])) {
                                $matchData['awayGoals'] = intval($cellValue);
                            }
                        }
                    }
                    else {
                        $matchData = $this->initTipStorage();
                    }

                    if (!is_null($matchData['homeTeam']) &&
                        !is_null($matchData['homeGoals']) &&
                        !is_null($matchData['awayTeam']) &&
                        !is_null($matchData['awayGoals'])
                    ) {
                        $tip = new Tip();
                        $tip->setHomeGoalsTip($matchData['homeGoals']);
                        $tip->setAwayGoalsTip($matchData['awayGoals']);

                        $match = $matchRepository->findMatchByTeams($matchData['homeTeam'], $matchData['awayTeam'], $allTMatches);
                        if (!$match) {
                            throw new \Exception(sprintf('No match for teams %s - %s', $matchData['homeTeam'], $matchData['awayTeam']));
                        }
                        $tip->setMatch($match);
                        $tip->setUser($importUser);

                        $em->persist($tip);
                        $em->flush();

                        $matchData = $this->initTipStorage();
                    }
                }
            }
        }
    }

    /**
     * @param $importFileName
     * @param User[] $allStoredUsers
     * @return User
     */
    private function getImportUser($importFileName, array $allStoredUsers): User
    {
        foreach ($allStoredUsers as $storedUser) {
            if (mb_strpos(mb_strtolower($importFileName), mb_strtolower($storedUser->getUsername())) !== false) {
                return $storedUser;
            }
        }
        return null;
    }

    private function initTipStorage(): array
    {
        return ['homeTeam' => null, 'homeGoals' => null, 'awayTeam' => null, 'awayGoals' => null];
    }

    private function convertUtf8(string $string): string {
        if (!mb_detect_encoding($string, 'UTF-8', true)) {
            // $string is not UTF-8
            return iconv("CP1250", "UTF-8", $string);
        }
        else {
            // already UTF-8
            return $string;
        }
    }

}