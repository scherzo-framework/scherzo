<?php declare(strict_types=1);

namespace App;

use Scherzo\HttpException;

class Index {
    public function getIndexPage($req) {
        ob_start();
        include __DIR__.'/index.tpl.php';
        return ob_get_clean();
    }
}
