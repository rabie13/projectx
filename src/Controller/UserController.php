<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[IsGranted('ROLE_ADMIN')]
class UserController extends AbstractController
{
    #[Route('/users', name: 'app_user')]
    public function index(Request $request, UserRepository $repository, PaginatorInterface $paginator): Response
    {
        $users = $paginator->paginate($repository->findAll(),  $request->query->getInt('page',1),   6);
        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/user/add', name: 'app_user_add')]
    #[Route('/user/{id}/edit', name: 'app_user_edit')]
    public function addUser(Request $request, User $user = null, UserRepository $repository, UserPasswordHasherInterface $passwordHasher): Response
    {
        if (!$user){
            $user = new User();
            $action = $this->generateUrl('app_user_add');
        }else{
            $action = $this->generateUrl('app_user_edit' , ['id' => $user->getId()]);
        }

        $form = $this->createForm(UserType::class, $user, [
            'action' => $action
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form['plainPassword']->getData();

            if ($plainPassword) {
                $hash = $passwordHasher->hashPassword( $user, $plainPassword);
                $user->setPassword($hash);
            }
            
            if ($request->get('imageBlob') != "") {
                $this->uploadBlobImage($user,  $request->get('imageBlob'));
            }
       
            $repository->save($user, true);
            $temp = $user->getId() == null ? 'created' : 'updated';
            $this->addFlash('success', "User has been successfully $temp");
            if ($temp == 'created')  return $this->redirectToRoute('app_user');
            else{
                $referer = $request->headers->get('referer');
                return $this->redirect($referer);
            }
        }elseif ($form->isSubmitted()) {
            $errors = "";
            for ($i=0; $i < count($form->getErrors(true)) ; $i++) { 
                $errors .= "* ".$form->getErrors(true)->offsetGet($i)->getMessageTemplate()."\n";
            }
            $this->addFlash('danger', $errors);
            return $this->redirectToRoute('app_user');
        }

        return $this->render('user/_form.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }


    #[Route('/user/{id}/delete', name: 'app_user_delete', methods: ['POST'] )]
    public function delete(Request $request, User $user, UserRepository $repository): Response
    {
        if ($this->isCsrfTokenValid('delete_user', $request->request->get('_csrf_token'))) {
            $repository->remove($user, true);
            $this->addFlash('success', 'User deleted');
        }
        return $this->redirectToRoute('app_user', [], Response::HTTP_SEE_OTHER);
    }

    private function uploadBlobImage($entity, $base64Content)
    {
        $uploadDir = $this->getParameter('users_directory').'/';
        $base64=str_replace('data:image/png;base64,', '', $base64Content);
        $filename = md5(uniqid()) . '.png';
        $path =  $uploadDir . $filename;
        try{
            $oldImage = $entity->getImage();
            $handle = fopen( $path, 'a' );
            fwrite( $handle, base64_decode($base64));
            fclose( $handle );
            $oldImage = $entity->getImage();
            $entity->setImage($filename);

            // if user already has an image -> delete old one
            if($oldImage) @unlink($uploadDir.$oldImage);
        }catch(\Exception $e){
            return new JsonResponse(['errors' => 'Unable to upload the image'], 500);
        }
    }
}
