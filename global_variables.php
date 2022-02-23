<?php
$current_directory = getcwd();
$base_directory = dirname(__FILE__);

$dotenv_values = [
	"SITE_TITLE" => [
		"ask" => "Your site title: \n",
		"base" => "change-site-title"
	],
	"PASSWORD" => [
		"base" => "test",
		"new" => (new GeneratePassword())->generateStrongPassword()
	],
	"THEME_SLUG" => [
		"ask" => "Your site theme slug: \n",
		"base" => "my-new-theme-slug"
	],
	"THEME_NAME" => [
		"ask" => "Your site theme name: \n",
		"base" => "my-new-theme-name"
	]
];

$last_wp_theme = 'twentytwentytwo';

$noiza_theme = 'git@github.com:NoizaDev/_s.git';

$base_search_replace_cmd = 'find . -name "*.php" -print | xargs sed -i "" "s/';

$success_message = "Complete! WordPress has been successfully copied to /var/www/html";

$file_successfully_copied = false;