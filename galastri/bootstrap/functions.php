<?php
function vardump(mixed ...$values): void
{
    \VarDump::addBacktraceLevel();
    \VarDump::print(...$values);
}



// -----------------------------------------------------------------------------
// ◀️ VOLTAR AO TÓPICO ANTERIOR:
// LINK: /galastri/bootstrap/bootstrap.php
