<?php

namespace App\Controller;

use App\Entity\Norme;
use App\Form\NormeType;
use App\Repository\NormeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/norme")
 */
class NormeController extends AbstractController
{
    /**
     * @Route("/", name="norme_index", methods={"GET"})
     */
    public function index(NormeRepository $normeRepository): Response
    {
        return $this->render('norme/index.html.twig', [
            'normes' => $normeRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="norme_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $norme = new Norme();
        $form = $this->createForm(NormeType::class, $norme);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($norme);
            foreach ($norme->getNormees() as $normees)
            {
                $entityManager->persist($normees);
            }
            $entityManager->flush();

            return $this->redirectToRoute('norme_index');
        }

        return $this->render('norme/new.html.twig', [
            'norme' => $norme,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="norme_show", methods={"GET"})
     */
    public function show(Norme $norme): Response
    {
        
    
        return $this->render('norme/show.html.twig', [
            'norme' => $norme,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="norme_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Norme $norme, EntityManagerInterface $entityManager,$id): Response
    {
        if (null === $norme = $entityManager->getRepository(Norme::class)->find($id)) {
            throw $this->createNotFoundException('No task found for id '.$id);
        }
        $normees = new ArrayCollection();

        // Create an ArrayCollection of the current Tag objects in the database
        foreach ($norme->getNormees() as $normees) {
            $norme->addNormee($normees);
        }
        $form = $this->createForm(NormeType::class, $norme);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($normees as $normees) {
                if (false === $norme->getNormees()->contains($normees)) {
                    // remove the Task from the Tag
                    $normees->getNormees()-> removeNormee($norme);
    
                    // if it was a many-to-one relationship, remove the relationship like this
                    // $tag->setTask(null);
    
                    $entityManager->persist($norme);
    
                    // if you wanted to delete the Tag entirely, you can also do that
                    // $entityManager->remove($tag);
                }}
                $entityManager->persist($norme);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('norme_index');
        }

        return $this->render('norme/edit.html.twig', [
            'norme' => $norme,
            'form' => $form->createView(),
        ]);
    }
    

    /**
     * @Route("/{id}", name="norme_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Norme $norme): Response
    {
        if ($this->isCsrfTokenValid('delete'.$norme->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($norme);
            foreach ($norme->getNormees() as $normees)
            {
                $entityManager->remove($normees);
            }
            $entityManager->flush();
        }

        return $this->redirectToRoute('norme_index');
    }
}
