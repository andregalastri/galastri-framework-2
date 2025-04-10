<?php
/**
 * Documentação:
 * https://github.com/andregalastri/galastri-framework-2/wiki/Bootstrap
 */

function vardump(mixed ...$values): void
{
    \VarDump::addBacktraceLevel();
    \VarDump::print(...$values);
}
