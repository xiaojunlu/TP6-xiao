<?php

namespace app\common\exception;

use Exception;
use think\Response;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\ValidateException;

class ExceptionHandler extends Handle
{
    public function render(Exception $e): Response
    {
        // 参数验证错误
        if ($e instanceof ValidateException) {
            return json($e->getError(), 422);
        }

        $error = array(
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
        );

        //请求异常
        if ($e instanceof HttpException && request()->isAjax()) {
            $statusCode = $this->getStatusCode($e);
            return json(array('error' => $error), $statusCode);
        }

        return parent::render($e);
    }

    private function getStatusCode($exception)
    {
        if (method_exists($exception, 'getStatusCode')) {
            return $exception->getStatusCode();
        }
        $statusCode = $exception->getCode();
        if (in_array($statusCode, array_keys(Response::$statusTexts))) {
            return $statusCode;
        }

        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
