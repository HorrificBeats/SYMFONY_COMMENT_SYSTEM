<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\PostType;
use App\Entity\Post;
use App\Entity\PostLike;
use App\Repository\CommentRepository;
use App\Repository\PostLikeRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function index(PostRepository $repo): Response
    {
        /* $this->denyAccessUnlessGranted('ROLE_USER'); */

        return $this->render('post/index.html.twig', [
            'controller_name' => 'PostController',
            'posts' => $repo->findAll(),
        ]);
    }

    /**
     * @Route("/post/{id}", name="one_post")
     */
    public function onePost(PostRepository $repo, $id, Request $request, EntityManagerInterface $em)
    {
        $onePost = $repo->findOneBy(array('id' => $id)); //query to get POST
        
        $user = $this->getUser(); //recovering USER

        //Comment form submit
        if ($request->isMethod('POST') && $user != null) {
            $comment = new Comment();
            $comment
                ->setUser($user)
                ->setContent($request->request->get('content'))
                ->setPost($onePost)
                ->setCreatedAt(new DateTime());

            //dd($comment);
            $em->persist($comment);
            $em->flush();

        } elseif ($request->isMethod('POST') && $user == null) {
            $this->addFlash('error', 'You need to be logged in order to comment!');
        }

        return $this->render('post/one_post.html.twig', [
            'post' => $onePost,
        ]);
    }


    /**
     * @Route("/createPost", name="create_post")
     */
    public function createPost(EntityManagerInterface $em, Request $request)
    {
        $form = $this->createForm(PostType::class);

        /* If there is a POST */
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $post = new Post();
            //$post->setTitle($data->getTitle());
            $post = $data;

            $em->persist($post);
            $em->flush();

            return $this->redirectToRoute('homepage',);
        }

        return $this->render('post/create_post.html.twig', [
            'postForm' => $form->createView(),
        ]);
    }

    /** 
     * @Route("/editPost/{id}", name="edit_post") 
     */
    public function editPost(Post $post, EntityManagerInterface $em, Request $request)
    {
        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $post = new Post();
            //$post->setTitle($data->getTitle());
            $post = $data;

            $em->persist($post);
            $em->flush();

            return $this->redirectToRoute('homepage',);
        }

        return $this->render('post/create_post.html.twig', [
            'postForm' => $form->createView(),
        ]);
    }

    /** 
    * @Route("/comments", name="comments_index") 
    */
    public function comments(CommentRepository $commentRepository)
    {
        $comments = $commentRepository->findAll();

        return $this->render('post/comments.html.twig', [
            'comments' => $comments,
        ]);
    }

    /** 
    * @Route("/comments/delete/{id}", name="comments_delete") 
    */
    public function deleteComment(CommentRepository $commentRepository, $id, EntityManagerInterface $em)
    {
        $comment = $commentRepository->find($id);
        if (!empty($comment)) {
            $em->remove($comment);
            $em->flush();
        }



        $this->addFlash('success', 'Successfuly deleted the comment!');
        return $this->redirectToRoute('comments_index');
    }


    /** 
    * @Route("/user/{id}", name="user_index") 
    */
    public function user(UserRepository $userRepository, $id)
    {
        $user = $userRepository->find($id);



        $this->addFlash('success', 'Successfuly deleted the comment!');
        return $this->render('user/user.html.twig', [
            'user' => $user,
        ]);
    }
}
