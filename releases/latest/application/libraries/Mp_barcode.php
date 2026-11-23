<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Barcode helpers shared by product validation and artwork downloads. */
class Mp_barcode {
	public function is_valid_ean13($value)
	{
		$value = (string)$value;
		if (!preg_match('/^[0-9]{13}$/', $value)) return false;
		$sum = 0;
		for ($i = 0; $i < 12; $i++) $sum += (int)$value[$i] * (($i % 2) ? 3 : 1);
		return ((10 - ($sum % 10)) % 10) === (int)$value[12];
	}

	public function render($value, $type, $format)
	{
		$type = $type === 'ean13' ? 'ean13' : 'code128';
		$format = strtolower((string)$format);
		$renderers = array('svg' => 'svg', 'png' => 'image', 'pdf' => 'pdf');
		if (!isset($renderers[$format])) return false;
		if ($type === 'ean13' && !$this->is_valid_ean13($value)) return false;
		$options = array(
			'text' => (string)$value, 'withQuietZones' => true,
			'providedChecksum' => $type === 'ean13',
			'drawText' => true, 'stretchText' => false,
			'barHeight' => $format === 'png' ? 220 : 110,
			'barThinWidth' => $format === 'png' ? 4 : 1,
		);
		if ($format === 'svg') $options['fontSize'] = 10;
		if ($format === 'pdf') {
			if (!class_exists('Dompdf\\Dompdf')) return false;
			$pngOptions = $options;
			$pngOptions['barThinWidth'] = 4;
			$pngOptions['barHeight'] = 220;
			$pngOptions['drawText'] = false;
			ob_start();
			\Laminas\Barcode\Barcode::render($type, 'image', $pngOptions);
			$png = ob_get_clean();
			$dompdf = new \Dompdf\Dompdf();
			$dompdf->loadHtml('<!doctype html><html><head><meta charset="utf-8"><style>@page{margin:0}body{margin:0;padding:2mm 5mm;text-align:center}img{display:block;width:40mm;height:auto;margin:0 auto}.digits{font:9pt Arial,sans-serif;letter-spacing:1pt;margin-top:.5mm}</style></head><body><img alt="EAN-13 barcode" src="data:image/png;base64,'.base64_encode($png).'" /><div class="digits">'.htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8').'</div></body></html>');
			$dompdf->setPaper(array(0, 0, 141.73, 85.04));
			$dompdf->render();
			header('Content-Type: application/pdf');
			echo $dompdf->output();
			return true;
		}
		return \Laminas\Barcode\Barcode::render($type, $renderers[$format], $options);
	}
}
