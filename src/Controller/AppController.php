<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AppController extends Controller
{
    /**
    * @Route("/", name="app_app_index")
    */
    public function index()
    {
        $number = mt_rand(0, 100);

        return $this->render('index.html.twig', array(
            'number' => $number,
        ));
    }
}