<?php

namespace App\Controller;

use App\Data\DataManager;
use App\Entity\Group;
use App\Entity\User;
use FOS\UserBundle\Model\UserManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AppController extends Controller
{
    /**
    * @Route("/", name="app_app_index")
    * @Template()
    */
    public function index(Request $request, DataManager $dataManager, UserManagerInterface $userManager)
    {

        $finishedMatches = $this->getDoctrine()->getRepository('App:Match')->getFinishedMatches();
        $futureMatches = $this->getDoctrine()->getRepository('App:Match')->getFutureMatches();
        $allUsers = $userManager->findUsers();

        usort($allUsers, function(User $userA, User $userB) {
            return $userB->getTotalPoints() <=> $userA->getTotalPoints();
        });


        return [
            'finishedMatches' => $finishedMatches,
            'futureMatches' => $futureMatches,
            'users' => $allUsers
        ];
    }

    /**
     * @Route("/skupina/{group}", name="app_app_group")
     * @Template()
     */
    public function group(Request $request, DataManager $dataManager, Group $group)
    {
        $finishedMatches = $this->getDoctrine()->getRepository('App:Match')->getFinishedMatches($group);
        $futureMatches = $this->getDoctrine()->getRepository('App:Match')->getFutureMatches($group);
        $users = $this->getDoctrine()->getRepository('App:Match')->getFutureMatches($group);
        return [
            'finishedMatches' => $finishedMatches,
            'futureMatches' => $futureMatches
        ];
    }

    /**
     * @Template(template="partials/menu.html.twig")
     */
    public function renderMenu(Request $request, Request $originalRequest)
    {
        $menuItems = [
        ];


        $allGroups = $this->getDoctrine()->getRepository('App:Group')->findAll();

        $menuItems['menu'] = [];

        array_push($menuItems['menu'], [
            'name' => 'Hlavní strana',
            'url' => $this->generateUrl('app_app_index'),
        ]);

        $menuItems['groups'] = [];

        foreach($allGroups as $group) {
            array_push($menuItems['groups'], [
                'name' => $group->getName(),
                'url' => $this->generateUrl('app_app_group', ['group' => $group->getId()])
            ]);
        }

        foreach ($menuItems as &$menuItem) {
            foreach ($menuItem as &$menuLink) {
                $menuLink['active'] = $menuLink['url'] === $originalRequest->getRequestUri();
            }
        }

        return [
            'menuItems' => $menuItems
        ];
    }
}