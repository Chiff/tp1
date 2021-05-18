<?php


namespace App\Http\Services;

use App\Dto\Event\EventDTO;
use App\Dto\Event\MyEventsDTO;
use App\Dto\EventTeam\EventTeamDTO;
use App\Dto\User\UserDTO;
use App\Http\Services\AS\EventAS;
use App\Http\Services\AS\UserTeamAS;
use App\Http\Services\Netgrif\AuthenticationService;
use App\Http\Services\Netgrif\TaskService;
use App\Http\Services\Netgrif\UserService;
use App\Http\Services\Netgrif\WorkflowService;
use App\Models\Event;
use App\Models\EventTeam;
use App\Models\Netgrif\CaseResource;
use App\Models\Netgrif\EmbededCases;
use App\Models\Netgrif\MessageResource;
use App\Models\Netgrif\TasksReferences;
use App\Models\Team;
use App\Models\User;
use App\Models\UserTeam;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use JsonMapper\JsonMapper;


class EventService
{
    private AuthenticationService $auth;
    private JsonMapper $jsonMapper;
    private WorkflowService $workflowService;
    private UserService $userService;
    private TaskService $taskService;
    private EventAS $eventAS;
    private UserTeamAS $userTeamAS;


    public function __construct(
        AuthenticationService $authService,
        WorkflowService $workflowService,
        UserService $userService,
        TaskService $taskService,
        JsonMapper $mapper,
        EventAS $eventAS,
        UserTeamAS $userTeamAS,
    )
    {
        $this->jsonMapper = $mapper;
        $this->workflowService = $workflowService;
        $this->auth = $authService;
        $this->userService = $userService;
        $this->taskService = $taskService;
        $this->eventAS = $eventAS;
        $this->userTeamAS = $userTeamAS;
    }

    public function deleteEvent($id): MessageResource
    {
        return $this->workflowService->deleteCaseUsingDELETE($id);
    }

    public function showUserEvents(): EmbededCases
    {
        $user = $this->userService->getLoggedUserUsingGET();
        $authorId = $user->id;
        return $this->workflowService->findAllByAuthorUsingPOST($authorId);
    }

    public function showAllEvents(): EmbededCases
    {
        return $this->workflowService->getAllUsingGET();
    }

    public function showOneEvent($id): CaseResource
    {
        return $this->workflowService->getOneUsingGET($id);
    }

    public function createOneEvent(Request $request): EventDTO
    {
        $dto = new EventDTO();
        $dto->user_id = auth()->id();
        $this->jsonMapper->mapObjectFromString(json_encode($request->toArray()), $dto);

        if ($dto->min_teams > $dto->max_teams) {
            throw new Exception("Max pocet timov je mensi ako minimalny", 500);
        }
        if ($dto->min_team_members > $dto->max_team_members) {
            throw new Exception("Max pocet hracov v time je mensi ako minimalny", 500);
        }

        $todayDatee = date('Y-m-dTH:m:i');

        $dt = new DateTime($todayDatee);
        $todayDate = Carbon::instance($dt);

        if (($todayDate > $dto->registration_end)) {
            throw new Exception("Koniec registracie je pred sucastnym datumom", 500);
        }
        if (($todayDate > $dto->event_end)) {
            throw new Exception("Koniec udalosti je pred sucastnym datumom", 500);
        }
        if (($todayDate > $dto->event_start)) {
            throw new Exception("Zaciatok udalosti je pred sucastnym datumom", 500);
        }
        // TODO - 13/05/2021 - NA TOTO POZOR!
        app('db')->transaction(function () use ($dto) {
            $createdEvent = $this->eventAS->createEvent($dto);

            if (!$createdEvent) {
                throw new Exception("could not create", 500);
            }

            $netId = env('API_INTERES_EVENT_NET_ID');
            $title = "event";
            $netgrifEvent = $this->workflowService->createCaseUsingPOST($netId, $title);

            if (!$netgrifEvent) {
                throw new Exception("could not create netgrif event", 500);
            }

            $createdEvent->ext_id = $netgrifEvent->stringId;
            $createdEvent->save();

            $caseId = $netgrifEvent->stringId;
            //TODO backend doplnit transitionId do databazy
            $tasks = $this->taskService->searchTask(array(
                'case' => array('id' => $caseId),
                'transitionId' => env('API_INTERES_EVENT_CREATE_EVENT_TRANSITION')
            ));
            $taskId = $tasks->_embedded->tasks[0]->stringId;

            $this->taskService->assignUsingGET($taskId);

            $taskData =
                '{
                    "300": {
                        "type": "number",
                        "value": ' . $dto->max_teams . '
                    },
                    "400": {
                        "type": "number",
                        "value": ' . $dto->min_teams . '
                    },
                    "podujatie_nazov": {
                        "type": "text",
                        "value": "' . $dto->name . '"
                    }
            }';

            $this->taskService->setTaskData($taskId, $taskData);
            $this->taskService->finishUsingGET($taskId);

            $this->jsonMapper->mapObjectFromString($createdEvent, $dto);
        });

