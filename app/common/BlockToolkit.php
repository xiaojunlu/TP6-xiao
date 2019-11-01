<?php

namespace app\common;

class BlockToolkit
{
    public static function render($block, $container)
    {

        if (empty($block['template_name']) || empty($block['data'])) {
            return '';
        }

        return $container['view']->fetch($block['template_name'], $block['data']);
    }

}