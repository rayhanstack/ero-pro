<?php

namespace App\Notifications;

use App\Models\Meeting;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MeetingReminderNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Meeting $meeting
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
        $timeFormatted = $this->meeting->formatted_time_range;
        $location = $this->meeting->location ?: ($this->meeting->meeting_link ?: _trans('common.Check details'));

        return [
            'meeting_id' => $this->meeting->id,
            'title' => _trans('common.Upcoming Meeting in 30 Minutes'),
            'message' => _trans('common.Reminder: ":title" starts at :time at :location.', [
                'title' => $this->meeting->title,
                'time' => $timeFormatted,
                'location' => $location,
            ]),
            'action_url' => route('meetings.show', $this->meeting->id),
        ];
    }
}
