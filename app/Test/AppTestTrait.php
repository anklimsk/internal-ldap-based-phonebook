<?php
/**
 * This file is the trait AppTestTrait
 *
 * CakeExtendTest: Extended test tools for CakePHP.
 * @copyright Copyright 2016, Andrey Klimov.
 * @package app.Test
 */

/**
 * AppTestTrait trait
 *
 */
trait AppTestTrait
{

    /**
     * Information about the logged in user.
     *
     * @var array
     */
    protected $userInfo = [
        'user' => 'Хвощинский В.В.',
        'role' => USER_ROLE_USER,
        'prefix' => '',
        'id' => '7',
        'includedFields' => [
            CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => '8c149661-7215-47de-b40e-35320a1ea508'
        ]
    ];

    /**
     * Add Extended fields to application configuration
     *
     * @param array|string $fields List of fields for adding.
     * @return bool Success.
     */
    public function addExtendedFields($fields = [])
    {
        if (empty($fields)) {
            return false;
        }

        if (!is_array($fields)) {
            $fields = [$fields];
        }

        $cfgPath = PROJECT_CONFIG_NAME . '.ExtendedFields';
        $extendedFields = Configure::read($cfgPath);
        $extendedFields = unserialize($extendedFields);
        $extendedFields = array_values(array_unique(array_merge($extendedFields, $fields)));

        return Configure::write($cfgPath, serialize($extendedFields));
    }

    /**
     * Create image file JPEG or GIF
     *
     * @param string $path Path to file
     * @param int $width Width of image
     * @param int $height Height of image
     * @param bool $isJpeg If True, create JPEG, otherwise create GIF file.
     * @return string|bool Return path to file, or False on failure.
     */
    public function createTestPhotoFile($path = null, $width = PHOTO_WIDTH, $height = PHOTO_HEIGHT, $isJpeg = true)
    {
        if (empty($path) || !file_exists($path)) {
            return false;
        }

        $imageFile = $path . 'test.' . ($isJpeg ? 'jpg' : 'gif');
        $im = imagecreatetruecolor($width, $height);
        $textColor = imagecolorallocate($im, 255, 255, 255);
        imagestring($im, 1, 5, 5, 'Test', $textColor);
        $imageFunc = ($isJpeg ? 'imagejpeg' : 'imagegif');
        if (!$imageFunc($im, $imageFile)) {
            return false;
        }

        imagedestroy($im);

        return $imageFile;
    }
}
