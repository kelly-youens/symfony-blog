<?php
/**
 * Created by PhpStorm.
 * User: Kelly
 * Date: 02/08/2019
 * Time: 17:37
 */

namespace App\Controller;

use App\Entity\User;
use App\Entity\Post;
use App\Form\PostType;
use App\Form\SearchType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function getAll()
    {
        $repository = $this->getDoctrine()->getRepository(Post::class);
        $posts = $repository->findAll();

        $posts = $this->getPostsWithExcerpts($posts);

        return $this->render('post/list.html.twig', [
            'posts' => $posts,
        ]);
    }

    /**
     * @Route("/posts", name="post_list")
     *
     * @return Response
     */
    public function getAllForAuthor()
    {
        $repository = $this->getDoctrine()->getRepository(Post::class);
        $posts = $repository->findBy(['user' => $this->getUser()->getId()]);

        $posts = $this->getPostsWithExcerpts($posts);

        return $this->render('post/author_list.html.twig', [
            'posts' => $posts,
        ]);
    }

    /**
     * @Route("/search", name="search_display", methods={"GET"})
     *
     * @return Response
     */
    public function searchDisplay()
    {
        $form = $this->buildSearchForm();

        return $this->render('search.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/search", name="search", methods={"POST"})
     *
     * @return Response
     */
    public function search()
    {
        $form = $this->buildSearchForm();

        $request = Request::createFromGlobals();

        $form->handleRequest($request);

        $posts = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $repository = $this->getDoctrine()->getRepository(Post::class);
            $posts = $repository->findBy(['title' => $data['searchTerm']]);

            $posts = $this->getPostsWithExcerpts($posts);
        }

        return $this->render('post/list.html.twig', [
            'posts' => $posts,
        ]);
    }

    /**
     * @Route("/posts/{id}", name="view_post", requirements={"id"="\d+"})
     *
     * @param string $id
     * @return Response
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

        return $this->render('post/new_edit.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/posts/{id}/edit", name="edit_post", methods={"GET"}, requirements={"id"="\d+"})
     *
     * @param string $id
     * @return Response
     */
    public function edit($id)
    {
        $repository = $this->getDoctrine()->getRepository(Post::class);
        $post = $repository->find($id);

        try {
            $this->denyAccessUnlessGranted('edit', $post);
        } catch (\Exception $e) {
            return new RedirectResponse($this->generateUrl('post_list'));
        }

        $form = $this->buildPostForm($post);

        return $this->render('post/new_edit.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/posts/{id}/edit", name="save_edited_post", methods={"POST"}, requirements={"id"="\d+"})
     *
     * @param string $id
     * @return Response
     */
    public function saveEdited($id)
    {
        $repository = $this->getDoctrine()->getRepository(Post::class);
        $post = $repository->find($id);

        try {
            $this->denyAccessUnlessGranted('edit', $post);
        } catch (\Exception $e) {
            return new RedirectResponse($this->generateUrl('post_list'));
        }

        $form = $this->buildPostForm($post);

        $request = Request::createFromGlobals();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();

            $post->setTitle($data->getTitle());
            $post->setBody($data->getBody());

            $entityManager->persist($post);
            $entityManager->flush();
        }

        return new RedirectResponse($this->generateUrl('post_list'));
    }

    /**
     * @Route("/posts/new", name="save_new_post", methods={"POST"})
     *
     * @throws \Exception
     * @return RedirectResponse
     */
    public function saveNew()
    {
        $entityManager = $this->getDoctrine()->getManager();

        $form = $this->buildPostForm();

        $request = Request::createFromGlobals();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $repository = $this->getDoctrine()->getRepository(User::class);

            $user = $repository->find($this->getUser()->getId());

            $post = new Post();
            $post->setTitle($data['title']);
            $post->setBody($data['body']);
            $post->setDateCreated(new \DateTime());
            $post->setDateUpdated(new \DateTime());
            $post->setUser($user);

            $entityManager->persist($post);
            $entityManager->flush();

            return new RedirectResponse($this->generateUrl('post_list'));
        }

        return $this->render('post/new.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/posts/{id}/delete", name="delete_post", requirements={"id"="\d+"})
     *
     * @param string $id
     * @return RedirectResponse|Response
     */
    public function delete($id)
    {
        $repository = $this->getDoctrine()->getRepository(Post::class);
        $post = $repository->find($id);

        try {
            $this->denyAccessUnlessGranted('delete', $post);
        } catch (\Exception $e) {
            return new RedirectResponse($this->generateUrl('post_list'));
        }

        if (!empty($post)) {
            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->remove($post);
            $entityManager->flush();
        }

        return new RedirectResponse($this->generateUrl('post_list'));
    }

    /**
     * @param Post|null $post
     * @return \Symfony\Component\Form\FormInterface
     */
    private function buildPostForm(?Post $post = null)
    {
        return $this->createForm(
            PostType::class,
            $post
        );
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    private function buildSearchForm()
    {
        return $this->createForm(
            SearchType::class
        );
    }

    /**
     * @param Post[]
     * @return Post[]
     */
    private function getPostsWithExcerpts(array $posts)
    {
        foreach ($posts as $post) {
            $body = $post->getBody();

            if (strlen($body) > 500) {
                $body = substr($body, 0, 500) . '...';
            }

            $post->setBody($body);
        }

        return $posts;
    }
}