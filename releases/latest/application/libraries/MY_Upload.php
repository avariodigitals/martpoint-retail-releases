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
	 * PHP 7.4's bundled magic database may report AVIF as octet-stream.
	 * Recognise its bounded FileTypeBox, never trust the browser's MIME type
	 * and never allow application/octet-stream globally in the MIME map.
	 * This recognises the container; it does not decode/transcode AV1 pixels.
	 * https://aomediacodec.github.io/av1-avif/#avif-brand
	 */
	public function is_allowed_filetype($ignore_mime = FALSE)
	{
		if (strtolower($this->file_ext) === '.avif')
		{
			$handle = @fopen($this->file_temp, 'rb');
			if ($handle === FALSE) return FALSE;
			try
			{
				$stat = fstat($handle);
				$header = fread($handle, 16);
				if (strlen($header) !== 16 || substr($header, 4, 4) !== 'ftyp') return FALSE;
				$size = unpack('Nsize', substr($header, 0, 4))['size'];
				if ($size < 16 || $size > 4096 || $size % 4 !== 0 || $size > $stat['size'] - 8) return FALSE;
				$brands = [substr($header, 8, 4)];
				if ($size > 16)
				{
					$compatible = fread($handle, $size - 16);
					if (strlen($compatible) !== $size - 16) return FALSE;
					$brands = array_merge($brands, str_split($compatible, 4));
				}
				if (!in_array('avif', $brands, TRUE) && !in_array('avis', $brands, TRUE)) return FALSE;
				// Reject truncated/malformed top-level boxes, not just renamed files.
				$offset = $size;
				$metadata = FALSE;
				for ($boxes = 0; $offset < $stat['size'] && $boxes < 1024; $boxes++)
				{
					if (fseek($handle, $offset) !== 0) return FALSE;
					$box = fread($handle, 8);
					if (strlen($box) !== 8) return FALSE;
					$length = unpack('Nsize', substr($box, 0, 4))['size'];
					$headerSize = 8;
					if ($length === 1)
					{
						$extended = fread($handle, 8);
						if (strlen($extended) !== 8) return FALSE;
						$parts = unpack('Nhigh/Nlow', $extended);
						if ($parts['high'] !== 0) return FALSE;
						$length = $parts['low']; $headerSize = 16;
					}
					if ($length === 0) $length = $stat['size'] - $offset;
					if ($length < $headerSize || $length > $stat['size'] - $offset) return FALSE;
					$type = substr($box, 4, 4);
					if (($type === 'meta' || $type === 'moov') && $length > $headerSize + 4) $metadata = TRUE;
					$offset += $length;
				}
				if (!$metadata || $offset !== $stat['size']) return FALSE;
				$this->file_type = 'image/avif';
			}
			finally { fclose($handle); }
		}
		return parent::is_allowed_filetype($ignore_mime);
	}

	/** Enforce configured dimensions even when PHP 7.4 cannot decode AVIF. */
	public function is_allowed_dimensions()
	{
		if ($this->file_type !== 'image/avif') return parent::is_allowed_dimensions();
		if (!$this->max_width && !$this->max_height && !$this->min_width && !$this->min_height) return TRUE;
		// File-size validation runs before dimensions. Bound this read as well.
		$data = @file_get_contents($this->file_temp, FALSE, NULL, 0, 8388609);
		if ($data === FALSE || strlen($data) > 8388608) return FALSE;
		$dimensions = []; $count = 0;
		// Walk actual box boundaries, never search compressed image bytes for
		// an "ispe" string. Still-image extents live in meta/iprp/ipco/ispe.
		$walk = function($start, $end, $depth) use (&$walk, &$dimensions, &$count, $data) {
			if ($depth > 4) return FALSE;
			while ($start < $end) {
				if (++$count > 4096 || $end - $start < 8) return FALSE;
				$size = unpack('Nsize', substr($data, $start, 4))['size'];
				$type = substr($data, $start + 4, 4); $header = 8;
				if ($size === 1) {
					if ($end - $start < 16) return FALSE;
					$parts = unpack('Nhigh/Nlow', substr($data, $start + 8, 8));
					if ($parts['high'] !== 0) return FALSE;
					$size = $parts['low']; $header = 16;
				}
				if ($size === 0) $size = $end - $start;
				if ($size < $header || $size > $end - $start) return FALSE;
				$payload = $start + $header;
				if ($type === 'ispe') {
					if ($size !== $header + 12 || substr($data, $payload, 4) !== "\0\0\0\0") return FALSE;
					$d = unpack('Nwidth/Nheight', substr($data, $payload + 4, 8));
					if (!$d['width'] || !$d['height']) return FALSE;
					$dimensions[] = $d;
				} elseif (in_array($type, ['meta','iprp','ipco'], TRUE)) {
					if ($type === 'meta') $payload += 4;
					if ($payload > $start + $size || !$walk($payload, $start + $size, $depth + 1)) return FALSE;
				}
				$start += $size;
			}
			return TRUE;
		};
		if (!$walk(0, strlen($data), 0) || !$dimensions) return FALSE;
		foreach ($dimensions as $d) {
			if (($this->max_width && $d['width'] > $this->max_width) || ($this->max_height && $d['height'] > $this->max_height)
				|| ($this->min_width && $d['width'] < $this->min_width) || ($this->min_height && $d['height'] < $this->min_height)) return FALSE;
		}
		return TRUE;
	}

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
