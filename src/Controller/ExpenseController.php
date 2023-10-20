<?php

namespace App\Controller;

use App\Entity\Expense;
use App\Form\Type\ExpenseType;
use App\Repository\ExpenseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExpenseController extends AbstractController
{
    #[Route('/expense', name: 'all_expense')]
    public function index () : Response {

        $user = $this->getUser();
        $expenses = $user->getExpense();

        //$expenses = $expenseRepository->findAll();

        return $this->render('expense/all.html.twig', [
            'expenses' => $expenses,
        ]);
    }

    public function single () : Response
    {
        return $this->render('expense/single.html.twig');
    }

    #[Route('/expense/add', name:'add_expense')]
    public function add(EntityManagerInterface $entityManager, Request $request) : Response
    {
        $expense = new Expense();

        $form = $this->createForm(ExpenseType::class, $expense);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $expense = $form->getData();
            $expense->setUser($user);

            $entityManager->persist($expense);
            $entityManager->flush();

            return $this->redirectToRoute('all_expense');
        }

        return $this->render('expense/add.html.twig', array(
            'form' => $form
        ));
    }

    #[Route('/expense/edit/{id}', name: 'edit_expense', methods: ['GET', 'POST'])]
    public function edit(EntityManagerInterface $entityManager, Expense $expense, Request $request) : Response
    {
        $form = $this->createForm(ExpenseType::class, $expense);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($expense, true);
            $entityManager->flush();

            return $this->redirectToRoute('all_expense', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('expense/edit.html.twig', array(
            'form' => $form,
            'expense' => $expense
        ));
    }

    #[Route('/expense/delete/{id}', name: 'delete_expense', methods: ['POST'])]
    public function remove(Expense $expense, EntityManagerInterface $entityManager) : Response
    {
        $entityManager->remove($expense);
        $entityManager->flush();

        return $this->redirectToRoute('all_expense');
    }
}