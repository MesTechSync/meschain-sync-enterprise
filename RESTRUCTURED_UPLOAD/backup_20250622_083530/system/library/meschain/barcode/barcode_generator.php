<?php

/**
 * MesChain Barcode Generator
 *
 * @author MesChain Development Team
 * @version 1.0.0
 * @since OpenCart 4.0.2.3
 */

namespace Meschain\Barcode;

class BarcodeGenerator
{

    private $width;
    private $height;
    private $font_size;
    private $show_text;
    private $margin;
    private $color_bars;
    private $color_background;
    private $color_text;

    // Barcode types
    const BARCODE_TYPES = [
        'EAN13' => 'EAN-13',
        'EAN8' => 'EAN-8',
        'CODE128' => 'Code 128',
        'CODE39' => 'Code 39',
        'UPCA' => 'UPC-A',
        'UPCE' => 'UPC-E',
        'ITF14' => 'ITF-14',
        'QR' => 'QR Code'
    ];

    // EAN-13 patterns
    private $ean13_first_digit = [
        0 => [0, 0, 0, 0, 0, 0],
        1 => [0, 0, 1, 0, 1, 1],
        2 => [0, 0, 1, 1, 0, 1],
        3 => [0, 0, 1, 1, 1, 0],
        4 => [0, 1, 0, 0, 1, 1],
        5 => [0, 1, 1, 0, 0, 1],
        6 => [0, 1, 1, 1, 0, 0],
        7 => [0, 1, 0, 1, 0, 1],
        8 => [0, 1, 0, 1, 1, 0],
        9 => [0, 1, 1, 0, 1, 0]
    ];

    private $ean13_left_odd = [
        0 => [0, 0, 0, 1, 1, 0, 1],
        1 => [0, 0, 1, 1, 0, 0, 1],
        2 => [0, 0, 1, 0, 0, 1, 1],
        3 => [0, 1, 1, 1, 1, 0, 1],
        4 => [0, 1, 0, 0, 0, 1, 1],
        5 => [0, 1, 1, 0, 0, 0, 1],
        6 => [0, 1, 0, 1, 1, 1, 1],
        7 => [0, 1, 1, 1, 0, 1, 1],
        8 => [0, 1, 1, 0, 1, 1, 1],
        9 => [0, 0, 0, 1, 0, 1, 1]
    ];

    private $ean13_left_even = [
        0 => [0, 1, 0, 0, 1, 1, 1],
        1 => [0, 1, 1, 0, 0, 1, 1],
        2 => [0, 0, 1, 1, 0, 1, 1],
        3 => [0, 1, 0, 0, 0, 0, 1],
        4 => [0, 0, 1, 1, 1, 0, 1],
        5 => [0, 1, 1, 1, 0, 0, 1],
        6 => [0, 0, 0, 0, 1, 0, 1],
        7 => [0, 0, 1, 0, 0, 0, 1],
        8 => [0, 0, 0, 1, 0, 0, 1],
        9 => [0, 0, 1, 0, 1, 1, 1]
    ];

    private $ean13_right = [
        0 => [1, 1, 1, 0, 0, 1, 0],
        1 => [1, 1, 0, 0, 1, 1, 0],
        2 => [1, 1, 0, 1, 1, 0, 0],
        3 => [1, 0, 0, 0, 0, 1, 0],
        4 => [1, 0, 1, 1, 1, 0, 0],
        5 => [1, 0, 0, 1, 1, 1, 0],
        6 => [1, 0, 1, 0, 0, 0, 0],
        7 => [1, 0, 0, 0, 1, 0, 0],
        8 => [1, 0, 0, 1, 0, 0, 0],
        9 => [1, 1, 1, 0, 1, 0, 0]
    ];

    public function __construct($config = [])
    {
        $this->width = $config['width'] ?? 200;
        $this->height = $config['height'] ?? 80;
        $this->font_size = $config['font_size'] ?? 12;
        $this->show_text = $config['show_text'] ?? true;
        $this->margin = $config['margin'] ?? 10;
        $this->color_bars = $config['color_bars'] ?? [0, 0, 0]; // Black
        $this->color_background = $config['color_background'] ?? [255, 255, 255]; // White
        $this->color_text = $config['color_text'] ?? [0, 0, 0]; // Black
    }

    /**
     * Generate barcode image
     */
    public function generate($code, $type = 'EAN13', $format = 'PNG')
    {
        switch (strtoupper($type)) {
            case 'EAN13':
                return $this->generateEAN13($code, $format);
            case 'EAN8':
                return $this->generateEAN8($code, $format);
            case 'CODE128':
                return $this->generateCode128($code, $format);
            case 'CODE39':
                return $this->generateCode39($code, $format);
            case 'QR':
                return $this->generateQRCode($code, $format);
            default:
                throw new \Exception('Unsupported barcode type: ' . $type);
        }
    }

