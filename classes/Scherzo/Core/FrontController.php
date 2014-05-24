<?php

namespace Scherzo\Core;


class FrontController extends Service
{
    public function execute()
    {
        $this->roadmap();
    }

    protected function roadmap() {
        echo '<h3>Roadmap to v0.1</h3>';
        echo '<ul>';
        echo '<li><del>Bootstrap</del></li>';
        echo '<li><del>Autoloader (Quick Start install)</del></li>';
        echo '<li>Autoloader (Composer install)</li>';
        echo '<li><del>Dependency injection container</del></li>';
        echo '<li><del>Errors, exceptions and shutdown</del></li>';
        echo '<li><del>Debug</del></li>';
        echo '<li>FrontController</li>';
        echo '<li>Request</li>';
        echo '<li>HttpRequest</li>';
        echo '<li>ErrorController</li>';
        echo '<li>DefaultController</li>';
        echo '<li>HttpResponse</li>';
        echo '</ul>';

        echo '<h3>Roadmap to v0.9</h3>';
        echo '<ul>';
        echo '<li>Logging</li>';
        echo '<li>Views</li>';
        echo '<li>Twig</li>';
        echo '<li>Filestore</li>';
        echo '<li>Sessions</li>';
        echo '</ul>';
    }
}
