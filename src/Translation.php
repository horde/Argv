<?php
declare(strict_types=1);
/**
 * Copyright 2010-2017 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (BSD). If you
 * did not receive this file, see http://www.horde.org/licenses/bsd.
 *
 * @package  Argv
 * @author   Jan Schneider <jan@horde.org>
 * @category Horde
 * @license  http://www.horde.org/licenses/bsd BSD
 */
namespace Horde\Argv;
use Horde\Translation\Autodetect;
/**
 * Horde_Argv_Translation is the translation wrapper class for Horde_Argv.
 *
 * @author    Jan Schneider <jan@horde.org>
 * @package   Argv
 * @category  Horde
 * @copyright 2010-2017 Horde LLC
 * @license   http://www.horde.org/licenses/bsd BSD
 */
class Translation extends Autodetect
{
    /**
     * The translation domain
     *
     * @var string
     */
    protected static string $domain = 'Horde_Argv';

    /**
     * The absolute PEAR path to the translations for the default gettext handler.
     *
     * @var string
     */
    protected static string $pearDirectory = '@data_dir@';
}