    /**
     * Generate EAN-13 barcode
     */
    private function generateEAN13($code, $format)
    {
        // Validate and format code
        $code = $this->validateEAN13($code);
        if (!$code) {
            throw new \Exception('Invalid EAN-13 code');
        }

        // Create image
        $image = imagecreate($this->width, $this->height);

        // Set colors
        $bg_color = imagecolorallocate($image, $this->color_background[0], $this->color_background[1], $this->color_background[2]);
        $bar_color = imagecolorallocate($image, $this->color_bars[0], $this->color_bars[1], $this->color_bars[2]);
        $text_color = imagecolorallocate($image, $this->color_text[0], $this->color_text[1], $this->color_text[2]);

        // Fill background
        imagefill($image, 0, 0, $bg_color);

        // Generate barcode pattern
        $pattern = $this->getEAN13Pattern($code);

        // Draw bars
        $bar_width = ($this->width - 2 * $this->margin) / strlen($pattern);
        $bar_height = $this->height - ($this->show_text ? 20 : 10);

        $x = $this->margin;
        for ($i = 0; $i < strlen($pattern); $i++) {
            if ($pattern[$i] == '1') {
                imagefilledrectangle($image, $x, 5, $x + $bar_width - 1, $bar_height, $bar_color);
            }
            $x += $bar_width;
        }

        // Add text
        if ($this->show_text) {
            $text_y = $this->height - 15;
            $text_x = ($this->width - strlen($code) * 8) / 2;
            imagestring($image, 3, $text_x, $text_y, $code, $text_color);
        }

        return $this->outputImage($image, $format);
    }

    /**
     * Generate EAN-8 barcode
     */
    private function generateEAN8($code, $format)
    {
        // Validate and format code
        $code = $this->validateEAN8($code);
        if (!$code) {
            throw new \Exception('Invalid EAN-8 code');
        }

        // Similar to EAN-13 but with 8 digits
        $image = imagecreate($this->width, $this->height);

        $bg_color = imagecolorallocate($image, $this->color_background[0], $this->color_background[1], $this->color_background[2]);
        $bar_color = imagecolorallocate($image, $this->color_bars[0], $this->color_bars[1], $this->color_bars[2]);
        $text_color = imagecolorallocate($image, $this->color_text[0], $this->color_text[1], $this->color_text[2]);

        imagefill($image, 0, 0, $bg_color);

        $pattern = $this->getEAN8Pattern($code);

        $bar_width = ($this->width - 2 * $this->margin) / strlen($pattern);
        $bar_height = $this->height - ($this->show_text ? 20 : 10);

        $x = $this->margin;
        for ($i = 0; $i < strlen($pattern); $i++) {
            if ($pattern[$i] == '1') {
                imagefilledrectangle($image, $x, 5, $x + $bar_width - 1, $bar_height, $bar_color);
            }
            $x += $bar_width;
        }

        if ($this->show_text) {
            $text_y = $this->height - 15;
            $text_x = ($this->width - strlen($code) * 8) / 2;
            imagestring($image, 3, $text_x, $text_y, $code, $text_color);
        }

        return $this->outputImage($image, $format);
    }

    /**
     * Generate Code 128 barcode
     */
    private function generateCode128($code, $format)
    {
        $image = imagecreate($this->width, $this->height);

        $bg_color = imagecolorallocate($image, $this->color_background[0], $this->color_background[1], $this->color_background[2]);
        $bar_color = imagecolorallocate($image, $this->color_bars[0], $this->color_bars[1], $this->color_bars[2]);
        $text_color = imagecolorallocate($image, $this->color_text[0], $this->color_text[1], $this->color_text[2]);

        imagefill($image, 0, 0, $bg_color);

        // Simple Code 128 implementation
        $pattern = $this->getCode128Pattern($code);

        $bar_width = ($this->width - 2 * $this->margin) / strlen($pattern);
        $bar_height = $this->height - ($this->show_text ? 20 : 10);

        $x = $this->margin;
        for ($i = 0; $i < strlen($pattern); $i++) {
            if ($pattern[$i] == '1') {
                imagefilledrectangle($image, $x, 5, $x + $bar_width - 1, $bar_height, $bar_color);
            }
            $x += $bar_width;
        }

        if ($this->show_text) {
            $text_y = $this->height - 15;
            $text_x = ($this->width - strlen($code) * 6) / 2;
            imagestring($image, 2, $text_x, $text_y, $code, $text_color);
        }

        return $this->outputImage($image, $format);
    }

