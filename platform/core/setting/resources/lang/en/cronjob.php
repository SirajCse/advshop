<?php

return [
    'name' => 'Cronjob',
    'description' => 'Set up automated background tasks to keep your website running smoothly.',
    'is_not_ready' => 'Cronjob is not configured yet',
    'is_not_ready_description' => 'Please follow the instructions below to set up the cronjob. This is required for features like abandoned cart reminders, email scheduling, and other automated tasks.',
    'is_working' => 'Cronjob is running properly!',
    'is_not_working' => 'Cronjob has stopped working',
    'is_not_working_description' => 'The cronjob has not run in the last 10 minutes. Please check your server configuration or contact your hosting provider.',
    'last_checked' => 'Last activity: :time',
    'copy_button' => 'Copy Command',
    'what_is' => [
        'title' => 'What is a Cronjob?',
        'description' => 'A cronjob is an automated task that runs in the background on your server. It allows your website to perform important tasks automatically without you having to do anything manually.',
        'examples' => 'Examples',
        'features' => 'Send abandoned cart reminders, process scheduled emails, clean up old data, generate reports, and more.',
    ],
    'command' => [
        'title' => 'Your Cronjob Command',
        'description' => 'Copy this command and add it to your hosting control panel. This command needs to run every minute to keep your automated tasks working.',
    ],
    'setup' => [
        'name' => 'How to Set Up',
        'copied' => 'Copied to clipboard!',
        'choose_hosting' => 'Select your hosting control panel below and follow the step-by-step instructions:',
    ],
    'cpanel' => [
        'step1' => 'Log in to your <strong>cPanel</strong> account',
        'step2' => 'Find and click on <strong>"Cron Jobs"</strong> under the "Advanced" section',
        'step3' => 'Under "Add New Cron Job", select <strong>"Once Per Minute (* * * * *)"</strong> from the timing dropdown',
        'step4' => '<strong>Paste the command</strong> you copied above into the "Command" field',
        'step5' => 'Click <strong>"Add New Cron Job"</strong> to save',
    ],
    'plesk' => [
        'step1' => 'Log in to your <strong>Plesk</strong> control panel',
        'step2' => 'Go to <strong>"Scheduled Tasks"</strong> (or "Cron Jobs")',
        'step3' => 'Click <strong>"Add Task"</strong> or <strong>"Schedule a Task"</strong>',
        'step4' => 'Set the schedule to run <strong>every minute</strong> and paste the command',
        'step5' => 'Click <strong>"OK"</strong> or <strong>"Apply"</strong> to save',
    ],
    'directadmin' => [
        'step1' => 'Log in to your <strong>DirectAdmin</strong> panel',
        'step2' => 'Navigate to <strong>"Advanced Features"</strong> → <strong>"Cron Jobs"</strong>',
        'step3' => 'Click <strong>"Add Cron Job"</strong>',
        'step4' => 'Set all time fields to <strong>*</strong> (every minute) and paste the command',
        'step5' => 'Click <strong>"Add"</strong> to save the cronjob',
    ],
    'ssh' => [
        'step1' => 'Connect to your server via <strong>SSH</strong> using Terminal or PuTTY',
        'step2' => 'Type <code>crontab -e</code> and press Enter to edit the crontab file',
        'step3' => 'Add a new line at the bottom and <strong>paste the command</strong>',
        'step4' => 'Press <strong>Ctrl+X</strong>, then <strong>Y</strong>, then <strong>Enter</strong> to save (for nano editor)',
        'step5' => 'The cronjob is now active and will run every minute',
    ],
    'need_help' => 'Need help? Contact your hosting provider and ask them to set up a cronjob that runs every minute with the command shown above.',
];
