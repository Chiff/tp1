<?php


namespace App\Http\Services;


use App\Dto\CiselnikDTO;
use App\Dto\Notification\MyNotificationsDTO;
use App\Dto\Notification\NotificationDTO;
use App\Models\Notifications;
use Exception;

class NotificationService
{
    private CiselnikService $ciselnikService;

    public function __construct(CiselnikService $ciselnikService,)
    {
        $this->ciselnikService = $ciselnikService;
    }

    public function createNotification(string $content, int $entity_id, CiselnikDTO $ciselnik): bool
    {
        $notification = new Notifications();
        $notification->html_content = $content;
        $notification->entity_id = $entity_id;
        $notification->entity_type = $ciselnik->id;

        return $notification->save();
    }

    public function createNotificationForUser(string $content, int $user_id): bool
    {
        $ciselnikDto = $this->ciselnikService->getType('ENTITY_TYPE', 'User')[0];
        return $this->createNotification($content, $user_id, $ciselnikDto);
    }

    public function createNotificationForTeam(string $content, int $team_id): bool
    {
        $ciselnikDto = $this->ciselnikService->getType('ENTITY_TYPE', 'Team')[0];
        return $this->createNotification($content, $team_id, $ciselnikDto);
    }

    public function createNotificationForEvent(string $content, int $event_id): bool
    {
        $ciselnikDto = $this->ciselnikService->getType('ENTITY_TYPE', 'Event')[0];
        return $this->createNotification($content, $event_id, $ciselnikDto);
    }

    public function mapNotification($notification):NotificationDTO
    {
        $notificationDto = new NotificationDTO();

        $notificationDto->entity_type = $notification->entity_type;
        $notificationDto->entity_id = $notification->entity_id;
        $notificationDto->html_content = $notification->html_content;
        $notificationDto->created_at = $notification->created_at;

        return $notificationDto;
    }

    public function getNotifications(string $entity_type, int $entity_id): MyNotificationsDTO
    {
        $ciselnikDto = $this->ciselnikService->getType('ENTITY_TYPE', $entity_type)[0];

        $notifications = Notifications::whereEntityType($ciselnikDto->id)->get();
        $my_notif_dto = new MyNotificationsDTO();
        foreach($notifications as $notification) {

            if($notification->entity_id == $entity_id) {
                array_push($my_notif_dto->notifications, $this->mapNotification($notification));
            }
        }

        return $my_notif_dto;
    }

    public function myNotifications(): MyNotificationsDTO
    {
        // 1) route /api/notifications/{entity_type}/{entity_id} - napr. vrati notifikacie pre EVENT s id=5 || vrati NotificationDTO[]
        // 2) metoda NotificationService::createNotif

        $ciselnikDto = $this->ciselnikService->getType('ENTITY_TYPE', 'User')[0];

        $notifications = Notifications::whereEntityType($ciselnikDto->id)->get();

        $my_notif_dto = new MyNotificationsDTO();
        foreach($notifications as $notification) {

            if($notification->entity_id == auth()->id()) {
                array_push($my_notif_dto->notifications, $this->mapNotification($notification));
            }
        }
        return $my_notif_dto;

    }

}