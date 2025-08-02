<?php
namespace Jaca\Data;

use Jaca\Http\RouteInfo;
use Jaca\View\Helper\DataHelper;

/**
 * Class Data
 *
 * Container class for shared data used across the application,
 * including routing information and helper utilities.
 *
 * @package Jaca\Data
 */
class Data
{
    /**
     * The current route information.
     *
     * @var RouteInfo|null
     */
    public ?RouteInfo $routeInfo = null;

    /**
     * Helper instance for data manipulation and presentation.
     *
     * @var DataHelper|null
     */
    public ?DataHelper $helper = null;
}
