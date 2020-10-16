<?php
declare(strict_types = 1);
ini_set('memory_limit','1G');
Swoole\Coroutine\run(function ()
{
    for ($i = 0;$i<1000000;$i++){
        \Swoole\Coroutine::create(function (){
            Swoole\Coroutine::sleep(1);
        });
    }
});



