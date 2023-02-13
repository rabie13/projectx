<?php

namespace App\Controller;

use App\Entity\Project;
use App\Form\ProjectType;
use App\Repository\ProjectRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ProjectController extends AbstractController
{
    #[Route('/', name: 'app_project')]
    public function index(Request $request, ProjectRepository $repository, PaginatorInterface $paginator): Response
    {
        $name = $request->query->get('name', '');
        $status = $request->query->get('status', '');
        $filename = $request->query->get('filename','');
        if ($name != '' || $status != '' || $filename != '') $projects = $repository->filterProjects($name, $status, $filename);
        else   $projects = $repository->findAll();

        $projects = $paginator->paginate($projects,  $request->query->getInt('page',1),   6);

        return $this->render('project/index.html.twig', [
            'projects' => $projects,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/project/add', name: 'app_project_add')]
    #[Route('/project/{id}/edit', name: 'app_project_edit')]
    public function addProject(Request $request, Project $project = null, ProjectRepository $repository, SluggerInterface $slugger): Response
    {
        if (!$project){
            $project = new Project();
        }
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $project->setUpdatedAt(new \DateTime());
            /** @var UploadedFile $file */
            $file = $form->get('photo')->getData();
            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();
                try {
                    $file->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                    $project->setImage($newFilename);
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
            }
            if (!$project->getId())  $project->setCreatedBy($this->getUser());
            $temp = $project->getId() == null ? 'created' : 'updated';
            $repository->save($project, true);
            $this->addFlash('success', "Project ".$project->getTitle()." has been successfully $temp");
            return $this->redirectToRoute('app_project_show', ['id' => $project->getId()]);
        }

        return $this->render('project/_form.html.twig', [
            'form' => $form->createView(),
            'project' => $project
        ]);
    }

    #[Route('/project/{id}', name: 'app_project_show')]
    public function show(Project $project): Response
    {
        return $this->render('project/show.html.twig', [
            'project' => $project,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/project/{id}/delete', name: 'app_project_delete', methods: ['POST'] )]
    public function delete(Request $request, Project $project, ProjectRepository $repository): Response
    {
        if ($this->isCsrfTokenValid('delete_project', $request->request->get('_csrf_token'))) {
            $repository->remove($project, true);
            $this->addFlash('success', 'Project deleted');
        }

        return $this->redirectToRoute('app_project', [], Response::HTTP_SEE_OTHER);
    }

}
