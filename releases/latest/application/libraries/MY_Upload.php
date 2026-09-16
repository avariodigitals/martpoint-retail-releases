<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Extends CI_Upload so upload directories are created on demand.
 *
 * Stock CI3 fails with "The upload path does not appear to be valid."
 * whenever the configured upload_path directory is missing (common on
 * fresh deployments where empty upload dirs are not shipped). Creating
 * it here fixes every upload site at once instead of guarding each call.
 */
class MY_Upload extends CI_Upload {

	/**
	 * Verifies that it is a valid upload path with proper permissions,
	 * creating the directory when it does not exist.
	 *
	 * @return	bool
	 */
	public function validate_upload_path()
	{
		if ($this->upload_path === '')
		{
			$this->set_error('upload_no_filepath', 'error');
			return FALSE;
		}

		if (realpath($this->upload_path) !== FALSE)
		{
			$this->upload_path = str_replace('\\', '/', realpath($this->upload_path));
		}

		if ( ! is_dir($this->upload_path))
		{
			@mkdir($this->upload_path, 0775, TRUE);
		}

		if ( ! is_dir($this->upload_path))
		{
			$this->set_error('upload_no_filepath', 'error');
			return FALSE;
		}

		if ( ! is_really_writable($this->upload_path))
		{
			$this->set_error('upload_not_writable', 'error');
			return FALSE;
		}

		$this->upload_path = preg_replace('/(.+?)\/*$/', '\\1/',  $this->upload_path);
		return TRUE;
	}
}
