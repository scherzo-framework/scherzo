<?php

namespace Scherzo\Test;

use Scherzo\Request;
use Scherzo\Response;

class TestController
{
    public function getId(Request $request): array
    {
        $id = $request->route->getInt('id');

        return ['data' => ['id' => $id, 'name' => "Item $id"]];
    }

    public function getIndex(Request $request): string
    {
        $id = $request->route->getInt('id');

        return "Hello $id";
    }

    public function getResponseModifier(Request $request, Response $response): array
    {
        $id = $request->route->getInt('id');

        $response->headers->set('x-api-key', 'APPLICATION_SECRET');
        return ['data' => ['id' => $id, 'name' => "Item $id"]];
    }

    public function divideByZero(): void
    {
        $error = 1 / 0;
    }
}
