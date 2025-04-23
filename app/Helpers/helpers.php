<?php

use Illuminate\Support\Facades\Log;
use App\Models\Notification;

if (!function_exists('pre')) {
    function pre($data)
    {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
        exit;
    }
}

if (!function_exists('sendNotification')) {
    function sendNotification($type, $content, $notifyTo, $createdBy, $details = null, $priority = 0)
    {
        Notification::create([
            'type' => $type,
            'content' => $content,
            'details' => $details,
            'notify_to' => $notifyTo,
            'created_by' => $createdBy,
            'priority' => $priority,
        ]);
    }
}

if (!function_exists('getNotificationsByNotifyTo')) {
    function getNotificationsByNotifyTo($userId, $limit = 50)
    {
        return Notification::where('notify_to', $userId)
                           ->orderBy('created_at', 'desc')
                           ->limit($limit)
                           ->get();
    }
}

if (!function_exists('getNotificationsByCreatedBy')) {
    function getNotificationsByCreatedBy($userId, $limit = 50)
    {
        return Notification::where('created_by', $userId)
                           ->orderBy('created_at', 'desc')
                           ->limit($limit)
                           ->get();
    }
}