    /**
     * Generate Code 39 barcode
     */
    private function generateCode39($code, $format)
    {
        $image = imagecreate($this->width, $this->height);

        $bg_color = imagecolorallocate($image, $this->color_background[0], $this->color_background[1], $this->color_background[2]);
        $bar_color = imagecolorallocate($image, $this->color_bars[0], $this->color_bars[1], $this->color_bars[2]);
        $text_color = imagecolorallocate($image, $this->color_text[0], $this->color_text[1], $this->color_text[2]);

        imagefill($image, 0, 0, $bg_color);

        $pattern = $this->getCode39Pattern($code);

        $bar_width = ($this->width - 2 * $this->margin) / strlen($pattern);
        $bar_height = $this->height - ($this->show_text ? 20 : 10);

        $x = $this->margin;
        for ($i = 0; $i < strlen($pattern); $i++) {
            if ($pattern[$i] == '1') {
                imagefilledrectangle($image, $x, 5, $x + $bar_width - 1, $bar_height, $bar_color);
            }
            $x += $bar_width;
        }

        if ($this->show_text) {
            $text_y = $this->height - 15;
            $text_x = ($this->width - strlen($code) * 6) / 2;
            imagestring($image, 2, $text_x, $text_y, '*' . $code . '*', $text_color);
        }

        return $this->outputImage($image, $format);
    }

    /**
     * Generate QR Code (basic implementation)
     */
    private function generateQRCode($code, $format)
    {
        // This is a placeholder for QR code generation
        // In a real implementation, you would use a QR code library
        $image = imagecreate($this->width, $this->height);

        $bg_color = imagecolorallocate($image, $this->color_background[0], $this->color_background[1], $this->color_background[2]);
        $bar_color = imagecolorallocate($image, $this->color_bars[0], $this->color_bars[1], $this->color_bars[2]);

        imagefill($image, 0, 0, $bg_color);

        // Draw a simple pattern as placeholder
        $size = min($this->width, $this->height) - 20;
        $start_x = ($this->width - $size) / 2;
        $start_y = ($this->height - $size) / 2;

        // Draw border
        imagerectangle($image, $start_x, $start_y, $start_x + $size, $start_y + $size, $bar_color);

        // Draw some pattern
        for ($i = 0; $i < 10; $i++) {
            for ($j = 0; $j < 10; $j++) {
                if (($i + $j) % 2 == 0) {
                    $x = $start_x + ($i * $size / 10);
                    $y = $start_y + ($j * $size / 10);
                    imagefilledrectangle($image, $x, $y, $x + $size / 10, $y + $size / 10, $bar_color);
                }
            }
        }

        return $this->outputImage($image, $format);
    }

    /**
     * Validate and format EAN-13 code
     */
    private function validateEAN13($code)
    {
        $code = preg_replace('/[^0-9]/', '', $code);

        if (strlen($code) == 12) {
            $code .= $this->calculateEAN13CheckDigit($code);
        } elseif (strlen($code) == 13) {
            if ($code[12] != $this->calculateEAN13CheckDigit(substr($code, 0, 12))) {
                return false;
            }
        } else {
            return false;
        }

        return $code;
    }

    /**
     * Validate and format EAN-8 code
     */
    private function validateEAN8($code)
    {
        $code = preg_replace('/[^0-9]/', '', $code);

        if (strlen($code) == 7) {
            $code .= $this->calculateEAN8CheckDigit($code);
        } elseif (strlen($code) == 8) {
            if ($code[7] != $this->calculateEAN8CheckDigit(substr($code, 0, 7))) {
                return false;
            }
        } else {
            return false;
        }

        return $code;
    }

