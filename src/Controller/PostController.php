<?php
/**
 * Created by PhpStorm.
 * User: Kelly
 * Date: 02/08/2019
 * Time: 17:37
 */

namespace App\Controller;

use App\Entity\Author;
use App\Entity\Post;
use App\Form\PostType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    /**
     * @Route("/", name="home")
     * @Route("/posts", name="post_list")
     */
    public function getAll()
    {
        $repository = $this->getDoctrine()->getRepository(Post::class);
        $posts = $repository->findAll();

        foreach ($posts as $post) {
            $body = $post->getBody();

            if (strlen($body) > 500) {
                $body = substr($body, 0, 500) . '...';
            }

            $post->setBody($body);
        }

        return $this->render('post/list.html.twig', [
            'posts' => $posts,
        ]);
    }

    /**
     * @Route("/posts/{id}", name="view_post", requirements={"id"="\d+"})
     */
    public function getOne($id)
    {
        $repository = $this->getDoctrine()->getRepository(Post::class);
        $post = $repository->find($id);

        return $this->render('post/single.html.twig', ['post' => $post]);
    }

    /**
     * @Route("/posts/new", name="new_post", methods={"GET"})
     */
    public function new()
    {
        $form = $this->buildPostForm();

        return $this->render('post/new.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/posts/new", name="save_new_post", methods={"POST"})
     *
     * @throws \Exception
     * @return RedirectResponse
     */
    public function save()
    {
        $entityManager = $this->getDoctrine()->getManager();

        $form = $this->buildPostForm();

        $request = Request::createFromGlobals();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $repository = $this->getDoctrine()->getRepository(Author::class);

            $author = $repository->find(1); // todo, get from session

            $post = new Post();
            $post->setTitle($data['title']);
            $post->setBody($data['body']);
            $post->setDateCreated(new \DateTimeImmutable());
            $post->setDateUpdated(new \DateTimeImmutable());
            $post->setAuthor($author);

            $entityManager->persist($post);
            $entityManager->flush();

            return new RedirectResponse($this->generateUrl('post_list'));
        }

        return $this->render('post/new.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    private function buildPostForm()
    {
        return $this->createForm(
            PostType::class
        );
    }
}