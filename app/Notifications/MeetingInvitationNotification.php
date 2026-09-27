<?php

namespace App\Notifications;

use App\Models\Meeting;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MeetingInvitationNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Meeting $meeting,
        public string $action = 'invited' // invited, updated, cancelled
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $dateFormatted = $this->meeting->date ? $this->meeting->date->format('M d, Y') : '';
        $timeFormatted = $this->meeting->formatted_time_range;
        $organizerName = $this->meeting->organizer?->name ?? 'System';

        $title = match ($this->action) {
            'updated' => _trans('common.Meeting Rescheduled / Updated'),
            'cancelled' => _trans('common.Meeting Cancelled'),
            default => _trans('common.Meeting Invitation'),
        };

        $message = match ($this->action) {
            'updated' => _trans('common.The meeting ":title" was updated by :organizer. Scheduled for :date (:time).', [
                'title' => $this->meeting->title,
                'organizer' => $organizerName,
                'date' => $dateFormatted,
                'time' => $timeFormatted,
            ]),
            'cancelled' => _trans('common.The meeting ":title" scheduled for :date was cancelled.', [
                'title' => $this->meeting->title,
                'date' => $dateFormatted,
            ]),
            default => _trans('common.:organizer invited you to ":title" on :date from :time.', [
                'organizer' => $organizerName,
                'title' => $this->meeting->title,
                'date' => $dateFormatted,
                'time' => $timeFormatted,
            ]),
        };

        return [
            'meeting_id' => $this->meeting->id,
            'title' => $title,
            'message' => $message,
            'action' => $this->action,
            'action_url' => route('meetings.show', $this->meeting->id),
        ];
    }
}
