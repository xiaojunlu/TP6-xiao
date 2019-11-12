<?php

namespace app;

class Viewer
{
    public function view($result, $status = 200)
    {
        $request = app('request');
        $isEnvelop = $request->param('envelope', false);

        if ($isEnvelop) {
            $result = array(
                'status' => $status,
                'headers' => array(),
                'response' => $result,
            );
        }

        return json($result, $status)->header(['Access-Control-Allow-Origin' => '*']);
    }
}
