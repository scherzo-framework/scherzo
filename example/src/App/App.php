<?php

namespace App;

use Scherzo\App as ScherzoApp;
use Scherzo\HttpException;

class App extends ScherzoApp {

    public function bootstrap() {
        $container = $this->container;

        $this->useDispatch();

        $this->use(function (\Throwable $err, $req, $res) use ($container) {
            if (is_a($err, HttpException::class)) {
                // We don't need to log HttpExceptions.
                throw $err;
            }
            $container->log->log('debug', 'Unexpected exception', [
                'message' => $err->getMessage(),
                'string' => (string)$err,
            ]);
            throw new HttpException(500, $err->getMessage(), $err);
        });
        
        $this->use(function (HttpException $err, $req, $res) use ($container) {
            $container->log->log('debug', 'Handling an HttpException');
        
            $res->setStatusCode($err->getStatusCode());
            $code = $err->getCode();
            $status = $err->getStatusCode();
            $info = $err->getInfo();
        
            $error = [
                'code' => $code,
                'status' => $status,
            ];
        
            if ($status === 500 && $req->isProduction()) {
                $error['message'] = $code;
            } else {
                $error['message'] = $err->getMessage();
            }
        
            if ($info) {
                $error['info'] = $info;
            }
        
            $res->setData();
            $res->setError($error);
        });
        
        $this->use(function ($req, $res) use ($container) {
            $res->addJson('meta', 'log', $container->log->getLog());
        });
        
        $this->use(function ($req, $res) use ($container) {
            $res->prepare($req);
            $res->send();
        });
    }
}
