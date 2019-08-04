<?php
/**
 * Created by PhpStorm.
 * User: Kelly
 * Date: 02/08/2019
 * Time: 18:39
 */

namespace App\Controller;

use App\Entity\Author;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AuthorController extends AbstractController
{
    /**
     * @Route("/authors", name="author_list")
     */
    public function index()
    {
        $repository = $this->getDoctrine()->getRepository(Author::class);
        $authors = $repository->findAll();

        return $this->render('author/list.html.twig', ['authors' => $authors]);
    }

    /**
     * @Route("/authors/{id}", name="author_details", requirements={"id"="\d+"})
     */
    public function getOne($id)
    {
        $repository = $this->getDoctrine()->getRepository(Author::class);
        $author = $repository->find($id);

        return $this->render('author/single.html.twig', ['author' => $author]);
    }
}