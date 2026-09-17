<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Extends CI_Image_lib so the GD driver can process WebP images.
 *
 * Stock CI3 only supports image types 1 (GIF), 2 (JPEG) and 3 (PNG),
 * so uploaded .webp files failed thumbnail creation with
 * imglib_unsupported_imagecreate. WebP is IMAGETYPE_WEBP (18) and is
 * handled here via imagecreatefromwebp()/imagewebp() when the GD
 * build supports it.
 */
class MY_Image_lib extends CI_Image_lib {

	/**
	 * initialize image preferences
	 *
	 * Stock CI3 only recognises jpg/jpeg/gif/png when deciding whether
	 * new_image contains a filename (system/libraries/Image_lib.php),
	 * so a '.webp' destination would be mistaken for a directory. We
	 * present a .png name to the parent and restore the real .webp
	 * destination afterwards.
	 *
	 * @param	array
	 * @return	bool
	 */
	public function initialize($props = array())
	{
		$new_image = isset($props['new_image']) ? $props['new_image'] : $this->new_image;
		$webp_new_image = '';

		if (is_string($new_image) && preg_match('/\.webp$/i', $new_image))
		{
			$webp_new_image = $new_image;
			$swapped = substr($new_image, 0, -4).'png';

			if (isset($props['new_image']))
			{
				$props['new_image'] = $swapped;
			}
			else
			{
				$this->new_image = $swapped;
			}
		}

		$result = parent::initialize($props);

		if ($result === TRUE && $webp_new_image !== '')
		{
			$this->new_image     = $webp_new_image;
			$this->dest_image    = basename($webp_new_image);
			$this->full_dst_path = preg_replace('/\.png$/i', '.webp', $this->full_dst_path);
		}

		return $result;
	}

	/**
	 * Image Process Using GD/GD2
	 *
	 * Identical to the parent implementation, except alpha preservation
	 * is also applied to WebP images so transparent .webp sources do not
	 * end up with a black background after resize/crop.
	 *
	 * @param	string
	 * @return	bool
	 */
	public function image_process_gd($action = 'resize')
	{
		$v2_override = FALSE;

		// If the target width/height match the source, AND if the new file name is not equal to the old file name
		// we'll simply make a copy of the original with the new name... assuming dynamic rendering is off.
		if ($this->dynamic_output === FALSE && $this->orig_width === $this->width && $this->orig_height === $this->height)
		{
			if ($this->source_image !== $this->new_image && @copy($this->full_src_path, $this->full_dst_path))
			{
				chmod($this->full_dst_path, $this->file_permissions);
			}

			return TRUE;
		}

		// Let's set up our values based on the action
		if ($action === 'crop')
		{
			// Reassign the source width/height if cropping
			$this->orig_width  = $this->width;
			$this->orig_height = $this->height;

			// GD 2.0 has a cropping bug so we'll test for it
			if ($this->gd_version() !== FALSE)
			{
				$gd_version = str_replace('0', '', $this->gd_version());
				$v2_override = ($gd_version == 2);
			}
		}
		else
		{
			// If resizing the x/y axis must be zero
			$this->x_axis = 0;
			$this->y_axis = 0;
		}

		// Create the image handle
		if ( ! ($src_img = $this->image_create_gd()))
		{
			return FALSE;
		}

		if ($this->image_library === 'gd2' && function_exists('imagecreatetruecolor'))
		{
			$create	= 'imagecreatetruecolor';
			$copy	= 'imagecopyresampled';
		}
		else
		{
			$create	= 'imagecreate';
			$copy	= 'imagecopyresized';
		}

		$dst_img = $create($this->width, $this->height);

		if ($this->image_type === 3 OR $this->image_type === IMAGETYPE_WEBP) // png and webp preserve transparency
		{
			imagealphablending($dst_img, FALSE);
			imagesavealpha($dst_img, TRUE);
		}

		$copy($dst_img, $src_img, 0, 0, $this->x_axis, $this->y_axis, $this->width, $this->height, $this->orig_width, $this->orig_height);

		// Show the image
		if ($this->dynamic_output === TRUE)
		{
			$this->image_display_gd($dst_img);
		}
		elseif ( ! $this->image_save_gd($dst_img)) // Or save it
		{
			return FALSE;
		}

		// Kill the file handles
		imagedestroy($dst_img);
		imagedestroy($src_img);

		if ($this->dynamic_output !== TRUE)
		{
			chmod($this->full_dst_path, $this->file_permissions);
		}

		return TRUE;
	}

