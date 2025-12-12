<?php

namespace App\Controllers;

use App\Config\Config;
use App\Clients\AmoCrmV4Client;
use App\Helpers\JsonResponse;
use App\Services\AmoCrmServices;

class AmoCrmController
{
    protected string $subDomain;
    protected string $clientId;
    protected string $clientSecret;
    protected string $code;
    protected string $redirectUrl;
    protected object $amoV4Client;
    protected object $services;
    protected int $statusIdUnsorted = 82027874;
    protected int $statusIdWaitingClient = 82088246;
    protected int $statusIdClientConfirmed = 82027406;

    public function __construct()
    {
        $this->subDomain = Config::get('SUB_DOMAIN');
        $this->clientId = Config::get('CLIENT_ID');
        $this->clientSecret = Config::get('CLIENT_SECRET');
        $this->code = Config::get('CODE');
        $this->redirectUrl = Config::get('REDIRECT_URL_ONE');

        try {
            $this->amoV4Client = new AmoCrmV4Client($this->subDomain, $this->clientId, $this->clientSecret, $this->code, $this->redirectUrl);

        } catch(Exception $e) {
            var_dump($e);
        }
        $this->services = new AmoCrmServices($this->amoV4Client);
    }

    public function addToClientWaitingList(): void
    {
        $leads = $this->amoV4Client->GETRequestApi("leads");
        //найти несортированные(задачи)
        $unsortedList = $this->services->searchByStatus($leads['_embedded']['leads'], $this->statusIdUnsorted);
        //найти сделки по условию
        $searchedLeads = $this->services->getByConditionPrice($unsortedList, '>', 5000);
        $count = count($searchedLeads);
        if($count > 0){
            foreach ($searchedLeads as $item){
                //выполнить перемещение
                $this->services->dragToStage($item, $this->statusIdWaitingClient);
            }
            JsonResponse::success("Найдено сделок: {$count}. Сделкам присвоен статус 'Ожидание клиента'");
        } else {
            JsonResponse::success("Сделок для переопределения статуса не найдено.");
        }
    }

    public function copyLeadsAtStageClientConfirmed()
    {
        //получить все сделки
        $leads = $this->amoV4Client->GETRequestApi("leads");
        //найти сделки на этапе “Клиент подтвердил”
        $confirmedList = $this->services->searchByStatus($leads['_embedded']['leads'], $this->statusIdClientConfirmed);
        //найти сделки по условию
        $searchedLeadList = $this->services->getByConditionPrice($confirmedList, '=', 4999);

        // создать сделки
        foreach ($searchedLeadList as $lead){
            $copyLead = $this->services->copyLead($lead, $this->statusIdWaitingClient);

            $addLead = $this->amoV4Client->POSTRequestApi("leads",[$copyLead]);
            $idNewLead = $addLead['_embedded']['leads'][0]['id'];

            //получить примечания сделки
            $getNotes = $this->amoV4Client->GETRequestApi("leads/{$lead['id']}/notes");
            $notesList = $getNotes['_embedded']['notes'];
            //скопировать примечания
            $copyNotesList = $this->services->copyNotes($notesList, $idNewLead);
            //добавить примечания к новой сделке
            $this->amoV4Client->POSTRequestApi("leads/notes", $copyNotesList);

            //получить все задачи
            $tasks = $this->amoV4Client->GETRequestApi("tasks");
            // выбрать задачи для сделки
            $tasksList = $this->services->getTasksForLead($tasks, $lead['id']);
            //скопировать сделки
            $copyTasksList = $this->services->copyTasks($tasksList, $idNewLead);
            //добавить задачи к новой сделке
            $this->amoV4Client->POSTRequestApi("tasks", $copyTasksList);

        }
        $count = count($searchedLeadList);
        JsonResponse::success("Найдено сделок: {$count}, со статусом 'Клиент подтвердил'. Сделки скопированы и присвоен статус 'Ожидание клиента'");
    }
}
