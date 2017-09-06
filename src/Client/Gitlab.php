<?php
namespace NightWatch\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

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

    private function buildApibrancheUrl($branchName)
    {
        return $this->buildApiBaseUrl() . '/repository/branches/' . $branchName;
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

        return json_decode($result->getBody()->getContents());
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

        return json_decode($result->getBody()->getContents())->packages;
    }

    public function createPullRequest($branchName, $title = '', $description= '')
    {
        $this->client->post(
            $this->buildApiMergeRequestUrl(),
            [
                'headers' => ['PRIVATE-TOKEN' => $this->privateToken],
                'form_params'    => [
                    'source_branch'	=> $branchName,
                    'target_branch' => 'master',
                    'title' => 'NightWatch Bot : ' . $title,
                    'description' => ''
                ]
            ]
        );
    }

    public function doesBranchExist($branchName)
    {
        try {
            $this->client->get(
                $this->buildApibrancheUrl(urlencode($branchName)),
                [
                    'headers' => ['PRIVATE-TOKEN' => $this->privateToken],
                ]
            );
        }
        catch (ClientException $exception) {
            if ($exception->getCode() == 404) {
                return false;
            }
            throw $exception;
        }
        return true;
    }
}
