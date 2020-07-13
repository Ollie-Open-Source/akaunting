<?php

namespace App\Http\ViewComposers;

use App\Traits\Modules;
use Route;
use Illuminate\View\View;

class Notifications
{
    use Modules;

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        // No need to add suggestions in console
        if (app()->runningInConsole() || !config('app.installed') || !user()) {
            return;
        }

        if (!$path = Route::current()->uri()) {
            return;
        }

        if (!$notifications = $this->getNotifications($path)) {
            return;
        }

        // Push to a stack
        foreach ($notifications as $notification) {
            $path = str_replace('/', '#', $notification->path);

            $message = str_replace('#path#', $path, $notification->message);
            $message = str_replace('#token#', csrf_token(), $message);
            $message = str_replace('#url#', url('/'), $message);

            if (!setting('notifications.' . $notification->path . '.' . $notification->id . '.status', 1)) {
                continue;
            }

            $view->getFactory()->startPush('content_content_start', $message);
        }
    }
}