        return $dto;
    }

    /**
     * @return EventDTO[]
     */
    public function getPublicEvents(): array
    {
        $todayDate = date('Y/m/d H:m:i');
        $events = Event::where('registration_end', '>=', $todayDate)->get();

        return $this->mapEventsWithOwner($events);
    }

    public function getMyEvents(bool $onlyActive = false): MyEventsDTO
    {
        $user_id = auth()->id();
        $user = User::findOrFail($user_id);

        $response = new MyEventsDTO();

        $response->owned = $this->getOwnedEvents($user, $onlyActive);
        $response->upcoming = $this->getUpcomingEvents($user, $onlyActive);

        return $response;
    }

    /**
     * @param User $user
     * @param bool $onlyActive
     * @return EventDTO[]
     */
    private function getOwnedEvents(User $user, $onlyActive = true): array
    {
        $events = $user->ownEvents();

        if ($onlyActive) {
            $todayDate = date('Y/m/d H:m:i');
            $events->where('event_end', '>=', $todayDate);
        }

        return $this->mapEventsWithOwner($events->get());
    }

    /**
     * @param User $user
     * @param bool $onlyActive
     * @return EventDTO[]
     */
    private function getUpcomingEvents(User $user, $onlyActive = true): array
    {
        $teams = UserTeam::whereUserId($user->id)->select('team_id');
        $eventTeam = EventTeam::select('event_id')->whereIn('team_id', $teams);
        $events = Event::select()->whereIn('id', $eventTeam->select('event_id'));

        if ($onlyActive) {
            $todayDate = date('Y/m/d H:m:i');
            $events->where('event_end', '>=', $todayDate);
        }

        return $this->mapEventsWithOwner($events->get());
    }

    private function mapEventWithOwner(Event $model, EventDTO $dto = null): EventDTO
    {
        $eventDTO = new EventDTO();

        // v pripade ze nechceme vytvorit novy ale len doplnit uz existujuci
        if ($dto) {
            $eventDTO = $dto;
        }

        $this->jsonMapper->mapObjectFromString($model->toJson(), $eventDTO);

        $user = new UserDTO();
        $userModel = User::whereId($model->user_id)->first();
        $this->jsonMapper->mapObjectFromString($userModel->toJson(), $user);

        $eventDTO->owner = $user;

        return $eventDTO;
    }

    /**
     * @param Collection $events
     * @return EventDTO[]
     */
    private function mapEventsWithOwner(Collection $events): array
    {
        $result = [];

        foreach ($events as $event) {
            $eventDTO = $this->mapEventWithOwner($event);
            array_push($result, $eventDTO);
        }

        return $result;
    }

    public function getEventActiveTasks($eventCaseId): TasksReferences
    {
        $tasks = $this->taskService->getTasksOfCaseUsingGET($eventCaseId);

        $isOwner = $this->isEventOwner(auth()->id(), $eventCaseId);
        $hasTeamOnEvent = $this->hasTeamOnEvent(auth()->id(), $eventCaseId);

        $allowForTeamOwner = ["66"];
        $allowForUnknown = ["1"];
        $adminTransIds = ["5", "6", "7", "96"];


        $result = new TasksReferences();
        $result->taskReference = [];

        foreach ($tasks->taskReference as $task) {
            if ($isOwner && in_array($task->transitionId, $adminTransIds)) {
                array_push($result->taskReference, $task);
            }

            if ($hasTeamOnEvent == true && in_array($task->transitionId, $allowForTeamOwner)) {
                array_push($result->taskReference, $task);
            }

            if ($hasTeamOnEvent == false && in_array($task->transitionId, $allowForUnknown)) {
                array_push($result->taskReference, $task);
            }
        }

        return $result;
    }

    public function hasTeamOnEvent($user_id, $eventCaseId): bool
    {
        $has_team = false;

        $userTeams = Team::whereUserId($user_id)->select('id')->get()->toArray();
        $eventTeams = Event::whereExtId($eventCaseId)->first()->teams;

        $tmp = [];
        foreach ($userTeams as $ut) {
            array_push($tmp, $ut["id"]);
        }

        foreach ($eventTeams as $eventTeam) {
            if (in_array($eventTeam->id, $tmp)) {
                return true;
            }
        }

        return $has_team;
    }

    public function isEventOwner($user_id, $eventCaseId): bool
    {
        $is_owner = false;

        $event = Event::whereExtId($eventCaseId)->first();
        if ($event->user_id == $user_id) return true;

        return $is_owner;
    }

    public function mapEventWithTeams($event, $dto): EventDTO
    {

        $dto = $this->mapEventWithOwner($event, $dto);
        $teams = [];
        foreach ($event->teams as $team) {
            $teamDTO = $this->userTeamAS->mapTeamDetail($team);
            array_push($teams, $teamDTO);
        }
        $this->jsonMapper->mapObjectFromString($event->toJson(), $dto);
        $dto->teams_on_event = $teams;

        return $dto;
    }

    public function mapEventWithTeamOnEvent($event, $dto): EventDTO
    {
        $collection = EventTeam::whereEventId($event->id)->get();

        $eventTeams = [];
        foreach ($collection as $model) {
            $dto2 = new EventTeamDTO();
            $this->jsonMapper->mapObjectFromString($model->toJson(), $dto2);
            array_push($eventTeams, $dto2);
        }

        $dto->event_team_info = $eventTeams;
        return $dto;
    }

    public function getFullEventById($id): EventDTO
    {
        $event = Event::whereId($id)->first();

        if (!$event || !$id) {
            throw new Exception('not found', 404);
        }

        $dto = new EventDTO();
        $dto = $this->mapEventWithTeams($event, $dto);
        $dto = $this->mapEventWithTeamOnEvent($event, $dto);
        $dto->available_transitions = $this->getEventActiveTasks($event->ext_id);

        return $dto;
    }

    public function runTask($taskId): MessageResource
    {
        return $this->taskService->runTask($taskId);
    }

}
