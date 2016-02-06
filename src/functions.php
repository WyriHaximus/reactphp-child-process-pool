<?php

namespace WyriHaximus\React\ChildProcess\Pool;

/**
 * @param string $instanceOf
 * @param array $options
 * @param string $key
 * @param string $fallback
 * @return string
 */
function getClassNameFromOptionOrDefault(array $options, $key, $instanceOf, $fallback)
{
    if (isset($options[$key]) && is_a($options[$key], $instanceOf, true)) {
        return $options[$key];
    }

    return $fallback;
}