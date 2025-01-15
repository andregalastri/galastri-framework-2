<?php

function vardump(mixed ...$values): void
{
    \VarDump::addBacktraceLevel();
    \VarDump::print(...$values);
}
