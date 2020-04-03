<?php declare(strict_types=1);

namespace App;

use Scherzo\HttpException;

class Hello {
    public function sayHelloTo($req) {
        $name = $req->params->get('name');
        if ($name === 'nobody') {
            throw (new HttpException(404, "Cannot find $name to say hello to"))
                ->setInfo('name', $name)
                ->setCode('PersonNotFound');
        }
        return ['message' => 'Hello', 'name' => $name];
    }

    public function sayHello() {
        return ['message' => 'Hello World'];
    }
}
