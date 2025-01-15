<?php

function vardump(mixed ...$values): void
{
    \VarDump::print(...$values);
}
