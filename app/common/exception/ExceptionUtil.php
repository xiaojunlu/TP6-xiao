<?php

namespace app\common\exception;

use think\exception\HttpException;
use app\common\ExceptionPrintingToolkit;

class ExceptionUtil
{
    public static function getErrorAndHttpCodeFromException(\Exception $exception, $isDebug)
    {
        $error = array();
        if ($exception instanceof HttpException) {
            $error['message'] = $exception->getMessage();
            $error['code'] = $exception->getCode();
            $httpCode = $exception->getStatusCode();
        } else {
            $error['message'] = '服务器开小差了';
            $error['code'] = $exception->getCode() ?: ErrorCode::INTERNAL_SERVER_ERROR;
            $httpCode = 500;
        }

        if ($isDebug) {
            $error['trace'] = ExceptionPrintingToolkit::printTraceAsArray($exception);
        }

        return array($error, $httpCode);
    }
}