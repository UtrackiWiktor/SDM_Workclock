<?php

namespace App\Controller;

use App\Entity\ClockEntry;
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
        $entry = new ClockEntry();
        $form = $this->createForm(ClockEntryForm::class, $entry);

//        $form->handleRequest($request);
//        if ($form->isSubmitted() && $form->isValid()) {
//            $em->persist($entry);
//            $em->flush();
//
//            return $this->redirectToRoute('app_clock');
//        }

        return $this->render('clock/index.html.twig', [
            'form' => $form
        ]);
    }
}