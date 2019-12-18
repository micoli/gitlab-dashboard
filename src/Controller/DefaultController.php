<?php
declare(strict_types=1);

namespace App\Controller;

use App\Services\GitlabApiService;
use App\Services\GitlabService;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class DefaultController
{

    /**
     * @var GitlabApiService
     */
    private $gitlabApiService;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var GitlabService
     */
    private $gitlabService;

    public function __construct(GitlabApiService $gitlabApiService, GitlabService $gitlabService, Environment $twig)
    {
        $this->gitlabApiService = $gitlabApiService;
        $this->twig = $twig;
        $this->gitlabService = $gitlabService;
    }

    function index()
    {
        $mergeRequests = $this->gitlabApiService->getMergeRequests();

        return new Response($this->twig->render('main.html.twig', [
            'mergeRequests' => $this->gitlabService->getFormattedMergeRequests($mergeRequests)
        ]));
    }

}
