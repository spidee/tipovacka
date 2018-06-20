<?php
namespace App\Data;


use App\Entity\Group;
use App\Entity\Match;
use App\Entity\Point;
use App\Entity\Team;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class DataManager
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;
    /**
     * @var ParameterBagInterface
     */
    protected $parameterBag;

    /**
     * DataManager constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager, ParameterBagInterface $parameterBag)
    {
        $this->entityManager = $entityManager;
        $this->parameterBag = $parameterBag;
    }

    public function getMatchDataAndUpdate(): bool
    {
        $resource = file_get_contents($this->parameterBag->get('data_api_url'));
        if ($resource) {
            $remoteData = json_decode($resource, true);

            $allStoredGroups = $this->entityManager->getRepository('App:Group')->findAll();
            $allStoredTeams = $this->entityManager->getRepository('App:Team')->findAll();

            $this->parseAndUpdateCreateMatchData($remoteData, 'groups', $allStoredGroups, $allStoredTeams);
            $this->parseAndUpdateCreateMatchData($remoteData, 'knockout', $allStoredGroups, $allStoredTeams);

            return true;
        }

        return false;
    }

    public function updatePoints(): bool
    {
        $matches = $this->entityManager->getRepository('App:Match')->getFinishedAndUnprocessedMatches();
        foreach ($matches as $match) {
            foreach ($match->getTips() as $tip) {
                $addedPoints = 0;
                $goalResultDiff = $match->getHomeResult() - $match->getAwayResult();
                $goalTipDiff = $tip->getHomeGoalsTip() - $tip->getAwayGoalsTip();

                if ($tip->getHomeGoalsTip() === $match->getHomeResult() &&
                    $tip->getAwayGoalsTip() === $match->getAwayResult()) {
                    $addedPoints = 20;
                }
                else if (
                    ($goalResultDiff < 0 && $goalTipDiff < 0) ||
                    ($goalResultDiff > 0 && $goalTipDiff > 0) ||
                    ($goalResultDiff === 0 && $goalTipDiff === 0))
                {
                    $addedPoints = 5;
                }

                $points = new Point();
                $points->setUser($tip->getUser());
                $points->setAmount($addedPoints);
                $points->setTip($tip);

                $this->entityManager->persist($points);
            }

            $match->setTipsProcessed(true);
            $this->entityManager->persist($match);
        }

        $this->entityManager->flush();
        return true;
    }

    /**
     * @param array $remoteData
     * @param $remoteDataKey
     * @param Group[] $allStoredGroups
     * @param Team[] $allStoredTeams
     */
    private function parseAndUpdateCreateMatchData(array $remoteData, $remoteDataKey, array $allStoredGroups, array $allStoredTeams)
    {
        $groupRepository = $this->entityManager->getRepository('App:Group');

        foreach ($remoteData[$remoteDataKey] as $remoteGroupData) {

            $groupName = $remoteGroupData['name'];
            $groupMatches = $remoteGroupData['matches'];

            $group = $groupRepository->findGroupByName($groupName, $allStoredGroups);
            if (!$group) {
                $group = new Group();
            }
            $group->setName($groupName);

            foreach ($groupMatches as $groupMatch) {

                $matchName = $groupMatch['name'];

                $matchInGroup = $group->getMatches()->filter(function(Match $match) use($matchName) {
                    return (string)$match->getName() === (string)$matchName;
                })->first();

                if (!$matchInGroup) {
                    $matchInGroup = new Match();
                    $group->addMatch($matchInGroup);
                }
                $this->setEntityFieldFromRemoteArrayField($groupMatch, 'name', function($data) use($matchInGroup) {
                    $matchInGroup->setName($data);
                });
                $this->setEntityFieldFromRemoteArrayField($groupMatch, 'type', function($data) use($matchInGroup) {
                    $matchInGroup->setType($data);
                });
                $this->setEntityFieldFromRemoteArrayField($groupMatch, 'home_result', function($data) use($matchInGroup) {
                    $matchInGroup->setHomeResult($data);
                });
                $this->setEntityFieldFromRemoteArrayField($groupMatch, 'away_result', function($data) use($matchInGroup) {
                    $matchInGroup->setAwayResult($data);
                });
                $this->setEntityFieldFromRemoteArrayField($groupMatch, 'home_penalty', function($data) use($matchInGroup) {
                    $matchInGroup->setHomePenalty($data);
                });
                $this->setEntityFieldFromRemoteArrayField($groupMatch, 'away_penalty', function($data) use($matchInGroup) {
                    $matchInGroup->setAwayPenalty($data);
                });
                $this->setEntityFieldFromRemoteArrayField($groupMatch, 'date', function($data) use($matchInGroup) {
                    $datetime = new \DateTime($data);
                    $datetime->setTimezone(new \DateTimeZone('Europe/Prague'));
                    $convertedDatetime = new \DateTime($datetime->format(DATE_W3C));

                    $matchInGroup->setDate($convertedDatetime);
                });
                $this->setEntityFieldFromRemoteArrayField($groupMatch, 'finished', function($data) use($matchInGroup) {
                    $matchInGroup->setFinished($data);
                });
                $this->setEntityFieldFromRemoteArrayField($groupMatch, 'matchday', function($data) use($matchInGroup) {
                    $matchInGroup->setMatchday($data);
                });

                $this->setTeam($groupMatch, 'home_team', $allStoredTeams, function(Team $team) use($matchInGroup) {
                    $matchInGroup->setHomeTeam($team);
                });
                $this->setTeam($groupMatch, 'away_team', $allStoredTeams, function(Team $team) use($matchInGroup) {
                    $matchInGroup->setAwayTeam($team);
                });
                $this->setTeam($groupMatch, 'winner', $allStoredTeams, function(Team $team) use($matchInGroup) {
                    $matchInGroup->setWinner($team);
                });

                $this->entityManager->persist($matchInGroup);
            }
            $this->entityManager->persist($group);
        }

        $this->entityManager->flush();
    }

    /**
     * @param array $remoteData
     * @param $remoteDataKey
     * @param Team[] $allStoredTeams
     * @param callable $setTeamCallback
     */
    private function setTeam(array $remoteData, $remoteDataKey, array $allStoredTeams, callable $setTeamCallback): void
    {
        $teamRepository = $this->entityManager->getRepository('App:Team');

        if (isset($remoteData[$remoteDataKey]) && is_integer($remoteData[$remoteDataKey]) && $remoteData[$remoteDataKey] &&
            $team = $teamRepository->findTeamById($remoteData[$remoteDataKey], $allStoredTeams)) {
            $setTeamCallback($team);
        }
    }


    private function setEntityFieldFromRemoteArrayField(array $remoteData, $remoteDataKey, callable $setFieldCallback): void
    {
        if (isset($remoteData[$remoteDataKey])) {
            $setFieldCallback($remoteData[$remoteDataKey]);
        }
    }

    public function getTeamDataAndUpdate(): bool
    {
        $resource = file_get_contents($this->parameterBag->get('data_api_url'));
        if ($resource) {
            $remoteData = json_decode($resource, true);

            $teamRepository = $this->entityManager->getRepository('App:Team');
            $allStoredTeams = $teamRepository->findAll();

            foreach ($remoteData['teams'] as $team) {

                $storedTeam = $teamRepository->findTeamByFifaCode($team['fifaCode'], $allStoredTeams);
                if (!$storedTeam) {
                    $storedTeam = new Team();
                }

                $storedTeam->setName($team['name']);
                $storedTeam->setIso2($team['iso2']);
                $storedTeam->setEmoji($team['emoji']);
                $storedTeam->setEmojiString($team['emojiString']);
                $storedTeam->setFlag($team['flag']);
                $storedTeam->setFifaCode($team['fifaCode']);

                $this->entityManager->persist($storedTeam);
            }

            $this->entityManager->flush();
            return true;
        }

        return false;
    }
}