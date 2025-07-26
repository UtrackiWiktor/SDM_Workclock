<?php

namespace App\Controller;

use App\Entity\ClockEntry;
use App\Entity\ProjectItem;
use App\Form\ClockEntryForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class ClockController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->render('base.html.twig', [
            'message' => 'Welcome to Symfony + Twig!',
        ]);
    }

    #[Route('/clock', name: 'app_clock')]
    public function clockAction(Request $request, EntityManagerInterface $em): Response
    {
        dump($request->getSession()->all());
        $user = $this->getUser();
        $entry = new ClockEntry();
        $form = $this->createForm(ClockEntryForm::class, $entry);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entry->setUserKeycloakId($user->getUserIdentifier());
            $em->persist($entry);
            $em->flush();

            return $this->redirectToRoute('app_clock');
        }

        $projectItems = $em->getRepository(ProjectItem::class)->findAll();

        return $this->render('clock/index.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/entries', name: 'view_entries')]
    public function viewEntries(EntityManagerInterface $em): Response
    {
        // Retrieve all ClockEntry records
        $entries = $em->getRepository(ClockEntry::class)->findBy(
            [
                'userKeycloakId' => $this->getUser()->getUserIdentifier()
            ]
        );

        $timeSum = 0;
        foreach ($entries as $entry) {
            if($entry->getStartTime() && $entry->getEndTime()) {
                $timeSum += $entry->getEndTime()->getTimestamp() - $entry->getStartTime()->getTimestamp();
            }
        }
        $timeSumHours = (int) floor($timeSum / 3600);
        $timeSumRemainingMinutes = (int) floor($timeSum / 60) % 60;

        // Render the template with the entries
        return $this->render('clock/view_entries.html.twig', [
            'entries' => $entries,
            'timeSumHours' => $timeSumHours,
            'timeSumRemainingMinutes' => $timeSumRemainingMinutes
        ]);
    }
}