<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\ActivityLogBundle\Admin;

use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Navigation\Navigation;
use Sulu\Bundle\AdminBundle\Navigation\NavigationItem;

/**
 * Integrates activity-log-bundle into sulu-admin.
 */
class ActivityLogAdmin extends Admin
{
    /**
     * @param string $title
     */
    public function __construct($title)
    {
        $rootNavigationItem = new NavigationItem($title);

        $section = new NavigationItem('navigation.modules');

        $rootNavigationItem->addChild($section);

        $activityLog = new NavigationItem('navigation.activity_log');
        $activityLog->setAction('activity-log');
        $activityLog->setIcon('list-alt');

        $section->addChild($activityLog);

        $this->setNavigation(new Navigation($rootNavigationItem));
    }

    /**
     * {@inheritdoc}
     */
    public function getJsBundleName()
    {
        return 'suluactivitylog';
    }
}
