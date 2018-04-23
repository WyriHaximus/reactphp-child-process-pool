<?php

if (!interface_exists('React\EventLoop\Timer\TimerInterface')) {
    class_alias('React\EventLoop\TimerInterface', 'React\EventLoop\Timer\TimerInterface');
}
