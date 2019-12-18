<?php
declare(strict_types=1);

namespace App\Services;


class GitlabService
{

    public function __construct(GitlabApiService $gitlabApiService)
    {
        $this->gitlabApiService = $gitlabApiService;
    }

    function getFormattedMergeRequests($mergeRequests)
    {
        return array_map(function ($mergeRequest) {
            $pipelines = $this->gitlabApiService->getMergeRequestPipelines($mergeRequest['project_id'], $mergeRequest['iid']);

            return [
                'title' => $mergeRequest['title'],
                'projectId' => $mergeRequest['project_id'],
                'sourceBranch' => $mergeRequest['source_branch'],
                'targetBranch' => $mergeRequest['target_branch'],
                'author' => $mergeRequest['author'],
                'latestPipelines' => $this->getFormattedPipelines($mergeRequest, $pipelines),
                'pipelineHistory' => $this->getFormattedHistoryPipelines($mergeRequest, $pipelines)
            ];
        }, $mergeRequests);
    }

    function getFormattedPipelines($mergeRequest, $pipelines)
    {
        return array_map(function ($pipeline) use ($mergeRequest) {
            $stages = [];
            array_map(function ($job) use (&$stages) {
                if (!array_key_exists($job['stage'], $stages)) {
                    $stages[$job['stage']] = [
                        'name' => $job['stage'],
                        'jobs' => []
                    ];
                }
                $stages[$job['stage']]['jobs'][] = [
                    'name' => $job['name'],
                    'status' => $job['status'],
                    'createdAt' => $job['created_at'],
                    'finishedAt' => $job['finished_at'],
                    'duration' => array_key_exists('duration', $job) ? $job['duration'] : -1,
                ];
            }, $this->gitlabApiService->getPipelineJobs($mergeRequest['project_id'], $pipeline['id']));

            return [
                "status" => $pipeline["status"],
                "createdAt" => $pipeline["created_at"],
                "updatedAt" => $pipeline["updated_at"],
                'stages' => $stages
            ];
        }, array_slice($pipelines, 0, 2));
    }

    function getFormattedHistoryPipelines($mergeRequest, $pipelines)
    {
        return array_map(function ($pipeline) {
            $createdAt = new \DateTime($pipeline["created_at"]);
            $updatedAt = new \DateTime($pipeline["updated_at"]);

            return [
                "status" => $pipeline["status"],
                "createdAt" => $pipeline["created_at"],
                "updatedAt" => $pipeline["updated_at"],
                "duration" => $updatedAt->getTimestamp() - $createdAt->getTimestamp()
            ];
        }, $pipelines);
    }
}
