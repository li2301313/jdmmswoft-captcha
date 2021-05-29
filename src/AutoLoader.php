<?php declare(strict_types=1);

namespace Jdmm\Captcha;

use Swoft\Helper\ComposerJSON;
use Swoft\SwoftComponent;
use function dirname;

/**
 * Class AutoLoader
 *
 * @since 2.0
 */
class AutoLoader extends SwoftComponent
{
    /**
     * Get namespace and dirs
     *
     * @return array
     */
    public function getPrefixDirs(): array
    {
        return [
            __NAMESPACE__ => __DIR__,
            'Jdmm\\Captcha' => dirname(dirname(dirname(__DIR__))) . '/jdmmswoft/captcha/src'
        ];
    }

    /**
     * @return array
     */
    protected function metadata(): array
    {
        $jsonFile = dirname(__DIR__) . '/composer.json';

        return ComposerJSON::open($jsonFile)->getMetadata();
    }
}
