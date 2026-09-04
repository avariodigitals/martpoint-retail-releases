<?php
class Database {
	public $error = '';

	function check_database_exist_or_not($data){
		$hostname = $data['hostname'];
		$username = $data['username'];
		$password = $data['password'];
		$database_name = $data['database'];

		// Creating a connection
		$conn = @new mysqli($hostname, $username, $password);
		// Check connection
		if ($conn->connect_error) {
		    $this->error = "Could not connect to the database server. Please check your hostname, username and password.";
		    return false;
		}

		$q3=$conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$database_name'");
		if(mysqli_num_rows($q3)>0){
			//Success
			return true;
		}
		return false;
	}
	function create_database($data){
		$mysqli = @new mysqli($data['hostname'],$data['username'],$data['password'],'');
		if(mysqli_connect_errno()){
			$this->error = "Database connection failed. Please verify your hostname, username and password.";
			return false;
		}
		$dbname = $data['database'];
		$mysqli->query("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
		$mysqli->close();
		return true;
	}

	private function _split_sql_statements($sql){
		/*
		 * Split a multi-statement SQL dump into individual statements while
		 * respecting single/double-quoted strings, backtick identifiers and
		 * SQL comments (both -- and /* ... * /). Semicolons inside comments or
		 * quotes are ignored. Returns an array of trimmed SQL statements.
		 */
		$statements = array();
		$current = '';
		$len = strlen($sql);
		$in_quote = false;
		$quote_char = '';
		$in_line_comment = false;
		$in_block_comment = false;
		for ($i = 0; $i < $len; $i++) {
			$char = $sql[$i];
			$next = ($i + 1 < $len) ? $sql[$i + 1] : '';

			if ($in_block_comment) {
				$current .= $char;
				if ($char === '*' && $next === '/') {
					$current .= $next;
					$in_block_comment = false;
					$i++;
				}
				continue;
			}

			if ($in_line_comment) {
				$current .= $char;
				if ($char === "\n") {
					$in_line_comment = false;
				}
				continue;
			}

			if ($in_quote) {
				$current .= $char;
				if ($char === $quote_char && ($i === 0 || $sql[$i - 1] !== '\\')) {
					$in_quote = false;
					$quote_char = '';
				}
				continue;
			}

			if ($char === '/' && $next === '*') {
				$current .= $char . $next;
				$in_block_comment = true;
				$i++;
				continue;
			}

			if ($char === '-' && $next === '-') {
				$current .= $char . $next;
				$in_line_comment = true;
				$i++;
				continue;
			}

			if ($char === "'" || $char === '"' || $char === '`') {
				$current .= $char;
				$in_quote = true;
				$quote_char = $char;
				continue;
			}

			if ($char === ';') {
				$trimmed = trim($current);
				if ($trimmed !== '') {
					$statements[] = $trimmed;
				}
				$current = '';
				continue;
			}

			$current .= $char;
		}
		$trimmed = trim($current);
		if ($trimmed !== '') {
			$statements[] = $trimmed;
		}
		return $statements;
	}

	function create_tables($data){
		// Prevent PHP timeout during large schema import.
		if (function_exists('set_time_limit')) {
			@set_time_limit(0);
		}
		if (function_exists('ini_set')) {
			@ini_set('max_execution_time', '0');
		}

		$schema_dir = __DIR__;
		$schema_files = array(
			$schema_dir . '/db.txt',
			$schema_dir . '/db_install_extensions.sql',
			$schema_dir . '/db_models_schema_part2.sql',
			$schema_dir . '/db_models_schema_part3.sql',
		);

		$response = '';
		foreach ($schema_files as $file) {
			$content = @file_get_contents($file);
			if ($content === false) {
				// Non-critical files are allowed to be absent; the main db.txt must exist.
				if ($file === $schema_dir . '/db.txt') {
					$this->error = "Installer schema file `{$file}` could not be read.";
					return false;
				}
				continue;
			}
			if ($response !== '') {
				$response .= "\n\n";
			}
			$response .= $content;
		}

		$con1 = @mysqli_connect($data['hostname'], $data['username'], $data['password'], $data['database']);
		if (!$con1) {
			$this->error = "Could not connect to the database. Please check your hostname, username, password, and database name.";
			return false;
		}

		// Ensure we can create large tables and receive clear errors.
		// Disable exception-throwing mode so failed optional server-setting queries
		// can be handled gracefully with the existing error-checking pattern.
		mysqli_report(MYSQLI_REPORT_OFF);

		mysqli_query($con1, "SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES'");
		// Relax InnoDB strict mode so that tables with many VARCHAR columns
		// (e.g. db_store) don't fail with "Row size too large" during ALTER
		// TABLE ADD COLUMN. With strict mode OFF, InnoDB stores overflow data
		// off-page instead of rejecting the DDL.
		mysqli_query($con1, "SET SESSION innodb_strict_mode = OFF");
		// innodb_default_row_format is a GLOBAL variable, not SESSION; try to
		// set it, but do not fail if the user lacks the SUPER privilege.
		@mysqli_query($con1, "SET GLOBAL innodb_default_row_format = 'DYNAMIC'");
		mysqli_query($con1, "SET FOREIGN_KEY_CHECKS = 0");

		$statements = $this->_split_sql_statements($response);
		$executed = 0;
		foreach ($statements as $stmt) {
			// Skip empty statements only; comments that prefix real SQL are
			// harmless and must be executed so the DDL/DML after them runs.
			$trimmed = trim($stmt);
			if ($trimmed === '') {
				continue;
			}
			$executed++;
			if (!mysqli_query($con1, $stmt)) {
				$num = mysqli_errno($con1);

				// errno 1068 "Multiple primary key defined": phpMyAdmin exports
				// and migration scripts often include both an inline PRIMARY KEY
				// in CREATE TABLE and a later ALTER TABLE ... ADD PRIMARY KEY.
				// Strip the redundant ADD PRIMARY KEY clause and retry the
				// remaining parts (ADD KEY, MODIFY COLUMN, etc.).
				if ($num === 1068) {
					$stripped = preg_replace('/\bADD\s+PRIMARY\s+KEY\s*\([^)]*\)\s*,?/i', ' ', $stmt);
					// Clean up any leftover double commas / trailing commas left
					// by the removal so the ALTER TABLE syntax stays valid.
					$stripped = preg_replace('/\s*,\s*,/', ',', $stripped);
					$stripped = preg_replace('/,\s*(;?\s*)$/', '$1', $stripped);
					$stripped = trim($stripped);
					// If only "ALTER TABLE `x` ;" remains there is nothing to do.
					if (preg_match('/^ALTER\s+TABLE\s+`?[\w]+`?\s*;?\s*$/i', $stripped)) {
						// Redundant statement; primary key already exists. Skip.
						continue;
					}
					if ($stripped !== '' && strcasecmp($stripped, $stmt) !== 0) {
						if (mysqli_query($con1, $stripped)) {
							continue; // retry succeeded
						}
						// Retry also failed; fall through to report the new error.
						$num = mysqli_errno($con1);
					}
				}

				// errno 1060 "Duplicate column name": schema migration scripts
				// may try to ADD COLUMN that already exists from a CREATE TABLE
				// or a prior migration. Strip the duplicate ADD COLUMN clause(s)
				// and retry the remaining parts.
				if ($num === 1060) {
					// Extract the duplicate column name from the error message.
					$dup_col = '';
					if (preg_match("/Duplicate column name '([^']+)'/i", mysqli_error($con1), $cm)) {
						$dup_col = $cm[1];
					}
					if ($dup_col !== '') {
						// Remove the specific ADD COLUMN clause for the duplicate.
						$stripped = preg_replace(
							'/\bADD\s+COLUMN\s+`?' . preg_quote($dup_col, '/') . '`?\s+[^,;]+,?\s*/i',
							'', $stmt
						);
						$stripped = preg_replace('/\s*,\s*,/', ',', $stripped);
						$stripped = preg_replace('/,\s*(;?\s*)$/', '$1', $stripped);
						$stripped = trim($stripped);
						if (preg_match('/^ALTER\s+TABLE\s+`?[\w]+`?\s*;?\s*$/i', $stripped)) {
							continue; // nothing left to do
						}
						if ($stripped !== '' && strcasecmp($stripped, $stmt) !== 0) {
							if (mysqli_query($con1, $stripped)) {
								continue;
							}
							$num = mysqli_errno($con1);
						}
					}
				}

				$err = mysqli_error($con1);
				// Provide a concise preview of the failing statement.
				$preview = substr($trimmed, 0, 300);
				if (strlen($trimmed) > 300) {
					$preview .= ' ...';
				}
				$this->error = "SQL error #{$num}: {$err}\n\nFailed statement (preview): {$preview}\n\nStatements executed successfully before failure: {$executed}";
				mysqli_query($con1, "SET FOREIGN_KEY_CHECKS = 1");
				mysqli_close($con1);
				return false;
			}
		}

		mysqli_query($con1, "SET FOREIGN_KEY_CHECKS = 1");
		mysqli_close($con1);
		return true;
	}//create_tables() end
	
	function support(){
		return "";
	}
}
