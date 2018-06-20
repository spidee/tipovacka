<?php

namespace App\Controller;

use App\Data\DataManager;
use App\Data\ImportTips;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CronController extends Controller
{
    /**
    * @Route("/cron/update", name="app_cron_update")
    */
    public function index(DataManager $dataManager)
    {
        $dataManager->getMatchDataAndUpdate();
        $dataManager->updatePoints();

        return Response::create('OK');
    }
    /**
     * @Route("/cron/import-tips", name="app_cron_import_tips")
     */
    public function importTips(ImportTips $importTips)
    {
        $importTips->procesImport();

        return Response::create('OK');
    }
}