    /**
     * Calculate EAN-13 check digit
     */
    private function calculateEAN13CheckDigit($code)
    {
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += $code[$i] * (($i % 2 == 0) ? 1 : 3);
        }
        return (10 - ($sum % 10)) % 10;
    }

    /**
     * Calculate EAN-8 check digit
     */
    private function calculateEAN8CheckDigit($code)
    {
        $sum = 0;
        for ($i = 0; $i < 7; $i++) {
            $sum += $code[$i] * (($i % 2 == 0) ? 3 : 1);
        }
        return (10 - ($sum % 10)) % 10;
    }

    /**
     * Get EAN-13 barcode pattern
     */
    private function getEAN13Pattern($code)
    {
        $pattern = '101'; // Start guard

        $first_digit = intval($code[0]);
        $left_pattern = $this->ean13_first_digit[$first_digit];

        // Left side
        for ($i = 1; $i <= 6; $i++) {
            $digit = intval($code[$i]);
            if ($left_pattern[$i - 1] == 0) {
                $pattern .= implode('', $this->ean13_left_odd[$digit]);
            } else {
                $pattern .= implode('', $this->ean13_left_even[$digit]);
            }
        }

        $pattern .= '01010'; // Center guard

        // Right side
        for ($i = 7; $i <= 12; $i++) {
            $digit = intval($code[$i]);
            $pattern .= implode('', $this->ean13_right[$digit]);
        }

        $pattern .= '101'; // End guard

        return $pattern;
    }

    /**
     * Get EAN-8 barcode pattern
     */
    private function getEAN8Pattern($code)
    {
        $pattern = '101'; // Start guard

        // Left side
        for ($i = 0; $i <= 3; $i++) {
            $digit = intval($code[$i]);
            $pattern .= implode('', $this->ean13_left_odd[$digit]);
        }

        $pattern .= '01010'; // Center guard

        // Right side
        for ($i = 4; $i <= 7; $i++) {
            $digit = intval($code[$i]);
            $pattern .= implode('', $this->ean13_right[$digit]);
        }

        $pattern .= '101'; // End guard

        return $pattern;
    }

    /**
     * Get Code 128 pattern (simplified)
     */
    private function getCode128Pattern($code)
    {
        // This is a simplified implementation
        // In a real implementation, you would use the full Code 128 character set
        $pattern = '11010010000'; // Start B

        foreach (str_split($code) as $char) {
            $ascii = ord($char);
            if ($ascii >= 32 && $ascii <= 126) {
                // Simple pattern for demonstration
                $pattern .= '1010101010';
            }
        }

        $pattern .= '1100011101011'; // Stop

        return $pattern;
    }

    /**
     * Get Code 39 pattern (simplified)
     */
    private function getCode39Pattern($code)
    {
        $patterns = [
            '0' => '101001101101',
            '1' => '110100101011',
            '2' => '101100101011',
            '3' => '110110010101',
            '4' => '101001101011',
            '5' => '110100110101',
            '6' => '101100110101',
            '7' => '101001011011',
            '8' => '110100101101',
            '9' => '101100101101',
            'A' => '110101001011',
            'B' => '101101001011',
            'C' => '110110100101',
            'D' => '101011001011',
            'E' => '110101100101',
            'F' => '101101100101',
            'G' => '101010011011',
            'H' => '110101001101',
            'I' => '101101001101',
            'J' => '101011001101',
            'K' => '110101010011',
            'L' => '101101010011',
            'M' => '110110101001',
            'N' => '101011010011',
            'O' => '110101101001',
            'P' => '101101101001',
            'Q' => '101010110011',
            'R' => '110101011001',
            'S' => '101101011001',
            'T' => '101011011001',
            'U' => '110010101011',
            'V' => '100110101011',
            'W' => '110011010101',
            'X' => '100101101011',
            'Y' => '110010110101',
            'Z' => '100110110101',
            ' ' => '100101011011',
            '*' => '100101101101'
        ];

        $pattern = $patterns['*']; // Start

        foreach (str_split(strtoupper($code)) as $char) {
            if (isset($patterns[$char])) {
                $pattern .= '0' . $patterns[$char];
            }
        }

        $pattern .= '0' . $patterns['*']; // End

        return $pattern;
    }

    /**
     * Output image in specified format
     */
    private function outputImage($image, $format)
    {
        ob_start();

        switch (strtoupper($format)) {
            case 'PNG':
                imagepng($image);
                break;
            case 'JPEG':
            case 'JPG':
                imagejpeg($image, null, 90);
                break;
            case 'GIF':
                imagegif($image);
                break;
            default:
                imagepng($image);
        }

        $image_data = ob_get_contents();
        ob_end_clean();

        imagedestroy($image);

        return $image_data;
    }

    /**
     * Generate random EAN-13 code
     */
    public static function generateRandomEAN13($prefix = '123')
    {
        $code = str_pad($prefix, 12, '0', STR_PAD_RIGHT);
        $code = substr($code, 0, 12);

        // Add random digits
        for ($i = strlen($prefix); $i < 12; $i++) {
            $code[$i] = rand(0, 9);
        }

        // Calculate check digit
        $generator = new self();
        $code .= $generator->calculateEAN13CheckDigit($code);

        return $code;
    }

    /**
     * Generate random EAN-8 code
     */
    public static function generateRandomEAN8($prefix = '12')
    {
        $code = str_pad($prefix, 7, '0', STR_PAD_RIGHT);
        $code = substr($code, 0, 7);

        // Add random digits
        for ($i = strlen($prefix); $i < 7; $i++) {
            $code[$i] = rand(0, 9);
        }

        // Calculate check digit
        $generator = new self();
        $code .= $generator->calculateEAN8CheckDigit($code);

        return $code;
    }

    /**
     * Validate barcode
     */
    public function validateBarcode($code, $type)
    {
        switch (strtoupper($type)) {
            case 'EAN13':
                return $this->validateEAN13($code) !== false;
            case 'EAN8':
                return $this->validateEAN8($code) !== false;
            case 'CODE128':
            case 'CODE39':
                return !empty($code);
            default:
                return false;
        }
    }

    /**
     * Get supported barcode types
     */
    public static function getSupportedTypes()
    {
        return self::BARCODE_TYPES;
    }
}
