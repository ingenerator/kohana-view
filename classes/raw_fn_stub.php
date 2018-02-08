<?php
/**
 * This function is never actually called - the template compiler uses it as a marker to render
 * the value directly without escaping it. It is defined here to allow IDE's to cope without marking
 * it as an undefined function call.
 *
 * @param string $value
 *
 * @return string
 */
function raw($value)
{
    throw new \BadFunctionCallException('Unexpected call to '.__FUNCTION__);
}
