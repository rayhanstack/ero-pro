<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeaveStatusNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public LeaveRequest $leaveRequest,
        public string $action = 'updated'
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
        $statusLabel = $this->leaveRequest->status->label();
        $typeName = $this->leaveRequest->leaveType?->name ?? 'Leave';

        return [
            'leave_request_id' => $this->leaveRequest->id,
            'title' => "Leave Request {$statusLabel}",
            'message' => "Your {$typeName} request for {$this->leaveRequest->days} day(s) ({$this->leaveRequest->from_date->format('M d, Y')} - {$this->leaveRequest->to_date->format('M d, Y')}) has been {$this->leaveRequest->status->value}.",
            'status' => $this->leaveRequest->status->value,
            'remark' => $this->leaveRequest->remark,
            'action_url' => route('leaves.my'),
        ];
    }
}
