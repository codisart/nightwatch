<?php
namespace NightWatch\Client;

use GuzzleHttp\Client;

class Gitlab
{
    public function __construct(
        Client $client,
        $baseUrl,
        $projectId,
        $privateToken
    ) {
        $this->client = $client;
        $this->baseUrl = $baseUrl;
        $this->projectId = $projectId;
        $this->privateToken = $privateToken;
    }

    private function buildApiBaseUrl()
    {
        return $this->baseUrl . '/api/v4/projects/' . $this->projectId;
    }

    private function buildApiFileUrl($fileName)
    {
        return $this->buildApiBaseUrl() . '/repository/files/' . str_replace('.','%2E',urlencode($fileName))  . '/raw';
    }

    private function buildApiMergeRequestUrl()
    {
        return $this->buildApiBaseUrl() . '/merge_requests';
    }

    public function getComposerRequiredPackages()
    {
        $result = $this->client->get(
            $this->buildApiFileUrl('composer.json'),
            [
                'headers' => ['PRIVATE-TOKEN' => $this->privateToken],
                'query' => ['ref' => 'master'],
            ]
        );

        $resultObject = json_decode($result->getBody()->getContents());
        return $resultObject;
    }

    public function getComposerLockedPackages()
    {
        $result = $this->client->get(
              $this->buildApiFileUrl('composer.lock'),
            [
                'headers' => ['PRIVATE-TOKEN' => $this->privateToken],
                'query' => ['ref' => 'master'],
            ]
        );

        $resultObject = json_decode($result->getBody()->getContents());
        return $resultObject->packages;
    }

    public function createPullRequest($branchName)
    {
        $result = $this->client->post(
            $this->buildApiMergeRequestUrl(),
            [
                'headers' => ['PRIVATE-TOKEN' => $this->privateToken],
                'form_params'    => [
                    'source_branch'	=> $branchName,
                    'target_branch' => 'master',
                    'title' => 'Update by nightwatch',
                ]
            ]
        );
    }
}