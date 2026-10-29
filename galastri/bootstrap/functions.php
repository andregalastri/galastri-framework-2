<?php
/**
 * -----------------------------------------------------------------------------
 * 📂
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                  ARQUIVO /galastri/bootstrap/functions.php                ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 * Este arquivo declara funções auxiliares que ficam disponíveis para uso em
 * diferentes partes do framework e da aplicação de maneira global.
 * ─────────────────────────────────────────────────────────────────────────────
 */


/**
 * 🟢 FUNÇÃO GLOBAL vardump()
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * A função `vardump()` é um atalho para o método `\VarDump::print()`. Ela exibe
 * informações sobre os valores recebidos da mesma forma que o var_dump()
 * convencional do PHP, mas com uma formatação HTML que torna o resultado mais
 * legível.
 *
 * ✔️ EXEMPLO
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ $nome = 'André';                                                          │
 * │ $idade = 35;                                                              │
 * │                                                                           │
 * │ vardump($nome, $idade);                                                   │
 * │                                                                           │
 * │ A chamada exibe os dois valores, com seus respectivos tipos, além do      │
 * │ arquivo e da linha em que `vardump()` foi chamada.                        │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * Antes de exibir os valores, `addBacktraceLevel()` ajusta o nível consultado
 * na pilha de chamadas. Essa pilha registra o caminho de chamadas que levou até
 * a execução do método.
 *
 * Esse ajuste faz o `VarDump` mostrar o local onde `vardump()` foi chamada. Sem
 * ele, a origem exibida seria a chamada a este arquivo aqui.
 *
 * ℹ️ OBSERVAÇÃO  
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ A função não interrompe a execução de demais códigos que estejam após a   │
 * │ chamada. O código seguinte continua sendo executado normalmente.          │
 * │                                                                           │
 * │ Orienta-se adicionar um exit() logo após a chamada do vardump() pra       │
 * │ facilitar a depuração dos resultados.                                     │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * Aproveite para ver sobre a classe VarDump agora, se ainda não viu:  
 * 🔗 LINK: /galastri/modules/VarDump.php
 *
 * ---
 * @param mixed ...$values  
 * :: Valores que serão exibidos para depuração.
 * 
 * @return void
 */

function vardump(mixed ...$values): void
{
    \VarDump::addBacktraceLevel();
    \VarDump::print(...$values);
}



/**
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * ◀️ VOLTAR AO TÓPICO ANTERIOR:
 * 🔗 LINK: /galastri/bootstrap/bootstrap.php
 */
