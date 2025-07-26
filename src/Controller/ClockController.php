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
    public function viewEntries(Request $request, EntityManagerInterface $em): Response
    {
        $start = $request->query->get('start_date');
        $end = $request->query->get('end_date');
        $projectId = $request->query->get('project_id');

        $qb = $em->getRepository(ClockEntry::class)->createQueryBuilder('c')
            ->orderBy('c.startTime', 'DESC');

        if ($start) {
            $qb->andWhere('c.startTime >= :start')->setParameter('start', new \DateTime($start));
        }
        if ($end) {
            $qb->andWhere('c.endTime <= :end')->setParameter('end', new \DateTime($end));
        }
        if ($projectId) {
            $qb->andWhere('c.project = :project')->setParameter('project', $projectId);
        }

        $entries = $qb->getQuery()->getResult();

        $projects = $em->getRepository(ProjectItem::class)->findAll();
        $timeSum = 0;
        foreach ($entries as $entry) {
            if($entry->getStartTime() && $entry->getEndTime()) {
                $timeSum += $entry->getEndTime()->getTimestamp() - $entry->getStartTime()->getTimestamp();
            }
        }
        $timeSumHours = (int) floor($timeSum / 3600);
        $timeSumRemainingMinutes = (int) floor($timeSum / 60) % 60;


        return $this->render('clock/view_entries.html.twig', [
            'entries' => $entries,
            'projects' => $projects,
            'timeSumHours' => $timeSumHours,
            'timeSumRemainingMinutes' => $timeSumRemainingMinutes
        ]);
    }

    #[Route('/clock/edit/{id}', name: 'app_clock_edit')]
    public function editClockEntry(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $entry = $em->getRepository(ClockEntry::class)->find($id);

        if (!$entry) {
            throw $this->createNotFoundException('Clock Entry not found');
        }

        $form = $this->createForm(ClockEntryForm::class, $entry);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('view_entries');
        }

        return $this->render('clock/edit.html.twig', [
            'form' => $form,
            'entry' => $entry,
        ]);
    }

    #[Route('/clock/stats', name: 'app_clock_stats')]
    public function clockStats(Request $request, EntityManagerInterface $em): Response
    {
        $start = $request->query->get('start_date');
        $end = $request->query->get('end_date');

        $qb = $em->getRepository(ClockEntry::class)->createQueryBuilder('e');

        if ($start && $end) {
            $qb->where('e.startTime >= :start')
                ->andWhere('e.endTime <= :end')
                ->setParameter('start', new \DateTime($start))
                ->setParameter('end', new \DateTime($end));
        }

        $entries = $qb->getQuery()->getResult();

        $dailyTotals = [];

        $projectDayTotals = [];
        $projects = [];

        foreach ($entries as $entry) {
            $startTime = $entry->getStartTime();
            $endTime = $entry->getEndTime();
            if (!$startTime || !$endTime) continue;

            $dateKey = $startTime->format('Y-m-d');
            $duration = ($endTime->getTimestamp() - $startTime->getTimestamp()) / 3600.0;
            $project = $entry->getProject()?->getName() ?? 'No Project';

            $projects[$project] = true;
            $projectDayTotals[$dateKey][$project] = ($projectDayTotals[$dateKey][$project] ?? 0) + $duration;
        }

        ksort($projectDayTotals);
        $labels = array_keys($projectDayTotals);
        $projectNames = array_keys($projects);

        $datasets = [];
        foreach ($projectNames as $project) {
            $data = [];
            foreach ($labels as $date) {
                $data[] = $projectDayTotals[$date][$project] ?? 0;
            }
            $datasets[] = [
                'label' => $project,
                'data' => $data,
                'backgroundColor' => sprintf('rgba(%d,%d,%d,0.7)', rand(0,255), rand(0,255), rand(0,255))
            ];
        }

        return $this->render('clock/stats.html.twig', [
            'labels' => $labels,
            'datasets' => $datasets,
        ]);
    }
}