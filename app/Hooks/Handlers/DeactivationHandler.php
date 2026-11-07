<?php

namespace NinjaPhotoFeed\App\Hooks\Handlers;

class DeactivationHandler
{
    public function handle()
    {
	    wp_clear_scheduled_hook('npfeed_cron_job');
	    wp_clear_scheduled_hook('npfeed_do_email_report_scheduled_tasks');
	    wp_clear_scheduled_hook('npfeed_scheduled_twicedaily');
	    wp_clear_scheduled_hook('npfeed_scheduled_weekly');
    }
}
