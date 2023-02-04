<?php

namespace TestFixtures;

use Scherzo\Request;

class MockController
{
    public function getIndex(Request $request): array
    {
        $id = $request->route->getInt('id');

        return [
            'data' => [
                'id' => $id,
                'name' => "Item $id",
            ],
        ];
    }

    public function divideByZero(): void
    {
        $error = 1 / 0;
    }
}
