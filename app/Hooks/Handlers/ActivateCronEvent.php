<?php

namespace NinjaPhotoFeed\App\Hooks\Handlers;

use NinjaPhotoFeed\Framework\Support\Arr;

class ActivateCronEvent
{
	public function activate()
    {
		if (!wp_next_scheduled('npfeed_cron_job')) {
			wp_schedule_event(time(), 'hourly', 'npfeed_cron_job');
		}

        $emailReportHook = 'npfeed_do_email_report_scheduled_tasks';
        if (!wp_next_scheduled($emailReportHook)) {
            wp_schedule_event(time(), 'daily', $emailReportHook);
        }

        $twicedailyHook = 'npfeed_scheduled_twicedaily';
        if (!wp_next_scheduled($twicedailyHook)) {
            wp_schedule_event(time(), 'twicedaily', $twicedailyHook);
        }

        $weeklyHook = 'npfeed_scheduled_weekly';
        if (!wp_next_scheduled($weeklyHook)) {
            wp_schedule_event(time(), '1week', $weeklyHook);
        }
    }
}
