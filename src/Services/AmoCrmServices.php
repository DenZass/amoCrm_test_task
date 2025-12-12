<?php

namespace App\Services;

class AmoCrmServices
{
    protected object $amoV4Client;
    public function __construct(object $amoV4Client)
    {
        $this->amoV4Client = $amoV4Client;
    }

    // перетащить на этап "Ожидание клиента"
    public function dragToStage(array $item, int $statusId): void
    {
        $array = [
            'status_id' => $statusId,
        ];
        $this->amoV4Client->POSTRequestApi("leads/{$item['id']}", $array, "PATCH");
    }

    //найти сделки по статусу
    public function searchByStatus(array $leadsList, int $statusId): array
    {
        $resultList = [];
        foreach ($leadsList as $item){
            if ($item['status_id'] === $statusId){
                $resultList[] = $item;
            }
        }
        return $resultList;
    }

    //найти сделки по условию
    public function getByConditionPrice(array $leadsList, string $condition, int $number): array
    {
        $resultList = [];
        foreach ($leadsList as $item){
            switch ($condition){
                case '>':
                    if($item['price'] > $number){
                        $resultList[] = $item;
                    }
                    break;
                case '>=':
                    if($item['price'] >= $number){
                        $resultList[] = $item;
                    }
                    break;
                case '=':
                    if($item['price'] === $number){
                        $resultList[] = $item;
                    }
                    break;
            }
        }
        return $resultList;
    }

    //выбрать задачи для конкретной сделки
    public function getTasksForLead(array $allTasksList, int $leadId): array
    {
        $resultList = [];
        foreach ($allTasksList['_embedded']['tasks'] as $item){
            if($item['entity_id'] === $leadId){
                $resultList[] = $item;
            }
        }
        return $resultList;
    }

    //копировать сделку
    public function copyLead(array $lead, int $statusId): array
    {
        $newLead = $lead;
        unset($newLead['id']);
        unset($newLead['loss_reason_id']);
        unset($newLead['_embedded']);
        $newLead['status_id'] = $statusId;
        return $newLead;
    }

    //копировать примечания
    public function copyNotes(array $notesList, int $idNewLead): array
    {
        $resultArray = [];
        foreach ($notesList as $item){
            $newItem = $item;
            unset($newItem['id']);
            unset($newItem['_links']);
            $newItem['entity_id'] = $idNewLead;
            $resultArray[] = $newItem;
        }
        return $resultArray;
    }

    //копировать сделки
    public function copyTasks(array $tasksList, int $idNewLead): array
    {
        $resultArray = [];
        foreach ($tasksList as $task){
            $newTask = $task;
            unset($newTask['id']);
            unset($newTask['_links']);
            $newTask['entity_id'] = $idNewLead;
            if($newTask['text'] === ''){
                $newTask['text'] = '-';
            }
            $resultArray[] = $newTask;
        }
        return $resultArray;
    }
}