	/**
	 * Create image - GD
	 *
	 * Same as parent, with an added WebP (IMAGETYPE_WEBP) branch.
	 *
	 * @param	string
	 * @param	string
	 * @return	resource
	 */
	public function image_create_gd($path = '', $image_type = '')
	{
		if ($path === '')
		{
			$path = $this->full_src_path;
		}

		if ($image_type === '')
		{
			$image_type = $this->image_type;
		}

		if ($image_type === IMAGETYPE_WEBP)
		{
			if ( ! function_exists('imagecreatefromwebp'))
			{
				$this->set_error('imglib_unsupported_imagecreate');
				return FALSE;
			}

			return imagecreatefromwebp($path);
		}

		return parent::image_create_gd($path, $image_type);
	}

	/**
	 * Write image file to disk - GD
	 *
	 * Same as parent, with an added WebP branch.
	 *
	 * @param	resource
	 * @return	bool
	 */
	public function image_save_gd($resource)
	{
		if ($this->image_type === IMAGETYPE_WEBP)
		{
			if ( ! function_exists('imagewebp'))
			{
				$this->set_error('imglib_unsupported_imagecreate');
				return FALSE;
			}

			if ( ! @imagewebp($resource, $this->full_dst_path, (int) $this->quality))
			{
				$this->set_error('imglib_save_failed');
				return FALSE;
			}

			return TRUE;
		}

		return parent::image_save_gd($resource);
	}

	/**
	 * Dynamically outputs an image
	 *
	 * Same as parent, with an added WebP branch.
	 *
	 * @param	resource
	 * @return	void
	 */
	public function image_display_gd($resource)
	{
		if ($this->image_type === IMAGETYPE_WEBP)
		{
			header('Content-Disposition: filename='.$this->source_image.';');
			header('Content-Type: '.$this->mime_type);
			header('Content-Transfer-Encoding: binary');
			header('Last-Modified: '.gmdate('D, d M Y H:i:s', time()).' GMT');
			imagewebp($resource, NULL, (int) $this->quality);
			return;
		}

		parent::image_display_gd($resource);
	}

	/**
	 * Get image properties
	 *
	 * Same as parent, but reports the correct 'image/webp' mime type
	 * instead of falling back to 'image/jpg'.
	 *
	 * @param	string
	 * @param	bool
	 * @return	mixed
	 */
	public function get_image_properties($path = '', $return = FALSE)
	{
		if ($path === '')
		{
			$path = $this->full_src_path;
		}

		if ( ! file_exists($path))
		{
			$this->set_error('imglib_invalid_path');
			return FALSE;
		}

		$vals = getimagesize($path);
		if ($vals === FALSE)
		{
			$this->set_error('imglib_invalid_image');
			return FALSE;
		}

		$types = array(1 => 'gif', 2 => 'jpeg', 3 => 'png', IMAGETYPE_WEBP => 'webp');
		$mime = isset($types[$vals[2]]) ? 'image/'.$types[$vals[2]] : 'image/jpg';

		if ($return === TRUE)
		{
			return array(
				'width'      => $vals[0],
				'height'     => $vals[1],
				'image_type' => $vals[2],
				'size_str'   => $vals[3],
				'mime_type'  => $mime
			);
		}

		$this->orig_width  = $vals[0];
		$this->orig_height = $vals[1];
		$this->image_type  = $vals[2];
		$this->size_str    = $vals[3];
		$this->mime_type   = $mime;

		return TRUE;
	}
}
