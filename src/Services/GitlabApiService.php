<?php
declare(strict_types=1);

namespace App\Services;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpClient\CachingHttpClient;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\Cache\ItemInterface;

class GitlabApiService
{

    /**
     * @var CachingHttpClient
     */
    private $client;

    /**
     * @var FilesystemAdapter
     */
    private $cache;

    /**
     * @var string
     */
    private $apiRoot;

    /**
     * @var string
     */
    private $privateToken;

    public function __construct(string $apiRoot, string $privateToken)
    {

        $this->apiRoot = $apiRoot;
        $this->privateToken = $privateToken;

        $this->cache = new FilesystemAdapter(
            '',
            600,
            __DIR__ . '/../../var/cache/http'
        );

        $this->client = HttpClient::create();

    }

    function getMergeRequests()
    {
        return $this->get('merge_requests?state=opened');
    }

    function get(...$args)
    {
        $url = sprintf(...$args);

        return $this->cache->get(md5($url), function (ItemInterface $item) use ($url) {
            $item->expiresAfter(3600);

            return $this->client->request('GET', sprintf('%s/%s', $this->apiRoot, $url), [
                'headers' => ["PRIVATE-TOKEN" => $this->privateToken]
            ])->toArray();
        });
    }

    function getMergeRequestPipelines($projectId, $mergeIid)
    {
        return $this->get('projects/%s/merge_requests/%s/pipelines', $projectId, $mergeIid);
    }

    function getPipelineJobs($projectId, $pipelineIid)
    {
        return $this->get('projects/%s/pipelines/%s/jobs', $projectId, $pipelineIid);
    }

}
