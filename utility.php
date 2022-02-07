<?php

function prompt_user() : void {
	global $dotenv_values;
	foreach ($dotenv_values as $key => $value) {
		$line = readline($value["ask"]);
		$line = check_user_input($line, $value["ask"]);
		$dotenv_values[$key]["new"] = $line;
	}
}

function check_user_input(string $input, string $ask) : string {
	if (! empty(trim($input))) {
		return trim($input);
	}
	if (strpos($ask, '(do not leave the field empty)') === false ) {
		$ask = str_replace(':', ' (do not leave the field empty):', $ask);	
	}
	$line = readline($ask);
	return check_user_input($line, $ask);
}

function check_and_replace_values_from_user_input(string $line) : string {
	global $dotenv_values;

	foreach ($dotenv_values as $key => $value) {
		if (strpos($line, $key) === false) {
			continue;
		}
		$line = str_replace($value["base"], $value["new"], $line);
	}
	return $line;
}

function get_base_files() : void {
	global $dotenv_values;
	global $current_directory;
	global $base_directory;

	prompt_user($dotenv_values);

	$handle = fopen($base_directory . '/env', 'r+');
	$writing = fopen($current_directory . '/.env', 'w+');

	if ($handle) {
		while ( ($buffer = fgets($handle)) !== false ) {
			$line = check_and_replace_values_from_user_input($buffer);
			fwrite($writing, $line);
		}
	}
	fclose($handle);
	fclose($writing);

	$result = file_get_contents($current_directory . '/.env');
	echo $result . "\n";

	copy($base_directory . '/docker-compose.yml', $current_directory . '/docker-compose.yml');
}

function copy_wordpress_files() : void {
	echo "Copying files ...";
	while ($file_successfully_copied === false) {
		sleep(10);
		$logs = shell_exec('docker-compose logs');
		if (strpos($logs, $success_message) !== false) {
			echo "\033[92m done \033[0m \n";
			$file_successfully_copied = true;
		}
	}
}

function get_docker_wp_cli() : string {
	$result = shell_exec('docker ps');

	$regex = '/([0-9a-z]+)   ([a-z:0-9\.]+) /mU';
	preg_match_all($regex, $result, $matches, PREG_SET_ORDER);

	foreach ($matches as $match) {
		if (strpos($match[2], 'wordpress') !== false) {
			$wordpress_container_id = $match[1];
		}
		if (strpos($match[2], 'mysql') !== false) {
			$mysql_container_id = $match[1];
		}
	}

	$wp_cli = "docker run -it --rm \
	--volumes-from " . $wordpress_container_id . " \
	--network container:" . $wordpress_container_id . " \
	-e WORDPRESS_DB_USER=site-builder \
	-e WORDPRESS_DB_PASSWORD=test \
	-e WORDPRESS_DB_NAME=" . $_ENV['THEME_SLUG'] . " \
	-e WORDPRESS_DB_HOST=" . $mysql_container_id . " \
	wordpress:cli ";

	return $wp_cli;
}

function install_wordpress() : void {
	$query = 'wp core install --path="/var/www/html" --url="http://' . $_ENV['URL'] . '" --title="' . $_ENV['SITE_TITLE'] . '" --admin_user=' . $_ENV['ADMIN'] . ' --admin_password=' . $_ENV['PASSWORD'] . ' --admin_email=' . $_ENV['ADMIN_MAIL'];

	$wp_cli = get_docker_wp_cli();
	$install_result = shell_exec($wp_cli . $query);

	echo $install_result;
}

function remove_themes() : void {
	global $last_wp_theme;
	$remove_theme_result = shell_exec('
		shopt -s extglob;
		rm -rf !("' . $last_wp_theme . '");
		shopt -u extglob
		');

	echo $remove_theme_result;
}

function download_and_rename_theme() : void {
	global $noiza_theme;
	$clone_noiza_theme_result = shell_exec('
		git clone --branch main ' . $noiza_theme
	);

	echo $clone_noiza_theme_result;

	rename('_s', $_ENV['THEME_SLUG']);
}

function get_replace_cases() : array {
	return $replace_cases = [
		"'_s'" => str_replace('_', '-', "'" . $_ENV['THEME_SLUG'] . "'"),
		"_s_" => str_replace('-', '_', $_ENV['THEME_SLUG'] . "_"),
		" _s" => str_replace('-', '_', " " . $_ENV['THEME_SLUG']),
		"_s-" => str_replace('_', '-', $_ENV['THEME_SLUG']),
		"_S_" => str_replace('-', '_', strtoupper($_ENV['THEME_SLUG'])),
		"\\\"_s\\\"" => str_replace('-', '_', "\\\"" . $_ENV['THEME_SLUG'] . "\\\"")
	];
}

function search_and_replace_strings_in_theme() : void {
	global $base_search_replace_cmd;
	$iteration = 0;
	foreach (get_replace_cases() as $key => $value) {
		$iteration++;
		if ($iteration === 3) {
			shell_exec('sed -i "" "s/Theme Name: _s/Theme Name: ' . $_ENV['THEME_NAME'] . '/g" style*.css');
			shell_exec('sed -i "" "s/Theme URI: https:\/\/underscores.me\//Theme URI: https:\/\/noiza.com/g" style*.css');
			shell_exec('sed -i "" "s/Author: Automattic/Author: ' . $_ENV['THEME_AUTHOR'] . '/g" style*.css');
			shell_exec('sed -i "" "s/Author URI: https:\/\/automattic.com\//Author URI: https:\/\/noiza.com/g" style*.css');
			shell_exec('sed -i "" "s/Text Domain: _s/Text Domain: ' . str_replace('-', '_', $_ENV['THEME_SLUG']) . '/g" style*.css');
		}
		shell_exec($base_search_replace_cmd . $key . '/' . $value . '/g"');
	}

	shell_exec('
		git add --all;
		git commit -m "Personalise general theme for current development";
		git remote remove origin
		');
}

function install_noiza_custom_theme() : void {
	chdir('wordpress/wp-content/themes/');

	remove_themes();
	download_and_rename_theme();

	chdir($_ENV['THEME_SLUG']);

	search_and_replace_strings_in_theme();

	$wp_cli = get_docker_wp_cli();

	$result = shell_exec($wp_cli . 'wp theme activate ' . $_ENV['THEME_SLUG']);
	echo $result;
}