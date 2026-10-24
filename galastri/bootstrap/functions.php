<?php
function vardump(mixed ...$values): void
{
    \VarDump::addBacktraceLevel();
    \VarDump::print(...$values);
}

function requireIfExists(string $filePath): mixed
{
    if (!is_file($filePath)) {
        return false;
    }

    return require($filePath);
}




// -----------------------------------------------------------------------------
// ◀️ VOLTAR AO TÓPICO ANTERIOR:
// LINK: /galastri/bootstrap/bootstrap.php
